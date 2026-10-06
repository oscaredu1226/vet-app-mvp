<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Catálogo de Tipos de Cirugía (Configuración)
        Schema::create('tipos_cirugia', function (Blueprint $table) {
            $table->id('id_tipo_cirugia');
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // 2. Registro Médico de Cirugías
        Schema::create('cirugias', function (Blueprint $table) {
            $table->id('id_cirugia');
            $table->unsignedBigInteger('id_mascota');
            $table->dateTime('fecha'); // Editable
            $table->string('medico')->nullable();
            $table->string('motivo');
            
            // Pre-quirúrgico
            $table->text('diagnostico');
            $table->text('observaciones_pre')->nullable();
            $table->boolean('ayuno')->default(false);
            
            // Constantes Pre
            $table->decimal('peso_pre', 6, 2);
            $table->decimal('temperatura_pre', 4, 1);
            $table->integer('fc_pre');
            $table->integer('fr_pre');
            $table->string('tlc_pre')->nullable();
            $table->string('hidratacion_pre')->nullable();

            // Procedimiento
            $table->string('tipo_cirugia'); // Nombre guardado (del catálogo o nuevo)
            
            // Personal Médico (Guardaremos JSON para flexibilidad)
            $table->json('cirujanos'); // Array: [{nombre, cargo, cmv}]
            $table->text('observaciones_cirujanos')->nullable();
            
            $table->json('anestesistas'); // Array: [{nombre, cargo, cmv}]
            
            // Tratamiento Intra/Post
            $table->text('tratamiento_aplicado');
            $table->text('observaciones_tratamiento')->nullable();

            // Constantes Post-quirúrgico
            $table->decimal('peso_post', 6, 2);
            $table->decimal('temperatura_post', 4, 1);
            $table->integer('fc_post');
            $table->integer('fr_post');

            $table->timestamps();

            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cirugias');
        Schema::dropIfExists('tipos_cirugia');
    }
};