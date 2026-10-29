<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Affiche la liste des auteurs/responsables.
     */
    public function index()
    {
        return view('back.author.index', [
            'authors' => User::all()
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouvel auteur.
     */
    public function create()
    {
        return view('back.author.create');
    }

    /**
     * Enregistre un nouvel auteur dans la base de données.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make('1234567890'),
            'role'     => $validated['role'],
        ]);

        return redirect()->route('author.index')->with('success', 'Auteur ajouté avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'un auteur existant.
     */
    public function edit(User $author)
    {
        return view('back.author.create', compact('author'));
    }

    /**
     * Met à jour les informations d'un auteur.
     */
    public function update(UserRequest $request, User $author)
    {
        $validatedData = $request->validated();

        // Si l'utilisateur connecté n'est pas superadmin, empêcher la modification du rôle
        if (Auth::user()?->role !== 'superadmin') {
            unset($validatedData['role']);
        }

        $author->update($validatedData);

        return redirect()->route('author.index')->with('success', 'Responsable modifié avec succès.');
    }

    /**
     * Supprime un auteur de la base de données.
     */
    public function destroy(User $author)
{
    $currentUser = auth()->user();

    // Empêcher la suppression de son propre compte
    if ($currentUser->id === $author->id) {
        return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    }

    // Si l'utilisateur connecté est admin, il ne peut pas supprimer un superadmin
    if ($currentUser->role === 'admin' && $author->role === 'superadmin') {
        return back()->with('error', 'Un administrateur ne peut pas supprimer un superadmin.');
    }

    // Sinon, suppression autorisée (superadmin ou admin supprimant un author)
    $author->delete();

    return back()->with('success', 'Responsable supprimé avec succès.');
}

}
