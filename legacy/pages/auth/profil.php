<?php
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('connexion.php');
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$erreurs = [];
$succes = '';

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = get_db_connection();
    
    // Mise à jour de la photo de profil
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo_profil'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        // Validation du fichier
        if (!in_array($file['type'], $allowed_types)) {
            $erreurs[] = "Format de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP.";
        } elseif ($file['size'] > $max_size) {
            $erreurs[] = "La taille du fichier ne doit pas dépasser 5 MB.";
        } else {
            // Créer le dossier uploads s'il n'existe pas
            $upload_dir = '../../uploads/profils/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $utilisateur_id . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Déplacer le fichier téléchargé
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Supprimer l'ancienne photo si elle existe
                $query = "SELECT photo_profil FROM utilisateurs WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $utilisateur_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                
                if ($user['photo_profil'] && file_exists('../../' . $user['photo_profil'])) {
                    unlink('../../' . $user['photo_profil']);
                }
                
                // Mettre à jour la base de données
                $photo_path = 'uploads/profils/' . $filename;
                $query = "UPDATE utilisateurs SET photo_profil = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $photo_path, $utilisateur_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $succes = "Photo de profil mise à jour avec succès!";
                    $_SESSION['photo_profil'] = $photo_path;
                } else {
                    $erreurs[] = "Erreur lors de la mise à jour de la photo.";
                }
            } else {
                $erreurs[] = "Erreur lors du téléchargement du fichier.";
            }
        }
    }
    
    // Mise à jour des informations du profil
    if (isset($_POST['update_info'])) {
        $nom = clean_input($_POST['nom'] ?? '');
        $prenom = clean_input($_POST['prenom'] ?? '');
        $telephone = clean_input($_POST['telephone'] ?? '');
        $pays = clean_input($_POST['pays'] ?? '');
        $province = clean_input($_POST['province'] ?? '');
        $ville = clean_input($_POST['ville'] ?? '');
        $adresse_complete = clean_input($_POST['adresse_complete'] ?? '');
        $langue_preferee = clean_input($_POST['langue_preferee'] ?? 'fr');
        
        if (empty($nom) || empty($prenom)) {
            $erreurs[] = "Le nom et le prénom sont requis.";
        } else {
            $query = "UPDATE utilisateurs SET nom = ?, prenom = ?, telephone = ?, pays = ?, province = ?, ville = ?, adresse_complete = ?, langue_preferee = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssssssi", $nom, $prenom, $telephone, $pays, $province, $ville, $adresse_complete, $langue_preferee, $utilisateur_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $succes = "Informations mises à jour avec succès!";
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['langue_preferee'] = $langue_preferee;
            } else {
                $erreurs[] = "Erreur lors de la mise à jour des informations.";
            }
        }
    }
    
    mysqli_close($conn);
}

// Récupérer les informations de l'utilisateur
$conn = get_db_connection();
$query = "SELECT * FROM utilisateurs WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $utilisateur_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$utilisateur = mysqli_fetch_assoc($result);
mysqli_close($conn);

// Langues disponibles
$langues_disponibles = [
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
    'ru' => 'Русский (Russe)'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - VOP</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../lessons/dashboard.php">VOP</a>
            </div>
            <div class="nav-menu">
                <a href="../lessons/dashboard.php">Tableau de bord</a>
                <a href="../history/historique.php">Historique</a>
                <a href="../prayers/demande_priere.php">Prière</a>
                <a href="profil.php" class="active">Profil</a>
            </div>
            <div class="nav-user">
                <span>👤 <?php echo h($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></span>
                <a href="deconnexion.php" class="btn btn-small btn-secondary">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Mon Profil</h1>
                <p>Gérez vos informations personnelles et votre photo de profil</p>
            </div>

            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?php echo $erreur; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($succes): ?>
                <div class="alert alert-success">
                    <?php echo $succes; ?>
                </div>
            <?php endif; ?>

            <!-- Section Photo de profil -->
            <div class="admin-section" style="margin-bottom: 30px;">
                <h2>Photo de profil</h2>
                <div style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
                    <div class="profile-photo-container">
                        <?php if ($utilisateur['photo_profil'] && file_exists('../../' . $utilisateur['photo_profil'])): ?>
                            <img src="../../<?php echo $utilisateur['photo_profil']; ?>" alt="Photo de profil" class="profile-photo">
                        <?php else: ?>
                            <div class="profile-photo-placeholder">
                                <span style="font-size: 4em;">👤</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <form method="POST" enctype="multipart/form-data">
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

            <!-- Section Informations personnelles -->
            <div class="admin-section">
                <h2>Informations personnelles</h2>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="update_info" value="1">
                    
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="<?php echo $utilisateur['nom']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo h($utilisateur['prenom']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" value="<?php echo h($utilisateur['email']); ?>" disabled>
                        <small>L'email ne peut pas être modifié</small>
                    </div>

                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" value="<?php echo h($utilisateur['telephone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="pays">Pays</label>
                        <select id="pays" name="pays" class="form-control" onchange="toggleProvinceFieldProfile()">
                            <option value="">-- Sélectionnez votre pays --</option>
                            <option value="RDC" <?php echo ($utilisateur['pays'] == 'RDC') ? 'selected' : ''; ?>>République Démocratique du Congo (RDC)</option>
                            <option value="Congo-Brazzaville" <?php echo ($utilisateur['pays'] == 'Congo-Brazzaville') ? 'selected' : ''; ?>>Congo-Brazzaville</option>
                            <option value="Angola" <?php echo ($utilisateur['pays'] == 'Angola') ? 'selected' : ''; ?>>Angola</option>
                            <option value="Burundi" <?php echo ($utilisateur['pays'] == 'Burundi') ? 'selected' : ''; ?>>Burundi</option>
                            <option value="Rwanda" <?php echo ($utilisateur['pays'] == 'Rwanda') ? 'selected' : ''; ?>>Rwanda</option>
                            <option value="Cameroun" <?php echo ($utilisateur['pays'] == 'Cameroun') ? 'selected' : ''; ?>>Cameroun</option>
                            <option value="France" <?php echo ($utilisateur['pays'] == 'France') ? 'selected' : ''; ?>>France</option>
                            <option value="Belgique" <?php echo ($utilisateur['pays'] == 'Belgique') ? 'selected' : ''; ?>>Belgique</option>
                            <option value="Canada" <?php echo ($utilisateur['pays'] == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                            <option value="Autre" <?php echo ($utilisateur['pays'] == 'Autre') ? 'selected' : ''; ?>>Autre</option>
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
                        <input type="text" id="ville" name="ville" class="form-control" value="<?php echo h($utilisateur['ville'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="adresse_complete">Adresse complète</label>
                        <textarea id="adresse_complete" name="adresse_complete" class="form-control" rows="3"><?php echo h($utilisateur['adresse_complete'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="langue_preferee">🌍 Langue préférée</label>
                        <select id="langue_preferee" name="langue_preferee" class="form-control">
                            <?php foreach ($langues_disponibles as $code => $nom): ?>
                                <option value="<?php echo h($code); ?>" <?php echo (($utilisateur['langue_preferee'] ?? '') === $code) ? 'selected' : ''; ?>>
                                    <?php echo h($nom); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Le contenu des leçons sera automatiquement traduit dans votre langue</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="../lessons/dashboard.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Base de données des provinces/régions par pays (même que inscription.php)
        const provincesParPays = {
            'RDC': ['Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Mai-Ndombe', 'Kasaï', 'Kasaï-Central', 'Kasaï-Oriental', 'Lomami', 'Sankuru', 'Maniema', 'Sud-Kivu', 'Nord-Kivu', 'Ituri', 'Haut-Uele', 'Tshopo', 'Bas-Uele', 'Nord-Ubangi', 'Mongala', 'Sud-Ubangi', 'Équateur', 'Tshuapa', 'Tanganyika', 'Haut-Lomami', 'Lualaba', 'Haut-Katanga'],
            'Congo-Brazzaville': ['Brazzaville', 'Pointe-Noire', 'Kouilou', 'Niari', 'Lékoumou', 'Bouenza', 'Pool', 'Plateaux', 'Cuvette', 'Cuvette-Ouest', 'Sangha', 'Likouala'],
            'Cameroun': ['Adamaoua', 'Centre', 'Est', 'Extrême-Nord', 'Littoral', 'Nord', 'Nord-Ouest', 'Ouest', 'Sud', 'Sud-Ouest'],
            'France': ['Île-de-France', 'Auvergne-Rhône-Alpes', 'Bourgogne-Franche-Comté', 'Bretagne', 'Centre-Val de Loire', 'Corse', 'Grand Est', 'Hauts-de-France', 'Normandie', 'Nouvelle-Aquitaine', 'Occitanie', 'Pays de la Loire', 'Provence-Alpes-Côte d\'Azur'],
            'Belgique': ['Bruxelles-Capitale', 'Flandre-Occidentale', 'Flandre-Orientale', 'Anvers', 'Limbourg', 'Brabant flamand', 'Brabant wallon', 'Hainaut', 'Liège', 'Luxembourg', 'Namur'],
            'Canada': ['Alberta', 'Colombie-Britannique', 'Manitoba', 'Nouveau-Brunswick', 'Terre-Neuve-et-Labrador', 'Nouvelle-Écosse', 'Ontario', 'Île-du-Prince-Édouard', 'Québec', 'Saskatchewan', 'Territoires du Nord-Ouest', 'Nunavut', 'Yukon'],
            'Burundi': ['Bubanza', 'Bujumbura Mairie', 'Bujumbura Rural', 'Bururi', 'Cankuzo', 'Cibitoke', 'Gitega', 'Karuzi', 'Kayanza', 'Kirundo', 'Makamba', 'Muramvya', 'Muyinga', 'Mwaro', 'Ngozi', 'Rumonge', 'Rutana', 'Ruyigi'],
            'Rwanda': ['Kigali', 'Est', 'Nord', 'Ouest', 'Sud']
        };

        function toggleProvinceFieldProfile() {
            const paysSelect = document.getElementById('pays');
            const provinceField = document.getElementById('province-field-profile');
            const provinceSelect = document.getElementById('province');
            const selectedPays = paysSelect.value;
            const savedProvince = '<?php echo $utilisateur['province']; ?>';

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

        document.addEventListener('DOMContentLoaded', function() {
            toggleProvinceFieldProfile();
        });
    </script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
