<?php
/**
 * CLI-only EyeMag diagnostic for contrareferencia output.
 *
 * Usage:
 *   php debug_eyemag_cli.php 21506 94927 44738
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$ignoreAuth = true;
$_GET['site'] = $_GET['site'] ?? 'default';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'default';

chdir(__DIR__);

require_once("../../globals.php");
require_once($GLOBALS['srcdir'] . "/iess.inc.php");

$pid = isset($argv[1]) ? (int)$argv[1] : 0;
$encounter = isset($argv[2]) ? (int)$argv[2] : 0;
$formId = isset($argv[3]) ? (int)$argv[3] : 0;

if (!$pid || !$encounter || !$formId) {
    fwrite(STDERR, "Usage: php debug_eyemag_cli.php <pid> <encounter> <form_id>\n");
    exit(1);
}

$row = sqlQuery(
    "SELECT
        f.id AS forms_id,
        f.pid,
        f.encounter,
        f.form_id,
        f.form_name,
        h.CC1,
        a.SCODVA,
        a.SCOSVA,
        v.ODIOPAP,
        v.OSIOPAP,
        e.RBROW,
        e.LBROW,
        e.RUL,
        e.LUL,
        e.RLL,
        e.LLL,
        e.RMCT,
        e.LMCT,
        e.RADNEXA,
        e.LADNEXA,
        e.EXT_COMMENTS,
        ant.ODCONJ,
        ant.OSCONJ,
        ant.ODCORNEA,
        ant.OSCORNEA,
        ant.ODAC,
        ant.OSAC,
        ant.ODLENS,
        ant.OSLENS,
        ant.ODIRIS,
        ant.OSIRIS,
        post.ODDISC,
        post.OSDISC,
        post.ODCUP,
        post.OSCUP,
        post.ODMACULA,
        post.OSMACULA,
        post.ODVESSELS,
        post.OSVESSELS,
        post.ODPERIPH,
        post.OSPERIPH,
        post.ODVITREOUS,
        post.OSVITREOUS
     FROM forms f
     LEFT JOIN form_eye_hpi h ON f.form_id = h.id
     LEFT JOIN form_eye_acuity a ON f.form_id = a.id
     LEFT JOIN form_eye_vitals v ON f.form_id = v.id
     LEFT JOIN form_eye_external e ON f.form_id = e.id
     LEFT JOIN form_eye_antseg ant ON f.form_id = ant.id
     LEFT JOIN form_eye_postseg post ON f.form_id = post.id
     WHERE f.pid = ?
       AND f.encounter = ?
       AND f.formdir = 'eye_mag'
       AND f.form_id = ?
       AND f.deleted != 1",
    array($pid, $encounter, $formId)
);

function debugEyePrintFields($title, $row)
{
    echo "\n=== $title ===\n";
    if (!$row) {
        echo "No rows\n";
        return;
    }

    foreach ($row as $key => $value) {
        if ($value !== null && $value !== '') {
            echo $key . ": " . strip_tags((string)$value) . "\n";
        }
    }
}

function debugEyeExamOutput($row, $encounter)
{
    if (!$row) {
        return '';
    }

    return strip_tags(ExamOftal(
        $encounter,
        $row['CC1'] ?? '',
        $row['RBROW'] ?? '',
        $row['LBROW'] ?? '',
        $row['RUL'] ?? '',
        $row['LUL'] ?? '',
        $row['RLL'] ?? '',
        $row['LLL'] ?? '',
        $row['RMCT'] ?? '',
        $row['LMCT'] ?? '',
        $row['RADNEXA'] ?? '',
        $row['LADNEXA'] ?? '',
        $row['EXT_COMMENTS'] ?? '',
        $row['SCODVA'] ?? '',
        $row['SCOSVA'] ?? '',
        '',
        '',
        $row['ODIOPAP'] ?? '',
        $row['OSIOPAP'] ?? '',
        $row['ODCONJ'] ?? '',
        $row['OSCONJ'] ?? '',
        $row['ODCORNEA'] ?? '',
        $row['OSCORNEA'] ?? '',
        $row['ODAC'] ?? '',
        $row['OSAC'] ?? '',
        $row['ODLENS'] ?? '',
        $row['OSLENS'] ?? '',
        $row['ODIRIS'] ?? '',
        $row['OSIRIS'] ?? '',
        $row['ODDISC'] ?? '',
        $row['OSDISC'] ?? '',
        $row['ODCUP'] ?? '',
        $row['OSCUP'] ?? '',
        $row['ODMACULA'] ?? '',
        $row['OSMACULA'] ?? '',
        $row['ODVESSELS'] ?? '',
        $row['OSVESSELS'] ?? '',
        $row['ODPERIPH'] ?? '',
        $row['OSPERIPH'] ?? '',
        $row['ODVITREOUS'] ?? '',
        $row['OSVITREOUS'] ?? ''
    ));
}

function debugEyeValue($row, $key)
{
    return isset($row[$key]) ? trim((string)$row[$key]) : '';
}

function debugEyePrintCondition($label, $condition)
{
    echo $label . ': ' . ($condition ? 'YES' : 'NO') . "\n";
}

function debugEyePrintSections($row)
{
    echo "\n=== Section checks ===\n";
    if (!$row) {
        echo "No rows\n";
        return;
    }

    $hasExternal = debugEyeValue($row, 'RBROW') || debugEyeValue($row, 'LBROW') || debugEyeValue($row, 'RUL') || debugEyeValue($row, 'LUL') || debugEyeValue($row, 'RLL') || debugEyeValue($row, 'LLL') || debugEyeValue($row, 'RMCT') || debugEyeValue($row, 'LMCT') || debugEyeValue($row, 'RADNEXA') || debugEyeValue($row, 'LADNEXA') || debugEyeValue($row, 'EXT_COMMENTS');
    $hasAntSeg = debugEyeValue($row, 'ODCONJ') || debugEyeValue($row, 'ODCORNEA') || debugEyeValue($row, 'ODAC') || debugEyeValue($row, 'ODLENS') || debugEyeValue($row, 'ODIRIS') || debugEyeValue($row, 'OSCONJ') || debugEyeValue($row, 'OSCORNEA') || debugEyeValue($row, 'OSAC') || debugEyeValue($row, 'OSLENS') || debugEyeValue($row, 'OSIRIS');
    $hasPostSeg = debugEyeValue($row, 'ODDISC') || debugEyeValue($row, 'OSDISC') || debugEyeValue($row, 'ODCUP') || debugEyeValue($row, 'OSCUP') || debugEyeValue($row, 'ODMACULA') || debugEyeValue($row, 'OSMACULA') || debugEyeValue($row, 'ODVESSELS') || debugEyeValue($row, 'OSVESSELS') || debugEyeValue($row, 'ODPERIPH') || debugEyeValue($row, 'OSPERIPH') || debugEyeValue($row, 'ODVITREOUS') || debugEyeValue($row, 'OSVITREOUS');

    debugEyePrintCondition('Has external data', $hasExternal);
    debugEyePrintCondition('Has anterior segment data', $hasAntSeg);
    debugEyePrintCondition('Has posterior segment data', $hasPostSeg);
}

function debugEyePrintRawValue($row, $key)
{
    $value = isset($row[$key]) ? (string)$row[$key] : '';
    echo $key . " raw json: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
    echo $key . " hex: " . bin2hex($value) . "\n";
}

function debugEyePrintRawSuspiciousFields($row)
{
    echo "\n=== Raw suspicious fields ===\n";
    if (!$row) {
        echo "No rows\n";
        return;
    }

    foreach (array('CC1', 'SCODVA', 'SCOSVA', 'ODVA', 'OSVA') as $key) {
        debugEyePrintRawValue($row, $key);
    }

    echo "\nFields containing angle brackets:\n";
    $found = false;
    foreach ($row as $key => $value) {
        $value = (string)$value;
        if (strpos($value, '<') !== false || strpos($value, '>') !== false) {
            $found = true;
            echo $key . ": " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    if (!$found) {
        echo "None\n";
    }
}

debugEyePrintFields('DB fields for selected eye_mag form', $row);
debugEyePrintSections($row);
debugEyePrintRawSuspiciousFields($row);

echo "\n=== ExamOftal output for selected eye_mag form ===\n";
echo debugEyeExamOutput($row, $encounter) . "\n";

$legacyRow = getEyeMagEncounterData($encounter, $pid);
debugEyePrintFields('Legacy getEyeMagEncounterData fields', $legacyRow);
debugEyePrintSections($legacyRow);
debugEyePrintRawSuspiciousFields($legacyRow);

echo "\n=== ExamOftal output from legacy getEyeMagEncounterData ===\n";
echo debugEyeExamOutput($legacyRow, $encounter) . "\n";
