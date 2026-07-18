<?php

namespace App\Services;

use App\Models\Traduction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TranslationService
{
    /** @var array<string, string> */
    public const LANGUAGES = [
        'fr' => 'Français',
        'en' => 'English',
        'es' => 'Español',
        'pt' => 'Português',
        'sw' => 'Kiswahili',
        'ln' => 'Lingala',
        'kg' => 'Kikongo',
        'ar' => 'العربية',
        'zh' => '中文',
        'de' => 'Deutsch',
        'it' => 'Italiano',
        'ru' => 'Русский',
    ];

    public function translate(string $texte, string $langueCible, string $langueSource = 'fr'): string
    {
        $texte = trim($texte);
        $langueCible = strtolower(trim($langueCible));
        $langueSource = strtolower(trim($langueSource));

        if ($texte === '' || $langueCible === $langueSource) {
            return $texte;
        }

        if (! array_key_exists($langueCible, self::LANGUAGES)) {
            return $texte;
        }

        $cle = hash('sha256', $langueSource.'|'.$langueCible.'|'.$texte);

        $cached = Traduction::query()
            ->where('cle_texte', $cle)
            ->where('langue', $langueCible)
            ->value('texte_traduit');

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $translated = $this->translateViaGoogle($texte, $langueCible, $langueSource);

        if ($translated === null || $translated === '') {
            return $texte;
        }

        Traduction::query()->updateOrCreate(
            [
                'cle_texte' => $cle,
                'langue' => $langueCible,
            ],
            [
                'type_contenu' => 'interface',
                'contenu_id' => null,
                'texte_original' => Str::limit($texte, 65000, ''),
                'texte_traduit' => $translated,
            ]
        );

        return $translated;
    }

    private function translateViaGoogle(string $texte, string $langueCible, string $langueSource): ?string
    {
        // Découper les textes longs pour rester sous la limite de l'API
        $chunks = $this->chunkText($texte, 4200);
        $parts = [];

        foreach ($chunks as $chunk) {
            $translatedChunk = $this->requestGoogleChunk($chunk, $langueCible, $langueSource);

            if ($translatedChunk === null) {
                return null;
            }

            $parts[] = $translatedChunk;
        }

        return implode(' ', $parts);
    }

    private function requestGoogleChunk(string $texte, string $langueCible, string $langueSource): ?string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; VOP-Translate/1.0)',
                ])
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => $langueSource,
                    'tl' => $langueCible,
                    'dt' => 't',
                    'q' => $texte,
                ]);

            if (! $response->successful()) {
                Log::warning('Traduction Google HTTP '.$response->status());

                return null;
            }

            $payload = $response->json();

            if (! is_array($payload) || ! isset($payload[0]) || ! is_array($payload[0])) {
                return null;
            }

            $out = '';
            foreach ($payload[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $out .= $segment[0];
                }
            }

            return $out !== '' ? $out : null;
        } catch (\Throwable $e) {
            Log::warning('Traduction échouée: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @return list<string>
     */
    private function chunkText(string $texte, int $maxLen): array
    {
        if (mb_strlen($texte) <= $maxLen) {
            return [$texte];
        }

        $chunks = [];
        $paragraphs = preg_split('/(\n+)/u', $texte, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$texte];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            if (mb_strlen($buffer.$paragraph) <= $maxLen) {
                $buffer .= $paragraph;
                continue;
            }

            if ($buffer !== '') {
                $chunks[] = $buffer;
                $buffer = '';
            }

            if (mb_strlen($paragraph) <= $maxLen) {
                $buffer = $paragraph;
                continue;
            }

            // Découper encore par phrases / longueur fixe
            $offset = 0;
            $length = mb_strlen($paragraph);
            while ($offset < $length) {
                $chunks[] = mb_substr($paragraph, $offset, $maxLen);
                $offset += $maxLen;
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return $chunks ?: [$texte];
    }
}
