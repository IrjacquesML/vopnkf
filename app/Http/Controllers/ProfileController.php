<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'pays' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'ville' => ['nullable', 'string', 'max:100'],
            'adresse_complete' => ['nullable', 'string'],
            'langue_preferee' => ['required', 'string', 'max:10'],
        ], [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'langue_preferee.required' => 'La langue préférée est requise.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Informations mises à jour avec succès!');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'photo_profil' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ], [
            'photo_profil.required' => 'Veuillez sélectionner une photo.',
            'photo_profil.image' => 'Format de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP.',
            'photo_profil.max' => 'La taille du fichier ne doit pas dépasser 5 MB.',
        ]);

        if ($user->photo_profil && Storage::disk('public')->exists($user->photo_profil)) {
            Storage::disk('public')->delete($user->photo_profil);
        }

        $path = $validated['photo_profil']->store('profils', 'public');

        $user->update(['photo_profil' => $path]);

        return back()->with('success', 'Photo de profil mise à jour avec succès!');
    }
}
