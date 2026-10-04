<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La decisión de aplicar IVA (y a qué tasa) se toma al ABRIR la caja,
 * no por producto. Útil para farmacias en Nicaragua, donde los
 * medicamentos están exentos de IVA por el Art. 114 de la
 * Constitución, pero una caja puede decidir aplicarlo si ese día va a
 * vender también productos gravados (cosméticos, cuidado personal, etc).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->boolean('aplica_iva')->default(false)->after('tipo_cambio');
            $table->decimal('iva_porcentaje', 5, 2)->default(15.00)->after('aplica_iva');
        });
    }

    public function down(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropColumn(['aplica_iva', 'iva_porcentaje']);
        });
    }
};
