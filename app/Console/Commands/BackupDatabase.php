<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--manual : Ejecutar respaldo manual desde consola}';
    protected $description = 'Crea un respaldo de la base de datos';

    public function handle()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        if (empty($database) || empty($username)) {
            $this->error('Configuración de base de datos incompleta.');
            return 1;
        }

        $fileName = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
        $folder = 'backups';
        $fullPath = storage_path('app/' . $folder . '/' . $fileName);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        // Usar ruta específica de XAMPP
        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump';
        }

        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --port=%s %s > "%s" 2>nul',
            $mysqldumpPath,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            $fullPath
        );

        $this->info('Ejecutando respaldo...');
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($fullPath) || filesize($fullPath) === 0) {
            $this->error('Error al crear el respaldo.');
            return 1;
        }

        $fileSize = number_format(filesize($fullPath) / 1024 / 1024, 2);
        $this->info("✓ Respaldo creado exitosamente: {$fileName} ({$fileSize} MB)");
        return 0;
    }
}
