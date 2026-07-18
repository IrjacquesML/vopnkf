<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use App\Models\Verset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'admin']),
        ];
    }

    public function bibleApi(): View
    {
        $apiKey = config('bible.api_key');

        return view('admin.settings.bible', [
            'apiKeySet' => ! empty($apiKey),
            'apiUrl' => config('bible.api_url'),
            'lsgId' => config('bible.lsg_id'),
            'cacheDays' => config('bible.cache_days'),
            'versetsCacheCount' => Verset::count(),
        ]);
    }

    public function certificate(): View
    {
        return view('admin.settings.certificate', [
            'signatories' => Parametre::certificateSignatories(),
        ]);
    }

    public function updateCertificate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signatories' => ['required', 'array', 'size:3'],
            'signatories.*.role' => ['required', 'string', 'max:150'],
            'signatories.*.nom' => ['required', 'string', 'max:120'],
        ], [
            'signatories.*.role.required' => 'Le titre / rôle du signataire est obligatoire.',
            'signatories.*.nom.required' => 'Le nom du signataire est obligatoire.',
        ]);

        Parametre::saveCertificateSignatories($validated['signatories']);

        return redirect()
            ->route('admin.settings.certificate')
            ->with('success', 'Les signataires du certificat ont été enregistrés.');
    }
}
