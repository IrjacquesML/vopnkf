@extends('layouts.admin')

@section('title', 'Gestion des Utilisateurs — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>👥 Gestion des Utilisateurs</h1>
            <p>Total: {{ $users->total() }} utilisateurs</p>
        </div>

        <div class="admin-toolbar">
            <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
                <input type="text" name="search" placeholder="Rechercher par nom, prénom ou email..." value="{{ $search }}" class="search-input">
                <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                @if ($search)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">✖ Effacer</a>
                @endif
            </form>
        </div>

        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Date Inscription</th>
                            <th>Dernière Connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->prenom }} {{ $user->nom }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                                <td>
                                    @if ($user->derniere_connexion)
                                        {{ $user->derniere_connexion->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">Jamais</span>
                                    @endif
                                </td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-small btn-info">👁 Voir</a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-small btn-danger">🗑 Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Aucun utilisateur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="pagination-wrapper">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
