<?php
// Page de démonstration pour la détection automatique des versets bibliques
require_once 'includes/config.php';

// Fonction de normalisation des noms de livres (abréviations → noms complets)
function normaliser_livre_demo($livre) {
    $livre_lower = mb_strtolower(trim($livre), 'UTF-8');
    $map = [
        'gen'=>'Genèse','genese'=>'Genèse','genèse'=>'Genèse',
        'ex'=>'Exode','exode'=>'Exode',
        'lev'=>'Lévitique','levitique'=>'Lévitique','lévitique'=>'Lévitique',
        'nb'=>'Nombres','nombres'=>'Nombres',
        'dt'=>'Deutéronome','deuteronome'=>'Deutéronome',
        'ps'=>'Psaumes','psaume'=>'Psaumes','psaumes'=>'Psaumes',
        'ec'=>'Ecclésiaste','ecclesiaste'=>'Ecclésiaste',
        'es'=>'Ésaïe','esaie'=>'Ésaïe','esaïe'=>'Ésaïe','ésaïe'=>'Ésaïe','isaie'=>'Ésaïe',
        'jr'=>'Jérémie','jeremie'=>'Jérémie',
        'ez'=>'Ézéchiel','ezechiel'=>'Ézéchiel','ézéchiel'=>'Ézéchiel','ezéchiel'=>'Ézéchiel',
        'da'=>'Daniel','daniel'=>'Daniel',
        'mi'=>'Michée','michee'=>'Michée',
        'ap'=>'Apocalypse','apocalypse'=>'Apocalypse','apoc'=>'Apocalypse',
        'mt'=>'Matthieu','matthieu'=>'Matthieu','matt'=>'Matthieu',
        'mc'=>'Marc','marc'=>'Marc',
        'lc'=>'Luc','luc'=>'Luc',
        'jn'=>'Jean','jean'=>'Jean',
        'ac'=>'Actes','actes'=>'Actes',
        'ro'=>'Romains','romains'=>'Romains',
        'ga'=>'Galates','galates'=>'Galates',
        'ep'=>'Éphésiens','ephesiens'=>'Éphésiens','éphésiens'=>'Éphésiens','ephésiens'=>'Éphésiens',
        'ph'=>'Philippiens','philippiens'=>'Philippiens',
        'col'=>'Colossiens','colossiens'=>'Colossiens',
        'he'=>'Hébreux','hebreux'=>'Hébreux','hébreux'=>'Hébreux',
        'ja'=>'Jacques','jacques'=>'Jacques',
        'ju'=>'Jude','jude'=>'Jude',
        'tim'=>'Timothée','timothée'=>'Timothée',
        'cor'=>'Corinthiens',
        'th'=>'Thessaloniciens',
        'sam'=>'Samuel',
        'pi'=>'Pierre','pierre'=>'Pierre',
    ];
    if (isset($map[$livre_lower])) return $map[$livre_lower];
    if (preg_match('/^(\d+)\s+(.+)$/u', $livre, $m)) {
        $nom_lower = mb_strtolower(trim($m[2]), 'UTF-8');
        if (isset($map[$nom_lower])) return $m[1] . ' ' . $map[$nom_lower];
    }
    return mb_convert_case($livre, MB_CASE_TITLE, 'UTF-8');
}

// Fonction pour traiter le contenu et détecter les versets bibliques
// Gère le contenu HTML avec entités (sortie TinyMCE) et les espaces insécables (&nbsp;)
function traiter_versets($html) {
    // \h = espace horizontal (espace, tabulation, espace insécable U+00A0 = &nbsp; décodé)
    $verse_pattern = '/\b((?:\d+\h+)?[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\h+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\h+(\d+)\h*[,:]\h*(\d+(?:-\d+)?)\b/u';

    $parts = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result = '';
    foreach ($parts as $part) {
        if (strlen($part) > 0 && $part[0] === '<') { $result .= $part; continue; }
        $decoded = html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $spans = []; $idx = 0;
        $processed = preg_replace_callback($verse_pattern, function($m) use (&$spans, &$idx) {
            $ref     = $m[0];
            $livre   = trim(preg_replace('/\h+/', ' ', $m[1]));
            $chap    = $m[2];
            $verset  = $m[3];
            $livre_n = normaliser_livre_demo($livre);
            $ref_n   = $livre_n . ' ' . $chap . ':' . $verset;
            $marker  = "\x02BV{$idx}\x03";
            $spans[$marker] = '<span class="bible-verse"'
                . ' data-reference="' . htmlspecialchars($ref_n, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-livre="'     . htmlspecialchars($livre_n, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-chapitre="'  . htmlspecialchars($chap, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-verset="'    . htmlspecialchars($verset, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') . '</span>';
            $idx++;
            return $marker;
        }, $decoded);
        $safe = htmlspecialchars($processed, ENT_NOQUOTES, 'UTF-8');
        foreach ($spans as $marker => $span) { $safe = str_replace($marker, $span, $safe); }
        $result .= $safe;
    }
    return $result;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Démonstration - Détection Automatique des Versets Bibliques | VOP</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/spiritual-icons.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php"><span class="icon-cross"></span> VOP</a>
            </div>
            <div class="nav-menu">
                <a href="index.php">Accueil</a>
                <a href="#" class="active">Démo Versets</a>
            </div>
        </div>
    </nav>

    <div class="container lesson-container">
        <div class="lesson-header">
            <h1>Démonstration de la Détection Automatique des Versets</h1>
            <p>Cette page montre comment les références bibliques sont automatiquement détectées et rendues cliquables
                dans le contenu des leçons.</p>
        </div>

        <div class="lesson-content">
            <div class="content-text">
                <h2>Exemples de Références Détectées</h2>
                <p>Notre système détecte automatiquement les références bibliques dans différents formats. Cliquez sur
                    les références ci-dessous pour voir les versets complets :</p>

                <h3>Références simples</h3>
                <p>Le verset le plus connu est <strong>Jean 3:16</strong> qui parle de l'amour de Dieu pour le monde. De
                    même, <strong>Matthieu 11:28</strong> nous invite à venir à Jésus pour trouver le repos.</p>

                <h3>Références avec numéros de livre</h3>
                <p>L'apôtre Jean nous rappelle dans <strong>1 Jean 1:9</strong> que si nous confessons nos péchés, Dieu
                    est fidèle pour nous pardonner. L'épître aux <strong>Éphésiens 2:8</strong> explique que le salaire
                    est un don gratuit de Dieu.</p>

                <h3>Références avec accents</h3>
                <p>La lettre aux <strong>Hébreux 4:12</strong> nous enseigne que la parole de Dieu est vivante et
                    efficace. Le livre des <strong>Psaumes 23:1</strong> nous présente Dieu comme notre berger.</p>

                <h3>Plage de versets</h3>
                <p>Parfois, nous pouvons référencer plusieurs versets comme <strong>Romains 3:23-24</strong> qui parlent
                    du péché et de la grâce de Dieu.</p>

                <h3>Texte biblique avec références multiples</h3>
                <p>Voici un exemple de texte d'étude biblique contenant plusieurs références :</p>

                <blockquote
                    style="border-left: 4px solid var(--dore-subtil); padding-left: 20px; margin: 20px 0; font-style: italic;">
                    <p>La Bible nous enseigne que <strong>tous ont péché</strong> selon <strong>Romains 3:23</strong>,
                        mais Dieu nous offre le salut par grâce. Comme le dit <strong>Éphésiens 2:8</strong>, "c'est par
                        la grâce que vous êtes sauvés". Jésus lui-même nous invite dans <strong>Matthieu 11:28</strong>
                        : "Venez à moi, vous tous qui êtes fatigués et chargés". Cette invitation est valable pour
                        chacun, car selon <strong>1 Jean 1:9</strong>, Dieu est fidèle pour pardonner ceux qui se
                        repentent.</p>
                </blockquote>

                <h3>Références dans des contextes différents</h3>
                <p>Que ce soit dans <strong>Philippiens 4:13</strong> pour trouver la force, ou dans <strong>Romains
                        6:23</strong> pour comprendre les conséquences du péché, chaque référence nous conduit à une
                    vérité biblique importante.</p>

                <div class="card-spiritual" style="margin: 30px 0; padding: 25px;">
                    <h3 style="color: var(--vert-foret); margin-bottom: 15px;">💡 Comment ça fonctionne ?</h3>
                    <ul style="line-height: 1.8;">
                        <li>Le système analyse automatiquement le contenu des leçons</li>
                        <li>Il détecte les références au format "Livre Chapitre:Verset"</li>
                        <li>Supporte les livres avec numéros (1 Jean, 2 Corinthiens, etc.)</li>
                        <li>Gère les caractères accentués français</li>
                        <li>Convertit automatiquement les références en liens cliquables</li>
                        <li>Affiche le texte complet dans une modale élégante</li>
                    </ul>
                </div>
            </div>
        </div>
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
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="pages/auth/connexion.php">Connexion</a></li>
                        <li><a href="pages/auth/inscription.php">Inscription</a></li>
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
    var VERSE_API_PATH = 'api/get_verset.php';
    </script>
    <script src="assets/js/script.js"></script>
</body>

</html>