@extends('layouts.admin')

@section('title', $user->prenom . ' ' . $user->nom . ' — VOP Admin')

@section('content')
    @php
        $leconsTerminees = $user->progressions->where('statut', 'termine')->count();
        $scoreMoyen = $user->progressions->where('statut', 'termine')->avg('score') ?? 0;
        $demandesPriere = $user->demandesPriere()->count();
    @endphp

    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>👤 Détails Utilisateur</h1>
            <div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">← Retour à la liste</a>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">🗑 Supprimer</button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>{{ $leconsTerminees }}</h3>
                    <p>Leçons terminées</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>{{ number_format($scoreMoyen, 1) }}%</h3>
                    <p>Score moyen</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🙏</div>
                <div class="stat-info">
                    <h3>{{ $demandesPriere }}</h3>
                    <p>Demandes de prière</p>
                </div>
            </div>
        </div>

        <div class="admin-section">
            <h2>Informations personnelles</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Nom:</strong> {{ $user->nom }}</div>
                <div class="info-item"><strong>Prénom:</strong> {{ $user->prenom }}</div>
                <div class="info-item"><strong>Email:</strong> {{ $user->email }}</div>
                <div class="info-item"><strong>Téléphone:</strong> {{ $user->telephone ?? '—' }}</div>
                <div class="info-item"><strong>Pays:</strong> {{ $user->pays ?? '—' }}</div>
                <div class="info-item"><strong>Province:</strong> {{ $user->province ?? '—' }}</div>
                <div class="info-item"><strong>Ville:</strong> {{ $user->ville ?? '—' }}</div>
                <div class="info-item"><strong>Langue:</strong> {{ $user->langue_preferee ?? 'fr' }}</div>
                <div class="info-item"><strong>Inscription:</strong> {{ $user->created_at?->format('d/m/Y H:i') }}</div>
                <div class="info-item"><strong>Dernière connexion:</strong> {{ $user->derniere_connexion?->format('d/m/Y H:i') ?? 'Jamais' }}</div>
            </div>
            @if ($user->adresse_complete)
                <p><strong>Adresse:</strong> {{ $user->adresse_complete }}</p>
            @endif
        </div>

        <div class="admin-section">
            <h2>Progression des leçons</h2>
            @if ($user->progressions->isNotEmpty())
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Leçon</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Score</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->progressions->sortByDesc('date_fin') as $progression)
                                <tr>
                                    <td>{{ $progression->lecon?->titre ?? '—' }}</td>
                                    <td>{{ $progression->lecon?->categorie?->nom ?? '—' }}</td>
                                    <td>{{ $progression->statut }}</td>
                                    <td>{{ $progression->score !== null ? number_format($progression->score, 0) . '%' : '—' }}</td>
                                    <td>{{ $progression->date_debut?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $progression->date_fin?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Aucune progression enregistrée.</p>
            @endif
        </div>
    </div>
@endsection
