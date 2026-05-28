<?php

namespace App\Http\Controllers;

use App\Models\PiezaConexion;
use App\Models\Rompecabezas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RompecabezasController extends Controller
{
    public function index()
    {
        $puzzles = Rompecabezas::withCount([
            'piezas',
            'piezas as piezas_disponibles_count' => fn ($q) => $q->where('disponible', true),
            'piezas as piezas_faltantes_count'   => fn ($q) => $q->where('disponible', false),
        ])->latest()->get();

        return Inertia::render('Rompecabezas/Index', [
            'puzzles' => $puzzles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Rompecabezas/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:255',
            'tipo'             => 'nullable|string|max:100',
            'tematica'         => 'nullable|string|max:100',
            'marca'            => 'nullable|string|max:100',
            'material'         => 'nullable|string|max:100',
            'num_piezas_total' => 'nullable|integer|min:1',
            'dificultad'       => 'nullable|in:facil,medio,dificil',
            'descripcion'      => 'nullable|string',
        ]);

        $puzzle = Rompecabezas::create($data);

        return redirect()
            ->route('rompecabezas.show', $puzzle)
            ->with('success', "Rompecabezas \"{$puzzle->nombre}\" creado correctamente.");
    }

    public function show(Rompecabezas $rompecabeza)
    {
        $rompecabeza->load(['piezas' => fn ($q) => $q->orderBy('etiqueta')]);

        // Agregar conteo de conexiones por pieza
        $piezasData = $rompecabeza->piezas->map(function ($pieza) {
            $conns = PiezaConexion::where('pieza1_id', $pieza->id)
                ->orWhere('pieza2_id', $pieza->id)
                ->count();

            return array_merge($pieza->toArray(), ['conexiones_count' => $conns]);
        })->values();

        // Cargar todas las conexiones del rompecabezas con datos de piezas
        $conexiones = PiezaConexion::whereHas('pieza1', fn ($q) => $q->where('rompecabezas_id', $rompecabeza->id))
            ->with(['pieza1', 'pieza2'])
            ->get()
            ->map(fn ($c) => [
                'id'              => $c->id,
                'pieza1'          => ['id' => $c->pieza1->id, 'etiqueta' => $c->pieza1->etiqueta],
                'pieza2'          => ['id' => $c->pieza2->id, 'etiqueta' => $c->pieza2->etiqueta],
                'conexion_pieza1' => $c->conexion_pieza1,
                'conexion_pieza2' => $c->conexion_pieza2,
            ])->values();

        // Serialize model without loaded piezas relation (piezas is sent separately)
        $rompecabezaArray = $rompecabeza->makeHidden(['piezas'])->toArray();

        $stats = [
            'total'       => $rompecabeza->piezas->count(),
            'disponibles' => $rompecabeza->piezas->where('disponible', true)->count(),
            'faltantes'   => $rompecabeza->piezas->where('disponible', false)->count(),
            'conexiones'  => $conexiones->count(),
        ];

        return Inertia::render('Rompecabezas/Show', [
            'rompecabezas' => $rompecabezaArray,
            'piezas'       => $piezasData,
            'conexiones'   => $conexiones,
            'stats'        => $stats,
        ]);
    }

    public function destroy(Rompecabezas $rompecabeza)
    {
        $nombre = $rompecabeza->nombre;
        $rompecabeza->delete();

        return redirect()
            ->route('rompecabezas.index')
            ->with('success', "Rompecabezas \"{$nombre}\" eliminado.");
    }
}
