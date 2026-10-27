-- Fix Now — registro de técnicos (cédula, correo, login)
-- Uso: /Applications/XAMPP/xamppfiles/bin/mysql -u root fixnow < bd/migracion_tecnico_registro.sql

USE fixnow;

SET @has_cedula := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tecnico' AND COLUMN_NAME = 'cedula'
);

SET @sql := IF(@has_cedula = 0,
  'ALTER TABLE tecnico
     ADD COLUMN cedula VARCHAR(20) NULL AFTER apellido,
     ADD COLUMN correo VARCHAR(150) NULL AFTER telefono,
     ADD COLUMN password_hash VARCHAR(255) NULL AFTER correo',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tecnico
SET cedula = CONCAT('100000', LPAD(id_tecnico, 5, '0'))
WHERE cedula IS NULL OR cedula = '';

ALTER TABLE tecnico
  MODIFY cedula VARCHAR(20) NOT NULL;

SET @has_uq_cedula := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tecnico' AND INDEX_NAME = 'uq_tecnico_cedula'
);
SET @sql := IF(@has_uq_cedula = 0,
  'ALTER TABLE tecnico ADD UNIQUE KEY uq_tecnico_cedula (cedula)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_uq_correo := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tecnico' AND INDEX_NAME = 'uq_tecnico_correo'
);
SET @sql := IF(@has_uq_correo = 0,
  'ALTER TABLE tecnico ADD UNIQUE KEY uq_tecnico_correo (correo)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
