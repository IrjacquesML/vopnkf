@extends('layouts.admin')

@section('title', 'Tableau de bord Admin — VOP')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'cross', 'class' => 'vop-icon-gold'])
                Administration
            </div>
            <h1>Tableau de bord</h1>
            <p>Vue d'ensemble de la plateforme VOP</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon icon-badge">
                    @include('partials.icon', ['name' => 'users'])
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['users_count'] }}</h3>
                    <p>Total Utilisateurs</p>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon icon-badge">
                    @include('partials.icon', ['name' => 'bible'])
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['lessons_count'] }}</h3>
                    <p>Leçons Disponibles</p>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon icon-badge">
                    @include('partials.icon', ['name' => 'check'])
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['completed_progressions_count'] }}</h3>
                    <p>Leçons Complétées</p>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon icon-badge">
                    @include('partials.icon', ['name' => 'prayer'])
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['prayers_pending_count'] }}</h3>
                    <p>Prières en attente</p>
                </div>
            </div>
        </div>

        <div class="admin-sections">
            <div class="admin-section">
                <h2>
                    @include('partials.icon', ['name' => 'users', 'class' => 'vop-icon-green'])
                    Derniers utilisateurs inscrits
                </h2>
                @if ($recentUsers->isNotEmpty())
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentUsers as $user)
                                    <tr>
                                        <td>{{ $user->prenom }} {{ $user->nom }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-small btn-info">Voir</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Aucun utilisateur inscrit.</p>
                @endif
            </div>

            <div class="admin-section">
                <h2>
                    @include('partials.icon', ['name' => 'prayer', 'class' => 'vop-icon-gold'])
                    Dernières demandes de prière
                </h2>
                @if ($recentPrayers->isNotEmpty())
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Sujet</th>
                                    <th>Utilisateur</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPrayers as $prayer)
                                    <tr>
                                        <td>{{ $prayer->sujet }}</td>
                                        <td>
                                            @if ($prayer->est_anonyme)
                                                Anonyme
                                            @else
                                                {{ $prayer->user?->prenom }} {{ $prayer->user?->nom }}
                                            @endif
                                        </td>
                                        <td>{{ $prayer->statut }}</td>
                                        <td>{{ $prayer->created_at?->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.prayers.show', $prayer) }}" class="btn btn-small btn-info">Voir</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Aucune demande de prière.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
