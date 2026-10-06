<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;
    
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    public $timestamps = false; // Sin timestamps en la migración

    protected $fillable = [
        'nombre',
        'precio',
        'tipo',
        'descripcion',
    ];
}