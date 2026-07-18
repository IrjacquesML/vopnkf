<?php

namespace App\Services;

use App\Models\Lecon;
use App\Models\OptionReponse;
use App\Models\ReponseUtilisateur;
use App\Models\User;

class QuizService
{
    public function submit(User $user, Lecon $lecon, array $answers): float
    {
        ReponseUtilisateur::query()
            ->where('user_id', $user->id)
            ->where('lecon_id', $lecon->id)
            ->delete();

        $questions = $lecon->questions()->with('options')->get();
        $totalQuestions = $questions->count();
        $correctCount = 0;
        $now = now();

        foreach ($questions as $question) {
            $optionId = $answers[$question->id] ?? null;

            if (! $optionId) {
                continue;
            }

            $option = $question->options->firstWhere('id', (int) $optionId)
                ?? OptionReponse::query()
                    ->where('id', $optionId)
                    ->where('question_id', $question->id)
                    ->first();

            $isCorrect = $option !== null && $option->est_correcte;

            if ($isCorrect) {
                $correctCount++;
            }

            ReponseUtilisateur::query()->create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'option_id' => $optionId,
                'lecon_id' => $lecon->id,
                'est_correcte' => $isCorrect,
                'date_reponse' => $now,
            ]);
        }

        $score = $totalQuestions > 0
            ? round(($correctCount / $totalQuestions) * 100, 2)
            : 100.0;

        app(LessonProgressService::class)->markCompleted($user, $lecon, $score);

        return $score;
    }
}
