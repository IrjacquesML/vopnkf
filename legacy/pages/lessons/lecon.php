<?php
require_once '../../includes/config.php';
require_once '../../includes/traduction.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    redirect('../auth/connexion.php');
}

$utilisateur_id = get_utilisateur_id();
$lecon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$lecon_id) {
    redirect('dashboard.php');
}

$conn = get_db_connection();

// Récupérer la langue préférée de l'utilisateur
$langue_utilisateur = get_langue_utilisateur($utilisateur_id);

// Récupérer les informations de la leçon
$query = "SELECT l.id, l.titre, l.contenu, l.categorie_id, l.ordre, c.nom as categorie_nom
          FROM lecons l
          INNER JOIN categories c ON l.categorie_id = c.id
          WHERE l.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lecon = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Traduire la leçon si nécessaire
if ($langue_utilisateur !== 'fr' && $lecon) {
    $lecon_traduite = traduire_lecon($lecon_id, $langue_utilisateur);
    if ($lecon_traduite) {
        $lecon['titre'] = $lecon_traduite['titre'];
        $lecon['contenu'] = $lecon_traduite['contenu'];
    }
}

if (!$lecon) {
    redirect('dashboard.php');
}

// Vérifier si la leçon est déverrouillée
function verifier_acces_lecon($conn, $utilisateur_id, $lecon_ordre, $categorie_id) {
    if ($lecon_ordre == 1) {
        return true;
    }
    
    $query = "SELECT l.id 
              FROM lecons l
              INNER JOIN progression_lecons pl ON l.id = pl.lecon_id
              WHERE l.categorie_id = ? 
              AND l.ordre = ? 
              AND pl.utilisateur_id = ?
              AND pl.statut = 'termine'";
    $stmt = mysqli_prepare($conn, $query);
    $ordre_precedent = $lecon_ordre - 1;
    mysqli_stmt_bind_param($stmt, "iii", $categorie_id, $ordre_precedent, $utilisateur_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $acces = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    
    return $acces;
}

if (!verifier_acces_lecon($conn, $utilisateur_id, $lecon['ordre'], $lecon['categorie_id'])) {
    redirect('dashboard.php');
}

// Marquer la leçon comme "en cours" si ce n'est pas déjà fait
$query = "INSERT INTO progression_lecons (utilisateur_id, lecon_id, statut, date_debut) 
          VALUES (?, ?, 'en_cours', NOW())
          ON DUPLICATE KEY UPDATE statut = IF(statut = 'non_commence', 'en_cours', statut),
          date_debut = IF(date_debut IS NULL, NOW(), date_debut)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $utilisateur_id, $lecon_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Récupérer les questions de la leçon
$query = "SELECT id, question, ordre FROM questions WHERE lecon_id = ? ORDER BY ordre ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $lecon_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$questions = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Traduire la question si nécessaire
    if ($langue_utilisateur !== 'fr') {
        $row['question'] = traduire_texte($row['question'], $langue_utilisateur);
    }
    $questions[] = $row;
}
mysqli_stmt_close($stmt);

// Fonction pour normaliser les abréviations de livres bibliques
function normaliser_livre($livre) {
    $livre = trim($livre);
    $livre_lower = mb_strtolower($livre, 'UTF-8');
    
    // Mapping complet : abréviations et variantes → nom complet
    $abreviations = [
        // Ancien Testament - abréviations et variantes
        'ps' => 'Psaumes', 'psaume' => 'Psaumes', 'psaumes' => 'Psaumes',
        'pr' => 'Proverbes', 'proverbe' => 'Proverbes', 'proverbes' => 'Proverbes',
        'ec' => 'Ecclésiaste', 'ecclésiaste' => 'Ecclésiaste', 'ecclesiaste' => 'Ecclésiaste',
        'ct' => 'Cantique', 'cantique' => 'Cantique', 'cant' => 'Cantique',
        'es' => 'Ésaïe', 'esaie' => 'Ésaïe', 'ésaïe' => 'Ésaïe', 'isaie' => 'Ésaïe',
        'jr' => 'Jérémie', 'jeremie' => 'Jérémie', 'jérémie' => 'Jérémie',
        'la' => 'Lamentations', 'lamentation' => 'Lamentations', 'lamentations' => 'Lamentations',
        'ez' => 'Ézéchiel', 'ezechiel' => 'Ézéchiel', 'ézéchiel' => 'Ézéchiel',
        'da' => 'Daniel', 'daniel' => 'Daniel',
        'os' => 'Osée', 'osee' => 'Osée', 'osée' => 'Osée',
        'jl' => 'Joël', 'joel' => 'Joël', 'joël' => 'Joël',
        'am' => 'Amos', 'amos' => 'Amos',
        'ab' => 'Abdias', 'abdias' => 'Abdias',
        'jon' => 'Jonas', 'jonas' => 'Jonas',
        'mi' => 'Michée', 'michee' => 'Michée', 'michée' => 'Michée',
        'na' => 'Nahum', 'nahum' => 'Nahum',
        'ha' => 'Habakuk', 'habakuk' => 'Habakuk',
        'so' => 'Sophonie', 'sophonie' => 'Sophonie',
        'ag' => 'Aggée', 'aggee' => 'Aggée', 'aggée' => 'Aggée',
        'za' => 'Zacharie', 'zacharie' => 'Zacharie',
        'ma' => 'Malachie', 'malachie' => 'Malachie',
        'gen' => 'Genèse', 'genese' => 'Genèse', 'genèse' => 'Genèse',
        'ex' => 'Exode', 'exode' => 'Exode',
        'lev' => 'Lévitique', 'levitique' => 'Lévitique', 'lévitique' => 'Lévitique',
        'nb' => 'Nombres', 'nombres' => 'Nombres',
        'dt' => 'Deutéronome', 'deuteronome' => 'Deutéronome', 'deutéronome' => 'Deutéronome',
        'jos' => 'Josué', 'josue' => 'Josué', 'josué' => 'Josué',
        'jug' => 'Juges', 'juges' => 'Juges',
        'ru' => 'Ruth', 'ruth' => 'Ruth',
        '1sam' => '1 Samuel', '1 samuel' => '1 Samuel', '1samuel' => '1 Samuel',
        '2sam' => '2 Samuel', '2 samuel' => '2 Samuel', '2samuel' => '2 Samuel',
        '1rois' => '1 Rois', '1 rois' => '1 Rois', '1rois' => '1 Rois',
        '2rois' => '2 Rois', '2 rois' => '2 Rois', '2rois' => '2 Rois',
        '1ch' => '1 Chroniques', '1 chroniques' => '1 Chroniques', '1chroniques' => '1 Chroniques',
        '2ch' => '2 Chroniques', '2 chroniques' => '2 Chroniques', '2chroniques' => '2 Chroniques',
        'esd' => 'Esdras', 'esdras' => 'Esdras',
        'neh' => 'Néhémie', 'nehemie' => 'Néhémie', 'néhémie' => 'Néhémie',
        'est' => 'Esther', 'esther' => 'Esther',
        'job' => 'Job', 'job' => 'Job',
        // Nouveau Testament
        'mt' => 'Matthieu', 'matthieu' => 'Matthieu',
        'mc' => 'Marc', 'marc' => 'Marc',
        'lc' => 'Luc', 'luc' => 'Luc',
        'jn' => 'Jean', 'jean' => 'Jean',
        'ac' => 'Actes', 'actes' => 'Actes',
        'ro' => 'Romains', 'romains' => 'Romains',
        '1co' => '1 Corinthiens', '1 corinthiens' => '1 Corinthiens', '1corinthiens' => '1 Corinthiens',
        '2co' => '2 Corinthiens', '2 corinthiens' => '2 Corinthiens', '2corinthiens' => '2 Corinthiens',
        'ga' => 'Galates', 'galates' => 'Galates',
        'ep' => 'Éphésiens', 'ephesiens' => 'Éphésiens', 'éphésiens' => 'Éphésiens',
        'ph' => 'Philippiens', 'philippiens' => 'Philippiens',
        'col' => 'Colossiens', 'colossiens' => 'Colossiens',
        '1th' => '1 Thessaloniciens', '1 thessaloniciens' => '1 Thessaloniciens', '1thessaloniciens' => '1 Thessaloniciens',
        '2th' => '2 Thessaloniciens', '2 thessaloniciens' => '2 Thessaloniciens', '2thessaloniciens' => '2 Thessaloniciens',
        '1tim' => '1 Timothée', '1 timothee' => '1 Timothée', '1 timothée' => '1 Timothée', '1timothee' => '1 Timothée',
        '2tim' => '2 Timothée', '2 timothee' => '2 Timothée', '2 timothée' => '2 Timothée', '2timothee' => '2 Timothée',
        'ti' => 'Tite', 'tite' => 'Tite',
        'phm' => 'Philémon', 'philemon' => 'Philémon', 'philémon' => 'Philémon',
        'he' => 'Hébreux', 'hebreux' => 'Hébreux', 'hébreux' => 'Hébreux',
        'ja' => 'Jacques', 'jacques' => 'Jacques',
        '1pi' => '1 Pierre', '1 pierre' => '1 Pierre', '1pierre' => '1 Pierre',
        '2pi' => '2 Pierre', '2 pierre' => '2 Pierre', '2pierre' => '2 Pierre',
        '1jn' => '1 Jean', '1 jean' => '1 Jean', '1jean' => '1 Jean',
        '2jn' => '2 Jean', '2 jean' => '2 Jean', '2jean' => '2 Jean',
        '3jn' => '3 Jean', '3 jean' => '3 Jean', '3jean' => '3 Jean',
        'ju' => 'Jude', 'jude' => 'Jude',
        'ap' => 'Apocalypse', 'apocalypse' => 'Apocalypse', 'apoc' => 'Apocalypse',
        // Variantes et abréviations courtes pour les livres numérotés
        'tim'  => 'Timothée',        // 2 Tim  → 2 Timothée
        'cor'  => 'Corinthiens',     // 1 Cor  → 1 Corinthiens
        'th'   => 'Thessaloniciens', // 1 Th   → 1 Thessaloniciens
        'sam'  => 'Samuel',          // 1 Sam  → 1 Samuel
        'roi'  => 'Rois',            // 1 Roi  → 1 Rois
        'rois' => 'Rois',
        'chr'  => 'Chroniques',      // 1 Chr  → 1 Chroniques
        'pi'   => 'Pierre',          // 1 Pi   → 1 Pierre
        'jn'   => 'Jean',            // 1 Jn   → 1 Jean (aussi Jean tout seul)
        // Variantes accentuées manquantes
        'matt'      => 'Matthieu',   // Matt ou Matt.
        'esaïe'     => 'Ésaïe',      // Esaïe (ï sans accent sur E)
        'ezéchiel'  => 'Ézéchiel',   // Ezéchiel
        'ephésiens' => 'Éphésiens',  // Ephésiens (sans accent sur E)
        'timothée'  => 'Timothée',
        'thimothée' => 'Timothée',
    ];
    
    // Vérifier si c'est une abréviation connue (insensible à la casse)
    if (isset($abreviations[$livre_lower])) {
        return $abreviations[$livre_lower];
    }
    
    // Si le livre commence par un numéro (ex: "1 Jean"), le préserver
    if (preg_match('/^(\d+)\s+(.+)$/u', $livre, $m)) {
        $num = $m[1];
        $nom = trim($m[2]);
        $nom_lower = mb_strtolower($nom, 'UTF-8');
        if (isset($abreviations[$nom_lower])) {
            return $num . ' ' . $abreviations[$nom_lower];
        }
    }
    
    // Si pas d'abréviation trouvée, capitaliser la première lettre de chaque mot
    return mb_convert_case($livre, MB_CASE_TITLE, 'UTF-8');
}

// Fonction pour traiter le contenu et détecter les versets bibliques
// Gère le contenu HTML avec entités (sortie TinyMCE) et les espaces insécables (&nbsp;)
function traiter_versets($html) {
    // Pattern appliqué après décodage des entités HTML
    // \h = espace horizontal (espace, tabulation, espace insécable U+00A0 = &nbsp; décodé)
    // Supporte: Jean 3:16  |  Jean 3 : 16  |  Matthieu 28 : 19-20  |  Hébreux 2 : 15
    $verse_pattern = '/\b((?:\d+\h+)?[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\h+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\h+(\d+)\h*[,:]\h*(\d+(?:-\d+)?)\b/u';

    // Séparer les balises HTML des nœuds texte
    $parts = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

    $result = '';
    foreach ($parts as $part) {
        // Balise HTML → passer telle quelle sans traitement
        if (strlen($part) > 0 && $part[0] === '<') {
            $result .= $part;
            continue;
        }

        // Nœud texte → décoder les entités HTML (é, &nbsp;, etc.) pour la détection
        $decoded = html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collecter les spans de versets avec des marqueurs de substitution uniques
        $spans = [];
        $idx   = 0;

        $processed = preg_replace_callback($verse_pattern, function($m) use (&$spans, &$idx) {
            $reference_complete = $m[0];
            // Normaliser les espaces multiples/insécables en espace simple
            $livre   = trim(preg_replace('/\h+/', ' ', $m[1]));
            $chapitre = $m[2];
            $verset   = $m[3];

            $livre_normalise      = normaliser_livre($livre);
            $reference_normalisee = $livre_normalise . ' ' . $chapitre . ':' . $verset;

            // Marqueur unique (caractères de contrôle non affectés par htmlspecialchars)
            $marker = "\x02BV{$idx}\x03";
            $spans[$marker] = '<span class="bible-verse"'
                . ' data-reference="' . htmlspecialchars($reference_normalisee, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-livre="'     . htmlspecialchars($livre_normalise, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-chapitre="'  . htmlspecialchars($chapitre, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-verset="'    . htmlspecialchars($verset, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($reference_complete, ENT_QUOTES, 'UTF-8') . '</span>';
            $idx++;
            return $marker;
        }, $decoded);

        // Ré-encoder le texte (les marqueurs \x02...\x03 ne sont pas touchés par htmlspecialchars)
        $safe = htmlspecialchars($processed, ENT_NOQUOTES, 'UTF-8');

        // Remplacer les marqueurs par les spans HTML finaux
        foreach ($spans as $marker => $span) {
            $safe = str_replace($marker, $span, $safe);
        }

        $result .= $safe;
    }

    return $result;
}
?>
<!DOCTYPE html>
<html lang="fr" data-langue="<?php echo $langue_utilisateur; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($lecon['titre']); ?> - VOP, Études Bibliques par Correspondance</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="dashboard.php">← Retour au tableau de bord</a>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php"><?php _e('mes_lecons'); ?></a>
                <a href="../history/historique.php"><?php _e('mon_historique'); ?></a>
                <a href="../prayers/mes_prieres.php"><?php _e('mes_prieres'); ?></a>
                <a href="../auth/profil.php"><?php _e('mon_profil'); ?></a>
            </div>
            <div class="nav-user">
                <a href="../auth/deconnexion.php" class="btn btn-small"><?php _e('deconnexion'); ?></a>
            </div>
        </div>
    </nav>

    <div class="container lesson-container">
        <div class="lesson-header">
            <div class="breadcrumb">
                <?php echo h($lecon['categorie_nom']); ?> / Leçon <?php echo (int) $lecon['ordre']; ?>
            </div>
            <h1 data-traduire="titre" data-id="<?php echo (int) $lecon_id; ?>"><?php echo h($lecon['titre']); ?></h1>
        </div>

        <div class="lesson-content">
            <div class="content-text" data-traduire="contenu" data-id="<?php echo $lecon_id; ?>">
                <?php echo traiter_versets($lecon['contenu']); ?>
            </div>
        </div>

        <?php if (!empty($questions)): ?>
        <div class="quiz-section">
            <h2><?php _e('interrogation'); ?></h2>
            <p>Répondez aux questions suivantes pour valider votre compréhension de la leçon.</p>

            <form method="POST" action="soumettre_quiz.php" id="quizForm">
                <input type="hidden" name="lecon_id" value="<?php echo (int) $lecon_id; ?>">

                <?php foreach ($questions as $index => $question): ?>
                <div class="question-block">
                    <h3><?php _e('question'); ?> <?php echo ($index + 1); ?></h3>
                    <p class="question-text"><?php echo nl2br(traiter_versets(h($question['question']))); ?></p>

                    <?php
                            // Récupérer les options pour cette question
                            $options_query = "SELECT id, texte_option, ordre FROM options_reponse WHERE question_id = ? ORDER BY ordre ASC";
                            $options_stmt = mysqli_prepare($conn, $options_query);
                            mysqli_stmt_bind_param($options_stmt, "i", $question['id']);
                            mysqli_stmt_execute($options_stmt);
                            $options_result = mysqli_stmt_get_result($options_stmt);
                            ?>

                    <div class="options-list">
                        <?php while ($option = mysqli_fetch_assoc($options_result)): 
                                    // Traduire l'option si nécessaire
                                    $texte_option = $option['texte_option'];
                                    if ($langue_utilisateur !== 'fr') {
                                        $texte_option = traduire_texte($texte_option, $langue_utilisateur);
                                    }
                                ?>
                        <label class="option-label">
                            <input type="radio" name="question_<?php echo (int) $question['id']; ?>"
                                value="<?php echo (int) $option['id']; ?>" required>
                            <span><?php echo h($texte_option); ?></span>
                        </label>
                        <?php endwhile; ?>
                    </div>

                    <?php mysqli_stmt_close($options_stmt); ?>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary btn-block"><?php _e('soumettre'); ?> mes réponses</button>
            </form>
        </div>
        <?php else: ?>
        <div class="no-quiz">
            <p>Cette leçon n'a pas d'interrogation pour le moment.</p>
            <a href="dashboard.php" class="btn btn-primary">Retour au tableau de bord</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal pour afficher les versets -->
    <div id="verseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="verseReference"></h3>
            <p id="verseText"></p>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VOP</h3>
                    <p>Études Bibliques par Correspondance</p>
                    <p class="footer-description">Découvrez la vérité biblique et approfondissez votre foi à travers nos
                        leçons interactives.</p>
                </div>

                <div class="footer-section">
                    <p>📧 Email: contact@vop.org</p>
                    <p>📞 Téléphone: +243 961 420 201</p>
                    <p>📍 Adresse: Butembo/ Eglise Adventiste du 7e jour, RDC</p>
                </div>

                <div class="footer-section">
                    <h3>Liens Utiles</h3>
                    <ul class="footer-links">
                        <li><a href="dashboard.php">Mes Leçons</a></li>
                        <li><a href="../history/historique.php">Mon Historique</a></li>
                        <li><a href="../prayers/mes_prieres.php">Mes Prières</a></li>
                        <li><a href="../prayers/demande_priere.php">Demande de Prière</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 VOP - Études Bibliques par Correspondance NKF | Développé par ML DATA +243 982 401 411
                </p>
                <p class="footer-verse">"Car la parole de Dieu est vivante et efficace" - Hébreux 4:12</p>
            </div>
        </div>
    </footer>

    <script>
    var VERSE_API_PATH = '../../api/get_verset.php';
    </script>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/traduction.js"></script>
</body>

</html>
<?php mysqli_close($conn); ?>