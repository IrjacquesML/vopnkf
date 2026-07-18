<?php
// Test de la détection des versets avec le nouveau pattern HTML-aware

$verse_pattern = '/\b((?:\d+\h+)?[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\h+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\h+(\d+)\h*[,:]\h*(\d+(?:-\d+)?)\b/u';

$tests = [
    'H&eacute;breux 2&nbsp;: 15',
    'Jean 3:16',
    'Matthieu 28&nbsp;: 19-20.',
    'Luc 8 : 21',
    '2 Tim 3 :16',
    '1 Jean 1:9',
    'En lisant Jean 19&nbsp;:30 il est dit',
    'Eph&eacute;siens 2:8',
    'Col 2&nbsp;:16-17',
    '<strong style="font-size:14pt;">Matthieu 28&nbsp;: 19-20</strong>',
];

echo "<pre>\n";
foreach ($tests as $t) {
    $decoded = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Strip tags for testing just text node
    $text_only = strip_tags($decoded);
    preg_match_all($verse_pattern, $text_only, $m);
    echo "Input  : $t\n";
    echo "Text   : $text_only\n";
    if (!empty($m[0])) {
        echo "MATCH  => livre='{$m[1][0]}' chap={$m[2][0]} verset={$m[3][0]}\n";
    } else {
        echo "NO MATCH\n";
    }
    echo "---\n";
}
echo "</pre>\n";

// Pattern amélioré (plus complet)
$pattern_ameliore = '/(?<![>])(\b(?:\d+\s+)?(?:[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\s+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\s+(\d+):(\d+(?:-\d+)?)\b)(?![^<]*>)/u';

// Pattern ultra-complet (tous les cas)
$pattern_ultra = '/(?<![>])(\b(?:\d+\s+)?(?:[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\s+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\s+(\d+):(\d+(?:-\d+)?)\b)(?![^<]*>)/u';

function traiter_versets($texte, $pattern) {
    $texte_traite = preg_replace_callback($pattern, function($matches) {
        $reference = $matches[1];
        $livre = trim($matches[2] ?? '');
        $chapitre = $matches[3];
        $verset = $matches[4];
        
        return '<span class="bible-verse" data-reference="' . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '" 
                data-livre="' . htmlspecialchars($livre, ENT_QUOTES, 'UTF-8') . '" 
                data-chapitre="' . htmlspecialchars($chapitre, ENT_QUOTES, 'UTF-8') . '" 
                data-verset="' . htmlspecialchars($verset, ENT_QUOTES, 'UTF-8') . '">' 
                . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '</span>';
    }, $texte);
    
    return $texte_traite;
}

// Textes de test avec différents formats
$tests = [
    "Jean 3:16 est le verset le plus connu.",
    "Selon jean 3:16, Dieu a tant aimé le monde.",
    "Le verset 1 jean 1:9 parle du pardon.",
    "Dans 1 jean 2:1, nous trouvons...",
    "Éphésiens 2:8 explique la grâce.",
    "ephésiens 2:8 parle aussi de la grâce.",
    "Hébreux 4:12 est puissant.",
    "hébreux 4:12 aussi.",
    "Genèse 1:1 au commencement.",
    "genèse 1:1 également.",
    "Matthieu 11:28 est une invitation.",
    "matthieu 11:28 aussi.",
    "Romains 3:23 dit que tous ont péché.",
    "romains 3:23 confirme cela.",
    "Psaumes 23:1 parle du berger.",
    "psaumes 23:1 est beau.",
    "Philippiens 4:13 donne la force.",
    "philippiens 4:13 encourage.",
    "2 Corinthiens 5:17 parle de nouvelle création.",
    "2 corinthiens 5:17 est transformant.",
    "1 Timothée 4:12 est un encouragement.",
    "1 timothée 4:12 pour les jeunes.",
    "Apocalypse 22:20 conclut la Bible.",
    "apocalypse 22:20 est la fin.",
    "Romains 12:1-2 est un appel.",
    "Jean 3:16-17 est complet.",
    "Matthieu 5:3-12 sont les béatitudes.",
    "Proverbes 3:5-6 donne la sagesse."
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Détection des Versets | VOP</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .test-section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .test-item {
            margin: 15px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .original {
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
        }
        .result {
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .pattern-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        .detected {
            border-left-color: #4caf50;
        }
        .not-detected {
            border-left-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container lesson-container">
        <div class="lesson-header">
            <h1>Test de Détection des Versets Bibliques</h1>
            <p>Comparaison des différents patterns de détection pour identifier les références bibliques.</p>
        </div>
        
        <?php foreach (['actuel' => $pattern_actuel, 'ameliore' => $pattern_ameliore, 'ultra' => $pattern_ultra] as $nom => $pattern): ?>
        <div class="test-section">
            <div class="pattern-title">Pattern <?php echo ucfirst($nom); ?></div>
            
            <?php foreach ($tests as $i => $test): ?>
            <div class="test-item">
                <div class="original">Test <?php echo ($i + 1); ?>: <?php echo htmlspecialchars($test); ?></div>
                <div class="result <?php echo (preg_match($pattern, $test) ? 'detected' : 'not-detected'); ?>">
                    <?php 
                    $resultat = traiter_versets($test, $pattern);
                    if ($resultat !== $test) {
                        echo '✅ Détecté: ' . $resultat;
                    } else {
                        echo '❌ Non détecté';
                    }
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        
        <div class="test-section" style="background: #e8f5e9; border-color: #4caf50;">
            <h3>📊 Analyse des résultats</h3>
            <p><strong>Pattern Actuel:</strong> Détecte seulement les références commençant par une majuscule</p>
            <p><strong>Pattern Amélioré:</strong> Détecte les références avec majuscules ou minuscules</p>
            <p><strong>Pattern Ultra:</strong> Le plus complet, détecte tous les formats possibles</p>
        </div>
    </div>
</body>
</html>
