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

$conn = get_db_connection();

// Filtres
$statut_filter = isset($_GET['statut']) ? clean_input($_GET['statut']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$par_page = 20;
$offset = ($page - 1) * $par_page;

// Construction de la requête
$where = "WHERE 1=1";
if (!empty($statut_filter)) {
    $where .= " AND dp.statut = '$statut_filter'";
}
if (!empty($search)) {
    $where .= " AND (dp.sujet LIKE '%$search%' OR u.nom LIKE '%$search%' OR u.prenom LIKE '%$search%')";
}

// Total demandes
$count_query = "SELECT COUNT(*) as total FROM demandes_priere dp INNER JOIN utilisateurs u ON dp.utilisateur_id = u.id $where";
$count_result = mysqli_query($conn, $count_query);
$total_prieres = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_prieres / $par_page);

// Récupérer les demandes
$query = "SELECT dp.id, dp.sujet, dp.message, dp.est_anonyme, dp.statut, dp.date_creation,
          u.nom, u.prenom, u.email
          FROM demandes_priere dp
          INNER JOIN utilisateurs u ON dp.utilisateur_id = u.id
          $where
          ORDER BY dp.date_creation DESC
          LIMIT $par_page OFFSET $offset";
$prieres = mysqli_query($conn, $query);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Demandes de Prière - VOP Admin</title>
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
            <h1>🙏 Gestion des Demandes de Prière</h1>
            <p>Total: <?php echo $total_prieres; ?> demandes</p>
        </div>
        
        <!-- Filtres et recherche -->
        <div class="admin-toolbar">
            <form method="GET" action="" class="filter-form">
                <select name="statut" class="filter-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="en_priere" <?php echo $statut_filter === 'en_priere' ? 'selected' : ''; ?>>En prière</option>
                    <option value="exaucee" <?php echo $statut_filter === 'exaucee' ? 'selected' : ''; ?>>Exaucée</option>
                </select>
                
                <input type="text" name="search" placeholder="Rechercher..." value="<?php echo h($search); ?>" class="search-input">
                
                <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                <?php if (!empty($statut_filter) || !empty($search)): ?>
                    <a href="liste.php" class="btn btn-secondary">✖ Effacer</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Liste des demandes -->
        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Sujet</th>
                            <th>Statut</th>
                            <th>Anonyme</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($prieres) > 0): ?>
                            <?php while ($priere = mysqli_fetch_assoc($prieres)): ?>
                                <tr>
                                    <td><?php echo $priere['id']; ?></td>
                                    <td><?php echo h($priere['prenom'] . ' ' . $priere['nom']); ?></td>
                                    <td><?php echo h(substr($priere['sujet'], 0, 50)) . (strlen($priere['sujet']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <?php
                                        $badge_class = '';
                                        $statut_text = '';
                                        switch($priere['statut']) {
                                            case 'en_attente':
                                                $badge_class = 'badge-warning';
                                                $statut_text = 'En attente';
                                                break;
                                            case 'en_priere':
                                                $badge_class = 'badge-info';
                                                $statut_text = 'En prière';
                                                break;
                                            case 'exaucee':
                                                $badge_class = 'badge-success';
                                                $statut_text = 'Exaucée';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $statut_text; ?></span>
                                    </td>
                                    <td><?php echo $priere['est_anonyme'] ? '🔒 Oui' : 'Non'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($priere['date_creation'])); ?></td>
                                    <td>
                                        <a href="voir.php?id=<?php echo $priere['id']; ?>" class="btn btn-small btn-info">👁 Voir</a>
                                        <a href="modifier_statut.php?id=<?php echo $priere['id']; ?>" class="btn btn-small btn-primary">✏ Modifier</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucune demande de prière trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($statut_filter) ? '&statut=' . urlencode($statut_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">← Précédent</a>
                    <?php endif; ?>
                    
                    <span class="pagination-info">Page <?php echo $page; ?> sur <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($statut_filter) ? '&statut=' . urlencode($statut_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">Suivant →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
