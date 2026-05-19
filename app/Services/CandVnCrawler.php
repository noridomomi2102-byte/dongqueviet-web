<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;

class CandVnCrawler
{
    /**
     * @var array<string, string|array{rss: string, month?: int, year?: int}>
     */
    protected const RSS_MAP = [
        'chong-dien-bien-hoa-binh' => 'https://cand.vn/rss/chong-dien-bien-hoa-binh-1456.rss',
        'tin-tuc-su-kien' => [
            'rss' => 'https://cand.vn/rss/thoi-su/su-kien-binh-luan-thoi-su-1416.rss',
            'month' => 5,
            'year' => 2026,
        ],
        'van-de-hom-nay' => [
            'rss' => 'https://cand.vn/rss/thoi-su/van-de-hom-nay-thoi-su-1417.rss',
        ],
    ];

    /** XPath selectors for nodes to remove from article body */
    protected const REMOVE_BODY_XPATHS = [
        '//div[contains(@class,"features")]',
        '//div[contains(@class,"features")]',
        '//div[contains(@class,"function")]',
        '//div[contains(@class,"wrap-btn-share")]',
        '//div[contains(@class,"list-share")]',
        '//div[contains(@class,"social-share")]',
        '//div[contains(@class,"wrap-btn-menu")]',
        '//div[contains(@class,"banner")]',
        '//div[contains(@class,"banner")]',
        '//div[contains(@id,"ads")]',
    ];

    public function __construct(
        protected int $delayMs = 600,
    ) {}

    /**
     * @return array{imported: int, skipped: int, failed: int}
     */
    public function crawlCategory(
        string $categorySlug,
        int $limit = 30,
        bool $force = false,
        ?int $month = null,
        ?int $year = null,
    ): array {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $feed = self::RSS_MAP[$categorySlug] ?? null;

        if (! $feed) {
            throw new \InvalidArgumentException("Chưa cấu hình RSS cho slug: {$categorySlug}");
        }

        if (is_string($feed)) {
            $rssUrl = $feed;
        } else {
            $rssUrl = $feed['rss'];
            $month = $month ?? ($feed['month'] ?? null);
            $year = $year ?? ($feed['year'] ?? null);
        }

        $admin = User::first();
        $items = $this->fetchRssItems($rssUrl, $limit, $month, $year);
        $stats = ['imported' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($items as $item) {
            $sourceUrl = $item['url'];

            if (! $force && Post::where('source_url', $sourceUrl)->exists()) {
                $stats['skipped']++;
                continue;
            }

            try {
                $article = $this->fetchArticle($sourceUrl);
                $slug = $this->slugFromSourceUrl($sourceUrl);

                Post::updateOrCreate(
                    ['source_url' => $sourceUrl],
                    [
                        'category_id' => $category->id,
                        'user_id' => $admin?->id,
                        'title' => $article['title'] ?: $item['title'],
                        'slug' => $slug,
                        'excerpt' => $article['excerpt'] ?: Str::limit(strip_tags($article['content']), 300),
                        'content' => $article['content'].($article['source_note'] ?? ''),
                        'featured_image' => $article['featured_image'],
                        'meta_title' => $article['title'] ?: $item['title'],
                        'meta_description' => Str::limit(strip_tags($article['excerpt'] ?: $article['content']), 160),
                        'status' => 'published',
                        'published_at' => $article['published_at'] ?? $item['published_at'] ?? now(),
                    ]
                );

                $stats['imported']++;
            } catch (\Throwable $e) {
                $stats['failed']++;
                report($e);
            }

            usleep($this->delayMs * 1000);
        }

        return $stats;
    }

    /** @return list<array{url: string, title: string, published_at: ?Carbon}> */
    protected function fetchRssItems(string $rssUrl, int $limit, ?int $month = null, ?int $year = null): array
    {
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; TinTucBot/1.0)'])
            ->get($rssUrl);

        $response->throw();

        $xml = new SimpleXMLElement($response->body());
        $items = [];
        $scanned = 0;
        $maxScan = $month !== null ? max($limit * 20, 200) : $limit;

        foreach ($xml->channel->item as $node) {
            if (count($items) >= $limit) {
                break;
            }

            if ($month !== null && ++$scanned > $maxScan) {
                break;
            }

            $link = trim((string) ($node->link ?: $node->guid));
            if ($link === '' || ! str_contains($link, 'cand.vn')) {
                continue;
            }

            $publishedAt = $this->parseDate((string) ($node->pubDate ?: $node->updated));

            if ($month !== null) {
                $filterYear = $year ?? (int) now()->format('Y');
                if (! $publishedAt || $publishedAt->month !== $month || $publishedAt->year !== $filterYear) {
                    continue;
                }
            }

            $items[] = [
                'url' => $link,
                'title' => trim(strip_tags((string) $node->title)),
                'published_at' => $publishedAt,
            ];
        }

        return $items;
    }

    /** @return array{title: string, excerpt: string, content: string, featured_image: ?string, published_at: ?Carbon, source_note: string} */
    protected function fetchArticle(string $url): array
    {
        $response = Http::timeout(45)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; TinTucBot/1.0)'])
            ->get($url);

        $response->throw();
        $html = $response->body();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $title = $this->nodeText($xpath, "//*[contains(@class,'article__title')]");
        if ($title === '') {
            $title = $this->metaContent($xpath, 'og:title');
        }

        $excerpt = $this->extractSapoText($xpath);

        $bodyNode = $xpath->query("//*[contains(@class,'article__body')]")->item(0);
        $content = '';
        if ($bodyNode) {
            $this->removeUnwantedNodes($xpath, $bodyNode);
            $content = $this->innerHtml($bodyNode);
            $content = $this->cleanContentHtml($content);
            $content = $this->processImagesInHtml($content, $url);
        }

        if ($content === '') {
            throw new \RuntimeException('Không tìm thấy nội dung bài viết.');
        }

        // 1. Thêm sapo (đoạn mở đầu) vào đầu bài
        $sapoBlock = $this->buildSapoBlock($xpath, $excerpt);
        $content = $sapoBlock.$content;

        $imageUrl = $this->metaContent($xpath, 'og:image');
        if ($imageUrl === '' || str_contains($imageUrl, 'logo_share')) {
            $imageUrl = $this->firstRealImageInHtml($content);
        }

        $featuredImage = $imageUrl ? $this->downloadImage($imageUrl, $url) : null;
        $publishedAt = $this->parseDate($this->metaContent($xpath, 'article:published_time'));

        $sourceNote = '<p class="article-source" style="margin-top:1.5rem;font-size:.9rem;color:#666"><em>'
            .'Nguồn: <a href="'.e($url).'" target="_blank" rel="nofollow noopener">Báo Công an nhân dân (cand.vn)</a></em></p>';

        return [
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'excerpt' => html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'content' => $content,
            'featured_image' => $featuredImage,
            'published_at' => $publishedAt,
            'source_note' => $sourceNote,
        ];
    }

    protected function extractSapoText(\DOMXPath $xpath): string
    {
        $sapoNode = $xpath->query("//*[contains(@class,'article__sapo')]")->item(0);
        if (! $sapoNode) {
            return $this->metaContent($xpath, 'og:description');
        }

        $text = trim($sapoNode->textContent);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    protected function buildSapoBlock(\DOMXPath $xpath, string $fallbackText): string
    {
        $sapoNode = $xpath->query("//*[contains(@class,'article__sapo')]")->item(0);

        if ($sapoNode) {
            $inner = $this->innerHtml($sapoNode);
            $inner = preg_replace('/<i[^>]*class=["\']cand["\'][^>]*>\s*<\/i>/i', '', $inner) ?? $inner;

            return '<div class="article-sapo">'.$inner.'</div>';
        }

        if ($fallbackText !== '') {
            return '<div class="article-sapo"><p>'.e($fallbackText).'</p></div>';
        }

        return '';
    }

    protected function removeUnwantedNodes(\DOMXPath $xpath, \DOMNode $contextNode): void
    {
        foreach (self::REMOVE_BODY_XPATHS as $query) {
            $nodes = $xpath->query($query, $contextNode);
            if (! $nodes) {
                continue;
            }

            $toRemove = [];
            foreach ($nodes as $node) {
                $toRemove[] = $node;
            }
            foreach ($toRemove as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        // Xóa img placeholder 1x1 gif còn sót (sẽ thay bằng ảnh thật sau)
        $imgs = $xpath->query('.//img', $contextNode);
        if ($imgs) {
            foreach ($imgs as $img) {
                if ($this->isPlaceholderImage($img->getAttribute('src'))
                    && ! $img->getAttribute('data-src')
                    && ! $img->getAttribute('data-large-src')) {
                    $img->parentNode?->removeChild($img);
                }
            }
        }
    }

    protected function processImagesInHtml(string $html, string $articleUrl): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $m) use ($articleUrl) {
            $tag = $m[0];
            $realUrl = $this->resolveImageUrlFromTag($tag);

            if (! $realUrl) {
                return '';
            }

            $local = $this->downloadImage($realUrl, $articleUrl);
            if (! $local) {
                return '';
            }

            $publicUrl = '/storage/'.$local;
            $alt = '';
            if (preg_match('/\btitle=["\']([^"\']*)["\']/i', $tag, $a)) {
                $alt = $a[1];
            } elseif (preg_match('/\balt=["\']([^"\']*)["\']/i', $tag, $a)) {
                $alt = $a[1];
            }

            $caption = $alt !== '' ? '<p class="article-img-caption"><em>'.e(html_entity_decode($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8')).'</em></p>' : '';

            return '<figure class="article-figure">'
                .'<img src="'.e($publicUrl).'" alt="'.e($alt).'" loading="lazy">'
                .$caption
                .'</figure>';
        }, $html) ?? $html;
    }

    protected function resolveImageUrlFromTag(string $imgTag): ?string
    {
        $attrs = ['data-large-src', 'data-src', 'data-original', 'data-lazy-src', 'src'];

        foreach ($attrs as $attr) {
            if (preg_match('/\b'.preg_quote($attr, '/').'=["\']([^"\']+)["\']/i', $imgTag, $m)) {
                $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($url && ! $this->isPlaceholderImage($url) && (str_starts_with($url, 'http') || str_starts_with($url, '//'))) {
                    return str_starts_with($url, '//') ? 'https:'.$url : $url;
                }
            }
        }

        return null;
    }

    protected function isPlaceholderImage(?string $url): bool
    {
        if (! $url) {
            return true;
        }

        return str_contains($url, 'data:image/gif')
            || str_contains($url, 'base64,R0lGODlhAQAB');
    }

    protected function slugFromSourceUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $base = basename($path, '.html');
        $slug = preg_replace('/-post\d+$/', '', $base) ?: $base;

        $slug = Str::slug($slug);
        if ($slug === '') {
            $slug = 'bai-viet-'.substr(md5($url), 0, 8);
        }

        if (Post::where('slug', $slug)->where('source_url', '!=', $url)->exists()) {
            $slug .= '-'.substr(md5($url), 0, 6);
        }

        return $slug;
    }

    protected function downloadImage(string $imageUrl, string $articleUrl): ?string
    {
        try {
            if (str_starts_with($imageUrl, '//')) {
                $imageUrl = 'https:'.$imageUrl;
            }

            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; TinTucBot/1.0)',
                    'Referer' => $articleUrl,
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                ])
                ->get($imageUrl);

            if (! $response->successful()) {
                return null;
            }

            $pathExt = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: '');
            $contentType = strtolower($response->header('Content-Type') ?? '');

            $ext = match (true) {
                str_contains($contentType, 'avif') => 'avif',
                str_contains($contentType, 'webp') => 'webp',
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'gif') => 'gif',
                in_array($pathExt, ['avif', 'webp', 'png', 'gif', 'jpg', 'jpeg'], true) => $pathExt,
                default => 'jpg',
            };

            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }

            $filename = 'posts/cand/'.md5($imageUrl).'.'.$ext;
            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    protected function nodeText(\DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query)->item(0);

        return $node ? trim($node->textContent) : '';
    }

    protected function metaContent(\DOMXPath $xpath, string $property): string
    {
        $node = $xpath->query("//meta[@property='{$property}']")->item(0)
            ?: $xpath->query("//meta[@name='{$property}']")->item(0);

        return $node ? trim($node->getAttribute('content')) : '';
    }

    protected function innerHtml(\DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    protected function cleanContentHtml(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', $html) ?? $html;

        // 2. Gỡ block chia sẻ / toolbar còn sót
        $html = preg_replace('#<div[^>]*class="[^"]*social-share[^"]*"[^>]*>.*?</div>#is', '', $html) ?? $html;
        $html = preg_replace('#<div[^>]*class="[^"]*list-share[^"]*"[^>]*>.*?</div>#is', '', $html) ?? $html;
        $html = preg_replace('#<div[^>]*class="[^"]*wrap-btn-share[^"]*"[^>]*>.*?</div>#is', '', $html) ?? $html;
        $html = preg_replace('#<div[^>]*class="[^"]*features[^"]*"[^>]*>.*?</div>#is', '', $html) ?? $html;
        $html = preg_replace('#<div[^>]*class="[^"]*function[^"]*"[^>]*>.*?</div>#is', '', $html) ?? $html;
        $html = preg_replace('#Facebook\s*Email\s*Copy\s*link#i', '', $html) ?? $html;

        return trim($html);
    }

    protected function firstRealImageInHtml(string $html): string
    {
        if (preg_match_all('/<img\b[^>]+>/i', $html, $matches)) {
            foreach ($matches[0] as $tag) {
                $url = $this->resolveImageUrlFromTag($tag);
                if ($url) {
                    return $url;
                }
            }
        }

        return '';
    }
}
