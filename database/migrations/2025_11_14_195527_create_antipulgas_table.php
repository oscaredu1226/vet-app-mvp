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
        Schema::create('antipulgas', function (Blueprint $table) {
            $table->id('id_antipulgas');
            $table->unsignedBigInteger('id_mascota');
            $table->date('fecha');
            $table->string('medico')->nullable();
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->string('producto');
            $table->string('laboratorio')->nullable();
            $table->string('dosis', 100)->nullable();
            $table->string('via_administracion', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->date('proxima_aplicacion')->nullable();
            $table->timestamps();

            // Clave foránea
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
            
            // Índices
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
        Schema::dropIfExists('antipulgas');
    }
};
