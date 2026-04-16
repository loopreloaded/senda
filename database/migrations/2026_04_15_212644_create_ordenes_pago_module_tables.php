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
        // 1. Tabla ordenes_pago
        Schema::create('ordenes_pago', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->date('fecha');
            $table->string('archivo')->nullable();
            $table->string('nro_op')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('motivo', ['pedido', 'particular'])->default('pedido');
            $table->decimal('importe_pagado', 15, 2)->default(0);
            $table->decimal('importe_saldado', 15, 2)->default(0);
            $table->enum('estado', ['Recibida', 'Anulada', 'Parcial', 'Pagada'])->default('Recibida');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });

        // 2. Tabla intermedia factura_op (N:N entre Factura y OP)
        Schema::create('factura_op', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_pago_id');
            $table->unsignedBigInteger('factura_id');
            $table->decimal('pagado', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('orden_pago_id')->references('id')->on('ordenes_pago')->onDelete('cascade');
            $table->foreign('factura_id')->references('id')->on('facturas')->onDelete('cascade');
        });

        // 3. Tabla intermedia orden_pago_recibo (N:N entre OP y Recibo)
        Schema::create('orden_pago_recibo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_pago_id');
            $table->integer('recibo_id');
            $table->decimal('monto', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('orden_pago_id')->references('id')->on('ordenes_pago')->onDelete('cascade');
            $table->foreign('recibo_id')->references('id_recibo')->on('recibos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_pago_recibo');
        Schema::dropIfExists('factura_op');
        Schema::dropIfExists('ordenes_pago');
    }
};
