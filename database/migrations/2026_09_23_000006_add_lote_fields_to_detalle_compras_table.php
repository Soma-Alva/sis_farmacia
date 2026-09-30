<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada línea de una compra necesita capturar el número de lote y la
 * fecha de vencimiento que trae físicamente esa mercancía, para poder
 * crear el registro real en `lotes` cuando la compra se aprueba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->string('numero_lote', 100)->nullable()->after('id_lote');
            $table->date('fecha_vencimiento')->nullable()->after('numero_lote');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropColumn(['numero_lote', 'fecha_vencimiento']);
        });
    }
};
