<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ua = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'];
$crawler = new App\Services\ExternalNewsCrawler();
$ref = new ReflectionClass($crawler);
$metaMethod = $ref->getMethod('fetchTingiaArticleMeta');
$metaMethod->setAccessible(true);

$seen = [];
$byMonth = [];

for ($page = 1; $page <= 7; $page++) {
    $url = $page === 1 ? 'https://tingia.gov.vn/linh-vuc' : 'https://tingia.gov.vn/linh-vuc/'.$page.'/';
    $html = Illuminate\Support\Facades\Http::withHeaders($ua)->timeout(60)->get($url)->body();
    preg_match_all(
        '#href="(https://tingia\.gov\.vn/(?!upload/|public/|in-bai-viet)[a-z0-9\-]+\.html)"#iu',
        $html,
        $m
    );
    foreach (array_unique($m[1]) as $articleUrl) {
        if (isset($seen[$articleUrl])) {
            continue;
        }
        $seen[$articleUrl] = true;
        try {
            $meta = $metaMethod->invoke($crawler, $articleUrl);
            $d = $meta['published_at'];
            if ($d) {
                $key = $d->format('Y-m');
                $byMonth[$key] = ($byMonth[$key] ?? 0) + 1;
            }
        } catch (Throwable) {
        }
        usleep(100000);
    }
}

ksort($byMonth);
echo "Articles by month (linh-vuc pages 1-7, ".count($seen)." URLs):\n";
foreach ($byMonth as $k => $n) {
    echo "  {$k}: {$n}\n";
}

// RSS all dates
$r = Illuminate\Support\Facades\Http::withHeaders($ua)->get('https://tingia.gov.vn/rss');
$xml = @simplexml_load_string($r->body());
if ($xml) {
    echo "\nRSS items:\n";
    foreach ($xml->channel->item as $item) {
        echo '  '.(string) $item->pubDate.' | '.substr((string) $item->title, 0, 60)."\n";
    }
}
