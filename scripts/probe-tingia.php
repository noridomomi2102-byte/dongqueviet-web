<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$crawler = new App\Services\ExternalNewsCrawler();
$ref = new ReflectionClass($crawler);
$method = $ref->getMethod('fetchTingiaListItems');
$method->setAccessible(true);

foreach ([[5, 2026], [1, 2026], [11, 2025]] as [$month, $year]) {
    $items = $method->invoke($crawler, 'https://tingia.gov.vn/linh-vuc', 10, $month, $year);
    echo "Month {$month}/{$year}: ".count($items)." articles\n";
    foreach (array_slice($items, 0, 3) as $item) {
        echo "  - {$item['published_at']} {$item['title']}\n";
    }
}

// sample first page URLs and dates
$response = Illuminate\Support\Facades\Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0',
])->get('https://tingia.gov.vn/linh-vuc');
$html = $response->body();
preg_match_all('#href="(https://tingia\.gov\.vn/[^"]+\.html)"#', $html, $m);
$urls = array_unique($m[1]);
echo "\nFirst 5 URLs on page 1:\n";
$metaMethod = $ref->getMethod('fetchTingiaArticleMeta');
$metaMethod->setAccessible(true);
foreach (array_slice($urls, 0, 5) as $url) {
    if (str_contains($url, 'upload') || str_contains($url, 'public')) {
        continue;
    }
    try {
        $meta = $metaMethod->invoke($crawler, $url);
        echo "  {$meta['published_at']} {$url}\n";
    } catch (Throwable $e) {
        echo "  ERR {$url}: {$e->getMessage()}\n";
    }
    usleep(300000);
}
