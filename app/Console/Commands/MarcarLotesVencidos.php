<?php

namespace App\Console\Commands;

use App\Models\Lote;
use Illuminate\Console\Command;

/**
 * Marca como VENCIDO cualquier lote ACTIVO cuya fecha de vencimiento
 * ya pasó. No toca el stock (eso lo decide el farmacéutico con la
 * acción "Dar de baja" en la ficha del lote) — solo actualiza el
 * estado, que es lo que hace que deje de aparecer como disponible en
 * el buscador de Lotes y en los reportes.
 *
 * Pensado para correr diario vía el scheduler de Laravel
 * (routes/console.php):
 *   Schedule::command('lotes:marcar-vencidos')->daily();
 */
class MarcarLotesVencidos extends Command
{
    protected $signature = 'lotes:marcar-vencidos';

    protected $description = 'Marca como VENCIDO los lotes activos cuya fecha de vencimiento ya pasó';

    public function handle()
    {
        $lotes = Lote::where('estado', 'ACTIVO')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->get();

        if ($lotes->isEmpty()) {
            $this->info('No hay lotes nuevos por marcar como vencidos.');
            return;
        }

        foreach ($lotes as $lote) {
            $lote->update(['estado' => 'VENCIDO']);
        }

        $this->info("{$lotes->count()} lote(s) marcado(s) como VENCIDO.");
    }
}
