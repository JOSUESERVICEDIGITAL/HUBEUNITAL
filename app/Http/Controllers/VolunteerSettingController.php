<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VolunteerSetting;


class VolunteerSettingController extends Controller
{ /**
     * Affiche la liste des inscrits au bénévolat.
     */
    public function index()
    {
        $volunteers = VolunteerSetting::all();
        return view('back.benevole.index', compact('volunteers'));
    }

    /**
     * Affiche le formulaire d'inscription au bénévolat (front-office).
     */
    public function create()
    {
        return view('back.benevole.create');
    }

    /**
     * Enregistre un nouvel inscrit à partir du formulaire.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre_principal' => 'nullable|string|max:255',
            'placeholder_nom' => 'required|string|max:255',
            'placeholder_prenom' => 'required|string|max:255',
            'placeholder_ville' => 'nullable|string|max:255',
            'placeholder_telephone' => 'nullable|string|max:20',
            'placeholder_nationalite' => 'nullable|string|max:100',
            'placeholder_annee_arrivee' => 'nullable|digits:4',
            'placeholder_email' => 'required|email|max:255',
            'texte_bouton' => 'nullable|string|max:255',
            'message_confirmation' => 'nullable|string',
        ]);

        VolunteerSetting::create([
    'placeholder_nom' => $request->placeholder_nom,
    'placeholder_prenom' => $request->placeholder_prenom,
    'placeholder_ville' => $request->placeholder_ville,
    'placeholder_telephone' => $request->placeholder_telephone,
    'placeholder_nationalite' => $request->placeholder_nationalite,
    'placeholder_annee_arrivee' => $request->placeholder_annee_arrivee,
    'placeholder_email' => $request->placeholder_email,
    'message_confirmation' => '', // <-- ajouté
]);

        return redirect()->back()->with('success', 'Inscription enregistrée avec succès.');
    }

    
   
    public function destroy($id)
    {
        $volunteer = VolunteerSetting::findOrFail($id);
        $volunteer->delete();

        return redirect()->back()->with('success', 'Inscrit supprimé avec succès.');
    }
}