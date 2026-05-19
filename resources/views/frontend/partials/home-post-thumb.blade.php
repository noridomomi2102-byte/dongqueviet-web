@php
    $size = $size ?? 'sm';
@endphp
@if($post)
<span class="home-thumb home-thumb--{{ $size }}">
    @if($post->featured_image)
        <img src="{{ asset('storage/'.$post->featured_image) }}" alt="" loading="lazy">
    @else
        <span class="home-thumb-ph" aria-hidden="true"></span>
    @endif
</span>
@endif
