<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLoggerService
{
    /**
     * Registra un evento de auditoría en la base de datos.
     *
     * @param string $module Nombre del módulo (ej. Usuarios, Publicaciones, Autenticación)
     * @param string $action Acción realizada (ej. CREAR, ACTUALIZAR, ELIMINAR, LOGIN, LOGOUT)
     * @param string $description Descripción detallada
     * @param mixed $user Usuario que realiza la acción (opcional)
     * @return AuditLog|null
     */
    public static function log(string $module, string $action, string $description, $user = null): ?AuditLog
    {
        try {
            $currentUser = $user ?? Auth::user();

            return AuditLog::create([
                'user_id'     => $currentUser?->id,
                'user_name'   => $currentUser?->name ?? 'Sistema / Visitante',
                'user_role'   => $currentUser?->rol ?? 'espectador',
                'module'      => $module,
                'action'      => strtoupper($action),
                'description' => $description,
                'ip_address'  => Request::ip(),
                'user_agent'  => substr(Request::userAgent() ?? '', 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Silenciosamente evitar interrumpir el flujo principal si falla la bitácora
            logger()->error('Error guardando AuditLog: ' . $e->getMessage());
            return null;
        }
    }
}
