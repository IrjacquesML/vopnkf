@extends('layouts.admin')

@section('title', 'Ajouter une catégorie — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div>
                <div class="page-kicker">
                    @include('partials.icon', ['name' => 'bible', 'class' => 'vop-icon-gold'])
                    Contenu
                </div>
                <h1>Ajouter une catégorie</h1>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">← Retour</a>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="admin-form">
                @csrf

                <div class="form-group">
                    <label for="nom">Nom de la catégorie *</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom') }}" required maxlength="150" autofocus>
                    @error('nom') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" maxlength="2000">{{ old('description') }}</textarea>
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="ordre">Ordre d'affichage *</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" value="{{ old('ordre', $nextOrdre) }}" required min="0" max="9999">
                    <small>Les catégories sont affichées du plus petit au plus grand numéro.</small>
                    @error('ordre') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
