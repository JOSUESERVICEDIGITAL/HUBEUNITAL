<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Mail\ContactConfirmationMail;
use Illuminate\Support\Facades\Mail;
class FrontContactController extends Controller
{
    //
    public function index()
    {
        return view('fronto.contact');
    }
   public function contact(StoreContactRequest $request)
{
    $data = $request->validated();

    Contact::create($data);

    // 🔥 Envoie du mail à l'adresse email du visiteur
    Mail::to($data['email'])->send(new ContactConfirmationMail($data['name']));

    return redirect()->route('front.contact')
        ->with('success', 'Nous avons bien reçu votre message, nous vous répondrons dans les plus brefs délais.');
}
}

