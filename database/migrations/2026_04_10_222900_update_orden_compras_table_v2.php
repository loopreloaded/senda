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
            if (!Schema::hasColumn('orden_compras', 'motivo')) {
                $table->enum('motivo', ['pedido', 'particular'])->default('particular')->after('id_cliente');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->dropColumn('motivo');
        });
    }
};
