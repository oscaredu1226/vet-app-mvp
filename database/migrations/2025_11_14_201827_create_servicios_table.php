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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('id_servicio'); // id_servicio INT AUTO_INCREMENT PRIMARY KEY [cite: 75]
            $table->string('nombre'); // nombre VARCHAR(255) NOT NULL [cite: 75]
            $table->decimal('precio', 10, 2); // precio DECIMAL(10,2) NOT NULL [cite: 75]
            $table->string('tipo', 20)->default('clinica'); // tipo VARCHAR(20) NOT NULL DEFAULT 'clinica' [cite: 75]
            $table->text('descripcion')->nullable(); // descripcion TEXT [cite: 76]

            // Índices [cite: 39]
            $table->index('nombre');
            $table->index('precio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicios');
    }
};
