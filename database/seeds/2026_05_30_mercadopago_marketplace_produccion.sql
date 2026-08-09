-- =============================================================================
-- Mercado Pago Marketplace — cobro de turnos presenciales (módulo 12)
-- Ejecutar en MySQL de PRODUCCIÓN (phpMyAdmin, consola, etc.)
-- Script idempotente: se puede correr más de una vez sin romper datos existentes.
-- =============================================================================

-- 1) Módulo 12 en tabla modulos
INSERT INTO modulos (id, descripcion, activo, created_at, updated_at)
SELECT 12, 'Cobro turnos MercadoPago', 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM modulos WHERE id = 12);

-- 2) Configuración global de la plataforma
CREATE TABLE IF NOT EXISTS mercadopago_platform_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    platform_commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    mp_app_id VARCHAR(255) NULL,
    mp_client_secret TEXT NULL,
    mode VARCHAR(20) NOT NULL DEFAULT 'sandbox',
    integrator_access_token TEXT NULL,
    integrator_public_key VARCHAR(255) NULL,
    mp_commission_fallback_percent DECIMAL(5,2) NOT NULL DEFAULT 0.8,
    checkout_description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO mercadopago_platform_settings (
    platform_commission_percent, mode, mp_commission_fallback_percent,
    checkout_description, created_at, updated_at
)
SELECT 5, 'production', 0.8, 'Reserva de turno online', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mercadopago_platform_settings LIMIT 1);

-- 3) Cuentas OAuth de cada médico
CREATE TABLE IF NOT EXISTS medico_mercadopago_accounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    medico_id INT UNSIGNED NOT NULL,
    access_token TEXT NULL,
    refresh_token TEXT NULL,
    expires_at TIMESTAMP NULL,
    collector_id VARCHAR(255) NULL,
    mp_user_id VARCHAR(255) NULL,
    cobro_activo TINYINT NOT NULL DEFAULT 0,
    importe_reserva DECIMAL(10,2) NOT NULL DEFAULT 0,
    linked_at TIMESTAMP NULL,
    mode VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY medico_mercadopago_accounts_medico_id_unique (medico_id),
    CONSTRAINT medico_mercadopago_accounts_medico_id_foreign
        FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Intents de pago (hold del turno hasta webhook)
CREATE TABLE IF NOT EXISTS turno_pago_intents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT UNSIGNED NOT NULL,
    medico_id INT UNSIGNED NOT NULL,
    consultorio_id INT UNSIGNED NOT NULL,
    dia TINYINT UNSIGNED NOT NULL,
    horario VARCHAR(10) NOT NULL,
    fecha_turno DATE NOT NULL,
    primer_control VARCHAR(5) NOT NULL DEFAULT 'NO',
    tipo_turno TINYINT UNSIGNED NOT NULL DEFAULT 0,
    especialidad VARCHAR(255) NULL,
    amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    preference_id VARCHAR(255) NULL,
    payment_id VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    turno_registrado_id INT UNSIGNED NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY turno_pago_intents_slot_status_idx (medico_id, consultorio_id, dia, horario, fecha_turno, status),
    KEY turno_pago_intents_preference_id_index (preference_id),
    KEY turno_pago_intents_payment_id_index (payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Campos de pago en turno_registrados
SET @db = DATABASE();

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'turno_registrados' AND COLUMN_NAME = 'pago') = 0,
    'ALTER TABLE turno_registrados ADD COLUMN pago TINYINT NOT NULL DEFAULT 0 AFTER activo',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'turno_registrados' AND COLUMN_NAME = 'pago_estado') = 0,
    'ALTER TABLE turno_registrados ADD COLUMN pago_estado VARCHAR(30) NULL AFTER pago',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'turno_registrados' AND COLUMN_NAME = 'mercadopago_payment_id') = 0,
    'ALTER TABLE turno_registrados ADD COLUMN mercadopago_payment_id VARCHAR(255) NULL AFTER pago_estado',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'turno_registrados' AND COLUMN_NAME = 'mercadopago_preference_id') = 0,
    'ALTER TABLE turno_registrados ADD COLUMN mercadopago_preference_id VARCHAR(255) NULL AFTER mercadopago_payment_id',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'turno_registrados' AND COLUMN_NAME = 'importe_reserva') = 0,
    'ALTER TABLE turno_registrados ADD COLUMN importe_reserva DECIMAL(10,2) NULL AFTER mercadopago_preference_id',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'turno_registrados' AND COLUMN_NAME = 'turno_pago_intent_id') = 0,
    'ALTER TABLE turno_registrados ADD COLUMN turno_pago_intent_id BIGINT UNSIGNED NULL AFTER importe_reserva',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6) Registrar migraciones en Laravel (solo si usás php artisan migrate después, podés omitir este bloque)
SET @batch = (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO migrations (migration, batch)
SELECT '2026_05_30_000001_add_modulo_cobro_turnos_mp_to_modulos_table', @batch
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM migrations WHERE migration = '2026_05_30_000001_add_modulo_cobro_turnos_mp_to_modulos_table'
);

INSERT INTO migrations (migration, batch)
SELECT '2026_05_30_000002_create_mercadopago_platform_settings_table', @batch
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM migrations WHERE migration = '2026_05_30_000002_create_mercadopago_platform_settings_table'
);

INSERT INTO migrations (migration, batch)
SELECT '2026_05_30_000003_create_medico_mercadopago_accounts_table', @batch
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM migrations WHERE migration = '2026_05_30_000003_create_medico_mercadopago_accounts_table'
);

INSERT INTO migrations (migration, batch)
SELECT '2026_05_30_000004_create_turno_pago_intents_table', @batch
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM migrations WHERE migration = '2026_05_30_000004_create_turno_pago_intents_table'
);

INSERT INTO migrations (migration, batch)
SELECT '2026_05_30_000005_add_pago_fields_to_turno_registrados_table', @batch
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM migrations WHERE migration = '2026_05_30_000005_add_pago_fields_to_turno_registrados_table'
);

-- =============================================================================
-- POST-SQL (manual):
-- 1) Admin → /admin/mercadopago/settings → credenciales MP producción + comisión
-- 2) Alta médico → activar módulo 12 para el médico piloto
-- 3) Médico → Pagos / Mercado Pago → importe + OAuth + generar link de prueba
-- 4) Webhook MP: POST https://TU-DOMINIO/api/webhooks/mercadopago
-- 5) OAuth redirect: https://TU-DOMINIO/medico/oauth/mercadopago/callback
-- =============================================================================
