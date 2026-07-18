<?php

namespace App\Services;

use App\Models\Lecon;
use App\Models\ProgressionLecon;
use App\Models\User;

class LessonProgressService
{
    /**
     * Une leçon est déverrouillée si c'est la première de la catégorie,
     * ou si la leçon précédente (ordre immédiat inférieur) est terminée.
     */
    public function isUnlocked(User $user, Lecon $lecon): bool
    {
        $previous = $this->previousLesson($lecon);

        if ($previous === null) {
            return true;
        }

        $progression = $this->getProgression($user, $previous);

        return $progression !== null && $progression->statut === 'termine';
    }

    public function previousLesson(Lecon $lecon): ?Lecon
    {
        return Lecon::query()
            ->where('categorie_id', $lecon->categorie_id)
            ->where('ordre', '<', (int) $lecon->ordre)
            ->orderByDesc('ordre')
            ->orderByDesc('id')
            ->first();
    }

    public function nextLesson(Lecon $lecon): ?Lecon
    {
        return Lecon::query()
            ->where('categorie_id', $lecon->categorie_id)
            ->where('ordre', '>', (int) $lecon->ordre)
            ->orderBy('ordre')
            ->orderBy('id')
            ->first();
    }

    /**
     * Prochaine leçon désormais accessible après avoir terminé $lecon.
     */
    public function nextUnlockedLesson(User $user, Lecon $lecon): ?Lecon
    {
        $next = $this->nextLesson($lecon);

        if ($next === null) {
            return null;
        }

        return $this->isUnlocked($user, $next) ? $next : null;
    }

    public function markInProgress(User $user, Lecon $lecon): void
    {
        $progression = ProgressionLecon::query()->firstOrNew([
            'user_id' => $user->id,
            'lecon_id' => $lecon->id,
        ]);

        if ($progression->statut === 'termine') {
            return;
        }

        if (! $progression->exists || $progression->date_debut === null) {
            $progression->date_debut = now();
        }

        $progression->statut = 'en_cours';
        $progression->save();
    }

    public function markCompleted(User $user, Lecon $lecon, ?float $score = null): ProgressionLecon
    {
        $now = now();

        return ProgressionLecon::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'lecon_id' => $lecon->id,
            ],
            [
                'statut' => 'termine',
                'score' => $score,
                'date_debut' => ProgressionLecon::query()
                    ->where('user_id', $user->id)
                    ->where('lecon_id', $lecon->id)
                    ->value('date_debut') ?? $now,
                'date_completion' => $now,
                'date_fin' => $now,
            ]
        );
    }

    public function getProgression(User $user, Lecon $lecon): ?ProgressionLecon
    {
        return ProgressionLecon::query()
            ->where('user_id', $user->id)
            ->where('lecon_id', $lecon->id)
            ->first();
    }
}
