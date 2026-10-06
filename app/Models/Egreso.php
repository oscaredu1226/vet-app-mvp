<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';
    protected $primaryKey = 'id_egreso';
    public $timestamps = false; // Sin timestamps

    protected $fillable = [
        'descripcion',
        'monto',
        'fecha',
    ];
}