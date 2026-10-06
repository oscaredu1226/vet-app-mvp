<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    Schema::create('clientes', function (Blueprint $table) {
        $table->id('id_cliente');
        $table->string('nombre');
        $table->string('apellido');
        $table->string('celular', 20);
        $table->string('dni', 20)->nullable()->unique();
        $table->string('direccion')->nullable();
        $table->timestamps();

        $table->index(['nombre', 'apellido']);
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
