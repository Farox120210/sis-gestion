<?php

namespace App\Services;

use App\Models\SystemBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    /**
     * Realiza el respaldo completo de la base de datos local y opcionalmente lo sube a Google Drive.
     *
     * @return SystemBackup
     */
    public function runBackup(): SystemBackup
    {
        $dbName = config('database.connections.mysql.database');
        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "sisgestion_backup_{$dbName}_{$timestamp}.sql";
        $directory = storage_path('app/backups');

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;
        $status = 'failed';
        $errorMessage = null;

        try {
            // Intentar respaldar primero con mysqldump
            $mysqldumpPath = $this->findMysqldumpPath();
            
            if ($mysqldumpPath) {
                $passFlag = $dbPass ? "-p\"{$dbPass}\"" : "";
                $cmd = "\"{$mysqldumpPath}\" --host={$dbHost} --port={$dbPort} --user={$dbUser} {$passFlag} {$dbName} > \"{$filePath}\"";
                exec($cmd, $output, $returnVar);

                if ($returnVar !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
                    $this->exportViaPdo($filePath);
                }
            } else {
                $this->exportViaPdo($filePath);
            }

            if (file_exists($filePath) && filesize($filePath) > 0) {
                $status = 'success';
            } else {
                throw new \Exception('No se pudo generar el archivo de copia de seguridad .sql');
            }
        } catch (Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            ErrorLoggerService::log('Sistema', 'Fallo al respaldar BD: ' . $e->getMessage(), 'CRITICAL', $e);
        }

        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        // Intentar subir a Google Drive si las credenciales están configuradas
        $gdriveResult = $this->uploadToGoogleDrive($filePath, $filename);

        $backup = SystemBackup::create([
            'filename'       => $filename,
            'file_path'      => $filePath,
            'file_size'      => $fileSize,
            'status'         => $status,
            'gdrive_status'  => $gdriveResult['status'],
            'gdrive_file_id' => $gdriveResult['file_id'],
            'error_message'  => $errorMessage ?? $gdriveResult['error'],
        ]);

        if ($status === 'success') {
            AuditLoggerService::log('Sistema', 'RESPALDO', "Respaldo de BD generado exitosamente ({$filename}, " . round($fileSize / 1024 / 1024, 2) . " MB)");
        }

        return $backup;
    }

    /**
     * Busca la ruta de mysqldump en Windows/Laragon o el PATH del sistema.
     */
    private function findMysqldumpPath(): ?string
    {
        $possiblePaths = [
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mariadb-10.4.19-winx64\\bin\\mysqldump.exe',
            'mysqldump',
        ];

        // Intentar buscar en laragon de forma dinámica
        if (is_dir('C:\\laragon\\bin\\mysql')) {
            $dirs = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe');
            if (!empty($dirs)) {
                return $dirs[0];
            }
        }

        foreach ($possiblePaths as $path) {
            if ($path === 'mysqldump') return 'mysqldump';
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Exportación de la base de datos mediante PDO como alternativa nativa en PHP.
     */
    private function exportViaPdo(string $outputPath): void
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = "Tables_in_" . $dbName;

        $handle = fopen($outputPath, 'w+');
        fwrite($handle, "-- RESPALDO DE BD SIS-GESTION\n-- Fecha: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$keyName ?? array_values((array)$tableObj)[0];

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTable[0]->{'Create Table'} ?? null;

            if ($createSql) {
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n{$createSql};\n\n");
            }

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    if (is_null($val)) return 'NULL';
                    return DB::connection()->getPdo()->quote($val);
                }, (array)$row);

                $sql = "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n";
                fwrite($handle, $sql);
            }
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    /**
     * Sube el archivo a Google Drive usando las credenciales en .env o configuración.
     */
    public function uploadToGoogleDrive(string $filePath, string $filename): array
    {
        $clientId = config('services.google_drive.client_id', env('GOOGLE_DRIVE_CLIENT_ID'));
        $clientSecret = config('services.google_drive.client_secret', env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $refreshToken = config('services.google_drive.refresh_token', env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $folderId = config('services.google_drive.folder_id', env('GOOGLE_DRIVE_FOLDER_ID'));

        if (!$clientId || !$clientSecret || !$refreshToken) {
            return [
                'status'  => 'not_configured',
                'file_id' => null,
                'error'   => 'Sincronización con Google Drive pendiente de configuración (faltan credenciales en .env)',
            ];
        }

        if (!file_exists($filePath) || filesize($filePath) === 0) {
            return [
                'status'  => 'failed',
                'file_id' => null,
                'error'   => 'El archivo local de respaldo no existe o está vacío',
            ];
        }

        try {
            // Obtener Access Token mediante Refresh Token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ]);

            if ($tokenResponse->failed()) {
                return [
                    'status'  => 'failed',
                    'file_id' => null,
                    'error'   => 'Error obteniendo Access Token de Google Drive: ' . $tokenResponse->body(),
                ];
            }

            $accessToken = $tokenResponse->json()['access_token'] ?? null;

            if (!$accessToken) {
                return [
                    'status'  => 'failed',
                    'file_id' => null,
                    'error'   => 'Access Token de Google Drive no recibido',
                ];
            }

            // Subir archivo a Google Drive (Multipart Upload)
            $metadata = ['name' => $filename];
            if ($folderId) {
                $metadata['parents'] = [$folderId];
            }

            $uploadResponse = Http::withToken($accessToken)
                ->attach('metadata', json_encode($metadata), 'metadata.json', ['Content-Type' => 'application/json; charset=UTF-8'])
                ->attach('file', file_get_contents($filePath), $filename, ['Content-Type' => 'application/x-sql'])
                ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

            if ($uploadResponse->successful()) {
                $driveFile = $uploadResponse->json();
                return [
                    'status'  => 'uploaded',
                    'file_id' => $driveFile['id'] ?? null,
                    'error'   => null,
                ];
            } else {
                return [
                    'status'  => 'failed',
                    'file_id' => null,
                    'error'   => 'Error subiendo archivo a Google Drive: ' . $uploadResponse->body(),
                ];
            }
        } catch (Throwable $e) {
            return [
                'status'  => 'failed',
                'file_id' => null,
                'error'   => 'Excepción en subida a Google Drive: ' . $e->getMessage(),
            ];
        }
    }
}
