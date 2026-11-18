@extends('admin.layout')
@section('title', ($record->exists ? 'Edit ' : 'Add ').$singular)
@section('main')
    <a class="breadcrumbs" href="{{ route('admin.collections.index', $collection) }}">← {{ $name }}</a>
    <header class="page-head"><h1>{{ $record->exists ? 'Edit ' : 'Add ' }}{{ $singular }}</h1></header>
    <form class="panel" action="{{ $record->exists ? route('admin.collections.update', ['collection' => $collection, 'record' => $record->getKey()]) : route('admin.collections.store', $collection) }}" method="post" enctype="multipart/form-data">
        @csrf
        @if($record->exists)
            @method('PATCH')
        @endif
        <div class="form-grid">
            <div class="field {{ $collection === 'gallery' ? 'full' : '' }}">
                <label for="title">Title</label>
                <input id="title" name="title" value="{{ old('title', $record->title) }}" maxlength="255" required>
            </div>
            @if($collection === 'gallery')
                @if($record->path)
                    <div class="field full"><img class="preview-image" src="{{ $record->path }}" alt="{{ $record->alt_text }}"></div>
                @endif
                <div class="field">
                    <label for="image-upload">{{ $record->exists ? 'Replace image' : 'Image' }}</label>
                    <input id="image-upload" name="image_upload" type="file" accept="image/jpeg,image/png,image/webp,image/avif" @required(! $record->exists)>
                </div>
                <div class="field">
                    <label for="alt-text">Alternative text</label>
                    <input id="alt-text" name="alt_text" value="{{ old('alt_text', $record->alt_text) }}" maxlength="255" required>
                </div>
                <div class="field full">
                    <label for="caption">Caption</label>
                    <textarea id="caption" name="caption" rows="3" maxlength="1000">{{ old('caption', $record->caption) }}</textarea>
                </div>
                <div class="field">
                    <label for="sort-order">Display order</label>
                    <input id="sort-order" name="sort_order" type="number" min="0" max="99999" value="{{ old('sort_order', $record->sort_order ?? 0) }}" required>
                </div>
            @else
                <div class="field">
                    <label for="slug">Address</label>
                    <input id="slug" name="slug" value="{{ old('slug', $record->slug) }}" maxlength="191" required>
                </div>
                <div class="field full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="8" maxlength="10000" @required($collection === 'events')>{{ old('description', $record->description) }}</textarea>
                </div>
            @endif
            @if($collection === 'events')
                <div class="field full">
                    <label for="location">Location</label>
                    <input id="location" name="location" value="{{ old('location', $record->location) }}" maxlength="255">
                </div>
                <div class="field">
                    <label for="starts-at">Starts (East Africa Time)</label>
                    <input id="starts-at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $record->starts_at?->copy()->setTimezone('Africa/Kampala')->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="field">
                    <label for="ends-at">Ends (East Africa Time)</label>
                    <input id="ends-at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $record->ends_at?->copy()->setTimezone('Africa/Kampala')->format('Y-m-d\TH:i')) }}">
                </div>
            @endif
            @php($flag = $collection === 'causes' ? 'is_active' : 'is_published')
            <label class="label full">
                <input type="hidden" name="{{ $flag }}" value="0">
                <input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $record->{$flag} ?? false))>
                {{ $collection === 'causes' ? 'Available for donations' : 'Published' }}
            </label>
        </div>
        <div class="actions" style="margin-top:1rem">
            <button type="submit">{{ $record->exists ? 'Save changes' : 'Add '.$singular }}</button>
            @if($record->exists)
                <button type="submit" class="danger" form="delete-record">{{ $collection === 'causes' ? 'Archive cause' : 'Delete '.$singular }}</button>
            @endif
        </div>
    </form>
    @if($record->exists)
        <form id="delete-record" action="{{ route('admin.collections.destroy', ['collection' => $collection, 'record' => $record->getKey()]) }}" method="post" onsubmit="return confirm('Remove this item?')">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
