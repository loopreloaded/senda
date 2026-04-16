<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cambiar temporalmente a VARCHAR para evitar errores de truncado
        DB::statement("ALTER TABLE recibos MODIFY COLUMN motivo VARCHAR(50)");
        
        // 2. Actualizar los valores de 'vinculado' a 'pedido'
        DB::table('recibos')->where('motivo', 'vinculado')->update(['motivo' => 'pedido']);

        // 3. Volver a ENUM con los nuevos valores
        DB::statement("ALTER TABLE recibos MODIFY COLUMN motivo ENUM('pedido', 'particular') DEFAULT 'pedido' NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE recibos MODIFY COLUMN motivo VARCHAR(50)");
        DB::table('recibos')->where('motivo', 'pedido')->update(['motivo' => 'vinculado']);
        DB::statement("ALTER TABLE recibos MODIFY COLUMN motivo ENUM('vinculado', 'particular') DEFAULT 'vinculado' NOT NULL");
    }
};
