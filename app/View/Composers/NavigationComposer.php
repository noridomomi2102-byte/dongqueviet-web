<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view): void
    {
        $view->with('navCategories', Category::roots()
            ->where('is_active', true)
            ->where('show_in_menu', true)
            ->with(['activeChildren'])
            ->orderBy('sort_order')
            ->get());
    }
}
