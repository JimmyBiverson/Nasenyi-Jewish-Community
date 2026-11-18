<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Updates')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image ?: '/media/headers/updates.jpg'"
    :noindex="$filtered"
>
    @include('site.partials.page-header', ['heading' => $entry?->title ?: 'Updates', 'image' => $entry?->image ?: '/media/headers/updates.jpg', 'subtitle' => 'Stories, news, and reflections from the Nasenyi Abayudaya community.'])
    @if(filled($entry?->body))
        <div class="wrapper prose index-intro" data-reveal>{!! $entry->body !!}</div>
    @endif
    <section class="wrapper listing-section" aria-label="Community updates">
        @if($filtered)
            <a class="text-link" href="{{ route('site.updates') }}/">← All community updates</a>
        @endif
        <div class="story-grid">
            @forelse($posts as $post)
                @include('site.partials.post-card')
            @empty
                <p>No updates found.</p>
            @endforelse
        </div>
        {{ $posts->links() }}
    </section>
</x-site-layout>
