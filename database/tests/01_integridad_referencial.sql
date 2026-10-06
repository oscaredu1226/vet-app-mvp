-- ============================================
-- PRUEBAS DE INTEGRIDAD REFERENCIAL
-- VetApp - Base de Datos
-- ============================================

-- 1. Verificar todas las foreign keys existentes
SELECT 
    TABLE_NAME as 'Tabla',
    COLUMN_NAME as 'Columna',
    CONSTRAINT_NAME as 'Constraint',
    REFERENCED_TABLE_NAME as 'Tabla Referenciada',
    REFERENCED_COLUMN_NAME as 'Columna Referenciada'
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'solutionvet_db' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

-- 2. Verificar huérfanos en mascotas (sin cliente válido)
SELECT 
    id_mascota,
    nombre,
    id_cliente,
    'Mascota huérfana - cliente no existe' as error
FROM mascotas 
WHERE id_cliente NOT IN (SELECT id_cliente FROM clientes);

-- 3. Verificar huérfanos en ventas (ventas tienen id_mascota, no id_cliente)
SELECT 
    id_venta,
    id_mascota,
    fecha_venta,
    'Venta huérfana - mascota no existe' as error
FROM ventas 
WHERE id_mascota NOT IN (SELECT id_mascota FROM mascotas);

-- 4. Verificar huérfanos en historia clínica (tabla es historia_clinica singular, columna id_historia)
SELECT 
    h.id_historia,
    h.id_mascota,
    'Historia huérfana - mascota no existe' as error
FROM historia_clinica h
WHERE h.id_mascota NOT IN (SELECT id_mascota FROM mascotas);

-- 5. Verificar huérfanos en cola médica
SELECT 
    cm.id,
    cm.id_mascota,
    'Cola médica - mascota no existe' as error
FROM cola_medica cm
WHERE cm.id_mascota NOT IN (SELECT id_mascota FROM mascotas);

-- 6. Verificar huérfanos en examenes (columna es id_examen_lab no id_examen)
SELECT 
    e.id_examen_lab,
    e.id_mascota,
    'Examen huérfano - mascota no existe' as error
FROM examenes_laboratorio e
WHERE e.id_mascota NOT IN (SELECT id_mascota FROM mascotas);

-- 7. Verificar usuarios que agregaron a cola sin existir
SELECT 
    cm.id,
    cm.agregado_por,
    'Usuario inexistente en cola médica' as error
FROM cola_medica cm
WHERE cm.agregado_por NOT IN (SELECT id FROM users);

-- 8. Resumen de integridad (corregido para ventas con id_mascota)
SELECT 
    'Mascotas' as Tabla,
    COUNT(*) as Total,
    SUM(CASE WHEN id_cliente NOT IN (SELECT id_cliente FROM clientes) THEN 1 ELSE 0 END) as Huerfanos
FROM mascotas
UNION ALL
SELECT 
    'Ventas',
    COUNT(*),
    SUM(CASE WHEN id_mascota NOT IN (SELECT id_mascota FROM mascotas) THEN 1 ELSE 0 END)
FROM ventas
UNION ALL
SELECT 
    'Historia Clinica',
    COUNT(*),
    SUM(CASE WHEN id_mascota NOT IN (SELECT id_mascota FROM mascotas) THEN 1 ELSE 0 END)
FROM historia_clinica
UNION ALL
SELECT 
    'Examenes',
    COUNT(*),
    SUM(CASE WHEN id_mascota NOT IN (SELECT id_mascota FROM mascotas) THEN 1 ELSE 0 END)
FROM examenes_laboratorio
UNION ALL
SELECT 
    'Cola Medica',
    COUNT(*),
    SUM(CASE WHEN id_mascota NOT IN (SELECT id_mascota FROM mascotas) THEN 1 ELSE 0 END)
FROM cola_medica;

-- ============================================
-- RESULTADO ESPERADO: 0 huérfanos en todas las tablas
-- ============================================
