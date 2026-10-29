<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OnlySuperAdmin
{
    public function handle($request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'superadmin') {
            return $next($request);
        }

        abort(403, 'Seul le Super Admin peut modifier les rôles.');
    }
}
