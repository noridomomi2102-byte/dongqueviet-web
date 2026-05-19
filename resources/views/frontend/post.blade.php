@extends('layouts.app')

@section('title', ($post->meta_title ?: $post->title) . ' - ' . config('app.name'))
@section('meta_description', $post->meta_description ?: $post->excerpt)

@push('styles')
<style>
    .article-header { margin-bottom: 1.5rem; }
    .article-meta { color: #666; font-size: .9rem; margin: .5rem 0; }
    .article-img { width: 100%; max-height: 420px; object-fit: cover; border-radius: 8px; margin-bottom: 1.5rem; }
    .article-content { font-size: 1.05rem; line-height: 1.8; }
    .article-content img { max-width: 100%; height: auto; }
    .article-sapo { font-size: 1.08rem; line-height: 1.75; margin-bottom: 1.25rem; color: #222; }
    .article-sapo p { margin: 0; }
    .article-figure { margin: 1.25rem 0; text-align: center; }
    .article-figure img { border-radius: 4px; }
    .article-img-caption { margin-top: .5rem; font-size: .92rem; color: #555; text-align: center; font-style: italic; }
    .article-byline { margin-top: 1.75rem; padding-top: 1rem; border-top: 1px solid #e8e8e8; font-size: .92rem; color: #555; text-align: center; }
    .article-byline a { color: var(--cand-red); text-decoration: none; font-weight: 600; }
    .article-byline a:hover { color: var(--cand-red-dark); text-decoration: underline; }
    .related { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e5e5e5; }
    .related h3 { margin-bottom: 1rem; }
    .related-list { display: grid; gap: .75rem; }
    .related-list a { display: block; padding: .75rem; background: #f8f9fa; border-radius: 6px; }
</style>
@endpush

@section('content')
    <article>
        <header class="article-header">
            <span class="badge">{{ $post->category->name }}</span>
            <h1>{{ $post->title }}</h1>
            <p class="article-meta">
                {{ $post->published_at->format('d/m/Y H:i') }}
                @if($post->author) · {{ $post->author->name }} @endif
                · {{ number_format($post->views) }} lượt xem
            </p>
        </header>

        @if($post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="article-img">
        @endif

        <div class="article-content">
            {!! $post->content !!}
        </div>
    </article>

    @if($related->isNotEmpty())
        <section class="related">
            <h3>Tin liên quan</h3>
            <div class="related-list">
                @foreach($related as $item)
                    <a href="{{ route('post.show', $item->slug) }}">{{ $item->title }}</a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
