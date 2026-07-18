<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VOP Admin')</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/spiritual-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vop-theme.css') }}">
    @stack('head')
</head>
<body class="admin-body">
    <header class="admin-topbar">
        <div class="admin-topbar-inner">
            <div class="admin-brand">
                <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo" class="admin-brand-logo">
                <div>
                    <strong>VOP Admin</strong>
                    <span>Administration</span>
                </div>
            </div>

            <div class="admin-topbar-actions">
                <span class="admin-topbar-user">
                    @include('partials.icon', ['name' => 'shield'])
                    {{ auth()->user()->prenom }} {{ auth()->user()->nom }}
                </span>
                <form action="{{ route('admin.logout') }}" method="POST" class="admin-logout-form">
                    @csrf
                    <button type="submit" class="btn-logout" title="Se déconnecter">
                        @include('partials.icon', ['name' => 'logout'])
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </div>

        <nav class="admin-subnav" aria-label="Navigation admin">
            <button type="button" class="nav-toggle admin-menu-toggle" id="adminNavToggle" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <div class="admin-subnav-links" id="adminNavMenu">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Utilisateurs</a>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Catégories</a>
                <a href="{{ route('admin.lessons.index') }}" class="{{ request()->routeIs('admin.lessons.*') ? 'active' : '' }}">Leçons</a>
                <a href="{{ route('admin.prayers.index') }}" class="{{ request()->routeIs('admin.prayers.*') ? 'active' : '' }}">Prières</a>
                <a href="{{ route('admin.reports.statistiques') }}" class="{{ request()->routeIs('admin.reports.statistiques') ? 'active' : '' }}">Rapports</a>
                <a href="{{ route('admin.reports.palmares') }}" class="{{ request()->routeIs('admin.reports.palmares') ? 'active' : '' }}">Palmarès</a>
                <a href="{{ route('admin.reports.certificat') }}" class="{{ request()->routeIs('admin.reports.certificat') ? 'active' : '' }}">Certificats</a>
                <a href="{{ route('admin.settings.certificate') }}" class="{{ request()->routeIs('admin.settings.certificate') ? 'active' : '' }}">Signataires</a>
                <a href="{{ route('admin.settings.bible') }}" class="{{ request()->routeIs('admin.settings.bible') ? 'active' : '' }}">API Bible</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="admin-logout-mobile">
                    @csrf
                    <button type="submit" class="btn-logout btn-logout-block">Déconnexion</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>

    <footer class="admin-footer">
        <p>&copy; {{ date('Y') }} VOP — Panneau d'Administration NKF | Développé par ML DATA +243 982 401 411</p>
    </footer>

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        (function () {
            const toggle = document.getElementById('adminNavToggle');
            const menu = document.getElementById('adminNavMenu');
            if (!toggle || !menu) return;
            toggle.addEventListener('click', function () {
                const open = menu.classList.toggle('open');
                toggle.classList.toggle('active', open);
                toggle.setAttribute('aria-expanded', String(open));
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
