<?php
require_once 'includes/config.php';

// Si l'utilisateur est déjà connecté, rediriger selon son rôle
if (est_connecte()) {
    redirect('pages/lessons/dashboard.php');
}

// Si l'admin est connecté, rediriger vers le panneau admin
if (isset($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOP, Études Bibliques par Correspondance</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/spiritual-icons.css">
</head>
<body>
    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="logo">
                <img src="assets/img/logo-adventiste.jpg" alt="Logo Adventiste" class="hero-logo">
                <h1>VOP</h1>
                <p class="subtitle">Études Bibliques par Correspondance</p>
            </div>
            
            <div class="welcome-message">
                <h2>Tu as des problèmes qui t'empêchent de réaliser tes rêves?</h2>
                <p class="hope-message">Il y a de l'espoir en Jésus-Christ.</p>
                <p class="invitation">Connecte-toi pour commencer l'étude pour découvrir la vérité biblique.</p>
            </div>
            
            <div class="action-buttons">
                <a href="pages/auth/connexion.php" class="btn btn-primary">Se Connecter</a>
                <a href="pages/auth/inscription.php" class="btn btn-secondary">S'inscrire</a>
            </div>
            
            <div class="bible-verse verse-decoration">
                <p class="verse-text">"Venez à moi, vous tous qui êtes fatigués et chargés, et je vous donnerai du repos."</p>
                <p class="verse-reference">- Matthieu 11:28</p>
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
                    <h3>Contact</h3>
                    <p>📧 Email: contact@vop.org</p>
                    <p>📞 Téléphone: +243 961 420 201</p>
                    <p>📍 Adresse: Butembo/ Eglise Adventiste du 7e jour, RDC</p>
                </div>
                
                <div class="footer-section">
                    <h3>Liens Utiles</h3>
                    <ul class="footer-links">
                        <li><a href="pages/auth/inscription.php">S'inscrire</a></li>
                        <li><a href="pages/auth/connexion.php">Se connecter</a></li>
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Nous contacter</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Études Bibliques par Correspondance NKF | Développé par ML DATA +243 982 401 411</p>
                <p class="footer-verse">"Car la parole de Dieu est vivante et efficace" - Hébreux 4:12</p>
            </div>
        </div>
    </footer>

</body>
</html>