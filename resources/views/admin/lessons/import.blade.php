@extends('layouts.admin')

@section('title', 'Importer une Leçon Word — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div>
                <h1>📄 Importer une leçon depuis Word</h1>
                <p>Fichier structuré type <code>VOP_LECON_1_fr.docx</code></p>
            </div>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">← Retour</a>
        </div>

        <div class="admin-section">
            <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                <strong>Format attendu :</strong>
                <ul style="margin: .5rem 0 0 1.2rem;">
                    <li>Titre : <em>LEÇON N° 1 : LA SAINTE BIBLE</em></li>
                    <li>Texte de la leçon (paragraphes)</li>
                    <li>Section questions : <em>Lisez votre Bible et répondez…</em></li>
                    <li>Questions du type : <em>Qu'est-ce que la Bible ? Luc 8 : 21</em></li>
                </ul>
                Les références bibliques du contenu seront cliquables après import.
            </div>

            <p style="margin-bottom: 1.5rem;">
                Pour un questionnaire à choix multiples (A, B, C, D) multi-leçons, utilisez plutôt
                <a href="{{ route('admin.lessons.import.questionnaire') }}">Importer QCM</a>.
            </p>

            <form method="POST" action="{{ route('admin.lessons.import.store') }}" enctype="multipart/form-data" class="admin-form">
                @csrf

                <div class="form-group">
                    <label for="fichier">Fichier Word (.docx) *</label>
                    <input type="file" name="fichier" id="fichier" class="form-control" accept=".doc,.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                </div>

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
                    <label for="ordre">Ordre (optionnel)</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" value="{{ old('ordre') }}" min="0" placeholder="Automatique si vide">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">📥 Importer la leçon</button>
                    <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
