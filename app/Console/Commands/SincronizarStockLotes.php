<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Models\Lote;
use Illuminate\Console\Command;

/**
 * Comando de mantenimiento (uso puntual): recalcula
 * productos.stock_actual como la suma real de sus lotes activos.
 *
 * Necesario para corregir productos cuyo stock_actual quedó
 * "inflado" o desincronizado por haberse editado a mano antes de que
 * el campo se volviera de solo lectura.
 *
 * Uso:
 *   php artisan stock:sincronizar          (aplica los cambios)
 *   php artisan stock:sincronizar --dry-run (solo muestra qué cambiaría)
 */
class SincronizarStockLotes extends Command
{
    protected $signature = 'stock:sincronizar {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    protected $description = 'Recalcula productos.stock_actual como la suma de sus lotes activos';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $productos = Producto::all();
        $corregidos = 0;

        $this->table(
            ['Producto', 'Stock actual (antes)', 'Suma de lotes activos', 'Diferencia'],
            $productos->map(function ($producto) use (&$corregidos, $dryRun) {

                $stockReal = Lote::where('id_producto', $producto->id_producto)
                    ->where('estado', 'ACTIVO')
                    ->sum('cantidad_actual');

                $diferencia = $stockReal - $producto->stock_actual;

                if ($diferencia != 0) {
                    $corregidos++;
                    if (!$dryRun) {
                        $producto->update(['stock_actual' => $stockReal]);
                    }
                }

                return [
                    $producto->nombre,
                    $producto->stock_actual,
                    $stockReal,
                    $diferencia == 0 ? '—' : ($diferencia > 0 ? "+{$diferencia}" : $diferencia),
                ];
            })->filter(fn ($fila) => $fila[3] !== '—')->values()->toArray()
        );

        if ($corregidos === 0) {
            $this->info('Todos los productos ya estaban sincronizados. Nada que corregir.');
            return;
        }

        if ($dryRun) {
            $this->warn("{$corregidos} producto(s) tienen diferencias. Corre sin --dry-run para corregirlos.");
        } else {
            $this->info("{$corregidos} producto(s) corregido(s) correctamente.");
        }
    }
}
