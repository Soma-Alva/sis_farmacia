<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Datos iniciales para una instalación nueva: roles y el primer
 * usuario administrador. Se corre una sola vez después de migrate.
 */
class InstalarFarmacia extends Command
{
    protected $signature = 'farmacia:instalar';
    protected $description = 'Crea los roles iniciales y el primer usuario administrador';

    public function handle(): int
    {
        if (DB::table('usuarios')->exists()) {
            $this->error('Ya existen usuarios en la base de datos. No se hace nada.');
            return self::FAILURE;
        }

        foreach ([
            'ADMINISTRADOR' => 'Acceso total al sistema, reportes y configuración',
            'CAJERO' => 'Ventas, caja e inventario',
        ] as $nombre => $desc) {
            if (!DB::table('roles')->where('nombre', $nombre)->exists()) {
                DB::table('roles')->insert(['nombre' => $nombre, 'descripcion' => $desc, 'activo' => 1]);
            }
        }

        $nombre = $this->ask('Nombre completo del administrador');
        $username = $this->ask('Nombre de usuario (para iniciar sesión)', 'admin');
        $email = $this->ask('Correo electrónico');
        $password = $this->secret('Contraseña (mínimo 8 caracteres)');
        $confirm = $this->secret('Repite la contraseña');

        if (!$nombre || !$username || !$email || strlen((string) $password) < 8 || $password !== $confirm) {
            $this->error('Datos incompletos, contraseña muy corta o no coincide. Vuelve a ejecutar el comando.');
            return self::FAILURE;
        }

        DB::table('usuarios')->insert([
            'username' => $username,
            'email' => $email,
            'password_hash' => Hash::make($password),
            'nombre_completo' => $nombre,
            'id_rol' => DB::table('roles')->where('nombre', 'ADMINISTRADOR')->value('id_rol'),
            'activo' => 1,
        ]);

        $this->info("Listo. Ya puedes iniciar sesión con el usuario \"$username\".");
        return self::SUCCESS;
    }
}
