<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next): Response
{
    $user = Auth::user();

    $roleHierarchy = [
        'author' => 1,
        'admin' => 2,
        'superadmin' => 3,
    ];

    $userLevel = $roleHierarchy[$user->role] ?? 0;

    // Autorise admin et superadmin
    if ($userLevel >= $roleHierarchy['admin']) {
        return $next($request);
    }

    abort(403, 'Accès non autorisé par CEI');
}

}
