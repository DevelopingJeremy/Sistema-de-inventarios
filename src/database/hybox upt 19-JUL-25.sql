-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-07-2025 a las 08:05:14
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

CREATE DATABASE hybox;
use hybox;

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
(9, 25, '7b26d10cf7268c5786369c1b4a80934c04514d23329ef01755a0e133fe19af8d', 1);

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
-- Indices de la tabla `t_empresa`
--
ALTER TABLE `t_empresa`
  ADD PRIMARY KEY (`ID_EMPRESA`);

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
  MODIFY `ID_A2F` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  MODIFY `ID_TOKEN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `t_empresa`
--
ALTER TABLE `t_empresa`
  MODIFY `ID_EMPRESA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `t_usuarios`
--
ALTER TABLE `t_usuarios`
  MODIFY `ID_USUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
-- Filtros para la tabla `t_usuarios`
--
ALTER TABLE `t_usuarios`
  ADD CONSTRAINT `fk_empresa` FOREIGN KEY (`ID_EMPRESA`) REFERENCES `t_empresa` (`ID_EMPRESA`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
