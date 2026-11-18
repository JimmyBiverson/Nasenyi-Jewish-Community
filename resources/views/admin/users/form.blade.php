@extends('admin.layout')
@section('title', $user->exists ? 'Edit account' : 'New account')
@section('main')
    <a class="breadcrumbs" href="{{ route('admin.users.index') }}">← Accounts</a>
    <header class="page-head"><h1>{{ $user->exists ? 'Edit account' : 'New account' }}</h1></header>
    <form id="account-form" class="panel" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="post">
        @csrf
        @if($user->exists)
            @method('PATCH')
        @endif
        <div class="form-grid">
            <div class="field">
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="off" required>
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="off" required>
            </div>
            <div class="field full">
                <label for="role">Role</label>
                @if($user->is(auth()->user()))
                    <input id="role" value="Administrator" readonly>
                    <input type="hidden" name="role" value="admin">
                @else
                    <select id="role" name="role">
                        @foreach(['admin' => 'Administrator', 'editor' => 'Editor', 'member' => 'Member'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small>Editors manage content, events, gallery and causes. Administrators also manage donations, inbox, settings and accounts. Members have no dashboard access.</small>
                @endif
            </div>
            <div class="field">
                <label for="password">{{ $user->exists ? 'New password' : 'Password' }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" @required(! $user->exists)>
                @if($user->exists)
                    <small>Leave blank to keep the current password.</small>
                @endif
            </div>
            <div class="field">
                <label for="password-confirmation">Confirm password</label>
                <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="72" @required(! $user->exists)>
            </div>
        </div>
        <div class="actions">
            <button type="submit">{{ $user->exists ? 'Save account' : 'Create account' }}</button>
            @if($user->exists && ! $user->is(auth()->user()))
                <button type="submit" form="delete-account" class="danger">Delete account</button>
            @endif
        </div>
    </form>
    @if($user->exists && ! $user->is(auth()->user()))
        <form id="delete-account" action="{{ route('admin.users.destroy', $user) }}" method="post" onsubmit="return confirm('Delete this account?')">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
