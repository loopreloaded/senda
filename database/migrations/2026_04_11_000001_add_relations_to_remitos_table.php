<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('remitos', function (Blueprint $table) {
            if (!Schema::hasColumn('remitos', 'id_orden_compra')) {
                $table->unsignedBigInteger('id_orden_compra')->nullable()->after('id');
            }
            if (!Schema::hasColumn('remitos', 'id_cliente')) {
                $table->unsignedBigInteger('id_cliente')->nullable()->after('id_orden_compra');
            }
            if (!Schema::hasColumn('remitos', 'numero_remito')) {
                $table->string('numero_remito')->nullable()->after('id_cliente');
            }
        });

        // Poblar id_orden_compra basándose en el texto 'orden_compra'
        DB::statement("UPDATE remitos r 
                       JOIN orden_compras oc ON r.orden_compra COLLATE utf8mb4_unicode_ci = oc.numero_oc 
                       SET r.id_orden_compra = oc.id 
                       WHERE r.id_orden_compra IS NULL");

        // Vincular id_cliente basándose en el CUIT
        DB::statement("UPDATE remitos r 
                       JOIN clientes c ON r.cuit COLLATE utf8mb4_unicode_ci = c.cuit 
                       SET r.id_cliente = c.id 
                       WHERE r.id_cliente IS NULL");
        
        // Si numero_remito está vacío, podemos usar el ID como fallback si no hay otra lógica
        DB::statement("UPDATE remitos SET numero_remito = id WHERE numero_remito IS NULL");
    }

    public function down()
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->dropColumn(['id_orden_compra', 'id_cliente', 'numero_remito']);
        });
    }
};
