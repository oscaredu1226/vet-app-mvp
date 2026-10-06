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
        Schema::create('total_caja', function (Blueprint $table) {
            $table->id('id_total'); // id_total INT AUTO_INCREMENT PRIMARY KEY [cite: 45]
            $table->decimal('monto', 10, 2)->default(0.00); // monto DECIMAL(10,2) NOT NULL DEFAULT 0.00 [cite: 45]
            $table->string('tipo_operacion', 20)->default('manual'); // tipo_operacion VARCHAR(20) NOT NULL DEFAULT 'manual' [cite: 45]
            $table->text('concepto')->nullable(); // concepto TEXT NULL [cite: 45]
            $table->string('usuario', 100)->nullable(); // usuario VARCHAR(100) NULL [cite: 45]
            $table->timestamps(); // fecha_creacion, fecha_actualizacion [cite: 45]
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_caja');
    }
};
