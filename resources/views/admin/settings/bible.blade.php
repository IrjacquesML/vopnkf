@extends('layouts.admin')

@section('title', 'Paramètres API Bible — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>📖 Paramètres API Bible</h1>
            <p>Configuration de l'intégration scripture.api.bible</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card {{ $apiKeySet ? 'success' : 'warning' }}">
                <div class="stat-icon">{{ $apiKeySet ? '✅' : '⚠️' }}</div>
                <div class="stat-info">
                    <h3>{{ $apiKeySet ? 'Configurée' : 'Non configurée' }}</h3>
                    <p>Clé API Bible</p>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">💾</div>
                <div class="stat-info">
                    <h3>{{ $versetsCacheCount }}</h3>
                    <p>Versets en cache</p>
                </div>
            </div>
        </div>

        <div class="admin-section">
            <h2>Configuration actuelle</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>URL API:</strong>
                    <code>{{ $apiUrl }}</code>
                </div>
                <div class="info-item">
                    <strong>ID Bible LSG:</strong>
                    <code>{{ $lsgId ?: 'Non défini' }}</code>
                </div>
                <div class="info-item">
                    <strong>Durée cache (jours):</strong> {{ $cacheDays }}
                </div>
                <div class="info-item">
                    <strong>Clé API:</strong>
                    @if ($apiKeySet)
                        <span class="badge badge-success">Définie dans .env (BIBLE_API_KEY)</span>
                    @else
                        <span class="badge badge-warning">Non définie — ajoutez BIBLE_API_KEY dans .env</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-section">
            <h2>Informations</h2>
            <p>Les versets bibliques sont récupérés selon la priorité suivante :</p>
            <ol style="margin: 15px 0 15px 20px; line-height: 1.8;">
                <li>Cache local (table <code>versets</code>) — {{ $versetsCacheCount }} entrée(s)</li>
                <li>scripture.api.bible — Louis Segond 1910 (clé gratuite requise)</li>
                <li>bible-api.com — fallback sans clé (anglais WEB)</li>
            </ol>
            <p class="text-muted">
                Pour configurer l'API, définissez les variables <code>BIBLE_API_KEY</code>, <code>BIBLE_API_URL</code> et <code>BIBLE_LSG_ID</code> dans votre fichier <code>.env</code>.
            </p>
        </div>
    </div>
@endsection
