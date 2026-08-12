<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Services\AuditLoggerService;

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
        // 👉 Definimos los Gates
        Gate::define('eliminar-publicaciones', function ($user) {
            return $user->rol === 'super_admin';
        });

        Gate::define('gestionar-publicaciones', function ($user) {
            return in_array($user->rol, ['super_admin', 'publicador']);
        });

        Gate::define('ver-publicaciones', function ($user) {
            return in_array($user->rol, ['super_admin', 'publicador', 'espectador']);
        });

        Gate::define('gestionar-usuarios', function ($user) {
            return $user->rol === 'super_admin';
        });

        // Escuchar eventos de Autenticación para AuditLogs
        Event::listen(Login::class, function (Login $event) {
            AuditLoggerService::log('Autenticación', 'LOGIN', "El usuario {$event->user->name} ({$event->user->email}) ha iniciado sesión.", $event->user);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLoggerService::log('Autenticación', 'LOGOUT', "El usuario {$event->user->name} ({$event->user->email}) ha cerrado sesión.", $event->user);
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            $email = $event->credentials['email'] ?? 'Desconocido';
            AuditLoggerService::log('Autenticación', 'LOGIN_FALLIDO', "Intento fallido de inicio de sesión para el correo: {$email}");
        });
    }
}