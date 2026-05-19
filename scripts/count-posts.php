<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['tin-gia', 'phap-luat', 'doi-song'] as $slug) {
    $cat = App\Models\Category::where('slug', $slug)->first();
    if (! $cat) {
        echo "$slug: no category\n";
        continue;
    }
    $total = App\Models\Post::where('category_id', $cat->id)->count();
    $may = App\Models\Post::where('category_id', $cat->id)
        ->whereYear('published_at', 2026)
        ->whereMonth('published_at', 5)
        ->count();
    echo "$slug: total=$total, May2026=$may\n";
}
