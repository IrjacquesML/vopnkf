@extends('layouts.admin')

@section('title', 'Catégories — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div>
                <div class="page-kicker">
                    @include('partials.icon', ['name' => 'bible', 'class' => 'vop-icon-gold'])
                    Contenu
                </div>
                <h1>Gestion des catégories</h1>
                <p>{{ $categories->count() }} catégorie(s) — organisez les leçons par thème</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                @include('partials.icon', ['name' => 'bible']) Ajouter une catégorie
            </a>
        </div>

        <div class="admin-section">
            @if ($categories->isNotEmpty())
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Leçons</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td><strong>{{ $category->ordre }}</strong></td>
                                    <td>{{ $category->nom }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($category->description ?: '—', 80) }}</td>
                                    <td>
                                        <span class="badge {{ $category->lecons_count > 0 ? 'badge-success' : 'badge-warning' }}">
                                            {{ $category->lecons_count }}
                                        </span>
                                    </td>
                                    <td class="admin-actions">
                                        <a href="{{ route('admin.lessons.index', ['categorie_id' => $category->id]) }}" class="btn btn-small btn-info">Leçons</a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-small btn-primary">Modifier</a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer la catégorie « {{ $category->nom }} » ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-small btn-danger" @disabled($category->lecons_count > 0) title="{{ $category->lecons_count > 0 ? 'Supprimez d\'abord les leçons liées' : 'Supprimer' }}">
                                                Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Aucune catégorie pour le moment. Créez-en une pour commencer à organiser les leçons.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Créer la première catégorie</a>
            @endif
        </div>
    </div>
@endsection
