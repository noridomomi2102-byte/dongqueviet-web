<?php

namespace App\View\Composers;

use App\Models\Post;
use Illuminate\View\View;

class TickerComposer
{
    public function compose(View $view): void
    {
        $view->with('tickerPosts', Post::published()
            ->latest('published_at')
            ->limit(8)
            ->get(['id', 'title', 'slug']));
    }
}
