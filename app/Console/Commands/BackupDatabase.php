<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupController;
use App\Services\DatabaseBackup;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--manual : Ejecutar respaldo manual desde consola} {--auto : Respaldo programado (aplica retención de 30 copias automáticas)}';
    protected $description = 'Crea un respaldo verificado de la base de datos';

    public function handle(DatabaseBackup $backup)
    {
        $this->info('Ejecutando respaldo...');

        try {
            $r = $backup->crear($this->option('auto') ? 'backup_auto' : 'backup');
        } catch (\Throwable $e) {
            $this->error('Error al crear el respaldo: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info(sprintf(
            '✓ Respaldo creado y verificado: %s (%s, %d tablas, %d filas)',
            $r['archivo'],
            BackupController::formatearTamano($r['bytes']),
            $r['tablas'],
            $r['filas']
        ));

        if ($this->option('auto')) {
            $borrados = $backup->limpiarAutomaticos(30);
            if ($borrados > 0) {
                $this->line("Se eliminaron {$borrados} respaldos automáticos antiguos.");
            }
        }

        return self::SUCCESS;
    }
}
