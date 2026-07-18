<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'lecon_id',
        'question',
        'ordre',
    ];

    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(OptionReponse::class)->orderBy('ordre');
    }
}
