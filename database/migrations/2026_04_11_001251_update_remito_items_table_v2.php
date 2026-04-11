<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('remito_items', function (Blueprint $table) {
            if (!Schema::hasColumn('remito_items', 'codigo')) {
                $table->string('codigo')->nullable()->after('remito_id');
            }
            if (!Schema::hasColumn('remito_items', 'id_orden_item')) {
                $table->unsignedBigInteger('id_orden_item')->nullable()->after('codigo');
            }
            
            // Aumentar tamaño de articulo
            $table->string('articulo', 255)->change();
            
            // Permitir que descripcion sea opcional
            $table->string('descripcion', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('remito_items', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'id_orden_item']);
            $table->string('articulo', 20)->change();
        });
    }
};
