# Idempotent schema update for WhatsApp consent field
# Safe to run multiple times.

SET @db := DATABASE();

SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'patient_data'
      AND COLUMN_NAME = 'hipaa_allowwhatsapp'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE patient_data ADD COLUMN hipaa_allowwhatsapp VARCHAR(3) NOT NULL DEFAULT ''NO'' AFTER hipaa_allowsms',
    'SELECT ''patient_data.hipaa_allowwhatsapp already exists'' AS msg'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
