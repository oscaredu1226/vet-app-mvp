<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCirugia extends Model
{
    protected $table = 'tipos_cirugia';
    protected $primaryKey = 'id_tipo_cirugia';
    protected $fillable = ['nombre'];
}