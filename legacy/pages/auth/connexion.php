<?php
require_once '../../includes/config.php';

// Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
if (est_connecte()) {
    redirect('../lessons/dashboard.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreurs[] = "Veuillez remplir tous les champs.";
    } else {
        $conn = get_db_connection();
        
        $query = "SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Mettre à jour la dernière connexion
                $update_query = "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
                
                // Vérifier le rôle et rediriger en conséquence
                if ($user['role'] === 'admin') {
                    // Connexion admin
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_nom'] = $user['nom'];
                    $_SESSION['admin_prenom'] = $user['prenom'];
                    
                    redirect('../../admin/dashboard.php');
                } else {
                    // Connexion utilisateur normal
                    $_SESSION['utilisateur_id'] = $user['id'];
                    $_SESSION['utilisateur_email'] = $user['email'];
                    $_SESSION['utilisateur_nom'] = $user['nom'];
                    $_SESSION['utilisateur_prenom'] = $user['prenom'];
                    
                    redirect('../lessons/dashboard.php');
                }
            } else {
                $erreurs[] = "Email ou mot de passe incorrect.";
            }
        } else {
            $erreurs[] = "Email ou mot de passe incorrect.";
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
    <title>Connexion - VOP, Étude Biblique par Correspondance</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <div class="logo-small">
                <h2>VOP</h2>
                <p>Étude Biblique par Correspondance</p>
            </div>
            
            <h3>Se connecter</h3>
            
            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?php echo h($erreur); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? h($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
            
            <p class="auth-link">Vous n'avez pas de compte? <a href="inscription.php">S'inscrire</a></p>
            <p class="auth-link"><a href="../../index.php">Retour à l'accueil</a></p>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VOP</h3>
                    <p>Étude Biblique par Correspondance</p>
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
                        <li><a href="inscription.php">S'inscrire</a></li>
                        <li><a href="connexion.php">Se connecter</a></li>
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Nous contacter</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Étude Biblique par Correspondance NKF | Développé par ML DATA +243 982 401 411</p>
                <p class="footer-verse">"Car la parole de Dieu est vivante et efficace" - Hébreux 4:12</p>
            </div>
        </div>
    </footer>
</body>
</html>
