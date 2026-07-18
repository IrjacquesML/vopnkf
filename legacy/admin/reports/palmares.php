<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('../auth/login.php');
}

$conn = get_db_connection();

// Filtres
$filtre_categorie = isset($_GET['categorie_id']) ? intval($_GET['categorie_id']) : 0;
$filtre_seuil     = isset($_GET['seuil'])        ? intval($_GET['seuil'])        : 0; // score minimum %

// Liste des catégories pour le filtre
$categories_arr = [];
$cat_res = mysqli_query($conn, "SELECT id, nom FROM categories ORDER BY ordre ASC");
while ($c = mysqli_fetch_assoc($cat_res)) { $categories_arr[] = $c; }

// Requête palmarès : utilisateurs ayant terminé AU MOINS une leçon
$where_cat = $filtre_categorie ? "AND l.categorie_id = $filtre_categorie" : '';
$where_seuil = '';

$query = "
    SELECT
        u.id,
        u.nom,
        u.prenom,
        u.email,
        u.telephone,
        u.ville,
        u.date_inscription,
        COUNT(DISTINCT pl.lecon_id)                       AS nb_lecons_terminees,
        (SELECT COUNT(*) FROM lecons lx
         WHERE 1=1 $where_cat)                            AS nb_lecons_total,
        ROUND(AVG(pl.score), 1)                           AS score_moyen,
        MAX(pl.date_completion)                           AS derniere_completion
    FROM utilisateurs u
    INNER JOIN progression_lecons pl ON u.id = pl.utilisateur_id
        AND pl.statut = 'termine'
    INNER JOIN lecons l ON pl.lecon_id = l.id
    WHERE u.role = 'utilisateur'
    $where_cat
    GROUP BY u.id
    HAVING nb_lecons_terminees > 0
    ORDER BY nb_lecons_terminees DESC, score_moyen DESC
";
$result_palmares = mysqli_query($conn, $query);
$palmares = [];
while ($row = mysqli_fetch_assoc($result_palmares)) {
    if ($filtre_seuil > 0 && $row['score_moyen'] < $filtre_seuil) continue;
    $palmares[] = $row;
}

// Nombre total de leçons (pour le score de complétion)
$nb_total_global = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as n FROM lecons" . ($filtre_categorie ? " WHERE categorie_id=$filtre_categorie" : '')))['n'];

mysqli_close($conn);

$titre_page = 'Palmarès des participants';
$date_impression = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titre_page) ?> - VOP Admin</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        /* ── Styles généraux ── */
        body { background: #f0f2f5; }

        .print-toolbar {
            background: #fff;
            border-bottom: 2px solid #e0e0e0;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .print-toolbar h2 { flex: 1; font-size: 1.1em; color: #2e7d32; margin: 0; }
        .print-toolbar form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .print-toolbar select, .print-toolbar input[type=number] {
            border: 1px solid #ccc; border-radius: 6px; padding: 5px 10px; font-size: .9em;
        }

        /* ── Tableau palmarès ── */
        .palmares-wrapper {
            max-width: 1100px;
            margin: 24px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .palmares-header {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: #fff;
            padding: 28px 36px 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
        }
        .palmares-header .org { font-size: .82em; opacity: .85; margin-bottom: 4px; }
        .palmares-header h1 { font-size: 1.7em; margin: 0 0 4px; font-family: 'Crimson Text', serif; }
        .palmares-header .meta { font-size: .85em; opacity: .8; }
        .palmares-header .badge-total {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 8px;
            padding: 10px 18px;
            text-align: center;
        }
        .palmares-header .badge-total strong { display: block; font-size: 2em; line-height: 1; }
        .palmares-header .badge-total span { font-size: .8em; opacity: .85; }

        .palmares-table { width: 100%; border-collapse: collapse; font-size: .93em; }
        .palmares-table thead th {
            background: #f5f5f5;
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e0e0e0;
            white-space: nowrap;
        }
        .palmares-table tbody tr:nth-child(even) { background: #fafafa; }
        .palmares-table tbody tr:hover { background: #e8f5e9; }
        .palmares-table td { padding: 11px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }

        .rank { font-weight: 700; font-size: 1.1em; color: #888; width: 40px; text-align: center; }
        .rank.gold   { color: #d4af37; }
        .rank.silver { color: #9e9e9e; }
        .rank.bronze { color: #a0522d; }

        .score-bar-wrap { display: flex; align-items: center; gap: 8px; }
        .score-bar { height: 7px; border-radius: 4px; background: #e0e0e0; flex: 1; min-width: 60px; }
        .score-bar-fill { height: 100%; border-radius: 4px; background: #2e7d32; transition: width .4s; }
        .score-val { font-weight: 600; color: #2e7d32; white-space: nowrap; }

        .completion-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .82em;
            font-weight: 600;
        }
        .chip-complete { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .chip-partial  { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }

        .btn-cert {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: .82em;
            font-weight: 600;
            background: #2e7d32;
            color: #fff;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-cert:hover { background: #1b5e20; }
        .btn-cert.disabled {
            background: #e0e0e0;
            color: #999;
            pointer-events: none;
        }

        .palmares-footer {
            padding: 14px 24px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: .82em;
            display: flex;
            justify-content: space-between;
        }

        /* ── Impression ── */
        @media print {
            @page { size: A4 landscape; margin: 0; }
            body { background: #fff !important; }
            .print-toolbar, .no-print, nav, .navbar { display: none !important; }
            .palmares-wrapper { box-shadow: none; border: none; margin: 0; padding-top: 10mm; }
            .btn-cert { display: none !important; }
            .score-bar { display: none; }
            .score-val { font-size: 1em; }
            .palmares-table { font-size: .85em; }
        }
    </style>
</head>
<body>

<!-- Barre outils (masquée à l'impression) -->
<div class="print-toolbar no-print">
    <a href="../dashboard.php" style="color:#666;text-decoration:none;">← Dashboard</a>
    <h2>📋 Palmarès</h2>

    <form method="GET" style="margin-left:auto;">
        <select name="categorie_id" onchange="this.form.submit()">
            <option value="0">Toutes les catégories</option>
            <?php foreach ($categories_arr as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filtre_categorie == $c['id'] ? 'selected' : '' ?>>
                <?= h($c['nom']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <label style="font-size:.88em;color:#555;">Score min.
            <input type="number" name="seuil" value="<?= $filtre_seuil ?>" min="0" max="100" style="width:60px;"> %
        </label>
        <button type="submit" class="btn btn-small" style="background:#2e7d32;color:#fff;">Filtrer</button>
    </form>

    <button onclick="window.print()" class="btn btn-small" style="background:#1565c0;color:#fff;">🖨️ Imprimer</button>
    <a href="certificat.php" class="btn btn-small" style="background:#6a1b9a;color:#fff;">🎓 Certificats</a>
</div>

<!-- Document imprimable -->
<div class="palmares-wrapper">
    <div class="palmares-header">
        <div style="display:flex;align-items:center;gap:16px;">
            <img src="../../assets/img/logo-adventiste.jpg" alt="Logo VOP" style="width:64px;height:64px;object-fit:contain;flex-shrink:0;">
            <div>
                <div class="org">Eglise Adventiste du 7eme jour — ASSOCIATION DU NORD-KIVU</div>
                <div class="org">DÉPARTEMENT DE LA VOIX DE L'ESPÉRANCE</div>
                <h1>Palmarès des Participants</h1>
                <div class="meta">
                    <?= $filtre_categorie ? 'Catégorie filtrée' : 'Toutes catégories' ?>
                    <?= $filtre_seuil ? " · Score ≥ {$filtre_seuil}%" : '' ?>
                     · Imprimé le <?= $date_impression ?>
                </div>
            </div>
        </div>
        <div class="badge-total">
            <strong><?= count($palmares) ?></strong>
            <span>participant<?= count($palmares) > 1 ? 's' : '' ?></span>
        </div>
    </div>

    <?php if (empty($palmares)): ?>
        <div style="padding:40px;text-align:center;color:#999;">Aucun participant ne correspond aux critères.</div>
    <?php else: ?>
    <table class="palmares-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom &amp; Prénom</th>
                <th>Ville</th>
                <th>Leçons terminées</th>
                <th>Score moyen</th>
                <th>Dernière completion</th>
                <th class="no-print">Certificat</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($palmares as $i => $p):
            $rang = $i + 1;
            $rank_class = $rang === 1 ? 'gold' : ($rang === 2 ? 'silver' : ($rang === 3 ? 'bronze' : ''));
            $pct_completion = $nb_total_global > 0 ? round($p['nb_lecons_terminees'] / $nb_total_global * 100) : 0;
            $score = $p['score_moyen'] ?? 0;
            $eligible_cert = ($p['nb_lecons_terminees'] == $nb_total_global && $nb_total_global > 0);
        ?>
        <tr>
            <td class="rank <?= $rank_class ?>">
                <?= $rang === 1 ? '🥇' : ($rang === 2 ? '🥈' : ($rang === 3 ? '🥉' : $rang)) ?>
            </td>
            <td>
                <strong><?= h($p['nom']) ?> <?= h($p['prenom']) ?></strong><br>
                <small style="color:#888;"><?= h($p['email']) ?></small>
            </td>
            <td><?= h($p['ville'] ?: '—') ?></td>
            <td>
                <span class="completion-chip <?= $eligible_cert ? 'chip-complete' : 'chip-partial' ?>">
                    <?= $p['nb_lecons_terminees'] ?> / <?= $nb_total_global ?>
                </span>
                <small style="color:#aaa;margin-left:4px;">(<?= $pct_completion ?>%)</small>
            </td>
            <td>
                <div class="score-bar-wrap">
                    <div class="score-bar"><div class="score-bar-fill" style="width:<?= min(100,$score) ?>%"></div></div>
                    <span class="score-val"><?= $score ?>%</span>
                </div>
            </td>
            <td style="color:#888;font-size:.88em;">
                <?= $p['derniere_completion'] ? date('d/m/Y', strtotime($p['derniere_completion'])) : '—' ?>
            </td>
            <td class="no-print">
                <?php if ($eligible_cert): ?>
                    <a href="certificat.php?user_id=<?= $p['id'] ?>" class="btn-cert" target="_blank">🎓 Imprimer</a>
                <?php else: ?>
                    <span class="btn-cert disabled">Non éligible</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="palmares-footer">
        <span>VOP — Études Bibliques par Correspondance &nbsp;·&nbsp; Butembo, RDC</span>
        <span>Total leçons disponibles : <?= $nb_total_global ?></span>
    </div>
</div>

</body>
</html>
