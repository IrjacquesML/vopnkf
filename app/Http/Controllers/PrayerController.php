<?php

namespace App\Http\Controllers;

use App\Models\DemandePriere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrayerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function index(): View
    {
        $user = Auth::user();

        $demandes = DemandePriere::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $stats = DemandePriere::where('user_id', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente")
            ->selectRaw("SUM(CASE WHEN statut = 'en_priere' THEN 1 ELSE 0 END) as en_priere")
            ->selectRaw("SUM(CASE WHEN statut = 'exaucee' THEN 1 ELSE 0 END) as exaucee")
            ->first();

        return view('prayers.index', [
            'user' => $user,
            'demandes' => $demandes,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('prayers.create', [
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sujet' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10'],
            'est_anonyme' => ['nullable', 'boolean'],
        ], [
            'sujet.required' => 'Le sujet est requis.',
            'message.required' => 'Le message est requis.',
            'message.min' => 'Le message doit contenir au moins 10 caractères.',
        ]);

        DemandePriere::create([
            'user_id' => Auth::id(),
            'sujet' => $validated['sujet'],
            'message' => $validated['message'],
            'est_anonyme' => $request->boolean('est_anonyme'),
            'statut' => 'en_attente',
        ]);

        return redirect()->route('prayers.index')
            ->with('success', 'Votre demande de prière a été envoyée avec succès. Nous prierons pour vous.');
    }
}
