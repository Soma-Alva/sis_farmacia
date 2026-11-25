<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conciliación bancaria por turno: compara lo que el sistema registró
 * como pagos con tarjeta/transferencia contra lo que el banco (voucher
 * del POS, estado de cuenta, app del banco) confirma realmente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conciliaciones_bancarias', function (Blueprint $table) {
            $table->id('id_conciliacion');
            $table->unsignedBigInteger('id_caja')->index();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->decimal('esperado_tarjeta', 12, 2)->default(0);
            $table->decimal('confirmado_tarjeta', 12, 2)->default(0);
            $table->decimal('esperado_transferencia', 12, 2)->default(0);
            $table->decimal('confirmado_transferencia', 12, 2)->default(0);
            $table->decimal('diferencia_total', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conciliaciones_bancarias');
    }
};
