<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historia_banos', function (Blueprint $table) {
            $table->id('id_historia_bano');
            $table->unsignedBigInteger('id_mascota');
            $table->timestamp('fecha_registro');
            $table->boolean('muerde')->default(false);
            $table->boolean('agresivo')->default(false);
            $table->boolean('no_recibir')->default(false);
            $table->boolean('tener_cuidado')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Clave foránea
            $table->foreign('id_mascota')->references('id_mascota')->on('mascotas')->onDelete('cascade');
            
            $table->index('id_mascota');
            $table->index('fecha_registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historia_banos');
    }
};
