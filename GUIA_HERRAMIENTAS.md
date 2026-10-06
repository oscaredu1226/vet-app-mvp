# 🎯 GUÍA DE USO - HERRAMIENTAS DE TESTING

## 📋 ÍNDICE
1. [Laravel Debugbar](#1-laravel-debugbar)
2. [Enlightn Security Checker](#2-enlightn-security-checker)
3. [Tests Automatizados](#3-tests-automatizados)
4. [Queries SQL de Verificación](#4-queries-sql-de-verificación)
5. [Seeder de Carga Masiva](#5-seeder-de-carga-masiva)
6. [Script de Verificación Completa](#6-script-de-verificación-completa)

---

## 1. LARAVEL DEBUGBAR

### ¿Qué es?
Herramienta de debugging y profiling para Laravel que muestra información detallada sobre:
- Queries SQL ejecutadas
- Tiempo de ejecución de cada query
- Uso de memoria
- Variables de sesión
- Logs del sistema
- Rutas y controladores ejecutados

### Instalación
```bash
composer require barryvdh/laravel-debugbar --dev
```
✅ **YA INSTALADO** (versión 3.16.3)

### Uso
1. **Activar modo debug** en `.env`:
   ```
   APP_DEBUG=true
   ```

2. **Acceder a cualquier página** del sistema (ejemplo: `/clientes`)

3. **Ver el panel de Debugbar** en la parte inferior de la página

4. **Explorar las pestañas:**
   - **Timeline**: Secuencia de eventos
   - **Queries**: Todas las consultas SQL con tiempo de ejecución
   - **Models**: Modelos Eloquent utilizados
   - **Request**: Datos de la petición HTTP
   - **Session**: Variables de sesión
   - **Views**: Vistas renderizadas
   - **Route**: Información de la ruta

### Ejemplo de Análisis de Performance
```
Si ves en la pestaña "Queries":

SELECT * FROM mascotas WHERE id_cliente = 1 (2.5ms)
SELECT * FROM clientes WHERE id = 1 (1.2ms)
SELECT * FROM mascotas WHERE id_cliente = 2 (2.3ms)
SELECT * FROM clientes WHERE id = 2 (1.1ms)
...

Esto indica un problema N+1. Solución: usar eager loading
Mascota::with('cliente')->get();
```

### Desactivar en Producción
```env
APP_DEBUG=false
```

---

## 2. ENLIGHTN SECURITY CHECKER

### ¿Qué es?
Escáner de vulnerabilidades de seguridad que analiza las dependencias de Composer buscando CVEs (Common Vulnerabilities and Exposures) conocidas.

### Instalación
```bash
composer require enlightn/security-checker --dev
```
✅ **YA INSTALADO** (versión 2.0.0)

### Uso
```bash
# Ejecutar auditoría de seguridad
composer audit

# Output esperado (si todo está bien):
No security vulnerability advisories found
```

### Interpretación de Resultados
Si encuentra vulnerabilidades, verás algo como:
```
Security vulnerability advisories found:

symfony/http-kernel
  CVE-2021-32693: HTTP cache poisoning
  Affected versions: >=2.0.0,<4.4.35|>=5.0.0,<5.3.12
  Fixed version: 4.4.35, 5.3.12
```

**Acción:** Actualizar el paquete vulnerable
```bash
composer update symfony/http-kernel
```

### Frecuencia Recomendada
- **Desarrollo:** Semanal
- **Antes de deploy:** Siempre
- **CI/CD:** En cada commit

---

## 3. TESTS AUTOMATIZADOS

### Archivos de Test Creados
- ✅ `tests/Feature/SystemHealthCheckTest.php` (15 tests)
- ✅ `tests/Feature/SecurityTest.php` (10 tests)
- ✅ `tests/Feature/PerformanceTest.php` (10 tests)
- ✅ `tests/Feature/ModulesTest.php` (10 tests)

### Comandos de Ejecución

#### Ejecutar TODOS los tests
```bash
php artisan test
```

#### Ejecutar tests específicos
```bash
# Tests de salud del sistema
php artisan test --filter=SystemHealthCheckTest

# Tests de seguridad
php artisan test --filter=SecurityTest

# Tests de performance
php artisan test --filter=PerformanceTest

# Tests de módulos funcionales
php artisan test --filter=ModulesTest
```

#### Ejecutar un test individual
```bash
php artisan test --filter=test_database_connection
```

#### Detener en el primer fallo
```bash
php artisan test --stop-on-failure
```

#### Ver output detallado
```bash
php artisan test --testdox
```

### Interpretación de Resultados
```
✓ database connection                    0.20s  ← EXITOSO
⨯ no orphan ventas                       0.04s  ← FALLIDO
- routes require auth                    0.03s  ← OMITIDO

Tests:  1 failed, 1 skipped, 13 passed (30 assertions)
```

---

## 4. QUERIES SQL DE VERIFICACIÓN

### Archivos Creados
- ✅ `database/tests/01_integridad_referencial.sql`
- ✅ `database/tests/02_indices_performance.sql`
- ✅ `database/tests/03_limpieza_datos.sql`

### Uso Individual

#### Test 1: Integridad Referencial
Verifica que no haya registros huérfanos.

```bash
mysql -u root solutionvet_db < database/tests/01_integridad_referencial.sql
```

**Resultado esperado:** 0 en todas las columnas `huerfanos`

#### Test 2: Índices y Performance
Analiza índices existentes y sugiere mejoras.

```bash
mysql -u root solutionvet_db < database/tests/02_indices_performance.sql
```

**Analiza:**
- Índices por tabla
- EXPLAIN de queries comunes
- Tamaño de tablas

#### Test 3: Limpieza de Datos
Detecta problemas de calidad de datos.

```bash
mysql -u root solutionvet_db < database/tests/03_limpieza_datos.sql
```

**Busca:**
- DNI duplicados
- Emails duplicados
- Formatos inválidos
- Fechas futuras
- Stock negativo
- Precios inválidos

### Ejecutar Todos a la Vez
```bash
# Windows PowerShell
Get-Content database/tests/01_integridad_referencial.sql | mysql -u root solutionvet_db
Get-Content database/tests/02_indices_performance.sql | mysql -u root solutionvet_db
Get-Content database/tests/03_limpieza_datos.sql | mysql -u root solutionvet_db
```

---

## 5. SEEDER DE CARGA MASIVA

### Archivo
`database/seeders/MassiveDataSeeder.php`

### Propósito
Generar grandes volúmenes de datos de prueba para testing de carga y performance.

### Capacidad
- **1,000 clientes**
- **5,000 mascotas**
- **10,000 ventas**

### Uso
```bash
php artisan db:seed --class=MassiveDataSeeder
```

### ⚠️ ADVERTENCIA
Este comando agregará MILES de registros a tu base de datos. 

**Recomendaciones:**
1. Usar solo en ambiente de desarrollo/testing
2. Hacer backup de la BD antes de ejecutar
3. Tener suficiente espacio en disco

### Restaurar la BD después del test
```bash
# Opción 1: Eliminar registros generados
# (el seeder marca registros de prueba)

# Opción 2: Restaurar desde backup
mysql -u root solutionvet_db < backup.sql

# Opción 3: Recrear BD
php artisan migrate:fresh --seed
```

### Personalizar el Seeder
Editar `database/seeders/MassiveDataSeeder.php`:
```php
// Cambiar cantidades
private $clientesCount = 500;    // En vez de 1000
private $mascotasCount = 2000;   // En vez de 5000
private $ventasCount = 5000;     // En vez de 10000
```

---

## 6. SCRIPT DE VERIFICACIÓN COMPLETA

### Archivo
`run_all_tests.php`

### ¿Qué hace?
Ejecuta TODAS las pruebas automáticamente en 4 fases:
1. **Verificaciones Preliminares** (archivo .env, conexión BD)
2. **Pruebas de Integridad** (tests de Laravel)
3. **Análisis de Código** (composer audit, PHPStan)
4. **Verificación de BD** (queries SQL)

### Uso
```bash
php run_all_tests.php
```

### Output
```
╔════════════════════════════════════════════════════════════════╗
║     SISTEMA DE VERIFICACIÓN AUTOMÁTICA - VetApp                ║
╚════════════════════════════════════════════════════════════════╝

📋 FASE 1: VERIFICACIONES PRELIMINARES
════════════════════════════════════════
▶ Verificando archivo .env...
✔ Todas las variables requeridas presentes

▶ Verificando conexión a base de datos...
✔ Tabla users OK
✔ Tabla clientes OK
...

🧪 FASE 2: PRUEBAS DE INTEGRIDAD
════════════════════════════════════════
▶ Tests de Salud del Sistema...
✔ EXITOSO (1.78s)

...

╔════════════════════════════════════════════════════════════════╗
║                    REPORTE FINAL                               ║
╚════════════════════════════════════════════════════════════════╝

⏱️  Tiempo total: 15.45s
📊 Total de pruebas: 35
✔ Exitosas: 26
✘ Fallidas: 9
📈 Tasa de éxito: 74.3%

🎉 EXCELENTE: El sistema está en buen estado!
```

### Códigos de Salida
- **0**: Todo exitoso (≥90% tests pasados)
- **1**: Advertencia (70-89% tests pasados)
- **2**: Crítico (<70% tests pasados)

### Usar en CI/CD
Ejecuta el siguiente comando en la etapa de pruebas de tu herramienta de integración continua:

```bash
php run_all_tests.php
```

Configura esa etapa para detener el despliegue si el comando devuelve un código de error.

---

## 📊 WORKFLOW RECOMENDADO

### Durante Desarrollo
1. **Activar Debugbar**: `APP_DEBUG=true`
2. **Monitorear queries** mientras desarrollas nuevas features
3. **Ejecutar tests** al terminar cada módulo:
   ```bash
   php artisan test --filter=ModulesTest
   ```

### Antes de Commit
```bash
# Tests rápidos
php artisan test --filter=SystemHealthCheckTest
```

### Antes de Merge/Deploy
```bash
# Verificación completa
php run_all_tests.php

# Security audit
composer audit
```

### Después de Deploy (Producción)
```bash
# Ejecutar queries SQL de verificación
mysql -u user -p database < database/tests/01_integridad_referencial.sql
```

### Testing de Carga (Mensual)
```bash
# Generar datos masivos
php artisan db:seed --class=MassiveDataSeeder

# Ejecutar tests de performance
php artisan test --filter=PerformanceTest

# Limpiar BD
php artisan migrate:fresh --seed
```

---

## 🚨 RESOLUCIÓN DE PROBLEMAS

### "Class not found" en tests
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Debugbar no aparece
1. Verificar `APP_DEBUG=true` en `.env`
2. Limpiar caché: `php artisan config:clear`
3. Verificar que estás en una ruta web (no API)

### Tests fallan por tiempo
Aumentar timeout en `phpunit.xml`:
```xml
<php>
    <env name="TIMEOUT" value="30"/>
</php>
```

### MySQL "Access Denied"
Verificar credenciales en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solutionvet_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## ✅ CHECKLIST DE CALIDAD

Antes de considerar el sistema "production-ready":

- [ ] Todos los tests de SystemHealthCheck pasan
- [ ] Al menos 80% de tests de seguridad pasan
- [ ] Queries principales < 200ms
- [ ] Sin registros huérfanos en BD
- [ ] Sin vulnerabilidades críticas (composer audit)
- [ ] Debugbar desactivado en producción
- [ ] APP_DEBUG=false en producción
- [ ] Backup de BD configurado
- [ ] Monitoring configurado (opcional: Sentry)

---

**📘 Documentación actualizada:** <?php echo date('Y-m-d'); ?>

**🔄 Mantener actualizado:** Ejecutar `composer audit` semanalmente
