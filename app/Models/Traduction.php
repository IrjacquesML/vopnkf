<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traduction extends Model
{
    protected $fillable = [
        'type_contenu',
        'contenu_id',
        'cle_texte',
        'texte_original',
        'langue',
        'texte_traduit',
    ];
}
