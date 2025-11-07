<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Campos adicionales requeridos por AFIP
            $table->string('telefono', 50)->nullable()->after('email');
            $table->string('codigo_postal', 10)->nullable()->after('direccion');
            $table->string('localidad', 100)->nullable()->after('codigo_postal');
            $table->string('provincia', 100)->nullable()->after('localidad');
            $table->string('pais', 100)->default('Argentina')->after('provincia');

            // Documentación fiscal
            $table->string('tipo_doc', 10)->default('CUIT')->after('cuit');
            $table->string('nro_doc', 20)->nullable()->after('tipo_doc');

            // Campo libre
            $table->text('observaciones')->nullable()->after('pais');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'codigo_postal',
                'localidad',
                'provincia',
                'pais',
                'tipo_doc',
                'nro_doc',
                'observaciones',
            ]);
        });
    }
};
