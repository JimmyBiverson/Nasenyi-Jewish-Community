@extends('admin.layout')
@section('title', $type === 'post' ? 'Posts' : 'Pages')
@section('main')
    <header class="page-head">
        <h1>{{ $type === 'post' ? 'Posts' : 'Pages' }}</h1>
        <a class="button" href="{{ route('admin.content.create', ['type' => $type]) }}">New {{ $type }}</a>
    </header>
    <section class="panel">
        <form class="toolbar" action="{{ route('admin.content.index') }}" method="get" role="search">
            <input type="hidden" name="type" value="{{ $type }}">
            <label class="label" for="content-search">Title</label>
            <div><input id="content-search" name="q" type="search" value="{{ $search }}"></div>
            <button class="ghost" type="submit">Search</button>
        </form>
        @if($entries->isEmpty())
            <p class="subtle">No {{ $type === 'post' ? 'posts' : 'pages' }} found.</p>
        @else
            <div class="table-scroll"><table>
                <thead><tr><th scope="col">Title</th><th scope="col">Address</th><th scope="col">Status</th><th scope="col">Updated</th></tr></thead>
                <tbody>
                @foreach($entries as $record)
                    <tr>
                        <td><a href="{{ route('admin.content.edit', $record) }}">{{ $record->title }}</a></td>
                        <td>{{ $record->slug === '' ? '/' : '/'.$record->slug.'/' }}</td>
                        <td><span class="pill">{{ ucfirst($record->status) }}</span></td>
                        <td>{{ $record->updated_at?->format('j M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            @include('admin.pagination', ['results' => $entries])
        @endif
    </section>
@endsection
