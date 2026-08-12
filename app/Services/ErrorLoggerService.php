<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class ErrorLoggerService
{
    /**
     * Registra un error o excepción en la base de datos.
     *
     * @param string $module Módulo donde ocurrió el error
     * @param string $message Mensaje del error
     * @param string $level Nivel (INFO, WARNING, ERROR, CRITICAL)
     * @param Throwable|null $exception Excepción capturada
     * @return ErrorLog|null
     */
    public static function log(string $module, string $message, string $level = 'ERROR', ?Throwable $exception = null): ?ErrorLog
    {
        try {
            $user = Auth::user();
            $trace = $exception ? $exception->getTraceAsString() : null;

            return ErrorLog::create([
                'level'      => strtoupper($level),
                'module'     => $module,
                'user_id'    => $user?->id,
                'user_name'  => $user?->name ?? 'Invitado / Sistema',
                'ip_address' => Request::ip(),
                'message'    => $message,
                'trace'      => $trace ? substr($trace, 0, 5000) : null,
            ]);
        } catch (Throwable $e) {
            logger()->error('Error guardando ErrorLog: ' . $e->getMessage());
            return null;
        }
    }
}
