@extends('layouts.app')

@section('title', 'Mes Prières — VOP')

@section('content')
    <div class="container prayers-container">
        @include('partials.alerts')

        <div class="prayers-header dashboard-header">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'prayer', 'class' => 'vop-icon-gold'])
                Communion
            </div>
            <h1>Mes Demandes de Prière</h1>
            <p>Suivez vos demandes de prière et voyez comment Dieu agit dans votre vie</p>
        </div>

        <div class="prayer-stats-grid">
            <div class="prayer-stat-card total">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'bible'])</div>
                <div class="stat-value">{{ (int) ($stats->total ?? 0) }}</div>
                <div class="stat-label">Total de demandes</div>
            </div>

            <div class="prayer-stat-card attente">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'history'])</div>
                <div class="stat-value">{{ (int) ($stats->en_attente ?? 0) }}</div>
                <div class="stat-label">En attente</div>
            </div>

            <div class="prayer-stat-card priere">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'prayer'])</div>
                <div class="stat-value">{{ (int) ($stats->en_priere ?? 0) }}</div>
                <div class="stat-label">En prière</div>
            </div>

            <div class="prayer-stat-card exaucee">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'check'])</div>
                <div class="stat-value">{{ (int) ($stats->exaucee ?? 0) }}</div>
                <div class="stat-label">Exaucées</div>
            </div>
        </div>

        <div class="new-prayer-action">
            <a href="{{ route('prayers.create') }}" class="btn btn-primary btn-large">
                @include('partials.icon', ['name' => 'prayer']) Nouvelle demande de prière
            </a>
        </div>

        <div class="prayers-list-section">
            <h2>Toutes mes demandes</h2>

            @if ($demandes->isNotEmpty())
                <div class="prayers-list">
                    @foreach ($demandes as $demande)
                        <div class="prayer-card">
                            <div class="prayer-card-header">
                                <div class="prayer-title-row">
                                    <h3>{{ $demande->sujet }}</h3>
                                    @switch($demande->statut)
                                        @case('en_attente')
                                            <span class="statut-badge en-attente">⏳ En attente</span>
                                            @break
                                        @case('en_priere')
                                            <span class="statut-badge en-priere">@include('partials.icon', ['name' => 'prayer']) En prière</span>
                                            @break
                                        @case('exaucee')
                                            <span class="statut-badge exaucee">@include('partials.icon', ['name' => 'check']) Exaucée</span>
                                            @break
                                        @default
                                            <span class="statut-badge">{{ $demande->statut }}</span>
                                    @endswitch
                                </div>
                                <div class="prayer-meta">
                                    <span class="prayer-date">📅 {{ $demande->created_at?->format('d/m/Y à H:i') }}</span>
                                    @if ($demande->est_anonyme)
                                        <span class="prayer-anonyme">🔒 Anonyme</span>
                                    @endif
                                </div>
                            </div>

                            <div class="prayer-card-body">
                                <p>{!! nl2br(e($demande->message)) !!}</p>
                            </div>

                            @if ($demande->updated_at && $demande->updated_at->ne($demande->created_at))
                                <div class="prayer-card-footer">
                                    <small>Dernière mise à jour: {{ $demande->updated_at->format('d/m/Y à H:i') }}</small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon icon-badge" style="margin:0 auto 1rem;">
                        @include('partials.icon', ['name' => 'prayer', 'class' => 'vop-icon-gold vop-icon-xl'])
                    </div>
                    <h3>Aucune demande de prière pour le moment</h3>
                    <p>Partagez vos besoins avec nous et laissez-nous prier pour vous.</p>
                    <a href="{{ route('prayers.create') }}" class="btn btn-primary">Envoyer une demande</a>
                </div>
            @endif
        </div>
    </div>
@endsection
