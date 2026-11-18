<x-site-layout title="Search" :noindex="true">
    @include('site.partials.page-header', ['heading' => 'Search'])
    <section class="wrapper listing-section" aria-labelledby="search-label">
        <form class="search-form" action="{{ route('site.search') }}">
            <label class="field" for="search-term" id="search-label">Search community pages and stories</label>
            <div><input id="search-term" name="q" type="search" maxlength="120" value="{{ $term }}" required><button class="button button-gold" type="submit">Search</button></div>
        </form>
        @if($matches)
            <p class="search-summary">{{ $matches->total() }} {{ \Illuminate\Support\Str::plural('result', $matches->total()) }} for “{{ $term }}”</p>
            <div class="story-grid">
                @foreach($matches as $post)
                    @include('site.partials.post-card')
                @endforeach
            </div>
            {{ $matches->links() }}
        @endif
    </section>
</x-site-layout>
