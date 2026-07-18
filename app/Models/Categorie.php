<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'ordre',
    ];

    public function lecons(): HasMany
    {
        return $this->hasMany(Lecon::class)->orderBy('ordre');
    }
}
