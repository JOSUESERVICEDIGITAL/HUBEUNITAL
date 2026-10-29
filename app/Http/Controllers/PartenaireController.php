<?php

namespace App\Http\Controllers;

use App\Models\Partenaire;
use Illuminate\Http\Request;

class PartenaireController extends Controller
{
    public function index()
    {
        // Affiche tous les partenaires
        $partenaires = Partenaire::all();
        return view('back.partenaires.index', compact('partenaires'));
    }

    public function create()
    {
        // Affiche le formulaire pour ajouter un nouveau partenaire
        return view('back.partenaires.create');
    }

   public function store(Request $request)
{
    // Valider les données
    $request->validate([
        'nom' => 'required|string|max:255',
        'site_web' => 'nullable|url',
        'logo' => 'nullable|image',
        'description' => 'nullable|string',
    ]);

    // Gérer l'upload du logo
    $logoPath = null;
    if ($request->hasFile('logo')) {
        // Récupérer le fichier
        $logo = $request->file('logo');
        
        // Déplacer le fichier dans le dossier public et obtenir son chemin
        $logoPath = $logo->store('logos', 'public'); // 'logos' est le sous-dossier où l'image sera stockée
    }

    // Créer un nouveau partenaire
    Partenaire::create([
        'nom' => $request->nom,
        'site_web' => $request->site_web,
        'logo' => $logoPath, // Enregistrer le chemin relatif du logo
        'description' => $request->description,
    ]);

    // Rediriger après la création
    return redirect()->route('partenaires.index')->with('success', 'Partenaire ajouté avec succès.');
}


  

    public function edit(Partenaire $partenaire)
    {
        // Affiche le formulaire pour modifier un partenaire
        return view('back.partenaires.create', compact('partenaire'));
    }

    public function update(Request $request, Partenaire $partenaire)
{
    // Valider les données
    $request->validate([
        'nom' => 'required|string|max:255',
        'site_web' => 'nullable|url',
        'logo' => 'nullable|image',
        'description' => 'nullable|string',
    ]);

    // Gérer l'upload du logo si un fichier est fourni
    if ($request->hasFile('logo')) {
        // Supprimer l'ancien logo s'il existe
        if ($partenaire->logo && file_exists(storage_path('app/public/' . $partenaire->logo))) {
            unlink(storage_path('app/public/' . $partenaire->logo));
        }

        // Récupérer le fichier du logo
        $logo = $request->file('logo');

        // Déplacer le fichier dans le dossier public et obtenir son chemin
        $logoPath = $logo->store('logos', 'public'); // 'logos' est le sous-dossier où l'image sera stockée
    } else {
        // Si aucun logo n'est téléchargé, garder le logo actuel
        $logoPath = $partenaire->logo;
    }

    // Mettre à jour le partenaire
    $partenaire->update([
        'nom' => $request->nom,
        'site_web' => $request->site_web,
        'logo' => $logoPath, // Enregistrer le chemin du logo mis à jour
        'description' => $request->description,
    ]);

    // Rediriger après la mise à jour
    return redirect()->route('partenaires.index')->with('success', 'Partenaire mis à jour avec succès.');
}


    public function destroy(Partenaire $partenaire)
    {
        // Supprimer le partenaire
        $partenaire->delete();

        // Rediriger après la suppression
        return redirect()->route('partenaires.index')->with('success', 'Partenaire supprimé avec succès.');
    }
}
