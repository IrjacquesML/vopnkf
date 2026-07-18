<?php
// Test simple pour vérifier la détection des versets

function traiter_versets($texte) {
    // Pattern simplifié mais complet pour détecter les références bibliques
    // Supporte: Jean3:16, jean 3:16, 1 Jean 1:9, Matthieu 11:28, etc.
    // Groupes: 1=référence complète, 2=livre, 3=chapitre, 4=verset
    $pattern = '/\b(((?:\d+\s+)?[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\s+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\s+(\d+):(\d+(?:-\d+)?))\b/u';
    
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

// Textes de test
$tests = [
    "Jean 3:16 est le verset le plus connu.",
    "jean 3:16 fonctionne maintenant en minuscules.",
    "1 Jean 1:9 parle du pardon.",
    "1 jean 1:9 aussi en minuscules.",
    "Matthieu 11:28 est une invitation.",
    "matthieu 11:28 fonctionne aussi."
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Simple - Détection des Versets</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .test-item {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
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
            border-left: 4px solid #4caf50;
        }
        .bible-verse {
            color: var(--vert-foret);
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
            background: rgba(46, 125, 50, 0.1);
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container lesson-container">
        <div class="lesson-header">
            <h1>Test Simple - Détection des Versets</h1>
            <p>Vérification que l'erreur "Undefined array key" est résolue.</p>
        </div>
        
        <div class="lesson-content">
            <div class="content-text">
                <?php foreach ($tests as $i => $test): ?>
                <div class="test-item">
                    <div class="original">Test <?php echo ($i + 1); ?>: <?php echo htmlspecialchars($test); ?></div>
                    <div class="result">
                        <?php 
                        try {
                            $resultat = traiter_versets($test);
                            echo '✅ Succès: ' . $resultat;
                        } catch (Exception $e) {
                            echo '❌ Erreur: ' . $e->getMessage();
                        } catch (Error $e) {
                            echo '❌ Erreur: ' . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
