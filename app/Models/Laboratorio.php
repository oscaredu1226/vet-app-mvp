<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    use HasFactory;

    protected $table = 'laboratorios';
    protected $primaryKey = 'id_laboratorio';

    protected $fillable = [
        'nombre',
    ];
}
