<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';
    protected $primaryKey = 'id_tratamiento';

    protected $fillable = [
        'nombre',
    ];
}
