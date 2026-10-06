<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColaMedica extends Model
{
    use HasFactory;

    protected $table = 'cola_medica';

    protected $fillable = [
        'id_mascota',
        'agregado_por',
        'fecha_ingreso',
        'motivo'
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
    ];

    // Relación con Mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    // Relación con Usuario que agregó
    public function usuario()
    {
        return $this->belongsTo(User::class, 'agregado_por');
    }
}
