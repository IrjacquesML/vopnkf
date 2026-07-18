<?php
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

$utilisateur_id = get_utilisateur_id();
$utilisateur = get_utilisateur_info();
$erreurs = [];
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet = clean_input($_POST['sujet'] ?? '');
    $message = clean_input($_POST['message'] ?? '');
    $est_anonyme = isset($_POST['est_anonyme']) ? 1 : 0;
    
    // Validation
    if (empty($sujet)) {
        $erreurs[] = "Le sujet est requis.";
    }
    if (empty($message)) {
        $erreurs[] = "Le message est requis.";
    }
    if (strlen($message) < 10) {
        $erreurs[] = "Le message doit contenir au moins 10 caractères.";
    }
    
    // Si pas d'erreurs, enregistrer la demande
    if (empty($erreurs)) {
        $conn = get_db_connection();
        
        $query = "INSERT INTO demandes_priere (utilisateur_id, sujet, message, est_anonyme) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issi", $utilisateur_id, $sujet, $message, $est_anonyme);
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "Votre demande de prière a été envoyée avec succès. Nous prierons pour vous.";
            // Réinitialiser les champs
            $sujet = '';
            $message = '';
            $est_anonyme = 0;
        } else {
            $erreurs[] = "Erreur lors de l'envoi de la demande. Veuillez réessayer.";
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Prière - VOP, Études Bibliques par Correspondance</title>
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
                <a href="mes_prieres.php">Mes Prières</a>
                <a href="../auth/profil.php">Mon Profil</a>
                <a href="demande_priere.php" class="active">Nouvelle Demande</a>
            </div>
            <div class="nav-user">
                <span>Bienvenue, <?php echo h($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></span>
                <a href="../auth/deconnexion.php" class="btn btn-small">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container prayer-container">
        <div class="prayer-header">
            <h1>🙏 Demande de Prière</h1>
            <p>Partagez vos besoins de prière avec nous. Nous prierons pour vous.</p>
        </div>
        
        <div class="prayer-intro">
            <div class="prayer-verse">
                <p class="verse-text">"Ne vous inquiétez de rien; mais en toute chose faites connaître vos besoins à Dieu par des prières et des supplications, avec des actions de grâces."</p>
                <p class="verse-reference">- Philippiens 4:6</p>
            </div>
        </div>
        
        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo $erreur; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($succes): ?>
            <div class="alert alert-success">
                <?php echo $succes; ?>
            </div>
        <?php endif; ?>
        
        <div class="prayer-form-container">
            <form method="POST" action="" class="prayer-form">
                <div class="form-group">
                    <label for="sujet">Sujet de la prière *</label>
                    <input type="text" id="sujet" name="sujet" value="<?php echo isset($sujet) ? h($sujet) : ''; ?>" required placeholder="Ex: Guérison, Travail, Famille, etc.">
                </div>
                
                <div class="form-group">
                    <label for="message">Votre demande de prière *</label>
                    <textarea id="message" name="message" rows="8" required placeholder="Partagez votre besoin de prière en détail..."><?php echo isset($message) ? h($message) : ''; ?></textarea>
                    <small>Minimum 10 caractères</small>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="est_anonyme" value="1" <?php echo (isset($est_anonyme) && $est_anonyme) ? 'checked' : ''; ?>>
                        <span>Garder cette demande anonyme</span>
                    </label>
                    <small>Si coché, votre nom ne sera pas associé à cette demande</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Envoyer ma demande</button>
                    <a href="mes_prieres.php" class="btn btn-secondary">Voir mes demandes</a>
                </div>
            </form>
        </div>
        
        <div class="prayer-info">
            <h3>💝 Notre engagement</h3>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">🙏</div>
                    <h4>Prière quotidienne</h4>
                    <p>Nous prions quotidiennement pour toutes les demandes reçues.</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🔒</div>
                    <h4>Confidentialité</h4>
                    <p>Vos demandes sont traitées avec respect et confidentialité.</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">💬</div>
                    <h4>Suivi personnalisé</h4>
                    <p>Vous pouvez suivre l'état de vos demandes à tout moment.</p>
                </div>
            </div>
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
