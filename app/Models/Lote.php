<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_producto',
        'id_compra',
        'numero_lote',
        'fecha_vencimiento',
        'cantidad_inicial',
        'cantidad_actual',
        'costo_unitario',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    /**
     * Lotes disponibles de un producto, ordenados FEFO
     * (First Expired, First Out): el que vence más pronto primero.
     * Los lotes sin fecha de vencimiento (ej. los "INICIAL-*" del
     * backfill) van al final, porque no sabemos si están por vencer.
     */
    public function scopeDisponiblesFefo($query, int $idProducto)
    {
        return $query
            ->where('id_producto', $idProducto)
            ->where('estado', 'ACTIVO')
            ->where('cantidad_actual', '>', 0)
            ->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento ASC');
    }

    /**
     * Lotes próximos a vencer, para las alertas del dashboard.
     */
    public function scopeProximosAVencer($query, int $dias = 90)
    {
        return $query
            ->where('estado', 'ACTIVO')
            ->where('cantidad_actual', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias));
    }
}
