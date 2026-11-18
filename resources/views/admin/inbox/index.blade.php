@extends('admin.layout')
@section('title', 'Inbox')
@section('main')
    <header class="page-head"><h1>Contact inbox</h1></header>
    <section class="panel">
        @if($messages->isEmpty())
            <p class="subtle">No contact messages yet.</p>
        @else
            <div class="table-scroll"><table>
                <thead><tr><th scope="col">Name</th><th scope="col">Email address</th><th scope="col">Received</th><th scope="col">Status</th></tr></thead>
                <tbody>
                @foreach($messages as $message)
                    <tr>
                        <td><a href="{{ route('admin.inbox.show', $message) }}">{{ $message->name }}</a></td>
                        <td>{{ $message->email }}</td>
                        <td>{{ $message->created_at?->format('j M Y') }}</td>
                        <td><span class="pill">{{ $message->read_at ? 'Read' : 'Unread' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            @include('admin.pagination', ['results' => $messages])
        @endif
    </section>
@endsection
