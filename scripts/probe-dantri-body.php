<?php

$url = $argv[1] ?? 'https://dantri.com.vn/doi-song/chu-re-ninh-binh-bat-khoc-nhan-mon-qua-bi-mat-cha-qua-co-gui-26-nam-truoc-20260518150557665.htm';
$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
$html = file_get_contents($url, false, stream_context_create(['http' => ['header' => "User-Agent: $ua\r\n"]]));

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
$xp = new DOMXPath($dom);

echo substr($url, -60)."\n";

foreach ([
    "//article/div[not(@class) or @class='']",
    "//div[contains(@class,'news-content')]",
    "//div[contains(@class,'singular-content')]",
    "//div[contains(@class,'sgk')]",
    "//*[contains(@class,'news-content')]",
    "//article//p",
] as $q) {
    $n = $xp->query($q);
    $len = 0;
    if ($n->length && $n->item(0)) {
        $len = strlen(trim($n->item(0)->textContent));
    }
    echo "  $q => {$n->length} (text $len)\n";
}

// print classes on article descendants with most text
$best = null;
$bestLen = 0;
foreach ($xp->query('//article//*') as $el) {
    if (! $el instanceof DOMElement) {
        continue;
    }
    $len = strlen(trim($el->textContent));
    if ($len > $bestLen && in_array($el->nodeName, ['div', 'section'], true)) {
        $bestLen = $len;
        $best = $el;
    }
}
if ($best) {
    echo "Largest in article: <{$best->nodeName} class=\"{$best->getAttribute('class')}\" id=\"{$best->getAttribute('id')}\" len=$bestLen\n";
}

$best = null;
$bestLen = 0;
foreach ($xp->query('//body//*') as $el) {
    if (! $el instanceof DOMElement || ! in_array($el->nodeName, ['motion', 'section'], true)) {
        continue;
    }
    $len = strlen(trim($el->textContent));
    if ($len > $bestLen) {
        $bestLen = $len;
        $best = $el;
    }
}
if ($best) {
    echo "Largest in body: <{$best->nodeName} class=\"{$best->getAttribute('class')}\" len=$bestLen\n";
}
echo 'og:desc len: ';
if (preg_match('/property="og:description" content="([^"]+)"/', $html, $m)) {
    echo strlen(html_entity_decode($m[1])). "\n";
}
