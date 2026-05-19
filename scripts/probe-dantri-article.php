<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = file_get_contents(__DIR__.'/../storage/app/da.html');
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
$xpath = new DOMXPath($dom);

$article = $xpath->query('//article')->item(0);
if ($article) {
    foreach ($article->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE) {
            $cls = $child->attributes?->getNamedItem('class')?->nodeValue ?? '';
            echo $child->nodeName, ' class=', substr($cls, 0, 80), ' len=', strlen($child->textContent), PHP_EOL;
        }
    }
}

$body = $xpath->query("//*[contains(@class,'dt-content')]")->item(0)
    ?: $xpath->query("//*[contains(@class,'content-detail')]")->item(0)
    ?: $xpath->query("//article//*[contains(@class,'content')]")->item(0);

echo 'body node: ', $body ? $body->nodeName.' '.$body->getAttribute('class') : 'none', PHP_EOL;
