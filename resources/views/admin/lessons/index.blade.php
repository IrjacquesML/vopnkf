@extends('layouts.admin')

@section('title', 'Gestion des Leçons — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div>
                <h1>📚 Gestion des Leçons</h1>
                <p>Total: {{ $lessons->total() }} leçons</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Catégories</a>
            <a href="{{ route('admin.lessons.import') }}" class="btn btn-primary">📄 Importer leçon</a>
            <a href="{{ route('admin.lessons.import.questionnaire') }}" class="btn btn-primary">☑️ Importer QCM</a>
            <a href="{{ route('admin.lessons.create') }}" class="btn btn-success">➕ Ajouter une Leçon</a>
        </div>

        <div class="admin-toolbar">
            <form method="GET" action="{{ route('admin.lessons.index') }}" class="filter-form">
                <select name="categorie_id" class="filter-select">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" @selected($categorieId == $categorie->id)>{{ $categorie->nom }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" placeholder="Rechercher par titre..." value="{{ $search }}" class="search-input">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if ($categorieId || $search)
                    <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">✖ Effacer</a>
                @endif
            </form>
        </div>

        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Ordre</th>
                            <th>Questions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lessons as $lesson)
                            <tr>
                                <td>{{ $lesson->id }}</td>
                                <td>{{ $lesson->titre }}</td>
                                <td>{{ $lesson->categorie?->nom }}</td>
                                <td>{{ $lesson->ordre }}</td>
                                <td>—</td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-small btn-info">👁 Voir</a>
                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-small btn-primary">✏️ Modifier</a>
                                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette leçon ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-small btn-danger">🗑 Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Aucune leçon trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($lessons->hasPages())
                <div class="pagination-wrapper">
                    {{ $lessons->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
