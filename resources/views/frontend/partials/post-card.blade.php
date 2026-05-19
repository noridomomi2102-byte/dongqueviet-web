<article class="post-card">
    <a href="{{ route('post.show', $post->slug) }}" class="post-card-link">
        @if($post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="post-card-img">
        @else
            <div class="post-card-img post-card-placeholder"></div>
        @endif
        <div class="post-card-body">
            <span class="badge">{{ $post->category->name }}</span>
            <h3>{{ $post->title }}</h3>
            @if($post->excerpt)
                <p class="excerpt">{{ \Illuminate\Support\Str::limit($post->excerpt, 120) }}</p>
            @endif
            <time datetime="{{ $post->published_at->toIso8601String() }}">
                {{ $post->published_at->format('d/m/Y H:i') }}
            </time>
        </div>
    </a>
</article>
