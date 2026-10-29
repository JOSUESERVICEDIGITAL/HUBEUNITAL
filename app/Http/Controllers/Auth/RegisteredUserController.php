<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\EmailVerificationOtp;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
   public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Création utilisateur
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // Générer OTP 6 chiffres
    $otp = rand(100000, 999999);
    $user->email_verification_code = $otp;
    $user->email_verified_at = null; // S’assure que non vérifié
    $user->save();

    // Envoyer mail OTP (crée un Mailable EmailVerificationOtp)
        \Mail::to($user->email)->send(new \App\Mail\EmailVerificationOtp($otp, $user));

    // Ne pas connecter l'utilisateur directement
    // Rediriger vers formulaire de vérification OTP (créé une route et vue)
    return redirect()->route('otp.form')->with('email', $user->email);
}

}
