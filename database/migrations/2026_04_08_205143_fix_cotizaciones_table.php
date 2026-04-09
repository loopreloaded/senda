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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('nro_cotizacion')->nullable()->after('id_cotizacion');
            $table->char('estado_cotizacion', 1)->default('v')->after('quien_cotiza');
        });

        Schema::create('pedido_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion');
            $table->unsignedBigInteger('id_pedido_cot');
            $table->string('producto')->nullable();
            $table->integer('cantidad')->default(0);
            $table->timestamps();

            $table->foreign('id_cotizacion')->references('id_cotizacion')->on('cotizaciones')->onDelete('cascade');
            $table->foreign('id_pedido_cot')->references('id_ped_cot')->on('pedidos_cotizacion')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_cotizacion');
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['nro_cotizacion', 'estado_cotizacion']);
        });
    }
};
