<?php
require_once '../../includes/config.php';

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id) {
    redirect('liste.php');
}

$conn = get_db_connection();

// Vérifier que l'utilisateur existe et n'est pas admin
$query = "SELECT id FROM utilisateurs WHERE id = ? AND role = 'utilisateur'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // Supprimer l'utilisateur (les données liées seront supprimées en cascade)
    $delete_query = "DELETE FROM utilisateurs WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

redirect('liste.php');
?>
