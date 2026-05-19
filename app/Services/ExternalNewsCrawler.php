<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;

class ExternalNewsCrawler
{
    /**
     * @var array<string, array{driver: string, rss?: string, list_url?: string}>
     */
    protected const SOURCES = [
        'tin-gia' => [
            'driver' => 'tingia',
            'rss' => 'https://tingia.gov.vn/rss',
            'list_urls' => [
                'https://tingia.gov.vn/linh-vuc',
                'https://tingia.gov.vn/tai-chinh-ngan-hang',
                'https://tingia.gov.vn/suc-khoe-cong-dong',
                'https://tingia.gov.vn/quyen-loi-nguoi-dan',
            ],
            'latest_only' => true,
            'default_limit' => 15,
        ],
        'phap-luat' => [
            'driver' => 'dantri_rss',
            'rss' => 'https://dantri.com.vn/rss/phap-luat.rss',
        ],
        'doi-song' => [
            'driver' => 'dantri_rss',
            'rss' => 'https://dantri.com.vn/rss/doi-song.rss',
        ],
    ];

    protected const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (compatible; TinTucBot/1.0)';

    public function __construct(
        protected int $delayMs = 700,
    ) {}

    /**
     * @return array{imported: int, skipped: int, failed: int}
     */
    public function crawlCategory(
        string $categorySlug,
        int $limit = 50,
        bool $force = false,
        ?int $month = null,
        ?int $year = null,
        bool $allowFallback = true,
    ): array {
        $config = self::SOURCES[$categorySlug] ?? null;
        if (! $config) {
            throw new \InvalidArgumentException("Chưa cấu hình nguồn crawl cho slug: {$categorySlug}");
        }

        $category = Category::where('slug', $categorySlug)->where('is_active', true)->firstOrFail();

        if (isset($config['default_limit']) && $limit === 50) {
            $limit = (int) $config['default_limit'];
        }

        $month = $month ?? 5;
        $year = $year ?? 2026;
        $latestOnly = ($config['latest_only'] ?? false) || ($config['driver'] === 'tingia' && $allowFallback);

        $relaxMonthFilter = false;
        if ($config['driver'] === 'tingia') {
            $tingiaResult = $this->fetchTingiaItems($config, $limit, $latestOnly ? null : $month, $latestOnly ? null : $year);
            $items = $tingiaResult['items'];
            $relaxMonthFilter = $latestOnly || $tingiaResult['fallback'];
        } else {
            $items = match ($config['driver']) {
                'dantri_rss' => $this->fetchDantriRssItems($config['rss'], $limit, $month, $year),
                default => throw new \InvalidArgumentException('Driver không hỗ trợ: '.$config['driver']),
            };
        }

        $admin = User::first();
        $stats = ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'fallback' => $relaxMonthFilter];

        foreach ($items as $item) {
            $sourceUrl = $item['url'];

            if (! $force && Post::where('source_url', $sourceUrl)->exists()) {
                $stats['skipped']++;
                continue;
            }

            try {
                $article = $config['driver'] === 'tingia'
                    ? $this->fetchTingiaArticle($sourceUrl)
                    : $this->fetchDantriArticle($sourceUrl);

                $publishedAt = $article['published_at'] ?? $item['published_at'] ?? null;
                if (! $relaxMonthFilter && $publishedAt && ($publishedAt->month !== $month || $publishedAt->year !== $year)) {
                    $stats['skipped']++;
                    continue;
                }

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
                        'published_at' => $publishedAt ?? now(),
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
    protected function fetchDantriRssItems(string $rssUrl, int $limit, int $month, int $year): array
    {
        $response = Http::timeout(30)->withHeaders(['User-Agent' => self::USER_AGENT])->get($rssUrl);
        $response->throw();

        $xmlBody = preg_replace('/<\?xml-stylesheet[^>]*\?>/', '', $response->body()) ?? $response->body();
        $xml = new SimpleXMLElement($xmlBody);
        $items = [];

        foreach ($xml->channel->item as $node) {
            if (count($items) >= $limit) {
                break;
            }

            $link = trim((string) ($node->link ?: $node->guid));
            if ($link === '' || ! str_contains($link, 'dantri.com.vn')) {
                continue;
            }

            $publishedAt = $this->parseDate((string) ($node->pubDate ?: ''));
            if (! $publishedAt || $publishedAt->month !== $month || $publishedAt->year !== $year) {
                continue;
            }

            $items[] = [
                'url' => $link,
                'title' => trim(strip_tags((string) $node->title)),
                'published_at' => $publishedAt,
            ];
        }

        return $items;
    }

    /**
     * @param  array{rss?: string, list_urls?: list<string>, latest_only?: bool}  $config
     * @return array{items: list<array{url: string, title: string, published_at: ?Carbon}>, fallback: bool}
     */
    protected function fetchTingiaItems(array $config, int $limit, ?int $month, ?int $year): array
    {
        $listUrls = $config['list_urls'] ?? (isset($config['list_url']) ? [$config['list_url']] : []);

        if ($config['latest_only'] ?? false) {
            return [
                'items' => $this->collectTingiaLatest($config['rss'] ?? null, $listUrls, $limit),
                'fallback' => false,
            ];
        }

        $candidates = $this->collectTingiaLatest($config['rss'] ?? null, $listUrls, max($limit * 3, 30));

        $filtered = [];
        foreach ($candidates as $candidate) {
            $publishedAt = $candidate['published_at'];
            if (! $publishedAt || $publishedAt->month !== $month || $publishedAt->year !== $year) {
                continue;
            }
            $filtered[] = $candidate;
            if (count($filtered) >= $limit) {
                break;
            }
        }

        if ($filtered !== []) {
            return ['items' => $filtered, 'fallback' => false];
        }

        return [
            'items' => array_slice($candidates, 0, $limit),
            'fallback' => true,
        ];
    }

    /**
     * Lấy N bài mới nhất từ tingia (ưu tiên RSS, sau đó trang listing).
     *
     * @param  list<string>  $listUrls
     * @return list<array{url: string, title: string, published_at: ?Carbon}>
     */
    protected function collectTingiaLatest(?string $rssUrl, array $listUrls, int $limit): array
    {
        $pending = [];

        if ($rssUrl) {
            foreach ($this->fetchTingiaRssLinks($rssUrl) as $link) {
                $pending[$link['url']] = $link['title'];
            }
        }

        foreach ($listUrls as $listUrl) {
            try {
                foreach ($this->extractTingiaLinksFromHtml($this->httpGet($listUrl)) as $link) {
                    $pending[$link['url']] ??= $link['title'];
                }
            } catch (\Throwable) {
                continue;
            }

            if (count($pending) >= $limit * 2) {
                break;
            }
        }

        $candidates = [];
        foreach ($pending as $articleUrl => $fallbackTitle) {
            try {
                $meta = $this->fetchTingiaArticleMeta($articleUrl);
            } catch (\Throwable) {
                continue;
            }

            if (! $meta['published_at']) {
                continue;
            }

            $candidates[] = [
                'url' => $articleUrl,
                'title' => $meta['title'] ?: $fallbackTitle,
                'published_at' => $meta['published_at'],
            ];

            usleep(120000);
        }

        usort($candidates, fn ($a, $b) => $b['published_at']->timestamp <=> $a['published_at']->timestamp);

        return array_slice($candidates, 0, $limit);
    }

    /** @return list<array{url: string, title: string}> */
    protected function fetchTingiaRssLinks(string $rssUrl): array
    {
        $response = Http::timeout(30)->withHeaders(['User-Agent' => self::USER_AGENT])->get($rssUrl);
        if (! $response->successful()) {
            return [];
        }

        $xmlBody = preg_replace('/<\?xml-stylesheet[^>]*\?>/', '', $response->body()) ?? $response->body();
        try {
            $xml = new SimpleXMLElement($xmlBody);
        } catch (\Throwable) {
            return [];
        }
        if (! isset($xml->channel->item)) {
            return [];
        }

        $links = [];
        foreach ($xml->channel->item as $node) {
            $url = trim((string) ($node->link ?: ''));
            if ($url === '' || ! str_contains($url, 'tingia.gov.vn')) {
                continue;
            }
            $links[] = [
                'url' => $url,
                'title' => trim(strip_tags((string) $node->title)),
            ];
        }

        return $links;
    }

    /** @return list<array{url: string, title: string}> */
    protected function extractTingiaLinksFromHtml(string $html): array
    {
        $links = [];

        if (preg_match_all(
            '#href="(https://tingia\.gov\.vn/(?!upload/|public/|in-bai-viet|wp-content)[^"]+\.html)"(?:[^>]*title="([^"]*)")?#iu',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $links[$url] = [
                    'url' => $url,
                    'title' => html_entity_decode($match[2] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ];
            }
        }

        return array_values($links);
    }

    /** @return array{title: string, published_at: ?Carbon} */
    protected function fetchTingiaArticleMeta(string $url): array
    {
        $html = $this->httpGet($url);
        $xpath = $this->domXPath($html);

        $title = $this->nodeText($xpath, "//h1[contains(@class,'post-title')]");
        $dateText = $this->nodeText($xpath, "//*[contains(@class,'date') and contains(@class,'meta-item')]")
            ?: $this->metaContent($xpath, 'article:published_time');

        $publishedAt = $this->parseTingiaDate($dateText) ?? $this->parseDate($dateText);

        return [
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'published_at' => $publishedAt,
        ];
    }

    /** @return array{title: string, excerpt: string, content: string, featured_image: ?string, published_at: ?Carbon, source_note: string} */
    protected function fetchTingiaArticle(string $url): array
    {
        $html = $this->httpGet($url);
        $xpath = $this->domXPath($html);

        $title = $this->nodeText($xpath, "//h1[contains(@class,'post-title')]");
        if ($title === '') {
            $title = $this->metaContent($xpath, 'og:title');
        }

        $excerpt = $this->nodeText($xpath, "//*[contains(@class,'content-detail-sapo')]");
        if ($excerpt === '') {
            $excerpt = $this->metaContent($xpath, 'og:description');
        }

        $bodyNode = $xpath->query("//*[@id='maincontent' or contains(@class,'maincontent')]")->item(0)
            ?: $xpath->query("//*[contains(@class,'entry-content')]//*[contains(@class,'maincontent')]")->item(0);

        $content = '';
        if ($bodyNode) {
            $this->removeTingiaUnwanted($xpath, $bodyNode);
            $content = $this->cleanContentHtml($this->innerHtml($bodyNode));
            $content = $this->processImagesInHtml($content, $url, 'tingia');
        }

        if ($content === '') {
            throw new \RuntimeException('Không tìm thấy nội dung bài viết tingia.');
        }

        if ($excerpt !== '') {
            $content = '<div class="article-sapo"><p>'.e($excerpt).'</p></div>'.$content;
        }

        $imageUrl = $this->metaContent($xpath, 'og:image');
        if ($imageUrl === '' || str_contains($imageUrl, 'logo')) {
            $imageUrl = $this->firstRealImageInHtml($content);
        }

        $dateText = $this->nodeText($xpath, "//*[contains(@class,'date') and contains(@class,'meta-item')]");

        return [
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'excerpt' => html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'content' => $content,
            'featured_image' => $imageUrl ? $this->downloadImage($imageUrl, $url, 'tingia') : null,
            'published_at' => $this->parseTingiaDate($dateText),
            'source_note' => '<p class="article-source" style="margin-top:1.5rem;font-size:.9rem;color:#666"><em>'
                .'Nguồn: <a href="'.e($url).'" target="_blank" rel="nofollow noopener">tingia.gov.vn</a></em></p>',
        ];
    }

    /** @return array{title: string, excerpt: string, content: string, featured_image: ?string, published_at: ?Carbon, source_note: string} */
    protected function fetchDantriArticle(string $url): array
    {
        $html = $this->httpGet($url);
        $xpath = $this->domXPath($html);

        $title = $this->nodeText($xpath, '//article//h1');
        if ($title === '') {
            $title = $this->metaContent($xpath, 'og:title');
        }

        $excerpt = $this->nodeText($xpath, '//article//h2');
        if ($excerpt === '') {
            $excerpt = $this->metaContent($xpath, 'og:description');
        }
        $excerpt = preg_replace('/^\(Dân trí\)\s*[-–—]\s*/u', '', $excerpt) ?? $excerpt;

        $contentNode = $this->findDantriContentNode($xpath);

        $content = '';
        if ($contentNode) {
            $content = $this->cleanContentHtml($this->innerHtml($contentNode));
            $content = $this->processImagesInHtml($content, $url, 'dantri');
        }

        if ($content === '' && $excerpt !== '') {
            $content = '<p>'.e($excerpt).'</p>';
        }

        if ($content === '') {
            throw new \RuntimeException('Không tìm thấy nội dung bài viết Dân trí.');
        }

        if ($excerpt !== '') {
            $content = '<div class="article-sapo"><p>'.e($excerpt).'</p></div>'.$content;
        }

        $imageUrl = $this->metaContent($xpath, 'og:image');
        if ($imageUrl === '') {
            $imageUrl = $this->firstRealImageInHtml($content);
        }

        $publishedAt = $this->parseDate($this->metaContent($xpath, 'article:published_time'));
        if (! $publishedAt && preg_match('#/(\d{8})\d+#', $url, $m)) {
            $publishedAt = $this->parseDate($m[1]);
        }

        return [
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'excerpt' => html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'content' => $content,
            'featured_image' => $imageUrl ? $this->downloadImage($imageUrl, $url, 'dantri') : null,
            'published_at' => $publishedAt,
            'source_note' => '<p class="article-source" style="margin-top:1.5rem;font-size:.9rem;color:#666"><em>'
                .'Nguồn: <a href="'.e($url).'" target="_blank" rel="nofollow noopener">dantri.com.vn</a></em></p>',
        ];
    }

    protected function findDantriContentNode(\DOMXPath $xpath): ?\DOMNode
    {
        foreach ($xpath->query('//article/div') as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }
            $class = trim($node->getAttribute('class'));
            if ($class !== '') {
                continue;
            }
            if (strlen(trim($node->textContent)) < 100) {
                continue;
            }

            return $node;
        }

        $best = null;
        $bestLen = 0;
        foreach ($xpath->query('//article//div | //article//section') as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }
            $len = strlen(trim($node->textContent));
            if ($len > $bestLen) {
                $bestLen = $len;
                $best = $node;
            }
        }

        if ($best && $bestLen >= 100) {
            return $best;
        }

        $best = null;
        $bestLen = 0;
        foreach ($xpath->query('//article//div') as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }
            $len = strlen(trim($node->textContent));
            if ($len > $bestLen) {
                $bestLen = $len;
                $best = $node;
            }
        }

        return ($best && $bestLen >= 100) ? $best : null;
    }

    protected function parseTingiaDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);

        if (preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})\s*-\s*(\d{1,2}):(\d{2})#', $value, $m)) {
            try {
                return Carbon::create((int) $m[3], (int) $m[2], (int) $m[1], (int) $m[4], (int) $m[5], 0, config('app.timezone'));
            } catch (\Throwable) {
                return null;
            }
        }

        if (preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $value, $m)) {
            try {
                return Carbon::create((int) $m[3], (int) $m[2], (int) $m[1], 0, 0, 0, config('app.timezone'));
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function removeTingiaUnwanted(\DOMXPath $xpath, \DOMNode $context): void
    {
        foreach (['.//script', './/style', './/*[contains(@class,"share")]', './/*[contains(@class,"sharing")]'] as $q) {
            $nodes = $xpath->query($q, $context);
            if (! $nodes) {
                continue;
            }
            $remove = [];
            foreach ($nodes as $node) {
                $remove[] = $node;
            }
            foreach ($remove as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    protected function httpGet(string $url): string
    {
        $response = Http::timeout(45)
            ->withHeaders(['User-Agent' => self::USER_AGENT])
            ->get($url);

        $response->throw();

        return $response->body();
    }

    protected function domXPath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        return new \DOMXPath($dom);
    }

    protected function slugFromSourceUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $base = basename($path, '.html');
        $base = basename($base, '.htm');
        $slug = Str::slug($base);

        if ($slug === '') {
            $slug = 'bai-viet-'.substr(md5($url), 0, 8);
        }

        if (Post::where('slug', $slug)->where('source_url', '!=', $url)->exists()) {
            $slug .= '-'.substr(md5($url), 0, 6);
        }

        return $slug;
    }

    protected function downloadImage(string $imageUrl, string $articleUrl, string $folder): ?string
    {
        try {
            if (str_starts_with($imageUrl, '//')) {
                $imageUrl = 'https:'.$imageUrl;
            }

            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Referer' => $articleUrl,
                    'Accept' => 'image/*,*/*;q=0.8',
                ])
                ->get($imageUrl);

            if (! $response->successful()) {
                return null;
            }

            $pathExt = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: '');
            $contentType = strtolower($response->header('Content-Type') ?? '');

            $ext = match (true) {
                str_contains($contentType, 'webp') => 'webp',
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'gif') => 'gif',
                in_array($pathExt, ['webp', 'png', 'gif', 'jpg', 'jpeg'], true) => $pathExt === 'jpeg' ? 'jpg' : $pathExt,
                default => 'jpg',
            };

            $filename = 'posts/'.$folder.'/'.md5($imageUrl).'.'.$ext;
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

        if (preg_match('#^(\d{8})$#', trim($value), $m)) {
            try {
                return Carbon::createFromFormat('Ymd', $m[1], config('app.timezone'));
            } catch (\Throwable) {
                return null;
            }
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

        return $node ? trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '') : '';
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

        return trim($html);
    }

    protected function processImagesInHtml(string $html, string $articleUrl, string $folder): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $m) use ($articleUrl, $folder) {
            $tag = $m[0];
            $realUrl = $this->resolveImageUrlFromTag($tag);

            if (! $realUrl) {
                return '';
            }

            $local = $this->downloadImage($realUrl, $articleUrl, $folder);
            if (! $local) {
                return '';
            }

            $publicUrl = '/storage/'.$local;
            $alt = '';
            if (preg_match('/\balt=["\']([^"\']*)["\']/i', $tag, $a)) {
                $alt = $a[1];
            }

            return '<figure class="article-figure"><img src="'.e($publicUrl).'" alt="'.e($alt).'" loading="lazy"></figure>';
        }, $html) ?? $html;
    }

    protected function resolveImageUrlFromTag(string $imgTag): ?string
    {
        foreach (['data-src', 'data-original', 'src'] as $attr) {
            if (preg_match('/\b'.preg_quote($attr, '/').'=["\']([^"\']+)["\']/i', $imgTag, $m)) {
                $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($url && str_starts_with($url, 'http')) {
                    return $url;
                }
                if ($url && str_starts_with($url, '//')) {
                    return 'https:'.$url;
                }
            }
        }

        return null;
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
