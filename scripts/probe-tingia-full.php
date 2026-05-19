<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ua = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'];

$listUrls = [
    'https://tingia.gov.vn/linh-vuc',
    'https://tingia.gov.vn/tai-chinh-ngan-hang',
    'https://tingia.gov.vn/suc-khoe-cong-dong',
    'https://tingia.gov.vn/quyen-loi-nguoi-dan',
    'https://tingia.gov.vn/',
];

$month = (int) ($argv[1] ?? 5);
$year = (int) ($argv[2] ?? 2026);

$crawler = new App\Services\ExternalNewsCrawler();
$ref = new ReflectionClass($crawler);
$metaMethod = $ref->getMethod('fetchTingiaArticleMeta');
$metaMethod->setAccessible(true);
$parseMethod = $ref->getMethod('parseTingiaDate');
$parseMethod->setAccessible(true);

echo "=== Scan for {$month}/{$year} ===\n";

foreach ($listUrls as $listUrl) {
    $r = Illuminate\Support\Facades\Http::withHeaders($ua)->timeout(60)->get($listUrl);
    if (! $r->successful()) {
        echo "{$listUrl}: HTTP {$r->status()}\n";
        continue;
    }
    $html = $r->body();
    preg_match_all('#href="(https://tingia\.gov\.vn/[^"]+\.html)"#iu', $html, $m);
    $urls = array_unique($m[1]);
    $withTitle = 0;
    if (preg_match_all(
        '#href="(https://tingia\.gov\.vn/(?!upload/|public/|in-bai-viet)[a-z0-9\-]+\.html)"[^>]*title="([^"]+)"#iu',
        $html,
        $m2,
        PREG_SET_ORDER
    )) {
        $withTitle = count($m2);
    }
    echo "\n{$listUrl}\n  all .html links: ".count($urls).", regex with title: {$withTitle}\n";

    $found = 0;
    foreach (array_slice($urls, 0, 15) as $url) {
        if (preg_match('#/(upload|public|in-bai-viet)/#', $url)) {
            continue;
        }
        try {
            $meta = $metaMethod->invoke($crawler, $url);
            $d = $meta['published_at'];
            if ($d && (int) $d->month === $month && (int) $d->year === $year) {
                $found++;
                echo "  MATCH: {$d->format('d/m/Y')} {$meta['title']}\n";
            }
        } catch (Throwable $e) {
            echo "  ERR: {$url} - {$e->getMessage()}\n";
        }
        usleep(150000);
    }
    echo "  matches in first 15: {$found}\n";
}

// RSS
echo "\n=== RSS ===\n";
$r = Illuminate\Support\Facades\Http::withHeaders($ua)->timeout(60)->get('https://tingia.gov.vn/rss');
echo 'status='.$r->status().' len='.strlen($r->body())."\n";
$xml = @simplexml_load_string($r->body());
if ($xml && isset($xml->channel->item)) {
    foreach ($xml->channel->item as $item) {
        $pub = strtotime((string) $item->pubDate);
        $m = $pub ? date('n', $pub) : 0;
        $y = $pub ? date('Y', $pub) : 0;
        if ($m == $month && $y == $year) {
            echo 'RSS MATCH: '.(string) $item->pubDate.' | '.(string) $item->link."\n";
        }
    }
}
