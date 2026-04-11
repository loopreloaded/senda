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
            $table->integer('id_cotizacion_item')->nullable()->after('orden_compra_id');
            
            // Si la tabla cotizacion_items existe y tiene PK id_cot_item
            $table->foreign('id_cotizacion_item')
                  ->references('id_cot_item')
                  ->on('cotizacion_items')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compras_items', function (Blueprint $table) {
            $table->dropForeign(['id_cotizacion_item']);
            $table->dropColumn('id_cotizacion_item');
        });
    }
};
