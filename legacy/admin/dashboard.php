<?php
require_once '../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('auth/login.php');
}

$admin_id = $_SESSION['admin_id'];
$admin = [
    'nom' => $_SESSION['admin_nom'],
    'prenom' => $_SESSION['admin_prenom'],
    'email' => $_SESSION['admin_email']
];

$conn = get_db_connection();

// Statistiques globales
$stats = [];

// Total utilisateurs
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur'";
$result = mysqli_query($conn, $query);
$stats['total_utilisateurs'] = mysqli_fetch_assoc($result)['total'];

// Nouveaux utilisateurs (7 derniers jours)
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur' AND date_inscription >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = mysqli_query($conn, $query);
$stats['nouveaux_utilisateurs'] = mysqli_fetch_assoc($result)['total'];

// Total leçons
$query = "SELECT COUNT(*) as total FROM lecons";
$result = mysqli_query($conn, $query);
$stats['total_lecons'] = mysqli_fetch_assoc($result)['total'];

// Total catégories
$query = "SELECT COUNT(*) as total FROM categories";
$result = mysqli_query($conn, $query);
$stats['total_categories'] = mysqli_fetch_assoc($result)['total'];

// Leçons complétées
$query = "SELECT COUNT(*) as total FROM progression_lecons WHERE statut = 'termine'";
$result = mysqli_query($conn, $query);
$stats['lecons_completees'] = mysqli_fetch_assoc($result)['total'];

// Demandes de prière
$query = "SELECT COUNT(*) as total FROM demandes_priere";
$result = mysqli_query($conn, $query);
$stats['total_prieres'] = mysqli_fetch_assoc($result)['total'];

// Demandes de prière en attente
$query = "SELECT COUNT(*) as total FROM demandes_priere WHERE statut = 'en_attente'";
$result = mysqli_query($conn, $query);
$stats['prieres_en_attente'] = mysqli_fetch_assoc($result)['total'];

// Utilisateurs actifs récents (connectés dans les 7 derniers jours)
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur' AND derniere_connexion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = mysqli_query($conn, $query);
$stats['utilisateurs_actifs'] = mysqli_fetch_assoc($result)['total'];

// Derniers utilisateurs inscrits
$query = "SELECT id, nom, prenom, email, date_inscription FROM utilisateurs WHERE role = 'utilisateur' ORDER BY date_inscription DESC LIMIT 5";
$derniers_utilisateurs = mysqli_query($conn, $query);

// Dernières demandes de prière
$query = "SELECT dp.id, dp.sujet, dp.statut, dp.date_creation, u.nom, u.prenom 
          FROM demandes_priere dp 
          INNER JOIN utilisateurs u ON dp.utilisateur_id = u.id 
          ORDER BY dp.date_creation DESC LIMIT 5";
$dernieres_prieres = mysqli_query($conn, $query);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - VOP</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <nav class="navbar admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>🔐 VOP Admin</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="active">Tableau de bord</a>
                <a href="users/liste.php">Utilisateurs</a>
                <a href="lessons/liste.php">Leçons</a>
                <a href="prayers/liste.php">Prières</a>
                <a href="reports/statistiques.php">Rapports</a>
                <a href="reports/palmares.php">Palmarès</a>
                <a href="reports/certificat.php">Certificats</a>
                <a href="settings/api_bible.php">API Bible</a>
            </div>
            <div class="nav-user">
                <span>👤 <?php echo h($admin['prenom'] . ' ' . $admin['nom']); ?></span>
                <a href="auth/logout.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container admin-container">
        <div class="admin-header">
            <h1>📊 Tableau de bord Administrateur</h1>
            <p>Vue d'ensemble de la plateforme VOP</p>
        </div>
        
        <!-- Statistiques principales -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_utilisateurs']; ?></h3>
                    <p>Total Utilisateurs</p>
                    <small>+<?php echo $stats['nouveaux_utilisateurs']; ?> cette semaine</small>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_lecons']; ?></h3>
                    <p>Leçons Disponibles</p>
                    <small><?php echo $stats['total_categories']; ?> catégories</small>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $stats['lecons_completees']; ?></h3>
                    <p>Leçons Complétées</p>
                    <small>Par tous les utilisateurs</small>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">🙏</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_prieres']; ?></h3>
                    <p>Demandes de Prière</p>
                    <small><?php echo $stats['prieres_en_attente']; ?> en attente</small>
                </div>
            </div>
            
            <div class="stat-card active">
                <div class="stat-icon">🔥</div>
                <div class="stat-info">
                    <h3><?php echo $stats['utilisateurs_actifs']; ?></h3>
                    <p>Utilisateurs Actifs</p>
                    <small>7 derniers jours</small>
                </div>
            </div>
        </div>
        
        <!-- Sections récentes -->
        <div class="admin-sections">
            <!-- Derniers utilisateurs -->
            <div class="admin-section">
                <h2>👥 Derniers Utilisateurs Inscrits</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($derniers_utilisateurs)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['date_inscription'])); ?></td>
                                    <td>
                                        <a href="users/voir.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-info">Voir</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <a href="users/liste.php" class="btn btn-secondary">Voir tous les utilisateurs →</a>
            </div>
            
            <!-- Dernières demandes de prière -->
            <div class="admin-section">
                <h2>🙏 Dernières Demandes de Prière</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($priere = mysqli_fetch_assoc($dernieres_prieres)): ?>
                                <tr>
                                    <td><?php echo h($priere['prenom'] . ' ' . $priere['nom']); ?></td>
                                    <td><?php echo h($priere['sujet']); ?></td>
                                    <td>
                                        <?php
                                        $badge_class = '';
                                        switch($priere['statut']) {
                                            case 'en_attente': $badge_class = 'badge-warning'; break;
                                            case 'en_priere': $badge_class = 'badge-info'; break;
                                            case 'exaucee': $badge_class = 'badge-success'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $priere['statut'])); ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($priere['date_creation'])); ?></td>
                                    <td>
                                        <a href="prayers/voir.php?id=<?php echo $priere['id']; ?>" class="btn btn-small btn-info">Voir</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <a href="prayers/liste.php" class="btn btn-secondary">Voir toutes les demandes →</a>
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
