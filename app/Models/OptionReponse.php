<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionReponse extends Model
{
    protected $table = 'options_reponse';

    protected $fillable = [
        'question_id',
        'texte_option',
        'est_correcte',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'est_correcte' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
