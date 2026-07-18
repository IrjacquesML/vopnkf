<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandePriere extends Model
{
    protected $table = 'demandes_priere';

    protected $fillable = [
        'user_id',
        'sujet',
        'message',
        'est_anonyme',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'est_anonyme' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
