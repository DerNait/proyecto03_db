<?php

namespace App\Services;

use App\Models\Pieza;
use App\Models\PiezaConexion;

class AlgoritmoService
{
    /**
     * Ejecuta el algoritmo BFS para armar el rompecabezas.
     * Maneja piezas faltantes formando "islas" independientes.
     *
     * @param  int  $rompecabezasId
     * @param  int|null  $piezaInicialId
     * @return array{pasos: array, islas: int, total_disponibles: int, total_general: int, porcentaje: float, piezas_sin_conexion: array}
     */
    public function armar(int $rompecabezasId, ?int $piezaInicialId = null): array
    {
        // 1. Cargar TODAS las piezas del rompecabezas (para estadísticas)
        $todasLasPiezas = Pieza::where('rompecabezas_id', $rompecabezasId)->get();
        $totalGeneral   = $todasLasPiezas->count();

        // 2. Solo piezas disponibles participan en el algoritmo
        $piezas = $todasLasPiezas->where('disponible', true)->keyBy('id');
        $totalDisponibles = $piezas->count();

        if ($piezas->isEmpty()) {
            return [
                'pasos'               => [],
                'islas'               => 0,
                'total_disponibles'   => 0,
                'total_general'       => $totalGeneral,
                'porcentaje'          => 0.0,
                'piezas_sin_conexion' => [],
                'mensaje'             => 'No hay piezas disponibles para armar el rompecabezas.',
            ];
        }

        // 3. Cargar conexiones entre piezas disponibles
        $ids = $piezas->keys()->toArray();
        $conexiones = PiezaConexion::whereIn('pieza1_id', $ids)
            ->whereIn('pieza2_id', $ids)
            ->get();

        // 4. Construir lista de adyacencia bidireccional
        $adjacency = [];
        foreach ($ids as $id) {
            $adjacency[$id] = [];
        }
        foreach ($conexiones as $c) {
            $adjacency[$c->pieza1_id][] = [
                'neighbor'      => $c->pieza2_id,
                'conn_self'     => $c->conexion_pieza1,
                'conn_neighbor' => $c->conexion_pieza2,
                'conexion_id'   => $c->id,
            ];
            $adjacency[$c->pieza2_id][] = [
                'neighbor'      => $c->pieza1_id,
                'conn_self'     => $c->conexion_pieza2,
                'conn_neighbor' => $c->conexion_pieza1,
                'conexion_id'   => $c->id,
            ];
        }

        // 5. BFS multi-isla
        $visited   = [];
        $pasos     = [];
        $islas     = 0;
        $stepNum   = 0;
        $remaining = $ids;

        if ($piezaInicialId !== null && $piezas->has($piezaInicialId)) {
            $remaining = array_values(array_filter(
                $remaining,
                fn ($id) => $id !== $piezaInicialId
            ));
            array_unshift($remaining, $piezaInicialId);
        } else {
            // Shuffle para aleatoriedad real cuando el usuario no elige pieza inicial.
            shuffle($remaining);
        }

        while (! empty($remaining)) {
            // Elegir pieza inicial de esta isla
            $startId    = array_shift($remaining);
            $startPieza = $piezas[$startId];
            $islas++;
            $isIslandStart = true;

            $visited[$startId] = true;
            $queue = [$startId];

            while (! empty($queue)) {
                $currentId    = array_shift($queue);
                $currentPieza = $piezas[$currentId];

                // Generar paso de colocación de la pieza inicial de la isla
                if ($isIslandStart) {
                    if ($islas === 1) {
                        // Primera pieza absoluta del rompecabezas
                        $pasos[] = [
                            'numero'             => 0,
                            'tipo'               => 'inicio',
                            'isla'               => 1,
                            'etiqueta_principal' => $currentPieza->etiqueta,
                            'instrucciones'      => $this->instruccionInicio($currentPieza),
                        ];
                    } else {
                        // Primera pieza de una nueva isla
                        $stepNum++;
                        $pasos[] = [
                            'numero'             => $stepNum,
                            'tipo'               => 'nueva_isla',
                            'isla'               => $islas,
                            'etiqueta_principal' => $currentPieza->etiqueta,
                            'instrucciones'      => $this->instruccionNuevaIsla($currentPieza, $islas),
                        ];
                    }
                    $isIslandStart = false;
                }

                // Procesar vecinos no visitados
                foreach ($adjacency[$currentId] as $edge) {
                    $neighborId = $edge['neighbor'];
                    if (isset($visited[$neighborId])) {
                        continue;
                    }

                    $visited[$neighborId] = true;
                    $neighborPieza        = $piezas[$neighborId];
                    $stepNum++;

                    $pasos[] = [
                        'numero'             => $stepNum,
                        'tipo'               => 'conexion',
                        'isla'               => $islas,
                        'etiqueta_principal' => $neighborPieza->etiqueta,
                        'etiqueta_base'      => $currentPieza->etiqueta,
                        'conexion_base'      => $edge['conn_self'],
                        'conexion_nueva'     => $edge['conn_neighbor'],
                        'instrucciones'      => $this->instruccionConexion(
                            $currentPieza,
                            $edge['conn_self'],
                            $neighborPieza,
                            $edge['conn_neighbor'],
                            $stepNum
                        ),
                    ];

                    $queue[]   = $neighborId;
                    $remaining = array_values(array_filter(
                        $remaining,
                        fn ($id) => $id !== $neighborId
                    ));
                }
            }
        }

        // 6. Detectar piezas sin ninguna conexión
        $piezasSinConexion = [];
        foreach ($piezas as $id => $pieza) {
            if (empty($adjacency[$id])) {
                $piezasSinConexion[] = $pieza->etiqueta;
            }
        }

        // 7. Calcular porcentaje de completado
        $piezasColocadas = count(array_unique(array_column($pasos, 'etiqueta_principal')));
        $porcentaje = $totalDisponibles > 0
            ? round(($piezasColocadas / $totalDisponibles) * 100, 1)
            : 0.0;

        return [
            'pasos'               => $pasos,
            'islas'               => $islas,
            'total_disponibles'   => $totalDisponibles,
            'total_general'       => $totalGeneral,
            'porcentaje'          => $porcentaje,
            'piezas_sin_conexion' => $piezasSinConexion,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Generadores de instrucciones con máxima claridad anti-ambigüedad
    // ─────────────────────────────────────────────────────────────────────────

    private function instruccionInicio(Pieza $pieza): string
    {
        $e = $pieza->etiqueta;

        return implode("\n", [
            "[PUZZLE] PIEZA INICIAL",
            "",
            "Toma la pieza etiquetada [{$e}].",
            "",
            "[WARN] ORIENTACIÓN OBLIGATORIA — MUY IMPORTANTE:",
            "    El maskin tape con la etiqueta \"{$e}\" debe mirar hacia ARRIBA",
            "    (hacia el techo) en TODO momento. NUNCA des vuelta la pieza.",
            "    Si la pieza quedara al revés, aunque encaje físicamente,",
            "    el rompecabezas quedará mal armado.",
            "",
            "[CHECK] Coloca la pieza [{$e}] sobre la mesa con la etiqueta visible.",
            "    Esta es tu PIEZA DE REFERENCIA. El resto se conectará a ella.",
        ]);
    }

    private function instruccionNuevaIsla(Pieza $pieza, int $islaNum): string
    {
        $e = $pieza->etiqueta;

        return implode("\n", [
            "[ISLAND] NUEVA ISLA #{$islaNum}",
            "",
            "No hay más piezas que se puedan conectar al grupo anterior.",
            "(Esto ocurre porque hay piezas faltantes o el grupo está completo.)",
            "",
            "Toma la pieza etiquetada [{$e}].",
            "",
            "[WARN] ORIENTACIÓN: El maskin tape de [{$e}] debe mirar hacia ARRIBA.",
            "",
            "[CHECK] Coloca esta pieza A UNOS 15 cm separada del último grupo armado.",
            "    Comenzarás a construir desde aquí un grupo nuevo (isla #{$islaNum}).",
        ]);
    }

    private function instruccionConexion(
        Pieza $base,
        int   $connBase,
        Pieza $nueva,
        int   $connNueva,
        int   $stepNum
    ): string {
        $b  = $base->etiqueta;
        $n  = $nueva->etiqueta;
        $cb = $connBase;
        $cn = $connNueva;

        return implode("\n", [
            "[LINK] PASO {$stepNum}: Conectar [{$n}] a [{$b}]",
            "",
            "1. Toma la pieza etiquetada [{$n}].",
            "",
            "2. [WARN] ORIENTACIÓN — antes de acercarla:",
            "   Verifica que el maskin tape de [{$n}] apunte hacia ARRIBA.",
            "   Ambas piezas ({$b} y {$n}) deben tener sus etiquetas mirando",
            "   hacia el techo al mismo tiempo.",
            "",
            "3. En la pieza [{$b}] (ya en la mesa), busca en la orilla de la pieza",
            "   el maskin tape que tenga escrito el número {$cb}.",
            "",
            "4. En la pieza [{$n}] (en tu mano), busca en la orilla de la pieza",
            "   el maskin tape que tenga escrito el número {$cn}.",
            "",
            "5. Acerca [{$n}] a [{$b}] de modo que la cara frontal (donde está escrito el número)",
            "   de ambos maskin queden pegadas entre sí:",
            "   [ARROW]  maskin #{$cb} de [{$b}]  cara a cara con  maskin #{$cn} de [{$n}]",
            "",
            "6. [CHECK] Presiona suavemente hasta que queden unidos.",
            "   Si no ceden, revisa que las etiquetas [{$b}] y [{$n}] de AMBAS piezas",
            "   sigan apuntando hacia arriba. Y que los maskin de las caras frontales", 
            "   de las conexiones estén correctamente alineados.",
        ]);
    }
}
