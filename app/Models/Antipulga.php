<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antipulga extends Model
{
    use HasFactory;

    protected $table = 'antipulgas';
    protected $primaryKey = 'id_antipulgas';

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
        'observaciones',
        'proxima_cita',
        'mensaje_proxima_cita',
    ];

    protected $casts = [
        'fecha' => 'date',
        'proxima_cita' => 'date',
    ];
    
    /**
     * Una Antipulga pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Archivos adjuntos del antipulgas
     */
    public function archivos()
    {
        return $this->morphMany(ArchivoHistoria::class, 'historia');
    }
}
