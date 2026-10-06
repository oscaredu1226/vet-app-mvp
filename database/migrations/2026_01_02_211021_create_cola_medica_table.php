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
        Schema::create('cola_medica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mascota')->constrained('mascotas', 'id_mascota')->onDelete('cascade');
            $table->foreignId('agregado_por')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_ingreso')->useCurrent();
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cola_medica');
    }
};
