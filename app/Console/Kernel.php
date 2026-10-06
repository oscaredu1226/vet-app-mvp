<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
// MVP_POSTERIOR: Mantenimiento del modulo de caja
// MVP_POSTERIOR |         // Limpieza diaria de reportes de caja antiguos (más de 45 días)
// MVP_POSTERIOR |         $schedule->call(function () {
// MVP_POSTERIOR |             $directory = 'reportes_caja';
// MVP_POSTERIOR |             
// MVP_POSTERIOR |             // Verificar que el directorio existe
// MVP_POSTERIOR |             if (!Storage::exists($directory)) {
// MVP_POSTERIOR |                 return;
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |             
// MVP_POSTERIOR |             // Obtener todos los archivos del directorio
// MVP_POSTERIOR |             $files = Storage::files($directory);
// MVP_POSTERIOR |             $fechaLimite = Carbon::now()->subDays(45); // Hace 45 días
// MVP_POSTERIOR |             $archivosEliminados = 0;
// MVP_POSTERIOR |             
// MVP_POSTERIOR |             foreach ($files as $file) {
// MVP_POSTERIOR |                 // Obtener la fecha de última modificación
// MVP_POSTERIOR |                 $lastModified = Storage::lastModified($file);
// MVP_POSTERIOR |                 $fechaArchivo = Carbon::createFromTimestamp($lastModified);
// MVP_POSTERIOR |                 
// MVP_POSTERIOR |                 // Si el archivo tiene más de 45 días, eliminarlo
// MVP_POSTERIOR |                 if ($fechaArchivo->lessThan($fechaLimite)) {
// MVP_POSTERIOR |                     Storage::delete($file);
// MVP_POSTERIOR |                     $archivosEliminados++;
// MVP_POSTERIOR |                 }
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |             
// MVP_POSTERIOR |             // Log para verificar la ejecución
// MVP_POSTERIOR |             \Log::info("Limpieza de reportes de caja ejecutada. Archivos eliminados: {$archivosEliminados}");
// MVP_POSTERIOR |             
// MVP_POSTERIOR |         })->daily()->at('02:00')->name('limpiar-reportes-caja')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
