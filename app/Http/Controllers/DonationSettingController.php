<?php

namespace App\Http\Controllers;

use App\Models\DonationSetting;
use Illuminate\Http\Request;

class DonationSettingController extends Controller
{
    /**
     * Affiche le formulaire pour créer ou modifier la configuration des dons
     */
    public function create()
{
    $donationSetting = DonationSetting::first();
    return view('back.don.create', compact('donationSetting'));
}


    /**
     * Stocke ou met à jour les paramètres de don
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'nullable|string|max:255',
            'texte_introduction' => 'nullable|string',
            'nom_banque' => 'nullable|string|max:255',
            'rib' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'swift' => 'nullable|string|max:255',
            'informations_complementaires' => 'nullable|string',
            'instructions_apres_don' => 'nullable|string',
        ]);

        // On met à jour si une config existe, sinon on crée
        $setting = DonationSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            DonationSetting::create($data);
        }

        return redirect()->back()->with('success', 'Les informations ont été enregistrées.');
    }
    public function update(Request $request, DonationSetting $donationSetting)
{
    $data = $request->validate([
        'titre' => 'nullable|string|max:255',
        'texte_introduction' => 'nullable|string',
        'nom_banque' => 'nullable|string|max:255',
        'rib' => 'nullable|string|max:255',
        'iban' => 'nullable|string|max:255',
        'swift' => 'nullable|string|max:255',
        'informations_complementaires' => 'nullable|string',
        'instructions_apres_don' => 'nullable|string',
    ]);

    $donationSetting->update($data);

    return redirect()->back()->with('success', 'Les informations ont été mises à jour.');
}

}
