<?php
// Script d'exécution de la migration des versets
// Usage : php database/run_migration.php
require_once dirname(__DIR__) . '/includes/config.php';

$conn = get_db_connection();

$sql = file_get_contents(__DIR__ . '/migration_versets.sql');

if (!$sql) {
    die("Impossible de lire le fichier SQL.\n");
}

// Exécuter les requêtes multiples
if (mysqli_multi_query($conn, $sql)) {
    do {
        if ($result = mysqli_store_result($conn)) {
            while ($row = mysqli_fetch_row($result)) {
                echo implode(', ', $row) . "\n";
            }
            mysqli_free_result($result);
        }
        $err = mysqli_error($conn);
        if ($err) {
            echo "Erreur: $err\n";
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));
} else {
    echo "Erreur lors de l'exécution: " . mysqli_error($conn) . "\n";
}

// Compter les versets ajoutés
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM versets");
$row = mysqli_fetch_assoc($result);
echo "\nTotal versets en base: " . $row['total'] . "\n";

mysqli_close($conn);
echo "Migration terminée.\n";
