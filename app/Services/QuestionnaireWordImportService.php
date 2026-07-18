<?php

namespace App\Services;

use App\Models\Categorie;
use App\Models\Lecon;
use App\Models\OptionReponse;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class QuestionnaireWordImportService
{
    /**
     * Importe un questionnaire QCM Word (plusieurs LECON avec options A-D).
     *
     * @return array{
     *   lessons_created: int,
     *   lessons_updated: int,
     *   questions_count: int,
     *   options_count: int,
     *   details: list<array{titre: string, numero: int, questions: int, action: string, lecon_id: int}>
     * }
     */
    public function import(
        UploadedFile $file,
        int $categorieId,
        string $mode = 'attach', // attach | create | replace
    ): array {
        $blocks = $this->parse($file->getRealPath());

        if ($blocks === []) {
            throw new \RuntimeException('Aucune leçon / question QCM détectée dans le fichier.');
        }

        return DB::transaction(function () use ($blocks, $categorieId, $mode) {
            $categorie = Categorie::findOrFail($categorieId);
            $created = 0;
            $updated = 0;
            $questionsCount = 0;
            $optionsCount = 0;
            $details = [];

            foreach ($blocks as $block) {
                $lecon = $this->resolveLesson($categorie, $block, $mode);

                if ($lecon->wasRecentlyCreated) {
                    $created++;
                    $action = 'créée';
                } else {
                    $updated++;
                    $action = $mode === 'replace' ? 'questions remplacées' : 'questions ajoutées';
                }

                if ($mode === 'replace' || $mode === 'attach') {
                    if ($mode === 'replace') {
                        $lecon->questions()->each(function (Question $question) {
                            $question->options()->delete();
                            $question->delete();
                        });
                    }
                }

                $startOrdre = $mode === 'attach'
                    ? ((int) $lecon->questions()->max('ordre')) + 1
                    : 1;

                foreach ($block['questions'] as $index => $questionData) {
                    $question = Question::create([
                        'lecon_id' => $lecon->id,
                        'question' => $questionData['texte'],
                        'ordre' => $startOrdre + $index,
                    ]);
                    $questionsCount++;

                    foreach ($questionData['options'] as $optIndex => $option) {
                        OptionReponse::create([
                            'question_id' => $question->id,
                            'texte_option' => $option['texte'],
                            'est_correcte' => $option['est_correcte'],
                            'ordre' => $optIndex + 1,
                        ]);
                        $optionsCount++;
                    }

                    // Si aucune bonne réponse marquée, ne pas forcer — l'admin cochera
                    $hasCorrect = collect($questionData['options'])->contains(fn ($o) => $o['est_correcte']);
                    if (! $hasCorrect && count($questionData['options']) > 0) {
                        // laisser toutes à false
                    }
                }

                $details[] = [
                    'titre' => $lecon->titre,
                    'numero' => $block['numero'],
                    'questions' => count($block['questions']),
                    'action' => $action,
                    'lecon_id' => $lecon->id,
                ];
            }

            return [
                'lessons_created' => $created,
                'lessons_updated' => $updated,
                'questions_count' => $questionsCount,
                'options_count' => $optionsCount,
                'details' => $details,
            ];
        });
    }

    /**
     * @return list<array{numero: int, titre: string, questions: list<array{texte: string, options: list<array{lettre: string, texte: string, est_correcte: bool}>}>}>
     */
    public function parse(string $path): array
    {
        $lines = $this->extractParagraphs($path);
        $blocks = [];
        $current = null;
        $currentQuestion = null;

        $flushQuestion = function () use (&$current, &$currentQuestion) {
            if ($current !== null && $currentQuestion !== null && $currentQuestion['texte'] !== '') {
                if (count($currentQuestion['options']) >= 2) {
                    $current['questions'][] = $currentQuestion;
                }
            }
            $currentQuestion = null;
        };

        $flushLesson = function () use (&$blocks, &$current, $flushQuestion) {
            $flushQuestion();
            if ($current !== null && count($current['questions']) > 0) {
                $blocks[] = $current;
            }
            $current = null;
        };

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // En-tête de leçon : LECON 1 : TITRE
            if (preg_match('/^LE[ÇC]ONS?\s*(\d+)\s*[:\-–—]\s*(.+)$/iu', $line, $m)) {
                $flushLesson();
                $current = [
                    'numero' => (int) $m[1],
                    'titre' => 'Leçon '.(int) $m[1].' : '.trim($m[2]),
                    'questions' => [],
                ];
                continue;
            }

            if ($current === null) {
                continue;
            }

            // Question numérotée : 1. Texte ? Ref
            if (preg_match('/^(\d+)\s*[\.\)]\s*(.+)$/u', $line, $m)) {
                $flushQuestion();
                $texte = trim($m[2]);
                $texte = preg_replace('/\s+/u', ' ', $texte);
                $currentQuestion = [
                    'texte' => $texte,
                    'options' => [],
                ];
                continue;
            }

            // Option A. / B) / *C. (étoile = bonne réponse)
            if (preg_match('/^\*?([A-Da-d])\s*[\.\)]\s*(.+)$/u', $line, $m)) {
                if ($currentQuestion === null) {
                    continue;
                }
                $lettre = strtoupper($m[1]);
                $estCorrecte = str_starts_with(ltrim($line), '*');
                // Aussi détecter (Bonne réponse) dans le texte
                $texteOpt = trim($m[2]);
                if (preg_match('/\((bonne\s*r[ée]ponse|correcte?)\)/iu', $texteOpt)) {
                    $estCorrecte = true;
                    $texteOpt = trim(preg_replace('/\((bonne\s*r[ée]ponse|correcte?)\)/iu', '', $texteOpt));
                }
                $currentQuestion['options'][] = [
                    'lettre' => $lettre,
                    'texte' => $lettre.'. '.$texteOpt,
                    'est_correcte' => $estCorrecte,
                ];
                continue;
            }

            // Ligne orpheline sous une question sans options encore → option A implicite
            if ($currentQuestion !== null && $currentQuestion['options'] === [] && ! preg_match('/^LE[ÇC]ON/iu', $line)) {
                if (mb_strlen($line) < 200 && ! preg_match('/^\d+\s*[\.\)]/', $line)) {
                    $currentQuestion['options'][] = [
                        'lettre' => 'A',
                        'texte' => 'A. '.$line,
                        'est_correcte' => false,
                    ];
                }
            }
        }

        $flushLesson();

        return $blocks;
    }

    /**
     * @param  array{numero: int, titre: string, questions: array}  $block
     */
    private function resolveLesson(Categorie $categorie, array $block, string $mode): Lecon
    {
        $existing = Lecon::query()
            ->where('categorie_id', $categorie->id)
            ->where('ordre', $block['numero'])
            ->first();

        if ($existing && in_array($mode, ['attach', 'replace'], true)) {
            return $existing;
        }

        if ($existing && $mode === 'create') {
            // Créer une nouvelle leçon avec un ordre libre à la fin
            $ordre = ((int) Lecon::where('categorie_id', $categorie->id)->max('ordre')) + 1;

            return Lecon::create([
                'categorie_id' => $categorie->id,
                'titre' => $block['titre'],
                'contenu' => '<p>Questionnaire à choix multiples — Leçon '.$block['numero'].'.</p>',
                'ordre' => $ordre,
            ]);
        }

        return Lecon::create([
            'categorie_id' => $categorie->id,
            'titre' => $block['titre'],
            'contenu' => '<p>Questionnaire à choix multiples — Leçon '.$block['numero'].'.</p>',
            'ordre' => $block['numero'],
        ]);
    }

    /**
     * @return list<string>
     */
    private function extractParagraphs(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier Word.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new \RuntimeException('document.xml introuvable.');
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
            if ($line === '') {
                continue;
            }

            $line = html_entity_decode($line, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Si Word a mis l'option en liste sans la lettre A/B/C/D dans le texte,
            // déduire la lettre via l'indice de numérotation (ilvl/numId approximate: next letter by order).
            if (! preg_match('/^\*?[A-Da-d]\s*[\.\)]/u', $line) && ! preg_match('/^\d+\s*[\.\)]/u', $line) && ! preg_match('/^LE[ÇC]ON/iu', $line)) {
                // Laisse tel quel ; le parseur gère les orphelins
            }

            $lines[] = $line;
        }

        return $lines;
    }
}
