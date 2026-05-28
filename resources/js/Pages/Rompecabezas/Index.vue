<template>
    <AppLayout>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 fw-bold">
                    <i class="fas fa-puzzle-piece text-danger me-2"></i>
                    Rompecabezas
                </h1>
                <p class="text-muted small mb-0">Gestiona y arma tus rompecabezas</p>
            </div>
            <Link :href="route('rompecabezas.create')" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nuevo Rompecabezas
            </Link>
        </div>

        <!-- Lista vacía -->
        <div v-if="puzzles.length === 0" class="text-center py-5">
            <i class="fas fa-puzzle-piece fa-4x text-muted mb-3 d-block"></i>
            <h4 class="text-muted">No hay rompecabezas registrados</h4>
            <p class="text-muted">Comienza creando tu primer rompecabezas.</p>
            <Link :href="route('rompecabezas.create')" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-1"></i>
                Crear primer rompecabezas
            </Link>
        </div>

        <!-- Grid de rompecabezas -->
        <div v-else class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <div v-for="puzzle in puzzles" :key="puzzle.id" class="col">
                <div class="card h-100 shadow-sm stat-card">
                    <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between">
                        <span class="fw-bold">
                            <i class="fas fa-puzzle-piece text-warning me-2"></i>
                            {{ puzzle.nombre }}
                        </span>
                        <span v-if="puzzle.dificultad" :class="dificultadBadge(puzzle.dificultad)" class="badge">
                            {{ puzzle.dificultad }}
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold text-primary fs-4">{{ puzzle.piezas_count }}</div>
                                    <div class="small text-muted">Total</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold text-success fs-4">{{ puzzle.piezas_disponibles_count }}</div>
                                    <div class="small text-muted">Disponibles</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold text-danger fs-4">{{ puzzle.piezas_faltantes_count }}</div>
                                    <div class="small text-muted">Faltantes</div>
                                </div>
                            </div>
                        </div>

                        <ul class="list-unstyled small text-muted mb-0">
                            <li v-if="puzzle.marca">
                                <i class="fas fa-tag me-1"></i>
                                <strong>Marca:</strong> {{ puzzle.marca }}
                            </li>
                            <li v-if="puzzle.tematica">
                                <i class="fas fa-image me-1"></i>
                                <strong>Temática:</strong> {{ puzzle.tematica }}
                            </li>
                            <li v-if="puzzle.material">
                                <i class="fas fa-layer-group me-1"></i>
                                <strong>Material:</strong> {{ puzzle.material }}
                            </li>
                            <li v-if="puzzle.num_piezas_total">
                                <i class="fas fa-hashtag me-1"></i>
                                <strong>Total declarado:</strong> {{ puzzle.num_piezas_total }} piezas
                            </li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent d-flex gap-2">
                        <Link
                            :href="route('rompecabezas.show', puzzle.id)"
                            class="btn btn-primary btn-sm flex-fill"
                        >
                            <i class="fas fa-eye me-1"></i>
                            Ver / Editar
                        </Link>
                        <Link
                            :href="route('rompecabezas.armar', puzzle.id)"
                            class="btn btn-success btn-sm flex-fill"
                        >
                            <i class="fas fa-wand-magic-sparkles me-1"></i>
                            Armar
                        </Link>
                        <button
                            @click="deletePuzzle(puzzle)"
                            class="btn btn-outline-danger btn-sm"
                            title="Eliminar"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { inject } from 'vue';

const route = inject('route');

defineProps({
    puzzles: Array,
});

const dificultadBadge = (d) => ({
    'bg-success': d === 'facil',
    'bg-warning text-dark': d === 'medio',
    'bg-danger': d === 'dificil',
});

const deletePuzzle = (puzzle) => {
    if (confirm(`¿Eliminar "${puzzle.nombre}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('rompecabezas.destroy', puzzle.id));
    }
};
</script>
