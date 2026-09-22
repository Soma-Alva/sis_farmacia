<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja';

    protected $primaryKey = 'id_caja';

    protected $fillable = [

        'fecha_apertura',

        'hora_apertura',

        'fecha_cierre',

        'hora_cierre',

        'id_usuario_apertura',

        'id_usuario_cierre',

        'saldo_inicial',

        'saldo_final',

        'tipo_cambio',

        'total_ingresos',

        'total_egresos',

        'diferencia',

        'estado',

        'observaciones'

    ];

    public $timestamps = false;
}