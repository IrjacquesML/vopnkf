@extends('layouts.app')

@section('title', 'Résultats — ' . $lecon->titre)

@section('content')
    @php
        if ($score >= 90) {
            $messageFelicitation = 'Excellent travail! Vous maîtrisez parfaitement cette leçon.';
            $classeScore = 'excellent';
        } elseif ($score >= 75) {
            $messageFelicitation = 'Très bien! Vous avez une bonne compréhension de la leçon.';
            $classeScore = 'tres-bien';
        } elseif ($score >= 60) {
            $messageFelicitation = 'Bien! Continuez vos efforts pour approfondir votre compréhension.';
            $classeScore = 'bien';
        } else {
            $messageFelicitation = 'Nous vous encourageons à relire la leçon et à réessayer.';
            $classeScore = 'a-revoir';
        }
    @endphp

    <div class="container results-container">
        @include('partials.alerts')

        <div class="results-header">
            <h1>Résultats de l'interrogation</h1>
            <div class="breadcrumb">
                {{ $lecon->categorie->nom ?? '' }} / {{ $lecon->titre }}
            </div>
        </div>

        <div class="score-summary {{ $classeScore }}">
            <div class="score-circle">
                <div class="score-value">{{ number_format($score, 0) }}%</div>
                <div class="score-label">{{ $bonnesReponses }} / {{ $totalQuestions }} correctes</div>
            </div>
            <p class="score-message">{{ $messageFelicitation }}</p>
        </div>

        <div class="results-details">
            <h2>Détails de vos réponses</h2>

            @foreach ($reponses as $index => $reponse)
                <div class="result-item {{ $reponse['est_correcte'] ? 'correct' : 'incorrect' }}">
                    <div class="result-header">
                        <h3>Question {{ $index + 1 }}</h3>
                        <span class="result-badge">
                            {{ $reponse['est_correcte'] ? '✓ Correct' : '✗ Incorrect' }}
                        </span>
                    </div>

                    <p class="result-question">{!! nl2br(e($reponse['question']->question)) !!}</p>

                    <div class="result-answers">
                        <div class="answer-row">
                            <strong>Votre réponse:</strong>
                            <span class="{{ $reponse['est_correcte'] ? 'correct-answer' : 'wrong-answer' }}">
                                {{ $reponse['reponse_donnee'] ?? 'Non répondu' }}
                            </span>
                        </div>

                        @if (! $reponse['est_correcte'])
                            <div class="answer-row">
                                <strong>Bonne réponse:</strong>
                                <span class="correct-answer">{{ $reponse['bonne_reponse'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="results-actions">
            @if (! empty($nextLecon))
                <div class="unlock-banner" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #e8f5e9; border-left: 4px solid #2e7d32; border-radius: 6px;">
                    <p style="margin: 0 0 .75rem; font-weight: 600; color: #1b5e20;">
                        🔓 Leçon suivante déverrouillée : {{ $nextLecon->titre }}
                    </p>
                    <a href="{{ route('lessons.show', $nextLecon->id) }}" class="btn btn-primary">Commencer la leçon suivante</a>
                </div>
            @endif

            <a href="{{ route('lessons.show', $lecon->id) }}" class="btn btn-secondary">Revoir la leçon</a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Retour au tableau de bord</a>
        </div>

        <div class="encouragement-verse">
            <p class="verse-text">"Je puis tout par celui qui me fortifie."</p>
            <p class="verse-reference">- Philippiens 4:13</p>
        </div>
    </div>
@endsection
