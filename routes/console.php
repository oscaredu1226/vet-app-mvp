<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Comando de Mantenimiento: Limpieza de Logs Antiguos
|--------------------------------------------------------------------------
|
| Elimina archivos de log con más de 30 días de antigüedad para evitar
| que el almacenamiento se llene. Se ejecuta mensualmente.
|
*/
Artisan::command('logs:clean', function () {
    $logsPath = storage_path('logs');
    $thirtyDaysAgo = now()->subDays(30)->timestamp;
    $deletedCount = 0;
    
    $files = File::files($logsPath);
    
    foreach ($files as $file) {
        // Solo eliminar archivos .log antiguos (no el actual laravel.log)
        if ($file->getExtension() === 'log' && 
            $file->getFilename() !== 'laravel.log' &&
            $file->getMTime() < $thirtyDaysAgo) {
            File::delete($file->getPathname());
            $deletedCount++;
            $this->info("Eliminado: {$file->getFilename()}");
        }
    }
    
    $this->info("✅ Limpieza completada. {$deletedCount} archivo(s) eliminado(s).");
})->purpose('Elimina logs con más de 30 días de antigüedad');

/*
|--------------------------------------------------------------------------
| Comando de Mantenimiento: Limpieza de Sesiones Expiradas
|--------------------------------------------------------------------------
|
| Solo aplica si SESSION_DRIVER=file. Elimina sesiones expiradas.
| Nota: Con SESSION_DRIVER=cookie este comando no hace nada.
|
*/
Artisan::command('sessions:clean', function () {
    $sessionDriver = config('session.driver');
    
    if ($sessionDriver !== 'file') {
        $this->info("ℹ️  Driver de sesión actual: {$sessionDriver}");
        $this->info("ℹ️  Limpieza de sesiones solo aplica con driver 'file'.");
        return 0;
    }
    
    $sessionsPath = storage_path('framework/sessions');
    $lifetimeMinutes = config('session.lifetime', 120);
    $expirationTime = now()->subMinutes($lifetimeMinutes)->timestamp;
    $deletedCount = 0;
    
    if (!File::exists($sessionsPath)) {
        $this->error("❌ Directorio de sesiones no existe: {$sessionsPath}");
        return 1;
    }
    
    $files = File::files($sessionsPath);
    
    foreach ($files as $file) {
        // No eliminar .gitignore
        if ($file->getFilename() === '.gitignore') {
            continue;
        }
        
        if ($file->getMTime() < $expirationTime) {
            File::delete($file->getPathname());
            $deletedCount++;
        }
    }
    
    $this->info("✅ Limpieza de sesiones completada. {$deletedCount} archivo(s) eliminado(s).");
})->purpose('Elimina sesiones expiradas (solo con driver file)');
