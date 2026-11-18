<x-site-layout :title="$event->title" :description="\Illuminate\Support\Str::limit($event->description, 150)">
    @include('site.partials.page-header', [
        'heading' => $event->title,
        'image' => '/media/headers/events.jpg',
        'parent' => ['label' => 'Community Events', 'url' => route('site.events').'/'],
    ])
    <article class="wrapper entry-story" data-reveal>
        <div class="article-meta">
            <time datetime="{{ $event->starts_at->toAtomString() }}">{{ $event->starts_at->setTimezone('Africa/Kampala')->format('j F Y, H:i') }} EAT</time>
            @if($event->ends_at)
                <span>to <time datetime="{{ $event->ends_at->toAtomString() }}">{{ $event->ends_at->setTimezone('Africa/Kampala')->format('j F Y, H:i') }} EAT</time></span>
            @endif
            @if($event->location)<span>{{ $event->location }}</span>@endif
        </div>
        <div class="prose"><p class="preserve-lines">{{ $event->description }}</p></div>
    </article>
</x-site-layout>
