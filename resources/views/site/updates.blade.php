<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Updates')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image"
    :noindex="$filtered"
>
    @include('site.partials.page-header', ['heading' => $entry?->title ?: 'Updates', 'image' => '/media/headers/updates.webp', 'override' => $entry?->header_image])
    <section class="wrapper listing-section" aria-labelledby="updates-heading">
        <div class="section-heading"><h2 id="updates-heading">Nasenyi Latest Updates</h2></div>
        @if($filtered)
            <a class="text-link" href="{{ route('site.updates') }}/">← All community updates</a>
        @endif
        <div @class(['article-index' => $categories->count() > 1])>
            <div>
                <div class="story-grid">
                    @forelse($posts as $post)
                        @include('site.partials.post-card')
                    @empty
                        <p>No updates found.</p>
                    @endforelse
                </div>
                {{ $posts->links() }}
            </div>
            @if($categories->count() > 1)
                <nav class="article-filters" aria-label="Update categories">
                    <h2>Categories</h2>
                    <ul>
                        @foreach($categories as $label)
                            <li><a href="{{ route('site.updates', ['category' => $label]) }}" @if($category === $label) aria-current="page" @endif>{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>
    @if(filled($entry?->body))
        <div class="wrapper prose updates-story">{!! \App\Support\StoryLayout::render($entry) !!}</div>
    @endif
</x-site-layout>
