<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacuna extends Model
{
    use HasFactory;

    protected $table = 'vacunas';
    protected $primaryKey = 'id_vacuna';
    // Timestamps es true por defecto

    protected $fillable = [
        'id_mascota',
        'fecha',
        'medico',
        'peso',
        'temperatura',
        'hidratacion',
        'tlc_tiempo_llenado',
        'anamnesis',
        'vacuna_aplicada',
        'laboratorio',
        'lote',
        'vencimiento',
        'via_administracion',
        'dosis',
        'tipo_proxima_vacuna',
        'observaciones',
        'proxima_dosis',
        'mensaje_proxima_cita',
    ];
    
    /**
     * Una Vacuna pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Archivos adjuntos de la vacuna
     */
    public function archivos()
    {
        return $this->morphMany(ArchivoHistoria::class, 'historia');
    }
}