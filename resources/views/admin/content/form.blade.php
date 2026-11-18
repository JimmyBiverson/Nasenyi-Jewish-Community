@extends('admin.layout')
@section('title', $entry->exists ? 'Edit '.$entry->type : 'New '.$entry->type)
@section('main')
    <a class="breadcrumbs" href="{{ route('admin.content.index', ['type' => $entry->type]) }}">← {{ $entry->type === 'post' ? 'Posts' : 'Pages' }}</a>
    <header class="page-head">
        <h1>{{ $entry->exists ? 'Edit '.$entry->type : 'New '.$entry->type }}</h1>
        @if($entry->exists)
            <div class="actions">
                <a class="button" href="{{ route('admin.content.media.edit', $entry) }}">Manage images</a>
                @if($entry->status === 'published')
                    <a class="button ghost" href="{{ url($entry->slug === '' ? '/' : '/'.$entry->slug.'/') }}">View published {{ $entry->type }}</a>
                @endif
            </div>
        @endif
    </header>
    <form id="content-form" class="panel" action="{{ $entry->exists ? route('admin.content.update', $entry) : route('admin.content.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @if($entry->exists)
            @method('PATCH')
        @endif
        <input type="hidden" name="type" value="{{ $entry->type }}">
        <div class="form-grid">
            <div class="field full">
                <label for="title">Title</label>
                <input id="title" name="title" value="{{ old('title', $entry->title) }}" required maxlength="255">
            </div>
            <div class="field">
                <label for="slug">Address</label>
                <input id="slug" name="slug" value="{{ old('slug', $entry->slug) }}" maxlength="191" @readonly($entry->exists && $entry->slug === '')>
                <small>{{ $entry->exists && $entry->slug === '' ? 'The homepage uses the root address.' : 'Use lowercase letters, numbers or hyphens. Keep existing addresses when editing.' }}</small>
            </div>
            <div class="field">
                <label for="entry-status">Publishing status</label>
                <select id="entry-status" name="status">
                    @if(! $entry->exists || $entry->slug !== '')
                        <option value="draft" @selected(old('status', $entry->status) === 'draft')>Draft</option>
                    @endif
                    <option value="published" @selected(old('status', $entry->status) === 'published')>Published</option>
                </select>
            </div>
            @if($entry->exists && $entry->slug === '')
                @php($slides = array_values(array_filter((array) old('hero_slides', $entry->hero_slides ?? []), 'is_array')))
                <details class="full">
                    <summary>Homepage welcome slides</summary>
                    @foreach(array_slice(array_merge($slides, [[]]), 0, 8) as $index => $slide)
                        <fieldset>
                            <legend>Slide {{ $index + 1 }}</legend>
                            <div class="form-grid">
                                @foreach(['title' => 'Title', 'description' => 'Description', 'image' => 'Image path or URL', 'label' => 'Link label', 'href' => 'Link destination'] as $key => $label)
                                    <div class="field">
                                        <label for="slide-{{ $index }}-{{ $key }}">{{ $label }}</label>
                                        <input id="slide-{{ $index }}-{{ $key }}" name="hero_slides[{{ $index }}][{{ $key }}]" value="{{ $slide[$key] ?? '' }}" type="text">
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                    <p class="subtle">Leave all fields in a row blank to remove that slide.</p>
                </details>
            @endif
            <div class="field full">
                <label for="summary">Summary</label>
                <textarea id="summary" name="summary" rows="3" maxlength="1000">{{ old('summary', $entry->summary) }}</textarea>
            </div>
            <div class="field full">
                <label id="body-label" for="body">Page content</label>
                <div id="rich-editor" hidden>
                    <div class="toolbar" aria-label="Text formatting">
                        <button class="ghost" type="button" data-format="bold">Bold</button>
                        <button class="ghost" type="button" data-format="italic">Italic</button>
                        <button class="ghost" type="button" data-format="formatBlock" data-value="h2">Heading</button>
                        <button class="ghost" type="button" data-format="insertUnorderedList">List</button>
                        <button class="ghost" type="button" id="make-link">Link</button>
                        <button class="ghost" type="button" id="toggle-source" aria-controls="editor-source" aria-expanded="false">Edit HTML</button>
                    </div>
                    <div id="editor-content" class="editor-content" contenteditable="true" role="textbox" aria-labelledby="body-label" aria-multiline="true"></div>
                </div>
                <div id="editor-source">
                    @php($oldBody = old('body', $entry->body ?? ''))
                    <textarea id="body" class="textarea-html" name="body" rows="14">{{ \App\Support\ContentHtml::sanitize(is_string($oldBody) ? $oldBody : '') }}</textarea>
                </div>
            </div>
            <div class="field">
                <label for="image">Featured image path or URL</label>
                <input id="image" name="image" value="{{ old('image', $entry->image) }}">
                @if($entry->image)
                    <img class="preview-image" src="{{ $entry->image }}" alt="Current featured image">
                @endif
            </div>
            <div class="field">
                <label for="image-upload">Upload a featured image</label>
                <input id="image-upload" name="image_upload" type="file" accept="image/jpeg,image/png,image/webp,image/avif">
                <small>Up to {{ \Illuminate\Support\Number::fileSize(\App\Support\StoryMedia::uploadLimit() * 1024, precision: 1) }}. An upload replaces the image path above.</small>
            </div>
            @if($entry->type === 'post')
                <div class="field">
                    <label for="category">Category</label>
                    <input id="category" name="category" value="{{ old('category', $entry->category) }}">
                </div>
                <div class="field">
                    <label for="tags">Tags</label>
                    <input id="tags" name="tags" value="{{ old('tags', implode(', ', $entry->tags ?? [])) }}">
                    <small>Separate tags with commas.</small>
                </div>
            @endif
            <div class="field">
                <label for="seo-title">Search title</label>
                <input id="seo-title" name="seo_title" value="{{ old('seo_title', $entry->seo_title) }}" maxlength="255">
            </div>
            <div class="field">
                <label for="social-image">Social sharing image path or URL</label>
                <input id="social-image" name="social_image" value="{{ old('social_image', $entry->social_image) }}">
            </div>
            <div class="field full">
                <label for="seo-description">Search description</label>
                <textarea id="seo-description" name="seo_description" rows="3" maxlength="600">{{ old('seo_description', $entry->seo_description) }}</textarea>
            </div>
        </div>
        <div class="actions">
            <button type="submit">{{ $entry->exists ? 'Save changes' : 'Create '.$entry->type }}</button>
            @if($entry->exists && $entry->slug !== '')
                <button form="delete-content" class="danger" type="submit">Delete {{ $entry->type }}</button>
            @endif
        </div>
    </form>
    @if($entry->exists && $entry->slug !== '')
        <form id="delete-content" action="{{ route('admin.content.destroy', $entry) }}" method="post" onsubmit="return confirm('Delete this content?')">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
@push('scripts')
    @include('admin.content.editor')
@endpush
