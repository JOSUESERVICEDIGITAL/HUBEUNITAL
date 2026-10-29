<?php

namespace App\Http\Middleware;

use App\Models\Chamber;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraphismeAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $privileged = in_array($user->role, ['admin', 'superadmin'], true);
        $member = DB::table('gfx_team')->where('user_id', $user->id)->where('status', 'active')->exists();
        abort_unless($privileged || $member, 403, 'Votre compte ne fait pas partie de la chambre Graphisme.');
        $chamber = Chamber::where('slug', 'graphisme')->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre Graphisme est fermée. Contactez le superadmin.');

        return $next($request);
    }
}
