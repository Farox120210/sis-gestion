<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArchivoPublicacionController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvestigadorController;

use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//  return view('welcome');
//});

Auth::routes();

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
    // PUBLICACIONES
    Route::resource('publicaciones', PublicacionController::class)
        ->names('publicaciones')
        ->parameters(['publicaciones' => 'id']); // => /admin/publicaciones/{id}

    Route::post('publicaciones/{publicacion}/archivos', [ArchivoPublicacionController::class, 'store'])
        ->name('publicaciones.archivos.store');

    Route::delete('publicaciones/{publicacion}/archivos/{archivo}', [ArchivoPublicacionController::class, 'destroy'])
        ->name('publicaciones.archivos.destroy');

    Route::patch('publicaciones/{publicacion}/archivos/{archivo}/principal', [ArchivoPublicacionController::class, 'setPrincipal'])
        ->name('publicaciones.archivos.principal');

    // INVESTIGADORES
    Route::resource('investigadores', InvestigadorController::class)
        ->names('investigadores')
        ->parameters(['investigadores' => 'id']); // => /admin/investigadores/{id}
});
