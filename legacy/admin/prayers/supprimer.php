<?php
require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$priere_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$priere_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Supprimer la demande de prière
$delete_query = "DELETE FROM demandes_priere WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $priere_id);
mysqli_stmt_execute($delete_stmt);
mysqli_stmt_close($delete_stmt);

mysqli_close($conn);

redirect('liste.php');
?>
