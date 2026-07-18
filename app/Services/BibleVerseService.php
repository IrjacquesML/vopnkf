<?php

namespace App\Services;

use App\Models\Verset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BibleVerseService
{
    private const VERSE_PATTERN = '/\b((?:\d+\h+)?[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+(?:\h+[A-Za-zÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸàâäæçéèêëïîôœùûüÿ]+)*)\h+(\d+)\h*[,:]\h*(\d+(?:-\d+)?)\b/u';

    public function getVerse(?string $reference, ?string $livre, ?int $chapitre, int|string|null $verset): array
    {
        $reference = $reference !== null ? trim($reference) : null;
        $livre = $livre !== null ? trim($livre) : null;
        $versetStart = 0;
        $versetEnd = 0;

        if (is_string($verset) && preg_match('/^(\d+)(?:-(\d+))?$/', trim($verset), $m)) {
            $versetStart = (int) $m[1];
            $versetEnd = isset($m[2]) ? (int) $m[2] : $versetStart;
        } elseif (is_int($verset)) {
            $versetStart = $versetEnd = $verset;
        }

        if ($reference && (! $livre || ! $chapitre || $versetStart <= 0)) {
            $parsed = $this->parseReference($reference);
            if ($parsed !== null) {
                $livre = $livre ?: $parsed['livre'];
                $chapitre = $chapitre ?: $parsed['chapitre'];
                $versetStart = $versetStart ?: $parsed['verset_debut'];
                $versetEnd = $versetEnd ?: $parsed['verset_fin'];
            }
        }

        if ($livre) {
            $livre = $this->normalizeBook($livre);
        }

        $canonical = null;
        if ($livre && $chapitre && $versetStart > 0) {
            $canonical = $versetEnd > $versetStart
                ? "{$livre} {$chapitre}:{$versetStart}-{$versetEnd}"
                : "{$livre} {$chapitre}:{$versetStart}";
        }

        // 1) Cache local
        $cached = $this->findInCache($livre, $chapitre, $versetStart, $versetEnd, $canonical, $reference);
        if ($cached) {
            return $cached;
        }

        if (! $livre || ! $chapitre || $versetStart <= 0) {
            return $this->failure($reference, $livre, $chapitre, $versetStart, 'Référence biblique invalide.');
        }

        // 2) getbible.net Louis Segond 1910 (français, sans clé)
        $fromGetBible = $this->fetchFromGetBible($livre, $chapitre, $versetStart, $versetEnd);
        if ($fromGetBible) {
            $this->cacheVerses($livre, $chapitre, $fromGetBible);
            return $this->apiSuccess($fromGetBible['texte'], $fromGetBible['reference'], $livre, $chapitre, $versetStart, 'LSG', 'getbible.net');
        }

        // 3) scripture.api.bible (clé optionnelle)
        $fromScripture = $this->fetchFromScriptureApi($livre, $chapitre, $versetStart, $versetEnd);
        if ($fromScripture) {
            $this->cacheSingleVerse($livre, $chapitre, $versetStart, $fromScripture['texte'], $fromScripture['reference']);
            return $this->apiSuccess($fromScripture['texte'], $fromScripture['reference'], $livre, $chapitre, $versetStart, 'LSG', 'scripture.api.bible');
        }

        // 4) bible-api.com (anglais WEB)
        $fromBibleApi = $this->fetchFromBibleApiCom($livre, $chapitre, $versetStart, $versetEnd);
        if ($fromBibleApi) {
            return $this->apiSuccess($fromBibleApi['texte'], $fromBibleApi['reference'], $livre, $chapitre, $versetStart, 'WEB', 'bible-api.com');
        }

        return $this->failure($canonical ?: $reference, $livre, $chapitre, $versetStart, 'Verset introuvable via les sources disponibles.');
    }

    public function processContent(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $parts = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';

        foreach ($parts as $part) {
            if ($part !== '' && str_starts_with($part, '<')) {
                $result .= $part;
                continue;
            }

            $decoded = html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $spans = [];
            $index = 0;

            $processed = preg_replace_callback(
                self::VERSE_PATTERN,
                function (array $matches) use (&$spans, &$index): string {
                    $raw = trim(preg_replace('/\h+/u', ' ', $matches[0]));
                    $livre = $this->normalizeBook(trim(preg_replace('/\h+/u', ' ', $matches[1])));
                    $chapitre = $matches[2];
                    $verset = $matches[3];
                    $normalized = "{$livre} {$chapitre}:{$verset}";

                    $marker = "\x02BV{$index}\x03";
                    $spans[$marker] = sprintf(
                        '<span class="bible-verse" data-reference="%s" data-livre="%s" data-chapitre="%s" data-verset="%s" title="Cliquer pour lire le verset">%s</span>',
                        e($normalized),
                        e($livre),
                        e($chapitre),
                        e($verset),
                        e($raw)
                    );
                    $index++;

                    return $marker;
                },
                $decoded
            );

            $safe = htmlspecialchars($processed ?? $decoded, ENT_NOQUOTES, 'UTF-8');
            foreach ($spans as $marker => $span) {
                $safe = str_replace($marker, $span, $safe);
            }

            $result .= $safe;
        }

        return $result;
    }

    private function findInCache(?string $livre, ?int $chapitre, int $versetStart, int $versetEnd, ?string $canonical, ?string $reference): ?array
    {
        if ($livre && $chapitre && $versetStart > 0 && $versetStart === $versetEnd) {
            $row = Verset::query()
                ->where('livre', $livre)
                ->where('chapitre', $chapitre)
                ->where('verset', $versetStart)
                ->first();

            if ($row) {
                return $this->successResponse($row, 'cache');
            }
        }

        foreach (array_filter([$canonical, $reference, $reference ? str_replace(',', ':', $reference) : null]) as $ref) {
            $row = Verset::query()->where('reference', $ref)->first();
            if ($row) {
                return $this->successResponse($row, 'cache');
            }
        }

        // Plage : concaténer les versets en cache
        if ($livre && $chapitre && $versetEnd > $versetStart) {
            $rows = Verset::query()
                ->where('livre', $livre)
                ->where('chapitre', $chapitre)
                ->whereBetween('verset', [$versetStart, $versetEnd])
                ->orderBy('verset')
                ->get();

            if ($rows->count() === ($versetEnd - $versetStart + 1)) {
                return [
                    'success' => true,
                    'texte' => $rows->pluck('texte')->implode(' '),
                    'reference' => $canonical,
                    'livre' => $livre,
                    'chapitre' => $chapitre,
                    'verset' => $versetStart,
                    'version' => $rows->first()->version ?? 'LSG',
                    'source' => 'cache',
                ];
            }
        }

        return null;
    }

    private function fetchFromGetBible(string $livre, int $chapitre, int $versetStart, int $versetEnd): ?array
    {
        $english = $this->bookToEnglish($livre);
        if (! $english) {
            return null;
        }

        $refEn = $english.' '.$chapitre.':'.$versetStart;
        if ($versetEnd > $versetStart) {
            $refEn .= '-'.$versetEnd;
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'VOP-Etude/1.0'])
                ->withoutVerifying()
                ->get('https://query.getbible.net/v2/ls1910/'.rawurlencode($refEn));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data) || empty($data)) {
                return null;
            }

            $block = reset($data);
            if (empty($block['verses']) || ! is_array($block['verses'])) {
                return null;
            }

            $texts = [];
            $verses = [];
            foreach ($block['verses'] as $verse) {
                $text = trim((string) ($verse['text'] ?? ''));
                $num = (int) ($verse['verse'] ?? 0);
                if ($text !== '' && $num > 0) {
                    $texts[] = $text;
                    $verses[] = ['verse' => $num, 'text' => $text];
                }
            }

            $texte = trim(implode(' ', $texts));
            if ($texte === '') {
                return null;
            }

            $bookName = $block['book_name'] ?? $livre;
            $reference = $versetEnd > $versetStart
                ? "{$livre} {$chapitre}:{$versetStart}-{$versetEnd}"
                : "{$livre} {$chapitre}:{$versetStart}";

            return [
                'texte' => $texte,
                'reference' => $reference,
                'verses' => $verses,
                'book_name' => $bookName,
            ];
        } catch (\Throwable $e) {
            Log::warning('getbible.net error: '.$e->getMessage());

            return null;
        }
    }

    private function fetchFromScriptureApi(string $livre, int $chapitre, int $versetStart, int $versetEnd): ?array
    {
        $apiKey = config('bible.api_key');
        if (empty($apiKey)) {
            return null;
        }

        $osis = $this->bookToOsis($livre);
        $bibleId = config('bible.lsg_id') ?: $this->discoverLsgId($apiKey);
        if (! $osis || ! $bibleId) {
            return null;
        }

        $passageId = "{$osis}.{$chapitre}.{$versetStart}";
        if ($versetEnd > $versetStart) {
            $passageId .= "-{$osis}.{$chapitre}.{$versetEnd}";
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->withoutVerifying()
                ->get(rtrim(config('bible.api_url'), '/')."/bibles/{$bibleId}/passages/".rawurlencode($passageId), [
                    'content-type' => 'text',
                    'include-notes' => 'false',
                    'include-titles' => 'false',
                    'include-chapter-numbers' => 'false',
                    'include-verse-numbers' => 'false',
                    'include-verse-spans' => 'false',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $content = $response->json('data.content');
            if (! $content) {
                return null;
            }

            $texte = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($texte === '') {
                return null;
            }

            $reference = $versetEnd > $versetStart
                ? "{$livre} {$chapitre}:{$versetStart}-{$versetEnd}"
                : "{$livre} {$chapitre}:{$versetStart}";

            return ['texte' => $texte, 'reference' => $reference];
        } catch (\Throwable $e) {
            Log::warning('scripture.api.bible error: '.$e->getMessage());

            return null;
        }
    }

    private function fetchFromBibleApiCom(string $livre, int $chapitre, int $versetStart, int $versetEnd): ?array
    {
        $english = $this->bookToEnglish($livre);
        if (! $english) {
            return null;
        }

        $ref = str_replace(' ', '+', $english)."+{$chapitre}:{$versetStart}";
        if ($versetEnd > $versetStart) {
            $ref .= "-{$versetEnd}";
        }

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withoutVerifying()
                ->get("https://bible-api.com/{$ref}");

            if (! $response->successful() || empty($response->json('text'))) {
                return null;
            }

            $texte = trim(preg_replace('/\s+/u', ' ', (string) $response->json('text')));
            $reference = $versetEnd > $versetStart
                ? "{$livre} {$chapitre}:{$versetStart}-{$versetEnd}"
                : "{$livre} {$chapitre}:{$versetStart}";

            return ['texte' => $texte, 'reference' => $reference];
        } catch (\Throwable $e) {
            Log::warning('bible-api.com error: '.$e->getMessage());

            return null;
        }
    }

    private function discoverLsgId(string $apiKey): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['api-key' => $apiKey, 'Accept' => 'application/json'])
                ->withoutVerifying()
                ->get(rtrim(config('bible.api_url'), '/').'/bibles', [
                    'language' => 'fra',
                    'includeFullDetails' => 'false',
                ]);

            if (! $response->successful()) {
                return null;
            }

            foreach ($response->json('data') ?? [] as $bible) {
                $haystack = mb_strtolower(($bible['name'] ?? '').' '.($bible['nameLocal'] ?? '').' '.($bible['abbreviation'] ?? ''), 'UTF-8');
                if (str_contains($haystack, 'louis segond') || str_contains($haystack, 'lsg') || ($bible['abbreviation'] ?? '') === 'LSG') {
                    return $bible['id'] ?? null;
                }
            }

            return $response->json('data.0.id');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function cacheVerses(string $livre, int $chapitre, array $payload): void
    {
        foreach ($payload['verses'] ?? [] as $verse) {
            $num = (int) ($verse['verse'] ?? 0);
            $text = trim((string) ($verse['text'] ?? ''));
            if ($num > 0 && $text !== '') {
                $this->cacheSingleVerse($livre, $chapitre, $num, $text, "{$livre} {$chapitre}:{$num}");
            }
        }
    }

    private function cacheSingleVerse(string $livre, int $chapitre, int $verset, string $texte, string $reference): void
    {
        Verset::query()->updateOrCreate(
            [
                'livre' => $livre,
                'chapitre' => $chapitre,
                'verset' => $verset,
                'version' => 'LSG',
            ],
            [
                'reference' => $reference,
                'texte' => $texte,
            ]
        );
    }

    private function parseReference(string $reference): ?array
    {
        if (! preg_match('/^(.+?)\s+(\d+)\s*[,:]\s*(\d+)(?:-(\d+))?/u', trim($reference), $matches)) {
            return null;
        }

        $start = (int) $matches[3];

        return [
            'livre' => $this->normalizeBook(trim($matches[1])),
            'chapitre' => (int) $matches[2],
            'verset_debut' => $start,
            'verset_fin' => isset($matches[4]) ? (int) $matches[4] : $start,
        ];
    }

    public function normalizeBook(string $livre): string
    {
        $livre = trim(preg_replace('/\s+/u', ' ', $livre));
        $key = $this->stripAccents(mb_strtolower($livre, 'UTF-8'));

        $map = [
            'ps' => 'Psaumes', 'psaume' => 'Psaumes', 'psaumes' => 'Psaumes',
            'pr' => 'Proverbes', 'proverbe' => 'Proverbes', 'proverbes' => 'Proverbes',
            'ec' => 'Ecclésiaste', 'ecclesiaste' => 'Ecclésiaste',
            'ct' => 'Cantique', 'cantique' => 'Cantique',
            'es' => 'Ésaïe', 'esaie' => 'Ésaïe', 'isaie' => 'Ésaïe',
            'jr' => 'Jérémie', 'jeremie' => 'Jérémie',
            'la' => 'Lamentations', 'lamentations' => 'Lamentations',
            'ez' => 'Ézéchiel', 'ezechiel' => 'Ézéchiel',
            'da' => 'Daniel', 'daniel' => 'Daniel',
            'os' => 'Osée', 'osee' => 'Osée',
            'jl' => 'Joël', 'joel' => 'Joël',
            'am' => 'Amos', 'amos' => 'Amos',
            'ab' => 'Abdias', 'abdias' => 'Abdias',
            'jon' => 'Jonas', 'jonas' => 'Jonas',
            'mi' => 'Michée', 'michee' => 'Michée',
            'na' => 'Nahum', 'nahum' => 'Nahum',
            'ha' => 'Habakuk', 'habakuk' => 'Habakuk',
            'so' => 'Sophonie', 'sophonie' => 'Sophonie',
            'ag' => 'Aggée', 'aggee' => 'Aggée',
            'za' => 'Zacharie', 'zacharie' => 'Zacharie',
            'ma' => 'Malachie', 'malachie' => 'Malachie',
            'gen' => 'Genèse', 'genese' => 'Genèse',
            'ex' => 'Exode', 'exode' => 'Exode',
            'lev' => 'Lévitique', 'levitique' => 'Lévitique',
            'nb' => 'Nombres', 'nombres' => 'Nombres',
            'dt' => 'Deutéronome', 'deuteronome' => 'Deutéronome',
            'jos' => 'Josué', 'josue' => 'Josué',
            'jug' => 'Juges', 'juges' => 'Juges',
            'ru' => 'Ruth', 'ruth' => 'Ruth',
            '1sam' => '1 Samuel', '1 samuel' => '1 Samuel', '1samuel' => '1 Samuel',
            '2sam' => '2 Samuel', '2 samuel' => '2 Samuel', '2samuel' => '2 Samuel',
            '1rois' => '1 Rois', '1 rois' => '1 Rois',
            '2rois' => '2 Rois', '2 rois' => '2 Rois',
            '1ch' => '1 Chroniques', '1 chroniques' => '1 Chroniques',
            '2ch' => '2 Chroniques', '2 chroniques' => '2 Chroniques',
            'esd' => 'Esdras', 'esdras' => 'Esdras',
            'neh' => 'Néhémie', 'nehemie' => 'Néhémie',
            'est' => 'Esther', 'esther' => 'Esther',
            'job' => 'Job',
            'mt' => 'Matthieu', 'matthieu' => 'Matthieu', 'matt' => 'Matthieu',
            'mc' => 'Marc', 'marc' => 'Marc',
            'lc' => 'Luc', 'luc' => 'Luc',
            'jn' => 'Jean', 'jean' => 'Jean',
            'ac' => 'Actes', 'actes' => 'Actes',
            'ro' => 'Romains', 'romains' => 'Romains',
            '1co' => '1 Corinthiens', '1 corinthiens' => '1 Corinthiens', '1cor' => '1 Corinthiens',
            '2co' => '2 Corinthiens', '2 corinthiens' => '2 Corinthiens', '2cor' => '2 Corinthiens',
            'ga' => 'Galates', 'galates' => 'Galates',
            'ep' => 'Éphésiens', 'ephesiens' => 'Éphésiens',
            'ph' => 'Philippiens', 'philippiens' => 'Philippiens',
            'col' => 'Colossiens', 'colossiens' => 'Colossiens',
            '1th' => '1 Thessaloniciens', '1 thessaloniciens' => '1 Thessaloniciens',
            '2th' => '2 Thessaloniciens', '2 thessaloniciens' => '2 Thessaloniciens',
            '1tim' => '1 Timothée', '1 timothee' => '1 Timothée', '1timothee' => '1 Timothée', '1 tim' => '1 Timothée', '2 tim' => '2 Timothée',
            '2tim' => '2 Timothée', '2 timothee' => '2 Timothée', '2timothee' => '2 Timothée',
            'ti' => 'Tite', 'tite' => 'Tite',
            'phm' => 'Philémon', 'philemon' => 'Philémon',
            'he' => 'Hébreux', 'hebreux' => 'Hébreux',
            'ja' => 'Jacques', 'jacques' => 'Jacques',
            '1pi' => '1 Pierre', '1 pierre' => '1 Pierre',
            '2pi' => '2 Pierre', '2 pierre' => '2 Pierre',
            '1jn' => '1 Jean', '1 jean' => '1 Jean',
            '2jn' => '2 Jean', '2 jean' => '2 Jean',
            '3jn' => '3 Jean', '3 jean' => '3 Jean',
            'ju' => 'Jude', 'jude' => 'Jude',
            'ap' => 'Apocalypse', 'apocalypse' => 'Apocalypse',
            'tim' => 'Timothée', 'cor' => 'Corinthiens', 'th' => 'Thessaloniciens',
            'sam' => 'Samuel', 'rois' => 'Rois', 'chr' => 'Chroniques', 'pi' => 'Pierre',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        if (preg_match('/^(\d+)\s+(.+)$/u', $livre, $m)) {
            $num = $m[1];
            $nameKey = $this->stripAccents(mb_strtolower(trim($m[2]), 'UTF-8'));
            $composed = $num.' '.$nameKey;
            if (isset($map[$composed])) {
                return $map[$composed];
            }
            if (isset($map[$nameKey])) {
                return $num.' '.$map[$nameKey];
            }
        }

        return mb_convert_case($livre, MB_CASE_TITLE, 'UTF-8');
    }

    private function bookToEnglish(string $livre): ?string
    {
        $key = $this->stripAccents($livre);
        $key = implode(' ', array_map('ucfirst', explode(' ', $key)));

        $map = [
            'Genese' => 'Genesis', 'Exode' => 'Exodus', 'Levitique' => 'Leviticus',
            'Nombres' => 'Numbers', 'Deuteronome' => 'Deuteronomy', 'Josue' => 'Joshua',
            'Juges' => 'Judges', 'Ruth' => 'Ruth', '1 Samuel' => '1 Samuel', '2 Samuel' => '2 Samuel',
            '1 Rois' => '1 Kings', '2 Rois' => '2 Kings',
            '1 Chroniques' => '1 Chronicles', '2 Chroniques' => '2 Chronicles',
            'Esdras' => 'Ezra', 'Nehemie' => 'Nehemiah', 'Esther' => 'Esther', 'Job' => 'Job',
            'Psaumes' => 'Psalms', 'Proverbes' => 'Proverbs', 'Ecclesiaste' => 'Ecclesiastes',
            'Cantique' => 'Song of Solomon', 'Esaie' => 'Isaiah', 'Jeremie' => 'Jeremiah',
            'Lamentations' => 'Lamentations', 'Ezechiel' => 'Ezekiel', 'Daniel' => 'Daniel',
            'Osee' => 'Hosea', 'Joel' => 'Joel', 'Amos' => 'Amos', 'Abdias' => 'Obadiah',
            'Jonas' => 'Jonah', 'Michee' => 'Micah', 'Nahum' => 'Nahum', 'Habakuk' => 'Habakkuk',
            'Sophonie' => 'Zephaniah', 'Aggee' => 'Haggai', 'Zacharie' => 'Zechariah',
            'Malachie' => 'Malachi', 'Matthieu' => 'Matthew', 'Marc' => 'Mark', 'Luc' => 'Luke',
            'Jean' => 'John', 'Actes' => 'Acts', 'Romains' => 'Romans',
            '1 Corinthiens' => '1 Corinthians', '2 Corinthiens' => '2 Corinthians',
            'Galates' => 'Galatians', 'Ephesiens' => 'Ephesians', 'Philippiens' => 'Philippians',
            'Colossiens' => 'Colossians', '1 Thessaloniciens' => '1 Thessalonians',
            '2 Thessaloniciens' => '2 Thessalonians', '1 Timothee' => '1 Timothy',
            '2 Timothee' => '2 Timothy', 'Tite' => 'Titus', 'Philemon' => 'Philemon',
            'Hebreux' => 'Hebrews', 'Jacques' => 'James', '1 Pierre' => '1 Peter', '2 Pierre' => '2 Peter',
            '1 Jean' => '1 John', '2 Jean' => '2 John', '3 Jean' => '3 John', 'Jude' => 'Jude',
            'Apocalypse' => 'Revelation',
        ];

        return $map[$key] ?? $map[$this->stripAccents(mb_convert_case($livre, MB_CASE_TITLE, 'UTF-8'))] ?? null;
    }

    private function bookToOsis(string $livre): ?string
    {
        $map = [
            'Genèse' => 'GEN', 'Exode' => 'EXO', 'Lévitique' => 'LEV', 'Nombres' => 'NUM',
            'Deutéronome' => 'DEU', 'Josué' => 'JOS', 'Juges' => 'JDG', 'Ruth' => 'RUT',
            '1 Samuel' => '1SA', '2 Samuel' => '2SA', '1 Rois' => '1KI', '2 Rois' => '2KI',
            '1 Chroniques' => '1CH', '2 Chroniques' => '2CH', 'Esdras' => 'EZR', 'Néhémie' => 'NEH',
            'Esther' => 'EST', 'Job' => 'JOB', 'Psaumes' => 'PSA', 'Proverbes' => 'PRO',
            'Ecclésiaste' => 'ECC', 'Cantique' => 'SNG', 'Ésaïe' => 'ISA', 'Jérémie' => 'JER',
            'Lamentations' => 'LAM', 'Ézéchiel' => 'EZK', 'Daniel' => 'DAN', 'Osée' => 'HOS',
            'Joël' => 'JOL', 'Amos' => 'AMO', 'Abdias' => 'OBA', 'Jonas' => 'JON',
            'Michée' => 'MIC', 'Nahum' => 'NAH', 'Habakuk' => 'HAB', 'Sophonie' => 'ZEP',
            'Aggée' => 'HAG', 'Zacharie' => 'ZEC', 'Malachie' => 'MAL', 'Matthieu' => 'MAT',
            'Marc' => 'MRK', 'Luc' => 'LUK', 'Jean' => 'JHN', 'Actes' => 'ACT',
            'Romains' => 'ROM', '1 Corinthiens' => '1CO', '2 Corinthiens' => '2CO',
            'Galates' => 'GAL', 'Éphésiens' => 'EPH', 'Philippiens' => 'PHP', 'Colossiens' => 'COL',
            '1 Thessaloniciens' => '1TH', '2 Thessaloniciens' => '2TH',
            '1 Timothée' => '1TI', '2 Timothée' => '2TI', 'Tite' => 'TIT', 'Philémon' => 'PHM',
            'Hébreux' => 'HEB', 'Jacques' => 'JAS', '1 Pierre' => '1PE', '2 Pierre' => '2PE',
            '1 Jean' => '1JN', '2 Jean' => '2JN', '3 Jean' => '3JN', 'Jude' => 'JUD',
            'Apocalypse' => 'REV',
        ];

        return $map[$livre] ?? null;
    }

    private function stripAccents(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        return str_replace(
            ['à', 'â', 'ä', 'æ', 'ç', 'é', 'è', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û', 'ü', 'ÿ'],
            ['a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'oe', 'u', 'u', 'u', 'y'],
            $value
        );
    }

    private function successResponse(Verset $verset, string $source): array
    {
        return [
            'success' => true,
            'texte' => $verset->texte,
            'reference' => $verset->reference,
            'livre' => $verset->livre,
            'chapitre' => $verset->chapitre,
            'verset' => $verset->verset,
            'version' => $verset->version ?? 'LSG',
            'source' => $source,
        ];
    }

    private function apiSuccess(string $texte, string $reference, string $livre, int $chapitre, int $verset, string $version, string $source): array
    {
        return [
            'success' => true,
            'texte' => $texte,
            'reference' => $reference,
            'livre' => $livre,
            'chapitre' => $chapitre,
            'verset' => $verset,
            'version' => $version,
            'source' => $source,
        ];
    }

    private function failure(?string $reference, ?string $livre, ?int $chapitre, int $verset, string $message): array
    {
        return [
            'success' => false,
            'texte' => null,
            'reference' => $reference,
            'livre' => $livre,
            'chapitre' => $chapitre,
            'verset' => $verset ?: null,
            'source' => 'none',
            'message' => $message,
        ];
    }
}
