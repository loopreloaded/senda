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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // A, B, Nota Débito A/B
            $table->string('numero')->nullable();
            $table->string('cliente');
            $table->string('cuit')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('importe_neto', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('estado')->default('pendiente');
            $table->string('cae')->nullable();
            $table->date('vto_cae')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
