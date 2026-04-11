<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion_items', 'id_pedido_cot')) {
                $table->unsignedBigInteger('id_pedido_cot')->nullable()->after('id_cotizacion');
            }
        });
    }

    public function down()
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->dropColumn('id_pedido_cot');
        });
    }
};
