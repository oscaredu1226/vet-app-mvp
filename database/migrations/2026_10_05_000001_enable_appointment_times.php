<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->time('hora')->nullable();
            $table->unsignedBigInteger('id_consulta')->nullable()->unique();
            $table->foreign('id_consulta')->references('id_consulta')->on('consultas')->nullOnDelete();
        });
        Schema::table('consultas', function (Blueprint $table) {
            $table->dateTime('proxima_cita')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['id_consulta']);
            $table->dropUnique(['id_consulta']);
            $table->dropColumn(['hora', 'id_consulta']);
        });
        Schema::table('consultas', function (Blueprint $table) {
            $table->date('proxima_cita')->nullable()->change();
        });
    }
};
