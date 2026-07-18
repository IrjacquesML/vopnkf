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

if (!$lecon_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Récupérer les informations de la leçon
$query = "SELECT l.*, c.nom as categorie_nom 
          FROM lecons l 
          INNER JOIN categories c ON l.categorie_id = c.id 
          WHERE l.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lecon = mysqli_fetch_assoc($result);

if (!$lecon) {
    mysqli_close($conn);
    redirect('liste.php');
}

// Récupérer les questions de la leçon
$query = "SELECT * FROM questions WHERE lecon_id = ? ORDER BY ordre ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$questions = mysqli_stmt_get_result($stmt);

// Fonction pour traiter le contenu et rendre les références bibliques cliquables
function traiter_references_bibliques($texte) {
    $pattern = '/\b([1-3]?\s?[A-Za-zéèêëàâäôöûüïî]+)\s+(\d+)\s*:\s*(\d+(?:-\d+)?)\b/u';
    
    $texte_traite = preg_replace_callback($pattern, function($matches) {
        $reference_complete = $matches[0];
        $livre = trim($matches[1]);
        $chapitre = $matches[2];
        $verset = $matches[3];
        $safe = function($v) {
            return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };
        return '<span class="bible-ref" data-reference="' . $safe($reference_complete) . '" 
                data-livre="' . $safe($livre) . '" 
                data-chapitre="' . $safe($chapitre) . '" 
                data-verset="' . $safe($verset) . '">' 
                . $safe($reference_complete) . '</span>';
    }, $texte);
    
    return $texte_traite;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($lecon['titre']); ?> - VOP Admin</title>
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
            <h1>📖 Aperçu de la Leçon</h1>
            <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
        </div>
        
        <!-- Informations de la leçon -->
        <div class="lesson-preview-section">
            <div class="lesson-info-card">
                <div class="breadcrumb">
                    <span class="badge badge-info"><?php echo h($lecon['categorie_nom']); ?></span>
                    <span> / Leçon #<?php echo $lecon['ordre']; ?></span>
                </div>
                <h2><?php echo h($lecon['titre']); ?></h2>
                <p class="text-muted">Créée le <?php echo date('d/m/Y', strtotime($lecon['date_creation'])); ?></p>
            </div>
            
            <!-- Contenu de la leçon -->
            <div class="lesson-content-card">
                <h3>📝 Contenu de la Leçon</h3>
                <div class="lesson-content">
                    <?php echo traiter_references_bibliques($lecon['contenu']); ?>
                </div>
            </div>
            
            <!-- Questions -->
            <div class="lesson-questions-card">
                <h3>❓ Questions (<?php echo mysqli_num_rows($questions); ?>)</h3>
                <?php if (mysqli_num_rows($questions) > 0): ?>
                    <?php 
                    $question_num = 1;
                    while ($question = mysqli_fetch_assoc($questions)): 
                        // Récupérer les options pour cette question
                        $conn = get_db_connection();
                        $options_query = "SELECT * FROM options_reponse WHERE question_id = ? ORDER BY ordre ASC";
                        $options_stmt = mysqli_prepare($conn, $options_query);
                        mysqli_stmt_bind_param($options_stmt, "i", $question['id']);
                        mysqli_stmt_execute($options_stmt);
                        $options = mysqli_stmt_get_result($options_stmt);
                        mysqli_close($conn);
                    ?>
                        <div class="question-preview">
                            <h4>Question <?php echo $question_num++; ?></h4>
                            <p class="question-text"><?php echo h($question['question']); ?></p>
                            
                            <div class="options-list">
                                <?php while ($option = mysqli_fetch_assoc($options)): ?>
                                    <div class="option-item <?php echo $option['est_correcte'] ? 'correct-option' : ''; ?>">
                                        <span class="option-letter"><?php echo chr(64 + $option['ordre']); ?>.</span>
                                        <span class="option-text"><?php echo h($option['texte_option']); ?></span>
                                        <?php if ($option['est_correcte']): ?>
                                            <span class="badge badge-success">✓ Correcte</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Aucune question pour cette leçon.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal pour afficher les versets -->
    <div id="verseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="verseReference"></h3>
            <p id="verseText"></p>
        </div>
    </div>
    
    <footer class="footer admin-footer">
        <div class="footer-container">
            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Panneau d'Administration NKF | Développé par ML DATA +243 982 401 411</p>
            </div>
        </div>
    </footer>
    
    <script src="../../assets/js/script.js"></script>
</body>
</html>
