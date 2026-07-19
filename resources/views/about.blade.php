@extends('layouts.guest')

@section('title', 'À propos — VOP')

@section('content')
    <div class="container about-container">
        <div class="about-card">
            <div class="logo-small">
                <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste">
                <h2>VOP</h2>
                <p>Études Bibliques par Correspondance</p>
            </div>

            <h1>À propos</h1>
            <p class="about-intro">
                L’équipe éditoriale et la direction qui portent les études bibliques VOP.
            </p>

            <section class="about-section">
                <h3>Équipe éditoriale</h3>
                <ul class="about-credits">
                    <li>
                        <span class="about-role">Rédacteur en chef</span>
                        <span class="about-name">Pr Kanyamusindi</span>
                    </li>
                    <li>
                        <span class="about-role">Correcteur</span>
                        <span class="about-name">Liliane</span>
                    </li>
                    <li>
                        <span class="about-role">Chargé de communication</span>
                        <span class="about-name">TSDR Saa-Mbili</span>
                    </li>
                </ul>
            </section>

            <section class="about-section">
                <h3>Sous la direction de</h3>
                <ul class="about-credits about-direction">
                    <li>
                        <span class="about-name">Pr Makeo Kirindera</span>
                        <span class="about-role">RELEGAL NKF</span>
                    </li>
                    <li>
                        <span class="about-name">Pr Cheleva Victor</span>
                        <span class="about-role">Secrétaire exécutif du NKF</span>
                    </li>
                    <li>
                        <span class="about-name">Mr Kasekwa Sylvain</span>
                        <span class="about-role">Trésorier NKF</span>
                    </li>
                </ul>
            </section>

            <p class="auth-link">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">← Retour à l'administration</a>
                    @else
                        <a href="{{ route('dashboard') }}">← Retour aux leçons</a>
                    @endif
                @else
                    <a href="{{ route('home') }}">← Retour à l'accueil</a>
                @endauth
            </p>
        </div>
    </div>
@endsection
