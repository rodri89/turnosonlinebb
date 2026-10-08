-- Tipos de turno configurables por médico (Entrevista con padres / Consulta y evaluación)
-- Correr una sola vez por entorno (local y luego prod). Es idempotente: se puede volver a
-- correr sin romper nada si ya se corrió antes.

-- 1) Catálogo de tipos de turno
CREATE TABLE IF NOT EXISTS `tipo_turnos` (
  `id` INT UNSIGNED NOT NULL,
  `nombre` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ids iguales a los que ya usa Util::getTipoTurno() / horario_medicos.tipo_turno hoy.
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 1, 'Consulta', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 1);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 2, 'Videollamada', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 2);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 4, 'Videollamada Salud360', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 4);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 22, 'Consulta Online', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 22);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 23, 'Ecografías', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 23);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 24, 'Deportología', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 24);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 25, 'Consulta + Ecografías', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 25);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 26, 'Entrevista con padres', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 26);
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 27, 'Consulta y evaluación', 1, NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 27);

-- 2) Qué tipos de turno ofrece cada médico (pivot, análoga a modulo_medicos)
CREATE TABLE IF NOT EXISTS `medico_tipo_turnos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `medico` INT UNSIGNED NOT NULL,
  `tipo_turno` INT UNSIGNED NOT NULL,
  `activo` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medico_tipo_turnos_medico_foreign` (`medico`),
  KEY `medico_tipo_turnos_tipo_turno_foreign` (`tipo_turno`),
  CONSTRAINT `medico_tipo_turnos_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`),
  CONSTRAINT `medico_tipo_turnos_tipo_turno_foreign` FOREIGN KEY (`tipo_turno`) REFERENCES `tipo_turnos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Registrar las migraciones en la tabla `migrations` de Laravel, para que
--    `php artisan migrate` no intente volver a correrlas (ajustá el valor de
--    `batch` al número más alto actual + 1; consultalo con:
--    SELECT MAX(batch) FROM migrations;)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_05_000000_create_tipo_turnos_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_05_000000_create_tipo_turnos_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_05_000001_create_medico_tipo_turnos_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_05_000001_create_medico_tipo_turnos_table');
