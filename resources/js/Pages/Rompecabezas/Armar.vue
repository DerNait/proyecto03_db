<template>
    <AppLayout>
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="d-flex align-items-center">
                <Link :href="route('rompecabezas.show', rompecabezas.id)" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="fas fa-arrow-left me-1"></i>
                    Volver
                </Link>
                <div>
                    <h1 class="h3 mb-0 fw-bold">
                        <i class="fas fa-wand-magic-sparkles text-success me-2"></i>
                        Guía de Armado: {{ rompecabezas.nombre }}
                    </h1>
                    <p class="text-muted small mb-0">
                        Instrucciones generadas automáticamente por el algoritmo BFS
                    </p>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-md-end gap-2">
                <div style="min-width: 220px;">
                    <label class="form-label small fw-semibold mb-1">
                        Pieza inicial
                    </label>
                    <select v-model="selectedPiezaInicialId" class="form-select">
                        <option value="">Al azar</option>
                        <option
                            v-for="pieza in piezasDisponibles"
                            :key="pieza.id"
                            :value="pieza.id"
                        >
                            {{ pieza.etiqueta }}
                        </option>
                    </select>
                </div>
                <a :href="algoritmoHref" class="btn btn-primary">
                    <i class="fas fa-shuffle me-1"></i>
                    Recalcular guía
                </a>
            </div>
        </div>

        <!-- Sin piezas -->
        <div v-if="resultado.total_disponibles === 0" class="text-center py-5">
            <i class="fas fa-box-open fa-4x text-muted mb-3 d-block"></i>
            <h4 class="text-muted">No hay piezas disponibles</h4>
            <p class="text-muted">{{ resultado.mensaje }}</p>
            <Link :href="route('rompecabezas.show', rompecabezas.id)" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i>
                Agregar piezas
            </Link>
        </div>

        <template v-else>
            <!-- Resumen de resultados -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body py-3">
                            <div class="display-6 fw-bold text-success">{{ resultado.porcentaje }}%</div>
                            <div class="small text-muted">Completado</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body py-3">
                            <div class="display-6 fw-bold text-primary">{{ resultado.pasos.length }}</div>
                            <div class="small text-muted">Pasos totales</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center" :class="resultado.islas > 1 ? 'border-warning' : 'border-success'">
                        <div class="card-body py-3">
                            <div class="display-6 fw-bold" :class="resultado.islas > 1 ? 'text-warning' : 'text-success'">
                                {{ resultado.islas }}
                            </div>
                            <div class="small text-muted">
                                {{ resultado.islas === 1 ? 'Isla (completo)' : 'Islas formadas' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center border-danger">
                        <div class="card-body py-3">
                            <div class="display-6 fw-bold text-danger">
                                {{ resultado.total_general - resultado.total_disponibles }}
                            </div>
                            <div class="small text-muted">Piezas faltantes</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advertencias -->
            <div v-if="resultado.islas > 1" class="alert alert-warning d-flex align-items-start mb-4">
                <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1 flex-shrink-0"></i>
                <div>
                    <strong>Hay {{ resultado.islas }} grupos (islas) separados</strong> debido a piezas faltantes.<br/>
                    El algoritmo ha generado instrucciones para cada isla. Cuando una isla se complete,
                    el algoritmo indicará comenzar una <em>nueva isla</em> a ~15cm de la anterior.
                </div>
            </div>

            <div v-if="resultado.piezas_sin_conexion?.length > 0" class="alert alert-info d-flex align-items-start mb-4">
                <i class="fas fa-info-circle fa-2x me-3 mt-1 flex-shrink-0"></i>
                <div>
                    <strong>Piezas sin conexiones registradas:</strong>
                    <span v-for="(e, i) in resultado.piezas_sin_conexion" :key="i" class="badge bg-secondary ms-1">{{ e }}</span><br/>
                    <small class="text-muted">Estas piezas están disponibles pero no tienen conexiones registradas. Se colocarán como islas individuales.</small>
                </div>
            </div>

            <!-- REGLA DE ORO -->
            <div class="card border-danger shadow mb-4">
                <div class="card-header bg-danger text-white fw-bold fs-5">
                    <i class="fas fa-triangle-exclamation me-2"></i>
                    REGLA DE ORO — Leer ANTES de comenzar
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6" v-for="(regla, i) in reglas" :key="i">
                            <div class="d-flex align-items-start">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                    style="width:36px;height:36px;font-weight:bold;">{{ i + 1 }}</div>
                                <div>
                                    <strong>{{ regla.titulo }}</strong><br/>
                                    <small>{{ regla.detalle }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de progreso global -->
            <div class="mb-4">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Progreso de armado</span>
                    <span>{{ resultado.porcentaje }}% ({{ resultado.total_disponibles }} de {{ resultado.total_general }} piezas)</span>
                </div>
                <div class="progress" style="height: 12px;">
                    <div
                        class="progress-bar"
                        :class="resultado.porcentaje === 100 ? 'bg-success' : resultado.porcentaje >= 60 ? 'bg-primary' : 'bg-warning'"
                        :style="{ width: resultado.porcentaje + '%' }"
                        role="progressbar"
                    ></div>
                </div>
            </div>

            <!-- ═══ NAVEGADOR PASO A PASO ═══ -->
            <div class="card shadow-sm paso-card" :class="`paso-${currentPaso.tipo}`">

                <!-- Cabecera del paso actual -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge fs-6 px-3 py-2" :class="badgeClass">
                            <i :class="badgeIcon" class="me-1"></i>
                            {{ badgeLabel }}
                        </span>
                        <span class="fw-semibold">{{ stepTitle }}</span>
                    </div>
                    <span class="badge bg-secondary">
                        <i class="fas fa-layer-group me-1"></i>
                        Isla {{ currentPaso.isla }}
                    </span>
                </div>

                <!-- Pills de navegación rápida -->
                <div class="card-body border-bottom py-2 px-3">
                    <div class="d-flex gap-1 flex-wrap align-items-center">
                        <span class="small text-muted me-1">Ir a:</span>
                        <button
                            v-for="(paso, i) in resultado.pasos"
                            :key="i"
                            @click="goTo(i)"
                            class="btn btn-sm rounded-pill px-2 py-0"
                            style="font-size:.72rem; min-width:30px;"
                            :class="pillClass(paso, i)"
                            :title="pillTitle(paso)"
                        >
                            {{ pillLabel(paso, i) }}
                        </button>
                    </div>
                </div>

                <!-- Barra de progreso del paso -->
                <div style="height:4px;">
                    <div
                        class="h-100 transition-width"
                        :class="badgeClass.replace('bg-','bg-')"
                        :style="{ width: ((currentIdx + 1) / resultado.pasos.length * 100) + '%' }"
                    ></div>
                </div>

                <!-- Contenido del paso -->
                <div class="card-body py-4 px-4">
                    <div v-html="renderedInstructions" class="instrucciones-html lh-lg"></div>
                </div>

                <!-- Navegación anterior / siguiente -->
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <button
                        @click="prev"
                        :disabled="currentIdx === 0"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Anterior
                    </button>

                    <span class="text-muted small fw-semibold">
                        <i class="fas fa-shoe-prints me-1 text-primary"></i>
                        Paso {{ currentIdx + 1 }} de {{ resultado.pasos.length }}
                    </span>

                    <button
                        @click="next"
                        :disabled="currentIdx === resultado.pasos.length - 1"
                        class="btn btn-primary"
                    >
                        Siguiente
                        <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- Footer: solo visible al llegar al último paso -->
            <transition name="fade">
                <div v-if="currentIdx === resultado.pasos.length - 1" class="card mt-4 border-0 bg-light">
                    <div class="card-body text-center py-4">
                        <div v-if="resultado.porcentaje === 100" class="text-success">
                            <i class="fas fa-trophy fa-3x mb-3 d-block"></i>
                            <strong class="fs-5">¡Rompecabezas completado al 100%!</strong>
                        </div>
                        <div v-else class="text-warning">
                            <i class="fas fa-puzzle-piece fa-3x mb-3 d-block"></i>
                            <strong class="fs-5">Rompecabezas armado al {{ resultado.porcentaje }}%</strong><br/>
                            <small class="text-muted">
                                {{ resultado.total_general - resultado.total_disponibles }} pieza(s) faltantes impiden completarlo al 100%.
                            </small>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed, inject, ref } from 'vue';

const route = inject('route');

const props = defineProps({
    rompecabezas: Object,
    resultado: Object,
    piezasDisponibles: Array,
    piezaInicialId: {
        type: [Number, String],
        default: '',
    },
});

const selectedPiezaInicialId = ref(props.piezaInicialId ?? '');
const algoritmoHref = computed(() => {
    const base = route('rompecabezas.armar', props.rompecabezas.id);

    return selectedPiezaInicialId.value
        ? `${base}?pieza_inicial_id=${encodeURIComponent(selectedPiezaInicialId.value)}`
        : base;
});

// ─── Reglas de oro (datos estáticos) ─────────────────────────────────────────
const reglas = [
    {
        titulo:  'Etiquetas SIEMPRE hacia arriba',
        detalle: 'El maskin tape con el nombre (ej: "P1") debe mirar hacia el techo en TODO momento. NUNCA voltees la pieza.',
    },
    {
        titulo:  'Verificar AMBAS piezas antes de conectar',
        detalle: 'Antes de unir dos piezas, confirma que AMBAS tienen su etiqueta mirando hacia arriba. Si una está al revés, el rompecabezas quedará mal.',
    },
    {
        titulo:  'Usar los NÚMEROS de conexión',
        detalle: 'Cada pieza tiene conectores numerados con maskin tape (1, 2, 3…). El paso indica exactamente qué número va con cuál.',
    },
    {
        titulo:  'Sin forzar',
        detalle: 'Si las piezas no encajan suavemente, hay un error de orientación. Revisa etiquetas y números de conexión.',
    },
];

// ─── Navegación paso a paso ───────────────────────────────────────────────────
const currentIdx = ref(0);
const currentPaso = computed(() => props.resultado.pasos[currentIdx.value]);

function next() { if (currentIdx.value < props.resultado.pasos.length - 1) currentIdx.value++; }
function prev() { if (currentIdx.value > 0) currentIdx.value--; }
function goTo(idx) { currentIdx.value = idx; }
function doPrint() { window.print(); }

// ─── Badge / título del paso actual ──────────────────────────────────────────
const badgeClass = computed(() => {
    const t = currentPaso.value?.tipo;
    if (t === 'inicio')     return 'bg-danger';
    if (t === 'nueva_isla') return 'bg-warning text-dark';
    return 'bg-success';
});

const badgeIcon = computed(() => {
    const t = currentPaso.value?.tipo;
    if (t === 'inicio')     return 'fas fa-flag';
    if (t === 'nueva_isla') return 'fas fa-map-pin';
    return 'fas fa-link';
});

const badgeLabel = computed(() => {
    const p = currentPaso.value;
    if (!p) return '';
    if (p.tipo === 'inicio')     return 'INICIO';
    if (p.tipo === 'nueva_isla') return `ISLA ${p.isla}`;
    return `PASO ${p.numero}`;
});

const stepTitle = computed(() => {
    const p = currentPaso.value;
    if (!p) return '';
    if (p.tipo === 'inicio')
        return `Pieza inicial: [${p.etiqueta_principal}]`;
    if (p.tipo === 'conexion')
        return `Conectar [${p.etiqueta_principal}] a [${p.etiqueta_base}] — #${p.conexion_base} ↔ #${p.conexion_nueva}`;
    return `Nueva isla — Pieza [${p.etiqueta_principal}]`;
});

// ─── Pills de navegación rápida ───────────────────────────────────────────────
function pillLabel(paso, i) {
    if (paso.tipo === 'inicio')     return 'I';
    if (paso.tipo === 'nueva_isla') return `I${paso.isla}`;
    return paso.numero;
}

function pillTitle(paso) {
    if (paso.tipo === 'inicio')     return `Inicio: [${paso.etiqueta_principal}]`;
    if (paso.tipo === 'nueva_isla') return `Isla ${paso.isla}: [${paso.etiqueta_principal}]`;
    return `Paso ${paso.numero}: [${paso.etiqueta_principal}]`;
}

function pillClass(paso, i) {
    const active = i === currentIdx.value;
    if (paso.tipo === 'inicio')
        return active ? 'btn-danger' : 'btn-outline-danger';
    if (paso.tipo === 'nueva_isla')
        return active ? 'btn-warning' : 'btn-outline-warning';
    return active ? 'btn-success' : 'btn-outline-secondary';
}

// ─── Renderizado de instrucciones con FontAwesome ────────────────────────────
// Mapeo de marcadores a iconos FA
const MARKERS = [
    { re: /\[PUZZLE\]/g,  icon: 'fa-puzzle-piece',         color: 'text-danger'  },
    { re: /\[LINK\]/g,    icon: 'fa-link',                  color: 'text-success' },
    { re: /\[ISLAND\]/g,  icon: 'fa-water',                 color: 'text-info'    },
    { re: /\[WARN\]/g,    icon: 'fa-triangle-exclamation',  color: 'text-warning' },
    { re: /\[CHECK\]/g,   icon: 'fa-circle-check',          color: 'text-success' },
    { re: /\[ARROW\]/g,   icon: 'fa-arrow-right',           color: 'text-primary' },
];

function applyMarkers(line) {
    let out = line;
    for (const { re, icon, color } of MARKERS) {
        out = out.replace(re, `<i class="fas ${icon} ${color} me-1"></i>`);
    }
    return out;
}

// Detecta qué marcador (si alguno) estaba al inicio de la línea original
function lineMarker(raw) {
    for (const { re } of MARKERS) {
        if (re.test(raw)) return re.source.replace(/\\/g, '').replace(/\[|\]/g, '');
        re.lastIndex = 0;
    }
    return null;
}

function renderInstructions(text) {
    if (!text) return '';
    return text.split('\n').map(raw => {
        const line = applyMarkers(raw);

        // Línea vacía → separador de espacio
        if (!raw.trim()) return '<div class="mb-2"></div>';

        // Título del paso (contiene marcador PUZZLE, LINK, ISLAND al inicio)
        if (/^\[(PUZZLE|LINK|ISLAND)\]/.test(raw)) {
            return `<p class="fw-bold fs-6 mb-3 pb-1 border-bottom">${line}</p>`;
        }

        // Línea de advertencia
        if (/^\s*\[WARN\]/.test(raw)) {
            return `<div class="d-flex align-items-start gap-2 mb-2 ps-2">
                        <span>${line.trimStart()}</span>
                    </div>`;
        }

        // Línea de check/ok
        if (/^\s*\[CHECK\]/.test(raw)) {
            return `<div class="d-flex align-items-start gap-2 mb-2 ps-2 text-success">
                        <span>${line.trimStart()}</span>
                    </div>`;
        }

        // Línea de la flecha/encaje (la línea más importante del paso)
        if (/\[ARROW\]/.test(raw)) {
            return `<div class="alert alert-primary d-flex align-items-center gap-2 py-2 px-3 mb-2 fw-bold">
                        ${line.trimStart()}
                    </div>`;
        }

        // Paso numerado (líneas que empiezan con "1." "2." etc.)
        const numbered = raw.match(/^(\d+)\.\s+(.*)/);
        if (numbered) {
            return `<div class="d-flex gap-2 mb-2">
                        <span class="badge bg-secondary rounded-pill flex-shrink-0 mt-1" style="min-width:22px">${numbered[1]}</span>
                        <span>${applyMarkers(numbered[2])}</span>
                    </div>`;
        }

        // Línea de continuación indentada (espacios al inicio)
        if (/^\s{3,}/.test(raw)) {
            return `<p class="mb-1 text-muted ms-4 small">${line.trimStart()}</p>`;
        }

        // Línea normal
        return `<p class="mb-1 ms-1">${line}</p>`;
    }).join('');
}

const renderedInstructions = computed(() =>
    renderInstructions(currentPaso.value?.instrucciones ?? '')
);
</script>

<style scoped>
/* Colores de borde según tipo de paso */
.paso-inicio     { border-left: 4px solid #dc3545 !important; }
.paso-conexion   { border-left: 4px solid #198754 !important; }
.paso-nueva_isla { border-left: 4px solid #ffc107 !important; }

/* Barra de progreso suave */
.transition-width {
    transition: width 0.35s ease;
}

/* Instrucciones renderizadas */
.instrucciones-html {
    font-size: 1rem;
    line-height: 1.7;
}

/* Fade de aparición del footer */
.fade-enter-active { transition: opacity 0.5s ease, transform 0.4s ease; }
.fade-enter-from   { opacity: 0; transform: translateY(12px); }

/* Impresión: mostrar solo la card del paso */
@media print {
    .no-print { display: none !important; }
    .card-footer { display: none !important; }
    .card-body.border-bottom { display: none !important; }
}
</style>
