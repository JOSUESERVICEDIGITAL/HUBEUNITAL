@extends('auth.auth-layout')
@section('title', 'Vérification Email')

@section('auth-form')
<h1>Vérification d'Email</h1>
<p>Un code à 6 chiffres a été envoyé sur votre adresse email.</p>

<form method="POST" action="{{ route('otp.verify') }}">
    @csrf
    <div class="form-group">
        <input type="text" name="otp" class="form-control" placeholder="Entrez le code" maxlength="6" required>
        @error('otp')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary btn-block">Vérifier</button>
    <div class="text-center mt-3">
        Vous n'avez pas reçu le code ? <a href="{{ route('otp.resend') }}">Renvoyer le code</a>
    </div>
</form>
@endsection
    