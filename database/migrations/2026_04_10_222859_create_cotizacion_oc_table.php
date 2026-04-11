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
        Schema::create('cotizacion_oc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_oc');
            $table->unsignedBigInteger('id_cot');
            $table->string('articulo');
            $table->decimal('cantidad', 12, 2);
            $table->timestamps();

            $table->foreign('id_oc')->references('id')->on('orden_compras')->onDelete('cascade');
            $table->foreign('id_cot')->references('id_cotizacion')->on('cotizaciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacion_oc');
    }
};
