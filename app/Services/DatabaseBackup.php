<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Respaldo y restauración de la base de datos MySQL/MariaDB escritos en
 * PHP puro (sin depender de mysqldump.exe ni de redirecciones de consola).
 *
 * Garantías de integridad:
 *  - El volcado se hace dentro de UNA transacción con snapshot consistente:
 *    aunque se esté vendiendo mientras se respalda, todas las tablas
 *    reflejan el mismo instante.
 *  - Por cada tabla se compara el COUNT(*) con las filas realmente
 *    escritas; si no coinciden, el respaldo se descarta.
 *  - Se escribe en un archivo temporal y solo se renombra al nombre final
 *    cuando está completo y verificado: nunca queda un .sql a medias o vacío.
 *  - Cada respaldo lleva un archivo .sha256 y una marca final de
 *    "respaldo completo"; antes de restaurar se verifican ambos.
 *  - Antes de restaurar se genera un respaldo de seguridad automático y, si
 *    la restauración falla a mitad, se vuelve a dejar la base como estaba.
 */
class DatabaseBackup
{
    private const MARCA_FINAL = '-- RESPALDO COMPLETO';
    private const LOTE_BYTES = 262144; // 256 KB por INSERT (cabe en max_allowed_packet)

    public static function carpeta(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear la carpeta de respaldos: ' . $dir);
        }
        return $dir;
    }

    /** Solo nombres simples tipo backup_xxx.sql; evita rutas como ../../.env */
    public static function nombreValido(string $nombre): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_\-]+\.sql$/', $nombre);
    }

    public static function rutaDe(string $nombre): ?string
    {
        $nombre = basename(str_replace('\\', '/', $nombre));
        if (!self::nombreValido($nombre)) {
            return null;
        }
        $ruta = self::carpeta() . DIRECTORY_SEPARATOR . $nombre;
        return is_file($ruta) ? $ruta : null;
    }

    /**
     * Crea un respaldo completo.
     *
     * @return array{archivo:string,ruta:string,bytes:int,tablas:int,filas:int}
     */
    public function crear(string $prefijo = 'backup'): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $conn = DB::connection();
        if (!in_array($conn->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('El respaldo solo está soportado para MySQL/MariaDB (conexión actual: ' . $conn->getDriverName() . ').');
        }

        $pdo = $conn->getPdo();
        $db = $conn->getDatabaseName();
        $nombre = $prefijo . '_' . date('Y_m_d_H_i_s') . '.sql';
        $final = self::carpeta() . DIRECTORY_SEPARATOR . $nombre;
        $tmp = $final . '.tmp';

        $fh = @fopen($tmp, 'wb');
        if (!$fh) {
            throw new RuntimeException('No se pudo escribir en la carpeta de respaldos. Revisa los permisos de storage/app/backups.');
        }

        $buffered = $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
        $enTransaccion = false;

        try {
            $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
            $enTransaccion = true;

            $this->w($fh, "-- Respaldo de la base de datos `{$db}`\n");
            $this->w($fh, '-- Generado: ' . date('Y-m-d H:i:s') . "\n");
            $this->w($fh, "-- Sistema: Grey Farmacia\n\n");
            $this->w($fh, "/*!40101 SET NAMES utf8mb4 */;\n");
            $this->w($fh, "SET FOREIGN_KEY_CHECKS=0;\n");
            $this->w($fh, "SET UNIQUE_CHECKS=0;\n");
            $this->w($fh, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
            $this->w($fh, "\n");

            $tablas = [];
            $vistas = [];
            foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as $f) {
                if (strtoupper($f[1]) === 'VIEW') {
                    $vistas[] = $f[0];
                } else {
                    $tablas[] = $f[0];
                }
            }

            $totalFilas = 0;
            foreach ($tablas as $t) {
                $totalFilas += $this->volcarTabla($pdo, $fh, $t);
            }

            foreach ($vistas as $v) {
                $row = $pdo->query('SHOW CREATE VIEW ' . $this->id($v))->fetch(PDO::FETCH_NUM);
                $sql = $this->sinDefiner($row[1] ?? '');
                if ($sql !== '') {
                    $this->w($fh, "-- Vista {$v}\n");
                    $this->w($fh, 'DROP VIEW IF EXISTS ' . $this->id($v) . ";\n");
                    $this->w($fh, $sql . ";\n\n");
                }
            }

            $this->volcarRutinasYTriggers($pdo, $fh, $db);

            $pdo->exec('COMMIT');
            $enTransaccion = false;

            $this->w($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
            $this->w($fh, "SET UNIQUE_CHECKS=1;\n\n");
            $this->w($fh, self::MARCA_FINAL . ' | tablas=' . count($tablas) . ' | filas=' . $totalFilas . ' | ' . date('Y-m-d H:i:s') . "\n");

            fflush($fh);
            fclose($fh);
            $fh = null;

            clearstatcache(true, $tmp);
            $bytes = (int) filesize($tmp);
            if ($bytes < 200) {
                throw new RuntimeException('El archivo generado está vacío o incompleto.');
            }

            if (!@rename($tmp, $final)) {
                throw new RuntimeException('No se pudo guardar el archivo final del respaldo.');
            }

            $hash = hash_file('sha256', $final);
            @file_put_contents($final . '.sha256', $hash . '  ' . $nombre . "\n");

            return [
                'archivo' => $nombre,
                'ruta' => $final,
                'bytes' => $bytes,
                'tablas' => count($tablas),
                'filas' => $totalFilas,
            ];
        } catch (Throwable $e) {
            if ($enTransaccion) {
                try { $pdo->exec('ROLLBACK'); } catch (Throwable $x) {}
            }
            if (is_resource($fh)) {
                fclose($fh);
            }
            @unlink($tmp);
            Log::error('Error al crear respaldo: ' . $e->getMessage());
            throw $e instanceof RuntimeException ? $e : new RuntimeException('No se pudo crear el respaldo: ' . $e->getMessage(), 0, $e);
        } finally {
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered);
        }
    }

    /** Escribe estructura + datos de una tabla y devuelve cuántas filas escribió. */
    private function volcarTabla(PDO $pdo, $fh, string $tabla): int
    {
        $q = $this->id($tabla);
        $row = $pdo->query('SHOW CREATE TABLE ' . $q)->fetch(PDO::FETCH_NUM);

        $this->w($fh, "-- Tabla {$tabla}\n");
        $this->w($fh, "DROP TABLE IF EXISTS {$q};\n");
        $this->w($fh, $row[1] . ";\n\n");

        $esperadas = (int) $pdo->query("SELECT COUNT(*) FROM {$q}")->fetchColumn();
        if ($esperadas === 0) {
            return 0;
        }

        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $pdo->query("SELECT * FROM {$q}", PDO::FETCH_NUM);

        $cols = [];
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $cols[] = $this->id($stmt->getColumnMeta($i)['name']);
        }
        $cabecera = "INSERT INTO {$q} (" . implode(', ', $cols) . ") VALUES\n";

        $escritas = 0;
        $lote = [];
        $bytes = 0;
        while (($fila = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
            $vals = [];
            foreach ($fila as $v) {
                $vals[] = $this->valor($pdo, $v);
            }
            $linea = '(' . implode(', ', $vals) . ')';
            $lote[] = $linea;
            $bytes += strlen($linea);
            $escritas++;

            if ($bytes >= self::LOTE_BYTES) {
                $this->w($fh, $cabecera . implode(",\n", $lote) . ";\n");
                $lote = [];
                $bytes = 0;
            }
        }
        $stmt->closeCursor();
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        if ($lote) {
            $this->w($fh, $cabecera . implode(",\n", $lote) . ";\n");
        }
        $this->w($fh, "\n");

        if ($escritas !== $esperadas) {
            throw new RuntimeException("Verificación fallida en la tabla {$tabla}: esperaba {$esperadas} filas y se escribieron {$escritas}.");
        }

        return $escritas;
    }

    private function volcarRutinasYTriggers(PDO $pdo, $fh, string $db): void
    {
        $bloques = [];

        foreach (['PROCEDURE' => 2, 'FUNCTION' => 2] as $tipo => $idx) {
            $lista = $pdo->query("SHOW {$tipo} STATUS WHERE Db = " . $pdo->quote($db))->fetchAll(PDO::FETCH_NUM);
            foreach ($lista as $r) {
                $c = $pdo->query("SHOW CREATE {$tipo} " . $this->id($r[1]))->fetch(PDO::FETCH_NUM);
                if (!empty($c[2])) {
                    $bloques[] = "DROP {$tipo} IF EXISTS " . $this->id($r[1]) . ";;\n" . $this->sinDefiner($c[2]) . ";;\n";
                }
            }
        }

        foreach ($pdo->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_NUM) as $r) {
            $c = $pdo->query('SHOW CREATE TRIGGER ' . $this->id($r[0]))->fetch(PDO::FETCH_NUM);
            if (!empty($c[2])) {
                $bloques[] = 'DROP TRIGGER IF EXISTS ' . $this->id($r[0]) . ";;\n" . $this->sinDefiner($c[2]) . ";;\n";
            }
        }

        if ($bloques) {
            $this->w($fh, "-- Rutinas y triggers\nDELIMITER ;;\n" . implode("\n", $bloques) . "DELIMITER ;\n\n");
        }
    }

    /**
     * Verifica que un respaldo esté completo y sin alteraciones.
     *
     * @throws RuntimeException
     */
    public function verificar(string $ruta): void
    {
        if (!is_file($ruta) || filesize($ruta) < 200) {
            throw new RuntimeException('El archivo de respaldo está vacío o no existe.');
        }

        $size = filesize($ruta);
        $fh = fopen($ruta, 'rb');
        fseek($fh, max(0, $size - 400));
        $cola = fread($fh, 400);
        fclose($fh);

        if (strpos($cola, self::MARCA_FINAL) === false) {
            throw new RuntimeException('El respaldo está incompleto (no tiene la marca final). No se usará.');
        }

        $sidecar = $ruta . '.sha256';
        if (is_file($sidecar)) {
            $esperado = strtok(trim((string) file_get_contents($sidecar)), ' ');
            if ($esperado && !hash_equals($esperado, hash_file('sha256', $ruta))) {
                throw new RuntimeException('El respaldo fue modificado o está dañado (la firma SHA-256 no coincide).');
            }
        }
    }

    /**
     * Restaura un respaldo. Antes hace un respaldo de seguridad del estado
     * actual y, si algo falla, lo vuelve a cargar.
     *
     * @return array{statements:int,seguridad:string}
     */
    public function restaurar(string $ruta): array
    {
        @set_time_limit(0);
        @ignore_user_abort(true);

        $this->verificar($ruta);

        $seguridad = $this->crear('prerestore');

        try {
            $n = $this->ejecutarArchivo($ruta);
        } catch (Throwable $e) {
            Log::error('Error al restaurar respaldo: ' . $e->getMessage());
            try {
                $this->ejecutarArchivo($seguridad['ruta']);
                throw new RuntimeException('La restauración falló y la base de datos se dejó como estaba. Detalle: ' . $e->getMessage(), 0, $e);
            } catch (RuntimeException $r) {
                throw $r;
            } catch (Throwable $r) {
                throw new RuntimeException('La restauración falló y no se pudo volver al estado anterior automáticamente. Tu copia de seguridad previa es: ' . $seguridad['archivo'] . '. Detalle: ' . $e->getMessage(), 0, $e);
            }
        }

        return ['statements' => $n, 'seguridad' => $seguridad['archivo']];
    }

    private function ejecutarArchivo(string $ruta): int
    {
        $pdo = DB::connection()->getPdo();
        $fh = fopen($ruta, 'rb');
        if (!$fh) {
            throw new RuntimeException('No se pudo abrir el archivo de respaldo.');
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $n = 0;
        try {
            foreach (self::sentencias($fh) as $sql) {
                $pdo->exec($sql);
                $n++;
            }
        } finally {
            fclose($fh);
            try { $pdo->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (Throwable $e) {}
        }
        return $n;
    }

    /**
     * Separa un volcado SQL en sentencias, respetando comillas, comentarios
     * y bloques DELIMITER (triggers/rutinas).
     *
     * @param resource $fh
     * @return \Generator<string>
     */
    public static function sentencias($fh): \Generator
    {
        $delim = ';';
        $buf = '';
        $quote = null;   // ' " ` mientras estamos dentro de un literal
        $bloque = false; // dentro de /* ... */

        while (($linea = fgets($fh)) !== false) {
            if ($quote === null && !$bloque && $buf === '') {
                $t = trim($linea);
                if ($t === '' || str_starts_with($t, '--') || str_starts_with($t, '#')) {
                    continue;
                }
                if (preg_match('/^DELIMITER\s+(\S+)\s*$/i', $t, $m)) {
                    $delim = $m[1];
                    continue;
                }
            }

            $len = strlen($linea);
            for ($i = 0; $i < $len; $i++) {
                $c = $linea[$i];
                if ($quote !== null) {
                    if ($c === '\\' && $quote !== '`') { $i++; continue; }
                    if ($c === $quote) {
                        if (($linea[$i + 1] ?? '') === $quote) { $i++; } else { $quote = null; }
                    }
                } elseif ($bloque) {
                    if ($c === '*' && ($linea[$i + 1] ?? '') === '/') { $bloque = false; $i++; }
                } elseif ($c === '\'' || $c === '"' || $c === '`') {
                    $quote = $c;
                } elseif ($c === '/' && ($linea[$i + 1] ?? '') === '*' && ($linea[$i + 2] ?? '') !== '!') {
                    $bloque = true; $i++;
                }
            }

            $buf .= $linea;

            if ($quote === null && !$bloque) {
                $r = rtrim($buf);
                if (str_ends_with($r, $delim)) {
                    $sql = trim(substr($r, 0, -strlen($delim)));
                    $buf = '';
                    if ($sql !== '') {
                        yield $sql;
                    }
                }
            }
        }

        if (trim($buf) !== '') {
            throw new RuntimeException('El archivo termina con una sentencia incompleta.');
        }
    }

    /** Aplica retención: conserva solo los últimos $max respaldos automáticos. */
    public function limpiarAutomaticos(int $max = 30): int
    {
        $archivos = glob(self::carpeta() . DIRECTORY_SEPARATOR . 'backup_auto_*.sql') ?: [];
        rsort($archivos);
        $borrados = 0;
        foreach (array_slice($archivos, $max) as $f) {
            if (@unlink($f)) {
                @unlink($f . '.sha256');
                $borrados++;
            }
        }
        return $borrados;
    }

    private function valor(PDO $pdo, mixed $v): string
    {
        if ($v === null) {
            return 'NULL';
        }
        if (is_int($v)) {
            return (string) $v;
        }
        if (is_float($v)) {
            return is_finite($v) ? var_export($v, true) : 'NULL';
        }
        if ($v === '') {
            return "''";
        }
        if (!mb_check_encoding($v, 'UTF-8')) {
            return '0x' . bin2hex($v); // datos binarios (BLOB, etc.)
        }
        return $pdo->quote($v);
    }

    private function id(string $nombre): string
    {
        return '`' . str_replace('`', '``', $nombre) . '`';
    }

    private function sinDefiner(string $sql): string
    {
        return preg_replace('/\sDEFINER\s*=\s*`[^`]*`@`[^`]*`/i', '', $sql) ?? $sql;
    }

    private function w($fh, string $texto): void
    {
        if (fwrite($fh, $texto) === false) {
            throw new RuntimeException('No se pudo escribir en el disco (¿disco lleno?).');
        }
    }
}
