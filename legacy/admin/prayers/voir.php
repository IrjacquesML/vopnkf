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

$priere_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$priere_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Récupérer la demande de prière
$query = "SELECT dp.*, u.nom, u.prenom, u.email
          FROM demandes_priere dp
          INNER JOIN utilisateurs u ON dp.utilisateur_id = u.id
          WHERE dp.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $priere_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$priere = mysqli_fetch_assoc($result);

if (!$priere) {
    mysqli_close($conn);
    redirect('liste.php');
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Demande de Prière - VOP Admin</title>
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
                <a href="../lessons/liste.php">Leçons</a>
                <a href="liste.php" class="active">Prières</a>
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
            <h1>🙏 Détails de la Demande de Prière</h1>
            <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
        </div>
        
        <!-- Informations de la demande -->
        <div class="prayer-detail-section">
            <div class="prayer-info-card">
                <div class="prayer-header-info">
                    <h2><?php echo h($priere['sujet']); ?></h2>
                    <?php
                    $badge_class = '';
                    $statut_text = '';
                    switch($priere['statut']) {
                        case 'en_attente':
                            $badge_class = 'badge-warning';
                            $statut_text = '⏳ En attente';
                            break;
                        case 'en_priere':
                            $badge_class = 'badge-info';
                            $statut_text = '🙏 En prière';
                            break;
                        case 'exaucee':
                            $badge_class = 'badge-success';
                            $statut_text = '✅ Exaucée';
                            break;
                    }
                    ?>
                    <span class="badge <?php echo $badge_class; ?> badge-large"><?php echo $statut_text; ?></span>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Utilisateur:</strong>
                        <span>
                            <?php echo h($priere['prenom'] . ' ' . $priere['nom']); ?>
                            <a href="../users/voir.php?id=<?php echo $priere['utilisateur_id']; ?>" class="btn btn-small btn-info">Voir profil</a>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?php echo h($priere['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Date de création:</strong>
                        <span><?php echo date('d/m/Y à H:i', strtotime($priere['date_creation'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Dernière modification:</strong>
                        <span>
                            <?php if ($priere['date_modification']): ?>
                                <?php echo date('d/m/Y à H:i', strtotime($priere['date_modification'])); ?>
                            <?php else: ?>
                                Jamais modifiée
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Anonyme:</strong>
                        <span><?php echo $priere['est_anonyme'] ? '🔒 Oui' : 'Non'; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Message de la demande -->
            <div class="prayer-message-card">
                <h3>📝 Message de la demande</h3>
                <div class="prayer-message-content">
                    <?php echo nl2br(h($priere['message'])); ?>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="admin-actions">
                <a href="modifier_statut.php?id=<?php echo $priere_id; ?>" class="btn btn-primary">✏ Modifier le statut</a>
                <a href="supprimer.php?id=<?php echo $priere_id; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande de prière ?')">🗑 Supprimer</a>
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
