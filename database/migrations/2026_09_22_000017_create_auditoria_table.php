<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->increments('id_auditoria');
            $table->unsignedInteger('id_usuario');
            $table->string('tabla_afectada', 100);
            $table->string('accion', 20);
            $table->integer('registro_id')->nullable();
            $table->longText('datos_anteriores')->nullable();
            $table->longText('datos_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->index('creado_en', 'idx_auditoria_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
