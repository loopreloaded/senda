<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // New foreign key columns
            $table->foreignId('condicion_iva_id')->nullable()->after('condicion_iva')->constrained('condiciones_iva');
            $table->foreignId('condicion_iibb_id')->nullable()->after('condicion_iibb')->constrained('condiciones_iibb');
            
            // SoftDeletes
            $table->softDeletes()->after('updated_at');
        });

        // Migrate existing data
        $clientes = DB::table('clientes')->get();
        foreach ($clientes as $cliente) {
            $ivaId = null;
            if ($cliente->condicion_iva) {
                $ivaId = DB::table('condiciones_iva')->where('codigo', $cliente->condicion_iva)->value('id');
            }

            $iibbId = null;
            if ($cliente->condicion_iibb) {
                $iibbId = DB::table('condiciones_iibb')->where('codigo', $cliente->condicion_iibb)->value('id');
            }

            DB::table('clientes')->where('id', $cliente->id)->update([
                'condicion_iva_id' => $ivaId,
                'condicion_iibb_id' => $iibbId
            ]);
        }

        // Change tipo to ENUM and remove old columns
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['condicion_iva', 'condicion_iibb']);
        });

        // MySQL/MariaDB specific ENUM conversion
        DB::statement("ALTER TABLE clientes MODIFY COLUMN tipo ENUM('C', 'P', 'A') DEFAULT 'C'");
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('condicion_iva')->nullable()->after('nro_doc');
            $table->string('condicion_iibb')->nullable()->after('condicion_iva');
            $table->dropSoftDeletes();
        });

        // Re-migrate data back if needed (optional for down, but good practice)
        $clientes = DB::table('clientes')->get();
        foreach ($clientes as $cliente) {
            $ivaCodigo = DB::table('condiciones_iva')->where('id', $cliente->condicion_iva_id)->value('codigo');
            $iibbCodigo = DB::table('condiciones_iibb')->where('id', $cliente->condicion_iibb_id)->value('codigo');
            
            DB::table('clientes')->where('id', $cliente->id)->update([
                'condicion_iva' => $ivaCodigo,
                'condicion_iibb' => $iibbCodigo
            ]);
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['condicion_iva_id']);
            $table->dropForeign(['condicion_iibb_id']);
            $table->dropColumn(['condicion_iva_id', 'condicion_iibb_id']);
        });

        DB::statement("ALTER TABLE clientes MODIFY COLUMN tipo CHAR(1)");
    }
};
