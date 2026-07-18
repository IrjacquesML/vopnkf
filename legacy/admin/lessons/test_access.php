<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test d'accès - Diagnostic</h1>";

// Test 1: Vérifier le chemin
echo "<h2>1. Chemin actuel</h2>";
echo "<p>" . __FILE__ . "</p>";

// Test 2: Vérifier l'inclusion de config
echo "<h2>2. Inclusion de config.php</h2>";
try {
    require_once '../../includes/config.php';
    echo "<p style='color: green;'>✓ Config chargé avec succès</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// Test 3: Vérifier la session
echo "<h2>3. Session</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'NON DÉFINI') . "</p>";
echo "<p>Session admin_nom: " . (isset($_SESSION['admin_nom']) ? $_SESSION['admin_nom'] : 'NON DÉFINI') . "</p>";
echo "<p>Session admin_prenom: " . (isset($_SESSION['admin_prenom']) ? $_SESSION['admin_prenom'] : 'NON DÉFINI') . "</p>";

// Test 4: Vérifier la connexion à la base de données
echo "<h2>4. Connexion à la base de données</h2>";
try {
    $conn = get_db_connection();
    echo "<p style='color: green;'>✓ Connexion réussie</p>";
    
    // Test de requête
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p style='color: green;'>✓ Nombre de catégories: " . $row['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Erreur de requête: " . mysqli_error($conn) . "</p>";
    }
    
    mysqli_close($conn);
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erreur de connexion: " . $e->getMessage() . "</p>";
}

// Test 5: Vérifier si on peut accéder à ajouter.php
echo "<h2>5. Test de redirection</h2>";
if (!isset($_SESSION['admin_id'])) {
    echo "<p style='color: red;'>✗ Vous n'êtes pas connecté en tant qu'admin. Vous serez redirigé vers la page de connexion.</p>";
    echo "<p><a href='../auth/login.php'>Aller à la page de connexion</a></p>";
} else {
    echo "<p style='color: green;'>✓ Vous êtes connecté en tant qu'admin</p>";
    echo "<p><a href='ajouter.php' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Accéder à la page Ajouter une Leçon</a></p>";
}

echo "<hr>";
echo "<p><a href='liste.php'>Retour à la liste des leçons</a></p>";
?>
