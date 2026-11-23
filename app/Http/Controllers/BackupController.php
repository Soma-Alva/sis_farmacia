<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function index()
    {
        $backupFolder = storage_path('app/backups');
        $archivos = [];

        if (is_dir($backupFolder)) {
            $files = scandir($backupFolder, SCANDIR_SORT_DESCENDING);
            $archivos = array_values(array_filter($files, function ($file) {
                return DatabaseBackup::nombreValido($file);
            }));
        }

        // $backups no viene de una consulta a la base de datos (son
        // archivos del disco), así que armamos el paginador a mano,
        // cortando el arreglo completo a los 15 que tocan según la
        // página actual, igual que en el resto de listados.
        $porPagina = 15;
        $paginaActual = Paginator::resolveCurrentPage('page');

        $backups = new LengthAwarePaginator(
            array_slice($archivos, ($paginaActual - 1) * $porPagina, $porPagina),
            count($archivos),
            $porPagina,
            $paginaActual,
            ['path' => Paginator::resolveCurrentPath()]
        );

        // El tamaño total se calcula sobre TODOS los respaldos, no solo
        // los 15 de la página actual, para que la tarjeta de resumen
        // sea correcta sin importar en qué página esté el usuario.
        $tamanoTotal = 0;
        foreach ($archivos as $archivo) {
            $ruta = $backupFolder . DIRECTORY_SEPARATOR . $archivo;
            if (file_exists($ruta)) {
                $tamanoTotal += filesize($ruta);
            }
        }

        return view('backups.index', compact('backups', 'tamanoTotal'));
    }

    public function create(DatabaseBackup $backup)
    {
        try {
            $r = $backup->crear('backup');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo crear el respaldo: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Respaldo creado y verificado: %s (%s, %d tablas, %s filas).',
            $r['archivo'],
            self::formatearTamano($r['bytes']),
            $r['tablas'],
            number_format($r['filas'])
        ));
    }

    public function restore(Request $request, DatabaseBackup $backup)
    {
        $request->validate(['file' => 'required|string']);

        $ruta = DatabaseBackup::rutaDe($request->input('file'));
        if (!$ruta) {
            return back()->with('error', 'El archivo de respaldo no existe o su nombre no es válido.');
        }

        try {
            $r = $backup->restaurar($ruta);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Base de datos restaurada correctamente. Antes de restaurar se guardó una copia de seguridad del estado anterior: ' . $r['seguridad']);
    }

    public function download($file)
    {
        $path = DatabaseBackup::rutaDe($file);

        if (!$path) {
            abort(404);
        }

        return response()->download($path);
    }

    public static function formatearTamano(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
