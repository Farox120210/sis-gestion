<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 👉 Simplemente definimos los Gates directamente aquí, sin registerPolicies()

        // 1. Regla para ELIMINAR (Solo super_admin)
        Gate::define('eliminar-publicaciones', function ($user) {
            return $user->rol === 'super_admin';
        });

        // 2. Regla para CREAR/EDITAR (super_admin y publicador)
        Gate::define('gestionar-publicaciones', function ($user) {
            return in_array($user->rol, ['super_admin', 'publicador']);
        });

        // 3. Regla para VER (Todos los registrados)
        Gate::define('ver-publicaciones', function ($user) {
            return in_array($user->rol, ['super_admin', 'publicador', 'espectador']);
        });
        Gate::define('gestionar-usuarios', function ($user) {
            return $user->rol === 'super_admin';
        });
    }
}