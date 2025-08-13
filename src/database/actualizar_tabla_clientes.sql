-- Script para actualizar la tabla t_clientes con campos faltantes
-- Ejecutar este script en la base de datos para agregar los nuevos campos

-- Agregar campos faltantes a la tabla t_clientes
ALTER TABLE `t_clientes` 
ADD COLUMN `ciudad` varchar(100) DEFAULT NULL AFTER `direccion`,
ADD COLUMN `estado_provincia` varchar(100) DEFAULT NULL AFTER `ciudad`,
ADD COLUMN `codigo_postal` varchar(20) DEFAULT NULL AFTER `estado_provincia`,
ADD COLUMN `fecha_nacimiento` date DEFAULT NULL AFTER `codigo_postal`,
ADD COLUMN `referido_por` varchar(100) DEFAULT NULL AFTER `notas`;

-- Actualizar el enum de metodo_pago_preferido para incluir 'digital'
ALTER TABLE `t_clientes` 
MODIFY COLUMN `metodo_pago_preferido` enum('efectivo','tarjeta','transferencia','cheque','digital') DEFAULT 'efectivo';

-- Actualizar el enum de metodo_pago en t_ventas para incluir 'digital'
ALTER TABLE `t_ventas` 
MODIFY COLUMN `metodo_pago` enum('efectivo','tarjeta','transferencia','cheque','digital') NOT NULL DEFAULT 'efectivo';

-- Agregar índices para los nuevos campos
ALTER TABLE `t_clientes`
ADD KEY `ciudad` (`ciudad`),
ADD KEY `estado_provincia` (`estado_provincia`),
ADD KEY `fecha_nacimiento` (`fecha_nacimiento`);

-- Verificar que los cambios se aplicaron correctamente
DESCRIBE `t_clientes`; 