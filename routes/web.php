<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvestigadorController;
use App\Http\Controllers\Admin\PublicacionDashboardController;
use App\Http\Controllers\ProjectController; // <-- agregado
use App\Http\Controllers\ExtractArticleController;




use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//  return view('welcome');
//});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Route::get('/', function () {return view('admin/usuarios');});
//USUARIOS
//create
Route::get('', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');
Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index')->middleware('auth');
Route::get('/admin/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create')->middleware('auth');
Route::post('/admin/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store')->middleware('auth');


//show
Route::get('/admin/usuarios/{id}', [UsuarioController::class, 'show'])->name('usuarios.show')->middleware('auth');


//edit
Route::get('/admin/usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit')->middleware('auth');

//update
Route::put('/admin/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update')->middleware('auth');



//RUTAS EN GENERAL 
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::resource('publicaciones', PublicacionController::class)
        ->names('publicaciones')
        ->parameters(['publicaciones' => 'id']); // opcional: para que sea /publicaciones/{id}

});

Route::middleware('auth')->prefix('admin')->group(function () {
    // PUBLICACIONES
    Route::resource('publicaciones', PublicacionController::class)
        ->names('publicaciones')
        ->parameters(['publicaciones' => 'id']); // => /admin/publicaciones/{id}
    Route::get('/api/investigadores/search', [PublicacionController::class, 'searchInvestigadores'])
        ->name('api.investigadores.search');

    // routes/web.php
    Route::get('/publicaciones/{id}/file/{file}', [PublicacionController::class, 'verArchivo'])
        ->where('file', '.*')->name('publicaciones.ver-archivo');

    Route::get('/publicaciones/{id}/download/{file}', [PublicacionController::class, 'descargarArchivo'])
        ->where('file', '.*')->name('publicaciones.descargar-archivo');

    // INVESTIGADORES
    Route::resource('investigadores', InvestigadorController::class)
        ->names('investigadores')
        ->parameters(['investigadores' => 'id']); // => /admin/investigadores/{id}

    Route::get('investigadores/export/general', [InvestigadorController::class, 'exportGeneral'])
        ->name('investigadores.export.general');

    Route::get('investigadores/export/filtrado', [InvestigadorController::class, 'exportFiltrado'])
        ->name('investigadores.export.filtrado');

    //CONTROLADOR REPORTES GRAFICOS 

    Route::get('admin/publicaciones', [PublicacionDashboardController::class, 'index'])
        ->name('admin.publicaciones.index');

        Route::post('/extract-article', [App\Http\Controllers\ExtractArticleController::class, 'extract'])
    ->name('extract.article');


Route::post('/extract-article', [ExtractArticleController::class, 'extract'])
    ->name('extract.article')
    ->middleware('auth'); 

    Route::get('/publicaciones/export', [PublicacionController::class, 'export'])
    ->name('publicaciones.export')
    ->middleware('auth');

    Route::pattern('id', '[0-9]+');

});
