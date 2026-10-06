<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\HistoriaClinica;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MassiveDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * PRUEBA DE CARGA: 1000 clientes, 5000 mascotas, 10000 ventas
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando carga masiva de datos de prueba...');
        $faker = Faker::create('es_PE');
        
        // Desactivar temporalmente las foreign key checks para velocidad
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 1. Crear productos y servicios base si no existen
        $this->command->info('📦 Creando productos y servicios...');
        
        $productos = [];
        $servicios = [];
        
        if (Producto::count() < 10) {
            for ($i = 0; $i < 20; $i++) {
                $productos[] = Producto::create([
                    'nombre' => $faker->word() . ' ' . $faker->randomElement(['Collar', 'Shampoo', 'Alimento', 'Juguete']),
                    'descripcion' => $faker->sentence(),
                    'precio' => $faker->randomFloat(2, 10, 500),
                    'precio_compra' => $faker->randomFloat(2, 5, 400),
                    'stock' => $faker->numberBetween(50, 200),
                    'stock_minimo' => $faker->numberBetween(5, 20),
                    'tipo' => $faker->randomElement(['clinica', 'tienda'])
                ]);
            }
        } else {
            $productos = Producto::all()->toArray();
        }
        
        if (Servicio::count() < 5) {
            for ($i = 0; $i < 10; $i++) {
                $servicios[] = Servicio::create([
                    'nombre' => $faker->randomElement(['Consulta General', 'Baño y Corte', 'Vacunación', 'Desparasitación', 'Cirugía Menor', 'Radiografía', 'Ecografía']),
                    'descripcion' => $faker->sentence(),
                    'precio' => $faker->randomFloat(2, 30, 300),
                    'tipo' => $faker->randomElement(['clinica', 'tienda'])
                ]);
            }
        } else {
            $servicios = Servicio::all()->toArray();
        }
        
        // 2. Crear 1000 clientes
        $this->command->info('👥 Creando 1000 clientes...');
        $clientes = [];
        
        for ($i = 0; $i < 1000; $i++) {
            $clientes[] = [
                'nombre' => $faker->firstName(),
                'apellido' => $faker->lastName(),
                'dni' => str_pad($faker->unique()->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'celular' => '9' . $faker->numberBetween(10000000, 99999999),
                'direccion' => $faker->address()
            ];
            
            if (count($clientes) >= 100) {
                Cliente::insert($clientes);
                $clientes = [];
                $this->command->info("  ✅ {$i} clientes creados...");
            }
        }
        if (count($clientes) > 0) {
            Cliente::insert($clientes);
        }
        
        $this->command->info('✅ 1000 clientes creados');
        
        // 3. Crear 5000 mascotas
        $this->command->info('🐶 Creando 5000 mascotas...');
        $clientesIds = Cliente::pluck('id_cliente')->toArray();
        $mascotas = [];
        $mascotasCreadas = 0;
        
        foreach ($clientesIds as $clienteId) {
            $numMascotas = $faker->numberBetween(1, 8);
            
            for ($j = 0; $j < $numMascotas && $mascotasCreadas < 5000; $j++) {
                $especie = $faker->randomElement(['Canino', 'Felino']);
                $mascotas[] = [
                    'id_cliente' => $clienteId,
                    'nombre' => $faker->firstName(),
                    'especie' => $especie,
                    'raza' => $especie == 'Canino' ? $faker->randomElement(['Labrador', 'Pastor Alemán', 'Chihuahua']) : $faker->randomElement(['Siamés', 'Persa', 'Angora']),
                    'genero' => $faker->randomElement(['Macho', 'Hembra']),
                    'fecha_nacimiento' => $faker->dateTimeBetween('-10 years', '-1 month')->format('Y-m-d'),
                    'esterilizado' => $faker->randomElement(['Si', 'No']),
                    'estado' => $faker->randomElement(['Activo', 'Activo', 'Activo', 'Fallecido'])
                ];
                $mascotasCreadas++;
                
                if (count($mascotas) >= 100) {
                    Mascota::insert($mascotas);
                    $mascotas = [];
                    $this->command->info("  ✅ {$mascotasCreadas} mascotas creadas...");
                }
            }
            
            if ($mascotasCreadas >= 5000) break;
        }
        
        if (count($mascotas) > 0) {
            Mascota::insert($mascotas);
        }
        
        $this->command->info('✅ 5000 mascotas creadas');
        
        // 4. Crear 10000 ventas
        $this->command->info('💰 Creando 10000 ventas...');
        $mascotasIds = Mascota::pluck('id_mascota')->toArray();
        $productosIds = Producto::pluck('id_producto')->toArray();
        $serviciosIds = Servicio::pluck('id_servicio')->toArray();
        $ventasCreadas = 0;
        
        for ($i = 0; $i < 10000; $i++) {
            $mascotaId = $faker->randomElement($mascotasIds);
            $fechaVenta = $faker->dateTimeBetween('-2 years', 'now');
            
            // Determinar si es producto o servicio
            $esProducto = $faker->boolean(60);
            $tipoItem = $esProducto ? 'producto' : 'servicio';
            $idItem = $esProducto ? $faker->randomElement($productosIds) : $faker->randomElement($serviciosIds);
            
            $cantidad = $esProducto ? $faker->numberBetween(1, 5) : 1;
            $precioUnitario = $faker->randomFloat(2, 20, 500);
            $subtotal = $cantidad * $precioUnitario;
            
            Venta::create([
                'id_mascota' => $mascotaId,
                'tipo_item' => $tipoItem,
                'id_item' => $idItem,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'medio_pago' => $faker->randomElement(['Efectivo', 'Tarjeta', 'Yape', 'Plin']),
                'tipo_negocio' => $faker->randomElement(['clinica', 'tienda']),
                'fecha_venta' => $fechaVenta,
                'en_caja' => $faker->boolean(90) ? 1 : 0
            ]);
            
            $ventasCreadas++;
            
            if ($ventasCreadas % 500 == 0) {
                $this->command->info("  ✅ {$ventasCreadas} ventas creadas...");
            }
        }
        
        $this->command->info('✅ 10000 ventas creadas');
        
        // Reactivar foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('🎉 Carga masiva completada exitosamente!');
        $this->command->info('📊 Resumen:');
        $this->command->info("  - Clientes: " . Cliente::count());
        $this->command->info("  - Mascotas: " . Mascota::count());
        $this->command->info("  - Ventas: " . Venta::count());
        $this->command->info("  - Productos: " . Producto::count());
        $this->command->info("  - Servicios: " . Servicio::count());
    }
}
