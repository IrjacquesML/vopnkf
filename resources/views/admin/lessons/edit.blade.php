@extends('layouts.admin')

@section('title', 'Modifier la Leçon — VOP Admin')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>✏️ Modifier la Leçon</h1>
            <div>
                <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-info">👁 Aperçu</a>
                <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">← Retour à la liste</a>
            </div>
        </div>

        <div class="admin-section">
            <h2>📝 Informations de la Leçon</h2>
            <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" class="admin-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="categorie_id">Catégorie *</label>
                    <select name="categorie_id" id="categorie_id" class="form-control" required>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie->id }}" @selected(old('categorie_id', $lesson->categorie_id) == $categorie->id)>
                                {{ $categorie->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="titre">Titre de la Leçon *</label>
                    <input type="text" name="titre" id="titre" class="form-control" required value="{{ old('titre', $lesson->titre) }}">
                </div>

                <div class="form-group">
                    <label for="ordre">Ordre dans la Catégorie *</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" required min="0" value="{{ old('ordre', $lesson->ordre) }}">
                </div>

                <div class="form-group">
                    <label for="contenu">Contenu de la Leçon *</label>
                    <textarea name="contenu" id="contenu" class="form-control" rows="15">{{ old('contenu', $lesson->contenu) }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Enregistrer les Modifications</button>
                </div>
            </form>
        </div>

        <div class="admin-section">
            <h2>❓ Questions de la Leçon ({{ $lesson->questions->count() }})</h2>

            @if ($lesson->questions->isNotEmpty())
                @php
                    $sansBonneReponse = $lesson->questions->filter(fn ($q) => $q->options->where('est_correcte', true)->isEmpty())->count();
                @endphp
                @if ($sansBonneReponse > 0)
                    <div class="alert alert-error" style="margin-bottom: 1rem;">
                        ⚠️ {{ $sansBonneReponse }} question(s) n'ont pas encore de bonne réponse cochée. Cochez « Correcte » sur la bonne option pour chaque question.
                    </div>
                @endif

                <form id="bulk-delete-questions"
                      method="POST"
                      action="{{ route('admin.lessons.questions.destroy-multiple', $lesson) }}"
                      onsubmit="return confirm('Supprimer les questions sélectionnées ?')">
                    @csrf
                    @method('DELETE')
                    <div class="questions-bulk-actions">
                        <label class="questions-select-all">
                            <input type="checkbox" id="select-all-questions">
                            Tout sélectionner
                        </label>
                        <button type="submit" class="btn btn-small btn-danger" id="bulk-delete-questions-btn" disabled>
                            🗑 Supprimer la sélection
                        </button>
                    </div>
                </form>

                <div class="questions-list">
                    @foreach ($lesson->questions as $index => $question)
                        <div class="question-item">
                            <div class="question-header">
                                <h4>
                                    <label class="question-select">
                                        <input type="checkbox"
                                               form="bulk-delete-questions"
                                               name="question_ids[]"
                                               value="{{ $question->id }}"
                                               class="question-checkbox">
                                        Question {{ $index + 1 }}
                                    </label>
                                    @if ($question->options->where('est_correcte', true)->isEmpty())
                                        <span style="color:#c0392b; font-size:.85rem;">— bonne réponse manquante</span>
                                    @endif
                                </h4>
                                <form action="{{ route('admin.lessons.questions.destroy', [$lesson, $question]) }}" method="POST" onsubmit="return confirm('Supprimer cette question ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-small btn-danger">🗑 Supprimer</button>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('admin.lessons.questions.update', [$lesson, $question]) }}" class="admin-form" style="margin-bottom: 15px;">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label>Texte de la question</label>
                                    <textarea name="question" class="form-control" rows="2" required>{{ old('question', $question->question) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Ordre</label>
                                    <input type="number" name="ordre" class="form-control" value="{{ old('ordre', $question->ordre) }}" min="0" required>
                                </div>
                                <button type="submit" class="btn btn-small btn-primary">Mettre à jour</button>
                            </form>

                            @if ($question->options->isNotEmpty())
                                <div class="options-preview">
                                    @foreach ($question->options as $option)
                                        <div class="option-preview {{ $option->est_correcte ? 'correct' : '' }}" style="margin-bottom: 10px;">
                                            <form method="POST" action="{{ route('admin.lessons.questions.options.update', [$lesson, $question, $option]) }}" class="admin-form" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="texte_option" class="form-control" value="{{ $option->texte_option }}" required style="flex:1; min-width:200px;">
                                                <input type="number" name="ordre" class="form-control" value="{{ $option->ordre }}" min="0" required style="width:80px;">
                                                <label style="display:flex; align-items:center; gap:5px;">
                                                    <input type="checkbox" name="est_correcte" value="1" @checked($option->est_correcte)>
                                                    Correcte
                                                </label>
                                                <button type="submit" class="btn btn-small btn-secondary">OK</button>
                                            </form>
                                            <form action="{{ route('admin.lessons.questions.options.destroy', [$lesson, $question, $option]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Supprimer cette option ?')">✖</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Aucune option de réponse</p>
                            @endif

                            <form method="POST" action="{{ route('admin.lessons.questions.options.store', [$lesson, $question]) }}" class="admin-form" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd;">
                                @csrf
                                <h5>Ajouter une option</h5>
                                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                    <input type="text" name="texte_option" class="form-control" placeholder="Texte de l'option" required style="flex:1; min-width:200px;">
                                    <input type="number" name="ordre" class="form-control" value="{{ $question->options->count() + 1 }}" min="0" required style="width:80px;">
                                    <label style="display:flex; align-items:center; gap:5px;">
                                        <input type="checkbox" name="est_correcte" value="1">
                                        Correcte
                                    </label>
                                    <button type="submit" class="btn btn-small btn-success">➕ Option</button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">Aucune question pour cette leçon.</p>
            @endif

            <form method="POST" action="{{ route('admin.lessons.questions.store', $lesson) }}" class="admin-form" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                @csrf
                <h3>➕ Ajouter une question</h3>
                <div class="form-group">
                    <label for="question">Texte de la question *</label>
                    <textarea name="question" id="question" class="form-control" rows="3" required placeholder="Saisissez la question...">{{ old('question') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="ordre_question">Ordre *</label>
                    <input type="number" name="ordre" id="ordre_question" class="form-control" value="{{ old('ordre', $lesson->questions->count() + 1) }}" min="0" required>
                </div>
                <button type="submit" class="btn btn-success">➕ Ajouter la Question</button>
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

    (function () {
        const selectAll = document.getElementById('select-all-questions');
        const checkboxes = document.querySelectorAll('.question-checkbox');
        const bulkBtn = document.getElementById('bulk-delete-questions-btn');

        if (!selectAll || !bulkBtn || checkboxes.length === 0) {
            return;
        }

        function syncBulkState() {
            const checkedCount = document.querySelectorAll('.question-checkbox:checked').length;
            bulkBtn.disabled = checkedCount === 0;
            selectAll.checked = checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            syncBulkState();
        });

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', syncBulkState);
        });

        syncBulkState();
    })();
</script>
@endpush
