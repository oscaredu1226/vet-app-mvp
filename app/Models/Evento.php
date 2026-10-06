<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';
    protected $primaryKey = 'id_evento';
    // Timestamps es true por defecto

    protected $fillable = [
        'id_mascota',
        'fecha',
        'hora',
        'id_consulta',
        'titulo',
        'descripcion',
        'estado',
    ];

    /**
     * Un Evento pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}