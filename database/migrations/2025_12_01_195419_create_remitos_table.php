<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('remitos')) {
            Schema::create('remitos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('creado_por');

                $table->string('numero_remito')->nullable();
                $table->date('fecha')->nullable();

                // Estado del remito
                $table->enum('estado', ['pendiente', 'aprobado', 'entregado', 'anulado'])
                    ->default('pendiente');

                $table->text('observaciones')->nullable();

                $table->timestamps();

                $table->foreign('cliente_id')->references('id')->on('clientes');
                $table->foreign('creado_por')->references('id')->on('users');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remitos');
    }
};
