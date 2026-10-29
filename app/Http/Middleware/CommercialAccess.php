<?php

namespace App\Http\Middleware;

use App\Models\Chamber;
use App\Services\CommercialAuthorization;
use Closure;
use Illuminate\Http\Request;

class CommercialAccess
{
    public function handle(Request $request, Closure $next)
    {
        $access = app(CommercialAuthorization::class);
        $access->refresh();
        abort_unless($request->user()->role === 'superadmin' || $access->account($request->user()), 403, 'Un accès actif est nécessaire.');
        abort_unless(Chamber::where('slug', $access->chamber($request->user()))->firstOrFail()->is_open, 403, 'La chambre de votre espace est fermée.');
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
