@if(!empty($bottomColumns))
    <section class="home-bottom-grid" aria-label="Chuyên mục dưới trang chủ">
        @foreach($bottomColumns as $col)
            @if($col['category'])
                <div class="home-bottom-col">
                    <header class="home-bottom-col-head">
                        <h2 class="home-bottom-col-title">
                            <a href="{{ route('category.show', $col['category']->slug) }}">{{ $col['title'] }}</a>
                        </h2>
                    </header>

                    @if($col['main'])
                        <article class="home-bottom-feature">
                            <a href="{{ route('post.show', $col['main']->slug) }}">
                                @include('frontend.partials.home-post-thumb', ['post' => $col['main'], 'size' => 'md'])
                                <h3>{{ $col['main']->title }}</h3>
                            </a>
                        </article>
                    @endif

                    @if($col['list']->isNotEmpty())
                        <ul class="home-bottom-list">
                            @foreach($col['list'] as $post)
                                <li>
                                    <a href="{{ route('post.show', $post->slug) }}" class="home-bottom-list-item">
                                        <span class="home-bottom-list-title">{{ $post->title }}</span>
                                        @include('frontend.partials.home-post-thumb', ['post' => $post, 'size' => 'sm'])
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @elseif(! $col['main'])
                        <p class="home-bottom-empty">Chưa có bài viết trong chuyên mục này.</p>
                    @endif
                </div>
            @endif
        @endforeach
    </section>
@endif
