<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandePriere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class PrayerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'admin']),
        ];
    }

    public function index(Request $request): View
    {
        $statut = $request->input('statut');
        $search = $request->input('search');

        $prayers = DemandePriere::query()
            ->with('user')
            ->when($statut, fn ($query, string $statut) => $query->where('statut', $statut))
            ->when($search, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sujet', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('nom', 'like', "%{$search}%")
                                ->orWhere('prenom', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.prayers.index', [
            'prayers' => $prayers,
            'statut' => $statut,
            'search' => $search,
        ]);
    }

    public function show(DemandePriere $prayer): View
    {
        $prayer->load('user');

        return view('admin.prayers.show', [
            'prayer' => $prayer,
        ]);
    }

    public function updateStatus(Request $request, DemandePriere $prayer): RedirectResponse
    {
        $validated = $request->validate([
            'statut' => ['required', 'in:en_attente,en_priere,exaucee'],
        ], [
            'statut.required' => 'Le statut est requis.',
            'statut.in' => 'Le statut sélectionné n\'est pas valide.',
        ]);

        $prayer->update(['statut' => $validated['statut']]);

        return back()->with('success', 'Statut de la demande de prière mis à jour avec succès.');
    }

    public function destroy(DemandePriere $prayer): RedirectResponse
    {
        $prayer->delete();

        return redirect()->route('admin.prayers.index')
            ->with('success', 'Demande de prière supprimée avec succès.');
    }
}
