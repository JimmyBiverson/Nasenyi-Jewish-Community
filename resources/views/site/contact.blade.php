<x-site-layout
    :title="$entry?->seo_title ?: ($entry?->title ?: 'Contact Us')"
    :description="$entry?->seo_description ?: $entry?->summary"
    :image="$entry?->social_image ?: $entry?->image"
>
    @include('site.partials.page-header', ['heading' => $entry?->title ?: 'Contact Us', 'image' => '/media/headers/contact.webp', 'override' => $entry?->header_image])
    <div class="wrapper contact-layout" data-reveal>
        @if(filled($entry?->body))
            <div class="prose contact-info">{!! \App\Support\StoryLayout::render($entry) !!}</div>
        @endif
        <form class="contact-form" action="{{ route('site.contact.store') }}" method="post">
            @csrf
            <h2>Send a message</h2>
            @if(session('contact_status'))
                <p class="form-notice" role="status">{{ session('contact_status') }}</p>
            @endif
            @if($errors->any())
                <div class="form-error" role="alert">
                    <p>Please check these fields.</p>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="field-grid">
                <label class="field">First name <input name="first_name" autocomplete="given-name" maxlength="100" required value="{{ old('first_name') }}"></label>
                <label class="field">Last name <input name="last_name" autocomplete="family-name" maxlength="100" required value="{{ old('last_name') }}"></label>
            </div>
            <label class="field">Email <input name="email" type="email" autocomplete="email" maxlength="254" required value="{{ old('email') }}"></label>
            <label class="field">Message <textarea name="message" rows="6" maxlength="5000" required>{{ old('message') }}</textarea></label>
            <div hidden><label>Leave this field empty <input name="website" tabindex="-1" autocomplete="off"></label></div>
            <button class="button button-gold" type="submit">Send message <span aria-hidden="true">→</span></button>
        </form>
    </div>
</x-site-layout>
