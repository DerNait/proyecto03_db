<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piezas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rompecabezas_id')
                ->constrained('rompecabezas')
                ->cascadeOnDelete();
            $table->string('etiqueta')->comment('Texto del maskin tape, ej: P1, A3');
            $table->unsignedInteger('num_conexiones')->default(1)
                ->comment('Cantidad de conectores/hoyos numerados del 1..N');
            $table->boolean('disponible')->default(true)
                ->comment('false = pieza retirada del rompecabezas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piezas');
    }
};
