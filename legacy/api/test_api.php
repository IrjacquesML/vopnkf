<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API - VOP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        h2 { color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { color: #2196F3; }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover { background: #45a049; }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #4CAF50;
        }
        .result {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>🧪 Test des API - VOP</h1>
    
    <!-- Test 1: API get_verset.php -->
    <div class="test-section">
        <h2>1. Test API get_verset.php</h2>
        <p>Cette API récupère les versets bibliques depuis la base de données.</p>
        <button onclick="testGetVerset()">🔍 Tester get_verset.php</button>
        <div id="result-verset" class="result" style="display:none;"></div>
    </div>
    
    <!-- Test 2: API traduire.php -->
    <div class="test-section">
        <h2>2. Test API traduire.php</h2>
        <p>Cette API traduit du texte dans différentes langues.</p>
        <button onclick="testTraduction()">🌍 Tester traduire.php</button>
        <div id="result-traduction" class="result" style="display:none;"></div>
    </div>
    
    <!-- Test 3: Vérification de la base de données -->
    <div class="test-section">
        <h2>3. Vérification de la Base de Données</h2>
        <?php
        require_once '../includes/config.php';
        
        try {
            $conn = get_db_connection();
            echo "<p class='success'>✓ Connexion à la base de données réussie</p>";
            
            // Vérifier la table versets
            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM versets");
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "<p class='info'>📖 Nombre de versets dans la base: <strong>{$row['total']}</strong></p>";
                
                if ($row['total'] > 0) {
                    // Afficher quelques exemples
                    $versets = mysqli_query($conn, "SELECT reference, texte FROM versets LIMIT 3");
                    echo "<p><strong>Exemples de versets:</strong></p><ul>";
                    while ($v = mysqli_fetch_assoc($versets)) {
                        echo "<li><strong>{$v['reference']}:</strong> " . substr($v['texte'], 0, 100) . "...</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='error'>⚠ Aucun verset dans la base de données. Exécutez le fichier database.sql</p>";
                }
            }
            
            // Vérifier la table traductions
            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM traductions");
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "<p class='info'>🌍 Nombre de traductions en cache: <strong>{$row['total']}</strong></p>";
            }
            
            mysqli_close($conn);
        } catch (Exception $e) {
            echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <!-- Test 4: Configuration PHP -->
    <div class="test-section">
        <h2>4. Configuration PHP</h2>
        <?php
        echo "<p><strong>allow_url_fopen:</strong> " . (ini_get('allow_url_fopen') ? '<span class="success">✓ Activé</span>' : '<span class="error">✗ Désactivé (requis pour la traduction)</span>') . "</p>";
        echo "<p><strong>Version PHP:</strong> " . phpversion() . "</p>";
        echo "<p><strong>Extensions chargées:</strong></p><ul>";
        $extensions = ['mysqli', 'json', 'openssl'];
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            echo "<li><strong>$ext:</strong> " . ($loaded ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . "</li>";
        }
        echo "</ul>";
        ?>
    </div>
    
    <script>
        // Test 1: get_verset.php
        async function testGetVerset() {
            const resultDiv = document.getElementById('result-verset');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<p>⏳ Test en cours...</p>';
            
            try {
                const response = await fetch('get_verset.php?reference=Jean 3:16&livre=Jean&chapitre=3&verset=16');
                const data = await response.json();
                
                let html = '<h3>Résultat:</h3>';
                html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                
                if (data.success) {
                    html += '<p class="success">✓ API fonctionne correctement!</p>';
                    html += '<p><strong>Texte:</strong> ' + data.texte + '</p>';
                } else {
                    html += '<p class="error">⚠ ' + data.message + '</p>';
                }
                
                resultDiv.innerHTML = html;
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Erreur: ' + error.message + '</p>';
            }
        }
        
        // Test 2: traduire.php
        async function testTraduction() {
            const resultDiv = document.getElementById('result-traduction');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<p>⏳ Test en cours...</p>';
            
            try {
                const response = await fetch('traduire.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        texte: 'Bonjour, bienvenue sur VOP',
                        langue: 'en'
                    })
                });
                
                const data = await response.json();
                
                let html = '<h3>Résultat:</h3>';
                html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                
                if (data.success) {
                    html += '<p class="success">✓ API de traduction fonctionne!</p>';
                    html += '<p><strong>Texte original:</strong> Bonjour, bienvenue sur VOP</p>';
                    html += '<p><strong>Traduction (EN):</strong> ' + data.traduction + '</p>';
                } else {
                    html += '<p class="error">⚠ Erreur: ' + (data.error || data.message) + '</p>';
                }
                
                resultDiv.innerHTML = html;
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Erreur: ' + error.message + '</p>';
            }
        }
    </script>
</body>
</html>
