<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'avisos';

    protected $fillable = [
        'temporada_id',
        'titulo',
        'descripcion',
        'fecha',
        'foto',
    ];
}
