@extends('layouts.admin')

@section('title', 'Ajouter une Leçon — VOP Admin')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>➕ Ajouter une Leçon</h1>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">← Retour à la liste</a>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.lessons.store') }}" class="admin-form">
                @csrf

                <div class="form-group">
                    <label for="categorie_id">Catégorie *</label>
                    <select name="categorie_id" id="categorie_id" class="form-control" required>
                        <option value="">-- Sélectionnez une catégorie --</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie->id }}" @selected(old('categorie_id') == $categorie->id)>{{ $categorie->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="titre">Titre de la Leçon *</label>
                    <input type="text" name="titre" id="titre" class="form-control" value="{{ old('titre') }}" required>
                </div>

                <div class="form-group">
                    <label for="ordre">Ordre dans la Catégorie *</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" value="{{ old('ordre', 1) }}" required min="0">
                </div>

                <div class="form-group">
                    <label for="contenu">Contenu de la Leçon *</label>
                    <textarea name="contenu" id="contenu" class="form-control" rows="15">{{ old('contenu') }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Créer la Leçon</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    tinymce.init({
        selector: '#contenu',
        height: 400,
        menubar: false,
        plugins: 'lists link table code',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
        language: 'fr_FR',
        content_style: 'body { font-family: Lato, sans-serif; font-size: 14px; }'
    });
</script>
@endpush
