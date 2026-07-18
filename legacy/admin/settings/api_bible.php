<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$admin = [
    'nom'    => $_SESSION['admin_nom'],
    'prenom' => $_SESSION['admin_prenom'],
];

$message = '';
$type_message = '';

// ── Test de connexion à l'API ────────────────────────────────
$action = $_GET['action'] ?? '';

if ($action === 'test_api') {
    [$code, $data, $err] = bible_api_call('/bibles?language=fra&includeFullDetails=false');
    if ($code === 200 && !empty($data['data'])) {
        $nb = count($data['data']);
        $message = "Connexion réussie ! $nb Bible(s) française(s) disponible(s) sur scripture.api.bible.";
        $type_message = 'success';
    } elseif ($code === 401) {
        $message = "Clé API invalide ou non configurée (HTTP 401). Vérifiez la valeur de BIBLE_API_KEY dans includes/config.php.";
        $type_message = 'error';
    } elseif ($code === 0) {
        $message = "Impossible de contacter l'API. Erreur : $err";
        $type_message = 'error';
    } else {
        $message = "Réponse inattendue (HTTP $code). " . ($err ?: substr(json_encode($data), 0, 200));
        $type_message = 'warning';
    }
}

if ($action === 'list_bibles') {
    [$code, $data, $err] = bible_api_call('/bibles?language=fra&includeFullDetails=false');
    $bibles_fr = ($code === 200 && !empty($data['data'])) ? $data['data'] : [];
}

if ($action === 'test_verse_web') {
    // Test bible-api.com avec Jean 3:16 (pas de clé nécessaire)
    $ch = curl_init('https://bible-api.com/john+3:16');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,
        CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>['Accept: application/json']]);
    $body = curl_exec($ch); $code = (int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    $data = json_decode($body, true);
    if ($code === 200 && !empty($data['text'])) {
        $texte = trim(preg_replace('/\s+/', ' ', $data['text']));
        $message = "bible-api.com OK — Jean 3:16 (WEB) : <em>" . h($texte) . "</em><br><small>Traduction : " . h($data['translation_name'] ?? 'WEB') . " — Note : texte en anglais, pas de Louis Segond disponible sur cette API.</small>";
        $type_message = 'success';
    } else {
        $message = "bible-api.com inaccessible (HTTP $code).";
        $type_message = 'error';
    }
}

if ($action === 'test_verse') {
    if ($bible_id) {
        // Test avec Jean 3:16
        $passage_id = 'JHN.3.16';
        $params = http_build_query([
            'content-type'            => 'text',
            'include-notes'           => 'false',
            'include-titles'          => 'false',
            'include-chapter-numbers' => 'false',
            'include-verse-numbers'   => 'false',
        ]);
        [$code, $data, $err] = bible_api_call("/bibles/$bible_id/passages/" . rawurlencode($passage_id) . "?$params");
        if ($code === 200 && !empty($data['data']['content'])) {
            $texte = strip_tags($data['data']['content']);
            $texte = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $texte = trim(preg_replace('/\s+/', ' ', $texte));
            $message = "Test Jean 3:16 — Bible ID : <code>" . h($bible_id) . "</code><br><em>" . h($texte) . "</em>";
            $type_message = 'success';
        } else {
            $message = "Bible ID : <code>" . h($bible_id) . "</code> — Réponse (HTTP $code) : " . h(substr(json_encode($data), 0, 300));
            $type_message = $code === 200 ? 'warning' : 'error';
        }
    } else {
        $message = "ID Bible LSG introuvable. Vérifiez votre clé API et que la Bible Louis Segond est disponible dans votre compte.";
        $type_message = 'error';
    }
}

// Statistiques cache
$conn = get_db_connection();
$cache_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT
      COUNT(*) as total,
      SUM(api_source = 'scripture.api.bible') as depuis_api,
      SUM(api_source = 'local') as locaux,
      MAX(cached_at) as dernier_cache
    FROM versets
"));
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres API Bible - VOP Admin</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .settings-card { background:#fff; border-radius:8px; padding:24px; margin-bottom:24px; box-shadow:0 1px 4px rgba(0,0,0,.1); }
        .settings-card h2 { margin-top:0; color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:8px; }
        .step { background:#f8f9fa; border-left:4px solid #3498db; padding:12px 16px; margin:12px 0; border-radius:0 4px 4px 0; }
        .step strong { color:#2980b9; }
        code { background:#eee; padding:2px 6px; border-radius:3px; font-family:monospace; font-size:.9em; }
        pre { background:#1e2229; color:#a8d8a8; padding:16px; border-radius:6px; overflow:auto; font-size:.85em; }
        .badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:.8em; font-weight:600; }
        .badge-ok { background:#d4edda; color:#155724; }
        .badge-warn { background:#fff3cd; color:#856404; }
        .badge-err { background:#f8d7da; color:#721c24; }
        .stat-row { display:flex; gap:16px; flex-wrap:wrap; margin:12px 0; }
        .stat-box { flex:1; min-width:120px; background:#f0f4f8; border-radius:8px; padding:12px; text-align:center; }
        .stat-box .num { font-size:1.8em; font-weight:700; color:#2c3e50; }
        .stat-box .lbl { font-size:.8em; color:#6c757d; }
        .btn-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
        .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:12px 16px; border-radius:6px; margin:12px 0; }
        .alert-error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:12px 16px; border-radius:6px; margin:12px 0; }
        .alert-warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; padding:12px 16px; border-radius:6px; margin:12px 0; }
        table.bible-list { width:100%; border-collapse:collapse; font-size:.9em; }
        table.bible-list th { background:#2c3e50; color:#fff; padding:8px 12px; }
        table.bible-list td { padding:8px 12px; border-bottom:1px solid #eee; }
        table.bible-list tr:nth-child(even) td { background:#f8f9fa; }
    </style>
</head>
<body>
<nav class="navbar admin-navbar">
    <div class="nav-container">
        <div class="nav-logo"><h2>🔐 VOP Admin</h2></div>
        <div class="nav-menu">
            <a href="../dashboard.php">Tableau de bord</a>
            <a href="../users/liste.php">Utilisateurs</a>
            <a href="../lessons/liste.php">Leçons</a>
            <a href="../prayers/liste.php">Prières</a>
            <a href="../reports/statistiques.php">Rapports</a>
            <a href="api_bible.php" class="active">API Bible</a>
        </div>
        <div class="nav-user">
            <span>👤 <?php echo h($admin['prenom'] . ' ' . $admin['nom']); ?></span>
            <a href="../auth/logout.php" class="btn btn-small">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container admin-container">
    <div class="admin-header">
        <h1>📖 Paramètres — API Bible</h1>
        <p>Configuration des sources de versets : cache local, scripture.api.bible (LSG français) et bible-api.com (WEB anglais)</p>
    </div>

    <?php if ($message): ?>
    <div class="alert-<?php echo h($type_message); ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- ── Statut des sources ────────────────────────────── -->
    <div class="settings-card">
        <h2>📊 Statut des sources</h2>
        <?php $api_key_ok = !empty(BIBLE_API_KEY); ?>

        <table style="width:100%;border-collapse:collapse;font-size:.95em;">
          <tr style="background:#f0f4f8;">
            <td style="padding:10px 14px;font-weight:600;">1. Cache local (LSG)</td>
            <td style="padding:10px 14px;"><span class="badge badge-ok">Actif ✓</span></td>
            <td style="padding:10px 14px;color:#555;"><?php echo (int)$cache_stats['total']; ?> versets
              (<?php echo (int)$cache_stats['locaux']; ?> seed + <?php echo (int)$cache_stats['depuis_api']; ?> depuis API)</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;font-weight:600;">2. scripture.api.bible <small>(LSG français)</small></td>
            <td style="padding:10px 14px;">
              <span class="badge <?php echo $api_key_ok ? 'badge-ok' : 'badge-warn'; ?>">
                <?php echo $api_key_ok ? 'Clé configurée ✓' : 'Clé manquante'; ?>
              </span>
            </td>
            <td style="padding:10px 14px;color:#555;">
              <?php echo $api_key_ok
                ? 'Louis Segond 1910 — mise en cache automatique'
                : 'Configurez BIBLE_API_KEY (inscription gratuite sur scripture.api.bible)'; ?>
            </td>
          </tr>
          <tr style="background:#f0f4f8;">
            <td style="padding:10px 14px;font-weight:600;">3. bible-api.com <small>(WEB anglais)</small></td>
            <td style="padding:10px 14px;"><span class="badge badge-ok">Actif ✓ (aucune clé)</span></td>
            <td style="padding:10px 14px;color:#555;">Fallback automatique — World English Bible — texte anglais, non mis en cache</td>
          </tr>
        </table>

        <div class="stat-row" style="margin-top:16px;">
            <div class="stat-box">
                <div class="num"><?php echo (int)$cache_stats['total']; ?></div>
                <div class="lbl">Versets en cache</div>
            </div>
            <div class="stat-box">
                <div class="num"><?php echo (int)$cache_stats['depuis_api']; ?></div>
                <div class="lbl">Depuis scripture</div>
            </div>
            <div class="stat-box">
                <div class="num"><?php echo (int)$cache_stats['locaux']; ?></div>
                <div class="lbl">Locaux (seed)</div>
            </div>
            <div class="stat-box">
                <div class="num"><?php echo $cache_stats['dernier_cache'] ? date('d/m/y', strtotime($cache_stats['dernier_cache'])) : '—'; ?></div>
                <div class="lbl">Dernier cache</div>
            </div>
        </div>
        <div class="btn-row">
            <a href="?action=test_verse_web" class="btn btn-success">🌐 Tester Jean 3:16 (bible-api.com)</a>
            <a href="?action=test_api"       class="btn btn-primary">🔌 Tester scripture.api.bible</a>
            <a href="?action=test_verse"     class="btn">📖 Tester Jean 3:16 (LSG)</a>
            <a href="?action=list_bibles"    class="btn">📋 Lister les Bibles LSG disponibles</a>
        </div>
    </div>

    <!-- ── Guide de configuration ───────────────────────── -->
    <div class="settings-card">
        <h2>⚙️ Comment activer les versets en français (LSG)</h2>
        <p>Sans configuration, les versets non cachés s'affichent en <strong>anglais</strong> (bible-api.com, gratuit et automatique).
        Pour obtenir les versets en <strong>français Louis Segond</strong>, configurez scripture.api.bible :</p>

        <div class="step">
            <strong>Étape 1 — Créer un compte gratuit</strong><br>
            Rendez-vous sur <a href="https://scripture.api.bible/" target="_blank">https://scripture.api.bible/</a>
            et créez un compte gratuit (jusqu'à 5 000 requêtes/jour, aucune carte de crédit requise).
        </div>

        <div class="step">
            <strong>Étape 2 — Obtenir votre clé API</strong><br>
            Dans votre tableau de bord, créez une nouvelle application et copiez la clé API générée.
        </div>

        <div class="step">
            <strong>Étape 3 — Configurer la clé dans l'application</strong><br>
            Ouvrez le fichier <code>includes/config.php</code> et remplacez la valeur vide :<br><br>
            <pre>// Avant :
define('BIBLE_API_KEY', '');

// Après (exemple) :
define('BIBLE_API_KEY', 'votre-cle-api-ici-32-caracteres');</pre>
        </div>

        <div class="step">
            <strong>Étape 4 — Tester</strong><br>
            Revenez sur cette page et cliquez sur <strong>"Tester Jean 3:16"</strong>.
            Si le verset s'affiche, l'intégration est fonctionnelle !
        </div>

        <div class="step">
            <strong>Étape 5 (optionnel) — Fixer l'ID Bible LSG</strong><br>
            Cliquez sur "Lister les Bibles françaises disponibles" pour trouver l'ID exact de la
            Bible Louis Segond 1910, puis ajoutez-le dans <code>config.php</code> :<br><br>
            <pre>define('BIBLE_LSG_ID', 'id-trouve-ici');</pre>
            Cela évite un appel API supplémentaire à chaque session.
        </div>
    </div>

    <!-- ── Liste des Bibles françaises ──────────────────── -->
    <?php if ($action === 'list_bibles'): ?>
    <div class="settings-card">
        <h2>📋 Bibles françaises disponibles</h2>
        <?php if (empty($bibles_fr)): ?>
            <p>Aucune Bible française trouvée. Vérifiez votre clé API.</p>
        <?php else: ?>
            <table class="bible-list">
                <thead><tr><th>ID (à copier)</th><th>Abréviation</th><th>Nom</th><th>Nom local</th></tr></thead>
                <tbody>
                <?php foreach ($bibles_fr as $b): ?>
                    <tr>
                        <td><code><?php echo h($b['id']); ?></code></td>
                        <td><?php echo h($b['abbreviation'] ?? '—'); ?></td>
                        <td><?php echo h($b['name'] ?? '—'); ?></td>
                        <td><?php echo h($b['nameLocal'] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><small>Copiez l'ID de la Bible "Louis Segond" et placez-le dans <code>BIBLE_LSG_ID</code> dans config.php.</small></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Architecture et fonctionnement ───────────────── -->
    <div class="settings-card">
        <h2>🏗️ Architecture du système</h2>
        <p>Quand un utilisateur clique sur un verset :</p>
        <ol>
            <li><strong>Cache local</strong> — L'application cherche d'abord le verset dans la base de données (table <code>versets</code>).</li>
            <li><strong>API externe</strong> — Si absent, un appel est fait à <em>scripture.api.bible</em> avec la clé configurée.</li>
            <li><strong>Mise en cache</strong> — Le verset récupéré est automatiquement sauvegardé en base pour les prochaines consultations.</li>
        </ol>
        <p>La durée du cache est de <code><?php echo BIBLE_CACHE_DAYS; ?></code> jours (0 = infini). Modifiable via <code>BIBLE_CACHE_DAYS</code> dans <code>config.php</code>.</p>
        <p>Les <?php echo (int)$cache_stats['locaux']; ?> versets "locaux" existants servent de cache initial et seront utilisés directement sans appel API.</p>
    </div>
</div>
</body>
</html>
