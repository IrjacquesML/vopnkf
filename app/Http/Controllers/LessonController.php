<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Lecon;
use App\Services\BibleVerseService;
use App\Services\LessonProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LessonController extends Controller implements HasMiddleware
{
    public function __construct(
        private LessonProgressService $progressService,
        private BibleVerseService $bibleVerseService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function dashboard(): View
    {
        $user = Auth::user();

        $categories = Categorie::with('lecons')
            ->orderBy('ordre')
            ->get()
            ->map(function (Categorie $categorie) use ($user) {
                $lecons = $categorie->lecons->map(function (Lecon $lecon) use ($user) {
                    $progression = $this->progressService->getProgression($user, $lecon);

                    return [
                        'lecon' => $lecon,
                        'deverrouillee' => $this->progressService->isUnlocked($user, $lecon),
                        'statut' => $progression?->statut ?? 'non_commence',
                        'score' => $progression?->score,
                    ];
                });

                return [
                    'categorie' => $categorie,
                    'lecons' => $lecons,
                ];
            });

        $allLessons = $categories->flatMap(fn ($group) => $group['lecons']);
        $totalLecons = $allLessons->count();
        $terminees = $allLessons->where('statut', 'termine')->count();
        $enCours = $allLessons->where('statut', 'en_cours')->count();
        $pourcentage = $totalLecons > 0 ? round(($terminees / $totalLecons) * 100) : 0;
        $prochaine = $allLessons->first(fn ($item) => $item['deverrouillee'] && $item['statut'] !== 'termine');

        return view('lessons.dashboard', [
            'categories' => $categories,
            'user' => $user,
            'stats' => [
                'total' => $totalLecons,
                'terminees' => $terminees,
                'en_cours' => $enCours,
                'pourcentage' => $pourcentage,
            ],
            'prochaine' => $prochaine,
        ]);
    }

    public function show(int $id): View|RedirectResponse
    {
        $user = Auth::user();

        $lecon = Lecon::with(['categorie', 'questions.options'])->findOrFail($id);

        if (! $this->progressService->isUnlocked($user, $lecon)) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette leçon n\'est pas encore déverrouillée.');
        }

        $this->progressService->markInProgress($user, $lecon);

        $lecon->contenu = $this->bibleVerseService->processContent($lecon->contenu ?? '');

        $questions = $lecon->questions->map(function ($question) {
            $question->question = $this->bibleVerseService->processContent(e($question->question));

            return $question;
        });

        $progression = $this->progressService->getProgression($user, $lecon);

        return view('lessons.show', [
            'lecon' => $lecon,
            'questions' => $questions,
            'user' => $user,
            'dejaTerminee' => $progression?->statut === 'termine',
        ]);
    }

    /**
     * Terminer une leçon sans quiz (ou valider la lecture) → déverrouille la suivante.
     */
    public function complete(int $id): RedirectResponse
    {
        $user = Auth::user();
        $lecon = Lecon::with('questions')->findOrFail($id);

        if (! $this->progressService->isUnlocked($user, $lecon)) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette leçon n\'est pas encore déverrouillée.');
        }

        // Si la leçon a un quiz, obliger de le passer
        if ($lecon->questions->isNotEmpty()) {
            return redirect()->route('lessons.show', $lecon->id)
                ->with('error', 'Veuillez répondre à l\'interrogation pour terminer cette leçon.');
        }

        $this->progressService->markCompleted($user, $lecon, 100);

        $next = $this->progressService->nextUnlockedLesson($user, $lecon);

        $message = 'Leçon terminée !';
        if ($next) {
            $message .= ' La leçon suivante « '.$next->titre.' » est maintenant déverrouillée.';
        }

        return redirect()->route('dashboard')->with('success', $message);
    }
}
