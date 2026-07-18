<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReponseUtilisateur extends Model
{
    protected $table = 'reponses_utilisateurs';

    protected $fillable = [
        'user_id',
        'question_id',
        'option_id',
        'lecon_id',
        'est_correcte',
        'date_reponse',
    ];

    protected function casts(): array
    {
        return [
            'est_correcte' => 'boolean',
            'date_reponse' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(OptionReponse::class, 'option_id');
    }

    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class);
    }
}
