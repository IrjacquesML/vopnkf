@extends('layouts.app')

@section('title', 'Mes Leçons — VOP')

@section('content')
    <div class="container dashboard-container">
        @include('partials.alerts')

        <div class="dashboard-header">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'cross', 'class' => 'vop-icon-gold'])
                Étude biblique
            </div>
            <h1>Bonjour, {{ $user->prenom }}</h1>
            <p class="dashboard-subtitle">Continue ton parcours biblique, une leçon à la fois.</p>
        </div>

        <div class="dashboard-progress-card">
            <div class="dashboard-progress-top">
                <div>
                    <span class="dashboard-progress-label">Ta progression</span>
                    <strong class="dashboard-progress-value">{{ $stats['terminees'] }}/{{ $stats['total'] }} leçons</strong>
                </div>
                <div class="dashboard-progress-pct">{{ $stats['pourcentage'] }}%</div>
            </div>
            <div class="progress-spiritual">
                <div class="progress-fill" style="width: {{ $stats['pourcentage'] }}%"></div>
            </div>
            <div class="dashboard-mini-stats">
                <div class="mini-stat">
                    <span class="mini-stat-value">{{ $stats['terminees'] }}</span>
                    <span class="mini-stat-label">Terminées</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value">{{ $stats['en_cours'] }}</span>
                    <span class="mini-stat-label">En cours</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value">{{ max($stats['total'] - $stats['terminees'] - $stats['en_cours'], 0) }}</span>
                    <span class="mini-stat-label">À faire</span>
                </div>
            </div>

            @if ($prochaine)
                <a href="{{ route('lessons.show', $prochaine['lecon']->id) }}" class="btn btn-primary dashboard-cta">
                    @include('partials.icon', ['name' => 'play'])
                    {{ $prochaine['statut'] === 'en_cours' ? 'Continuer' : 'Commencer' }} :
                    {{ \Illuminate\Support\Str::limit($prochaine['lecon']->titre, 36) }}
                </a>
            @endif
        </div>

        @forelse ($categories as $group)
            @php
                $categorie = $group['categorie'];
                $lecons = $group['lecons'];
                $catTerminees = $lecons->where('statut', 'termine')->count();
                $catTotal = $lecons->count();
            @endphp

            <div class="category-section">
                <div class="category-header">
                    <div class="icon-badge">
                        @include('partials.icon', ['name' => 'bible', 'class' => 'vop-icon-green'])
                    </div>
                    <div class="category-header-text">
                        <h2>{{ $categorie->nom }}</h2>
                        <p>{{ $categorie->description }}</p>
                        <span class="category-count">{{ $catTerminees }}/{{ $catTotal }} terminées</span>
                    </div>
                </div>

                <div class="lessons-grid">
                    @foreach ($lecons as $item)
                        @php
                            $lecon = $item['lecon'];
                            $deverrouillee = $item['deverrouillee'];
                            $statut = $item['statut'];
                            $score = $item['score'] ?? null;
                        @endphp

                        <article class="lesson-card {{ ! $deverrouillee ? 'locked' : '' }} {{ $statut === 'termine' ? 'termine' : '' }} {{ $statut === 'en_cours' ? 'en-cours' : '' }}">
                            <div class="lesson-card-main">
                                <div class="lesson-number">
                                    @include('partials.icon', ['name' => 'light', 'class' => 'vop-icon-gold'])
                                    Leçon {{ $lecon->ordre }}
                                </div>
                                <h3>{{ $lecon->titre }}</h3>

                                @if (! $deverrouillee)
                                    <p class="lock-message">
                                        @include('partials.icon', ['name' => 'lock', 'class' => 'vop-icon-muted'])
                                        Terminez la leçon précédente
                                    </p>
                                @elseif ($statut === 'termine')
                                    <div class="lesson-status completed">
                                        @include('partials.icon', ['name' => 'check', 'class' => 'vop-icon-green'])
                                        Terminée
                                        @if ($score !== null)
                                            <span class="score">{{ number_format($score, 0) }}%</span>
                                        @endif
                                    </div>
                                @elseif ($statut === 'en_cours')
                                    <div class="lesson-status in-progress">En cours</div>
                                @else
                                    <div class="lesson-status available">Disponible</div>
                                @endif
                            </div>

                            <div class="lesson-card-actions">
                                @if (! $deverrouillee)
                                    <span class="lesson-locked-badge" aria-hidden="true">
                                        @include('partials.icon', ['name' => 'lock', 'class' => 'vop-icon-muted vop-icon-lg'])
                                    </span>
                                @elseif ($statut === 'termine')
                                    <a href="{{ route('lessons.show', $lecon->id) }}" class="btn btn-secondary btn-small">Revoir</a>
                                @elseif ($statut === 'en_cours')
                                    <a href="{{ route('lessons.show', $lecon->id) }}" class="btn btn-primary btn-small">
                                        @include('partials.icon', ['name' => 'play']) Continuer
                                    </a>
                                @else
                                    <a href="{{ route('lessons.show', $lecon->id) }}" class="btn btn-primary btn-small">
                                        @include('partials.icon', ['name' => 'unlock']) Commencer
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="icon-badge" style="margin:0 auto 1rem;">
                    @include('partials.icon', ['name' => 'bible'])
                </div>
                <p>Aucune leçon disponible pour le moment.</p>
            </div>
        @endforelse
    </div>
@endsection
