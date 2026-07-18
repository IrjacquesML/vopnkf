<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin — VOP</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/spiritual-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vop-theme.css') }}">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <div class="logo-small">
                <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste">
                <h2>VOP Admin</h2>
                <p>Panneau d'Administration</p>
            </div>

            <h3>
                @include('partials.icon', ['name' => 'shield', 'class' => 'vop-icon-green'])
                Connexion Administrateur
            </h3>

            @include('partials.alerts')

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    @include('partials.icon', ['name' => 'unlock']) Se connecter
                </button>
            </form>

            <p class="auth-link"><a href="{{ route('home') }}">← Retour au site</a></p>
        </div>
    </div>
</body>
</html>
