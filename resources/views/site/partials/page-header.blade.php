<header class="page-hero {{ $image ?? null ? 'page-hero-photo' : '' }}">
    @if($image ?? null)
        <img class="hero-photo" src="{{ $image }}" alt="" fetchpriority="high">
        <div class="hero-overlay"></div>
    @endif
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
            <p>{{ $subtitle }}</p>
        @endif
    </div>
</header>
