@extends('layouts.admin')

@section('title', 'Gestion des Prières — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>🙏 Gestion des Demandes de Prière</h1>
            <p>Total: {{ $prayers->total() }} demandes</p>
        </div>

        <div class="admin-toolbar">
            <form method="GET" action="{{ route('admin.prayers.index') }}" class="filter-form">
                <select name="statut" class="filter-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" @selected($statut === 'en_attente')>En attente</option>
                    <option value="en_priere" @selected($statut === 'en_priere')>En prière</option>
                    <option value="exaucee" @selected($statut === 'exaucee')>Exaucée</option>
                </select>
                <input type="text" name="search" placeholder="Rechercher..." value="{{ $search }}" class="search-input">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if ($statut || $search)
                    <a href="{{ route('admin.prayers.index') }}" class="btn btn-secondary">✖ Effacer</a>
                @endif
            </form>
        </div>

        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sujet</th>
                            <th>Utilisateur</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($prayers as $prayer)
                            <tr>
                                <td>{{ $prayer->id }}</td>
                                <td>{{ $prayer->sujet }}</td>
                                <td>
                                    @if ($prayer->est_anonyme)
                                        Anonyme
                                    @else
                                        {{ $prayer->user?->prenom }} {{ $prayer->user?->nom }}
                                    @endif
                                </td>
                                <td>{{ $prayer->statut }}</td>
                                <td>{{ $prayer->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.prayers.show', $prayer) }}" class="btn btn-small btn-info">👁 Voir</a>
                                    <form action="{{ route('admin.prayers.destroy', $prayer) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette demande ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-small btn-danger">🗑</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Aucune demande trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($prayers->hasPages())
                <div class="pagination-wrapper">
                    {{ $prayers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
