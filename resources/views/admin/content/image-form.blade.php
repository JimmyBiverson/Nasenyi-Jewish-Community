@php
    $showFocalPoint = $showFocalPoint ?? false;
    $showFit = $showFit ?? false;
    $positionX = (int) ($photo['position_x'] ?? 50);
    $positionY = (int) ($photo['position_y'] ?? 50);
    $fit = ($photo['fit'] ?? $defaultFit ?? 'cover') === 'contain' ? 'contain' : 'cover';
    $source = ($photo['image'] ?? null) ?: ($fallback ?? null);
@endphp
<form id="{{ $anchor }}" class="panel image-form" action="{{ $action }}" method="post" enctype="multipart/form-data" data-image-form>
    @csrf
    @method('PATCH')
    @isset($signature)
        <input type="hidden" name="slide_signature" value="{{ $signature }}">
    @endisset
    <div>
        <h3>{{ $heading }}</h3>
        @if(filled($caption ?? null))
            <p class="subtle image-form-caption" dir="auto">{{ $caption }}</p>
        @endif
        <div class="image-form-frame" style="aspect-ratio: {{ $ratio }}">
            <img @if($source) src="{{ $source }}" @else hidden @endif alt="" loading="lazy"
                style="object-position: {{ $positionX }}% {{ $positionY }}%; object-fit: {{ $fit }}"
                data-image-preview data-fallback="{{ $fallback ?? '' }}" data-fit="{{ $fit }}">
        </div>
    </div>
    <div class="image-form-fields">
        <div class="field">
            <label for="{{ $anchor }}-url">Image path or URL</label>
            <input id="{{ $anchor }}-url" name="image" type="text" maxlength="2048"
                value="{{ $photo['image'] ?? '' }}" placeholder="{{ $fallback ?? 'https://' }}">
        </div>
        <div class="field">
            <label for="{{ $anchor }}-file">Upload a replacement</label>
            <input id="{{ $anchor }}-file" name="image_upload" type="file" accept="{{ \App\Support\ImageUpload::accept() }}">
            <small>JPEG, PNG, WebP or AVIF. Maximum {{ number_format(\App\Support\StoryMedia::uploadLimit()) }} KB.</small>
        </div>
        @if($showFocalPoint)
            <div class="form-grid media-focal-point">
                <div class="field">
                    <label for="{{ $anchor }}-x">Horizontal position (%)</label>
                    <input id="{{ $anchor }}-x" name="position_x" type="number" min="0" max="100" value="{{ $positionX }}" required>
                    <small>0 left, 100 right</small>
                </div>
                <div class="field">
                    <label for="{{ $anchor }}-y">Vertical position (%)</label>
                    <input id="{{ $anchor }}-y" name="position_y" type="number" min="0" max="100" value="{{ $positionY }}" required>
                    <small>0 top, 100 bottom</small>
                </div>
            </div>
        @endif
        @if($showFit)
            <div class="field">
                <label for="{{ $anchor }}-fit">Image fit</label>
                <select id="{{ $anchor }}-fit" name="fit">
                    <option value="cover" @selected($fit === 'cover')>Fill frame</option>
                    <option value="contain" @selected($fit === 'contain')>Show whole image</option>
                </select>
            </div>
        @endif
        <div class="actions">
            <button type="submit">Save image</button>
            @if($canReset ?? false)
                <button class="ghost" type="submit" name="reset" value="1">Restore original</button>
            @endif
        </div>
    </div>
</form>
