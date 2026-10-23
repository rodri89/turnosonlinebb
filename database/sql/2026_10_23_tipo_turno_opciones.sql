-- Tipo de turno "con opciones" (tipo_turno_opciones) + generalizacion del radio-group que
-- antes estaba hardcodeado para el medico 13 (Lucas Sosa), aplicada tambien a Nadia (42).
-- Equivale a las migraciones:
--   2026_10_23_000000_make_tipo_turnos_id_autoincrement
--   2026_10_23_000001_create_tipo_turno_opciones_table
--   2026_10_23_000002_migrar_consulta_con_opciones
-- Correr una sola vez por entorno (ya corrido en local via `php artisan migrate`, ahora en prod).
-- Es idempotente: se puede volver a correr sin romper nada si se corta a mitad de camino.
-- Requiere que ya esten corridas: 2026_10_05_tipo_turnos.sql y 2026_10_08_primer_control_tipo_turno.sql
-- (si `SELECT migration FROM migrations WHERE migration LIKE '2026_10%'` no las muestra, correlas primero).

-- ============================================================
-- 1) tipo_turnos.id pasa a ser autoincremental (para poder crear tipos nuevos desde el admin)
-- ============================================================
ALTER TABLE `medico_tipo_turnos` DROP FOREIGN KEY `medico_tipo_turnos_tipo_turno_foreign`;
ALTER TABLE `tipo_turnos` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `medico_tipo_turnos` ADD CONSTRAINT `medico_tipo_turnos_tipo_turno_foreign` FOREIGN KEY (`tipo_turno`) REFERENCES `tipo_turnos` (`id`);

SET @max_tipo_turno_id = (SELECT IFNULL(MAX(id), 0) FROM `tipo_turnos`);
SET @sql_autoincrement = CONCAT('ALTER TABLE `tipo_turnos` AUTO_INCREMENT = ', @max_tipo_turno_id + 1);
PREPARE stmt_autoincrement FROM @sql_autoincrement;
EXECUTE stmt_autoincrement;
DEALLOCATE PREPARE stmt_autoincrement;

-- ============================================================
-- 2) tipo_turno_opciones: opciones de un tipo "padre", por medico. El horario fijo queda
--    asociado al padre; el paciente elige la opcion concreta recien al confirmar el turno.
-- ============================================================
CREATE TABLE IF NOT EXISTS `tipo_turno_opciones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `medico` INT UNSIGNED NOT NULL,
  `tipo_turno_padre` INT UNSIGNED NOT NULL,
  `tipo_turno_opcion` INT UNSIGNED NOT NULL,
  `activo` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo_turno_opciones_unicas` (`medico`, `tipo_turno_padre`, `tipo_turno_opcion`),
  KEY `tipo_turno_opciones_medico_foreign` (`medico`),
  KEY `tipo_turno_opciones_tipo_turno_padre_foreign` (`tipo_turno_padre`),
  KEY `tipo_turno_opciones_tipo_turno_opcion_foreign` (`tipo_turno_opcion`),
  CONSTRAINT `tipo_turno_opciones_medico_foreign` FOREIGN KEY (`medico`) REFERENCES `medicos` (`id`),
  CONSTRAINT `tipo_turno_opciones_tipo_turno_padre_foreign` FOREIGN KEY (`tipo_turno_padre`) REFERENCES `tipo_turnos` (`id`),
  CONSTRAINT `tipo_turno_opciones_tipo_turno_opcion_foreign` FOREIGN KEY (`tipo_turno_opcion`) REFERENCES `tipo_turnos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3) Datos: Nadia (42) pasa de 3 tipos sueltos (26/27/28) a un tipo padre unico "Consulta
--    con opciones", con esos 3 como opciones elegibles al confirmar el turno.
-- ============================================================
INSERT INTO `tipo_turnos` (`nombre`, `activo`, `created_at`, `updated_at`)
SELECT 'Consulta con opciones', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `nombre` = 'Consulta con opciones');

SET @padre_nadia = (SELECT id FROM `tipo_turnos` WHERE `nombre` = 'Consulta con opciones' LIMIT 1);

UPDATE `horario_medicos`
SET `tipo_turno` = @padre_nadia
WHERE `medico` = 42 AND `tipo_turno` IN (26, 27, 28) AND `activo` = 1;

-- guardar(42, [padre]): deja habilitado solo el padre en medico_tipo_turnos (desactiva 26/27/28 sueltos)
UPDATE `medico_tipo_turnos`
SET `activo` = 0, `updated_at` = NOW()
WHERE `medico` = 42 AND `tipo_turno` <> @padre_nadia;

INSERT INTO `medico_tipo_turnos` (`medico`, `tipo_turno`, `primer_control`, `activo`, `created_at`, `updated_at`)
SELECT 42, @padre_nadia, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `medico_tipo_turnos` WHERE `medico` = 42 AND `tipo_turno` = @padre_nadia);

UPDATE `medico_tipo_turnos`
SET `activo` = 1, `primer_control` = NULL, `updated_at` = NOW()
WHERE `medico` = 42 AND `tipo_turno` = @padre_nadia;

-- guardarOpciones(42, padre, [26,27,28])
INSERT INTO `tipo_turno_opciones` (`medico`, `tipo_turno_padre`, `tipo_turno_opcion`, `activo`, `created_at`, `updated_at`)
VALUES
  (42, @padre_nadia, 26, 1, NOW(), NOW()),
  (42, @padre_nadia, 27, 1, NOW(), NOW()),
  (42, @padre_nadia, 28, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `activo` = 1, `updated_at` = NOW();

-- ============================================================
-- 4) Datos: Lucas Sosa (13) ya tiene todos sus horarios en tipo_turno=1 ("Consulta"), que pasa
--    a ser el padre. Se crean 3 opciones nuevas (no se reusan 1/23/25: tienen otro nombre en el
--    catalogo global y 23 se usa como tipo reservable normal en otros medicos).
-- ============================================================
INSERT INTO `tipo_turnos` (`nombre`, `activo`, `created_at`, `updated_at`)
SELECT 'Consulta endocrina', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `nombre` = 'Consulta endocrina');

INSERT INTO `tipo_turnos` (`nombre`, `activo`, `created_at`, `updated_at`)
SELECT 'Ecografía', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `nombre` = 'Ecografía');

INSERT INTO `tipo_turnos` (`nombre`, `activo`, `created_at`, `updated_at`)
SELECT 'Consulta endocrina + Ecografía', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `nombre` = 'Consulta endocrina + Ecografía');

SET @op_endocrina = (SELECT id FROM `tipo_turnos` WHERE `nombre` = 'Consulta endocrina' LIMIT 1);
SET @op_ecografia = (SELECT id FROM `tipo_turnos` WHERE `nombre` = 'Ecografía' LIMIT 1);
SET @op_endocrina_eco = (SELECT id FROM `tipo_turnos` WHERE `nombre` = 'Consulta endocrina + Ecografía' LIMIT 1);

INSERT INTO `tipo_turno_opciones` (`medico`, `tipo_turno_padre`, `tipo_turno_opcion`, `activo`, `created_at`, `updated_at`)
VALUES
  (13, 1, @op_endocrina, 1, NOW(), NOW()),
  (13, 1, @op_ecografia, 1, NOW(), NOW()),
  (13, 1, @op_endocrina_eco, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `activo` = 1, `updated_at` = NOW();

-- ============================================================
-- 5) Registrar las migraciones en Laravel para que `php artisan migrate` no las repita.
-- ============================================================
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_23_000000_make_tipo_turnos_id_autoincrement',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_23_000000_make_tipo_turnos_id_autoincrement'
);
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_23_000001_create_tipo_turno_opciones_table',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_23_000001_create_tipo_turno_opciones_table'
);
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_23_000002_migrar_consulta_con_opciones',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_23_000002_migrar_consulta_con_opciones'
);

-- Después de correr esto: Nadia y Lucas Sosa ya quedan funcionando igual que en local
-- (padre "Consulta con opciones" / "Consulta" con sus 3 opciones). Para cualquier otro medico
-- que quieras agregar a este mecanismo, usalo desde /admin_medico (crear tipo de turno,
-- tildar opciones) - no hace falta tocar la base a mano.
