-- Script idempotente para PROD
-- 1) Agrega vigencia a horario_medicos (valido_desde / valido_hasta)
-- 2) Crea medico_mensajes_especiales
-- 3) Inserta mensajes especiales base

-- ---------------------------------------------------------------------------
-- 1) horario_medicos: columnas de vigencia
-- ---------------------------------------------------------------------------
SET @sql_valido_desde := IF(
  EXISTS(
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'horario_medicos'
      AND COLUMN_NAME = 'valido_desde'
  ),
  'SELECT ''columna valido_desde ya existe'';',
  'ALTER TABLE horario_medicos ADD COLUMN valido_desde DATE NULL AFTER activo;'
);
PREPARE stmt FROM @sql_valido_desde;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql_valido_hasta := IF(
  EXISTS(
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'horario_medicos'
      AND COLUMN_NAME = 'valido_hasta'
  ),
  'SELECT ''columna valido_hasta ya existe'';',
  'ALTER TABLE horario_medicos ADD COLUMN valido_hasta DATE NULL AFTER valido_desde;'
);
PREPARE stmt FROM @sql_valido_hasta;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- 2) Tabla medico_mensajes_especiales
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medico_mensajes_especiales (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  medico_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT NOT NULL,
  valido_desde DATE NULL,
  valido_hasta DATE NULL,
  activo INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_medico_activo (medico_id, activo),
  KEY idx_vigencia (valido_desde, valido_hasta),
  CONSTRAINT fk_medico_mensajes_medico
    FOREIGN KEY (medico_id) REFERENCES medicos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 3) Mensajes base (evita duplicados exactos)
-- ---------------------------------------------------------------------------
INSERT INTO medico_mensajes_especiales
  (medico_id, titulo, descripcion, valido_desde, valido_hasta, activo, created_at, updated_at)
SELECT
  12, 'Aviso',
  'Antes de asistir a la consulta por favor consulte por la cobertura de su obra social. Muchas Gracias.',
  NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM medico_mensajes_especiales
  WHERE medico_id = 12
    AND titulo = 'Aviso'
    AND descripcion = 'Antes de asistir a la consulta por favor consulte por la cobertura de su obra social. Muchas Gracias.'
);

INSERT INTO medico_mensajes_especiales
  (medico_id, titulo, descripcion, valido_desde, valido_hasta, activo, created_at, updated_at)
SELECT
  35, 'Aviso',
  'El especialista no trabaja con las obras sociales, solo atiende de manera PARTICULAR. Muchas Gracias.',
  NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM medico_mensajes_especiales
  WHERE medico_id = 35
    AND titulo = 'Aviso'
    AND descripcion = 'El especialista no trabaja con las obras sociales, solo atiende de manera PARTICULAR. Muchas Gracias.'
);

INSERT INTO medico_mensajes_especiales
  (medico_id, titulo, descripcion, valido_desde, valido_hasta, activo, created_at, updated_at)
SELECT
  15, 'Aviso',
  'El modo de pago para las consultas es unicamente en EFECTIVO. Muchas Gracias.',
  NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM medico_mensajes_especiales
  WHERE medico_id = 15
    AND titulo = 'Aviso'
    AND descripcion = 'El modo de pago para las consultas es unicamente en EFECTIVO. Muchas Gracias.'
);

INSERT INTO medico_mensajes_especiales
  (medico_id, titulo, descripcion, valido_desde, valido_hasta, activo, created_at, updated_at)
SELECT
  3, 'Aviso',
  'A partir de Enero 2025 el profesional NO va a trabajar mas con la obra social IOSFA. Muchas Gracias!',
  '2025-01-01', NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM medico_mensajes_especiales
  WHERE medico_id = 3
    AND titulo = 'Aviso'
    AND descripcion = 'A partir de Enero 2025 el profesional NO va a trabajar mas con la obra social IOSFA. Muchas Gracias!'
);

INSERT INTO medico_mensajes_especiales
  (medico_id, titulo, descripcion, valido_desde, valido_hasta, activo, created_at, updated_at)
SELECT
  18, 'Aviso',
  'Por excepción las atención del viernes 2 de mayo se atenderá durante la mañana y no a la tarde! Para solicitar turno comunicarse al telefono 4814538 o <a href=''https://api.whatsapp.com/send?phone=2915107335'' target=''_blank''>2915107335</a> (Lunes, martes y jueves de 15 a 19, miércoles de 9:30 a 15)Saludos!!',
  NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM medico_mensajes_especiales
  WHERE medico_id = 18
    AND titulo = 'Aviso'
    AND descripcion LIKE 'Por excepción las atención del viernes 2 de mayo%'
);

INSERT INTO medico_mensajes_especiales
  (medico_id, titulo, descripcion, valido_desde, valido_hasta, activo, created_at, updated_at)
SELECT
  1, 'Aviso',
  'Queridas familias<br><br>Por el momento no me encontraré atendiendo en el consultorio debido a mi licencia por embarazo.<br><br>Quiero agradecerles profundamente la confianza de siempre y el cariño brindado en cada consulta. Oportunamente estaré informando mi regreso y la reanudación de la atención.<br><br>Les envío un afectuoso saludo. Flor',
  NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM medico_mensajes_especiales
  WHERE medico_id = 1
    AND titulo = 'Aviso'
    AND descripcion LIKE 'Queridas familias%'
);

