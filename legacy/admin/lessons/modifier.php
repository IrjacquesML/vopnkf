<?php
require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$admin = [
    'nom' => $_SESSION['admin_nom'],
    'prenom' => $_SESSION['admin_prenom']
];

$lecon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nouveau = isset($_GET['nouveau']) ? true : false;

if (!$lecon_id) {
    redirect('liste.php');
}

$conn = get_db_connection();
$erreurs = [];
$succes = '';

// Récupérer la leçon
$query = "SELECT l.*, c.nom as categorie_nom FROM lecons l INNER JOIN categories c ON l.categorie_id = c.id WHERE l.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lecon = mysqli_fetch_assoc($result);

if (!$lecon) {
    mysqli_close($conn);
    redirect('liste.php');
}

// Récupérer toutes les catégories
$categories_query = "SELECT id, nom FROM categories ORDER BY ordre ASC";
$categories = mysqli_query($conn, $categories_query);

// Récupérer les questions de la leçon
$questions_query = "SELECT * FROM questions WHERE lecon_id = ? ORDER BY ordre ASC";
$stmt = mysqli_prepare($conn, $questions_query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$questions = mysqli_stmt_get_result($stmt);

// Traitement du formulaire de modification de la leçon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_lecon') {
    $categorie_id = isset($_POST['categorie_id']) ? intval($_POST['categorie_id']) : 0;
    $titre = prepare_text_for_storage($_POST['titre'] ?? '');
    // Ne pas utiliser clean_input pour le contenu car il contient du HTML de TinyMCE
    $contenu = trim($_POST['contenu'] ?? '');
    $ordre = isset($_POST['ordre']) ? intval($_POST['ordre']) : 1;
    
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
        $update_query = "UPDATE lecons SET categorie_id = ?, titre = ?, contenu = ?, ordre = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issii", $categorie_id, $titre, $contenu, $ordre, $lecon_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $succes = "La leçon a été mise à jour avec succès !";
                // Recharger les données
                $lecon['categorie_id'] = $categorie_id;
                $lecon['titre'] = $titre;
                $lecon['contenu'] = $contenu;
                $lecon['ordre'] = $ordre;
            } else {
                $erreurs[] = "Erreur lors de la mise à jour de la leçon: " . mysqli_stmt_error($stmt);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $erreurs[] = "Erreur de préparation de la requête: " . mysqli_error($conn);
        }
    }
}

// Traitement de l'ajout d'une question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_question') {
    $question_texte = prepare_text_for_storage($_POST['question'] ?? '');
    $ordre_question = isset($_POST['ordre_question']) ? intval($_POST['ordre_question']) : 1;
    
    if (empty($question_texte)) {
        $erreurs[] = "Le texte de la question est obligatoire.";
    } else {
        $insert_query = "INSERT INTO questions (lecon_id, question, ordre) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "isi", $lecon_id, $question_texte, $ordre_question);
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "Question ajoutée avec succès !";
        } else {
            $erreurs[] = "Erreur lors de l'ajout de la question.";
        }
        
        mysqli_stmt_close($stmt);
        
        // Recharger les questions
        $stmt = mysqli_prepare($conn, $questions_query);
        mysqli_stmt_bind_param($stmt, "i", $lecon_id);
        mysqli_stmt_execute($stmt);
        $questions = mysqli_stmt_get_result($stmt);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Leçon - VOP Admin</title>
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
            <h1>✏️ Modifier la Leçon</h1>
            <div>
                <a href="voir.php?id=<?php echo (int) $lecon_id; ?>" class="btn btn-info">👁 Aperçu</a>
                <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
            </div>
        </div>
        
        <?php if ($nouveau): ?>
            <div class="alert alert-success">
                ✅ Leçon créée avec succès ! Vous pouvez maintenant ajouter des questions.
            </div>
        <?php endif; ?>
        
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
        
        <!-- Formulaire de modification de la leçon -->
        <div class="admin-section">
            <h2>📝 Informations de la Leçon</h2>
            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="action" value="modifier_lecon">
                
                <div class="form-group">
                    <label for="categorie_id">Catégorie *</label>
                    <select name="categorie_id" id="categorie_id" class="form-control" required>
                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo (int) $cat['id']; ?>" <?php echo (int) $lecon['categorie_id'] === (int) $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo h($cat['nom']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="titre">Titre de la Leçon *</label>
                    <input type="text" name="titre" id="titre" class="form-control" required 
                           value="<?php echo h($lecon['titre']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="ordre">Ordre dans la Catégorie *</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" required min="1"
                           value="<?php echo (int) $lecon['ordre']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="contenu">Contenu de la Leçon *</label>
                    <textarea name="contenu" id="contenu" class="form-control" rows="15" required><?php echo h($lecon['contenu']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Enregistrer les Modifications</button>
                </div>
            </form>
        </div>
        
        <!-- Gestion des questions -->
        <div class="admin-section">
            <h2>❓ Questions de la Leçon (<?php echo mysqli_num_rows($questions); ?>)</h2>
            
            <!-- Liste des questions existantes -->
            <?php if (mysqli_num_rows($questions) > 0): ?>
                <div class="questions-list">
                    <?php 
                    $q_num = 1;
                    mysqli_data_seek($questions, 0);
                    while ($question = mysqli_fetch_assoc($questions)): 
                        $conn = get_db_connection();
                        $options_query = "SELECT * FROM options_reponse WHERE question_id = ? ORDER BY ordre ASC";
                        $opt_stmt = mysqli_prepare($conn, $options_query);
                        mysqli_stmt_bind_param($opt_stmt, "i", $question['id']);
                        mysqli_stmt_execute($opt_stmt);
                        $options = mysqli_stmt_get_result($opt_stmt);
                        mysqli_close($conn);
                    ?>
                        <div class="question-item">
                            <div class="question-header">
                                <h4>Question <?php echo $q_num++; ?></h4>
                                <div class="question-actions">
                                    <a href="modifier_question.php?id=<?php echo (int) $question['id']; ?>&lecon_id=<?php echo (int) $lecon_id; ?>" class="btn btn-small btn-primary">✏️ Modifier</a>
                                    <a href="supprimer_question.php?id=<?php echo (int) $question['id']; ?>&lecon_id=<?php echo (int) $lecon_id; ?>" class="btn btn-small btn-danger" onclick="return confirm('Supprimer cette question ?')">🗑️ Supprimer</a>
                                </div>
                            </div>
                            <p class="question-text"><?php echo h($question['question']); ?></p>
                            
                            <?php if (mysqli_num_rows($options) > 0): ?>
                                <div class="options-preview">
                                    <?php while ($opt = mysqli_fetch_assoc($options)): ?>
                                        <div class="option-preview <?php echo $opt['est_correcte'] ? 'correct' : ''; ?>">
                                            <span><?php echo chr(64 + $opt['ordre']); ?>.</span>
                                            <?php echo h($opt['texte_option']); ?>
                                            <?php if ($opt['est_correcte']): ?>
                                                <span class="badge badge-success">✓</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Aucune option de réponse</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune question pour cette leçon.</p>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout de question -->
            <div class="add-question-form">
                <h3>➕ Ajouter une Nouvelle Question</h3>
                <form method="POST" action="" class="admin-form">
                    <input type="hidden" name="action" value="ajouter_question">
                    
                    <div class="form-group">
                        <label for="question">Texte de la Question *</label>
                        <textarea name="question" id="question" class="form-control" rows="3" required
                                  placeholder="Saisissez votre question ici..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="ordre_question">Ordre</label>
                        <input type="number" name="ordre_question" id="ordre_question" class="form-control" min="1" value="<?php echo mysqli_num_rows($questions) + 1; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-success">➕ Ajouter la Question</button>
                    <small class="form-help">Après avoir ajouté la question, vous pourrez ajouter les options de réponse.</small>
                </form>
            </div>
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
        document.querySelectorAll('form.admin-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Synchroniser le contenu de TinyMCE avec le textarea
                tinymce.triggerSave();
                
                // Si c'est le formulaire de modification de leçon
                if (this.querySelector('input[name="action"]') && 
                    this.querySelector('input[name="action"]').value === 'modifier_lecon') {
                    var contenu = tinymce.get('contenu').getContent();
                    if (!contenu || contenu.trim() === '') {
                        e.preventDefault();
                        alert('Le contenu de la leçon est obligatoire.');
                        return false;
                    }
                    console.log('Formulaire soumis avec contenu:', contenu.substring(0, 100) + '...');
                }
            });
        });
    </script>
</body>
</html>
