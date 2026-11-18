<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Sign in · Nasenyi Administration</title>
    <link rel="icon" href="{{ \App\Support\SiteBranding::image('logo') }}">
    @include('admin.styles')
</head>
<body class="login-wrap">
<main class="login">
    <h1>Admin sign in</h1>
    <p class="subtle">Nasenyi Abayudaya Community</p>
    @include('admin.flash')
    <form action="{{ route('admin.login.store') }}" method="post">
        @csrf
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <div class="actions spread">
            <button type="submit">Sign in</button>
            <a href="{{ url('/') }}">Back to website</a>
        </div>
    </form>
</main>
</body>
</html>
