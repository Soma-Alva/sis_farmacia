<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function index()
    {
        $backupFolder = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupFolder)) {
            $files = scandir($backupFolder, SCANDIR_SORT_DESCENDING);
            $backups = array_filter($files, function ($file) {
                return str_ends_with($file, '.sql');
            });
        }

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        if (empty($database) || empty($username)) {
            return back()->with('error', 'No se pudo crear el respaldo porque la configuración de la base de datos no está completa.');
        }

        $fileName = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
        $folder = 'backups';
        $fullPath = storage_path('app/' . $folder . '/' . $fileName);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        // Intentar usar mysqldump con rutas específicas para Windows
        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump';
        }

        // Construir comando para Windows
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

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($fullPath) || filesize($fullPath) === 0) {
            \Log::error('Backup error', [
                'returnVar' => $returnVar,
                'command' => $command,
                'fileExists' => file_exists($fullPath),
                'fileSize' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'output' => implode("\n", $output)
            ]);
            return back()->with('error', 'No se pudo crear el respaldo. Verifica que MySQL esté disponible.');
        }

        return back()->with('success', 'Respaldo creado correctamente en la carpeta de almacenamiento.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
        ]);

        $file = $request->input('file');
        $fullPath = storage_path('app/' . $file);

        if (!file_exists($fullPath)) {
            return back()->with('error', 'El archivo de respaldo no existe.');
        }

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        // Intentar usar mysql con rutas específicas para Windows
        $mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysql';
        }

        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --port=%s %s < "%s" 2>nul',
            $mysqlPath,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            $fullPath
        );

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            \Log::error('Restore error', [
                'returnVar' => $returnVar,
                'command' => $command,
                'file' => $file,
                'output' => implode("\n", $output)
            ]);
            return back()->with('error', 'No se pudo restaurar la base de datos.');
        }

        return back()->with('success', 'Base de datos restaurada correctamente.');
    }

    public function download($file)
    {
        $path = storage_path('app/backups/' . basename($file));

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }
}
