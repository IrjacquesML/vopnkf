<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressionLecon extends Model
{
    protected $table = 'progression_lecons';

    protected $fillable = [
        'user_id',
        'lecon_id',
        'statut',
        'score',
        'date_debut',
        'date_completion',
        'date_fin',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'date_debut' => 'datetime',
            'date_completion' => 'datetime',
            'date_fin' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class);
    }
}
