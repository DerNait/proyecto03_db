<?php

namespace App\Http\Controllers;

use App\Models\Rompecabezas;
use App\Services\AlgoritmoService;
use Inertia\Inertia;

class AlgoritmoController extends Controller
{
    public function __construct(private AlgoritmoService $service) {}

    public function armar(Rompecabezas $rompecabezas)
    {
        $resultado = $this->service->armar($rompecabezas->id);

        return Inertia::render('Rompecabezas/Armar', [
            'rompecabezas' => $rompecabezas,
            'resultado'    => $resultado,
        ]);
    }
}
