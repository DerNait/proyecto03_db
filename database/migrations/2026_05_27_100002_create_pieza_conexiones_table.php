<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieza_conexiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pieza1_id')
                ->constrained('piezas')
                ->cascadeOnDelete();
            $table->foreignId('pieza2_id')
                ->constrained('piezas')
                ->cascadeOnDelete();
            $table->unsignedInteger('conexion_pieza1')
                ->comment('Número de conexión en pieza1 que se conecta');
            $table->unsignedInteger('conexion_pieza2')
                ->comment('Número de conexión en pieza2 que se conecta');
            $table->timestamps();

            // Previene registrar la misma conexión dos veces
            // pieza1_id siempre será el menor ID (normalizado en el controlador)
            $table->unique(
                ['pieza1_id', 'pieza2_id', 'conexion_pieza1', 'conexion_pieza2'],
                'pieza_conexion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieza_conexiones');
    }
};
