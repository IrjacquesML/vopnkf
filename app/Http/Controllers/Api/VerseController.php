<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BibleVerseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerseController extends Controller
{
    public function __construct(
        private BibleVerseService $bibleVerseService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $reference = $request->query('reference');
        $livre = $request->query('livre');
        $chapitre = $request->query('chapitre') ? (int) $request->query('chapitre') : null;
        $versetParam = $request->query('verset');

        // Accepte "16" ou "16-17"
        $verset = null;
        if (is_string($versetParam) && preg_match('/^\d+(-\d+)?$/', trim($versetParam))) {
            $verset = trim($versetParam);
        } elseif (is_numeric($versetParam)) {
            $verset = (int) $versetParam;
        }

        if (! $reference && $livre && $chapitre && $versetParam) {
            $reference = trim($livre).' '.$chapitre.':'.$versetParam;
        }

        if (empty($reference) && (empty($livre) || ! $chapitre || empty($versetParam))) {
            return response()->json([
                'success' => false,
                'message' => 'Référence manquante.',
            ], 400);
        }

        $result = $this->bibleVerseService->getVerse($reference, $livre, $chapitre, $verset);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 404);
    }
}
