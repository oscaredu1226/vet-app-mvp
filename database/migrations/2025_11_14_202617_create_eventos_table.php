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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id('id_evento'); // id_evento INT AUTO_INCREMENT PRIMARY KEY [cite: 81]
            // Se usa id_mascota basado en la lógica de actualización [cite: 85, 114]
            $table->unsignedBigInteger('id_mascota'); // id_mascota INT NOT NULL [cite: 114]
            $table->date('fecha'); // fecha DATE NOT NULL [cite: 82]
            $table->string('titulo'); // titulo VARCHAR(255) NOT NULL [cite: 82]
            $table->text('descripcion')->nullable(); // descripcion TEXT [cite: 82]
            $table->enum('estado', ['pendiente', 'completado'])->default('pendiente'); // estado ENUM(...) [cite: 82]
            $table->timestamps(); // fecha_creacion, fecha_actualizacion [cite: 82]

            // Clave foránea [cite: 114]
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
            
            // Índices [cite: 100]
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos');
    }
};
