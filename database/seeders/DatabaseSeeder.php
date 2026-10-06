<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuario administrador para testing
        $this->call(AdminUserSeeder::class);
        
        // Crear usuario normal para testing
        \App\Models\User::firstOrCreate(
            ['usuario' => 'usuario_test'],
            [
                'name' => 'Usuario',
                'apellido' => 'Test',
                'cargo' => 'Veterinario',
                'email' => 'usuario@test.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'usuario',
            ]
        );
    }
}
