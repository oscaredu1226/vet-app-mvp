<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';
    protected $primaryKey = 'id_cita';
    public $timestamps = false; // Sin timestamps

    protected $fillable = [
        'id_cliente',
        'id_mascota',
        'fecha',
        'hora',
        'motivo',
    ];

    /**
     * Una Cita pertenece a un Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Una Cita pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}