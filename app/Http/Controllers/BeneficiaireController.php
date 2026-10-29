<?php

namespace App\Http\Controllers;

use App\Models\Beneficiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class BeneficiaireController extends Controller
{
   public function index()
{
    $beneficiaires = Beneficiaire::with('user')->latest()->paginate(10);
    return view('back.beneficiaires.index', compact('beneficiaires'));
}
    public function create()
    {
        return view('back.beneficiaires.create');
    }

    public function store(Request $request)
    {
       $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'ville' => 'nullable|string|max:100',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:beneficiaires,email',
            'identifiant_national' => 'required|string|unique:beneficiaires,identifiant_national',
            'photo' => 'nullable|image|max:2048',
        ]);


        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('beneficiaires', 'public');
        }

        Beneficiaire::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'ville' => $request->ville,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'identifiant_national' => $request->identifiant_national,
            'photo' => $photoPath,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('beneficiaires.index')->with('success', 'Bénéficiaire ajouté avec succès.');
    }

    public function show(Beneficiaire $beneficiaire)
    {
        $aides = $beneficiaire->aides()->with('category')->get();

        return view('back.beneficiaires.show', compact('beneficiaire', 'aides'));
    }

    public function edit(Beneficiaire $beneficiaire)
    {
        return view('back.beneficiaires.create', compact('beneficiaire'));
    }

    public function update(Request $request, Beneficiaire $beneficiaire)
    {
       $request->validate([
    'nom' => 'required|string|max:100',
    'prenom' => 'required|string|max:100',
    'ville' => 'nullable|string|max:100',
    'adresse' => 'nullable|string|max:255',
    'telephone' => 'nullable|string|max:20',
    'email' => 'required|email|unique:beneficiaires,email,' . $beneficiaire->id,
    'identifiant_national' => 'required|string|unique:beneficiaires,identifiant_national,' . $beneficiaire->id,
    'photo' => 'nullable|image|max:2048',
]);

                $beneficiaire->update([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'ville' => $request->ville,
                    'adresse' => $request->adresse,
                    'telephone' => $request->telephone,
                    'email' => $request->email,
                    'identifiant_national' => $request->identifiant_national,
                    'photo' => $beneficiaire->photo,
                ]);


        // Si nouvelle photo, supprimer l’ancienne
        if ($request->hasFile('photo')) {
            if ($beneficiaire->photo) {
                Storage::disk('public')->delete($beneficiaire->photo);
            }
            $beneficiaire->photo = $request->file('photo')->store('beneficiaires', 'public');
        }

       $beneficiaire->update([
    'nom' => $request->nom,
    'prenom' => $request->prenom,
    'ville' => $request->ville,
    'adresse' => $request->adresse,
    'telephone' => $request->telephone,
    'email' => $request->email,
    'identifiant_national' => $request->identifiant_national,
    'photo' => $beneficiaire->photo,
]);

        return redirect()->route('beneficiaires.index')->with('success', 'Bénéficiaire mis à jour.');
    }

    public function destroy(Beneficiaire $beneficiaire)
    {
        if ($beneficiaire->photo) {
            Storage::disk('public')->delete($beneficiaire->photo);
        }

        $beneficiaire->delete();

        return redirect()->route('beneficiaires.index')->with('success', 'Bénéficiaire supprimé.');
    }


   public function verifierIdentifiant(Request $request)
{
    $identifiant = $request->input('identifiant');

    $resultats = Beneficiaire::where('identifiant_national', 'LIKE', "%{$identifiant}%")->get();

    return response()->json([
        'existe' => $resultats->isNotEmpty(),
        'suggestions' => $resultats->map(function ($b) {
            return [
                'identifiant' => $b->identifiant_national,
                'nom' => $b->nom,
                'prenom' => $b->prenom,
                'ville' => $b->ville,
                'adresse' => $b->adresse,
                'email' => $b->email,
                'telephone' => $b->telephone,
            ];
        }),
    ]);
}

public function verifierNomEmail(Request $request)
{

    $term = $request->input('term');

    $resultats = Beneficiaire::where('prenom', 'LIKE', "%{$term}%")->get();
                    
    return response()->json([
        'existe' => $resultats->isNotEmpty(),
        'suggestions' => $resultats->map(function ($b) {
            return [
                'identifiant' => $b->identifiant_national,
                'nom' => $b->nom,
                'prenom' => $b->prenom,
                'ville' => $b->ville,
                'adresse' => $b->adresse,
                'email' => $b->email,
                'telephone' => $b->telephone,
            ];
        }),
    ]);
}






}
