<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orden_compras_items', function (Blueprint $table) {
            // Añadimos id_cotizacion para vincular a la cabecera (espejando lógica de Cotizaciones)
            $table->unsignedBigInteger('id_cotizacion')->nullable()->after('orden_compra_id');
            
            // Establecemos la relación
            $table->foreign('id_cotizacion')
                  ->references('id_cotizacion')
                  ->on('cotizaciones')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compras_items', function (Blueprint $table) {
            $table->dropForeign(['id_cotizacion']);
            $table->dropColumn('id_cotizacion');
        });
    }
};
