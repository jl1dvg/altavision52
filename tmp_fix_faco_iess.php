<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mode = $argv[1] ?? '--dry-run';
if (!in_array($mode, ['--dry-run', '--apply'], true)) {
    fwrite(STDERR, "Usage: php tmp_fix_faco_iess.php [--dry-run|--apply]\n");
    exit(1);
}

$db = new mysqli('35.192.95.218', 'jl1dvg', 'JorgeAMI2018', 'altavision', 3306);
$db->set_charset('utf8mb4');

function parsePackageCodes(string $fsCodes): array
{
    $items = [];
    $orderedKeys = [];
    foreach (explode('~', $fsCodes) as $entry) {
        if ($entry === '') {
            continue;
        }
        $parts = explode('|', $entry);
        $codeType = $parts[0] ?? '';
        $rawCode = $parts[1] ?? '';
        if ($codeType === '' || $rawCode === '') {
            continue;
        }
        $modifier = '';
        $code = $rawCode;
        if (strpos($rawCode, ':') !== false) {
            [$code, $modifier] = explode(':', $rawCode, 2);
        }
        $key = implode('|', [$codeType, $code, $modifier]);
        if (!isset($items[$key])) {
            $items[$key] = [
                'code_type' => $codeType,
                'code' => $code,
                'modifier' => $modifier,
                'units' => 0,
            ];
            $orderedKeys[] = $key;
        }
        $items[$key]['units']++;
    }

    $orderedItems = [];
    foreach ($orderedKeys as $key) {
        $orderedItems[] = $items[$key];
    }

    return $orderedItems;
}

function fetchPackageDefinition(mysqli $db): array
{
    $pkgStmt = $db->prepare("SELECT fs_codes FROM fee_sheet_options WHERE fs_option = '1Faco - IESS' LIMIT 1");
    $pkgStmt->execute();
    $pkg = $pkgStmt->get_result()->fetch_assoc();
    if (!$pkg) {
        throw new RuntimeException("Package '1Faco - IESS' not found.");
    }

    $items = parsePackageCodes($pkg['fs_codes']);
    $metaStmt = $db->prepare(
        "SELECT
            c.code_text,
            COALESCE(pr.pr_price, 0) AS unit_price
         FROM code_types ct
         JOIN codes c
           ON c.code_type = ct.ct_id
          AND c.code = ?
         LEFT JOIN prices pr
           ON pr.pr_id = c.id
          AND pr.pr_level = 'IESS'
         WHERE ct.ct_key = ?
         ORDER BY c.modifier
         LIMIT 1"
    );

    foreach ($items as &$item) {
        $metaStmt->bind_param('ss', $item['code'], $item['code_type']);
        $metaStmt->execute();
        $row = $metaStmt->get_result()->fetch_assoc();
        if (!$row) {
            throw new RuntimeException("Missing code metadata for {$item['code_type']} {$item['code']}.");
        }
        $item['code_text'] = $row['code_text'];
        $item['unit_price'] = (float) $row['unit_price'];
        $item['fee'] = round($item['units'] * $item['unit_price'], 2);
    }
    unset($item);

    return $items;
}

function fetchAffectedEncounters(mysqli $db): array
{
    $sql = <<<SQL
SELECT
    base.pid,
    base.encounter,
    base.form_id,
    base.protocol_date,
    COALESCE(t.date, base.protocol_date) AS billing_date,
    COALESCE(t.provider_id, base.provider_id, 0) AS provider_id,
    COALESCE(t.user, 0) AS billing_user,
    COALESCE(t.groupname, 'Default') AS groupname,
    COALESCE(t.authorized, 1) AS authorized,
    COALESCE(t.pricelevel, 'IESS') AS pricelevel
FROM (
    SELECT
        f.pid,
        f.encounter,
        MAX(f.form_id) AS form_id,
        MAX(CONCAT(DATE(f.date), ' 00:00:00')) AS protocol_date,
        MAX(fe.provider_id) AS provider_id
    FROM forms f
    JOIN lbf_data d
      ON d.form_id = f.form_id
     AND d.field_id = 'Prot_opr'
     AND d.field_value = 'faco'
    JOIN patient_data p
      ON p.pid = f.pid
    LEFT JOIN form_encounter fe
      ON fe.pid = f.pid
     AND fe.encounter = f.encounter
    WHERE f.formdir = 'LBFprotocolo'
      AND f.deleted = 0
      AND p.pricelevel = 'IESS'
      AND DATE(f.date) >= '2026-05-01'
      AND DATE(f.date) <= CURRENT_DATE()
    GROUP BY f.pid, f.encounter
) base
LEFT JOIN (
    SELECT b1.*
    FROM billing b1
    JOIN (
        SELECT pid, encounter, MIN(id) AS first_id
        FROM billing
        WHERE activity = 1
        GROUP BY pid, encounter
    ) pick
      ON pick.first_id = b1.id
) t
  ON t.pid = base.pid
 AND t.encounter = base.encounter
ORDER BY base.protocol_date, base.pid, base.encounter
SQL;

    $result = $db->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function fetchCurrentBillingSummary(mysqli $db): array
{
    $sql = <<<SQL
SELECT
    b.pid,
    b.encounter,
    COUNT(*) AS row_count,
    SUM(b.units) AS total_units,
    ROUND(SUM(b.fee), 2) AS total_fee
FROM billing b
JOIN (
    SELECT DISTINCT f.pid, f.encounter
    FROM forms f
    JOIN lbf_data d
      ON d.form_id = f.form_id
     AND d.field_id = 'Prot_opr'
     AND d.field_value = 'faco'
    JOIN patient_data p
      ON p.pid = f.pid
    WHERE f.formdir = 'LBFprotocolo'
      AND f.deleted = 0
      AND p.pricelevel = 'IESS'
      AND DATE(f.date) >= '2026-05-01'
      AND DATE(f.date) <= CURRENT_DATE()
) a
  ON a.pid = b.pid
 AND a.encounter = b.encounter
WHERE b.activity = 1
GROUP BY b.pid, b.encounter
SQL;

    $result = $db->query($sql);
    $summary = [];
    while ($row = $result->fetch_assoc()) {
        $summary[$row['pid'] . '-' . $row['encounter']] = $row;
    }
    return $summary;
}

$packageItems = fetchPackageDefinition($db);
$encounters = fetchAffectedEncounters($db);
$currentSummary = fetchCurrentBillingSummary($db);

$expectedRowCount = count($packageItems);
$expectedUnits = array_sum(array_column($packageItems, 'units'));
$expectedFee = round(array_sum(array_column($packageItems, 'fee')), 2);

echo "PACKAGE_SUMMARY\trows={$expectedRowCount}\tunits={$expectedUnits}\tfee={$expectedFee}\n";
echo "AFFECTED_ENCOUNTERS\t" . count($encounters) . "\n";

$sample = array_slice($encounters, 0, 5);
echo "SAMPLE_ENCOUNTERS\n";
foreach ($sample as $row) {
    $key = $row['pid'] . '-' . $row['encounter'];
    $current = $currentSummary[$key] ?? ['row_count' => 0, 'total_units' => 0, 'total_fee' => 0];
    echo implode("\t", [
        $row['pid'],
        $row['encounter'],
        $row['form_id'],
        $row['protocol_date'],
        $current['row_count'],
        $current['total_units'],
        $current['total_fee'],
    ]) . "\n";
}

if ($mode === '--dry-run') {
    $activeRows = 0;
    $activeUnits = 0;
    $activeFee = 0.0;
    foreach ($currentSummary as $current) {
        $activeRows += (int) $current['row_count'];
        $activeUnits += (int) $current['total_units'];
        $activeFee += (float) $current['total_fee'];
    }
    echo "CURRENT_ACTIVE_TOTALS\trows={$activeRows}\tunits={$activeUnits}\tfee=" . round($activeFee, 2) . "\n";
    echo "NEW_EXPECTED_TOTALS\trows=" . (count($encounters) * $expectedRowCount) . "\tunits=" . (count($encounters) * $expectedUnits) . "\tfee=" . round(count($encounters) * $expectedFee, 2) . "\n";
    exit(0);
}

$backupTable = 'billing_backup_faco_iess_' . date('Ymd_His');
$backupMetaPath = __DIR__ . '/tmp_fix_faco_iess_' . date('Ymd_His') . '.json';

$db->query("DROP TEMPORARY TABLE IF EXISTS tmp_faco_iess_encounters");
$db->query(
    "CREATE TEMPORARY TABLE tmp_faco_iess_encounters (
        pid BIGINT NOT NULL,
        encounter INT NOT NULL,
        form_id INT NOT NULL,
        billing_date DATETIME NULL,
        provider_id INT NULL,
        billing_user INT NULL,
        groupname VARCHAR(255) NULL,
        authorized TINYINT NULL,
        pricelevel VARCHAR(31) NULL,
        PRIMARY KEY (pid, encounter)
    )"
);

$insertEncounter = $db->prepare(
    "INSERT INTO tmp_faco_iess_encounters
        (pid, encounter, form_id, billing_date, provider_id, billing_user, groupname, authorized, pricelevel)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

foreach ($encounters as $row) {
    $insertEncounter->bind_param(
        'iiisiisis',
        $row['pid'],
        $row['encounter'],
        $row['form_id'],
        $row['billing_date'],
        $row['provider_id'],
        $row['billing_user'],
        $row['groupname'],
        $row['authorized'],
        $row['pricelevel']
    );
    $insertEncounter->execute();
}

$db->query(
    "CREATE TABLE {$backupTable} AS
     SELECT b.*, NOW() AS backup_ts, 'faco_iess_fix' AS backup_batch
       FROM billing b
       JOIN tmp_faco_iess_encounters t
         ON t.pid = b.pid
        AND t.encounter = b.encounter
      WHERE b.activity = 1"
);

file_put_contents($backupMetaPath, json_encode([
    'created_at' => date('c'),
    'backup_table' => $backupTable,
    'encounters' => count($encounters),
    'expected_rows_per_encounter' => $expectedRowCount,
    'expected_units_per_encounter' => $expectedUnits,
    'expected_fee_per_encounter' => $expectedFee,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$db->begin_transaction();

try {
    $db->query(
        "UPDATE billing b
         JOIN tmp_faco_iess_encounters t
           ON t.pid = b.pid
          AND t.encounter = b.encounter
         SET b.activity = 0
         WHERE b.activity = 1"
    );

    $insertBilling = $db->prepare(
        "INSERT INTO billing
            (`date`, code_type, code, pid, provider_id, `user`, groupname, authorized, encounter, code_text, billed, activity, modifier, units, fee, pricelevel)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?, ?, ?)"
    );

    foreach ($encounters as $enc) {
        foreach ($packageItems as $item) {
            $insertBilling->bind_param(
                'sssiiisiissids',
                $enc['billing_date'],
                $item['code_type'],
                $item['code'],
                $enc['pid'],
                $enc['provider_id'],
                $enc['billing_user'],
                $enc['groupname'],
                $enc['authorized'],
                $enc['encounter'],
                $item['code_text'],
                $item['modifier'],
                $item['units'],
                $item['fee'],
                $enc['pricelevel']
            );
            $insertBilling->execute();
        }
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    throw $e;
}

echo "APPLY_DONE\tbackup_table={$backupTable}\tbackup_meta={$backupMetaPath}\tencounters=" . count($encounters) . "\n";
