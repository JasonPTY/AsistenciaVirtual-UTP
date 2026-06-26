-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-06-2026 a las 10:35:35
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
-- Base de datos: `loodle_system`
--
CREATE DATABASE IF NOT EXISTS `loodle_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `loodle_system`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `cedula_profesor` varchar(12) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `id_curso`, `cedula_profesor`, `fecha`, `hora`) VALUES
(166, 23, '9-123-4321', '2024-12-03', '07:55:00'),
(167, 5, '9-123-4321', '2025-01-04', '04:57:00'),
(169, 25, '9-123-4321', '2025-01-04', '09:05:00'),
(170, 5, '9-123-4321', '2024-12-03', '13:35:00'),
(171, 5, '9-123-4321', '2024-12-18', '00:00:00'),
(172, 5, '9-123-4321', '2024-12-18', '00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_detalle`
--

DROP TABLE IF EXISTS `asistencia_detalle`;
CREATE TABLE `asistencia_detalle` (
  `id_asistencia_detalle` int(11) NOT NULL,
  `id_asistencia` int(11) DEFAULT NULL,
  `cedula` varchar(12) DEFAULT NULL,
  `asistencia` enum('Presente','Ausente','Tardanza') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia_detalle`
--

INSERT INTO `asistencia_detalle` (`id_asistencia_detalle`, `id_asistencia`, `cedula`, `asistencia`) VALUES
(555, 166, '9-763-2168', 'Presente'),
(558, 167, '9-763-2168', 'Ausente'),
(568, 169, '9-763-2168', 'Presente'),
(570, 170, '9-763-2168', 'Ausente'),
(575, 172, '9-763-2168', 'Presente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

DROP TABLE IF EXISTS `carreras`;
CREATE TABLE `carreras` (
  `id_carrera` int(11) NOT NULL,
  `nombre_carrera` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id_carrera`, `nombre_carrera`) VALUES
(1, 'Ingeniería Mecánica'),
(2, 'Ingeniería Industrial'),
(3, 'Ingeniería en Sistemas Computacionales'),
(4, 'Ingeniería Civil'),
(5, 'Ingeniería Eléctrica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases`
--

DROP TABLE IF EXISTS `clases`;
CREATE TABLE `clases` (
  `id_clase` int(11) NOT NULL,
  `id_curso` int(11) DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miércoles','jueves','viernes','sábado','domingo') DEFAULT NULL,
  `hora_clase` time NOT NULL,
  `cedula` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clases`
--

INSERT INTO `clases` (`id_clase`, `id_curso`, `dia_semana`, `hora_clase`, `cedula`) VALUES
(1, 5, 'martes', '07:50:00', '9-123-4321'),
(2, 6, 'lunes', '09:30:00', '9-123-4321'),
(7, 25, 'viernes', '14:00:00', '9-123-4321'),
(23, 23, 'miércoles', '01:50:00', '9-123-4321'),
(24, 23, 'lunes', '03:50:00', '9-123-4321'),
(25, 23, 'lunes', '04:00:00', '9-123-4321');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

DROP TABLE IF EXISTS `cursos`;
CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `nombre_curso` varchar(50) NOT NULL,
  `id_grupo` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `nombre_curso`, `id_grupo`) VALUES
(1, 'Mecánica de Fluidos', '1LS001'),
(2, 'Termodinámica', '1LS002'),
(3, 'Optimización en la Producción', '2LS001'),
(4, 'Gestión de la Calidad', '2LS002'),
(5, 'Fundamentos de Programación', '3LS001'),
(6, 'Estructuras de Datos', '3LS002'),
(7, 'Estructuras de Concreto', '4LS001'),
(8, 'Topografía', '4LS002'),
(9, 'Circuitos Eléctricos', '5LS001'),
(10, 'Electrónica Digital', '5LS002'),
(21, 'Introducción a la Programación', '3LS001'),
(22, 'Algoritmos y Estructuras de Datos', '3LS001'),
(23, 'Bases de Datos', '3LS001'),
(24, 'Desarrollo Web Frontend', '3LS001'),
(25, 'Desarrollo Web Backend', '3LS001'),
(26, 'Ingeniería de Software', '3LS001'),
(27, 'Sistemas Operativos', '3LS001'),
(28, 'Redes y Comunicaciones', '3LS001'),
(29, 'Inteligencia Artificial', '3LS001'),
(30, 'Seguridad Informática', '3LS001'),
(31, 'Circuitos Eléctricos I', '3LS001'),
(32, 'Electromagnetismo', '3LS001'),
(33, 'Máquinas Eléctricas', '3LS001'),
(34, 'Electrónica Analógica', '3LS001'),
(35, 'Control Automático', '3LS001'),
(36, 'Electrónica Digital', '3LS001'),
(37, 'Instalaciones Eléctricas_', '3LS001'),
(38, 'Generación de Energía Eléctrica', '3LS001'),
(39, 'Distribución de Energía Eléctrica', '3LS001'),
(40, 'Energías Renovables', '3LS001'),
(42, 'Fundamentos de Hacking', '3LS001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE `estudiantes` (
  `cedula` varchar(12) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `id_grupo` varchar(10) DEFAULT NULL,
  `estado_academico` enum('Activo','Retirado','Suspendido') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`cedula`, `id_carrera`, `id_grupo`, `estado_academico`) VALUES
('9-763-2168', 3, '3LS001', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes_cursos`
--

DROP TABLE IF EXISTS `estudiantes_cursos`;
CREATE TABLE `estudiantes_cursos` (
  `id_curso` int(11) NOT NULL,
  `cedula` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes_cursos`
--

INSERT INTO `estudiantes_cursos` (`id_curso`, `cedula`) VALUES
(5, '9-763-2168'),
(6, '9-763-2168'),
(21, '9-763-2168'),
(22, '9-763-2168'),
(23, '9-763-2168'),
(24, '9-763-2168'),
(25, '9-763-2168'),
(26, '9-763-2168'),
(27, '9-763-2168'),
(28, '9-763-2168'),
(29, '9-763-2168'),
(30, '9-763-2168');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `id_grupo` varchar(10) NOT NULL,
  `nombre_grupo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id_grupo`, `nombre_grupo`) VALUES
('1LS001', 'Grupo Mecanica 1'),
('1LS002', 'Grupo Mecanica 2'),
('2LS001', 'Grupo Industrial 1'),
('2LS002', 'Grupo Industrial 2'),
('3LS001', 'Grupo Sistemas 1'),
('3LS002', 'Grupo Sistemas 2'),
('4LS001', 'Grupo Civil 1'),
('4LS002', 'Grupo Civil 2'),
('5LS001', 'Grupo Electrica 1'),
('5LS002', 'Grupo Electrica 2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

DROP TABLE IF EXISTS `intentos_login`;
CREATE TABLE `intentos_login` (
  `id` int(11) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 0,
  `ultimo_intento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `intentos_login`
--

INSERT INTO `intentos_login` (`id`, `correo`, `intentos`, `ultimo_intento`) VALUES
(1, 'jason.arena@utp.ac.pa', 0, '2026-06-26 08:20:22'),
(2, 'mili.jovio@gmail.com', 0, '2026-06-26 08:18:32'),
(3, 'carlos.mendoza@utp.ac.pa', 0, '2026-06-26 08:11:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `es_urgente` tinyint(1) DEFAULT 0,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT 'no_leida',
  `cedula_profesor` varchar(12) NOT NULL,
  `correo_destinatario` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_usuarios`
--

DROP TABLE IF EXISTS `notificaciones_usuarios`;
CREATE TABLE `notificaciones_usuarios` (
  `id` int(11) NOT NULL,
  `id_notificacion` int(11) NOT NULL,
  `cedula_profesor` varchar(50) NOT NULL,
  `cedula_estudiante` varchar(50) NOT NULL,
  `fecha_recibido` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

DROP TABLE IF EXISTS `profesores`;
CREATE TABLE `profesores` (
  `cedula` varchar(12) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_grupo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`cedula`, `id_curso`, `id_grupo`) VALUES
('9-123-4321', 5, '3LS001'),
('9-123-4321', 6, '3LS002'),
('9-123-4321', 23, '3LS001'),
('9-123-4321', 25, '3LS001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor_curso`
--

DROP TABLE IF EXISTS `profesor_curso`;
CREATE TABLE `profesor_curso` (
  `cedula_profesor` varchar(12) NOT NULL,
  `id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesor_curso`
--

INSERT INTO `profesor_curso` (`cedula_profesor`, `id_curso`) VALUES
('9-123-4321', 5),
('9-123-4321', 6),
('9-123-4321', 23),
('9-123-4321', 25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuarios`
--

DROP TABLE IF EXISTS `sesiones_usuarios`;
CREATE TABLE `sesiones_usuarios` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `inicio_sesion` datetime NOT NULL,
  `fin_sesion` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesiones_usuarios`
--

INSERT INTO `sesiones_usuarios` (`id`, `cedula`, `inicio_sesion`, `fin_sesion`, `ip_address`, `user_agent`) VALUES
(1, '9-763-2168', '2026-06-26 09:57:50', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0'),
(2, '9-123-4321', '2026-06-26 09:59:26', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0'),
(3, '7-1543-6550', '2026-06-26 10:00:11', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0'),
(4, '1-222-3333', '2026-06-26 10:10:24', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0'),
(5, '9-123-4321', '2026-06-26 10:11:26', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0'),
(6, '1-222-3333', '2026-06-26 10:18:33', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0'),
(7, '9-763-2168', '2026-06-26 10:20:22', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_usuario`
--

DROP TABLE IF EXISTS `tipos_usuario`;
CREATE TABLE `tipos_usuario` (
  `id_tipoUsuario` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_usuario`
--

INSERT INTO `tipos_usuario` (`id_tipoUsuario`, `tipo`) VALUES
(1, 'Administrador'),
(2, 'Estudiante'),
(3, 'Docente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens_recuerdo`
--

DROP TABLE IF EXISTS `tokens_recuerdo`;
CREATE TABLE `tokens_recuerdo` (
  `id` int(11) NOT NULL,
  `cedula` varchar(12) NOT NULL,
  `token` varchar(64) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `cedula` varchar(12) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `id_tipoUsuario` int(11) NOT NULL,
  `pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`cedula`, `nombre`, `apellido`, `correo`, `id_tipoUsuario`, `pass`) VALUES
('1-222-3333', 'Milagros', 'Jovio', 'mili.jovio@gmail.com', 2, '$2y$10$RuP0V8rrfNZUYiBfGOUOyeFqMru0b7twR0GXI8pmrvPw85e1dF1KG'),
('7-1543-6550', 'Víctor', 'Santos', 'victor.santos@utp.ac.pa', 1, 'victor123'),
('9-123-4321', 'Carlos', 'Mendoza', 'carlos.mendoza@utp.ac.pa', 3, 'carlos123'),
('9-763-2168', 'Jason', 'Arena', 'jason.arena@utp.ac.pa', 2, 'jason123');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `fk_asistencia_id_curso` (`id_curso`);

--
-- Indices de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
  ADD PRIMARY KEY (`id_asistencia_detalle`),
  ADD KEY `id_asistencia` (`id_asistencia`),
  ADD KEY `cedula_estudiante` (`cedula`);

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id_carrera`);

--
-- Indices de la tabla `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id_clase`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `cedula` (`cedula`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `fk_cursos_id_grupo` (`id_grupo`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`cedula`),
  ADD KEY `fk_estudiantes_id_carrera` (`id_carrera`),
  ADD KEY `fk_estudiantes_id_grupo` (`id_grupo`);

--
-- Indices de la tabla `estudiantes_cursos`
--
ALTER TABLE `estudiantes_cursos`
  ADD PRIMARY KEY (`id_curso`,`cedula`),
  ADD KEY `cedula` (`cedula`);

--
-- Indices de la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id_grupo`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_profesor_notificacion` (`cedula_profesor`);

--
-- Indices de la tabla `notificaciones_usuarios`
--
ALTER TABLE `notificaciones_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_notificacion` (`id_notificacion`),
  ADD KEY `cedula_profesor` (`cedula_profesor`),
  ADD KEY `cedula_estudiante` (`cedula_estudiante`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`cedula`,`id_curso`,`id_grupo`),
  ADD KEY `fk_profesores_id_curso` (`id_curso`),
  ADD KEY `fk_profesores_id_grupo` (`id_grupo`);

--
-- Indices de la tabla `profesor_curso`
--
ALTER TABLE `profesor_curso`
  ADD KEY `fk_profesor_curso` (`id_curso`);

--
-- Indices de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula` (`cedula`);

--
-- Indices de la tabla `tipos_usuario`
--
ALTER TABLE `tipos_usuario`
  ADD PRIMARY KEY (`id_tipoUsuario`);

--
-- Indices de la tabla `tokens_recuerdo`
--
ALTER TABLE `tokens_recuerdo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`cedula`),
  ADD KEY `fk_usuarios_id_tipoUsuario` (`id_tipoUsuario`),
  ADD KEY `idx_correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
  MODIFY `id_asistencia_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=580;

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clases`
--
ALTER TABLE `clases`
  MODIFY `id_clase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones_usuarios`
--
ALTER TABLE `notificaciones_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tipos_usuario`
--
ALTER TABLE `tipos_usuario`
  MODIFY `id_tipoUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tokens_recuerdo`
--
ALTER TABLE `tokens_recuerdo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_id_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
  ADD CONSTRAINT `asistencia_detalle_ibfk_1` FOREIGN KEY (`id_asistencia`) REFERENCES `asistencia` (`id_asistencia`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencia_detalle_ibfk_2` FOREIGN KEY (`cedula`) REFERENCES `estudiantes` (`cedula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clases`
--
ALTER TABLE `clases`
  ADD CONSTRAINT `clases_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
  ADD CONSTRAINT `clases_ibfk_2` FOREIGN KEY (`cedula`) REFERENCES `usuarios` (`cedula`);

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `fk_cursos_id_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `fk_estudiantes_id_carrera` FOREIGN KEY (`id_carrera`) REFERENCES `carreras` (`id_carrera`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estudiantes_id_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes_cursos`
--
ALTER TABLE `estudiantes_cursos`
  ADD CONSTRAINT `estudiantes_cursos_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `estudiantes_cursos_ibfk_2` FOREIGN KEY (`cedula`) REFERENCES `estudiantes` (`cedula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_profesor_notificacion` FOREIGN KEY (`cedula_profesor`) REFERENCES `usuarios` (`cedula`);

--
-- Filtros para la tabla `notificaciones_usuarios`
--
ALTER TABLE `notificaciones_usuarios`
  ADD CONSTRAINT `notificaciones_usuarios_ibfk_1` FOREIGN KEY (`id_notificacion`) REFERENCES `notificaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_usuarios_ibfk_2` FOREIGN KEY (`cedula_profesor`) REFERENCES `usuarios` (`cedula`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_usuarios_ibfk_3` FOREIGN KEY (`cedula_estudiante`) REFERENCES `usuarios` (`cedula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD CONSTRAINT `fk_profesores_id_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_profesores_id_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `profesor_curso`
--
ALTER TABLE `profesor_curso`
  ADD CONSTRAINT `fk_profesor_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);

--
-- Filtros para la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  ADD CONSTRAINT `sesiones_usuarios_ibfk_1` FOREIGN KEY (`cedula`) REFERENCES `usuarios` (`cedula`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_id_tipoUsuario` FOREIGN KEY (`id_tipoUsuario`) REFERENCES `tipos_usuario` (`id_tipoUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
