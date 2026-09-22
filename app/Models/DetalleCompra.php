<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];
}