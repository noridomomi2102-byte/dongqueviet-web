<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $posts = Post::published()
            ->with('category')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qry) use ($q) {
                    $qry->where('title', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.search', compact('posts', 'q'));
    }
}
