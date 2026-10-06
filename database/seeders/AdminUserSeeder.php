<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si ya existe un administrador
        if (!User::where('role', 'administrador')->exists()) {
            User::create([
                'name' => 'Admin',
                'apellido' => 'Sistema',
                'cargo' => 'Administrador del Sistema',
                'email' => 'admin@VetApp.com',
                'usuario' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'administrador',
                // Permisos de exportación
                'puede_exportar_clientes' => true,
                'puede_exportar_mascotas' => true,
                'puede_exportar_ventas' => true,
                'puede_exportar_productos' => true,
                'puede_exportar_servicios' => true,
                'puede_exportar_caja' => true,
                // Permisos de acceso a módulos
                'puede_acceder_caja' => true,
                'puede_acceder_ventas' => true,
                'puede_acceder_egresos' => true,
            ]);

            $this->command->info('Usuario administrador creado exitosamente.');
            $this->command->info('Usuario: admin');
            $this->command->info('Contraseña: admin123');
            $this->command->info('✓ Todos los permisos de exportación habilitados');
            $this->command->info('✓ Todos los permisos de acceso a módulos habilitados');
        } else {
            $this->command->info('Ya existe un usuario administrador.');
        }
    }
}
