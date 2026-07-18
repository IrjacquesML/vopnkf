<?php
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

$utilisateur_id = get_utilisateur_id();
$lecon_id = isset($_GET['lecon_id']) ? intval($_GET['lecon_id']) : 0;

if (!$lecon_id) {
    redirect('dashboard.php');
}

$conn = get_db_connection();

// Récupérer les informations de la leçon
$query = "SELECT l.id, l.titre, l.categorie_id, c.nom as categorie_nom
          FROM lecons l
          INNER JOIN categories c ON l.categorie_id = c.id
          WHERE l.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lecon = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$lecon) {
    redirect('dashboard.php');
}

// Récupérer le score
$score_query = "SELECT score FROM progression_lecons WHERE utilisateur_id = ? AND lecon_id = ?";
$score_stmt = mysqli_prepare($conn, $score_query);
mysqli_stmt_bind_param($score_stmt, "ii", $utilisateur_id, $lecon_id);
mysqli_stmt_execute($score_stmt);
$score_result = mysqli_stmt_get_result($score_stmt);
$progression = mysqli_fetch_assoc($score_result);
$score = $progression ? $progression['score'] : 0;
mysqli_stmt_close($score_stmt);

// Récupérer toutes les réponses avec les détails
$reponses_query = "SELECT 
    q.id as question_id,
    q.question,
    q.ordre,
    ru.option_id,
    ru.est_correcte,
    o.texte_option as reponse_donnee,
    (SELECT texte_option FROM options_reponse WHERE question_id = q.id AND est_correcte = 1 LIMIT 1) as bonne_reponse
FROM questions q
LEFT JOIN reponses_utilisateurs ru ON q.id = ru.question_id AND ru.utilisateur_id = ?
LEFT JOIN options_reponse o ON ru.option_id = o.id
WHERE q.lecon_id = ?
ORDER BY q.ordre ASC";

$reponses_stmt = mysqli_prepare($conn, $reponses_query);
mysqli_stmt_bind_param($reponses_stmt, "ii", $utilisateur_id, $lecon_id);
mysqli_stmt_execute($reponses_stmt);
$reponses_result = mysqli_stmt_get_result($reponses_stmt);

$total_questions = 0;
$bonnes_reponses = 0;
$reponses = [];

while ($r = mysqli_fetch_assoc($reponses_result)) {
    $reponses[] = $r;
    $total_questions++;
    if ($r['est_correcte']) {
        $bonnes_reponses++;
    }
}
mysqli_stmt_close($reponses_stmt);

// Déterminer le message de félicitation
$message_felicitation = '';
$classe_score = '';
if ($score >= 90) {
    $message_felicitation = "Excellent travail! Vous maîtrisez parfaitement cette leçon.";
    $classe_score = 'excellent';
} elseif ($score >= 75) {
    $message_felicitation = "Très bien! Vous avez une bonne compréhension de la leçon.";
    $classe_score = 'tres-bien';
} elseif ($score >= 60) {
    $message_felicitation = "Bien! Continuez vos efforts pour approfondir votre compréhension.";
    $classe_score = 'bien';
} else {
    $message_felicitation = "Nous vous encourageons à relire la leçon et à réessayer.";
    $classe_score = 'a-revoir';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - <?php echo h($lecon['titre']); ?> - VOP, Études Bibliques par Correspondance</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="dashboard.php">← Retour au tableau de bord</a>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php">Mes Leçons</a>
                <a href="../history/historique.php">Mon Historique</a>
                <a href="../prayers/mes_prieres.php">Mes Prières</a>
                <a href="../auth/profil.php">Mon Profil</a>
            </div>
            <div class="nav-user">
                <a href="../auth/deconnexion.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container results-container">
        <div class="results-header">
            <h1>Résultats de l'interrogation</h1>
            <div class="breadcrumb">
                <?php echo h($lecon['categorie_nom']); ?> / <?php echo h($lecon['titre']); ?>
            </div>
        </div>
        
        <div class="score-summary <?php echo $classe_score; ?>">
            <div class="score-circle">
                <div class="score-value"><?php echo number_format($score, 0); ?>%</div>
                <div class="score-label"><?php echo $bonnes_reponses; ?> / <?php echo $total_questions; ?> correctes</div>
            </div>
            <p class="score-message"><?php echo $message_felicitation; ?></p>
        </div>
        
        <div class="results-details">
            <h2>Détails de vos réponses</h2>
            
            <?php foreach ($reponses as $index => $reponse): ?>
                <div class="result-item <?php echo $reponse['est_correcte'] ? 'correct' : 'incorrect'; ?>">
                    <div class="result-header">
                        <h3>Question <?php echo ($index + 1); ?></h3>
                        <span class="result-badge">
                            <?php echo $reponse['est_correcte'] ? '✓ Correct' : '✗ Incorrect'; ?>
                        </span>
                    </div>
                    
                    <p class="result-question"><?php echo nl2br(h($reponse['question'])); ?></p>
                    
                    <div class="result-answers">
                        <div class="answer-row">
                            <strong>Votre réponse:</strong>
                            <span class="<?php echo $reponse['est_correcte'] ? 'correct-answer' : 'wrong-answer'; ?>">
                                <?php echo h($reponse['reponse_donnee'] ?? 'Non répondu'); ?>
                            </span>
                        </div>
                        
                        <?php if (!$reponse['est_correcte']): ?>
                            <div class="answer-row">
                                <strong>Bonne réponse:</strong>
                                <span class="correct-answer">
                                    <?php echo h($reponse['bonne_reponse']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="results-actions">
            <a href="lecon.php?id=<?php echo (int) $lecon_id; ?>" class="btn btn-secondary">Revoir la leçon</a>
            <a href="dashboard.php" class="btn btn-primary">Continuer mes études</a>
        </div>
        
        <div class="encouragement-verse">
            <p class="verse-text">"Je puis tout par celui qui me fortifie."</p>
            <p class="verse-reference">- Philippiens 4:13</p>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VOP</h3>
                    <p>Études Bibliques par Correspondance</p>
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
                        <li><a href="dashboard.php">Mes Leçons</a></li>
                        <li><a href="../history/historique.php">Mon Historique</a></li>
                        <li><a href="../prayers/mes_prieres.php">Mes Prières</a></li>
                        <li><a href="../prayers/demande_priere.php">Demande de Prière</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Études Bibliques par Correspondance NKF | Développé par ML DATA +243 982 401 411</p>
                <p class="footer-verse">"Car la parole de Dieu est vivante et efficace" - Hébreux 4:12</p>
            </div>
        </div>
    </footer>
    
    <script src="../../assets/js/script.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
