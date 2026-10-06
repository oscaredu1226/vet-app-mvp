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
            $table->boolean('puede_exportar_clientes')->default(false)->after('role');
            $table->boolean('puede_exportar_mascotas')->default(false)->after('puede_exportar_clientes');
            $table->boolean('puede_exportar_ventas')->default(false)->after('puede_exportar_mascotas');
            $table->boolean('puede_exportar_productos')->default(false)->after('puede_exportar_ventas');
            $table->boolean('puede_exportar_servicios')->default(false)->after('puede_exportar_productos');
            $table->boolean('puede_exportar_caja')->default(false)->after('puede_exportar_servicios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'puede_exportar_clientes',
                'puede_exportar_mascotas',
                'puede_exportar_ventas',
                'puede_exportar_productos',
                'puede_exportar_servicios',
                'puede_exportar_caja'
            ]);
        });
    }
};
