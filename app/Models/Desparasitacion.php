<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desparasitacion extends Model
{
    use HasFactory;

    protected $table = 'desparasitaciones';
    protected $primaryKey = 'id_desparasitacion';
    // Timestamps es true por defecto

    protected $fillable = [
        'id_mascota',
        'fecha',
        'medico',
        'peso',
        'temperatura',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'tlc',
        'hidratacion',
        'producto',
        'laboratorio',
        'dosis',
        'via_administracion',
        'tipo_parasito',
        'observaciones',
        'proxima_cita',
        'mensaje_proxima_cita',
    ];
    
    /**
     * Una Desparasitación pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Archivos adjuntos de la desparasitación
     */
    public function archivos()
    {
        return $this->morphMany(ArchivoHistoria::class, 'historia');
    }
}