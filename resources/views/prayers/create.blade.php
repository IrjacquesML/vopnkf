@extends('layouts.app')

@section('title', 'Nouvelle demande de prière — VOP')

@section('content')
    <div class="container prayer-container">
        @include('partials.alerts')

        <div class="prayer-form-header">
            <h1>🙏 Nouvelle Demande de Prière</h1>
            <p>Partagez vos besoins avec nous. Notre équipe priera pour vous.</p>
        </div>

        <div class="prayer-form-section">
            <form method="POST" action="{{ route('prayers.store') }}" class="prayer-form">
                @csrf

                <div class="form-group">
                    <label for="sujet">Sujet de la prière *</label>
                    <input type="text" id="sujet" name="sujet" class="form-control" value="{{ old('sujet') }}" required placeholder="Ex: Guérison, guidance, famille...">
                </div>

                <div class="form-group">
                    <label for="message">Votre message *</label>
                    <textarea id="message" name="message" class="form-control" rows="6" required placeholder="Décrivez votre situation et vos besoins de prière...">{{ old('message') }}</textarea>
                    <small>Minimum 10 caractères</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="est_anonyme" value="1" @checked(old('est_anonyme'))>
                        <span>Demande anonyme (votre nom ne sera pas visible publiquement)</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Envoyer ma demande</button>
                    <a href="{{ route('prayers.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
