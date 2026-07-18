<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vop_etude');

// Connexion à la base de données
function get_db_connection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Erreur de connexion à la base de données: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger le système d'internationalisation
require_once __DIR__ . '/i18n.php';

// Fonction pour vérifier si l'utilisateur est connecté
function est_connecte() {
    return isset($_SESSION['utilisateur_id']) || isset($_SESSION['admin_id']);
}

// Fonction pour obtenir l'ID de l'utilisateur connecté
function get_utilisateur_id() {
    if (isset($_SESSION['utilisateur_id'])) {
        return $_SESSION['utilisateur_id'];
    }
    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_id'];
    }
    return null;
}

// Fonction pour obtenir les informations de l'utilisateur connecté
function get_utilisateur_info() {
    if (!est_connecte()) {
        return null;
    }
    
    $conn = get_db_connection();
    $utilisateur_id = get_utilisateur_id();
    
    $query = "SELECT id, nom, prenom, email FROM utilisateurs WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $utilisateur_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $user;
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Prépare une chaîne pour le stockage en base (questions, options, texte utilisateur).
 * - Trim des espaces.
 * - Sanitisation UTF-8 : séquences invalides supprimées (iconv //IGNORE) pour éviter
 *   erreurs SQL, JSON ou affichage.
 * - Aucun échappement HTML : on stocke le texte brut. Les caractères { } [ ] ( ) " ' < > & / \ et Unicode sont conservés.
 *
 * Règle : à l'ENTRÉE, préparer puis stocker brut. À la SORTIE HTML, échapper avec h().
 */
function prepare_text_for_storage($data) {
    if (!is_string($data)) {
        return '';
    }
    $data = trim($data);
    if (function_exists('iconv')) {
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
        if ($clean !== false) {
            $data = $clean;
        }
    }
    return $data;
}

/**
 * Échappe une chaîne pour affichage HTML (prévention XSS).
 * Utilise htmlspecialchars avec ENT_QUOTES, ENT_SUBSTITUTE et UTF-8.
 * À utiliser pour toute donnée utilisateur ou BDD insérée dans du HTML (contenu, attributs).
 */
function h($s) {
    $flags = ENT_QUOTES | ENT_SUBSTITUTE;
    return htmlspecialchars((string) $s, $flags, 'UTF-8');
}

/**
 * @deprecated Utiliser prepare_text_for_storage() pour les champs texte stockés.
 * clean_input fait désormais uniquement trim (plus de stripslashes/htmlspecialchars)
 * pour éviter la corruption des caractères spéciaux et le double encodage.
 */
function clean_input($data) {
    return prepare_text_for_storage($data);
}

// ============================================================
// Configuration de l'API Bible externe
// ============================================================
// Le système utilise 3 sources de versets par ordre de priorité :
//  1. Cache local (table `versets`) — versets déjà récupérés
//  2. scripture.api.bible — Louis Segond 1910 EN FRANÇAIS (clé gratuite)
//     → Inscription sur https://scripture.api.bible/ (5000 req/jour)
//  3. bible-api.com — fallback automatique SANS clé (texte anglais WEB)
//
// Sans clé BIBLE_API_KEY, les versets non cachés s'affichent en anglais.
// ============================================================
define('BIBLE_API_KEY', '');  // Optionnel — clé scripture.api.bible pour LSG français
define('BIBLE_API_URL', 'https://api.scripture.api.bible/v1');
// ID de la Bible LSG sur scripture.api.bible (auto-découvert si vide)
// Valeur connue : '61fd76eafa1ef8b2-01' — vérifiez via l'admin si la détection échoue
define('BIBLE_LSG_ID', '');
// Durée du cache des versets en jours (0 = cache infini)
define('BIBLE_CACHE_DAYS', 30);

/**
 * Fait un appel HTTP vers scripture.api.bible.
 * Retourne [code_http (int), tableau_données | null, message_erreur (string)].
 */
function bible_api_call(string $endpoint): array {
    if (empty(BIBLE_API_KEY)) {
        return [0, null, 'Clé API non configurée. Rendez-vous dans Admin > Paramètres Bible.'];
    }
    $url = BIBLE_API_URL . $endpoint;
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        // SSL vérification désactivée pour XAMPP local ; à activer en production
        // avec un fichier cacert.pem configuré dans php.ini (curl.cainfo)
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BIBLE_API_KEY,
            'Accept: application/json',
        ],
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return [0, null, "cURL : $err"];
    }
    $json = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [$code, null, "Réponse API non-JSON (HTTP $code): " . substr($body, 0, 200)];
    }
    return [$code, $json, ''];
}

/**
 * Découvre et retourne l'ID de la Bible Louis Segond 1910 sur scripture.api.bible.
 * Le résultat est mis en cache dans la session pour éviter des appels répétés.
 * Retourne null si non trouvé ou si la clé est invalide.
 */
function bible_get_lsg_id(): ?string {
    // 1. Valeur explicite dans config
    if (!empty(BIBLE_LSG_ID)) {
        return BIBLE_LSG_ID;
    }
    // 2. Cache session
    if (!empty($_SESSION['_bible_lsg_id'])) {
        return $_SESSION['_bible_lsg_id'];
    }
    // 3. Appel API
    [$code, $data, $err] = bible_api_call('/bibles?language=fra&includeFullDetails=false');
    if ($code !== 200 || empty($data['data'])) {
        return null;
    }
    // Chercher Louis Segond 1910 (LSG) parmi les Bibles françaises
    $keywords = ['louis segond', 'lsg', 'segond 1910'];
    foreach ($data['data'] as $bible) {
        $name  = mb_strtolower($bible['name']        ?? '', 'UTF-8');
        $abbr  = mb_strtolower($bible['abbreviation'] ?? '', 'UTF-8');
        $nameL = mb_strtolower($bible['nameLocal']    ?? '', 'UTF-8');
        foreach ($keywords as $kw) {
            if (str_contains($name, $kw) || str_contains($nameL, $kw) || $abbr === 'lsg') {
                $id = $bible['id'];
                $_SESSION['_bible_lsg_id'] = $id;
                return $id;
            }
        }
    }
    // Fallback : première Bible française disponible
    if (!empty($data['data'][0]['id'])) {
        $_SESSION['_bible_lsg_id'] = $data['data'][0]['id'];
        return $data['data'][0]['id'];
    }
    return null;
}

/**
 * Mapping noms de livres français → identifiants OSIS (scripture.api.bible).
 */
function bible_livre_to_osis(string $livre): ?string {
    static $map = [
        'Genèse'             => 'GEN', 'Exode'            => 'EXO',
        'Lévitique'          => 'LEV', 'Nombres'          => 'NUM',
        'Deutéronome'        => 'DEU', 'Josué'            => 'JOS',
        'Juges'              => 'JDG', 'Ruth'             => 'RUT',
        '1 Samuel'           => '1SA', '2 Samuel'         => '2SA',
        '1 Rois'             => '1KI', '2 Rois'           => '2KI',
        '1 Chroniques'       => '1CH', '2 Chroniques'     => '2CH',
        'Esdras'             => 'EZR', 'Néhémie'          => 'NEH',
        'Esther'             => 'EST', 'Job'              => 'JOB',
        'Psaumes'            => 'PSA', 'Proverbes'        => 'PRO',
        'Ecclésiaste'        => 'ECC', 'Cantique'         => 'SNG',
        'Ésaïe'              => 'ISA', 'Jérémie'          => 'JER',
        'Lamentations'       => 'LAM', 'Ézéchiel'         => 'EZK',
        'Daniel'             => 'DAN', 'Osée'             => 'HOS',
        'Joël'               => 'JOL', 'Amos'             => 'AMO',
        'Abdias'             => 'OBA', 'Jonas'            => 'JON',
        'Michée'             => 'MIC', 'Nahum'            => 'NAH',
        'Habakuk'            => 'HAB', 'Sophonie'         => 'ZEP',
        'Aggée'              => 'HAG', 'Zacharie'         => 'ZEC',
        'Malachie'           => 'MAL', 'Matthieu'         => 'MAT',
        'Marc'               => 'MRK', 'Luc'              => 'LUK',
        'Jean'               => 'JHN', 'Actes'            => 'ACT',
        'Romains'            => 'ROM', '1 Corinthiens'    => '1CO',
        '2 Corinthiens'      => '2CO', 'Galates'          => 'GAL',
        'Éphésiens'          => 'EPH', 'Philippiens'      => 'PHP',
        'Colossiens'         => 'COL', '1 Thessaloniciens'=> '1TH',
        '2 Thessaloniciens'  => '2TH', '1 Timothée'       => '1TI',
        '2 Timothée'         => '2TI', 'Tite'             => 'TIT',
        'Philémon'           => 'PHM', 'Hébreux'          => 'HEB',
        'Jacques'            => 'JAS', '1 Pierre'         => '1PE',
        '2 Pierre'           => '2PE', '1 Jean'           => '1JN',
        '2 Jean'             => '2JN', '3 Jean'           => '3JN',
        'Jude'               => 'JUD', 'Apocalypse'       => 'REV',
    ];
    return $map[$livre] ?? null;
}

