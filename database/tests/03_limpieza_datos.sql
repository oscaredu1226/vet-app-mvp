-- ============================================
-- PRUEBAS DE LIMPIEZA Y VALIDACIÓN DE DATOS
-- VetApp - Calidad de Datos
-- ============================================

-- 1. Detectar DNI duplicados en clientes
SELECT 
    dni,
    COUNT(*) as cantidad,
    GROUP_CONCAT(id_cliente) as ids_clientes,
    'DNI DUPLICADO' as problema
FROM clientes 
WHERE dni IS NOT NULL AND dni != ''
GROUP BY dni 
HAVING cantidad > 1;

-- 2. Detectar celulares duplicados (en vez de email que no existe)
SELECT 
    celular,
    COUNT(*) as cantidad,
    GROUP_CONCAT(id_cliente) as ids_clientes,
    'CELULAR DUPLICADO' as problema
FROM clientes 
WHERE celular IS NOT NULL AND celular != ''
GROUP BY celular 
HAVING cantidad > 1;

-- 3. Validar formato de DNI (debe ser 8 dígitos)
SELECT 
    id_cliente,
    nombre,
    apellido,
    dni,
    'DNI inválido (no 8 dígitos)' as problema
FROM clientes 
WHERE LENGTH(dni) != 8 OR dni NOT REGEXP '^[0-9]+$';

-- 4. Validar formato de celular (debe ser 9 dígitos)
SELECT 
    id_cliente,
    nombre,
    apellido,
    celular,
    'Celular inválido (no 9 dígitos)' as problema
FROM clientes 
WHERE celular IS NOT NULL 
AND (LENGTH(celular) != 9 OR celular NOT REGEXP '^[0-9]+$');

-- 5. Validar fechas de nacimiento de mascotas (no futuras)
SELECT 
    id_mascota,
    nombre,
    fecha_nacimiento,
    'Fecha de nacimiento futura' as problema
FROM mascotas 
WHERE fecha_nacimiento > CURDATE();

-- 6. Validar fechas de nacimiento muy antiguas (> 30 años)
SELECT 
    id_mascota,
    nombre,
    fecha_nacimiento,
    TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad_años,
    'Mascota con más de 30 años' as problema
FROM mascotas 
WHERE fecha_nacimiento < DATE_SUB(CURDATE(), INTERVAL 30 YEAR);

-- 7. Detectar mascotas con estado inconsistente
SELECT 
    id_mascota,
    nombre,
    estado,
    'Estado no válido' as problema
FROM mascotas 
WHERE estado NOT IN ('Activo', 'Fallecido');

-- 8. Detectar productos con stock negativo
SELECT 
    id_producto,
    nombre,
    stock,
    'Stock negativo' as problema
FROM productos 
WHERE stock < 0;

-- 9. Detectar ventas con cantidades inválidas
SELECT 
    id_venta,
    fecha_venta,
    cantidad,
    'Cantidad inválida en venta' as problema
FROM ventas 
WHERE cantidad <= 0 OR cantidad IS NULL;

-- 10. Detectar precios negativos o cero
SELECT 
    'Productos' as Tabla,
    id_producto as ID,
    nombre,
    precio,
    'Precio inválido' as problema
FROM productos 
WHERE precio <= 0
UNION ALL
SELECT 
    'Servicios',
    id_servicio,
    nombre,
    precio,
    'Precio inválido'
FROM servicios 
WHERE precio <= 0;

-- 11. Resumen de calidad de datos
SELECT 
    'Clientes con DNI inválido' as Validacion,
    COUNT(*) as Cantidad
FROM clientes 
WHERE LENGTH(dni) != 8 OR dni NOT REGEXP '^[0-9]+$'
UNION ALL
SELECT 
    'Clientes con celular inválido',
    COUNT(*)
FROM clientes 
WHERE celular IS NOT NULL AND (LENGTH(celular) != 9 OR celular NOT REGEXP '^[0-9]+$')
UNION ALL
SELECT 
    'Mascotas con fecha futura',
    COUNT(*)
FROM mascotas 
WHERE fecha_nacimiento > CURDATE()
UNION ALL
SELECT 
    'Mascotas con > 30 años',
    COUNT(*)
FROM mascotas 
WHERE fecha_nacimiento < DATE_SUB(CURDATE(), INTERVAL 30 YEAR)
UNION ALL
SELECT 
    'Productos con stock negativo',
    COUNT(*)
FROM productos 
WHERE stock < 0
UNION ALL
SELECT 
    'Precios inválidos',
    COUNT(*)
FROM (
    SELECT id_producto as id, nombre, precio FROM productos WHERE precio <= 0
    UNION ALL
    SELECT id_servicio as id, nombre, precio FROM servicios WHERE precio <= 0
) as precios_invalidos;

-- ============================================
-- RESULTADO ESPERADO: 0 problemas en todas las validaciones
-- ============================================
