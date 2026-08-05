SET NAMES utf8;
DROP TEMPORARY TABLE IF EXISTS tmp_cie10_sync;
CREATE TEMPORARY TABLE tmp_cie10_sync (
  dx_code varchar(7) COLLATE utf8_general_ci NOT NULL PRIMARY KEY,
  formatted_dx_code varchar(10) COLLATE utf8_general_ci NOT NULL,
  label varchar(300) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

LOAD DATA LOCAL INFILE '/Users/jorgeluisdevera/PhpstormProjects/altavision52/tmp_cie10_icd10_load.tsv'
INTO TABLE tmp_cie10_sync
CHARACTER SET utf8
FIELDS TERMINATED BY '\t'
LINES TERMINATED BY '\n'
(dx_code, formatted_dx_code, label);

SELECT COUNT(*) AS excel_codes_loaded FROM tmp_cie10_sync;

START TRANSACTION;

INSERT INTO icd10_dx_order_code (dx_code, formatted_dx_code, valid_for_coding, short_desc, long_desc, active, revision)
SELECT x.dx_code, x.formatted_dx_code, '1', x.label, x.label, 1, 1
FROM tmp_cie10_sync x
WHERE NOT EXISTS (
  SELECT 1 FROM icd10_dx_order_code d WHERE d.dx_code = x.dx_code
);

SELECT ROW_COUNT() AS rows_inserted;

UPDATE icd10_dx_order_code d
JOIN tmp_cie10_sync x ON x.dx_code = d.dx_code
SET d.formatted_dx_code = x.formatted_dx_code,
    d.valid_for_coding = '1',
    d.short_desc = x.label,
    d.long_desc = x.label,
    d.active = 1,
    d.revision = 1;

SELECT ROW_COUNT() AS excel_rows_updated_or_changed;

UPDATE icd10_dx_order_code d
LEFT JOIN tmp_cie10_sync x ON x.dx_code = d.dx_code
SET d.active = 0,
    d.revision = 1
WHERE x.dx_code IS NULL;

SELECT ROW_COUNT() AS non_excel_rows_deactivated_or_changed;

COMMIT;

SELECT COUNT(*) AS final_total_rows FROM icd10_dx_order_code;
SELECT SUM(active = 1) AS final_active_rows, SUM(active = 0) AS final_inactive_rows FROM icd10_dx_order_code;
SELECT COUNT(*) AS active_excel_codes FROM icd10_dx_order_code d JOIN tmp_cie10_sync x ON x.dx_code = d.dx_code WHERE d.active = 1;
SELECT COUNT(*) AS inactive_codes_not_in_excel FROM icd10_dx_order_code d LEFT JOIN tmp_cie10_sync x ON x.dx_code = d.dx_code WHERE x.dx_code IS NULL AND d.active = 0;
SELECT dx_code, formatted_dx_code, short_desc, long_desc, active, revision FROM icd10_dx_order_code WHERE dx_code IN ('A251','A26354','A27','A00','Z999') ORDER BY dx_code;
