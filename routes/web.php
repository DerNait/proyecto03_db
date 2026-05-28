<?php

use App\Http\Controllers\AlgoritmoController;
use App\Http\Controllers\ConexionController;
use App\Http\Controllers\PiezaController;
use App\Http\Controllers\RompecabezasController;
use Illuminate\Support\Facades\Route;

// Redirect raíz a lista de rompecabezas
Route::get('/', fn () => redirect()->route('rompecabezas.index'));

// CRUD de Rompecabezas (Inertia pages)
Route::resource('rompecabezas', RompecabezasController::class)
    ->only(['index', 'create', 'store', 'show', 'destroy']);

// Gestión de piezas (AJAX - JSON responses)
Route::prefix('rompecabezas/{rompecabezas}/piezas')
    ->name('piezas.')
    ->group(function () {
        Route::post('/', [PiezaController::class, 'store'])->name('store');
    });

Route::prefix('piezas')
    ->name('piezas.')
    ->group(function () {
        Route::patch('{pieza}/toggle', [PiezaController::class, 'toggle'])->name('toggle');
        Route::delete('{pieza}', [PiezaController::class, 'destroy'])->name('destroy');
    });

// Gestión de conexiones (AJAX - JSON responses)
Route::prefix('conexiones')
    ->name('conexiones.')
    ->group(function () {
        Route::post('/', [ConexionController::class, 'store'])->name('store');
        Route::post('/auto', [ConexionController::class, 'autoStore'])->name('autoStore');
        Route::delete('{conexion}', [ConexionController::class, 'destroy'])->name('destroy');
    });

// Algoritmo de armado
Route::get('rompecabezas/{rompecabezas}/armar', [AlgoritmoController::class, 'armar'])
    ->name('rompecabezas.armar');
