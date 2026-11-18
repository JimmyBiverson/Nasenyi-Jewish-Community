<x-site-layout
    :title="$entry->seo_title ?: $entry->title"
    :description="$entry->seo_description ?: $entry->summary"
    :image="$entry->social_image ?: $entry->image"
    :kind="$entry->type === 'post' ? 'article' : 'website'"
>
    @include('site.partials.page-header', [
        'heading' => $entry->title,
        'image' => $entry->image,
        'parent' => $entry->type === 'post' ? ['url' => route('site.updates').'/', 'label' => 'Updates'] : null,
    ])
    <article class="wrapper entry-story">
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
        <div class="prose story-body">{!! $entry->body !!}</div>
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
