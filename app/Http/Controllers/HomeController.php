<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $heroPosts = Post::published()
            ->with('category')
            ->whereNotNull('featured_image')
            ->latest('published_at')
            ->limit(9)
            ->get();

        if ($heroPosts->count() < 9) {
            $more = Post::published()
                ->with('category')
                ->whereNotIn('id', $heroPosts->pluck('id'))
                ->latest('published_at')
                ->limit(9 - $heroPosts->count())
                ->get();

            $heroPosts = $heroPosts->merge($more);
        }

        $carouselPosts = $heroPosts->slice(0, 3)->values();
        $heroSideTop = $heroPosts->get(3);
        $heroSideBottom = $heroPosts->get(4);
        $heroBottom = $heroPosts->slice(5, 3)->values();

        $sidebarLatest = Post::published()
            ->latest('published_at')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'published_at']);

        $sidebarPopular = Post::published()
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'views', 'published_at']);

        $usedIds = $heroPosts->pluck('id');

        $newsCategory = Category::where('slug', 'thoi-su')->where('is_active', true)->first();
        $newsPosts = $this->postsForCategory($newsCategory, 6, $usedIds);
        $usedIds = $usedIds->merge($newsPosts->pluck('id'));
        $newsMain = $newsPosts->first();
        $newsList = $newsPosts->slice(1, 5)->values();

        $opinionCategory = Category::where('slug', 'goc-nhin-phan-bien')->where('is_active', true)->first();
        $opinionPosts = $this->postsForCategory($opinionCategory, 6, $usedIds);
        $usedIds = $usedIds->merge($opinionPosts->pluck('id'));
        $opinionCol1Main = $opinionPosts->get(0);
        $opinionCol1List = $opinionPosts->slice(1, 2)->values();
        $opinionCol2Main = $opinionPosts->get(3);
        $opinionCol2List = $opinionPosts->slice(4, 2)->values();

        $verifyCategory = Category::where('slug', 'kiem-chung-thong-tin')->where('is_active', true)->first();
        $verifyPosts = $this->postsForCategory($verifyCategory, 6, $usedIds);
        $usedIds = $usedIds->merge($verifyPosts->pluck('id'));

        $bottomColumns = [
            $this->columnBlock('phap-luat', null, 4, $usedIds),
            $this->columnBlock('kiem-chung-thong-tin', 'Tin giả', 4, $usedIds),
            $this->columnBlock('doi-song', null, 4, $usedIds),
        ];

        foreach ($bottomColumns as $col) {
            $usedIds = $usedIds->merge(collect($col['posts'] ?? [])->pluck('id'));
        }

        return view('frontend.home', compact(
            'carouselPosts',
            'heroSideTop',
            'heroSideBottom',
            'heroBottom',
            'sidebarLatest',
            'sidebarPopular',
            'newsCategory',
            'newsMain',
            'newsList',
            'opinionCategory',
            'opinionCol1Main',
            'opinionCol1List',
            'opinionCol2Main',
            'opinionCol2List',
            'verifyCategory',
            'verifyPosts',
            'bottomColumns',
        ));
    }

    /** @return array{category: ?Category, title: string, main: ?Post, list: Collection, posts: Collection} */
    protected function columnBlock(string $slug, ?string $title, int $limit, Collection $excludeIds): array
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        $posts = $this->postsForCategory($category, $limit, $excludeIds);

        return [
            'category' => $category,
            'title' => $title ?? $category?->name ?? '',
            'main' => $posts->first(),
            'list' => $posts->slice(1, 3)->values(),
            'posts' => $posts,
        ];
    }

    /** @param  Collection<int, int>|array<int, int>  $excludeIds */
    protected function postsForCategory(?Category $category, int $limit, Collection|array $excludeIds = []): Collection
    {
        if (! $category) {
            return collect();
        }

        $excludeIds = collect($excludeIds);

        return Post::published()
            ->with('category')
            ->whereIn('category_id', $category->descendantIds())
            ->when($excludeIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
