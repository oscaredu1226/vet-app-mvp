<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'apellido',
        'cargo',
        'email',
        'usuario',
        'password',
        'role',
        'puede_exportar_clientes',
        'puede_exportar_mascotas',
        'puede_exportar_ventas',
        'puede_exportar_productos',
        'puede_exportar_servicios',
        'puede_exportar_caja',
        'puede_acceder_caja',
        'puede_acceder_ventas',
        'puede_acceder_egresos',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'puede_exportar_clientes' => 'boolean',
        'puede_exportar_mascotas' => 'boolean',
        'puede_exportar_ventas' => 'boolean',
        'puede_exportar_productos' => 'boolean',
        'puede_exportar_servicios' => 'boolean',
        'puede_exportar_caja' => 'boolean',
        'puede_acceder_caja' => 'boolean',
        'puede_acceder_ventas' => 'boolean',
        'puede_acceder_egresos' => 'boolean',
    ];

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin()
    {
        return $this->role === 'administrador';
    }

    /**
     * Verifica si el usuario es un usuario normal
     */
    public function isUser()
    {
        return $this->role === 'usuario';
    }
}
