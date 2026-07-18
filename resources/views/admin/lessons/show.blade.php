@extends('layouts.admin')

@section('title', $lesson->titre . ' — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>👁 Aperçu de la Leçon</h1>
            <div>
                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-primary">✏️ Modifier</a>
                <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">← Retour à la liste</a>
            </div>
        </div>

        <div class="admin-section">
            <div class="info-grid">
                <div class="info-item"><strong>ID:</strong> {{ $lesson->id }}</div>
                <div class="info-item"><strong>Catégorie:</strong> {{ $lesson->categorie?->nom }}</div>
                <div class="info-item"><strong>Ordre:</strong> {{ $lesson->ordre }}</div>
                <div class="info-item"><strong>Questions:</strong> {{ $lesson->questions->count() }}</div>
            </div>
            <h2>{{ $lesson->titre }}</h2>
        </div>

        <div class="admin-section">
            <h2>Contenu</h2>
            <div class="lesson-content">
                <div class="content-text">
                    {!! $lesson->contenu !!}
                </div>
            </div>
        </div>

        @if ($lesson->questions->isNotEmpty())
            <div class="admin-section">
                <h2>Questions ({{ $lesson->questions->count() }})</h2>
                @foreach ($lesson->questions as $index => $question)
                    <div class="question-item">
                        <h4>Question {{ $index + 1 }}</h4>
                        <p class="question-text">{{ $question->question }}</p>
                        @if ($question->options->isNotEmpty())
                            <div class="options-preview">
                                @foreach ($question->options as $option)
                                    <div class="option-preview {{ $option->est_correcte ? 'correct' : '' }}">
                                        <span>{{ $option->ordre }}.</span>
                                        {{ $option->texte_option }}
                                        @if ($option->est_correcte)
                                            <span class="badge badge-success">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
