SELECT
  'impplan_duplicate_code' AS check_name,
  COUNT(*) AS issue_count
FROM (
  SELECT form_id, pid, TRIM(code) AS code, COUNT(*) AS c
  FROM form_eye_mag_impplan
  WHERE COALESCE(TRIM(code), '') <> ''
  GROUP BY form_id, pid, TRIM(code)
  HAVING COUNT(*) > 1
) t
UNION ALL
SELECT
  'orphan_locks_gt_4h' AS check_name,
  COUNT(*) AS issue_count
FROM form_eye_locking
WHERE LOCKED = '1'
  AND LOCKEDDATE IS NOT NULL
  AND LOCKEDDATE < (NOW() - INTERVAL 4 HOUR)
UNION ALL
SELECT
  'billing_churn_eye_mag' AS check_name,
  COUNT(*) AS issue_count
FROM (
  SELECT encounter, pid
  FROM billing
  WHERE notecodes = 'eye_mag'
  GROUP BY encounter, pid
  HAVING SUM(CASE WHEN activity = 0 THEN 1 ELSE 0 END) >= 10
     AND SUM(CASE WHEN activity = 1 THEN 1 ELSE 0 END) >= 1
) churn
UNION ALL
SELECT
  'billing_active_duplicate_code' AS check_name,
  COUNT(*) AS issue_count
FROM (
  SELECT encounter, pid, code_type, code, COUNT(*) AS c
  FROM billing
  WHERE notecodes = 'eye_mag' AND activity = 1
  GROUP BY encounter, pid, code_type, code
  HAVING COUNT(*) > 1
) dup_codes;
