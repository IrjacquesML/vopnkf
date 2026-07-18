<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandePriere;
use App\Models\Lecon;
use App\Models\ProgressionLecon;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'admin']),
        ];
    }

    public function index(): View
    {
        $stats = [
            'users_count' => User::where('role', 'utilisateur')->count(),
            'lessons_count' => Lecon::count(),
            'prayers_pending_count' => DemandePriere::where('statut', 'en_attente')->count(),
            'completed_progressions_count' => ProgressionLecon::where('statut', 'termine')->count(),
        ];

        $recentUsers = User::query()
            ->where('role', 'utilisateur')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentPrayers = DemandePriere::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentPrayers' => $recentPrayers,
        ]);
    }
}
