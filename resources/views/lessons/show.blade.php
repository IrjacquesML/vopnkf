@extends('layouts.app')

@section('title', $lecon->titre . ' — VOP')

@section('content')
    <div class="container lesson-container">
        @include('partials.alerts')

        <div class="lesson-header">
            <div class="breadcrumb">
                {{ $lecon->categorie->nom ?? '' }} / Leçon {{ $lecon->ordre }}
            </div>
            <div class="lesson-translate-bar">
                <p class="lesson-translate-hint">
                    @include('partials.icon', ['name' => 'dove', 'class' => 'vop-icon-gold'])
                    Utilisez le bouton <strong>Traduire la leçon</strong> (en bas à droite) pour lire dans une autre langue.
                </p>
            </div>
            <h1 data-traduire="titre" data-id="{{ $lecon->id }}">{{ $lecon->titre }}</h1>
        </div>

        <div class="lesson-content">
            <div class="content-text" data-traduire="contenu" data-id="{{ $lecon->id }}">
                {!! $lecon->contenu !!}
            </div>
        </div>

        @if ($questions->isNotEmpty())
            <div class="quiz-section">
                <h2 data-traduire="quiz_title" data-id="{{ $lecon->id }}">Interrogation</h2>
                <p data-traduire="quiz_intro" data-id="{{ $lecon->id }}">
                    Répondez aux questions suivantes pour valider votre compréhension de la leçon et déverrouiller la suivante.
                </p>

                <form method="POST" action="{{ route('quiz.store') }}" id="quizForm">
                    @csrf
                    <input type="hidden" name="lecon_id" value="{{ $lecon->id }}">

                    @foreach ($questions as $index => $question)
                        <div class="question-block">
                            <h3 data-traduire="q_label_{{ $question->id }}" data-id="{{ $question->id }}">Question {{ $index + 1 }}</h3>
                            <p class="question-text" data-traduire="question" data-id="{{ $question->id }}">{!! $question->question !!}</p>

                            <div class="options-list">
                                @foreach ($question->options as $option)
                                    <label class="option-label">
                                        <input type="radio" name="question_{{ $question->id }}" value="{{ $option->id }}" required>
                                        <span data-traduire="option" data-id="{{ $option->id }}">{{ $option->texte_option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary btn-block">Soumettre mes réponses</button>
                </form>
            </div>
        @else
            <div class="no-quiz">
                <p data-traduire="no_quiz" data-id="{{ $lecon->id }}">Cette leçon n'a pas d'interrogation.</p>
                @if (empty($dejaTerminee))
                    <form method="POST" action="{{ route('lessons.complete', $lecon->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">✓ Marquer comme terminée et déverrouiller la suivante</button>
                    </form>
                @else
                    <p class="lesson-status completed">✓ Leçon déjà terminée</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Retour au tableau de bord</a>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        window.VOP_TRADUCTION = {
            apiUrl: @json(route('api.traduire')),
            csrfToken: @json(csrf_token()),
            langue: @json(auth()->user()->langue_preferee ?? 'fr'),
            langues: @json(\App\Services\TranslationService::LANGUAGES),
        };
    </script>
    <script src="{{ asset('js/traduction.js') }}"></script>
@endpush
