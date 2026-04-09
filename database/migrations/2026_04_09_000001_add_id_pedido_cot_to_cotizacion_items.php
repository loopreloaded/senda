<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pedido_cot')->nullable()->after('id_cotizacion');
            
            // Opcional: Relación de clave foránea si la tabla pedidos_cotizacion lo permite
            // $table->foreign('id_pedido_cot')->references('id_ped_cot')->on('pedidos_cotizacion')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->dropColumn('id_pedido_cot');
        });
    }
};
