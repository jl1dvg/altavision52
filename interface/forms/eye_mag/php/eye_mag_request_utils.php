<?php
/**
 * Request and IMPPLAN normalization helpers for Eye Mag.
 *
 * @package OpenEMR
 */

function eyeMagRequestString($key, $default = '')
{
    if (!isset($_REQUEST[$key])) {
        return $default;
    }

    $value = $_REQUEST[$key];
    if (is_array($value)) {
        return $default;
    }

    return trim((string) $value);
}

function eyeMagRequestInt($key, $default = 0)
{
    if (!isset($_REQUEST[$key])) {
        return (int) $default;
    }

    if (is_array($_REQUEST[$key])) {
        return (int) $default;
    }

    return (int) $_REQUEST[$key];
}

function eyeMagRequestBool($key, $default = false)
{
    if (!isset($_REQUEST[$key])) {
        return (bool) $default;
    }

    if (is_bool($_REQUEST[$key])) {
        return $_REQUEST[$key];
    }

    $value = strtolower(trim((string) $_REQUEST[$key]));
    return in_array($value, array('1', 'true', 'yes', 'on'), true);
}

function eyeMagNormalizeCodeList($codes)
{
    $list = array();

    if (is_array($codes)) {
        foreach ($codes as $code) {
            if (is_array($code)) {
                continue;
            }
            $list[] = trim((string) $code);
        }
    } else {
        $parts = preg_split('/\s*,\s*/', (string) $codes);
        if (is_array($parts)) {
            $list = $parts;
        }
    }

    $unique = array();
    foreach ($list as $code) {
        if ($code === '') {
            continue;
        }
        if (!isset($unique[$code])) {
            $unique[$code] = true;
        }
    }

    return implode(', ', array_keys($unique));
}

function eyeMagNormalizeImpPlanItems(array $items)
{
    $normalized = array();
    $seen = array();

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $row = array();
        $row['title'] = isset($item['title']) ? trim((string) $item['title']) : '';
        $row['code'] = eyeMagNormalizeCodeList(isset($item['codes']) ? $item['codes'] : ($item['code'] ?? ''));
        $row['codetype'] = isset($item['codetype']) ? trim((string) $item['codetype']) : '';
        $row['codedesc'] = isset($item['codedesc']) ? trim((string) $item['codedesc']) : '';
        $row['codetext'] = isset($item['codetext']) ? trim((string) $item['codetext']) : '';
        $row['plan'] = isset($item['plan']) ? (string) $item['plan'] : '';
        $row['PMSFH_link'] = isset($item['PMSFH_link']) ? trim((string) $item['PMSFH_link']) : '';

        $dedupeKey = $row['title'] . '|' . $row['code'] . '|' . trim($row['plan']) . '|' . $row['PMSFH_link'];
        if (isset($seen[$dedupeKey])) {
            continue;
        }

        $seen[$dedupeKey] = true;
        $normalized[] = $row;
    }

    return $normalized;
}
