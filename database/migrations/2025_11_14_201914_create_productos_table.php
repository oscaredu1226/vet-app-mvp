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
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto'); // id_producto INT AUTO_INCREMENT PRIMARY KEY [cite: 76]
            $table->string('nombre'); // nombre VARCHAR(255) NOT NULL [cite: 76]
            $table->decimal('precio', 10, 2); // precio DECIMAL(10,2) NOT NULL [cite: 76]
            $table->decimal('precio_compra', 10, 2)->nullable(); // precio_compra DECIMAL(10,2) NULLABLE
            $table->integer('stock'); // stock INT NOT NULL [cite: 76]
            $table->integer('stock_minimo')->default(2); // stock_minimo INT NOT NULL DEFAULT 2 [cite: 76, 92]
            $table->string('tipo', 20)->default('clinica'); // tipo VARCHAR(20) NOT NULL DEFAULT 'clinica' [cite: 76, 92]
            $table->text('descripcion')->nullable(); // descripcion TEXT [cite: 76, 92]
            $table->date('fecha_vencimiento')->nullable(); // fecha_vencimiento DATE NULLABLE

            // Índices [cite: 39]
            $table->index('nombre');
            $table->index('stock');
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
        Schema::dropIfExists('productos');
    }
};
