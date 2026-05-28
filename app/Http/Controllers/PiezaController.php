<?php

namespace App\Http\Controllers;

use App\Models\Pieza;
use App\Models\Rompecabezas;
use Illuminate\Http\Request;

class PiezaController extends Controller
{
    /**
     * Agregar una pieza al rompecabezas (AJAX - quick add).
     */
    public function store(Request $request, Rompecabezas $rompecabezas)
    {
        $data = $request->validate([
            'etiqueta'       => 'required|string|max:50',
            'num_conexiones' => 'required|integer|min:1|max:100',
        ]);

        // Verificar que la etiqueta no esté duplicada en el mismo rompecabezas
        $existe = $rompecabezas->piezas()
            ->where('etiqueta', $data['etiqueta'])
            ->exists();

        if ($existe) {
            return response()->json([
                'error' => "Ya existe una pieza con la etiqueta \"{$data['etiqueta']}\" en este rompecabezas.",
            ], 422);
        }

        $pieza = $rompecabezas->piezas()->create([
            'etiqueta'       => $data['etiqueta'],
            'num_conexiones' => $data['num_conexiones'],
            'disponible'     => true,
        ]);

        return response()->json([
            'pieza'   => $pieza,
            'message' => "Pieza \"{$pieza->etiqueta}\" agregada.",
        ], 201);
    }

    /**
     * Cambiar disponibilidad de la pieza (AJAX toggle).
     */
    public function toggle(Pieza $pieza)
    {
        $pieza->update(['disponible' => ! $pieza->disponible]);

        return response()->json([
            'disponible' => $pieza->disponible,
            'message'    => $pieza->disponible
                ? "Pieza \"{$pieza->etiqueta}\" marcada como disponible."
                : "Pieza \"{$pieza->etiqueta}\" marcada como faltante.",
        ]);
    }

    /**
     * Eliminar una pieza y sus conexiones (AJAX).
     */
    public function destroy(Pieza $pieza)
    {
        $etiqueta = $pieza->etiqueta;
        $pieza->delete(); // cascades to pieza_conexiones

        return response()->json([
            'message' => "Pieza \"{$etiqueta}\" eliminada.",
        ]);
    }
}
