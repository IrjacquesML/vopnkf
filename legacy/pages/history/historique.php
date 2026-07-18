<?php
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

$utilisateur_id = get_utilisateur_id();
$utilisateur = get_utilisateur_info();
$conn = get_db_connection();

// Récupérer les statistiques globales
$stats_query = "SELECT 
    COUNT(DISTINCT pl.lecon_id) as total_lecons_terminees,
    AVG(pl.score) as score_moyen,
    MIN(pl.date_fin) as premiere_lecon_date,
    MAX(pl.date_fin) as derniere_lecon_date
FROM progression_lecons pl
WHERE pl.utilisateur_id = ? AND pl.statut = 'termine'";

$stats_stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "i", $utilisateur_id);
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);
mysqli_stmt_close($stats_stmt);

// Récupérer le nombre total de leçons disponibles
$total_query = "SELECT COUNT(*) as total FROM lecons";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
$total_lecons = $total_data['total'];

// Récupérer l'historique détaillé des leçons terminées
$historique_query = "SELECT 
    l.id,
    l.titre,
    c.nom as categorie_nom,
    pl.score,
    pl.date_debut,
    pl.date_fin,
    (SELECT COUNT(*) FROM questions WHERE lecon_id = l.id) as total_questions,
    (SELECT COUNT(*) FROM reponses_utilisateurs ru 
     WHERE ru.lecon_id = l.id AND ru.utilisateur_id = ? AND ru.est_correcte = 1) as bonnes_reponses
FROM progression_lecons pl
INNER JOIN lecons l ON pl.lecon_id = l.id
INNER JOIN categories c ON l.categorie_id = c.id
WHERE pl.utilisateur_id = ? AND pl.statut = 'termine'
ORDER BY pl.date_fin DESC";

$historique_stmt = mysqli_prepare($conn, $historique_query);
mysqli_stmt_bind_param($historique_stmt, "ii", $utilisateur_id, $utilisateur_id);
mysqli_stmt_execute($historique_stmt);
$historique_result = mysqli_stmt_get_result($historique_stmt);

// Récupérer les leçons en cours
$en_cours_query = "SELECT 
    l.id,
    l.titre,
    c.nom as categorie_nom,
    pl.date_debut
FROM progression_lecons pl
INNER JOIN lecons l ON pl.lecon_id = l.id
INNER JOIN categories c ON l.categorie_id = c.id
WHERE pl.utilisateur_id = ? AND pl.statut = 'en_cours'
ORDER BY pl.date_debut DESC";

$en_cours_stmt = mysqli_prepare($conn, $en_cours_query);
mysqli_stmt_bind_param($en_cours_stmt, "i", $utilisateur_id);
mysqli_stmt_execute($en_cours_stmt);
$en_cours_result = mysqli_stmt_get_result($en_cours_stmt);

// Calculer le pourcentage de progression
$pourcentage_progression = $total_lecons > 0 ? ($stats['total_lecons_terminees'] / $total_lecons) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Historique d'Études - VOP, Études Bibliques par Correspondance</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>VOP, Études Bibliques par Correspondance</h2>
            </div>
            <div class="nav-menu">
                <a href="../lessons/dashboard.php">Mes Leçons</a>
                <a href="historique.php" class="active">Mon Historique</a>
                <a href="../prayers/mes_prieres.php">Mes Prières</a>
                <a href="../auth/profil.php">Mon Profil</a>
            </div>
            <div class="nav-user">
                <span>Bienvenue, <?php echo h($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></span>
                <a href="../auth/deconnexion.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container historique-container">
        <div class="historique-header">
            <h1>📚 Mon Historique d'Étude</h1>
            <p>Suivez votre progression et vos accomplissements</p>
        </div>
        
        <!-- Statistiques globales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $stats['total_lecons_terminees'] ?? 0; ?></div>
                <div class="stat-label">Leçons terminées</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?php echo number_format($stats['score_moyen'] ?? 0, 1); ?>%</div>
                <div class="stat-label">Score moyen</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-value"><?php echo number_format($pourcentage_progression, 0); ?>%</div>
                <div class="stat-label">Progression globale</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-value"><?php echo $total_lecons; ?></div>
                <div class="stat-label">Total de leçons</div>
            </div>
        </div>
        
        <!-- Barre de progression -->
        <div class="progress-section">
            <h3>Votre progression</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $pourcentage_progression; ?>%"></div>
            </div>
            <p class="progress-text">
                <?php echo $stats['total_lecons_terminees'] ?? 0; ?> sur <?php echo $total_lecons; ?> leçons complétées
            </p>
        </div>
        
        <!-- Leçons en cours -->
        <?php if (mysqli_num_rows($en_cours_result) > 0): ?>
            <div class="section">
                <h2>🔄 Leçons en cours</h2>
                <div class="historique-list">
                    <?php while ($lecon = mysqli_fetch_assoc($en_cours_result)): ?>
                        <div class="historique-item en-cours">
                            <div class="historique-info">
                                <h3><?php echo h($lecon['titre']); ?></h3>
                                <p class="historique-category"><?php echo h($lecon['categorie_nom']); ?></p>
                                <p class="historique-date">
                                    Commencée le <?php echo date('d/m/Y à H:i', strtotime($lecon['date_debut'])); ?>
                                </p>
                            </div>
                            <div class="historique-actions">
                                <span class="status-badge in-progress">En cours</span>
                                <a href="../lessons/lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-primary btn-small">Continuer</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Historique des leçons terminées -->
        <div class="section">
            <h2>✅ Leçons terminées</h2>
            
            <?php if (mysqli_num_rows($historique_result) > 0): ?>
                <div class="historique-list">
                    <?php while ($lecon = mysqli_fetch_assoc($historique_result)): 
                        $score = $lecon['score'];
                        $classe_score = '';
                        if ($score >= 90) $classe_score = 'excellent';
                        elseif ($score >= 75) $classe_score = 'tres-bien';
                        elseif ($score >= 60) $classe_score = 'bien';
                        else $classe_score = 'a-revoir';
                    ?>
                        <div class="historique-item completed">
                            <div class="historique-info">
                                <h3><?php echo h($lecon['titre']); ?></h3>
                                <p class="historique-category"><?php echo h($lecon['categorie_nom']); ?></p>
                                <div class="historique-details">
                                    <span class="detail-item">
                                        📅 Terminée le <?php echo date('d/m/Y', strtotime($lecon['date_fin'])); ?>
                                    </span>
                                    <span class="detail-item">
                                        ✓ <?php echo $lecon['bonnes_reponses']; ?>/<?php echo $lecon['total_questions']; ?> bonnes réponses
                                    </span>
                                </div>
                            </div>
                            <div class="historique-actions">
                                <div class="score-badge <?php echo $classe_score; ?>">
                                    <?php echo number_format($score, 0); ?>%
                                </div>
                                <a href="../lessons/resultats.php?lecon_id=<?php echo $lecon['id']; ?>" class="btn btn-secondary btn-small">Voir résultats</a>
                                <a href="../lessons/lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-outline btn-small">Revoir</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3>Aucune leçon terminée pour le moment</h3>
                    <p>Commencez votre parcours d'étude biblique dès maintenant!</p>
                    <a href="../lessons/dashboard.php" class="btn btn-primary">Voir les leçons</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($stats['premiere_lecon_date']): ?>
            <div class="encouragement-section">
                <h3>🌟 Votre parcours</h3>
                <p>
                    Vous avez commencé votre étude biblique le 
                    <strong><?php echo date('d/m/Y', strtotime($stats['premiere_lecon_date'])); ?></strong>.
                    <?php if ($stats['total_lecons_terminees'] > 0): ?>
                        Vous avez complété <strong><?php echo $stats['total_lecons_terminees']; ?> leçon<?php echo $stats['total_lecons_terminees'] > 1 ? 's' : ''; ?></strong>
                        avec un score moyen de <strong><?php echo number_format($stats['score_moyen'], 1); ?>%</strong>.
                    <?php endif; ?>
                </p>
                <p class="verse-encouragement">
                    "Je puis tout par celui qui me fortifie." - Philippiens 4:13
                </p>
            </div>
        <?php endif; ?>
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
                        <li><a href="../lessons/dashboard.php">Mes Leçons</a></li>
                        <li><a href="historique.php">Mon Historique</a></li>
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
<?php 
mysqli_stmt_close($historique_stmt);
mysqli_stmt_close($en_cours_stmt);
mysqli_close($conn); 
?>
