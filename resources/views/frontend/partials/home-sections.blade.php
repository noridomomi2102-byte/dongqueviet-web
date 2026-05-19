@php
    $hasNews = $newsCategory && ($newsMain || $newsList->isNotEmpty());
    $hasOpinion = $opinionCategory && ($opinionCol1Main || $opinionCol2Main);
    $hasVerify = $verifyCategory && $verifyPosts->isNotEmpty();
    $hasSidebar = $hasNews || $hasOpinion;
@endphp
<div class="home-sections-layout{{ ($hasNews && $hasOpinion) ? ' home-sections-layout--paired' : '' }}">
    {{-- Thời sự (cột trái, hàng 1) --}}
    @if($newsCategory && ($newsMain || $newsList->isNotEmpty()))
        <div class="home-sections-main home-sections-main--news">
            <section class="home-section home-section--news">
                @include('frontend.partials.home-section-head', [
                    'title' => $newsCategory->name,
                    'url' => route('category.show', $newsCategory->slug),
                ])
                <div class="home-news-body">
                    @if($newsMain)
                        <article class="home-news-feature">
                            <a href="{{ route('post.show', $newsMain->slug) }}" class="home-news-feature-link">
                                @include('frontend.partials.home-post-thumb', ['post' => $newsMain, 'size' => 'lg'])
                                <h3 class="home-news-feature-title">{{ $newsMain->title }}</h3>
                                @if($newsMain->excerpt)
                                    <p class="home-news-feature-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($newsMain->excerpt), 200) }}</p>
                                @endif
                            </a>
                        </article>
                    @endif
                    @if($newsList->isNotEmpty())
                        <ul class="home-news-list">
                            @foreach($newsList as $post)
                                <li>
                                    <a href="{{ route('post.show', $post->slug) }}" class="home-news-list-item">
                                        @include('frontend.partials.home-post-thumb', ['post' => $post, 'size' => 'sm'])
                                        <span class="home-news-list-title">{{ $post->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>
    @endif

    {{-- Sidebar: Phản ánh tin giả (+ Kiểm chứng nếu có bài) --}}
    @if($hasSidebar)
        <aside class="home-sections-aside">
            @if($hasVerify)
                <section class="home-section home-section--verify home-section--verify-compact">
                    @include('frontend.partials.home-section-head', [
                        'title' => $verifyCategory->name,
                        'url' => route('category.show', $verifyCategory->slug),
                    ])
                    <ul class="home-verify-list">
                        @foreach($verifyPosts->take(4) as $post)
                            <li>
                                <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @include('frontend.partials.home-sidebar-report')
        </aside>
    @endif
    {{-- Góc nhìn & Phản biện (cột trái, hàng 2) --}}
    @if($opinionCategory && ($opinionCol1Main || $opinionCol2Main))
        <div class="home-sections-main home-sections-main--opinion">
            <section class="home-section home-section--opinion">
                @include('frontend.partials.home-section-head', [
                    'title' => $opinionCategory->name,
                    'url' => route('category.show', $opinionCategory->slug),
                ])
                <div class="home-opinion-cols">
                    <div class="home-opinion-col">
                        @if($opinionCol1Main)
                            <article class="home-opinion-feature">
                                <a href="{{ route('post.show', $opinionCol1Main->slug) }}">
                                    @include('frontend.partials.home-post-thumb', ['post' => $opinionCol1Main, 'size' => 'md'])
                                    <h3>{{ $opinionCol1Main->title }}</h3>
                                    @if($opinionCol1Main->excerpt)
                                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($opinionCol1Main->excerpt), 120) }}</p>
                                    @endif
                                </a>
                            </article>
                        @endif
                        <ul class="home-opinion-list">
                            @foreach($opinionCol1List as $post)
                                <li>
                                    <a href="{{ route('post.show', $post->slug) }}">
                                        @include('frontend.partials.home-post-thumb', ['post' => $post, 'size' => 'sm'])
                                        <span>{{ $post->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="home-opinion-col">
                        @if($opinionCol2Main)
                            <article class="home-opinion-feature">
                                <a href="{{ route('post.show', $opinionCol2Main->slug) }}">
                                    @include('frontend.partials.home-post-thumb', ['post' => $opinionCol2Main, 'size' => 'md'])
                                    <h3>{{ $opinionCol2Main->title }}</h3>
                                    @if($opinionCol2Main->excerpt)
                                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($opinionCol2Main->excerpt), 120) }}</p>
                                    @endif
                                </a>
                            </article>
                        @endif
                        <ul class="home-opinion-list">
                            @foreach($opinionCol2List as $post)
                                <li>
                                    <a href="{{ route('post.show', $post->slug) }}">
                                        @include('frontend.partials.home-post-thumb', ['post' => $post, 'size' => 'sm'])
                                        <span>{{ $post->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    @endif

    {{-- Banner quảng cáo: full width (main + sidebar) --}}
    <div class="home-banner-ad" aria-label="Quảng cáo">
        <a href="https://dangcongsan.vn" target="_blank" rel="noopener noreferrer sponsored">
            <img
                src="{{ asset('images/ads/banner-ky-nguyen.jpg') }}"
                alt="Việt Nam — Kỷ nguyên mới, kỷ nguyên vươn mình của dân tộc"
                width="1360"
                height="105"
                loading="lazy"
                decoding="async"
            >
        </a>
    </div>
</div>
