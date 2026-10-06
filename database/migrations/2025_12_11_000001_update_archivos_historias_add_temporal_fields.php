<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('archivos_historias', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('historia_id');
            $table->boolean('is_temporal')->default(false)->after('session_id');
        });
        
        // Hacer historia_id nullable en una operación separada
        DB::statement('ALTER TABLE archivos_historias MODIFY historia_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archivos_historias', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'is_temporal']);
        });
        
        // Restaurar historia_id a no nullable
        DB::statement('ALTER TABLE archivos_historias MODIFY historia_id BIGINT UNSIGNED NOT NULL');
    }
};