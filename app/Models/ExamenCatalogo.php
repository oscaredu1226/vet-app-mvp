<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenCatalogo extends Model
{
    use HasFactory;

    protected $table = 'examenes_catalogo';
    protected $primaryKey = 'id_examen_catalogo';

    protected $fillable = [
        'nombre',
    ];
}
