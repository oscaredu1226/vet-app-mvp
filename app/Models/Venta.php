<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    public $timestamps = false; // Usa 'fecha_venta'

    protected $fillable = [
        'id_mascota',
        'tipo_item',
        'id_item',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'medio_pago',
        'tipo_negocio',
        'fecha_venta',
        'en_caja',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'en_caja' => 'boolean',
    ];

    /**
     * Una Venta pertenece a una Mascota.
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Una Venta (total) tiene muchos Pagos (parciales).
     */
    public function pagos()
    {
        return $this->hasMany(PagoVenta::class, 'id_venta', 'id_venta');
    }

    /**
     * Relación con Producto (cuando tipo_item = 'producto')
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_item', 'id_producto');
    }

    /**
     * Relación con Servicio (cuando tipo_item = 'servicio')
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_item', 'id_servicio');
    }

    /**
     * Obtener el item relacionado según el tipo_item
     */
    public function item()
    {
        if ($this->tipo_item === 'producto') {
            return $this->producto();
        } elseif ($this->tipo_item === 'servicio') {
            return $this->servicio();
        }
        return null;
    }
}