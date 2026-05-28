<template>
    <div>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <Link :href="route('rompecabezas.index')" class="navbar-brand">
                    <i class="fas fa-puzzle-piece me-2"></i>
                    Puzzle Manager
                </Link>

                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarMain"
                    aria-controls="navbarMain"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <Link
                                :href="route('rompecabezas.index')"
                                class="nav-link"
                                :class="{ active: $page.url.startsWith('/rompecabezas') }"
                            >
                                <i class="fas fa-list me-1"></i>
                                Rompecabezas
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link
                                :href="route('rompecabezas.create')"
                                class="nav-link"
                                :class="{ active: $page.url === '/rompecabezas/create' }"
                            >
                                <i class="fas fa-plus me-1"></i>
                                Nuevo
                            </Link>
                        </li>
                    </ul>

                    <span class="navbar-text text-muted small">
                        <i class="fas fa-database me-1"></i>
                        BD2 — UVG 2026
                    </span>
                </div>
            </div>
        </nav>

        <!-- Flash messages -->
        <div class="container-fluid px-4 pt-3" v-if="$page.props.flash?.success || $page.props.flash?.error">
            <div
                v-if="$page.props.flash?.success"
                class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                role="alert"
            >
                <i class="fas fa-check-circle me-2"></i>
                {{ $page.props.flash.success }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="alert alert-danger alert-dismissible fade show d-flex align-items-center"
                role="alert"
            >
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ $page.props.flash.error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        <!-- Page content -->
        <main class="container-fluid px-4 py-3">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="text-center text-muted small py-3 mt-4 border-top">
            <i class="fas fa-puzzle-piece me-1"></i>
            Puzzle Manager · CC3089 Base de Datos 2 · UVG Semestre I 2026
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
