-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 14-10-2019 a las 23:16:29
-- Versión del servidor: 10.2.24-MariaDB
-- Versión de PHP: 7.2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u895805914_turnos`
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `consultorios`
--

INSERT INTO `consultorios` (`id`, `direccion`, `telefono`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Av La Plata 1260', 2914556090, 1, '2019-09-21 21:10:31', '2019-09-21 21:10:31'),
(2, '9 de Julio - 585', 4550045, 1, '2019-09-21 21:10:43', '2019-09-21 21:10:43');

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
(1, 'Pediatria', '1569089409pediatria.png', 1, '2019-09-21 21:10:09', '2019-09-21 21:10:09');

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
(1, 1, 1, 1, '08:00', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(2, 1, 1, 1, '08:30', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(3, 1, 1, 1, '09:00', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(4, 1, 1, 1, '09:30', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(5, 1, 1, 1, '10:00', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(6, 1, 1, 1, '10:30', 1, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(7, 1, 1, 3, '08:00', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(8, 1, 1, 3, '08:30', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(9, 1, 1, 3, '09:00', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(10, 1, 1, 3, '09:30', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(11, 1, 1, 3, '10:00', 0, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(12, 1, 1, 3, '10:30', 1, 1, '2019-09-21 21:14:35', '2019-09-21 21:14:35'),
(13, 2, 1, 2, '14:00', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(14, 2, 1, 2, '14:30', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(15, 2, 1, 2, '15:00', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(16, 2, 1, 2, '15:30', 1, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(17, 2, 1, 2, '18:00', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(18, 2, 1, 2, '18:30', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(19, 2, 1, 2, '19:00', 1, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(20, 2, 1, 4, '14:00', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(21, 2, 1, 4, '14:30', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(22, 2, 1, 4, '15:00', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(23, 2, 1, 4, '15:30', 1, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(24, 2, 1, 4, '18:00', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(25, 2, 1, 4, '18:30', 0, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(26, 2, 1, 4, '19:00', 1, 1, '2019-09-21 21:16:30', '2019-09-21 21:16:30'),
(27, 3, 2, 3, '08:00', 0, 1, '2019-09-21 21:17:23', '2019-09-21 21:17:23'),
(28, 3, 2, 3, '08:30', 0, 1, '2019-09-21 21:17:23', '2019-09-21 21:17:23'),
(29, 3, 2, 3, '09:00', 0, 1, '2019-09-21 21:17:23', '2019-09-21 21:17:23'),
(30, 3, 2, 3, '09:30', 0, 1, '2019-09-21 21:17:23', '2019-09-21 21:17:23'),
(31, 3, 2, 3, '10:00', 0, 1, '2019-09-21 21:17:23', '2019-09-21 21:17:23'),
(32, 3, 2, 3, '10:30', 1, 1, '2019-09-21 21:17:23', '2019-09-21 21:17:23');

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `medicos`
--

INSERT INTO `medicos` (`id`, `nombre`, `apellido`, `especialidad`, `consultorio`, `telefono`, `mail`, `castigo_automatico`, `foto`, `activo`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Lucas', 'Gonzalez Gili', 1, 1, 2915050050, 'lucas@gmail.com', 0, 'medico_sin_foto.png', 1, 3, '2019-09-21 21:11:45', '2019-09-21 21:11:45'),
(2, 'Florencia', 'Garcia Elliot', 1, 1, 2914556090, 'flor@gmail.com', 0, '15690895571567029135medico1.png', 1, 4, '2019-09-21 21:12:37', '2019-09-21 21:12:37'),
(3, 'Eliana', 'Fortunatti', 1, 2, 2914556090, 'eli@gmail.com', 0, '1569089587medico3.png', 1, 5, '2019-09-21 21:13:07', '2019-09-21 21:13:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medico_primercontrol`
--

CREATE TABLE `medico_primercontrol` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medico` int(10) UNSIGNED NOT NULL,
  `dia` int(11) NOT NULL,
  `cantidadPrimerControl` int(11) NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(17, '2019_09_21_175823_create_medico_primercontrol_table', 1),
(19, '2019_09_26_145749_altapaciente_secretaria', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombre`, `apellido`, `dni`, `telefono`, `mail`, `fecha_nacimiento`, `fecha_castigo`, `obra_social`, `numero_afiliado`, `obra_social_plan`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Rodrigo', 'Banegas', 34741602, '1', 'rodri@gmail.com', '1989-12-14', '2000-01-01', 'Swis Medical', '1', '1', 1, '2019-09-21 22:13:25', '2019-09-21 22:13:25'),
(2, 'Florencia', 'Garcia', 32198410, '1', 'florge@gmail.com', '1987-12-12', '2000-01-01', 'Swis Medical', '1', '', 2, '2019-09-21 22:33:06', '2019-09-21 22:33:06'),
(3, 'Sofia', 'Banegas', 57764801, '2915090090', 'sofibanegas@gmail.com', '2019-07-19', '2000-01-01', '', '', '', 2, '2019-09-21 22:35:52', '2019-09-21 22:35:52'),
(4, 'Bruno', 'Boschetti', 34741513, '2915080090', 'bruno@gmail.com', '1989-07-30', '2000-01-01', '', '', '', 2, '2019-09-21 23:11:36', '2019-09-21 23:11:36'),
(5, 'Franco', 'Burgos', 45, '45322323', 'franco@gmail.com', '1990-01-07', '2000-01-01', 'OSDE', '12', 'Full', 2, '2019-09-21 23:13:17', '2019-09-21 23:13:17'),
(6, 'Hilario', 'Marquez', 57764802, '1', 'hilario@gmail.com', '2019-09-18', '2000-01-01', 'OSDE', '1', '1', 2, '2019-09-26 18:10:52', '2019-09-26 18:10:52'),
(8, 'Marcos', 'Jagoe', 57764803, '1', 'marcos@gmail.com', '2019-09-09', '2000-01-01', '', '', '', 2, '2019-09-26 18:12:27', '2019-09-26 18:12:27'),
(10, 'Jorge', 'Frod', 57764804, '1', 'jorge@gmail.com', '2019-08-27', '2000-01-01', '', '', '', 1, '2019-09-26 18:17:05', '2019-09-26 23:04:10'),
(11, 'Maria Jose', 'Banegas', 57764805, '2915090091', 'mari@gmail.com', '2019-01-02', '2000-01-01', '', '', '', 1, '2019-09-26 22:02:33', '2019-09-26 23:04:23'),
(12, 'Marta', 'Perez', 34741601, '2915050050', 'marta@gmail.com', '2019-09-09', '2000-01-01', '', '', '', 2, '2019-09-26 23:57:39', '2019-09-26 23:57:39'),
(13, 'Javier', 'Castell', 34741600, '2914556090', 'javi@gmail.com', '2019-07-07', '2000-01-01', 'DOSEM', '876', 'FULL', 1, '2019-09-27 01:06:36', '2019-09-27 01:06:36'),
(14, 'Juana', 'Paladino', 34741599, '45322323', 'juana@gmail.com', '2018-08-09', '2000-01-01', 'OSDE', '13888183', 'GBU', 1, '2019-09-27 01:09:44', '2019-09-27 01:09:44'),
(99999, '', 'cancelado', 99999, '0', '', '0000-00-00', '0000-00-00', '', '', '', 1, NULL, NULL);

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
(1, 10, 1, 1, '2019-09-26 18:17:05', '2019-09-26 23:04:10'),
(2, 11, 1, 1, '2019-09-26 22:02:33', '2019-09-26 23:04:23'),
(3, 12, 1, 0, '2019-09-26 23:57:39', '2019-09-26 23:57:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Sofia', 'Banegas', 2, '2019-09-21 21:11:01', '2019-09-21 21:11:01');

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
(1, 1, 1, 1, '2019-09-21 21:11:08', '2019-09-21 21:11:08');

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
  `fechaTurno` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asistio` int(11) NOT NULL,
  `sobreturno` int(11) NOT NULL,
  `primerControl` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turno_registrados`
--

INSERT INTO `turno_registrados` (`id`, `paciente`, `medico`, `consultorio`, `dia`, `horario`, `fechaTurno`, `asistio`, `sobreturno`, `primerControl`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 2, '15:30', '24/09/2019', 0, 0, 'NO', 1, '2019-09-21 23:27:18', '2019-09-21 23:27:18'),
(2, 1, 2, 1, 4, '15:00', '26/09/2019', 1, 0, 'SI', 1, '2019-09-21 23:29:24', '2019-09-27 03:26:12'),
(3, 1, 2, 1, 4, '15:30', '26/09/2019', 2, 0, 'SI', 1, '2019-09-21 23:29:24', '2019-09-27 03:26:17'),
(4, 1, 2, 1, 4, '18:30', '26/09/2019', 0, 0, 'SI', 0, '2019-09-21 23:29:54', '2019-09-21 23:32:12'),
(5, 1, 2, 1, 4, '19:00', '26/09/2019', 0, 0, 'SI', 1, '2019-09-21 23:29:54', '2019-09-21 23:29:54'),
(6, 11, 2, 1, 4, '18:00', '26/09/2019', 2, 0, 'NO', 0, '2019-09-26 22:39:18', '2019-09-27 03:26:55'),
(7, 11, 2, 1, 4, '18:00', '26/09/2019', 2, 0, 'NO', 0, '2019-09-26 22:39:21', '2019-09-27 03:27:00'),
(8, 11, 2, 1, 4, '18:00', '26/09/2019', 1, 0, 'NO', 1, '2019-09-26 22:39:23', '2019-09-27 03:27:10'),
(10, 3, 2, 1, 5, '15:30', '27/09/2019', 0, 0, 'NO', 1, '2019-09-28 01:29:26', '2019-09-28 01:29:26'),
(11, 3, 2, 1, 2, '15:30', '08/10/2019', 0, 0, 'NO', 1, '2019-09-28 01:30:00', '2019-09-28 01:30:00'),
(12, 1, 2, 1, 2, '14:30', '15/10/2019', 0, 0, 'NO', 1, '2019-09-28 01:57:25', '2019-09-28 01:57:25'),
(14, 1, 2, 1, 2, '15:00', '08/10/2019', 0, 0, 'NO', 1, '2019-09-30 03:20:07', '2019-09-30 03:20:07'),
(15, 3, 2, 1, 2, '18:00', '08/10/2019', 0, 0, 'NO', 1, '2019-09-30 03:20:45', '2019-09-30 03:20:45'),
(17, 2, 2, 1, 2, '19:00', '08/10/2019', 0, 0, 'NO', 1, '2019-09-30 03:26:43', '2019-09-30 03:26:43'),
(19, 2, 2, 1, 4, '14:30', '03/10/2019', 0, 0, 'NO', 1, '2019-09-30 03:47:28', '2019-09-30 03:47:28'),
(20, 99999, 2, 1, 2, '14:00', '08/10/2019', 0, 0, 'NO', 1, '2019-09-30 15:56:23', '2019-09-30 15:56:23'),
(21, 99999, 2, 1, 2, '14:30', '08/10/2019', 0, 0, 'NO', 1, '2019-09-30 16:07:49', '2019-09-30 16:07:49'),
(31, 99999, 2, 1, 4, '14:30', '10/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:14:27', '2019-09-30 23:14:27'),
(32, 99999, 2, 1, 4, '15:00', '10/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:14:27', '2019-09-30 23:14:27'),
(33, 99999, 2, 1, 4, '15:30', '10/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:14:27', '2019-09-30 23:14:27'),
(34, 99999, 2, 1, 4, '18:00', '10/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:14:27', '2019-09-30 23:14:27'),
(35, 99999, 2, 1, 4, '18:30', '10/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:14:27', '2019-09-30 23:14:27'),
(36, 99999, 2, 1, 4, '19:00', '10/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:14:27', '2019-09-30 23:14:27'),
(38, 99999, 2, 1, 4, '14:30', '17/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:20:32', '2019-09-30 23:20:32'),
(39, 99999, 2, 1, 4, '15:00', '17/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:20:32', '2019-09-30 23:20:32'),
(40, 99999, 2, 1, 4, '15:30', '17/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:20:32', '2019-09-30 23:20:32'),
(41, 99999, 2, 1, 4, '18:00', '17/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:20:32', '2019-09-30 23:20:32'),
(42, 99999, 2, 1, 4, '18:30', '17/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:20:32', '2019-09-30 23:20:32'),
(43, 99999, 2, 1, 4, '19:00', '17/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:20:32', '2019-09-30 23:20:32'),
(46, 99999, 2, 1, 4, '14:00', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(47, 99999, 2, 1, 4, '14:30', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(48, 99999, 2, 1, 4, '15:00', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(49, 99999, 2, 1, 4, '15:30', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(50, 99999, 2, 1, 4, '18:00', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(51, 99999, 2, 1, 4, '18:30', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(52, 99999, 2, 1, 4, '19:00', '31/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:25:58', '2019-09-30 23:25:58'),
(53, 99999, 2, 1, 2, '14:30', '29/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:26:00', '2019-09-30 23:26:00'),
(56, 99999, 2, 1, 4, '15:30', '03/10/2019', 0, 0, 'NO', 1, '2019-09-30 23:50:56', '2019-09-30 23:50:56'),
(57, 99999, 2, 1, 2, '15:00', '15/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:56:47', '2019-10-01 02:56:47'),
(58, 99999, 2, 1, 4, '14:00', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(59, 99999, 2, 1, 4, '14:30', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(60, 99999, 2, 1, 4, '15:00', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(61, 99999, 2, 1, 4, '15:30', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(62, 99999, 2, 1, 4, '18:00', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(63, 99999, 2, 1, 4, '18:30', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(64, 99999, 2, 1, 4, '19:00', '24/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:57:20', '2019-10-01 02:57:20'),
(66, 1, 2, 1, 2, '15:30', '15/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:58:28', '2019-10-01 02:58:28'),
(67, 3, 2, 1, 2, '18:30', '15/10/2019', 0, 0, 'NO', 1, '2019-10-01 02:58:50', '2019-10-01 02:58:50'),
(70, 99999, 2, 1, 4, '18:00', '03/10/2019', 0, 0, 'NO', 1, '2019-10-01 15:54:25', '2019-10-01 15:54:25'),
(81, 1, 2, 1, 2, '14:00', '01/10/2019', 0, 0, 'NO', 1, '2019-10-01 17:09:57', '2019-10-01 17:09:57'),
(82, 3, 2, 1, 2, '14:30', '01/10/2019', 0, 0, 'SI', 1, '2019-10-01 17:10:46', '2019-10-01 17:10:46'),
(83, 3, 2, 1, 2, '15:00', '01/10/2019', 0, 0, 'SI', 1, '2019-10-01 17:10:46', '2019-10-01 17:10:46'),
(84, 1, 2, 1, 2, '18:00', '01/10/2019', 0, 0, 'NO', 1, '2019-10-01 17:24:13', '2019-10-01 17:24:13'),
(85, 2, 2, 1, 4, '14:00', '03/10/2019', 0, 0, 'NO', 1, '2019-10-03 01:51:39', '2019-10-03 01:51:39'),
(86, 1, 2, 1, 4, '15:00', '03/10/2019', 0, 0, 'NO', 1, '2019-10-03 01:51:47', '2019-10-03 01:51:47'),
(87, 3, 2, 1, 4, '18:30', '03/10/2019', 0, 0, 'NO', 1, '2019-10-03 01:51:58', '2019-10-03 01:51:58'),
(88, 1, 2, 1, 4, '19:00', '03/10/2019', 0, 0, 'NO', 1, '2019-10-03 01:52:11', '2019-10-03 01:52:11'),
(89, 99999, 2, 1, 4, '14:00', '17/10/2019', 0, 0, 'NO', 1, '2019-10-06 05:17:46', '2019-10-06 05:17:46');

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `usuario_tipo`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'banegasrodrigo89@gmail.com', NULL, '$2y$10$GGWx6e2UuQcfnsDICHVONeNRj0TJoyBYpZ2NmclGMYquysQNCnPZ.', 1, NULL, '2019-09-21 21:09:24', '2019-09-21 21:09:24'),
(2, 'Sofia', 'sofia@gmail.com', NULL, '$2y$10$uiRw0NeV9yjxlXjyNZDX1eZM7ZCC/mYaqoSKL0Yf80yuvfUgc5VOa', 3, NULL, '2019-09-21 21:11:01', '2019-09-21 21:11:01'),
(3, 'Lucas', 'lucas@gmail.com', NULL, '$2y$10$c.ZKMed4dh5xT014ACtF3eMezXTODDGtjZvFQZaWFNosff3KVwixK', 2, NULL, '2019-09-21 21:11:29', '2019-09-21 21:11:29'),
(4, 'Florencia', 'flor@gmail.com', NULL, '$2y$10$i6mQTmytGsPgG25itlGeJeyxju/1SVWeF5Z0jhOpqKAmG496jRnLm', 2, NULL, '2019-09-21 21:12:21', '2019-09-21 21:12:21'),
(5, 'Eliana', 'eli@gmail.com', NULL, '$2y$10$bIvvISelmydPMyUeFIrtdeM9.FdM6cmvqULgubrRi2bvhpDvEfwY6', 2, NULL, '2019-09-21 21:12:55', '2019-09-21 21:12:55');

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
-- Indices de la tabla `horario_medicos`
--
ALTER TABLE `horario_medicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `horario_medicos_medico_foreign` (`medico`),
  ADD KEY `horario_medicos_consultorio_foreign` (`consultorio`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicos_especialidad_foreign` (`especialidad`),
  ADD KEY `medicos_consultorio_foreign` (`consultorio`),
  ADD KEY `medicos_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `medico_primercontrol`
--
ALTER TABLE `medico_primercontrol`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medico_primercontrol_medico_foreign` (`medico`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de la tabla `horario_medicos`
--
ALTER TABLE `horario_medicos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `medico_primercontrol`
--
ALTER TABLE `medico_primercontrol`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100004;

--
-- AUTO_INCREMENT de la tabla `paciente_secretarias`
--
ALTER TABLE `paciente_secretarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `secretarias`
--
ALTER TABLE `secretarias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `secretaria_consultorios`
--
ALTER TABLE `secretaria_consultorios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `turno_registrados`
--
ALTER TABLE `turno_registrados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Filtros para la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD CONSTRAINT `medicos_consultorio_foreign` FOREIGN KEY (`consultorio`) REFERENCES `consultorios` (`id`),
  ADD CONSTRAINT `medicos_especialidad_foreign` FOREIGN KEY (`especialidad`) REFERENCES `especialidads` (`id`),
  ADD CONSTRAINT `medicos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `medico_primercontrol`
--
ALTER TABLE `medico_primercontrol`
  ADD CONSTRAINT `medico_primercontrol_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`);

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
