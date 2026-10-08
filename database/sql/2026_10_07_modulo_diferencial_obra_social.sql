-- Módulo "Diferencial por obra social" (id 13), configurable por médico.
-- Correr una sola vez por entorno (local y luego prod). Es idempotente.

INSERT INTO `modulos` (`id`, `descripcion`, `activo`, `created_at`, `updated_at`)
SELECT 13, 'Diferencial por obra social', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `modulos` WHERE `id` = 13);

-- Registrar la migración en Laravel para que `php artisan migrate` no la vuelva a correr.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_07_000000_add_modulo_diferencial_obra_social_to_modulos_table',
       (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_10_07_000000_add_modulo_diferencial_obra_social_to_modulos_table'
);

-- OPCIONAL: si querés preservar el comportamiento actual para los médicos que ya
-- mostraban este aviso hardcodeado (médico 2 = Deportología, médico 39), destildá
-- el comentario de abajo y corré esto también. Si preferís activarlos a mano desde
-- /admin_medico, no hace falta correr este bloque.
--
-- INSERT INTO `modulo_medicos` (`medico`, `modulo`, `activo`, `created_at`, `updated_at`)
-- SELECT m.id, 13, 1, NOW(), NOW()
-- FROM (SELECT 2 AS id UNION SELECT 39) AS m
-- WHERE NOT EXISTS (
--     SELECT 1 FROM `modulo_medicos` WHERE `medico` = m.id AND `modulo` = 13 AND `activo` = 1
-- );
