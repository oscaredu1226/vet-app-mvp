<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoHistoria extends Model
{
    use HasFactory;

    protected $table = 'archivos_historias';

    protected $fillable = [
        'historia_type',
        'historia_id', 
        'session_id',
        'is_temporal',
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'tipo_mime',
        'tipo_archivo',
        'tamaño'
    ];

    /**
     * Relación polimórfica con historias médicas
     */
    public function historia()
    {
        return $this->morphTo();
    }

    /**
     * Obtener URL completa del archivo
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->ruta);
    }

    /**
     * Obtener tamaño formateado
     */
    public function getTamañoFormateadoAttribute()
    {
        $bytes = $this->tamaño;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Obtener archivos temporales por session_id y tipo de historia
     */
    public static function getArchivosTemporales($sessionId, $tipoHistoria)
    {
        return static::where('session_id', $sessionId)
                    ->where('historia_type', $tipoHistoria)
                    ->where('is_temporal', true)
                    ->get();
    }

    /**
     * Asociar archivos temporales a una historia guardada
     */
    public static function asociarArchivosTemporales($sessionId, $tipoHistoria, $historiaId)
    {
        return static::where('session_id', $sessionId)
                    ->where('historia_type', $tipoHistoria)
                    ->where('is_temporal', true)
                    ->update([
                        'historia_id' => $historiaId,
                        'session_id' => null,
                        'is_temporal' => false
                    ]);
    }
}