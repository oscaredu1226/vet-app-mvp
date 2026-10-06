<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;
    
    protected $table = 'configuracion';
    // Timestamps es true por defecto

    protected $fillable = [
        'clave',
        'valor',
        'logo',
    ];
}