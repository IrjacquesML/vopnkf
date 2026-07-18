<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\DemandePriere;
use App\Models\Lecon;
use App\Models\Parametre;
use App\Models\ProgressionLecon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'admin']),
        ];
    }

    public function statistiques(): View
    {
        $countsByRole = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $progressionsByStatut = ProgressionLecon::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $avgScores = [
            'global' => ProgressionLecon::where('statut', 'termine')
                ->whereNotNull('score')
                ->avg('score'),
            'by_categorie' => ProgressionLecon::query()
                ->join('lecons', 'progression_lecons.lecon_id', '=', 'lecons.id')
                ->join('categories', 'lecons.categorie_id', '=', 'categories.id')
                ->where('progression_lecons.statut', 'termine')
                ->whereNotNull('progression_lecons.score')
                ->selectRaw('categories.nom as categorie, AVG(progression_lecons.score) as score_moyen')
                ->groupBy('categories.id', 'categories.nom', 'categories.ordre')
                ->orderBy('categories.ordre')
                ->get(),
        ];

        $totals = [
            'users' => User::where('role', 'utilisateur')->count(),
            'lessons' => Lecon::count(),
            'progressions' => ProgressionLecon::count(),
            'completed' => ProgressionLecon::where('statut', 'termine')->count(),
            'prayers' => DemandePriere::count(),
            'score_moyen' => round((float) ($avgScores['global'] ?? 0), 1),
        ];

        $prayersByStatut = DemandePriere::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $topLessons = ProgressionLecon::query()
            ->join('lecons', 'progression_lecons.lecon_id', '=', 'lecons.id')
            ->where('progression_lecons.statut', 'termine')
            ->selectRaw('lecons.titre, COUNT(*) as total, ROUND(AVG(progression_lecons.score), 1) as score_moyen')
            ->groupBy('lecons.id', 'lecons.titre', 'lecons.ordre')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $charts = [
            'roles' => [
                'labels' => $countsByRole->keys()->map(fn ($role) => $this->labelRole((string) $role))->values(),
                'data' => $countsByRole->values()->map(fn ($v) => (int) $v)->values(),
            ],
            'progressions' => [
                'labels' => $progressionsByStatut->keys()->map(fn ($s) => $this->labelStatut((string) $s))->values(),
                'data' => $progressionsByStatut->values()->map(fn ($v) => (int) $v)->values(),
            ],
            'scores' => [
                'labels' => $avgScores['by_categorie']->pluck('categorie')->values(),
                'data' => $avgScores['by_categorie']->pluck('score_moyen')->map(fn ($v) => round((float) $v, 1))->values(),
            ],
            'completions' => $this->monthlySeries(
                ProgressionLecon::query()
                    ->where('statut', 'termine')
                    ->whereNotNull('date_completion')
                    ->where('date_completion', '>=', now()->subMonths(11)->startOfMonth())
                    ->selectRaw($this->monthSelectExpression('date_completion').' as mois, COUNT(*) as total')
                    ->groupBy('mois')
                    ->orderBy('mois')
                    ->pluck('total', 'mois')
                    ->all()
            ),
            'inscriptions' => $this->monthlySeries(
                User::query()
                    ->where('role', 'utilisateur')
                    ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                    ->selectRaw($this->monthSelectExpression('created_at').' as mois, COUNT(*) as total')
                    ->groupBy('mois')
                    ->orderBy('mois')
                    ->pluck('total', 'mois')
                    ->all()
            ),
            'prayers' => [
                'labels' => $prayersByStatut->keys()->map(fn ($s) => $this->labelPriere((string) $s))->values(),
                'data' => $prayersByStatut->values()->map(fn ($v) => (int) $v)->values(),
            ],
            'topLessons' => [
                'labels' => $topLessons->map(fn ($row) => \Illuminate\Support\Str::limit($row->titre, 28))->values(),
                'data' => $topLessons->pluck('total')->map(fn ($v) => (int) $v)->values(),
                'scores' => $topLessons->pluck('score_moyen')->map(fn ($v) => round((float) $v, 1))->values(),
            ],
        ];

        return view('admin.reports.statistiques', [
            'countsByRole' => $countsByRole,
            'progressionsByStatut' => $progressionsByStatut,
            'avgScores' => $avgScores,
            'totals' => $totals,
            'charts' => $charts,
        ]);
    }

    public function palmares(Request $request): View
    {
        $categorieId = $request->input('categorie_id');

        $totalLessonsQuery = Lecon::query();
        if ($categorieId) {
            $totalLessonsQuery->where('categorie_id', $categorieId);
        }
        $totalLessons = $totalLessonsQuery->count();

        $palmares = User::query()
            ->where('role', 'utilisateur')
            ->select([
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'users.ville',
                'users.created_at',
            ])
            ->selectRaw('COUNT(DISTINCT progression_lecons.lecon_id) as nb_lecons_terminees')
            ->selectRaw('ROUND(AVG(progression_lecons.score), 2) as score_moyen')
            ->selectRaw('MAX(progression_lecons.date_completion) as derniere_completion')
            ->join('progression_lecons', function ($join) {
                $join->on('users.id', '=', 'progression_lecons.user_id')
                    ->where('progression_lecons.statut', '=', 'termine');
            })
            ->join('lecons', 'progression_lecons.lecon_id', '=', 'lecons.id')
            ->when($categorieId, fn ($query, $categorieId) => $query->where('lecons.categorie_id', $categorieId))
            ->groupBy(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'users.ville',
                'users.created_at'
            )
            ->having('nb_lecons_terminees', '>', 0)
            ->orderByDesc('nb_lecons_terminees')
            ->orderByDesc('score_moyen')
            ->get()
            ->map(function ($user) use ($totalLessons) {
                $user->total_lecons = $totalLessons;
                $user->taux_completion = $totalLessons > 0
                    ? round(($user->nb_lecons_terminees / $totalLessons) * 100, 1)
                    : 0;

                return $user;
            });

        $top = $palmares->take(10);

        $charts = [
            'scores' => [
                'labels' => $top->map(fn ($u) => trim($u->prenom.' '.$u->nom))->values(),
                'data' => $top->pluck('score_moyen')->map(fn ($v) => round((float) $v, 1))->values(),
            ],
            'lecons' => [
                'labels' => $top->map(fn ($u) => trim($u->prenom.' '.$u->nom))->values(),
                'data' => $top->pluck('nb_lecons_terminees')->map(fn ($v) => (int) $v)->values(),
            ],
        ];

        return view('admin.reports.palmares', [
            'palmares' => $palmares,
            'categories' => Categorie::orderBy('ordre')->get(),
            'categorieId' => $categorieId,
            'totalLessons' => $totalLessons,
            'charts' => $charts,
        ]);
    }

    public function certificat(Request $request): View
    {
        $userId = $request->input('user_id');
        $totalLessons = Lecon::count();

        if ($userId) {
            $user = User::query()
                ->where('role', 'utilisateur')
                ->findOrFail($userId);

            $stats = ProgressionLecon::query()
                ->where('user_id', $user->id)
                ->where('statut', 'termine')
                ->selectRaw('COUNT(DISTINCT lecon_id) as nb_lecons_terminees')
                ->selectRaw('ROUND(AVG(score), 2) as score_moyen')
                ->selectRaw('MAX(date_completion) as derniere_completion')
                ->first();

            $user->nb_lecons_terminees = (int) ($stats->nb_lecons_terminees ?? 0);
            $user->score_moyen = $stats->score_moyen;
            $user->derniere_completion = $stats->derniere_completion;
            $user->eligible = $totalLessons > 0 && $user->nb_lecons_terminees >= $totalLessons;

            return view('admin.reports.certificat', [
                'mode' => 'certificat',
                'user' => $user,
                'totalLessons' => $totalLessons,
                'dateCertificat' => now()->format('d/m/Y'),
                'signatories' => Parametre::certificateSignatories(),
            ]);
        }

        $users = User::query()
            ->where('role', 'utilisateur')
            ->select([
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'users.ville',
            ])
            ->selectRaw('COUNT(DISTINCT progression_lecons.lecon_id) as nb_lecons_terminees')
            ->selectRaw('ROUND(AVG(progression_lecons.score), 2) as score_moyen')
            ->selectRaw('MAX(progression_lecons.date_completion) as derniere_completion')
            ->join('progression_lecons', function ($join) {
                $join->on('users.id', '=', 'progression_lecons.user_id')
                    ->where('progression_lecons.statut', '=', 'termine');
            })
            ->groupBy(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'users.ville'
            )
            ->orderByDesc('nb_lecons_terminees')
            ->orderByDesc('score_moyen')
            ->get()
            ->map(function ($user) use ($totalLessons) {
                $user->eligible = $totalLessons > 0 && (int) $user->nb_lecons_terminees >= $totalLessons;

                return $user;
            });

        return view('admin.reports.certificat', [
            'mode' => 'liste',
            'users' => $users,
            'totalLessons' => $totalLessons,
        ]);
    }

    private function monthSelectExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * @param  array<string, int|string>  $raw
     * @return array{labels: list<string>, data: list<int>}
     */
    private function monthlySeries(array $raw): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $key = $month->format('Y-m');
            $labels[] = $month->locale('fr')->translatedFormat('M Y');
            $data[] = (int) ($raw[$key] ?? 0);
        }

        return compact('labels', 'data');
    }

    private function labelRole(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrateurs',
            'utilisateur' => 'Participants',
            default => ucfirst($role),
        };
    }

    private function labelStatut(string $statut): string
    {
        return match ($statut) {
            'termine' => 'Terminées',
            'en_cours' => 'En cours',
            'non_commence' => 'Non commencées',
            default => ucfirst(str_replace('_', ' ', $statut)),
        };
    }

    private function labelPriere(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'En attente',
            'en_priere' => 'En prière',
            'exaucee' => 'Exaucées',
            default => ucfirst(str_replace('_', ' ', $statut)),
        };
    }
}
