<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Newsletter;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterWelcomeMail;

class NewsletterController extends Controller
{
   public function subscribe(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:newsletters,email',
    ]);

    $subscriber = Newsletter::create(['email' => $request->email]);

    // Envoi de l'email de bienvenue
    Mail::to($request->email)->send(new NewsletterWelcomeMail($request->email));


    return back()->with('success', 'Merci pour votre inscription !');
}



public function index()
{
    $subscribers = \App\Models\Newsletter::latest()->paginate(10);
    return view('back.newsletters.index', compact('subscribers'));
}

public function destroy(Newsletter $newsletter)
{
    $newsletter->delete();
    return redirect()->route('newsletter.index')->with('success', 'Email supprimé avec succès.');
}

}