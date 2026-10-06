<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalCaja extends Model
{
    use HasFactory;

    protected $table = 'total_caja';
    protected $primaryKey = 'id_total';
    // Timestamps es true por defecto
    
    protected $fillable = [
        'monto',
        'tipo_operacion',
        'concepto',
        'usuario',
    ];
}