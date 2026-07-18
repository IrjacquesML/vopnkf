<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['cle', 'valeur'])]
class Parametre extends Model
{
    protected $table = 'parametres';

    public static function get(string $cle, mixed $default = null): mixed
    {
        $valeur = Cache::remember("parametre.{$cle}", 3600, function () use ($cle) {
            return static::query()->where('cle', $cle)->value('valeur');
        });

        return $valeur ?? $default;
    }

    public static function set(string $cle, mixed $valeur): void
    {
        static::query()->updateOrCreate(
            ['cle' => $cle],
            ['valeur' => $valeur]
        );

        Cache::forget("parametre.{$cle}");
    }

    /**
     * @return array{role: string, nom: string}
     */
    public static function defaultSignatories(): array
    {
        return [
            [
                'role' => "Le Président de l'Association du Nord-Kivu",
                'nom' => 'Pasteur K. Kirindera Makeo',
            ],
            [
                'role' => "Le Directeur de la Voix de l'Espérance",
                'nom' => 'Pasteur K. Karasaba Sophonie',
            ],
            [
                'role' => "Le Directeur de l'Évangélisation",
                'nom' => 'Pasteur K. Kasanga Celestin',
            ],
        ];
    }

    /**
     * @return array<int, array{role: string, nom: string}>
     */
    public static function certificateSignatories(): array
    {
        $defaults = static::defaultSignatories();
        $stored = static::get('certificat_signataires');

        if (! is_string($stored) || $stored === '') {
            return $defaults;
        }

        $decoded = json_decode($stored, true);

        if (! is_array($decoded) || count($decoded) === 0) {
            return $defaults;
        }

        $signatories = [];

        foreach (array_slice($decoded, 0, 3) as $index => $item) {
            $signatories[] = [
                'role' => trim((string) ($item['role'] ?? $defaults[$index]['role'] ?? '')),
                'nom' => trim((string) ($item['nom'] ?? $defaults[$index]['nom'] ?? '')),
            ];
        }

        while (count($signatories) < 3) {
            $signatories[] = $defaults[count($signatories)];
        }

        return $signatories;
    }

    /**
     * @param  array<int, array{role?: string, nom?: string}>  $signatories
     */
    public static function saveCertificateSignatories(array $signatories): void
    {
        $defaults = static::defaultSignatories();
        $normalized = [];

        foreach (array_slice(array_values($signatories), 0, 3) as $index => $item) {
            $normalized[] = [
                'role' => trim((string) ($item['role'] ?? $defaults[$index]['role'] ?? '')),
                'nom' => trim((string) ($item['nom'] ?? $defaults[$index]['nom'] ?? '')),
            ];
        }

        while (count($normalized) < 3) {
            $normalized[] = $defaults[count($normalized)];
        }

        static::set('certificat_signataires', json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }
}
