<?php
/**
 * Système de traduction multilingue
 * Utilise l'API Google Translate gratuite via HTTP
 */

// Langues supportées
$langues_supportees = [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español',
    'pt' => 'Português',
    'sw' => 'Kiswahili',
    'ln' => 'Lingala',
    'kg' => 'Kikongo',
    'ar' => 'العربية',
    'zh' => '中文',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'ru' => 'Русский'
];

/**
 * Traduire un texte
 * @param string $texte Texte à traduire
 * @param string $langue_cible Code de la langue cible (ex: 'en', 'es')
 * @param string $langue_source Code de la langue source (par défaut 'fr')
 * @return string Texte traduit ou texte original en cas d'erreur
 */
function traduire_texte($texte, $langue_cible, $langue_source = 'fr') {
    // Si la langue cible est la même que la source, retourner le texte original
    if ($langue_cible === $langue_source) {
        return $texte;
    }
    
    // Vérifier si la traduction existe déjà en cache
    $conn = get_db_connection();
    $texte_hash = md5($texte);
    
    $query = "SELECT texte_traduit FROM traductions 
              WHERE MD5(texte_original) = ? 
              AND langue = ? 
              LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $texte_hash, $langue_cible);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_close($conn);
        return $row['texte_traduit'];
    }
    
    // Utiliser l'API Google Translate gratuite via file_get_contents
    try {
        $texte_encode = urlencode($texte);
        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl={$langue_source}&tl={$langue_cible}&dt=t&q={$texte_encode}";
        
        // Configuration du contexte pour file_get_contents
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0\r\n",
                'timeout' => 10
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $result_array = json_decode($response, true);
            
            if (isset($result_array[0]) && is_array($result_array[0])) {
                $texte_traduit = '';
                foreach ($result_array[0] as $translation) {
                    if (isset($translation[0])) {
                        $texte_traduit .= $translation[0];
                    }
                }
                
                // Sauvegarder la traduction en cache
                if (!empty($texte_traduit)) {
                    $query = "INSERT INTO traductions (type_contenu, texte_original, langue, texte_traduit) 
                              VALUES ('interface', ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sss", $texte, $langue_cible, $texte_traduit);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                mysqli_close($conn);
                return $texte_traduit;
            }
        }
    } catch (Exception $e) {
        error_log("Erreur de traduction: " . $e->getMessage());
    }
    
    mysqli_close($conn);
    return $texte; // Retourner le texte original en cas d'erreur
}

/**
 * Traduire le contenu d'une leçon
 * @param int $lecon_id ID de la leçon
 * @param string $langue_cible Code de la langue cible
 * @return array Contenu traduit (titre, contenu)
 */
function traduire_lecon($lecon_id, $langue_cible) {
    $conn = get_db_connection();
    
    // Récupérer la leçon originale
    $query = "SELECT titre, contenu FROM lecons WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $lecon_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $lecon = mysqli_fetch_assoc($result);
    
    if (!$lecon) {
        mysqli_close($conn);
        return null;
    }
    
    // Vérifier si la traduction existe déjà
    $query = "SELECT texte_traduit FROM traductions 
              WHERE type_contenu = 'lecon' 
              AND contenu_id = ? 
              AND langue = ? 
              AND cle_texte = 'titre'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $lecon_id, $langue_cible);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $titre_traduit_row = mysqli_fetch_assoc($result);
    
    $query = "SELECT texte_traduit FROM traductions 
              WHERE type_contenu = 'lecon' 
              AND contenu_id = ? 
              AND langue = ? 
              AND cle_texte = 'contenu'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $lecon_id, $langue_cible);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $contenu_traduit_row = mysqli_fetch_assoc($result);
    
    // Si les traductions existent, les retourner
    if ($titre_traduit_row && $contenu_traduit_row) {
        mysqli_close($conn);
        return [
            'titre' => $titre_traduit_row['texte_traduit'],
            'contenu' => $contenu_traduit_row['texte_traduit']
        ];
    }
    
    // Sinon, traduire et sauvegarder
    $titre_traduit = traduire_texte($lecon['titre'], $langue_cible);
    $contenu_traduit = traduire_texte($lecon['contenu'], $langue_cible);
    
    // Sauvegarder les traductions
    $query = "INSERT INTO traductions (type_contenu, contenu_id, cle_texte, texte_original, langue, texte_traduit) 
              VALUES ('lecon', ?, 'titre', ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isss", $lecon_id, $lecon['titre'], $langue_cible, $titre_traduit);
    mysqli_stmt_execute($stmt);
    
    $query = "INSERT INTO traductions (type_contenu, contenu_id, cle_texte, texte_original, langue, texte_traduit) 
              VALUES ('lecon', ?, 'contenu', ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isss", $lecon_id, $lecon['contenu'], $langue_cible, $contenu_traduit);
    mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    
    return [
        'titre' => $titre_traduit,
        'contenu' => $contenu_traduit
    ];
}

/**
 * Obtenir la langue préférée de l'utilisateur
 * @param int $utilisateur_id ID de l'utilisateur
 * @return string Code de la langue (par défaut 'fr')
 */
function get_langue_utilisateur($utilisateur_id) {
    if (isset($_SESSION['langue_preferee'])) {
        return $_SESSION['langue_preferee'];
    }
    
    $conn = get_db_connection();
    $query = "SELECT langue_preferee FROM utilisateurs WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $utilisateur_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    
    $langue = $user['langue_preferee'] ?? 'fr';
    $_SESSION['langue_preferee'] = $langue;
    
    return $langue;
}

/**
 * Définir la langue préférée de l'utilisateur
 * @param int $utilisateur_id ID de l'utilisateur
 * @param string $langue Code de la langue
 * @return bool Succès de l'opération
 */
function set_langue_utilisateur($utilisateur_id, $langue) {
    $conn = get_db_connection();
    $query = "UPDATE utilisateurs SET langue_preferee = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $langue, $utilisateur_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_close($conn);
    
    if ($success) {
        $_SESSION['langue_preferee'] = $langue;
    }
    
    return $success;
}

/**
 * Fonction raccourcie pour traduire (alias)
 * @param string $texte Texte à traduire
 * @param string $langue_cible Langue cible (optionnel, utilise la langue de l'utilisateur)
 * @return string Texte traduit
 */
function t($texte, $langue_cible = null) {
    if ($langue_cible === null && isset($_SESSION['langue_preferee'])) {
        $langue_cible = $_SESSION['langue_preferee'];
    }
    
    if ($langue_cible === null || $langue_cible === 'fr') {
        return $texte;
    }
    
    return traduire_texte($texte, $langue_cible);
}
?>
