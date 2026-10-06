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
    Schema::create('historia_clinica', function (Blueprint $table) {
        $table->id('id_historia'); // id_historia INT AUTO_INCREMENT PRIMARY KEY [cite: 67]
        $table->unsignedBigInteger('id_mascota'); // id_mascota INT NOT NULL [cite: 67]
        $table->dateTime('fecha_atencion')->default(DB::raw('CURRENT_TIMESTAMP')); // fecha_atencion DATETIME DEFAULT CURRENT_TIMESTAMP [cite: 67]
        $table->text('motivo_atencion')->nullable(); // motivo_atencion TEXT [cite: 67]
        $table->text('anamnesis'); // anamnesis TEXT NOT NULL [cite: 67]
        $table->text('descripcion_caso'); // descripcion_caso TEXT NOT NULL [cite: 67, 88]
        $table->decimal('temperatura', 4, 2); // temperatura DECIMAL(4,2) NOT NULL [cite: 67]
        $table->decimal('peso', 6, 2); // peso DECIMAL(6,2) NOT NULL [cite: 67]
        $table->integer('frecuencia_cardiaca')->nullable(); // frecuencia_cardiaca INT NULL [cite: 68, 89]
        $table->string('tlc_tiempo_llenado', 50)->nullable(); // tlc_tiempo_llenado VARCHAR(50) NULL [cite: 68, 89]
        $table->string('dth_deshidratacion', 50)->nullable(); // dth_deshidratacion VARCHAR(50) NULL [cite: 68, 89]
        $table->text('examen_clinico'); // examen_clinico TEXT NOT NULL [cite: 68, 89]
        $table->text('diagnostico')->nullable(); // diagnostico TEXT [cite: 68]
        $table->text('observaciones')->nullable(); // observaciones TEXT [cite: 68]
        $table->text('tratamiento')->nullable(); // tratamiento TEXT [cite: 68]

        // Clave foránea [cite: 68]
        $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
        
        // Índices [cite: 40, 99]
        $table->index('fecha_atencion');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historia_clinica');
    }
};
