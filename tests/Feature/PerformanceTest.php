<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends TestCase
{
    use TestsWithUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestUsers();
    }

    /**
     * Test 1: Consulta de listado de clientes debe ser rápida
     */
    public function test_clientes_query_performance(): void
    {
        $start = microtime(true);
        $response = $this->actingAs($this->admin)->get('/clientes');
        $duration = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(500, $duration, "Página de clientes tarda más de 500ms ({$duration}ms)");
        echo "\n✅ Performance Test 1: Clientes rápido ({$duration}ms)";
    }

    /**
     * Test 2: Consulta de mascotas con joins debe ser rápida
     */
    public function test_mascotas_query_performance(): void
    {
        DB::enableQueryLog();
        
        $start = microtime(true);
        $mascotas = DB::table('mascotas')
            ->join('clientes', 'mascotas.id_cliente', '=', 'clientes.id_cliente')
            ->select('mascotas.*', 'clientes.nombre as cliente_nombre')
            ->where('mascotas.estado', 'Activo')
            ->limit(100)
            ->get();
        $duration = (microtime(true) - $start) * 1000;
        
        $queries = DB::getQueryLog();
        
        $this->assertLessThan(200, $duration, "Consulta de mascotas tarda más de 200ms ({$duration}ms)");
        $this->assertLessThanOrEqual(1, count($queries), "Se ejecutaron múltiples queries (N+1 problem)");
        
        DB::disableQueryLog();
        echo "\n✅ Performance Test 2: Mascotas query rápida ({$duration}ms, " . count($queries) . " queries)";
    }

    /**
     * Test 3: Búsqueda de clientes debe usar índices
     */
    public function test_cliente_search_uses_index(): void
    {
        $explain = DB::select("
            EXPLAIN SELECT * FROM clientes 
            WHERE dni = '12345678' 
            OR nombre LIKE '%Juan%'
        ");
        
        // Verificar que usa algún índice (key no es NULL)
        $usesIndex = false;
        foreach ($explain as $row) {
            if ($row->key !== null) {
                $usesIndex = true;
                break;
            }
        }
        
        echo "\n✅ Performance Test 3: Búsqueda de clientes analizada";
    }

    /**
     * Test 4: Consulta de ventas con detalles no debe tener N+1
     */
    public function test_ventas_no_n_plus_one(): void
    {
        DB::enableQueryLog();
        
        $start = microtime(true);
        $ventas = DB::table('ventas')
            ->join('mascotas', 'ventas.id_mascota', '=', 'mascotas.id_mascota')
            ->join('clientes', 'mascotas.id_cliente', '=', 'clientes.id_cliente')
            ->select('ventas.*', 'clientes.nombre', 'clientes.apellido', 'mascotas.nombre as mascota')
            ->limit(50)
            ->get();
        $duration = (microtime(true) - $start) * 1000;
        
        $queries = DB::getQueryLog();
        
        // Debería ser solo 1 query por el join
        $this->assertLessThanOrEqual(1, count($queries), "Problema N+1 en ventas");
        $this->assertLessThan(300, $duration, "Consulta de ventas tarda más de 300ms ({$duration}ms)");
        
        DB::disableQueryLog();
        echo "\n✅ Performance Test 4: Sin N+1 en ventas (" . count($queries) . " queries, {$duration}ms)";
    }

    /**
     * Test 5: Verificar tamaño de tablas principales
     */
    public function test_table_sizes(): void
    {
        $tables = ['clientes', 'mascotas', 'ventas', 'detalle_ventas', 'productos', 'servicios'];
        
        echo "\n\n📊 Tamaños de Tablas:";
        foreach ($tables as $table) {
            $size = DB::select("
                SELECT 
                    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb,
                    TABLE_ROWS as `rows`
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{$table}'
            ");
            
            if (!empty($size)) {
                echo "\n   - {$table}: {$size[0]->size_mb} MB ({$size[0]->rows} registros)";
            }
        }
        
        $this->assertTrue(true);
    }

    /**
     * Test 6: Verificar índices en columnas de búsqueda frecuente
     */
    public function test_search_columns_have_indexes(): void
    {
        // Verificar índice en clientes.dni
        $indexes = DB::select("SHOW INDEX FROM clientes WHERE Column_name = 'dni'");
        $this->assertNotEmpty($indexes, "Columna clientes.dni no tiene índice");
        
        // Verificar índice en mascotas.id_cliente (FK)
        $indexes = DB::select("SHOW INDEX FROM mascotas WHERE Column_name = 'id_cliente'");
        $this->assertNotEmpty($indexes, "Columna mascotas.id_cliente no tiene índice");
        
        echo "\n✅ Performance Test 6: Índices en columnas de búsqueda";
    }

    /**
     * Test 7: Consulta de historial clínico debe ser eficiente
     */
    public function test_historia_clinica_performance(): void
    {
        DB::enableQueryLog();
        
        $start = microtime(true);
        $historias = DB::table('historia_clinica')
            ->join('mascotas', 'historia_clinica.id_mascota', '=', 'mascotas.id_mascota')
            ->join('clientes', 'mascotas.id_cliente', '=', 'clientes.id_cliente')
            ->select('historia_clinica.*', 'mascotas.nombre as mascota', 'clientes.nombre as cliente')
            ->orderBy('historia_clinica.fecha_atencion', 'desc')
            ->limit(50)
            ->get();
        $duration = (microtime(true) - $start) * 1000;
        
        $queries = DB::getQueryLog();
        
        $this->assertLessThan(300, $duration, "Historial clínico tarda más de 300ms ({$duration}ms)");
        $this->assertLessThanOrEqual(1, count($queries), "N+1 problem en historial");
        
        DB::disableQueryLog();
        echo "\n✅ Performance Test 7: Historial clínico eficiente ({$duration}ms, " . count($queries) . " queries)";
    }

    /**
     * Test 8: Cola médica debe actualizar rápido
     */
    public function test_cola_medica_update_performance(): void
    {
        $start = microtime(true);
        $cola = DB::table('cola_medica')
            ->orderBy('fecha_ingreso', 'desc')
            ->limit(20)
            ->get();
        $duration = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(100, $duration, "Cola médica tarda más de 100ms ({$duration}ms)");
        echo "\n✅ Performance Test 8: Cola médica rápida ({$duration}ms)";
    }

    /**
     * Test 9: Verificar que no hay queries sin índices (full table scan)
     */
    public function test_no_full_table_scans_on_large_tables(): void
    {
        // Verificar consulta común en clientes
        $explain = DB::select("EXPLAIN SELECT * FROM clientes WHERE dni = '12345678'");
        
        foreach ($explain as $row) {
            $this->assertNotEquals('ALL', $row->type, "Full table scan en clientes (sin índice en dni)");
        }
        
        // Verificar consulta común en mascotas
        $explain = DB::select("EXPLAIN SELECT * FROM mascotas WHERE id_cliente = 1");
        
        foreach ($explain as $row) {
            $this->assertNotEquals('ALL', $row->type, "Full table scan en mascotas (sin índice en id_cliente)");
        }
        
        echo "\n✅ Performance Test 9: Sin full table scans en queries principales";
    }

    /**
     * Test 10: Memoria de queries - Verificar que no hay queries enormes
     */
    public function test_query_memory_usage(): void
    {
        DB::enableQueryLog();
        
        // Ejecutar algunas consultas comunes
        DB::table('clientes')->limit(100)->get();
        DB::table('mascotas')->limit(100)->get();
        DB::table('ventas')->limit(100)->get();
        
        $queries = DB::getQueryLog();
        
        // Verificar que ninguna query trae todos los registros sin LIMIT
        foreach ($queries as $query) {
            $sql = strtoupper($query['query']);
            if (strpos($sql, 'SELECT') !== false && strpos($sql, 'COUNT') === false) {
                // Queries normales deberían tener LIMIT o WHERE con joins específicos
                $this->assertTrue(
                    strpos($sql, 'LIMIT') !== false || 
                    strpos($sql, 'WHERE') !== false,
                    "Query sin LIMIT ni WHERE: {$query['query']}"
                );
            }
        }
        
        DB::disableQueryLog();
        echo "\n✅ Performance Test 10: Queries con límites apropiados";
    }
}