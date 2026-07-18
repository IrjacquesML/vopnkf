<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecon extends Model
{
    protected $table = 'lecons';

    protected $fillable = [
        'categorie_id',
        'titre',
        'contenu',
        'ordre',
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('ordre');
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(ProgressionLecon::class);
    }
}
