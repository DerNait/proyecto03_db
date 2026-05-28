<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rompecabezas extends Model
{
    protected $table = 'rompecabezas';

    protected $fillable = [
        'nombre',
        'tipo',
        'tematica',
        'marca',
        'material',
        'num_piezas_total',
        'dificultad',
        'descripcion',
    ];

    public function piezas(): HasMany
    {
        return $this->hasMany(Pieza::class);
    }
}
