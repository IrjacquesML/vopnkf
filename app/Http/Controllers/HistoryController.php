<?php

namespace App\Http\Controllers;

use App\Models\Lecon;
use App\Models\ProgressionLecon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistoryController extends Controller implements HasMiddleware
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

        $stats = ProgressionLecon::query()
            ->where('user_id', $user->id)
            ->where('statut', 'termine')
            ->selectRaw('COUNT(DISTINCT lecon_id) as total_lecons_terminees')
            ->selectRaw('AVG(score) as score_moyen')
            ->selectRaw('MIN(date_fin) as premiere_lecon_date')
            ->selectRaw('MAX(date_fin) as derniere_lecon_date')
            ->first();

        $totalLecons = Lecon::count();
        $totalTerminees = (int) ($stats->total_lecons_terminees ?? 0);
        $pourcentageProgression = $totalLecons > 0
            ? ($totalTerminees / $totalLecons) * 100
            : 0;

        $enCours = ProgressionLecon::with(['lecon.categorie'])
            ->where('user_id', $user->id)
            ->where('statut', 'en_cours')
            ->orderByDesc('date_debut')
            ->get();

        $termine = ProgressionLecon::with(['lecon.categorie'])
            ->where('user_id', $user->id)
            ->where('statut', 'termine')
            ->orderByDesc('date_fin')
            ->get()
            ->map(function (ProgressionLecon $progression) use ($user) {
                $lecon = $progression->lecon;

                $bonnesReponses = DB::table('reponses_utilisateurs')
                    ->where('user_id', $user->id)
                    ->where('lecon_id', $lecon->id)
                    ->where('est_correcte', true)
                    ->count();

                $totalQuestions = $lecon->questions()->count();

                return [
                    'progression' => $progression,
                    'lecon' => $lecon,
                    'bonnes_reponses' => $bonnesReponses,
                    'total_questions' => $totalQuestions,
                ];
            });

        return view('history.index', [
            'user' => $user,
            'stats' => $stats,
            'totalLecons' => $totalLecons,
            'pourcentageProgression' => $pourcentageProgression,
            'enCours' => $enCours,
            'termine' => $termine,
        ]);
    }
}
