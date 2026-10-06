<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('archivos_historias', function (Blueprint $table) {
            $table->id();
            $table->morphs('historia'); // historia_type (consulta, vacuna, etc.) + historia_id
            $table->string('nombre_original');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('tipo_mime');
            $table->enum('tipo_archivo', ['imagen', 'documento', 'video']);
            $table->bigInteger('tamaño');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos_historias');
    }
};