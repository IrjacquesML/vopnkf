<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$conn = get_db_connection();

// ── Mode liste ou mode certificat individuel ──────────────────────────────────
$mode = 'liste';
$user = null;

if (isset($_GET['user_id'])) {
    $uid = intval($_GET['user_id']);
    $res = mysqli_query($conn, "
        SELECT u.*,
               COUNT(DISTINCT pl.lecon_id)   AS nb_lecons_terminees,
               ROUND(AVG(pl.score),1)         AS score_moyen,
               MAX(pl.date_completion)        AS derniere_completion
        FROM utilisateurs u
        LEFT JOIN progression_lecons pl ON u.id = pl.utilisateur_id AND pl.statut='termine'
        WHERE u.id = $uid AND u.role='utilisateur'
        GROUP BY u.id
    ");
    $user = mysqli_fetch_assoc($res);
    if ($user) $mode = 'certificat';
}

// ── Liste des utilisateurs éligibles (tous terminés si pas de filtre) ─────────
$nb_total_lecons = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM lecons"))['n'];

$utilisateurs = [];
$res2 = mysqli_query($conn, "
    SELECT u.id, u.nom, u.prenom, u.email, u.ville,
           COUNT(DISTINCT pl.lecon_id) AS nb_terminees,
           ROUND(AVG(pl.score),1)      AS score_moyen,
           MAX(pl.date_completion)     AS derniere_completion
    FROM utilisateurs u
    INNER JOIN progression_lecons pl ON u.id = pl.utilisateur_id AND pl.statut='termine'
    WHERE u.role='utilisateur'
    GROUP BY u.id
    ORDER BY nb_terminees DESC, score_moyen DESC
");
while ($r = mysqli_fetch_assoc($res2)) {
    $utilisateurs[] = $r;
}

mysqli_close($conn);

// ── Date du certificat : toujours la date du jour ──────────────────────────────────
$date_cert = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat de Participation — VOP Admin</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        /* ════════════════════════════════════════════
           LISTE DES BÉNÉFICIAIRES (mode sélection)
        ════════════════════════════════════════════ */
        body { background: #f0f2f5; }

        .list-toolbar {
            background: #fff;
            border-bottom: 2px solid #e0e0e0;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .list-toolbar h2 { flex:1; font-size:1.1em; margin:0; color:#6a1b9a; }

        .list-wrapper {
            max-width: 900px;
            margin: 28px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .list-head {
            background: linear-gradient(135deg, #6a1b9a, #4a148c);
            color: #fff;
            padding: 24px 32px 18px;
        }
        .list-head h1 { margin:0 0 6px; font-size:1.5em; }
        .list-head p  { margin:0; opacity:.8; font-size:.88em; }

        .user-table { width:100%; border-collapse:collapse; font-size:.92em; }
        .user-table thead th { background:#f5f5f5; padding:10px 14px; text-align:left; font-weight:600; color:#555; border-bottom:2px solid #e0e0e0; }
        .user-table tbody tr:nth-child(even) { background:#fafafa; }
        .user-table tbody tr:hover { background:#f3e5f5; }
        .user-table td { padding:10px 14px; border-bottom:1px solid #eee; vertical-align:middle; }
        .chip-ok { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; border-radius:20px; padding:3px 10px; font-size:.8em; font-weight:600; }
        .chip-partial { background:#fff8e1; color:#f57f17; border:1px solid #ffe082; border-radius:20px; padding:3px 10px; font-size:.8em; font-weight:600; }
        .btn-open-cert { background:#6a1b9a; color:#fff; border:none; padding:6px 14px; border-radius:6px; font-size:.82em; font-weight:600; cursor:pointer; text-decoration:none; }
        .btn-open-cert:hover { background:#4a148c; }
        .btn-back { background:#e0e0e0; color:#333; border:none; padding:6px 14px; border-radius:6px; font-size:.88em; cursor:pointer; text-decoration:none; }
        .btn-back:hover { background:#bdbdbd; }

        /* ════════════════════════════════════════════
           CERTIFICAT (rendu final)
        ════════════════════════════════════════════ */
        #cert-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.65);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        #cert-overlay.active { display: flex; }

        #cert-wrap {
            width: 297mm;
            min-height: 210mm;
            background: #f5f5f0;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 0 8px 48px rgba(0,0,0,.5);
            font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, Georgia, serif;
        }

        /* Texture de fond (imitation marbre/parchemin) */
        #cert-wrap::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 80% at 30% 40%, rgba(200,195,180,.35) 0%, transparent 70%),
                radial-gradient(ellipse 80% 60% at 70% 60%, rgba(180,175,160,.25) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Barre verticale teal droite */
        .cert-side-bar {
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 42mm;
            background: linear-gradient(180deg, #00838f 0%, #006064 100%);
            z-index: 1;
        }
        /* Motif décoratif sur la barre */
        .cert-side-bar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 6px,
                rgba(255,255,255,.06) 6px,
                rgba(255,255,255,.06) 12px
            );
        }

        /* Dots décoratifs bas-gauche */
        .cert-dots {
            position: absolute;
            bottom: -10mm;
            left: -10mm;
            width: 80mm;
            height: 80mm;
            z-index: 1;
        }

        /* Barre verte bas droite (devant la barre teal) */
        .cert-bottom-bar {
            position: absolute;
            bottom: 0; right: 0;
            width: 42mm;
            height: 18mm;
            background: #2e7d32;
            z-index: 2;
        }

        /* Zone de contenu */
        .cert-content {
            position: relative;
            z-index: 3;
            padding: 12mm 52mm 14mm 16mm;
            min-height: 210mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* En-tête */
        .cert-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8mm; }
        .cert-org-block { flex: 1; }
        .cert-org-line1 { font-size: 10.5pt; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: .04em; line-height:1.3; }
        .cert-org-line2 { font-size: 9pt; color: #444; margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }
        .cert-org-line3 { font-size: 8.5pt; color: #00838f; font-weight: 600; text-transform: uppercase; margin-top: 3px; }
        .cert-logo {
            width: 20mm;
            height: 20mm;
            flex-shrink: 0;
            margin-left: 10mm;
        }

        /* Séparateur décoratif */
        .cert-divider {
            height: 3px;
            background: linear-gradient(90deg, #00838f, #2e7d32, #00838f);
            margin: 0 0 8mm;
            border-radius: 2px;
        }

        /* Titre */
        .cert-title {
            text-align: center;
            font-size: 26pt;
            font-weight: 700;
            color: #00838f;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 3mm;
            line-height: 1.1;
            text-shadow: 1px 1px 0 rgba(0,131,143,.15);
        }
        .cert-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #888;
            letter-spacing: .25em;
            text-transform: uppercase;
            margin-bottom: 8mm;
        }

        /* Corps du texte */
        .cert-body {
            font-size: 12.5pt;
            color: #1a1a1a;
            line-height: 1.9;
            text-align: justify;
            flex: 1;
        }
        .cert-name {
            font-size: 16pt;
            font-weight: 700;
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding: 0 6px;
            display: inline-block;
        }

        /* Date */
        .cert-date {
            font-size: 10.5pt;
            color: #444;
            margin-top: 8mm;
        }

        /* Signatures */
        .cert-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 10mm;
            gap: 6mm;
        }
        .cert-sig {
            flex: 1;
            text-align: center;
            border-top: 1.5px solid #00838f;
            padding-top: 4mm;
        }
        .cert-sig .sig-role { font-size: 8pt; color: #555; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2mm; }
        .cert-sig .sig-nom  { font-size: 9pt; font-weight: 700; color: #1a1a1a; }

        /* Logo SVG adventiste (flamme stylisée) */
        .adventist-logo { width: 100%; height: 100%; }

        /* ════════════════════════════════════════════
           BOUTONS DE CONTRÔLE (overlay)
        ════════════════════════════════════════════ */
        #cert-actions {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 1100;
            display: none;
            flex-direction: column;
            gap: 8px;
        }
        #cert-actions.active { display: flex; }
        #cert-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: .9em;
            font-weight: 600;
            cursor: pointer;
        }
        #btn-print-cert { background: #1565c0; color: #fff; }
        #btn-close-cert { background: #c62828; color: #fff; }

        /* ════════════════════════════════════════════
           IMPRESSION CERTIFICAT
        ════════════════════════════════════════════ */
        @media print {
            @page { size: A4 landscape; margin: 0; }
            body, html { margin:0; padding:0; background:#f5f5f0 !important; }
            .list-toolbar, .list-wrapper, #cert-actions, .no-print { display:none !important; }
            #cert-overlay {
                display: flex !important;
                position: static !important;
                background: none !important;
            }
            #cert-wrap {
                width: 297mm !important;
                min-height: 210mm !important;
                box-shadow: none !important;
            }
            /* Compenser margin:0 avec padding interne */
            .cert-content { padding-top: 10mm !important; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════
     MODE : CERTIFICAT DIRECT (appelé depuis palmarès)
══════════════════════════════════════════════════ -->
<?php if ($mode === 'certificat' && $user): ?>
<div id="cert-overlay" class="active">
    <?= cert_render($user, $date_cert) ?>
</div>
<div id="cert-actions" class="active">
    <button id="btn-print-cert" onclick="window.print()">🖨️ Imprimer / PDF</button>
    <button id="btn-close-cert" onclick="history.back()">✕ Fermer</button>
</div>

<?php else: ?>

<!-- ══════════════════════════════════════════════════
     MODE : LISTE DE SÉLECTION
══════════════════════════════════════════════════ -->
<div class="list-toolbar no-print">
    <a href="../dashboard.php" style="color:#666;text-decoration:none;">← Dashboard</a>
    <h2>🎓 Certificats de Participation</h2>
    <a href="palmares.php" class="btn-back">📋 Voir le Palmarès</a>
    <button onclick="window.print()" class="btn-back">🖨️ Imprimer la liste</button>
</div>

<div class="list-wrapper">
    <div class="list-head">
        <h1>Sélectionner un bénéficiaire</h1>
        <p>Cliquez sur un participant pour générer et imprimer son certificat.</p>
    </div>
    <?php if (empty($utilisateurs)): ?>
        <div style="padding:40px;text-align:center;color:#999;">Aucun participant avec des leçons terminées.</div>
    <?php else: ?>
    <table class="user-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom &amp; Prénom</th>
                <th>Ville</th>
                <th>Leçons terminées</th>
                <th>Score moyen</th>
                <th>Éligibilité</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $i => $u):
            $eligible = ($u['nb_terminees'] >= $nb_total_lecons && $nb_total_lecons > 0);
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td>
                <strong><?= h($u['nom']) ?> <?= h($u['prenom']) ?></strong><br>
                <small style="color:#888;"><?= h($u['email']) ?></small>
            </td>
            <td><?= h($u['ville'] ?: '—') ?></td>
            <td><?= $u['nb_terminees'] ?> / <?= $nb_total_lecons ?></td>
            <td><?= $u['score_moyen'] ?>%</td>
            <td>
                <?php if ($eligible): ?>
                    <span class="chip-ok">✔ Complet</span>
                <?php else: ?>
                    <span class="chip-partial">⚠ Partiel</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="certificat.php?user_id=<?= $u['id'] ?>" class="btn-open-cert">🎓 Certificat</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Overlay (utilisation JS si on reste sur la même page) -->
<div id="cert-overlay">
    <div id="cert-dynamic"></div>
</div>
<div id="cert-actions">
    <button id="btn-print-cert" onclick="window.print()">🖨️ Imprimer / PDF</button>
    <button id="btn-close-cert">✕ Fermer</button>
</div>
<script>
document.getElementById('btn-close-cert').addEventListener('click', function() {
    document.getElementById('cert-overlay').classList.remove('active');
    document.getElementById('cert-actions').classList.remove('active');
});
</script>

<?php endif; ?>

</body>
</html>
<?php
// ══════════════════════════════════════════════════
// HELPER : génère le HTML du certificat
// ══════════════════════════════════════════════════
function cert_render(array $user, string $date): string {
    $nom_complet = htmlspecialchars($user['prenom'] . ' ' . strtoupper($user['nom']), ENT_QUOTES, 'UTF-8');
    // $date est toujours au format d/m/Y
    $parts = explode('/', $date);
    $day   = $parts[0] ?? date('d');
    $month = $parts[1] ?? date('m');
    $year  = $parts[2] ?? date('Y');

    ob_start();
    ?>
    <div id="cert-wrap">
        <!-- Éléments décoratifs de fond -->
        <div class="cert-side-bar"></div>
        <div class="cert-bottom-bar"></div>

        <!-- Cercles décoratifs bas gauche -->
        <svg class="cert-dots" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <circle cx="40"  cy="160" r="38" fill="#e65100" opacity=".75"/>
            <circle cx="80"  cy="145" r="28" fill="#00838f" opacity=".7"/>
            <circle cx="15"  cy="120" r="22" fill="#2e7d32" opacity=".6"/>
            <circle cx="105" cy="170" r="18" fill="#00838f" opacity=".5"/>
            <circle cx="60"  cy="185" r="14" fill="#e65100" opacity=".5"/>
        </svg>

        <!-- Contenu -->
        <div class="cert-content">

            <!-- En-tête organisation -->
            <div class="cert-header">
                <div class="cert-org-block">
                    <div class="cert-org-line1">Eglise Adventiste du 7eme jour</div>
                    <div class="cert-org-line2">Association du Nord-Kivu</div>
                    <div class="cert-org-line3">Département de la Voix de l'Espérance</div>
                </div>
                <!-- Logo adventiste -->
                <div class="cert-logo">
                    <img src="../../assets/img/logo-adventiste.jpg" alt="Logo VOP" style="width:100%;height:100%;object-fit:contain;">
                </div>
            </div>

            <!-- Séparateur -->
            <div class="cert-divider"></div>

            <!-- Titre -->
            <div class="cert-title">Certificat de Participation</div>
            <div class="cert-subtitle">Études Bibliques par Correspondance</div>

            <!-- Corps -->
            <div class="cert-body">
                <p>
                    Le présent document atteste que
                </p>
                <p style="text-align:center; margin: 4mm 0;">
                    <span class="cert-name"><?= $nom_complet ?></span>
                </p>
                <p>
                    a suivi avec succès les cours par correspondance sur les doctrines bibliques,
                    en foi de quoi le présent certificat lui est délivré tout en lui souhaitant
                    une bonne application des vérités étudiées.
                </p>
            </div>

            <!-- Date -->
            <div class="cert-date">
                Fait à Butembo, le&nbsp; <u><?= htmlspecialchars($day,ENT_QUOTES,'UTF-8') ?></u>
                &nbsp;/&nbsp;<u><?= htmlspecialchars($month,ENT_QUOTES,'UTF-8') ?></u>
                &nbsp;/&nbsp;<u><?= htmlspecialchars($year,ENT_QUOTES,'UTF-8') ?></u>
            </div>

            <!-- Signatures -->
            <div class="cert-signatures">
                <div class="cert-sig">
                    <div class="sig-role">Le Président de l'Association du Nord-Kivu</div>
                    <div class="sig-nom">Pasteur K. Kirindera Makeo</div>
                </div>
                <div class="cert-sig">
                    <div class="sig-role">Le Directeur de la Voix de l'Espérance</div>
                    <div class="sig-nom">Pasteur K. Karasaba Sophonie</div>
                </div>
                <div class="cert-sig">
                    <div class="sig-role">Le Directeur de l'Évangélisation</div>
                    <div class="sig-nom">Pasteur K. Kasanga Celestin</div>
                </div>
            </div>

        </div><!-- /cert-content -->
    </div><!-- /cert-wrap -->
    <?php
    return ob_get_clean();
}
// Si on est en mode certificat direct, la fonction a déjà été appelée dans le HTML.
// Si on reste en mode liste, la fonction n'est pas appelée dans le HTML (overlay JS).
