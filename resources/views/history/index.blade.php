@extends('layouts.app')

@section('title', 'Mon Historique — VOP')

@section('content')
    <div class="container historique-container">
        @include('partials.alerts')

        <div class="historique-header dashboard-header">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'history', 'class' => 'vop-icon-gold'])
                Progression
            </div>
            <h1>Mon Historique d'Étude</h1>
            <p>Suivez votre progression et vos accomplissements</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'check'])</div>
                <div class="stat-value">{{ (int) ($stats->total_lecons_terminees ?? 0) }}</div>
                <div class="stat-label">Leçons terminées</div>
            </div>

            <div class="stat-card primary">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'chart'])</div>
                <div class="stat-value">{{ number_format($stats->score_moyen ?? 0, 1) }}%</div>
                <div class="stat-label">Score moyen</div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'light'])</div>
                <div class="stat-value">{{ number_format($pourcentageProgression, 0) }}%</div>
                <div class="stat-label">Progression globale</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'bible'])</div>
                <div class="stat-value">{{ $totalLecons }}</div>
                <div class="stat-label">Total de leçons</div>
            </div>
        </div>

        <div class="progress-section">
            <h3>Votre progression</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $pourcentageProgression }}%"></div>
            </div>
            <p class="progress-text">
                {{ (int) ($stats->total_lecons_terminees ?? 0) }} sur {{ $totalLecons }} leçons complétées
            </p>
        </div>

        @if ($enCours->isNotEmpty())
            <div class="section">
                <h2>🔄 Leçons en cours</h2>
                <div class="historique-list">
                    @foreach ($enCours as $progression)
                        <div class="historique-item en-cours">
                            <div class="historique-info">
                                <h3>{{ $progression->lecon->titre }}</h3>
                                <p class="historique-category">{{ $progression->lecon->categorie->nom ?? '' }}</p>
                                <p class="historique-date">
                                    Commencée le {{ $progression->date_debut?->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                            <div class="historique-actions">
                                <span class="status-badge in-progress">En cours</span>
                                <a href="{{ route('lessons.show', $progression->lecon_id) }}" class="btn btn-primary btn-small">Continuer</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="section">
            <h2>✅ Leçons terminées</h2>

            @if ($termine->isNotEmpty())
                <div class="historique-list">
                    @foreach ($termine as $item)
                        @php
                            $score = $item['progression']->score;
                            if ($score >= 90) {
                                $classeScore = 'excellent';
                            } elseif ($score >= 75) {
                                $classeScore = 'tres-bien';
                            } elseif ($score >= 60) {
                                $classeScore = 'bien';
                            } else {
                                $classeScore = 'a-revoir';
                            }
                        @endphp

                        <div class="historique-item completed">
                            <div class="historique-info">
                                <h3>{{ $item['lecon']->titre }}</h3>
                                <p class="historique-category">{{ $item['lecon']->categorie->nom ?? '' }}</p>
                                <div class="historique-details">
                                    <span class="detail-item">
                                        📅 Terminée le {{ $item['progression']->date_fin?->format('d/m/Y') }}
                                    </span>
                                    <span class="detail-item">
                                        ✓ {{ $item['bonnes_reponses'] }}/{{ $item['total_questions'] }} bonnes réponses
                                    </span>
                                </div>
                            </div>
                            <div class="historique-actions">
                                <div class="score-badge {{ $classeScore }}">
                                    {{ number_format($score, 0) }}%
                                </div>
                                <a href="{{ route('lessons.show', $item['lecon']->id) }}" class="btn btn-secondary btn-small">Revoir</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>Vous n'avez pas encore terminé de leçon. Commencez votre parcours d'étude biblique!</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Voir mes leçons</a>
                </div>
            @endif
        </div>
    </div>
@endsection
