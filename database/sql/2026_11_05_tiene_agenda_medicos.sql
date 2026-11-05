-- "Tiene agenda de turnos" por medico (Salud 360, spec 005), columna propia e independiente
-- de `castigo_automatico` ("Mostrar Medico": 1 = el paciente lo ve en la web).
-- Equivale a la migracion: 2026_11_05_000000_add_tiene_agenda_to_medicos_table
-- Correr una sola vez por entorno. Es idempotente: si se corta a mitad de camino, se puede
-- volver a correr sin romper nada (a diferencia de un ALTER simple, usa information_schema
-- para no reintentar el ALTER ni pisar ediciones del administrador en una segunda corrida).

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'medicos' AND COLUMN_NAME = 'tiene_agenda'
);

SET @sql_add_tiene_agenda = IF(@col_exists = 0,
    'ALTER TABLE `medicos` ADD COLUMN `tiene_agenda` TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT 1'
);
PREPARE stmt_add_tiene_agenda FROM @sql_add_tiene_agenda;
EXECUTE stmt_add_tiene_agenda;
DEALLOCATE PREPARE stmt_add_tiene_agenda;

-- Relleno por horarios (mismo criterio que `tiene_horarios` en catalogos): 1 si el medico
-- tiene algun horario_medicos activo, 0 si no. Se incluyen tambien las fichas de baja.
-- Solo corre la primera vez que se agrega la columna (si ya existia, no se toca: puede
-- haberla editado el administrador a mano).
UPDATE `medicos`
SET `tiene_agenda` = CASE WHEN EXISTS (
    SELECT 1 FROM `horario_medicos` h WHERE h.medico = medicos.id AND h.activo = 1
) THEN 1 ELSE 0 END
WHERE @col_exists = 0;

-- Registrar la migracion en Laravel para que `php artisan migrate` no la repita.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_05_000000_add_tiene_agenda_to_medicos_table',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_05_000000_add_tiene_agenda_to_medicos_table'
);
