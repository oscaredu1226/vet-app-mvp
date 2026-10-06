<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    use HasFactory;

    protected $table = 'consultas';
    protected $primaryKey = 'id_consulta';
    // Timestamps es true por defecto (lo dejamos)

    protected $fillable = [
        'id_mascota',
        'fecha',
        'medico',
        'motivo',
        'peso',
        'temperatura',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'tlc_tiempo_llenado',
        'hidratacion',
        'anamnesis',
        'examen_fisico',
        'diagnostico',
        'plan_tratamiento',
        'examenes',
        'tratamiento',
        'receta',
        'observaciones',
        'proxima_cita',
        'mensaje_proxima_cita',
    ];
    
    /**
     * Una Consulta pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Archivos adjuntos de la consulta
     */
    public function archivos()
    {
        return $this->morphMany(ArchivoHistoria::class, 'historia');
    }
}