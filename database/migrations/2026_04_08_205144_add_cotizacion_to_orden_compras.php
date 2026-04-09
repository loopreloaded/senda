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
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->unsignedBigInteger('cotizacion_id')->nullable()->after('id');
            $table->foreign('cotizacion_id')->references('id_cotizacion')->on('cotizaciones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->dropForeign(['cotizacion_id']);
            $table->dropColumn('cotizacion_id');
        });
    }
};
