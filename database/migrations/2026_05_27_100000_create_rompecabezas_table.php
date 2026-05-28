<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rompecabezas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo')->nullable()->comment('cartón, madera, plástico...');
            $table->string('tematica')->nullable()->comment('animales, paisaje, abstracto...');
            $table->string('marca')->nullable();
            $table->string('material')->nullable();
            $table->unsignedInteger('num_piezas_total')->nullable()->comment('Total declarado en la caja');
            $table->enum('dificultad', ['facil', 'medio', 'dificil'])->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rompecabezas');
    }
};
