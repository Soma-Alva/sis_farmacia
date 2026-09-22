<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    // IMPORTANTE
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'nombre_completo',
        'id_rol'
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function rol()
    {
    return $this->belongsTo(\App\Models\Rol::class, 'id_rol', 'id_rol');
    }


}
