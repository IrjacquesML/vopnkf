<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'admin']),
        ];
    }

    public function index(): View
    {
        $categories = Categorie::query()
            ->withCount('lecons')
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $nextOrdre = ((int) Categorie::max('ordre')) + 1;

        return view('admin.categories.create', [
            'nextOrdre' => $nextOrdre,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Categorie::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Categorie $categorie): View
    {
        $categorie->loadCount('lecons');

        return view('admin.categories.edit', [
            'category' => $categorie,
        ]);
    }

    public function update(Request $request, Categorie $categorie): RedirectResponse
    {
        $validated = $this->validated($request, $categorie);

        $categorie->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Categorie $categorie): RedirectResponse
    {
        $lessonsCount = $categorie->lecons()->count();

        if ($lessonsCount > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "Impossible de supprimer « {$categorie->nom} » : {$lessonsCount} leçon(s) y sont encore liées. Déplacez ou supprimez d'abord ces leçons.");
        }

        $categorie->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * @return array{nom: string, description: ?string, ordre: int}
     */
    private function validated(Request $request, ?Categorie $category = null): array
    {
        return $request->validate([
            'nom' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories', 'nom')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'ordre' => ['required', 'integer', 'min:0', 'max:9999'],
        ], [
            'nom.required' => 'Le nom de la catégorie est requis.',
            'nom.max' => 'Le nom ne peut pas dépasser 150 caractères.',
            'nom.unique' => 'Une catégorie avec ce nom existe déjà.',
            'description.max' => 'La description est trop longue.',
            'ordre.required' => 'L\'ordre d\'affichage est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);
    }
}
