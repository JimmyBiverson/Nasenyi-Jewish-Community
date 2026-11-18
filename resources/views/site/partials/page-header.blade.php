@props([
    'heading',
    'subtitle' => null,
    'image' => null,
    'parent' => null,
    'align' => 'left',
])
@php
    $default = '/media/headers/story.jpg';
    $hero = $image ?: $default;
    $webp = preg_match('/\.(jpe?g|png)$/i', $hero) ? preg_replace('/\.(jpe?g|png)$/i', '.webp', $hero) : null;
    $sm = preg_match('/\.(jpe?g|png|webp)$/i', $hero) ? preg_replace('/(\.(jpe?g|png|webp))$/i', '-sm$1', $hero) : null;
@endphp
<header class="page-hero {{ $align === 'center' ? 'page-hero-center' : '' }}" data-reveal>
    <div class="hero-media" aria-hidden="true">
        <picture>
            @if($webp && $sm)
                <source type="image/webp" media="(max-width: 720px)" srcset="{{ $sm }}">
            @endif
            @if($webp)
                <source type="image/webp" srcset="{{ $webp }}">
            @endif
            <img class="hero-photo" src="{{ $hero }}" alt="" fetchpriority="high" decoding="async" width="1920" height="700">
        </picture>
        <div class="hero-overlay"></div>
    </div>
    <div class="wrapper page-hero-inner">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('site.home') }}">Home</a>
            <span aria-hidden="true">/</span>
            @if($parent ?? null)
                <a href="{{ $parent['url'] }}">{{ $parent['label'] }}</a>
                <span aria-hidden="true">/</span>
            @endif
            <span aria-current="page">{{ $heading }}</span>
        </nav>
        <h1>{{ $heading }}</h1>
        @if(filled($subtitle ?? null))
            <p class="page-hero-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</header>
