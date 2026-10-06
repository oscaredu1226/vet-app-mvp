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
        Schema::create('logo', function (Blueprint $table) {
            $table->id(); // id INT AUTO_INCREMENT PRIMARY KEY [cite: 81]
            $table->longText('imagen')->nullable(); // imagen LONGTEXT DEFAULT NULL [cite: 81]
            $table->timestamps(); // fecha_creacion, fecha_actualizacion [cite: 81]
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logo');
    }
};
