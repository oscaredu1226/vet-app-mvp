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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('puede_acceder_caja')->default(true)->after('puede_exportar_caja');
            $table->boolean('puede_acceder_ventas')->default(true)->after('puede_acceder_caja');
            $table->boolean('puede_acceder_egresos')->default(true)->after('puede_acceder_ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'puede_acceder_caja',
                'puede_acceder_ventas',
                'puede_acceder_egresos'
            ]);
        });
    }
};
