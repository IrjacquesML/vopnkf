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

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$par_page = 20;
$offset = ($page - 1) * $par_page;

// Recherche
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$where = "WHERE role = 'utilisateur'";
if (!empty($search)) {
    $where .= " AND (nom LIKE '%$search%' OR prenom LIKE '%$search%' OR email LIKE '%$search%')";
}

// Total utilisateurs
$count_query = "SELECT COUNT(*) as total FROM utilisateurs $where";
$count_result = mysqli_query($conn, $count_query);
$total_users = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_users / $par_page);

// Récupérer les utilisateurs
$query = "SELECT u.id, u.nom, u.prenom, u.email, u.date_inscription, u.derniere_connexion,
          (SELECT COUNT(*) FROM progression_lecons pl WHERE pl.utilisateur_id = u.id AND pl.statut = 'termine') as lecons_terminees
          FROM utilisateurs u
          $where
          ORDER BY u.date_inscription DESC
          LIMIT $par_page OFFSET $offset";
$users = mysqli_query($conn, $query);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - VOP Admin</title>
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
            <h1>👥 Gestion des Utilisateurs</h1>
            <p>Total: <?php echo $total_users; ?> utilisateurs</p>
        </div>
        
        <!-- Barre de recherche -->
        <div class="admin-toolbar">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="Rechercher par nom, prénom ou email..." value="<?php echo h($search); ?>" class="search-input">
                <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                <?php if (!empty($search)): ?>
                    <a href="liste.php" class="btn btn-secondary">✖ Effacer</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Liste des utilisateurs -->
        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Leçons Terminées</th>
                            <th>Date Inscription</th>
                            <th>Dernière Connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($users) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo h($user['prenom'] . ' ' . $user['nom']); ?></td>
                                    <td><?php echo h($user['email']); ?></td>
                                    <td><span class="badge badge-info"><?php echo $user['lecons_terminees']; ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                    <td>
                                        <?php if ($user['derniere_connexion']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($user['derniere_connexion'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Jamais</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="voir.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-info">👁 Voir</a>
                                        <a href="supprimer.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">🗑 Supprimer</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun utilisateur trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">← Précédent</a>
                    <?php endif; ?>
                    
                    <span class="pagination-info">Page <?php echo $page; ?> sur <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">Suivant →</a>
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
