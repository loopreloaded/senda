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
        // 1. Actualizar tabla recibos
        Schema::table('recibos', function (Blueprint $table) {
            // Campos de relación y motivo
            $table->unsignedBigInteger('cliente_id')->nullable()->after('id_recibo');
            $table->enum('motivo', ['vinculado', 'particular'])->default('vinculado')->after('fecha');
            
            // Importes y Retenciones
            $table->decimal('importe_saldado', 15, 2)->default(0)->after('motivo');
            $table->decimal('iva', 15, 2)->default(0)->after('importe_saldado');
            $table->decimal('ganancia', 15, 2)->default(0)->after('iva');
            $table->decimal('iibb', 15, 2)->default(0)->after('ganancia');
            $table->decimal('percepcion_ib', 15, 2)->default(0)->after('iibb');
            $table->decimal('total_retenciones', 15, 2)->default(0)->after('percepcion_ib');
            $table->decimal('importe_total', 15, 2)->default(0)->after('total_retenciones');
            
            // Otros
            $table->string('detalles_pago')->nullable()->after('importe_total');
            $table->enum('estado', ['Emitida', 'Cerrada'])->default('Emitida')->after('detalles_pago');
            
            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable()->after('estado');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->softDeletes();

            // Foreign Key
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });

        // 2. Renombrar y actualizar tabla intermedia
        if (Schema::hasTable('orden_pago_recibo')) {
            Schema::rename('orden_pago_recibo', 'op_recibo');
            
            Schema::table('op_recibo', function (Blueprint $table) {
                $table->renameColumn('orden_pago_id', 'id_op');
                $table->renameColumn('recibo_id', 'id_rec');
                $table->renameColumn('monto', 'saldado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('op_recibo')) {
            Schema::table('op_recibo', function (Blueprint $table) {
                $table->renameColumn('id_op', 'orden_pago_id');
                $table->renameColumn('id_rec', 'recibo_id');
                $table->renameColumn('saldado', 'monto');
            });
            Schema::rename('op_recibo', 'orden_pago_recibo');
        }

        Schema::table('recibos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn([
                'cliente_id', 'motivo', 'importe_saldado', 'iva', 'ganancia', 
                'iibb', 'percepcion_ib', 'total_retenciones', 'importe_total', 
                'detalles_pago', 'estado', 'created_by', 'updated_by', 'deleted_at'
            ]);
        });
    }
};
