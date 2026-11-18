<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Gallery')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image ?: '/media/headers/gallery.jpg'"
>
    @include('site.partials.page-header', ['heading' => $entry?->title ?: 'Gallery', 'image' => $entry?->image ?: '/media/headers/gallery.jpg', 'subtitle' => 'Moments of prayer, learning, celebration, and everyday life in Nasenyi.'])
    @if(filled($entry?->body))
        <div class="wrapper prose index-intro" data-reveal>{!! $entry->body !!}</div>
    @endif
    <section class="wrapper listing-section" aria-label="Community photos" data-reveal>
        <div class="gallery-grid" data-gallery>
            @foreach($images as $image)
                <figure data-reveal>
                    <a href="{{ $image->path }}" data-lightbox data-caption="{{ $image->caption ?: $image->title }}">
                        <img src="{{ $image->path }}" alt="{{ $image->alt_text }}" loading="lazy" decoding="async">
                    </a>
                    @if($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
        @if($images->isEmpty() && ! filled($entry?->body))
            <p>No community photos are published yet.</p>
        @endif
        {{ $images->links() }}
    </section>
    <dialog class="gallery-dialog" data-lightbox-dialog aria-label="Community photograph">
        <form method="dialog"><button type="submit" aria-label="Close photo">×</button></form>
        <img src="" alt="">
        <p></p>
    </dialog>
</x-site-layout>
