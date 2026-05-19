<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['parent', 'activeChildren'])
            ->firstOrFail();

        $categoryIds = $category->descendantIds();

        $posts = Post::published()
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->latest('published_at')
            ->paginate(12);

        return view('frontend.category', compact('category', 'posts'));
    }
}
