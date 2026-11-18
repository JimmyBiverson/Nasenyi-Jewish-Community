<article class="story-card" data-reveal>
    <a href="{{ url($post->slug) }}/" class="story-card-link">
        @if($post->image)
            <div class="story-card-media">
                <img src="{{ $post->image }}" alt="" width="600" height="370" loading="lazy" decoding="async">
            </div>
        @endif
        <div class="story-card-content">
            @if($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
            @endif
            <h3>{{ $post->title }}</h3>
            @if($post->summary)
                <p>{{ \Illuminate\Support\Str::limit($post->summary, 140) }}</p>
            @endif
            <span class="story-card-arrow" aria-hidden="true">Read story →</span>
        </div>
    </a>
</article>
