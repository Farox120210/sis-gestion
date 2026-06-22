<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvestigadorController;
use App\Http\Controllers\Admin\PublicacionDashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ExtractArticleController;

// --------------------------------------------------
// AUTH
// --------------------------------------------------

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Patrón global para IDs numéricos (opcional)
Route::pattern('id', '[0-9]+');

// --------------------------------------------------
// ADMIN - DASHBOARD PRINCIPAL
// --------------------------------------------------

Route::get('', [AdminController::class, 'index'])
    ->name('admin.index')
    ->middleware('auth');

// --------------------------------------------------
// USUARIOS
// --------------------------------------------------

Route::get('/admin/usuarios', [UsuarioController::class, 'index'])
    ->name('usuarios.index')
    ->middleware('auth');

Route::get('/admin/usuarios/create', [UsuarioController::class, 'create'])
    ->name('usuarios.create')
    ->middleware('auth');

Route::post('/admin/usuarios', [UsuarioController::class, 'store'])
    ->name('usuarios.store')
    ->middleware('auth');

Route::get('/admin/usuarios/{id}', [UsuarioController::class, 'show'])
    ->name('usuarios.show')
    ->middleware('auth');

Route::get('/admin/usuarios/{id}/edit', [UsuarioController::class, 'edit'])
    ->name('usuarios.edit')
    ->middleware('auth');

Route::put('/admin/usuarios/{usuario}', [UsuarioController::class, 'update'])
    ->name('usuarios.update')
    ->middleware('auth');

Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])
    ->name('usuarios.destroy')
    ->middleware('auth');

// --------------------------------------------------
// ADMIN (prefix: /admin)
// --------------------------------------------------

Route::middleware('auth')->prefix('admin')->group(function () {

    // PUBLICACIONES (CRUD)
    Route::resource('publicaciones', PublicacionController::class)
        ->names('publicaciones')
        ->parameters(['publicaciones' => 'id']);   // /admin/publicaciones/{id}

    // EXPORTAR PUBLICACIONES A EXCEL
    Route::get('publicaciones/export', [PublicacionController::class, 'export'])
        ->name('publicaciones.export');

    // BUSCADOR DE INVESTIGADORES PARA FORMULARIOS
    Route::get('api/investigadores/search', [PublicacionController::class, 'searchInvestigadores'])
        ->name('api.investigadores.search');

    // VER / DESCARGAR ARCHIVOS DE PUBLICACIONES
    Route::get('publicaciones/{id}/file/{file}', [PublicacionController::class, 'verArchivo'])
        ->where('file', '.*')
        ->name('publicaciones.ver-archivo');

    Route::get('publicaciones/{id}/download/{file}', [PublicacionController::class, 'descargarArchivo'])
        ->where('file', '.*')
        ->name('publicaciones.descargar-archivo');

    // INVESTIGADORES
    Route::resource('investigadores', InvestigadorController::class)
        ->names('investigadores')
        ->parameters(['investigadores' => 'id']);  // /admin/investigadores/{id}

    Route::get('investigadores/export/general', [InvestigadorController::class, 'exportGeneral'])
        ->name('investigadores.export.general');

    Route::get('investigadores/export/filtrado', [InvestigadorController::class, 'exportFiltrado'])
        ->name('investigadores.export.filtrado');

    // DASHBOARD GRÁFICO DE PUBLICACIONES
    Route::get('publicaciones-dashboard', [PublicacionDashboardController::class, 'index'])
        ->name('admin.publicaciones.index');

    // EXTRACTOR DE ARTÍCULOS
    Route::post('extract-article', [ExtractArticleController::class, 'extract'])
        ->name('extract.article');
});
