<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(string $slug): View
    {
        $post = Post::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        $post->incrementViews();

        $related = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('frontend.post', compact('post', 'related'));
    }
}
