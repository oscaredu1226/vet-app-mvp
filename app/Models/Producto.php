<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false; // Sin timestamps en la migración

    protected $fillable = [
        'nombre',
        'precio',
        'precio_compra',
        'stock',
        'stock_minimo',
        'tipo',
        'descripcion',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    /**
     * Verifica si el producto está próximo a vencer (30 días o menos)
     */
    public function proximoAVencer()
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }
        return $this->fecha_vencimiento->diffInDays(now(), false) <= 30;
    }
}