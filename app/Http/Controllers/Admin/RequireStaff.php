<?php

namespace App\Http\Controllers\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireStaff
{
    public function handle(Request $request, Closure $next, string $level = 'editor'): Response
    {
        $roles = $level === 'admin' ? ['admin'] : ['admin', 'editor'];

        abort_unless(in_array($request->user()?->role, $roles, true), 403);

        return $next($request);
    }
}
