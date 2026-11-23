@props([
    'id' => 'pinned-cta',
    'eyebrow' => 'Ready When You Are',
    'title' => 'Support the Nasenyi Jewish Community',
    'body' => 'Your gift keeps our stories, gatherings, and learning alive in Nasenyi and beyond.',
    'href' => route('donations.show'),
    'label' => 'Donate',
    'image' => '/media/headers/donation.webp',
    'tone' => 'royal',
])
<section class="pinned-cta pinned-cta--{{ $tone }}" id="{{ $id }}" data-pinned-cta aria-labelledby="{{ $id }}-heading">
    <div class="pinned-cta__media" data-pinned-cta-media aria-hidden="true">
        <img src="{{ $image }}" alt="" loading="lazy" decoding="async">
    </div>
    <svg class="pinned-cta__ring" data-pinned-cta-ring viewBox="0 0 100 100" aria-hidden="true" focusable="false">
        <circle cx="50" cy="50" r="46" fill="none" stroke="currentColor" stroke-width="1.1"></circle>
    </svg>
    <div class="pinned-cta__content" data-pinned-cta-content>
        @if(filled($eyebrow))
            <span class="pinned-cta__eyebrow">{{ $eyebrow }}</span>
        @endif
        <h2 id="{{ $id }}-heading">{{ $title }}</h2>
        @if(filled($body))
            <p>{{ $body }}</p>
        @endif
        <a class="button button-gold" href="{{ $href }}">{{ $label }} <span aria-hidden="true">→</span></a>
    </div>
</section>
