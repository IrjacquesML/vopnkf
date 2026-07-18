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

$question_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lecon_id = isset($_GET['lecon_id']) ? intval($_GET['lecon_id']) : 0;

if (!$question_id || !$lecon_id) {
    redirect('liste.php');
}

$conn = get_db_connection();
$erreurs = [];
$succes = '';

// Récupérer la question
$query = "SELECT q.*, l.titre as lecon_titre FROM questions q INNER JOIN lecons l ON q.lecon_id = l.id WHERE q.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $question_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$question = mysqli_fetch_assoc($result);

if (!$question) {
    mysqli_close($conn);
    redirect('liste.php');
}

// Récupérer les options de réponse
$options_query = "SELECT * FROM options_reponse WHERE question_id = ? ORDER BY ordre ASC";
$stmt = mysqli_prepare($conn, $options_query);
mysqli_stmt_bind_param($stmt, "i", $question_id);
mysqli_stmt_execute($stmt);
$options = mysqli_stmt_get_result($stmt);

// Traitement de la modification de la question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_question') {
    $question_texte = prepare_text_for_storage($_POST['question'] ?? '');
    $ordre = isset($_POST['ordre']) ? intval($_POST['ordre']) : 1;
    
    if (empty($question_texte)) {
        $erreurs[] = "Le texte de la question est obligatoire.";
    } else {
        $update_query = "UPDATE questions SET question = ?, ordre = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sii", $question_texte, $ordre, $question_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "Question mise à jour avec succès !";
            $question['question'] = $question_texte;
            $question['ordre'] = $ordre;
        } else {
            $erreurs[] = "Erreur lors de la mise à jour de la question.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Traitement de l'ajout d'une option
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_option') {
    $texte_option = prepare_text_for_storage($_POST['texte_option'] ?? '');
    $est_correcte = isset($_POST['est_correcte']) ? 1 : 0;
    $ordre_option = isset($_POST['ordre_option']) ? intval($_POST['ordre_option']) : 1;
    
    if (empty($texte_option)) {
        $erreurs[] = "Le texte de l'option est obligatoire.";
    } else {
        $insert_query = "INSERT INTO options_reponse (question_id, texte_option, est_correcte, ordre) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "isii", $question_id, $texte_option, $est_correcte, $ordre_option);
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "Option ajoutée avec succès !";
        } else {
            $erreurs[] = "Erreur lors de l'ajout de l'option.";
        }
        
        mysqli_stmt_close($stmt);
        
        // Recharger les options
        $stmt = mysqli_prepare($conn, $options_query);
        mysqli_stmt_bind_param($stmt, "i", $question_id);
        mysqli_stmt_execute($stmt);
        $options = mysqli_stmt_get_result($stmt);
    }
}

// Traitement de la modification d'une option
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_option') {
    $option_id = isset($_POST['option_id']) ? intval($_POST['option_id']) : 0;
    $texte_option = prepare_text_for_storage($_POST['texte_option'] ?? '');
    $est_correcte = isset($_POST['est_correcte']) ? 1 : 0;
    $ordre_option = isset($_POST['ordre_option']) ? intval($_POST['ordre_option']) : 1;
    
    if ($option_id > 0 && !empty($texte_option)) {
        $update_query = "UPDATE options_reponse SET texte_option = ?, est_correcte = ?, ordre = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "siii", $texte_option, $est_correcte, $ordre_option, $option_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "Option mise à jour avec succès !";
        } else {
            $erreurs[] = "Erreur lors de la mise à jour de l'option.";
        }
        
        mysqli_stmt_close($stmt);
        
        // Recharger les options
        $stmt = mysqli_prepare($conn, $options_query);
        mysqli_stmt_bind_param($stmt, "i", $question_id);
        mysqli_stmt_execute($stmt);
        $options = mysqli_stmt_get_result($stmt);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Question - VOP Admin</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
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
            <h1>✏️ Modifier la Question</h1>
            <a href="modifier.php?id=<?php echo (int) $lecon_id; ?>" class="btn btn-secondary">← Retour à la leçon</a>
        </div>
        
        <div class="breadcrumb" style="margin-bottom: 20px;">
            <span class="badge badge-info"><?php echo h($question['lecon_titre']); ?></span>
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
        
        <!-- Formulaire de modification de la question -->
        <div class="admin-section">
            <h2>📝 Texte de la Question</h2>
            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="action" value="modifier_question">
                
                <div class="form-group">
                    <label for="question">Question *</label>
                    <textarea name="question" id="question" class="form-control" rows="4" required><?php echo h($question['question']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="ordre">Ordre</label>
                    <input type="number" name="ordre" id="ordre" class="form-control" min="1" value="<?php echo (int) $question['ordre']; ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>
        
        <!-- Gestion des options de réponse -->
        <div class="admin-section">
            <h2>📋 Options de Réponse (<?php echo mysqli_num_rows($options); ?>)</h2>
            
            <!-- Liste des options existantes -->
            <?php if (mysqli_num_rows($options) > 0): ?>
                <div class="options-management">
                    <?php 
                    mysqli_data_seek($options, 0);
                    while ($option = mysqli_fetch_assoc($options)): 
                    ?>
                        <div class="option-edit-item">
                            <form method="POST" action="" class="option-edit-form">
                                <input type="hidden" name="action" value="modifier_option">
                                <input type="hidden" name="option_id" value="<?php echo (int) $option['id']; ?>">
                                
                                <div class="option-edit-content">
                                    <div class="form-group">
                                        <label>Ordre</label>
                                        <input type="number" name="ordre_option" class="form-control form-control-small" min="1" value="<?php echo (int) $option['ordre']; ?>">
                                    </div>
                                    
                                    <div class="form-group flex-grow">
                                        <label>Texte de l'option</label>
                                        <input type="text" name="texte_option" class="form-control" value="<?php echo h($option['texte_option']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Correcte ?</label>
                                        <input type="checkbox" name="est_correcte" <?php echo $option['est_correcte'] ? 'checked' : ''; ?>>
                                    </div>
                                    
                                    <div class="option-actions">
                                        <button type="submit" class="btn btn-small btn-primary">💾</button>
                                        <a href="supprimer_option.php?id=<?php echo $option['id']; ?>&question_id=<?php echo $question_id; ?>&lecon_id=<?php echo $lecon_id; ?>" 
                                           class="btn btn-small btn-danger" 
                                           onclick="return confirm('Supprimer cette option ?')">🗑️</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune option de réponse pour cette question.</p>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout d'option -->
            <div class="add-option-form">
                <h3>➕ Ajouter une Nouvelle Option</h3>
                <form method="POST" action="" class="admin-form">
                    <input type="hidden" name="action" value="ajouter_option">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ordre_option">Ordre</label>
                            <input type="number" name="ordre_option" id="ordre_option" class="form-control" min="1" value="<?php echo mysqli_num_rows($options) + 1; ?>">
                        </div>
                        
                        <div class="form-group flex-grow">
                            <label for="texte_option">Texte de l'option *</label>
                            <input type="text" name="texte_option" id="texte_option" class="form-control" required placeholder="Ex: 66 livres">
                        </div>
                        
                        <div class="form-group">
                            <label for="est_correcte">Réponse correcte ?</label>
                            <input type="checkbox" name="est_correcte" id="est_correcte">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">➕ Ajouter l'Option</button>
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
</body>
</html>
