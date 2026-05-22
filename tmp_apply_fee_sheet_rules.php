<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

const DB_HOST = '35.192.95.218';
const DB_PORT = 3306;
const DB_NAME = 'altavision';
const DB_USER = 'jl1dvg';
const DB_PASS = 'JorgeAMI2018';

$args = parseArgs($argv);

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
$db->set_charset('utf8mb4');

$config = loadConfig($args['config']);
$packageCache = [];
$rules = [];
foreach ($config['rules'] as $idx => $ruleConfig) {
    $rules[] = normalizeRule($ruleConfig, $config['defaults'] ?? [], $idx);
}

$targets = [];
$targetIndex = [];

foreach ($rules as $rule) {
    $packageItems = fetchPackageDefinition($db, $rule['package'], $rule['pricelevel'], $packageCache);
    $ruleTargets = fetchRuleEncounters($db, $rule);
    foreach ($ruleTargets as $target) {
        $key = $target['pid'] . '-' . $target['encounter'];
        if (isset($targetIndex[$key])) {
            throw new RuntimeException(
                "Encounter {$target['encounter']} / pid {$target['pid']} matched multiple rules: " .
                $targetIndex[$key]['rule_label'] . " and " . $rule['label']
            );
        }

        $target['rule_label'] = $rule['label'];
        $target['package'] = $rule['package'];
        $target['expected_rows'] = count($packageItems);
        $target['expected_units'] = array_sum(array_column($packageItems, 'units'));
        $target['expected_fee'] = round(array_sum(array_column($packageItems, 'fee')), 2);

        $targets[] = $target;
        $targetIndex[$key] = $target;
    }
}

createTemporaryTargetsTable($db);
insertTargets($db, $targets);
$currentSummary = fetchCurrentBillingSummary($db);

$summaryByRule = [];
foreach ($rules as $rule) {
    $summaryByRule[$rule['label']] = [
        'package' => $rule['package'],
        'encounters' => 0,
        'current_rows' => 0,
        'current_units' => 0,
        'current_fee' => 0.0,
        'new_rows' => 0,
        'new_units' => 0,
        'new_fee' => 0.0,
    ];
}

foreach ($targets as $target) {
    $key = $target['pid'] . '-' . $target['encounter'];
    $current = $currentSummary[$key] ?? ['row_count' => 0, 'total_units' => 0, 'total_fee' => 0];
    $bucket = &$summaryByRule[$target['rule_label']];
    $bucket['encounters']++;
    $bucket['current_rows'] += (int) $current['row_count'];
    $bucket['current_units'] += (int) $current['total_units'];
    $bucket['current_fee'] += (float) $current['total_fee'];
    $bucket['new_rows'] += (int) $target['expected_rows'];
    $bucket['new_units'] += (int) $target['expected_units'];
    $bucket['new_fee'] += (float) $target['expected_fee'];
    unset($bucket);
}

$totals = [
    'encounters' => 0,
    'current_rows' => 0,
    'current_units' => 0,
    'current_fee' => 0.0,
    'new_rows' => 0,
    'new_units' => 0,
    'new_fee' => 0.0,
];

echo "RULE_SUMMARY\n";
foreach ($summaryByRule as $label => $summary) {
    $totals['encounters'] += $summary['encounters'];
    $totals['current_rows'] += $summary['current_rows'];
    $totals['current_units'] += $summary['current_units'];
    $totals['current_fee'] += $summary['current_fee'];
    $totals['new_rows'] += $summary['new_rows'];
    $totals['new_units'] += $summary['new_units'];
    $totals['new_fee'] += $summary['new_fee'];

    echo implode("\t", [
        $label,
        "package={$summary['package']}",
        "encounters={$summary['encounters']}",
        "current_rows={$summary['current_rows']}",
        "current_units={$summary['current_units']}",
        "current_fee=" . round($summary['current_fee'], 2),
        "new_rows={$summary['new_rows']}",
        "new_units={$summary['new_units']}",
        "new_fee=" . round($summary['new_fee'], 2),
    ]) . "\n";
}

echo "TOTALS\tencounters={$totals['encounters']}\tcurrent_rows={$totals['current_rows']}\tcurrent_units={$totals['current_units']}\tcurrent_fee=" .
    round($totals['current_fee'], 2) . "\tnew_rows={$totals['new_rows']}\tnew_units={$totals['new_units']}\tnew_fee=" .
    round($totals['new_fee'], 2) . "\n";

echo "SAMPLE_TARGETS\n";
foreach (array_slice($targets, 0, 10) as $target) {
    echo implode("\t", [
        $target['rule_label'],
        $target['package'],
        $target['pid'],
        $target['encounter'],
        $target['form_id'],
        $target['protocol_date'],
    ]) . "\n";
}

if ($args['mode'] === '--dry-run') {
    exit(0);
}

$backupTable = 'billing_backup_fee_sheet_' . date('Ymd_His');
$backupMetaPath = __DIR__ . '/tmp_apply_fee_sheet_rules_' . date('Ymd_His') . '.json';

$db->query(
    "CREATE TABLE {$backupTable} AS
     SELECT b.*, t.rule_label, t.package_name, NOW() AS backup_ts, 'fee_sheet_rules_apply' AS backup_batch
       FROM billing b
       JOIN tmp_fee_sheet_rule_targets t
         ON t.pid = b.pid
        AND t.encounter = b.encounter
      WHERE b.activity = 1"
);

file_put_contents($backupMetaPath, json_encode([
    'created_at' => date('c'),
    'config_path' => realpath($args['config']) ?: $args['config'],
    'backup_table' => $backupTable,
    'mode' => $args['mode'],
    'rules' => array_values($summaryByRule),
    'totals' => [
        'encounters' => $totals['encounters'],
        'current_rows' => $totals['current_rows'],
        'current_units' => $totals['current_units'],
        'current_fee' => round($totals['current_fee'], 2),
        'new_rows' => $totals['new_rows'],
        'new_units' => $totals['new_units'],
        'new_fee' => round($totals['new_fee'], 2),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$db->begin_transaction();

try {
    $db->query(
        "UPDATE billing b
         JOIN tmp_fee_sheet_rule_targets t
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

    foreach ($targets as $target) {
        $packageItems = $packageCache[$target['package'] . '|' . $target['pricelevel']];
        foreach ($packageItems as $item) {
            $insertBilling->bind_param(
                'sssiiisiissids',
                $target['billing_date'],
                $item['code_type'],
                $item['code'],
                $target['pid'],
                $target['provider_id'],
                $target['billing_user'],
                $target['groupname'],
                $target['authorized'],
                $target['encounter'],
                $item['code_text'],
                $item['modifier'],
                $item['units'],
                $item['fee'],
                $target['pricelevel']
            );
            $insertBilling->execute();
        }
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    throw $e;
}

echo "APPLY_DONE\tbackup_table={$backupTable}\tbackup_meta={$backupMetaPath}\tencounters={$totals['encounters']}\n";

function parseArgs(array $argv): array
{
    $mode = '--dry-run';
    $config = __DIR__ . '/tmp_apply_fee_sheet_rules.json';

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--dry-run' || $arg === '--apply') {
            $mode = $arg;
            continue;
        }
        if (strpos($arg, '--config=') === 0) {
            $config = substr($arg, 9);
            continue;
        }
    }

    if (!is_file($config)) {
        throw new RuntimeException(
            "Config file not found: {$config}\nUsage: php tmp_apply_fee_sheet_rules.php [--dry-run|--apply] [--config=/path/config.json]"
        );
    }

    return [
        'mode' => $mode,
        'config' => $config,
    ];
}

function loadConfig(string $path): array
{
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON config: {$path}");
    }
    if (empty($data['rules']) || !is_array($data['rules'])) {
        throw new RuntimeException("Config {$path} must include a non-empty 'rules' array.");
    }

    return $data;
}

function normalizeRule(array $rule, array $defaults, int $index): array
{
    $merged = array_merge($defaults, $rule);
    if (empty($merged['package'])) {
        throw new RuntimeException("Rule #" . ($index + 1) . " is missing 'package'.");
    }
    if (empty($merged['formdir'])) {
        throw new RuntimeException("Rule {$merged['package']} is missing 'formdir'.");
    }
    if (empty($merged['pricelevel'])) {
        throw new RuntimeException("Rule {$merged['package']} is missing 'pricelevel'.");
    }
    if (empty($merged['date_from'])) {
        throw new RuntimeException("Rule {$merged['package']} is missing 'date_from'.");
    }

    $filters = [];
    if (!empty($merged['field_filters']) && is_array($merged['field_filters'])) {
        foreach ($merged['field_filters'] as $filter) {
            if (empty($filter['field_id']) || !array_key_exists('field_value', $filter)) {
                throw new RuntimeException("Rule {$merged['package']} has an invalid field filter.");
            }
            $filters[] = [
                'field_id' => (string) $filter['field_id'],
                'field_value' => (string) $filter['field_value'],
            ];
        }
    } elseif (!empty($merged['field_id']) && array_key_exists('field_value', $merged)) {
        $filters[] = [
            'field_id' => (string) $merged['field_id'],
            'field_value' => (string) $merged['field_value'],
        ];
    } else {
        throw new RuntimeException("Rule {$merged['package']} must define 'field_filters' or 'field_id' + 'field_value'.");
    }

    return [
        'label' => (string) ($merged['label'] ?? ('rule_' . ($index + 1))),
        'package' => (string) $merged['package'],
        'formdir' => (string) $merged['formdir'],
        'pricelevel' => (string) $merged['pricelevel'],
        'date_from' => (string) $merged['date_from'],
        'date_to' => (string) ($merged['date_to'] ?? date('Y-m-d')),
        'field_filters' => $filters,
    ];
}

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

function fetchPackageDefinition(mysqli $db, string $packageName, string $pricelevel, array &$cache): array
{
    $cacheKey = $packageName . '|' . $pricelevel;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $pkgStmt = $db->prepare("SELECT fs_codes FROM fee_sheet_options WHERE fs_option = ? LIMIT 1");
    $pkgStmt->bind_param('s', $packageName);
    $pkgStmt->execute();
    $pkg = $pkgStmt->get_result()->fetch_assoc();
    if (!$pkg) {
        throw new RuntimeException("Package '{$packageName}' not found.");
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
          AND pr.pr_level = ?
         WHERE ct.ct_key = ?
         ORDER BY c.modifier
         LIMIT 1"
    );

    foreach ($items as &$item) {
        $metaStmt->bind_param('sss', $item['code'], $pricelevel, $item['code_type']);
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

    $cache[$cacheKey] = $items;
    return $cache[$cacheKey];
}

function fetchRuleEncounters(mysqli $db, array $rule): array
{
    $params = [$rule['pricelevel']];
    $types = 's';
    $joins = [];
    foreach ($rule['field_filters'] as $idx => $filter) {
        $alias = 'd' . ($idx + 1);
        $joins[] = "JOIN lbf_data {$alias}
      ON {$alias}.form_id = f.form_id
     AND {$alias}.field_id = ?
     AND {$alias}.field_value = ?";
        $types .= 'ss';
        $params[] = $filter['field_id'];
        $params[] = $filter['field_value'];
    }

    $sql = "SELECT
        f.pid,
        f.encounter,
        MAX(f.form_id) AS form_id,
        MAX(CONCAT(DATE(f.date), ' 00:00:00')) AS protocol_date,
        COALESCE(t.date, MAX(CONCAT(DATE(f.date), ' 00:00:00'))) AS billing_date,
        COALESCE(t.provider_id, MAX(fe.provider_id), 0) AS provider_id,
        COALESCE(t.user, 0) AS billing_user,
        COALESCE(t.groupname, 'Default') AS groupname,
        COALESCE(t.authorized, 1) AS authorized,
        COALESCE(t.pricelevel, ?) AS pricelevel
    FROM forms f
    " . implode("\n", $joins) . "
    JOIN patient_data p
      ON p.pid = f.pid
    LEFT JOIN form_encounter fe
      ON fe.pid = f.pid
     AND fe.encounter = f.encounter
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
      ON t.pid = f.pid
     AND t.encounter = f.encounter
    WHERE f.formdir = ?
      AND f.deleted = 0
      AND p.pricelevel = ?
      AND DATE(f.date) >= ?
      AND DATE(f.date) <= ?
    GROUP BY f.pid, f.encounter
    ORDER BY protocol_date, f.pid, f.encounter";

    $types .= 'ssss';
    $params[] = $rule['formdir'];
    $params[] = $rule['pricelevel'];
    $params[] = $rule['date_from'];
    $params[] = $rule['date_to'];

    $stmt = $db->prepare($sql);
    bindParams($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function createTemporaryTargetsTable(mysqli $db): void
{
    $db->query("DROP TEMPORARY TABLE IF EXISTS tmp_fee_sheet_rule_targets");
    $db->query(
        "CREATE TEMPORARY TABLE tmp_fee_sheet_rule_targets (
            pid BIGINT NOT NULL,
            encounter INT NOT NULL,
            form_id INT NOT NULL,
            rule_label VARCHAR(191) NOT NULL,
            package_name VARCHAR(191) NOT NULL,
            protocol_date DATETIME NULL,
            billing_date DATETIME NULL,
            provider_id INT NULL,
            billing_user INT NULL,
            groupname VARCHAR(255) NULL,
            authorized TINYINT NULL,
            pricelevel VARCHAR(31) NULL,
            PRIMARY KEY (pid, encounter)
        )"
    );
}

function insertTargets(mysqli $db, array $targets): void
{
    $stmt = $db->prepare(
        "INSERT INTO tmp_fee_sheet_rule_targets
            (pid, encounter, form_id, rule_label, package_name, protocol_date, billing_date, provider_id, billing_user, groupname, authorized, pricelevel)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($targets as $target) {
        $stmt->bind_param(
            'iiissssiisis',
            $target['pid'],
            $target['encounter'],
            $target['form_id'],
            $target['rule_label'],
            $target['package'],
            $target['protocol_date'],
            $target['billing_date'],
            $target['provider_id'],
            $target['billing_user'],
            $target['groupname'],
            $target['authorized'],
            $target['pricelevel']
        );
        $stmt->execute();
    }
}

function fetchCurrentBillingSummary(mysqli $db): array
{
    $sql = "SELECT
        b.pid,
        b.encounter,
        COUNT(*) AS row_count,
        SUM(b.units) AS total_units,
        ROUND(SUM(b.fee), 2) AS total_fee
    FROM billing b
    JOIN tmp_fee_sheet_rule_targets t
      ON t.pid = b.pid
     AND t.encounter = b.encounter
    WHERE b.activity = 1
    GROUP BY b.pid, b.encounter";

    $result = $db->query($sql);
    $summary = [];
    while ($row = $result->fetch_assoc()) {
        $summary[$row['pid'] . '-' . $row['encounter']] = $row;
    }

    return $summary;
}

function bindParams(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types === '') {
        return;
    }

    $refs = [];
    $refs[] = &$types;
    foreach ($params as $idx => $value) {
        $refs[] = &$params[$idx];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
}
