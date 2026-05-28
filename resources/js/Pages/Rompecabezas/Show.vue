<template>
    <AppLayout>
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <Link :href="route('rompecabezas.index')" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="fas fa-arrow-left me-1"></i>
                    Volver
                </Link>
                <div>
                    <h1 class="h3 mb-0 fw-bold">
                        <i class="fas fa-puzzle-piece text-danger me-2"></i>
                        {{ rompecabezas.nombre }}
                    </h1>
                    <div class="d-flex gap-2 mt-1">
                        <span v-if="rompecabezas.marca" class="badge bg-secondary">
                            <i class="fas fa-tag me-1"></i>{{ rompecabezas.marca }}
                        </span>
                        <span v-if="rompecabezas.dificultad" :class="dificultadBadge(rompecabezas.dificultad)" class="badge">
                            {{ rompecabezas.dificultad }}
                        </span>
                        <span v-if="rompecabezas.tematica" class="badge bg-info text-dark">
                            {{ rompecabezas.tematica }}
                        </span>
                    </div>
                </div>
            </div>
            <a :href="route('rompecabezas.armar', rompecabezas.id)" class="btn btn-success btn-lg shadow">
                <i class="fas fa-wand-magic-sparkles me-2"></i>
                Iniciar Algoritmo
            </a>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center stat-card border-primary">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-primary">{{ localPiezas.length }}</div>
                        <div class="small text-muted">Total piezas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center stat-card border-success">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-success">{{ disponiblesCount }}</div>
                        <div class="small text-muted">Disponibles</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center stat-card border-danger">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-danger">{{ faltantesCount }}</div>
                        <div class="small text-muted">Faltantes</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center stat-card border-warning">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-warning">{{ localConexiones.length }}</div>
                        <div class="small text-muted">Conexiones</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna izquierda: Lista de piezas + agregar -->
            <div class="col-lg-6">

                <!-- Quick Add Pieza -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white fw-semibold">
                        <i class="fas fa-plus-circle me-2"></i>
                        Agregar Pieza Rápida
                        <small class="float-end opacity-75">Presiona Enter para agregar</small>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Tip:</strong> Pon el maskin tape en la pieza con su etiqueta (ej: P1) y el número en cada conector.
                        </div>
                        <div v-if="addError" class="alert alert-danger py-2 small alert-dismissible">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ addError }}
                            <button type="button" class="btn-close btn-close-sm" @click="addError = null"></button>
                        </div>
                        <div v-if="addSuccess" class="alert alert-success py-2 small alert-dismissible">
                            <i class="fas fa-check me-1"></i>
                            {{ addSuccess }}
                            <button type="button" class="btn-close btn-close-sm" @click="addSuccess = null"></button>
                        </div>

                        <div class="quick-add-form">
                            <div class="row g-2 align-items-end">
                                <div class="col-5">
                                    <label class="form-label form-label-sm fw-semibold">
                                        Etiqueta (maskin tape)
                                    </label>
                                    <input
                                        ref="etiquetaInput"
                                        v-model="quickForm.etiqueta"
                                        type="text"
                                        class="form-control"
                                        placeholder="P1, A3, B2..."
                                        @keyup.enter="addPieza"
                                        maxlength="50"
                                    />
                                </div>
                                <div class="col-4">
                                    <label class="form-label form-label-sm fw-semibold">
                                        # Conexiones
                                    </label>
                                    <input
                                        v-model="quickForm.num_conexiones"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="form-control"
                                        @keyup.enter="addPieza"
                                    />
                                </div>
                                <div class="col-3">
                                    <button
                                        @click="addPieza"
                                        class="btn btn-primary w-100"
                                        :disabled="addingPieza || !quickForm.etiqueta"
                                    >
                                        <span v-if="addingPieza" class="spinner-border spinner-border-sm"></span>
                                        <i v-else class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de piezas -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold">
                            <i class="fas fa-list me-2"></i>
                            Piezas ({{ localPiezas.length }})
                        </span>
                        <div class="d-flex gap-1">
                            <span class="badge bg-success">{{ disponiblesCount }} disponibles</span>
                            <span class="badge bg-danger">{{ faltantesCount }} faltantes</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="localPiezas.length === 0" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No hay piezas registradas aún.
                        </div>
                        <div v-else class="table-responsive">
                            <table class="table table-hover table-piezas mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Etiqueta</th>
                                        <th class="text-center">Conexiones</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pieza in localPiezas" :key="pieza.id" :class="{ 'table-danger': !pieza.disponible }">
                                        <td class="fw-semibold align-middle">
                                            <i class="fas fa-puzzle-piece me-1 text-muted"></i>
                                            <span :class="{ 'pieza-faltante': !pieza.disponible }">
                                                {{ pieza.etiqueta }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-secondary">
                                                {{ pieza.num_conexiones }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span v-if="pieza.disponible" class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Disponible
                                            </span>
                                            <span v-else class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Faltante
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-sm">
                                                <button
                                                    @click="togglePieza(pieza)"
                                                    :class="pieza.disponible ? 'btn-outline-warning' : 'btn-outline-success'"
                                                    class="btn"
                                                    :title="pieza.disponible ? 'Marcar como faltante' : 'Marcar como disponible'"
                                                >
                                                    <i :class="pieza.disponible ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                </button>
                                                <button
                                                    @click="deletePieza(pieza)"
                                                    class="btn btn-outline-danger"
                                                    title="Eliminar pieza"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Conexiones -->
            <div class="col-lg-6">

                <!-- Formulario de conexión -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white fw-semibold">
                        <i class="fas fa-link me-2"></i>
                        Agregar Conexión entre Piezas
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border small mb-3 py-2">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            Selecciona dos piezas y los números de conexión que se unen entre sí.
                        </div>

                        <div v-if="connError" class="alert alert-danger py-2 small alert-dismissible">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ connError }}
                            <button type="button" class="btn-close btn-close-sm" @click="connError = null"></button>
                        </div>
                        <div v-if="connSuccess" class="alert alert-success py-2 small alert-dismissible">
                            <i class="fas fa-check me-1"></i>
                            {{ connSuccess }}
                            <button type="button" class="btn-close btn-close-sm" @click="connSuccess = null"></button>
                        </div>

                        <div class="row g-3">
                            <!-- Pieza 1 -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    <i class="fas fa-puzzle-piece text-primary me-1"></i>
                                    Pieza 1
                                </label>
                                <select v-model="connForm.pieza1_id" class="form-select form-select-sm" @change="connForm.conexion_pieza1 = ''">
                                    <option value="">— Selecciona la primera pieza —</option>
                                    <option v-for="p in piezasParaConexion" :key="p.id" :value="p.id">
                                        {{ p.etiqueta }} ({{ p.num_conexiones }} conexiones)
                                    </option>
                                </select>
                            </div>

                            <!-- Conexión de pieza 1 -->
                            <div class="col-6">
                                <label class="form-label fw-semibold small">
                                    Conexión # de Pieza 1
                                </label>
                                <select v-model="connForm.conexion_pieza1" class="form-select form-select-sm" :disabled="!connForm.pieza1_id">
                                    <option value="">— Nro. —</option>
                                    <option v-for="n in conexionesDisponiblesPieza1" :key="n" :value="n">
                                        Conexión #{{ n }}
                                    </option>
                                </select>
            </div>

                            <!-- Conexión de pieza 2 -->
                            <div class="col-6">
                                <label class="form-label fw-semibold small">
                                    Conexión # de Pieza 2
                                </label>
                                <select v-model="connForm.conexion_pieza2" class="form-select form-select-sm" :disabled="!connForm.pieza2_id">
                                    <option value="">— Nro. —</option>
                                    <option v-for="n in conexionesDisponiblesPieza2" :key="n" :value="n">
                                        Conexión #{{ n }}
                                    </option>
                                </select>
                            </div>

                            <!-- Pieza 2 -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    <i class="fas fa-puzzle-piece text-success me-1"></i>
                                    Pieza 2
                                </label>
                                <select v-model="connForm.pieza2_id" class="form-select form-select-sm" @change="connForm.conexion_pieza2 = ''">
                                    <option value="">— Selecciona la segunda pieza —</option>
                                    <option v-for="p in piezasParaConexion.filter(p => p.id !== connForm.pieza1_id)" :key="p.id" :value="p.id">
                                        {{ p.etiqueta }} ({{ p.num_conexiones }} conexiones)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Resumen visual -->
                        <div v-if="connForm.pieza1_id && connForm.pieza2_id && connForm.conexion_pieza1 && connForm.conexion_pieza2" class="mt-3 p-3 bg-light rounded border">
                            <div class="text-center fw-semibold text-success">
                                <i class="fas fa-link me-1"></i>
                                {{ getPiezaLabel(connForm.pieza1_id) }} <strong class="text-danger">#{{ connForm.conexion_pieza1 }}</strong>
                                &nbsp;↔&nbsp;
                                <strong class="text-danger">#{{ connForm.conexion_pieza2 }}</strong> {{ getPiezaLabel(connForm.pieza2_id) }}
                            </div>
                        </div>

                        <button
                            @click="addConexion"
                            class="btn btn-success w-100 mt-3"
                            :disabled="!connFormValid || addingConexion"
                        >
                            <span v-if="addingConexion" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-link me-1"></i>
                            Registrar Conexión
                        </button>
                    </div>
                </div>

                <!-- Lista de conexiones -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold">
                            <i class="fas fa-project-diagram me-2"></i>
                            Conexiones ({{ localConexiones.length }})
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="localConexiones.length === 0" class="text-center py-4 text-muted">
                            <i class="fas fa-unlink fa-2x mb-2 d-block"></i>
                            No hay conexiones registradas aún.
                        </div>
                        <div v-else class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center">Pieza 1</th>
                                        <th class="text-center">Conn.</th>
                                        <th class="text-center"></th>
                                        <th class="text-center">Conn.</th>
                                        <th class="text-center">Pieza 2</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in localConexiones" :key="c.id">
                                        <td class="text-center fw-semibold align-middle">{{ c.pieza1.etiqueta }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-primary">#{{ c.conexion_pieza1 }}</span>
                                        </td>
                                        <td class="text-center align-middle text-muted">↔</td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-success">#{{ c.conexion_pieza2 }}</span>
                                        </td>
                                        <td class="text-center fw-semibold align-middle">{{ c.pieza2.etiqueta }}</td>
                                        <td class="text-end align-middle">
                                            <button
                                                @click="deleteConexion(c)"
                                                class="btn btn-outline-danger btn-sm"
                                                title="Eliminar conexión"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ GRAFO VISUAL DE CONEXIONES ═══ -->
        <div class="card shadow-sm mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-semibold">
                    <i class="fas fa-diagram-project me-2 text-primary"></i>
                    Grafo de Conexiones
                    <span class="text-muted fw-normal small ms-2">
                        {{ localPiezas.length }} nodos · {{ localConexiones.length }} aristas
                    </span>
                </span>
                <div class="d-flex gap-2" v-if="localPiezas.length > 0">
                    <button @click="resetZoom" class="btn btn-outline-secondary btn-sm" title="Reset zoom">
                        <i class="fas fa-expand me-1"></i>Reset
                    </button>
                    <button @click="runSimulation()" class="btn btn-outline-secondary btn-sm" title="Reorganizar nodos">
                        <i class="fas fa-rotate me-1"></i>Reorganizar
                    </button>
                </div>
            </div>
            <div class="card-body p-3 position-relative">

                <!-- Sin piezas -->
                <div v-if="localPiezas.length === 0" class="text-center py-5 text-muted">
                    <i class="fas fa-circle-nodes fa-3x mb-3 d-block opacity-25"></i>
                    <p class="mb-0">Agrega piezas para ver el grafo</p>
                </div>

                <template v-else>
                    <svg
                        ref="svgRef"
                        viewBox="0 0 640 480"
                        class="w-100"
                        :style="{
                            display: 'block',
                            maxHeight: '520px',
                            cursor: mode === 'draw' ? 'crosshair'
                                  : dragging       ? 'grabbing'
                                  : panning        ? 'grabbing'
                                  : 'grab',
                            userSelect: 'none',
                        }"
                        @mousedown="onSVGMousedown"
                        @mousemove="onSVGPointerMove"
                        @mouseup="onSVGPointerUp"
                        @mouseleave="onSVGPointerUp"
                        @wheel.prevent="onSVGWheel"
                        @touchmove.prevent="onSVGPointerMove"
                        @touchend="onSVGPointerUp"
                    >
                        <defs>
                            <filter id="node-shadow" x="-40%" y="-40%" width="180%" height="180%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#00000033"/>
                            </filter>
                        </defs>

                        <!-- Grupo con zoom/pan — todo el contenido va aquí dentro -->
                        <g :transform="innerTransform">

                        <!-- Aristas (debajo de los nodos) -->
                        <g v-for="edge in graphEdges" :key="edge.id">
                            <!-- Línea de conexión -->
                            <line
                                :x1="edge.x1" :y1="edge.y1"
                                :x2="edge.x2" :y2="edge.y2"
                                stroke="#adb5bd"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                            <!-- Badge conn. pieza1 (azul, cerca del nodo 1) -->
                            <g :transform="`translate(${edge.lx1},${edge.ly1})`">
                                <rect x="-13" y="-9" width="26" height="17" rx="4" fill="#0d6efd"/>
                                <text
                                    text-anchor="middle"
                                    dominant-baseline="middle"
                                    fill="white"
                                    font-size="9"
                                    font-weight="bold"
                                    y="0"
                                >#{{ edge.conn1 }}</text>
                            </g>
                            <!-- Badge conn. pieza2 (verde, cerca del nodo 2) -->
                            <g :transform="`translate(${edge.lx2},${edge.ly2})`">
                                <rect x="-13" y="-9" width="26" height="17" rx="4" fill="#198754"/>
                                <text
                                    text-anchor="middle"
                                    dominant-baseline="middle"
                                    fill="white"
                                    font-size="9"
                                    font-weight="bold"
                                    y="0"
                                >#{{ edge.conn2 }}</text>
                            </g>
                        </g>

                        <!-- Arista fantasma (draw mode) -->
                        <g v-if="ghostEdge" pointer-events="none">
                            <line
                                :x1="ghostEdge.x1" :y1="ghostEdge.y1"
                                :x2="ghostEdge.x2" :y2="ghostEdge.y2"
                                stroke="#0d6efd"
                                stroke-width="2.5"
                                stroke-dasharray="7 4"
                                stroke-linecap="round"
                                opacity="0.85"
                            />
                            <circle :cx="ghostEdge.x1" :cy="ghostEdge.y1"
                                r="5" fill="#0d6efd"/>
                        </g>

                        <!-- Nodos (encima de las aristas) -->
                        <g
                            v-for="pieza in localPiezas"
                            :key="pieza.id"
                            :transform="`translate(${simPos[pieza.id]?.x ?? 320},${simPos[pieza.id]?.y ?? 240})`"
                            :style="{ cursor: mode === 'draw' ? 'pointer' : dragging && _dragNode?.id === pieza.id ? 'grabbing' : 'grab' }"
                            @mousedown="(e) => onNodePointerDown(e, pieza.id)"
                            @touchstart.prevent="(e) => onNodePointerDown(e, pieza.id)"
                            @mouseenter="() => { hoveredNodeId = pieza.id; }"
                            @mouseleave="() => { if (hoveredNodeId === pieza.id) hoveredNodeId = null; }"
                            @mouseup="(e) => onNodeMouseUp(e, pieza.id)"
                        >
                            <!-- Halo azul: nodo destino válido durante draw mode -->
                            <circle
                                v-if="mode === 'draw' && hoveredNodeId === pieza.id && ghostEdge?.sourceId !== pieza.id"
                                r="28"
                                fill="none"
                                stroke="#0d6efd"
                                stroke-width="2.5"
                                opacity="0.7"
                            />
                            <!-- Halo de estado (faltante = punteado) -->
                            <circle
                                v-if="!pieza.disponible"
                                r="28"
                                fill="none"
                                stroke="#e74c3c"
                                stroke-width="2"
                                stroke-dasharray="4 3"
                                opacity="0.6"
                            />
                            <!-- Círculo principal -->
                            <circle
                                r="22"
                                :fill="pieza.disponible ? '#27ae60' : '#e74c3c'"
                                stroke="white"
                                stroke-width="2.5"
                                filter="url(#node-shadow)"
                            />
                            <!-- Etiqueta -->
                            <text
                                text-anchor="middle"
                                dominant-baseline="middle"
                                fill="white"
                                font-size="11"
                                font-weight="bold"
                                font-family="'Segoe UI', sans-serif"
                            >{{ pieza.etiqueta }}</text>
                            <!-- Sub-label: num_conexiones -->
                            <text
                                text-anchor="middle"
                                y="34"
                                fill="#6c757d"
                                font-size="9"
                                font-family="'Segoe UI', sans-serif"
                            >{{ pieza.num_conexiones }}c</text>
                            <!-- Handles de arista: 4 dots (N/E/S/W), visibles en hover (modo view) -->
                            <template v-if="hoveredNodeId === pieza.id && mode === 'view'">
                                <!-- Arriba -->
                                <g @mousedown.stop.prevent="(e) => onHandleMousedown(e, pieza.id, 0, -22)" style="cursor: crosshair">
                                    <circle cx="0" cy="-22" r="10" fill="transparent"/>
                                    <circle cx="0" cy="-22" r="6" fill="#0d6efd" stroke="white" stroke-width="1.5"/>
                                    <circle cx="0" cy="-22" r="2.5" fill="white" opacity="0.8"/>
                                </g>
                                <!-- Derecha -->
                                <g @mousedown.stop.prevent="(e) => onHandleMousedown(e, pieza.id, 22, 0)" style="cursor: crosshair">
                                    <circle cx="22" cy="0" r="10" fill="transparent"/>
                                    <circle cx="22" cy="0" r="6" fill="#0d6efd" stroke="white" stroke-width="1.5"/>
                                    <circle cx="22" cy="0" r="2.5" fill="white" opacity="0.8"/>
                                </g>
                                <!-- Abajo -->
                                <g @mousedown.stop.prevent="(e) => onHandleMousedown(e, pieza.id, 0, 22)" style="cursor: crosshair">
                                    <circle cx="0" cy="22" r="10" fill="transparent"/>
                                    <circle cx="0" cy="22" r="6" fill="#0d6efd" stroke="white" stroke-width="1.5"/>
                                    <circle cx="0" cy="22" r="2.5" fill="white" opacity="0.8"/>
                                </g>
                                <!-- Izquierda -->
                                <g @mousedown.stop.prevent="(e) => onHandleMousedown(e, pieza.id, -22, 0)" style="cursor: crosshair">
                                    <circle cx="-22" cy="0" r="10" fill="transparent"/>
                                    <circle cx="-22" cy="0" r="6" fill="#0d6efd" stroke="white" stroke-width="1.5"/>
                                    <circle cx="-22" cy="0" r="2.5" fill="white" opacity="0.8"/>
                                </g>
                            </template>
                        </g>

                        </g><!-- /zoom-pan group -->
                    </svg>

                    <!-- Overlay: input inline para nombre de nueva pieza -->
                    <div
                        v-if="inlineInput.visible"
                        class="position-absolute bg-white border border-primary rounded shadow p-2"
                        :style="{ left: inlineInput.screenX + 'px', top: inlineInput.screenY + 'px', zIndex: 200, minWidth: '200px' }"
                        @mousedown.stop
                    >
                        <div class="small fw-semibold text-primary mb-1">
                            <i class="fas fa-plus-circle me-1"></i>Nueva pieza conectada
                        </div>
                        <div v-if="drawError" class="alert alert-danger py-1 px-2 small mb-1">{{ drawError }}</div>
                        <input
                            ref="inlineInputRef"
                            v-model="inlineInput.name"
                            type="text"
                            class="form-control form-control-sm mb-1"
                            placeholder="Etiqueta (ej: P3)"
                            @keyup.enter="confirmInlineInput"
                            @keyup.escape="cancelInlineInput"
                        />
                        <div class="d-flex gap-1">
                            <button @click="confirmInlineInput" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="fas fa-check me-1"></i>Crear
                            </button>
                            <button @click="cancelInlineInput" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Leyenda -->
                    <div class="d-flex flex-wrap gap-3 justify-content-center mt-2 small text-muted border-top pt-2">
                        <span><i class="fas fa-circle text-success me-1"></i>Disponible</span>
                        <span><i class="fas fa-circle text-danger me-1"></i>Faltante</span>
                        <span>
                            <span class="badge bg-primary" style="font-size:.7rem">#N</span>
                            Conector de Pieza 1
                        </span>
                        <span>
                            <span class="badge bg-success" style="font-size:.7rem">#N</span>
                            Conector de Pieza 2
                        </span>
                        <span><span class="text-muted">Nc</span> = número de conectores de la pieza</span>
                        <span>
                            <span class="d-inline-block rounded-circle bg-primary" style="width:8px;height:8px;vertical-align:middle"></span>
                            Hover nodo + arrastrar dot = nueva arista
                        </span>
                        <span class="ms-auto text-muted" style="font-size:.75rem;">
                            <i class="fas fa-scroll me-1"></i>Scroll = zoom &nbsp;·&nbsp;
                            <i class="fas fa-hand me-1"></i>Arrastrar fondo = mover &nbsp;·&nbsp;
                            <i class="fas fa-hand-pointer me-1"></i>Arrastrar nodo = reposicionar
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Info del rompecabezas -->
        <div class="card shadow-sm mt-4" v-if="rompecabezas.descripcion || rompecabezas.tipo || rompecabezas.material">
            <div class="card-header fw-semibold">
                <i class="fas fa-info-circle me-2"></i>
                Información adicional
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <div v-if="rompecabezas.tipo" class="col-md-3">
                        <strong>Tipo:</strong> {{ rompecabezas.tipo }}
                    </div>
                    <div v-if="rompecabezas.material" class="col-md-3">
                        <strong>Material:</strong> {{ rompecabezas.material }}
                    </div>
                    <div v-if="rompecabezas.num_piezas_total" class="col-md-3">
                        <strong>Total declarado:</strong> {{ rompecabezas.num_piezas_total }} piezas
                    </div>
                    <div v-if="rompecabezas.descripcion" class="col-12">
                        <strong>Descripción:</strong> {{ rompecabezas.descripcion }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const route = inject('route');

const props = defineProps({
    rompecabezas: Object,
    piezas: Array,
    conexiones: Array,
    stats: Object,
});

// ─── Estado local reactivo ───────────────────────────────────────────────────
const localPiezas     = ref([...props.piezas]);
const localConexiones = ref([...props.conexiones]);

// Re-correr simulación cuando cambia estructura del grafo (nodos o aristas)
watch(
    () => [localPiezas.value.length, localConexiones.value.length],
    () => runSimulation(),
);
function onKeydown(e) { if (e.key === 'Escape') cancelInlineInput(); }
onMounted(() => { runSimulation(); document.addEventListener('keydown', onKeydown); });
onUnmounted(() => { if (_sim) _sim.stop(); document.removeEventListener('keydown', onKeydown); });

// ─── Stats computados ────────────────────────────────────────────────────────
const disponiblesCount = computed(() => localPiezas.value.filter(p => p.disponible).length);
const faltantesCount   = computed(() => localPiezas.value.filter(p => !p.disponible).length);

// ─── Grafo SVG — d3-force layout ─────────────────────────────────────────────
import {
    forceSimulation, forceLink, forceManyBody, forceCenter, forceCollide,
} from 'd3-force';

const NODE_R = 24;
const SVG_W  = 640;
const SVG_H  = 480;

const simPos = ref({});  // { [piezaId]: { x, y } }
let _sim = null;         // instancia d3 simulation

function runSimulation() {
    // Detener simulación anterior
    if (_sim) _sim.stop();

    const piezas = localPiezas.value;
    const conns  = localConexiones.value;
    if (!piezas.length) { simPos.value = {}; return; }

    const cx = SVG_W / 2, cy = SVG_H / 2;
    const n  = piezas.length;
    const r0 = Math.min(cx, cy) * 0.6;

    // Nodos d3: conservar posición previa si ya existe
    const nodes = piezas.map((p, i) => {
        const prev  = simPos.value[p.id];
        const angle = (2 * Math.PI * i / n) - Math.PI / 2;
        return {
            id: p.id,
            x:  prev ? prev.x : cx + (n === 1 ? 0 : r0 * Math.cos(angle)),
            y:  prev ? prev.y : cy + (n === 1 ? 0 : r0 * Math.sin(angle)),
        };
    });

    // Aristas d3: índices numéricos (d3-force los requiere)
    const idxById = new Map(nodes.map((nd, i) => [nd.id, i]));
    const links = conns
        .map(c => ({
            source: idxById.get(c.pieza1.id),
            target: idxById.get(c.pieza2.id),
        }))
        .filter(l => l.source !== undefined && l.target !== undefined);

    _sim = forceSimulation(nodes)
        .force('link',    forceLink(links).distance(130).strength(0.6))
        .force('charge',  forceManyBody().strength(-400))
        .force('center',  forceCenter(cx, cy).strength(0.08))
        .force('collide', forceCollide(NODE_R + 8).strength(0.8))
        .alphaDecay(0.025)   // enfriamiento gradual (más tiempo de animación)
        .on('tick', () => {
            const pos = {};
            nodes.forEach(nd => {
                pos[nd.id] = {
                    x: Math.max(NODE_R + 4, Math.min(SVG_W - NODE_R - 4, nd.x)),
                    y: Math.max(NODE_R + 4, Math.min(SVG_H - NODE_R - 4, nd.y)),
                };
            });
            simPos.value = { ...pos };  // spread para forzar reactividad
        });
}

// ─── Editor visual de aristas ─────────────────────────────────────────────────
const mode          = ref('view');   // 'view' | 'draw' | 'naming'
const hoveredNodeId = ref(null);
const ghostEdge     = ref(null);     // { sourceId, x1, y1, x2, y2 } world coords
const inlineInput   = ref({ visible: false, screenX: 0, screenY: 0, worldX: 0, worldY: 0, name: '' });
const inlineInputRef = ref(null);
const drawError     = ref(null);

// ─── Drag + Zoom + Pan (igual que Neo4J AuraDB) ──────────────────────────────
const svgRef       = ref(null);
const dragging     = ref(false);
const panning      = ref(false);
let   _dragNode    = null;

// Estado de zoom/pan: translate(tx ty) scale(s) aplicado al <g> interior
const vt = ref({ x: 0, y: 0, s: 1 });   // vt = view transform
const innerTransform = computed(() =>
    `translate(${vt.value.x} ${vt.value.y}) scale(${vt.value.s})`
);

// Variables internas de pan
let _panLast = null;

// ── Conversión de coordenadas ─────────────────────────────────────────────────
// Screen → viewBox (espacio fijo 640×480)
function toViewBox(event) {
    const svg = svgRef.value;
    if (!svg) return { x: 0, y: 0 };
    const src = event.touches ? event.touches[0] : event;
    const pt  = svg.createSVGPoint();
    pt.x = src.clientX;
    pt.y = src.clientY;
    return pt.matrixTransform(svg.getScreenCTM().inverse());
}

// Screen → mundo d3 (descontando el pan/zoom del <g> interior)
function toWorld(event) {
    const { x, y } = toViewBox(event);
    return { x: (x - vt.value.x) / vt.value.s, y: (y - vt.value.y) / vt.value.s };
}

// ── Nodo drag ────────────────────────────────────────────────────────────────
function onNodePointerDown(event, piezaId) {
    if (mode.value !== 'view') return;   // no arrastrar nodos durante draw/naming
    event.preventDefault();
    event.stopPropagation();   // evitar que inicie pan
    if (!_sim) return;
    _dragNode = _sim.nodes().find(n => n.id === piezaId) ?? null;
    if (!_dragNode) return;
    dragging.value = true;
    _dragNode.fx = _dragNode.x;
    _dragNode.fy = _dragNode.y;
    _sim.alphaTarget(0.4).restart();
}

// ── Pan (arrastrar fondo) ─────────────────────────────────────────────────────
function onSVGMousedown(event) {
    if (_dragNode) return;   // está en modo drag de nodo
    panning.value = true;
    _panLast = toViewBox(event);
}

// ── Movimiento compartido: drag de nodo O pan ─────────────────────────────────
function onSVGPointerMove(event) {
    // Draw mode: actualizar extremo de la arista fantasma
    if (mode.value === 'draw' && ghostEdge.value) {
        const { x, y } = toWorld(event);
        ghostEdge.value = { ...ghostEdge.value, x2: x, y2: y };
        return;
    }
    if (_dragNode) {
        // Drag de nodo: convertir a coordenadas del mundo d3
        const { x, y } = toWorld(event);
        _dragNode.fx = Math.max(NODE_R + 4, Math.min(SVG_W - NODE_R - 4, x));
        _dragNode.fy = Math.max(NODE_R + 4, Math.min(SVG_H - NODE_R - 4, y));
    } else if (panning.value && _panLast) {
        // Pan: delta en coordenadas del viewBox → mover translate
        const cur = toViewBox(event);
        vt.value = {
            ...vt.value,
            x: vt.value.x + (cur.x - _panLast.x),
            y: vt.value.y + (cur.y - _panLast.y),
        };
        _panLast = cur;
    }
}

function onSVGPointerUp(event) {
    // Draw mode: soltar en espacio vacío → mostrar overlay de nueva pieza
    if (mode.value === 'draw' && ghostEdge.value) {
        const svgCardBody = svgRef.value?.closest('.card-body');
        const rect = svgCardBody?.getBoundingClientRect() ?? { left: 0, top: 0, width: 640, height: 480 };
        const src  = event?.touches ? event.changedTouches[0] : event;
        const wPos = toWorld(event);
        inlineInput.value = {
            visible: true,
            screenX: Math.min((src?.clientX ?? 0) - rect.left + 10, rect.width - 220),
            screenY: Math.min((src?.clientY ?? 0) - rect.top  + 10, rect.height - 130),
            worldX: wPos.x,
            worldY: wPos.y,
            name: suggestLabel(),
        };
        mode.value = 'naming';
        nextTick(() => inlineInputRef.value?.select());
        return;
    }
    if (_dragNode) {
        _dragNode.fx = null;
        _dragNode.fy = null;
        _dragNode = null;
        dragging.value = false;
        _sim?.alphaTarget(0);
    }
    panning.value = false;
    _panLast = null;
}

// ── Zoom con scroll (centrado en el cursor) ───────────────────────────────────
const ZOOM_MIN = 0.2;
const ZOOM_MAX = 6;

function onSVGWheel(event) {
    const factor  = event.deltaY < 0 ? 1.15 : 1 / 1.15;
    const newS    = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, vt.value.s * factor));
    const cursor  = toViewBox(event);
    // Mantener el punto bajo el cursor fijo en el mundo
    const ratio   = newS / vt.value.s;
    vt.value = {
        s: newS,
        x: cursor.x + (vt.value.x - cursor.x) * ratio,
        y: cursor.y + (vt.value.y - cursor.y) * ratio,
    };
}

// ── Reset zoom ────────────────────────────────────────────────────────────────
function resetZoom() { vt.value = { x: 0, y: 0, s: 1 }; }

// Aristas calculadas a partir de simPos (reactivo)
const graphEdges = computed(() => {
    return localConexiones.value.map(conn => {
        const p1 = simPos.value[conn.pieza1.id];
        const p2 = simPos.value[conn.pieza2.id];
        if (!p1 || !p2) return null;

        const dx   = p2.x - p1.x;
        const dy   = p2.y - p1.y;
        const dist = Math.sqrt(dx * dx + dy * dy) || 1;
        const ux   = dx / dist;
        const uy   = dy / dist;

        const x1  = p1.x + ux * NODE_R;
        const y1  = p1.y + uy * NODE_R;
        const x2  = p2.x - ux * NODE_R;
        const y2  = p2.y - uy * NODE_R;

        const gap = Math.min(40, dist * 0.30);
        return {
            id:   conn.id,
            x1, y1, x2, y2,
            lx1:  p1.x + ux * (NODE_R + gap),
            ly1:  p1.y + uy * (NODE_R + gap),
            lx2:  p2.x - ux * (NODE_R + gap),
            ly2:  p2.y - uy * (NODE_R + gap),
            conn1: conn.conexion_pieza1,
            conn2: conn.conexion_pieza2,
        };
    }).filter(Boolean);
});

// ─── Editor visual: funciones ────────────────────────────────────────────────

function onHandleMousedown(event, piezaId, offsetX = 0, offsetY = -22) {
    mode.value = 'draw';
    hoveredNodeId.value = null;
    const src = simPos.value[piezaId];
    const wPos = toWorld(event);
    ghostEdge.value = {
        sourceId: piezaId,
        x1: (src?.x ?? 320) + offsetX,
        y1: (src?.y ?? 240) + offsetY,
        x2: wPos.x,
        y2: wPos.y,
    };
}

function onNodeMouseUp(event, piezaId) {
    if (mode.value !== 'draw' || !ghostEdge.value) return;
    if (ghostEdge.value.sourceId === piezaId) {
        cancelDraw();
        return;
    }
    event.stopPropagation();   // evitar que dispare onSVGPointerUp
    const sourceId = ghostEdge.value.sourceId;
    ghostEdge.value = null;
    mode.value = 'view';
    autoConnect(sourceId, piezaId);
}

function cancelDraw() {
    ghostEdge.value = null;
    mode.value = 'view';
    hoveredNodeId.value = null;
}

function cancelInlineInput() {
    inlineInput.value = { visible: false, screenX: 0, screenY: 0, worldX: 0, worldY: 0, name: '' };
    ghostEdge.value = null;
    mode.value = 'view';
    drawError.value = null;
}

function suggestLabel() {
    return `P${localPiezas.value.length + 1}`;
}

async function autoConnect(sourceId, targetId) {
    drawError.value = null;
    try {
        const res = await axios.post(route('conexiones.autoStore'), {
            pieza1_id: sourceId,
            pieza2_id: targetId,
        });
        const { conexion, pieza1_updated, pieza2_updated } = res.data;
        localConexiones.value.push(conexion);
        [pieza1_updated, pieza2_updated].forEach(upd => {
            const p = localPiezas.value.find(p => p.id === upd.id);
            if (p) p.num_conexiones = upd.num_conexiones;
        });
    } catch (e) {
        drawError.value = e.response?.data?.error ?? 'Error al conectar las piezas.';
    }
}

async function confirmInlineInput() {
    const label = inlineInput.value.name.trim();
    if (!label) return;
    drawError.value = null;

    const sourceId = ghostEdge.value?.sourceId;
    if (!sourceId) { cancelInlineInput(); return; }

    // 1. Crear nueva pieza
    let newPieza;
    try {
        const res = await axios.post(
            route('piezas.store', props.rompecabezas.id),
            { etiqueta: label, num_conexiones: 1 },
        );
        newPieza = res.data.pieza;
    } catch (e) {
        drawError.value = e.response?.data?.error ?? 'Error al crear la pieza.';
        return;
    }

    // 2. Sembrar posición inicial cerca del cursor (antes de que la simulación arranque)
    simPos.value[newPieza.id] = { x: inlineInput.value.worldX, y: inlineInput.value.worldY };
    localPiezas.value.push(newPieza);   // dispara watch → runSimulation()

    // 3. Crear conexión automática
    await nextTick();
    await autoConnect(sourceId, newPieza.id);

    cancelInlineInput();
}

// ─── Quick add pieza ─────────────────────────────────────────────────────────
const etiquetaInput = ref(null);
const quickForm     = ref({ etiqueta: '', num_conexiones: 1 });
const addingPieza   = ref(false);
const addError      = ref(null);
const addSuccess    = ref(null);

const addPieza = async () => {
    if (!quickForm.value.etiqueta) return;
    addingPieza.value = true;
    addError.value    = null;
    addSuccess.value  = null;

    try {
        const res = await axios.post(route('piezas.store', props.rompecabezas.id), quickForm.value);
        localPiezas.value.push({ ...res.data.pieza, conexiones_count: 0 });
        addSuccess.value = res.data.message;
        quickForm.value.etiqueta = '';
        await nextTick();
        etiquetaInput.value?.focus();
    } catch (e) {
        addError.value = e.response?.data?.error ?? 'Error al agregar la pieza.';
    } finally {
        addingPieza.value = false;
    }
};

const togglePieza = async (pieza) => {
    try {
        const res = await axios.patch(route('piezas.toggle', pieza.id));
        const idx = localPiezas.value.findIndex(p => p.id === pieza.id);
        if (idx >= 0) localPiezas.value[idx].disponible = res.data.disponible;
    } catch {
        alert('Error al cambiar disponibilidad de la pieza.');
    }
};

const deletePieza = async (pieza) => {
    if (!confirm(`¿Eliminar la pieza "${pieza.etiqueta}"? También se eliminarán sus conexiones.`)) return;
    try {
        await axios.delete(route('piezas.destroy', pieza.id));
        localPiezas.value     = localPiezas.value.filter(p => p.id !== pieza.id);
        localConexiones.value = localConexiones.value.filter(
            c => c.pieza1.id !== pieza.id && c.pieza2.id !== pieza.id
        );
    } catch {
        alert('Error al eliminar la pieza.');
    }
};

// ─── Conexiones ──────────────────────────────────────────────────────────────
const connForm = ref({
    pieza1_id: '',
    pieza2_id: '',
    conexion_pieza1: '',
    conexion_pieza2: '',
});
const addingConexion = ref(false);
const connError      = ref(null);
const connSuccess    = ref(null);

// Solo piezas (disponibles o no) para asignar conexiones
const piezasParaConexion = computed(() => localPiezas.value);

const getPiezaById = (id) => localPiezas.value.find(p => p.id === Number(id));
const getPiezaLabel = (id) => getPiezaById(id)?.etiqueta ?? '?';

const conexionesDisponiblesPieza1 = computed(() => {
    const p = getPiezaById(connForm.value.pieza1_id);
    if (!p) return [];
    return Array.from({ length: p.num_conexiones }, (_, i) => i + 1);
});

const conexionesDisponiblesPieza2 = computed(() => {
    const p = getPiezaById(connForm.value.pieza2_id);
    if (!p) return [];
    return Array.from({ length: p.num_conexiones }, (_, i) => i + 1);
});

const connFormValid = computed(() =>
    connForm.value.pieza1_id &&
    connForm.value.pieza2_id &&
    connForm.value.conexion_pieza1 &&
    connForm.value.conexion_pieza2 &&
    connForm.value.pieza1_id !== connForm.value.pieza2_id
);

const addConexion = async () => {
    if (!connFormValid.value) return;
    addingConexion.value = true;
    connError.value      = null;
    connSuccess.value    = null;

    try {
        const res = await axios.post(route('conexiones.store'), connForm.value);
        localConexiones.value.push(res.data.conexion);
        connSuccess.value = res.data.message;
        // Reset form
        connForm.value = { pieza1_id: '', pieza2_id: '', conexion_pieza1: '', conexion_pieza2: '' };
    } catch (e) {
        connError.value = e.response?.data?.error ?? 'Error al registrar la conexión.';
    } finally {
        addingConexion.value = false;
    }
};

const deleteConexion = async (conn) => {
    if (!confirm(`¿Eliminar la conexión ${conn.pieza1.etiqueta}#${conn.conexion_pieza1} ↔ ${conn.pieza2.etiqueta}#${conn.conexion_pieza2}?`)) return;
    try {
        await axios.delete(route('conexiones.destroy', conn.id));
        localConexiones.value = localConexiones.value.filter(c => c.id !== conn.id);
    } catch {
        alert('Error al eliminar la conexión.');
    }
};

// ─── Helpers ─────────────────────────────────────────────────────────────────
const dificultadBadge = (d) => ({
    'bg-success': d === 'facil',
    'bg-warning text-dark': d === 'medio',
    'bg-danger': d === 'dificil',
});
</script>
