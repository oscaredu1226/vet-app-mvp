-- ============================================
-- PRUEBAS DE ÍNDICES Y PERFORMANCE
-- VetApp - Optimización de Base de Datos
-- ============================================

-- 1. Ver todos los índices existentes en tablas principales
SHOW INDEX FROM clientes;
SHOW INDEX FROM mascotas;
SHOW INDEX FROM ventas;
SHOW INDEX FROM historia_clinica;
SHOW INDEX FROM examenes_laboratorio;
SHOW INDEX FROM cola_medica;
SHOW INDEX FROM productos;
SHOW INDEX FROM servicios;

-- 2. Verificar tablas sin índices en foreign keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'solutionvet_db' 
AND REFERENCED_TABLE_NAME IS NOT NULL
AND COLUMN_NAME NOT IN (
    SELECT COLUMN_NAME 
    FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = 'solutionvet_db'
);

-- 3. Analizar consultas más comunes (simular carga)
EXPLAIN SELECT m.*, c.nombre, c.apellido 
FROM mascotas m 
INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
WHERE m.estado = 'Activo';

EXPLAIN SELECT * FROM cola_medica ORDER BY fecha_ingreso DESC;

EXPLAIN SELECT * FROM ventas WHERE fecha_venta BETWEEN '2024-01-01' AND '2024-12-31';

-- 4. Crear índices recomendados si no existen
ALTER TABLE mascotas ADD INDEX IF NOT EXISTS idx_estado (estado);
ALTER TABLE mascotas ADD INDEX IF NOT EXISTS idx_especie (especie);
ALTER TABLE historia_clinica ADD INDEX IF NOT EXISTS idx_fecha_atencion (fecha_atencion);
ALTER TABLE ventas ADD INDEX IF NOT EXISTS idx_fecha_venta (fecha_venta);
ALTER TABLE cola_medica ADD INDEX IF NOT EXISTS idx_fecha_ingreso (fecha_ingreso);
ALTER TABLE examenes_laboratorio ADD INDEX IF NOT EXISTS idx_fecha_envio (fecha_envio);

-- 5. Verificar tamaño de tablas e índices
SELECT 
    table_name AS 'Tabla',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Tamaño Total (MB)',
    ROUND((data_length / 1024 / 1024), 2) AS 'Datos (MB)',
    ROUND((index_length / 1024 / 1024), 2) AS 'Índices (MB)',
    table_rows AS 'Filas'
FROM information_schema.TABLES 
WHERE table_schema = 'solutionvet_db'
ORDER BY (data_length + index_length) DESC;

-- 6. Detectar índices duplicados o innecesarios
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as columns
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'solutionvet_db'
GROUP BY TABLE_NAME, INDEX_NAME
HAVING COUNT(*) > 1;

-- 7. Análisis de cardinalidad de índices
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    CARDINALITY,
    CASE 
        WHEN CARDINALITY < 10 THEN 'Baja cardinalidad - considerar remover'
        WHEN CARDINALITY BETWEEN 10 AND 100 THEN 'Cardinalidad media'
        ELSE 'Buena cardinalidad'
    END as Evaluacion
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'solutionvet_db'
AND INDEX_NAME != 'PRIMARY';

-- ============================================
-- RECOMENDACIONES:
-- - Índices en foreign keys: CRÍTICO
-- - Índices en columnas de búsqueda frecuente: IMPORTANTE
-- - Índices en columnas de fecha para reportes: RECOMENDADO
-- ============================================
