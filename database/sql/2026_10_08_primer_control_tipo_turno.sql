-- Scoping por "primera consulta" para tipos de turno + nuevo tipo "Estudios".
-- Correr una sola vez por entorno (local y luego prod). Es idempotente.

-- 1) Columna primer_control en medico_tipo_turnos (NULL = siempre, 1 = solo primera
--    consulta, 0 = solo consultas de seguimiento). Si ya corriste esta migración
--    (la columna ya existe), el ALTER de abajo fallará con "Duplicate column name" -- en
--    ese caso simplemente no lo corras de nuevo.
ALTER TABLE `medico_tipo_turnos`
  ADD COLUMN `primer_control` TINYINT NULL AFTER `tipo_turno`;

-- Entrevista con padres (26) y Consulta y evaluación (27) quedan acotados a
-- "primera consulta = sí" para los médicos que ya los tengan cargados.
UPDATE `medico_tipo_turnos`
SET `primer_control` = 1
WHERE `tipo_turno` IN (26, 27) AND `primer_control` IS NULL;

-- 2) Nuevo tipo de turno "Estudios" (id 28), para el caso "no es primera consulta".
INSERT INTO `tipo_turnos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`)
SELECT 28, 'Estudios', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tipo_turnos` WHERE `id` = 28);

-- 3) Registrar las migraciones en Laravel para que `php artisan migrate` no las repita.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_08_000000_add_primer_control_to_medico_tipo_turnos_table',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_10_08_000000_add_primer_control_to_medico_tipo_turnos_table'
);
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_08_000001_add_tipo_turno_estudios',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_10_08_000001_add_tipo_turno_estudios'
);

-- Después de correr esto: entrá a /admin_medico para el médico correspondiente y
-- tildá "Estudios" con el selector en "Solo consultas de seguimiento".
