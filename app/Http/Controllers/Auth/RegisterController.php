<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class RegisterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('guest'),
        ];
    }

    public function show(): View
    {
        return view('auth.inscription');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'mot_de_passe' => ['required', 'string', 'min:6'],
            'confirmer_mot_de_passe' => ['required', 'same:mot_de_passe'],
            'pays' => ['required', 'string', 'max:100'],
            'ville' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'province' => ['nullable', 'string', 'max:100'],
            'adresse_complete' => ['nullable', 'string'],
        ], [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email n\'est pas valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'mot_de_passe.required' => 'Le mot de passe est requis.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'confirmer_mot_de_passe.required' => 'La confirmation du mot de passe est requise.',
            'confirmer_mot_de_passe.same' => 'Les mots de passe ne correspondent pas.',
            'pays.required' => 'Le pays est requis.',
            'ville.required' => 'La ville est requise.',
        ]);

        User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => $validated['mot_de_passe'],
            'role' => 'utilisateur',
            'pays' => $validated['pays'],
            'ville' => $validated['ville'],
            'telephone' => $validated['telephone'] ?? null,
            'province' => $validated['province'] ?? null,
            'adresse_complete' => $validated['adresse_complete'] ?? null,
        ]);

        return redirect()->route('connexion')
            ->with('success', 'Inscription réussie! Vous pouvez maintenant vous connecter.');
    }
}
