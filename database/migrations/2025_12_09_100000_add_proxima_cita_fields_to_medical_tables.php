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
        // Agregar campos a vacunas
        Schema::table('vacunas', function (Blueprint $table) {
            if (!Schema::hasColumn('vacunas', 'temperatura')) {
                $table->decimal('temperatura', 4, 1)->nullable()->after('peso');
            }
            if (!Schema::hasColumn('vacunas', 'hidratacion')) {
                $table->string('hidratacion', 100)->nullable()->after('temperatura');
            }
            if (!Schema::hasColumn('vacunas', 'tlc_tiempo_llenado')) {
                $table->string('tlc_tiempo_llenado', 100)->nullable()->after('hidratacion');
            }
            if (!Schema::hasColumn('vacunas', 'anamnesis')) {
                $table->text('anamnesis')->nullable()->after('tlc_tiempo_llenado');
            }
            if (!Schema::hasColumn('vacunas', 'tipo_proxima_vacuna')) {
                $table->string('tipo_proxima_vacuna')->nullable()->after('proxima_dosis');
            }
            if (!Schema::hasColumn('vacunas', 'mensaje_proxima_cita')) {
                $table->string('mensaje_proxima_cita')->nullable()->after('tipo_proxima_vacuna');
            }
        });

        // Agregar campos a desparasitaciones
        Schema::table('desparasitaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('desparasitaciones', 'frecuencia_cardiaca')) {
                $table->integer('frecuencia_cardiaca')->nullable()->after('temperatura');
            }
            if (!Schema::hasColumn('desparasitaciones', 'frecuencia_respiratoria')) {
                $table->integer('frecuencia_respiratoria')->nullable()->after('frecuencia_cardiaca');
            }
            if (!Schema::hasColumn('desparasitaciones', 'tlc')) {
                $table->string('tlc', 100)->nullable()->after('frecuencia_respiratoria');
            }
            if (!Schema::hasColumn('desparasitaciones', 'hidratacion')) {
                $table->string('hidratacion', 100)->nullable()->after('tlc');
            }
            if (!Schema::hasColumn('desparasitaciones', 'proxima_cita')) {
                $table->dateTime('proxima_cita')->nullable()->after('hidratacion');
            }
            if (!Schema::hasColumn('desparasitaciones', 'mensaje_proxima_cita')) {
                $table->string('mensaje_proxima_cita')->nullable()->after('proxima_cita');
            }
        });

        // Agregar campos a antipulgas
        Schema::table('antipulgas', function (Blueprint $table) {
            if (!Schema::hasColumn('antipulgas', 'frecuencia_cardiaca')) {
                $table->integer('frecuencia_cardiaca')->nullable()->after('temperatura');
            }
            if (!Schema::hasColumn('antipulgas', 'frecuencia_respiratoria')) {
                $table->integer('frecuencia_respiratoria')->nullable()->after('frecuencia_cardiaca');
            }
            if (!Schema::hasColumn('antipulgas', 'tlc')) {
                $table->string('tlc', 100)->nullable()->after('frecuencia_respiratoria');
            }
            if (!Schema::hasColumn('antipulgas', 'hidratacion')) {
                $table->string('hidratacion', 100)->nullable()->after('tlc');
            }
            if (!Schema::hasColumn('antipulgas', 'proxima_cita')) {
                $table->dateTime('proxima_cita')->nullable()->after('hidratacion');
            }
            if (!Schema::hasColumn('antipulgas', 'mensaje_proxima_cita')) {
                $table->string('mensaje_proxima_cita')->nullable()->after('proxima_cita');
            }
        });

        // Agregar campos a cirugias
        Schema::table('cirugias', function (Blueprint $table) {
            if (!Schema::hasColumn('cirugias', 'proxima_cita')) {
                $table->dateTime('proxima_cita')->nullable()->after('fr_post');
            }
            if (!Schema::hasColumn('cirugias', 'mensaje_proxima_cita')) {
                $table->string('mensaje_proxima_cita')->nullable()->after('proxima_cita');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $columns = ['temperatura', 'hidratacion', 'tlc_tiempo_llenado', 'anamnesis', 'tipo_proxima_vacuna', 'mensaje_proxima_cita'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('vacunas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('desparasitaciones', function (Blueprint $table) {
            $columns = ['frecuencia_cardiaca', 'frecuencia_respiratoria', 'tlc', 'hidratacion', 'proxima_cita', 'mensaje_proxima_cita'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('desparasitaciones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('antipulgas', function (Blueprint $table) {
            $columns = ['frecuencia_cardiaca', 'frecuencia_respiratoria', 'tlc', 'hidratacion', 'proxima_cita', 'mensaje_proxima_cita'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('antipulgas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('cirugias', function (Blueprint $table) {
            if (Schema::hasColumn('cirugias', 'proxima_cita')) {
                $table->dropColumn('proxima_cita');
            }
            if (Schema::hasColumn('cirugias', 'mensaje_proxima_cita')) {
                $table->dropColumn('mensaje_proxima_cita');
            }
        });
    }
};
