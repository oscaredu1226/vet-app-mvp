<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'celular',
        'dni',
        'direccion',
    ];

    /**
     * Un Cliente tiene muchas Mascotas.
     */
    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Un Cliente tiene muchas Citas.
     */
    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_cliente', 'id_cliente');
    }
}