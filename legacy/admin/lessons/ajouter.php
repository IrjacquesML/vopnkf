<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$admin = [
    'nom' => $_SESSION['admin_nom'],
    'prenom' => $_SESSION['admin_prenom']
];

$conn = get_db_connection();
$erreurs = [];
$succes = '';

// Récupérer toutes les catégories
$categories_query = "SELECT id, nom FROM categories ORDER BY ordre ASC";
$categories = mysqli_query($conn, $categories_query);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categorie_id = isset($_POST['categorie_id']) ? intval($_POST['categorie_id']) : 0;
    $titre = trim($_POST['titre'] ?? '');
    // Ne pas utiliser clean_input pour le contenu car il contient du HTML de TinyMCE
    $contenu = trim($_POST['contenu'] ?? '');
    $ordre = isset($_POST['ordre']) ? intval($_POST['ordre']) : 1;
    
    // Validation
    if ($categorie_id <= 0) {
        $erreurs[] = "Veuillez sélectionner une catégorie.";
    }
    if (empty($titre)) {
        $erreurs[] = "Le titre est obligatoire.";
    }
    if (empty($contenu)) {
        $erreurs[] = "Le contenu est obligatoire.";
    }
    
    if (empty($erreurs)) {
        $insert_query = "INSERT INTO lecons (categorie_id, titre, contenu, ordre) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issi", $categorie_id, $titre, $contenu, $ordre);
            
            if (mysqli_stmt_execute($stmt)) {
                $lecon_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                
                // Rediriger vers la page de modification pour ajouter des questions
                header("Location: modifier.php?id=$lecon_id&nouveau=1");
                exit;
            } else {
                $erreurs[] = "Erreur lors de la création de la leçon: " . mysqli_stmt_error($stmt);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $erreurs[] = "Erreur de préparation de la requête: " . mysqli_error($conn);
        }
    }
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Leçon - VOP Admin</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <!-- TinyMCE Editor - Version gratuite -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <nav class="navbar admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🔐 VOP Admin</h2>
            </div>
            <div class="nav-menu">
                <a href="../dashboard.php">Tableau de bord</a>
                <a href="../users/liste.php">Utilisateurs</a>
                <a href="liste.php" class="active">Leçons</a>
                <a href="../prayers/liste.php">Prières</a>
                <a href="../reports/statistiques.php">Rapports</a>
            </div>
            <div class="nav-user">
                <span>👤 <?php echo h($admin['prenom'] . ' ' . $admin['nom']); ?></span>
                <a href="../auth/logout.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container admin-container">
        <div class="admin-header">
            <h1>➕ Ajouter une Nouvelle Leçon</h1>
            <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
        </div>
        
        <?php if (!empty($succes)): ?>
            <div class="alert alert-success">
                <?php echo h($succes); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo h($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="admin-section">
            <form method="POST" action="" class="admin-form">
                <div class="form-group">
                    <label for="categorie_id">Catégorie *</label>
                    <select name="categorie_id" id="categorie_id" class="form-control" required>
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php 
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo h($cat['nom']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="titre">Titre de la Leçon *</label>
                    <input type="text" name="titre" id="titre" class="form-control" required 
                           value="<?php echo h($_POST['titre'] ?? ''); ?>"
                           placeholder="Ex: Qu'est-ce que la Bible?">
                </div>
                
                <div class="form-group">
                    <label for="ordre">Ordre dans la Catégorie *</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" required min="1"
                           value="<?php echo htmlspecialchars($_POST['ordre'] ?? '1'); ?>">
                    <small class="form-help">Numéro d'ordre de cette leçon dans sa catégorie</small>
                </div>
                
                <div class="form-group">
                    <label for="contenu">Contenu de la Leçon *</label>
                    <textarea name="contenu" id="contenu" class="form-control" rows="15" required><?php echo h($_POST['contenu'] ?? ''); ?></textarea>
                    <small class="form-help">Utilisez l'éditeur pour formater votre texte (gras, italique, listes, etc.)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Créer la Leçon</button>
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                </div>
                
                <div class="alert alert-info" style="margin-top: 20px;">
                    <strong>ℹ️ Note:</strong> Après avoir créé la leçon, vous pourrez ajouter des questions et des options de réponse.
                </div>
            </form>
        </div>
    </div>
    
    <footer class="footer admin-footer">
        <div class="footer-container">
            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Panneau d'Administration NKF | Développé par ML DATA +243 982 401 411</p>
            </div>
        </div>
    </footer>
    
    <script>
        tinymce.init({
            selector: '#contenu',
            height: 500,
            menubar: 'file edit view insert format tools table',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | removeformat code preview fullscreen',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
            branding: false,
            language: 'fr_FR',
            setup: function(editor) {
                editor.on('init', function() {
                    console.log('✓ TinyMCE initialisé avec succès');
                });
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
        
        // S'assurer que le contenu de TinyMCE est synchronisé avant la soumission du formulaire
        document.querySelector('form.admin-form').addEventListener('submit', function(e) {
            // Synchroniser le contenu de TinyMCE avec le textarea
            tinymce.triggerSave();
            
            // Vérifier que le contenu n'est pas vide
            var contenu = tinymce.get('contenu').getContent();
            if (!contenu || contenu.trim() === '') {
                e.preventDefault();
                alert('Le contenu de la leçon est obligatoire.');
                return false;
            }
            
            console.log('Formulaire soumis avec contenu:', contenu.substring(0, 100) + '...');
        });
    </script>
</body>
</html>
