<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenLaboratorio extends Model
{
    use HasFactory;

    protected $table = 'examenes_laboratorio';
    protected $primaryKey = 'id_examen_lab';
    // Timestamps es true por defecto

    protected $fillable = [
        'id_mascota',
        'laboratorio',
        'tipo_analisis',
        'costo',
        'pagado',
        'fecha_envio',
        'subido_historia',
        'lectura_realizada',
    ];

    protected $casts = [
        'fecha_envio' => 'date',
        'pagado' => 'boolean',
        'subido_historia' => 'boolean',
        'lectura_realizada' => 'boolean',
    ];

    /**
     * Un Examen de Laboratorio pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}