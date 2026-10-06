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
        Schema::create('groomings', function (Blueprint $table) {
            $table->id('id_grooming');
            $table->integer('numero_turno');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_mascota');
            $table->unsignedBigInteger('id_servicio');
            $table->date('fecha');
            $table->time('hora_entrada');
            $table->time('hora_salida')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'COMPLETADO', 'CANCELADO'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();

            // Índices y claves foráneas
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios')->onDelete('cascade');
            
            $table->index('fecha');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groomings');
    }
};
