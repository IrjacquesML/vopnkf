<?php
/**
 * API interne : recuperation de versets bibliques.
 *
 * Architecture (par ordre de priorite) :
 *  1. Cache local (table `versets`) — LSG si disponible.
 *  2. scripture.api.bible — LSG francais (cle BIBLE_API_KEY requise, optionnel).
 *  3. bible-api.com — fallback gratuit SANS cle API (texte anglais WEB).
 *  La reponse inclut 'version' ('LSG' ou 'WEB') pour que l UI l affiche.
 *
 * Parametres GET :
 *  - reference : texte complet, ex. "Jean 3:16" ou "Jean 3:16-17"
 *  - livre     : nom du livre seul, ex. "Jean"
 *  - chapitre  : numero du chapitre (entier)
 *  - verset    : numero du verset ou plage, ex. "16" ou "16-17"
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Supprime les accents d'une chaine UTF-8 et met en minuscules.
 * Permet la recherche insensible aux accents dans les tables de mapping.
 */
function strip_accents(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    return str_replace(
        ['à','â','ä','æ','ç','é','è','ê','ë','î','ï','ô','œ','ù','û','ü','ÿ'],
        ['a','a','a','ae','c','e','e','e','e','i','i','o','oe','u','u','u','y'],
        $s
    );
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

function normaliser_livre(string $livre): string {
    $livre = trim($livre);
    $livre_lower = mb_strtolower($livre, 'UTF-8');
    $abreviations = [
        'ps'=>'Psaumes','psaume'=>'Psaumes','psaumes'=>'Psaumes',
        'pr'=>'Proverbes','proverbe'=>'Proverbes','proverbes'=>'Proverbes',
        'ec'=>'Ecclesiaste','ecclesiaste'=>'Ecclesiaste',
        'ct'=>'Cantique','cantique'=>'Cantique','cant'=>'Cantique',
        'es'=>'Esaie','esaie'=>'Esaie','isaie'=>'Esaie',
        'jr'=>'Jeremie','jeremie'=>'Jeremie',
        'la'=>'Lamentations','lamentation'=>'Lamentations','lamentations'=>'Lamentations',
        'ez'=>'Ezechiel','ezechiel'=>'Ezechiel',
        'da'=>'Daniel','daniel'=>'Daniel',
        'os'=>'Osee','osee'=>'Osee',
        'jl'=>'Joel','joel'=>'Joel',
        'am'=>'Amos','amos'=>'Amos',
        'ab'=>'Abdias','abdias'=>'Abdias',
        'jon'=>'Jonas','jonas'=>'Jonas',
        'mi'=>'Michee','michee'=>'Michee',
        'na'=>'Nahum','nahum'=>'Nahum',
        'ha'=>'Habakuk','habakuk'=>'Habakuk',
        'so'=>'Sophonie','sophonie'=>'Sophonie',
        'ag'=>'Aggee','aggee'=>'Aggee',
        'za'=>'Zacharie','zacharie'=>'Zacharie',
        'ma'=>'Malachie','malachie'=>'Malachie',
        'gen'=>'Genese','genese'=>'Genese',
        'ex'=>'Exode','exode'=>'Exode',
        'lev'=>'Levitique','levitique'=>'Levitique',
        'nb'=>'Nombres','nombres'=>'Nombres',
        'dt'=>'Deuteronome','deuteronome'=>'Deuteronome',
        'jos'=>'Josue','josue'=>'Josue',
        'jug'=>'Juges','juges'=>'Juges',
        'ru'=>'Ruth','ruth'=>'Ruth',
        '1sam'=>'1 Samuel','1 samuel'=>'1 Samuel','1samuel'=>'1 Samuel',
        '2sam'=>'2 Samuel','2 samuel'=>'2 Samuel','2samuel'=>'2 Samuel',
        '1rois'=>'1 Rois','1 rois'=>'1 Rois',
        '2rois'=>'2 Rois','2 rois'=>'2 Rois',
        '1ch'=>'1 Chroniques','1 chroniques'=>'1 Chroniques','1chroniques'=>'1 Chroniques',
        '2ch'=>'2 Chroniques','2 chroniques'=>'2 Chroniques','2chroniques'=>'2 Chroniques',
        'esd'=>'Esdras','esdras'=>'Esdras',
        'neh'=>'Nehemie','nehemie'=>'Nehemie',
        'est'=>'Esther','esther'=>'Esther',
        'job'=>'Job',
        'mt'=>'Matthieu','matthieu'=>'Matthieu','matt'=>'Matthieu',
        'mc'=>'Marc','marc'=>'Marc',
        'lc'=>'Luc','luc'=>'Luc',
        'jn'=>'Jean','jean'=>'Jean',
        'ac'=>'Actes','actes'=>'Actes',
        'ro'=>'Romains','romains'=>'Romains',
        '1co'=>'1 Corinthiens','1 corinthiens'=>'1 Corinthiens','1corinthiens'=>'1 Corinthiens',
        '2co'=>'2 Corinthiens','2 corinthiens'=>'2 Corinthiens','2corinthiens'=>'2 Corinthiens',
        'ga'=>'Galates','galates'=>'Galates',
        'ep'=>'Ephesiens','ephesiens'=>'Ephesiens',
        'ph'=>'Philippiens','philippiens'=>'Philippiens',
        'col'=>'Colossiens','colossiens'=>'Colossiens',
        '1th'=>'1 Thessaloniciens','1 thessaloniciens'=>'1 Thessaloniciens','1thessaloniciens'=>'1 Thessaloniciens',
        '2th'=>'2 Thessaloniciens','2 thessaloniciens'=>'2 Thessaloniciens','2thessaloniciens'=>'2 Thessaloniciens',
        '1tim'=>'1 Timothee','1 timothee'=>'1 Timothee','1timothee'=>'1 Timothee',
        '2tim'=>'2 Timothee','2 timothee'=>'2 Timothee','2timothee'=>'2 Timothee',
        'ti'=>'Tite','tite'=>'Tite',
        'phm'=>'Philemon','philemon'=>'Philemon',
        'he'=>'Hebreux','hebreux'=>'Hebreux',
        'ja'=>'Jacques','jacques'=>'Jacques',
        '1pi'=>'1 Pierre','1 pierre'=>'1 Pierre','1pierre'=>'1 Pierre',
        '2pi'=>'2 Pierre','2 pierre'=>'2 Pierre','2pierre'=>'2 Pierre',
        '1jn'=>'1 Jean','1 jean'=>'1 Jean','1jean'=>'1 Jean',
        '2jn'=>'2 Jean','2 jean'=>'2 Jean','2jean'=>'2 Jean',
        '3jn'=>'3 Jean','3 jean'=>'3 Jean','3jean'=>'3 Jean',
        'ju'=>'Jude','jude'=>'Jude',
        'ap'=>'Apocalypse','apocalypse'=>'Apocalypse','apoc'=>'Apocalypse',
        'tim'=>'Timothee','cor'=>'Corinthiens','th'=>'Thessaloniciens',
        'sam'=>'Samuel','roi'=>'Rois','rois'=>'Rois','chr'=>'Chroniques','pi'=>'Pierre',
    ];
    // 1) Correspondance exacte (insensible à la casse)
    if (isset($abreviations[$livre_lower])) {
        return $abreviations[$livre_lower];
    }
    // 2) Correspondance sans accents (ex. "Timothée" → "timothee" → trouvé)
    $livre_ascii = strip_accents($livre_lower);
    if ($livre_ascii !== $livre_lower && isset($abreviations[$livre_ascii])) {
        return $abreviations[$livre_ascii];
    }
    // 3) Livre numéroté ("2 Timothée", "1 Corinthiens", etc.)
    if (preg_match('/^(\d+)\s+(.+)$/u', $livre, $m)) {
        $num      = $m[1];
        $nom_lower = mb_strtolower(trim($m[2]), 'UTF-8');
        if (isset($abreviations[$nom_lower])) {
            return $num . ' ' . $abreviations[$nom_lower];
        }
        $nom_ascii = strip_accents($nom_lower);
        if ($nom_ascii !== $nom_lower && isset($abreviations[$nom_ascii])) {
            return $num . ' ' . $abreviations[$nom_ascii];
        }
        // 4) Clé composée "2 timothee" dans la table (ex. '2 timothee'=>'2 Timothee')
        $key_full = $num . ' ' . $nom_ascii;
        if (isset($abreviations[$key_full])) {
            return $abreviations[$key_full];
        }
    }
    return mb_convert_case($livre, MB_CASE_TITLE, 'UTF-8');
}

/**
 * Mapping noms de livres francais → noms anglais pour bible-api.com.
 */
function livre_to_english(string $livre): string {
    // Normaliser les accents pour la recherche dans la map (ex. "Ésaïe" → "Esaie")
    $livre_norm = $livre;
    if (function_exists('strip_accents')) {
        // Construire le nom normalisé en préservant la casse initiale
        $stripped = strip_accents($livre); // minuscules sans accents
        // Capitaliser chaque mot séparé par un espace pour correspondre aux clés de la map
        $livre_norm = implode(' ', array_map('ucfirst', explode(' ', $stripped)));
        // Restaurer les chiffres en début ("2 Timothy" → la casse du chiffre n'importe pas)
    }
    static $map = [
        'Genese'=>'Genesis','Exode'=>'Exodus','Levitique'=>'Leviticus',
        'Nombres'=>'Numbers','Deuteronome'=>'Deuteronomy','Josue'=>'Joshua',
        'Juges'=>'Judges','Ruth'=>'Ruth','1 Samuel'=>'1 Samuel','2 Samuel'=>'2 Samuel',
        '1 Rois'=>'1 Kings','2 Rois'=>'2 Kings',
        '1 Chroniques'=>'1 Chronicles','2 Chroniques'=>'2 Chronicles',
        'Esdras'=>'Ezra','Nehemie'=>'Nehemiah','Esther'=>'Esther','Job'=>'Job',
        'Psaumes'=>'Psalms','Proverbes'=>'Proverbs','Ecclesiaste'=>'Ecclesiastes',
        'Cantique'=>'Song of Solomon','Esaie'=>'Isaiah','Jeremie'=>'Jeremiah',
        'Lamentations'=>'Lamentations','Ezechiel'=>'Ezekiel','Daniel'=>'Daniel',
        'Osee'=>'Hosea','Joel'=>'Joel','Amos'=>'Amos','Abdias'=>'Obadiah',
        'Jonas'=>'Jonah','Michee'=>'Micah','Nahum'=>'Nahum','Habakuk'=>'Habakkuk',
        'Sophonie'=>'Zephaniah','Aggee'=>'Haggai','Zacharie'=>'Zechariah',
        'Malachie'=>'Malachi','Matthieu'=>'Matthew','Marc'=>'Mark','Luc'=>'Luke',
        'Jean'=>'John','Actes'=>'Acts','Romains'=>'Romans',
        '1 Corinthiens'=>'1 Corinthians','2 Corinthiens'=>'2 Corinthians',
        'Galates'=>'Galatians','Ephesiens'=>'Ephesians','Philippiens'=>'Philippians',
        'Colossiens'=>'Colossians','1 Thessaloniciens'=>'1 Thessalonians',
        '2 Thessaloniciens'=>'2 Thessalonians','1 Timothee'=>'1 Timothy',
        '2 Timothee'=>'2 Timothy','Tite'=>'Titus','Philemon'=>'Philemon',
        'Hebreux'=>'Hebrews','Jacques'=>'James','1 Pierre'=>'1 Peter','2 Pierre'=>'2 Peter',
        '1 Jean'=>'1 John','2 Jean'=>'2 John','3 Jean'=>'3 John','Jude'=>'Jude',
        'Apocalypse'=>'Revelation',
    ];
    return $map[$livre] ?? $map[$livre_norm] ?? $livre;
}

// Lecture des parametres
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : '';
$livre     = isset($_GET['livre'])     ? trim($_GET['livre'])     : '';
$chapitre  = isset($_GET['chapitre'])  ? intval($_GET['chapitre']): 0;
$verset    = isset($_GET['verset'])    ? trim($_GET['verset'])    : '';

if (empty($reference) && (empty($livre) || $chapitre <= 0 || empty($verset))) {
    echo json_encode(['success' => false, 'message' => 'Reference manquante'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!empty($livre)) {
    $livre = normaliser_livre($livre);
}

// Plage de versets
$verset_debut = 0;
$verset_fin   = 0;
if (preg_match('/^(\d+)(?:-(\d+))?$/', $verset, $m)) {
    $verset_debut = (int) $m[1];
    $verset_fin   = isset($m[2]) ? (int) $m[2] : $verset_debut;
}
if ($verset_debut <= 0) {
    $verset_debut = $verset_fin = intval($verset);
}

$ref_canonique = '';
if (!empty($livre) && $chapitre > 0 && $verset_debut > 0) {
    $ref_canonique = $verset_debut < $verset_fin
        ? "$livre $chapitre:$verset_debut-$verset_fin"
        : "$livre $chapitre:$verset_debut";
} elseif (!empty($reference)) {
    $ref_canonique = $reference;
}

// Cache DB : chercher par livre+chapitre+verset
function cache_chercher(mysqli $conn, string $livre, int $chapitre, int $verset_debut): ?array {
    if (!$livre || !$chapitre || !$verset_debut) return null;
    $cond = '';
    if (defined('BIBLE_CACHE_DAYS') && BIBLE_CACHE_DAYS > 0) {
        $days = (int) BIBLE_CACHE_DAYS;
        $cond = " AND (cached_at IS NULL OR cached_at >= DATE_SUB(NOW(), INTERVAL $days DAY))";
    }
    $sql  = "SELECT texte, version, reference FROM versets WHERE livre = ? AND chapitre = ? AND verset = ?$cond LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, 'sii', $livre, $chapitre, $verset_debut);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

// Cache DB : chercher par reference
function cache_chercher_par_reference(mysqli $conn, string $reference): ?array {
    if (empty($reference)) return null;
    $sql  = "SELECT texte, version, reference FROM versets WHERE reference = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, 's', $reference);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

// Cache DB : sauvegarder
function cache_sauvegarder(mysqli $conn, string $livre, int $chapitre, int $verset_num,
                            string $texte, string $reference): void {
    if (!$livre || !$chapitre || !$verset_num || !$texte) return;
    $sql = "INSERT INTO versets (reference, livre, chapitre, verset, texte, version, api_source, cached_at)
            VALUES (?, ?, ?, ?, ?, 'LSG', 'scripture.api.bible', NOW())
            ON DUPLICATE KEY UPDATE
              texte = VALUES(texte), reference = VALUES(reference),
              api_source = VALUES(api_source), cached_at = NOW()";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return;
    mysqli_stmt_bind_param($stmt, 'ssiis', $reference, $livre, $chapitre, $verset_num, $texte);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Appel API scripture.api.bible
function api_get_verset(string $livre, int $chapitre, int $verset_debut, int $verset_fin): ?array {
    $osis = bible_livre_to_osis($livre);
    if (!$osis) return null;
    $bible_id = bible_get_lsg_id();
    if (!$bible_id) return null;
    $passage_id = "$osis.$chapitre.$verset_debut";
    if ($verset_fin > $verset_debut) {
        $passage_id .= "-$osis.$chapitre.$verset_fin";
    }
    $params = http_build_query([
        'content-type'            => 'text',
        'include-notes'           => 'false',
        'include-titles'          => 'false',
        'include-chapter-numbers' => 'false',
        'include-verse-numbers'   => 'false',
        'include-verse-spans'     => 'false',
    ]);
    $endpoint = "/bibles/$bible_id/passages/" . rawurlencode($passage_id) . "?$params";
    [$code, $data, $err] = bible_api_call($endpoint);
    if ($code !== 200 || empty($data['data']['content'])) return null;
    $texte = strip_tags($data['data']['content']);
    $texte = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texte = trim(preg_replace('/\s+/', ' ', $texte));
    $ref_locale = $verset_fin > $verset_debut
        ? "$livre $chapitre:$verset_debut-$verset_fin"
        : "$livre $chapitre:$verset_debut";
    return ['texte' => $texte, 'reference' => $ref_locale, 'version' => 'LSG'];
}

/**
 * Recupere un verset depuis query.getbible.net — Louis Segond 1910 (francais, SANS cle API).
 * Priorite 2 : toujours disponible, texte LSG francais, supporte les plages.
 */
function getbible_ls1910_get_verset(string $livre, int $chapitre, int $verset_debut, int $verset_fin): ?array {
    $english = livre_to_english($livre);
    if (empty($english)) return null;
    $ref_en = $english . ' ' . $chapitre . ':' . $verset_debut;
    if ($verset_fin > $verset_debut) {
        $ref_en .= '-' . $verset_fin;
    }
    $url = 'https://query.getbible.net/v2/ls1910/' . rawurlencode($ref_en);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'User-Agent: Mozilla/5.0'],
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || empty($body)) return null;
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) return null;
    // La reponse est un objet { "ls1910_43_3": { ... "verses": [...] } }
    $bloc = reset($data);
    if (empty($bloc['verses'])) return null;
    $book_name = $bloc['book_name'] ?? $livre;
    $textes = [];
    foreach ($bloc['verses'] as $v) {
        $t = trim($v['text'] ?? '');
        if ($t !== '') $textes[] = $t;
    }
    $texte = trim(implode(' ', $textes));
    if (empty($texte)) return null;
    $ref_locale = $verset_fin > $verset_debut
        ? "$book_name $chapitre:$verset_debut-$verset_fin"
        : "$book_name $chapitre:$verset_debut";
    return ['texte' => $texte, 'reference' => $ref_locale, 'version' => 'LSG',
            'verses' => $bloc['verses'], 'book_name' => $book_name];
}

/**
 * Recupere un verset depuis bible-api.com (WEB anglais, SANS cle API).
 * Dernier recours quand aucune source francaise n est disponible.
 */
function bible_api_com_get_verset(string $livre, int $chapitre, int $verset_debut, int $verset_fin): ?array {
    $english = livre_to_english($livre);
    if (empty($english)) return null;
    $ref_en = str_replace(' ', '+', $english) . "+$chapitre:$verset_debut";
    if ($verset_fin > $verset_debut) {
        $ref_en .= "-$verset_fin";
    }
    $url = "https://bible-api.com/$ref_en";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || empty($body)) return null;
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($data['text'])) return null;
    $texte = trim(preg_replace('/\s+/', ' ', $data['text']));
    $ref_locale = $verset_fin > $verset_debut
        ? "$livre $chapitre:$verset_debut-$verset_fin"
        : "$livre $chapitre:$verset_debut";
    return ['texte' => $texte, 'reference' => $ref_locale, 'version' => 'WEB'];
}

// ── Logique principale ────────────────────────────────────────
try {
    $conn = get_db_connection();
    $trouve = null;

    // Pour une plage (16-17), on saute le cache par coordonnées (qui retournerait seulement le v.16)
    // et on cherche directement par la référence canonique complète.
    if (!empty($livre) && $chapitre > 0 && $verset_debut > 0 && $verset_fin === $verset_debut) {
        $trouve = cache_chercher($conn, $livre, $chapitre, $verset_debut);
    }
    if (!$trouve && !empty($ref_canonique)) {
        $trouve = cache_chercher_par_reference($conn, $ref_canonique);
    }
    if (!$trouve && !empty($reference) && $reference !== $ref_canonique) {
        $trouve = cache_chercher_par_reference($conn, $reference);
        if (!$trouve) {
            $ref_alt = str_replace(',', ':', $reference);
            if ($ref_alt !== $reference) {
                $trouve = cache_chercher_par_reference($conn, $ref_alt);
            }
        }
    }

    if ($trouve) {
        echo json_encode([
            'success'   => true,
            'reference' => $trouve['reference'],
            'texte'     => $trouve['texte'],
            'version'   => $trouve['version'] ?? 'LSG',
            'source'    => 'cache',
        ], JSON_UNESCAPED_UNICODE);
        mysqli_close($conn); exit;
    }

    // Résoudre l_ref/c_ref/v_ref/vf_ref une seule fois pour toutes les sources
    $l_ref = $livre; $c_ref = $chapitre; $v_ref = $verset_debut; $vf_ref = $verset_fin;
    if (empty($livre) && !empty($reference)) {
        if (preg_match('/^(.+?)\s+(\d+)[:\s,](\d+)(?:-(\d+))?$/u', $reference, $m)) {
            $l_ref  = normaliser_livre(trim($m[1]));
            $c_ref  = (int) $m[2];
            $v_ref  = (int) $m[3];
            $vf_ref = isset($m[4]) ? (int) $m[4] : $v_ref;
        }
    }

    // ── Priorite 2 : query.getbible.net — Louis Segond 1910 (francais, sans cle) ──
    $resultat_getbible = null;
    if (!empty($l_ref) && $c_ref > 0 && $v_ref > 0) {
        $resultat_getbible = getbible_ls1910_get_verset($l_ref, $c_ref, $v_ref, $vf_ref);
    }
    if ($resultat_getbible) {
        // Mettre en cache chaque verset individuel pour les futures requetes
        if (!empty($resultat_getbible['verses'])) {
            foreach ($resultat_getbible['verses'] as $rv) {
                $tv   = trim($rv['text'] ?? '');
                $vnum = (int) ($rv['verse'] ?? 0);
                if ($tv !== '' && $vnum > 0) {
                    $ref_v = $resultat_getbible['book_name'] . ' ' . $c_ref . ':' . $vnum;
                    cache_sauvegarder($conn, $l_ref, $c_ref, $vnum, $tv, $ref_v);
                }
            }
        }
        echo json_encode([
            'success'   => true,
            'reference' => $resultat_getbible['reference'],
            'texte'     => $resultat_getbible['texte'],
            'version'   => 'LSG',
            'source'    => 'getbible.net',
        ], JSON_UNESCAPED_UNICODE);
        mysqli_close($conn); exit;
    }

    // ── Priorite 3 : scripture.api.bible (LSG francais, cle optionnelle) ────
    $resultat_api = null;
    if (!empty($l_ref) && $c_ref > 0 && $v_ref > 0) {
        $resultat_api = api_get_verset($l_ref, $c_ref, $v_ref, $vf_ref);
    }
    if ($resultat_api) {
        if ($l_ref && $c_ref > 0 && $v_ref > 0) {
            cache_sauvegarder($conn, $l_ref, $c_ref, $v_ref,
                              $resultat_api['texte'], $resultat_api['reference']);
        }
        echo json_encode([
            'success'   => true,
            'reference' => $resultat_api['reference'],
            'texte'     => $resultat_api['texte'],
            'version'   => 'LSG',
            'source'    => 'scripture.api.bible',
        ], JSON_UNESCAPED_UNICODE);
        mysqli_close($conn); exit;
    }

    // ── Priorite 4 : bible-api.com (WEB anglais, sans cle, dernier recours) ──
    $resultat_web = bible_api_com_get_verset($l_ref, $c_ref, $v_ref, $vf_ref);

    if ($resultat_web) {
        // Ne pas sauvegarder en cache : texte anglais ne doit pas ecraser LSG
        echo json_encode([
            'success'   => true,
            'reference' => $resultat_web['reference'],
            'texte'     => $resultat_web['texte'],
            'version'   => 'WEB',
            'source'    => 'bible-api.com',
        ], JSON_UNESCAPED_UNICODE);
        mysqli_close($conn); exit;
    }

    echo json_encode([
        'success'   => false,
        'reference' => $ref_canonique ?: $reference,
        'message'   => 'Verset non trouve. Veuillez consulter votre Bible.',
        'source'    => 'none',
    ], JSON_UNESCAPED_UNICODE);

    mysqli_close($conn);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Erreur serveur',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}