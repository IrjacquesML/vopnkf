<?php
require_once '../../includes/config.php';
require_once '../../includes/traduction.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

$utilisateur_id = get_utilisateur_id();
$utilisateur = get_utilisateur_info();
$conn = get_db_connection();

// Récupérer la langue préférée de l'utilisateur
$langue_utilisateur = get_langue_utilisateur($utilisateur_id);

// Récupérer toutes les catégories avec leurs leçons
$query = "SELECT c.id, c.nom, c.description, c.ordre 
          FROM categories c 
          ORDER BY c.ordre ASC";
$categories_result = mysqli_query($conn, $query);

// Fonction pour vérifier si une leçon est déverrouillée
function est_lecon_deverrouillee($conn, $utilisateur_id, $lecon_id, $lecon_ordre, $categorie_id) {
    // La première leçon de chaque catégorie est toujours déverrouillée
    if ($lecon_ordre == 1) {
        return true;
    }
    
    // Vérifier si la leçon précédente est terminée
    $query = "SELECT l.id 
              FROM lecons l
              INNER JOIN progression_lecons pl ON l.id = pl.lecon_id
              WHERE l.categorie_id = ? 
              AND l.ordre = ? 
              AND pl.utilisateur_id = ?
              AND pl.statut = 'termine'";
    $stmt = mysqli_prepare($conn, $query);
    $ordre_precedent = $lecon_ordre - 1;
    mysqli_stmt_bind_param($stmt, "iii", $categorie_id, $ordre_precedent, $utilisateur_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $est_deverrouillee = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    
    return $est_deverrouillee;
}

// Fonction pour obtenir la progression d'une leçon
function get_progression_lecon($conn, $utilisateur_id, $lecon_id) {
    $query = "SELECT statut, score FROM progression_lecons WHERE utilisateur_id = ? AND lecon_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $utilisateur_id, $lecon_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $progression = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $progression;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - VOP, Études Bibliques par Correspondance</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>VOP, Études Bibliques par Correspondance</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="active"><?php _e('mes_lecons'); ?></a>
                <a href="../history/historique.php"><?php _e('mon_historique'); ?></a>
                <a href="../prayers/mes_prieres.php"><?php _e('mes_prieres'); ?></a>
                <a href="../auth/profil.php"><?php _e('mon_profil'); ?></a>
            </div>
            <div class="nav-user">
                <span><?php _e('bienvenue'); ?>, <?php echo h($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></span>
                <a href="../auth/deconnexion.php" class="btn btn-small"><?php _e('deconnexion'); ?></a>
            </div>
        </div>
    </nav>
    
    <div class="container dashboard-container">
        <div class="dashboard-header">
            <h1>Mes Études Bibliques</h1>
            <p>Découvrez la vérité biblique à travers nos leçons organisées par catégorie</p>
        </div>
        
        <?php while ($categorie = mysqli_fetch_assoc($categories_result)): ?>
            <div class="category-section">
                <div class="category-header">
                    <h2><?php echo h($categorie['nom']); ?></h2>
                    <p><?php echo h($categorie['description']); ?></p>
                </div>
                
                <div class="lessons-grid">
                    <?php
                    // Récupérer les leçons de cette catégorie
                    $lecons_query = "SELECT id, titre, contenu, ordre FROM lecons WHERE categorie_id = ? ORDER BY ordre ASC";
                    $stmt = mysqli_prepare($conn, $lecons_query);
                    mysqli_stmt_bind_param($stmt, "i", $categorie['id']);
                    mysqli_stmt_execute($stmt);
                    $lecons_result = mysqli_stmt_get_result($stmt);
                    
                    while ($lecon = mysqli_fetch_assoc($lecons_result)):
                        $est_deverrouillee = est_lecon_deverrouillee($conn, $utilisateur_id, $lecon['id'], $lecon['ordre'], $categorie['id']);
                        $progression = get_progression_lecon($conn, $utilisateur_id, $lecon['id']);
                        $statut = $progression ? $progression['statut'] : 'non_commence';
                        $score = $progression ? $progression['score'] : null;
                    ?>
                        <div class="lesson-card <?php echo !$est_deverrouillee ? 'locked' : ''; ?> <?php echo $statut; ?>">
                            <div class="lesson-number">Leçon <?php echo $lecon['ordre']; ?></div>
                            <h3><?php echo h($lecon['titre']); ?></h3>
                            
                            <?php if (!$est_deverrouillee): ?>
                                <div class="lock-icon">🔒</div>
                                <p class="lock-message">Terminez la leçon précédente pour déverrouiller</p>
                            <?php else: ?>
                                <?php if ($statut === 'termine'): ?>
                                    <div class="lesson-status completed">
                                        ✓ Terminée
                                        <?php if ($score !== null): ?>
                                            <span class="score"><?php echo number_format($score, 0); ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-secondary btn-small">Revoir</a>
                                <?php elseif ($statut === 'en_cours'): ?>
                                    <div class="lesson-status in-progress">En cours</div>
                                    <a href="lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-primary btn-small">Continuer</a>
                                <?php else: ?>
                                    <a href="lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-primary btn-small">Commencer</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                    <?php mysqli_stmt_close($stmt); ?>
                </div>
            </div>
        <?php endwhile; ?>
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
