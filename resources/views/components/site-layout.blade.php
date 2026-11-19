<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#092A73">
    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $description ?: 'Makor Chayim, the Nasenyi Abayudaya Jewish community in Uganda.' }}">
    <meta name="robots" content="{{ $noindex || ! app()->isProduction() ? 'noindex,nofollow' : 'index,follow' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $logo }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $kind }}">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $description ?: 'Makor Chayim, the Nasenyi Abayudaya Jewish community in Uganda.' }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ url($socialImage) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteTitle }}">
    <meta name="twitter:description" content="{{ $description ?: 'Makor Chayim, the Nasenyi Abayudaya Jewish community in Uganda.' }}">
    <meta name="twitter:image" content="{{ url($socialImage) }}">
    <script type="application/ld+json">{!! Illuminate\Support\Js::encode($organization) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body style="--post-image-ratio: {{ str_replace(':', ' / ', $blogImageRatio) }}; --post-image-fit: {{ $blogImageFit }}">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="scroll-progress" data-scroll-progress aria-hidden="true"></div>
    <header class="site-header" data-header>
        <div class="wrapper header-inner">
            <a class="site-brand" href="{{ route('site.home') }}" aria-label="{{ $siteName }} home">
                <img src="{{ $logo }}" alt="" width="52" height="52" decoding="async">
                <span><strong>Nasenyi</strong><small>Abayudaya Community</small></span>
            </a>
            <a class="button button-gold mobile-donate" href="{{ route('donations.show') }}">Donate</a>
            <button class="menu-button" type="button" aria-controls="main-navigation" aria-expanded="false" data-menu-toggle hidden>
                <span class="menu-icon" aria-hidden="true"></span> Menu
            </button>
            <div class="navigation-panel" data-menu-panel>
                <div class="navigation-clip">
                    <nav id="main-navigation" class="primary-nav" aria-label="Main navigation">
                @if($available->has('about-us'))
                    <a href="{{ url('about-us') }}/" @if(request()->is('about-us')) aria-current="page" @endif>About</a>
                @endif
                @if(count($communityLinks))
                    <details class="nav-dropdown" data-nav-dropdown>
                        <summary>Community <span aria-hidden="true">▾</span></summary>
                        <div class="nav-links">
                            @foreach($communityLinks as $slug => $label)
                                <a href="{{ url($slug) }}/" @if(request()->is($slug)) aria-current="page" @endif>{{ $label }}</a>
                            @endforeach
                            <a href="{{ route('site.events') }}/" @if(request()->is('events*')) aria-current="page" @endif>Events</a>
                        </div>
                    </details>
                @endif
                <a href="{{ route('site.updates') }}/" @if(request()->is('updates')) aria-current="page" @endif>Updates</a>
                <a href="{{ route('site.gallery') }}/" @if(request()->is('donation-history')) aria-current="page" @endif>Gallery</a>
                <a href="{{ route('site.contact') }}/" @if(request()->is('contact-us')) aria-current="page" @endif>Contact</a>
                <a class="search-link" href="{{ route('site.search') }}" aria-label="Search the website" @if(request()->is('search')) aria-current="page" @endif><span aria-hidden="true"></span><span class="search-text">Search</span></a>
                <a class="button button-gold header-donate" href="{{ route('donations.show') }}">Donate <span aria-hidden="true">→</span></a>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <main id="main-content">
        {{ $slot }}
        @unless(request()->routeIs('donations.*'))
            <section class="wrapper giving-section" aria-labelledby="giving-heading">
                <div class="giving-panel" data-reveal="up">
                    <h2 id="giving-heading">Support the Nasenyi Jewish Community</h2>
                    <a class="button button-gold" href="{{ route('donations.show') }}">Donate <span aria-hidden="true">→</span></a>
                </div>
            </section>
        @endunless
    </main>
    <footer class="site-footer">
        <div class="wrapper footer-grid">
            <div class="footer-about">
                <a class="site-brand footer-brand" href="{{ route('site.home') }}">
                    <img src="{{ $logo }}" alt="" width="52" height="52" loading="lazy" decoding="async">
                    <span><strong>Nasenyi</strong><small>Abayudaya Community</small></span>
                </a>
                @if(filled($settings['location'] ?? null))
                    <p class="footer-location">{{ $settings['location'] }}</p>
                @endif
                <div class="footer-socials">
                    @foreach($socialLinks as $label => $link)
                        <a href="{{ $link }}" rel="noopener noreferrer" target="_blank">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h2>Explore</h2>
                <div class="footer-links">
                    @foreach(array_slice($communityLinks, 0, 8, true) as $slug => $label)
                        <a href="{{ url($slug) }}/">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h2>Get involved</h2>
                <div class="footer-links">
                    <a href="{{ route('site.updates') }}/">Community updates</a>
                    <a href="{{ route('site.events') }}/">Events</a>
                    <a href="{{ route('site.gallery') }}/">Gallery</a>
                    <a href="{{ route('site.contact') }}/">Contact us</a>
                    <a href="{{ route('donations.show') }}">Support Nasenyi</a>
                    @if(filled($settings['contact_email'] ?? null))
                        <a href="mailto:{{ $settings['contact_email'] }}">{{ $settings['contact_email'] }}</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="wrapper footer-bottom">
            <span>&copy; {{ now()->year }} {{ $siteName }}</span>
            <a href="#main-content">Back to top ↑</a>
        </div>
    </footer>
</body>
</html>
