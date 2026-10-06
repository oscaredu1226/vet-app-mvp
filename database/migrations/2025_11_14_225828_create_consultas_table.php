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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id('id_consulta');
            $table->unsignedBigInteger('id_mascota');
            $table->dateTime('fecha');
            $table->string('medico')->nullable();
            $table->string('motivo', 100)->nullable();
            
            // Constantes fisiológicas
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();
            $table->string('tlc_tiempo_llenado', 50)->nullable();
            $table->string('hidratacion', 50)->nullable();
            
            // Información médica
            $table->text('anamnesis')->nullable();
            $table->text('examen_fisico')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('plan_tratamiento')->nullable();
            
            // Exámenes, tratamientos y recetas (formato texto)
            $table->text('examenes')->nullable();
            $table->text('tratamiento')->nullable();
            $table->text('receta')->nullable();
            
            $table->text('observaciones')->nullable();
            $table->date('proxima_cita')->nullable();
            $table->timestamps();

            // Llave foránea
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultas');
    }
};
