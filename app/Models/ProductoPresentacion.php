<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoPresentacion extends Model
{
    protected $table = 'producto_presentaciones';
    protected $primaryKey = 'id_presentacion';
    public $timestamps = false;

    protected $fillable = ['id_producto', 'nombre', 'unidades', 'precio_venta', 'activo'];
}
