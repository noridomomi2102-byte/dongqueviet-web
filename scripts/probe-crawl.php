<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ua = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'];
$r = Illuminate\Support\Facades\Http::withHeaders($ua)->get('https://tingia.gov.vn/rss');
file_put_contents(__DIR__.'/../storage/app/tingia-rss.xml', $r->body());
echo substr($r->body(), 0, 2000), "\n---\n";
$xml = @new SimpleXMLElement($r->body());
if ($xml && isset($xml->channel->item)) {
    echo 'items: '.count($xml->channel->item)."\n";
    foreach ($xml->channel->item as $item) {
        echo (string)$item->pubDate.' | '.(string)$item->link."\n";
    }
} else {
    echo "not standard rss\n";
}

// dantri article
$url = 'https://dantri.com.vn/phap-luat/dai-su-quan-anh-cam-on-viet-nam-sau-vu-triet-pha-xoi-lac-tv-20260519130416383.htm';
$r2 = Illuminate\Support\Facades\Http::withHeaders($ua)->get($url);
file_put_contents(__DIR__.'/../storage/app/probe-dantri-article.html', $r2->body());
echo "dantri article len=".strlen($r2->body())."\n";
foreach (['singledetail', 'article-content', 'dt-news', 'sapo', 'og:title'] as $kw) {
    if (stripos($r2->body(), $kw) !== false) echo "  has $kw\n";
}
