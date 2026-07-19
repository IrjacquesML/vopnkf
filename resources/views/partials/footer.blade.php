<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>VOP</h3>
                <p>Études Bibliques par Correspondance</p>
                <p class="footer-description">Découvrez la vérité biblique et approfondissez votre foi à travers nos leçons interactives.</p>
                <div class="hero-ornament" style="justify-content:flex-start; margin:1rem 0 0;">
                    @include('partials.icon', ['name' => 'dove', 'class' => 'vop-icon-gold'])
                </div>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <div class="footer-contact-item">
                    @include('partials.icon', ['name' => 'mail'])
                    <span>contact@vop.org</span>
                </div>
                <div class="footer-contact-item">
                    @include('partials.icon', ['name' => 'phone'])
                    <span>+243 961 420 201</span>
                </div>
                <div class="footer-contact-item">
                    @include('partials.icon', ['name' => 'map'])
                    <span>Butembo / Église Adventiste du 7<sup>e</sup> jour, RDC</span>
                </div>
            </div>

            <div class="footer-section">
                <h3>Liens utiles</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('about') }}">À propos</a></li>
                    @auth
                        <li><a href="{{ route('dashboard') }}">Mes Leçons</a></li>
                        <li><a href="{{ route('history.index') }}">Mon Historique</a></li>
                        <li><a href="{{ route('prayers.index') }}">Mes Prières</a></li>
                        <li><a href="{{ route('prayers.create') }}">Demande de Prière</a></li>
                    @else
                        <li><a href="{{ route('inscription') }}">S'inscrire</a></li>
                        <li><a href="{{ route('connexion') }}">Se connecter</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} VOP — Études Bibliques par Correspondance NKF | Développé par ML DATA +243 982 401 411</p>
            <p class="footer-verse">«&nbsp;Car la parole de Dieu est vivante et efficace&nbsp;» — Hébreux 4:12</p>
        </div>
    </div>
</footer>
