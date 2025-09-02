<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
  //  return view('welcome');
//});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Route::get('/', function () {return view('admin/usuarios');});

Route::get('', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');
Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index')->middleware('auth');
Route::get('/admin/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create')->middleware('auth');
Route::post('/admin/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store')->middleware('auth');

