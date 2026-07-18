@extends('layouts.guest')

@section('title', 'Connexion — VOP')

@section('content')
    <div class="container">
        <div class="auth-form">
            <div class="logo-small">
                <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste">
                <h2>VOP</h2>
                <p>Études Bibliques par Correspondance</p>
            </div>

            <h3>
                @include('partials.icon', ['name' => 'user', 'class' => 'vop-icon-green'])
                Se connecter
            </h3>

            @include('partials.alerts')

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    @include('partials.icon', ['name' => 'unlock']) Se connecter
                </button>
            </form>

            <p class="auth-link">Vous n'avez pas de compte&nbsp;? <a href="{{ route('inscription') }}">S'inscrire</a></p>
            <p class="auth-link"><a href="{{ route('home') }}">← Retour à l'accueil</a></p>
        </div>
    </div>
@endsection
