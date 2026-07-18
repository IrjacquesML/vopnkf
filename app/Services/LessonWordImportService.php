<?php

namespace App\Services;

use App\Models\Categorie;
use App\Models\Lecon;
use App\Models\OptionReponse;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class LessonWordImportService
{
    /**
     * @return array{lecon: Lecon, questions_count: int, titre: string, preview: array}
     */
    public function import(UploadedFile $file, int $categorieId, ?int $ordre = null): array
    {
        $parsed = $this->parse($file->getRealPath());

        return DB::transaction(function () use ($parsed, $categorieId, $ordre) {
            $categorie = Categorie::findOrFail($categorieId);
            $ordre ??= ((int) Lecon::where('categorie_id', $categorie->id)->max('ordre')) + 1;

            $lecon = Lecon::create([
                'categorie_id' => $categorie->id,
                'titre' => $parsed['titre'],
                'contenu' => $parsed['contenu_html'],
                'ordre' => $ordre,
            ]);

            $questionsCount = 0;
            foreach ($parsed['questions'] as $index => $questionData) {
                $question = Question::create([
                    'lecon_id' => $lecon->id,
                    'question' => $questionData['texte'],
                    'ordre' => $index + 1,
                ]);

                OptionReponse::create([
                    'question_id' => $question->id,
                    'texte_option' => 'À compléter par l\'administrateur',
                    'est_correcte' => true,
                    'ordre' => 1,
                ]);

                $questionsCount++;
            }

            return [
                'lecon' => $lecon,
                'questions_count' => $questionsCount,
                'titre' => $parsed['titre'],
                'preview' => [
                    'numero' => $parsed['numero'],
                    'contenu_extrait' => mb_substr(strip_tags($parsed['contenu_html']), 0, 280).'…',
                ],
            ];
        });
    }

    /**
     * @return array{titre: string, numero: ?int, contenu_html: string, questions: list<array{texte: string, reference: ?string}>}
     */
    public function parse(string $path): array
    {
        $paragraphs = $this->extractParagraphsFromDocx($path);
        $paragraphs = $this->deduplicate($paragraphs);

        $numero = null;
        $titre = 'Leçon importée';
        $titleIndex = null;
        $titleRemainder = null;

        foreach ($paragraphs as $i => $line) {
            if (! preg_match('/LE[ÇC]ON\s*N[°ºo]?\s*(\d+)\s*[:\-–—]\s*(.+)$/iu', $line, $m)) {
                continue;
            }

            $numero = (int) $m[1];
            $rest = trim($m[2]);

            // Titre en majuscules jusqu'au début du corps de texte
            if (preg_match('/^([A-ZÀÂÄÉÈÊËÏÎÔÙÛÜÇ0-9][A-ZÀÂÄÉÈÊËÏÎÔÙÛÜÇ0-9\s\'\-]{1,80}?)(?=[A-ZÀÂÄÉÈÊËÏÎÔÙÛÜÇ]?[a-zàâäéèêëïîôùûüç«“"]|$)/u', $rest, $tm)) {
                $titreCourt = trim($tm[1], " \t-–—:");
                $titleRemainder = trim(mb_substr($rest, mb_strlen($tm[1])));
            } else {
                $titreCourt = mb_substr($rest, 0, 80);
                $titleRemainder = null;
            }

            $titre = 'Leçon '.$numero.' : '.$titreCourt;
            $titleIndex = $i;
            break;
        }

        // Les questions sont souvent sur la feuille détachable (début du document)
        $questions = $this->extractQuestionsFromAll($paragraphs);

        $contentLines = [];
        if ($titleRemainder) {
            $contentLines[] = $titleRemainder;
        }

        $start = $titleIndex !== null ? $titleIndex + 1 : 0;
        for ($i = $start; $i < count($paragraphs); $i++) {
            $line = $paragraphs[$i];

            if ($this->isNoise($line) || $this->isQuestionsHeader($line) || $this->looksLikeQuestion($line)) {
                continue;
            }

            // Fin du contenu : 2e occurrence du titre (recto/verso)
            if ($titleIndex !== null && preg_match('/LE[ÇC]ON\s*N[°ºo]?\s*\d+/iu', $line)) {
                break;
            }

            $contentLines[] = $line;
        }

        // Nettoyage : couper si on retombe sur un gros bloc dupliqué
        $contentLines = $this->stopAtDuplicateLesson($contentLines);
        $contentLines = array_map(function (string $line): string {
            // Couper au milieu d'un paragraphe si le titre réapparaît (recto/verso Word)
            if (preg_match('/^(.*?)(?=LE[ÇC]ON\s*N[°ºo]?\s*\d+)/iu', $line, $m) && trim($m[1]) !== '') {
                return trim($m[1]);
            }

            return $line;
        }, $contentLines);
        $contentLines = array_values(array_filter($contentLines, fn ($l) => ! $this->isNoise($l) && ! preg_match('/^[\.…]{3,}$/u', $l)));

        return [
            'titre' => $titre,
            'numero' => $numero,
            'contenu_html' => $this->linesToHtml($contentLines),
            'questions' => $questions,
        ];
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>
     */
    private function stopAtDuplicateLesson(array $lines): array
    {
        $result = [];
        foreach ($lines as $line) {
            if (preg_match('/LE[ÇC]ON\s*N[°ºo]?\s*\d+/iu', $line) && $result !== []) {
                break;
            }
            $result[] = $line;
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function extractParagraphsFromDocx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier Word.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new \RuntimeException('document.xml introuvable dans le fichier Word.');
        }

        $dom = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $lines = [];
        foreach ($xpath->query('//w:p') as $paragraph) {
            $texts = [];
            foreach ($xpath->query('.//w:t', $paragraph) as $node) {
                $texts[] = $node->textContent;
            }
            $line = trim(preg_replace('/\s+/u', ' ', implode('', $texts)));
            if ($line !== '') {
                $lines[] = html_entity_decode($line, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return $lines;
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>
     */
    private function deduplicate(array $lines): array
    {
        $result = [];
        $prev = null;
        $seenFingerprints = [];

        foreach ($lines as $i => $line) {
            if ($line === $prev) {
                continue;
            }

            // Détection de blocs dupliqués (mise en page recto-verso)
            if ($i >= 5) {
                $window = array_slice($result, -5);
                $window[] = $line;
                $fp = md5(implode('|', $window));
                if (isset($seenFingerprints[$fp])) {
                    continue;
                }
                $seenFingerprints[$fp] = true;
            }

            $result[] = $line;
            $prev = $line;
        }

        return $result;
    }

    private function isNoise(string $line): bool
    {
        $clean = trim($line);
        if ($clean === '' || preg_match('/^\.+$/', $clean) || preg_match('/^\d+$/', $clean)) {
            return true;
        }
        if (preg_match('/^R[ée]ponse\s*:/iu', $clean)) {
            return true;
        }
        if (preg_match('/^Fr\s*\d+$/iu', $clean)) {
            return true;
        }
        if (preg_match('/^CONTACTS?\s*:/iu', $clean)) {
            return true;
        }
        if (preg_match('/^(T[ée]l[ée]phone|Email)\s*:/iu', $clean)) {
            return true;
        }
        if (preg_match('/^ÉTUDES BIBLIQUES/iu', $clean)) {
            return true;
        }
        if (preg_match('/^Note\s*:/iu', $clean) && mb_stripos($clean, 'découpez') !== false) {
            return true;
        }
        if (mb_stripos($clean, 'nkfadventist.org') !== false) {
            return true;
        }

        return false;
    }

    private function isQuestionsHeader(string $line): bool
    {
        return (bool) preg_match('/Lisez votre Bible|r[ée]pondez aux questions/iu', $line);
    }

    private function looksLikeQuestion(string $line): bool
    {
        // Question courte + référence biblique, sans feuille collée
        if (mb_strlen($line) > 220) {
            return false;
        }
        if (mb_stripos($line, 'Note') === 0 || mb_stripos($line, 'Réponse') !== false || mb_stripos($line, 'Reponse') !== false) {
            return false;
        }
        if (substr_count($line, '?') !== 1) {
            return false;
        }

        return (bool) preg_match('/^.+\?\s+((?:\d+\s+)?[A-Za-zÀ-ÿ][^?]{0,60}\d+\s*:\s*[\d\-]+)/u', $line);
    }

    /**
     * @param  list<string>  $paragraphs
     * @return list<array{texte: string, reference: ?string}>
     */
    private function extractQuestionsFromAll(array $paragraphs): array
    {
        $questions = [];
        $seen = [];

        foreach ($paragraphs as $line) {
            // Parfois plusieurs questions sont collées : les découper
            $chunks = preg_split('/(?<=\?)\s+(?=(?:Qui|Que|Quel|Quelle|Quels|Comment|Pourquoi|Quand|O[ùu]|Combien)\b)/u', $line) ?: [$line];

            foreach ($chunks as $chunk) {
                $chunk = trim($chunk);
                if (! $this->looksLikeQuestion($chunk)) {
                    continue;
                }
                if (! preg_match('/^(.+\?)\s+(.+)$/u', $chunk, $m)) {
                    continue;
                }

                $texte = trim($m[1]);
                $ref = trim(preg_replace('/\s*R[ée]ponse\s*:.*$/iu', '', $m[2]));
                $ref = trim(preg_replace('/\.{2,}.*/u', '', $ref));

                if (mb_strlen($texte) < 8 || mb_strlen($texte) > 180) {
                    continue;
                }

                $full = $texte.($ref !== '' ? ' ('.$ref.')' : '');
                $key = mb_strtolower($texte);

                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $questions[] = [
                    'texte' => $full,
                    'reference' => $ref !== '' ? $ref : null,
                ];
            }
        }

        return $questions;
    }

    /**
     * @param  list<string>  $lines
     */
    private function linesToHtml(array $lines): string
    {
        $html = '';
        foreach ($lines as $line) {
            $escaped = e($line);
            if (mb_strlen($line) < 90 && preg_match('/^(R[ée]v[ée]lation|Utilit[ée]|L.Esprit|Inspiration)/iu', $line)) {
                $html .= '<h3>'.$escaped.'</h3>';
                continue;
            }
            $html .= '<p>'.$escaped.'</p>';
        }

        return $html !== '' ? $html : '<p>Contenu à compléter.</p>';
    }
}
