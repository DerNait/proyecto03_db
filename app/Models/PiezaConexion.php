<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiezaConexion extends Model
{
    protected $table = 'pieza_conexiones';

    protected $fillable = [
        'pieza1_id',
        'pieza2_id',
        'conexion_pieza1',
        'conexion_pieza2',
    ];

    protected $casts = [
        'conexion_pieza1' => 'integer',
        'conexion_pieza2' => 'integer',
    ];

    public function pieza1(): BelongsTo
    {
        return $this->belongsTo(Pieza::class, 'pieza1_id');
    }

    public function pieza2(): BelongsTo
    {
        return $this->belongsTo(Pieza::class, 'pieza2_id');
    }
}
