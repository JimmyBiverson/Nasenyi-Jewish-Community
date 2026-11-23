@php($logo = \App\Support\SiteBranding::image('logo'))
@php($favicon = \App\Support\SiteBranding::image('favicon'))
@php($faviconType = \App\Support\ImageUpload::mime($favicon))
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · Nasenyi Administration</title>
    <link rel="icon" href="{{ $favicon }}" type="{{ $faviconType }}">
    @include('admin.styles')
</head>
<body>
<a class="skip" href="#main-content">Skip to content</a>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ $logo }}" alt="" width="36" height="36">
            <span>Nasenyi <small>Administration</small></span>
        </a>
        <nav aria-label="Administration">
            <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Overview</a>
            <a href="{{ route('admin.content.index', ['type' => 'page']) }}" @if(request()->routeIs('admin.content.*') && (request()->query('type', $entry->type ?? 'page') === 'page')) aria-current="page" @endif>Pages</a>
            <a href="{{ route('admin.content.index', ['type' => 'post']) }}" @if(request()->routeIs('admin.content.*') && (request()->query('type', $entry->type ?? 'page') === 'post')) aria-current="page" @endif>Posts</a>
            @foreach(['gallery' => 'Gallery', 'events' => 'Events', 'causes' => 'Donation causes'] as $section => $label)
                <a href="{{ route('admin.collections.index', $section) }}" @if(request()->route('collection') === $section) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.donations.index') }}" @if(request()->routeIs('admin.donations.*')) aria-current="page" @endif>Donations</a>
                <a href="{{ route('admin.inbox.index') }}" @if(request()->routeIs('admin.inbox.*')) aria-current="page" @endif>Inbox</a>
                <a href="{{ route('admin.settings.edit') }}" @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif>Settings</a>
                <a href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>Accounts</a>
            @endif
        </nav>
    </aside>
    <div class="main">
        <header class="topbar">
            <a href="{{ url('/') }}">View website</a>
            <span class="person">{{ auth()->user()->name }}</span>
            <form action="{{ route('admin.logout') }}" method="post">
                @csrf
                <button class="ghost" type="submit">Sign out</button>
            </form>
        </header>
        <main id="main-content" class="wrap">
            @include('admin.flash')
            @yield('main')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
