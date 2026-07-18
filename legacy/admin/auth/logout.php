<?php
require_once '../../includes/config.php';

// Détruire toutes les variables de session admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_nom']);
unset($_SESSION['admin_prenom']);

// Rediriger vers la page de connexion admin
redirect('login.php');
?>
