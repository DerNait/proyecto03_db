<?php

namespace App\Http\Controllers;

use App\Models\Pieza;
use App\Models\PiezaConexion;
use Illuminate\Http\Request;

class ConexionController extends Controller
{
    /**
     * Registrar una conexión entre dos piezas (AJAX).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'pieza1_id'       => 'required|exists:piezas,id',
            'pieza2_id'       => 'required|exists:piezas,id|different:pieza1_id',
            'conexion_pieza1' => 'required|integer|min:1',
            'conexion_pieza2' => 'required|integer|min:1',
        ]);

        $pieza1 = Pieza::findOrFail($data['pieza1_id']);
        $pieza2 = Pieza::findOrFail($data['pieza2_id']);

        // Validar que pertenecen al mismo rompecabezas
        if ($pieza1->rompecabezas_id !== $pieza2->rompecabezas_id) {
            return response()->json([
                'error' => 'Las piezas deben pertenecer al mismo rompecabezas.',
            ], 422);
        }

        // Validar que los números de conexión son válidos para cada pieza
        if ($data['conexion_pieza1'] > $pieza1->num_conexiones) {
            return response()->json([
                'error' => "La pieza \"{$pieza1->etiqueta}\" solo tiene {$pieza1->num_conexiones} conexiones (recibido: {$data['conexion_pieza1']}).",
            ], 422);
        }

        if ($data['conexion_pieza2'] > $pieza2->num_conexiones) {
            return response()->json([
                'error' => "La pieza \"{$pieza2->etiqueta}\" solo tiene {$pieza2->num_conexiones} conexiones (recibido: {$data['conexion_pieza2']}).",
            ], 422);
        }

        // Normalizar: siempre pieza1_id < pieza2_id para respetar el UNIQUE constraint
        if ($data['pieza1_id'] > $data['pieza2_id']) {
            [$data['pieza1_id'], $data['pieza2_id']]         = [$data['pieza2_id'], $data['pieza1_id']];
            [$data['conexion_pieza1'], $data['conexion_pieza2']] = [$data['conexion_pieza2'], $data['conexion_pieza1']];
            // Re-asignar piezas normalizadas
            [$pieza1, $pieza2] = [$pieza2, $pieza1];
        }

        // Verificar que esa conexión específica no esté ya ocupada en cada pieza
        $connOcupada1 = PiezaConexion::where('pieza1_id', $data['pieza1_id'])
            ->where('conexion_pieza1', $data['conexion_pieza1'])
            ->exists()
            || PiezaConexion::where('pieza2_id', $data['pieza1_id'])
            ->where('conexion_pieza2', $data['conexion_pieza1'])
            ->exists();

        $connOcupada2 = PiezaConexion::where('pieza2_id', $data['pieza2_id'])
            ->where('conexion_pieza2', $data['conexion_pieza2'])
            ->exists()
            || PiezaConexion::where('pieza1_id', $data['pieza2_id'])
            ->where('conexion_pieza1', $data['conexion_pieza2'])
            ->exists();

        if ($connOcupada1) {
            return response()->json([
                'error' => "La conexión #{$data['conexion_pieza1']} de la pieza \"{$pieza1->etiqueta}\" ya está siendo usada.",
            ], 422);
        }

        if ($connOcupada2) {
            return response()->json([
                'error' => "La conexión #{$data['conexion_pieza2']} de la pieza \"{$pieza2->etiqueta}\" ya está siendo usada.",
            ], 422);
        }

        try {
            $conexion = PiezaConexion::create($data);
            $conexion->load(['pieza1', 'pieza2']);

            return response()->json([
                'conexion' => [
                    'id'              => $conexion->id,
                    'pieza1'          => ['id' => $conexion->pieza1->id, 'etiqueta' => $conexion->pieza1->etiqueta],
                    'pieza2'          => ['id' => $conexion->pieza2->id, 'etiqueta' => $conexion->pieza2->etiqueta],
                    'conexion_pieza1' => $conexion->conexion_pieza1,
                    'conexion_pieza2' => $conexion->conexion_pieza2,
                ],
                'message' => "Conexión registrada: {$pieza1->etiqueta}#{$data['conexion_pieza1']} ↔ {$pieza2->etiqueta}#{$data['conexion_pieza2']}",
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Esta conexión ya existe.',
            ], 409);
        }
    }

    /**
     * Registrar una conexión con numeración de conector auto-asignada (AJAX).
     * Usado por el editor visual de aristas: solo requiere las dos piezas.
     */
    public function autoStore(Request $request)
    {
        $data = $request->validate([
            'pieza1_id' => 'required|exists:piezas,id',
            'pieza2_id' => 'required|exists:piezas,id|different:pieza1_id',
        ]);

        $pieza1 = Pieza::findOrFail($data['pieza1_id']);
        $pieza2 = Pieza::findOrFail($data['pieza2_id']);

        // Validar que pertenecen al mismo rompecabezas
        if ($pieza1->rompecabezas_id !== $pieza2->rompecabezas_id) {
            return response()->json([
                'error' => 'Las piezas deben pertenecer al mismo rompecabezas.',
            ], 422);
        }

        // Auto-asignar número de conector: total de conexiones existentes + 1
        $n1 = PiezaConexion::where('pieza1_id', $pieza1->id)->count()
            + PiezaConexion::where('pieza2_id', $pieza1->id)->count() + 1;
        $n2 = PiezaConexion::where('pieza1_id', $pieza2->id)->count()
            + PiezaConexion::where('pieza2_id', $pieza2->id)->count() + 1;

        // Expandir num_conexiones si el nuevo conector supera el valor actual
        if ($n1 > $pieza1->num_conexiones) {
            $pieza1->update(['num_conexiones' => $n1]);
        }
        if ($n2 > $pieza2->num_conexiones) {
            $pieza2->update(['num_conexiones' => $n2]);
        }

        // Normalizar: siempre pieza1_id < pieza2_id
        [$pid1, $pid2, $c1, $c2] = $data['pieza1_id'] < $data['pieza2_id']
            ? [$data['pieza1_id'], $data['pieza2_id'], $n1, $n2]
            : [$data['pieza2_id'], $data['pieza1_id'], $n2, $n1];

        try {
            $conexion = PiezaConexion::create([
                'pieza1_id'       => $pid1,
                'pieza2_id'       => $pid2,
                'conexion_pieza1' => $c1,
                'conexion_pieza2' => $c2,
            ]);
            $conexion->load(['pieza1', 'pieza2']);

            return response()->json([
                'conexion' => [
                    'id'              => $conexion->id,
                    'pieza1'          => ['id' => $conexion->pieza1->id, 'etiqueta' => $conexion->pieza1->etiqueta],
                    'pieza2'          => ['id' => $conexion->pieza2->id, 'etiqueta' => $conexion->pieza2->etiqueta],
                    'conexion_pieza1' => $conexion->conexion_pieza1,
                    'conexion_pieza2' => $conexion->conexion_pieza2,
                ],
                'pieza1_updated' => [
                    'id'              => $pieza1->id,
                    'num_conexiones'  => $pieza1->fresh()->num_conexiones,
                ],
                'pieza2_updated' => [
                    'id'              => $pieza2->id,
                    'num_conexiones'  => $pieza2->fresh()->num_conexiones,
                ],
                'message' => "Conexión auto: {$pieza1->etiqueta}#{$n1} ↔ {$pieza2->etiqueta}#{$n2}",
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Esta combinación de conectores ya existe.',
            ], 409);
        }
    }

    /**
     * Eliminar una conexión (AJAX).
     */
    public function destroy(PiezaConexion $conexion)
    {
        $info = "{$conexion->pieza1->etiqueta}#{$conexion->conexion_pieza1} ↔ {$conexion->pieza2->etiqueta}#{$conexion->conexion_pieza2}";
        $conexion->delete();

        return response()->json([
            'message' => "Conexión {$info} eliminada.",
        ]);
    }
}
