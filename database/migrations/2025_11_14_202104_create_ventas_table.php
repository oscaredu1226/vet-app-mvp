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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta'); // id_venta INT AUTO_INCREMENT PRIMARY KEY [cite: 77]
            $table->unsignedBigInteger('id_mascota'); // id_mascota INT NOT NULL [cite: 77]
            $table->string('tipo_item', 20); // tipo_item
            $table->unsignedBigInteger('id_item'); // id_item INT NOT NULL [cite: 77]
            $table->integer('cantidad'); // cantidad INT NOT NULL [cite: 77]
            $table->decimal('precio_unitario', 10, 2); // precio_unitario DECIMAL(10,2) NOT NULL [cite: 77]
            $table->decimal('subtotal', 10, 2); // subtotal DECIMAL(10,2) NOT NULL [cite: 77]
            $table->string('medio_pago', 50); // medio_pago VARCHAR(50) NOT NULL [cite: 77]
            $table->string('tipo_negocio', 20)->default('clinica'); // tipo_negocio VARCHAR(20) DEFAULT 'clinica' [cite: 77, 94]
            $table->dateTime('fecha_venta'); // fecha_venta DATETIME NOT NULL [cite: 77]

            // Clave foránea [cite: 77]
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');

            // Índices [cite: 39, 40, 94, 95]
            $table->index('fecha_venta');
            $table->index('medio_pago');
            $table->index('tipo_item');
            $table->index('id_item');
            $table->index('tipo_negocio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
};
