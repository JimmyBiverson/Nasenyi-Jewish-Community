@extends('admin.layout')
@section('title', 'Manage images')
@section('main')
    @php
        $homepage = $entry->slug === '';
        $blogRatio = match($display->get('blog_image_ratio')) {
            '16:9' => '16 / 9',
            '4:3' => '4 / 3',
            '1:1' => '1 / 1',
            default => '3 / 2',
        };
        $blogFit = $display->get('blog_image_fit') === 'contain' ? 'contain' : 'cover';
    @endphp
    <a class="breadcrumbs" href="{{ route('admin.content.edit', $entry) }}">← Edit {{ $entry->type }}</a>
    <header class="page-head">
        <div>
            <h1>Manage images</h1>
            <p class="subtle">{{ $entry->title }}</p>
        </div>
        @if($entry->status === 'published')
            <a class="button ghost" href="{{ $homepage ? route('site.home') : url($entry->slug).'/' }}">View published {{ $entry->type }}</a>
        @endif
    </header>
    <nav class="media-nav" aria-label="Image controls">
        <a class="button ghost" href="#featured">Featured image</a>
        @if($homepage)
            <a class="button ghost" href="#welcome-slides">Welcome slides</a>
        @else
            <a class="button ghost" href="#banner">Page banner</a>
        @endif
        <a class="button ghost" href="#section-images">Section images <span aria-hidden="true">({{ count($images) }})</span></a>
    </nav>

    <div class="media-stack">
        @include('admin.content.image-form', [
            'anchor' => 'featured',
            'heading' => 'Featured image',
            'action' => route('admin.content.media.featured', $entry),
            'photo' => [
                'image' => $entry->image,
                'position_x' => $entry->image_position_x,
                'position_y' => $entry->image_position_y,
            ],
            'ratio' => $entry->type === 'post' ? $blogRatio : '3 / 2',
            'defaultFit' => $entry->type === 'post' ? $blogFit : 'cover',
            'showFocalPoint' => true,
        ])

        @if($homepage)
            <h2 id="welcome-slides">Welcome slides</h2>
            @forelse($entry->hero_slides ?? [] as $index => $slide)
                @include('admin.content.image-form', [
                    'anchor' => 'slide-'.$index,
                    'heading' => 'Slide '.($index + 1),
                    'caption' => $slide['title'] ?? '',
                    'action' => route('admin.content.media.slide', [$entry, $index]),
                    'photo' => $slide,
                    'ratio' => '16 / 7',
                    'showFocalPoint' => true,
                    'showFit' => true,
                    'signature' => \App\Support\StoryMedia::slideSignature($slide),
                ])
            @empty
                <p class="subtle">Add a welcome slide from Edit page to manage its image here.</p>
            @endforelse
        @else
            @include('admin.content.image-form', [
                'anchor' => 'banner',
                'heading' => 'Page banner',
                'caption' => 'Leave the address empty to use the shared inner-page banner.',
                'action' => route('admin.content.media.header', $entry),
                'photo' => ['image' => $entry->header_image],
                'fallback' => $display->get('inner_header_image'),
                'ratio' => '16 / 5',
            ])
        @endif

        <h2 id="section-images">Section images</h2>
        @forelse($images as $key => $image)
            @php($override = ($entry->body_image_overrides ?? [])[$key] ?? [])
            @include('admin.content.image-form', [
                'anchor' => 'image-'.$key,
                'heading' => ($image['section'] !== '' ? $image['section'].' · ' : '').'Image '.($loop->iteration),
                'caption' => $image['alt'],
                'action' => route('admin.content.media.section', [$entry, $key]),
                'photo' => $override,
                'fallback' => $image['source'],
                'ratio' => '3 / 2',
                'showFocalPoint' => true,
                'showFit' => true,
                'canReset' => $override !== [],
            ])
        @empty
            <p class="subtle">This content has no images in its body.</p>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        for (const form of document.querySelectorAll('[data-image-form]')) {
            const preview = form.querySelector('[data-image-preview]');
            const url = form.elements.image;
            const file = form.elements.image_upload;
            let objectUrl;

            const updateSource = () => {
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = undefined;
                }

                if (file.files[0]) {
                    objectUrl = URL.createObjectURL(file.files[0]);
                }

                const address = url.value.trim();
                const validAddress = /^(https?:\/\/|\/(?!\/))/i.test(address) && !/[\s\\]/.test(address);
                const source = objectUrl ?? (validAddress ? address : preview.dataset.fallback);
                preview.hidden = !source;

                if (source) {
                    preview.src = source;
                } else {
                    preview.removeAttribute('src');
                }
            };

            const updateFrame = () => {
                const position = name => Math.min(100, Math.max(0, Number(form.elements[name]?.value || 50)));
                preview.style.objectPosition = `${position('position_x')}% ${position('position_y')}%`;
                preview.style.objectFit = form.elements.fit?.value ?? preview.dataset.fit;
            };

            file.addEventListener('change', updateSource);
            url.addEventListener('change', updateSource);
            form.addEventListener('input', updateFrame);
        }
    </script>
@endpush
