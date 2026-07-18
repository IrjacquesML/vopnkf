<?php
require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$question_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lecon_id = isset($_GET['lecon_id']) ? intval($_GET['lecon_id']) : 0;

if (!$question_id || !$lecon_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Supprimer la question (les options seront supprimées en cascade)
$delete_query = "DELETE FROM questions WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $question_id);
mysqli_stmt_execute($delete_stmt);
mysqli_stmt_close($delete_stmt);

mysqli_close($conn);

redirect("modifier.php?id=$lecon_id");
?>
