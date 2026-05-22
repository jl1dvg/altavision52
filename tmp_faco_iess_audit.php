<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db = new mysqli('35.192.95.218', 'jl1dvg', 'JorgeAMI2018', 'altavision', 3306);
$db->set_charset('utf8mb4');

function parsePackageCodes(string $fsCodes): array
{
    $items = [];
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
        }
        $items[$key]['units']++;
    }

    ksort($items);
    return array_values($items);
}

function buildSignature(array $rows): string
{
    $parts = [];
    foreach ($rows as $row) {
        $parts[] = implode('|', [
            $row['code_type'],
            $row['code'],
            $row['modifier'],
            (int) $row['units'],
        ]);
    }
    sort($parts);
    return implode('~', $parts);
}

$pkgStmt = $db->prepare("SELECT fs_codes FROM fee_sheet_options WHERE fs_option = '1Faco - IESS' LIMIT 1");
$pkgStmt->execute();
$pkgResult = $pkgStmt->get_result()->fetch_assoc();
if (!$pkgResult) {
    throw new RuntimeException("Package '1Faco - IESS' not found.");
}

$expectedRows = parsePackageCodes($pkgResult['fs_codes']);
$expectedSignature = buildSignature($expectedRows);

echo "EXPECTED_PACKAGE_ITEMS\n";
foreach ($expectedRows as $row) {
    echo implode("\t", [$row['code_type'], $row['code'], $row['modifier'], $row['units']]) . "\n";
}
echo "EXPECTED_SIGNATURE_HASH\t" . md5($expectedSignature) . "\n";

$encounterSql = <<<SQL
SELECT
    f.pid,
    f.encounter,
    f.form_id,
    DATE(f.date) AS protocol_date,
    p.pricelevel
FROM forms AS f
JOIN lbf_data AS d
    ON d.form_id = f.form_id
   AND d.field_id = 'Prot_opr'
   AND d.field_value = 'faco'
JOIN patient_data AS p
    ON p.pid = f.pid
WHERE f.formdir = 'LBFprotocolo'
  AND f.deleted = 0
  AND p.pricelevel = 'IESS'
  AND DATE(f.date) >= '2026-01-01'
ORDER BY f.date, f.pid, f.encounter
SQL;

$totals = [
    'encounters' => 0,
    'matching' => 0,
    'different' => 0,
    'no_billing' => 0,
];

$examples = [];

$signatureSql = <<<SQL
SELECT
    base.pid,
    base.encounter,
    base.form_id,
    base.protocol_date,
    billing_sig.signature AS actual_signature
FROM (
    SELECT
        f.pid,
        f.encounter,
        f.form_id,
        DATE(f.date) AS protocol_date
    FROM forms AS f
    JOIN lbf_data AS d
        ON d.form_id = f.form_id
       AND d.field_id = 'Prot_opr'
       AND d.field_value = 'faco'
    JOIN patient_data AS p
        ON p.pid = f.pid
    WHERE f.formdir = 'LBFprotocolo'
      AND f.deleted = 0
      AND p.pricelevel = 'IESS'
      AND DATE(f.date) >= '2026-01-01'
) AS base
LEFT JOIN (
    SELECT
        grouped.pid,
        grouped.encounter,
        GROUP_CONCAT(
            CONCAT(grouped.code_type, '|', grouped.code, '|', grouped.modifier, '|', grouped.units)
            ORDER BY grouped.code_type, grouped.code, grouped.modifier
            SEPARATOR '~'
        ) AS signature
    FROM (
        SELECT
            b.pid,
            b.encounter,
            b.code_type,
            b.code,
            COALESCE(b.modifier, '') AS modifier,
            SUM(b.units) AS units
        FROM billing AS b
        WHERE b.activity = 1
        GROUP BY b.pid, b.encounter, b.code_type, b.code, COALESCE(b.modifier, '')
    ) AS grouped
    GROUP BY grouped.pid, grouped.encounter
) AS billing_sig
    ON billing_sig.pid = base.pid
   AND billing_sig.encounter = base.encounter
ORDER BY base.protocol_date, base.pid, base.encounter
SQL;

$encounters = $db->query($signatureSql);

while ($enc = $encounters->fetch_assoc()) {
    $totals['encounters']++;
    $actualSignature = $enc['actual_signature'];

    if ($actualSignature === null || $actualSignature === '') {
        $totals['no_billing']++;
        $totals['different']++;
    } elseif ($actualSignature === $expectedSignature) {
        $totals['matching']++;
    } else {
        $totals['different']++;
    }

    if (count($examples) < 10 && ($actualSignature === null || $actualSignature === '' || $actualSignature !== $expectedSignature)) {
        $examples[] = [
            'pid' => $enc['pid'],
            'encounter' => $enc['encounter'],
            'form_id' => $enc['form_id'],
            'protocol_date' => $enc['protocol_date'],
            'actual_hash' => $actualSignature ? md5($actualSignature) : 'NO_BILLING',
        ];
    }
}

echo "SUMMARY\n";
foreach ($totals as $key => $value) {
    echo $key . "\t" . $value . "\n";
}

echo "DIFFERENT_EXAMPLES\n";
foreach ($examples as $example) {
    echo implode("\t", [
        $example['pid'],
        $example['encounter'],
        $example['form_id'],
        $example['protocol_date'],
        $example['actual_hash'],
    ]) . "\n";
}
