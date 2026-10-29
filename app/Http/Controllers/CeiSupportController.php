<?php

namespace App\Http\Controllers;

use App\Models\CeiSupport;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCeiSupportRequest;
use App\Http\Requests\UpdateCeiSupportRequest;
use Illuminate\Support\Facades\Storage;


class CeiSupportController extends Controller
{
    
    // Formulaire de création
   public function create()
{
    $ceiSupport = CeiSupport::first(); // ou where(...)->first()
    return view('back.soutien.create', compact('ceiSupport'));
}

    // Enregistrer un nouveau contenu
   public function store(StoreCeiSupportRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('image_path')) {
        $data['image_path'] = $request->file('image_path')->store('cei_supports', 'public');
    }

    $ceiSupport = CeiSupport::create($data); // <- maintenant $ceiSupport existe

    return redirect()->route('soutien.edit', $ceiSupport)->with('success', 'Contenu ajouté avec succès.');
}



   // Formulaire de modification
public function edit()
{
    $ceiSupport = CeiSupport::first(); // ou where(...)->first()
    return view('back.soutien.create', compact('ceiSupport'));
}

// Mise à jour du contenu
public function update(UpdateCeiSupportRequest $request)
{
    $ceiSupport = CeiSupport::first(); // ou where(...)->first()

    $data = $request->validated();

    if ($request->hasFile('image_path')) {
        if ($ceiSupport->image_path && Storage::disk('public')->exists($ceiSupport->image_path)) {
            Storage::disk('public')->delete($ceiSupport->image_path);
        }

        $data['image_path'] = $request->file('image_path')->store('cei_supports', 'public');
    }

    $ceiSupport->update($data);

    return redirect()->route('soutien.edit')->with('success', 'Contenu mis à jour avec succès.');
}

    
}
