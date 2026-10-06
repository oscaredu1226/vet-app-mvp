<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait TestsWithUsers
{
    protected $admin;
    protected $usuario;

    /**
     * Crear usuarios de prueba para los tests
     */
    protected function createTestUsers()
    {
        // Crear usuario administrador
        $this->admin = User::firstOrCreate(
            ['usuario' => 'admin'],
            [
                'name' => 'Admin',
                'apellido' => 'Sistema',
                'cargo' => 'Administrador del Sistema',
                'email' => 'admin@VetApp.com',
                'password' => Hash::make('admin123'),
                'role' => 'administrador',
            ]
        );

        // Crear usuario normal
        $this->usuario = User::firstOrCreate(
            ['usuario' => 'usuario_test'],
            [
                'name' => 'Usuario',
                'apellido' => 'Test',
                'cargo' => 'Veterinario',
                'email' => 'usuario@test.com',
                'password' => Hash::make('password'),
                'role' => 'usuario',
            ]
        );
    }
}
