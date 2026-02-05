-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-01-2020 a las 04:34:07
-- Versión del servidor: 10.1.35-MariaDB
-- Versión de PHP: 7.2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `turnosonlinebb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultorios`
--

CREATE TABLE `consultorios` (
  `id` int(10) UNSIGNED NOT NULL,
  `direccion` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `foto` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `consultorios`
--

INSERT INTO `consultorios` (`id`, `direccion`, `telefono`, `activo`, `created_at`, `updated_at`, `foto`, `nombre`) VALUES
(1, 'Direccion', 454545, 1, '2019-10-16 16:20:07', '2019-10-16 16:20:07', NULL, NULL),
(2, 'Direccion', 464646, 1, '2019-10-26 15:30:35', '2019-10-26 15:30:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidads`
--

CREATE TABLE `especialidads` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `especialidads`
--

INSERT INTO `especialidads` (`id`, `nombre`, `foto`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Pediatria', 'Pediatria1571191509.png', 1, '2019-10-16 02:05:09', '2019-10-16 02:05:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

CREATE TABLE `feriados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `feriados`
--

INSERT INTO `feriados` (`id`, `fecha`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, '2019-12-25', 'Navidad', '2019-11-19 20:50:57', '2019-11-19 20:50:57'),
(2, '2020-01-01', 'Año Nuevo', '2019-11-19 20:51:14', '2019-11-19 20:51:14'),
(3, '2020-02-24', 'Carnaval', '2019-11-19 20:52:04', '2019-11-19 20:52:04'),
(4, '2020-02-25', 'Carnaval', '2019-11-19 20:52:14', '2019-11-19 20:52:14'),
(5, '2020-03-24', 'Día Nacional de la Memoria por la Verdad y la Justicia.', '2019-11-19 20:52:55', '2019-11-19 20:52:55'),
(6, '2020-04-02', 'Día del Veterano y de los Caídos en la Guerra de Malvinas.', '2019-11-19 20:53:30', '2019-11-19 20:53:30'),
(7, '2020-04-10', 'Viernes Santo', '2019-11-19 20:53:45', '2019-11-19 20:53:45'),
(8, '2020-05-01', 'Día del Trabajador.', '2019-11-19 20:54:02', '2019-11-19 20:54:02'),
(9, '2020-05-25', 'Día de la Revolución de Mayo.', '2019-11-19 20:54:17', '2019-11-19 20:54:17'),
(10, '2020-06-20', 'Paso a la Inmortalidad del General Manuel Belgrano.', '2019-11-19 20:54:36', '2019-11-19 20:54:36'),
(11, '2020-07-09', 'Día de la Independencia.', '2019-11-19 20:54:54', '2019-11-19 20:54:54'),
(12, '2020-12-08', 'Inmaculada Concepción de María.', '2019-11-19 20:55:18', '2019-11-19 20:55:18'),
(13, '2020-12-25', 'Navidad.', '2019-11-19 20:55:30', '2019-11-19 20:55:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario_medicos`
--

CREATE TABLE `horario_medicos` (
  `id` int(10) UNSIGNED NOT NULL,
  `medico` int(10) UNSIGNED NOT NULL,
  `consultorio` int(10) UNSIGNED NOT NULL,
  `dia` int(11) NOT NULL,
  `horario` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `doble` int(11) NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horario_medicos`
--

INSERT INTO `horario_medicos` (`id`, `medico`, `consultorio`, `dia`, `horario`, `doble`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, '18:00', 0, 1, '2019-10-16 16:27:21', '2019-10-16 16:27:21'),
(2, 1, 1, 3, '18:30', 0, 1, '2019-10-16 16:27:21', '2019-10-16 16:27:21'),
(3, 1, 1, 3, '19:00', 0, 1, '2019-10-16 16:27:21', '2019-10-16 16:27:21'),
(4, 1, 1, 3, '19:30', 1, 1, '2019-10-16 16:27:21', '2019-10-16 16:27:21'),
(16, 2, 2, 5, '15:00', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(17, 2, 2, 5, '15:30', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(18, 2, 2, 5, '16:00', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(19, 2, 2, 5, '16:30', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(20, 2, 2, 5, '17:00', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(21, 2, 2, 5, '17:30', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(22, 2, 2, 5, '18:00', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(23, 2, 2, 5, '18:30', 0, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(24, 2, 2, 5, '19:00', 1, 1, '2019-10-26 15:38:26', '2019-10-26 15:38:26'),
(25, 3, 2, 2, '15:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(26, 3, 2, 2, '15:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(27, 3, 2, 2, '16:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(28, 3, 2, 2, '16:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(29, 3, 2, 2, '17:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(30, 3, 2, 2, '17:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(31, 3, 2, 2, '18:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(32, 3, 2, 2, '18:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(33, 3, 2, 2, '19:00', 1, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(34, 3, 2, 4, '15:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(35, 3, 2, 4, '15:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(36, 3, 2, 4, '16:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(37, 3, 2, 4, '16:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(38, 3, 2, 4, '17:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(39, 3, 2, 4, '17:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(40, 3, 2, 4, '18:00', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(41, 3, 2, 4, '18:30', 0, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(42, 3, 2, 4, '19:00', 1, 1, '2019-10-26 15:46:08', '2019-10-26 15:46:08'),
(43, 4, 2, 1, '15:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(44, 4, 2, 1, '15:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(45, 4, 2, 1, '16:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(46, 4, 2, 1, '16:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(47, 4, 2, 1, '17:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(48, 4, 2, 1, '17:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(49, 4, 2, 1, '18:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(50, 4, 2, 1, '18:30', 1, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(51, 4, 2, 4, '15:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(52, 4, 2, 4, '15:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(53, 4, 2, 4, '16:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(54, 4, 2, 4, '16:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(55, 4, 2, 4, '17:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(56, 4, 2, 4, '17:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(57, 4, 2, 4, '18:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(58, 4, 2, 4, '18:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(59, 4, 2, 4, '19:00', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(60, 4, 2, 4, '19:30', 0, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(61, 4, 2, 4, '20:00', 1, 1, '2019-10-30 22:28:31', '2019-10-30 22:28:31'),
(62, 5, 2, 5, '15:00', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(63, 5, 2, 5, '15:30', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(64, 5, 2, 5, '16:00', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(65, 5, 2, 5, '16:30', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(66, 5, 2, 5, '17:00', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(67, 5, 2, 5, '17:30', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(68, 5, 2, 5, '18:00', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(69, 5, 2, 5, '18:30', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(70, 5, 2, 5, '19:00', 0, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(71, 5, 2, 5, '19:30', 1, 1, '2019-11-04 16:26:59', '2019-11-04 16:26:59'),
(72, 6, 2, 3, '10:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(73, 6, 2, 3, '10:30', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(74, 6, 2, 3, '11:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(75, 6, 2, 3, '11:30', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(76, 6, 2, 3, '12:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(77, 6, 2, 3, '12:30', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(78, 6, 2, 3, '13:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(79, 6, 2, 3, '13:30', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(80, 6, 2, 3, '14:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(81, 6, 2, 3, '14:30', 1, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(82, 6, 2, 2, '14:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(83, 6, 2, 2, '14:30', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(84, 6, 2, 2, '15:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(85, 6, 2, 2, '15:30', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(86, 6, 2, 2, '16:00', 0, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(87, 6, 2, 2, '16:30', 1, 1, '2019-11-13 14:38:41', '2019-11-13 14:38:41'),
(92, 2, 2, 3, '09:30', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(93, 2, 2, 3, '10:00', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(94, 2, 2, 3, '10:30', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(95, 2, 2, 3, '11:00', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(96, 2, 2, 3, '11:30', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(97, 2, 2, 3, '12:00', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(98, 2, 2, 3, '12:30', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(99, 2, 2, 3, '13:00', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(100, 2, 2, 3, '13:30', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(101, 2, 2, 3, '14:00', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(102, 2, 2, 3, '14:30', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(103, 2, 2, 3, '15:00', 0, 1, '2019-11-14 05:33:09', '2019-11-14 05:33:09'),
(104, 2, 2, 3, '15:30', 1, 1, '2019-11-14 05:34:20', '2019-11-14 05:34:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario_medico_d_h_s`
--

CREATE TABLE `horario_medico_d_h_s` (
  `id` int(10) UNSIGNED NOT NULL,
  `medico` int(10) UNSIGNED NOT NULL,
  `consultorio` int(10) UNSIGNED NOT NULL,
  `dia` int(11) NOT NULL,
  `desde` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hasta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horario_medico_d_h_s`
--

INSERT INTO `horario_medico_d_h_s` (`id`, `medico`, `consultorio`, `dia`, `desde`, `hasta`, `activo`, `created_at`, `updated_at`) VALUES
(11, 2, 2, 3, '09:30', '16:00', 1, '2019-12-31 17:11:21', '2019-12-31 17:11:21'),
(12, 2, 2, 5, '15:00', '19:30', 1, '2019-12-31 17:11:36', '2019-12-31 17:11:36'),
(13, 3, 2, 2, '15:00', '19:30', 1, '2019-12-31 21:53:05', '2019-12-31 21:53:05'),
(14, 3, 2, 4, '15:00', '19:30', 1, '2019-12-31 21:53:25', '2019-12-31 21:53:25'),
(15, 4, 2, 1, '15:00', '19:00', 1, '2019-12-31 21:53:53', '2019-12-31 21:53:53'),
(16, 4, 2, 4, '15:00', '20:30', 1, '2019-12-31 21:54:13', '2019-12-31 21:54:13'),
(17, 5, 2, 5, '15:00', '20:00', 1, '2019-12-31 21:54:46', '2019-12-31 21:54:46'),
(18, 6, 2, 2, '14:00', '17:00', 1, '2019-12-31 21:55:03', '2019-12-31 21:55:03'),
(19, 6, 2, 3, '10:00', '15:00', 1, '2019-12-31 21:55:17', '2019-12-31 21:55:17'),
(20, 1, 1, 3, '18:00', '20:00', 1, '2020-01-06 01:09:05', '2020-01-06 01:09:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

CREATE TABLE `medicos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` int(10) UNSIGNED NOT NULL,
  `consultorio` int(10) UNSIGNED NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `mail` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `castigo_automatico` int(11) NOT NULL,
  `foto` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `perfil` int(11) DEFAULT NULL,
  `sexo` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `medicos`
--

INSERT INTO `medicos` (`id`, `nombre`, `apellido`, `especialidad`, `consultorio`, `telefono`, `mail`, `castigo_automatico`, `foto`, `activo`, `user_id`, `created_at`, `updated_at`, `perfil`, `sexo`) VALUES
(1, '1', 'Doctor', 1, 1, 2914688467, 'doctor1@test.com', 1, 'medico_sin_foto_3.png1579316397.png', 1, 4, '2019-10-16 16:24:41', '2020-01-18 06:05:11', 1, 'F'),
(2, '2', 'Doctor', 1, 2, 1, 'doctor2@test.com', 1, 'medico_sin_foto.png1579316415.png', 1, 10, '2019-10-26 15:34:29', '2020-01-18 06:05:23', 2, 'M'),
(3, '3', 'Doctor', 1, 2, 1, 'doctor3@test.com', 1, 'medico_sin_foto_2.png1579316428.png', 1, 11, '2019-10-26 15:44:12', '2020-01-18 06:05:37', 2, 'M'),
(4, '4', 'Doctor', 1, 2, 1, 'doctor4@test.com', 1, 'medico_sin_foto_4.png1579316461.png', 1, 13, '2019-10-30 22:24:40', '2020-01-18 06:05:47', 2, 'F'),
(5, '5', 'Doctor', 1, 2, 1, 'doctor5@test.com', 1, 'medico_sin_foto.png1579316475.png', 1, 14, '2019-11-04 16:25:43', '2020-01-18 06:04:44', 2, 'F'),
(6, '6', 'Doctor', 1, 2, 2215598360, 'doctor6@test.com', 1, 'medico_sin_foto_3.png1579316487.png', 1, 15, '2019-11-13 14:36:28', '2020-01-18 06:04:59', 2, 'F');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medico_primer_controls`
--

CREATE TABLE `medico_primer_controls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medico` int(10) UNSIGNED NOT NULL,
  `dia` int(11) NOT NULL,
  `consultorio` int(10) UNSIGNED NOT NULL,
  `cantidadPrimerControl` int(11) NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `medico_primer_controls`
--

INSERT INTO `medico_primer_controls` (`id`, `medico`, `dia`, `consultorio`, `cantidadPrimerControl`, `activo`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 2, 1, 1, '2019-12-31 17:11:21', '2019-12-31 20:49:13'),
(2, 2, 5, 2, 1, 1, '2019-12-31 17:11:36', '2019-12-31 20:25:22'),
(3, 3, 2, 2, 1, 1, '2019-12-31 21:53:05', '2019-12-31 21:53:05'),
(4, 3, 4, 2, 1, 1, '2019-12-31 21:53:25', '2019-12-31 21:53:25'),
(5, 4, 1, 2, 1, 1, '2019-12-31 21:53:53', '2019-12-31 21:53:53'),
(6, 4, 4, 2, 1, 1, '2019-12-31 21:54:13', '2019-12-31 21:54:13'),
(7, 5, 5, 2, 1, 1, '2019-12-31 21:54:46', '2019-12-31 21:54:46'),
(8, 6, 2, 2, 1, 1, '2019-12-31 21:55:04', '2019-12-31 21:55:04'),
(9, 6, 3, 2, 1, 1, '2019-12-31 21:55:17', '2019-12-31 21:55:17'),
(10, 1, 3, 1, 1, 1, '2020-01-06 01:09:05', '2020-01-06 01:09:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2013_06_22_234753_create_tipo_usuario_table', 1),
(2, '2014_10_12_000000_create_users_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(5, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(6, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(7, '2016_06_01_000004_create_oauth_clients_table', 1),
(8, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(9, '2019_05_28_130941_create_paciente_table', 1),
(10, '2019_06_05_003752_create_especialidad_table', 1),
(11, '2019_06_07_011034_create_consultorio_table', 1),
(12, '2019_06_08_153516_create_medico_table', 1),
(13, '2019_06_08_191440_create_horarios_medicos_table', 1),
(14, '2019_06_09_145409_create_turno_registrado_table', 1),
(15, '2019_06_22_011617_create_secretaria_table', 1),
(16, '2019_06_26_211801_create_secretaria_consultorio_table', 1),
(18, '2019_09_26_145749_altapaciente_secretaria', 1),
(19, '2019_11_19_035453_create_feriado_table', 2),
(20, '2019_11_29_192459_create_medico_horario_dh_table', 3),
(22, '2019_09_21_175823_create_medico_primercontrol_table', 4),
(24, '2020_01_02_193339_create_modulo_table', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Activar Paciente', 1, NULL, NULL),
(2, 'Caja - Comentario', 1, NULL, NULL),
(3, 'Primer Control Doble', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo_medicos`
--

CREATE TABLE `modulo_medicos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medico` int(10) UNSIGNED NOT NULL,
  `modulo` int(10) UNSIGNED NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulo_medicos`
--

INSERT INTO `modulo_medicos` (`id`, `medico`, `modulo`, `activo`, `created_at`, `updated_at`) VALUES
(7, 2, 2, 1, '2020-01-03 23:03:54', '2020-01-03 23:03:54'),
(8, 2, 3, 1, '2020-01-03 23:03:54', '2020-01-03 23:03:54'),
(9, 3, 1, 1, '2020-01-03 23:04:09', '2020-01-03 23:04:09'),
(10, 3, 2, 1, '2020-01-03 23:04:09', '2020-01-03 23:04:09'),
(11, 3, 3, 1, '2020-01-03 23:04:09', '2020-01-03 23:04:09'),
(14, 4, 1, 1, '2020-01-05 23:26:45', '2020-01-05 23:26:45'),
(15, 4, 2, 1, '2020-01-05 23:26:45', '2020-01-05 23:26:45'),
(16, 4, 3, 1, '2020-01-05 23:26:45', '2020-01-05 23:26:45'),
(17, 5, 1, 1, '2020-01-05 23:26:52', '2020-01-05 23:26:52'),
(18, 5, 2, 1, '2020-01-05 23:26:52', '2020-01-05 23:26:52'),
(19, 5, 3, 1, '2020-01-05 23:26:52', '2020-01-05 23:26:52'),
(20, 6, 1, 1, '2020-01-05 23:26:59', '2020-01-05 23:26:59'),
(21, 6, 2, 1, '2020-01-05 23:26:59', '2020-01-05 23:26:59'),
(22, 6, 3, 1, '2020-01-05 23:26:59', '2020-01-05 23:26:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` int(11) NOT NULL,
  `telefono` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `fecha_castigo` date NOT NULL,
  `obra_social` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_afiliado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `obra_social_plan` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `terminos_condiciones` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombre`, `apellido`, `dni`, `telefono`, `mail`, `fecha_nacimiento`, `fecha_castigo`, `obra_social`, `numero_afiliado`, `obra_social_plan`, `activo`, `created_at`, `updated_at`, `terminos_condiciones`) VALUES
(84, '', 'cancelado', 99999, '0', '', '0000-00-00', '0000-00-00', '', '', '', 1, NULL, NULL, 0),
(85, 'luisana isabel', 'Pérez Henriquez', 55729408, '2915706547', 'yamihenriquezpalma@gmail.com', '2016-08-13', '2000-01-01', '', '', '', 1, '2019-11-07 19:18:51', '2019-11-11 15:19:44', 0),
(86, 'ramiro angel', 'Pérez Henriquez', 57673560, '2915706547', 'yamihenriquezpalma@gmail.com', '2019-05-04', '2000-01-01', '', '', '', 1, '2019-11-07 19:20:12', '2019-11-11 15:22:06', 0),
(87, 'SOFIA', 'ANFOSSI ALZA', 57316218, '2914662762', 'duilioanfossi@yahoo.com.ar', '2018-06-16', '2000-01-01', 'MEDIFE', '3-06207246-02/000', 'PLATA', 1, '2019-11-07 22:59:07', '2019-11-09 19:28:56', 0),
(88, 'Bastian', 'Zunino Balbo', 57627564, '2915102771', 'Lucreciabalborotili23@gmail.com', '2019-04-20', '2000-01-01', 'Osecac', '000049', 'Materno infantil', 1, '2019-11-08 00:05:03', '2019-11-09 19:29:24', 0),
(89, 'valentin ezequiel', 'gimenez', 48774834, '2914478028', 'pao_valen_guille@hotmail.com', '2008-09-30', '2000-01-01', 'ospim prevencion salud', '3725801031', 'A molinero', 1, '2019-11-08 01:43:54', '2019-11-11 15:19:51', 0),
(90, 'ambar camila', 'gimenez', 54128033, '2914478028', 'pao_valen_guille@hotmail.com', '2014-06-11', '2000-01-01', 'ospim prevencion salud', '3725802029', 'A molinero', 1, '2019-11-08 01:45:33', '2019-11-14 19:52:01', 0),
(91, 'Milo', 'Gimenez', 57021625, '2914478028', 'pao_valen_guille@hotmail.com', '2018-04-25', '2000-01-01', 'ospim prevencion salud', '3725804015', 'A molinero', 1, '2019-11-08 01:46:47', '2019-11-14 20:11:33', 0),
(92, 'catalina', 'galletti', 53883170, '2932638299', 'emiilce_77@hotmail.com', '2014-06-10', '2000-01-01', 'ioma', '2361836146/02', '', 1, '2019-11-08 04:47:00', '2019-11-09 19:29:11', 0),
(93, 'JUAN IGNACIO', 'ANFOSSI ALZA', 53372106, '2914662762', 'duilioanfossi@yahoo.com.ar', '2013-06-14', '2000-01-01', 'Swis Medical', '1', '1', 1, '2019-11-08 14:26:49', '2020-01-10 23:54:31', 0),
(94, 'Rodrigo', 'Banegas', 34741602, '2915080081', 'banegasrodrigo89@gmail.com', '1989-12-14', '2000-01-01', 'Swis Medical', '11111', '1', 1, '2019-11-08 20:57:43', '2020-01-11 07:27:06', 1),
(95, 'Florencia', 'García Elliot', 32198410, '2914688467', 'mfge23@hotmail.com', '1987-06-28', '2000-01-01', '', '', '', 1, '2019-11-09 13:54:10', '2019-12-29 00:48:42', 1),
(96, 'Isabella', 'Sargado', 56797539, '2914068267', 'Estefaniasargado12345@gmail.com', '2018-01-26', '2000-01-01', '', '', '', 1, '2019-11-10 01:36:52', '2019-11-11 15:20:04', 0),
(97, 'Alma Jazmin', 'Bouquez', 52429393, '2914475806', 'alet95@hotmail.com', '2012-05-18', '2000-01-01', 'ioma', '230351373702', 'obligatorio', 1, '2019-11-10 10:23:49', '2019-11-11 19:56:23', 0),
(98, 'Nicolas Lionel', 'Bouquez', 56797618, '2914475804', 'alet95@hotmail.com', '2018-02-15', '2000-01-01', 'ioma', '230351373703', '', 1, '2019-11-10 10:24:53', '2019-11-11 15:20:15', 0),
(99, 'Mateo Valentin', 'Montes Lopez', 51245384, '293215508800', 'paobeatrizlopez86@gmail.com', '2011-09-21', '2000-01-01', 'ioma', '232332114902', 'obligatorio', 1, '2019-11-10 10:26:58', '2019-11-11 15:21:08', 0),
(100, 'Facundo Federico', 'Villagra', 56411500, '2932418021', 'Ludmilaredondo1981@gmail.com', '2017-07-26', '2000-01-01', 'Medife', '3-01226751-01/340', 'Bronce', 1, '2019-11-10 21:28:24', '2019-11-11 15:19:10', 0),
(101, 'Lucas', 'Lucarelli', 1, '1', 'l_lucarelli@hotmail.com', '2000-01-01', '2000-01-01', '', '', '', 1, '2019-11-11 12:42:27', '2019-12-09 06:06:33', 1),
(102, 'Lucas', 'Gonzalez Gili', 2, '1', 'lucasgonzalezg@hotmail.com', '2000-01-01', '2000-01-01', '', '', '', 1, '2019-11-11 12:44:17', '2019-11-11 12:45:43', 0),
(103, 'Felicitas Alma', 'Balestra', 52020147, '02914611170', 'noralihermida@yahoo.com.ar', '2012-01-26', '2000-01-01', 'federada', '17617503', '3000', 1, '2019-11-11 15:45:09', '2019-11-14 20:11:40', 0),
(104, 'Justina Paz', 'Balestra', 57765134, '02914611170', 'noralihermida@yahoo.com.ar', '2019-06-27', '2000-01-01', 'federada', '17617504', '3000', 1, '2019-11-11 15:46:33', '2019-11-14 20:12:12', 0),
(105, 'Josefina', 'Gimeno', 50535061, '2914371234', 'noeliamammana@gmail.com', '2010-08-12', '2000-01-01', 'Osde', '31208953003', '210', 1, '2019-11-11 18:30:10', '2019-11-11 19:56:54', 0),
(106, 'Dante', 'Gimeno', 54827365, '2914371234', 'noeliamammana@gmail.com', '0215-07-31', '2000-01-01', 'Osde', '31208953004', '210', 1, '2019-11-11 18:32:40', '2019-11-11 19:57:02', 0),
(107, 'Amelia', 'Moyano', 57684313, '2914399245', 'ccolalonga@gmail.com', '2019-06-09', '2000-01-01', 'OSDIPP', '02453904164', '3SQ', 1, '2019-11-11 19:35:48', '2019-11-11 19:56:41', 0),
(108, 'gennaro', 'batalla gestoso', 55039476, '2914259983', 'valeruchas@hotmail.com', '2015-10-14', '2000-01-01', 'galeno', '0162693301 03', 'azul', 1, '2019-11-11 19:43:22', '2019-11-14 20:24:07', 0),
(109, 'Nicolás', 'Gabriani', 53521804, '2915711845', 'lorecantero@hotmail.com', '2013-09-04', '2000-01-01', 'Swiss Medical', '8000060469843030012', 'SMG30', 1, '2019-11-11 20:07:40', '2019-11-14 20:24:15', 0),
(110, 'Tomás', 'Gabriani', 56112828, '2915711845', 'lorecantero@hotmail.com', '2017-01-20', '2000-01-01', 'Swiss Medical', '8000060469843040011', 'SMG30', 1, '2019-11-11 20:09:39', '2019-11-14 20:24:24', 0),
(111, 'Lola', 'Sainz', 55196971, '154224678', 'nataliareali@gmail.com', '2015-11-06', '2000-01-01', 'IOMA', '231175729503', '', 1, '2019-11-11 21:10:33', '2019-11-14 20:24:29', 0),
(112, 'Alejo', 'Sainx', 55196970, '154224678', 'nataliareali@gmail.com', '2015-11-06', '2000-01-01', 'IOMA', '231175729502', '', 1, '2019-11-11 21:12:56', '2019-11-19 13:33:25', 0),
(113, 'Milagros', 'Arzer', 55269286, '2914688608', 'Mechillanos15@gmail.com', '2015-12-10', '2000-01-01', 'Prevención salud', '9790702017', 'A1', 1, '2019-11-12 14:50:13', '2019-11-14 20:24:33', 0),
(114, 'Allegra', 'Bahl', 34808459, '02914700072', 'Laraygael2310@gmail.com', '2017-04-26', '2000-01-01', '', '', '', 1, '2019-11-12 16:13:39', '2019-11-14 20:24:39', 0),
(115, 'Lara', 'Huinca', 50889770, '02914700072', 'Laraygael2310@gmail.com', '2011-03-23', '2000-01-01', '', '1343334555/02', 'IOMA', 1, '2019-11-12 17:05:15', '2019-11-19 21:02:49', 0),
(116, 'Juan gael', 'Huinca', 52784822, '2914700072', 'Laraygael2310@gmail.com', '2012-10-10', '2000-01-01', 'IOMA', '1343334555/03', 'IOMA', 1, '2019-11-12 17:07:49', '2019-11-19 21:03:30', 0),
(117, 'Allegra', 'Bahl', 56184694, '02914700072', 'Laraygael2310@gmail.com', '2017-04-26', '2000-01-01', '', '', '', 1, '2019-11-12 17:10:02', '2019-11-19 21:03:37', 0),
(118, 'Valentino Gabriel', 'Azcona', 57638174, '2914271971', 'gutierrezortizjulia@gmail.com', '2019-04-05', '2000-01-01', 'Federada Salud', '19231902', '3000', 1, '2019-11-13 16:43:46', '2019-11-14 20:24:50', 0),
(119, 'Milo Rafael', 'Soria', 57765140, '2915041879', 'Jimehenriquez@gmail.com', '2019-06-25', '2000-01-01', 'Ioma', '23356327764/02', '', 1, '2019-11-13 23:25:02', '2019-11-19 21:03:49', 0),
(120, 'Felipe Luis', 'Bezzato', 54833669, '2932449576', 'gumo170888@gmail.com', '2019-11-28', '2000-01-01', 'Iosfa', '068259-7/02', '', 1, '2019-11-13 23:30:22', '2019-12-11 03:47:44', 1),
(125, 'Dante', 'Ricci', 56938027, '1169719619', 'm_belus@hotmail.com', '2018-03-26', '2000-01-01', 'Sancor Salud', '1099822/02', '3000', 1, '2019-11-13 23:32:48', '2019-11-14 20:24:58', 0),
(126, 'VALENTIN', 'BARRAGAN ORAZI', 57258811, '2915036480', 'lauraorazi@hotmail.com', '2018-08-17', '2000-01-01', 'dosem', '15440/1', '', 1, '2019-11-13 23:34:24', '2019-11-19 21:04:09', 0),
(127, 'Salvador', 'Cortese', 55492666, '2916454806', 'nachitas02@hotmail.com', '2016-03-08', '2000-01-01', 'Comei', '014881-01-3', 'Cobertura máxima', 1, '2019-11-13 23:35:18', '2019-11-14 20:25:04', 0),
(128, 'Julieta', 'Aspitia', 54656504, '2914486613', 'Fmaspitia@outlook.com.ar', '2015-03-03', '2000-01-01', 'OSDIPP', '02295384248', 'Plan 3SQ', 1, '2019-11-13 23:45:42', '2019-11-14 20:25:09', 0),
(129, 'Justino', 'Basan', 54524600, '2915042664', 'Antillancagimena23@gmail.com', '2015-02-04', '2000-01-01', '', '', '', 1, '2019-11-13 23:46:10', '2019-11-19 21:04:17', 0),
(130, 'Tobías Ezequiel', 'Corneli', 57663730, '2915029152', 'marinaheiland@hotmail.com', '2019-04-23', '2000-01-01', 'Federada Salud', '16617502', 'Plan 3000', 1, '2019-11-13 23:46:13', '2019-11-19 21:04:27', 0),
(131, 'Mateo', 'Aspitia', 55199683, '2914486673', 'Fmaspitia@outlook.com.ar', '2016-02-22', '2000-01-01', 'OSDIPP', '04069104841', 'Plan 350', 1, '2019-11-13 23:47:02', '2019-11-14 20:25:16', 0),
(132, 'Maitena Nicole', 'Cleppe', 56639568, '2914623390', 'sonia.castro@outlook.com.ar', '2017-12-03', '2000-01-01', 'Osdop', '94124251', '', 1, '2019-11-14 01:30:23', '2019-11-14 20:25:21', 0),
(133, 'Pierina Deyamira', 'Cleppe', 47376719, '2914623390', 'sonia.castro@outlook.com.ar', '2006-07-25', '2000-01-01', 'Osdop', '94124251', '', 1, '2019-11-14 01:31:57', '2019-11-19 21:04:36', 0),
(134, 'efrain', 'veliz', 57170429, '0293215472261', 'jacque.caballero28@gmail.com', '2019-01-17', '2000-01-01', 'iosfa', '', '', 1, '2019-11-14 01:59:16', '2019-11-19 21:04:46', 0),
(135, 'Román', 'Jara', 47692935, '2915328595', 'rominatresr@hotmail.com.ar', '2005-06-01', '2000-01-01', 'Ospatca', '291342', '', 1, '2019-11-14 02:12:42', '2019-11-19 21:04:53', 0),
(136, 'Rosario', 'Jara', 50402516, '2915328595', 'rominatresr@hotmail.com.ar', '2010-06-23', '2000-01-01', 'Ospatca', '291343', '', 1, '2019-11-14 02:14:27', '2019-11-19 21:05:03', 0),
(137, 'Lucas', 'Lucarelli', 3, '2915722804', 'lucas@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:54:12', '2019-11-14 05:54:12', 0),
(138, 'Lucas', 'Gili', 4, '2914165011', 'lucasg@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:55:08', '2019-11-14 05:55:08', 0),
(139, 'Guillermina', 'G', 5, '2215598360', 'guille@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:55:53', '2019-11-14 05:55:53', 0),
(140, 'Lucia', 'L', 6, '1151791210', 'lucia@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:56:38', '2019-11-14 05:56:38', 0),
(141, 'Pato', 'P', 7, '2914124634', 'pato@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:58:01', '2019-11-14 05:58:01', 0),
(142, 'Nuria', 'N', 8, '2914636102', 'nuria@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:58:40', '2019-11-14 05:58:40', 0),
(143, 'Yoha', 'Y', 9, '2914745733', 'yoha@test.com', '2000-10-10', '2000-01-01', '', '', '', 1, '2019-11-14 05:59:27', '2019-11-14 05:59:27', 0),
(144, 'Mateo', 'Bernetti', 47279368, '2915750394', 'ANITA_PILOTO@HOTMAIL.COM', '2006-07-04', '2000-01-01', 'IOMA', '', '', 1, '2019-11-14 10:05:23', '2019-11-19 21:05:12', 0),
(145, 'Justina', 'Martinez Forgione', 56639579, '2914364469', 'Aldana_forgione@hotmail.com', '2017-11-17', '2000-01-01', 'Acá Salud', '08306438', 'As300', 1, '2019-11-14 11:54:00', '2019-11-20 13:33:03', 0),
(146, 'Valentín', 'Mantel', 56779515, '2914024724', 'marubore@hotmail.com', '2018-01-08', '2000-01-01', 'Unión personal', '02519927018', 'Accord dorado', 1, '2019-11-14 12:42:03', '2019-11-19 21:05:25', 0),
(147, 'Francisca', 'Lurbe', 57969703, '2915707653', 'veromaria_17@hotmail.com', '2019-10-05', '2000-01-01', 'Osde', '62307343803', '410', 1, '2019-11-14 17:37:53', '2019-11-19 21:05:36', 0),
(148, 'Guillermina milagros', 'Román serone', 53681367, '2915102485', 'Luly_serone@hotmail.com', '2013-12-27', '2000-01-01', 'Medife', '3-01097043-01/340', 'Plata', 1, '2019-11-14 19:51:50', '2019-11-14 21:18:41', 0),
(149, 'Jano rafael', 'Bustelo cossia', 56719564, '02915277540', 'Fanny.day33@yahoo.com.ar', '2017-11-28', '2000-01-01', 'Ospsa', '20-37555992-8-04', '', 1, '2019-11-14 21:53:05', '2019-11-15 02:15:44', 0),
(150, 'Francisco vicente', 'Cossia soulier', 49059703, '02915277540', 'Fanny.day33@yahoo.com.ar', '2009-01-23', '2000-01-01', 'Ospsa', '20-37555992-8-02', '', 1, '2019-11-14 21:55:38', '2019-11-15 02:15:45', 0),
(151, 'Matilda', 'Eisenmeier', 56938056, '155098418', '', '2018-03-14', '2000-01-01', 'part', '', '', 1, '2019-11-14 21:59:20', '2019-11-19 13:32:15', 0),
(155, 'Uma', 'Sapienza', 51155330, '156465113', 'spsofia1115@gmail.com', '2011-07-06', '2000-01-01', 'Ospsa', '2735334803002', 'Obligatorio', 1, '2019-11-15 12:10:35', '2019-11-19 12:38:11', 0),
(156, 'Juan', 'hernandez bender', 54380073, '1131563451', 'arb.bender@gmail.com', '2014-11-11', '2000-01-01', 'ospbb', '', '', 1, '2019-11-16 04:16:00', '2019-11-20 13:33:13', 0),
(157, 'Gregorio', 'Cantero', 57087362, '2915718491', 'julianacolalonga@hotmail.com', '2018-06-24', '2000-01-01', 'Jerárquicos', '86939-2', 'Pmi 2000', 1, '2019-11-17 23:30:19', '2019-11-19 13:32:24', 0),
(158, 'Lucas Miguel', 'Serrano', 52185137, '2932440888', '', '2012-04-25', '2000-01-01', 'IOSFA', '010709-2/06', '', 1, '2019-11-18 00:42:06', '2019-11-26 19:28:39', 0),
(159, 'Tomas', 'Casanova', 57764726, '2914764767', 'melisa_schwerdt@hotmail.com', '2019-07-03', '2000-01-01', 'Medife', '3-06205524-01/000', 'Plata', 1, '2019-11-18 15:33:25', '2019-11-19 13:32:36', 0),
(160, 'Felipe', 'Lucarelli', 50551419, '2915722804', 'l_lucarelli@hotmail.com', '2010-10-03', '2000-01-01', 'IOMA', '', '', 1, '2019-11-19 12:37:13', '2019-11-19 12:37:13', 0),
(161, 'Emilia', 'Lucarelli', 53756120, '2915722804', 'l_lucarelli@hotmail.com', '2014-02-03', '2000-01-01', 'IOMA', '', '', 1, '2019-11-19 13:09:24', '2019-11-19 13:09:24', 0),
(162, 'Felipe', 'Bavio', 56034832, '2914297060', 'ctamborindegui@yahoo.com.ar', '2016-11-27', '2000-01-01', 'Federada salud', '121396 02', '2000', 1, '2019-11-19 21:58:58', '2019-11-20 13:32:49', 0),
(163, 'Enzo mauricio', 'Herrera', 52193366, '2914617239', 'leoferenzo@gmail.com', '2012-02-08', '2000-01-01', 'Opsa', '27-24413614-7-02', '', 1, '2019-11-20 21:35:59', '2019-11-26 19:28:32', 0),
(164, 'LAUTARO', 'GOMEZ BRIZZOLA', 57671414, '2914222194', 'maricelbrizzola@hotmail.com', '2019-05-13', '2000-01-01', 'IOSFA', '57671414', 'MATERNO', 1, '2019-11-20 22:48:31', '2019-11-22 12:25:31', 0),
(165, 'IMANOL', 'GOMEZ BRIZZOLA', 57671413, '2914222194', 'maricelbrizzola@hotmail.com', '2019-05-13', '2000-01-01', 'IOSFA', '57671413', 'MATERNO', 1, '2019-11-20 22:50:44', '2019-11-22 12:25:38', 0),
(166, 'SANTIAGO', 'GOMEZ BRIZZOLA', 57671412, '2914222194', 'maricelbrizzola@hotmail.com', '2019-05-13', '2000-01-01', 'IOSFA', '57671412', 'MATERNO', 1, '2019-11-20 22:51:41', '2019-11-22 12:25:49', 0),
(167, 'Maite', 'Mildenberger', 54880940, '2915105035', 'Cintiabeiteleztn@gmail.com', '2015-06-19', '2000-01-01', 'Osde', '', 'Osde', 1, '2019-11-21 13:44:22', '2019-11-22 20:13:09', 0),
(168, 'Uma', 'Tkalecz Malizia', 50551308, '2914068241', 'maliziavanina@gmail.com', '2010-09-20', '2000-01-01', 'Iofa', 'D230446102', '', 1, '2019-11-21 14:30:12', '2019-11-26 19:28:24', 0),
(169, 'Juan Pedro', 'Bosso', 47883172, '2914664731', 'anahitce@hotmail.com', '2007-02-27', '2000-01-01', 'Swiss Medical', '8000060993710040028', 'SMG30', 1, '2019-11-21 14:37:51', '2019-11-22 20:13:36', 0),
(170, 'Gael', 'Garcia Massarella', 47739268, '2914406978', 'noeliamass@hotmail.com', '2006-12-02', '2000-01-01', 'Ioma', '22657137403', 'Obligatorio', 1, '2019-11-21 18:13:57', '2019-11-21 19:20:25', 0),
(189, 'Emma', 'Mussa Massarella', 57316294, '2914406978', 'noeliamass@hotmail.com', '2018-10-23', '2000-01-01', 'Ioma', '2265713742', 'Obligatorio', 1, '2019-11-21 18:32:05', '2019-11-21 19:02:36', 0),
(190, 'Manuel', 'Mussa Massarella', 57316295, '2914406978', 'noeliamass@hotmail.com', '2018-10-23', '2000-01-01', 'Ioma', '22657137474', 'Obligatorio', 1, '2019-11-21 18:33:12', '2019-11-21 19:02:40', 0),
(191, 'Mateo Ezequiel', 'Jardin Gazzarri', 48115825, '2914818404', 'jatar70@gmail.com', '2007-08-01', '2000-01-01', 'OSPSA', '20213859723/02', '', 1, '2019-11-21 19:40:42', '2019-11-21 21:33:01', 0),
(192, 'María de los Milagros', 'Jardin Gazzarri', 51124103, '2914818404', 'jatar70@gmail.com', '2011-04-30', '2000-01-01', 'OSPSA', '20213859723/03', '', 1, '2019-11-21 19:43:42', '2019-11-21 19:48:24', 0),
(193, 'María Isabela', 'Jardin Gazzarri', 53472220, '2914818404', 'jatar70@gmail.com', '2013-09-25', '2000-01-01', 'OSPSA', '20213859723/04', '', 1, '2019-11-21 19:45:52', '2019-11-21 19:48:16', 0),
(194, 'Emma Alejandra', 'gigena barrera', 53600261, '2932617042', 'hrgigena@hotmail.com', '2014-01-14', '2000-01-01', 'iosfa', '741700-3/02', '', 1, '2019-11-21 20:36:41', '2019-11-21 20:36:41', 0),
(195, 'Joaquín', 'Elizondo', 53993519, '2914628437', 'debic_81@hotmail.com', '2014-05-06', '2000-01-01', 'OSDOP', '2930011206', '', 1, '2019-11-21 21:40:01', '2019-11-22 18:29:02', 0),
(196, 'Sofía', 'Elizondo', 56719528, '2914628437', 'debic_81@hotmail.com', '2017-11-30', '2000-01-01', 'OSDOP', '2930011207', '', 1, '2019-11-21 21:41:34', '2019-11-21 22:38:49', 0),
(197, 'Maximo', 'Montironi', 53756129, '2915032428', 'Betianaaizpuru@yahoo.com.ar', '2014-03-03', '2000-01-01', 'Federada salud', '972513', 'Familiar', 1, '2019-11-21 22:18:22', '2019-11-21 22:38:57', 0),
(198, 'Sofia', 'Montironi', 57316261, '2915032428', 'Betianaaizpuru@yahoo.com.ar', '2018-10-26', '2000-01-01', 'Federada Salud', '972514', 'Familiar', 1, '2019-11-21 22:20:19', '2019-11-21 22:38:40', 0),
(199, 'Santiago', 'Pascual', 52932975, '2914438983', 'Cuatrodejulio@hotmail.com', '2013-01-13', '2000-01-01', 'IOSFA', '021397-5/04', '', 1, '2019-11-22 14:51:25', '2019-11-22 18:29:08', 0),
(200, 'Nicolás', 'Pascual', 50889715, '2914438983', 'Cuatrodejulio@hotmail.com', '2011-03-18', '2000-01-01', 'IOSFA', '021397-5/03', '', 1, '2019-11-22 14:53:29', '2019-11-22 18:29:14', 0),
(201, 'Aldi', 'Mulena', 39444103, '2932573107', 'aldimt_96@hotmail.com', '1996-11-02', '2000-01-01', '', '', '', 1, '2019-11-22 14:54:00', '2019-11-22 14:54:16', 0),
(202, 'Noah uriel', 'Baigorria', 56938122, '2915030126', 'Danny_5592@hotmail.com', '2018-03-15', '2000-01-01', 'Somu', '37574/05', '', 1, '2019-11-22 20:10:29', '2019-11-22 20:12:45', 0),
(203, 'Pedro', 'Giqueaux Ochoa', 55196811, '2914322049', 'ochoajuliana09@gmail.com', '2015-11-06', '2000-01-01', 'OSECAC', '', '', 1, '2019-11-22 22:53:37', '2019-11-26 19:28:12', 0),
(205, 'Paz', 'Silva', 55101872, '2914369981', 'laliypaz8@gmail.com', '2015-10-02', '2000-01-01', '', '', '', 1, '2019-11-23 22:28:51', '2019-11-26 19:28:02', 0),
(206, 'Ignacio', 'Merlano', 37621618, '2923400938', 'ignaciomerlano@hotmail.com', '1993-09-09', '2000-01-01', '', '', '', 1, '2019-11-25 16:34:48', '2019-11-25 16:38:02', 0),
(207, 'Anastasia', 'Isla acosta', 54347665, '2914661986', 'sofy2102@hotmail.com', '2014-11-19', '2000-01-01', 'Ioma', '232209411202', '', 1, '2019-11-25 20:01:42', '2019-11-26 19:27:53', 0),
(208, 'Gio', 'Emanuele Gatti', 57638162, '2916431657', 'pamelagatti_84@hotmail.com', '2019-04-11', '2000-01-01', 'Particular', '', '', 1, '2019-11-25 21:08:01', '2019-11-26 19:27:43', 0),
(209, 'Vico', 'Emanuele Gatti', 57638163, '2916431657', 'pamelagatti_84@hotmail.com', '2019-04-11', '2000-01-01', 'Particular', '', '', 1, '2019-11-25 21:08:51', '2019-11-26 19:27:33', 0),
(210, 'Isabella Mía', 'Guerra Cassataro', 57838581, '2915740066', 'mairacassa76@hotmail.com', '2019-09-25', '2000-01-01', 'Swiss Medical', '8000060470484051010', 'Smg 20', 1, '2019-11-26 01:55:10', '2019-11-26 11:31:25', 0),
(211, 'Francesca', 'Quintana', 50840279, '2914136198', 'veronicalang2008@hotmail.com', '2011-03-16', '2000-01-01', 'Medife', '3-00956306-01/340', 'Oro', 1, '2019-11-26 12:44:40', '2019-11-26 19:27:23', 0),
(212, 'María emilia', 'Llera soule', 57838497, '02914316935', 'Jime.soule@gmail.com', '2019-09-11', '2000-01-01', 'Federada', '19103903', 'G1', 1, '2019-11-26 17:23:07', '2019-11-26 19:27:13', 0),
(213, 'Julia', 'Santos', 57176967, '2914492252', 'Nataliadanielaaranda@gmail.com', '2018-08-01', '2000-01-01', 'No', 'No', 'No', 1, '2019-11-26 19:15:52', '2019-11-26 19:27:02', 0),
(214, 'Valentino gael', 'Gutierrez', 57258985, '01150202536', 'Antonellariggio.33@gmail.com', '2018-09-29', '2000-01-01', 'Ioma', '133327049402', '010', 1, '2019-11-26 20:27:17', '2019-11-26 21:01:25', 0),
(215, 'kalid', 'Tomassini', 49664695, '154046926', 'Keila_kilian@hotmail.com', '2009-07-29', '2000-01-01', 'Swiss medical', '8000067207793040042', '4125', 1, '2019-11-26 23:00:08', '2019-11-27 12:56:16', 0),
(216, 'Kilian', 'Tomassini', 46270968, '154046926', 'Keila_kilian@hotmail.com', '2004-12-21', '2000-01-01', 'Swiss medical', '8000067207793030043', '4125', 1, '2019-11-26 23:01:38', '2019-11-27 12:56:54', 0),
(217, 'Olivia justina', 'Venegas', 57241503, '2914736460', 'fabianaruiz2009@hotmail.com', '2018-08-24', '2000-01-01', 'Ioma', '235633522302', 'No', 1, '2019-11-26 23:42:40', '2019-11-27 12:57:05', 0),
(218, 'Jazmín Abigail', 'Courteaux', 55729412, '2915373585', 'guillerminagomez@outlook.com', '2016-08-16', '2000-01-01', 'Ioma', '1367514908/02', 'Ioma', 1, '2019-11-26 23:46:15', '2019-11-27 02:17:14', 0),
(219, 'León Santiago', 'Courteaux', 57898010, '2915373585', 'guillerminagomez@outlook.com', '2019-08-23', '2000-01-01', 'Ioma', '1367514908/03', 'Ioma', 1, '2019-11-26 23:49:37', '2019-11-27 02:17:24', 0),
(220, 'Mateo', 'Morales', 54365364, '2915096861', 'luchipolito1989@gmail.com', '2014-10-03', '2000-01-01', 'Ioma', '1354131268/02', '', 1, '2019-11-26 23:50:55', '2019-11-27 12:57:12', 0),
(221, 'Franchesco', 'Manzo Ramazzotti', 56639564, '2915006522', 'debo_ramazzotti@hotmail.com', '2017-11-22', '2000-01-01', 'Medife', '3-01199793-01/340', 'Plata', 1, '2019-11-27 01:12:27', '2019-11-27 12:57:19', 0),
(222, 'Jeremias', 'Gonzalez', 51458230, '2914992358', 'danny_alcaraz86@hotmail.com', '2011-12-01', '2000-01-01', 'Federada seguros', '13863203', '4000', 1, '2019-11-27 17:25:04', '2019-11-27 17:53:48', 0),
(223, 'VALENTINA', 'BILLORDO', 47516422, '2915713528', 'angy.ibanez@live.com.ar', '2006-09-07', '2000-01-01', 'iosfa', '47516422', '', 1, '2019-11-27 19:34:28', '2019-11-27 19:58:48', 0),
(224, 'MARTIN SAMUEL', 'BILLORDO', 52399985, '2915713528', 'angy.ibanez@live.com.ar', '2012-04-11', '2000-01-01', 'IOSFA', '52399985', '', 1, '2019-11-27 19:36:08', '2019-11-27 19:58:38', 0),
(225, 'LORENZO EMANUEL', 'BILLORDO', 54730130, '2915713528', 'angy.ibanez@live.com.ar', '2015-06-02', '2000-01-01', 'IOSFA', '54730130', '', 1, '2019-11-27 19:37:51', '2019-11-27 19:58:33', 0),
(226, 'ignacio', 'alaniz', 55944529, '2916436712', '', '2016-11-01', '2000-01-01', 'osdop', '3017459307', '', 1, '2019-11-27 22:04:27', '2019-11-28 11:30:50', 0),
(229, 'Mateo ariel', 'Saibene', 54857881, '2914058302', 'magui_ms15@hotmail.com', '2015-09-21', '2000-01-01', 'Iosfa', '070415-0/02', 'Familia', 1, '2019-11-28 12:36:07', '2019-11-28 20:38:14', 0),
(233, 'Milena', 'Garrini', 56628085, '2914460011', 'cinalarcon06@gmail.com', '2017-10-29', '2000-01-01', 'Ospif', '3440270201', 'Interno', 1, '2019-11-28 19:08:19', '2019-11-28 19:10:20', 0),
(234, 'Samira', 'Mendez', 52613216, '2914460011', 'cinalarcon06@gmail.com', '2012-08-02', '2000-01-01', '', '', '', 1, '2019-11-28 19:09:34', '2019-11-28 19:10:30', 0),
(235, 'Moroni Joaquina', 'Moroni', 29300912, '2914055912', 'Leticia.flamini@hotmail.com', '2018-03-26', '2000-01-01', 'Ospedyc', '29300912/02', '', 1, '2019-11-28 20:08:33', '2019-11-28 20:12:12', 0),
(236, 'gennaro', 'coronel', 56639456, '154683306', '', '2017-11-08', '2000-01-01', '', '', '', 1, '2019-11-28 20:09:14', '2019-11-28 20:09:14', 0),
(237, 'joaquina', 'moroni', 56407791, '154055912', '', '2018-03-26', '2000-01-01', '', '', '', 1, '2019-11-28 20:21:37', '2019-11-28 20:21:37', 0),
(238, 'constanza', 'foti', 57079964, '155702087', '', '2018-06-29', '2000-01-01', '', '', '', 1, '2019-11-28 20:26:20', '2019-11-28 20:26:20', 0),
(239, 'joaquin', 'Gonzalez Richardson', 57021699, '2914165011', 'lucasgonzalezg@hotmail.com', '2018-04-23', '2000-01-01', 'DOSEM', '030692/3', '', 1, '2019-11-28 20:30:39', '2019-11-28 20:38:20', 0),
(240, 'bianca', 'onorio', 46339611, '155742529', 'bianquita2005@hotmail.com.ar', '2005-02-23', '2000-01-01', 'ioma', '225447763002', '', 1, '2019-11-28 21:09:30', '2019-11-28 21:09:30', 0),
(241, 'morena', 'onorio', 50659082, '', '', '2010-10-22', '2000-01-01', '', '', '', 1, '2019-11-28 21:14:43', '2019-11-28 21:14:43', 0),
(242, 'franco', 'onorio', 53521961, '', '', '2013-09-27', '2000-01-01', 'ioma', '225447763004', '', 1, '2019-11-28 21:15:45', '2019-11-28 21:15:45', 0),
(243, 'matheo', 'benegas', 57241517, '155065665', '', '2018-08-17', '2000-01-01', 'ioma', '236751952603', '', 1, '2019-11-28 22:20:01', '2019-11-28 22:20:01', 0),
(244, 'Bautista', 'Cuende Darrechi', 56046707, '2914253092', 'pauladarrechi@hotmail.com', '2016-12-31', '2000-01-01', 'Swiss Medical', '800006 0480054 02 1004', 'SMG20', 1, '2019-11-29 02:47:57', '2019-11-29 21:32:55', 0),
(245, 'FRANCO LAUTARO', 'DARRECHI', 47013517, '2914046641', 'silvanaschmidt@hotmail.com.ar', '2005-10-13', '2000-01-01', '', '', '', 1, '2019-11-29 11:49:50', '2019-11-29 21:33:05', 0),
(246, 'LUCAS', 'BERTONE', 46339672, '2914133740', 'danielaabertone@hotmail.com', '2005-03-02', '2000-01-01', 'FEDERADA SALUD', '13813702', 'PLAN 2000', 1, '2019-11-30 11:59:05', '2019-12-02 20:41:50', 0),
(247, 'DANTE', 'LEGUIZAMO BERTONE', 57399010, '2914133740', 'danielaabertone@hotmail.com', '2019-01-21', '2000-01-01', 'FEDERADA SALUD', '13813704', 'PLAN 2000', 1, '2019-11-30 11:59:47', '2019-12-02 20:41:49', 0),
(248, 'Juan', 'Test', 90, '1', '', '2020-10-10', '2000-01-01', '', '', '', 1, '2019-11-30 16:27:10', '2019-11-30 16:27:10', 0),
(249, 'Vicente', 'Santos', 55147836, '2914392252', 'Nataliadanielaaranda@gmail', '2015-10-08', '2000-01-01', 'No', 'No', 'No', 1, '2019-12-01 19:25:56', '2019-12-03 19:13:33', 0),
(250, 'Isabella', 'Hernandez', 57671500, '2915058687', 'Danaavila084@gmail.com', '2019-05-02', '2000-01-01', 'Ospm', '08/0002126/04', 'Plan maritimos', 1, '2019-12-02 03:56:07', '2019-12-03 19:13:46', 0),
(251, 'Micaela aylen', 'montenegro', 44881869, '2916456324', 'sabry-79@hotmail.es', '2003-03-10', '2000-01-01', 'sancor salud', '1012308/03', '3000', 1, '2019-12-02 14:20:53', '2019-12-03 19:13:58', 0),
(252, 'tomas elian', 'barberis', 57977314, '2916456324', 'sabry-79@hotmail.es', '2019-11-07', '2000-01-01', 'no', 'no', 'no', 1, '2019-12-02 14:25:22', '2019-12-03 19:14:14', 0),
(253, 'Lima gael', 'Ponce bertoni', 57241452, '2916454312', 'Silvanabertoni2008@gmail.com', '2018-08-16', '2000-01-01', 'Ioma', '232978137056-01', '', 1, '2019-12-02 14:27:10', '2019-12-03 19:14:26', 0),
(254, 'Bianca luna', 'Domínguez', 48503009, '2916454312', 'Silvanabertoni2008@gmail.com', '2008-02-06', '2000-01-01', 'Ioma', '2329781372/02', '', 1, '2019-12-02 14:30:27', '2019-12-03 19:14:46', 0),
(255, 'Medina kiara', 'Medina', 46697665, '2914164544', 'Silvia_b81@hotmail.com', '2005-07-09', '2000-01-01', 'Ospsa', '27_29159172_3_02', 'Obligatorio', 1, '2019-12-02 19:44:47', '2019-12-02 20:42:09', 0),
(256, 'Micaela yamila', 'Schroeder', 51458117, '2914229861', 'mendezleonela25@gmail.com', '2011-11-21', '2000-01-01', 'Osdipp', '03085604282', '3sq', 1, '2019-12-02 20:28:15', '2019-12-02 20:42:03', 0),
(257, 'Martin agustin', 'Schroeder', 55322587, '2914229861', 'mendezleonela25@gmail.com', '2016-02-03', '2000-01-01', 'Osdipp', '03085605600', '3sq', 1, '2019-12-02 20:29:40', '2019-12-02 20:42:14', 0),
(258, 'Genaro', 'Diaz', 56779518, '2914488728', 'Gaunaflorencia@hotmail.com', '2017-12-27', '2000-01-01', 'Iosfa', '018162-2/04', '', 1, '2019-12-02 20:41:14', '2019-12-03 19:15:02', 0),
(259, 'Mauricio', 'Diaz', 54833360, '2914488728', 'Gaunaflorencia@hotmail.com', '2016-04-07', '2000-01-01', 'Iosfa', '018162-2/03', '', 1, '2019-12-02 20:43:06', '2019-12-03 19:16:53', 0),
(260, 'Nuevo I', 'Paciente 1', 10, '0', '', '1000-01-01', '2000-01-01', '', '', '', 1, '2019-12-02 18:10:53', '2019-12-02 18:10:53', 0),
(261, 'Nuevo 2', 'Paciente 2', 11, '0', '', '1000-01-01', '2000-01-01', '', '', '', 1, '2019-12-02 18:11:38', '2019-12-02 18:11:38', 0),
(262, 'Benicio', 'Arnaldi Herrera', 56938003, '2914149274', 'fabian250174@hotmail.com', '2018-03-18', '2000-01-01', 'Federada salud', '11034902', 'Plan 2000', 1, '2019-12-02 21:16:00', '2019-12-03 19:17:01', 0),
(263, 'Matías Nahuel', 'Bustos', 49059968, '2914741129', 'gomez35ivi@gmail.com', '2008-12-14', '2000-01-01', 'OSPEDYC', '29510624 01', '', 1, '2019-12-02 21:48:35', '2019-12-03 19:17:10', 0),
(268, 'amparo', 'alvarado', 57258826, '2915225796', '', '2019-09-14', '2000-01-01', 'no', '', 'particular', 1, '2019-12-03 16:22:05', '2019-12-03 19:17:18', 0),
(269, 'EMILIA', 'BELLADONNA', 56628100, '2914621307', 'soledad.bordenave@gmail.com', '2017-10-10', '2000-01-01', 'ACCORD SALUD', '00813964018', 'DORADO', 1, '2019-12-03 19:33:50', '2019-12-03 19:40:45', 0),
(270, 'Franco', 'Corn', 48774009, '2916413207', 'ccorn182@gmail.com', '2008-06-08', '2000-01-01', 'O.S.C.T.C.P.', '9797/02', '', 1, '2019-12-03 20:38:21', '2019-12-03 20:42:00', 0),
(271, 'LAUTARO', 'MADAMI', 57541339, '2920517856', '', '2019-02-14', '2000-01-01', 'IOMA', '', '', 1, '2019-12-03 20:51:29', '2019-12-03 20:51:29', 0),
(272, 'Jeremias', 'Macre', 48293407, '2915715751', 'Betina_barca@hotmail.com', '2007-09-08', '2000-01-01', 'Ioma', '2220536252/04', '', 1, '2019-12-03 21:15:54', '2019-12-03 21:16:07', 0),
(273, 'Fermín', 'Rodriguez Rivera', 58051721, '2914729735', 'mailenrivera@gmail.com', '2019-11-24', '2000-01-01', 'Ospbb', '1031568/02', '', 1, '2019-12-03 21:17:34', '2019-12-03 21:17:42', 0),
(274, 'Francisco', 'Fiz', 54365384, '2914199663', 'leonardo_fiz@hotmail.com', '2014-11-04', '2000-01-01', 'OSPEDYC', '28665388-02', 'unico', 1, '2019-12-03 21:22:50', '2019-12-04 13:49:45', 0),
(275, 'Santiago Nahuel', 'Ramirez', 49589864, '2914664679', 'fabyocon@hotmail.com', '2009-09-10', '2000-01-01', 'Iosfa', '031267-6/03', '', 1, '2019-12-03 21:24:32', '2019-12-04 13:49:46', 0),
(285, 'Felipe', 'Trespando', 48883303, '2915037168', 'lauratrespando@hotmail.com', '2008-09-12', '2000-01-01', 'Osdipp', '03407304405', 'Plan 4f', 1, '2019-12-03 21:27:19', '2019-12-04 13:49:57', 0),
(286, 'Ignacio Gael', 'Ramirez', 51155147, '2914664679', 'fabyocon@hotmail.com', '2011-07-17', '2000-01-01', 'Iosfa', '031267-6/04', '', 1, '2019-12-03 21:30:19', '2019-12-04 13:49:48', 0),
(287, 'Gino', 'Iriroy', 57764781, '2914184016', 'Cintiayanil87@hotmail.com', '2019-07-20', '2000-01-01', '', '', '', 1, '2019-12-03 21:33:17', '2019-12-04 13:49:54', 0),
(288, 'Mia', 'Billa', 51155455, '2932504234', 'cldbotter@gmail.com', '2011-08-12', '2000-01-01', 'Iosfa', '609614-3/06', '', 1, '2019-12-03 21:34:44', '2019-12-04 13:50:01', 0),
(289, 'Brisa', 'Billa', 49536740, '2932504234', 'cldbotter@gmail.comiosfa', '2009-08-12', '2000-01-01', 'Iosfa', '609614-3/05', '', 1, '2019-12-03 21:36:06', '2019-12-04 13:50:04', 0),
(290, 'Luana Emilia', 'Felipe', 53127271, '2915700032', 'dollyaries76@hotmail.com', '2013-03-07', '2000-01-01', 'Ioma', '2248894779/02', '', 1, '2019-12-03 22:01:12', '2019-12-04 13:50:07', 0),
(291, 'Sofia abril', 'Andrada', 49918211, '2915053844', 'Sole812011@hotmail.com', '2010-01-12', '2000-01-01', 'Diba', '025982-6/05', '', 1, '2019-12-03 22:05:40', '2019-12-04 13:50:17', 0),
(292, 'Tiziano', 'Donato', 53883188, '0291155075215', 'flanroir@hotmail.com', '2014-05-31', '2000-01-01', 'Osde', '', '210', 1, '2019-12-03 22:10:37', '2019-12-04 13:50:21', 0),
(293, 'Valentina', 'Detzel', 54226720, '2914619572', 'collazoscarolina@gmail.com', '2014-10-06', '2000-01-01', 'Acá salud', '237224 20', 'Integral', 1, '2019-12-03 22:11:17', '2019-12-04 13:50:32', 0),
(294, 'Bianca', 'Donato', 52029381, '2915075215', 'flanroir@hotmail.com', '2012-01-18', '2000-01-01', 'Osde', '', '210', 1, '2019-12-03 22:12:16', '2019-12-04 13:50:12', 0),
(295, 'Camila', 'Detzel', 56112932, '2914619572', 'collazoscarolina@gmail.com', '2017-02-06', '2000-01-01', 'Acá salud', '237224 37', 'Integral', 1, '2019-12-03 22:13:10', '2019-12-04 13:50:35', 0),
(296, 'Abril Olivia', 'De Maria', 56352315, '2914189679', 'lucasjdm@speedy.com.ar', '2017-05-27', '2000-01-01', 'Osde', '31209152605', '410', 1, '2019-12-03 22:24:46', '2019-12-04 13:50:39', 0),
(297, 'Lupe Agostina', 'De Maria', 57765251, '2914189679', 'lucasjdm@speedy.com.ar', '2019-07-18', '2000-01-01', 'Osde', '31209152606', '410', 1, '2019-12-03 22:27:02', '2019-12-04 13:50:43', 0),
(298, 'aldo', 'moreti', 32198776, '02392442482', 'mamaaldo@hotmail.com', '1998-06-04', '2000-01-01', 'osde', '728979', '910', 1, '2019-12-03 22:52:43', '2019-12-03 22:52:43', 0),
(299, 'Francisco', 'Alvarez', 50889620, '2915036106', 'elidaarroyo97@gmail.com', '2011-03-01', '2000-01-01', 'iOS BA', '388789-8/05', '', 1, '2019-12-03 19:58:58', '2019-12-03 19:58:58', 0),
(300, 'juana', 'perez', 89, '02392442482', 'juanigomez@gmail.com', '1980-05-02', '2000-01-01', 'ioma', '23', '', 1, '2019-12-03 20:01:23', '2019-12-03 20:01:23', 0),
(301, 'Juan', 'Perez', 88, '', '', '1000-01-01', '2000-01-01', '', '', '', 1, '2019-12-03 23:06:14', '2019-12-03 23:06:14', 0),
(302, 'Isabella', 'Zsizsik raissing', 54663511, '2914984648', 'zsizsikcristian@gmail.com', '2015-03-23', '2000-01-01', 'No', '', '', 1, '2019-12-03 23:10:13', '2019-12-04 13:50:46', 0),
(303, 'Joaquín', 'Gimenez', 48568238, '2914022928', 'valeteru1580@gmail.com', '2008-02-13', '2000-01-01', 'No', '', '', 1, '2019-12-04 01:37:00', '2019-12-04 13:50:50', 0),
(304, 'David Ezequiel', 'Coronel', 48883288, '2915264998', 'Cintiagodoy_7@hotmail.com', '2008-09-30', '2000-01-01', 'IOSFA', '078159-6/03', '', 1, '2019-12-04 11:43:52', '2019-12-04 13:51:28', 0),
(305, 'Tatiana Micaela', 'Coronel', 52035839, '2915264998', 'Cintiagodoy_7@hotmail.com', '2012-01-12', '2000-01-01', 'Iosfa', '078159-6/04', '', 1, '2019-12-04 11:46:16', '2019-12-04 13:51:33', 0),
(306, 'Olivia Anabel', 'Coronel', 57633646, '2915264998', 'Cintiagodoy_7@hotmail.com', '2019-04-09', '2000-01-01', 'Iosfa', '078159-6/05', '', 1, '2019-12-04 11:48:13', '2019-12-04 13:51:22', 0),
(307, 'Aitana', 'Arzer', 56797503, '2914729980', 'mdanielafer@yahoo.com.ar', '2017-12-26', '2000-01-01', 'IOMA', '2300625983/00', '', 1, '2019-12-04 11:59:52', '2019-12-04 13:51:18', 0),
(308, 'Pilar', 'Gachetegui', 52399919, '2914748294', 'maderasnecochea@hotmail.com', '2012-03-07', '2000-01-01', 'Medife', '3-01212381-02/340', 'Plan Plata', 1, '2019-12-04 12:26:08', '2019-12-04 13:51:12', 0),
(309, 'Isabella', 'Gachetegui', 56411474, '2914748294', 'maderasnecochea@hotmail.com', '2017-06-29', '2000-01-01', 'Medife', '3-01212381-03/340', 'Plan Plata', 1, '2019-12-04 12:28:11', '2019-12-04 13:51:36', 0),
(310, 'Bruno', 'Lopes', 27235218, '01123405569', 'ignaciolopes79@gmail.com', '2008-05-14', '2000-01-01', 'Unión Personal', '', '', 1, '2019-12-04 12:31:49', '2019-12-04 13:51:04', 0),
(311, 'Agustín', 'Telez', 51054883, '155386089', 'Susimastrovalerio12@gmail.com', '2011-06-12', '2000-01-01', 'Ioma', '2296312344/02', 'Ioma', 1, '2019-12-04 12:36:22', '2019-12-04 13:51:08', 0),
(312, 'Juan francisco e', 'Ruiz', 55029339, '2914052587', 'daianette@gmail.com', '2015-08-27', '2000-01-01', '', '', '', 1, '2019-12-04 13:18:16', '2019-12-04 13:52:34', 0),
(313, 'PILAR', 'LLAMBRICH', 55029470, '155718540', 'ximeprin@hotmail.com', '2015-09-01', '2000-01-01', 'OSDE', '61725077302', '210', 1, '2019-12-04 13:27:48', '2019-12-04 14:20:24', 0),
(314, 'Bianca', 'Seijas Radice', 53681305, '2914602411', 'Day_gise@hotmail.com', '2013-12-12', '2000-01-01', 'Swiss medical', '800006 1212281 03 1004', 'SMG20', 1, '2019-12-04 13:46:56', '2019-12-04 13:51:00', 0),
(315, 'Iñaki', 'Sancio', 56411503, '2914255305', 'gabriela.miguel82@gmail.com', '2017-07-17', '2000-01-01', 'OSDE', '61049785403', '210', 1, '2019-12-04 14:09:27', '2019-12-04 14:19:54', 0),
(316, 'Santino', 'Saccoccia', 47163438, '2915129573', 'andreapais78@hotmail.com', '2006-03-28', '2000-01-01', 'Ospsa', '20-29360460-7-03', '', 1, '2019-12-04 14:13:40', '2019-12-04 14:20:04', 0),
(317, 'Bruno', 'Lopes', 48592162, '01123405569', 'ignaciolopes79@gmail.com', '2008-05-14', '2000-01-01', 'Unión Personal', '', '', 1, '2019-12-04 14:43:59', '2019-12-04 17:10:50', 0),
(318, 'Alma', 'Mondino murcia', 54956888, '2915042335', 'evelyn.murcia@hotmail.com', '2015-06-20', '2000-01-01', 'Aca salud', '17847724', 'As300', 1, '2019-12-04 14:58:57', '2019-12-04 17:10:56', 0),
(319, 'Ian', 'Zwenger Kees', 57399140, '2914479949', 'diamekees@gmail.com', '2019-02-25', '2000-01-01', 'Ospif', '3213760201', 'Plan único materno infantil', 1, '2019-12-04 15:33:17', '2019-12-04 17:11:11', 0),
(320, 'Valentin', 'Malanga', 53992447, '2914027728', 'sara.t.castro@hotmail.com', '2014-05-09', '2000-01-01', 'Ioma', '2299202112/02', '', 1, '2019-12-04 16:22:14', '2019-12-04 17:12:29', 0),
(321, 'Vicente', 'Malanga', 57258982, '2914027728', 'sara.t.castro@hotmail.com', '2018-09-27', '2000-01-01', 'Ioma', '229920211203', '', 1, '2019-12-04 16:23:45', '2019-12-04 17:12:35', 0),
(322, 'Jazmin', 'Rodríguez Tiette', 57258918, '2915090848', 'nadiatiette@gmail.com', '2019-09-19', '2000-01-01', 'Osde', '62345662004', '210', 1, '2019-12-04 17:03:48', '2019-12-04 17:12:42', 0),
(323, 'Milo pol', 'fernandez godoy', 57258933, '2914443985', 'licho_loma@hotmail.com', '2018-09-18', '2000-01-01', 'federada', '17954202', '4000', 1, '2019-12-04 17:10:52', '2019-12-04 17:12:48', 0),
(324, 'Bautista', 'Meyer', 57316249, '2923409264', 'gabrielamagnook@gmail.com', '2018-10-19', '2000-01-01', 'Osde', '62758625103', '210', 1, '2019-12-04 17:14:28', '2019-12-04 17:14:37', 0),
(325, 'Catalina isabel', 'Celone', 50723203, '2915763612', 'cris_2683@hotmail.com', '2010-11-30', '2000-01-01', 'Osdip', '03658704481', '3sq obligatorio', 1, '2019-12-04 17:43:29', '2019-12-05 18:34:56', 0),
(326, 'Gonzalo Hernan', 'Celone', 52932821, '2915763612', 'Cris-2683@hot email.com', '2012-12-28', '2000-01-01', 'Osdipp', '03658705364', '3sq obligatorio', 1, '2019-12-04 17:47:49', '2019-12-05 18:35:01', 0),
(335, 'Martina', 'Rodríguez Tiette', 55322540, '291155090848', 'nadiatiette@gmail.com', '2016-01-26', '2000-01-01', 'Osde', '62345662003', '210', 1, '2019-12-04 20:34:40', '2019-12-05 18:35:42', 0),
(336, 'Valentino', 'Gerling', 57463059, '2914048390', 'Flormanquehual@gmail.com', '2018-12-28', '2000-01-01', '', '', '', 1, '2019-12-04 21:53:18', '2019-12-06 18:53:05', 0),
(337, 'Martín', 'Sevillano', 51497146, '2915272004', 'correak952@gmail.com', '2011-11-23', '2000-01-01', 'Particular', '', '', 1, '2019-12-04 22:27:05', '2019-12-11 03:49:54', 1),
(338, 'Sofia', 'Gonzalez', 48165197, '2914250559', 'aguaiaco@gmail.com', '2007-07-15', '2000-01-01', 'Accord Salud', '02631330034', 'Dorado', 1, '2019-12-04 22:32:37', '2019-12-05 18:35:46', 0),
(339, 'Theo', 'Hermosa', 55196832, '2914480990', 'celesteosorio89@gmail.com', '2015-11-06', '2000-01-01', 'Osiad', '68732/02-1', '', 1, '2019-12-05 01:22:54', '2019-12-05 18:35:38', 0),
(341, 'Candela', 'Cacciatori', 48883153, '2914294524', 'gabellad@hotmail.com', '2008-09-12', '2000-01-01', 'UNS', '1439501', 'A', 1, '2019-12-05 02:29:29', '2019-12-05 18:35:33', 0),
(342, 'Felipe', 'Cacciatori', 48883154, '2914294524', 'gabellad@hotmail.com', '2008-09-12', '2000-01-01', 'UNS', '1439502', 'A', 1, '2019-12-05 02:31:12', '2019-12-05 18:35:25', 0),
(343, 'Pedro', 'Lopez', 57541204, '2915043093', 'mgyadasilva@hotmail.com', '2019-01-15', '2000-01-01', 'Swiss medical', '8000060085406041012', 'Sb04', 1, '2019-12-05 11:08:40', '2019-12-05 18:35:21', 0),
(344, 'Juana', 'Leoz Bernat', 57472897, '2914185138', 'magdatango@hotmail.com', '2018-12-27', '2000-01-01', 'OSDE', '', '210', 1, '2019-12-05 14:58:56', '2019-12-05 18:35:29', 0),
(345, 'LORENZO', 'MADER BUEZAS', 56192360, '2915264083', 'mmbuezas@gmail.com', '2017-05-22', '2000-01-01', 'ACA SALUD', '13862541', 'SUPERIOR', 1, '2019-12-05 18:28:55', '2019-12-11 04:06:17', 1),
(346, 'SIMON', 'FUENTES BUEZAS', 50770495, '2915264083', 'mmbuezas@gmail.com', '2011-02-24', '2000-01-01', 'OSDE', '693621', '210', 1, '2019-12-05 18:36:50', '2019-12-05 18:41:56', 0),
(347, 'SEGUNDO', 'FUENTES BUEZAS', 47562498, '02915264083', 'mmbuezas@gmail.com', '2006-11-13', '2000-01-01', 'OSDE', '693621', '210', 1, '2019-12-05 18:37:49', '2019-12-05 18:42:00', 0),
(348, 'ALEXANDER', 'ZARATE', 57977297, '291154261037', 'mari.ro.ubeda@hotmail.com', '2019-11-08', '2000-01-01', 'hogares de belen', '', '', 1, '2019-12-05 18:41:48', '2019-12-05 18:41:48', 0),
(349, 'Benjamin', 'Leiva Beiteleztn', 56269031, '2914425777', 'Beiteleztnjohanna@gmail.com', '2017-09-29', '2000-01-01', '', '', '', 1, '2019-12-05 20:53:37', '2019-12-06 18:53:20', 0),
(350, 'ALEGRA', 'EISELE', 57765164, '2915778935', 'cintiabarrera01@gmail.com', '2019-07-03', '2000-01-01', 'PREVENCION SALUD', '9141904017', 'A2', 1, '2019-12-06 14:58:20', '2019-12-06 18:53:30', 0),
(351, 'LUCIO SANTIAGO', 'EISELE', 52726583, '2915778935', 'cintiabarrera01@gmail.com', '2013-02-18', '2000-01-01', 'PREVENCION SALUD', '9141903014', 'A2', 1, '2019-12-06 15:01:01', '2019-12-06 18:53:36', 0),
(352, 'genaro', 'ferreyra krenz', 56036561, '154328900', 'melikrenz@gmail.com', '2019-05-03', '2000-01-01', 'uta', '', '', 1, '2019-12-06 21:48:12', '2019-12-06 21:48:25', 0),
(353, 'genaro', 'ferreyra krenz', 56036651, '154328900', 'melikrenz@gmail.com', '2019-05-03', '2000-01-01', '', '', '', 1, '2019-12-06 21:49:35', '2019-12-06 21:49:35', 0),
(354, 'ramiro', 'ferreyra krenz', 48500840, '154328900', 'melikrenz@gmail.com', '2008-02-14', '2000-01-01', 'uta', '', '', 2, '2019-12-06 22:28:44', '2019-12-06 22:28:44', 0),
(355, 'Jeremias', 'Cappa', 55336037, '2916452344', 'Florencia_aravena198@hotmail.com', '2016-03-23', '2000-01-01', 'Ospatca', '471992', '', 2, '2019-12-06 22:42:41', '2019-12-06 22:42:41', 0),
(356, 'Benjamin Leonel', 'Godot', 48581441, '2914259819', 'cabrera_virgi@hotmail.com', '2008-05-11', '2000-01-01', 'Iosfa', '066507-5/03', '', 2, '2019-12-06 23:14:41', '2019-12-06 23:14:41', 0),
(357, 'Valentin Nicolas', 'Godoy', 50551459, '2914259819', 'cabrera_virgi@hotmail.com', '2010-08-28', '2000-01-01', 'Iosfa', '066507-5/02', '', 2, '2019-12-06 23:17:49', '2019-12-06 23:17:49', 0),
(358, 'Dylan Maximiliano', 'Godoy', 51458185, '2914259819', 'cabrera_virgi@hotmail.com', '2011-11-09', '2000-01-01', 'Iosfa', '066507-5/04', '', 2, '2019-12-06 23:19:23', '2019-12-06 23:19:23', 0),
(359, 'Ambar Eunice', 'caceres', 57838546, '1163246000', 'gcacer', '2019-09-18', '2000-01-01', 'iosfa', 'f3036865', '', 1, '2019-12-09 01:14:48', '2019-12-09 09:17:44', 0),
(360, 'Lucas', 'Lucarelli', 12, '1', 'l_lucarelli@hotmail.com', '1000-01-01', '2000-01-01', '', '', '', 1, '2019-12-09 06:06:07', '2019-12-09 06:06:07', 0),
(361, 'Juan', 'Ochenta', 86, '1', '', '2020-10-10', '2000-01-01', '', '', '', 2, '2019-12-11 06:34:30', '2019-12-11 06:34:30', 1),
(362, 'NATHAN', 'SAAVEDRA', 56628047, '2916424235', 'carolinazanin83@gmail.com', '2017-10-06', '2000-01-01', 'No', '', '', 1, '2019-12-11 07:11:48', '2019-12-11 07:11:48', NULL),
(363, 'Felicitas', 'Montero', 56352440, '0291155340483', 'colladomariavictoria@gmail.com', '2017-08-22', '2000-01-01', 'Federada Salud', '12206203', '2000', 1, '2019-12-11 07:14:15', '2019-12-11 07:14:15', NULL),
(364, 'Juan', 'Treintaytres', 33, '2915080081', 'juan33@test.com', '2019-10-10', '2000-01-01', '', '', '', 1, '2019-12-11 19:39:09', '2019-12-11 19:39:36', NULL),
(365, 'Juan', 'Test', 34741600, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2019-12-11 19:52:38', '2019-12-11 19:52:38', NULL),
(366, 'Juan', 'Teest', 34741556, '', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2019-12-11 20:03:42', '2019-12-18 12:59:45', 1),
(367, 'Juan', 'TEst 2', 34741557, '2915080081', 'juantest2@test.com', '2019-10-10', '2000-01-01', 'Swiss Medical', '1', 'f', 1, '2019-12-11 20:07:20', '2019-12-11 20:07:45', NULL),
(368, 'Nuevo Test', 'Tets', 34741601, '121212', 'nuevotest@test.com', '2019-10-10', '2000-01-01', 'Swis Medical', '', '', 1, '2019-12-11 20:09:18', '2019-12-11 20:13:52', NULL),
(369, 'Martin', 'Gonzalez', 111111, '111', '', '1990-04-13', '2000-01-01', '', '', '', 1, '2019-12-15 23:28:51', '2019-12-15 23:28:51', 1),
(370, 'Sofia', 'Banegas', 57764801, '', '', '2019-07-19', '2000-01-01', '', '', '', 1, '2019-12-18 16:23:52', '2019-12-18 16:23:52', NULL),
(371, 'Juan', 'Juan test', 98, '1', '', '2010-10-10', '2000-01-01', '', '', '', 2, '2019-12-20 23:21:53', '2019-12-20 23:21:53', 1),
(372, 'Juan', 'Test 99', 999, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-04 15:05:17', '2020-01-04 15:05:17', 1),
(373, 'Juan', 'Test', 998, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-04 15:06:06', '2020-01-04 15:06:06', 1),
(374, 'Juan', 'Test', 997, '1', '', '2019-10-10', '2000-01-01', '', '', '', 2, '2020-01-04 18:12:11', '2020-01-04 18:12:11', 1),
(375, 'Juan', 'Test', 996, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-04 15:12:46', '2020-01-04 15:12:46', 1),
(376, 'Juan', 'Test', 995, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-05 16:11:01', '2020-01-05 16:11:01', 1),
(377, 'Juan', 'Test', 994, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-05 16:15:16', '2020-01-05 16:15:16', 1),
(378, 'Juan', 'Test', 993, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-05 21:48:21', '2020-01-05 21:49:07', 1),
(379, 'Juan', 'Test', 9009, '1', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-16 06:56:52', '2020-01-16 06:56:52', NULL),
(380, 'Paciente', '900', 900, '90909090', '', '2019-10-10', '2000-01-01', '', '', '', 1, '2020-01-18 03:11:22', '2020-01-18 03:11:22', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_secretarias`
--

CREATE TABLE `paciente_secretarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paciente` int(10) UNSIGNED NOT NULL,
  `consultorio` int(10) UNSIGNED NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `paciente_secretarias`
--

INSERT INTO `paciente_secretarias` (`id`, `paciente`, `consultorio`, `activo`, `created_at`, `updated_at`) VALUES
(3210, 95, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3211, 137, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3212, 138, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3213, 139, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3214, 140, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3215, 141, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3216, 142, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3217, 143, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3218, 95, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3219, 128, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3220, 131, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3221, 94, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3222, 146, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3223, 145, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3224, 156, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3225, 162, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3226, 189, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3227, 190, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3228, 197, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3229, 198, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3230, 166, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3231, 165, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3232, 164, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3233, 101, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3234, 102, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3235, 139, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3236, 137, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3237, 97, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3238, 206, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3239, 130, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3240, 203, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3241, 217, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3242, 236, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3243, 237, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3244, 238, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3245, 229, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3246, 243, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3247, 222, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3248, 213, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3249, 257, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3250, 94, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3251, 263, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3252, 104, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3253, 254, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3254, 253, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3255, 259, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3256, 258, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3257, 269, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3258, 271, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3259, 262, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3260, 210, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3261, 298, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3262, 300, 1, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3263, 299, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3264, 160, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3265, 250, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3266, 324, 2, 1, '2020-01-11 09:00:40', '2020-01-11 09:00:40'),
(3267, 319, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3268, 322, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3269, 314, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3270, 323, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3271, 321, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3272, 320, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3273, 341, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3274, 342, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3275, 339, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3276, 350, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3277, 336, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3278, 353, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3279, 306, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3280, 349, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3281, 84, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3282, 120, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3283, 337, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3284, 345, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3285, 348, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3286, 362, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3287, 363, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3288, 359, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3289, 365, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3290, 366, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3291, 367, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3292, 368, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3293, 369, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3294, 366, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3295, 370, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3296, 101, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3297, 376, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3298, 375, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3299, 377, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3300, 375, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3301, 374, 1, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3302, 378, 2, 1, '2020-01-11 09:00:41', '2020-01-11 09:00:41'),
(3303, 379, 1, 1, '2020-01-16 06:56:52', '2020-01-16 06:56:52'),
(3304, 380, 1, 1, '2020-01-18 03:11:22', '2020-01-18 03:11:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('mfge23@hotmail.com', '$2y$10$xxI1boLexw80vT58jIDq0en4LxHZZkmPOvP8Sa5wg.OxIbfiXfDTe', '2019-12-11 19:57:36'),
('banegasrodrigo89@gmail.com', '$2y$10$PzrMohPCDvzYU7Wspy/nyeY1YPGBlnkLrowxmnUFM9Xx//ctlbw1G', '2019-12-13 05:12:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secretarias`
--

CREATE TABLE `secretarias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `secretarias`
--

INSERT INTO `secretarias` (`id`, `nombre`, `apellido`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Patricia', 'Stern', 7, '2019-10-16 16:32:27', '2019-10-16 16:32:27'),
(2, 'Paula', 'Holzmann', 8, '2019-10-16 16:33:35', '2019-10-16 16:33:35'),
(3, 'Secretaria1', 'Secretaria1', 12, '2019-10-26 15:51:04', '2019-10-26 15:51:04'),
(4, 'Test', 'Test', 16, '2019-12-12 22:20:29', '2019-12-12 22:20:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secretaria_consultorios`
--

CREATE TABLE `secretaria_consultorios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `secretaria_id` int(10) UNSIGNED NOT NULL,
  `consultorio_id` int(10) UNSIGNED NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `secretaria_consultorios`
--

INSERT INTO `secretaria_consultorios` (`id`, `secretaria_id`, `consultorio_id`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2019-10-16 16:32:35', '2019-10-16 16:32:35'),
(2, 2, 1, 1, '2019-10-16 16:33:42', '2019-10-16 16:33:42'),
(3, 3, 2, 1, '2019-10-26 15:51:12', '2019-10-26 15:51:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`id`, `tipo`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'admin', 1, NULL, NULL),
(2, 'medico', 1, NULL, NULL),
(3, 'secretaria', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turno_registrados`
--

CREATE TABLE `turno_registrados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paciente` int(10) UNSIGNED NOT NULL,
  `medico` int(10) UNSIGNED NOT NULL,
  `consultorio` int(10) UNSIGNED NOT NULL,
  `dia` int(11) NOT NULL,
  `horario` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechaTurno` date NOT NULL,
  `asistio` int(11) NOT NULL,
  `sobreturno` int(11) NOT NULL,
  `primerControl` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `caja` float DEFAULT NULL,
  `comentario` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turno_registrados`
--

INSERT INTO `turno_registrados` (`id`, `paciente`, `medico`, `consultorio`, `dia`, `horario`, `fechaTurno`, `asistio`, `sobreturno`, `primerControl`, `activo`, `created_at`, `updated_at`, `caja`, `comentario`) VALUES
(186, 95, 3, 2, 2, '17:30', '2019-12-03', 0, 0, 'NO', 1, '2019-11-07 22:11:03', '2019-11-07 22:11:03', 0, ''),
(356, 137, 2, 2, 5, '15:00', '2019-12-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(357, 138, 2, 2, 5, '15:30', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(358, 139, 2, 2, 5, '16:00', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(359, 140, 2, 2, 5, '16:30', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(360, 141, 2, 2, 5, '17:00', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(361, 142, 2, 2, 5, '17:30', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(362, 143, 2, 2, 5, '18:00', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(363, 95, 2, 2, 5, '18:30', '2019-11-15', 0, 0, 'NO', 1, '2019-11-07 22:12:46', '2019-11-07 22:12:46', 0, ''),
(474, 95, 4, 2, 4, '16:00', '2019-12-05', 0, 0, 'NO', 1, '2019-11-07 22:24:54', '2019-11-07 22:24:54', 0, ''),
(580, 95, 5, 2, 5, '16:30', '2019-12-06', 0, 0, 'NO', 1, '2019-11-07 22:38:32', '2019-11-07 22:38:32', 0, ''),
(622, 95, 1, 1, 3, '18:00', '2019-11-13', 0, 0, 'NO', 1, '2019-11-12 01:22:09', '2019-11-12 01:22:09', 0, ''),
(643, 95, 2, 2, 3, '12:00', '2019-12-04', 0, 0, 'NO', 1, '2019-11-14 05:37:48', '2019-11-14 05:37:48', 0, ''),
(847, 128, 2, 2, 5, '18:30', '2020-01-03', 0, 0, 'NO', 1, '2019-11-14 23:51:34', '2019-11-14 23:51:34', 0, ''),
(848, 131, 2, 2, 5, '19:00', '2020-01-03', 0, 0, 'NO', 1, '2019-11-14 23:54:17', '2019-11-14 23:54:17', 0, ''),
(849, 94, 1, 1, 3, '19:00', '2019-11-20', 0, 0, 'NO', 1, '2019-11-17 00:29:09', '2019-11-17 00:29:09', 0, ''),
(911, 146, 2, 2, 5, '18:00', '0000-00-00', 0, 0, 'NO', 0, '2019-11-20 12:10:15', '2019-11-20 12:10:28', 0, ''),
(912, 146, 2, 2, 5, '17:30', '2020-01-03', 0, 0, 'NO', 1, '2019-11-20 12:10:29', '2019-11-20 12:10:29', 0, ''),
(913, 145, 2, 2, 5, '15:00', '2010-01-20', 0, 0, 'NO', 0, '2019-11-20 13:45:49', '2019-11-20 13:45:57', 0, ''),
(914, 145, 2, 2, 5, '15:30', '2020-01-10', 0, 0, 'NO', 1, '2019-11-20 13:45:57', '2019-11-20 13:45:57', 0, ''),
(915, 156, 3, 2, 2, '19:00', '2020-01-07', 0, 0, 'NO', 1, '2019-11-21 01:47:41', '2019-11-21 01:47:41', 0, ''),
(916, 162, 2, 2, 3, '11:30', '2020-01-15', 0, 0, 'NO', 1, '2019-11-21 10:12:40', '2019-11-21 10:12:40', 0, ''),
(917, 189, 3, 2, 2, '15:00', '2020-01-14', 0, 0, 'NO', 1, '2019-11-21 19:07:35', '2019-11-21 19:07:35', 0, ''),
(918, 190, 3, 2, 2, '15:30', '2020-01-14', 0, 0, 'NO', 1, '2019-11-21 19:08:14', '2019-11-21 19:08:14', 0, ''),
(919, 197, 6, 2, 2, '16:30', '0000-00-00', 0, 0, 'NO', 0, '2019-11-21 23:15:37', '2019-11-21 23:15:55', 0, ''),
(920, 197, 6, 2, 2, '14:00', '2020-01-28', 0, 0, 'NO', 1, '2019-11-21 23:21:30', '2019-11-21 23:21:30', 0, ''),
(921, 198, 6, 2, 2, '14:30', '2020-01-28', 0, 0, 'NO', 1, '2019-11-21 23:24:50', '2019-11-21 23:24:50', 0, ''),
(953, 166, 6, 2, 3, '11:00', '2020-01-08', 0, 0, 'NO', 1, '2019-11-22 12:47:47', '2019-11-22 12:47:47', 0, ''),
(954, 165, 6, 2, 3, '11:30', '2020-01-08', 0, 0, 'NO', 1, '2019-11-22 12:48:27', '2019-11-22 12:48:27', 0, ''),
(955, 164, 6, 2, 3, '12:00', '2020-01-08', 0, 0, 'NO', 1, '2019-11-22 12:48:57', '2019-11-22 12:48:57', 0, ''),
(956, 94, 1, 1, 3, '19:00', '2019-11-27', 0, 0, 'NO', 1, '2019-11-22 14:46:26', '2019-11-22 14:46:26', 0, ''),
(957, 101, 1, 1, 3, '19:30', '2019-11-27', 0, 0, 'NO', 1, '2019-11-22 14:46:54', '2019-11-22 14:46:54', 0, ''),
(958, 102, 1, 1, 3, '18:00', '2019-11-27', 1, 0, 'NO', 1, '2019-11-22 14:47:16', '2019-11-28 11:28:47', 0, 'No cobrar'),
(959, 139, 1, 1, 3, '19:31', '2019-11-27', 0, 1, 'NO', 1, '2019-11-22 14:50:07', '2019-11-22 14:50:07', 0, ''),
(960, 137, 1, 1, 3, '18:00', '2019-12-04', 0, 0, 'NO', 1, '2019-11-22 14:51:36', '2019-11-22 14:51:36', 0, ''),
(961, 94, 1, 1, 3, '18:30', '2019-12-04', 0, 0, 'NO', 1, '2019-11-22 14:51:52', '2019-11-22 14:51:52', 0, ''),
(962, 97, 4, 2, 1, '15:00', '2020-02-03', 0, 0, 'NO', 1, '2019-11-22 17:21:53', '2019-11-22 17:21:53', 0, ''),
(963, 206, 1, 1, 3, '19:00', '0000-00-00', 0, 0, 'SI', 0, '2019-11-25 16:39:44', '2019-11-25 16:41:03', 0, ''),
(964, 206, 1, 1, 3, '19:30', '0000-00-00', 0, 0, 'SI', 0, '2019-11-25 16:39:44', '2019-11-25 16:41:06', 0, ''),
(965, 206, 1, 1, 3, '18:30', '0000-00-00', 0, 0, 'NO', 0, '2019-11-25 16:43:39', '2019-11-25 16:45:40', 0, ''),
(971, 130, 2, 2, 3, '09:30', '0000-00-00', 0, 0, 'NO', 0, '2019-11-25 21:06:19', '2019-11-25 21:06:25', 0, ''),
(972, 130, 2, 2, 3, '09:30', '2020-01-15', 0, 0, 'NO', 1, '2019-11-25 21:06:29', '2020-01-04 22:49:57', 0, 'hola'),
(978, 203, 2, 2, 5, '17:00', '2020-01-03', 0, 0, 'NO', 1, '2019-11-26 19:59:27', '2019-11-26 19:59:27', 0, ''),
(979, 217, 3, 2, 4, '18:30', '2020-01-09', 0, 0, 'NO', 1, '2019-11-27 21:50:52', '2019-11-27 21:50:52', 0, ''),
(980, 236, 2, 2, 3, '15:00', '2020-01-08', 0, 0, 'NO', 1, '2019-11-28 20:10:47', '2019-11-28 20:10:47', 0, ''),
(981, 237, 2, 2, 5, '17:30', '2020-01-17', 0, 0, 'NO', 1, '2019-11-28 20:23:13', '2019-11-28 20:23:13', 0, ''),
(982, 238, 2, 2, 3, '10:00', '2020-01-08', 0, 0, 'NO', 1, '2019-11-28 20:27:06', '2019-11-28 20:27:06', 0, ''),
(1027, 229, 2, 2, 5, '18:00', '2020-01-03', 0, 0, 'NO', 1, '2019-11-28 21:25:11', '2019-11-28 21:25:11', 0, ''),
(1028, 243, 6, 2, 3, '11:00', '2020-02-05', 0, 0, 'NO', 1, '2019-11-28 22:20:58', '2019-11-28 22:20:58', 0, ''),
(1029, 222, 3, 2, 2, '17:00', '2020-01-07', 0, 0, 'NO', 1, '2019-11-29 01:14:51', '2019-11-29 01:14:51', 0, ''),
(1030, 213, 6, 2, 2, '14:00', '2020-01-07', 0, 0, 'NO', 1, '2019-12-01 19:22:28', '2019-12-01 19:22:28', 0, ''),
(1031, 257, 3, 2, 4, '18:30', '0000-00-00', 0, 0, 'NO', 0, '2019-12-03 09:56:57', '2019-12-03 09:57:01', 0, ''),
(1032, 257, 3, 2, 4, '18:30', '2020-01-02', 0, 0, 'NO', 1, '2019-12-03 09:57:26', '2019-12-03 09:57:26', 0, ''),
(1033, 94, 2, 2, 5, '15:00', '0000-00-00', 0, 0, 'NO', 0, '2019-12-03 19:08:46', '2019-12-03 19:08:49', 0, ''),
(1034, 263, 4, 2, 4, '16:00', '2020-01-16', 0, 0, 'NO', 1, '2019-12-03 19:23:48', '2019-12-03 19:23:48', 0, ''),
(1035, 104, 3, 2, 4, '16:00', '0000-00-00', 0, 0, 'NO', 0, '2019-12-03 19:25:13', '2019-12-04 13:02:51', 0, ''),
(1037, 254, 1, 1, 3, '19:00', '2019-12-11', 0, 0, 'SI', 1, '2019-12-03 19:26:04', '2019-12-03 19:26:04', 0, ''),
(1038, 254, 1, 1, 3, '18:00', '2019-12-11', 0, 0, 'SI', 1, '2019-12-03 19:26:08', '2019-12-03 19:26:08', 0, ''),
(1039, 254, 1, 1, 3, '18:30', '2019-12-11', 0, 0, 'SI', 1, '2019-12-03 19:26:08', '2019-12-03 19:26:08', 0, ''),
(1040, 253, 2, 2, 5, '15:00', '2020-01-03', 0, 0, 'NO', 1, '2019-12-03 19:32:02', '2019-12-03 19:32:02', 0, ''),
(1041, 259, 4, 2, 1, '15:00', '2020-01-20', 0, 0, 'NO', 1, '2019-12-03 20:14:57', '2019-12-03 20:14:57', 0, ''),
(1042, 258, 4, 2, 1, '15:30', '2020-01-20', 0, 0, 'NO', 1, '2019-12-03 20:16:32', '2019-12-03 20:16:32', 0, ''),
(1043, 269, 3, 2, 2, '15:00', '2020-03-31', 0, 0, 'NO', 1, '2019-12-03 20:29:06', '2019-12-03 20:29:06', 0, ''),
(1044, 271, 3, 2, 4, '18:00', '2020-01-02', 0, 0, 'NO', 1, '2019-12-03 20:52:10', '2019-12-03 20:52:10', 0, ''),
(1045, 262, 4, 2, 4, '20:00', '2020-01-02', 0, 0, 'NO', 1, '2019-12-03 21:05:10', '2019-12-03 21:05:10', 0, ''),
(1046, 210, 3, 2, 4, '18:30', '2020-01-16', 0, 0, 'NO', 1, '2019-12-03 21:18:29', '2019-12-03 21:18:29', 0, ''),
(1047, 298, 1, 1, 3, '19:00', '2019-12-04', 0, 0, 'NO', 1, '2019-12-03 22:56:00', '2019-12-03 22:56:00', 0, ''),
(1048, 94, 1, 1, 3, '18:15', '2019-12-04', 0, 1, 'NO', 1, '2019-12-03 22:57:06', '2019-12-03 22:57:06', 0, ''),
(1049, 300, 1, 1, 3, '19:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-03 23:01:33', '2019-12-03 23:01:33', 0, ''),
(1050, 94, 1, 1, 3, '18:30', '0000-00-00', 0, 0, 'NO', 0, '2019-12-03 23:02:35', '2019-12-03 23:02:43', 0, ''),
(1051, 94, 1, 1, 3, '18:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-03 23:02:50', '2019-12-03 23:02:50', 0, ''),
(1052, 300, 1, 1, 3, '19:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-03 23:03:19', '2019-12-03 23:03:19', 0, ''),
(1053, 299, 5, 2, 5, '15:00', '2019-12-13', 0, 0, 'SI', 1, '2019-12-03 23:03:29', '2019-12-03 23:03:29', 0, ''),
(1054, 299, 5, 2, 5, '15:30', '2019-12-13', 0, 0, 'SI', 1, '2019-12-03 23:03:29', '2019-12-03 23:03:29', 0, ''),
(1055, 104, 3, 2, 2, '16:00', '2020-01-07', 0, 0, 'NO', 1, '2019-12-04 13:06:09', '2019-12-04 13:06:09', 0, ''),
(1056, 104, 3, 2, 2, '15:00', '0000-00-00', 0, 0, 'NO', 0, '2019-12-04 13:14:52', '2019-12-04 13:15:31', 0, ''),
(1057, 104, 3, 2, 4, '15:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-04 13:15:50', '2019-12-04 13:15:50', 0, ''),
(1058, 160, 3, 2, 4, '15:00', '0000-00-00', 0, 0, 'NO', 0, '2019-12-04 14:31:27', '2019-12-04 14:31:31', 0, ''),
(1060, 250, 4, 2, 1, '18:00', '2020-01-06', 0, 0, 'NO', 1, '2019-12-04 16:16:46', '2019-12-04 16:16:46', 0, ''),
(1061, 324, 2, 2, 3, '14:00', '2020-02-05', 0, 0, 'NO', 1, '2019-12-04 17:18:54', '2019-12-04 17:18:54', 0, ''),
(1062, 319, 3, 2, 2, '16:30', '2020-01-07', 0, 0, 'NO', 1, '2019-12-04 18:40:17', '2019-12-04 18:40:17', 0, ''),
(1063, 322, 3, 2, 4, '17:30', '2020-01-02', 0, 0, 'NO', 1, '2019-12-04 20:32:33', '2019-12-04 20:32:33', 0, ''),
(1064, 314, 3, 2, 4, '17:00', '2020-01-02', 0, 0, 'NO', 1, '2019-12-04 21:15:57', '2019-12-04 21:15:57', 0, ''),
(1065, 323, 2, 2, 5, '15:00', '2020-02-07', 0, 0, 'SI', 1, '2019-12-04 22:13:47', '2019-12-04 22:13:47', 0, ''),
(1066, 323, 2, 2, 5, '15:30', '2020-02-07', 0, 0, 'SI', 1, '2019-12-04 22:13:47', '2019-12-04 22:13:47', 0, ''),
(1067, 321, 3, 2, 4, '16:00', '0000-00-00', 0, 0, 'NO', 0, '2019-12-05 02:25:28', '2019-12-05 02:25:36', 0, ''),
(1068, 321, 3, 2, 4, '16:00', '2020-01-02', 0, 0, 'NO', 1, '2019-12-05 02:26:09', '2019-12-05 02:26:09', 0, ''),
(1069, 320, 3, 2, 4, '16:30', '2020-01-02', 0, 0, 'NO', 1, '2019-12-05 02:27:35', '2019-12-05 02:27:35', 0, ''),
(1072, 341, 3, 2, 2, '15:00', '2020-01-07', 0, 0, 'NO', 1, '2019-12-05 18:54:58', '2019-12-05 18:54:58', 0, ''),
(1073, 342, 3, 2, 2, '15:30', '2020-01-07', 0, 0, 'NO', 1, '2019-12-05 18:56:34', '2019-12-05 18:56:34', 0, ''),
(1074, 339, 3, 2, 2, '17:30', '2020-01-07', 0, 0, 'NO', 1, '2019-12-06 15:54:54', '2019-12-06 15:54:54', 0, ''),
(1075, 350, 3, 2, 4, '17:30', '2020-01-09', 0, 0, 'NO', 1, '2019-12-06 18:57:37', '2019-12-06 18:57:37', 0, ''),
(1076, 350, 3, 2, 2, '18:00', '2020-01-07', 0, 0, 'NO', 1, '2019-12-06 19:02:07', '2019-12-06 19:02:07', 0, ''),
(1077, 336, 2, 2, 5, '16:30', '2020-01-03', 0, 0, 'NO', 1, '2019-12-06 19:28:48', '2019-12-06 19:28:48', 0, ''),
(1078, 353, 5, 2, 5, '15:00', '2020-01-10', 0, 0, 'NO', 1, '2019-12-06 21:49:51', '2019-12-06 21:49:51', 0, ''),
(1079, 306, 3, 2, 2, '16:00', '2020-01-14', 0, 0, 'NO', 1, '2019-12-06 23:10:55', '2019-12-06 23:10:55', 0, ''),
(1080, 349, 2, 2, 3, '09:30', '0000-00-00', 0, 0, 'NO', 0, '2019-12-06 23:45:41', '2019-12-06 23:45:49', 0, ''),
(1081, 349, 2, 2, 3, '09:30', '2020-01-08', 0, 0, 'NO', 1, '2019-12-06 23:46:13', '2019-12-06 23:46:13', 0, ''),
(1084, 84, 2, 2, 3, '09:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1085, 84, 2, 2, 3, '10:00', '2019-12-11', 1, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-12 01:46:25', 0, ''),
(1086, 84, 2, 2, 3, '10:30', '2019-12-11', 2, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-12 01:46:29', 0, ''),
(1087, 84, 2, 2, 3, '11:00', '2019-12-11', 2, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-12 01:46:30', 0, ''),
(1088, 84, 2, 2, 3, '11:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1089, 84, 2, 2, 3, '12:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1090, 84, 2, 2, 3, '12:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1091, 84, 2, 2, 3, '13:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1092, 84, 2, 2, 3, '13:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1093, 84, 2, 2, 3, '14:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1094, 84, 2, 2, 3, '14:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1095, 84, 2, 2, 3, '15:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1096, 84, 2, 2, 3, '15:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:42:25', '2019-12-09 08:42:25', 0, ''),
(1097, 84, 2, 2, 5, '15:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1098, 84, 2, 2, 5, '15:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1099, 84, 2, 2, 5, '16:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1100, 84, 2, 2, 5, '16:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1101, 84, 2, 2, 5, '17:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1102, 84, 2, 2, 5, '17:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1103, 84, 2, 2, 5, '18:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1104, 84, 2, 2, 5, '18:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1105, 84, 2, 2, 5, '19:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:45:10', '2019-12-09 08:45:10', 0, ''),
(1106, 84, 2, 2, 3, '09:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-12 22:36:50', 0, 'hola'),
(1107, 84, 2, 2, 3, '10:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-12 22:37:38', 100, 'No cobrar'),
(1108, 84, 2, 2, 3, '10:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-12 22:37:44', 30, ''),
(1109, 84, 2, 2, 3, '11:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1110, 84, 2, 2, 3, '11:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1111, 84, 2, 2, 3, '12:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1112, 84, 2, 2, 3, '12:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1113, 84, 2, 2, 3, '13:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1114, 84, 2, 2, 3, '13:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1115, 84, 2, 2, 3, '14:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1116, 84, 2, 2, 3, '14:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1117, 84, 2, 2, 3, '15:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1118, 84, 2, 2, 3, '15:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:45:34', '2019-12-09 08:45:34', 0, ''),
(1119, 84, 2, 2, 5, '15:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1120, 84, 2, 2, 5, '15:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1121, 84, 2, 2, 5, '16:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1122, 84, 2, 2, 5, '16:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1123, 84, 2, 2, 5, '17:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1124, 84, 2, 2, 5, '17:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1125, 84, 2, 2, 5, '18:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1126, 84, 2, 2, 5, '18:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1127, 84, 2, 2, 5, '19:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:45:36', '2019-12-09 08:45:36', 0, ''),
(1128, 84, 2, 2, 3, '09:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1129, 84, 2, 2, 3, '10:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1130, 84, 2, 2, 3, '10:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1131, 84, 2, 2, 3, '11:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1132, 84, 2, 2, 3, '11:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1133, 84, 2, 2, 3, '12:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1134, 84, 2, 2, 3, '12:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1135, 84, 2, 2, 3, '13:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1136, 84, 2, 2, 3, '13:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1137, 84, 2, 2, 3, '14:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1138, 84, 2, 2, 3, '14:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1139, 84, 2, 2, 3, '15:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1140, 84, 2, 2, 3, '15:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:45:39', '2019-12-09 08:45:39', 0, ''),
(1141, 84, 2, 2, 5, '15:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1142, 84, 2, 2, 5, '15:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1143, 84, 2, 2, 5, '16:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1144, 84, 2, 2, 5, '16:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1145, 84, 2, 2, 5, '17:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1146, 84, 2, 2, 5, '17:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1147, 84, 2, 2, 5, '18:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1148, 84, 2, 2, 5, '18:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1149, 84, 2, 2, 5, '19:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:45:45', '2019-12-09 08:45:45', 0, ''),
(1150, 84, 2, 2, 3, '09:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1151, 84, 2, 2, 3, '10:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1152, 84, 2, 2, 3, '10:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1153, 84, 2, 2, 3, '11:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1154, 84, 2, 2, 3, '11:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1155, 84, 2, 2, 3, '12:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1156, 84, 2, 2, 3, '12:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1157, 84, 2, 2, 3, '13:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1158, 84, 2, 2, 3, '13:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1159, 84, 2, 2, 3, '14:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1160, 84, 2, 2, 3, '14:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1161, 84, 2, 2, 3, '15:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1162, 84, 2, 2, 3, '15:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:45:50', '2019-12-09 08:45:50', 0, ''),
(1163, 84, 3, 2, 2, '15:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1164, 84, 3, 2, 2, '15:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1165, 84, 3, 2, 2, '16:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1166, 84, 3, 2, 2, '16:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1167, 84, 3, 2, 2, '17:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1168, 84, 3, 2, 2, '17:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1169, 84, 3, 2, 2, '18:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1170, 84, 3, 2, 2, '18:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1171, 84, 3, 2, 2, '19:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:47:10', '2019-12-09 08:47:10', 0, ''),
(1172, 84, 3, 2, 4, '15:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1173, 84, 3, 2, 4, '15:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1174, 84, 3, 2, 4, '16:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1175, 84, 3, 2, 4, '16:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1176, 84, 3, 2, 4, '17:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1177, 84, 3, 2, 4, '17:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1178, 84, 3, 2, 4, '18:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1179, 84, 3, 2, 4, '18:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1180, 84, 3, 2, 4, '19:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:14', '2019-12-09 08:47:14', 0, ''),
(1181, 84, 3, 2, 2, '15:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1182, 84, 3, 2, 2, '15:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1183, 84, 3, 2, 2, '16:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1184, 84, 3, 2, 2, '16:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1185, 84, 3, 2, 2, '17:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1186, 84, 3, 2, 2, '17:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1187, 84, 3, 2, 2, '18:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1188, 84, 3, 2, 2, '18:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1189, 84, 3, 2, 2, '19:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:47:16', '2019-12-09 08:47:16', 0, ''),
(1190, 84, 3, 2, 4, '15:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1191, 84, 3, 2, 4, '15:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1192, 84, 3, 2, 4, '16:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1193, 84, 3, 2, 4, '16:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1194, 84, 3, 2, 4, '17:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1195, 84, 3, 2, 4, '17:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1196, 84, 3, 2, 4, '18:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1197, 84, 3, 2, 4, '18:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1198, 84, 3, 2, 4, '19:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:47:19', '2019-12-09 08:47:19', 0, ''),
(1199, 84, 3, 2, 2, '15:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1200, 84, 3, 2, 2, '15:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1201, 84, 3, 2, 2, '16:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1202, 84, 3, 2, 2, '16:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1203, 84, 3, 2, 2, '17:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1204, 84, 3, 2, 2, '17:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1205, 84, 3, 2, 2, '18:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1206, 84, 3, 2, 2, '18:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1207, 84, 3, 2, 2, '19:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:47:21', '2019-12-09 08:47:21', 0, ''),
(1208, 84, 3, 2, 4, '15:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1209, 84, 3, 2, 4, '15:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1210, 84, 3, 2, 4, '16:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1211, 84, 3, 2, 4, '16:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1212, 84, 3, 2, 4, '17:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1213, 84, 3, 2, 4, '17:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1214, 84, 3, 2, 4, '18:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1215, 84, 3, 2, 4, '18:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1216, 84, 3, 2, 4, '19:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:47:30', '2019-12-09 08:47:30', 0, ''),
(1217, 84, 3, 2, 2, '15:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1218, 84, 3, 2, 2, '15:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1219, 84, 3, 2, 2, '16:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1220, 84, 3, 2, 2, '16:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1221, 84, 3, 2, 2, '17:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1222, 84, 3, 2, 2, '17:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1223, 84, 3, 2, 2, '18:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1224, 84, 3, 2, 2, '18:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1225, 84, 3, 2, 2, '19:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:47:32', '2019-12-09 08:47:32', 0, ''),
(1227, 84, 4, 2, 1, '15:00', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1228, 84, 4, 2, 1, '15:30', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1229, 84, 4, 2, 1, '16:00', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1230, 84, 4, 2, 1, '16:30', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1231, 84, 4, 2, 1, '17:00', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1232, 84, 4, 2, 1, '17:30', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1233, 84, 4, 2, 1, '18:00', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1234, 84, 4, 2, 1, '18:30', '2019-12-09', 0, 0, 'NO', 1, '2019-12-09 08:47:53', '2019-12-09 08:47:53', 0, ''),
(1235, 84, 4, 2, 4, '15:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1236, 84, 4, 2, 4, '15:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1237, 84, 4, 2, 4, '16:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1238, 84, 4, 2, 4, '16:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1239, 84, 4, 2, 4, '17:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1240, 84, 4, 2, 4, '17:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1241, 84, 4, 2, 4, '18:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1242, 84, 4, 2, 4, '18:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1243, 84, 4, 2, 4, '19:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1244, 84, 4, 2, 4, '19:30', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1245, 84, 4, 2, 4, '20:00', '2019-12-12', 0, 0, 'NO', 1, '2019-12-09 08:47:55', '2019-12-09 08:47:55', 0, ''),
(1246, 84, 4, 2, 1, '15:00', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1247, 84, 4, 2, 1, '15:30', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1248, 84, 4, 2, 1, '16:00', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1249, 84, 4, 2, 1, '16:30', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1250, 84, 4, 2, 1, '17:00', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1251, 84, 4, 2, 1, '17:30', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1252, 84, 4, 2, 1, '18:00', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1253, 84, 4, 2, 1, '18:30', '2019-12-16', 0, 0, 'NO', 1, '2019-12-09 08:47:58', '2019-12-09 08:47:58', 0, ''),
(1254, 84, 4, 2, 4, '15:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1255, 84, 4, 2, 4, '15:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1256, 84, 4, 2, 4, '16:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1257, 84, 4, 2, 4, '16:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1258, 84, 4, 2, 4, '17:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1259, 84, 4, 2, 4, '17:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1260, 84, 4, 2, 4, '18:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1261, 84, 4, 2, 4, '18:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1262, 84, 4, 2, 4, '19:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1263, 84, 4, 2, 4, '19:30', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1264, 84, 4, 2, 4, '20:00', '2019-12-19', 0, 0, 'NO', 1, '2019-12-09 08:48:00', '2019-12-09 08:48:00', 0, ''),
(1265, 84, 4, 2, 1, '15:00', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1266, 84, 4, 2, 1, '15:30', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1267, 84, 4, 2, 1, '16:00', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1268, 84, 4, 2, 1, '16:30', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1269, 84, 4, 2, 1, '17:00', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1270, 84, 4, 2, 1, '17:30', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1271, 84, 4, 2, 1, '18:00', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1272, 84, 4, 2, 1, '18:30', '2019-12-23', 0, 0, 'NO', 1, '2019-12-09 08:48:02', '2019-12-09 08:48:02', 0, ''),
(1273, 84, 4, 2, 4, '15:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1274, 84, 4, 2, 4, '15:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1275, 84, 4, 2, 4, '16:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1276, 84, 4, 2, 4, '16:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1277, 84, 4, 2, 4, '17:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1278, 84, 4, 2, 4, '17:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1279, 84, 4, 2, 4, '18:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1280, 84, 4, 2, 4, '18:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1281, 84, 4, 2, 4, '19:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1282, 84, 4, 2, 4, '19:30', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1283, 84, 4, 2, 4, '20:00', '2019-12-26', 0, 0, 'NO', 1, '2019-12-09 08:48:06', '2019-12-09 08:48:06', 0, ''),
(1284, 84, 4, 2, 1, '15:00', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1285, 84, 4, 2, 1, '15:30', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1286, 84, 4, 2, 1, '16:00', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1287, 84, 4, 2, 1, '16:30', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1288, 84, 4, 2, 1, '17:00', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1289, 84, 4, 2, 1, '17:30', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1290, 84, 4, 2, 1, '18:00', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1291, 84, 4, 2, 1, '18:30', '2019-12-30', 0, 0, 'NO', 1, '2019-12-09 08:48:09', '2019-12-09 08:48:09', 0, ''),
(1292, 84, 5, 2, 5, '15:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1293, 84, 5, 2, 5, '15:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1294, 84, 5, 2, 5, '16:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1295, 84, 5, 2, 5, '16:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1296, 84, 5, 2, 5, '17:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1297, 84, 5, 2, 5, '17:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1298, 84, 5, 2, 5, '18:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1299, 84, 5, 2, 5, '18:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1300, 84, 5, 2, 5, '19:00', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1301, 84, 5, 2, 5, '19:30', '2019-12-20', 0, 0, 'NO', 1, '2019-12-09 08:48:26', '2019-12-09 08:48:26', 0, ''),
(1302, 84, 5, 2, 5, '15:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1303, 84, 5, 2, 5, '15:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1304, 84, 5, 2, 5, '16:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1305, 84, 5, 2, 5, '16:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1306, 84, 5, 2, 5, '17:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1307, 84, 5, 2, 5, '17:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1308, 84, 5, 2, 5, '18:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1309, 84, 5, 2, 5, '18:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1310, 84, 5, 2, 5, '19:00', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1311, 84, 5, 2, 5, '19:30', '2019-12-27', 0, 0, 'NO', 1, '2019-12-09 08:48:29', '2019-12-09 08:48:29', 0, ''),
(1312, 84, 5, 2, 5, '16:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:36', '2019-12-09 08:48:36', 0, ''),
(1313, 84, 5, 2, 5, '16:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:39', '2019-12-09 08:48:39', 0, ''),
(1314, 84, 5, 2, 5, '17:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:41', '2019-12-09 08:48:41', 0, ''),
(1315, 84, 5, 2, 5, '17:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:45', '2019-12-09 08:48:45', 0, ''),
(1316, 84, 5, 2, 5, '18:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:48', '2019-12-09 08:48:48', 0, ''),
(1317, 84, 5, 2, 5, '18:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:51', '2019-12-09 08:48:51', 0, ''),
(1318, 84, 5, 2, 5, '19:00', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:54', '2019-12-09 08:48:54', 0, ''),
(1319, 84, 5, 2, 5, '19:30', '2019-12-13', 0, 0, 'NO', 1, '2019-12-09 08:48:56', '2019-12-09 08:48:56', 0, ''),
(1320, 84, 6, 2, 2, '14:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:49:07', '2019-12-09 08:49:07', 0, ''),
(1321, 84, 6, 2, 2, '14:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:49:07', '2019-12-09 08:49:07', 0, ''),
(1322, 84, 6, 2, 2, '15:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:49:07', '2019-12-09 08:49:07', 0, ''),
(1323, 84, 6, 2, 2, '15:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:49:07', '2019-12-09 08:49:07', 0, ''),
(1324, 84, 6, 2, 2, '16:00', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:49:07', '2019-12-09 08:49:07', 0, ''),
(1325, 84, 6, 2, 2, '16:30', '2019-12-10', 0, 0, 'NO', 1, '2019-12-09 08:49:07', '2019-12-09 08:49:07', 0, ''),
(1326, 84, 6, 2, 3, '10:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1327, 84, 6, 2, 3, '10:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1328, 84, 6, 2, 3, '11:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1329, 84, 6, 2, 3, '11:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1330, 84, 6, 2, 3, '12:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1331, 84, 6, 2, 3, '12:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1332, 84, 6, 2, 3, '13:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1333, 84, 6, 2, 3, '13:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1334, 84, 6, 2, 3, '14:00', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1335, 84, 6, 2, 3, '14:30', '2019-12-11', 0, 0, 'NO', 1, '2019-12-09 08:49:09', '2019-12-09 08:49:09', 0, ''),
(1336, 84, 6, 2, 2, '14:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:49:13', '2019-12-09 08:49:13', 0, ''),
(1337, 84, 6, 2, 2, '14:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:49:13', '2019-12-09 08:49:13', 0, ''),
(1338, 84, 6, 2, 2, '15:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:49:13', '2019-12-09 08:49:13', 0, ''),
(1339, 84, 6, 2, 2, '15:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:49:13', '2019-12-09 08:49:13', 0, ''),
(1340, 84, 6, 2, 2, '16:00', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:49:13', '2019-12-09 08:49:13', 0, ''),
(1341, 84, 6, 2, 2, '16:30', '2019-12-17', 0, 0, 'NO', 1, '2019-12-09 08:49:13', '2019-12-09 08:49:13', 0, ''),
(1342, 84, 6, 2, 3, '10:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1343, 84, 6, 2, 3, '10:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1344, 84, 6, 2, 3, '11:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1345, 84, 6, 2, 3, '11:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1346, 84, 6, 2, 3, '12:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1347, 84, 6, 2, 3, '12:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1348, 84, 6, 2, 3, '13:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1349, 84, 6, 2, 3, '13:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1350, 84, 6, 2, 3, '14:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1351, 84, 6, 2, 3, '14:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-09 08:49:15', '2019-12-09 08:49:15', 0, ''),
(1352, 84, 6, 2, 2, '14:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:49:17', '2019-12-09 08:49:17', 0, ''),
(1353, 84, 6, 2, 2, '14:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:49:17', '2019-12-09 08:49:17', 0, ''),
(1354, 84, 6, 2, 2, '15:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:49:17', '2019-12-09 08:49:17', 0, ''),
(1355, 84, 6, 2, 2, '15:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:49:17', '2019-12-09 08:49:17', 0, ''),
(1356, 84, 6, 2, 2, '16:00', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:49:17', '2019-12-09 08:49:17', 0, ''),
(1357, 84, 6, 2, 2, '16:30', '2019-12-24', 0, 0, 'NO', 1, '2019-12-09 08:49:17', '2019-12-09 08:49:17', 0, ''),
(1358, 84, 6, 2, 3, '10:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1359, 84, 6, 2, 3, '10:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1360, 84, 6, 2, 3, '11:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1361, 84, 6, 2, 3, '11:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1362, 84, 6, 2, 3, '12:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1363, 84, 6, 2, 3, '12:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1364, 84, 6, 2, 3, '13:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1365, 84, 6, 2, 3, '13:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1366, 84, 6, 2, 3, '14:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1367, 84, 6, 2, 3, '14:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-09 08:49:23', '2019-12-09 08:49:23', 0, ''),
(1368, 84, 6, 2, 2, '14:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:49:26', '2019-12-09 08:49:26', 0, ''),
(1369, 84, 6, 2, 2, '14:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:49:26', '2019-12-09 08:49:26', 0, ''),
(1370, 84, 6, 2, 2, '15:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:49:26', '2019-12-09 08:49:26', 0, ''),
(1371, 84, 6, 2, 2, '15:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:49:26', '2019-12-09 08:49:26', 0, ''),
(1372, 84, 6, 2, 2, '16:00', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:49:26', '2019-12-09 08:49:26', 0, ''),
(1373, 84, 6, 2, 2, '16:30', '2019-12-31', 0, 0, 'NO', 1, '2019-12-09 08:49:26', '2019-12-09 08:49:26', 0, ''),
(1374, 84, 6, 2, 3, '10:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1375, 84, 6, 2, 3, '10:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1376, 84, 6, 2, 3, '11:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1377, 84, 6, 2, 3, '11:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1378, 84, 6, 2, 3, '12:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1379, 84, 6, 2, 3, '12:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1380, 84, 6, 2, 3, '13:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1381, 84, 6, 2, 3, '13:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1382, 84, 6, 2, 3, '14:00', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1383, 84, 6, 2, 3, '14:30', '2020-01-01', 0, 0, 'NO', 1, '2019-12-09 08:49:36', '2019-12-09 08:49:36', 0, ''),
(1384, 84, 2, 2, 3, '09:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1385, 84, 2, 2, 3, '10:00', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1386, 84, 2, 2, 3, '10:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1387, 84, 2, 2, 3, '11:00', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1388, 84, 2, 2, 3, '11:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1389, 84, 2, 2, 3, '12:00', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1390, 84, 2, 2, 3, '12:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1391, 84, 2, 2, 3, '13:00', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1392, 84, 2, 2, 3, '13:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1393, 84, 2, 2, 3, '14:00', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1394, 84, 2, 2, 3, '14:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1395, 84, 2, 2, 3, '15:00', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1396, 84, 2, 2, 3, '15:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-09 08:53:51', '2019-12-09 08:53:51', 0, ''),
(1397, 84, 2, 2, 5, '15:00', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1398, 84, 2, 2, 5, '15:30', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1399, 84, 2, 2, 5, '16:00', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1400, 84, 2, 2, 5, '16:30', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1401, 84, 2, 2, 5, '17:00', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1402, 84, 2, 2, 5, '17:30', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1403, 84, 2, 2, 5, '18:00', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1404, 84, 2, 2, 5, '18:30', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1405, 84, 2, 2, 5, '19:00', '2020-01-24', 0, 0, 'NO', 1, '2019-12-09 08:53:53', '2019-12-09 08:53:53', 0, ''),
(1406, 84, 2, 2, 3, '09:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1407, 84, 2, 2, 3, '10:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1408, 84, 2, 2, 3, '10:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1409, 84, 2, 2, 3, '11:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1410, 84, 2, 2, 3, '11:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1411, 84, 2, 2, 3, '12:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1412, 84, 2, 2, 3, '12:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1413, 84, 2, 2, 3, '13:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1414, 84, 2, 2, 3, '13:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1415, 84, 2, 2, 3, '14:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1416, 84, 2, 2, 3, '14:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1417, 84, 2, 2, 3, '15:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1418, 84, 2, 2, 3, '15:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-09 08:53:55', '2019-12-09 08:53:55', 0, ''),
(1419, 84, 2, 2, 5, '15:00', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1420, 84, 2, 2, 5, '15:30', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1421, 84, 2, 2, 5, '16:00', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1422, 84, 2, 2, 5, '16:30', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1423, 84, 2, 2, 5, '17:00', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1424, 84, 2, 2, 5, '17:30', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1425, 84, 2, 2, 5, '18:00', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1426, 84, 2, 2, 5, '18:30', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1427, 84, 2, 2, 5, '19:00', '2020-01-31', 0, 0, 'NO', 1, '2019-12-09 08:54:11', '2019-12-09 08:54:11', 0, ''),
(1428, 84, 3, 2, 4, '15:00', '2020-01-02', 0, 0, 'NO', 1, '2019-12-09 08:55:48', '2019-12-09 08:55:48', 0, ''),
(1429, 84, 3, 2, 4, '15:30', '2020-01-02', 0, 0, 'NO', 1, '2019-12-09 08:55:50', '2019-12-09 08:55:50', 0, ''),
(1430, 84, 3, 2, 4, '19:00', '2020-01-02', 0, 0, 'NO', 1, '2019-12-09 08:55:57', '2019-12-09 08:55:57', 0, ''),
(1431, 84, 3, 2, 4, '19:00', '2020-01-09', 0, 0, 'NO', 1, '2019-12-09 08:56:05', '2019-12-09 08:56:05', 0, ''),
(1432, 84, 3, 2, 2, '19:00', '2020-01-14', 0, 0, 'NO', 1, '2019-12-09 08:56:08', '2019-12-09 08:56:08', 0, ''),
(1433, 84, 3, 2, 4, '19:00', '2020-01-16', 0, 0, 'NO', 1, '2019-12-09 08:56:11', '2019-12-09 08:56:11', 0, ''),
(1434, 84, 3, 2, 2, '15:00', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1435, 84, 3, 2, 2, '15:30', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1436, 84, 3, 2, 2, '16:00', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1437, 84, 3, 2, 2, '16:30', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, '');
INSERT INTO `turno_registrados` (`id`, `paciente`, `medico`, `consultorio`, `dia`, `horario`, `fechaTurno`, `asistio`, `sobreturno`, `primerControl`, `activo`, `created_at`, `updated_at`, `caja`, `comentario`) VALUES
(1438, 84, 3, 2, 2, '17:00', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1439, 84, 3, 2, 2, '17:30', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1440, 84, 3, 2, 2, '18:00', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1441, 84, 3, 2, 2, '18:30', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1442, 84, 3, 2, 2, '19:00', '2020-01-21', 0, 0, 'NO', 1, '2019-12-09 08:56:51', '2019-12-09 08:56:51', 0, ''),
(1443, 84, 3, 2, 4, '15:00', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1444, 84, 3, 2, 4, '15:30', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1445, 84, 3, 2, 4, '16:00', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1446, 84, 3, 2, 4, '16:30', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1447, 84, 3, 2, 4, '17:00', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1448, 84, 3, 2, 4, '17:30', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1449, 84, 3, 2, 4, '18:00', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1450, 84, 3, 2, 4, '18:30', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1451, 84, 3, 2, 4, '19:00', '2020-01-23', 0, 0, 'NO', 1, '2019-12-09 08:56:53', '2019-12-09 08:56:53', 0, ''),
(1452, 84, 3, 2, 2, '15:00', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1453, 84, 3, 2, 2, '15:30', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1454, 84, 3, 2, 2, '16:00', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1455, 84, 3, 2, 2, '16:30', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1456, 84, 3, 2, 2, '17:00', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1457, 84, 3, 2, 2, '17:30', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1458, 84, 3, 2, 2, '18:00', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1459, 84, 3, 2, 2, '18:30', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1460, 84, 3, 2, 2, '19:00', '2020-01-28', 0, 0, 'NO', 1, '2019-12-09 08:56:55', '2019-12-09 08:56:55', 0, ''),
(1461, 84, 3, 2, 4, '15:00', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1462, 84, 3, 2, 4, '15:30', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1463, 84, 3, 2, 4, '16:00', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1464, 84, 3, 2, 4, '16:30', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1465, 84, 3, 2, 4, '17:00', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1466, 84, 3, 2, 4, '17:30', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1467, 84, 3, 2, 4, '18:00', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1468, 84, 3, 2, 4, '18:30', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1469, 84, 3, 2, 4, '19:00', '2020-01-30', 0, 0, 'NO', 1, '2019-12-09 08:56:57', '2019-12-09 08:56:57', 0, ''),
(1470, 84, 3, 2, 2, '15:00', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1471, 84, 3, 2, 2, '15:30', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1472, 84, 3, 2, 2, '16:00', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1473, 84, 3, 2, 2, '16:30', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1474, 84, 3, 2, 2, '17:00', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1475, 84, 3, 2, 2, '17:30', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1476, 84, 3, 2, 2, '18:00', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1477, 84, 3, 2, 2, '18:30', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1478, 84, 3, 2, 2, '19:00', '2020-02-04', 0, 0, 'NO', 1, '2019-12-09 08:57:15', '2019-12-09 08:57:15', 0, ''),
(1479, 84, 3, 2, 4, '15:00', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1480, 84, 3, 2, 4, '15:30', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1481, 84, 3, 2, 4, '16:00', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1482, 84, 3, 2, 4, '16:30', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1483, 84, 3, 2, 4, '17:00', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1484, 84, 3, 2, 4, '17:30', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1485, 84, 3, 2, 4, '18:00', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1486, 84, 3, 2, 4, '18:30', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1487, 84, 3, 2, 4, '19:00', '2020-02-06', 0, 0, 'NO', 1, '2019-12-09 08:57:17', '2019-12-09 08:57:17', 0, ''),
(1488, 84, 4, 2, 1, '15:00', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:58', '2019-12-09 08:58:58', 0, ''),
(1489, 84, 4, 2, 1, '15:30', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1490, 84, 4, 2, 1, '16:00', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1491, 84, 4, 2, 1, '16:30', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1492, 84, 4, 2, 1, '17:00', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1493, 84, 4, 2, 1, '17:30', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1494, 84, 4, 2, 1, '18:00', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1495, 84, 4, 2, 1, '18:30', '2020-02-17', 0, 0, 'NO', 1, '2019-12-09 08:58:59', '2019-12-09 08:58:59', 0, ''),
(1496, 84, 4, 2, 4, '15:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1497, 84, 4, 2, 4, '15:30', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1498, 84, 4, 2, 4, '16:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1499, 84, 4, 2, 4, '16:30', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1500, 84, 4, 2, 4, '17:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1501, 84, 4, 2, 4, '17:30', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1502, 84, 4, 2, 4, '18:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1503, 84, 4, 2, 4, '18:30', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1504, 84, 4, 2, 4, '19:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1505, 84, 4, 2, 4, '19:30', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1506, 84, 4, 2, 4, '20:00', '2020-02-20', 0, 0, 'NO', 1, '2019-12-09 08:59:01', '2019-12-09 08:59:01', 0, ''),
(1507, 84, 4, 2, 1, '15:00', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1508, 84, 4, 2, 1, '15:30', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1509, 84, 4, 2, 1, '16:00', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1510, 84, 4, 2, 1, '16:30', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1511, 84, 4, 2, 1, '17:00', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1512, 84, 4, 2, 1, '17:30', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1513, 84, 4, 2, 1, '18:00', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1514, 84, 4, 2, 1, '18:30', '2020-02-24', 0, 0, 'NO', 1, '2019-12-09 08:59:03', '2019-12-09 08:59:03', 0, ''),
(1515, 84, 4, 2, 4, '15:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1516, 84, 4, 2, 4, '15:30', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1517, 84, 4, 2, 4, '16:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1518, 84, 4, 2, 4, '16:30', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1519, 84, 4, 2, 4, '17:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1520, 84, 4, 2, 4, '17:30', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1521, 84, 4, 2, 4, '18:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1522, 84, 4, 2, 4, '18:30', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1523, 84, 4, 2, 4, '19:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1524, 84, 4, 2, 4, '19:30', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1525, 84, 4, 2, 4, '20:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-09 08:59:05', '2019-12-09 08:59:05', 0, ''),
(1526, 84, 4, 2, 1, '15:00', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1527, 84, 4, 2, 1, '15:30', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1528, 84, 4, 2, 1, '16:00', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1529, 84, 4, 2, 1, '16:30', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1530, 84, 4, 2, 1, '17:00', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1531, 84, 4, 2, 1, '17:30', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1532, 84, 4, 2, 1, '18:00', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1533, 84, 4, 2, 1, '18:30', '2020-03-02', 0, 0, 'NO', 1, '2019-12-09 08:59:27', '2019-12-09 08:59:27', 0, ''),
(1534, 84, 5, 2, 5, '15:00', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1535, 84, 5, 2, 5, '15:30', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1536, 84, 5, 2, 5, '16:00', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1537, 84, 5, 2, 5, '16:30', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1538, 84, 5, 2, 5, '17:00', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1539, 84, 5, 2, 5, '17:30', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1540, 84, 5, 2, 5, '18:00', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1541, 84, 5, 2, 5, '18:30', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1542, 84, 5, 2, 5, '19:00', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1543, 84, 5, 2, 5, '19:30', '2020-02-07', 0, 0, 'NO', 1, '2019-12-09 09:00:28', '2019-12-09 09:00:28', 0, ''),
(1544, 84, 5, 2, 5, '15:00', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1545, 84, 5, 2, 5, '15:30', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1546, 84, 5, 2, 5, '16:00', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1547, 84, 5, 2, 5, '16:30', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1548, 84, 5, 2, 5, '17:00', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1549, 84, 5, 2, 5, '17:30', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1550, 84, 5, 2, 5, '18:00', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1551, 84, 5, 2, 5, '18:30', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1552, 84, 5, 2, 5, '19:00', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1553, 84, 5, 2, 5, '19:30', '2020-02-14', 0, 0, 'NO', 1, '2019-12-09 09:00:31', '2019-12-09 09:00:31', 0, ''),
(1554, 84, 5, 2, 5, '15:00', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1555, 84, 5, 2, 5, '15:30', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1556, 84, 5, 2, 5, '16:00', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1557, 84, 5, 2, 5, '16:30', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1558, 84, 5, 2, 5, '17:00', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1559, 84, 5, 2, 5, '17:30', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1560, 84, 5, 2, 5, '18:00', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1561, 84, 5, 2, 5, '18:30', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1562, 84, 5, 2, 5, '19:00', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1563, 84, 5, 2, 5, '19:30', '2020-02-21', 0, 0, 'NO', 1, '2019-12-09 09:00:32', '2019-12-09 09:00:32', 0, ''),
(1564, 94, 2, 2, 5, '15:30', '2020-01-03', 0, 0, 'NO', 0, '2019-12-09 09:04:35', '2019-12-09 09:05:28', 0, ''),
(1567, 120, 2, 2, 3, '10:30', '2020-01-08', 0, 0, 'NO', 1, '2019-12-11 06:48:10', '2019-12-11 06:48:10', 0, ''),
(1568, 337, 4, 2, 4, '16:00', '2020-01-02', 0, 0, 'NO', 0, '2019-12-11 06:55:46', '2019-12-11 06:55:48', 0, ''),
(1569, 345, 3, 2, 4, '16:00', '2020-02-27', 0, 0, 'NO', 1, '2019-12-11 07:07:06', '2019-12-11 07:07:06', 0, ''),
(1570, 348, 3, 2, 2, '18:30', '2020-01-07', 0, 0, 'NO', 1, '2019-12-11 07:07:58', '2019-12-11 07:07:58', 0, ''),
(1571, 362, 2, 2, 5, '15:30', '2020-01-03', 0, 0, 'NO', 1, '2019-12-11 07:11:51', '2019-12-11 07:11:51', 0, ''),
(1573, 363, 2, 2, 5, '16:00', '2020-01-03', 0, 0, 'NO', 1, '2019-12-11 07:14:19', '2019-12-11 07:14:19', 0, ''),
(1574, 359, 2, 2, 5, '18:00', '2020-01-17', 0, 0, 'NO', 1, '2019-12-11 07:15:04', '2019-12-11 07:15:04', 0, ''),
(1575, 94, 1, 1, 3, '18:00', '2019-12-18', 0, 0, 'NO', 1, '2019-12-11 19:40:57', '2019-12-11 19:40:57', 0, ''),
(1576, 94, 1, 1, 3, '19:01', '2019-12-18', 0, 1, 'NO', 1, '2019-12-11 19:52:17', '2019-12-11 19:52:17', 0, ''),
(1577, 365, 1, 1, 3, '19:02', '2019-12-18', 0, 1, 'NO', 1, '2019-12-11 19:53:07', '2019-12-11 19:53:07', 0, ''),
(1578, 94, 1, 1, 3, '19:30', '2019-12-18', 0, 0, 'NO', 1, '2019-12-11 20:03:20', '2019-12-11 20:03:20', 0, ''),
(1579, 366, 1, 1, 3, '18:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-11 20:07:02', '2019-12-11 20:07:02', 0, ''),
(1580, 367, 1, 1, 3, '18:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-11 20:07:24', '2019-12-11 20:07:24', 0, ''),
(1581, 368, 1, 1, 3, '19:00', '2019-12-25', 0, 0, 'NO', 1, '2019-12-11 20:14:11', '2019-12-11 20:14:11', 0, ''),
(1582, 94, 1, 1, 3, '19:15', '2019-12-11', 0, 1, 'NO', 1, '2019-12-11 20:35:58', '2019-12-11 20:35:58', 0, ''),
(1583, 101, 1, 1, 3, '19:20', '2019-12-11', 2, 1, 'NO', 1, '2019-12-11 20:41:50', '2019-12-12 01:50:51', 0, ''),
(1584, 94, 1, 1, 3, '19:45', '2019-12-11', 0, 1, 'NO', 1, '2019-12-11 20:50:00', '2019-12-11 20:50:00', 0, ''),
(1585, 94, 1, 1, 3, '19:50', '2019-12-11', 0, 1, 'NO', 1, '2019-12-11 20:50:28', '2019-12-11 20:50:28', 0, ''),
(1586, 94, 1, 1, 3, '19:51', '2019-12-11', 0, 1, 'NO', 1, '2019-12-11 20:51:01', '2019-12-11 20:51:01', 0, ''),
(1587, 94, 1, 1, 3, '19:01', '2019-12-25', 0, 1, 'NO', 1, '2019-12-11 22:31:53', '2019-12-11 22:31:53', 0, ''),
(1588, 366, 1, 1, 3, '19:02', '2019-12-25', 0, 1, 'NO', 1, '2019-12-11 22:32:57', '2019-12-11 22:32:57', 0, ''),
(1589, 94, 1, 1, 3, '19:03', '2019-12-25', 0, 1, 'NO', 1, '2019-12-11 23:34:50', '2019-12-11 23:34:50', 0, ''),
(1590, 94, 1, 1, 3, '19:52', '2019-12-11', 0, 1, 'NO', 1, '2019-12-11 23:37:36', '2019-12-11 23:37:36', 0, ''),
(1591, 94, 1, 1, 3, '19:31', '2019-12-18', 0, 1, 'NO', 1, '2019-12-11 23:38:49', '2019-12-11 23:38:49', 0, ''),
(1592, 94, 1, 1, 3, '19:32', '2019-12-18', 0, 1, 'NO', 1, '2019-12-12 01:00:16', '2019-12-12 01:00:16', 0, ''),
(1593, 137, 2, 2, 3, '10:00', '2020-01-15', 0, 0, 'NO', 1, '2019-12-12 22:29:36', '2020-01-04 22:50:01', 100, ''),
(1594, 137, 2, 2, 5, '19:00', '2020-01-17', 0, 0, 'NO', 1, '2019-12-12 22:30:13', '2019-12-12 22:30:13', 0, ''),
(1595, 94, 2, 2, 3, '14:00', '2020-01-08', 0, 0, 'NO', 0, '2019-12-12 22:31:23', '2019-12-28 23:33:49', 0, 'hola'),
(1596, 369, 1, 1, 3, '19:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-16 02:29:50', '2019-12-16 02:29:50', 0, ''),
(1598, 94, 1, 1, 3, '18:30', '2020-01-15', 0, 0, 'SI', 0, '2019-12-18 15:49:08', '2019-12-28 23:34:07', 0, ''),
(1599, 94, 1, 1, 3, '19:30', '2020-01-22', 0, 0, 'SI', 0, '2019-12-18 15:56:26', '2019-12-28 23:34:11', 0, ''),
(1600, 94, 1, 1, 3, '18:30', '2020-01-29', 0, 0, 'NO', 0, '2019-12-18 15:57:31', '2019-12-28 23:34:13', 0, ''),
(1601, 94, 2, 2, 3, '12:30', '2020-01-15', 0, 0, 'SI', 0, '2019-12-18 15:58:13', '2019-12-28 23:34:03', 0, ''),
(1602, 94, 2, 2, 3, '13:00', '2020-01-15', 0, 0, 'SI', 0, '2019-12-18 15:58:13', '2019-12-28 23:34:05', 0, ''),
(1603, 366, 2, 2, 3, '13:00', '2020-01-08', 0, 0, 'NO', 1, '2019-12-18 15:59:52', '2019-12-18 15:59:52', 0, ''),
(1604, 94, 2, 2, 3, '09:30', '2020-02-05', 0, 0, 'SI', 0, '2019-12-18 16:14:28', '2019-12-28 23:34:15', 0, ''),
(1605, 94, 2, 2, 3, '10:00', '2020-02-05', 0, 0, 'SI', 0, '2019-12-18 16:14:28', '2019-12-28 23:34:18', 0, ''),
(1606, 366, 2, 2, 3, '10:30', '2020-02-05', 0, 0, 'NO', 1, '2019-12-18 16:14:42', '2019-12-18 16:14:42', 0, ''),
(1607, 95, 2, 2, 3, '12:30', '2020-02-05', 0, 0, 'NO', 0, '2019-12-18 16:15:07', '2019-12-29 03:48:26', 0, ''),
(1608, 366, 2, 2, 3, '11:00', '2020-02-05', 0, 0, 'SI', 1, '2019-12-18 16:15:16', '2019-12-18 16:15:16', 0, ''),
(1609, 366, 2, 2, 3, '11:30', '2020-02-05', 0, 0, 'SI', 1, '2019-12-18 16:15:16', '2019-12-18 16:15:16', 0, ''),
(1610, 94, 1, 1, 3, '19:30', '2019-12-25', 0, 0, 'NO', 1, '2019-12-18 16:16:13', '2019-12-18 16:16:13', 0, ''),
(1611, 94, 1, 1, 3, '18:00', '2020-01-08', 0, 0, 'NO', 0, '2019-12-18 16:17:18', '2019-12-28 23:33:51', 0, ''),
(1612, 101, 1, 1, 3, '18:00', '2020-01-15', 0, 0, 'SI', 1, '2019-12-18 16:22:47', '2019-12-18 16:22:47', 0, ''),
(1613, 366, 1, 1, 3, '19:00', '2020-01-15', 0, 0, 'NO', 1, '2019-12-18 16:23:18', '2019-12-18 16:23:18', 0, ''),
(1614, 370, 1, 1, 3, '19:30', '2020-01-15', 0, 0, 'NO', 1, '2019-12-18 16:23:54', '2019-12-18 16:23:54', 0, ''),
(1615, 94, 1, 1, 3, '18:00', '2020-01-22', 0, 0, 'NO', 0, '2019-12-18 16:29:48', '2019-12-28 23:34:09', 0, ''),
(1616, 366, 1, 1, 3, '18:30', '2020-01-22', 0, 0, 'SI', 1, '2019-12-18 16:29:55', '2019-12-18 16:29:55', 0, ''),
(1617, 370, 1, 1, 3, '19:00', '2020-01-22', 0, 0, 'SI', 1, '2019-12-18 16:30:01', '2019-12-18 16:30:01', 0, ''),
(1618, 370, 1, 1, 3, '18:00', '2020-01-29', 0, 0, 'NO', 1, '2019-12-18 16:30:38', '2019-12-18 16:30:38', 0, ''),
(1619, 370, 1, 1, 3, '19:30', '2020-01-29', 0, 0, 'NO', 1, '2019-12-18 16:30:43', '2019-12-18 16:30:43', 0, ''),
(1623, 94, 2, 2, 3, '11:30', '2020-01-08', 0, 0, 'SI', 0, '2019-12-18 16:33:24', '2019-12-28 23:33:44', 0, ''),
(1624, 94, 2, 2, 3, '12:00', '2020-01-08', 0, 0, 'SI', 0, '2019-12-18 16:33:24', '2019-12-28 23:33:47', 0, ''),
(1625, 94, 2, 2, 3, '10:30', '2020-01-15', 0, 0, 'SI', 0, '2019-12-18 16:35:08', '2019-12-28 23:33:58', 0, ''),
(1626, 94, 2, 2, 3, '11:00', '2020-01-15', 0, 0, 'SI', 0, '2019-12-18 16:35:08', '2019-12-28 23:34:01', 0, ''),
(1627, 94, 2, 2, 5, '16:00', '2020-01-10', 0, 0, 'SI', 0, '2019-12-19 16:10:08', '2019-12-28 23:33:54', 0, ''),
(1628, 94, 2, 2, 5, '16:30', '2020-01-10', 0, 0, 'SI', 0, '2019-12-19 16:10:08', '2019-12-28 23:33:55', 0, ''),
(1629, 101, 2, 2, 5, '15:00', '2020-01-10', 0, 0, 'NO', 1, '2019-12-19 16:13:32', '2019-12-19 16:13:32', 0, ''),
(1630, 101, 2, 2, 3, '13:30', '2020-01-15', 0, 0, 'SI', 1, '2019-12-19 16:14:05', '2019-12-19 16:14:05', 0, ''),
(1631, 101, 2, 2, 3, '14:00', '2020-01-15', 0, 0, 'SI', 1, '2019-12-19 16:14:05', '2019-12-19 16:14:05', 0, ''),
(1632, 101, 2, 2, 3, '09:30', '2020-02-26', 0, 0, 'SI', 1, '2019-12-19 16:15:39', '2019-12-19 16:15:39', 0, ''),
(1633, 101, 2, 2, 3, '10:00', '2020-02-26', 0, 0, 'SI', 1, '2019-12-19 16:15:39', '2019-12-19 16:15:39', 0, ''),
(1634, 101, 2, 2, 3, '13:00', '2020-02-05', 0, 0, 'SI', 1, '2019-12-19 16:17:22', '2019-12-19 16:17:22', 0, ''),
(1635, 101, 2, 2, 3, '13:30', '2020-02-05', 0, 0, 'SI', 1, '2019-12-19 16:17:22', '2019-12-19 16:17:22', 0, ''),
(1636, 94, 2, 2, 3, '11:00', '2020-01-08', 0, 0, 'SI', 1, '2019-12-29 02:21:23', '2019-12-29 02:21:23', 0, ''),
(1637, 94, 2, 2, 3, '11:30', '2020-01-08', 0, 0, 'SI', 1, '2019-12-29 02:21:23', '2019-12-29 02:21:23', 0, ''),
(1638, 94, 2, 2, 3, '09:30', '2020-02-05', 0, 0, 'SI', 0, '2019-12-29 02:22:24', '2019-12-29 04:36:46', 0, 'Cancelado por paciente'),
(1639, 94, 2, 2, 3, '10:00', '2020-02-05', 0, 0, 'SI', 0, '2019-12-29 02:22:24', '2019-12-29 04:36:46', 0, 'Cancelado por paciente'),
(1640, 94, 2, 2, 3, '09:30', '2020-02-12', 0, 0, 'NO', 0, '2019-12-29 02:22:49', '2019-12-29 02:22:50', 0, ''),
(1641, 94, 2, 2, 3, '14:00', '2020-02-12', 0, 0, 'NO', 0, '2019-12-29 02:22:52', '2019-12-29 04:38:17', 0, 'Cancelado por paciente'),
(1642, 94, 1, 1, 3, '18:30', '2020-01-08', 0, 0, 'SI', 1, '2019-12-29 02:23:21', '2019-12-29 02:23:21', 0, ''),
(1643, 94, 1, 1, 3, '19:30', '2020-01-22', 0, 0, 'NO', 1, '2019-12-29 02:23:50', '2019-12-29 02:23:50', 0, ''),
(1646, 95, 1, 1, 3, '19:00', '2020-01-08', 0, 0, 'SI', 0, '2019-12-29 03:53:13', '2019-12-29 03:53:30', 0, ''),
(1647, 95, 2, 2, 3, '14:00', '2020-01-08', 0, 0, 'SI', 0, '2019-12-29 04:05:08', '2019-12-29 04:05:53', 0, 'Cancelado por paciente'),
(1648, 95, 2, 2, 3, '14:30', '2020-01-08', 0, 0, 'SI', 0, '2019-12-29 04:05:08', '2019-12-29 04:05:53', 0, 'Cancelado por paciente'),
(1649, 95, 2, 2, 3, '14:00', '2020-01-08', 0, 0, 'SI', 0, '2019-12-29 04:07:03', '2019-12-29 04:34:19', 0, 'Cancelado por paciente'),
(1650, 95, 2, 2, 3, '14:30', '2020-01-08', 0, 0, 'SI', 0, '2019-12-29 04:07:03', '2019-12-29 04:34:19', 0, 'Cancelado por paciente'),
(1651, 95, 2, 2, 3, '15:00', '2020-01-15', 0, 0, 'SI', 0, '2019-12-29 04:07:28', '2019-12-29 04:32:04', 0, 'Cancelado por paciente'),
(1652, 95, 2, 2, 3, '15:30', '2020-01-15', 0, 0, 'SI', 0, '2019-12-29 04:07:28', '2019-12-29 04:32:04', 0, 'Cancelado por paciente'),
(1653, 95, 2, 2, 3, '15:00', '2020-02-05', 0, 0, 'SI', 0, '2019-12-29 04:07:52', '2019-12-29 04:35:17', 0, 'Cancelado por paciente'),
(1654, 95, 2, 2, 3, '15:30', '2020-02-05', 0, 0, 'SI', 0, '2019-12-29 04:07:52', '2019-12-29 04:35:17', 0, 'Cancelado por paciente'),
(1655, 94, 2, 2, 5, '16:00', '2020-01-10', 0, 0, 'SI', 1, '2019-12-31 18:50:25', '2019-12-31 18:50:25', 0, ''),
(1656, 94, 2, 2, 5, '16:30', '2020-01-10', 0, 0, 'SI', 1, '2019-12-31 18:50:25', '2019-12-31 18:50:25', 0, ''),
(1657, 95, 2, 2, 5, '17:00', '2020-01-10', 0, 0, 'SI', 1, '2019-12-31 18:50:50', '2019-12-31 18:50:50', 0, ''),
(1658, 95, 2, 2, 5, '17:30', '2020-01-10', 0, 0, 'SI', 1, '2019-12-31 18:50:50', '2019-12-31 18:50:50', 0, ''),
(1659, 101, 2, 2, 3, '12:30', '2020-01-08', 0, 0, 'NO', 0, '2019-12-31 18:53:19', '2019-12-31 18:53:20', 0, ''),
(1660, 94, 2, 2, 5, '15:00', '2020-01-17', 0, 0, 'SI', 1, '2019-12-31 19:23:48', '2019-12-31 19:23:48', 0, ''),
(1661, 94, 2, 2, 5, '15:30', '2020-01-17', 0, 0, 'SI', 1, '2019-12-31 19:23:48', '2019-12-31 19:23:48', 0, ''),
(1662, 95, 2, 2, 5, '16:30', '2020-01-17', 0, 0, 'SI', 1, '2019-12-31 19:24:20', '2019-12-31 19:24:20', 0, ''),
(1663, 95, 2, 2, 5, '17:00', '2020-01-17', 0, 0, 'SI', 1, '2019-12-31 19:24:20', '2019-12-31 19:24:20', 0, ''),
(1664, 94, 2, 2, 5, '15:00', '2020-02-14', 0, 0, 'NO', 1, '2019-12-31 20:14:27', '2019-12-31 20:14:27', 0, ''),
(1665, 95, 2, 2, 5, '15:30', '2020-02-14', 0, 0, 'NO', 1, '2019-12-31 20:14:45', '2019-12-31 20:14:45', 0, ''),
(1666, 94, 2, 2, 5, '16:00', '2020-02-07', 0, 0, 'SI', 1, '2019-12-31 20:16:31', '2019-12-31 20:16:31', 0, ''),
(1667, 94, 2, 2, 5, '16:30', '2020-02-07', 0, 0, 'SI', 1, '2019-12-31 20:16:31', '2019-12-31 20:16:31', 0, ''),
(1668, 95, 2, 2, 5, '17:00', '2020-02-07', 0, 0, 'NO', 1, '2019-12-31 20:17:49', '2019-12-31 20:17:49', 0, ''),
(1669, 101, 2, 2, 3, '12:00', '2020-01-08', 0, 0, 'NO', 1, '2019-12-31 20:22:30', '2019-12-31 20:22:30', 0, ''),
(1670, 101, 2, 2, 3, '13:30', '2020-01-08', 0, 0, 'SI', 1, '2019-12-31 20:23:57', '2019-12-31 20:23:57', 0, ''),
(1671, 101, 2, 2, 3, '14:00', '2020-01-08', 0, 0, 'SI', 1, '2019-12-31 20:23:57', '2019-12-31 20:23:57', 0, ''),
(1672, 94, 2, 2, 5, '16:00', '2020-02-14', 0, 0, 'SI', 1, '2019-12-31 20:24:18', '2019-12-31 20:24:18', 0, ''),
(1673, 94, 2, 2, 5, '16:30', '2020-02-14', 0, 0, 'SI', 1, '2019-12-31 20:24:18', '2019-12-31 20:24:18', 0, ''),
(1674, 95, 2, 2, 5, '17:00', '2020-02-14', 0, 0, 'SI', 1, '2019-12-31 20:24:23', '2019-12-31 20:24:23', 0, ''),
(1675, 95, 2, 2, 5, '17:30', '2020-02-14', 0, 0, 'SI', 1, '2019-12-31 20:24:23', '2019-12-31 20:24:23', 0, ''),
(1676, 101, 2, 2, 5, '18:00', '2020-02-14', 0, 0, 'SI', 1, '2019-12-31 20:24:27', '2019-12-31 20:24:27', 0, ''),
(1677, 101, 2, 2, 5, '18:30', '2020-02-14', 0, 0, 'SI', 1, '2019-12-31 20:24:27', '2019-12-31 20:24:27', 0, ''),
(1678, 94, 2, 2, 3, '09:30', '2020-02-12', 0, 0, 'SI', 1, '2019-12-31 20:26:14', '2019-12-31 20:26:14', 0, ''),
(1679, 94, 2, 2, 3, '10:00', '2020-02-12', 0, 0, 'SI', 1, '2019-12-31 20:26:14', '2019-12-31 20:26:14', 0, ''),
(1680, 101, 2, 2, 3, '10:30', '2020-02-12', 0, 0, 'SI', 1, '2019-12-31 20:27:37', '2019-12-31 20:27:37', 0, ''),
(1681, 101, 2, 2, 3, '11:00', '2020-02-12', 0, 0, 'SI', 1, '2019-12-31 20:27:37', '2019-12-31 20:27:37', 0, ''),
(1682, 94, 1, 1, 3, '18:30', '2020-01-15', 0, 0, 'SI', 1, '2020-01-04 22:33:08', '2020-01-04 22:33:08', 0, ''),
(1683, 94, 1, 1, 3, '18:30', '2020-01-29', 0, 0, 'NO', 1, '2020-01-04 22:33:56', '2020-01-04 22:33:56', 0, ''),
(1684, 94, 2, 2, 3, '09:30', '2020-02-19', 0, 0, 'SI', 1, '2020-01-04 22:34:32', '2020-01-04 22:34:32', 0, ''),
(1685, 94, 2, 2, 3, '10:00', '2020-02-19', 0, 0, 'SI', 1, '2020-01-04 22:34:32', '2020-01-04 22:34:32', 0, ''),
(1686, 94, 3, 2, 4, '15:00', '2020-01-09', 0, 0, 'SI', 1, '2020-01-04 22:36:35', '2020-01-04 22:36:35', 0, ''),
(1687, 94, 3, 2, 4, '15:30', '2020-01-09', 0, 0, 'SI', 1, '2020-01-04 22:36:35', '2020-01-04 22:36:35', 0, ''),
(1688, 101, 3, 2, 4, '16:00', '2020-01-09', 0, 0, 'NO', 1, '2020-01-04 22:37:30', '2020-01-04 22:37:30', 0, ''),
(1689, 94, 2, 2, 5, '15:00', '2020-02-21', 0, 0, 'SI', 1, '2020-01-04 23:06:27', '2020-01-04 23:06:27', 0, ''),
(1690, 94, 2, 2, 5, '15:30', '2020-02-21', 0, 0, 'SI', 1, '2020-01-04 23:06:27', '2020-01-04 23:06:27', 0, ''),
(1691, 95, 2, 2, 5, '16:00', '2020-02-21', 0, 0, 'NO', 1, '2020-01-04 23:06:35', '2020-01-04 23:06:35', 0, ''),
(1692, 94, 2, 2, 3, '10:30', '2020-01-15', 0, 0, 'NO', 1, '2020-01-04 23:06:55', '2020-01-04 23:06:55', 0, ''),
(1693, 376, 2, 2, 5, '15:00', '2020-02-28', 0, 0, 'SI', 1, '2020-01-05 19:12:37', '2020-01-05 19:12:37', 0, ''),
(1694, 376, 2, 2, 5, '15:30', '2020-02-28', 0, 0, 'SI', 1, '2020-01-05 19:12:37', '2020-01-05 19:12:37', 0, ''),
(1695, 375, 2, 2, 5, '16:00', '2020-02-28', 0, 0, 'NO', 1, '2020-01-05 19:14:37', '2020-01-05 19:14:37', 0, ''),
(1696, 377, 1, 1, 3, '18:00', '2020-01-08', 0, 0, 'SI', 1, '2020-01-05 19:15:31', '2020-01-05 19:15:31', 0, ''),
(1697, 375, 1, 1, 3, '19:00', '2020-01-08', 0, 0, 'NO', 1, '2020-01-05 19:17:05', '2020-01-05 19:17:05', 0, ''),
(1698, 374, 1, 1, 3, '19:30', '2020-01-08', 0, 0, 'NO', 1, '2020-01-05 19:17:48', '2020-01-05 19:17:48', 0, ''),
(1699, 378, 3, 2, 4, '16:30', '2020-01-09', 0, 0, 'NO', 1, '2020-01-05 21:49:30', '2020-01-05 21:49:30', 0, ''),
(1701, 379, 1, 1, 3, '18:00', '2020-01-22', 0, 0, 'NO', 1, '2020-01-16 07:00:45', '2020-01-16 07:00:45', 0, ''),
(1703, 84, 1, 1, 3, '18:00', '2020-02-05', 0, 0, 'NO', 1, '2020-01-16 07:43:28', '2020-01-16 07:43:28', 0, ''),
(1704, 84, 1, 1, 3, '18:30', '2020-02-05', 0, 0, 'NO', 1, '2020-01-16 07:43:28', '2020-01-16 07:43:28', 0, ''),
(1705, 84, 1, 1, 3, '19:00', '2020-02-05', 0, 0, 'NO', 1, '2020-01-16 07:43:28', '2020-01-16 07:43:28', 0, ''),
(1706, 84, 1, 1, 3, '19:30', '2020-02-05', 0, 0, 'NO', 1, '2020-01-16 07:43:28', '2020-01-16 07:43:28', 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_tipo` int(10) UNSIGNED NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `perfil` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `usuario_tipo`, `remember_token`, `created_at`, `updated_at`, `perfil`) VALUES
(2, 'admin', 'banegasrodrigo89@gmail.com', NULL, '$2y$10$NTaWBiZEFAhUTrEIViFjJ.bj6YnMb2tHD8Y4HPHdaJN5XJjgdCbs6', 1, 'JgM5f2z8ZvwqRuvIyzXy0l6wZ3AWdWDOGgxSPoMiFLIWxwVURPQsNt1zgOvq', '2019-10-14 23:22:10', '2019-11-04 16:24:04', 2),
(4, 'María Florencia', 'mfge23@hotmail.com', NULL, '$2y$10$H.m71Oiw4sr64LnIvF02/.LAlhTm2epOV.wFYEKa4uxOiEc6bDIBm', 2, '95xXPFhhH1oSuzG5hkqCryTasYzxdhucmOsoNtW4qoVfGvwaxt5aRS0JTRqk', '2019-10-16 16:21:52', '2020-01-06 01:19:23', 1),
(7, 'Patricia', 'pato@gmail.com', NULL, '$2y$10$H.m71Oiw4sr64LnIvF02/.LAlhTm2epOV.wFYEKa4uxOiEc6bDIBm', 3, NULL, '2019-10-16 16:32:27', '2019-10-16 16:32:27', 1),
(8, 'Paula', 'paula@gmail.com', NULL, '$2y$10$FQhBzoX/mLgjSnEfcfIsduCr6Szsh.e9rzB1i36LyzWifHQ0B5EC2', 3, NULL, '2019-10-16 16:33:35', '2019-10-16 16:33:35', 1),
(10, 'Lucas', 'lucasgonzalezg@hotmail.com', NULL, '$2y$10$H.m71Oiw4sr64LnIvF02/.LAlhTm2epOV.wFYEKa4uxOiEc6bDIBm', 2, 'LOrbTbVPMoZIrghluozx8NYUo1TpcfECQfB1qHti8FCG0IzaJdh7nLHM1iDI', '2019-10-26 15:34:09', '2020-01-04 23:28:55', 2),
(11, 'Lucas', 'l_lucarelli@hotmail.com', NULL, '$2y$10$RnDLWlMiJKcDMoEE1tWAR.Q8A0aK00ajNp4ob7rmwTtVmdueIw2MC', 2, 'Y0eIN0foZJXZRlv0VKkHRDRc64lvgNrRY8puOwBMJYjJdhS5SMLR8PFbogDK', '2019-10-26 15:44:02', '2019-10-26 15:44:02', 2),
(12, 'Secretaria1', 'consultoriogaribaldi@gmail.com', NULL, '$2y$10$kNKt9aLGrsSBIemtTjzIju2rTAbiXpIgspdmriyZZoo2/zZOOUZbq', 3, 'F1i4tLnd48uENy7wRt889tkbhPwZUcGWEvMdFgq8H2j46dIkeNTBWubZBMiQ', '2019-10-26 15:51:04', '2019-10-26 15:51:04', 2),
(13, 'Maria de los Angeles', 'patoechegoyen@gmail.com', NULL, '$2y$10$tFPNLUOfp0yT7IPlySyNqegbeculyno3qceIhyatCu.w/mvqI8HFS', 2, 'jp3TOuLHSS8MHjKcBFF4Ytj1Lb70dT8bKnLnLGH8fBPb2mYAPz47JAZWb2pA', '2019-10-30 22:24:26', '2019-10-30 22:24:26', 2),
(14, 'Lucía', 'ludiomedi@gmail.com', NULL, '$2y$10$d.AAaNWpVs.GglyLEq4cFOSvNu5eonJtsihsVNp02npBXmtCI.N5y', 2, 'Y6YaEc7e4q4K0AAZK8diHYlcezskh4qmHBK9P4iet5dScEFuXPvueF98pG4E', '2019-11-04 16:25:34', '2019-11-04 16:25:34', 2),
(15, 'Guillermina', 'guigariboldi@gmail.com', NULL, '$2y$10$OPz0o5HJlwaFIJt7pnELQeFXHI7CcuBleISElRSioxrOuIRMDx6Au', 2, 'PhRRRWmHJeA1IsBNFuq905dgAcb2JDIBdpXhyRSAKzazOwbPR5OD6HH6DDKg', '2019-11-13 14:35:41', '2019-11-13 14:35:41', 2),
(16, 'Test', 'test@test.com', NULL, '$2y$10$iiGzMo8YhrTbhGLp8MnOo.zKgcv/yOBaCRsfySPWips7k9A8x8c4W', 3, NULL, '2019-12-12 22:20:29', '2019-12-12 22:20:29', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `consultorios`
--
ALTER TABLE `consultorios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `especialidads`
--
ALTER TABLE `especialidads`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `feriados_fecha_unique` (`fecha`);

--
-- Indices de la tabla `horario_medicos`
--
ALTER TABLE `horario_medicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `horario_medicos_medico_foreign` (`medico`),
  ADD KEY `horario_medicos_consultorio_foreign` (`consultorio`);

--
-- Indices de la tabla `horario_medico_d_h_s`
--
ALTER TABLE `horario_medico_d_h_s`
  ADD PRIMARY KEY (`id`),
  ADD KEY `horario_medico_d_h_s_medico_foreign` (`medico`),
  ADD KEY `horario_medico_d_h_s_consultorio_foreign` (`consultorio`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicos_especialidad_foreign` (`especialidad`),
  ADD KEY `medicos_consultorio_foreign` (`consultorio`),
  ADD KEY `medicos_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `medico_primer_controls`
--
ALTER TABLE `medico_primer_controls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medico_primer_controls_medico_foreign` (`medico`),
  ADD KEY `medico_primer_controls_consultorio_foreign` (`consultorio`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `modulo_medicos`
--
ALTER TABLE `modulo_medicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modulo_medicos_medico_foreign` (`medico`);

--
-- Indices de la tabla `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indices de la tabla `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indices de la tabla `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indices de la tabla `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pacientes_dni_unique` (`dni`);

--
-- Indices de la tabla `paciente_secretarias`
--
ALTER TABLE `paciente_secretarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_secretarias_paciente_foreign` (`paciente`),
  ADD KEY `paciente_secretarias_consultorio_foreign` (`consultorio`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `secretarias`
--
ALTER TABLE `secretarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretarias_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `secretaria_consultorios`
--
ALTER TABLE `secretaria_consultorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretaria_consultorios_secretaria_id_foreign` (`secretaria_id`),
  ADD KEY `secretaria_consultorios_consultorio_id_foreign` (`consultorio_id`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `turno_registrados`
--
ALTER TABLE `turno_registrados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turno_registrados_paciente_foreign` (`paciente`),
  ADD KEY `turno_registrados_medico_foreign` (`medico`),
  ADD KEY `turno_registrados_consultorio_foreign` (`consultorio`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_usuario_tipo_foreign` (`usuario_tipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `consultorios`
--
ALTER TABLE `consultorios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `especialidads`
--
ALTER TABLE `especialidads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `horario_medicos`
--
ALTER TABLE `horario_medicos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `horario_medico_d_h_s`
--
ALTER TABLE `horario_medico_d_h_s`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `medico_primer_controls`
--
ALTER TABLE `medico_primer_controls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `modulo_medicos`
--
ALTER TABLE `modulo_medicos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=381;

--
-- AUTO_INCREMENT de la tabla `paciente_secretarias`
--
ALTER TABLE `paciente_secretarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3305;

--
-- AUTO_INCREMENT de la tabla `secretarias`
--
ALTER TABLE `secretarias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `secretaria_consultorios`
--
ALTER TABLE `secretaria_consultorios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `turno_registrados`
--
ALTER TABLE `turno_registrados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1707;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `horario_medicos`
--
ALTER TABLE `horario_medicos`
  ADD CONSTRAINT `horario_medicos_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `horario_medicos_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`);

--
-- Filtros para la tabla `horario_medico_d_h_s`
--
ALTER TABLE `horario_medico_d_h_s`
  ADD CONSTRAINT `horario_medico_d_h_s_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `horario_medico_d_h_s_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`);

--
-- Filtros para la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD CONSTRAINT `medicos_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `medicos_especialidad_foreign` FOREIGN KEY (`especialidad`) REFERENCES `especialidads` (`id`),
  ADD CONSTRAINT `medicos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `medico_primer_controls`
--
ALTER TABLE `medico_primer_controls`
  ADD CONSTRAINT `medico_primer_controls_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `medico_primer_controls_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`);

--
-- Filtros para la tabla `modulo_medicos`
--
ALTER TABLE `modulo_medicos`
  ADD CONSTRAINT `modulo_medicos_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`);

--
-- Filtros para la tabla `paciente_secretarias`
--
ALTER TABLE `paciente_secretarias`
  ADD CONSTRAINT `paciente_secretarias_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `paciente_secretarias_paciente_foreign` FOREIGN KEY (`paciente`) REFERENCES `pacientes` (`id`);

--
-- Filtros para la tabla `secretarias`
--
ALTER TABLE `secretarias`
  ADD CONSTRAINT `secretarias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `secretaria_consultorios`
--
ALTER TABLE `secretaria_consultorios`
  ADD CONSTRAINT `secretaria_consultorios_consultorio_id_foreign` FOREIGN KEY (`consultorio_id`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `secretaria_consultorios_secretaria_id_foreign` FOREIGN KEY (`secretaria_id`) REFERENCES `secretarias` (`id`);

--
-- Filtros para la tabla `turno_registrados`
--
ALTER TABLE `turno_registrados`
  ADD CONSTRAINT `turno_registrados_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `turno_registrados_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`),
  ADD CONSTRAINT `turno_registrados_paciente_foreign` FOREIGN KEY (`paciente`) REFERENCES `pacientes` (`id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_usuario_tipo_foreign` FOREIGN KEY (`usuario_tipo`) REFERENCES `tipo_usuario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
