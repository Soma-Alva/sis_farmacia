<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CompraRepository
{
    public function getAll()
    {
        return DB::table('compras as c')
            ->leftJoin(
                'proveedores as p',
                'p.id_proveedor',
                '=',
                'c.id_proveedor'
            )
            ->select(
                'c.*',
                'p.nombre as proveedor'
            )
            ->orderBy('c.id_compra', 'desc')
            ->get();
    }
}