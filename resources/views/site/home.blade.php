<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Nasenyi Abayudaya Community')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image"
    :canonical="route('site.home')"
>
    @if($entry && count($entry->hero_slides ?? []))
        <section class="home-hero" data-hero data-hero-interval="7500" aria-roledescription="carousel" aria-label="Welcome to Nasenyi">
            <div class="hero-slides" id="welcome-slides">
                @foreach($entry->hero_slides as $slide)
                    @php
                        $banner = \App\Support\PublicImage::banner($slide['image']);
                        $positionX = \App\Support\PublicImage::position($slide['position_x'] ?? null);
                        $positionY = \App\Support\PublicImage::position($slide['position_y'] ?? null, $loop->iteration === 3 ? 60 : 50);
                    @endphp
                    <article class="hero-slide {{ $loop->first ? 'is-active' : '' }} {{ ($slide['fit'] ?? 'cover') === 'contain' ? 'hero-slide--contain' : '' }}"
                        style="--slide-position: {{ $positionX }}% {{ $positionY }}%"
                        data-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @if(! $loop->first) inert @endif>
                        <picture class="hero-media" aria-hidden="true">
                            @if($banner['narrow'])
                                <source media="(max-width: 720px)" srcset="{{ $banner['narrow'] }}">
                            @endif
                            <img class="hero-photo" src="{{ $banner['source'] }}" alt="" decoding="async"
                                fetchpriority="{{ $loop->first ? 'high' : 'low' }}">
                        </picture>
                        <div class="hero-overlay" aria-hidden="true"></div>
                        <div class="wrapper hero-content">
                            @if($loop->first)
                                <h1 dir="auto">{{ $slide['title'] }}</h1>
                            @else
                                <h2 dir="auto">{{ $slide['title'] }}</h2>
                            @endif
                            <p>{{ $slide['description'] }}</p>
                            <div class="hero-actions">
                                <a class="button button-gold" href="{{ $slide['href'] }}">{{ $slide['label'] ?? 'View Our Story' }} <span aria-hidden="true">→</span></a>
                                <a class="button button-quiet" href="{{ route('donations.show') }}">Donate <span aria-hidden="true">→</span></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if(count($entry->hero_slides) > 1)
                <div class="wrapper hero-control-row" data-hero-controls hidden>
                    <div class="hero-dots" role="group" aria-label="Choose a welcome slide">
                        @foreach($entry->hero_slides as $slide)
                            <button type="button" data-slide-to="{{ $loop->index }}" aria-label="Slide {{ $loop->iteration }} of {{ $loop->count }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <div class="hero-arrows">
                        <button type="button" data-slide-pause aria-label="Pause slideshow" aria-pressed="false" aria-controls="welcome-slides">Ⅱ</button>
                        <button type="button" data-slide-prev aria-label="Previous slide" aria-controls="welcome-slides">‹</button>
                        <button type="button" data-slide-next aria-label="Next slide" aria-controls="welcome-slides">›</button>
                    </div>
                </div>
            @endif
        </section>
    @else
        @include('site.partials.page-header', ['heading' => $entry?->title ?: 'Nasenyi Abayudaya Community', 'image' => '/media/headers/about.webp'])
    @endif

    @if($entry)
        <div class="home-story prose wrapper">{!! \App\Support\StoryLayout::render($entry) !!}</div>
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
