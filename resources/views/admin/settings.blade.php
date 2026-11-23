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
                        <input id="{{ $key }}_upload" name="{{ $key }}_upload" type="file" accept="{{ $imageAccept }}" data-branding-upload>
                        <small>
                            {{ match ($key) {
                                'logo' => 'Shown on the website and in link previews that use the site logo.',
                                'favicon' => 'Shown in browser tabs. JPEG, PNG, WebP, AVIF, GIF, BMP, and ICO are supported.',
                                'social_image' => 'Used as the image in shared links when a page or post has no featured image.',
                                'inner_header_image' => 'Fallback image for pages without their own banner. Individual pages can still use a different banner in Manage images.',
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
    <form class="panel" action="{{ route('admin.settings.payments') }}" method="post" autocomplete="off">
        @csrf
        <h2>Pesapal donations</h2>
        <p>Environment: {{ ucfirst($payments['environment']) }}. {{ $payments['ready'] ? 'Checkout is enabled.' : 'Checkout is disabled.' }}</p>
        <p>{{ $payments['credentials_saved'] ? 'Credentials are configured. Leave both fields blank to keep them.' : 'Enter both credentials for the environment shown above.' }}</p>
        <div class="form-grid">
            <div class="field"><label for="pesapal-key">Consumer key</label><input id="pesapal-key" name="pesapal_key" type="password" maxlength="255" autocomplete="new-password"></div>
            <div class="field"><label for="pesapal-secret">Consumer secret</label><input id="pesapal-secret" name="pesapal_secret" type="password" maxlength="255" autocomplete="new-password"></div>
            <div class="field"><label for="pesapal-ipn">Notification ID (IPN)</label><input id="pesapal-ipn" name="pesapal_ipn" value="{{ old('pesapal_ipn', $payments['notification_id']) }}" maxlength="36"><small>Register {{ route('donations.ipn') }} as a GET notification for this merchant and environment.</small></div>
            <div class="field"><label for="payments-password">Confirm your admin password</label><input id="payments-password" name="current_password" type="password" autocomplete="current-password" required></div>
        </div>
        <label><input type="checkbox" name="pesapal_enable" value="1" @checked($payments['enabled'])> Enable donation checkout</label>
        <div class="actions">
            <button type="submit">Save Pesapal settings</button>
            <button type="submit" class="ghost" name="pesapal_register" value="1">Save and register notification URL</button>
        </div>
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
