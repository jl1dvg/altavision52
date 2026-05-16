<?php
/**
 * Dashboard de Particulares.
 *
 * Analiza pacientes no publicos (excluye IESS y MSP) y cruza
 * agenda, encounters y billing para mostrar embudo operativo,
 * crecimiento mensual y bloques por unidad de negocio.
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

function isValidIsoDateDashboard($value)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
        return false;
    }

    $parts = explode('-', $value);
    return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}

function dashboardMoney($amount)
{
    return oeFormatMoney((float) $amount);
}

function dashboardPct($value, $decimals = 1)
{
    return number_format((float) $value, $decimals) . '%';
}

function dashboardMonthLabelEs($monthKey)
{
    static $months = array(
        '01' => 'Ene',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Abr',
        '05' => 'May',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Ago',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dic',
    );

    $year = substr($monthKey, 0, 4);
    $month = substr($monthKey, 5, 2);
    return ($months[$month] ?? $month) . ' ' . $year;
}

function dashboardCategoryKey($category)
{
    $category = trim((string) $category);
    return $category === '' ? '__UNCAT__' : $category;
}

function dashboardCategoryLabel($category)
{
    $category = trim((string) $category);
    return $category === '' ? 'Sin categoria' : $category;
}

function dashboardBusinessUnit($category)
{
    switch ((string) $category) {
        case 'Consulta':
            return 'Servicios Oftalmologicos';
        case 'ExaImage':
            return 'Imagenes';
        case 'NoInvasivo':
            return 'PNI';
        case 'Proced':
        case 'DerechoSala':
        case 'Anestesia':
        case 'Ayudante':
        case 'Equipos':
        case 'MaterialEspecial':
        case 'MaterialFungible':
            return 'Cirugias';
        default:
            return 'Otros';
    }
}

function dashboardSuperiorCategory($pricelevel)
{
    return strtoupper(trim((string) $pricelevel)) === 'STANDARD' ? 'Particulares' : 'Privados';
}

function dashboardSurgeryStatusEligible($status, $statusToggle1 = null, $statusToggle2 = null)
{
    $status = trim((string) $status);
    if ($status === '') {
        return false;
    }

    if (!empty($statusToggle1) || !empty($statusToggle2)) {
        return true;
    }

    return in_array($status, array('<', '$'), true);
}

function dashboardDelta($current, $previous)
{
    $current = (float) $current;
    $previous = (float) $previous;
    if (abs($previous) < 0.00001) {
        return null;
    }

    return (($current - $previous) / $previous) * 100;
}

function dashboardGrowthBadge($amountDelta, $countDelta)
{
    if ($amountDelta === null && $countDelta === null) {
        return array('label' => 'SIN BASE', 'class' => 'status-neutral');
    }

    if (($amountDelta !== null && $amountDelta < 0) || ($countDelta !== null && $countDelta < 0)) {
        return array('label' => 'EN RETROCESO', 'class' => 'status-danger');
    }

    if (($amountDelta !== null && $amountDelta > 0) && ($countDelta !== null && $countDelta > 0)) {
        return array('label' => 'EN CRECIMIENTO', 'class' => 'status-success');
    }

    return array('label' => 'EN VIGILANCIA', 'class' => 'status-warning');
}

function dashboardDeltaLabel($current, $previous)
{
    $delta = dashboardDelta($current, $previous);
    if ($delta === null) {
        return 'N/D';
    }

    return number_format($delta, 2) . '%';
}

function dashboardMonthsInRange($fromDate, $toDate)
{
    $fromStamp = strtotime(substr($fromDate, 0, 7) . '-01');
    $toStamp = strtotime(substr($toDate, 0, 7) . '-01');
    if (!$fromStamp || !$toStamp || $fromStamp > $toStamp) {
        return 1;
    }

    $months = 0;
    while ($fromStamp <= $toStamp) {
        $months++;
        $fromStamp = strtotime('+1 month', $fromStamp);
    }

    return max(1, $months);
}

function dashboardCompareRowsByAmountDesc($a, $b)
{
    if ((float) $a['amount'] === (float) $b['amount']) {
        return strcmp((string) ($a['label'] ?? $a['unit'] ?? ''), (string) ($b['label'] ?? $b['unit'] ?? ''));
    }

    return ((float) $a['amount'] > (float) $b['amount']) ? -1 : 1;
}

$form_refresh = !empty($_POST['form_refresh']);
$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = isset($_POST['form_facility']) ? trim((string) $_POST['form_facility']) : '';
$form_provider = isset($_POST['form_provider']) ? trim((string) $_POST['form_provider']) : '';
$form_procedure = isset($_POST['form_procedure']) ? trim((string) $_POST['form_procedure']) : '';

if (!isValidIsoDateDashboard($form_from_date)) {
    $form_from_date = date('Y-01-01');
}

if (!isValidIsoDateDashboard($form_to_date)) {
    $form_to_date = date('Y-m-d');
}

if ($form_from_date > $form_to_date) {
    $tempDate = $form_from_date;
    $form_from_date = $form_to_date;
    $form_to_date = $tempDate;
}

if ($form_facility !== '' && !ctype_digit($form_facility)) {
    $form_facility = '';
}

if ($form_provider !== '' && !ctype_digit($form_provider)) {
    $form_provider = '';
}

$facilityOptions = array();
$facilityRes = sqlStatement("SELECT id, name FROM facility ORDER BY name");
while ($frow = sqlFetchArray($facilityRes)) {
    $facilityOptions[] = $frow;
}

$providerOptions = array();
$providerRes = sqlStatement("SELECT id, fname, lname FROM users WHERE active = 1 ORDER BY lname, fname");
while ($prow = sqlFetchArray($providerRes)) {
    $providerOptions[] = $prow;
}

$unitNames = array('Servicios Oftalmologicos', 'Imagenes', 'PNI', 'Cirugias', 'Otros');
$unitSummary = array();
foreach ($unitNames as $unitName) {
    $unitSummary[$unitName] = array(
        'unit' => $unitName,
        'amount' => 0.0,
        'lines' => 0,
        'encounters' => array(),
        'patients' => array(),
    );
}

$kpis = array(
    'evaluadas' => 0,
    'realizadas' => 0,
    'facturadas' => 0,
    'pendientes' => 0,
    'perdida' => 0,
    'unique_patients' => 0,
    'total_billed' => 0.0,
    'total_collected' => 0.0,
    'avg_ticket' => 0.0,
    'collection_rate' => 0.0,
    'pending_rate' => 0.0,
    'loss_rate' => 0.0,
);

$encounterIndex = array();
$encounterPidDate = array();
$appointmentPidDate = array();
$surgeryAppointmentStatusByPidDate = array();
$billingEncounterKeys = array();
$doctorSummary = array();
$rawCategorySummary = array();
$topProcedures = array();
$appointmentStatusSummary = array();
$monthlyFunnel = array();
$monthlyUnitAmounts = array();
$alerts = array();
$detailRows = array();
$monthsInRange = dashboardMonthsInRange($form_from_date, $form_to_date);
$superiorCategorySummary = array(
    'Particulares' => array('label' => 'Particulares', 'amount' => 0.0, 'collected' => 0.0, 'encounters' => array(), 'patients' => array()),
    'Privados' => array('label' => 'Privados', 'amount' => 0.0, 'collected' => 0.0, 'encounters' => array(), 'patients' => array()),
);

if ($form_refresh) {
    $ensureMonth = function ($monthKey) use (&$monthlyFunnel, &$monthlyUnitAmounts, $unitNames) {
        if (!isset($monthlyFunnel[$monthKey])) {
            $monthlyFunnel[$monthKey] = array(
                'label' => dashboardMonthLabelEs($monthKey),
                'Facturacion' => 0.0,
                'Recaudado' => 0.0,
                'Evaluadas' => 0,
                'Realizadas' => 0,
                'Facturadas' => 0,
                'Pendientes' => 0,
                'Perdida' => 0,
            );
        }

        if (!isset($monthlyUnitAmounts[$monthKey])) {
            $monthlyUnitAmounts[$monthKey] = array(
                'label' => dashboardMonthLabelEs($monthKey),
            );
            foreach ($unitNames as $unitName) {
                $monthlyUnitAmounts[$monthKey][$unitName] = 0.0;
                $monthlyUnitAmounts[$monthKey][$unitName . '_encounters'] = array();
            }
        }
    };

    $encounterSql = "SELECT
            DATE(fe.date) AS service_date,
            fe.encounter,
            fe.pid,
            p.pubpid,
            CONCAT_WS(' ', p.fname, p.lname, p.lname2) AS patient_name,
            COALESCE(NULLIF(loprice.title, ''), NULLIF(p.pricelevel, ''), 'Particular') AS payer_name,
            COALESCE(NULLIF(p.pricelevel, ''), 'STANDARD') AS effective_pricelevel,
            COALESCE(fa.name, 'Sin sede') AS facility_name,
            fe.facility_id,
            fe.reason,
            COALESCE(fe.provider_id, 0) AS provider_id,
            TRIM(CONCAT(COALESCE(NULLIF(u.lname, ''), ''), CASE WHEN COALESCE(NULLIF(u.lname, ''), '') != '' AND COALESCE(NULLIF(u.fname, ''), '') != '' THEN ', ' ELSE '' END, COALESCE(NULLIF(u.fname, ''), 'Sin medico'))) AS provider_name
        FROM form_encounter AS fe
        INNER JOIN patient_data AS p ON p.pid = fe.pid
        LEFT JOIN users AS u ON u.id = fe.provider_id
        LEFT JOIN facility AS fa ON fa.id = fe.facility_id
        LEFT JOIN list_options AS loprice ON loprice.list_id = 'pricelevel' AND loprice.option_id = p.pricelevel AND loprice.activity = 1
        WHERE fe.date >= ? AND fe.date <= ?
            AND UPPER(COALESCE(NULLIF(p.pricelevel, ''), 'STANDARD')) NOT IN (?, ?)";
    $encounterBind = array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59', 'IESS', 'MSP');

    if ($form_facility !== '') {
        $encounterSql .= " AND fe.facility_id = ? ";
        $encounterBind[] = $form_facility;
    }

    if ($form_provider !== '') {
        $encounterSql .= " AND fe.provider_id = ? ";
        $encounterBind[] = $form_provider;
    }

    if ($form_procedure !== '') {
        $encounterSql .= " AND COALESCE(fe.reason, '') LIKE ? ";
        $encounterBind[] = '%' . $form_procedure . '%';
    }

    $encounterSql .= " ORDER BY fe.date DESC, fe.encounter DESC";
    $encounterRes = sqlStatement($encounterSql, $encounterBind);
    while ($row = sqlFetchArray($encounterRes)) {
        $encounterKey = $row['pid'] . ':' . $row['encounter'];
        $pidDateKey = $row['pid'] . ':' . $row['service_date'];
        $monthKey = substr($row['service_date'], 0, 7);
        $ensureMonth($monthKey);

        $encounterIndex[$encounterKey] = array(
            'encounter_key' => $encounterKey,
            'service_date' => $row['service_date'],
            'encounter' => $row['encounter'],
            'pid' => $row['pid'],
            'pubpid' => $row['pubpid'],
            'patient_name' => $row['patient_name'],
            'payer_name' => $row['payer_name'],
            'superior_category' => dashboardSuperiorCategory($row['effective_pricelevel']),
            'facility_name' => $row['facility_name'],
            'provider_name' => $row['provider_name'],
            'reason' => $row['reason'],
            'billed_amount' => 0.0,
            'collected_amount' => 0.0,
            'billing_lines' => 0,
            'units' => array(),
            'dominant_unit' => 'Sin clasificar',
            'appt_status_title' => '',
            'appt_status_code' => '',
            'appt_title' => '',
        );

        if (!isset($encounterPidDate[$pidDateKey])) {
            $encounterPidDate[$pidDateKey] = array();
        }
        $encounterPidDate[$pidDateKey][] = $encounterKey;
        $monthlyFunnel[$monthKey]['Realizadas']++;
    }

    $appointmentSql = "SELECT
            e.pc_eid,
            e.pc_eventDate AS service_date,
            e.pc_startTime,
            e.pc_pid AS pid,
            p.pubpid,
            CONCAT_WS(' ', p.fname, p.lname, p.lname2) AS patient_name,
            COALESCE(NULLIF(loprice.title, ''), NULLIF(p.pricelevel, ''), 'Particular') AS payer_name,
            COALESCE(NULLIF(p.pricelevel, ''), 'STANDARD') AS effective_pricelevel,
            COALESCE(fa.name, 'Sin sede') AS facility_name,
            e.pc_facility AS facility_id,
            TRIM(CONCAT(COALESCE(NULLIF(u.lname, ''), ''), CASE WHEN COALESCE(NULLIF(u.lname, ''), '') != '' AND COALESCE(NULLIF(u.fname, ''), '') != '' THEN ', ' ELSE '' END, COALESCE(NULLIF(u.fname, ''), 'Sin medico'))) AS provider_name,
            e.pc_title,
            e.pc_hometext,
            e.pc_catid,
            e.pc_apptstatus,
            COALESCE(loa.title, e.pc_apptstatus) AS appt_status_title,
            loa.toggle_setting_1 AS appt_toggle_1,
            loa.toggle_setting_2 AS appt_toggle_2
        FROM openemr_postcalendar_events AS e
        INNER JOIN patient_data AS p ON p.pid = e.pc_pid
        LEFT JOIN facility AS fa ON fa.id = e.pc_facility
        LEFT JOIN users AS u ON u.id = CAST(e.pc_aid AS UNSIGNED)
        LEFT JOIN list_options AS loprice ON loprice.list_id = 'pricelevel' AND loprice.option_id = p.pricelevel AND loprice.activity = 1
        LEFT JOIN list_options AS loa ON loa.list_id = 'apptstat' AND loa.option_id = e.pc_apptstatus AND loa.activity = 1
        WHERE e.pc_eventDate >= ? AND e.pc_eventDate <= ?
            AND e.pc_pid != ''
            AND UPPER(COALESCE(NULLIF(p.pricelevel, ''), 'STANDARD')) NOT IN (?, ?)";
    $appointmentBind = array($form_from_date, $form_to_date, 'IESS', 'MSP');

    if ($form_facility !== '') {
        $appointmentSql .= " AND e.pc_facility = ? ";
        $appointmentBind[] = $form_facility;
    }

    if ($form_provider !== '') {
        $appointmentSql .= " AND CAST(e.pc_aid AS UNSIGNED) = ? ";
        $appointmentBind[] = $form_provider;
    }

    if ($form_procedure !== '') {
        $appointmentSql .= " AND (COALESCE(e.pc_title, '') LIKE ? OR COALESCE(e.pc_hometext, '') LIKE ?) ";
        $appointmentBind[] = '%' . $form_procedure . '%';
        $appointmentBind[] = '%' . $form_procedure . '%';
    }

    $appointmentSql .= " ORDER BY e.pc_eventDate DESC, e.pc_startTime DESC, e.pc_eid DESC";
    $appointmentRes = sqlStatement($appointmentSql, $appointmentBind);
    while ($row = sqlFetchArray($appointmentRes)) {
        $pidDateKey = $row['pid'] . ':' . $row['service_date'];
        $monthKey = substr($row['service_date'], 0, 7);
        $ensureMonth($monthKey);

        if (!isset($appointmentPidDate[$pidDateKey])) {
            $appointmentPidDate[$pidDateKey] = array();
        }
        $appointmentPidDate[$pidDateKey][] = $row;
        $monthlyFunnel[$monthKey]['Evaluadas']++;

        if (in_array((int) $row['pc_catid'], array(15, 19), true)) {
            if (!isset($surgeryAppointmentStatusByPidDate[$pidDateKey])) {
                $surgeryAppointmentStatusByPidDate[$pidDateKey] = array(
                    'has_surgery_appointment' => true,
                    'has_eligible_status' => false,
                    'status_labels' => array(),
                );
            }

            if (dashboardSurgeryStatusEligible($row['pc_apptstatus'], $row['appt_toggle_1'], $row['appt_toggle_2'])) {
                $surgeryAppointmentStatusByPidDate[$pidDateKey]['has_eligible_status'] = true;
            }

            $statusLabel = trim((string) $row['appt_status_title']);
            if ($statusLabel === '') {
                $statusLabel = trim((string) $row['pc_apptstatus']);
            }
            if ($statusLabel !== '') {
                $surgeryAppointmentStatusByPidDate[$pidDateKey]['status_labels'][$statusLabel] = true;
            }
        }

        $statusKey = trim((string) $row['appt_status_title']) === '' ? 'Sin estado' : $row['appt_status_title'];
        if (!isset($appointmentStatusSummary[$statusKey])) {
            $appointmentStatusSummary[$statusKey] = array('label' => $statusKey, 'count' => 0);
        }
        $appointmentStatusSummary[$statusKey]['count']++;
    }

    $billingSql = "SELECT
            DATE(fe.date) AS service_date,
            fe.encounter,
            fe.pid,
            p.pubpid,
            CONCAT_WS(' ', p.fname, p.lname, p.lname2) AS patient_name,
            COALESCE(NULLIF(loprice.title, ''), NULLIF(p.pricelevel, ''), 'Particular') AS payer_name,
            COALESCE(NULLIF(b.pricelevel, ''), NULLIF(p.pricelevel, ''), 'STANDARD') AS effective_pricelevel,
            COALESCE(fa.name, 'Sin sede') AS facility_name,
            COALESCE(NULLIF(b.provider_id, 0), fe.provider_id, 0) AS provider_id,
            TRIM(CONCAT(COALESCE(NULLIF(u.lname, ''), ''), CASE WHEN COALESCE(NULLIF(u.lname, ''), '') != '' AND COALESCE(NULLIF(u.fname, ''), '') != '' THEN ', ' ELSE '' END, COALESCE(NULLIF(u.fname, ''), 'Sin medico'))) AS provider_name,
            b.id AS billing_id,
            b.code,
            b.modifier,
            b.fee,
            COALESCE(NULLIF(b.units, 0), 1) AS units,
            COALESCE(NULLIF(c.superbill, ''), '') AS code_category,
            COALESCE(NULLIF(los.title, ''), NULLIF(c.superbill, ''), '') AS code_category_title,
            COALESCE(NULLIF(c.code_text_short, ''), NULLIF(c.code_text, ''), NULLIF(b.code_text, ''), b.code) AS procedure_text
        FROM form_encounter AS fe
        INNER JOIN patient_data AS p ON p.pid = fe.pid
        INNER JOIN billing AS b ON b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1
        INNER JOIN code_types AS ct ON ct.ct_key = b.code_type AND ct.ct_fee = 1
        LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND COALESCE(c.modifier, '') = COALESCE(b.modifier, '')
        LEFT JOIN users AS u ON u.id = COALESCE(NULLIF(b.provider_id, 0), fe.provider_id)
        LEFT JOIN facility AS fa ON fa.id = fe.facility_id
        LEFT JOIN list_options AS loprice ON loprice.list_id = 'pricelevel' AND loprice.option_id = p.pricelevel AND loprice.activity = 1
        LEFT JOIN list_options AS los ON los.list_id = 'superbill' AND los.option_id = c.superbill AND los.activity = 1
        WHERE fe.date >= ? AND fe.date <= ?
            AND UPPER(COALESCE(NULLIF(b.pricelevel, ''), NULLIF(p.pricelevel, ''), 'STANDARD')) NOT IN (?, ?)";
    $billingBind = array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59', 'IESS', 'MSP');

    if ($form_facility !== '') {
        $billingSql .= " AND fe.facility_id = ? ";
        $billingBind[] = $form_facility;
    }

    if ($form_provider !== '') {
        $billingSql .= " AND COALESCE(NULLIF(b.provider_id, 0), fe.provider_id) = ? ";
        $billingBind[] = $form_provider;
    }

    if ($form_procedure !== '') {
        $billingSql .= " AND (
                b.code LIKE ?
                OR COALESCE(c.code_text_short, '') LIKE ?
                OR COALESCE(c.code_text, '') LIKE ?
                OR COALESCE(b.code_text, '') LIKE ?
            ) ";
        $billingBind[] = '%' . $form_procedure . '%';
        $billingBind[] = '%' . $form_procedure . '%';
        $billingBind[] = '%' . $form_procedure . '%';
        $billingBind[] = '%' . $form_procedure . '%';
    }

    $billingSql .= " ORDER BY fe.date DESC, fe.encounter DESC, b.id DESC";
    $billingRes = sqlStatement($billingSql, $billingBind);
    while ($row = sqlFetchArray($billingRes)) {
        $encounterKey = $row['pid'] . ':' . $row['encounter'];
        $monthKey = substr($row['service_date'], 0, 7);
        $ensureMonth($monthKey);

        if (!isset($encounterIndex[$encounterKey])) {
            $encounterIndex[$encounterKey] = array(
                'encounter_key' => $encounterKey,
                'service_date' => $row['service_date'],
                'encounter' => $row['encounter'],
                'pid' => $row['pid'],
                'pubpid' => $row['pubpid'],
                'patient_name' => $row['patient_name'],
                'payer_name' => $row['payer_name'],
                'superior_category' => dashboardSuperiorCategory($row['effective_pricelevel']),
                'facility_name' => $row['facility_name'],
                'provider_name' => $row['provider_name'],
                'reason' => '',
                'billed_amount' => 0.0,
                'collected_amount' => 0.0,
                'billing_lines' => 0,
                'units' => array(),
                'dominant_unit' => 'Sin clasificar',
                'appt_status_title' => '',
                'appt_status_code' => '',
                'appt_title' => '',
            );
            $monthlyFunnel[$monthKey]['Realizadas']++;
        }

        $amount = (float) $row['fee'];
        $unitName = dashboardBusinessUnit($row['code_category']);
        $superiorCategory = dashboardSuperiorCategory($row['effective_pricelevel']);
        $procedureLabel = trim($row['code'] . (!empty($row['modifier']) ? '-' . $row['modifier'] : '') . ' ' . $row['procedure_text']);
        $pidDateKey = $row['pid'] . ':' . $row['service_date'];
        $skipSurgeryLine = $unitName === 'Cirugias'
            && !empty($surgeryAppointmentStatusByPidDate[$pidDateKey]['has_surgery_appointment'])
            && empty($surgeryAppointmentStatusByPidDate[$pidDateKey]['has_eligible_status']);

        if ($skipSurgeryLine) {
            continue;
        }

        $encounterIndex[$encounterKey]['billed_amount'] += $amount;
        $encounterIndex[$encounterKey]['billing_lines']++;
        if (!isset($encounterIndex[$encounterKey]['units'][$unitName])) {
            $encounterIndex[$encounterKey]['units'][$unitName] = 0.0;
        }
        $encounterIndex[$encounterKey]['units'][$unitName] += $amount;

        $billingEncounterKeys[$encounterKey] = true;
        $monthlyFunnel[$monthKey]['Facturacion'] += $amount;
        $monthlyUnitAmounts[$monthKey][$unitName] += $amount;
        $monthlyUnitAmounts[$monthKey][$unitName . '_encounters'][$encounterKey] = true;

        $unitSummary[$unitName]['amount'] += $amount;
        $unitSummary[$unitName]['lines']++;
        $unitSummary[$unitName]['encounters'][$encounterKey] = true;
        $unitSummary[$unitName]['patients'][$row['pid']] = true;

        $superiorCategorySummary[$superiorCategory]['amount'] += $amount;
        $superiorCategorySummary[$superiorCategory]['encounters'][$encounterKey] = true;
        $superiorCategorySummary[$superiorCategory]['patients'][$row['pid']] = true;

        $rawCategoryKey = dashboardCategoryKey($row['code_category']);
        if (!isset($rawCategorySummary[$rawCategoryKey])) {
            $rawCategorySummary[$rawCategoryKey] = array(
                'label' => dashboardCategoryLabel($row['code_category_title']),
                'amount' => 0.0,
            );
        }
        $rawCategorySummary[$rawCategoryKey]['amount'] += $amount;

        $procedureKey = trim($row['code'] . '|' . $row['modifier'] . '|' . $row['procedure_text']);
        if (!isset($topProcedures[$procedureKey])) {
            $topProcedures[$procedureKey] = array(
                'label' => $procedureLabel,
                'amount' => 0.0,
                'lines' => 0,
                'unit' => $unitName,
            );
        }
        $topProcedures[$procedureKey]['amount'] += $amount;
        $topProcedures[$procedureKey]['lines']++;

        $doctorKey = trim((string) $row['provider_name']) === '' ? 'Sin medico' : $row['provider_name'];
        if (!isset($doctorSummary[$doctorKey])) {
            $doctorSummary[$doctorKey] = array(
                'label' => $doctorKey,
                'amount' => 0.0,
                'encounters' => array(),
            );
        }
        $doctorSummary[$doctorKey]['amount'] += $amount;
        $doctorSummary[$doctorKey]['encounters'][$encounterKey] = true;
    }

    $paymentSql = "SELECT
            a.pid,
            a.encounter,
            COALESCE(SUM(a.pay_amount), 0) AS total_paid
        FROM ar_activity AS a
        INNER JOIN form_encounter AS fe ON fe.pid = a.pid AND fe.encounter = a.encounter
        INNER JOIN patient_data AS p ON p.pid = fe.pid
        WHERE fe.date >= ? AND fe.date <= ?
            AND a.pay_amount != 0
            AND UPPER(COALESCE(NULLIF(p.pricelevel, ''), 'STANDARD')) NOT IN (?, ?)";
    $paymentBind = array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59', 'IESS', 'MSP');

    if ($form_facility !== '') {
        $paymentSql .= " AND fe.facility_id = ? ";
        $paymentBind[] = $form_facility;
    }

    $paymentSql .= " GROUP BY a.pid, a.encounter";
    $paymentRes = sqlStatement($paymentSql, $paymentBind);
    while ($payRow = sqlFetchArray($paymentRes)) {
        $encounterKey = $payRow['pid'] . ':' . $payRow['encounter'];
        if (empty($encounterIndex[$encounterKey])) {
            continue;
        }

        $collectedAmount = (float) $payRow['total_paid'];
        $encounterIndex[$encounterKey]['collected_amount'] = $collectedAmount;

        $monthKey = substr($encounterIndex[$encounterKey]['service_date'], 0, 7);
        $ensureMonth($monthKey);
        $monthlyFunnel[$monthKey]['Recaudado'] += $collectedAmount;

        $superiorCategory = $encounterIndex[$encounterKey]['superior_category'];
        if (isset($superiorCategorySummary[$superiorCategory])) {
            $superiorCategorySummary[$superiorCategory]['collected'] += $collectedAmount;
        }
    }

    $uniquePatients = array();
    foreach ($appointmentPidDate as $pidDateAppointments) {
        foreach ($pidDateAppointments as $appt) {
            $uniquePatients[$appt['pid']] = true;
        }
    }

    foreach ($encounterIndex as $encounterKey => &$encounterRow) {
        $uniquePatients[$encounterRow['pid']] = true;
        $pidDateKey = $encounterRow['pid'] . ':' . $encounterRow['service_date'];

        if (!empty($appointmentPidDate[$pidDateKey])) {
            $firstAppointment = $appointmentPidDate[$pidDateKey][0];
            $encounterRow['appt_status_title'] = $firstAppointment['appt_status_title'];
            $encounterRow['appt_status_code'] = $firstAppointment['pc_apptstatus'];
            $encounterRow['appt_title'] = $firstAppointment['pc_title'];
        }

        if (empty($encounterRow['units'])) {
            $encounterRow['dominant_unit'] = 'Sin clasificar';
        } else {
            arsort($encounterRow['units']);
            $encounterRow['dominant_unit'] = (string) key($encounterRow['units']);
        }

        $monthKey = substr($encounterRow['service_date'], 0, 7);
        $ensureMonth($monthKey);
        if (isset($billingEncounterKeys[$encounterKey])) {
            $monthlyFunnel[$monthKey]['Facturadas']++;
        } else {
            $monthlyFunnel[$monthKey]['Pendientes']++;
        }
    }
    unset($encounterRow);

    foreach ($encounterIndex as $encounterRow) {
        $pidDateKey = $encounterRow['pid'] . ':' . $encounterRow['service_date'];
        $monthKey = substr($encounterRow['service_date'], 0, 7);
        if (empty($appointmentPidDate[$pidDateKey])) {
            $monthlyFunnel[$monthKey]['Evaluadas']++;
        }
    }

    foreach ($appointmentPidDate as $pidDateKey => $appointments) {
        $matched = !empty($encounterPidDate[$pidDateKey]);
        if ($matched) {
            continue;
        }

        foreach ($appointments as $apptRow) {
            $monthKey = substr($apptRow['service_date'], 0, 7);
            $ensureMonth($monthKey);
            $monthlyFunnel[$monthKey]['Perdida']++;
        }
    }

    ksort($monthlyFunnel);
    ksort($monthlyUnitAmounts);

    $kpis['evaluadas'] = 0;
    $kpis['realizadas'] = count($encounterIndex);
    $kpis['facturadas'] = count($billingEncounterKeys);
    $kpis['pendientes'] = max(0, $kpis['realizadas'] - $kpis['facturadas']);
    $kpis['perdida'] = 0;
    $kpis['unique_patients'] = count($uniquePatients);
    $kpis['total_billed'] = 0.0;
    $kpis['total_collected'] = 0.0;

    foreach ($monthlyFunnel as $monthRow) {
        $kpis['evaluadas'] += (int) $monthRow['Evaluadas'];
        $kpis['perdida'] += (int) $monthRow['Perdida'];
        $kpis['total_billed'] += (float) $monthRow['Facturacion'];
        $kpis['total_collected'] += (float) $monthRow['Recaudado'];
    }

    $kpis['avg_ticket'] = $kpis['realizadas'] > 0 ? ($kpis['total_billed'] / $kpis['realizadas']) : 0.0;
    $kpis['collection_rate'] = $kpis['total_billed'] > 0 ? (($kpis['total_collected'] / $kpis['total_billed']) * 100) : 0.0;
    $kpis['pending_rate'] = $kpis['realizadas'] > 0 ? (($kpis['pendientes'] / $kpis['realizadas']) * 100) : 0.0;
    $kpis['loss_rate'] = $kpis['evaluadas'] > 0 ? (($kpis['perdida'] / $kpis['evaluadas']) * 100) : 0.0;

    $currentMonth = substr($form_to_date, 0, 7);
    $previousMonth = date('Y-m', strtotime($currentMonth . '-01 -1 month'));

    $currentOverall = $monthlyFunnel[$currentMonth] ?? array('Facturacion' => 0.0, 'Recaudado' => 0.0, 'Realizadas' => 0);
    $previousOverall = $monthlyFunnel[$previousMonth] ?? array('Facturacion' => 0.0, 'Recaudado' => 0.0, 'Realizadas' => 0);
    $overallAmountDelta = dashboardDelta($currentOverall['Facturacion'], $previousOverall['Facturacion']);
    $overallCountDelta = dashboardDelta($currentOverall['Realizadas'], $previousOverall['Realizadas']);
    $overallBadge = dashboardGrowthBadge($overallAmountDelta, $overallCountDelta);

    $objectiveCard = array(
        'status' => $overallBadge,
        'total_amount' => $kpis['total_billed'],
        'total_collected' => $kpis['total_collected'],
        'avg_monthly_amount' => $kpis['total_billed'] / $monthsInRange,
        'avg_monthly_collected' => $kpis['total_collected'] / $monthsInRange,
        'attentions' => $kpis['realizadas'],
        'avg_monthly_attentions' => $kpis['realizadas'] / $monthsInRange,
        'avg_ticket' => $kpis['avg_ticket'],
        'unique_patients' => $kpis['unique_patients'],
        'current_month' => $currentMonth,
        'previous_month' => $previousMonth,
        'current_amount' => $currentOverall['Facturacion'],
        'previous_amount' => $previousOverall['Facturacion'],
        'current_collected' => $currentOverall['Recaudado'],
        'previous_collected' => $previousOverall['Recaudado'],
        'current_attentions' => $currentOverall['Realizadas'],
        'previous_attentions' => $previousOverall['Realizadas'],
        'current_ticket' => $currentOverall['Realizadas'] > 0 ? ($currentOverall['Facturacion'] / $currentOverall['Realizadas']) : 0.0,
        'previous_ticket' => $previousOverall['Realizadas'] > 0 ? ($previousOverall['Facturacion'] / $previousOverall['Realizadas']) : 0.0,
    );

    $unitCards = array();
    foreach ($unitNames as $unitName) {
        $totalAmount = (float) $unitSummary[$unitName]['amount'];
        $encounterCount = count($unitSummary[$unitName]['encounters']);
        $patientCount = count($unitSummary[$unitName]['patients']);
        $currentAmount = isset($monthlyUnitAmounts[$currentMonth][$unitName]) ? (float) $monthlyUnitAmounts[$currentMonth][$unitName] : 0.0;
        $previousAmount = isset($monthlyUnitAmounts[$previousMonth][$unitName]) ? (float) $monthlyUnitAmounts[$previousMonth][$unitName] : 0.0;
        $currentCount = isset($monthlyUnitAmounts[$currentMonth][$unitName . '_encounters']) ? count($monthlyUnitAmounts[$currentMonth][$unitName . '_encounters']) : 0;
        $previousCount = isset($monthlyUnitAmounts[$previousMonth][$unitName . '_encounters']) ? count($monthlyUnitAmounts[$previousMonth][$unitName . '_encounters']) : 0;
        $amountDelta = dashboardDelta($currentAmount, $previousAmount);
        $countDelta = dashboardDelta($currentCount, $previousCount);

        $unitCards[] = array(
            'unit' => $unitName,
            'status' => dashboardGrowthBadge($amountDelta, $countDelta),
            'total_amount' => $totalAmount,
            'avg_monthly_amount' => $totalAmount / $monthsInRange,
            'attentions' => $encounterCount,
            'avg_monthly_attentions' => $encounterCount / $monthsInRange,
            'avg_ticket' => $encounterCount > 0 ? ($totalAmount / $encounterCount) : 0.0,
            'patients' => $patientCount,
            'current_amount' => $currentAmount,
            'previous_amount' => $previousAmount,
            'current_count' => $currentCount,
            'previous_count' => $previousCount,
            'current_ticket' => $currentCount > 0 ? ($currentAmount / $currentCount) : 0.0,
            'previous_ticket' => $previousCount > 0 ? ($previousAmount / $previousCount) : 0.0,
        );
    }

    usort($unitCards, function ($a, $b) use ($unitNames) {
        $posA = array_search($a['unit'], $unitNames, true);
        $posB = array_search($b['unit'], $unitNames, true);
        return ((int) $posA < (int) $posB) ? -1 : 1;
    });

    $highestUnit = '';
    $highestUnitAmount = 0.0;
    foreach ($unitSummary as $unitName => $summary) {
        if ($summary['amount'] > $highestUnitAmount) {
            $highestUnitAmount = (float) $summary['amount'];
            $highestUnit = $unitName;
        }
    }

    if ($kpis['pending_rate'] >= 20) {
        $alerts[] = 'El porcentaje pendiente de facturar se mantiene alto (' . dashboardPct($kpis['pending_rate'], 2) . ').';
    }

    if ($kpis['loss_rate'] >= 15) {
        $alerts[] = 'La perdida operativa del periodo es relevante (' . dashboardPct($kpis['loss_rate'], 2) . ' de las evaluadas).';
    }

    if ($highestUnit !== '') {
        $alerts[] = $highestUnit . ' lidera la facturacion del periodo con ' . dashboardMoney($highestUnitAmount) . '.';
    }

    if ($objectiveCard['current_ticket'] > 0 && $objectiveCard['previous_ticket'] > 0 && $objectiveCard['current_ticket'] < $objectiveCard['previous_ticket']) {
        $alerts[] = 'El ticket del mes actual esta por debajo del mes anterior (' . dashboardMoney($objectiveCard['current_ticket']) . ' vs ' . dashboardMoney($objectiveCard['previous_ticket']) . ').';
    }

    $doctorSummary = array_values($doctorSummary);
    foreach ($doctorSummary as &$doctorRow) {
        $doctorRow['encounters'] = count($doctorRow['encounters']);
    }
    unset($doctorRow);
    usort($doctorSummary, 'dashboardCompareRowsByAmountDesc');

    $rawCategorySummary = array_values($rawCategorySummary);
    usort($rawCategorySummary, 'dashboardCompareRowsByAmountDesc');
    $rawCategorySummary = array_slice($rawCategorySummary, 0, 8);

    $topProcedures = array_values($topProcedures);
    usort($topProcedures, 'dashboardCompareRowsByAmountDesc');
    $topProcedures = array_slice($topProcedures, 0, 12);

    $appointmentStatusSummary = array_values($appointmentStatusSummary);
    usort($appointmentStatusSummary, function ($a, $b) {
        if ((int) $a['count'] === (int) $b['count']) {
            return strcmp((string) $a['label'], (string) $b['label']);
        }
        return ((int) $a['count'] > (int) $b['count']) ? -1 : 1;
    });

    $detailRows = array_values($encounterIndex);
    usort($detailRows, function ($a, $b) {
        if ($a['service_date'] === $b['service_date']) {
            return ((int) $a['encounter'] > (int) $b['encounter']) ? -1 : 1;
        }
        return strcmp((string) $b['service_date'], (string) $a['service_date']);
    });
    $detailRows = array_slice($detailRows, 0, 50);

    foreach ($detailRows as &$detailRow) {
        $detailRow['flow_status'] = $detailRow['billed_amount'] > 0 ? 'FACTURADA' : 'REALIZADA SIN FACTURA';
        if ($detailRow['appt_status_title'] === '') {
            $detailRow['appt_status_title'] = 'SIN CITA';
        }
    }
    unset($detailRow);
}
?>
<html>
<head>
    <title><?php echo xlt('Dashboard Particulares y Privados'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/modified/dygraphs-2-0-0/dygraph.js?v=<?php echo attr($GLOBALS['v_js_includes']); ?>"></script>

    <style>
        .dashboard-shell {
            padding: 10px 12px 18px;
            background: #f7fafc;
        }

        .card-box,
        .filters-card,
        .section-card,
        .hero-card {
            background: #ffffff;
            border: 1px solid #d8e0ea;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .filters-card,
        .section-card,
        .hero-card {
            padding: 14px;
            margin-bottom: 12px;
        }

        .section-title {
            margin: 0 0 8px;
            color: #17324d;
            font-size: 16px;
            font-weight: 700;
        }

        .section-subtitle,
        .muted-note {
            color: #5f7488;
            font-size: 12px;
        }

        .filters-grid,
        .kpi-grid,
        .two-col-grid,
        .unit-grid {
            display: grid;
            gap: 12px;
        }

        .filters-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            align-items: end;
        }

        .kpi-grid {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }

        .two-col-grid {
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        }

        .unit-grid {
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        }

        .hero-card {
            background: linear-gradient(180deg, #f8fbff 0%, #eef5fb 100%);
        }

        .hero-head,
        .status-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .kpi-card {
            padding: 14px;
            border: 1px solid #d8e0ea;
            border-radius: 12px;
            background: #ffffff;
        }

        .kpi-label {
            display: block;
            color: #4a6276;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 6px;
        }

        .kpi-value {
            font-size: 28px;
            line-height: 1.05;
            font-weight: 700;
            color: #17324d;
        }

        .kpi-foot {
            display: block;
            margin-top: 4px;
            color: #6b7d90;
            font-size: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
        }

        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .status-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-neutral {
            background: #e2e8f0;
            color: #334155;
        }

        .metrics-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .metric-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
        }

        .metric-box strong {
            display: block;
            font-size: 22px;
            color: #17324d;
            line-height: 1.15;
        }

        .metric-box small {
            display: block;
            color: #64748b;
            font-size: 12px;
        }

        .bars-wrap {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .bar-row {
            display: grid;
            grid-template-columns: minmax(110px, 170px) 1fr 90px;
            gap: 10px;
            align-items: center;
        }

        .bar-track {
            background: #edf2f7;
            border-radius: 999px;
            overflow: hidden;
            height: 12px;
        }

        .bar-fill {
            height: 12px;
            border-radius: 999px;
            background: linear-gradient(90deg, #2563eb 0%, #16a34a 100%);
        }

        .graph-box {
            min-height: 280px;
        }

        .alert-list {
            margin: 0;
            padding-left: 18px;
        }

        .alert-list li {
            margin-bottom: 8px;
        }

        .table > thead > tr > th {
            background: #eef4f8;
            color: #17324d;
            border-bottom-width: 1px;
        }

        .table > tbody > tr > td {
            vertical-align: middle;
        }

        .nowrap {
            white-space: nowrap;
        }

        @media print {
            .filters-card {
                display: none;
            }
        }
    </style>

    <script type="text/javascript">
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        var monthlyFunnelRows = <?php echo json_encode(array_values($monthlyFunnel), JSON_UNESCAPED_UNICODE); ?>;
        var monthlyUnitRows = <?php
            $monthlyUnitRowsForJs = array();
            foreach ($monthlyUnitAmounts as $monthRow) {
                $monthlyUnitRowsForJs[] = array(
                    'label' => $monthRow['label'],
                    'Servicios Oftalmologicos' => $monthRow['Servicios Oftalmologicos'],
                    'Imagenes' => $monthRow['Imagenes'],
                    'PNI' => $monthRow['PNI'],
                    'Cirugias' => $monthRow['Cirugias'],
                    'Otros' => $monthRow['Otros'],
                );
            }
            echo json_encode($monthlyUnitRowsForJs, JSON_UNESCAPED_UNICODE);
        ?>;

        function buildDygraphCsv(rows, labels) {
            var csv = labels.join(',') + "\n";
            rows.forEach(function (row) {
                var line = [row.label];
                for (var i = 1; i < labels.length; i++) {
                    line.push(row[labels[i]] || 0);
                }
                csv += line.join(',') + "\n";
            });
            return csv;
        }

        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });

            if (monthlyFunnelRows.length) {
                new Dygraph(
                    document.getElementById('facturacionTrend'),
                    buildDygraphCsv(monthlyFunnelRows, ['Fecha', 'Facturacion', 'Recaudado']),
                    {
                        title: <?php echo xlj('Generado vs Recaudado Mensual'); ?>,
                        ylabel: <?php echo xlj('Monto'); ?>,
                        xlabel: <?php echo xlj('Mes'); ?>,
                        legend: 'always',
                        labelsKMB: true,
                        strokeWidth: 3,
                        colors: ['#2563eb', '#16a34a']
                    }
                );

                new Dygraph(
                    document.getElementById('funnelTrend'),
                    buildDygraphCsv(monthlyFunnelRows, ['Fecha', 'Evaluadas', 'Realizadas', 'Facturadas', 'Perdida']),
                    {
                        title: <?php echo xlj('Embudo Mensual'); ?>,
                        ylabel: <?php echo xlj('Cantidad'); ?>,
                        xlabel: <?php echo xlj('Mes'); ?>,
                        legend: 'always',
                        strokeWidth: 2,
                        connectSeparatedPoints: true,
                        colors: ['#64748b', '#16a34a', '#0ea5e9', '#dc2626']
                    }
                );
            }

            if (monthlyUnitRows.length) {
                new Dygraph(
                    document.getElementById('unidadTrend'),
                    buildDygraphCsv(monthlyUnitRows, ['Fecha', 'Servicios Oftalmologicos', 'Imagenes', 'PNI', 'Cirugias', 'Otros']),
                    {
                        title: <?php echo xlj('Facturacion Por Unidad'); ?>,
                        ylabel: <?php echo xlj('Monto'); ?>,
                        xlabel: <?php echo xlj('Mes'); ?>,
                        legend: 'always',
                        labelsKMB: true,
                        strokeWidth: 2,
                        connectSeparatedPoints: true,
                        colors: ['#1d4ed8', '#0f766e', '#7c3aed', '#b45309', '#64748b']
                    }
                );
            }
        });
    </script>
</head>
<body class="body_top">
<div class="dashboard-shell">
    <div class="filters-card">
        <div class="section-title"><?php echo xlt('Dashboard Particulares y Privados'); ?></div>
        <div class="section-subtitle"><?php echo xlt('Cruza agenda, encounters y billing para pacientes no publicos.'); ?></div>

        <form method="post" id="theform" action="particulares_dashboard.php" onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" name="form_refresh" id="form_refresh" value="" />

            <div class="filters-grid">
                <div>
                    <label><?php echo xlt('Desde'); ?></label>
                    <input type="text" class="datepicker form-control" name="form_from_date" value="<?php echo attr(oeFormatShortDate($form_from_date)); ?>">
                </div>
                <div>
                    <label><?php echo xlt('Hasta'); ?></label>
                    <input type="text" class="datepicker form-control" name="form_to_date" value="<?php echo attr(oeFormatShortDate($form_to_date)); ?>">
                </div>
                <div>
                    <label><?php echo xlt('Sede'); ?></label>
                    <select name="form_facility" class="form-control">
                        <option value=""><?php echo xlt('Todas'); ?></option>
                        <?php foreach ($facilityOptions as $facility) { ?>
                            <option value="<?php echo attr($facility['id']); ?>"<?php echo ((string) $form_facility === (string) $facility['id']) ? ' selected' : ''; ?>>
                                <?php echo text($facility['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label><?php echo xlt('Medico'); ?></label>
                    <select name="form_provider" class="form-control">
                        <option value=""><?php echo xlt('Todos'); ?></option>
                        <?php foreach ($providerOptions as $provider) { ?>
                            <option value="<?php echo attr($provider['id']); ?>"<?php echo ((string) $form_provider === (string) $provider['id']) ? ' selected' : ''; ?>>
                                <?php echo text(trim($provider['lname'] . ', ' . $provider['fname'])); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label><?php echo xlt('Procedimiento / texto'); ?></label>
                    <input type="text" class="form-control" name="form_procedure" value="<?php echo attr($form_procedure); ?>" placeholder="<?php echo attr(xl('Ej: consulta oftalmologica')); ?>">
                </div>
                <div>
                    <a href="#" class="btn btn-default btn-save" onclick="$('#form_refresh').val('1'); $('#theform').submit(); return false;">
                        <?php echo xlt('Generar'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if ($form_refresh) { ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Evaluadas'); ?></span>
                <span class="kpi-value"><?php echo text(number_format($kpis['evaluadas'])); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Realizadas'); ?></span>
                <span class="kpi-value"><?php echo text(number_format($kpis['realizadas'])); ?></span>
                <span class="kpi-foot"><?php echo text($kpis['evaluadas'] > 0 ? dashboardPct(($kpis['realizadas'] / $kpis['evaluadas']) * 100, 2) : '0.00%'); ?> <?php echo xlt('del total evaluado'); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Facturadas'); ?></span>
                <span class="kpi-value"><?php echo text(number_format($kpis['facturadas'])); ?></span>
                <span class="kpi-foot"><?php echo text($kpis['realizadas'] > 0 ? dashboardPct(($kpis['facturadas'] / $kpis['realizadas']) * 100, 2) : '0.00%'); ?> <?php echo xlt('de las realizadas'); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Generado'); ?></span>
                <span class="kpi-value"><?php echo text(dashboardMoney($kpis['total_billed'])); ?></span>
                <span class="kpi-foot"><?php echo xlt('Billing generado'); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Colectado'); ?></span>
                <span class="kpi-value"><?php echo text(dashboardMoney($kpis['total_collected'])); ?></span>
                <span class="kpi-foot"><?php echo text(dashboardPct($kpis['collection_rate'], 2)); ?> <?php echo xlt('del generado'); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Pendientes'); ?></span>
                <span class="kpi-value"><?php echo text(number_format($kpis['pendientes'])); ?></span>
                <span class="kpi-foot"><?php echo text(dashboardPct($kpis['pending_rate'], 2)); ?> <?php echo xlt('de las realizadas'); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Perdida'); ?></span>
                <span class="kpi-value"><?php echo text(number_format($kpis['perdida'])); ?></span>
                <span class="kpi-foot"><?php echo text(dashboardPct($kpis['loss_rate'], 2)); ?> <?php echo xlt('del total evaluado'); ?></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label"><?php echo xlt('Pacientes Unicos'); ?></span>
                <span class="kpi-value"><?php echo text(number_format($kpis['unique_patients'])); ?></span>
                <span class="kpi-foot"><?php echo xlt('Particulares y privados'); ?></span>
            </div>
        </div>

        <div class="two-col-grid">
            <?php foreach ($superiorCategorySummary as $summaryRow) {
                $encountersCount = count($summaryRow['encounters']);
                $patientsCount = count($summaryRow['patients']);
                $avgTicket = $encountersCount > 0 ? ($summaryRow['amount'] / $encountersCount) : 0.0;
                $collectionRate = $summaryRow['amount'] > 0 ? (($summaryRow['collected'] / $summaryRow['amount']) * 100) : 0.0;
                ?>
                <div class="section-card">
                    <div class="status-line">
                        <div class="section-title mb-0"><?php echo text($summaryRow['label']); ?></div>
                        <span class="status-badge <?php echo attr($summaryRow['label'] === 'Particulares' ? 'status-success' : 'status-warning'); ?>">
                            <?php echo text($summaryRow['label'] === 'Particulares' ? 'STANDARD' : 'SEGUROS PRIVADOS'); ?>
                        </span>
                    </div>
                    <div class="metrics-row">
                        <div class="metric-box">
                            <small><?php echo xlt('Generado'); ?></small>
                            <strong><?php echo text(dashboardMoney($summaryRow['amount'])); ?></strong>
                        </div>
                        <div class="metric-box">
                            <small><?php echo xlt('Colectado'); ?></small>
                            <strong><?php echo text(dashboardMoney($summaryRow['collected'])); ?></strong>
                            <small><?php echo text(dashboardPct($collectionRate, 2)); ?></small>
                        </div>
                        <div class="metric-box">
                            <small><?php echo xlt('Atenciones'); ?></small>
                            <strong><?php echo text(number_format($encountersCount)); ?></strong>
                        </div>
                        <div class="metric-box">
                            <small><?php echo xlt('Ticket promedio'); ?></small>
                            <strong><?php echo text(dashboardMoney($avgTicket)); ?></strong>
                            <small><?php echo text(number_format($patientsCount)); ?> <?php echo xlt('pacientes'); ?></small>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="hero-card">
            <div class="hero-head">
                <div>
                    <div class="section-title"><?php echo xlt('Objetivo 1: crecimiento de no publicos'); ?></div>
                    <div class="section-subtitle"><?php echo xlt('Señales ejecutivas con agenda, encounters y billing real para particulares y privados.'); ?></div>
                </div>
                <span class="status-badge <?php echo attr($objectiveCard['status']['class']); ?>"><?php echo text($objectiveCard['status']['label']); ?></span>
            </div>

            <div class="metrics-row">
                <div class="metric-box">
                    <small><?php echo xlt('Generado total'); ?></small>
                    <strong><?php echo text(dashboardMoney($objectiveCard['total_amount'])); ?></strong>
                    <small><?php echo xlt('Prom. mensual'); ?> <?php echo text(dashboardMoney($objectiveCard['avg_monthly_amount'])); ?></small>
                </div>
                <div class="metric-box">
                    <small><?php echo xlt('Colectado total'); ?></small>
                    <strong><?php echo text(dashboardMoney($objectiveCard['total_collected'])); ?></strong>
                    <small><?php echo xlt('Prom. mensual'); ?> <?php echo text(dashboardMoney($objectiveCard['avg_monthly_collected'])); ?></small>
                </div>
                <div class="metric-box">
                    <small><?php echo xlt('Atenciones'); ?></small>
                    <strong><?php echo text(number_format($objectiveCard['attentions'])); ?></strong>
                    <small><?php echo xlt('Prom. mensual'); ?> <?php echo text(number_format($objectiveCard['avg_monthly_attentions'], 1)); ?></small>
                </div>
                <div class="metric-box">
                    <small><?php echo xlt('Ticket promedio'); ?></small>
                    <strong><?php echo text(dashboardMoney($objectiveCard['avg_ticket'])); ?></strong>
                    <small><?php echo text(number_format($objectiveCard['unique_patients'])); ?> <?php echo xlt('pacientes unicos'); ?></small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                    <tr>
                        <th><?php echo xlt('KR operativo'); ?></th>
                        <th class="text-right"><?php echo xlt('Actual'); ?></th>
                        <th class="text-right"><?php echo xlt('Anterior'); ?></th>
                        <th class="text-right"><?php echo xlt('Delta'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo xlt('Generado mensual'); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['current_month']) . ': ' . dashboardMoney($objectiveCard['current_amount'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['previous_month']) . ': ' . dashboardMoney($objectiveCard['previous_amount'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardDeltaLabel($objectiveCard['current_amount'], $objectiveCard['previous_amount'])); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo xlt('Colectado mensual'); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['current_month']) . ': ' . dashboardMoney($objectiveCard['current_collected'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['previous_month']) . ': ' . dashboardMoney($objectiveCard['previous_collected'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardDeltaLabel($objectiveCard['current_collected'], $objectiveCard['previous_collected'])); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo xlt('Atenciones mensuales'); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['current_month']) . ': ' . number_format($objectiveCard['current_attentions'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['previous_month']) . ': ' . number_format($objectiveCard['previous_attentions'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardDeltaLabel($objectiveCard['current_attentions'], $objectiveCard['previous_attentions'])); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo xlt('Ticket mensual'); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['current_month']) . ': ' . dashboardMoney($objectiveCard['current_ticket'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardMonthLabelEs($objectiveCard['previous_month']) . ': ' . dashboardMoney($objectiveCard['previous_ticket'])); ?></td>
                        <td class="text-right"><?php echo text(dashboardDeltaLabel($objectiveCard['current_ticket'], $objectiveCard['previous_ticket'])); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><?php echo xlt('Alertas automaticas'); ?></div>
            <?php if (empty($alerts)) { ?>
                <div class="muted-note"><?php echo xlt('No se detectaron alertas relevantes para este corte.'); ?></div>
            <?php } else { ?>
                <ul class="alert-list">
                    <?php foreach ($alerts as $alertLine) { ?>
                        <li><?php echo text($alertLine); ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>

        <div class="unit-grid">
            <?php foreach ($unitCards as $unitCard) { ?>
                <div class="section-card">
                    <div class="status-line">
                        <div class="section-title mb-0"><?php echo text($unitCard['unit']); ?></div>
                        <span class="status-badge <?php echo attr($unitCard['status']['class']); ?>"><?php echo text($unitCard['status']['label']); ?></span>
                    </div>

                    <div class="metrics-row">
                        <div class="metric-box">
                            <small><?php echo xlt('Facturacion total'); ?></small>
                            <strong><?php echo text(dashboardMoney($unitCard['total_amount'])); ?></strong>
                            <small><?php echo xlt('Prom. mensual'); ?> <?php echo text(dashboardMoney($unitCard['avg_monthly_amount'])); ?></small>
                        </div>
                        <div class="metric-box">
                            <small><?php echo xlt('Atenciones'); ?></small>
                            <strong><?php echo text(number_format($unitCard['attentions'])); ?></strong>
                            <small><?php echo xlt('Prom. mensual'); ?> <?php echo text(number_format($unitCard['avg_monthly_attentions'], 1)); ?></small>
                        </div>
                        <div class="metric-box">
                            <small><?php echo xlt('Ticket promedio'); ?></small>
                            <strong><?php echo text(dashboardMoney($unitCard['avg_ticket'])); ?></strong>
                            <small><?php echo text(number_format($unitCard['patients'])); ?> <?php echo xlt('pacientes'); ?></small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                            <tr>
                                <th><?php echo xlt('KR operativo'); ?></th>
                                <th class="text-right"><?php echo xlt('Actual'); ?></th>
                                <th class="text-right"><?php echo xlt('Anterior'); ?></th>
                                <th class="text-right"><?php echo xlt('Delta'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?php echo xlt('Facturacion mensual'); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($unitCard['current_amount'])); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($unitCard['previous_amount'])); ?></td>
                                <td class="text-right"><?php echo text(dashboardDeltaLabel($unitCard['current_amount'], $unitCard['previous_amount'])); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo xlt('Atenciones mensuales'); ?></td>
                                <td class="text-right"><?php echo text(number_format($unitCard['current_count'])); ?></td>
                                <td class="text-right"><?php echo text(number_format($unitCard['previous_count'])); ?></td>
                                <td class="text-right"><?php echo text(dashboardDeltaLabel($unitCard['current_count'], $unitCard['previous_count'])); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo xlt('Ticket mensual'); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($unitCard['current_ticket'])); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($unitCard['previous_ticket'])); ?></td>
                                <td class="text-right"><?php echo text(dashboardDeltaLabel($unitCard['current_ticket'], $unitCard['previous_ticket'])); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="two-col-grid">
            <div class="section-card">
                <div class="section-title"><?php echo xlt('Tendencia de facturacion'); ?></div>
                <div id="facturacionTrend" class="graph-box"></div>
            </div>
            <div class="section-card">
                <div class="section-title"><?php echo xlt('Embudo mensual'); ?></div>
                <div id="funnelTrend" class="graph-box"></div>
            </div>
        </div>

        <div class="two-col-grid">
            <div class="section-card">
                <div class="section-title"><?php echo xlt('Facturacion por unidad'); ?></div>
                <div id="unidadTrend" class="graph-box"></div>
            </div>
            <div class="section-card">
                <div class="section-title"><?php echo xlt('Mix por unidad'); ?></div>
                <div class="bars-wrap">
                    <?php foreach ($unitCards as $unitCard) {
                        $mixPct = $kpis['total_billed'] > 0 ? (($unitCard['total_amount'] / $kpis['total_billed']) * 100) : 0;
                        ?>
                        <div class="bar-row">
                            <div><?php echo text($unitCard['unit']); ?></div>
                            <div class="bar-track"><div class="bar-fill" style="width: <?php echo attr(max(2, round($mixPct, 1))); ?>%;"></div></div>
                            <div class="text-right"><?php echo text(dashboardMoney($unitCard['total_amount'])); ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="two-col-grid">
            <div class="section-card">
                <div class="section-title"><?php echo xlt('Top procedimientos'); ?></div>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Procedimiento'); ?></th>
                        <th><?php echo xlt('Unidad'); ?></th>
                        <th class="text-right"><?php echo xlt('Facturacion'); ?></th>
                        <th class="text-right"><?php echo xlt('Lineas'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($topProcedures)) { ?>
                        <tr><td colspan="4" class="text-center text-muted"><?php echo xlt('No hay datos para este filtro.'); ?></td></tr>
                    <?php } else { ?>
                        <?php foreach ($topProcedures as $row) { ?>
                            <tr>
                                <td><?php echo text($row['label']); ?></td>
                                <td><?php echo text($row['unit']); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($row['amount'])); ?></td>
                                <td class="text-right"><?php echo text(number_format($row['lines'])); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="section-card">
                <div class="section-title"><?php echo xlt('Top medicos facturados'); ?></div>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Medico'); ?></th>
                        <th class="text-right"><?php echo xlt('Facturacion'); ?></th>
                        <th class="text-right"><?php echo xlt('Atenciones'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($doctorSummary)) { ?>
                        <tr><td colspan="3" class="text-center text-muted"><?php echo xlt('No hay datos para este filtro.'); ?></td></tr>
                    <?php } else { ?>
                        <?php foreach ($doctorSummary as $row) { ?>
                            <tr>
                                <td><?php echo text($row['label']); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($row['amount'])); ?></td>
                                <td class="text-right"><?php echo text(number_format($row['encounters'])); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="two-col-grid">
            <div class="section-card">
                <div class="section-title"><?php echo xlt('Estados de agenda'); ?></div>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Estado'); ?></th>
                        <th class="text-right"><?php echo xlt('Cantidad'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($appointmentStatusSummary)) { ?>
                        <tr><td colspan="2" class="text-center text-muted"><?php echo xlt('No hay citas para este filtro.'); ?></td></tr>
                    <?php } else { ?>
                        <?php foreach ($appointmentStatusSummary as $row) { ?>
                            <tr>
                                <td><?php echo text($row['label']); ?></td>
                                <td class="text-right"><?php echo text(number_format($row['count'])); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="section-card">
                <div class="section-title"><?php echo xlt('Mix por categoria superbill'); ?></div>
                <div class="bars-wrap">
                    <?php foreach ($rawCategorySummary as $row) {
                        $mixPct = $kpis['total_billed'] > 0 ? (($row['amount'] / $kpis['total_billed']) * 100) : 0;
                        ?>
                        <div class="bar-row">
                            <div><?php echo text($row['label']); ?></div>
                            <div class="bar-track"><div class="bar-fill" style="width: <?php echo attr(max(2, round($mixPct, 1))); ?>%;"></div></div>
                            <div class="text-right"><?php echo text(dashboardMoney($row['amount'])); ?></div>
                        </div>
                    <?php } ?>
                </div>
                <div class="muted-note"><?php echo xlt('Cirugias agrupa procedimiento, derecho de sala, anestesia, ayudante, equipos y materiales.'); ?></div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><?php echo xlt('Detalle reciente de realizadas'); ?></div>
            <div class="section-subtitle"><?php echo xlt('Se muestra una vista operativa de encounters realizados en el rango, con estado de agenda asociado cuando existe.'); ?></div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Fecha'); ?></th>
                        <th><?php echo xlt('HC'); ?></th>
                        <th><?php echo xlt('Paciente'); ?></th>
                        <th><?php echo xlt('Sede'); ?></th>
                        <th><?php echo xlt('Medico'); ?></th>
                        <th><?php echo xlt('Agenda'); ?></th>
                        <th><?php echo xlt('Unidad'); ?></th>
                        <th><?php echo xlt('Estado'); ?></th>
                        <th class="text-right"><?php echo xlt('Generado'); ?></th>
                        <th class="text-right"><?php echo xlt('Colectado'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($detailRows)) { ?>
                        <tr><td colspan="10" class="text-center text-muted"><?php echo xlt('No hay datos para este filtro.'); ?></td></tr>
                    <?php } else { ?>
                        <?php foreach ($detailRows as $row) { ?>
                            <tr>
                                <td class="nowrap"><?php echo text(oeFormatShortDate($row['service_date'])); ?></td>
                                <td><?php echo text($row['pubpid']); ?></td>
                                <td><?php echo text($row['patient_name']); ?></td>
                                <td><?php echo text($row['facility_name']); ?></td>
                                <td><?php echo text($row['provider_name']); ?></td>
                                <td><?php echo text($row['appt_status_title']); ?></td>
                                <td><?php echo text($row['dominant_unit']); ?></td>
                                <td><?php echo text($row['flow_status']); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($row['billed_amount'])); ?></td>
                                <td class="text-right"><?php echo text(dashboardMoney($row['collected_amount'])); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
</div>
</body>
</html>
