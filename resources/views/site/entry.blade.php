@php
    $fallbackImage = match($entry->slug) {
        'about-us', 'our-history' => '/media/headers/about.jpg',
        'updates' => '/media/headers/updates.jpg',
        'donation-history' => '/media/headers/gallery.jpg',
        'contact-us' => '/media/headers/contact.jpg',
        'donation' => '/media/headers/donation.jpg',
        'my-account', 'home' => '/media/headers/story.jpg',
        'education-tab' => '/media/headers/education.jpg',
        'health', 'care-wellbeing' => '/media/headers/community.jpg',
        'team', 'community-spiritual-life', 'community-life', 'study-learning' => '/media/headers/community.jpg',
        'causes', 'checkout-2' => '/media/headers/heritage.jpg',
        'volunteer' => '/media/headers/community.jpg',
        'event-details' => '/media/headers/events.jpg',
        default => '/media/headers/story.jpg',
    };
    $headerImage = $entry->type === 'post' ? '/media/headers/updates.webp' : $fallbackImage;
@endphp
<x-site-layout
    :title="$entry->seo_title ?: $entry->title"
    :description="$entry->seo_description ?: $entry->summary"
    :image="$entry->social_image ?: $entry->image ?: $entry->header_image"
    :kind="$entry->type === 'post' ? 'article' : 'website'"
>
    @include('site.partials.page-header', [
        'heading' => $entry->title,
        'image' => $headerImage,
        'override' => $entry->header_image,
        'parent' => $entry->type === 'post' ? ['url' => route('site.updates').'/', 'label' => 'Updates'] : null,
    ])
    <article class="wrapper entry-story entry-story--{{ $entry->type }}">
        @if($entry->type === 'post')
            <div class="article-meta">
                @if($entry->published_at)
                    <time datetime="{{ $entry->published_at->toDateString() }}">{{ $entry->published_at->format('j F Y') }}</time>
                @endif
                @if($entry->category)
                    <a href="{{ route('site.updates', ['category' => $entry->category]) }}">{{ $entry->category }}</a>
                @endif
            </div>
        @endif
        <div class="prose story-body">{!! \App\Support\StoryLayout::render($entry) !!}</div>
        @if($entry->type === 'post')
            <div class="article-meta article-end">
                <a class="text-link" href="{{ route('site.updates') }}/">← All community updates</a>
                @foreach($entry->tags ?? [] as $tag)
                    <a href="{{ route('site.updates', ['tag' => $tag]) }}">{{ $tag }}</a>
                @endforeach
            </div>
        @endif
    </article>
</x-site-layout>
