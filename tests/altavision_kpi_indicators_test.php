<?php

require_once __DIR__ . '/../library/altavision_kpi_indicators.inc.php';

function assertSameValue($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

function assertTrueValue($actual, $message)
{
    if (!$actual) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$definitions = altavision_kpi_indicator_definitions();
assertSameValue(17, count($definitions), 'The indicator catalog must contain 17 indicators.');
assertSameValue('Tiempo promedio de espera en consulta externa oftalmologica', $definitions[2]['name'], 'Indicator 2 must be the adapted ambulatory waiting-time KPI.');
assertSameValue('not_applicable', $definitions[3]['status'], 'Hospital stay is not applicable for an ambulatory ophthalmology unit.');

$segmentFilters = array(
    'iess' => altavision_kpi_segment_filter('iess'),
    'resto' => altavision_kpi_segment_filter('resto'),
    'todos' => altavision_kpi_segment_filter('todos'),
);
assertSameValue("p.pricelevel = 'IESS'", $segmentFilters['iess']['sql'], 'IESS filter must only include IESS patients.');
assertSameValue("(p.pricelevel IS NULL OR p.pricelevel <> 'IESS')", $segmentFilters['resto']['sql'], 'Resto filter must exclude IESS patients.');
assertSameValue('1 = 1', $segmentFilters['todos']['sql'], 'Todos filter must not filter by pricelevel.');

$metrics = array(
    'iess' => array('avg_wait_minutes' => 12.3, 'wait_count' => 10, 'surgery_scheduled' => 8, 'surgery_suspended' => 2),
    'resto' => array('avg_wait_minutes' => 7.7, 'wait_count' => 5, 'surgery_scheduled' => 4, 'surgery_suspended' => 1),
    'todos' => array('avg_wait_minutes' => 10.8, 'wait_count' => 15, 'surgery_scheduled' => 12, 'surgery_suspended' => 3),
);

$rows = altavision_kpi_build_indicator_rows($metrics);
assertSameValue(17, count($rows), 'The rendered matrix must keep all 17 indicators.');
assertSameValue('12.3 min', $rows[1]['iess_value'], 'Waiting time IESS value must be formatted.');
assertSameValue('25.0%', $rows[6]['total_value'], 'Suspended surgery total rate must be formatted.');
assertSameValue('No aplica', $rows[2]['iess_value'], 'Non-applicable indicators must not invent values.');
assertTrueValue(strpos($rows[12]['gap'], 'encuesta') !== false, 'Satisfaction indicator must explain the survey data gap.');

$summary = altavision_kpi_build_summary($rows);
assertSameValue(17, $summary['total_indicators'], 'Summary must count all indicators.');
assertSameValue(2, $summary['automatic'], 'V1 must only automate consultation waiting and suspended surgeries.');
assertTrueValue($summary['not_applicable'] >= 5, 'Summary must count ambulatory non-applicable indicators.');

$sheetNames = altavision_kpi_workbook_sheet_names();
assertSameValue(array('Resumen', 'IESS', 'Resto', 'Todos', 'Brechas'), $sheetNames, 'Workbook must contain exactly the planned sheets.');

echo "altavision_kpi_indicators_test passed\n";
