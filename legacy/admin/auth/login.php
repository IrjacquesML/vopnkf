<?php
require_once '../../includes/config.php';

// Si l'administrateur est déjà connecté, rediriger vers le tableau de bord
if (isset($_SESSION['admin_id'])) {
    redirect('../dashboard.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreurs[] = "Veuillez remplir tous les champs.";
    } else {
        $conn = get_db_connection();
        
        $query = "SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ? AND role = 'admin'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_nom'] = $user['nom'];
                $_SESSION['admin_prenom'] = $user['prenom'];
                
                // Mettre à jour la dernière connexion
                $update_query = "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
                
                redirect('../dashboard.php');
            } else {
                $erreurs[] = "Email ou mot de passe incorrect.";
            }
        } else {
            $erreurs[] = "Accès administrateur non autorisé.";
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
    <title>Connexion Admin - VOP</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <div class="logo-small">
                <h2>🔐 VOP Admin</h2>
                <p>Panneau d'Administration</p>
            </div>
            
            <h3>Connexion Administrateur</h3>
            
            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?php echo $erreur; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo h($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
            
            <p class="auth-link"><a href="../../index.php">← Retour au site</a></p>
        </div>
    </div>
</body>
</html>
