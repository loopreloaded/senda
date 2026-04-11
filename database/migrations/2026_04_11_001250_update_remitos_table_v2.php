<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('remitos', function (Blueprint $table) {
            // Motivo: pedido (vinculado), particular
            if (!Schema::hasColumn('remitos', 'motivo')) {
                $table->enum('motivo', ['pedido', 'particular'])->default('particular')->after('numero_remito');
            }

            // ID Cotización
            if (!Schema::hasColumn('remitos', 'id_cot')) {
                $table->unsignedBigInteger('id_cot')->nullable()->after('motivo');
            }

            // Condición de Venta
            if (!Schema::hasColumn('remitos', 'condicion_venta')) {
                $table->string('condicion_venta')->nullable()->after('fecha');
            }

            // Flete fields (if missing)
            if (!Schema::hasColumn('remitos', 'transportista')) {
                $table->string('transportista')->nullable();
            }
            if (!Schema::hasColumn('remitos', 'domicilio_transportista')) {
                $table->string('domicilio_transportista')->nullable();
            }
            if (!Schema::hasColumn('remitos', 'iva_transportista')) {
                $table->string('iva_transportista')->nullable();
            }
            if (!Schema::hasColumn('remitos', 'cuit_transportista')) {
                $table->string('cuit_transportista', 11)->nullable();
            }

            if (!Schema::hasColumn('remitos', 'observacion')) {
                $table->string('observacion')->nullable();
            }

            if (!Schema::hasColumn('remitos', 'cai')) {
                $table->string('cai')->nullable();
            }
            if (!Schema::hasColumn('remitos', 'cai_vto')) {
                $table->date('cai_vto')->nullable();
            }
        });

        // Actualizar estados existentes y convertir la columna a enum de la especificación
        // Nota: Primero actualizamos los valores de texto para que coincidan con el nuevo enum
        DB::statement("UPDATE remitos SET estado = 'Emitido' WHERE estado IN ('pendiente', 'aprobado', 'confirmado')");
        DB::statement("UPDATE remitos SET estado = 'Anulado' WHERE estado = 'anulado'");

        Schema::table('remitos', function (Blueprint $table) {
            $table->enum('estado', ['Emitido', 'Anulado', 'Parcial', 'Facturado'])
                  ->default('Emitido')
                  ->change();
        });
        
        // Poblar motivo basado en si tiene OC
        DB::statement("UPDATE remitos SET motivo = 'pedido' WHERE id_orden_compra IS NOT NULL");
    }

    public function down()
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->dropColumn([
                'motivo', 'id_cot', 'condicion_venta', 
                'transportista', 'domicilio_transportista', 'iva_transportista', 'cuit_transportista',
                'observacion', 'cai', 'cai_vto'
            ]);
            
            $table->string('estado')->default('pendiente')->change();
        });
    }
};
