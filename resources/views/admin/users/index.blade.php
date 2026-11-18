@extends('admin.layout')
@section('title', 'Accounts')
@section('main')
    <header class="page-head">
        <h1>Accounts</h1>
        <a class="button" href="{{ route('admin.users.create') }}">New account</a>
    </header>
    <section class="panel">
        <div class="table-scroll"><table>
            <thead><tr><th scope="col">Name</th><th scope="col">Email address</th><th scope="col">Role</th></tr></thead>
            <tbody>
            @foreach($users as $account)
                <tr>
                    <td><a href="{{ route('admin.users.edit', $account) }}">{{ $account->name }}</a></td>
                    <td>{{ $account->email }}</td>
                    <td><span class="pill">{{ ucfirst($account->role ?? 'member') }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        @include('admin.pagination', ['results' => $users])
    </section>
@endsection
