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
        Schema::create('examenes_laboratorio', function (Blueprint $table) {
            $table->id('id_examen_lab'); // id_examen_lab INT AUTO_INCREMENT PRIMARY KEY [cite: 74]
            $table->unsignedBigInteger('id_mascota'); // id_mascota INT NOT NULL [cite: 74]
            $table->string('laboratorio'); // laboratorio VARCHAR(255) NOT NULL [cite: 74]
            $table->string('tipo_analisis'); // tipo_analisis VARCHAR(255) NOT NULL [cite: 74]
            $table->decimal('costo', 10, 2); // costo DECIMAL(10,2) NOT NULL [cite: 75]
            $table->boolean('pagado')->default(false); // pagado BOOLEAN DEFAULT FALSE [cite: 75]
            $table->date('fecha_envio'); // fecha_envio DATE NOT NULL [cite: 75]
            $table->boolean('subido_historia')->default(false); // subido_historia BOOLEAN DEFAULT FALSE
            $table->boolean('lectura_realizada')->default(false); // lectura_realizada BOOLEAN DEFAULT FALSE [cite: 75]
            $table->timestamps(); // fecha_creacion [cite: 75]

            // Clave foránea [cite: 75]
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
        Schema::dropIfExists('examenes_laboratorio');
    }
};
