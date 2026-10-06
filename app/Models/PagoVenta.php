<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoVenta extends Model
{
    use HasFactory;

    protected $table = 'pagos_venta';
    protected $primaryKey = 'id_pago';
    public $timestamps = false; // Usa 'fecha_pago'

    protected $fillable = [
        'id_venta',
        'id_mascota',
        'medio_pago',
        'monto',
        'fecha_pago',
    ];

    /**
     * Un Pago pertenece a una Venta principal.
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    /**
     * Un Pago pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}