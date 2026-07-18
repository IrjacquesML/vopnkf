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
$succes = '';
$erreurs = [];

// Récupérer la demande de prière
$query = "SELECT dp.*, u.nom, u.prenom FROM demandes_priere dp INNER JOIN utilisateurs u ON dp.utilisateur_id = u.id WHERE dp.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $priere_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$priere = mysqli_fetch_assoc($result);

if (!$priere) {
    mysqli_close($conn);
    redirect('liste.php');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_statut = clean_input($_POST['statut'] ?? '');
    
    if (empty($nouveau_statut) || !in_array($nouveau_statut, ['en_attente', 'en_priere', 'exaucee'])) {
        $erreurs[] = "Statut invalide.";
    } else {
        $update_query = "UPDATE demandes_priere SET statut = ?, date_modification = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $nouveau_statut, $priere_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $succes = "Le statut a été mis à jour avec succès.";
            $priere['statut'] = $nouveau_statut;
        } else {
            $erreurs[] = "Erreur lors de la mise à jour du statut.";
        }
        
        mysqli_stmt_close($update_stmt);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Statut - VOP Admin</title>
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
            <h1>✏ Modifier le Statut de la Demande</h1>
            <a href="voir.php?id=<?php echo $priere_id; ?>" class="btn btn-secondary">← Retour aux détails</a>
        </div>
        
        <?php if (!empty($succes)): ?>
            <div class="alert alert-success">
                <?php echo $succes; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo $erreur; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="admin-section">
            <div class="prayer-info-summary">
                <h3>Demande de: <?php echo h($priere['prenom'] . ' ' . $priere['nom']); ?></h3>
                <p><strong>Sujet:</strong> <?php echo h($priere['sujet']); ?></p>
            </div>
            
            <form method="POST" action="" class="admin-form">
                <div class="form-group">
                    <label for="statut">Nouveau Statut</label>
                    <select name="statut" id="statut" class="form-control" required>
                        <option value="en_attente" <?php echo $priere['statut'] === 'en_attente' ? 'selected' : ''; ?>>⏳ En attente</option>
                        <option value="en_priere" <?php echo $priere['statut'] === 'en_priere' ? 'selected' : ''; ?>>🙏 En prière</option>
                        <option value="exaucee" <?php echo $priere['statut'] === 'exaucee' ? 'selected' : ''; ?>>✅ Exaucée</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                    <a href="voir.php?id=<?php echo $priere_id; ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
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
