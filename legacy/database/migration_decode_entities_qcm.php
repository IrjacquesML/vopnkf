<?php
/**
 * Migration : décoder les entités HTML dans les données QCM existantes.
 *
 * Les questions et options ont pu être enregistrées avec l'ancien clean_input()
 * (htmlspecialchars). On stocke désormais le texte brut et on échappe à l'affichage.
 * Ce script convertit les données déjà en base : html_entity_decode sur question
 * et texte_option, puis UPDATE.
 *
 * À exécuter une seule fois (en CLI ou via navigateur, protégé par mot de passe ou
 * accès admin). Vérifier la BDD avant/après.
 */
require_once __DIR__ . '/../includes/config.php';

$conn = get_db_connection();

function decode_entities($s) {
    return html_entity_decode((string) $s, ENT_QUOTES, 'UTF-8');
}

$updated_questions = 0;
$updated_options = 0;
$errors = [];

// 1. Questions
$q = mysqli_query($conn, "SELECT id, question FROM questions");
if (!$q) {
    $errors[] = 'Erreur SELECT questions: ' . mysqli_error($conn);
} else {
    $stmt = mysqli_prepare($conn, "UPDATE questions SET question = ? WHERE id = ?");
    if (!$stmt) {
        $errors[] = 'Erreur prepare UPDATE questions: ' . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_assoc($q)) {
            $decoded = decode_entities($row['question']);
            if ($decoded === $row['question']) {
                continue;
            }
            mysqli_stmt_bind_param($stmt, "si", $decoded, $row['id']);
            if (mysqli_stmt_execute($stmt)) {
                $updated_questions++;
            } else {
                $errors[] = 'UPDATE question id=' . $row['id'] . ': ' . mysqli_stmt_error($stmt);
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_free_result($q);
}

// 2. Options de réponse
$q = mysqli_query($conn, "SELECT id, texte_option FROM options_reponse");
if (!$q) {
    $errors[] = 'Erreur SELECT options_reponse: ' . mysqli_error($conn);
} else {
    $stmt = mysqli_prepare($conn, "UPDATE options_reponse SET texte_option = ? WHERE id = ?");
    if (!$stmt) {
        $errors[] = 'Erreur prepare UPDATE options_reponse: ' . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_assoc($q)) {
            $decoded = decode_entities($row['texte_option']);
            if ($decoded === $row['texte_option']) {
                continue;
            }
            mysqli_stmt_bind_param($stmt, "si", $decoded, $row['id']);
            if (mysqli_stmt_execute($stmt)) {
                $updated_options++;
            } else {
                $errors[] = 'UPDATE option id=' . $row['id'] . ': ' . mysqli_stmt_error($stmt);
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_free_result($q);
}

mysqli_close($conn);

// Sortie CLI ou HTML
$is_cli = php_sapi_name() === 'cli';
if ($is_cli) {
    echo "Migration decode entities QCM\n";
    echo "Questions mises à jour: $updated_questions\n";
    echo "Options mises à jour: $updated_options\n";
    if (!empty($errors)) {
        echo "Erreurs:\n";
        foreach ($errors as $e) echo "  - $e\n";
    }
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"><title>Migration QCM</title></head><body>";
    echo "<h1>Migration decode entities QCM</h1>";
    echo "<p>Questions mises à jour: <strong>" . (int) $updated_questions . "</strong></p>";
    echo "<p>Options mises à jour: <strong>" . (int) $updated_options . "</strong></p>";
    if (!empty($errors)) {
        echo "<h2>Erreurs</h2><ul>";
        foreach ($errors as $e) echo "<li>" . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . "</li>";
        echo "</ul>";
    }
    echo "<p><a href=\"../admin/lessons/liste.php\">Retour leçons</a></p></body></html>";
}
