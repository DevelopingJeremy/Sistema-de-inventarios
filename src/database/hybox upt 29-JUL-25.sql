-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-07-2025 a las 19:07:44
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hybox`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_2fa`
--

CREATE TABLE `codigos_2fa` (
  `ID_A2F` int(11) NOT NULL,
  `ID_USUARIO` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `codigos_2fa`
--

INSERT INTO `codigos_2fa` (`ID_A2F`, `ID_USUARIO`, `codigo`, `expiracion`, `usado`) VALUES
(17, 31, '8168', '2025-07-27 21:34:09', 1),
(18, 29, '6658', '2025-07-27 22:02:25', 1),
(19, 29, '2508', '2025-07-27 22:03:32', 1),
(20, 29, '9536', '2025-07-27 22:06:26', 1),
(21, 29, '6725', '2025-07-27 22:07:46', 1),
(22, 29, '8026', '2025-07-27 22:09:07', 1),
(23, 31, '9893', '2025-07-27 22:10:12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `ID_PLAN` int(11) NOT NULL,
  `ID_USUARIO` int(11) NOT NULL,
  `plan` varchar(15) NOT NULL,
  `vencimiento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pruebas`
--

CREATE TABLE `pruebas` (
  `id` int(11) NOT NULL,
  `tipo` varchar(11) NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens`
--

CREATE TABLE `tokens` (
  `ID_TOKEN` int(11) NOT NULL,
  `ID_USUARIO` int(11) NOT NULL,
  `token` text NOT NULL,
  `usado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tokens`
--

INSERT INTO `tokens` (`ID_TOKEN`, `ID_USUARIO`, `token`, `usado`) VALUES
(6, 22, '84646ff1452904c3de46ecbccb703848694f6971c2f3593a49a7091cc3e4fd7c', 1),
(8, 24, '70ae0c2b8c52a9c6d95dbfe51fc66add77e4ac4196e3c9adfd41d42a87687486', 1),
(9, 25, '7b26d10cf7268c5786369c1b4a80934c04514d23329ef01755a0e133fe19af8d', 1),
(13, 30, '7438862a0d7b841c74e7df35798b9d1d21727a11ce174adadaffd7477ae301c5', 0),
(15, 32, 'b62047a95c6f4bb797f6a48b6910c8e223417859a6314b6ccd1e75d7da577474', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_ajustes_inventario`
--

CREATE TABLE `t_ajustes_inventario` (
  `ID_AJUSTE` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `ID_PRODUCTO` int(11) NOT NULL,
  `ID_USUARIO` int(11) NOT NULL,
  `tipo_ajuste` enum('positivo','negativo') NOT NULL,
  `cantidad_ajustada` int(11) NOT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_nuevo` int(11) NOT NULL,
  `valor_ajuste` decimal(10,2) NOT NULL,
  `motivo_ajuste` text NOT NULL,
  `fecha_ajuste` datetime NOT NULL DEFAULT current_timestamp(),
  `responsable` varchar(100) DEFAULT NULL,
  `tipo_diferencia` varchar(50) DEFAULT NULL,
  `documento_respaldo` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `comentarios` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `t_ajustes_inventario`
--

INSERT INTO `t_ajustes_inventario` (`ID_AJUSTE`, `ID_EMPRESA`, `ID_PRODUCTO`, `ID_USUARIO`, `tipo_ajuste`, `cantidad_ajustada`, `stock_anterior`, `stock_nuevo`, `valor_ajuste`, `motivo_ajuste`, `fecha_ajuste`, `responsable`, `tipo_diferencia`, `documento_respaldo`, `observaciones`, `fecha_creacion`, `estado`, `comentarios`) VALUES
(1, 13, 2, 29, 'positivo', 10, 45, 55, 200000.00, 'Corrección de stock - Conteo físico', '2024-01-25 09:30:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(2, 13, 2, 29, 'negativo', 5, 55, 50, 100000.00, 'Pérdida por daño - Productos defectuosos', '2024-01-26 14:15:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(3, 13, 41, 29, 'positivo', 15, 27, 42, 300000.00, 'Reposición física - Devolución de cliente', '2024-01-27 11:45:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(4, 13, 41, 29, 'negativo', 3, 42, 39, 60000.00, 'Merma natural - Productos vencidos', '2024-01-28 16:20:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(5, 13, 46, 29, 'positivo', 8, 23, 31, 304000.00, 'Corrección de conteo - Error en sistema', '2024-01-29 10:10:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(6, 13, 46, 29, 'negativo', 2, 31, 29, 76000.00, 'Producto vencido - Retiro de inventario', '2024-01-30 13:30:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(7, 13, 47, 29, 'positivo', 12, 19, 31, 540000.00, 'Devolución de cliente - Productos en buen estado', '2024-01-31 08:45:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(8, 13, 47, 29, 'negativo', 1, 31, 30, 45000.00, 'Muestra para cliente - Demostración', '2024-02-01 15:15:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(9, 14, 48, 31, 'positivo', 5, 7, 12, 9000.00, 'Corrección de stock - Conteo físico', '2024-01-25 10:20:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(10, 14, 48, 31, 'negativo', 2, 12, 10, 3600.00, 'Producto defectuoso - Retiro de inventario', '2024-01-26 14:30:00', NULL, NULL, NULL, NULL, '2025-07-29 00:43:05', 'aprobado', NULL),
(11, 13, 58, 29, 'negativo', 23, 1061, 1038, 28359.00, '33333333', '2025-07-29 02:44:00', NULL, NULL, NULL, NULL, '2025-07-29 00:44:44', 'aprobado', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_categorias`
--

CREATE TABLE `t_categorias` (
  `ID_CATEGORIA` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `t_categorias`
--

INSERT INTO `t_categorias` (`ID_CATEGORIA`, `ID_EMPRESA`, `nombre_categoria`, `descripcion`, `color`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 13, 'Electrónicos', 'Productos electrónicos y tecnológicos', '#007bff', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(2, 13, 'Accesoriossssss', 'Accesorios para dispositivos electrónicos', '#28a745', 'activo', '2025-07-28 03:44:24', '2025-07-29 15:50:53'),
(3, 13, 'Ropa', 'Vestimenta y accesorios de moda', '#dc3545', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:47:04'),
(4, 13, 'Hogar', 'Productos para el hogar y decoración', '#ffc107', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(5, 13, 'Deportes', 'Artículos deportivos y fitness', '#17a2b8', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(6, 13, 'Libros', 'Libros y material educativo', '#6f42c1', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(7, 13, 'Juguetes', 'Juguetes y entretenimiento', '#fd7e14', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(8, 13, 'Otros', 'Otros productos diversos', '#6c757d', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(9, 14, 'Farmacéuticos', 'Medicamentos y productos farmacéuticos', '#dc3545', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(10, 14, 'Cosméticos', 'Productos de belleza y cuidado personal', '#e83e8c', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(11, 14, 'Suplementos', 'Vitaminas y suplementos nutricionales', '#fd7e14', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(12, 14, 'Higiene', 'Productos de higiene personal', '#20c997', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(13, 14, 'Equipos Médicos', 'Equipos y dispositivos médicos', '#6f42c1', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(14, 14, 'Cuidado Infantil', 'Productos para bebés y niños', '#ffc107', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(15, 14, 'Bienestar', 'Productos para el bienestar y salud', '#28a745', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(16, 14, 'Otros', 'Otros productos de salud', '#6c757d', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(17, 13, 'dddd', 'asdasdasd', '#007bff', 'activo', '2025-07-28 17:59:25', '2025-07-28 17:59:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_empresa`
--

CREATE TABLE `t_empresa` (
  `ID_EMPRESA` int(11) NOT NULL,
  `ID_DUEÑO` int(11) NOT NULL,
  `nombre_empresa` varchar(50) NOT NULL,
  `fecha_creacion` date NOT NULL DEFAULT current_timestamp(),
  `categoria` varchar(50) NOT NULL,
  `empleados` varchar(10) DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `moneda` varchar(40) NOT NULL,
  `pais` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `t_empresa`
--

INSERT INTO `t_empresa` (`ID_EMPRESA`, `ID_DUEÑO`, `nombre_empresa`, `fecha_creacion`, `categoria`, `empleados`, `logo`, `moneda`, `pais`) VALUES
(13, 29, 'Desarrollo Web', '2025-07-26', 'Farmacia', '51-100', NULL, 'NIO', 'AS'),
(14, 31, 'Desarrollo Web', '2025-07-27', 'Fotografía', '6-15', 'logos-empresa/logo_68867b91635d49.17066049.jpg', 'CRC', 'CR'),
(15, 32, 'Marquitos empresario', '2025-07-28', 'Apicultura', '1-5', 'logos-empresa/logo_68884957a01405.84169927.jpg', 'CRC', 'CR');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_movimientos_inventario`
--

CREATE TABLE `t_movimientos_inventario` (
  `ID_MOVIMIENTO` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `ID_PRODUCTO` int(11) NOT NULL,
  `ID_USUARIO` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT 0.00,
  `valor_movimiento` decimal(10,2) NOT NULL,
  `motivo` text NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `referencia` varchar(100) DEFAULT NULL,
  `proveedor_cliente` varchar(100) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `t_movimientos_inventario`
--

INSERT INTO `t_movimientos_inventario` (`ID_MOVIMIENTO`, `ID_EMPRESA`, `ID_PRODUCTO`, `ID_USUARIO`, `tipo_movimiento`, `cantidad`, `precio_unitario`, `valor_movimiento`, `motivo`, `fecha_movimiento`, `referencia`, `proveedor_cliente`, `documento`, `observaciones`, `fecha_creacion`) VALUES
(11, 13, 58, 29, 'entrada', 1000, 0.00, 1233000.00, 'Compramos massssss', '2025-07-28 20:21:00', NULL, NULL, NULL, NULL, '2025-07-28 18:21:36'),
(12, 13, 58, 29, 'salida', 3, 0.00, 3699.00, 'Corrección de error', '2025-07-29 02:36:00', NULL, NULL, NULL, NULL, '2025-07-29 00:36:53'),
(13, 13, 58, 29, 'entrada', 12, 0.00, 14796.00, 'Correción', '2025-07-29 02:37:00', NULL, NULL, NULL, NULL, '2025-07-29 00:37:54'),
(14, 13, 58, 29, 'entrada', 12, 0.00, 14796.00, 'aaaaaaa', '2025-07-29 02:42:00', NULL, NULL, NULL, NULL, '2025-07-29 00:42:31'),
(15, 13, 61, 29, 'salida', 5, 2000.00, 10000.00, 'AAAAA', '2025-07-29 06:35:00', '23123123', '12313', '11233', '12323', '2025-07-29 04:36:16'),
(16, 13, 61, 29, 'entrada', 1, 2000.00, 2000.00, 'Saliditaa', '2025-07-29 07:20:00', '11111', 'Juancito', '2222', 'Salidita', '2025-07-29 05:21:05'),
(17, 13, 47, 29, 'salida', 2, 55000.00, 110000.00, 'aaa', '2025-07-29 18:00:00', '', '', '', '', '2025-07-29 16:01:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_productos`
--

CREATE TABLE `t_productos` (
  `ID_PRODUCTO` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_compra` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `codigo_interno` varchar(50) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `imagen` text DEFAULT NULL,
  `estado` enum('activo','inactivo','agotado') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `actualizado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `t_productos`
--

INSERT INTO `t_productos` (`ID_PRODUCTO`, `ID_EMPRESA`, `nombre_producto`, `descripcion`, `categoria`, `precio`, `precio_compra`, `stock`, `stock_minimo`, `codigo_barras`, `codigo_interno`, `proveedor`, `ubicacion`, `imagen`, `estado`, `fecha_creacion`, `fecha_actualizacion`, `creado_por`, `actualizado_por`) VALUES
(2, 13, 'Reloj', 'Un reloj muy bello', 'dddd', 20000.00, 15000.00, 50, 3, '123123123123', '33333333', 'TechCorp INC', 'Estante 1', 'uploads/img/fotos-productos/6886f52e289b7_1753675054.jpg', 'activo', '2025-07-27 21:20:54', '2025-07-28 11:59:42', 29, 29),
(41, 13, 'Cargador USB-C', 'Cargador USB-C 65W, carga rápida, compatible universal', 'Accesoriossssss', 25000.00, 20000.00, 0, 10, '7891234567898', 'CAR001', 'Distribuidora Norte', 'Estante D-3', NULL, 'activo', '2025-07-27 22:01:34', '2025-07-29 09:50:53', NULL, 29),
(46, 13, 'Router WiFi TP-Link', 'Router WiFi TP-Link Archer C6, AC1200, doble banda', 'Electrónicos', 45000.00, 38000.00, 5, 8, '7891234567903', 'ROU001', 'Distribuidora Norte', 'Estante F-2', NULL, 'activo', '2025-07-27 22:01:34', '2025-07-28 09:54:00', NULL, 29),
(47, 13, 'Altavoces Bluetooth JBL', 'Altavoces Bluetooth JBL Flip 5, sonido portátil', 'Hogar', 55000.00, 45000.00, 16, 6, '7891234567904', '44444', 'Proveedor Express', 'Estante C-3', NULL, 'activo', '2025-07-27 22:01:34', '2025-07-29 10:01:08', NULL, 29),
(48, 14, 'Paracetamol 500mg', 'Paracetamol 500mg, 20 tabletas, alivia dolor y fiebre', 'Farmacéuticos', 2500.00, 1800.00, 100, 20, '7891234568001', 'PAR001', 'Farmacéutica Nacional', 'Estante A-1', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(49, 14, 'Ibuprofeno 400mg', 'Ibuprofeno 400mg, 30 tabletas, antiinflamatorio', 'Farmacéuticos', 3200.00, 2400.00, 80, 15, '7891234568002', 'IBU001', 'Distribuidora Médica', 'Estante A-2', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(50, 14, 'Vitamina C 1000mg', 'Vitamina C 1000mg, 60 tabletas, refuerzo inmunológico', 'Suplementos', 4500.00, 3500.00, 60, 12, '7891234568003', 'VIT001', 'Importadora de Salud', 'Estante B-1', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(51, 14, 'Omega 3 1000mg', 'Omega 3 1000mg, 90 cápsulas, salud cardiovascular', 'Suplementos', 8500.00, 6500.00, 45, 10, '7891234568004', 'OME001', 'Proveedor Farmacéutico', 'Estante B-2', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(52, 14, 'Shampoo Anticaspa', 'Shampoo anticaspa Head & Shoulders 400ml', 'Higiene', 3500.00, 2800.00, 30, 8, '7891234568005', 'SHA001', 'Distribuidora de Cosméticos', 'Estante C-1', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(53, 14, 'Jabón Antibacterial', 'Jabón antibacterial Dial 90g, protección contra bacterias', 'Higiene', 1200.00, 900.00, 5, 15, '7891234568006', 'JAB001', 'Farmacéutica Nacional', 'Estante C-2', NULL, 'activo', '2025-07-27 22:01:34', '2025-07-28 22:02:30', NULL, 31),
(54, 14, 'Pasta Dental Colgate', 'Pasta dental Colgate Total 150g, protección completa', 'Higiene', 2800.00, 2200.00, 0, 12, '7891234568007', 'PAS001', 'Distribuidora Médica', 'Estante C-3', NULL, 'activo', '2025-07-27 22:01:34', '2025-07-28 22:01:49', NULL, 31),
(55, 14, 'Desodorante Rexona', 'Desodorante Rexona Clinical 50ml, protección 48h', 'Higiene', 4200.00, 3400.00, 40, 10, '7891234568008', 'DES001', 'Importadora de Salud', 'Estante C-4', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(56, 14, 'Termómetro Digital', 'Termómetro digital Braun, lectura rápida, pantalla LCD', 'Equipos Médicos', 8500.00, 6800.00, 25, 6, '7891234568009', 'TER001', 'Proveedor Farmacéutico', 'Estante D-1', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(57, 14, 'Tensiómetro Digital', 'Tensiómetro digital Omron, medición automática', 'Equipos Médicos', 45000.00, 36000.00, 8, 3, '7891234568010', 'TEN001', 'Distribuidora de Cosméticos', 'Estante D-2', NULL, 'activo', '2025-07-27 22:01:34', NULL, NULL, NULL),
(58, 13, 'Perejil', 'Un alimento muy rico', 'Juguetes', 1233.00, 132.00, 1038, 3, '331223212', '123', 'TechCorp INC', 'Estante 1', 'uploads/img/fotos-productos/68870433519eb_1753678899.JPEG', 'activo', '2025-07-27 23:01:39', '2025-07-28 18:44:44', 29, 29),
(60, 14, 'aaaaa', 'asd', 'Cuidado Infantil', 22222.00, 200.00, 45, 15, '1444123', '12332', 'Distribuidora Médica', '', '', 'activo', '2025-07-28 22:00:54', NULL, 31, NULL),
(61, 13, 'UEA', 'JUAN CARLITOS', 'Accesoriossssss', 2000.00, 500.00, 41, 15, '7891234127904', '231235123', 'Proveedor Express', 'Estante C-3', 'uploads/img/fotos-productos/68884f8bdb77b_1753763723.jpeg', 'activo', '2025-07-28 22:35:23', '2025-07-29 10:02:32', 29, 29);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_proveedores`
--

CREATE TABLE `t_proveedores` (
  `ID_PROVEEDOR` int(11) NOT NULL,
  `ID_EMPRESA` int(11) NOT NULL,
  `nombre_proveedor` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `t_proveedores`
--

INSERT INTO `t_proveedores` (`ID_PROVEEDOR`, `ID_EMPRESA`, `nombre_proveedor`, `contacto`, `telefono`, `email`, `direccion`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 13, 'ElectroMax S.A.', 'Juan Pérez', '+506 2222-1111', 'contacto@electromax.com', 'San José, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(2, 13, 'TechCorp INC', 'María González', '+506 3333-2222', 'ventas@techcorp.com', 'Heredia, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:46:26'),
(3, 13, 'Importadora Central', 'Carlos Rodríguez', '+506 4444-3333', 'info@importadoracentral.com', 'Alajuela, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(4, 13, 'Distribuidora Norte', 'Ana Martínez', '+506 5555-4444', 'ventas@distribuidoranorte.com', 'San Carlos, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(5, 13, 'Proveedor Express', 'Luis Fernández', '+506 6666-5555', 'pedidos@proveedorexpress.com', 'Cartago, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(6, 14, 'Farmacéutica Nacional', 'Dr. Roberto Jiménez', '+506 7777-6666', 'ventas@farmaceuticanacional.com', 'San José, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(7, 14, 'Distribuidora Médica', 'Dra. Carmen Vargas', '+506 8888-7777', 'pedidos@distribuidoramedica.com', 'Heredia, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(8, 14, 'Importadora de Salud', 'Lic. Patricia Mora', '+506 9999-8888', 'info@importadoradesalud.com', 'Alajuela, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(9, 14, 'Proveedor Farmacéutico', 'Dr. Manuel Rojas', '+506 1111-9999', 'contacto@proveedorfarmaceutico.com', 'Cartago, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24'),
(10, 14, 'Distribuidora de Cosméticos', 'Sra. Laura Herrera', '+506 2222-1111', 'ventas@cosmeticos.com', 'Puntarenas, Costa Rica', 'activo', '2025-07-28 03:44:24', '2025-07-28 03:44:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `t_usuarios`
--

CREATE TABLE `t_usuarios` (
  `ID_USUARIO` int(11) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `correo_verifi` tinyint(1) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `nombre_completo` varchar(65) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `tel_verifi` tinyint(1) NOT NULL,
  `rol` int(11) NOT NULL,
  `ID_EMPRESA` int(11) DEFAULT NULL,
  `a2f` tinyint(1) NOT NULL DEFAULT 0,
  `plan` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `t_usuarios`
--

INSERT INTO `t_usuarios` (`ID_USUARIO`, `correo`, `correo_verifi`, `contraseña`, `nombre_completo`, `telefono`, `tel_verifi`, `rol`, `ID_EMPRESA`, `a2f`, `plan`) VALUES
(29, 'quesadajeremy7@gmail.com', 1, '$2y$10$nNAVj5Yg2H4veRFlm16u6utD1oe5MrTXF99Al1WXvKfHN/r.fYMOa', 'Jeremy Quesada', NULL, 0, 0, 13, 0, NULL),
(31, 'kekan12327@0tires.com', 1, '$2y$10$.lz.K2gYms0dMwKDd9O0lurE7K91GVjw5jx8mm1o6WPac2uWhnwJW', 'Juan Peralta', NULL, 0, 0, 14, 0, NULL),
(32, 'test@mail.com', 1, '$2y$10$nNAVj5Yg2H4veRFlm16u6utD1oe5MrTXF99Al1WXvKfHN/r.fYMOa', 'Carlos Martinez', NULL, 0, 0, 15, 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `codigos_2fa`
--
ALTER TABLE `codigos_2fa`
  ADD PRIMARY KEY (`ID_A2F`),
  ADD KEY `codigos_2fa_ibfk_1` (`ID_USUARIO`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`ID_PLAN`),
  ADD KEY `fk_usuario` (`ID_USUARIO`);

--
-- Indices de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`ID_TOKEN`);

--
-- Indices de la tabla `t_ajustes_inventario`
--
ALTER TABLE `t_ajustes_inventario`
  ADD PRIMARY KEY (`ID_AJUSTE`),
  ADD KEY `ID_EMPRESA` (`ID_EMPRESA`),
  ADD KEY `ID_PRODUCTO` (`ID_PRODUCTO`),
  ADD KEY `ID_USUARIO` (`ID_USUARIO`),
  ADD KEY `tipo_ajuste` (`tipo_ajuste`),
  ADD KEY `fecha_ajuste` (`fecha_ajuste`),
  ADD KEY `estado` (`estado`);

--
-- Indices de la tabla `t_categorias`
--
ALTER TABLE `t_categorias`
  ADD PRIMARY KEY (`ID_CATEGORIA`),
  ADD KEY `ID_EMPRESA` (`ID_EMPRESA`);

--
-- Indices de la tabla `t_empresa`
--
ALTER TABLE `t_empresa`
  ADD PRIMARY KEY (`ID_EMPRESA`);

--
-- Indices de la tabla `t_movimientos_inventario`
--
ALTER TABLE `t_movimientos_inventario`
  ADD PRIMARY KEY (`ID_MOVIMIENTO`),
  ADD KEY `ID_EMPRESA` (`ID_EMPRESA`),
  ADD KEY `ID_PRODUCTO` (`ID_PRODUCTO`),
  ADD KEY `ID_USUARIO` (`ID_USUARIO`),
  ADD KEY `tipo_movimiento` (`tipo_movimiento`),
  ADD KEY `fecha_movimiento` (`fecha_movimiento`);

--
-- Indices de la tabla `t_productos`
--
ALTER TABLE `t_productos`
  ADD PRIMARY KEY (`ID_PRODUCTO`),
  ADD KEY `fk_producto_empresa` (`ID_EMPRESA`),
  ADD KEY `fk_producto_creado_por` (`creado_por`),
  ADD KEY `fk_producto_actualizado_por` (`actualizado_por`),
  ADD KEY `idx_codigo_barras` (`codigo_barras`),
  ADD KEY `idx_codigo_interno` (`codigo_interno`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `t_proveedores`
--
ALTER TABLE `t_proveedores`
  ADD PRIMARY KEY (`ID_PROVEEDOR`),
  ADD KEY `ID_EMPRESA` (`ID_EMPRESA`);

--
-- Indices de la tabla `t_usuarios`
--
ALTER TABLE `t_usuarios`
  ADD PRIMARY KEY (`ID_USUARIO`),
  ADD KEY `fk_empresa` (`ID_EMPRESA`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `codigos_2fa`
--
ALTER TABLE `codigos_2fa`
  MODIFY `ID_A2F` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `ID_PLAN` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tokens`
--
ALTER TABLE `tokens`
  MODIFY `ID_TOKEN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `t_ajustes_inventario`
--
ALTER TABLE `t_ajustes_inventario`
  MODIFY `ID_AJUSTE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `t_categorias`
--
ALTER TABLE `t_categorias`
  MODIFY `ID_CATEGORIA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `t_empresa`
--
ALTER TABLE `t_empresa`
  MODIFY `ID_EMPRESA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `t_movimientos_inventario`
--
ALTER TABLE `t_movimientos_inventario`
  MODIFY `ID_MOVIMIENTO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `t_productos`
--
ALTER TABLE `t_productos`
  MODIFY `ID_PRODUCTO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `t_proveedores`
--
ALTER TABLE `t_proveedores`
  MODIFY `ID_PROVEEDOR` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `t_usuarios`
--
ALTER TABLE `t_usuarios`
  MODIFY `ID_USUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `codigos_2fa`
--
ALTER TABLE `codigos_2fa`
  ADD CONSTRAINT `codigos_2fa_ibfk_1` FOREIGN KEY (`ID_USUARIO`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `planes`
--
ALTER TABLE `planes`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`ID_USUARIO`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `t_ajustes_inventario`
--
ALTER TABLE `t_ajustes_inventario`
  ADD CONSTRAINT `fk_ajustes_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ajustes_producto` FOREIGN KEY (`ID_PRODUCTO`) REFERENCES `t_productos` (`ID_PRODUCTO`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ajustes_usuario` FOREIGN KEY (`ID_USUARIO`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE CASCADE;

--
-- Filtros para la tabla `t_categorias`
--
ALTER TABLE `t_categorias`
  ADD CONSTRAINT `t_categorias_ibfk_1` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE;

--
-- Filtros para la tabla `t_movimientos_inventario`
--
ALTER TABLE `t_movimientos_inventario`
  ADD CONSTRAINT `fk_movimientos_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`ID_PRODUCTO`) REFERENCES `t_productos` (`ID_PRODUCTO`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`ID_USUARIO`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE CASCADE;

--
-- Filtros para la tabla `t_productos`
--
ALTER TABLE `t_productos`
  ADD CONSTRAINT `fk_producto_actualizado_por` FOREIGN KEY (`actualizado_por`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `t_usuarios` (`ID_USUARIO`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `t_proveedores`
--
ALTER TABLE `t_proveedores`
  ADD CONSTRAINT `t_proveedores_ibfk_1` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`) ON DELETE CASCADE;

--
-- Filtros para la tabla `t_usuarios`
--
ALTER TABLE `t_usuarios`
  ADD CONSTRAINT `fk_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
