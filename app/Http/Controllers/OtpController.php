<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Mail\EmailVerificationOtp;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UserRequest;


class OtpController extends Controller
{
    public function showForm()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if ($user && $user->email_verification_code == $request->otp) {
            $user->email_verified_at = now();
            $user->email_verification_code = null;
            $user->save();

            return redirect()->route('dashboard')->with('success', 'Email vérifié avec succès !');
        }

        return back()->withErrors(['otp' => 'Code incorrect. Veuillez réessayer.']);
    }


   public function resendOtp()
{
    $user = Auth::user();

    // Générer un nouveau code OTP
    $otp = rand(100000, 999999);
    $user->email_verification_code = $otp;
    $user->save();

    // Envoyer le mail avec OTP et user
    Mail::to($user->email)->send(new EmailVerificationOtp($otp, $user));

    return back()->with('success', 'Un nouveau code a été envoyé à votre adresse email.');
}

}
