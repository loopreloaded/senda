<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('oc_remito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rem');
            $table->unsignedBigInteger('id_oc');
            $table->string('articulo')->nullable();
            $table->integer('cantidad')->default(0);
            $table->timestamps();

            $table->foreign('id_rem')->references('id')->on('remitos')->onDelete('cascade');
            $table->foreign('id_oc')->references('id')->on('orden_compras')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('oc_remito');
    }
};
