<?php
require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$lecon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$lecon_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Supprimer la leçon (les questions et options seront supprimées en cascade)
$delete_query = "DELETE FROM lecons WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $lecon_id);
mysqli_stmt_execute($delete_stmt);
mysqli_stmt_close($delete_stmt);

mysqli_close($conn);

redirect('liste.php');
?>
