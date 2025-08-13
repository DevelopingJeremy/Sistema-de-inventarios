-- =====================================================
-- TABLAS PARA MÓDULO DE VENTAS Y CLIENTES
-- =====================================================

-- Tabla de clientes
CREATE TABLE `t_clientes` (
  `ID_CLIENTE` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `tipo_cliente` enum('regular','premium','vip','corporativo') DEFAULT 'regular',
  `metodo_pago_preferido` enum('efectivo','tarjeta','transferencia','cheque') DEFAULT 'efectivo',
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `ultima_compra` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de ventas
CREATE TABLE `t_ventas` (
  `ID_VENTA` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `ID_CLIENTE` int(11) NOT NULL,
  `ID_USUARIO` int(11) NOT NULL,
  `numero_venta` varchar(20) NOT NULL,
  `fecha_venta` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` enum('efectivo','tarjeta','transferencia','cheque') NOT NULL DEFAULT 'efectivo',
  `estado` enum('pendiente','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `referencia_pago` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de venta
CREATE TABLE `t_ventas_detalle` (
  `ID_DETALLE` int(11) NOT NULL,
  `ID_VENTA` int(11) NOT NULL,
  `ID_PRODUCTO` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- DATOS DE EJEMPLO
-- =====================================================

-- Insertar clientes de ejemplo
INSERT INTO `t_clientes` (`ID_CLIENTE`, `ID_EMPRESA`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `tipo_cliente`, `metodo_pago_preferido`, `limite_credito`, `descuento`, `notas`, `estado`, `fecha_registro`, `ultima_compra`) VALUES
(1, 1, 'Juan', 'Pérez', 'juan.perez@email.com', '555-0101', 'Calle Principal 123, Ciudad', 'regular', 'efectivo', 1000.00, 0.00, 'Cliente regular', 'activo', '2024-01-15 10:30:00', '2024-01-20 14:15:00'),
(2, 1, 'María', 'García', 'maria.garcia@email.com', '555-0102', 'Avenida Central 456, Ciudad', 'premium', 'tarjeta', 5000.00, 5.00, 'Cliente premium', 'activo', '2024-01-16 11:45:00', '2024-01-22 16:30:00'),
(3, 1, 'Carlos', 'López', 'carlos.lopez@email.com', '555-0103', 'Plaza Mayor 789, Ciudad', 'vip', 'transferencia', 10000.00, 10.00, 'Cliente VIP', 'activo', '2024-01-17 09:20:00', '2024-01-25 12:45:00'),
(4, 1, 'Ana', 'Martínez', 'ana.martinez@email.com', '555-0104', 'Calle Secundaria 321, Ciudad', 'regular', 'efectivo', 2000.00, 0.00, 'Cliente regular', 'activo', '2024-01-18 15:10:00', '2024-01-23 10:20:00'),
(5, 1, 'Roberto', 'Hernández', 'roberto.hernandez@email.com', '555-0105', 'Boulevard Norte 654, Ciudad', 'corporativo', 'cheque', 25000.00, 15.00, 'Cliente corporativo', 'activo', '2024-01-19 13:25:00', '2024-01-26 17:00:00'),
(6, 1, 'Laura', 'Díaz', 'laura.diaz@email.com', '555-0106', 'Calle Este 987, Ciudad', 'premium', 'tarjeta', 7500.00, 7.50, 'Cliente premium', 'inactivo', '2024-01-20 08:40:00', '2024-01-21 11:30:00');

-- Insertar ventas de ejemplo
INSERT INTO `t_ventas` (`ID_VENTA`, `ID_EMPRESA`, `ID_CLIENTE`, `ID_USUARIO`, `numero_venta`, `fecha_venta`, `subtotal`, `descuento`, `iva`, `total`, `metodo_pago`, `estado`, `referencia_pago`, `notas`) VALUES
(1, 1, 1, 1, 'V-001-2024', '2024-01-20 14:15:00', 150.00, 0.00, 24.00, 174.00, 'efectivo', 'completada', NULL, 'Venta al contado'),
(2, 1, 2, 1, 'V-002-2024', '2024-01-22 16:30:00', 450.00, 22.50, 68.40, 495.90, 'tarjeta', 'completada', 'TXN-123456', 'Pago con tarjeta'),
(3, 1, 3, 1, 'V-003-2024', '2024-01-25 12:45:00', 1200.00, 120.00, 172.80, 1252.80, 'transferencia', 'completada', 'TRF-789012', 'Cliente VIP'),
(4, 1, 4, 1, 'V-004-2024', '2024-01-23 10:20:00', 75.00, 0.00, 12.00, 87.00, 'efectivo', 'completada', NULL, 'Venta pequeña'),
(5, 1, 5, 1, 'V-005-2024', '2024-01-26 17:00:00', 3500.00, 525.00, 475.20, 3450.20, 'cheque', 'pendiente', 'CHK-345678', 'Pedido corporativo'),
(6, 1, 1, 1, 'V-006-2024', '2024-01-27 09:30:00', 200.00, 0.00, 32.00, 232.00, 'efectivo', 'pendiente', NULL, 'Venta pendiente'),
(7, 1, 2, 1, 'V-007-2024', '2024-01-28 11:45:00', 300.00, 15.00, 45.60, 330.60, 'tarjeta', 'cancelada', NULL, 'Venta cancelada');

-- Insertar detalles de venta de ejemplo
INSERT INTO `t_ventas_detalle` (`ID_DETALLE`, `ID_VENTA`, `ID_PRODUCTO`, `cantidad`, `precio_unitario`, `subtotal`, `descuento`) VALUES
(1, 1, 1, 2, 75.00, 150.00, 0.00),
(2, 2, 2, 1, 300.00, 300.00, 15.00),
(3, 2, 3, 1, 150.00, 150.00, 7.50),
(4, 3, 4, 3, 400.00, 1200.00, 120.00),
(5, 4, 5, 1, 75.00, 75.00, 0.00),
(6, 5, 6, 2, 1000.00, 2000.00, 300.00),
(7, 5, 7, 3, 500.00, 1500.00, 225.00),
(8, 6, 8, 1, 200.00, 200.00, 0.00),
(9, 7, 9, 2, 150.00, 300.00, 15.00);

-- =====================================================
-- ÍNDICES Y CLAVES PRIMARIAS
-- =====================================================

-- Índices para t_clientes
ALTER TABLE `t_clientes`
  ADD PRIMARY KEY (`ID_CLIENTE`),
  ADD KEY `ID_EMPRESA` (`ID_EMPRESA`),
  ADD KEY `tipo_cliente` (`tipo_cliente`),
  ADD KEY `estado` (`estado`),
  ADD KEY `fecha_registro` (`fecha_registro`);

-- Índices para t_ventas
ALTER TABLE `t_ventas`
  ADD PRIMARY KEY (`ID_VENTA`),
  ADD KEY `ID_EMPRESA` (`ID_EMPRESA`),
  ADD KEY `ID_CLIENTE` (`ID_CLIENTE`),
  ADD KEY `ID_USUARIO` (`ID_USUARIO`),
  ADD KEY `numero_venta` (`numero_venta`),
  ADD KEY `fecha_venta` (`fecha_venta`),
  ADD KEY `estado` (`estado`),
  ADD KEY `metodo_pago` (`metodo_pago`);

-- Índices para t_ventas_detalle
ALTER TABLE `t_ventas_detalle`
  ADD PRIMARY KEY (`ID_DETALLE`),
  ADD KEY `ID_VENTA` (`ID_VENTA`),
  ADD KEY `ID_PRODUCTO` (`ID_PRODUCTO`);

-- =====================================================
-- AUTO_INCREMENT
-- =====================================================

ALTER TABLE `t_clientes`
  MODIFY `ID_CLIENTE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `t_ventas`
  MODIFY `ID_VENTA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `t_ventas_detalle`
  MODIFY `ID_DETALLE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

-- =====================================================
-- CLAVES FORÁNEAS
-- =====================================================

-- Claves foráneas para t_clientes
ALTER TABLE `t_clientes`
  ADD CONSTRAINT `fk_clientes_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE;

-- Claves foráneas para t_ventas
ALTER TABLE `t_ventas`
  ADD CONSTRAINT `fk_ventas_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`ID_CLIENTE`) REFERENCES `t_clientes` (`ID_CLIENTE`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`ID_USUARIO`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE CASCADE;

-- Claves foráneas para t_ventas_detalle
ALTER TABLE `t_ventas_detalle`
  ADD CONSTRAINT `fk_ventas_detalle_venta` FOREIGN KEY (`ID_VENTA`) REFERENCES `t_ventas` (`ID_VENTA`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ventas_detalle_producto` FOREIGN KEY (`ID_PRODUCTO`) REFERENCES `t_productos` (`ID_PRODUCTO`) ON DELETE CASCADE; 