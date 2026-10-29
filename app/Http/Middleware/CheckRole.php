<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = Auth::user();

    if (!$user) {
        abort(403, 'Utilisateur non authentifié');
    }

    // Définir la hiérarchie des rôles
    $roleHierarchy = [
        'author' => 1,
        'admin' => 2,
        'superadmin' => 3,
    ];

    $userRoleLevel = $roleHierarchy[$user->role] ?? 0;

    // Si un des rôles requis a un niveau inférieur ou égal à celui de l'utilisateur
    foreach ($roles as $requiredRole) {
        if ($userRoleLevel >= ($roleHierarchy[$requiredRole] ?? 0)) {
            return $next($request);
        }
    }

    abort(403, 'Accès non autorisé par CEI');
}

}
