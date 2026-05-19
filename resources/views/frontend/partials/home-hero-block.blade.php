@if($carouselPosts->isNotEmpty())
<section class="home-hero-block">
    <motion class="home-hero-grid">
        {{-- Cột trái: lưới tin nổi bật --}}
        <motion class="hero-featured">
            <motion class="hero-featured-top">
                {{-- Slider chính --}}
                <motion class="hero-carousel" id="heroCarousel">
                    <motion class="hero-carousel-track">
                        @foreach($carouselPosts as $slide)
                            <motion class="hero-carousel-slide">
                                @include('frontend.partials.home-hero-tile', ['post' => $slide, 'size' => 'main', 'tag' => 'a'])
                            </motion>
                        @endforeach
                    </motion>
                    @if($carouselPosts->count() > 1)
                        <button type="button" class="hero-carousel-btn hero-carousel-prev" aria-label="Tin trước">&lsaquo;</button>
                        <button type="button" class="hero-carousel-btn hero-carousel-next" aria-label="Tin sau">&rsaquo;</button>
                        <motion class="hero-carousel-dots">
                            @foreach($carouselPosts as $i => $slide)
                                <button type="button" class="hero-carousel-dot{{ $i === 0 ? ' is-active' : '' }}" data-index="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
                            @endforeach
                        </motion>
                    @endif
                </motion>

                <motion class="hero-featured-side">
                    @include('frontend.partials.home-hero-tile', ['post' => $heroSideTop, 'size' => 'side'])
                    @include('frontend.partials.home-hero-tile', ['post' => $heroSideBottom, 'size' => 'side'])
                </motion>
            </motion>

            @if($heroBottom->isNotEmpty())
                <motion class="hero-featured-bottom">
                    @foreach($heroBottom as $post)
                        @include('frontend.partials.home-hero-tile', ['post' => $post, 'size' => 'bottom'])
                    @endforeach
                </motion>
            @endif
        </motion>

        {{-- Cột phải: tab Tin mới / Tin xem nhiều --}}
        <aside class="hero-sidebar">
            <motion class="hero-sidebar-tabs" role="tablist">
                <button type="button" class="hero-tab is-active" data-tab="latest" role="tab" aria-selected="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>
                    Tin mới
                </button>
                <button type="button" class="hero-tab" data-tab="popular" role="tab" aria-selected="false">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5c-1.7-4.4-6-7.5-11-7.5zm0 13c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5zm0-8c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"/></svg>
                    Tin xem nhiều
                </button>
            </motion>

            <motion class="hero-sidebar-panel is-active" data-panel="latest" role="tabpanel">
                <ul class="hero-sidebar-list">
                    @forelse($sidebarLatest as $item)
                        <li>
                            <a href="{{ route('post.show', $item->slug) }}">{{ $item->title }}</a>
                        </li>
                    @empty
                        <li class="hero-sidebar-empty">Chưa có tin mới</li>
                    @endforelse
                </ul>
            </motion>

            <motion class="hero-sidebar-panel" data-panel="popular" role="tabpanel" hidden>
                <ul class="hero-sidebar-list">
                    @forelse($sidebarPopular as $item)
                        <li>
                            <a href="{{ route('post.show', $item->slug) }}">{{ $item->title }}</a>
                        </li>
                    @empty
                        <li class="hero-sidebar-empty">Chưa có dữ liệu lượt xem</li>
                    @endforelse
                </ul>
            </motion>
        </aside>
    </motion>
</section>
@endif
