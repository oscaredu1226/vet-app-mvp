<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\User;
use App\Models\Venta;
use App\Models\ColaMedica;
use Illuminate\Support\Facades\DB;

class SystemHealthCheckTest extends TestCase
{
    use TestsWithUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestUsers();
    }

    /**
     * Test 1: Verificar conexión a base de datos
     */
    public function test_database_connection(): void
    {
        $this->assertDatabaseHas('users', ['usuario' => 'admin']);
        echo "\n✅ Test 1: Conexión a BD exitosa";
    }

    /**
     * Test 2: Verificar integridad referencial - Mascotas
     */
    public function test_no_orphan_mascotas(): void
    {
        $orphans = DB::select("
            SELECT COUNT(*) as count 
            FROM mascotas 
            WHERE id_cliente NOT IN (SELECT id_cliente FROM clientes)
        ");
        
        $this->assertEquals(0, $orphans[0]->count, "Existen mascotas huérfanas sin cliente");
        echo "\n✅ Test 2: Sin mascotas huérfanas";
    }

    /**
     * Test 3: Verificar integridad referencial - Ventas
     */
    public function test_no_orphan_ventas(): void
    {
        $orphans = DB::select("
            SELECT COUNT(*) as count 
            FROM ventas 
            WHERE id_mascota NOT IN (SELECT id_mascota FROM mascotas)
        ");
        
        $this->assertEquals(0, $orphans[0]->count, "Existen ventas huérfanas sin mascota");
        echo "\n✅ Test 3: Sin ventas huérfanas";
    }

    /**
     * Test 4: Validar formato de DNI en clientes
     */
    public function test_dni_format_validation(): void
    {
        $invalid = DB::select("
            SELECT COUNT(*) as count 
            FROM clientes 
            WHERE LENGTH(dni) != 8 OR dni NOT REGEXP '^[0-9]+$'
        ");
        
        $this->assertEquals(0, $invalid[0]->count, "Existen DNIs con formato inválido");
        echo "\n✅ Test 4: Todos los DNI tienen formato válido";
    }

    /**
     * Test 5: Validar formato de celular en clientes
     */
    public function test_celular_format_validation(): void
    {
        $invalid = DB::select("
            SELECT COUNT(*) as count 
            FROM clientes 
            WHERE celular IS NOT NULL 
            AND (LENGTH(celular) != 9 OR celular NOT REGEXP '^[0-9]+$')
        ");
        
        $this->assertEquals(0, $invalid[0]->count, "Existen celulares con formato inválido");
        echo "\n✅ Test 5: Todos los celulares tienen formato válido";
    }

    /**
     * Test 6: Validar fechas de nacimiento de mascotas
     */
    public function test_mascota_fecha_nacimiento_validation(): void
    {
        $futuras = DB::select("
            SELECT COUNT(*) as count 
            FROM mascotas 
            WHERE fecha_nacimiento > CURDATE()
        ");
        
        $this->assertEquals(0, $futuras[0]->count, "Existen mascotas con fecha de nacimiento futura");
        
        $antiguas = DB::select("
            SELECT COUNT(*) as count 
            FROM mascotas 
            WHERE fecha_nacimiento < DATE_SUB(CURDATE(), INTERVAL 30 YEAR)
        ");
        
        $this->assertEquals(0, $antiguas[0]->count, "Existen mascotas con más de 30 años");
        echo "\n✅ Test 6: Fechas de nacimiento válidas";
    }

    /**
     * Test 7: Verificar autenticación y roles
     */
    public function test_admin_can_access_usuarios(): void
    {
        $response = $this->actingAs($this->admin)->get('/usuarios');
        $response->assertStatus(200);
        echo "\n✅ Test 7: Admin puede acceder a /usuarios";
    }

    /**
     * Test 8: Verificar que usuario normal NO puede acceder a /usuarios
     */
    public function test_usuario_cannot_access_usuarios(): void
    {
        $response = $this->actingAs($this->usuario)->get('/usuarios');
        $response->assertStatus(403);
        echo "\n✅ Test 8: Usuario normal no puede acceder a /usuarios";
    }

    /**
     * Test 9: Verificar que las rutas requieren autenticación
     */
    public function test_routes_require_authentication(): void
    {
        $routes = [
            '/clientes',
            '/mascotas',
            '/ventas',
            '/productos',
            '/servicios'
        ];
        
        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
        
        echo "\n✅ Test 9: Rutas protegidas requieren autenticación";
    }

    /**
     * Test 10: Verificar índices en tablas principales
     */
    public function test_important_indexes_exist(): void
    {
        $tables = ['clientes', 'mascotas', 'ventas', 'productos', 'servicios'];
        
        foreach ($tables as $table) {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            $this->assertNotEmpty($indexes, "Tabla {$table} no tiene índices");
        }
        
        echo "\n✅ Test 10: Índices existen en tablas principales";
    }

    /**
     * Test 11: Verificar que no hay productos con stock negativo
     */
    public function test_no_negative_stock(): void
    {
        $negative = DB::select("
            SELECT COUNT(*) as count 
            FROM productos 
            WHERE stock < 0
        ");
        
        $this->assertEquals(0, $negative[0]->count, "Existen productos con stock negativo");
        echo "\n✅ Test 11: Sin stock negativo";
    }

    /**
     * Test 12: Verificar que no hay precios inválidos
     */
    public function test_no_invalid_prices(): void
    {
        $productosInvalidos = DB::select("
            SELECT COUNT(*) as count 
            FROM productos 
            WHERE precio <= 0
        ");
        
        $serviciosInvalidos = DB::select("
            SELECT COUNT(*) as count 
            FROM servicios 
            WHERE precio <= 0
        ");
        
        $this->assertEquals(0, $productosInvalidos[0]->count, "Existen productos con precio inválido");
        $this->assertEquals(0, $serviciosInvalidos[0]->count, "Existen servicios con precio inválido");
        echo "\n✅ Test 12: Todos los precios son válidos";
    }

    /**
     * Test 13: Performance - Consulta de mascotas debe ser rápida
     */
    public function test_mascota_query_performance(): void
    {
        $start = microtime(true);
        
        $mascotas = DB::table('mascotas')
            ->join('clientes', 'mascotas.id_cliente', '=', 'clientes.id_cliente')
            ->where('mascotas.estado', 'Activo')
            ->limit(100)
            ->get();
        
        $duration = (microtime(true) - $start) * 1000; // ms
        
        $this->assertLessThan(200, $duration, "Consulta de mascotas tarda más de 200ms");
        echo "\n✅ Test 13: Consulta de mascotas rápida ({$duration}ms)";
    }

    /**
     * Test 14: Verificar que storage es accesible
     */
    public function test_storage_is_accessible(): void
    {
        $this->assertTrue(is_dir(storage_path('app/public')), "Directorio storage/app/public no existe");
        $this->assertTrue(is_writable(storage_path('app/public')), "storage/app/public no es escribible");
        echo "\n✅ Test 14: Storage accesible y escribible";
    }

    /**
     * Test 15: Verificar archivos .env críticos
     */
    public function test_env_critical_variables(): void
    {
        $this->assertNotEmpty(env('APP_KEY'), "APP_KEY no está configurada");
        $this->assertNotEmpty(env('DB_DATABASE'), "DB_DATABASE no está configurada");
        $this->assertNotEmpty(env('DB_USERNAME'), "DB_USERNAME no está configurada");
        echo "\n✅ Test 15: Variables de entorno críticas configuradas";
    }
}
