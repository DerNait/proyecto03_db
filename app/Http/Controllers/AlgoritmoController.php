<?php

namespace App\Http\Controllers;

use App\Models\Rompecabezas;
use App\Services\AlgoritmoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AlgoritmoController extends Controller
{
    public function __construct(private AlgoritmoService $service) {}

    public function armar(Request $request, Rompecabezas $rompecabezas)
    {
        $data = $request->validate([
            'pieza_inicial_id' => [
                'nullable',
                'integer',
                Rule::exists('piezas', 'id')
                    ->where('rompecabezas_id', $rompecabezas->id)
                    ->where('disponible', true),
            ],
        ]);

        $piezaInicialId = $data['pieza_inicial_id'] ?? null;
        $resultado = $this->service->armar($rompecabezas->id, $piezaInicialId);

        return Inertia::render('Rompecabezas/Armar', [
            'rompecabezas'    => $rompecabezas,
            'resultado'       => $resultado,
            'piezasDisponibles' => $rompecabezas->piezas()
                ->where('disponible', true)
                ->orderBy('etiqueta')
                ->get(['id', 'etiqueta']),
            'piezaInicialId' => $piezaInicialId,
        ]);
    }
}
