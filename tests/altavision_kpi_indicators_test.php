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

assertSameValue(
    20,
    altavision_kpi_effective_wait_minutes('2026-06-29 10:00:00', '2026-06-29 09:40:00', '2026-06-29 10:20:00'),
    'Early arrivals must wait only from the scheduled time.'
);
assertSameValue(
    15,
    altavision_kpi_effective_wait_minutes('2026-06-29 10:00:00', '2026-06-29 10:10:00', '2026-06-29 10:25:00'),
    'Late arrivals must wait only from their real arrival time.'
);
assertSameValue(
    0,
    altavision_kpi_effective_wait_minutes('2026-06-29 10:00:00', '2026-06-29 10:20:00', '2026-06-29 10:15:00'),
    'Negative waits must be clamped to zero.'
);

$definitions = altavision_kpi_indicator_definitions();
assertSameValue(17, count($definitions), 'The indicator catalog must contain 17 indicators.');
assertSameValue('automatic', $definitions[1]['status'], 'Indicator 1 must be calculated from surgical tracker time.');
assertTrueValue(strpos($definitions[2]['formula'], 'mayor entre hora agendada y hora de llegada') !== false, 'Indicator 2 formula must use effective start time.');
assertSameValue('automatic', $definitions[4]['status'], 'Indicator 4 must use referrals and same-diagnosis returns.');
assertSameValue('temporary_zero', $definitions[5]['status'], 'Indicator 5 must remain at zero by operational decision.');
assertSameValue('not_applicable', $definitions[6]['status'], 'Indicator 6 must be marked as not applicable for ambulatory care.');
assertSameValue('not_applicable', $definitions[8]['status'], 'Indicator 8 must be marked as not applicable for ambulatory care.');
assertSameValue('gap', $definitions[10]['status'], 'Indicator 10 remains pending.');
assertSameValue('temporary_zero', $definitions[17]['status'], 'Indicator 17 must remain zero because it is out of scope.');

$segmentFilters = array(
    'iess' => altavision_kpi_segment_filter('iess'),
    'resto' => altavision_kpi_segment_filter('resto'),
    'todos' => altavision_kpi_segment_filter('todos'),
);
assertSameValue("p.pricelevel = 'IESS'", $segmentFilters['iess']['sql'], 'IESS filter must only include IESS patients.');
assertSameValue("(p.pricelevel IS NULL OR p.pricelevel <> 'IESS')", $segmentFilters['resto']['sql'], 'Resto filter must exclude IESS patients.');
assertSameValue('1 = 1', $segmentFilters['todos']['sql'], 'Todos filter must not filter by pricelevel.');

$metrics = array(
    'iess' => array(
        'observation_avg_minutes' => 18.0,
        'observation_cases' => 6,
        'avg_wait_minutes' => 12.3,
        'wait_count' => 10,
        'repeat_referral_returns' => 2,
        'referral_patients' => 5,
        'preop_avg_hours' => 1.6,
        'preop_cases' => 6,
        'surgery_scheduled' => 8,
        'surgery_suspended' => 2,
        'complication_proxy_cases' => 1,
        'faco_lio_cases' => 8,
        'monthly' => altavision_kpi_empty_month_metrics(),
    ),
    'resto' => array(
        'observation_avg_minutes' => 24.0,
        'observation_cases' => 4,
        'avg_wait_minutes' => 7.7,
        'wait_count' => 5,
        'repeat_referral_returns' => 1,
        'referral_patients' => 4,
        'preop_avg_hours' => 2.1,
        'preop_cases' => 4,
        'surgery_scheduled' => 4,
        'surgery_suspended' => 1,
        'complication_proxy_cases' => 0,
        'faco_lio_cases' => 3,
        'monthly' => altavision_kpi_empty_month_metrics(),
    ),
    'todos' => array(
        'observation_avg_minutes' => 20.4,
        'observation_cases' => 10,
        'avg_wait_minutes' => 10.8,
        'wait_count' => 15,
        'repeat_referral_returns' => 3,
        'referral_patients' => 9,
        'preop_avg_hours' => 1.8,
        'preop_cases' => 10,
        'surgery_scheduled' => 12,
        'surgery_suspended' => 3,
        'complication_proxy_cases' => 1,
        'faco_lio_cases' => 11,
        'monthly' => altavision_kpi_empty_month_metrics(),
    ),
);
$metrics['iess']['monthly'][6]['observation_avg_minutes'] = 16.5;
$metrics['iess']['monthly'][6]['observation_cases'] = 2;
$metrics['iess']['monthly'][6]['avg_wait_minutes'] = 9.5;
$metrics['iess']['monthly'][6]['wait_count'] = 4;
$metrics['todos']['monthly'][6]['repeat_referral_returns'] = 1;
$metrics['todos']['monthly'][6]['referral_patients'] = 2;
$metrics['todos']['monthly'][6]['preop_avg_hours'] = 1.3;
$metrics['todos']['monthly'][6]['preop_cases'] = 3;
$metrics['todos']['monthly'][6]['surgery_scheduled'] = 3;
$metrics['todos']['monthly'][6]['surgery_suspended'] = 1;
$metrics['todos']['monthly'][6]['complication_proxy_cases'] = 1;
$metrics['todos']['monthly'][6]['faco_lio_cases'] = 4;

$rows = altavision_kpi_build_indicator_rows($metrics);
assertSameValue(17, count($rows), 'The rendered matrix must keep all 17 indicators.');
assertSameValue('18.0 min', $rows[0]['iess_value'], 'Observation stay must be formatted in minutes.');
assertSameValue('12.3 min', $rows[1]['iess_value'], 'Waiting time IESS value must be formatted.');
assertSameValue('40.0%', $rows[3]['iess_value'], 'Referral repeat return KPI must be formatted as percent.');
assertSameValue('No aplica', $rows[5]['iess_value'], 'Indicator 6 must display as not applicable.');
assertSameValue('25.0%', $rows[6]['total_value'], 'Suspended surgery total rate must be formatted.');
assertSameValue('9.1%', $rows[14]['total_value'], 'Aphakia complication proxy must be formatted as percent.');
assertSameValue('0.0%', $rows[4]['iess_value'], 'Indicator 5 must stay at zero.');
assertSameValue('0.0%', $rows[16]['total_value'], 'Indicator 17 must stay at zero.');
assertSameValue(16.5, $rows[0]['monthly_values']['iess'][6], 'Monthly observation values must be available for the annual template.');
assertSameValue(9.5, $rows[1]['monthly_values']['iess'][6], 'Monthly waiting values must be available for the annual template.');
assertSameValue(50.0, $rows[3]['monthly_values']['todos'][6], 'Monthly referral repeat rate must be available for the annual template.');
assertSameValue('N/A', $rows[5]['monthly_values']['todos'][6], 'Indicator 6 monthly values must remain unavailable.');
assertSameValue(33.3, $rows[6]['monthly_values']['todos'][6], 'Monthly surgery suspension rate must be available for the annual template.');
assertSameValue(25.0, $rows[14]['monthly_values']['todos'][6], 'Monthly aphakia proxy rate must be available for the annual template.');
assertSameValue('No aplica', $rows[2]['iess_value'], 'Hospital-stay indicator remains non-applicable.');
assertSameValue('No aplica', $rows[7]['iess_value'], 'Indicator 8 must display as not applicable.');
assertTrueValue(strpos($rows[12]['gap'], 'encuesta') !== false, 'Satisfaction indicator must explain the survey data gap.');

$summary = altavision_kpi_build_summary($rows);
assertSameValue(17, $summary['total_indicators'], 'Summary must count all indicators.');
assertSameValue(5, $summary['automatic'], 'This version must automate the clinically approved wins.');
assertSameValue(3, $summary['gap'], 'Only the explicitly pending indicators should remain as gaps.');
assertSameValue(3, $summary['not_applicable'], 'Hospital-stay indicators in days must remain non-applicable.');
assertSameValue(6, $summary['temporary_zero'], 'Operational zero indicators must be tracked separately.');

$sheetNames = altavision_kpi_workbook_sheet_names();
assertSameValue(array('IESS', 'Resto', 'Todos', 'Resumen', 'Brechas'), $sheetNames, 'Workbook must contain exactly the planned sheets in user-facing order.');

echo "altavision_kpi_indicators_test passed\n";
