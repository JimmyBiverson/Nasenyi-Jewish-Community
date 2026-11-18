@extends('admin.layout')
@section('title', 'Overview')
@section('main')
    <header class="page-head">
        <h1>Overview</h1>
        <a class="button" href="{{ route('admin.content.create', ['type' => 'post']) }}">New post</a>
    </header>
    <div class="metric-grid">
        @foreach($counts as $label => $count)
            <div class="metric"><strong>{{ number_format($count) }}</strong> <span>{{ $label }}</span></div>
        @endforeach
    </div>
    <section class="panel">
        <h2>Recently edited</h2>
        @if($recent->isEmpty())
            <p class="subtle">There is no content yet.</p>
        @else
            <div class="table-scroll">
                <table>
                    <thead><tr><th scope="col">Title</th><th scope="col">Type</th><th scope="col">Status</th><th scope="col">Updated</th></tr></thead>
                    <tbody>
                    @foreach($recent as $entry)
                        <tr>
                            <td><a href="{{ route('admin.content.edit', $entry) }}">{{ $entry->title }}</a></td>
                            <td>{{ ucfirst($entry->type) }}</td>
                            <td><span class="pill">{{ ucfirst($entry->status) }}</span></td>
                            <td>{{ $entry->updated_at?->format('j M Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
