<?php
/**
 * API de traduction
 * Endpoint pour traduire du texte via AJAX
 */

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs dans la réponse JSON

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/config.php';
require_once '../includes/traduction.php';

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['texte']) || !isset($data['langue'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

$texte = $data['texte'];
$langue = $data['langue'];

// Valider la langue
$langues_valides = ['fr', 'en', 'es', 'pt', 'sw', 'ln', 'kg', 'ar', 'zh', 'de', 'it', 'ru'];
if (!in_array($langue, $langues_valides)) {
    http_response_code(400);
    echo json_encode(['error' => 'Langue non supportée']);
    exit;
}

// Traduire le texte
try {
    $traduction = traduire_texte($texte, $langue);
    
    echo json_encode([
        'success' => true,
        'traduction' => $traduction,
        'langue' => $langue,
        'texte_original' => $texte
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la traduction',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
