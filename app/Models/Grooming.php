<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grooming extends Model
{
    use HasFactory;

    protected $table = 'groomings';
    protected $primaryKey = 'id_grooming';
    public $timestamps = false;

    protected $fillable = [
        'numero_turno',
        'id_cliente',
        'id_mascota',
        'id_servicio',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'estado',
        'observaciones',
    ];

    /**
     * Un Grooming pertenece a un Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Un Grooming pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Un Grooming pertenece a un Servicio.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    /**
     * Scope para obtener turnos de hoy
     */
    public function scopeTurnosHoy($query)
    {
        return $query->whereDate('fecha', now()->toDateString());
    }

    /**
     * Scope para obtener turnos programados (fechas futuras)
     */
    public function scopeProgramados($query)
    {
        return $query->whereDate('fecha', '>', now()->toDateString());
    }
}
