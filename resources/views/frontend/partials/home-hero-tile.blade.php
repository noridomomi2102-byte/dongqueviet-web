@php
    $size = $size ?? 'small';
    $tag = $tag ?? 'a';
@endphp

@if($post)
<{{ $tag }} href="{{ route('post.show', $post->slug) }}" class="hero-tile hero-tile--{{ $size }}">
    @if($post->featured_image)
        <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" loading="lazy">
    @else
        <span class="hero-tile-placeholder" aria-hidden="true"></span>
    @endif
    <span class="hero-tile-overlay"></span>
    <span class="hero-tile-title">{{ $post->title }}</span>
</{{ $tag }}>
@endif
