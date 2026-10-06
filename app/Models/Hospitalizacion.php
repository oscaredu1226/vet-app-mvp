<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospitalizacion extends Model
{
    use HasFactory;

    protected $table = 'hospitalizaciones';
    protected $primaryKey = 'id_hospitalizacion';
    public $timestamps = false; // Sin timestamps

    protected $fillable = [
        'id_mascota',
        'fecha_ingreso',
        'fecha_salida',
        'motivo',
        'observaciones',
    ];

    /**
     * Una Hospitalización pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}