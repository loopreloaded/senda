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
        Schema::create('orden_compras_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('orden_compra_id');
            $table->string('codigo')->nullable();
            $table->string('descripcion');
            $table->decimal('cantidad', 15, 2)->default(0);
            $table->string('unidad')->nullable(); // kg, mts, u., caja, etc
            $table->decimal('precio_unitario', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->foreign('orden_compra_id')
                ->references('id')
                ->on('orden_compras')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compras_items');
    }
};
