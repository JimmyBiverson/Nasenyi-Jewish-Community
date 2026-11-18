<article class="story-card" data-reveal>
    <a href="{{ url($post->slug) }}/" class="story-card-link">
        @php
            $positionX = \App\Support\PublicImage::position($post->image_position_x);
            $positionY = \App\Support\PublicImage::position($post->image_position_y);
        @endphp
        <div class="story-card-media" style="--photo-position: {{ $positionX }}% {{ $positionY }}%">
            <img src="{{ $post->image ?: \App\Support\SiteBranding::image('social_image') }}" alt="" loading="lazy" decoding="async">
        </div>
        <div class="story-card-content">
            <div class="story-card-meta">
                @if($post->category)
                    <span>{{ $post->category }}</span>
                @endif
                @if($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
                @endif
            </div>
            <h3>{{ $post->title }}</h3>
            @if($post->summary)
                <p>{{ \Illuminate\Support\Str::limit($post->summary, 140) }}</p>
            @endif
            <span class="story-card-arrow" aria-hidden="true">Read story <span>→</span></span>
        </div>
    </a>
</article>
