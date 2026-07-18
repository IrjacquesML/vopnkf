<?php
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

$utilisateur_id = get_utilisateur_id();
$utilisateur = get_utilisateur_info();
$conn = get_db_connection();

// Récupérer toutes les demandes de prière de l'utilisateur
$query = "SELECT id, sujet, message, est_anonyme, statut, date_creation, date_modification
          FROM demandes_priere
          WHERE utilisateur_id = ?
          ORDER BY date_creation DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $utilisateur_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Compter les demandes par statut
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
    SUM(CASE WHEN statut = 'en_priere' THEN 1 ELSE 0 END) as en_priere,
    SUM(CASE WHEN statut = 'exaucee' THEN 1 ELSE 0 END) as exaucee
FROM demandes_priere
WHERE utilisateur_id = ?";

$stats_stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "i", $utilisateur_id);
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);
mysqli_stmt_close($stats_stmt);

// Fonction pour obtenir le badge de statut
function get_statut_badge($statut) {
    switch($statut) {
        case 'en_attente':
            return '<span class="statut-badge en-attente">⏳ En attente</span>';
        case 'en_priere':
            return '<span class="statut-badge en-priere">🙏 En prière</span>';
        case 'exaucee':
            return '<span class="statut-badge exaucee">✅ Exaucée</span>';
        default:
            return '<span class="statut-badge">' . h($statut) . '</span>';
    }
}

// Fonction pour formater la date
function format_date_fr($date) {
    $timestamp = strtotime($date);
    $maintenant = time();
    $diff = $maintenant - $timestamp;
    
    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Il y a " . $minutes . " minute" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $heures = floor($diff / 3600);
        return "Il y a " . $heures . " heure" . ($heures > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $jours = floor($diff / 86400);
        return "Il y a " . $jours . " jour" . ($jours > 1 ? 's' : '');
    } else {
        return date('d/m/Y à H:i', $timestamp);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de Prière - VOP, Études Bibliques par Correspondance</title>
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
                <a href="../history/historique.php">Mon Historique</a>
                <a href="mes_prieres.php" class="active">Mes Prières</a>
                <a href="../auth/profil.php">Mon Profil</a>
                <a href="demande_priere.php">Nouvelle Demande</a>
            </div>
            <div class="nav-user">
                <span>Bienvenue, <?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></span>
                <a href="../auth/deconnexion.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container prayers-container">
        <div class="prayers-header">
            <h1>🙏 Mes Demandes de Prière</h1>
            <p>Suivez vos demandes de prière et voyez comment Dieu agit dans votre vie</p>
        </div>
        
        <!-- Statistiques -->
        <div class="prayer-stats-grid">
            <div class="prayer-stat-card total">
                <div class="stat-icon">📋</div>
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total de demandes</div>
            </div>
            
            <div class="prayer-stat-card attente">
                <div class="stat-icon">⏳</div>
                <div class="stat-value"><?php echo $stats['en_attente'] ?? 0; ?></div>
                <div class="stat-label">En attente</div>
            </div>
            
            <div class="prayer-stat-card priere">
                <div class="stat-icon">🙏</div>
                <div class="stat-value"><?php echo $stats['en_priere'] ?? 0; ?></div>
                <div class="stat-label">En prière</div>
            </div>
            
            <div class="prayer-stat-card exaucee">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $stats['exaucee'] ?? 0; ?></div>
                <div class="stat-label">Exaucées</div>
            </div>
        </div>
        
        <!-- Bouton pour nouvelle demande -->
        <div class="new-prayer-action">
            <a href="demande_priere.php" class="btn btn-primary btn-large">➕ Nouvelle demande de prière</a>
        </div>
        
        <!-- Liste des demandes -->
        <div class="prayers-list-section">
            <h2>Toutes mes demandes</h2>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="prayers-list">
                    <?php while ($demande = mysqli_fetch_assoc($result)): ?>
                        <div class="prayer-card">
                            <div class="prayer-card-header">
                                <div class="prayer-title-row">
                                    <h3><?php echo h($demande['sujet']); ?></h3>
                                    <?php echo get_statut_badge($demande['statut']); ?>
                                </div>
                                <div class="prayer-meta">
                                    <span class="prayer-date">📅 <?php echo format_date_fr($demande['date_creation']); ?></span>
                                    <?php if ($demande['est_anonyme']): ?>
                                        <span class="prayer-anonyme">🔒 Anonyme</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="prayer-card-body">
                                <p><?php echo nl2br(h($demande['message'])); ?></p>
                            </div>
                            
                            <?php if ($demande['date_modification']): ?>
                                <div class="prayer-card-footer">
                                    <small>Dernière mise à jour: <?php echo format_date_fr($demande['date_modification']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🙏</div>
                    <h3>Aucune demande de prière pour le moment</h3>
                    <p>Partagez vos besoins avec nous et laissez-nous prier pour vous.</p>
                    <a href="demande_priere.php" class="btn btn-primary">Envoyer une demande</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Verset d'encouragement -->
        <div class="prayer-encouragement">
            <h3>✨ Promesse de Dieu</h3>
            <p class="verse-text">"La prière fervente du juste a une grande efficacité."</p>
            <p class="verse-reference">- Jacques 5:16</p>
            <p class="encouragement-text">
                Dieu entend vos prières et Il agit selon Sa volonté parfaite. 
                Continuez à Lui faire confiance et à persévérer dans la prière.
            </p>
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
                        <li><a href="../lessons/dashboard.php">Mes Leçons</a></li>
                        <li><a href="../history/historique.php">Mon Historique</a></li>
                        <li><a href="mes_prieres.php">Mes Prières</a></li>
                        <li><a href="demande_priere.php">Demande de Prière</a></li>
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
mysqli_stmt_close($stmt);
mysqli_close($conn); 
?>
