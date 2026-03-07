-- EyeMag Lensometry fields for existing databases
-- Run this once on environments that already have form_eye_refraction created.

SET @db_name := DATABASE();

SET @missing_columns := (
    SELECT GROUP_CONCAT(
        CONCAT('ADD COLUMN `', src.column_name, '` ', src.column_type, ' DEFAULT NULL')
        ORDER BY src.sort_order
        SEPARATOR ', '
    )
    FROM (
        SELECT 1 AS sort_order,  'LMODSPH' AS column_name, 'varchar(25)' AS column_type
        UNION ALL SELECT 2,  'LMODCYL',    'varchar(25)'
        UNION ALL SELECT 3,  'LMODAXIS',   'varchar(25)'
        UNION ALL SELECT 4,  'LMODVA',     'varchar(25)'
        UNION ALL SELECT 5,  'LMODADD',    'varchar(25)'
        UNION ALL SELECT 6,  'LMNEARODVA', 'varchar(25)'
        UNION ALL SELECT 7,  'LMODPRISM',  'varchar(50)'
        UNION ALL SELECT 8,  'LMOSSPH',    'varchar(25)'
        UNION ALL SELECT 9,  'LMOSCYL',    'varchar(25)'
        UNION ALL SELECT 10, 'LMOSAXIS',   'varchar(25)'
        UNION ALL SELECT 11, 'LMOSVA',     'varchar(25)'
        UNION ALL SELECT 12, 'LMOSADD',    'varchar(25)'
        UNION ALL SELECT 13, 'LMNEAROSVA', 'varchar(25)'
        UNION ALL SELECT 14, 'LMOSPRISM',  'varchar(50)'
    ) AS src
    LEFT JOIN information_schema.COLUMNS c
        ON c.TABLE_SCHEMA = @db_name
       AND c.TABLE_NAME = 'form_eye_refraction'
       AND c.COLUMN_NAME = src.column_name
    WHERE c.COLUMN_NAME IS NULL
);

SET @alter_stmt := IF(
    @missing_columns IS NULL OR @missing_columns = '',
    'SELECT ''form_eye_refraction already has lensometry columns'' AS status_msg',
    CONCAT('ALTER TABLE `form_eye_refraction` ', @missing_columns)
);

PREPARE s1 FROM @alter_stmt;
EXECUTE s1;
DEALLOCATE PREPARE s1;
