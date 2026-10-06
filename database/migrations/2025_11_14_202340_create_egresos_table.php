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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id('id_egreso'); // id_egreso INT AUTO_INCREMENT PRIMARY KEY [cite: 80]
            $table->text('descripcion'); // descripcion TEXT NOT NULL [cite: 80]
            $table->decimal('monto', 10, 2); // monto DECIMAL(10,2) NOT NULL [cite: 80]
            $table->date('fecha'); // fecha DATE NOT NULL [cite: 80]
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('egresos');
    }
};
