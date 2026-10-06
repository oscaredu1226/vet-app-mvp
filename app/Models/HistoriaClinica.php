<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    use HasFactory;
    
    protected $table = 'historia_clinica';
    protected $primaryKey = 'id_historia';
    public $timestamps = false; // Usa 'fecha_atencion' en lugar de timestamps

    protected $fillable = [
        'id_mascota',
        'fecha_atencion',
        'motivo_atencion',
        'anamnesis',
        'descripcion_caso',
        'temperatura',
        'peso',
        'frecuencia_cardiaca',
        'tlc_tiempo_llenado',
        'dth_deshidratacion',
        'examen_clinico',
        'diagnostico',
        'observaciones',
        'tratamiento',
    ];

    /**
     * Una Historia Clínica pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}