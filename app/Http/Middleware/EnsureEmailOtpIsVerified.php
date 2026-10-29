<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailOtpIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && is_null(Auth::user()->email_verified_at)) {
            return redirect()->route('otp.form')->withErrors([
                'otp' => 'Vous devez vérifier votre adresse email avant de continuer.'
            ]);
        }

        return $next($request);
    }
}
