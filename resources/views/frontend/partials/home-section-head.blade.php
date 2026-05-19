@php
    $title = $title ?? '';
    $url = $url ?? null;
@endphp
<div class="home-section-head">
    <h2 class="home-section-title">{{ $title }}</h2>
    @if($url)
        <a href="{{ $url }}" class="home-section-more">Xem tất cả</a>
    @endif
</div>
