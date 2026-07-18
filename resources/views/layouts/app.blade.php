<!DOCTYPE html>
<html lang="fr" @auth data-langue="{{ auth()->user()->langue_preferee ?? 'fr' }}" @endauth>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VOP — Études Bibliques par Correspondance')</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/spiritual-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vop-theme.css') }}">
    @stack('head')
</head>
<body class="app-body">
    <nav class="navbar user-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <div>
                    <h2>VOP</h2>
                    <span class="brand-tag">Voix de l'Espérance</span>
                </div>
            </div>

            <button type="button" class="nav-toggle" id="userNavToggle" aria-label="Ouvrir le menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

            <div class="nav-menu" id="userNavMenu">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    @include('partials.icon', ['name' => 'bible']) Mes Leçons
                </a>
                <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">
                    @include('partials.icon', ['name' => 'history']) Historique
                </a>
                <a href="{{ route('prayers.index') }}" class="{{ request()->routeIs('prayers.*') ? 'active' : '' }}">
                    @include('partials.icon', ['name' => 'prayer']) Prières
                </a>
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    @include('partials.icon', ['name' => 'user']) Profil
                </a>
                <div class="nav-user nav-user-drawer">
                    <span>@include('partials.icon', ['name' => 'user', 'class' => 'vop-icon-green']) {{ auth()->user()->prenom }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-small btn-secondary">
                            @include('partials.icon', ['name' => 'logout']) Déconnexion
                        </button>
                    </form>
                </div>
            </div>

            <div class="nav-user nav-user-desktop">
                <span>@include('partials.icon', ['name' => 'user', 'class' => 'vop-icon-green']) {{ auth()->user()->prenom }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-small btn-secondary">
                        @include('partials.icon', ['name' => 'logout']) Déconnexion
                    </button>
                </form>
            </div>
        </div>
        <div class="nav-overlay" id="userNavOverlay"></div>
    </nav>

    <main class="app-main">
        @yield('content')
    </main>

    @include('partials.footer')

    <nav class="mobile-bottom-nav" aria-label="Navigation rapide">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard', 'lessons.*') ? 'active' : '' }}">
            @include('partials.icon', ['name' => 'bible'])
            <span>Leçons</span>
        </a>
        <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">
            @include('partials.icon', ['name' => 'history'])
            <span>Historique</span>
        </a>
        <a href="{{ route('prayers.index') }}" class="{{ request()->routeIs('prayers.*') ? 'active' : '' }}">
            @include('partials.icon', ['name' => 'prayer'])
            <span>Prières</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            @include('partials.icon', ['name' => 'user'])
            <span>Profil</span>
        </a>
    </nav>

    <div id="verseModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" role="button" aria-label="Fermer">&times;</span>
            <h3 id="verseReference"></h3>
            <p id="verseText"></p>
        </div>
    </div>

    <script>
        const VERSE_API_PATH = @json(route('api.versets'));
    </script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        (function () {
            const toggle = document.getElementById('userNavToggle');
            const menu = document.getElementById('userNavMenu');
            const overlay = document.getElementById('userNavOverlay');
            if (!toggle || !menu) return;

            const close = () => {
                menu.classList.remove('active', 'open');
                toggle.classList.remove('active');
                overlay?.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            };

            const open = () => {
                menu.classList.add('active', 'open');
                toggle.classList.add('active');
                overlay?.classList.add('active');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            };

            toggle.addEventListener('click', () => {
                menu.classList.contains('active') ? close() : open();
            });
            overlay?.addEventListener('click', close);
            menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
        })();
    </script>
    @stack('scripts')
</body>
</html>
