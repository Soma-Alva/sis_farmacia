<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
          'codigo_barra',
    'nombre',
    'descripcion',
    'id_categoria',
    'id_proveedor',
    'concentracion',
    'presentacion',
    'stock_actual',
    'stock_minimo',
    'precio_compra',
    'precio_venta',
    'activo'
    ];

    public function categoria()
    {
        return $this->belongsTo(
            Categoria::class,
            'id_categoria',
            'id_categoria'
        );
    }

    public function proveedor()
    {
        return $this->belongsTo(
            Proveedor::class,
            'id_proveedor',
            'id_proveedor'
        );
    }
}
