#!/usr/bin/env php
<?php

/**
 * Script de Verificación Automática del Sistema VetApp
 * Ejecuta todas las pruebas y genera un reporte completo
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     SISTEMA DE VERIFICACIÓN AUTOMÁTICA - VetApp          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$startTime = microtime(true);
$results = [];
$errors = [];

// Colores para terminal
$GREEN = "\033[32m";
$RED = "\033[31m";
$YELLOW = "\033[33m";
$BLUE = "\033[34m";
$RESET = "\033[0m";

/**
 * Ejecuta un comando y retorna el resultado
 */
function runCommand($command, $description) {
    global $GREEN, $RED, $YELLOW, $BLUE, $RESET, $results, $errors;
    
    echo "\n{$BLUE}▶ {$description}...{$RESET}\n";
    echo str_repeat("─", 70) . "\n";
    
    $output = [];
    $returnVar = 0;
    
    $start = microtime(true);
    exec($command . " 2>&1", $output, $returnVar);
    $duration = round((microtime(true) - $start), 2);
    
    $success = $returnVar === 0;
    $results[] = [
        'description' => $description,
        'success' => $success,
        'duration' => $duration
    ];
    
    if ($success) {
        echo "{$GREEN}✔ EXITOSO{$RESET} ({$duration}s)\n";
    } else {
        echo "{$RED}✘ FALLIDO{$RESET} ({$duration}s)\n";
        $errors[] = [
            'test' => $description,
            'output' => implode("\n", $output)
        ];
    }
    
    // Mostrar últimas líneas del output
    $lastLines = array_slice($output, -5);
    foreach ($lastLines as $line) {
        echo "  " . $line . "\n";
    }
    
    return $success;
}

/**
 * Verifica conexión a base de datos
 */
function checkDatabase() {
    global $GREEN, $RED, $BLUE, $RESET, $results;
    
    echo "\n{$BLUE}▶ Verificando conexión a base de datos...{$RESET}\n";
    echo str_repeat("─", 70) . "\n";
    
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_DATABASE') ?: 'solutionvet_db';
    $dbUser = getenv('DB_USERNAME') ?: 'root';
    $dbPass = getenv('DB_PASSWORD') ?: '';
    
    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar algunas tablas
        $tables = ['users', 'clientes', 'mascotas', 'ventas'];
        $allExist = true;
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() == 0) {
                echo "{$RED}  ✘ Tabla {$table} no existe{$RESET}\n";
                $allExist = false;
            } else {
                echo "{$GREEN}  ✔ Tabla {$table} OK{$RESET}\n";
            }
        }
        
        $results[] = [
            'description' => 'Conexión a Base de Datos',
            'success' => $allExist,
            'duration' => 0
        ];
        
        return $allExist;
    } catch (PDOException $e) {
        echo "{$RED}✘ Error de conexión: {$e->getMessage()}{$RESET}\n";
        $results[] = [
            'description' => 'Conexión a Base de Datos',
            'success' => false,
            'duration' => 0
        ];
        return false;
    }
}

/**
 * Verifica archivos .env
 */
function checkEnvFile() {
    global $GREEN, $RED, $YELLOW, $BLUE, $RESET, $results;
    
    echo "\n{$BLUE}▶ Verificando archivo .env...{$RESET}\n";
    echo str_repeat("─", 70) . "\n";
    
    if (!file_exists('.env')) {
        echo "{$RED}✘ Archivo .env no encontrado{$RESET}\n";
        $results[] = ['description' => 'Archivo .env', 'success' => false, 'duration' => 0];
        return false;
    }
    
    $required = ['APP_KEY', 'DB_DATABASE', 'DB_USERNAME'];
    $missing = [];
    
    $envContent = file_get_contents('.env');
    foreach ($required as $var) {
        if (strpos($envContent, $var) === false) {
            $missing[] = $var;
        }
    }
    
    if (empty($missing)) {
        echo "{$GREEN}✔ Todas las variables requeridas presentes{$RESET}\n";
        $results[] = ['description' => 'Archivo .env', 'success' => true, 'duration' => 0];
        return true;
    } else {
        echo "{$RED}✘ Variables faltantes: " . implode(', ', $missing) . "{$RESET}\n";
        $results[] = ['description' => 'Archivo .env', 'success' => false, 'duration' => 0];
        return false;
    }
}

// =============== INICIO DE PRUEBAS ===============

echo "\n📋 FASE 1: VERIFICACIONES PRELIMINARES\n";
echo "════════════════════════════════════════\n";

checkEnvFile();
checkDatabase();

echo "\n\n🧪 FASE 2: PRUEBAS DE INTEGRIDAD\n";
echo "════════════════════════════════════════\n";

// Ejecutar tests de Laravel
runCommand(
    'php artisan test --filter=SystemHealthCheckTest',
    'Tests de Salud del Sistema'
);

runCommand(
    'php artisan test --filter=SecurityTest',
    'Tests de Seguridad'
);

runCommand(
    'php artisan test --filter=PerformanceTest',
    'Tests de Performance'
);

runCommand(
    'php artisan test --filter=ModulesTest',
    'Tests de Módulos Funcionales'
);

echo "\n\n🔍 FASE 3: ANÁLISIS DE CÓDIGO\n";
echo "════════════════════════════════════════\n";

// Verificar vulnerabilidades de seguridad
runCommand(
    'composer audit',
    'Auditoría de Seguridad de Dependencias'
);

// Análisis de código estático (si existe)
if (file_exists('vendor/bin/phpstan')) {
    runCommand(
        'vendor/bin/phpstan analyse app',
        'Análisis Estático de Código (PHPStan)'
    );
}

echo "\n\n📊 FASE 4: VERIFICACIÓN DE BASE DE DATOS\n";
echo "════════════════════════════════════════\n";

// Ejecutar queries SQL de verificación
$sqlTests = [
    '01_integridad_referencial.sql',
    '02_indices_performance.sql',
    '03_limpieza_datos.sql'
];

foreach ($sqlTests as $sqlFile) {
    $path = "database/tests/{$sqlFile}";
    if (file_exists($path)) {
        $description = basename($sqlFile, '.sql');
        runCommand(
            "mysql -u root solutionvet_db < {$path}",
            "SQL Test: {$description}"
        );
    }
}

// =============== REPORTE FINAL ===============

$totalDuration = round((microtime(true) - $startTime), 2);
$totalTests = count($results);
$passed = count(array_filter($results, fn($r) => $r['success']));
$failed = $totalTests - $passed;
$passRate = $totalTests > 0 ? round(($passed / $totalTests) * 100, 1) : 0;

echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    REPORTE FINAL                               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "⏱️  Tiempo total: {$totalDuration}s\n";
echo "📊 Total de pruebas: {$totalTests}\n";
echo "{$GREEN}✔ Exitosas: {$passed}{$RESET}\n";
echo "{$RED}✘ Fallidas: {$failed}{$RESET}\n";
echo "📈 Tasa de éxito: {$passRate}%\n";

echo "\n📋 RESUMEN DE PRUEBAS:\n";
echo str_repeat("─", 70) . "\n";

foreach ($results as $result) {
    $status = $result['success'] ? "{$GREEN}✔{$RESET}" : "{$RED}✘{$RESET}";
    $duration = $result['duration'] > 0 ? " ({$result['duration']}s)" : "";
    printf("  %s %-50s%s\n", $status, $result['description'], $duration);
}

if (!empty($errors)) {
    echo "\n\n{$RED}❌ ERRORES DETECTADOS:{$RESET}\n";
    echo str_repeat("─", 70) . "\n";
    
    foreach ($errors as $error) {
        echo "\n{$YELLOW}Test: {$error['test']}{$RESET}\n";
        echo substr($error['output'], 0, 500) . "...\n";
    }
}

echo "\n";

if ($passRate >= 90) {
    echo "{$GREEN}🎉 EXCELENTE: El sistema está en buen estado!{$RESET}\n";
    exit(0);
} elseif ($passRate >= 70) {
    echo "{$YELLOW}⚠️  ADVERTENCIA: Hay algunos problemas que requieren atención{$RESET}\n";
    exit(1);
} else {
    echo "{$RED}❌ CRÍTICO: El sistema tiene problemas graves{$RESET}\n";
    exit(2);
}
