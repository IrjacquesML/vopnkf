@extends('layouts.app')

@section('title', 'Mon Profil — VOP')

@section('content')
    @php
        $languesDisponibles = [
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'pt' => 'Português',
            'sw' => 'Kiswahili',
            'ln' => 'Lingala',
            'kg' => 'Kikongo',
            'ar' => 'العربية (Arabe)',
            'zh' => '中文 (Chinois)',
            'de' => 'Deutsch (Allemand)',
            'it' => 'Italiano (Italien)',
            'ru' => 'Русский (Russe)',
        ];
    @endphp

    <div class="container">
        <div class="dashboard-container">
            @include('partials.alerts')

            <div class="dashboard-header">
                <h1>Mon Profil</h1>
                <p>Gérez vos informations personnelles et votre photo de profil</p>
            </div>

            <div class="admin-section" style="margin-bottom: 30px;">
                <h2>Photo de profil</h2>
                <div style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
                    <div class="profile-photo-container">
                        @if ($user->photo_profil && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo_profil))
                            <img src="{{ asset('storage/' . $user->photo_profil) }}" alt="Photo de profil" class="profile-photo">
                        @else
                            <div class="profile-photo-placeholder">
                                <span style="font-size: 4em;">👤</span>
                            </div>
                        @endif
                    </div>

                    <div style="flex: 1;">
                        <form method="POST" action="{{ route('profile.updatePhoto') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="photo_profil">Choisir une nouvelle photo</label>
                                <input type="file" id="photo_profil" name="photo_profil" accept="image/*" required>
                                <small>Formats acceptés: JPG, PNG, GIF, WEBP (Max 5 MB)</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Télécharger la photo</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2>Informations personnelles</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="admin-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="{{ old('nom', $user->nom) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="{{ old('prenom', $user->prenom) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" value="{{ $user->email }}" disabled>
                        <small>L'email ne peut pas être modifié</small>
                    </div>

                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                    </div>

                    <div class="form-group">
                        <label for="pays">Pays</label>
                        <select id="pays" name="pays" class="form-control" onchange="toggleProvinceFieldProfile()">
                            <option value="">-- Sélectionnez votre pays --</option>
                            @foreach ([
                                'RDC' => 'République Démocratique du Congo (RDC)',
                                'Congo-Brazzaville' => 'Congo-Brazzaville',
                                'Angola' => 'Angola',
                                'Burundi' => 'Burundi',
                                'Rwanda' => 'Rwanda',
                                'Cameroun' => 'Cameroun',
                                'France' => 'France',
                                'Belgique' => 'Belgique',
                                'Canada' => 'Canada',
                                'Autre' => 'Autre',
                            ] as $code => $label)
                                <option value="{{ $code }}" @selected(old('pays', $user->pays) == $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="province-field-profile" style="display: none;">
                        <label for="province">Province/Région</label>
                        <select id="province" name="province" class="form-control">
                            <option value="">-- Sélectionnez votre province --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" class="form-control" value="{{ old('ville', $user->ville) }}">
                    </div>

                    <div class="form-group">
                        <label for="adresse_complete">Adresse complète</label>
                        <textarea id="adresse_complete" name="adresse_complete" class="form-control" rows="3">{{ old('adresse_complete', $user->adresse_complete) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="langue_preferee">🌍 Langue préférée</label>
                        <select id="langue_preferee" name="langue_preferee" class="form-control" required>
                            @foreach ($languesDisponibles as $code => $nom)
                                <option value="{{ $code }}" @selected(old('langue_preferee', $user->langue_preferee ?? 'fr') === $code)>{{ $nom }}</option>
                            @endforeach
                        </select>
                        <small>Le contenu des leçons sera automatiquement traduit dans votre langue</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const provincesParPays = {
        'RDC': ['Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Mai-Ndombe', 'Kasaï', 'Kasaï-Central', 'Kasaï-Oriental', 'Lomami', 'Sankuru', 'Maniema', 'Sud-Kivu', 'Nord-Kivu', 'Ituri', 'Haut-Uele', 'Tshopo', 'Bas-Uele', 'Nord-Ubangi', 'Mongala', 'Sud-Ubangi', 'Équateur', 'Tshuapa', 'Tanganyika', 'Haut-Lomami', 'Lualaba', 'Haut-Katanga'],
        'Congo-Brazzaville': ['Brazzaville', 'Pointe-Noire', 'Kouilou', 'Niari', 'Lékoumou', 'Bouenza', 'Pool', 'Plateaux', 'Cuvette', 'Cuvette-Ouest', 'Sangha', 'Likouala'],
        'Cameroun': ['Adamaoua', 'Centre', 'Est', 'Extrême-Nord', 'Littoral', 'Nord', 'Nord-Ouest', 'Ouest', 'Sud', 'Sud-Ouest'],
        'France': ['Île-de-France', 'Auvergne-Rhône-Alpes', 'Bourgogne-Franche-Comté', 'Bretagne', 'Centre-Val de Loire', 'Corse', 'Grand Est', 'Hauts-de-France', 'Normandie', 'Nouvelle-Aquitaine', 'Occitanie', 'Pays de la Loire', "Provence-Alpes-Côte d'Azur"],
        'Belgique': ['Bruxelles-Capitale', 'Flandre-Occidentale', 'Flandre-Orientale', 'Anvers', 'Limbourg', 'Brabant flamand', 'Brabant wallon', 'Hainaut', 'Liège', 'Luxembourg', 'Namur'],
        'Canada': ['Alberta', 'Colombie-Britannique', 'Manitoba', 'Nouveau-Brunswick', 'Terre-Neuve-et-Labrador', 'Nouvelle-Écosse', 'Ontario', 'Île-du-Prince-Édouard', 'Québec', 'Saskatchewan', 'Territoires du Nord-Ouest', 'Nunavut', 'Yukon'],
        'Burundi': ['Bubanza', 'Bujumbura Mairie', 'Bujumbura Rural', 'Bururi', 'Cankuzo', 'Cibitoke', 'Gitega', 'Karuzi', 'Kayanza', 'Kirundo', 'Makamba', 'Muramvya', 'Muyinga', 'Mwaro', 'Ngozi', 'Rumonge', 'Rutana', 'Ruyigi']
    };

    function toggleProvinceFieldProfile() {
        const paysSelect = document.getElementById('pays');
        const provinceField = document.getElementById('province-field-profile');
        const provinceSelect = document.getElementById('province');
        const selectedPays = paysSelect.value;
        const savedProvince = @json(old('province', $user->province ?? ''));

        provinceSelect.innerHTML = '<option value="">-- Sélectionnez votre province --</option>';

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
        } else {
            provinceField.style.display = 'none';
            provinceSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', toggleProvinceFieldProfile);
</script>
@endpush
