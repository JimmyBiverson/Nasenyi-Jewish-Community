@extends('admin.layout')
@section('title', 'Website settings')
@section('main')
    <header class="page-head"><h1>Website settings</h1></header>
    <form class="panel" action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="form-grid">
            @foreach($fields as $key => $field)
                @php
                    $value = old($key, $details[$key] ?? '');
                    $value = is_string($value) ? $value : '';
                    $isImage = isset($imageMimes[$key]);
                @endphp
                <div class="field" @if($isImage) data-branding-image @endif>
                    <label for="{{ $key }}">{{ $field['label'] }}</label>
                    @if($field['type'] === 'select')
                        <select id="{{ $key }}" name="{{ $key }}">
                            @foreach($field['options'] as $value => $label)
                                <option value="{{ $value }}" @selected(old($key, ($details[$key] ?? null) ?: $field['default']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="{{ $key }}" name="{{ $key }}" type="{{ $field['type'] }}" value="{{ $value }}" @if($isImage) data-branding-path placeholder="/media/example.png or https://…" @endif>
                    @endif
                    @if($isImage)
                        @php($preview = \App\Support\ContentHtml::safeUrl($value) ? $value : ($imageDefaults[$key] ?? null))
                        <img
                            class="branding-preview"
                            data-branding-preview
                            data-fallback="{{ $imageDefaults[$key] ?? '' }}"
                            @if($preview) src="{{ $preview }}" @else hidden @endif
                            alt="{{ $field['label'] }} preview"
                            referrerpolicy="no-referrer"
                        >
                        <label for="{{ $key }}_upload">Upload {{ strtolower($field['label']) }}</label>
                        <input id="{{ $key }}_upload" name="{{ $key }}_upload" type="file" accept="{{ $imageMimes[$key] }}" data-branding-upload>
                        <small>
                            {{ match ($key) {
                                'logo' => 'Shown on the website and used as the admin favicon.',
                                'favicon' => 'Shown in public browser tabs. Use a square PNG or ICO.',
                                'social_image' => 'Used when a page or post has no featured image for sharing or for its thumbnail.',
                                default => 'Individual pages can use a different banner in Manage images.',
                            } }}
                            Maximum {{ number_format($imageLimit) }} KB per file.
                        </small>
                    @endif
                </div>
            @endforeach
        </div>
        <button type="submit">Save settings</button>
    </form>
@endsection

@push('scripts')
    <script>
        for (const field of document.querySelectorAll('[data-branding-image]')) {
            const path = field.querySelector('[data-branding-path]');
            const upload = field.querySelector('[data-branding-upload]');
            const preview = field.querySelector('[data-branding-preview]');
            let temporaryUrl;

            function refreshPreview() {
                if (temporaryUrl) {
                    URL.revokeObjectURL(temporaryUrl);
                    temporaryUrl = undefined;
                }

                const address = path.value.trim();
                const allowed = /^(https?:\/\/|\/(?!\/))/i.test(address) && !/[\s\\]/.test(address);
                if (upload.files[0]) {
                    temporaryUrl = URL.createObjectURL(upload.files[0]);
                }
                const source = temporaryUrl ?? (allowed ? address : preview.dataset.fallback);

                preview.hidden = !source;
                if (source) {
                    preview.src = source;
                } else {
                    preview.removeAttribute('src');
                }
            }

            path.addEventListener('change', refreshPreview);
            upload.addEventListener('change', refreshPreview);
        }
    </script>
@endpush
