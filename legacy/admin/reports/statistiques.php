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

// Statistiques globales
$stats = [];

// Total utilisateurs
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur'";
$result = mysqli_query($conn, $query);
$stats['total_utilisateurs'] = mysqli_fetch_assoc($result)['total'];

// Utilisateurs actifs (7 derniers jours)
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur' AND derniere_connexion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = mysqli_query($conn, $query);
$stats['utilisateurs_actifs_7j'] = mysqli_fetch_assoc($result)['total'];

// Utilisateurs actifs (30 derniers jours)
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur' AND derniere_connexion >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = mysqli_query($conn, $query);
$stats['utilisateurs_actifs_30j'] = mysqli_fetch_assoc($result)['total'];

// Total leçons
$query = "SELECT COUNT(*) as total FROM lecons";
$result = mysqli_query($conn, $query);
$stats['total_lecons'] = mysqli_fetch_assoc($result)['total'];

// Total questions
$query = "SELECT COUNT(*) as total FROM questions";
$result = mysqli_query($conn, $query);
$stats['total_questions'] = mysqli_fetch_assoc($result)['total'];

// Leçons complétées
$query = "SELECT COUNT(*) as total FROM progression_lecons WHERE statut = 'termine'";
$result = mysqli_query($conn, $query);
$stats['lecons_completees'] = mysqli_fetch_assoc($result)['total'];

// Score moyen global
$query = "SELECT AVG(score) as moyenne FROM progression_lecons WHERE statut = 'termine' AND score IS NOT NULL";
$result = mysqli_query($conn, $query);
$stats['score_moyen'] = mysqli_fetch_assoc($result)['moyenne'] ?? 0;

// Demandes de prière
$query = "SELECT COUNT(*) as total FROM demandes_priere";
$result = mysqli_query($conn, $query);
$stats['total_prieres'] = mysqli_fetch_assoc($result)['total'];

// Taux de complétion
$stats['taux_completion'] = $stats['total_utilisateurs'] > 0 
    ? ($stats['lecons_completees'] / ($stats['total_utilisateurs'] * $stats['total_lecons'])) * 100 
    : 0;

// Leçons les plus populaires
$query = "SELECT l.titre, c.nom as categorie, COUNT(pl.id) as nb_completions, AVG(pl.score) as score_moyen
          FROM lecons l
          INNER JOIN categories c ON l.categorie_id = c.id
          LEFT JOIN progression_lecons pl ON l.id = pl.lecon_id AND pl.statut = 'termine'
          GROUP BY l.id
          ORDER BY nb_completions DESC
          LIMIT 10";
$lecons_populaires = mysqli_query($conn, $query);

// Utilisateurs les plus actifs
$query = "SELECT u.nom, u.prenom, u.email, 
          COUNT(pl.id) as nb_lecons_terminees,
          AVG(pl.score) as score_moyen,
          u.date_inscription
          FROM utilisateurs u
          LEFT JOIN progression_lecons pl ON u.id = pl.utilisateur_id AND pl.statut = 'termine'
          WHERE u.role = 'utilisateur'
          GROUP BY u.id
          ORDER BY nb_lecons_terminees DESC
          LIMIT 10";
$utilisateurs_actifs = mysqli_query($conn, $query);

// Inscriptions par mois (6 derniers mois)
$query = "SELECT DATE_FORMAT(date_inscription, '%Y-%m') as mois, COUNT(*) as nb_inscriptions
          FROM utilisateurs
          WHERE role = 'utilisateur' AND date_inscription >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
          GROUP BY mois
          ORDER BY mois DESC";
$inscriptions_mois = mysqli_query($conn, $query);

// Progression par catégorie
$query = "SELECT c.nom as categorie,
          COUNT(DISTINCT l.id) as nb_lecons,
          COUNT(pl.id) as nb_completions,
          AVG(pl.score) as score_moyen
          FROM categories c
          LEFT JOIN lecons l ON c.id = l.categorie_id
          LEFT JOIN progression_lecons pl ON l.id = pl.lecon_id AND pl.statut = 'termine'
          GROUP BY c.id
          ORDER BY c.ordre ASC";
$stats_categories = mysqli_query($conn, $query);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques et Rapports - VOP Admin</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                <a href="../prayers/liste.php">Prières</a>
                <a href="statistiques.php" class="active">Rapports</a>
                <a href="palmares.php">Palmarès</a>
                <a href="certificat.php">Certificats</a>
            </div>
            <div class="nav-user">
                <span>👤 <?php echo h($admin['prenom'] . ' ' . $admin['nom']); ?></span>
                <a href="../auth/logout.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container admin-container">
        <div class="admin-header">
            <h1>📊 Statistiques et Rapports</h1>
            <p>Vue d'ensemble complète de la plateforme VOP</p>
        </div>
        
        <!-- Statistiques principales -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_utilisateurs']; ?></h3>
                    <p>Total Utilisateurs</p>
                    <small><?php echo $stats['utilisateurs_actifs_7j']; ?> actifs (7j)</small>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_lecons']; ?></h3>
                    <p>Leçons Disponibles</p>
                    <small><?php echo $stats['total_questions']; ?> questions</small>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $stats['lecons_completees']; ?></h3>
                    <p>Leçons Complétées</p>
                    <small><?php echo number_format($stats['taux_completion'], 1); ?>% taux</small>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['score_moyen'], 1); ?>%</h3>
                    <p>Score Moyen Global</p>
                    <small>Toutes leçons confondues</small>
                </div>
            </div>
            
            <div class="stat-card active">
                <div class="stat-icon">🔥</div>
                <div class="stat-info">
                    <h3><?php echo $stats['utilisateurs_actifs_30j']; ?></h3>
                    <p>Actifs (30 jours)</p>
                    <small><?php echo number_format(($stats['utilisateurs_actifs_30j'] / max($stats['total_utilisateurs'], 1)) * 100, 1); ?>% du total</small>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">🙏</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_prieres']; ?></h3>
                    <p>Demandes de Prière</p>
                    <small>Total enregistré</small>
                </div>
            </div>
        </div>
        
        <!-- Graphiques -->
        <div class="charts-grid">
            <!-- Graphique des inscriptions par mois -->
            <div class="admin-section chart-container">
                <h2>📈 Évolution des Inscriptions (6 derniers mois)</h2>
                <canvas id="inscriptionsChart"></canvas>
            </div>
            
            <!-- Graphique des scores par catégorie -->
            <div class="admin-section chart-container">
                <h2>📊 Scores Moyens par Catégorie</h2>
                <canvas id="categoriesChart"></canvas>
            </div>
        </div>
        
        <div class="charts-grid">
            <!-- Graphique des utilisateurs actifs -->
            <div class="admin-section chart-container">
                <h2>👥 Répartition des Utilisateurs</h2>
                <canvas id="utilisateursChart"></canvas>
            </div>
            
            <!-- Graphique des leçons populaires -->
            <div class="admin-section chart-container">
                <h2>🏆 Top 5 Leçons les Plus Populaires</h2>
                <canvas id="leconsPopulairesChart"></canvas>
            </div>
        </div>
        
        <!-- Leçons les plus populaires -->
        <div class="admin-section">
            <h2>🏆 Top 10 Leçons les Plus Populaires</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Catégorie</th>
                            <th>Titre</th>
                            <th>Complétions</th>
                            <th>Score Moyen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rang = 1;
                        while ($lecon = mysqli_fetch_assoc($lecons_populaires)): 
                        ?>
                            <tr>
                                <td><strong>#<?php echo $rang++; ?></strong></td>
                                <td><span class="badge badge-info"><?php echo h($lecon['categorie']); ?></span></td>
                                <td><?php echo h($lecon['titre']); ?></td>
                                <td><span class="badge badge-success"><?php echo $lecon['nb_completions']; ?> fois</span></td>
                                <td>
                                    <?php if ($lecon['score_moyen']): ?>
                                        <span class="badge badge-warning"><?php echo number_format($lecon['score_moyen'], 1); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Utilisateurs les plus actifs -->
        <div class="admin-section">
            <h2>⭐ Top 10 Utilisateurs les Plus Actifs</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Leçons Terminées</th>
                            <th>Score Moyen</th>
                            <th>Membre Depuis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rang = 1;
                        while ($user = mysqli_fetch_assoc($utilisateurs_actifs)): 
                        ?>
                            <tr>
                                <td><strong>#<?php echo $rang++; ?></strong></td>
                                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge badge-success"><?php echo $user['nb_lecons_terminees']; ?></span></td>
                                <td>
                                    <?php if ($user['score_moyen']): ?>
                                        <span class="badge badge-warning"><?php echo number_format($user['score_moyen'], 1); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Inscriptions par mois -->
        <div class="admin-section">
            <h2>📈 Inscriptions des 6 Derniers Mois</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th>Nombre d'Inscriptions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($mois = mysqli_fetch_assoc($inscriptions_mois)): ?>
                            <tr>
                                <td><strong><?php echo date('F Y', strtotime($mois['mois'] . '-01')); ?></strong></td>
                                <td><span class="badge badge-primary"><?php echo $mois['nb_inscriptions']; ?> nouveaux</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Statistiques par catégorie -->
        <div class="admin-section">
            <h2>📚 Performance par Catégorie</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Nombre de Leçons</th>
                            <th>Total Complétions</th>
                            <th>Score Moyen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cat = mysqli_fetch_assoc($stats_categories)): ?>
                            <tr>
                                <td><strong><?php echo h($cat['categorie']); ?></strong></td>
                                <td><span class="badge badge-info"><?php echo $cat['nb_lecons']; ?> leçons</span></td>
                                <td><span class="badge badge-success"><?php echo $cat['nb_completions']; ?> fois</span></td>
                                <td>
                                    <?php if ($cat['score_moyen']): ?>
                                        <span class="badge badge-warning"><?php echo number_format($cat['score_moyen'], 1); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
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
    
    <script>
        // Préparer les données pour les graphiques
        <?php
        // Récupérer les données pour les graphiques
        $conn = get_db_connection();
        
        // Inscriptions par mois
        mysqli_data_seek($inscriptions_mois, 0);
        $mois_labels = [];
        $mois_data = [];
        while ($row = mysqli_fetch_assoc($inscriptions_mois)) {
            $mois_labels[] = date('M Y', strtotime($row['mois'] . '-01'));
            $mois_data[] = $row['nb_inscriptions'];
        }
        $mois_labels = array_reverse($mois_labels);
        $mois_data = array_reverse($mois_data);
        
        // Scores par catégorie
        mysqli_data_seek($stats_categories, 0);
        $cat_labels = [];
        $cat_scores = [];
        $cat_completions = [];
        while ($row = mysqli_fetch_assoc($stats_categories)) {
            $cat_labels[] = $row['categorie'];
            $cat_scores[] = round($row['score_moyen'] ?? 0, 1);
            $cat_completions[] = $row['nb_completions'];
        }
        
        // Top 5 leçons populaires
        mysqli_data_seek($lecons_populaires, 0);
        $lecons_labels = [];
        $lecons_data = [];
        $count = 0;
        while ($row = mysqli_fetch_assoc($lecons_populaires)) {
            if ($count >= 5) break;
            $lecons_labels[] = substr($row['titre'], 0, 30) . (strlen($row['titre']) > 30 ? '...' : '');
            $lecons_data[] = $row['nb_completions'];
            $count++;
        }
        
        mysqli_close($conn);
        ?>
        
        const moisLabels = <?php echo json_encode($mois_labels); ?>;
        const moisData = <?php echo json_encode($mois_data); ?>;
        const catLabels = <?php echo json_encode($cat_labels); ?>;
        const catScores = <?php echo json_encode($cat_scores); ?>;
        const catCompletions = <?php echo json_encode($cat_completions); ?>;
        const leconsLabels = <?php echo json_encode($lecons_labels); ?>;
        const leconsData = <?php echo json_encode($lecons_data); ?>;
        
        // Configuration des couleurs
        const colors = {
            primary: 'rgba(52, 152, 219, 0.8)',
            success: 'rgba(46, 204, 113, 0.8)',
            warning: 'rgba(241, 196, 15, 0.8)',
            danger: 'rgba(231, 76, 60, 0.8)',
            info: 'rgba(155, 89, 182, 0.8)',
            gradient: [
                'rgba(52, 152, 219, 0.8)',
                'rgba(46, 204, 113, 0.8)',
                'rgba(241, 196, 15, 0.8)',
                'rgba(231, 76, 60, 0.8)',
                'rgba(155, 89, 182, 0.8)'
            ]
        };
        
        // 1. Graphique des inscriptions par mois (Line Chart)
        const inscriptionsCtx = document.getElementById('inscriptionsChart').getContext('2d');
        new Chart(inscriptionsCtx, {
            type: 'line',
            data: {
                labels: moisLabels,
                datasets: [{
                    label: 'Nouvelles Inscriptions',
                    data: moisData,
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // 2. Graphique des scores par catégorie (Bar Chart)
        const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
        new Chart(categoriesCtx, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Score Moyen (%)',
                    data: catScores,
                    backgroundColor: colors.gradient,
                    borderColor: colors.gradient.map(c => c.replace('0.8', '1')),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // 3. Graphique des utilisateurs (Doughnut Chart)
        const utilisateursCtx = document.getElementById('utilisateursChart').getContext('2d');
        new Chart(utilisateursCtx, {
            type: 'doughnut',
            data: {
                labels: ['Actifs (7j)', 'Actifs (30j)', 'Inactifs'],
                datasets: [{
                    data: [
                        <?php echo $stats['utilisateurs_actifs_7j']; ?>,
                        <?php echo $stats['utilisateurs_actifs_30j'] - $stats['utilisateurs_actifs_7j']; ?>,
                        <?php echo $stats['total_utilisateurs'] - $stats['utilisateurs_actifs_30j']; ?>
                    ],
                    backgroundColor: [
                        colors.success,
                        colors.warning,
                        'rgba(149, 165, 166, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $stats['total_utilisateurs']; ?>;
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // 4. Graphique des leçons populaires (Horizontal Bar Chart)
        const leconsPopulairesCtx = document.getElementById('leconsPopulairesChart').getContext('2d');
        new Chart(leconsPopulairesCtx, {
            type: 'bar',
            data: {
                labels: leconsLabels,
                datasets: [{
                    label: 'Nombre de Complétions',
                    data: leconsData,
                    backgroundColor: colors.success,
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
