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
    <link rel="icon" href="{{ $favicon }}" type="{{ $faviconType }}">
    <link rel="apple-touch-icon" href="{{ url($logo) }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $kind }}">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $description ?: 'Makor Chayim, the Nasenyi Abayudaya Jewish community in Uganda.' }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:logo" content="{{ url($logo) }}">
    <meta property="og:image" content="{{ $social['url'] }}">
    @if($social['secureUrl'])
        <meta property="og:image:secure_url" content="{{ $social['secureUrl'] }}">
    @endif
    <meta property="og:image:type" content="{{ $social['type'] }}">
    @if($social['width'] && $social['height'])
        <meta property="og:image:width" content="{{ $social['width'] }}">
        <meta property="og:image:height" content="{{ $social['height'] }}">
    @endif
    <meta property="og:image:alt" content="{{ $social['alt'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteTitle }}">
    <meta name="twitter:description" content="{{ $description ?: 'Makor Chayim, the Nasenyi Abayudaya Jewish community in Uganda.' }}">
    <meta name="twitter:image" content="{{ $social['url'] }}">
    <meta name="twitter:image:alt" content="{{ $social['alt'] }}">
    <script type="application/ld+json">{!! Illuminate\Support\Js::encode($organization) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts

    <style>
        /* Keep editorial photos in their original proportions. */
        :is(.story-body, .home-story) .story-columns > .story-column > figure,
        .story-body .story-photo-spread > figure {
            min-block-size: 0;
            block-size: auto;
            align-self: start;
        }

        :is(.story-body, .home-story) .story-columns > .story-column > figure > img,
        .story-body .story-photo-spread > figure > img {
            display: block;
            inline-size: 100%;
            block-size: auto;
            min-block-size: 0;
            max-block-size: min(36rem, 72svh);
            aspect-ratio: auto attr(width type(<number>), 3) / attr(height type(<number>), 2);
            object-fit: contain;
            background: #eef2f8;
        }

        :is(.story-body, .home-story) :is(.story-gallery, .story-layout--topics, .story-layout--leadership) figure img {
            object-fit: contain;
            background: #eef2f8;
        }

        :is(.story-body, .home-story) :is(.story-layout--opening, .story-layout--profile, .story-layout--faith, .story-layout--spread) > .story-columns,
        .story-body .story-photo-spread {
            align-items: start;
        }

        :is(.story-body, .home-story) :is(.story-layout--opening, .story-layout--profile, .story-layout--faith, .story-layout--spread)
        > .story-columns > .story-column:has(> figure),
        .story-body .story-photo-spread > figure {
            align-self: center;
        }

        /* About: give the introduction, archive photos, and timeline distinct treatments. */
        [data-story-page="about"] .page-hero {
            border-block-end: 4px solid #d9ae60;
        }

        [data-story-page="about"] .entry-story .story-body {
            --history-paper: #fbf7ed;
            --history-line: #e7d8b8;
            --history-ink: #102c57;
            padding-block-start: clamp(1rem, 3vw, 3rem);
        }

        [data-story-page="about"] .story-body > .story-section:first-child {
            max-inline-size: 58rem;
            padding: clamp(1.5rem, 5vw, 4rem);
            margin-inline: auto;
            border: 1px solid var(--history-line);
            border-inline-start: 4px solid #bc8a37;
            border-radius: 1.35rem;
            background: linear-gradient(130deg, var(--history-paper), #fffdf8 75%);
        }

        [data-story-page="about"] .story-body > .story-section:not(.story-layout--illustrated, .story-layout--spread):not(:first-child) {
            max-inline-size: 58rem;
            margin-inline: auto;
        }

        [data-story-page="about"] .story-body .story-photo-spread,
        [data-story-page="about"] .story-body .story-layout--spread > .story-columns {
            padding: clamp(1rem, 3vw, 2.75rem);
            border-radius: 1.35rem;
            background: #f1f5fa;
        }

        [data-story-page="about"] .story-body .story-photo-spread h2,
        [data-story-page="about"] .story-body .story-layout--spread h2 {
            color: var(--history-ink);
        }

        [data-story-page="about"] .story-body > .story-section > blockquote {
            margin-inline: 0;
            padding: clamp(1.25rem, 4vw, 3rem);
            border-radius: 1.1rem;
            background: var(--history-ink);
            color: #fff;
        }

        [data-story-page="about"] .story-body > .story-section > blockquote * {
            color: inherit;
        }

        /* Decorative particles use only compositor-friendly transforms and opacity. */
        .giving-panel,
        [data-story-page="about"] .story-body > .story-section:first-child {
            position: relative;
            isolation: isolate;
            overflow: hidden;
        }

        .giving-panel::after,
        [data-story-page="about"] .story-body > .story-section:first-child::after {
            content: "";
            pointer-events: none;
            position: absolute;
            z-index: -1;
            inset: 0;
            background:
                radial-gradient(circle at 8% 21%, #d3a552 0 2px, transparent 3px),
                radial-gradient(circle at 17% 74%, #d3a552 0 3px, transparent 4px),
                radial-gradient(circle at 27% 15%, #e3b965 0 1px, transparent 2px),
                radial-gradient(circle at 69% 84%, #ddb66b 0 2px, transparent 3px),
                radial-gradient(circle at 79% 31%, #d3a552 0 3px, transparent 4px),
                radial-gradient(circle at 88% 71%, #ddb66b 0 1px, transparent 2px),
                radial-gradient(circle at 94% 20%, #d3a552 0 2px, transparent 3px);
            opacity: .56;
        }

        @keyframes nasenyi-particle-drift {
            from { transform: translate3d(-.6rem, .35rem, 0); opacity: .4; }
            to { transform: translate3d(.6rem, -.7rem, 0); opacity: .76; }
        }

        @media (prefers-reduced-motion: no-preference) {
            .giving-panel::after,
            [data-story-page="about"] .story-body > .story-section:first-child::after {
                animation: nasenyi-particle-drift 8s ease-in-out infinite alternate;
            }
        }

        @media (max-width: 42rem) {
            [data-story-page="about"] .story-body .story-photo-spread,
            [data-story-page="about"] .story-body .story-layout--spread > .story-columns {
                padding: 1rem;
                gap: 1.25rem;
            }

            :is(.story-body, .home-story) .story-columns > .story-column > figure > img,
            .story-body .story-photo-spread > figure > img {
                max-block-size: 65svh;
            }
        }
    </style>

    <style>
        .home-story .story-layout--topics > .story-columns {
            display: block;
        }

        .home-story .story-layout--topics > .story-columns > .story-column {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: auto auto;
            grid-auto-rows: 1fr;
            align-items: stretch;
            gap: clamp(.85rem, 2vw, 1.5rem);
        }

        .home-story .story-layout--topics > .story-columns > .story-column > h2 {
            grid-column: 1 / -1;
            margin-block: 0;
        }

        .home-story .story-layout--topics > .story-columns > .story-column > .story-columns {
            display: contents;
        }

        .home-story .story-layout--topics .story-columns .story-columns > .story-column {
            display: flex;
            min-inline-size: 0;
        }

        .home-story .story-layout--topics .story-card {
            display: grid;
            grid-template-rows: auto auto 1fr;
            align-content: start;
            inline-size: 100%;
            overflow: hidden;
        }

        .home-story .story-layout--topics .story-card figure,
        .home-story .story-layout--topics .story-card figure a {
            display: block;
            inline-size: 100%;
            block-size: auto;
            margin: 0;
        }

        .home-story .story-layout--topics .story-card figure a img {
            display: block;
            inline-size: 100%;
            block-size: auto;
            max-block-size: none;
            aspect-ratio: 16 / 10;
            object-fit: cover;
        }

        .photo-carousel {
            max-inline-size: min(100%, 58rem);
            margin: 1.75rem auto 0;
        }

        .photo-carousel .photo-carousel-track {
            display: flex;
            gap: 0;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            margin: 0;
            border-radius: 1rem;
            background: #102541;
        }

        .photo-carousel-track::-webkit-scrollbar {
            display: none;
        }

        .photo-carousel-track > figure {
            flex: 0 0 100%;
            min-inline-size: 0;
            margin: 0;
            scroll-snap-align: start;
            scroll-snap-stop: always;
            background: #102541;
            color: #fff;
        }

        .photo-carousel .photo-carousel-photo {
            display: block;
            inline-size: 100%;
            padding: 0;
            border: 0;
            background: #102541;
            cursor: zoom-in;
        }

        .photo-carousel .photo-carousel-track .photo-carousel-photo > img {
            display: block;
            inline-size: 100%;
            block-size: auto;
            max-block-size: none;
            aspect-ratio: 5 / 3;
            object-fit: cover;
            border-radius: 0;
            background: #102541;
        }

        .photo-carousel-controls {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: .55rem;
            margin-block-start: .8rem;
        }

        .photo-carousel-controls button,
        .photo-carousel-dialog button {
            min-block-size: 44px;
            min-inline-size: 44px;
            padding: .4rem .8rem;
            border: 1px solid #c8d4e6;
            border-radius: 2rem;
            background: #fff;
            color: #102541;
            font: inherit;
            cursor: pointer;
        }

        .photo-carousel-controls button:hover,
        .photo-carousel-dialog button:hover {
            background: #eaf0fa;
        }

        .photo-carousel-photo:focus-visible {
            outline: 3px solid #f2bd54;
            outline-offset: -4px;
        }

        .photo-carousel-controls output {
            color: #344963;
            font-variant-numeric: tabular-nums;
            min-inline-size: 4ch;
            text-align: center;
        }

        .photo-carousel-dialog {
            inline-size: fit-content;
            max-inline-size: calc(100% - 1.5rem);
            max-block-size: calc(100% - 1.5rem);
            padding: .75rem;
            border: 0;
            border-radius: 1rem;
            background: #102541;
            color: #fff;
        }

        .photo-carousel-dialog::backdrop {
            background: rgb(7 16 31 / .87);
        }

        .photo-carousel-dialog button {
            display: block;
            margin: 0 0 .65rem auto;
        }

        .photo-carousel-dialog img {
            display: block;
            margin: auto;
            max-inline-size: min(80rem, calc(100vw - 3rem));
            max-block-size: 78svh;
            object-fit: contain;
        }

        @media (max-width: 58rem) {
            .home-story .story-layout--topics > .story-columns > .story-column {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 38rem) {
            .home-story .story-layout--topics > .story-columns > .story-column {
                grid-template-columns: minmax(0, 1fr);
                grid-auto-rows: auto;
            }
        }
    </style>

    <style>
        :root { --nas-navy: #102541; --nas-ivory: #faf7ef; --nas-gold: #efb946; }
        body { background-color: var(--nas-ivory); }
        .site-header, .site-header.is-scrolled {
            color: var(--nas-navy);
            background: var(--nas-ivory);
            border-bottom-color: #e8dfcb;
        }
        .site-header .site-brand, .site-header .primary-nav > a:not(.button),
        .site-header .primary-nav summary, .site-header .menu-button { color: var(--nas-navy); }
        .site-header .site-brand :is(strong,small) { color: var(--nas-navy); }
        .site-header .primary-nav a[aria-current="page"] { text-decoration: underline; text-underline-offset: .42em; }
        .site-header .header-donate, .site-header .mobile-donate { background: var(--nas-gold); color: var(--nas-navy); }
        .site-header .menu-button { border: 1px solid #a8afba; background: transparent; }
        .site-header .menu-icon, .site-header .menu-icon::before, .site-header .menu-icon::after { background-color: currentColor; }
        .site-header .search-link > span:first-child { border-color: currentColor; }
        .site-header .search-link > span:first-child::after { background-color: currentColor; }
        .site-header .nav-links { background: #fffdf7; color: var(--nas-navy); }
        .site-header .nav-links a { color: var(--nas-navy); }

        .home-hero, .home-hero .hero-slides { background: var(--nas-navy); }
        .home-hero .hero-slide { min-height: clamp(35rem, 100svh, 45rem); justify-content: flex-start; }
        .home-hero .hero-media { inset: 0 0 0 42%; inline-size: auto; block-size: auto; overflow: hidden; }
        .home-hero .hero-overlay { inset: 0 0 0 42%; background: linear-gradient(0deg,#0d2036a8,transparent 35%); }
        .home-hero .hero-content {
            position: relative;
            inline-size: 42%;
            margin: 0;
            padding: clamp(6.8rem, 17svh, 8.5rem) clamp(1.4rem, 4vw, 4.5rem) 3.2rem max(1.5rem, calc((100vw - 1240px) / 2));
            align-self: center;
            box-sizing: border-box;
        }
        .home-hero .hero-content :is(h1,h2) { font-family: "Instrument Serif", Georgia, serif; font-size: clamp(2.1rem, min(4.25vw, 8.2svh), 4.65rem); line-height: 1.02; text-wrap: balance; }
        .home-hero .hero-content p { max-inline-size: 34ch; }
        .home-hero .hero-actions { gap: .65rem; }
        .home-hero .hero-actions a { min-height: 44px; }
        .home-hero .hero-control-row:not([hidden]) {
            left: 42%; right: 0; bottom: 1.4rem; transform: none; width: auto;
            margin: 0; padding: 0 clamp(1rem, 3vw, 3rem); gap: .75rem;
        }
        .home-hero .hero-dots { gap: .5rem; }
        .home-hero .hero-dots button {
            position: relative; inline-size: clamp(2.8rem,5.4vw,5rem); block-size: clamp(2.75rem,4vw,3.7rem);
            overflow: hidden; border-radius: .45rem; border: 2px solid #ffffff84;
            background: #172d47; transition: transform 250ms, border-color 250ms;
        }
        .home-hero .hero-dots button[aria-pressed="true"], .home-hero .hero-dots button:hover {
            border-color: var(--nas-gold); transform: translateY(-.22rem) scale(1.07);
        }
        .home-hero .hero-dots button:has(img)::before, .home-hero .hero-dots button:has(img)::after { display: none; }
        .home-hero .hero-dots button img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .home-hero .hero-arrows { gap: .35rem; }
        .home-hero .hero-arrows button { background: #102541d9; color: white; }
        @media (prefers-reduced-motion: no-preference) {
            .home-hero .hero-slide.is-active .hero-media { animation: nasenyi-hero-grow 900ms cubic-bezier(.2,.7,.2,1) both; }
        }
        @keyframes nasenyi-hero-grow {
            from { opacity: .42; transform: translateX(4%) scale(.92); }
            to { opacity: 1; transform: none; }
        }

        .page-hero { background: var(--nas-navy); border-bottom: 3px solid var(--nas-gold); }
        .page-hero .page-hero-photo, .page-hero .page-hero-shade, .page-hero .page-hero-texture {
            inset-inline-start: 42%; inline-size: auto;
        }
        .page-hero .page-hero-shade { background: linear-gradient(90deg,#102541,transparent 36%),linear-gradient(0deg,#10254166,transparent 50%); }
        .page-hero .page-hero-inner { text-align: start; }
        .page-hero h1, .home-story .story-layout--topics .story-card h3 { font-family: "Instrument Serif", Georgia, serif; }
        .page-hero .page-hero-inner :is(h1,.page-hero-subtitle) { margin-inline: 0; max-inline-size: 17ch; }
        .page-hero .page-hero-rule { margin-inline: 0; }
        .home-story .story-layout--opening .story-columns { grid-template-columns: minmax(0,1fr) minmax(0,1.05fr); }
        .home-story .story-layout--opening .story-stat {
            background: #fffaf0; border: 1px solid #e4d6b3; border-radius: .55rem; color: var(--nas-navy);
        }
        .story-stat strong { font-variant-numeric: tabular-nums; }
        .home-story .story-layout--topics .story-card { background: #fffdf7; border: 1px solid #e3d9c7; border-radius: .75rem; }
        .home-story .story-layout--topics .story-card :is(h3,p) { padding-inline: .9rem; }
        .home-story .story-layout--topics .story-card h3 { color: var(--nas-navy); }
        .story-section.nasenyi-timeline { border-inline-start: 3px solid var(--nas-gold); padding-inline-start: clamp(1rem,3vw,2.5rem); }
        .contact-layout.nasenyi-donate .contact-form { background: #fffdf8; border: 1px solid #e2d7c4; border-radius: .9rem; }
        .nasenyi-donate-photo { display: block; margin: 0 0 1.5rem; }
        .nasenyi-donate-photo img { display: block; inline-size: 100%; max-block-size: 32rem; object-fit: contain; border-radius: .75rem; }

        .home-story .story-layout--seder {
            background: var(--nas-navy); color: #f9f4e9; border-radius: 1rem;
            padding: clamp(1.25rem,4vw,3.4rem); border-top: 3px solid var(--nas-gold);
        }
        .home-story .story-layout--seder :is(h2,h3,p) { color: inherit; }
        .home-story .story-layout--seder a { color: #ffd884; }
        .photo-carousel { max-inline-size: 100%; }
        .photo-carousel .photo-carousel-track {
            --photo-gap: .7rem;
            --photo-width: calc((100% - 3 * var(--photo-gap)) / 3.15);
            position: relative; gap: var(--photo-gap); background: transparent; border-radius: 0;
        }
        .photo-carousel .photo-carousel-track > figure {
            flex: 0 0 var(--photo-width); border-radius: .65rem; overflow: hidden; height: auto;
        }
        .photo-carousel .photo-carousel-track .photo-carousel-photo > img { aspect-ratio: 5 / 4; object-fit: cover; }
        .photo-carousel .photo-carousel-photo:hover { outline: 3px solid var(--nas-gold); outline-offset: -3px; position: relative; z-index: 1; }
        .photo-carousel .photo-carousel-controls { justify-content: flex-end; margin: 0 0 .8rem; }
        .story-layout--seder .photo-carousel-controls output { color: #fff6e2; }

        @media (max-width: 64rem) {
            .home-hero .hero-content { padding-inline: 1.4rem; }
            .photo-carousel .photo-carousel-track { --photo-width: calc((100% - 2 * var(--photo-gap)) / 2.16); }
        }
        @media (max-width: 52rem) {
            .site-header .primary-nav { background: var(--nas-ivory); }
            .home-hero .hero-slide { display: flex; flex-direction: column; min-height: 0; }
            .home-hero .hero-media { position: relative; inset: auto; inline-size: 100%; aspect-ratio: 5 / 3; order: 1; }
            .home-hero .hero-overlay { display: none; }
            .home-hero .hero-content { inline-size: 100%; padding: 6rem 1.25rem 1.7rem; min-height: 20rem; }
            .home-hero .hero-control-row:not([hidden]) { left: 0; right: 0; bottom: .55rem; padding-inline: .65rem; }
            .home-hero .hero-dots { gap: .25rem; }
            .home-hero .hero-arrows { gap: .2rem; }
            .page-hero .page-hero-photo, .page-hero .page-hero-shade, .page-hero .page-hero-texture { inset-inline-start: 0; }
            .page-hero .page-hero-shade { background: linear-gradient(90deg,#102541e8,#1025415c); }
            .home-story .story-layout--opening .story-columns { grid-template-columns: minmax(0,1fr); }
        }
        @media (max-width: 37rem) {
            .photo-carousel .photo-carousel-track { --photo-width: calc((100% - var(--photo-gap)) / 1.23); }
        }
        @media (prefers-reduced-motion: no-preference) {
            .nasenyi-reveal { transition: transform .8s cubic-bezier(.2,.7,.2,1), opacity .7s; transition-delay: var(--nas-delay,0ms); }
            .nasenyi-reveal:not(.nasenyi-visible) { opacity: .34; transform: translateY(1.6rem); }
        }
    </style>

    <style>
        :root { --nas-paper: #fbf8f0; --nas-line: #e7dbc0; }
        .home-story, .entry-story, .nas-community-overview, .contact-layout.nasenyi-donate {
            --nas-story-font: "Instrument Serif", Georgia, serif;
        }

        /* The homepage banner uses its existing layout and slideshow. */
        main > .page-hero {
            background: var(--nas-navy);
            min-block-size: 0;
            border: 0;
            overflow: hidden;
        }
        main > .page-hero .page-hero-photo {
            inset: 0 0 0 33%;
            clip-path: polygon(14% 0, 100% 0, 100% 100%, 0 100%);
        }
        main > .page-hero .page-hero-photo img { object-position: center; }
        main > .page-hero :is(.page-hero-shade,.page-hero-texture) { display: none; }
        main > .page-hero .page-hero-inner {
            display: flex; flex-direction: column; align-items: flex-start; justify-content: center; text-align: start;
            padding-block: clamp(8.1rem, 11vw, 10rem) clamp(2.4rem, 5vw, 4.4rem);
            min-block-size: 0;
        }
        main > .page-hero .page-hero-inner h1 {
            font-family: "Instrument Serif", Georgia, serif;
            font-size: clamp(2.4rem, 4vw, 4.5rem); text-wrap: wrap;
            line-height: .98; text-align: start;
            inline-size: min(31vw, 22rem); max-inline-size: none;
            margin-block: .45rem .75rem;
        }
        main > .page-hero .page-hero-inner .page-hero-subtitle { max-inline-size: min(33%, 34ch); }
        main > .page-hero .page-hero-inner .page-hero-rule { margin-block: .8rem 0; }
        main > .page-hero .breadcrumbs { opacity: .86; justify-content: flex-start; margin-inline: 0; }
        [data-story-page="about"] .entry-story.entry-story--page { padding-block-start: 0; }

        .home-story.wrapper { padding-block-start: clamp(2.4rem, 4.2vw, 4.6rem); }
        .home-story .story-section.nas-home-opening {
            max-inline-size: none;
            padding: 0 0 clamp(1.7rem,3.5vw,3.4rem);
            margin: 0 0 clamp(1.6rem,3.5vw,3.4rem);
            border: 0; border-bottom: 1px solid var(--nas-line);
            border-radius: 0; background: transparent; box-shadow: none;
        }
        .nas-home-opening .nas-opening-title h2, .nas-topics-eyebrow {
            font: 700 .92rem/1.35 "Instrument Sans", sans-serif !important;
            color: #8a570a !important;
            letter-spacing: .12em; text-transform: uppercase; margin: 0 0 .45rem !important;
        }
        .nas-home-opening .nas-opening-title h3, .nas-topics-heading {
            font: 700 clamp(2.3rem,4vw,4.6rem)/1.04 var(--nas-story-font) !important;
            color: var(--nas-navy) !important; margin: 0 0 1.5rem !important;
        }
        .nas-opening-content {
            display: grid; grid-template-columns: minmax(0,1.52fr) minmax(0,1fr);
            align-items: start; gap: clamp(1.1rem,4vw,3rem);
        }
        .nas-opening-content figure { margin: 0; overflow: hidden; border-radius: .75rem; }
        .nas-opening-content figure img {
            display: block; inline-size: 100%; block-size: auto; max-block-size: none;
            object-fit: contain;
        }
        .nas-opening-copy > :first-child { margin-block-start: 0; }
        .nas-home-opening .nas-stat-row {
            display: grid; grid-template-columns: repeat(4, minmax(0,1fr));
            gap: .65rem; margin-block-start: 1.35rem;
        }
        .nas-home-opening .nas-stat-row .story-stat {
            display: grid; align-content: start; gap: .35rem; min-inline-size: 0;
            margin: 0; padding: clamp(.6rem, 1.6vw, 1.2rem);
            background: #fffaf0; border: 1px solid var(--nas-line); border-radius: .55rem;
        }
        .nas-stat-row .story-stat strong { white-space: nowrap; line-height: 1.05; font-size: clamp(1.1rem, 2.55vw, 2.5rem); }
        .nas-stat-row .story-stat span { white-space: normal; font-size: clamp(.73rem,1.2vw,.95rem); line-height: 1.3; }

        .home-story .story-layout--topics, .nas-community-overview {
            padding: clamp(1.55rem,3.4vw,3.3rem) 0;
            background: transparent; border: 0; box-shadow: none;
        }
        .nas-community-overview { margin: 0 auto; }
        .nas-community-overview .story-layout--topics { margin: 0; }
        .nas-community-overview .story-layout--topics > .story-columns { display: block; }
        .nas-community-overview .story-layout--topics > .story-columns > .story-column {
            display: grid; grid-template-columns: repeat(3,minmax(0,1fr));
            grid-template-rows: auto auto; grid-auto-rows: 1fr; gap: 1.15rem;
        }
        .nas-community-overview .story-layout--topics > .story-columns > .story-column > h2 { grid-column: 1 / -1; }
        .nas-community-overview .story-layout--topics > .story-columns > .story-column > .story-columns { display: contents; }
        .nas-community-overview .story-layout--topics .story-columns .story-columns > .story-column { display: flex; min-inline-size: 0; }
        :is(.home-story,.nas-community-overview) .story-layout--topics .story-card {
            width: 100%; display: grid; grid-template-rows: auto auto 1fr;
            border: 1px solid #d3c7b0; border-radius: .65rem;
            box-shadow: none; overflow: hidden; background: #fffdf7;
        }
        :is(.home-story,.nas-community-overview) .story-layout--topics .story-card figure { margin: 0; }
        :is(.home-story,.nas-community-overview) .story-layout--topics .story-card figure img {
            display: block; inline-size: 100%; block-size: auto; max-block-size: none;
            aspect-ratio: 16/10; object-fit: cover;
        }
        :is(.home-story,.nas-community-overview) .story-layout--topics .story-card h3 {
            font: 700 clamp(1.45rem,2.1vw,2.2rem)/1.08 var(--nas-story-font);
            margin: .85rem 0 .4rem; padding-inline: .85rem; color: var(--nas-navy);
        }
        :is(.home-story,.nas-community-overview) .story-layout--topics .story-card p { margin: 0 0 .9rem; padding-inline: .85rem; }
        .nas-community-overview .nas-topics-heading { display: none; }
        .nas-community-overview .nas-topics-eyebrow { font: 700 clamp(1.85rem, 3vw, 3.3rem)/1.08 var(--nas-story-font) !important; color: var(--nas-navy) !important; letter-spacing: normal; text-transform: none; text-align: start; justify-self: stretch; max-inline-size: none; }

        [data-story-page="about"] .entry-story > .story-body { padding-block-start: clamp(1.75rem,3vw,3rem); }
        [data-story-page="about"] .story-body > .story-section:first-child.nas-about-intro {
            display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1.06fr); gap: clamp(1.25rem,3.5vw,3rem);
            max-inline-size: none; padding: 0; margin: 0 auto clamp(2rem,4vw,3.4rem);
            background: transparent; border: 0; border-radius: 0; box-shadow: none;
        }
        .nas-about-intro figure { margin: 0; }
        .nas-about-intro img, .nas-history-photo img { display: block; width: 100%; height: auto; border-radius: .65rem; object-fit: contain; }
        [data-story-page="about"] .story-body > .story-section:first-child.nas-about-intro::after { display: none; }
        .nas-about-copy :is(h2,h3), .nas-history-copy :is(h2,h3,h4),
        .nas-land-heading {
            font-family: var(--nas-story-font);
            color: var(--nas-navy); line-height: 1.06;
        }
        .nas-about-copy .nas-about-kicker {
            font: 700 .9rem/1.25 "Instrument Sans",sans-serif;
            color: #8a570a; letter-spacing: .12em; text-transform: uppercase; margin: 0 .05rem .45rem;
        }
        .nas-about-copy h2:not(.nas-about-kicker) { font-size: clamp(2rem,4vw,4.1rem); margin: 0 0 .8rem; }
        .nas-about-copy h2:not(.nas-about-kicker)::after, .nas-land-heading::after {
            display: block; content: ""; width: 3.35rem; height: .22rem; margin-block: .75rem;
            border-radius: 2px; background: var(--nas-gold);
        }
        [data-story-page="about"] .story-body > .story-section.nas-history-row {
            position: relative; max-inline-size: none; margin: 0; padding: .1rem 0 clamp(1.45rem,3vw,2.6rem) clamp(1rem,3vw,2.5rem);
            border: 0; border-inline-start: 2px solid #d7a341; border-radius: 0; background: transparent; box-shadow: none;
        }
        .nas-history-row::before {
            position: absolute; inset-inline-start: -.44rem; inset-block-start: .65rem;
            content: ""; background: #d7a341; width: .75rem; height: .75rem; border-radius: 50%;
        }
        .nas-history-row .nas-history-grid {
            display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1.06fr);
            gap: clamp(1.15rem,3vw,2.5rem); align-items: start;
        }
        [data-story-page="about"] .story-body .nas-history-grid.story-photo-spread { padding: 0; background: transparent; }
        .nas-history-grid > figure { grid-column: 2; grid-row: 1; margin: 0; }
        .nas-history-grid > :is(.nas-history-copy,.story-photo-copy) { grid-column: 1; grid-row: 1; }
        .nas-history-row :is(.nas-history-copy,.story-photo-copy) > :first-child { margin-top: 0; }
        [data-story-page="about"] .story-body > .story-section.nas-land-section {
            border-top: 1px solid var(--nas-line); max-inline-size: none; margin-block-start: 2.5rem;
        }
        .nas-land-section .photo-carousel { margin-block: 1rem 1.8rem; }
        .nas-land-section .photo-carousel-controls { margin: .55rem 0 0; justify-content: center; }

        .home-story .story-layout--seder {
            max-inline-size: none; border-radius: .75rem; padding: clamp(1.2rem,3vw,2.3rem);
            border-top: 4px solid var(--nas-gold); margin-block: 2.4rem;
        }
        .nas-seder-bar {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .65rem 1rem;
            margin-block-end: 1rem;
        }
        .home-story .story-layout--seder .nas-seder-bar h3 {
            font: 700 clamp(1.9rem,3.25vw,3.5rem)/1.08 var(--nas-story-font);
            color: var(--nas-paper); margin: 0;
        }
        .nas-seder-bar .photo-carousel-controls { display: flex; gap: .42rem; margin: 0; }
        .nas-seder-bar .photo-carousel-controls button {
            border: 1px solid #faf7ef; border-radius: .4rem;
            background: transparent; color: #faf7ef; min-width: 44px;
        }
        .nas-seder-bar .photo-carousel-controls button:hover { background: #ffffff26; }
        .home-story .story-layout--seder .photo-carousel { margin: 0 0 1.7rem; }

        .nas-donation-heading.wrapper { padding-block: clamp(7rem,10vw,9rem) .2rem; }
        .nas-donation-heading h1 {
            font: 700 clamp(2.4rem,5.4vw,5.7rem)/1.03 "Instrument Serif",Georgia,serif;
            color: var(--nas-navy); margin: .3rem 0 .9rem;
        }
        .nas-donation-heading h1::after { display:block; content:""; width:4rem; height:.22rem; margin-top:.75rem; background:var(--nas-gold); }
        .nas-donation-heading .breadcrumbs { justify-content: flex-start; margin-inline: 0; }
        .nas-donation-heading .breadcrumbs, .nas-donation-heading .breadcrumbs :is(a,span) { color: #435266; }
        .contact-layout.nasenyi-donate {
            padding-block-start: 1rem; gap: clamp(1.2rem,3vw,2.7rem);
            grid-template-columns: minmax(0,1.12fr) minmax(0,1fr); align-items: start;
        }
        .nasenyi-donate-photo img { width: 100%; max-block-size: none; aspect-ratio: 1.2; object-fit: cover; }
        .nasenyi-donate .donation-intro-subtitle { margin-top: .5rem; }
        .nasenyi-donate .contact-form { padding: clamp(1rem,2.5vw,2rem); box-shadow:none; }
        .nasenyi-donate .contact-form .field-grid { grid-template-columns: minmax(0,1fr); }
        .nasenyi-donate .contact-form .button { width:100%; justify-content:center; background:var(--nas-gold); color:var(--nas-navy); }

        @media (max-width: 62rem) {
            .nas-community-overview .story-layout--topics > .story-columns > .story-column { grid-template-columns: repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 48rem) {
            main > .page-hero .page-hero-photo { inset: 0; clip-path: none; }
            main > .page-hero .page-hero-shade { display:block; inset:0; background:linear-gradient(90deg,#102541eb,#10254169); }
            main > .page-hero .page-hero-inner h1 { inline-size: 100%; max-inline-size: 14ch; }
            main > .page-hero .page-hero-inner .page-hero-subtitle { max-inline-size: 34ch; }
            .nas-opening-content, .contact-layout.nasenyi-donate,
            [data-story-page="about"] .story-body > .story-section:first-child.nas-about-intro,
            .nas-history-row .nas-history-grid { grid-template-columns: minmax(0,1fr); }
            .nas-history-grid > figure { grid-column: 1; grid-row: 2; }
            .nas-home-opening .nas-stat-row { grid-template-columns: repeat(3,minmax(0,1fr)); gap: .4rem; }
        }
        @media (max-width: 37rem) {
            .nas-community-overview .story-layout--topics > .story-columns > .story-column { grid-template-columns: minmax(0,1fr); grid-auto-rows: auto; }
        }
    </style>
</head>
<body @if(request()->is('about-us', 'public/about-us')) data-story-page="about" @endif style="--post-image-ratio: {{ str_replace(':', ' / ', $blogImageRatio) }}; --post-image-fit: {{ $blogImageFit }}">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="scroll-progress" data-scroll-progress aria-hidden="true"></div>
    <header class="site-header" data-header>
        <div class="wrapper header-inner">
            <a class="site-brand" href="{{ route('site.home') }}" aria-label="{{ $siteName }} home">
                <img src="{{ $logo }}" alt="" width="52" height="52" decoding="async">
                <span><strong>{{ $brandPrimary }}</strong><small>{{ $brandSecondary }}</small></span>
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
                <a href="https://shop.nasenyijewishcommunity.or.ug/">Shop</a>
                <a class="search-link" href="{{ route('site.search') }}" aria-label="Search the website" @if(request()->is('search')) aria-current="page" @endif><span aria-hidden="true"></span><span class="search-text">Search</span></a>
                <a class="button button-gold header-donate" href="{{ route('donations.show') }}">Donate <span aria-hidden="true">→</span></a>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <main id="main-content">
        {{ $slot }}

        @if(str_ends_with(rtrim(request()->path(), '/'), 'community-spiritual-life'))
            @php($communityHome = \App\Models\ContentEntry::published()->where('type', 'page')->where('slug', '')->first())
            @if($communityHome !== null)
                <template id="nas-community-source">{!! \App\Support\StoryLayout::render($communityHome) !!}</template>
            @endif
        @endif
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
                    <span><strong>{{ $brandPrimary }}</strong><small>{{ $brandSecondary }}</small></span>
                </a>
                @if(filled($settings['location'] ?? null))
                    <p class="footer-location">{{ $settings['location'] }}</p>
                @endif
                @if(filled($settings['phone'] ?? null))
                    <p class="footer-location"><a href="tel:{{ preg_replace('/[^+0-9]/', '', $settings['phone']) }}">{{ $settings['phone'] }}</a></p>
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

    <script>
        function initializePhotoCarousels() {
            const galleries = [...document.querySelectorAll('main .story-gallery')].filter((gallery) =>
                gallery.closest('.story-layout--seder') ||
                (document.body.dataset.storyPage === 'about' && gallery.children.length >= 5)
            );

            if (!galleries.length) return;

            const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
            const viewer = document.createElement('dialog');
            viewer.className = 'photo-carousel-dialog';
            viewer.setAttribute('aria-label', 'Full-size photo');
            viewer.innerHTML = '<button type="button" autofocus>Close photo</button><img alt="">';
            viewer.querySelector('button').addEventListener('click', () => viewer.close());
            viewer.addEventListener('click', (event) => {
                if (event.target === viewer) viewer.close();
            });
            viewer.addEventListener('close', () => viewer.querySelector('img').removeAttribute('src'));
            document.body.append(viewer);

            galleries.forEach((track) => {
                const slides = [...track.children];
                if (slides.length < 2 || !slides.every((slide) => slide.matches('figure') && slide.querySelector(':scope > img'))) return;

                const section = track.closest('.story-section');
                const title = section.querySelector('h2, h3')?.textContent.trim() || 'Photo gallery';
                const carousel = document.createElement('div');
                carousel.className = 'photo-carousel';
                carousel.setAttribute('role', 'region');
                carousel.setAttribute('aria-label', title + ' photo slider');
                track.before(carousel);
                carousel.append(track);
                track.classList.add('photo-carousel-track');
                track.tabIndex = 0;
                track.setAttribute('aria-label', 'Photos. Use the left and right arrow keys to browse.');

                slides.forEach((slide, index) => {
                    const image = slide.querySelector('img');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'photo-carousel-photo';
                    button.setAttribute('aria-label', 'Open photo ' + (index + 1) + (image.alt ? ': ' + image.alt : ''));
                    image.before(button);
                    button.append(image);

                });

                track.addEventListener('click', (event) => {
                    const image = event.target.closest('.photo-carousel-photo')?.querySelector('img');
                    if (!image) return;
                    const fullImage = viewer.querySelector('img');
                    fullImage.alt = image.alt;
                    fullImage.src = image.getAttribute('src');
                    viewer.showModal();
                });

                const buffer = Math.min(4, slides.length);
                const makeEdge = (slide) => {
                    const copy = slide.cloneNode(true);
                    copy.setAttribute('aria-hidden', 'true');
                    copy.querySelector('button').tabIndex = -1;
                    return copy;
                };
                track.prepend(...slides.slice(-buffer).map(makeEdge));
                track.append(...slides.slice(0, buffer).map(makeEdge));

                const controls = document.createElement('div');
                controls.className = 'photo-carousel-controls';
                controls.innerHTML = '<button type="button" aria-label="Previous photo">←</button>' +
                    '<output aria-live="off"></output>' +
                    '<button type="button" aria-label="Next photo">→</button>' +
                    '<button type="button">Pause</button>';
                carousel.prepend(controls);
                const [previous, next, playback] = controls.querySelectorAll('button');
                const counter = controls.querySelector('output');

                let slideIndex = buffer;
                const stride = () => track.children[1].offsetLeft - track.children[0].offsetLeft;
                const normalize = (index) => buffer + ((index - buffer) % slides.length + slides.length) % slides.length;
                let viewed = false;
                let playing = !reducedMotion.matches;
                let settleTimer;
                let autoplayTimer;

                const moveTo = (index, smooth = false) => {
                    slideIndex = index;
                    track.scrollTo({
                        left: track.children[index].offsetLeft,
                        behavior: smooth && !reducedMotion.matches ? 'smooth' : 'instant',
                    });
                };

                const update = () => {
                    counter.textContent = (normalize(slideIndex) - buffer + 1) + ' / ' + slides.length;
                    playback.textContent = playing ? 'Pause' : 'Play';
                    playback.setAttribute('aria-label', playing ? 'Pause photo slideshow' : 'Play photo slideshow');
                    slides.forEach((slide, index) => {
                        const offset = (index - (normalize(slideIndex) - buffer) + slides.length) % slides.length;
                        const visible = index + buffer >= slideIndex && offset < Math.floor((track.clientWidth + 1) / (stride() || 1));
                        slide.querySelector('button').tabIndex = visible ? 0 : -1;
                    });
                };

                const settle = () => {
                    if (!stride()) return;
                    slideIndex = Math.round(track.scrollLeft / stride());
                    const normalized = normalize(slideIndex);
                    if (slideIndex !== normalized) moveTo(normalized);
                    update();
                };

                const schedule = () => {
                    clearTimeout(autoplayTimer);
                    if (!playing || !viewed || document.hidden || viewer.open || (matchMedia('(hover: hover)').matches && carousel.matches(':hover')) || track.matches(':focus-within')) return;
                    autoplayTimer = setTimeout(() => {
                        moveTo(normalize(slideIndex) + 1, true);
                        schedule();
                    }, 4400);
                };

                const step = (direction) => {
                    const index = normalize(slideIndex);
                    if (slideIndex !== index) moveTo(index);
                    moveTo(index + direction, true);
                    schedule();
                };

                track.addEventListener('scroll', () => {
                    clearTimeout(settleTimer);
                    settleTimer = setTimeout(settle, 140);
                }, { passive: true });
                track.addEventListener('keydown', (event) => {
                    if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
                    event.preventDefault();
                    step(event.key === 'ArrowLeft' ? -1 : 1);
                });
                previous.addEventListener('click', () => step(-1));
                next.addEventListener('click', () => step(1));
                playback.addEventListener('click', () => {
                    playing = !playing;
                    update();
                    schedule();
                });

                ['pointerenter', 'pointerleave', 'focusin', 'focusout'].forEach((event) => {
                    carousel.addEventListener(event, () => requestAnimationFrame(schedule));
                });
                document.addEventListener('visibilitychange', schedule);
                viewer.addEventListener('close', schedule);
                reducedMotion.addEventListener('change', () => {
                    playing = !reducedMotion.matches;
                    update();
                    schedule();
                });
                new ResizeObserver(() => moveTo(slideIndex)).observe(track);
                new IntersectionObserver(([entry]) => {
                    viewed = entry.isIntersecting;
                    schedule();
                }, { threshold: .15 }).observe(carousel);

                moveTo(buffer);
                update();
            });
        }


        function initializeNasenyiDesign() {
            const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
            const heroPhotos = [...document.querySelectorAll('.home-hero .hero-media')];
            document.querySelectorAll('.home-hero [data-slide-to]').forEach((button, index) => {
                const picture = heroPhotos[index];
                const source = picture?.querySelector('source')?.getAttribute('srcset') || picture?.querySelector('img')?.getAttribute('src');
                if (!source) return;
                const image = document.createElement('img');
                image.alt = '';
                image.loading = 'lazy';
                image.decoding = 'async';
                image.src = source;
                button.append(image);
            });

            const donationForm = document.querySelector('.contact-layout form[action$="/donation"]');
            const donationPicture = document.querySelector('.page-hero .page-hero-photo');
            if (donationForm && donationPicture) {
                const layout = donationForm.closest('.contact-layout');
                const copy = donationPicture.cloneNode(true);
                copy.className = 'nasenyi-donate-photo';
                copy.querySelector('img').loading = 'lazy';
                copy.querySelector('img').removeAttribute('fetchpriority');
                layout.classList.add('nasenyi-donate');
                layout.querySelector('.prose')?.prepend(copy);
            }

            document.querySelectorAll('[data-story-page="about"] .story-body > .story-section').forEach((section) => {
                const title = section.querySelector('h2, h3')?.textContent.trim() || '';
                if (/^(1919|1979|Timeline block)/i.test(title)) section.classList.add('nasenyi-timeline');
            });

            if (reducedMotion.matches) return;
            const reveal = new IntersectionObserver((entries, observer) => {
                entries.forEach(({ target, isIntersecting }) => {
                    if (!isIntersecting) return;
                    target.classList.add('nasenyi-visible');
                    observer.unobserve(target);
                });
            }, { threshold: .12 });
            document.querySelectorAll('.story-layout--topics .story-card, .nasenyi-timeline').forEach((item, index) => {
                item.style.setProperty('--nas-delay', (index % 3) * 90 + 'ms');
                item.classList.add('nasenyi-reveal');
                reveal.observe(item);
            });
        }


        function arrangeApprovedLayouts() {
            const element = (tag, className) => {
                const node = document.createElement(tag);
                node.className = className;
                return node;
            };

            const communitySource = document.querySelector('#nas-community-source');
            const communitySection = communitySource && [...communitySource.content.querySelectorAll('.story-section')].find((section) =>
                [...section.querySelectorAll('h2')].some((title) => title.textContent.trim() === 'Prayer, traditions, cultural identity, learning and festivals')
            );
            if (communitySection) {
                const overview = element('div', 'wrapper prose nas-community-overview');
                communitySection.classList.add('story-layout--topics');
                overview.append(communitySection);
                document.querySelector('main > .page-hero')?.after(overview);
                const cover = overview.querySelector('.story-card img[alt="Global connection"]');
                const heroPhoto = document.querySelector('main > .page-hero .page-hero-photo');
                const heroImage = heroPhoto?.querySelector('img');
                if (cover && heroImage && heroImage.getAttribute('src') === '/media/headers/community.webp') {
                    heroPhoto.querySelectorAll('source').forEach((source) => source.remove());
                    heroImage.setAttribute('src', cover.getAttribute('src'));
                    const sizes = cover.getAttribute('srcset');
                    if (sizes) heroImage.setAttribute('srcset', sizes);
                    else heroImage.removeAttribute('srcset');
                    heroImage.setAttribute('sizes', '(max-width: 48rem) 100vw, 67vw');
                }
            }

            const opening = document.querySelector('.home-story .story-layout--opening');
            const columns = opening?.querySelector(':scope > .story-columns');
            if (columns?.children.length === 2) {
                const [textColumn, imageColumn] = columns.children;
                const photo = imageColumn.querySelector(':scope > figure');
                const subtitle = textColumn.querySelector(':scope > h3');
                if (photo && subtitle) {
                    const title = element('header', 'nas-opening-title');
                    const split = element('div', 'nas-opening-content');
                    const copy = element('div', 'nas-opening-copy');
                    const stats = element('div', 'nas-stat-row');
                    title.append(...textColumn.querySelectorAll(':scope > h2, :scope > h3'));
                    stats.append(...columns.querySelectorAll('.story-stat'));
                    copy.append(...textColumn.children);
                    split.append(photo, copy);
                    opening.classList.add('nas-home-opening');
                    columns.replaceWith(title, split, stats, ...imageColumn.children);
                }
            }

            document.querySelectorAll('.story-layout--topics > .story-columns > .story-column').forEach((column) => {
                const [eyebrow, title] = column.querySelectorAll(':scope > h2');
                eyebrow?.classList.add('nas-topics-eyebrow');
                title?.classList.add('nas-topics-heading');
            });

            const seder = document.querySelector('.home-story .story-layout--seder');
            const sederCarousel = seder?.querySelector('.photo-carousel');
            const sederTitle = seder?.querySelector('h3');
            const sederControls = sederCarousel?.querySelector('.photo-carousel-controls');
            if (sederTitle && sederCarousel && sederControls) {
                const bar = element('header', 'nas-seder-bar');
                bar.append(sederTitle, sederControls);
                seder.prepend(bar, sederCarousel);
            }

            if (document.body.dataset.storyPage === 'about') {
                const sections = [...document.querySelectorAll('.entry-story .story-body > .story-section')];
                const intro = sections[0];
                const illustrated = sections.find((section) => section.querySelector('.story-photo-spread > figure'));
                const archivePhoto = illustrated?.querySelector('.story-photo-spread > figure');
                if (intro && archivePhoto) {
                    const copy = element('div', 'nas-about-copy');
                    copy.append(...intro.children);
                    copy.querySelector('h2')?.classList.add('nas-about-kicker');
                    const inset = archivePhoto.cloneNode(true);
                    inset.removeAttribute('id');
                    inset.removeAttribute('data-reveal-pending');
                    inset.querySelectorAll('[data-reveal-pending]').forEach((node) => node.removeAttribute('data-reveal-pending'));
                    inset.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));
                    inset.querySelector('img')?.setAttribute('alt', '');
                    inset.querySelector('figcaption')?.remove();
                    intro.append(copy, inset);
                    intro.classList.add('nas-about-intro');
                }
                sections.forEach((section) => {
                    if (!section.classList.contains('nasenyi-timeline')) return;
                    section.classList.add('nas-history-row');
                    const spread = section.querySelector(':scope > .story-photo-spread');
                    if (spread) {
                        spread.classList.add('nas-history-grid');
                        spread.querySelector('figure')?.classList.add('nas-history-photo');
                        return;
                    }
                    const figure = section.querySelector(':scope > figure');
                    if (!figure) return;
                    const grid = element('div', 'nas-history-grid');
                    const copy = element('div', 'nas-history-copy');
                    figure.classList.add('nas-history-photo');
                    figure.remove();
                    copy.append(...section.children);
                    grid.append(copy, figure);
                    section.append(grid);
                });
                const landGallery = document.querySelector('.entry-story .photo-carousel');
                const land = landGallery?.closest('.story-section');
                const landHeading = land?.querySelector(':scope > h2');
                const controls = landGallery?.querySelector('.photo-carousel-controls');
                if (landHeading && controls) {
                    land.classList.add('nas-land-section');
                    landHeading.classList.add('nas-land-heading');
                    land.prepend(landHeading);
                    landGallery.append(controls);
                }
            }

            const giving = document.querySelector('.contact-layout.nasenyi-donate');
            const givingForm = giving?.querySelector('form');
            const formTitle = givingForm?.querySelector('h2');
            const banner = giving?.previousElementSibling;
            if (formTitle && banner?.classList.contains('page-hero')) {
                const heading = element('header', 'wrapper nas-donation-heading');
                const title = element('h1', '');
                title.append(...formTitle.childNodes);
                const breadcrumb = banner.querySelector('.breadcrumbs');
                const subtitle = banner.querySelector('.page-hero-subtitle');
                if (breadcrumb) heading.append(breadcrumb);
                heading.append(title);
                formTitle.remove();
                if (subtitle) {
                    subtitle.className = 'donation-intro-subtitle';
                    const copy = giving.querySelector('.prose');
                    copy?.querySelector('h2')?.after(subtitle);
                }
                banner.replaceWith(heading);
            }
        }

        document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(() => {
            initializePhotoCarousels();
            initializeNasenyiDesign();
            arrangeApprovedLayouts();
        }), { once: true });
    </script>
</body>
</html>
