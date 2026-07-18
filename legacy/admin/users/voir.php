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

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM utilisateurs WHERE id = ? AND role = 'utilisateur'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    mysqli_close($conn);
    redirect('liste.php');
}

// Statistiques de l'utilisateur
$stats = [];

// Leçons terminées
$query = "SELECT COUNT(*) as total FROM progression_lecons WHERE utilisateur_id = ? AND statut = 'termine'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['lecons_terminees'] = mysqli_fetch_assoc($result)['total'];

// Score moyen
$query = "SELECT AVG(score) as moyenne FROM progression_lecons WHERE utilisateur_id = ? AND statut = 'termine'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['score_moyen'] = mysqli_fetch_assoc($result)['moyenne'] ?? 0;

// Demandes de prière
$query = "SELECT COUNT(*) as total FROM demandes_priere WHERE utilisateur_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['demandes_priere'] = mysqli_fetch_assoc($result)['total'];

// Progression récente
$query = "SELECT l.titre, c.nom as categorie, pl.score, pl.date_completion
          FROM progression_lecons pl
          INNER JOIN lecons l ON pl.lecon_id = l.id
          INNER JOIN categories c ON l.categorie_id = c.id
          WHERE pl.utilisateur_id = ? AND pl.statut = 'termine'
          ORDER BY pl.date_completion DESC
          LIMIT 10";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$progression = mysqli_stmt_get_result($stmt);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Utilisateur - VOP Admin</title>
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
                <a href="liste.php" class="active">Utilisateurs</a>
                <a href="../lessons/liste.php">Leçons</a>
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
            <h1>👤 Profil Utilisateur</h1>
            <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
        </div>
        
        <!-- Informations utilisateur -->
        <div class="user-profile-section">
            <div class="user-info-card">
                <h2>Informations Personnelles</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Nom complet:</strong>
                        <span><?php echo h($user['prenom'] . ' ' . $user['nom']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?php echo h($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Date d'inscription:</strong>
                        <span><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Dernière connexion:</strong>
                        <span>
                            <?php if ($user['derniere_connexion']): ?>
                                <?php echo date('d/m/Y à H:i', strtotime($user['derniere_connexion'])); ?>
                            <?php else: ?>
                                Jamais connecté
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['lecons_terminees']; ?></h3>
                        <p>Leçons Terminées</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['score_moyen'], 1); ?>%</h3>
                        <p>Score Moyen</p>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">🙏</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['demandes_priere']; ?></h3>
                        <p>Demandes de Prière</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Progression récente -->
        <div class="admin-section">
            <h2>📚 Progression Récente</h2>
            <?php if (mysqli_num_rows($progression) > 0): ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th>Leçon</th>
                                <th>Score</th>
                                <th>Date de Complétion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($prog = mysqli_fetch_assoc($progression)): ?>
                                <tr>
                                    <td><?php echo h($prog['categorie']); ?></td>
                                    <td><?php echo h($prog['titre']); ?></td>
                                    <td>
                                        <?php
                                        $score_class = '';
                                        if ($prog['score'] >= 80) $score_class = 'badge-success';
                                        elseif ($prog['score'] >= 60) $score_class = 'badge-warning';
                                        else $score_class = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $score_class; ?>"><?php echo number_format($prog['score'], 0); ?>%</span>
                                    </td>
                                    <td><?php echo date('d/m/Y à H:i', strtotime($prog['date_completion'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune leçon terminée pour le moment.</p>
            <?php endif; ?>
        </div>
        
        <!-- Actions -->
        <div class="admin-actions">
            <a href="supprimer.php?id=<?php echo $user_id; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">🗑 Supprimer l'utilisateur</a>
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
