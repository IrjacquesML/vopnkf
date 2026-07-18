<?php
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$utilisateur_id = get_utilisateur_id();
$lecon_id = isset($_POST['lecon_id']) ? intval($_POST['lecon_id']) : 0;

if (!$lecon_id) {
    redirect('dashboard.php');
}

$conn = get_db_connection();

// Récupérer toutes les questions de la leçon
$query = "SELECT id FROM questions WHERE lecon_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$questions = [];
while ($q = mysqli_fetch_assoc($result)) {
    $questions[] = $q['id'];
}
mysqli_stmt_close($stmt);

// Supprimer les anciennes réponses pour cette leçon
$delete_query = "DELETE FROM reponses_utilisateurs WHERE utilisateur_id = ? AND lecon_id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "ii", $utilisateur_id, $lecon_id);
mysqli_stmt_execute($delete_stmt);
mysqli_stmt_close($delete_stmt);

// Enregistrer les nouvelles réponses et calculer le score
$total_questions = count($questions);
$bonnes_reponses = 0;

foreach ($questions as $question_id) {
    $option_id = isset($_POST["question_$question_id"]) ? intval($_POST["question_$question_id"]) : 0;
    
    if ($option_id) {
        // Vérifier si la réponse est correcte
        $check_query = "SELECT est_correcte FROM options_reponse WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $option_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $option = mysqli_fetch_assoc($check_result);
        $est_correcte = $option ? $option['est_correcte'] : 0;
        mysqli_stmt_close($check_stmt);
        
        if ($est_correcte) {
            $bonnes_reponses++;
        }
        
        // Enregistrer la réponse
        $insert_query = "INSERT INTO reponses_utilisateurs (utilisateur_id, question_id, option_id, lecon_id, est_correcte) 
                        VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "iiiii", $utilisateur_id, $question_id, $option_id, $lecon_id, $est_correcte);
        mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);
    }
}

// Calculer le score en pourcentage
$score = ($total_questions > 0) ? ($bonnes_reponses / $total_questions) * 100 : 0;

// Mettre à jour la progression de la leçon
$update_query = "UPDATE progression_lecons 
                SET statut = 'termine', score = ?, date_fin = NOW() 
                WHERE utilisateur_id = ? AND lecon_id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, "dii", $score, $utilisateur_id, $lecon_id);
mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

mysqli_close($conn);

// Rediriger vers la page de résultats
redirect("resultats.php?lecon_id=$lecon_id");
?>
