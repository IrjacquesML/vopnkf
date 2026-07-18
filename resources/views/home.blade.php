<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOP — Études Bibliques par Correspondance</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/spiritual-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vop-theme.css') }}">
</head>
<body>
    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="logo">
                <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste" class="hero-logo">
                <h1>VOP</h1>
                <div class="hero-ornament">
                    @include('partials.icon', ['name' => 'cross', 'class' => 'vop-icon-gold vop-icon-lg'])
                </div>
                <p class="subtitle">Études Bibliques par Correspondance</p>
            </div>

            <div class="welcome-message">
                <h2>Tu as des problèmes qui t'empêchent de réaliser tes rêves&nbsp;?</h2>
                <p class="hope-message">Il y a de l'espoir en Jésus-Christ.</p>
                <p class="invitation">Connecte-toi pour commencer l'étude et découvrir la vérité biblique.</p>
            </div>

            <div class="action-buttons">
                <a href="{{ route('connexion') }}" class="btn btn-primary">
                    @include('partials.icon', ['name' => 'user']) Se Connecter
                </a>
                <a href="{{ route('inscription') }}" class="btn btn-secondary">
                    @include('partials.icon', ['name' => 'bible']) S'inscrire
                </a>
            </div>

            <div class="bible-verse verse-decoration">
                <p class="verse-text">«&nbsp;Venez à moi, vous tous qui êtes fatigués et chargés, et je vous donnerai du repos.&nbsp;»</p>
                <p class="verse-reference">— Matthieu 11:28</p>
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>
