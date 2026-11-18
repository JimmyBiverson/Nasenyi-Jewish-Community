<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Nasenyi Abayudaya Community')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image"
    :canonical="route('site.home')"
>
    @if($entry && count($entry->hero_slides ?? []))
        <section class="home-hero" data-hero aria-label="Welcome to Nasenyi">
            <div class="hero-slides" id="welcome-slides">
                @foreach($entry->hero_slides as $slide)
                    <article class="hero-slide" data-slide @if(! $loop->first) hidden @endif>
                        <img class="hero-photo" src="{{ $slide['image'] }}" alt="" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        <div class="hero-overlay"></div>
                        <div class="wrapper hero-content">
                            <span class="hero-eyebrow"><i></i> Makor Chayim · Uganda</span>
                            @if($loop->first)
                                <h1 dir="auto">{{ $slide['title'] }}</h1>
                            @else
                                <h2 dir="auto">{{ $slide['title'] }}</h2>
                            @endif
                            <p>{{ $slide['description'] }}</p>
                            <div class="hero-actions">
                                <a class="button button-gold" href="{{ $slide['href'] }}">{{ $slide['label'] ?? 'Read Our Story' }} <span aria-hidden="true">↗</span></a>
                                <a class="button button-quiet" href="{{ url('donation') }}/">Support Nasenyi <span aria-hidden="true">→</span></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if(count($entry->hero_slides) > 1)
                <div class="wrapper hero-control-row" data-hero-controls hidden>
                    <div class="hero-dots" aria-label="Choose a welcome slide">
                        @foreach($entry->hero_slides as $slide)
                            <button type="button" data-slide-to="{{ $loop->index }}" aria-label="Slide {{ $loop->iteration }} of {{ $loop->count }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <div class="hero-arrows">
                        <button type="button" data-slide-prev aria-label="Previous slide" aria-controls="welcome-slides">←</button>
                        <button type="button" data-slide-next aria-label="Next slide" aria-controls="welcome-slides">→</button>
                    </div>
                </div>
            @endif
        </section>
    @else
        <section class="page-hero"><div class="wrapper"><h1>{{ $entry?->title ?: 'Nasenyi Abayudaya Community' }}</h1></div></section>
    @endif

    @if($entry)
        <div class="home-story prose wrapper">{!! $entry->body !!}</div>
    @endif
    @if($latestPosts->isNotEmpty())
        <section class="wrapper latest-section" aria-labelledby="latest-heading">
            <div class="section-heading">
                <h2 id="latest-heading">Latest Community Updates</h2>
                <a class="text-link" href="{{ route('site.updates') }}/">View all updates <span aria-hidden="true">→</span></a>
            </div>
            <div class="story-grid">
                @foreach($latestPosts as $post)
                    @include('site.partials.post-card')
                @endforeach
            </div>
        </section>
    @endif
</x-site-layout>
