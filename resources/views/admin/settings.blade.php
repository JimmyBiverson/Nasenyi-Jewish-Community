@extends('admin.layout')
@section('title', 'Website settings')
@section('main')
    <header class="page-head"><h1>Website settings</h1></header>
    <form class="panel" action="{{ route('admin.settings.update') }}" method="post">
        @csrf
        @method('PATCH')
        <div class="form-grid">
            @foreach($fields as $key => $field)
                <div class="field">
                    <label for="{{ $key }}">{{ $field['label'] }}</label>
                    <input id="{{ $key }}" name="{{ $key }}" type="{{ $field['type'] }}" value="{{ old($key, $details->get($key)) }}">
                </div>
            @endforeach
        </div>
        <button type="submit">Save settings</button>
    </form>
@endsection
