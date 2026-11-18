<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Nasenyi Abayudaya Community')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image"
    :canonical="route('site.home')"
    :preload-image="isset($entry->hero_slides[0]['image']) ? $entry->hero_slides[0]['image'] : null"
>
    @if($entry && count($entry->hero_slides ?? []))
        <section class="home-hero" data-hero data-hero-interval="6500" aria-label="Welcome to Nasenyi">
            <div class="hero-slides" id="welcome-slides">
                @foreach($entry->hero_slides as $slide)
                    @php
                        $img = $slide['image'] ?? '';
                        $webp = preg_match('/\.(jpe?g|png)$/i', $img) ? preg_replace('/\.(jpe?g|png)$/i', '.webp', $img) : null;
                        $sm = preg_match('/\.(jpe?g|png|webp)$/i', $img) ? preg_replace('/(\.(jpe?g|png|webp))$/i', '-sm$1', $img) : null;
                    @endphp
                    <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-slide @if(! $loop->first) aria-hidden="true" @endif>
                        <div class="hero-media" aria-hidden="true">
                            <picture>
                                @if($webp && $sm)
                                    <source type="image/webp" media="(max-width: 720px)" srcset="{{ $sm }}">
                                @endif
                                @if($webp)
                                    <source type="image/webp" srcset="{{ $webp }}">
                                @endif
                                <img class="hero-photo" src="{{ $img }}" alt="" @if($loop->first) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif width="1920" height="1080">
                            </picture>
                        </div>
                        <div class="hero-overlay"></div>
                        <div class="wrapper hero-content">
                            <span class="hero-eyebrow"><i></i> Makor Chayim — Uganda</span>
                            @if($loop->first)
                                <h1 dir="auto">{{ $slide['title'] }}</h1>
                            @else
                                <h2 dir="auto">{{ $slide['title'] }}</h2>
                            @endif
                            <p>{{ $slide['description'] }}</p>
                            <div class="hero-actions">
                                <a class="button button-gold" href="{{ $slide['href'] }}">{{ $slide['label'] ?? 'Read Our Story' }} <span aria-hidden="true">→</span></a>
                                <a class="button button-quiet" href="{{ url('donation') }}/">Support Nasenyi <span aria-hidden="true">♥</span></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if(count($entry->hero_slides) > 1)
                <div class="wrapper hero-control-row" data-hero-controls hidden>
                    <div class="hero-dots" role="tablist" aria-label="Choose a welcome slide">
                        @foreach($entry->hero_slides as $slide)
                            <button type="button" data-slide-to="{{ $loop->index }}" aria-label="Slide {{ $loop->iteration }} of {{ $loop->count }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <div class="hero-arrows">
                        <button type="button" data-slide-prev aria-label="Previous slide" aria-controls="welcome-slides">‹</button>
                        <button type="button" data-slide-next aria-label="Next slide" aria-controls="welcome-slides">›</button>
                    </div>
                </div>
            @endif
        </section>
    @else
        @include('site.partials.page-header', ['heading' => $entry?->title ?: 'Nasenyi Abayudaya Community', 'image' => '/media/headers/about.jpg'])
    @endif

    @if($entry)
        <div class="home-story prose wrapper" data-reveal-stagger>{!! $entry->body !!}</div>
    @endif
    @if($latestPosts->isNotEmpty())
        <section class="wrapper latest-section" aria-labelledby="latest-heading" data-reveal>
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
