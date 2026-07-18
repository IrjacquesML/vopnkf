@extends('layouts.guest')

@section('title', 'Inscription — VOP')

@section('content')
    <div class="container">
        <div class="auth-form">
            <div class="logo-small">
                <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste">
                <h2>VOP</h2>
                <p>Études Bibliques par Correspondance</p>
            </div>

            <h3>
                @include('partials.icon', ['name' => 'bible', 'class' => 'vop-icon-green'])
                Créer un compte
            </h3>

            @include('partials.alerts')

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}" placeholder="+243 XXX XXX XXX">
                    <small>Optionnel</small>
                </div>

                <div class="form-group">
                    <label for="pays">Pays *</label>
                    <select id="pays" name="pays" class="form-control" required onchange="toggleProvinceField()">
                        <option value="">-- Sélectionnez votre pays --</option>
                        @foreach ([
                            'RDC' => 'République Démocratique du Congo (RDC)',
                            'Congo-Brazzaville' => 'Congo-Brazzaville',
                            'Angola' => 'Angola',
                            'Burundi' => 'Burundi',
                            'Rwanda' => 'Rwanda',
                            'Ouganda' => 'Ouganda',
                            'Tanzanie' => 'Tanzanie',
                            'Zambie' => 'Zambie',
                            'Cameroun' => 'Cameroun',
                            'Gabon' => 'Gabon',
                            'Centrafrique' => 'République Centrafricaine',
                            'Soudan-du-Sud' => 'Soudan du Sud',
                            'Kenya' => 'Kenya',
                            'Bénin' => 'Bénin',
                            'Burkina-Faso' => 'Burkina Faso',
                            'Côte-d-Ivoire' => "Côte d'Ivoire",
                            'Mali' => 'Mali',
                            'Niger' => 'Niger',
                            'Sénégal' => 'Sénégal',
                            'Togo' => 'Togo',
                            'France' => 'France',
                            'Belgique' => 'Belgique',
                            'Suisse' => 'Suisse',
                            'Canada' => 'Canada',
                            'Autre' => 'Autre',
                        ] as $code => $label)
                            <option value="{{ $code }}" @selected(old('pays') == $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="province-field" style="display: none;">
                    <label for="province">Province/Région</label>
                    <select id="province" name="province" class="form-control">
                        <option value="">-- Sélectionnez votre province/région --</option>
                    </select>
                    <small id="province-help">Sélectionnez d'abord un pays</small>
                </div>

                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" class="form-control" value="{{ old('ville') }}" required placeholder="Ex: Kinshasa, Lubumbashi, Brazzaville, Paris...">
                </div>

                <div class="form-group">
                    <label for="adresse_complete">Adresse complète</label>
                    <textarea id="adresse_complete" name="adresse_complete" class="form-control" rows="2" placeholder="Avenue, numéro, quartier... (Optionnel)">{{ old('adresse_complete') }}</textarea>
                    <small>Optionnel - Précisez votre adresse si vous le souhaitez</small>
                </div>

                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe *</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                    <small>Au moins 6 caractères</small>
                </div>

                <div class="form-group">
                    <label for="confirmer_mot_de_passe">Confirmer le mot de passe *</label>
                    <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
            </form>

            <p class="auth-link">Vous avez déjà un compte? <a href="{{ route('connexion') }}">Se connecter</a></p>
            <p class="auth-link"><a href="{{ route('home') }}">Retour à l'accueil</a></p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const provincesParPays = {
        'RDC': [
            'Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Mai-Ndombe',
            'Kasaï', 'Kasaï-Central', 'Kasaï-Oriental', 'Lomami', 'Sankuru',
            'Maniema', 'Sud-Kivu', 'Nord-Kivu', 'Ituri', 'Haut-Uele', 'Tshopo',
            'Bas-Uele', 'Nord-Ubangi', 'Mongala', 'Sud-Ubangi', 'Équateur',
            'Tshuapa', 'Tanganyika', 'Haut-Lomami', 'Lualaba', 'Haut-Katanga'
        ],
        'Congo-Brazzaville': [
            'Brazzaville', 'Pointe-Noire', 'Kouilou', 'Niari', 'Lékoumou',
            'Bouenza', 'Pool', 'Plateaux', 'Cuvette', 'Cuvette-Ouest',
            'Sangha', 'Likouala'
        ],
        'Cameroun': [
            'Adamaoua', 'Centre', 'Est', 'Extrême-Nord', 'Littoral',
            'Nord', 'Nord-Ouest', 'Ouest', 'Sud', 'Sud-Ouest'
        ],
        'France': [
            'Île-de-France', 'Auvergne-Rhône-Alpes', 'Bourgogne-Franche-Comté',
            'Bretagne', 'Centre-Val de Loire', 'Corse', 'Grand Est',
            'Hauts-de-France', 'Normandie', 'Nouvelle-Aquitaine', 'Occitanie',
            'Pays de la Loire', "Provence-Alpes-Côte d'Azur"
        ],
        'Belgique': [
            'Bruxelles-Capitale', 'Flandre-Occidentale', 'Flandre-Orientale',
            'Anvers', 'Limbourg', 'Brabant flamand', 'Brabant wallon',
            'Hainaut', 'Liège', 'Luxembourg', 'Namur'
        ],
        'Canada': [
            'Alberta', 'Colombie-Britannique', 'Manitoba', 'Nouveau-Brunswick',
            'Terre-Neuve-et-Labrador', 'Nouvelle-Écosse', 'Ontario',
            'Île-du-Prince-Édouard', 'Québec', 'Saskatchewan',
            'Territoires du Nord-Ouest', 'Nunavut', 'Yukon'
        ],
        'Gabon': [
            'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié',
            'Nyanga', 'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'
        ],
        'Burundi': [
            'Bubanza', 'Bujumbura Mairie', 'Bujumbura Rural', 'Bururi',
            'Cankuzo', 'Cibitoke', 'Gitega', 'Karuzi', 'Kayanza',
            'Kirundo', 'Makamba', 'Muramvya', 'Muyinga', 'Mwaro',
            'Ngozi', 'Rumonge', 'Rutana', 'Ruyigi'
        ],
        'Rwanda': ['Kigali', 'Est', 'Nord', 'Ouest', 'Sud'],
        'Côte-d-Ivoire': [
            'Abidjan', 'Bas-Sassandra', 'Comoé', 'Denguélé', 'Gôh-Djiboua',
            'Lacs', 'Lagunes', 'Montagnes', 'Sassandra-Marahoué', 'Savanes',
            'Vallée du Bandama', 'Woroba', 'Yamoussoukro', 'Zanzan'
        ],
        'Sénégal': [
            'Dakar', 'Diourbel', 'Fatick', 'Kaffrine', 'Kaolack',
            'Kédougou', 'Kolda', 'Louga', 'Matam', 'Saint-Louis',
            'Sédhiou', 'Tambacounda', 'Thiès', 'Ziguinchor'
        ]
    };

    function toggleProvinceField() {
        const paysSelect = document.getElementById('pays');
        const provinceField = document.getElementById('province-field');
        const provinceSelect = document.getElementById('province');
        const provinceHelp = document.getElementById('province-help');
        const selectedPays = paysSelect.value;
        const savedProvince = @json(old('province', ''));

        provinceSelect.innerHTML = '<option value="">-- Sélectionnez votre province/région --</option>';

        if (provincesParPays[selectedPays]) {
            provinceField.style.display = 'block';

            provincesParPays[selectedPays].forEach(function(province) {
                const option = document.createElement('option');
                option.value = province;
                option.textContent = province;
                if (savedProvince && province === savedProvince) {
                    option.selected = true;
                }
                provinceSelect.appendChild(option);
            });

            provinceHelp.textContent = 'Optionnel';
        } else {
            provinceField.style.display = 'none';
            provinceSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', toggleProvinceField);
</script>
@endpush
