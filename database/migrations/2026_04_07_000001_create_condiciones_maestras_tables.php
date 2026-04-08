<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condiciones_iva', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
            $table->integer('id_afip')->nullable();
        });

        DB::table('condiciones_iva')->insert([
            ['codigo' => 'RI',   'nombre' => 'Responsable Inscripto', 'id_afip' => 1],
            ['codigo' => 'MT',   'nombre' => 'Responsable Monotributo', 'id_afip' => 11],
            ['codigo' => 'CF',   'nombre' => 'Consumidor Final', 'id_afip' => 5],
            ['codigo' => 'EX',   'nombre' => 'IVA Sujeto Exento', 'id_afip' => 4],
            ['codigo' => 'NR',   'nombre' => 'Sujeto No Categorizado', 'id_afip' => 3], // "No responsable"
        ]);

        Schema::create('condiciones_iibb', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
        });

        DB::table('condiciones_iibb')->insert([
            ['codigo' => 'L',  'nombre' => 'Local'],
            ['codigo' => 'CM', 'nombre' => 'Convenio Multilateral'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_iva');
        Schema::dropIfExists('condiciones_iibb');
    }
};
