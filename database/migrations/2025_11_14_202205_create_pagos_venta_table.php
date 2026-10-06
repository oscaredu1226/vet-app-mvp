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
        Schema::create('pagos_venta', function (Blueprint $table) {
            $table->id('id_pago'); // id_pago INT AUTO_INCREMENT PRIMARY KEY [cite: 78]
            $table->unsignedBigInteger('id_venta'); // id_venta INT NOT NULL [cite: 78]
            $table->unsignedBigInteger('id_mascota'); // id_mascota INT NOT NULL [cite: 78]
            $table->string('medio_pago', 50); // medio_pago VARCHAR(50) NOT NULL [cite: 78]
            $table->decimal('monto', 10, 2); // monto DECIMAL(10,2) NOT NULL [cite: 78]
            $table->dateTime('fecha_pago')->default(DB::raw('CURRENT_TIMESTAMP')); // fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP [cite: 78]

            // Claves foráneas [cite: 78, 79]
            $table->foreign('id_venta')->references('id_venta')->on('ventas')->onDelete('cascade');
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
            
            // Índices [cite: 78]
            $table->index('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagos_venta');
    }
};
