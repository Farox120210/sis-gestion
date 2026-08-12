<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ErrorLog;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\AuditLoggerService;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AuditoriaController extends Controller
{
    public function __construct()
    {
        // El módulo de auditoría es exclusivo para super_admin
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->rol !== 'super_admin') {
                abort(403, 'Acceso restringido. Solo los usuarios con rol Super Admin pueden acceder al módulo de Auditoría y Sistema.');
            }
            return $next($request);
        });
    }

    /**
     * Bitácora de Acciones de Usuario
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('rol')) {
            $query->where('user_role', $request->rol);
        }

        if ($request->filled('usuario')) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', '%' . $request->usuario . '%')
                  ->orWhere('user_id', $request->usuario);
            });
        }

        if ($request->filled('modulo')) {
            $query->where('module', $request->modulo);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        if ($request->filled('q')) {
            $qStr = '%' . $request->q . '%';
            $query->where(function ($q) use ($qStr) {
                $q->where('description', 'like', $qStr)
                  ->orWhere('action', 'like', $qStr)
                  ->orWhere('user_name', 'like', $qStr)
                  ->orWhere('ip_address', 'like', $qStr);
            });
        }

        $logs = $query->paginate(20)->withQueryString();
        $modulos = AuditLog::select('module')->distinct()->pluck('module');
        $usuarios = User::select('id', 'name', 'email', 'rol')->get();

        return view('admin.auditoria.index', compact('logs', 'modulos', 'usuarios'));
    }

    /**
     * Log de Errores del Sistema
     */
    public function errores(Request $request)
    {
        $query = ErrorLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('nivel')) {
            $query->where('level', strtoupper($request->nivel));
        }

        if ($request->filled('modulo')) {
            $query->where('module', 'like', '%' . $request->modulo . '%');
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        if ($request->filled('q')) {
            $qStr = '%' . $request->q . '%';
            $query->where(function ($q) use ($qStr) {
                $q->where('message', 'like', $qStr)
                  ->orWhere('trace', 'like', $qStr)
                  ->orWhere('user_name', 'like', $qStr);
            });
        }

        $errores = $query->paginate(20)->withQueryString();

        return view('admin.auditoria.errores', compact('errores'));
    }

    /**
     * Estado del Sistema y Respaldos
     */
    public function sistema()
    {
        // 1. Información del sistema
        $systemVersion = 'v1.2.0';
        $laravelVersion = app()->version();
        $phpVersion = PHP_VERSION;

        $mysqlVersion = 'Desconocido';
        $dbSizeMb = 0;
        try {
            $verResult = DB::select('SELECT VERSION() as ver');
            $mysqlVersion = $verResult[0]->ver ?? 'MySQL';

            $dbName = config('database.connections.mysql.database');
            $sizeResult = DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            $dbSizeMb = round($sizeResult[0]->size_mb ?? 0, 2);
        } catch (\Throwable $e) {
            // Ignorar errores de métricas
        }

        // 2. Estado de Google Drive
        $gdriveClientId = env('GOOGLE_DRIVE_CLIENT_ID');
        $gdriveClientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');
        $gdriveRefreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');
        $gdriveActive = (!empty($gdriveClientId) && !empty($gdriveClientSecret) && !empty($gdriveRefreshToken));

        // 3. Historial de respaldos
        $backups = SystemBackup::orderBy('created_at', 'desc')->get();
        $lastBackup = $backups->first();

        return view('admin.auditoria.sistema', compact(
            'systemVersion',
            'laravelVersion',
            'phpVersion',
            'mysqlVersion',
            'dbSizeMb',
            'gdriveActive',
            'backups',
            'lastBackup'
        ));
    }

    /**
     * Dispara la copia de seguridad inmediatamente
     */
    public function runBackup(BackupService $backupService)
    {
        $backup = $backupService->runBackup();

        if ($backup->status === 'success') {
            return redirect()->route('admin.auditoria.sistema')
                ->with('ok', "Copia de seguridad '{$backup->filename}' generada correctamente. Tamaño: " . round($backup->file_size / 1024 / 1024, 2) . " MB.");
        } else {
            return redirect()->route('admin.auditoria.sistema')
                ->with('error', "Error al generar respaldo: " . $backup->error_message);
        }
    }

    /**
     * Descarga local del archivo .sql de respaldo
     */
    public function downloadBackup($id)
    {
        $backup = SystemBackup::findOrFail($id);

        if (!file_exists($backup->file_path)) {
            return back()->with('error', 'El archivo físico de respaldo no se encuentra en el servidor.');
        }

        AuditLoggerService::log('Sistema', 'DESCARGAR_RESPALDO', "Descargó la copia de seguridad: {$backup->filename}");

        return response()->download($backup->file_path, $backup->filename);
    }

    /**
     * Elimina una copia de seguridad local y su registro
     */
    public function deleteBackup($id)
    {
        $backup = SystemBackup::findOrFail($id);

        if (file_exists($backup->file_path)) {
            @unlink($backup->file_path);
        }

        $filename = $backup->filename;
        $backup->delete();

        AuditLoggerService::log('Sistema', 'ELIMINAR_RESPALDO', "Eliminó la copia de seguridad: {$filename}");

        return redirect()->route('admin.auditoria.sistema')
            ->with('ok', "Copia de seguridad '{$filename}' eliminada correctamente.");
    }

    /**
     * Guarda la configuración de Google Drive en el archivo .env
     */
    public function saveGoogleDriveConfig(Request $request)
    {
        $request->validate([
            'client_id'     => 'nullable|string',
            'client_secret' => 'nullable|string',
            'refresh_token' => 'nullable|string',
            'folder_id'     => 'nullable|string',
        ]);

        $this->updateEnvFile([
            'GOOGLE_DRIVE_CLIENT_ID'     => $request->client_id ?? '',
            'GOOGLE_DRIVE_CLIENT_SECRET' => $request->client_secret ?? '',
            'GOOGLE_DRIVE_REFRESH_TOKEN' => $request->refresh_token ?? '',
            'GOOGLE_DRIVE_FOLDER_ID'     => $request->folder_id ?? '',
        ]);

        AuditLoggerService::log('Sistema', 'CONFIG_GDRIVE', "Actualizó las credenciales de sincronización con Google Drive.");

        return redirect()->route('admin.auditoria.sistema')
            ->with('ok', 'Configuración de Google Drive actualizada correctamente.');
    }

    /**
     * Escribe/Actualiza variables en el archivo .env
     */
    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $keyPattern = "/^{$key}=.*/m";
            if (preg_match($keyPattern, $envContent)) {
                $envContent = preg_replace($keyPattern, "{$key}=\"{$value}\"", $envContent);
            } else {
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
