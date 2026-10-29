<?php

namespace App\Http\Middleware;

use App\Models\Chamber;
use App\Services\RhAuthorization;
use Closure;
use Illuminate\Http\Request;

class RhAccess
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = app(RhAuthorization::class);
        $authorization->refresh();
        abort_unless($request->user()->role === 'superadmin' || $authorization->account($request->user()), 403, 'Un accès RH actif est nécessaire.');
        abort_unless(Chamber::where('slug', 'rh')->firstOrFail()->is_open, 403, 'La Chambre RH est fermée.');
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
