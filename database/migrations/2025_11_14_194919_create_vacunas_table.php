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
        Schema::create('vacunas', function (Blueprint $table) {
            $table->id('id_vacuna'); // id_vacuna INT AUTO_INCREMENT PRIMARY KEY [cite: 70]
            $table->unsignedBigInteger('id_mascota'); // id_mascota INT NOT NULL [cite: 70]
            $table->date('fecha'); // fecha DATE NOT NULL [cite: 70]
            $table->string('medico')->nullable();
            $table->decimal('peso', 6, 2)->nullable(); // peso DECIMAL(6,2) NULL [cite: 70]
            $table->decimal('temperatura', 5, 2); // temperatura DECIMAL(5,2) NOT NULL
            $table->string('vacuna_aplicada'); // vacuna_aplicada VARCHAR(255) NOT NULL [cite: 70]
            $table->string('laboratorio')->nullable(); // laboratorio VARCHAR(255) NULL [cite: 70]
            $table->string('lote', 100)->nullable(); // lote VARCHAR(100) NULL [cite: 70]
            $table->date('vencimiento')->nullable(); // vencimiento DATE NULL [cite: 71]
            $table->string('via_administracion', 100)->nullable(); // via_administracion VARCHAR(100) NULL [cite: 71]
            $table->string('dosis', 100)->nullable(); // dosis VARCHAR(100) NULL [cite: 71]
            $table->text('observaciones')->nullable(); // observaciones TEXT NULL [cite: 71]
            $table->date('proxima_dosis')->nullable(); // proxima_dosis DATE NULL [cite: 71]
            $table->timestamps(); // fecha_creacion, fecha_actualizacion [cite: 71]

            // Clave foránea [cite: 71]
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
            
            // Índices [cite: 99]
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
        Schema::dropIfExists('vacunas');
    }
};
