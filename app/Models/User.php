<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'nom',
    'prenom',
    'email',
    'password',
    'role',
    'pays',
    'province',
    'ville',
    'adresse_complete',
    'telephone',
    'photo_profil',
    'langue_preferee',
    'derniere_connexion',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'derniere_connexion' => 'datetime',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(ProgressionLecon::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(ReponseUtilisateur::class);
    }

    public function demandesPriere(): HasMany
    {
        return $this->hasMany(DemandePriere::class);
    }
}
