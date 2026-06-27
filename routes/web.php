<?php
//controladores
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WelcomeController; 
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArchivoPublicacionController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvestigadorController;
use App\Http\Controllers\Admin\PublicacionDashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ExtractArticleController;

// --------------------------------------------------
// REPOSITORIO PÚBLICO (No requiere login)
// --------------------------------------------------

// Esta es la ruta que mostrará tu diseño del repositorio al entrar a http://localhost/sis-gestion/public/
Route::get('/', [WelcomeController::class, 'index'])->name('inicio');
Route::get('/autores', [\App\Http\Controllers\WelcomeController::class, 'autores'])->name('public.autores');
Route::get('/publicacion/{id}', [WelcomeController::class, 'showPublicacion'])->name('public.publicacion.show');
Route::get('public/publicaciones/{id}/file/{file}', [WelcomeController::class, 'verArchivoPublico'])
    ->where('file', '.*')
    ->name('public.publicaciones.ver-archivo');
Route::get('public/publicaciones/{id}/download/{file}', [WelcomeController::class, 'descargarArchivoPublico'])
    ->where('file', '.*')
    ->name('public.publicaciones.descargar-archivo');
// --------------------------------------------------
// AUTH
// --------------------------------------------------

Auth::routes(['register' => false]);

// --------------------------------------------------
// ACTIVACIÓN DE CUENTA (Flujo de Invitación)
// --------------------------------------------------
Route::get('/activate/{token}', [\App\Http\Controllers\Auth\ActivationController::class, 'showForm'])
    ->name('activation.form')
    ->middleware('guest');
Route::post('/activate/{token}', [\App\Http\Controllers\Auth\ActivationController::class, 'activate'])
    ->name('activation.process')
    ->middleware('guest');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Patrón global para IDs numéricos (opcional)
Route::pattern('id', '[0-9]+');

// --------------------------------------------------
// ADMIN - DASHBOARD PRINCIPAL
// --------------------------------------------------

// Movimos el panel de control a /admin/dashboard
Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->name('admin.index')
    ->middleware('auth');   
    

    // Cuando Laravel intente mandarte a /home después del login, 
// lo interceptamos y lo enviamos a tu panel de administrador
Route::get('/home', function () {
    return redirect()->route('admin.index');
});

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

Route::post('/admin/usuarios/{id}/resend-invitation', [UsuarioController::class, 'resendInvitation'])
    ->name('usuarios.resend-invitation')
    ->middleware('auth');

    

// --------------------------------------------------
// ADMIN (prefix: /admin)
// --------------------------------------------------

Route::middleware('auth')->prefix('admin')->group(function () {

    // PUBLICACIONES (CRUD)
    Route::resource('publicaciones', PublicacionController::class)
        ->names('publicaciones')
        ->parameters(['publicaciones' => 'id']);   // /admin/publicaciones/{id}

    // EXPORTAR PUBLICACIONES A EXCEL Y PDF
    Route::get('publicaciones/export/excel', [PublicacionController::class, 'exportExcel'])
        ->name('publicaciones.export.excel');

    Route::get('publicaciones/export/pdf', [PublicacionController::class, 'exportPdf'])
        ->name('publicaciones.export.pdf');

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

    Route::post('publicaciones/{publicacion}/archivos', [ArchivoPublicacionController::class, 'store'])
        ->name('publicaciones.archivos.store');

    Route::delete('publicaciones/{publicacion}/archivos/{archivo}', [ArchivoPublicacionController::class, 'destroy'])
        ->name('publicaciones.archivos.destroy');

    Route::patch('publicaciones/{publicacion}/archivos/{archivo}/principal', [ArchivoPublicacionController::class, 'setPrincipal'])
        ->name('publicaciones.archivos.principal');

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