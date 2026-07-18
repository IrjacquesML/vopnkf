@extends('layouts.admin')

@section('title', 'Importer questionnaire QCM Word — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div>
                <h1>☑️ Importer un questionnaire QCM (Word)</h1>
                <p>Fichier type <code>QUESTIONNAIRE VOP.docx</code> — plusieurs leçons avec options A, B, C, D</p>
            </div>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">← Retour</a>
        </div>

        <div class="admin-section">
            <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                <strong>Format attendu :</strong>
                <pre style="white-space: pre-wrap; margin: .75rem 0 0; font-size: .9rem;">LECON 1 : LA PAROLE DE DIEU
1. Qu'est-ce que la Bible ? Luc 8 : 21
A. C'est l'amour de Dieu
B. c'est la parole de Dieu
C. c'est le chemin de la vie
D. c'est l'amour du prochain
LECON 2 : LA TRINITE
...</pre>
                <p style="margin: .75rem 0 0;">
                    Pour marquer la bonne réponse dans le Word, préfixez l'option d'une étoile :
                    <code>*B. c'est la parole de Dieu</code>
                    Sinon, cochez la bonne réponse après import dans l'édition de chaque leçon.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.lessons.import.questionnaire.store') }}" enctype="multipart/form-data" class="admin-form">
                @csrf

                <div class="form-group">
                    <label for="fichier">Fichier Word (.docx) *</label>
                    <input type="file" name="fichier" id="fichier" class="form-control" accept=".doc,.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                </div>

                <div class="form-group">
                    <label for="categorie_id">Catégorie cible *</label>
                    <select name="categorie_id" id="categorie_id" class="form-control" required>
                        <option value="">-- Sélectionnez une catégorie --</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie->id }}" @selected(old('categorie_id') == $categorie->id)>{{ $categorie->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="mode">Mode d'import *</label>
                    <select name="mode" id="mode" class="form-control" required>
                        <option value="attach" @selected(old('mode', 'attach') === 'attach')>
                            Associer aux leçons existantes (même numéro d'ordre) — ajouter les questions
                        </option>
                        <option value="replace" @selected(old('mode') === 'replace')>
                            Associer aux leçons existantes — remplacer toutes les questions
                        </option>
                        <option value="create" @selected(old('mode') === 'create')>
                            Créer de nouvelles leçons pour chaque LECON du fichier
                        </option>
                    </select>
                    <small>Le numéro « LECON 1 » correspond à l'ordre 1 dans la catégorie.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">📥 Importer le questionnaire</button>
                    <a href="{{ route('admin.lessons.import') }}" class="btn btn-secondary">Import leçon (texte)</a>
                </div>
            </form>
        </div>
    </div>
@endsection
