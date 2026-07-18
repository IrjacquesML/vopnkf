<?php
require_once '../../includes/config.php';

// Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
if (est_connecte()) {
    redirect('../lessons/dashboard.php');
}

$erreurs = [];
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = clean_input($_POST['nom'] ?? '');
    $prenom = clean_input($_POST['prenom'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
    $pays = clean_input($_POST['pays'] ?? '');
    $province = clean_input($_POST['province'] ?? '');
    $ville = clean_input($_POST['ville'] ?? '');
    $adresse_complete = clean_input($_POST['adresse_complete'] ?? '');
    $telephone = clean_input($_POST['telephone'] ?? '');
    
    // Validation
    if (empty($nom)) {
        $erreurs[] = "Le nom est requis.";
    }
    if (empty($prenom)) {
        $erreurs[] = "Le prénom est requis.";
    }
    if (empty($email)) {
        $erreurs[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    }
    if (empty($mot_de_passe)) {
        $erreurs[] = "Le mot de passe est requis.";
    } elseif (strlen($mot_de_passe) < 6) {
        $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    if ($mot_de_passe !== $confirmer_mot_de_passe) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    }
    if (empty($pays)) {
        $erreurs[] = "Le pays est requis.";
    }
    if (empty($ville)) {
        $erreurs[] = "La ville est requise.";
    }
    
    // Si pas d'erreurs, créer l'utilisateur
    if (empty($erreurs)) {
        $conn = get_db_connection();
        
        // Vérifier si l'email existe déjà
        $query = "SELECT id FROM utilisateurs WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $erreurs[] = "Cet email est déjà utilisé.";
        } else {
            // Créer l'utilisateur
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $query = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, pays, province, ville, adresse_complete, telephone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssssss", $nom, $prenom, $email, $mot_de_passe_hash, $pays, $province, $ville, $adresse_complete, $telephone);
            
            if (mysqli_stmt_execute($stmt)) {
                $succes = "Inscription réussie! Vous pouvez maintenant vous connecter.";
                // Rediriger vers la page de connexion après 2 secondes
                header("refresh:2;url=connexion.php");
            } else {
                $erreurs[] = "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - VOP, Étude Biblique par Correspondance</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <div class="logo-small">
                <h2>VOP</h2>
                <p>Étude Biblique par Correspondance</p>
            </div>
            
            <h3>Créer un compte</h3>
            
            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?php echo h($erreur); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($succes): ?>
                <div class="alert alert-success">
                    <?php echo h($succes); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?php echo isset($nom) ? h($nom) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo isset($prenom) ? h($prenom) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo isset($telephone) ? h($telephone) : ''; ?>" placeholder="+243 XXX XXX XXX">
                    <small>Optionnel</small>
                </div>
                
                <div class="form-group">
                    <label for="pays">Pays *</label>
                    <select id="pays" name="pays" class="form-control" required onchange="toggleProvinceField()">
                        <option value="">-- Sélectionnez votre pays --</option>
                        <option value="RDC" <?php echo (isset($pays) && $pays == 'RDC') ? 'selected' : ''; ?>>République Démocratique du Congo (RDC)</option>
                        <option value="Congo-Brazzaville" <?php echo (isset($pays) && $pays == 'Congo-Brazzaville') ? 'selected' : ''; ?>>Congo-Brazzaville</option>
                        <option value="Angola" <?php echo (isset($pays) && $pays == 'Angola') ? 'selected' : ''; ?>>Angola</option>
                        <option value="Burundi" <?php echo (isset($pays) && $pays == 'Burundi') ? 'selected' : ''; ?>>Burundi</option>
                        <option value="Rwanda" <?php echo (isset($pays) && $pays == 'Rwanda') ? 'selected' : ''; ?>>Rwanda</option>
                        <option value="Ouganda" <?php echo (isset($pays) && $pays == 'Ouganda') ? 'selected' : ''; ?>>Ouganda</option>
                        <option value="Tanzanie" <?php echo (isset($pays) && $pays == 'Tanzanie') ? 'selected' : ''; ?>>Tanzanie</option>
                        <option value="Zambie" <?php echo (isset($pays) && $pays == 'Zambie') ? 'selected' : ''; ?>>Zambie</option>
                        <option value="Cameroun" <?php echo (isset($pays) && $pays == 'Cameroun') ? 'selected' : ''; ?>>Cameroun</option>
                        <option value="Gabon" <?php echo (isset($pays) && $pays == 'Gabon') ? 'selected' : ''; ?>>Gabon</option>
                        <option value="Centrafrique" <?php echo (isset($pays) && $pays == 'Centrafrique') ? 'selected' : ''; ?>>République Centrafricaine</option>
                        <option value="Soudan-du-Sud" <?php echo (isset($pays) && $pays == 'Soudan-du-Sud') ? 'selected' : ''; ?>>Soudan du Sud</option>
                        <option value="Kenya" <?php echo (isset($pays) && $pays == 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                        <option value="Bénin" <?php echo (isset($pays) && $pays == 'Bénin') ? 'selected' : ''; ?>>Bénin</option>
                        <option value="Burkina-Faso" <?php echo (isset($pays) && $pays == 'Burkina-Faso') ? 'selected' : ''; ?>>Burkina Faso</option>
                        <option value="Côte-d-Ivoire" <?php echo (isset($pays) && $pays == 'Côte-d-Ivoire') ? 'selected' : ''; ?>>Côte d'Ivoire</option>
                        <option value="Mali" <?php echo (isset($pays) && $pays == 'Mali') ? 'selected' : ''; ?>>Mali</option>
                        <option value="Niger" <?php echo (isset($pays) && $pays == 'Niger') ? 'selected' : ''; ?>>Niger</option>
                        <option value="Sénégal" <?php echo (isset($pays) && $pays == 'Sénégal') ? 'selected' : ''; ?>>Sénégal</option>
                        <option value="Togo" <?php echo (isset($pays) && $pays == 'Togo') ? 'selected' : ''; ?>>Togo</option>
                        <option value="France" <?php echo (isset($pays) && $pays == 'France') ? 'selected' : ''; ?>>France</option>
                        <option value="Belgique" <?php echo (isset($pays) && $pays == 'Belgique') ? 'selected' : ''; ?>>Belgique</option>
                        <option value="Suisse" <?php echo (isset($pays) && $pays == 'Suisse') ? 'selected' : ''; ?>>Suisse</option>
                        <option value="Canada" <?php echo (isset($pays) && $pays == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                        <option value="Autre" <?php echo (isset($pays) && $pays == 'Autre') ? 'selected' : ''; ?>>Autre</option>
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
                    <input type="text" id="ville" name="ville" class="form-control" value="<?php echo isset($ville) ? $ville : ''; ?>" required placeholder="Ex: Kinshasa, Lubumbashi, Brazzaville, Paris...">
                </div>
                
                <div class="form-group">
                    <label for="adresse_complete">Adresse complète</label>
                    <textarea id="adresse_complete" name="adresse_complete" class="form-control" rows="2" placeholder="Avenue, numéro, quartier... (Optionnel)"><?php echo isset($adresse_complete) ? h($adresse_complete) : ''; ?></textarea>
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
            
            <p class="auth-link">Vous avez déjà un compte? <a href="connexion.php">Se connecter</a></p>
            <p class="auth-link"><a href="../../index.php">Retour à l'accueil</a></p>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VOP</h3>
                    <p>Étude Biblique par Correspondance</p>
                    <p class="footer-description">Découvrez la vérité biblique et approfondissez votre foi à travers nos leçons interactives.</p>
                </div>
                
                 <div class="footer-section">
                    <p>📧 Email: contact@vop.org</p>
                    <p>📞 Téléphone: +243 961 420 201</p>
                    <p>📍 Adresse: Butembo/ Eglise Adventiste du 7e jour, RDC</p>
                </div>
                
                <div class="footer-section">
                    <h3>Liens Utiles</h3>
                    <ul class="footer-links">
                        <li><a href="inscription.php">S'inscrire</a></li>
                        <li><a href="connexion.php">Se connecter</a></li>
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Nous contacter</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Étude Biblique par Correspondance NKF | Développé par ML DATA +243 982 401 411</p>
                <p class="footer-verse">"Car la parole de Dieu est vivante et efficace" - Hébreux 4:12</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Base de données des provinces/régions par pays
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
                'Pays de la Loire', 'Provence-Alpes-Côte d\'Azur'
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
            'Rwanda': [
                'Kigali', 'Est', 'Nord', 'Ouest', 'Sud'
            ],
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
        
        // Fonction pour afficher/masquer et remplir le champ province selon le pays sélectionné
        function toggleProvinceField() {
            const paysSelect = document.getElementById('pays');
            const provinceField = document.getElementById('province-field');
            const provinceSelect = document.getElementById('province');
            const provinceHelp = document.getElementById('province-help');
            const selectedPays = paysSelect.value;
            const savedProvince = '<?php echo isset($province) ? $province : ''; ?>';
            
            // Réinitialiser le select
            provinceSelect.innerHTML = '<option value="">-- Sélectionnez votre province/région --</option>';
            
            // Si le pays a des provinces définies
            if (provincesParPays[selectedPays]) {
                provinceField.style.display = 'block';
                
                // Ajouter les provinces du pays sélectionné
                provincesParPays[selectedPays].forEach(function(province) {
                    const option = document.createElement('option');
                    option.value = province;
                    option.textContent = province;
                    
                    // Restaurer la sélection précédente si elle existe
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
        
        // Vérifier au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            toggleProvinceField();
        });
    </script>
</body>
</html>
