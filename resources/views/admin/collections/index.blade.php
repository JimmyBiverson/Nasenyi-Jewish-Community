@extends('admin.layout')
@section('title', $name)
@section('main')
    <header class="page-head">
        <h1>{{ $name }}</h1>
        <a class="button" href="{{ route('admin.collections.create', $collection) }}">Add {{ ['gallery' => 'image', 'events' => 'event', 'causes' => 'cause'][$collection] }}</a>
    </header>
    <section class="panel">
        @if($records->isEmpty())
            <p class="subtle">No records yet.</p>
        @else
            <div class="table-scroll"><table>
                <thead><tr><th scope="col">Title</th><th scope="col">Status</th><th scope="col">{{ $collection === 'events' ? 'Starts (EAT)' : 'Updated' }}</th></tr></thead>
                <tbody>
                @foreach($records as $record)
                    <tr>
                        <td><a href="{{ route('admin.collections.edit', ['collection' => $collection, 'record' => $record->getKey()]) }}">{{ $record->title }}</a></td>
                        <td><span class="pill">{{ $collection === 'causes' ? ($record->is_active ? 'Active' : 'Archived') : ($record->is_published ? 'Published' : 'Hidden') }}</span></td>
                        <td>{{ $collection === 'events' ? $record->starts_at?->copy()->setTimezone('Africa/Kampala')->format('j M Y, H:i') : $record->updated_at?->format('j M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            @include('admin.pagination', ['results' => $records])
        @endif
    </section>
@endsection
