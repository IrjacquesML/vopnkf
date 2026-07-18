<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verset extends Model
{
    protected $fillable = [
        'reference',
        'livre',
        'chapitre',
        'verset',
        'texte',
        'version',
    ];
}
