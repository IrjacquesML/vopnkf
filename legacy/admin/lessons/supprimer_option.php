<?php
require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$option_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;
$lecon_id = isset($_GET['lecon_id']) ? intval($_GET['lecon_id']) : 0;

if (!$option_id || !$question_id || !$lecon_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Supprimer l'option
$delete_query = "DELETE FROM options_reponse WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $option_id);
mysqli_stmt_execute($delete_stmt);
mysqli_stmt_close($delete_stmt);

mysqli_close($conn);

redirect("modifier_question.php?id=$question_id&lecon_id=$lecon_id");
?>
