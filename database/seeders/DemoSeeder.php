<?php

namespace Database\Seeders;

use App\Models\Pieza;
use App\Models\PiezaConexion;
use App\Models\Rompecabezas;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Rompecabezas 1: cadena lineal de 4 piezas
        $r1 = Rompecabezas::create([
            'nombre'           => 'Rompecabezas Demo (4 piezas)',
            'marca'            => 'UVG Demo',
            'tematica'         => 'Universitario',
            'material'         => 'Cartón',
            'tipo'             => 'Tradicional',
            'dificultad'       => 'facil',
            'num_piezas_total' => 4,
            'descripcion'      => 'Rompecabezas de demostración para el proyecto BD2.',
        ]);

        $p1 = $r1->piezas()->create(['etiqueta' => 'P1', 'num_conexiones' => 4, 'disponible' => true]);
        $p2 = $r1->piezas()->create(['etiqueta' => 'P2', 'num_conexiones' => 3, 'disponible' => true]);
        $p3 = $r1->piezas()->create(['etiqueta' => 'P3', 'num_conexiones' => 2, 'disponible' => true]);
        $p4 = $r1->piezas()->create(['etiqueta' => 'P4', 'num_conexiones' => 3, 'disponible' => true]);

        // P1-c2 ↔ P2-c3
        PiezaConexion::create(['pieza1_id' => $p1->id, 'pieza2_id' => $p2->id, 'conexion_pieza1' => 2, 'conexion_pieza2' => 3]);
        // P2-c1 ↔ P3-c2
        PiezaConexion::create(['pieza1_id' => $p2->id, 'pieza2_id' => $p3->id, 'conexion_pieza1' => 1, 'conexion_pieza2' => 2]);
        // P3-c1 ↔ P4-c2
        PiezaConexion::create(['pieza1_id' => $p3->id, 'pieza2_id' => $p4->id, 'conexion_pieza1' => 1, 'conexion_pieza2' => 2]);

        $this->command->info("Rompecabezas '{$r1->nombre}' creado con ID {$r1->id}");
        $this->command->info("Ejecuta: http://localhost:8500/rompecabezas/{$r1->id}/armar");

        // Rompecabezas 2: con piezas faltantes (P3 faltante → 2 islas)
        $r2 = Rompecabezas::create([
            'nombre'           => 'Demo con Piezas Faltantes',
            'marca'            => 'UVG Demo',
            'tematica'         => 'Algoritmo de islas',
            'dificultad'       => 'medio',
            'num_piezas_total' => 5,
        ]);

        $q1 = $r2->piezas()->create(['etiqueta' => 'Q1', 'num_conexiones' => 2, 'disponible' => true]);
        $q2 = $r2->piezas()->create(['etiqueta' => 'Q2', 'num_conexiones' => 2, 'disponible' => true]);
        $q3 = $r2->piezas()->create(['etiqueta' => 'Q3', 'num_conexiones' => 2, 'disponible' => false]); // FALTANTE
        $q4 = $r2->piezas()->create(['etiqueta' => 'Q4', 'num_conexiones' => 2, 'disponible' => true]);
        $q5 = $r2->piezas()->create(['etiqueta' => 'Q5', 'num_conexiones' => 2, 'disponible' => true]);

        // Q1-c1 ↔ Q2-c1
        PiezaConexion::create(['pieza1_id' => $q1->id, 'pieza2_id' => $q2->id, 'conexion_pieza1' => 1, 'conexion_pieza2' => 1]);
        // Q2-c2 ↔ Q3-c1 (Q3 faltante, pero la conexión existe en BD)
        PiezaConexion::create(['pieza1_id' => $q2->id, 'pieza2_id' => $q3->id, 'conexion_pieza1' => 2, 'conexion_pieza2' => 1]);
        // Q3-c2 ↔ Q4-c1 (Q3 faltante)
        PiezaConexion::create(['pieza1_id' => $q3->id, 'pieza2_id' => $q4->id, 'conexion_pieza1' => 2, 'conexion_pieza2' => 1]);
        // Q4-c2 ↔ Q5-c1
        PiezaConexion::create(['pieza1_id' => $q4->id, 'pieza2_id' => $q5->id, 'conexion_pieza1' => 2, 'conexion_pieza2' => 1]);

        $this->command->info("Rompecabezas '{$r2->nombre}' creado con ID {$r2->id} (Q3 es faltante → 2 islas)");
    }
}
