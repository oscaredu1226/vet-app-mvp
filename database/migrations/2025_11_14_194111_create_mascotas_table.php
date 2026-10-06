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
    public function up(){
    Schema::create('mascotas', function (Blueprint $table) {
        $table->id('id_mascota');
        $table->unsignedBigInteger('id_cliente');
        $table->string('nombre');
        $table->string('especie', 50);
        $table->string('raza', 100);
        $table->string('genero', 10);
        $table->date('fecha_nacimiento');
        $table->string('estado', 10);
        $table->string('esterilizado', 10)->default('No');

        $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');

        $table->index('nombre');
        $table->index('especie');
        $table->index('fecha_nacimiento');
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mascotas');
    }
};
