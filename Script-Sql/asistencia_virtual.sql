-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-01-2025 a las 03:00:39
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
-- Base de datos: `asistencia_virtual`
--
CREATE DATABASE IF NOT EXISTS `asistencia_virtual` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `asistencia_virtual`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

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
(556, 166, '9-1543-9804', 'Presente'),
(557, 166, '9-1543-9263', 'Presente'),
(558, 167, '9-763-2168', 'Ausente'),
(559, 167, '5-1543-5639', 'Presente'),
(560, 167, '5-1543-6451', 'Presente'),
(561, 167, '5-1543-7586', 'Presente'),
(562, 167, '5-1543-7231', 'Presente'),
(568, 169, '9-763-2168', 'Presente'),
(569, 169, '9-1543-9804', 'Presente'),
(570, 170, '9-763-2168', 'Ausente'),
(571, 170, '5-1543-5639', 'Presente'),
(572, 170, '5-1543-6451', 'Presente'),
(573, 170, '5-1543-7586', 'Presente'),
(574, 170, '5-1543-7231', 'Presente'),
(575, 172, '9-763-2168', 'Presente'),
(576, 172, '5-1543-5639', 'Presente'),
(577, 172, '5-1543-6451', 'Presente'),
(578, 172, '5-1543-7586', 'Presente'),
(579, 172, '5-1543-7231', 'Presente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

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
(1, 5, 'martes', '07:50:00', '9-763-2168'),
(2, 6, 'lunes', '09:30:00', '9-763-2168'),
(3, 21, 'lunes', '08:00:00', '9-763-2168'),
(4, 22, 'martes', '09:30:00', '9-763-2168'),
(5, 23, 'miércoles', '10:45:00', '9-763-2168'),
(6, 24, 'jueves', '11:00:00', '9-763-2168'),
(7, 25, 'viernes', '14:00:00', '9-763-2168'),
(8, 26, 'lunes', '15:30:00', '9-763-2168'),
(9, 27, 'martes', '16:00:00', '9-763-2168'),
(10, 28, 'miércoles', '17:00:00', '9-763-2168'),
(11, 29, 'jueves', '18:00:00', '9-763-2168'),
(12, 30, 'viernes', '19:00:00', '9-763-2168'),
(13, 31, 'lunes', '08:00:00', '9-1543-9804'),
(14, 32, 'martes', '09:30:00', '9-1543-9804'),
(15, 33, 'miércoles', '10:45:00', '9-1543-9804'),
(16, 34, 'jueves', '11:00:00', '9-1543-9804'),
(17, 35, 'viernes', '14:00:00', '9-1543-9804'),
(18, 36, 'lunes', '15:30:00', '9-1543-9804'),
(19, 37, 'martes', '16:00:00', '9-1543-9804'),
(20, 38, 'miércoles', '17:00:00', '9-1543-9804'),
(21, 39, 'jueves', '18:00:00', '9-1543-9804'),
(22, 40, 'viernes', '19:00:00', '9-1543-9804'),
(23, 23, 'miércoles', '01:50:00', '9-1543-9263'),
(24, 23, 'lunes', '03:50:00', '9-1543-9263'),
(25, 23, 'lunes', '04:00:00', '9-1543-9263');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

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
('1-1543-2173', 1, '1LS001', 'Activo'),
('1-1543-3928', 1, '1LS001', 'Activo'),
('1-1543-8123', 1, '1LS001', 'Activo'),
('1-1543-8706', 1, '1LS001', 'Activo'),
('1-1543-9471', 1, '1LS001', 'Activo'),
('10-1543-4035', 5, '5LS002', 'Activo'),
('10-1543-5729', 5, '5LS002', 'Activo'),
('10-1543-6492', 5, '5LS002', 'Activo'),
('10-1543-8006', 5, '5LS002', 'Activo'),
('10-1543-9605', 5, '5LS002', 'Activo'),
('2-1543-1837', 1, '1LS002', 'Activo'),
('2-1543-2471', 1, '1LS002', 'Activo'),
('2-1543-4387', 1, '1LS002', 'Activo'),
('2-1543-4731', 1, '1LS002', 'Activo'),
('2-1543-4923', 1, '1LS002', 'Activo'),
('3-1543-2048', 2, '2LS001', 'Activo'),
('3-1543-2682', 2, '2LS001', 'Activo'),
('3-1543-3689', 2, '2LS001', 'Activo'),
('3-1543-6521', 2, '2LS001', 'Activo'),
('3-1543-8254', 2, '2LS001', 'Activo'),
('4-1543-1982', 2, '2LS002', 'Activo'),
('4-1543-4812', 2, '2LS002', 'Activo'),
('4-1543-5439', 2, '2LS002', 'Activo'),
('4-1543-7362', 2, '2LS002', 'Activo'),
('4-1543-9357', 2, '2LS002', 'Activo'),
('5-1543-5487', 3, '3LS001', 'Activo'),
('5-1543-5639', 3, '3LS001', 'Activo'),
('5-1543-6451', 3, '3LS001', 'Activo'),
('5-1543-7231', 3, '3LS001', 'Activo'),
('5-1543-7586', 3, '3LS001', 'Activo'),
('6-1543-2365', 3, '3LS002', 'Activo'),
('6-1543-2894', 3, '3LS002', 'Activo'),
('6-1543-3064', 3, '3LS002', 'Activo'),
('6-1543-6357', 3, '3LS002', 'Activo'),
('6-1543-7382', 3, '3LS002', 'Activo'),
('7-1543-3904', 4, '4LS001', 'Activo'),
('7-1543-4681', 4, '4LS001', 'Activo'),
('7-1543-8137', 4, '4LS001', 'Activo'),
('7-1543-8362', 4, '4LS001', 'Activo'),
('7-1543-8420', 4, '4LS001', 'Activo'),
('8-1543-2543', 4, '4LS002', 'Activo'),
('8-1543-3729', 4, '4LS002', 'Activo'),
('8-1543-5173', 4, '4LS002', 'Activo'),
('8-1543-7023', 4, '4LS002', 'Activo'),
('8-1543-9872', 4, '4LS002', 'Activo'),
('9-1543-1945', 5, '5LS001', 'Activo'),
('9-1543-3641', 5, '5LS001', 'Activo'),
('9-1543-5319', 5, '5LS001', 'Activo'),
('9-1543-8412', 5, '5LS001', 'Activo'),
('9-1543-9263', 3, '3LS002', 'Activo'),
('9-1543-9804', 5, '5LS001', 'Activo'),
('9-763-2168', 3, '3LS001', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes_cursos`
--

CREATE TABLE `estudiantes_cursos` (
  `id_curso` int(11) NOT NULL,
  `cedula` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes_cursos`
--

INSERT INTO `estudiantes_cursos` (`id_curso`, `cedula`) VALUES
(1, '1-1543-2173'),
(1, '1-1543-3928'),
(1, '1-1543-8123'),
(1, '1-1543-8706'),
(1, '1-1543-9471'),
(2, '2-1543-1837'),
(2, '2-1543-2471'),
(2, '2-1543-4387'),
(2, '2-1543-4731'),
(2, '2-1543-4923'),
(3, '3-1543-2048'),
(3, '3-1543-2682'),
(3, '3-1543-3689'),
(3, '3-1543-6521'),
(3, '3-1543-8254'),
(4, '4-1543-1982'),
(4, '4-1543-4812'),
(4, '4-1543-5439'),
(4, '4-1543-7362'),
(4, '4-1543-9357'),
(5, '5-1543-5487'),
(5, '5-1543-5639'),
(5, '5-1543-6451'),
(5, '5-1543-7231'),
(5, '5-1543-7586'),
(5, '9-763-2168'),
(6, '6-1543-2365'),
(6, '6-1543-2894'),
(6, '6-1543-3064'),
(6, '6-1543-6357'),
(6, '6-1543-7382'),
(6, '9-763-2168'),
(7, '7-1543-3904'),
(7, '7-1543-4681'),
(7, '7-1543-8137'),
(7, '7-1543-8362'),
(7, '7-1543-8420'),
(7, '9-1543-9804'),
(8, '8-1543-2543'),
(8, '8-1543-3729'),
(8, '8-1543-5173'),
(8, '8-1543-7023'),
(8, '8-1543-9872'),
(9, '8-1543-2543'),
(9, '9-1543-1945'),
(9, '9-1543-3641'),
(9, '9-1543-5319'),
(9, '9-1543-8412'),
(9, '9-1543-9804'),
(10, '10-1543-4035'),
(10, '10-1543-5729'),
(10, '10-1543-6492'),
(10, '10-1543-8006'),
(10, '10-1543-9605'),
(21, '9-763-2168'),
(22, '9-763-2168'),
(23, '9-1543-9263'),
(23, '9-1543-9804'),
(23, '9-763-2168'),
(24, '9-763-2168'),
(25, '9-1543-9804'),
(25, '9-763-2168'),
(26, '9-763-2168'),
(27, '9-763-2168'),
(28, '9-763-2168'),
(29, '9-1543-9804'),
(29, '9-763-2168'),
(30, '9-763-2168'),
(31, '9-1543-9804'),
(32, '9-1543-9804'),
(33, '9-1543-9804'),
(34, '9-1543-9804'),
(35, '9-1543-9804'),
(36, '9-1543-9804'),
(37, '9-1543-9804'),
(38, '9-1543-9804'),
(39, '9-1543-9804'),
(40, '9-1543-9804');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

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
(25, 'jason.arena@utp.ac.pa', 0, '2025-01-07 07:58:20'),
(26, 'carlos.mendoza@utp.ac.pa', 0, '2025-01-07 07:56:28'),
(27, 'hellsingpty@gmail.com', 3, '2024-12-03 18:29:10'),
(28, 'roberto.lopez@utp.ac.pa', 3, '2024-12-03 10:14:38'),
(29, 'tomas.perez@utp.ac.pa', 1, '2024-12-03 10:16:10'),
(30, 'raul.solis@utp.ac.pa', 1, '2024-12-03 17:09:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

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

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `tipo`, `asunto`, `mensaje`, `es_urgente`, `fecha_envio`, `estado`, `cedula_profesor`, `correo_destinatario`) VALUES
(76, 'Normal', 'Asistencia', 'Estimado/a Jason,\r\n\r\nLe informamos que su porcentaje de asistencia es de 66.6667%, lo cual es aceptable, pero se recomienda mejorar en las próximas semanas.\r\n\r\nSaludos cordiales,\r\nSistema de Notificaciones', 0, '2024-12-03 05:04:34', 'enviado', '9-123-4321', 'jason.arena@utp.ac.pa'),
(77, 'Normal', 'Documento', 'Descargar archivo', 0, '2024-12-03 05:43:13', 'enviado', '9-123-4321', 'jason.arena@utp.ac.pa'),
(78, 'Normal', 'Asistencia', 'Estimado/a Jason,\r\n\r\nLe informamos que su porcentaje de asistencia es de 25%, lo cual es inferior al 50%. Es importante que tome medidas para mejorar su asistencia.\r\n\r\nSaludos cordiales,\r\nSistema de Notificaciones', 1, '2024-12-03 13:33:58', 'enviado', '9-123-4321', 'jason.arena@utp.ac.pa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_usuarios`
--

CREATE TABLE `notificaciones_usuarios` (
  `id` int(11) NOT NULL,
  `id_notificacion` int(11) NOT NULL,
  `cedula_profesor` varchar(50) NOT NULL,
  `cedula_estudiante` varchar(50) NOT NULL,
  `fecha_recibido` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones_usuarios`
--

INSERT INTO `notificaciones_usuarios` (`id`, `id_notificacion`, `cedula_profesor`, `cedula_estudiante`, `fecha_recibido`) VALUES
(2692, 76, '9-123-4321', '9-763-2168', '2024-12-03 05:04:34'),
(2693, 77, '9-123-4321', '9-763-2168', '2024-12-03 05:43:14'),
(2694, 78, '9-123-4321', '9-763-2168', '2024-12-03 13:33:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `cedula` varchar(12) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_grupo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`cedula`, `id_curso`, `id_grupo`) VALUES
('01-9876-5432', 4, '4LS001'),
('02-3456-7890', 4, '4LS002'),
('03-8765-4321', 2, '2LS002'),
('04-1234-5678', 1, '1LS001'),
('05-5432-8765', 5, '5LS001'),
('06-2345-6789', 3, '3LS001'),
('07-8765-4321', 2, '2LS001'),
('08-5678-1234', 5, '5LS002'),
('09-5678-1234', 3, '3LS002'),
('10-2345-6789', 1, '1LS002');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor_curso`
--

CREATE TABLE `profesor_curso` (
  `cedula_profesor` varchar(12) NOT NULL,
  `id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesor_curso`
--

INSERT INTO `profesor_curso` (`cedula_profesor`, `id_curso`) VALUES
('1-1543-8723', 1),
('2-1543-4721', 2),
('3-1543-3892', 3),
('4-1543-2184', 4),
('5-1543-6293', 5),
('6-1543-8351', 6),
('7-1543-7452', 7),
('8-1543-9032', 8),
('9-1543-5764', 9),
('10-1543-2356', 10),
('9-123-4321', 5),
('9-123-4321', 23),
('9-123-4321', 6),
('9-123-4321', 25),
('1-1543-8723', 6),
('1-1543-8723', 1),
('2-1543-4721', 2),
('3-1543-3892', 3),
('4-1543-2184', 4),
('5-1543-6293', 5),
('6-1543-8351', 6),
('7-1543-7452', 7),
('8-1543-9032', 8),
('9-1543-5764', 9),
('10-1543-2356', 10),
('9-123-4321', 5),
('9-123-4321', 23),
('9-123-4321', 6),
('9-123-4321', 25),
('1-1543-8723', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuarios`
--

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
(28, '9-763-2168', '2024-12-03 04:52:53', '2024-12-03 04:53:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(29, '9-123-4321', '2024-12-03 04:53:14', '2024-12-03 04:56:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(30, '9-123-4321', '2024-12-03 04:56:20', '2024-12-03 05:04:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(31, '9-123-4321', '2024-12-03 05:06:53', '2024-12-03 05:08:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(32, '9-763-2168', '2024-12-03 05:14:50', '2024-12-03 05:15:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(33, '7-1543-6550', '2024-12-03 05:16:53', '2024-12-03 05:20:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(34, '9-763-2168', '2024-12-03 05:20:19', '2024-12-03 05:23:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(35, '9-763-2168', '2024-12-03 05:24:20', '2024-12-03 05:24:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(36, '7-1543-6550', '2024-12-03 05:25:19', '2024-12-03 05:29:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(37, '9-763-2168', '2024-12-03 05:29:56', '2024-12-03 05:30:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(38, '9-123-4321', '2024-12-03 05:30:28', '2024-12-03 05:32:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(39, '9-123-4321', '2024-12-03 05:32:49', '2024-12-03 05:33:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(40, '7-1543-6550', '2024-12-03 05:33:19', '2024-12-03 05:33:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(41, '9-763-2168', '2024-12-03 05:41:55', '2024-12-03 05:42:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(42, '9-123-4321', '2024-12-03 05:42:07', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(43, '9-123-4321', '2024-12-03 11:42:14', '2024-12-03 11:57:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(44, '9-763-2168', '2024-12-03 11:58:22', '2024-12-03 11:58:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(45, '9-763-2168', '2024-12-03 11:59:02', '2024-12-03 11:59:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(46, '7-1543-6550', '2024-12-03 12:07:26', '2024-12-03 12:09:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(47, '7-1543-6550', '2024-12-03 12:10:18', '2024-12-03 12:11:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(48, '8-123-0021', '2024-12-03 12:11:10', '2024-12-03 12:11:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(49, '9-763-2168', '2024-12-03 12:15:15', '2024-12-03 12:15:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(50, '9-123-4321', '2024-12-03 12:15:32', '2024-12-03 12:17:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(51, '9-763-2168', '2024-12-03 12:17:48', '2024-12-03 12:18:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(52, '9-123-4321', '2024-12-03 12:18:53', '2024-12-03 12:26:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(53, '9-123-4321', '2024-12-03 13:08:25', '2024-12-03 13:12:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(54, '9-123-4321', '2024-12-03 13:29:32', '2024-12-03 13:36:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(55, '9-763-2168', '2024-12-03 13:38:49', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(56, '9-763-2168', '2024-12-05 03:17:18', '2024-12-05 03:17:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(57, '9-123-4321', '2024-12-05 03:25:46', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(58, '9-763-2168', '2024-12-05 03:30:06', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(59, '9-763-2168', '2024-12-05 03:30:07', '2024-12-05 03:33:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(60, '9-123-4321', '2024-12-05 03:34:08', '2024-12-05 03:34:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(61, '9-123-4321', '2024-12-05 03:34:21', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(62, '9-123-4321', '2024-12-05 03:34:22', '2024-12-05 03:34:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(63, '9-763-2168', '2024-12-05 03:36:32', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(64, '9-763-2168', '2024-12-05 03:42:25', '2024-12-05 03:49:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(65, '9-123-4321', '2024-12-05 03:50:01', '2024-12-05 03:51:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(66, '9-763-2168', '2024-12-05 03:51:05', '2024-12-05 03:51:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(67, '9-763-2168', '2024-12-05 03:51:16', '2024-12-05 04:08:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(68, '9-763-2168', '2024-12-05 04:09:03', '2024-12-05 04:12:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(69, '9-123-4321', '2024-12-05 04:14:39', '2024-12-05 04:17:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(70, '7-1543-6550', '2024-12-05 04:17:16', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(71, '9-763-2168', '2024-12-30 00:56:36', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0'),
(72, '9-123-4321', '2024-12-30 00:57:49', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(73, '9-763-2168', '2025-01-07 02:52:38', '2025-01-07 02:56:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(74, '9-123-4321', '2025-01-07 02:56:29', '2025-01-07 02:57:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'),
(75, '9-763-2168', '2025-01-07 02:58:21', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_usuario`
--

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

CREATE TABLE `tokens_recuerdo` (
  `id` int(11) NOT NULL,
  `cedula` varchar(12) NOT NULL,
  `token` varchar(64) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tokens_recuerdo`
--

INSERT INTO `tokens_recuerdo` (`id`, `cedula`, `token`, `fecha_creacion`) VALUES
(21, '9-763-2168', '4d85201c9460c7f8a9499e091f04b776', '2025-01-07 07:58:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

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
('1-1543-2173', 'Fernando', 'Sánchez', 'fernando.sanchez@utp.ac.pa', 2, '$2y$10$ThzrSeYlCE9Fwdn9XTXijOkmD8R/OFTXpbm0RSXwpUnsY3rcSTkSm'),
('1-1543-2879', 'Javier', 'Romero', 'javier.romero@utp.ac.pa', 2, '$2y$10$9Peh1mtxtAlHZZeSIYm5CecUr/.wQ6/UtyNLwyHC/1qOACRC..fLK'),
('1-1543-3145', 'Raquel', 'González', 'raquel.gonzalez@utp.ac.pa', 2, '$2y$10$wN0b1KZHprmWFva5QoGta.OACUNPpmkz8DBWYgEeMsILbciVCOJ3q'),
('1-1543-3928', 'Miguel', 'González', 'miguel.gonzalez@utp.ac.pa', 2, '$2y$10$UmRx2mcR3n67zxtmPV02devPjVuSuVMHthqB0ZvvkRKvV.bEOqSa2'),
('1-1543-7423', 'Francisco', 'Mendoza', 'francisco.mendoza@utp.ac.pa', 2, '$2y$10$5cTk9fNDjjoaN6VqRtatYO8/gVT64cGTM1twh/MBmmpFynTucyQw.'),
('1-1543-8123', 'Samuel', 'Ramírez', 'samuel.ramirez@utp.ac.pa', 2, '$2y$10$HqzrFvqJdNdSGb8gv10x2e08C9R7Drtu4ZcuI3QmRo0iK7VQ8y4Ni'),
('1-1543-8706', 'Carolina', 'Castillo', 'carolina.castillo@utp.ac.pa', 2, '$2y$10$U08E8nfXC9BVLfGCJlg1w.9UAttRMJGHSV8Z5b29pvUjM.U0ecRUa'),
('1-1543-8723', 'Álvaro', 'Méndez', 'alvaro.mendez@utp.ac.pa', 3, '$2y$10$BLQnMGXhYXEHNG5Jg9JpzOzt9j/6P1/zPnbc8bXJcszHAfGa9JoS6'),
('1-1543-9471', 'Mónica', 'Serrano', 'monica.serrano@utp.ac.pa', 2, '$2y$10$dWtfr4ms8g84Gv0fnyup4uOwbYoeGsqe6Xpg1hoLhSGpo50c2/QPG'),
('10-1543-1248', 'Lina', 'Gómez', 'lina.gomez@utp.ac.pa', 2, '$2y$10$tMt6kh9W5nU9jrLJXd0kIeFX6ySqoXDWTWR6SvUb0XjW7TOVV86Ve'),
('10-1543-2356', 'Joaquín', 'Mora', 'joaquin.mora@utp.ac.pa', 3, '$2y$10$xX98bE0kYfqQdFE4mIJVaeyiEfFLTFR4/5VwRv8zHk7bkSakpzTxm'),
('10-1543-4035', 'Elena', 'Morales', 'elena.morales@utp.ac.pa', 2, '$2y$10$aj5yaEhoT5kDGGDDwavdwu6IIWseaQdgPKZo6tfMtkLYrMt8ZnpSW'),
('10-1543-5328', 'Martín', 'Ortega', 'martin.ortega@utp.ac.pa', 2, '$2y$10$Drj4BbIQH0V/tZR/MLkvNOjDicwpajdtaNScgLLcdYI3WzfeEceB2'),
('10-1543-5729', 'Lucía', 'Álvarez', 'lucia.alvarez@utp.ac.pa', 2, '$2y$10$AQRAkc.7G5mDQIaD9BUgOeFHMrzsDDKEmYKU3zMshT6aYzeWXhdBq'),
('10-1543-6492', 'Luis', 'Ortiz', 'luis.ortiz@utp.ac.pa', 2, '$2y$10$XwX2TfU0/26WlmzTLYZSgup0IazVqvf9yWgMZG0fPoAh3RqKlzJEe'),
('10-1543-7741', 'Pablo', 'Navarro', 'pablo.navarro@utp.ac.pa', 2, '$2y$10$OwXkopUAxWxkCdTgDbBDfen8eHOUDKjm7l3qQrcdUCNvIhQOlMqd2'),
('10-1543-8419', 'Luz', 'Paredes', 'luz.paredes@utp.ac.pa', 2, '$2y$10$HX.rCalgjHiyKBihzSL0f.LDdatNN3xoymNp1ucQ8.udlgjoM3xoa'),
('10-1543-9605', 'Verónica', 'Salazar', 'veronica.salazar@utp.ac.pa', 2, '$2y$10$WsEF3W78/idlQaY7zi7LTuhuAXrieWogAHfq.Cga227INr2U2BTbq'),
('2-1543-1837', 'Diana', 'Martínez', 'diana.martinez@utp.ac.pa', 2, '$2y$10$KFwXztHzxkjoVqRMqYA5/OxL/ndAeMx9QV3cDLzZET73KfRH0elAi'),
('2-1543-2471', 'Carmen', 'Vásquez', 'carmen.vasquez@utp.ac.pa', 2, '$2y$10$PuSYjsIqyxR1k.ITrhi/fe3aqfNASee.L8TjYSqVDqyH78hdq6.gW'),
('2-1543-3845', 'Raquel', 'Fuentes', 'raquel.fuentes@utp.ac.pa', 2, '$2y$10$u5pNKXpXrBAfGKeJ9drgw.pXdwArwLKGuKZQlJ2.7.hLgG.b1x4mq'),
('2-1543-4387', 'Gabriela', 'Moreno', 'gabriela.moreno@utp.ac.pa', 2, '$2y$10$GLqX2mECPgc6WaqgZCSMlurIES0KjzqIourbQ4QFFk0pn94MB.xn6'),
('2-1543-4721', 'Gonzalo', 'Ramírez', 'gonzalo.ramirez@utp.ac.pa', 3, '$2y$10$ryX8L9Sm.uJ5B2LICcaQROyiFrOoV7Uz6xJ2kPQql4t18RR4KLKdS'),
('2-1543-4731', 'Ana', 'Pérez', 'ana.perez@utp.ac.pa', 2, '$2y$10$XvefD0FgoKuTFcsbdThWGu6QwsuDVbdMvGLQmxzuOWh4OlR/pLWDq'),
('2-1543-4923', 'Joaquín', 'Torres', 'joaquin.torres@utp.ac.pa', 2, '$2y$10$MMlAFNuYES6MUAzJtcJNbOvjmonqYukxCdrFHsGXdSGRxohGdsXfi'),
('2-1543-6584', 'Eva', 'Cortez', 'eva.cortez@utp.ac.pa', 2, '$2y$10$7urVIDgrPriaXt5neh1MBe.fIYs8gMJKSAwyjEuOXuN9Z5gy40HA6'),
('2-1543-8061', 'Gustavo', 'Vargas', 'gustavo.vargas@utp.ac.pa', 2, '$2y$10$S0vkKlHw5LZDznZMruBdjOSoH6iEoY7QLtRJrVyrtcHRbT5lkJe3u'),
('3-1543-2048', 'Carlos', 'Martínez', 'carlos.martinez@utp.ac.pa', 2, '$2y$10$Z2ZvTaAUEzAxqMU0C.2gjugev5/w6izDqZM6muwHCi5BAyC/bf1Hu'),
('3-1543-2263', 'Javier', 'Alonso', 'javier.alonso@utp.ac.pa', 2, '$2y$10$oBrZh1vLt9Y0iWNqZVvPjOejOfhnw2ApYjyftqxKyIvJhssPUp0qC'),
('3-1543-2682', 'Héctor', 'Ríos', 'hector.rios@utp.ac.pa', 2, '$2y$10$u0CV.sE1J6CQNQRa9dCk3Ob81V2WRr2f0y4USjnS/dfOIOmq.qVyy'),
('3-1543-2957', 'Andrea', 'García', 'andrea.garcia@utp.ac.pa', 2, '$2y$10$yAW25vsPKjotInlBJfyt0OIvhfoVoR3XVXwKhRzdC9sT7H/nKjVnu'),
('3-1543-3689', 'Beatriz', 'Ferrer', 'beatriz.ferrer@utp.ac.pa', 2, '$2y$10$QQJjYxBT6asv/Fu5Pyy60OsUNmKTaklY/p3wZrEPAr.tEb5OEXJCG'),
('3-1543-3892', 'Inés', 'Morales', 'ines.morales@utp.ac.pa', 3, '$2y$10$5Lp0BMX3Jk2J0cHb1kXmkuYQpMTSw2jrDDqVsEvyZk65340Hg6bS2'),
('3-1543-6521', 'Ricardo', 'Díaz', 'ricardo.diaz@utp.ac.pa', 2, '$2y$10$YYFF64qBG5x6yXaHknfD2ORNPTsV0l3JJGglf1CfQu4sWh1cH.JBW'),
('3-1543-8254', 'Andrés', 'Jiménez', 'andres.jimenez@utp.ac.pa', 2, '$2y$10$anfDD14yd.qyWiY4sqjClOU0yUHp4XLyzY1F7BUqu5s.Rhu.FN4Lq'),
('3-1543-9421', 'Andrés', 'Torres', 'andres.torres@utp.ac.pa', 2, '$2y$10$ngranXx3p1S46jkIxYyovOUsgpiEzEA5zeEva.eC4IzZ8/Kc06xm2'),
('4-1543-1982', 'Teresa', 'Ruiz', 'teresa.ruiz@utp.ac.pa', 2, '$2y$10$mZO6g4rNYJhVWDZqhDBBvuprXqR30difAOZ1nNov7aZFKwpDwIG3a'),
('4-1543-2184', 'Ricardo', 'Fernández', 'ricardo.fernandez@utp.ac.pa', 3, '$2y$10$DsiGQGHwCyqXx86m7AwoQeieObHZ10Bmzt4SN25YrKg9p6qkUgXqq'),
('4-1543-4812', 'Carlos', 'Pérez', 'carlos.perez@utp.ac.pa', 2, '$2y$10$BnkesQPxd6CzE7DIJyzHS.umn//WW/DZOK09U648q10bDtm7swca6'),
('4-1543-5012', 'Felipe', 'Díaz', 'felipe.diaz@utp.ac.pa', 2, '$2y$10$Ni4mkOhvcf5ShFILW62uEuof2jm0TMIkgZRyQqOu0Z7.Yh2OpgyJq'),
('4-1543-5439', 'Laura', 'Rodríguez', 'laura.rodriguez@utp.ac.pa', 2, '$2y$10$L27FFmIjIo1KX9w5H4DnnOrLhk3hV/fBQj9y9HYwsqAjQcFtOsL4W'),
('4-1543-6174', 'José', 'López', 'jose.lopez@utp.ac.pa', 2, '$2y$10$thcADcJffKBdlkJ2ViZaaeogc/olndZnZA4rGw17GiHQXh54Lqklq'),
('4-1543-6715', 'Ramón', 'Figueroa', 'ramon.figueroa@utp.ac.pa', 2, '$2y$10$gs5rMuHBoBGbMI4YXdfUO.hcpPvcg2f3PBpZaG15uzj8gfryqVI6q'),
('4-1543-7362', 'Liliana', 'Vera', 'liliana.vera@utp.ac.pa', 2, '$2y$10$lTOHx2cIcnwov8T9przKe.y20sN4JOL7JkBrukTuW00qiuFtNYGFi'),
('4-1543-9357', 'Patricia', 'Torres', 'patricia.torres@utp.ac.pa', 2, '$2y$10$maxkZwoKUuobUAzepbykq.ZsOd5KxFRLAt9XmADekSsM5nk1kw7EW'),
('5-1543-5639', 'Javier', 'Gómez', 'javier.gomez@utp.ac.pa', 2, '$2y$10$bydSjZ0WDxSl8s3O28MgRuAG0Tm/eq3N4Ncp.DxeRk34eLZ7imRC6'),
('5-1543-6293', 'Verónica', 'Vargas', 'veronica.vargas@utp.ac.pa', 3, '$2y$10$r4PBWQCBKcvIEeJLK7zw..t6MvNgYUtDylLumJlpV/6BscF1tpyIy'),
('5-1543-6451', 'José', 'López', 'jose.lopez@utp.ac.pa', 2, '$2y$10$thcADcJffKBdlkJ2ViZaaeogc/olndZnZA4rGw17GiHQXh54Lqklq'),
('5-1543-6749', 'Adriana', 'Suárez', 'adriana.suarez@utp.ac.pa', 2, '$2y$10$CKy2jm4qTdL8fZVOxBgbu.E5wTrWLUBsjkOQWFpL1b.XO3k7pTp2S'),
('5-1543-7231', 'Manuel', 'Vega', 'manuel.vega@utp.ac.pa', 2, '$2y$10$pgLNiGPHMN/JrodNMARtbOQNZD6ZzxHBpIafjBBn2xsOX6aNzqGV.'),
('5-1543-7586', 'Roberto', 'Martínez', 'roberto.martinez@utp.ac.pa', 2, '$2y$10$1Dm3/TtSa.IwX1PwvkK9lOs.vaz4KDaLIToZcUuvEgMcPhaAmdSPO'),
('5-1543-8391', 'Carlos', 'Martín', 'carlos.martin@utp.ac.pa', 2, '$2y$10$r0hGpvPSkGgHob5tGUDTpOvJl0MTnzamQo/5rHrmDt.KdJW9IFk4m'),
('5-1543-8395', 'Cecilia', 'Fernández', 'cecilia.fernandez@utp.ac.pa', 2, '$2y$10$j/1nc0f5H1ZV9jEf9pRt/O5AHb47SugLG314UgXUtlz77DVPMh8S6'),
('5-1543-9432', 'Patricia', 'Álvarez', 'patricia.alvarez@utp.ac.pa', 2, '$2y$10$Xr5werKCs.z.LRRB5OTCyONrQrJ5ypdJwAsiqMvzHqrMY/GMfXPJa'),
('6-1543-1953', 'Óscar', 'González', 'oscar.gonzalez@utp.ac.pa', 2, '$2y$10$vh8zEdZyNlk.i8A/C/TGv.MSxWvrg8x01xK2ByEIeUqFoGsMRH3V.'),
('6-1543-2189', 'Sofía', 'Martínez', 'sofia.martinez@utp.ac.pa', 2, '$2y$10$AL7WCK0.OxLopbBnKe/oQuGvYn0GJ1CIQrVDbNv6gtmXl743ZzuXy'),
('6-1543-2894', 'María', 'Silva', 'maria.silva@utp.ac.pa', 2, '$2y$10$IUgp2SJEPPMdSBtdd/os1.e9WPgKv7Yx/EANF9m1090XCnQXXHGLW'),
('6-1543-3064', 'Juliana', 'Castro', 'juliana.castro@utp.ac.pa', 2, '$2y$10$WZr3slhhJQeeNbw1QFqUhuG1Mlm3h7YbHM8tVK3nk69z7w/InwZAi'),
('6-1543-4913', 'Isabel', 'González', 'isabel.gonzalez@utp.ac.pa', 2, '$2y$10$eks/TM4Th.ubUKeAf2pex.tXoKuVl1eGgbaUoAQ2QzQBCFLh1ujei'),
('6-1543-6357', 'Nuria', 'Sánchez', 'nuria.sanchez@utp.ac.pa', 2, '$2y$10$P3EaNKA..ok1kQ9/aCgdeOr/MsX8u1k6Bh24S5uHuap3ZWldpZ9o.'),
('6-1543-7382', 'Isabel', 'Hernández', 'isabel.hernandez@utp.ac.pa', 2, '$2y$10$BPzp8aJAufLVSttyxtYHMehe/S34K7KqCQx0KdSKYUwgPkqIE5s76'),
('6-1543-8351', 'Oscar', 'Herrera', 'oscar.herrera@utp.ac.pa', 3, '$2y$10$I2W5542LO.yKcKpbY4yln./BGgfypTyU4QQwIrXMw4pVUrX8McZha'),
('6-1543-8720', 'Emilio', 'Ramos', 'emilio.ramos@utp.ac.pa', 2, '$2y$10$Q4ny28/JiSUBEezKMsMkdOEpai4FU3O1ECnxUDxEwbRx8RGtZ2jHS'),
('7-123-2223', 'Roberto', 'Lopez', 'roberto.lopez@utp.ac.pa', 1, 'roberto123'),
('7-1543-3904', 'Patricia', 'Vázquez', 'patricia.vazquez@utp.ac.pa', 2, '$2y$10$gZo2lO96ag9kbV8yXpsyTuetDPqFRQtlAekBgfg2A2Hdl1C.Wo6/.'),
('7-1543-4681', 'Eduardo', 'Ramírez', 'eduardo.ramirez@utp.ac.pa', 2, '$2y$10$MELQow076.EUdjCgWsHDtuOGcJd/F.SFoJ6rcl4iayZ7/2uNh856W'),
('7-1543-5276', 'Raul', 'Jiménez', 'raul.jimenez@utp.ac.pa', 2, '$2y$10$TxDdGWbVMyKB/wQM4.x.jeAWBoqiTQVD3xoew9eXq51sWa270OvZu'),
('7-1543-5406', 'Tomás', 'Pérez', 'tomas.perez@utp.ac.pa', 1, '$2y$10$4737EgYqZUhgIjffJwH4SuJf.WVTJwR.hvbueVA5xgCoe1MxN2a3m'),
('7-1543-6550', 'Víctor', 'Santos', 'victor.santos@utp.ac.pa', 1, '$2y$10$fOlnai2n9aRck85q5h88EuMpenstiSGdvZ3.WoGTD6UrbMAUi4dgW'),
('7-1543-7340', 'Nicolás', 'Santos', 'nicolas.santos@utp.ac.pa', 2, '$2y$10$mvIUKToKIa2S3g1pIQidGu7UqiEThAzSN4YI7CcBMsb0/SYX3PPNa'),
('7-1543-7452', 'Rocío', 'Pérez', 'rocio.perez@utp.ac.pa', 3, '$2y$10$3rvA9PHIochMBiZBIpIcju2JVf4EYgnkGYMJKqpZqm1wqzNIqPbGy'),
('7-1543-8137', 'David', 'García', 'david.garcia@utp.ac.pa', 2, '$2y$10$1wJZKvMrHyJrBjlCl.jd4ebCmQUr679StdFDD2VqyPlSS8LqeaaHW'),
('7-1543-8362', 'Alberto', 'Pineda', 'alberto.pineda@utp.ac.pa', 2, '$2y$10$.sW8EZbCLrVJcuJe.in7DestZUQeShL/0eXnT24RvTBIt.wjyxUz.'),
('8-123-0021', 'Edwin', 'Tejedor', 'edwin.tejedor@utp.ac.pa', 2, '$2y$10$.J0q2kwwQ3uFZV5qDvKOm.v/ej3YdTFpv428gDAbtB4PPA/zsNN4y'),
('8-123-00212', 'raul', 'solis', 'raul.solis@utp.ac.pa', 3, '$2y$10$xqEtbeTuzwldWizRTL3TC.agd0jRo1mzHGKV.XFd74rI9EY0258fS'),
('8-1543-2543', 'Martín', 'Serrano', 'martin.serrano@utp.ac.pa', 2, '$2y$10$HCuB200tm7fnGGQWB43EheAsyPajzVcAhM/e0fVnaFWpU2KFvx0uO'),
('8-1543-3138', 'Marcos', 'Paredes', 'marcos.paredes@utp.ac.pa', 2, '$2y$10$TLHz2SbPz0nM1zkooSCTPuTLsf/vaL1GhEK1ghHcJYm6HY9PPMXfO'),
('8-1543-5173', 'Alba', 'Hernández', 'alba.hernandez@utp.ac.pa', 2, '$2y$10$4BkCyK8ZZNtHmZp9wQWp4Od5NtlMssOXJTt0oquK2szCR3F26k.RG'),
('8-1543-5763', 'Raul', 'Vega', 'raul.vega@utp.ac.pa', 2, '$2y$10$1XbWftKIgs/sCeO.i6PrJOWDE7BvPkT3G0QBZXsqext9EmROkWLxy'),
('8-1543-6523', 'Claudia', 'Sánchez', 'claudia.sanchez@utp.ac.pa', 2, '$2y$10$FIVjWVdLcLH2ZO1OPWmQA.XIrpr8z9B94GG8Bt5qTxrlx71yg6koi'),
('8-1543-7023', 'Sandra', 'Campos', 'sandra.campos@utp.ac.pa', 2, '$2y$10$LM.hBUcuRrLFFGcePtHTzuEI.z88EYlx102AYgJhBR.DymQsn7PA2'),
('8-1543-9032', 'Antonio', 'Gómez', 'antonio.gomez@utp.ac.pa', 3, '$2y$10$aBc196PznkqjF3alSkefcO7OsjamxYdMA1GD7LkB4leuDryAgtAFy'),
('8-1543-9102', 'Lorena', 'Jiménez', 'lorena.jimenez@utp.ac.pa', 2, '$2y$10$IcRUv3g8BkGWdZSNGsymd.xKzkZTfgllU993Yon/1x1VjsE4M3/ge'),
('8-1543-9872', 'Sofía', 'Martín', 'sofia.martin@utp.ac.pa', 2, '$2y$10$HO15J3i.EzXJRX8Lc/MjiO8xVXN7gtZw3lmPAdcU1cP1aX.8HYXFG'),
('9-123-4321', 'Carlos', 'Mendoza', 'carlos.mendoza@utp.ac.pa', 3, '$2y$10$Iq2oWscCVMUffMU4b3jnPeHeluqcaLxEIqk9c6p6WGR6mtr4tRsXq'),
('9-1543-1723', 'Felipe', 'Molina', 'felipe.molina@utp.ac.pa', 2, '$2y$10$nKLsDgd.UPS3c9TlriR/FOt2O7jAUQC4JgY9jIWvzXI0yvewYbxYO'),
('9-1543-2859', 'Ismael', 'Gutiérrez', 'ismael.gutierrez@utp.ac.pa', 2, '$2y$10$K5MHFabYKsaQbY1d7Fgbte6LDuLT47x9cjRHaBU73AzgCVq2QXduO'),
('9-1543-3641', 'Pedro', 'Fernández', 'pedro.fernandez@utp.ac.pa', 2, '$2y$10$CPkg/OeEnJ.ZIXFs3C6p0.qxhZ2ss8ssV/H.oLEsKBsQVoEeBe56q'),
('9-1543-5319', 'Verónica', 'Gómez', 'veronica.gomez@utp.ac.pa', 2, '$2y$10$ttiNPAs91SQDGKpOQTm0VubwvVATWdLTGx.7FYmjsmmymTvHGDz6m'),
('9-1543-5764', 'Elena', 'López', 'elena.lopez@utp.ac.pa', 3, '$2y$10$8XbuxbZx7DdK9VMPSdgdXOP13gXzlKsZMhQxzCmE4HNsElNuI/t3W'),
('9-1543-7392', 'Carlos', 'Rojas', 'carlos.rojas@utp.ac.pa', 2, '$2y$10$kCinBhgbkaCHNRVdh4z5N.yqCuAzbj6NZtMaHo99GkG93ex88gY0S'),
('9-1543-8412', 'Ignacio', 'García', 'ignacio.garcia@utp.ac.pa', 2, '$2y$10$1teOFfg2O9yPwtRmcXZuXOK8cZFr9V1.VKgTPDkFN3Xj230CsAxRm'),
('9-1543-9263', 'Hell', 'Sing', 'hellsingpty@gmail.com', 2, '$2y$10$cpP2jfkFahUKDk5mpcV8ne6a4Gcv6BLqrPm6Bpoe9PNvvAXAImUfu'),
('9-1543-9804', 'Raúl', 'Méndez', 'raul.mendez@utp.ac.pa', 2, '$2y$10$qrUxCMGOJVNE7myWU12HOeV7hNIPR0yGftyH3fr5V5n6.0w3uN3v6'),
('9-763-2168', 'Jason', 'Arena', 'jason.arena@utp.ac.pa', 2, '$2y$10$FaLeMsOyxTNevoGFLV2V5eu0KXc66TJHQWTg.vPch1xr5G9oVx.MK');

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
  ADD PRIMARY KEY (`cedula`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `notificaciones_usuarios`
--
ALTER TABLE `notificaciones_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2695;

--
-- AUTO_INCREMENT de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `tipos_usuario`
--
ALTER TABLE `tipos_usuario`
  MODIFY `id_tipoUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tokens_recuerdo`
--
ALTER TABLE `tokens_recuerdo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
