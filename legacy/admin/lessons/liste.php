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
$categorie_filter = isset($_GET['categorie']) ? intval($_GET['categorie']) : 0;
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$par_page = 20;
$offset = ($page - 1) * $par_page;

// Construction de la requête
$where = "WHERE 1=1";
if ($categorie_filter > 0) {
    $where .= " AND l.categorie_id = $categorie_filter";
}
if (!empty($search)) {
    $where .= " AND (l.titre LIKE '%$search%' OR c.nom LIKE '%$search%')";
}

// Total leçons
$count_query = "SELECT COUNT(*) as total FROM lecons l INNER JOIN categories c ON l.categorie_id = c.id $where";
$count_result = mysqli_query($conn, $count_query);
$total_lecons = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_lecons / $par_page);

// Récupérer les leçons
$query = "SELECT l.id, l.titre, l.ordre, l.date_creation, c.nom as categorie_nom, c.id as categorie_id,
          (SELECT COUNT(*) FROM questions q WHERE q.lecon_id = l.id) as nb_questions,
          (SELECT COUNT(*) FROM progression_lecons pl WHERE pl.lecon_id = l.id AND pl.statut = 'termine') as nb_completions
          FROM lecons l
          INNER JOIN categories c ON l.categorie_id = c.id
          $where
          ORDER BY c.ordre ASC, l.ordre ASC
          LIMIT $par_page OFFSET $offset";
$lecons = mysqli_query($conn, $query);

// Récupérer toutes les catégories pour le filtre
$categories_query = "SELECT id, nom FROM categories ORDER BY ordre ASC";
$categories = mysqli_query($conn, $categories_query);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Leçons - VOP Admin</title>
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
            <div>
                <h1>📚 Gestion des Leçons</h1>
                <p>Total: <?php echo $total_lecons; ?> leçons</p>
            </div>
            <a href="ajouter.php" class="btn btn-success">➕ Ajouter une Leçon</a>
        </div>
        
        <!-- Filtres et recherche -->
        <div class="admin-toolbar">
            <form method="GET" action="" class="filter-form">
                <select name="categorie" class="filter-select">
                    <option value="0">Toutes les catégories</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo h($cat['nom']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <input type="text" name="search" placeholder="Rechercher une leçon..." value="<?php echo h($search); ?>" class="search-input">
                
                <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                <?php if ($categorie_filter > 0 || !empty($search)): ?>
                    <a href="liste.php" class="btn btn-secondary">✖ Effacer</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Liste des leçons -->
        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Catégorie</th>
                            <th>Titre</th>
                            <th>Ordre</th>
                            <th>Questions</th>
                            <th>Complétions</th>
                            <th>Date Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($lecons) > 0): ?>
                            <?php while ($lecon = mysqli_fetch_assoc($lecons)): ?>
                                <tr>
                                    <td><?php echo $lecon['id']; ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo h($lecon['categorie_nom']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($lecon['titre']); ?></td>
                                    <td><span class="badge badge-warning">#<?php echo $lecon['ordre']; ?></span></td>
                                    <td><span class="badge badge-success"><?php echo $lecon['nb_questions']; ?> questions</span></td>
                                    <td><span class="badge badge-primary"><?php echo $lecon['nb_completions']; ?> fois</span></td>
                                    <td><?php echo date('d/m/Y', strtotime($lecon['date_creation'])); ?></td>
                                    <td>
                                        <a href="voir.php?id=<?php echo $lecon['id']; ?>" class="btn btn-small btn-info">👁 Voir</a>
                                        <a href="modifier.php?id=<?php echo $lecon['id']; ?>" class="btn btn-small btn-primary">✏️ Modifier</a>
                                        <a href="supprimer.php?id=<?php echo $lecon['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette leçon et toutes ses questions ?')">🗑️ Supprimer</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Aucune leçon trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $categorie_filter > 0 ? '&categorie=' . $categorie_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">← Précédent</a>
                    <?php endif; ?>
                    
                    <span class="pagination-info">Page <?php echo $page; ?> sur <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $categorie_filter > 0 ? '&categorie=' . $categorie_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">Suivant →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Statistiques par catégorie -->
        <div class="admin-section">
            <h2>📊 Statistiques par Catégorie</h2>
            <?php
            $conn = get_db_connection();
            $stats_query = "SELECT c.nom, c.ordre,
                           COUNT(l.id) as nb_lecons,
                           (SELECT COUNT(*) FROM questions q INNER JOIN lecons l2 ON q.lecon_id = l2.id WHERE l2.categorie_id = c.id) as nb_questions,
                           (SELECT COUNT(*) FROM progression_lecons pl INNER JOIN lecons l3 ON pl.lecon_id = l3.id WHERE l3.categorie_id = c.id AND pl.statut = 'termine') as nb_completions
                           FROM categories c
                           LEFT JOIN lecons l ON c.id = l.categorie_id
                           GROUP BY c.id
                           ORDER BY c.ordre ASC";
            $stats = mysqli_query($conn, $stats_query);
            mysqli_close($conn);
            ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Ordre</th>
                            <th>Nombre de Leçons</th>
                            <th>Total Questions</th>
                            <th>Total Complétions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($stat = mysqli_fetch_assoc($stats)): ?>
                            <tr>
                                <td><strong><?php echo h($stat['nom']); ?></strong></td>
                                <td><span class="badge badge-info">#<?php echo $stat['ordre']; ?></span></td>
                                <td><span class="badge badge-success"><?php echo $stat['nb_lecons']; ?></span></td>
                                <td><span class="badge badge-warning"><?php echo $stat['nb_questions']; ?></span></td>
                                <td><span class="badge badge-primary"><?php echo $stat['nb_completions']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
