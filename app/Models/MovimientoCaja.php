<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';

    protected $primaryKey = 'id_movimiento';

    public $timestamps = false;

    protected $fillable = [
        'id_caja',
        'id_venta',
        'id_compra',
        'tipo_movimiento',
        'monto',
        'descripcion',
        'saldo_anterior',
        'saldo_actual',
        'id_usuario'
    ];
}