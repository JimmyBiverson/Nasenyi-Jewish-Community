<x-site-layout title="Community Events">
    @include('site.partials.page-header', ['heading' => 'Community Events'])
    <section class="wrapper listing-section" aria-label="Events">
        <ul class="event-list">
            @forelse($events as $event)
                <li>
                    <time datetime="{{ $event->starts_at->toAtomString() }}"><strong>{{ $event->starts_at->setTimezone('Africa/Kampala')->format('j') }}</strong>{{ $event->starts_at->setTimezone('Africa/Kampala')->format('M Y') }}</time>
                    <div><h2><a href="{{ route('site.event', $event->slug) }}/">{{ $event->title }}</a></h2>@if($event->location)<p>{{ $event->location }}</p>@endif</div>
                </li>
            @empty
                <li>No community events are published yet.</li>
            @endforelse
        </ul>
        {{ $events->links() }}
    </section>
</x-site-layout>
