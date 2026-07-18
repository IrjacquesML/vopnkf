<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Lecon;
use App\Models\OptionReponse;
use App\Models\Question;
use App\Services\LessonWordImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class LessonController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'admin']),
        ];
    }

    public function index(Request $request): View
    {
        $categorieId = $request->input('categorie_id');
        $search = $request->input('search');

        $lessons = Lecon::query()
            ->with('categorie')
            ->when($categorieId, fn ($query, $categorieId) => $query->where('categorie_id', $categorieId))
            ->when($search, fn ($query, string $search) => $query->where('titre', 'like', "%{$search}%"))
            ->orderBy('categorie_id')
            ->orderBy('ordre')
            ->paginate(15)
            ->withQueryString();

        $categories = Categorie::orderBy('ordre')->get();

        return view('admin.lessons.index', [
            'lessons' => $lessons,
            'categories' => $categories,
            'categorieId' => $categorieId,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.lessons.create', [
            'categories' => Categorie::orderBy('ordre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categorie_id' => ['required', 'exists:categories,id'],
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], [
            'categorie_id.required' => 'La catégorie est requise.',
            'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'titre.required' => 'Le titre est requis.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'contenu.required' => 'Le contenu est requis.',
            'ordre.required' => 'L\'ordre est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);

        $lecon = Lecon::create($validated);

        return redirect()->route('admin.lessons.edit', $lecon)
            ->with('success', 'Leçon créée avec succès.');
    }

    public function show(Lecon $lesson): View
    {
        $lesson->load(['categorie', 'questions.options']);

        return view('admin.lessons.show', [
            'lesson' => $lesson,
        ]);
    }

    public function edit(Lecon $lesson): View
    {
        $lesson->load(['categorie', 'questions.options']);

        return view('admin.lessons.edit', [
            'lesson' => $lesson,
            'categories' => Categorie::orderBy('ordre')->get(),
        ]);
    }

    public function update(Request $request, Lecon $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'categorie_id' => ['required', 'exists:categories,id'],
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], [
            'categorie_id.required' => 'La catégorie est requise.',
            'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'titre.required' => 'Le titre est requis.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'contenu.required' => 'Le contenu est requis.',
            'ordre.required' => 'L\'ordre est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);

        $lesson->update($validated);

        return back()->with('success', 'Leçon mise à jour avec succès.');
    }

    public function destroy(Lecon $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()->route('admin.lessons.index')
            ->with('success', 'Leçon supprimée avec succès.');
    }

    public function importForm(): View
    {
        return view('admin.lessons.import', [
            'categories' => Categorie::orderBy('ordre')->get(),
        ]);
    }

    public function importStore(Request $request, LessonWordImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'fichier' => ['required', 'file', 'mimes:doc,docx', 'max:10240'],
            'categorie_id' => ['required', 'exists:categories,id'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ], [
            'fichier.required' => 'Le fichier Word est requis.',
            'fichier.mimes' => 'Le fichier doit être un document Word (.doc ou .docx).',
            'fichier.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
            'categorie_id.required' => 'La catégorie est requise.',
            'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
        ]);

        try {
            $result = $importer->import(
                $request->file('fichier'),
                (int) $validated['categorie_id'],
                isset($validated['ordre']) ? (int) $validated['ordre'] : null
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Impossible d\'importer le fichier Word : '.$e->getMessage());
        }

        return redirect()
            ->route('admin.lessons.edit', $result['lecon'])
            ->with('success', "Leçon « {$result['titre']} » importée avec {$result['questions_count']} question(s). Complétez les options de réponses si besoin.");
    }

    public function importQuestionnaireForm(): View
    {
        return view('admin.lessons.import-questionnaire', [
            'categories' => Categorie::orderBy('ordre')->get(),
        ]);
    }

    public function importQuestionnaireStore(Request $request, \App\Services\QuestionnaireWordImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'fichier' => ['required', 'file', 'mimes:doc,docx', 'max:15360'],
            'categorie_id' => ['required', 'exists:categories,id'],
            'mode' => ['required', 'in:attach,replace,create'],
        ], [
            'fichier.required' => 'Le fichier Word est requis.',
            'fichier.mimes' => 'Le fichier doit être un document Word (.docx).',
            'categorie_id.required' => 'La catégorie est requise.',
            'mode.required' => 'Le mode d\'import est requis.',
            'mode.in' => 'Mode d\'import invalide.',
        ]);

        try {
            $result = $importer->import(
                $request->file('fichier'),
                (int) $validated['categorie_id'],
                $validated['mode']
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Impossible d\'importer le questionnaire : '.$e->getMessage());
        }

        $summary = "Questionnaire importé : {$result['questions_count']} question(s), {$result['options_count']} option(s) — "
            ."{$result['lessons_created']} leçon(s) créée(s), {$result['lessons_updated']} mise(s) à jour. "
            .'Pensez à cocher la bonne réponse pour chaque question.';

        return redirect()
            ->route('admin.lessons.index', ['categorie_id' => $validated['categorie_id']])
            ->with('success', $summary);
    }

    public function storeQuestion(Request $request, Lecon $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], [
            'question.required' => 'La question est requise.',
            'ordre.required' => 'L\'ordre est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);

        $lesson->questions()->create($validated);

        return back()->with('success', 'Question ajoutée avec succès.');
    }

    public function updateQuestion(Request $request, Lecon $lesson, Question $question): RedirectResponse
    {
        if ($question->lecon_id !== $lesson->id) {
            abort(404);
        }

        $validated = $request->validate([
            'question' => ['required', 'string'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], [
            'question.required' => 'La question est requise.',
            'ordre.required' => 'L\'ordre est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);

        $question->update($validated);

        return back()->with('success', 'Question mise à jour avec succès.');
    }

    public function destroyQuestion(Lecon $lesson, Question $question): RedirectResponse
    {
        if ($question->lecon_id !== $lesson->id) {
            abort(404);
        }

        $question->delete();

        return back()->with('success', 'Question supprimée avec succès.');
    }

    public function storeOption(Request $request, Lecon $lesson, Question $question): RedirectResponse
    {
        if ($question->lecon_id !== $lesson->id) {
            abort(404);
        }

        $validated = $request->validate([
            'texte_option' => ['required', 'string'],
            'est_correcte' => ['nullable', 'boolean'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], [
            'texte_option.required' => 'Le texte de l\'option est requis.',
            'ordre.required' => 'L\'ordre est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);

        $question->options()->create([
            'texte_option' => $validated['texte_option'],
            'est_correcte' => $request->boolean('est_correcte'),
            'ordre' => $validated['ordre'],
        ]);

        return back()->with('success', 'Option de réponse ajoutée avec succès.');
    }

    public function updateOption(Request $request, Lecon $lesson, Question $question, OptionReponse $option): RedirectResponse
    {
        if ($question->lecon_id !== $lesson->id || $option->question_id !== $question->id) {
            abort(404);
        }

        $validated = $request->validate([
            'texte_option' => ['required', 'string'],
            'est_correcte' => ['nullable', 'boolean'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], [
            'texte_option.required' => 'Le texte de l\'option est requis.',
            'ordre.required' => 'L\'ordre est requis.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être positif ou nul.',
        ]);

        $option->update([
            'texte_option' => $validated['texte_option'],
            'est_correcte' => $request->boolean('est_correcte'),
            'ordre' => $validated['ordre'],
        ]);

        return back()->with('success', 'Option de réponse mise à jour avec succès.');
    }

    public function destroyOption(Lecon $lesson, Question $question, OptionReponse $option): RedirectResponse
    {
        if ($question->lecon_id !== $lesson->id || $option->question_id !== $question->id) {
            abort(404);
        }

        $option->delete();

        return back()->with('success', 'Option de réponse supprimée avec succès.');
    }
}
