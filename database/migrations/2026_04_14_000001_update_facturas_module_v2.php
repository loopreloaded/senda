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
        // 1. Actualizar tabla facturas
        Schema::table('facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturas', 'motivo')) {
                $table->enum('motivo', ['pedido', 'particular'])->default('particular')->after('numero_comprobante_afip');
            }
            if (!Schema::hasColumn('facturas', 'importe_pagado')) {
                $table->decimal('importe_pagado', 15, 2)->default(0)->after('importe_total');
            }
            if (!Schema::hasColumn('facturas', 'art_fac')) {
                $table->text('art_fac')->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('facturas', 'cant_art_fac')) {
                $table->decimal('cant_art_fac', 15, 2)->default(0)->after('art_fac');
            }
            
            // Ajustar tipos de moneda si es necesario (ya es varchar 10, suficiente para ARS, USD_BIL, USD_DIV)
        });

        // 2. Crear tabla pivot remito_factura
        if (!Schema::hasTable('remito_factura')) {
            Schema::create('remito_factura', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_fac');
                $table->unsignedBigInteger('id_rem');
                $table->string('articulo')->nullable();
                $table->decimal('cantidad', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('id_fac')->references('id')->on('facturas')->onDelete('cascade');
                $table->foreign('id_rem')->references('id')->on('remitos')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remito_factura');
        
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['motivo', 'importe_pagado', 'art_fac', 'cant_art_fac']);
        });
    }
};
