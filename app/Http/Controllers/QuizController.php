<?php

namespace App\Http\Controllers;

use App\Models\Lecon;
use App\Models\OptionReponse;
use App\Models\Question;
use App\Models\ReponseUtilisateur;
use App\Services\LessonProgressService;
use App\Services\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuizController extends Controller implements HasMiddleware
{
    public function __construct(
        private QuizService $quizService,
        private LessonProgressService $progressService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lecon_id' => ['required', 'integer', 'exists:lecons,id'],
        ], [
            'lecon_id.required' => 'La leçon est requise.',
            'lecon_id.exists' => 'Leçon introuvable.',
        ]);

        $user = Auth::user();
        $lecon = Lecon::findOrFail($validated['lecon_id']);

        if (! $this->progressService->isUnlocked($user, $lecon)) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette leçon n\'est pas accessible.');
        }

        $answers = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^question_(\d+)$/', $key, $matches)) {
                $answers[(int) $matches[1]] = (int) $value;
            }
        }

        $this->quizService->submit($user, $lecon, $answers);

        $next = $this->progressService->nextUnlockedLesson($user, $lecon);

        $message = 'Interrogation soumise. Leçon terminée !';
        if ($next) {
            $message .= ' La leçon suivante « '.$next->titre.' » est déverrouillée.';
        }

        return redirect()
            ->route('lessons.results', ['lecon' => $lecon->id])
            ->with('success', $message);
    }

    public function results(Request $request): View|RedirectResponse
    {
        $leconId = (int) $request->query('lecon_id', $request->route('lecon'));

        if (! $leconId) {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $lecon = Lecon::with('categorie')->findOrFail($leconId);

        $progression = $this->progressService->getProgression($user, $lecon);
        $score = $progression?->score ?? 0;

        $questions = Question::where('lecon_id', $lecon->id)
            ->orderBy('ordre')
            ->get();

        $reponsesUtilisateur = ReponseUtilisateur::where('user_id', $user->id)
            ->where('lecon_id', $lecon->id)
            ->get()
            ->keyBy('question_id');

        $reponses = $questions->map(function (Question $question) use ($reponsesUtilisateur) {
            $reponseUtilisateur = $reponsesUtilisateur->get($question->id);
            $optionChoisie = $reponseUtilisateur
                ? OptionReponse::find($reponseUtilisateur->option_id)
                : null;
            $bonneOption = OptionReponse::where('question_id', $question->id)
                ->where('est_correcte', true)
                ->first();

            return [
                'question' => $question,
                'reponse_donnee' => $optionChoisie?->texte_option,
                'bonne_reponse' => $bonneOption?->texte_option,
                'est_correcte' => (bool) ($reponseUtilisateur?->est_correcte ?? false),
            ];
        });

        $totalQuestions = $reponses->count();
        $bonnesReponses = $reponses->where('est_correcte', true)->count();
        $nextLecon = $this->progressService->nextUnlockedLesson($user, $lecon);

        return view('lessons.results', [
            'lecon' => $lecon,
            'score' => $score,
            'reponses' => $reponses,
            'totalQuestions' => $totalQuestions,
            'bonnesReponses' => $bonnesReponses,
            'nextLecon' => $nextLecon,
        ]);
    }
}
