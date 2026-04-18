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
        Schema::rename('pedidos_cotizacion', 'pedidos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('pedidos', 'pedidos_cotizacion');
    }
};
