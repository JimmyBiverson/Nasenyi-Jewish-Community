@props([
    'heading',
    'subtitle' => null,
    'image' => null,
    'override' => null,
    'parent' => null,
])
@php
    $sharedImage = \App\Models\SiteSetting::where('key', 'inner_header_image')->value('value');
    $banner = \App\Support\PublicImage::banner($override ?: ($sharedImage ?: ($image ?: '/media/headers/story.webp')));
@endphp
<header class="page-hero">
    <picture class="page-hero-photo" aria-hidden="true">
        @if($banner['narrow'])
            <source media="(max-width: 720px)" srcset="{{ $banner['narrow'] }}">
        @endif
        <img src="{{ $banner['source'] }}" alt="" fetchpriority="high" decoding="async">
    </picture>
    <div class="page-hero-shade" aria-hidden="true"></div>
    <div class="page-hero-texture" aria-hidden="true"></div>
    <div class="wrapper page-hero-inner">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('site.home') }}">Home</a>
            <span aria-hidden="true">/</span>
            @if($parent)
                <a href="{{ $parent['url'] }}">{{ $parent['label'] }}</a>
                <span aria-hidden="true">/</span>
            @endif
            <span aria-current="page">{{ $heading }}</span>
        </nav>
        <h1 dir="auto">{{ $heading }}</h1>
        @if(filled($subtitle))
            <p class="page-hero-subtitle">{{ $subtitle }}</p>
        @endif
        <div class="page-hero-rule" aria-hidden="true"></div>
    </div>
</header>
