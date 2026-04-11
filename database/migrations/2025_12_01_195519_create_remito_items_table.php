<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('remito_items')) {
            Schema::create('remito_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('remito_id');
                $table->string('descripcion');
                $table->integer('cantidad')->default(1);

                $table->timestamps();

                $table->foreign('remito_id')->references('id')->on('remitos')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('remito_items');
    }

};
