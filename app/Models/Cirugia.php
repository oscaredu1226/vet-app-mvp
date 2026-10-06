<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cirugia extends Model
{
    protected $table = 'cirugias';
    protected $primaryKey = 'id_cirugia';

    protected $fillable = [
        'id_mascota', 'fecha', 'medico', 'motivo', 'diagnostico', 'observaciones_pre', 'ayuno',
        'peso_pre', 'temperatura_pre', 'fc_pre', 'fr_pre', 'tlc_pre', 'hidratacion_pre',
        'tipo_cirugia', 'cirujanos', 'observaciones_cirujanos', 'anestesistas',
        'tratamiento_aplicado', 'observaciones_tratamiento',
        'peso_post', 'temperatura_post', 'fc_post', 'fr_post',
        'proxima_cita', 'mensaje_proxima_cita'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'ayuno' => 'boolean',
        'cirujanos' => 'array',    // Convierte JSON a Array automáticamente
        'anestesistas' => 'array'  // Convierte JSON a Array automáticamente
    ];

    // Accessors para que Blade pueda acceder a los datos JSON
    public function getCirujanosDataAttribute()
    {
        return json_encode($this->cirujanos ?? []);
    }

    public function getAnestesistasDataAttribute()
    {
        return json_encode($this->anestesistas ?? []);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Archivos adjuntos de la cirugía
     */
    public function archivos()
    {
        return $this->morphMany(ArchivoHistoria::class, 'historia');
    }
}