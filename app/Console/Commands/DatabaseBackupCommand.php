<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo de la base de datos MySQL local y lo sincroniza con Google Drive';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $this->info('Iniciando copia de seguridad de la base de datos...');

        $backup = $backupService->runBackup();

        if ($backup->status === 'success') {
            $this->info("¡Respaldo generado con éxito! Archivo: {$backup->filename}");
            $this->info("Google Drive status: {$backup->gdrive_status}");
            return Command::SUCCESS;
        } else {
            $this->error("Falló la copia de seguridad: {$backup->error_message}");
            return Command::FAILURE;
        }
    }
}
