<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pieza extends Model
{
    protected $fillable = [
        'rompecabezas_id',
        'etiqueta',
        'num_conexiones',
        'disponible',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'num_conexiones' => 'integer',
    ];

    public function rompecabezas(): BelongsTo
    {
        return $this->belongsTo(Rompecabezas::class);
    }

    public function conexionesComoPieza1(): HasMany
    {
        return $this->hasMany(PiezaConexion::class, 'pieza1_id');
    }

    public function conexionesComoPieza2(): HasMany
    {
        return $this->hasMany(PiezaConexion::class, 'pieza2_id');
    }
}
