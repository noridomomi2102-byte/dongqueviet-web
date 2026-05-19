@extends('layouts.app')

@section('title', $category->name . ' - ' . config('app.name'))
@section('meta_description', $category->description ?? $category->name)

@push('styles')
<style>
    .subcats { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .subcat-card { padding: 1rem; border: 1px solid #e5e5e5; border-radius: 8px; background: #f8f9fa; }
    .subcat-card h3 { margin: 0 0 .35rem; font-size: 1rem; }
    .subcat-card p { margin: 0; font-size: .85rem; color: #666; }
    .post-card { border: 1px solid #e5e5e5; border-radius: 8px; overflow: hidden; }
    .post-card-img { width: 100%; height: 180px; object-fit: cover; }
    .post-card-placeholder { background: #ddd; height: 180px; }
    .post-card-body { padding: 1rem; }
    .grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .breadcrumb { font-size: .9rem; color: #666; margin-bottom: 1rem; }
    .breadcrumb a { color: var(--primary); }
</style>
@endpush

@section('content')
    @if($category->parent)
        <p class="breadcrumb">
            <a href="{{ route('category.show', $category->parent->slug) }}">{{ $category->parent->name }}</a>
            → {{ $category->name }}
        </p>
    @endif

    <h1 class="page-title">{{ $category->name }}</h1>
    @if($category->description)
        <p style="color:#666;margin-bottom:1.5rem">{{ $category->description }}</p>
    @endif

    @if($category->activeChildren->isNotEmpty())
        <h2 style="font-size:1.1rem;margin-bottom:1rem">Chuyên mục con</h2>
        <div class="subcats">
            @foreach($category->activeChildren as $child)
                <a href="{{ route('category.show', $child->slug) }}" class="subcat-card">
                    <h3>{{ $child->name }}</h3>
                    @if($child->description)
                        <p>{{ \Illuminate\Support\Str::limit($child->description, 80) }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <h2 style="font-size:1.1rem;margin-bottom:1rem">Bài viết</h2>
    <div class="grid grid-3">
        @forelse($posts as $post)
            @include('frontend.partials.post-card', ['post' => $post])
        @empty
            <p>Chưa có bài viết trong chuyên mục này.</p>
        @endforelse
    </div>

    <div class="pagination">{{ $posts->links() }}</div>
@endsection
