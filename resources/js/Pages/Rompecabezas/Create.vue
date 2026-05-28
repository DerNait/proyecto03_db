<template>
    <AppLayout>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-4">
                    <Link :href="route('rompecabezas.index')" class="btn btn-outline-secondary btn-sm me-3">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver
                    </Link>
                    <div>
                        <h1 class="h3 mb-0 fw-bold">
                            <i class="fas fa-plus-circle text-primary me-2"></i>
                            Nuevo Rompecabezas
                        </h1>
                        <p class="text-muted small mb-0">Registra la información del rompecabezas</p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form @submit.prevent="submit">
                            <!-- Nombre (requerido) -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-signature me-1 text-muted"></i>
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.nombre }"
                                    placeholder="ej: Rompecabezas Bosque 500 piezas"
                                    required
                                    autofocus
                                />
                                <div class="invalid-feedback">{{ form.errors.nombre }}</div>
                            </div>

                            <div class="row g-3">
                                <!-- Marca -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tag me-1 text-muted"></i>
                                        Marca
                                    </label>
                                    <input
                                        v-model="form.marca"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.marca }"
                                        placeholder="ej: Ravensburger"
                                    />
                                    <div class="invalid-feedback">{{ form.errors.marca }}</div>
                                </div>

                                <!-- Temática -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-image me-1 text-muted"></i>
                                        Temática
                                    </label>
                                    <input
                                        v-model="form.tematica"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.tematica }"
                                        placeholder="ej: Paisaje, Animales, Abstracto"
                                    />
                                    <div class="invalid-feedback">{{ form.errors.tematica }}</div>
                                </div>

                                <!-- Tipo -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-shapes me-1 text-muted"></i>
                                        Tipo
                                    </label>
                                    <input
                                        v-model="form.tipo"
                                        type="text"
                                        class="form-control"
                                        placeholder="ej: Tradicional, 3D"
                                    />
                                </div>

                                <!-- Material -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-layer-group me-1 text-muted"></i>
                                        Material
                                    </label>
                                    <input
                                        v-model="form.material"
                                        type="text"
                                        class="form-control"
                                        placeholder="ej: Cartón, Madera"
                                    />
                                </div>

                                <!-- Num piezas total -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-hashtag me-1 text-muted"></i>
                                        Piezas totales (caja)
                                    </label>
                                    <input
                                        v-model="form.num_piezas_total"
                                        type="number"
                                        min="1"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.num_piezas_total }"
                                        placeholder="ej: 500"
                                    />
                                    <div class="invalid-feedback">{{ form.errors.num_piezas_total }}</div>
                                </div>

                                <!-- Dificultad -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-gauge-high me-1 text-muted"></i>
                                        Dificultad
                                    </label>
                                    <select v-model="form.dificultad" class="form-select">
                                        <option value="">— Sin especificar —</option>
                                        <option value="facil">Fácil</option>
                                        <option value="medio">Medio</option>
                                        <option value="dificil">Difícil</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mt-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-align-left me-1 text-muted"></i>
                                    Descripción
                                </label>
                                <textarea
                                    v-model="form.descripcion"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Notas adicionales sobre este rompecabezas..."
                                ></textarea>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary px-4"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="fas fa-save me-1"></i>
                                    Crear Rompecabezas
                                </button>
                                <Link :href="route('rompecabezas.index')" class="btn btn-outline-secondary">
                                    Cancelar
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { inject } from 'vue';

const route = inject('route');

const form = useForm({
    nombre: '',
    tipo: '',
    tematica: '',
    marca: '',
    material: '',
    num_piezas_total: '',
    dificultad: '',
    descripcion: '',
});

const submit = () => {
    form.post(route('rompecabezas.store'));
};
</script>
