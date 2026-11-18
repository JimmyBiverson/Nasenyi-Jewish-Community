@extends('admin.layout')
@section('title', 'Contact message')
@section('main')
    <a class="breadcrumbs" href="{{ route('admin.inbox.index') }}">← Contact inbox</a>
    <header class="page-head"><h1>Message from {{ $message->name }}</h1></header>
    <article class="panel">
        <dl>
            <dt>Email address</dt><dd><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd>
            <dt>Received</dt><dd>{{ $message->created_at?->format('j M Y, H:i') }}</dd>
            <dt>Status</dt><dd>{{ $message->read_at ? 'Read' : 'Unread' }}</dd>
        </dl>
        <h2>Message</h2>
        <p class="body-text">{{ $message->message }}</p>
        <div class="actions">
            <form action="{{ route('admin.inbox.update', $message) }}" method="post">
                @csrf
                @method('PATCH')
                <button type="submit" class="ghost">Mark {{ $message->read_at ? 'unread' : 'read' }}</button>
            </form>
            <form action="{{ route('admin.inbox.destroy', $message) }}" method="post" onsubmit="return confirm('Delete this message?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger">Delete message</button>
            </form>
        </div>
    </article>
@endsection
