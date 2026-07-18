<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    public function __construct(
        private readonly TranslationService $translator
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'texte' => ['required', 'string', 'max:20000'],
            'langue' => ['required', 'string', 'max:10'],
            'source' => ['nullable', 'string', 'max:10'],
        ], [
            'texte.required' => 'Le texte est requis.',
            'langue.required' => 'La langue est requise.',
        ]);

        $texte = $validated['texte'];
        $langue = strtolower($validated['langue']);
        $source = strtolower($validated['source'] ?? 'fr');

        if (! array_key_exists($langue, TranslationService::LANGUAGES)) {
            return response()->json([
                'success' => false,
                'message' => 'Langue non supportée.',
            ], 422);
        }

        $traduction = $this->translator->translate($texte, $langue, $source);

        return response()->json([
            'success' => true,
            'traduction' => $traduction,
            'langue' => $langue,
            'texte_original' => $texte,
            'source' => $traduction === $texte && $langue !== $source ? 'fallback' : 'ok',
        ]);
    }

    public function languages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'langues' => TranslationService::LANGUAGES,
        ]);
    }
}
