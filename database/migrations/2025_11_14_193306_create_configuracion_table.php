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
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id(); // id INT AUTO_INCREMENT PRIMARY KEY
            $table->string('clave', 100)->unique()->nullable(); // clave VARCHAR(100) UNIQUE DEFAULT NULL
            $table->text('valor')->nullable(); // valor TEXT DEFAULT NULL
            $table->longText('logo')->nullable(); // logo LONGTEXT DEFAULT NULL
            $table->string('telefono', 9)->nullable(); // telefono VARCHAR(9) DEFAULT NULL
            $table->timestamps(); // ultima_actualizacion TIMESTAMP...
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracion');
    }
};
