<?php
/**
 * This report lists referrals for a given date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

/**
 * Validate YYYY-MM-DD date strings.
 *
 * @param string $value
 * @return bool
 */
function isValidIsoDate($value)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }

    $parts = explode('-', $value);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

/**
 * Very lightweight specialty classifier from referral reason text.
 * Returns: catarata | retina | glaucoma | pterigion | mixto_retina_catarata | unknown
 *
 * @param string $reason
 * @return string
 */
function classifyReferralSpecialty($reason)
{
    $text = trim((string)$reason);
    if ($text === '') {
        return 'unknown';
    }

    $text = mb_strtolower($text, 'UTF-8');

    $isRetina = (strpos($text, 'retina') !== false) ||
        (strpos($text, 'retinopat') !== false) ||
        (strpos($text, 'vitrect') !== false) ||
        (strpos($text, 'vítre') !== false) ||
        (strpos($text, 'vitre') !== false) ||
        (strpos($text, 'desprendimiento') !== false) ||
        (strpos($text, 'hemorragia') !== false) ||
        (strpos($text, 'macula') !== false) ||
        (strpos($text, 'mácula') !== false);

    $isCataract = (strpos($text, 'catarata') !== false) ||
        (strpos($text, 'facoemuls') !== false) ||
        (strpos($text, 'lente intraocular') !== false);

    if ($isRetina && $isCataract) {
        return 'mixto_retina_catarata';
    }

    if ($isRetina) {
        return 'retina';
    }

    if ($isCataract) {
        return 'catarata';
    }

    if ((strpos($text, 'glaucoma') !== false) || (strpos($text, 'pio') !== false)) {
        return 'glaucoma';
    }

    if ((strpos($text, 'pterig') !== false) || (strpos($text, 'conjuntivoplast') !== false)) {
        return 'pterigion';
    }

    return 'unknown';
}

/**
 * Extracts a CPT4 code from the referral requested-service field.
 *
 * @param string $value
 * @return string
 */
function getReferralRequestedServiceCode($value)
{
    $codes = getReferralRequestedServiceCodes($value);
    return empty($codes) ? '' : reset($codes);
}

/**
 * Extracts unique strict CPT4 tokens from the referral requested-service field.
 *
 * @param string $value
 * @return array
 */
function getReferralRequestedServiceCodes($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return array();
    }

    preg_match_all('/(?:^|;)CPT4:([A-Za-z0-9]+)/i', $value, $matches);
    if (empty($matches[1])) {
        return array();
    }

    $codes = array();
    foreach ($matches[1] as $code) {
        $code = strtoupper(trim($code));
        if ($code !== '') {
            $codes[$code] = $code;
        }
    }

    return array_values($codes);
}

/**
 * Builds a code => short description map for CPT4 service codes.
 *
 * @param array $values
 * @return array
 */
function getReferralRequestedServiceDescriptions($values)
{
    global $code_types;

    $codes = array();
    foreach ($values as $value) {
        foreach (getReferralRequestedServiceCodes($value) as $code) {
            $codes[$code] = $code;
        }
    }

    if (empty($codes)) {
        return array();
    }

    $cpt4TypeId = $code_types['CPT4']['id'] ?? null;
    if (empty($cpt4TypeId)) {
        $typeRow = sqlQuery("SELECT ct_id FROM code_types WHERE ct_key = 'CPT4' LIMIT 1");
        $cpt4TypeId = $typeRow['ct_id'] ?? null;
    }

    if (empty($cpt4TypeId)) {
        return array();
    }

    $codeValues = array_values($codes);
    $placeholders = implode(',', array_fill(0, count($codeValues), '?'));
    $params = array_merge(array($cpt4TypeId), $codeValues);
    $res = sqlStatement(
        "SELECT code, COALESCE(NULLIF(code_text_short, ''), NULLIF(code_text, ''), code) AS code_description " .
        "FROM codes WHERE code_type = ? AND code IN ($placeholders)",
        $params
    );

    $descriptions = array();
    while ($row = sqlFetchArray($res)) {
        $descriptions[strtoupper($row['code'])] = $row['code_description'];
    }

    return $descriptions;
}

/**
 * Formats requested-service display as code - short description.
 *
 * @param string $value
 * @param array $descriptions
 * @return string
 */
function formatReferralRequestedService($value, $descriptions)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $codes = getReferralRequestedServiceCodes($value);
    if (empty($codes)) {
        return $value;
    }

    $displayValues = array();
    foreach ($codes as $code) {
        $displayValues[] = formatReferralRequestedServiceCode($code, $descriptions);
    }

    return implode('; ', $displayValues);
}

/**
 * Formats one CPT4 code display as code - short description.
 *
 * @param string $code
 * @param array $descriptions
 * @return string
 */
function formatReferralRequestedServiceCode($code, $descriptions)
{
    $code = strtoupper(trim((string)$code));
    if ($code === '') {
        return '';
    }

    if (!empty($descriptions[$code])) {
        return $code . ' - ' . $descriptions[$code];
    }

    return $code;
}

function formatReferralState($state, $listId = '')
{
    $state = trim((string)$state);
    if ($state === '') {
        return '';
    }

    $listId = trim((string)$listId);
    if ($listId !== '') {
        $label = trim((string)getListItemTitle($listId, $state));
        if ($label !== '') {
            return $label;
        }
    }

    return $state;
}

/**
 * Formats protocol operation option ids into readable procedure names.
 *
 * @param string $operationValue
 * @return string
 */
function formatProtocolOperation($operationValue)
{
    static $operationLabelCache = array();

    $operationValue = trim((string)$operationValue);
    if ($operationValue === '' || $operationValue === '0') {
        return '';
    }

    $operationIds = array();
    foreach (explode('|', $operationValue) as $operationId) {
        $operationId = trim($operationId);
        if ($operationId !== '' && $operationId !== '0') {
            $operationIds[$operationId] = $operationId;
        }
    }

    if (empty($operationIds)) {
        return '';
    }

    $labels = array();
    foreach ($operationIds as $operationId) {
        if (!isset($operationLabelCache[$operationId])) {
            $row = sqlQuery(
                "SELECT COALESCE(NULLIF(notes, ''), NULLIF(title, ''), option_id) AS operation_label " .
                "FROM list_options WHERE list_id = 'cirugia_propuesta_defaults' AND option_id = ? LIMIT 1",
                array($operationId)
            );
            $operationLabelCache[$operationId] = !empty($row['operation_label']) ? $row['operation_label'] : $operationId;
        }

        $labels[] = $operationLabelCache[$operationId];
    }

    return implode(' + ', $labels);
}

$form_refresh = !empty($_POST['form_refresh']);
$form_auto_assign = !empty($_POST['form_auto_assign']);
$form_revert_auto_assign = !empty($_POST['form_revert_auto_assign']);
$autoAssignMessage = '';
$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = isset($_POST['form_facility']) ? trim((string)$_POST['form_facility']) : '';
$form_validity = isset($_POST['form_validity']) ? trim((string)$_POST['form_validity']) : '';
$form_appt_state = isset($_POST['form_appt_state']) ? trim((string)$_POST['form_appt_state']) : '';
$form_assigned_provider = isset($_POST['form_assigned_provider']) ? trim((string)$_POST['form_assigned_provider']) : '';
$form_requested_service = isset($_POST['form_requested_service']) ? trim((string)$_POST['form_requested_service']) : '';
$form_refer_state = isset($_POST['form_refer_state']) ? trim((string)$_POST['form_refer_state']) : '';
$form_operation_state = isset($_POST['form_operation_state']) ? trim((string)$_POST['form_operation_state']) : '';
$form_patient_type = isset($_POST['form_patient_type']) ? trim((string)$_POST['form_patient_type']) : '';
$form_pricelevel = isset($_POST['form_pricelevel']) ? trim((string)$_POST['form_pricelevel']) : '';
$form_insurance_type = isset($_POST['form_insurance_type']) ? trim((string)$_POST['form_insurance_type']) : '';
$form_pubpid = isset($_POST['form_pubpid']) ? trim((string)$_POST['form_pubpid']) : '';

if (!isValidIsoDate($form_from_date)) {
    $form_from_date = date('Y-01-01');
}

if (!isValidIsoDate($form_to_date)) {
    $form_to_date = date('Y-m-d');
}

if ($form_from_date > $form_to_date) {
    $tmpDate = $form_from_date;
    $form_from_date = $form_to_date;
    $form_to_date = $tmpDate;
}

if ($form_facility !== '' && $form_facility !== '0' && !ctype_digit($form_facility)) {
    $form_facility = '';
}

$allowedValidityFilters = array('', 'vigente', 'active', 'expired', 'no_end');
if (!in_array($form_validity, $allowedValidityFilters, true)) {
    $form_validity = '';
}

$allowedAppointmentFilters = array('', 'scheduled', 'pending_schedule', 'pending', 'assigned');
if (!in_array($form_appt_state, $allowedAppointmentFilters, true)) {
    $form_appt_state = '';
}
if ($form_appt_state === 'assigned') {
    $form_appt_state = 'scheduled';
}

$allowedOperationFilters = array('', 'operated', 'not_operated');
if (!in_array($form_operation_state, $allowedOperationFilters, true)) {
    $form_operation_state = '';
}

$allowedPatientTypeFilters = array('', 'new', 'returning');
if (!in_array($form_patient_type, $allowedPatientTypeFilters, true)) {
    $form_patient_type = '';
}

if ($form_assigned_provider !== '' && $form_assigned_provider !== '__unassigned__' && !ctype_digit($form_assigned_provider)) {
    $form_assigned_provider = '';
}

if ($form_requested_service !== '' && !preg_match('/^[A-Za-z0-9]+$/', $form_requested_service)) {
    $form_requested_service = '';
}

if (strlen($form_refer_state) > 128) {
    $form_refer_state = '';
}

if (strlen($form_pricelevel) > 128) {
    $form_pricelevel = '';
}

if (strlen($form_insurance_type) > 128) {
    $form_insurance_type = '';
}

if (strlen($form_pubpid) > 64) {
    $form_pubpid = substr($form_pubpid, 0, 64);
}

$providerOptions = array();
$providerRes = sqlStatement("SELECT id, fname, lname FROM users WHERE active = 1 ORDER BY lname, fname");
while ($prow = sqlFetchArray($providerRes)) {
    $providerOptions[] = $prow;
}

$pricelevelOptions = array();
$pricelevelRes = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
while ($priceRow = sqlFetchArray($pricelevelRes)) {
    $priceValue = trim((string)($priceRow['option_id'] ?? ''));
    if ($priceValue === '') {
        continue;
    }

    $pricelevelOptions[$priceValue] = !empty($priceRow['title']) ? $priceRow['title'] : $priceValue;
}
if ($form_pricelevel !== '' && !isset($pricelevelOptions[$form_pricelevel])) {
    $pricelevelOptions[$form_pricelevel] = $form_pricelevel;
}

$insuranceTypeOptions = array();
$insuranceTypeQuery = "SELECT DISTINCT p.genericval1 AS insurance_type " .
    "FROM transactions AS t " .
    "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
    "JOIN lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
    "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
    "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
    "WHERE t.title = 'LBTref' " .
    "AND p.genericval1 != '' " .
    "AND d1.field_value >= ? AND d1.field_value <= ? ";
$insuranceTypeParams = array($form_from_date, $form_to_date);
if ($form_facility !== '') {
    if ($form_facility === '0') {
        $insuranceTypeQuery .= "AND (uf.facility_id IS NULL OR uf.facility_id = 0) ";
    } else {
        $insuranceTypeQuery .= "AND uf.facility_id = ? ";
        $insuranceTypeParams[] = (int)$form_facility;
    }
}
if ($form_pubpid !== '') {
    $insuranceTypeQuery .= "AND p.pubpid LIKE ? ";
    $insuranceTypeParams[] = '%' . $form_pubpid . '%';
}
if ($form_pricelevel !== '') {
    $insuranceTypeQuery .= "AND p.pricelevel = ? ";
    $insuranceTypeParams[] = $form_pricelevel;
}
$insuranceTypeQuery .= "ORDER BY p.genericval1";
$insuranceTypeRes = sqlStatement($insuranceTypeQuery, $insuranceTypeParams);
while ($insuranceTypeRow = sqlFetchArray($insuranceTypeRes)) {
    $insuranceTypeValue = trim((string)($insuranceTypeRow['insurance_type'] ?? ''));
    if ($insuranceTypeValue !== '') {
        $insuranceTypeOptions[$insuranceTypeValue] = $insuranceTypeValue;
    }
}
if ($form_insurance_type !== '' && !isset($insuranceTypeOptions[$form_insurance_type])) {
    $insuranceTypeOptions[$form_insurance_type] = $form_insurance_type;
}

$referStateListId = '';
$referStateLayout = sqlQuery("SELECT list_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'refer_state' LIMIT 1");
if (!empty($referStateLayout['list_id'])) {
    $referStateListId = trim((string)$referStateLayout['list_id']);
}

$referStateOptions = array();
$referStateQuery = "SELECT DISTINCT ds.field_value AS refer_state " .
    "FROM transactions AS t " .
    "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
    "JOIN lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
    "JOIN lbt_data AS ds ON ds.form_id = t.id AND ds.field_id = 'refer_state' " .
    "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
    "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
    "WHERE t.title = 'LBTref' " .
    "AND ds.field_value != '' " .
    "AND d1.field_value >= ? AND d1.field_value <= ? ";
$referStateParams = array($form_from_date, $form_to_date);
if ($form_facility !== '') {
    if ($form_facility === '0') {
        $referStateQuery .= "AND (uf.facility_id IS NULL OR uf.facility_id = 0) ";
    } else {
        $referStateQuery .= "AND uf.facility_id = ? ";
        $referStateParams[] = (int)$form_facility;
    }
}
if ($form_pubpid !== '') {
    $referStateQuery .= "AND p.pubpid LIKE ? ";
    $referStateParams[] = '%' . $form_pubpid . '%';
}
if ($form_pricelevel !== '') {
    $referStateQuery .= "AND p.pricelevel = ? ";
    $referStateParams[] = $form_pricelevel;
}
if ($form_insurance_type !== '') {
    $referStateQuery .= "AND p.genericval1 = ? ";
    $referStateParams[] = $form_insurance_type;
}
$referStateQuery .= "ORDER BY ds.field_value";
$referStateRes = sqlStatement($referStateQuery, $referStateParams);
while ($stateRow = sqlFetchArray($referStateRes)) {
    $stateValue = trim((string)($stateRow['refer_state'] ?? ''));
    if ($stateValue !== '') {
        $referStateOptions[$stateValue] = formatReferralState($stateValue, $referStateListId);
    }
}
if ($form_refer_state !== '' && !isset($referStateOptions[$form_refer_state])) {
    $referStateOptions[$form_refer_state] = formatReferralState($form_refer_state, $referStateListId);
}

$requestedServiceOptions = array();
$requestedServiceValues = array();
$serviceQuery = "SELECT DISTINCT d.field_value AS requested_service " .
    "FROM transactions AS t " .
    "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
    "JOIN lbt_data AS d ON d.form_id = t.id AND d.field_id = 'refer_related_code' " .
    "JOIN lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
    "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
    "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
    "WHERE t.title = 'LBTref' " .
    "AND d.field_value != '' " .
    "AND d1.field_value >= ? AND d1.field_value <= ? ";
$serviceParams = array($form_from_date, $form_to_date);
if ($form_facility !== '') {
    if ($form_facility === '0') {
        $serviceQuery .= "AND (uf.facility_id IS NULL OR uf.facility_id = 0) ";
    } else {
        $serviceQuery .= "AND uf.facility_id = ? ";
        $serviceParams[] = (int)$form_facility;
    }
}
if ($form_pubpid !== '') {
    $serviceQuery .= "AND p.pubpid LIKE ? ";
    $serviceParams[] = '%' . $form_pubpid . '%';
}
if ($form_pricelevel !== '') {
    $serviceQuery .= "AND p.pricelevel = ? ";
    $serviceParams[] = $form_pricelevel;
}
if ($form_insurance_type !== '') {
    $serviceQuery .= "AND p.genericval1 = ? ";
    $serviceParams[] = $form_insurance_type;
}
$serviceQuery .= "ORDER BY d.field_value";
$serviceRes = sqlStatement(
    $serviceQuery,
    $serviceParams
);
while ($srow = sqlFetchArray($serviceRes)) {
    $serviceValue = trim((string)($srow['requested_service'] ?? ''));
    if ($serviceValue === '') {
        continue;
    }

    foreach (getReferralRequestedServiceCodes($serviceValue) as $serviceCode) {
        $requestedServiceOptions[$serviceCode] = $serviceCode;
    }
    $requestedServiceValues[] = $serviceValue;
}
ksort($requestedServiceOptions, SORT_NATURAL);
$requestedServiceDescriptions = getReferralRequestedServiceDescriptions($requestedServiceValues);
?>
<html>
<head>
    <title><?php echo xlt('Referencias'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-bs/css/dataTables.bootstrap.min.css" type="text/css">
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net/js/jquery.dataTables.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <script language="JavaScript">
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        $(function () {
            var reportTable = $('#mymaintable');
            if (reportTable.length) {
                var dataTable = reportTable.DataTable({
                    pageLength: 25,
                    order: [[1, 'desc']],
                    autoWidth: false,
                    scrollX: true,
                    language: {
                        emptyTable: <?php echo xlj('No hay datos disponibles en la tabla'); ?>,
                        info: <?php echo xlj('Mostrando _START_ a _END_ de _TOTAL_ registros'); ?>,
                        infoEmpty: <?php echo xlj('Mostrando 0 a 0 de 0 registros'); ?>,
                        infoFiltered: <?php echo xlj('(filtrado de _MAX_ registros totales)'); ?>,
                        lengthMenu: <?php echo xlj('Mostrar _MENU_ registros'); ?>,
                        loadingRecords: <?php echo xlj('Cargando...'); ?>,
                        processing: <?php echo xlj('Procesando...'); ?>,
                        search: <?php echo xlj('Buscar'); ?> + ':',
                        zeroRecords: <?php echo xlj('No se encontraron registros'); ?>,
                        paginate: {
                            first: <?php echo xlj('Primero'); ?>,
                            last: <?php echo xlj('Último'); ?>,
                            next: <?php echo xlj('Siguiente'); ?>,
                            previous: <?php echo xlj('Anterior'); ?>
                        },
                        aria: {
                            sortAscending: ': ' + <?php echo xlj('activar para ordenar la columna ascendente'); ?>,
                            sortDescending: ': ' + <?php echo xlj('activar para ordenar la columna descendente'); ?>
                        }
                    }
                });

                var requestedServiceFilter = $('#form_requested_service');
                var selectedRequestedService = requestedServiceFilter.length ? requestedServiceFilter.val() : '';
                var requestedServiceLabels = {};
                if (requestedServiceFilter.length) {
                    requestedServiceFilter.find('option').each(function () {
                        var optionValue = $(this).attr('value') || '';
                        if (optionValue) {
                            requestedServiceLabels[optionValue] = $(this).text();
                        }
                    });
                }

                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    if (!settings.nTable || settings.nTable.id !== 'mymaintable' || !selectedRequestedService) {
                        return true;
                    }

                    var serviceCell = dataTable.cell(dataIndex, 7).node();
                    var serviceCodes = ($(serviceCell).attr('data-cpt4') || '').toString().split(';');
                    return serviceCodes.some(function (serviceCode) {
                        return $.trim(serviceCode) === selectedRequestedService;
                    });
                });

                function updateReferralSummaryCards() {
                    var counts = {
                        total: 0,
                        active: 0,
                        expired: 0,
                        no_end: 0,
                        scheduled: 0,
                        pending_schedule: 0,
                        pending: 0,
                        operated: 0,
                        not_operated: 0,
                        new_patient: 0,
                        returning_patient: 0
                    };

                    dataTable.rows({filter: 'applied'}).nodes().each(function (rowNode) {
                        var row = $(rowNode);
                        counts.total++;

                        var validity = row.attr('data-validity') || '';
                        if (validity === 'expired') {
                            counts.expired++;
                        } else {
                            counts.active++;
                            if (validity === 'no_end') {
                                counts.no_end++;
                            }
                        }

                        var appt = row.attr('data-appt') || 'pending';
                        if (appt === 'scheduled') {
                            counts.scheduled++;
                        } else if (appt === 'pending_schedule') {
                            counts.pending_schedule++;
                        } else {
                            counts.pending++;
                        }

                        if ((row.attr('data-operation') || '') === 'operated') {
                            counts.operated++;
                        } else {
                            counts.not_operated++;
                        }

                        if ((row.attr('data-patient-type') || '') === 'new') {
                            counts.new_patient++;
                        } else {
                            counts.returning_patient++;
                        }
                    });

                    $('[data-summary-key]').each(function () {
                        var key = $(this).attr('data-summary-key');
                        $(this).text(counts[key] || 0);
                    });
                }

                if (requestedServiceFilter.length) {
                    var availableRequestedServices = {};
                    dataTable.rows().nodes().each(function (rowNode) {
                        var serviceCodes = ($('td', rowNode).eq(7).attr('data-cpt4') || '').toString().split(';');
                        serviceCodes.forEach(function (serviceCode) {
                            serviceCode = $.trim(serviceCode);
                            if (serviceCode) {
                                availableRequestedServices[serviceCode] = requestedServiceLabels[serviceCode] || serviceCode;
                            }
                        });
                    });

                    requestedServiceFilter.empty();
                    requestedServiceFilter.append($('<option>', {
                        value: '',
                        text: '-- <?php echo xla('Todos'); ?> --'
                    }));

                    Object.keys(availableRequestedServices).sort(function (a, b) {
                        return a.localeCompare(b, undefined, {numeric: true, sensitivity: 'base'});
                    }).forEach(function (serviceCode) {
                        requestedServiceFilter.append($('<option>', {
                            value: serviceCode,
                            text: availableRequestedServices[serviceCode]
                        }));
                    });

                    if (selectedRequestedService && !availableRequestedServices[selectedRequestedService]) {
                        requestedServiceFilter.append($('<option>', {
                            value: selectedRequestedService,
                            text: requestedServiceLabels[selectedRequestedService] || selectedRequestedService
                        }));
                    }
                    requestedServiceFilter.val(selectedRequestedService);

                    requestedServiceFilter.on('change', function () {
                        selectedRequestedService = $(this).val();
                        dataTable.draw();
                    });
                }

                dataTable.on('draw', updateReferralSummaryCards);
                dataTable.draw();
            } else if (document.getElementById('mymaintable')) {
                oeFixedHeaderSetup(document.getElementById('mymaintable'));
            }

            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        // The OnClick handler for referral display.
        function show_referral(transid) {
            dlgopen('../patient_file/transaction/print_referral.php?transid=' + encodeURIComponent(transid),
                '_blank', 550, 400, true); // Force new window rather than iframe because of the dynamic generation of the content in print_referral.php
            return false;
        }

        function exportReferralCsv() {
            var table = document.getElementById('mymaintable');
            if (!table) {
                return false;
            }

            var rows = table.querySelectorAll('tr');
            var csvRows = [];

            rows.forEach(function (row) {
                var cols = row.querySelectorAll('th, td');
                var values = [];

                cols.forEach(function (col) {
                    var value = (col.innerText || col.textContent || '').replace(/\s+/g, ' ').trim();
                    value = value.replace(/"/g, '""');
                    values.push('"' + value + '"');
                });

                csvRows.push(values.join(','));
            });

            var csvContent = "\uFEFF" + csvRows.join('\n');
            var blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8;'});
            var url = URL.createObjectURL(blob);
            var fromDate = $('#form_from_date').val() || '';
            var toDate = $('#form_to_date').val() || '';
            var fileName = 'referrals_report_' + fromDate.replace(/\//g, '-') + '_to_' + toDate.replace(/\//g, '-') + '.csv';

            var link = document.createElement('a');
            link.href = url;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            return false;
        }

        function autoAssignReferrals() {
            $('#form_auto_assign').val('1');
            $('#form_revert_auto_assign').val('');
            $('#form_refresh').val('true');
            $('#theform').submit();
            return false;
        }

        function revertAutoAssignReferrals() {
            if (!confirm(<?php echo xlj('Esto eliminará el proveedor asignado para las referencias pendientes en los filtros actuales. ¿Continuar?'); ?>)) {
                return false;
            }

            $('#form_revert_auto_assign').val('1');
            $('#form_auto_assign').val('');
            $('#form_refresh').val('true');
            $('#theform').submit();
            return false;
        }
    </script>

    <style type="text/css">
        .report-shell {
            margin-top: 10px;
        }

        .referral-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin: 12px 0;
        }

        .summary-card {
            border: 1px solid #d8e0e8;
            border-radius: 8px;
            background: #f8fbff;
            padding: 10px 12px;
        }

        .summary-label {
            display: block;
            font-size: 12px;
            color: #4d5b68;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 600;
            color: #1f2d3d;
            line-height: 1.1;
        }

        .status-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .pill-active {
            background: #e7f6ec;
            color: #187d3c;
        }

        .pill-expired {
            background: #fdecec;
            color: #af1f1f;
        }

        .pill-assigned {
            background: #e8f1ff;
            color: #1d5ebd;
        }

        .pill-pending {
            background: #fff4e5;
            color: #9e5a00;
        }

        .pill-neutral {
            background: #eef1f4;
            color: #42566a;
        }

        #report_results .table td,
        #report_results .table th {
            vertical-align: middle;
            font-size: 12px;
        }

        #report_results .table th {
            white-space: nowrap;
        }

        .stats-title {
            margin: 12px 0 6px;
            font-size: 14px;
            font-weight: 600;
            color: #1f2d3d;
        }

        .provider-stats-wrap {
            max-width: 640px;
        }

        #report_parameters .form-control {
            width: 300px;
        }

        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }

            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }

            #report_results table {
                margin-top: 0;
            }

            .status-pill {
                border: 1px solid #777;
                color: #222;
                background: #fff;
            }

            .referral-summary {
                display: none;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }
    </style>
</head>

<body class="body_top">

<div class="report-shell">
    <span class='title'><?php echo xlt('Reporte'); ?> - <?php echo xlt('Referencias'); ?></span>

    <div id="report_parameters_daterange">
        <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('hasta') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
    </div>

    <form name='theform' id='theform' method='post' action='referrals_report.php'
          onsubmit='return top.restoreSession()'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>

        <div id="report_parameters">
            <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
            <input type='hidden' name='form_auto_assign' id='form_auto_assign' value=''/>
            <input type='hidden' name='form_revert_auto_assign' id='form_revert_auto_assign' value=''/>
            <table>
                <tr>
                    <td width='640px'>
                        <div style='float:left'>
                            <table class='text'>
                                <tr>
                                    <td class='control-label'>
                                        <?php echo xlt('Centro'); ?>:
                                    </td>
                                    <td>
                                        <?php dropdown_facility($form_facility, 'form_facility', true); ?>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Desde'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_from_date' id="form_from_date" size='10'
                                               value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'
                                               class='datepicker form-control'>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Hasta'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_to_date' id="form_to_date" size='10'
                                               value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'
                                               class='datepicker form-control'>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='control-label'>
                                        <?php echo xlt('Vigencia'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_validity' id='form_validity' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <option value='vigente'<?php echo ($form_validity === 'vigente') ? ' selected' : ''; ?>><?php echo xlt('Vigente (Todos)'); ?></option>
                                            <option value='active'<?php echo ($form_validity === 'active') ? ' selected' : ''; ?>><?php echo xlt('Vigente (Con fecha fin)'); ?></option>
                                            <option value='no_end'<?php echo ($form_validity === 'no_end') ? ' selected' : ''; ?>><?php echo xlt('Sin fecha fin'); ?></option>
                                            <option value='expired'<?php echo ($form_validity === 'expired') ? ' selected' : ''; ?>><?php echo xlt('Caducada'); ?></option>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Estado de cita'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_appt_state' id='form_appt_state' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <option value='scheduled'<?php echo ($form_appt_state === 'scheduled') ? ' selected' : ''; ?>><?php echo xlt('Asignada'); ?></option>
                                            <option value='pending_schedule'<?php echo ($form_appt_state === 'pending_schedule') ? ' selected' : ''; ?>><?php echo xlt('Pendiente de agendar'); ?></option>
                                            <option value='pending'<?php echo ($form_appt_state === 'pending') ? ' selected' : ''; ?>><?php echo xlt('Pendiente'); ?></option>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Proveedor asignado'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_assigned_provider' id='form_assigned_provider' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <option value='__unassigned__'<?php echo ($form_assigned_provider === '__unassigned__') ? ' selected' : ''; ?>><?php echo xlt('Sólo sin asignar'); ?></option>
                                            <?php foreach ($providerOptions as $providerOption) { ?>
                                                <option value='<?php echo attr($providerOption['id']); ?>'<?php echo ($form_assigned_provider === (string)$providerOption['id']) ? ' selected' : ''; ?>>
                                                    <?php echo text(trim(($providerOption['lname'] ?? '') . ', ' . ($providerOption['fname'] ?? ''))); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='control-label'>
                                        <?php echo xlt('Cédula'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_pubpid' id='form_pubpid'
                                               value='<?php echo attr($form_pubpid); ?>'
                                               class='form-control'>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Afiliación'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_pricelevel' id='form_pricelevel' class='form-control'>
                                            <option value=''>-- <?php echo xlt('IESS'); ?> --</option>
                                            <?php foreach ($pricelevelOptions as $priceValue => $priceLabel) { ?>
                                                <option value='<?php echo attr($priceValue); ?>'<?php echo ($form_pricelevel === (string)$priceValue) ? ' selected' : ''; ?>>
                                                    <?php echo text($priceLabel); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Tipo de seguro'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_insurance_type' id='form_insurance_type' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <?php foreach ($insuranceTypeOptions as $insuranceTypeValue => $insuranceTypeLabel) { ?>
                                                <option value='<?php echo attr($insuranceTypeValue); ?>'<?php echo ($form_insurance_type === (string)$insuranceTypeValue) ? ' selected' : ''; ?>>
                                                    <?php echo text($insuranceTypeLabel); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='control-label'>
                                        <?php echo xlt('Servicio solicitado'); ?>:
                                    </td>
                                    <td colspan='5'>
                                        <select name='form_requested_service' id='form_requested_service' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <?php foreach ($requestedServiceOptions as $serviceCode) { ?>
                                                <option value='<?php echo attr($serviceCode); ?>'<?php echo ($form_requested_service === $serviceCode) ? ' selected' : ''; ?>>
                                                    <?php echo text(formatReferralRequestedServiceCode($serviceCode, $requestedServiceDescriptions)); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='control-label'>
                                        <?php echo xlt('Cirugía'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_operation_state' id='form_operation_state' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <option value='operated'<?php echo ($form_operation_state === 'operated') ? ' selected' : ''; ?>><?php echo xlt('Operados'); ?></option>
                                            <option value='not_operated'<?php echo ($form_operation_state === 'not_operated') ? ' selected' : ''; ?>><?php echo xlt('No operados'); ?></option>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Tipo de paciente'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_patient_type' id='form_patient_type' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <option value='new'<?php echo ($form_patient_type === 'new') ? ' selected' : ''; ?>><?php echo xlt('Nuevos'); ?></option>
                                            <option value='returning'<?php echo ($form_patient_type === 'returning') ? ' selected' : ''; ?>><?php echo xlt('Recurrentes'); ?></option>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Estado de referencia'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_refer_state' id='form_refer_state' class='form-control'>
                                            <option value=''>-- <?php echo xlt('Todos'); ?> --</option>
                                            <?php foreach ($referStateOptions as $stateValue => $stateLabel) { ?>
                                                <option value='<?php echo attr($stateValue); ?>'<?php echo ($form_refer_state === (string)$stateValue) ? ' selected' : ''; ?>>
                                                    <?php echo text($stateLabel); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td align='left' valign='middle' height="100%">
                        <table style='border-left:1px solid; width:100%; height:100%'>
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href='#' class='btn btn-default btn-save'
                                               onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                                <?php echo xlt('Consultar'); ?>
                                            </a>
                                            <?php if ($form_refresh) { ?>
                                                <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                                    <?php echo xlt('Imprimir'); ?>
                                                </a>
                                                <a href='#' class='btn btn-default' onclick='return exportReferralCsv();'>
                                                    <?php echo xlt('Exportar CSV'); ?>
                                                </a>
                                                <a href='#' class='btn btn-default' onclick='return autoAssignReferrals();'>
                                                    <?php echo xlt('Autoasignar pendientes'); ?>
                                                </a>
                                                <a href='#' class='btn btn-danger' onclick='return revertAutoAssignReferrals();'>
                                                    <?php echo xlt('Revertir autoasignación'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div> <!-- end of parameters -->

        <?php
        if ($form_refresh) {
            $today = date('Y-m-d');
            $totalReferrals = 0;
            $vigenteCount = 0;
            $caducadaCount = 0;
            $openEndCount = 0;
            $assignedCount = 0;
            $pendingScheduleCount = 0;
            $pendingCount = 0;
            $operatedCount = 0;
            $notOperatedCount = 0;
            $newPatientCount = 0;
            $returningPatientCount = 0;

            $query = "SELECT t.id, t.pid, t.date AS created_date, " .
                "d1.field_value AS refer_date, " .
                "d2.field_value AS refer_end_date, " .
                "d4.field_value AS body, " .
                "d5.field_value AS refer_id, " .
                "d8.field_value AS refer_from_id, " .
                "d9.field_value AS assigned_provider_id, " .
                "d10.field_value AS refer_related_code, " .
                "d11.field_value AS refer_state, " .
                "ut.organization, uf.facility_id, p.pubpid, p.pricelevel, p.genericval1, " .
                "COALESCE(NULLIF(loprice.title, ''), NULLIF(p.pricelevel, ''), '') AS pricelevel_title, " .
                "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
                "CONCAT(ut.fname,' ', ut.lname) AS referer_to, " .
                "CONCAT(ua.fname,' ', ua.lname) AS assigned_provider_name, " .
                "CONCAT(p.fname,' ', p.lname) AS patient_name " .
                "FROM transactions AS t " .
                "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
                "JOIN      lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
                "LEFT JOIN lbt_data AS d2 ON d2.form_id = t.id AND d2.field_id = 'refer_end_date' " .
                "LEFT JOIN lbt_data AS d4 ON d4.form_id = t.id AND d4.field_id = 'body' " .
                "LEFT JOIN lbt_data AS d5 ON d5.form_id = t.id AND d5.field_id = 'refer_id' " .
                "LEFT JOIN lbt_data AS d7 ON d7.form_id = t.id AND d7.field_id = 'refer_to' " .
                "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
                "LEFT JOIN lbt_data AS d9 ON d9.form_id = t.id AND d9.field_id = 'assigned_provider' " .
                "LEFT JOIN lbt_data AS d10 ON d10.form_id = t.id AND d10.field_id = 'refer_related_code' " .
                "LEFT JOIN lbt_data AS d11 ON d11.form_id = t.id AND d11.field_id = 'refer_state' " .
                "LEFT JOIN users AS ut ON ut.id = d7.field_value " .
                "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
                "LEFT JOIN users AS ua ON ua.id = d9.field_value " .
                "LEFT JOIN list_options AS loprice ON loprice.list_id = 'pricelevel' AND loprice.option_id = p.pricelevel AND loprice.activity = 1 " .
                "WHERE t.title = 'LBTref' AND " .
                "d1.field_value >= ? AND d1.field_value <= ? ";

            $queryParams = array($form_from_date, $form_to_date);

            if ($form_facility !== '') {
                if ($form_facility === '0') {
                    $query .= "AND (uf.facility_id IS NULL OR uf.facility_id = 0) ";
                } else {
                    $query .= "AND uf.facility_id = ? ";
                    $queryParams[] = (int)$form_facility;
                }
            }

            if ($form_pubpid !== '') {
                $query .= "AND p.pubpid LIKE ? ";
                $queryParams[] = '%' . $form_pubpid . '%';
            }

            if ($form_pricelevel !== '') {
                $query .= "AND p.pricelevel = ? ";
                $queryParams[] = $form_pricelevel;
            }

            if ($form_insurance_type !== '') {
                $query .= "AND p.genericval1 = ? ";
                $queryParams[] = $form_insurance_type;
            }

            $query .= "ORDER BY ut.organization, d1.field_value, t.id";
            $res = sqlStatement($query, $queryParams);
            ?>

            <?php
            $allRows = array();
            $pidSet = array();
            $minReferDate = '';
            $minReferralEntryDate = '';
            while ($row = sqlFetchArray($res)) {
                $referToDisplay = !empty($row['organization']) ? $row['organization'] : $row['referer_to'];

                $isExpired = !empty($row['refer_end_date']) && $row['refer_end_date'] < $today;
                if ($isExpired) {
                    $validityKey = 'expired';
                    $validityClass = 'pill-expired';
                    $validityLabel = xlt('Caducada');
                } elseif (empty($row['refer_end_date'])) {
                    $validityKey = 'no_end';
                    $validityClass = 'pill-neutral';
                    $validityLabel = xlt('Sin fecha fin');
                } else {
                    $validityKey = 'active';
                    $validityClass = 'pill-active';
                    $validityLabel = xlt('Vigente');
                }

                $row['refer_to_display'] = $referToDisplay;
                $row['validity_key'] = $validityKey;
                $row['validity_class'] = $validityClass;
                $row['validity_label'] = $validityLabel;
                $row['next_appt_date'] = '';
                $row['next_appt_status'] = '';
                $row['next_appt_provider_id'] = '';
                $row['next_appt_provider'] = '';
                $row['appt_key'] = '';
                $row['appt_class'] = '';
                $row['appt_label'] = '';
                $row['next_appointment'] = '';
                $row['effective_provider_id'] = '';
                $row['effective_provider_name'] = '';
                $row['specialty_key'] = classifyReferralSpecialty($row['body'] ?? '');

                $referFromId = trim((string)($row['refer_from_id'] ?? ''));
                if ($referFromId !== '') {
                    $row['effective_provider_id'] = $referFromId;
                    $row['effective_provider_name'] = trim((string)($row['referer_name'] ?? ''));
                } elseif (trim((string)($row['assigned_provider_id'] ?? '')) !== '') {
                    $row['effective_provider_id'] = trim((string)$row['assigned_provider_id']);
                    $row['effective_provider_name'] = trim((string)($row['assigned_provider_name'] ?? ''));
                }
                $allRows[] = $row;

                if (!empty($row['pid'])) {
                    $pidSet[(string)$row['pid']] = (string)$row['pid'];
                }

                if (!empty($row['refer_date']) && ($minReferDate === '' || $row['refer_date'] < $minReferDate)) {
                    $minReferDate = $row['refer_date'];
                }

                $referralEntryDate = empty($row['created_date']) ? '' : substr((string)$row['created_date'], 0, 10);
                if ($referralEntryDate !== '' && ($minReferralEntryDate === '' || $referralEntryDate < $minReferralEntryDate)) {
                    $minReferralEntryDate = $referralEntryDate;
                }
            }

            $appointmentsByPid = array();
            if (!empty($pidSet) && !empty($minReferDate)) {
                $pidList = array_values($pidSet);
                $pidPlaceholders = implode(',', array_fill(0, count($pidList), '?'));
                $apptQuery = "SELECT e.pc_pid, e.pc_eventDate, e.pc_startTime, e.pc_eid, e.pc_apptstatus, e.pc_aid, " .
                    "CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name " .
                    "FROM openemr_postcalendar_events AS e " .
                    "LEFT JOIN users AS u ON u.id = e.pc_aid " .
                    "WHERE e.pc_pid IN ($pidPlaceholders) " .
                    "AND e.pc_eventDate >= ? " .
                    "AND e.pc_apptstatus NOT IN ('x', CHAR(63), '%') " .
                    "ORDER BY e.pc_pid, e.pc_eventDate ASC, e.pc_startTime ASC, e.pc_eid ASC";

                $apptParams = $pidList;
                $apptParams[] = $minReferDate;
                $apptRes = sqlStatement($apptQuery, $apptParams);
                while ($apptRow = sqlFetchArray($apptRes)) {
                    $pidKey = (string)($apptRow['pc_pid'] ?? '');
                    if ($pidKey === '') {
                        continue;
                    }

                    if (!isset($appointmentsByPid[$pidKey])) {
                        $appointmentsByPid[$pidKey] = array();
                    }

                    $appointmentsByPid[$pidKey][] = $apptRow;
                }
            }

            $protocolsByPid = array();
            if (!empty($pidSet) && !empty($minReferDate)) {
                $pidList = array_values($pidSet);
                $pidPlaceholders = implode(',', array_fill(0, count($pidList), '?'));
                $protocolQuery = "SELECT f.pid, f.form_id, f.encounter, COALESCE(fe.date, f.date) AS protocol_date, lopr.field_value AS operation_value " .
                    "FROM forms AS f " .
                    "LEFT JOIN form_encounter AS fe ON fe.pid = f.pid AND fe.encounter = f.encounter " .
                    "LEFT JOIN lbf_data AS lopr ON lopr.form_id = f.form_id AND lopr.field_id = 'Prot_opr' " .
                    "WHERE f.pid IN ($pidPlaceholders) " .
                    "AND f.formdir = 'LBFprotocolo' " .
                    "AND f.deleted = 0 " .
                    "AND COALESCE(fe.date, f.date) > ? " .
                    "ORDER BY f.pid, COALESCE(fe.date, f.date) ASC, f.form_id ASC";

                $protocolParams = $pidList;
                $protocolParams[] = $minReferDate . ' 00:00:00';
                $protocolRes = sqlStatement($protocolQuery, $protocolParams);
                while ($protocolRow = sqlFetchArray($protocolRes)) {
                    $pidKey = (string)($protocolRow['pid'] ?? '');
                    if ($pidKey === '') {
                        continue;
                    }

                    if (!isset($protocolsByPid[$pidKey])) {
                        $protocolsByPid[$pidKey] = array();
                    }

                    $protocolsByPid[$pidKey][] = $protocolRow;
                }
            }

            $carePlansByPid = array();
            if (!empty($pidSet) && !empty($minReferDate)) {
                $pidList = array_values($pidSet);
                $pidPlaceholders = implode(',', array_fill(0, count($pidList), '?'));
                $carePlanQuery = "SELECT pid, id, date AS care_plan_date, code, codetext, description " .
                    "FROM form_care_plan " .
                    "WHERE pid IN ($pidPlaceholders) " .
                    "AND activity = 1 " .
                    "AND date > ? " .
                    "ORDER BY pid, date ASC, id ASC";

                $carePlanParams = $pidList;
                $carePlanParams[] = $minReferDate;
                $carePlanRes = sqlStatement($carePlanQuery, $carePlanParams);
                while ($carePlanRow = sqlFetchArray($carePlanRes)) {
                    $pidKey = (string)($carePlanRow['pid'] ?? '');
                    if ($pidKey === '') {
                        continue;
                    }

                    if (!isset($carePlansByPid[$pidKey])) {
                        $carePlansByPid[$pidKey] = array();
                    }

                    $carePlansByPid[$pidKey][] = $carePlanRow;
                }
            }

            $encountersByPid = array();
            if (!empty($pidSet)) {
                $pidList = array_values($pidSet);
                $pidPlaceholders = implode(',', array_fill(0, count($pidList), '?'));
                $encounterQuery = "SELECT pid, date AS encounter_date " .
                    "FROM form_encounter " .
                    "WHERE pid IN ($pidPlaceholders) " .
                    "ORDER BY pid, date ASC";

                $encounterRes = sqlStatement($encounterQuery, $pidList);
                while ($encounterRow = sqlFetchArray($encounterRes)) {
                    $pidKey = (string)($encounterRow['pid'] ?? '');
                    if ($pidKey === '') {
                        continue;
                    }

                    if (!isset($encountersByPid[$pidKey])) {
                        $encountersByPid[$pidKey] = array();
                    }

                    $encountersByPid[$pidKey][] = substr((string)($encounterRow['encounter_date'] ?? ''), 0, 10);
                }
            }

            foreach ($allRows as &$row) {
                $row['operation_key'] = 'not_operated';
                $row['operation_class'] = 'pill-pending';
                $row['operation_label'] = xlt('No operado');
                $row['operation_date'] = '';
                $row['operation_details'] = '';
                $row['patient_type_key'] = 'new';
                $row['patient_type_class'] = 'pill-active';
                $row['patient_type_label'] = xlt('Nuevo');

                $pidKey = (string)($row['pid'] ?? '');
                $referDate = (string)($row['refer_date'] ?? '');
                $referEndDate = trim((string)($row['refer_end_date'] ?? ''));
                $referralEntryDate = empty($row['created_date']) ? '' : substr((string)$row['created_date'], 0, 10);
                $patientTypeCompareDate = $referralEntryDate !== '' ? $referralEntryDate : $referDate;
                $requestedServiceCodes = getReferralRequestedServiceCodes($row['refer_related_code'] ?? '');
                $useCarePlanForOperation = !empty(array_intersect($requestedServiceCodes, array('281339', '281351', '66761')));

                if ($pidKey !== '' && !empty($encountersByPid[$pidKey]) && $patientTypeCompareDate !== '') {
                    foreach ($encountersByPid[$pidKey] as $encounterDate) {
                        if ($encounterDate !== '' && $encounterDate < $patientTypeCompareDate) {
                            $row['patient_type_key'] = 'returning';
                            $row['patient_type_class'] = 'pill-neutral';
                            $row['patient_type_label'] = xlt('Recurrente');
                            break;
                        }
                    }
                }

                if ($pidKey !== '' && !empty($appointmentsByPid[$pidKey])) {
                    foreach ($appointmentsByPid[$pidKey] as $apptRow) {
                        $eventDate = (string)($apptRow['pc_eventDate'] ?? '');
                        if ($eventDate === '' || (!empty($referDate) && $eventDate < $referDate)) {
                            continue;
                        }

                        if ($referEndDate !== '' && $eventDate > $referEndDate) {
                            continue;
                        }

                        $row['next_appt_date'] = $eventDate;
                        $row['next_appt_status'] = (string)($apptRow['pc_apptstatus'] ?? '');
                        $row['next_appt_provider_id'] = trim((string)($apptRow['pc_aid'] ?? ''));
                        $row['next_appt_provider'] = trim((string)($apptRow['provider_name'] ?? ''));

                        if (trim((string)$row['effective_provider_id']) === '') {
                            $row['effective_provider_id'] = $row['next_appt_provider_id'];
                            $row['effective_provider_name'] = $row['next_appt_provider'];
                        }
                        break;
                    }
                }

                if ($useCarePlanForOperation && $pidKey !== '' && !empty($carePlansByPid[$pidKey])) {
                    foreach ($carePlansByPid[$pidKey] as $carePlanRow) {
                        $carePlanDate = substr((string)($carePlanRow['care_plan_date'] ?? ''), 0, 10);
                        if ($carePlanDate === '' || (!empty($referDate) && $carePlanDate <= $referDate)) {
                            continue;
                        }

                        $carePlanDetails = trim((string)($carePlanRow['codetext'] ?? ''));
                        if ($carePlanDetails === '') {
                            $carePlanDetails = trim((string)($carePlanRow['description'] ?? ''));
                        }
                        if ($carePlanDetails === '') {
                            $carePlanDetails = trim((string)($carePlanRow['code'] ?? ''));
                        }

                        $row['operation_key'] = 'operated';
                        $row['operation_class'] = 'pill-active';
                        $row['operation_label'] = xlt('Operado');
                        $row['operation_date'] = $carePlanDate;
                        $row['operation_details'] = $carePlanDetails;
                        break;
                    }
                } elseif (!$useCarePlanForOperation && $pidKey !== '' && !empty($protocolsByPid[$pidKey])) {
                    foreach ($protocolsByPid[$pidKey] as $protocolRow) {
                        $protocolDate = substr((string)($protocolRow['protocol_date'] ?? ''), 0, 10);
                        if ($protocolDate === '' || (!empty($referDate) && $protocolDate <= $referDate)) {
                            continue;
                        }

                        $row['operation_key'] = 'operated';
                        $row['operation_class'] = 'pill-active';
                        $row['operation_label'] = xlt('Operado');
                        $row['operation_date'] = $protocolDate;
                        $row['operation_details'] = formatProtocolOperation($protocolRow['operation_value'] ?? '');
                        break;
                    }
                }
            }
            unset($row);

            if ($form_revert_auto_assign) {
                $revertedCount = 0;
                foreach ($allRows as &$arow) {
                    if (!empty($arow['next_appt_date'])) {
                        continue;
                    }

                    $existingAssignedProvider = trim((string)($arow['assigned_provider_id'] ?? ''));
                    if ($existingAssignedProvider === '') {
                        continue;
                    }

                    sqlStatement("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'assigned_provider'", array($arow['id']));
                    $arow['assigned_provider_id'] = '';
                    $arow['assigned_provider_name'] = '';
                    $revertedCount++;
                }
                unset($arow);

                $autoAssignMessage = xlt('Reversión completada.') . ' ' . xlt('Referencias pendientes limpiadas') . ': ' . $revertedCount;
            }

            if ($form_auto_assign) {
                $providerPoolBySpecialty = array();
                $providerLoadBySpecialty = array();

                foreach ($allRows as $arow) {
                    $sp = $arow['specialty_key'] ?? 'unknown';
                    if ($sp === 'unknown') {
                        continue;
                    }

                    $provId = trim((string)($arow['next_appt_provider_id'] ?? ''));
                    $provName = trim((string)($arow['next_appt_provider'] ?? ''));
                    if ($provId === '' || $provName === '') {
                        continue;
                    }

                    if (!isset($providerPoolBySpecialty[$sp])) {
                        $providerPoolBySpecialty[$sp] = array();
                        $providerLoadBySpecialty[$sp] = array();
                    }

                    $providerPoolBySpecialty[$sp][$provId] = $provName;
                    if (!isset($providerLoadBySpecialty[$sp][$provId])) {
                        $providerLoadBySpecialty[$sp][$provId] = 0;
                    }
                    $providerLoadBySpecialty[$sp][$provId]++;
                }

                $autoAssignedCount = 0;
                foreach ($allRows as &$arow) {
                    if (!empty($arow['next_appt_date'])) {
                        continue;
                    }

                    $existingAssignedProvider = trim((string)($arow['assigned_provider_id'] ?? ''));
                    if ($existingAssignedProvider !== '') {
                        continue;
                    }

                    $sp = $arow['specialty_key'] ?? 'unknown';
                    if (!isset($providerPoolBySpecialty[$sp]) || empty($providerPoolBySpecialty[$sp])) {
                        continue;
                    }

                    $candidateId = '';
                    $candidateLoad = null;
                    foreach ($providerPoolBySpecialty[$sp] as $pid => $pname) {
                        $load = $providerLoadBySpecialty[$sp][$pid] ?? 0;
                        if ($candidateId === '' || $load < $candidateLoad) {
                            $candidateId = (string)$pid;
                            $candidateLoad = $load;
                        }
                    }

                    if ($candidateId === '') {
                        continue;
                    }

                    sqlStatement("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'assigned_provider'", array($arow['id']));
                    sqlStatement("INSERT INTO lbt_data (form_id, field_id, field_value) VALUES (?, 'assigned_provider', ?)", array($arow['id'], $candidateId));

                    $arow['assigned_provider_id'] = $candidateId;
                    $arow['assigned_provider_name'] = $providerPoolBySpecialty[$sp][$candidateId] ?? '';
                    $providerLoadBySpecialty[$sp][$candidateId] = ($providerLoadBySpecialty[$sp][$candidateId] ?? 0) + 1;
                    $autoAssignedCount++;
                }
                unset($arow);

                $autoAssignMessage = xlt('Autoasignación completada.') . ' ' . xlt('Referencias asignadas') . ': ' . $autoAssignedCount;
            }

            foreach ($allRows as &$row) {
                $hasAppointment = !empty($row['next_appt_date']);
                $hasExplicitAssignedProvider = trim((string)($row['assigned_provider_id'] ?? '')) !== '';
                if ($hasAppointment) {
                    $apptKey = 'scheduled';
                    $apptClass = 'pill-assigned';
                    $apptLabel = xlt('Agendada');
                } elseif ($hasExplicitAssignedProvider) {
                    $apptKey = 'pending_schedule';
                    $apptClass = 'pill-pending';
                    $apptLabel = xlt('Pendiente de agendar');
                } else {
                    $apptKey = 'pending';
                    $apptClass = 'pill-pending';
                    $apptLabel = xlt('Pendiente');
                }

                $nextAppointment = xlt('Sin cita');
                if ($hasAppointment) {
                    $nextAppointment = oeFormatShortDate($row['next_appt_date']);
                    if (!empty($row['next_appt_status'])) {
                        $statusTitle = getListItemTitle('apptstat', $row['next_appt_status']);
                        if (!empty($statusTitle)) {
                            $nextAppointment .= ' (' . $statusTitle . ')';
                        }
                    }
                }

                $row['appt_key'] = $apptKey;
                $row['appt_class'] = $apptClass;
                $row['appt_label'] = $apptLabel;
                $row['next_appointment'] = $nextAppointment;
            }
            unset($row);

            $rows = array();
            $providerStats = array();
            foreach ($allRows as $row) {
                if ($form_validity === 'vigente' && $row['validity_key'] === 'expired') {
                    continue;
                }

                if ($form_validity === 'active' && $row['validity_key'] !== 'active') {
                    continue;
                }

                if ($form_validity === 'expired' && $row['validity_key'] !== 'expired') {
                    continue;
                }

                if ($form_validity === 'no_end' && $row['validity_key'] !== 'no_end') {
                    continue;
                }

                if ($form_appt_state !== '' && $row['appt_key'] !== $form_appt_state) {
                    continue;
                }

                if ($form_operation_state !== '' && $row['operation_key'] !== $form_operation_state) {
                    continue;
                }

                if ($form_patient_type !== '' && $row['patient_type_key'] !== $form_patient_type) {
                    continue;
                }

                if ($form_refer_state !== '' && trim((string)($row['refer_state'] ?? '')) !== $form_refer_state) {
                    continue;
                }

                $rowProviderId = trim((string)($row['effective_provider_id'] ?? ''));
                if ($form_assigned_provider === '__unassigned__' && $rowProviderId !== '') {
                    continue;
                }
                if ($form_assigned_provider !== '' && $form_assigned_provider !== '__unassigned__' && $rowProviderId !== $form_assigned_provider) {
                    continue;
                }

                $rows[] = $row;

                $totalReferrals++;
                if ($row['validity_key'] === 'expired') {
                    $caducadaCount++;
                } elseif ($row['validity_key'] === 'no_end') {
                    $vigenteCount++;
                    $openEndCount++;
                } else {
                    $vigenteCount++;
                }

                if ($row['appt_key'] === 'scheduled') {
                    $assignedCount++;
                } elseif ($row['appt_key'] === 'pending_schedule') {
                    $pendingScheduleCount++;
                } else {
                    $pendingCount++;
                }

                if ($row['operation_key'] === 'operated') {
                    $operatedCount++;
                } else {
                    $notOperatedCount++;
                }

                if ($row['patient_type_key'] === 'new') {
                    $newPatientCount++;
                } else {
                    $returningPatientCount++;
                }

                if ($row['appt_key'] === 'scheduled') {
                    $providerName = trim((string)($row['effective_provider_name'] ?? ''));
                    if ($providerName === '') {
                        $providerName = xlt('Sin asignar');
                    }

                    $providerKey = $rowProviderId !== '' ? ('id:' . $rowProviderId) : ('name:' . $providerName);
                    if (!isset($providerStats[$providerKey])) {
                        $providerStats[$providerKey] = array(
                            'provider_name' => $providerName,
                            'assigned_referrals' => 0,
                            'patients' => array(),
                        );
                    }

                    $providerStats[$providerKey]['assigned_referrals']++;
                    $providerStats[$providerKey]['patients'][$row['pid']] = true;
                }
            }

            $providerStatsRows = array_values($providerStats);
            foreach ($providerStatsRows as &$psrow) {
                $psrow['unique_patients'] = count($psrow['patients']);
            }
            unset($psrow);

            usort($providerStatsRows, function ($a, $b) {
                if ($a['assigned_referrals'] === $b['assigned_referrals']) {
                    return strcmp($a['provider_name'], $b['provider_name']);
                }

                return ($a['assigned_referrals'] > $b['assigned_referrals']) ? -1 : 1;
            });
            ?>

            <div id="report_results">
                <?php if (!empty($autoAssignMessage)) { ?>
                    <div class="alert alert-success" style="margin-bottom:8px; padding:8px 10px;">
                        <?php echo text($autoAssignMessage); ?>
                    </div>
                <?php } ?>
                <div class="text-muted" style="margin-bottom:8px; font-size:12px;">
                    <?php echo xlt('El estado de cita considera citas desde la fecha de referencia y dentro del rango de vigencia de la referencia.'); ?>
                </div>
                <div class="referral-summary">
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Total de referencias'); ?></span>
                        <span class="summary-value" data-summary-key="total"><?php echo text($totalReferrals); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Vigentes'); ?></span>
                        <span class="summary-value" data-summary-key="active"><?php echo text($vigenteCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Caducadas'); ?></span>
                        <span class="summary-value" data-summary-key="expired"><?php echo text($caducadaCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Sin fecha fin'); ?></span>
                        <span class="summary-value" data-summary-key="no_end"><?php echo text($openEndCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Asignadas'); ?></span>
                        <span class="summary-value" data-summary-key="scheduled"><?php echo text($assignedCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Pendientes de agendar'); ?></span>
                        <span class="summary-value" data-summary-key="pending_schedule"><?php echo text($pendingScheduleCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Pendientes'); ?></span>
                        <span class="summary-value" data-summary-key="pending"><?php echo text($pendingCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Operados'); ?></span>
                        <span class="summary-value" data-summary-key="operated"><?php echo text($operatedCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('No operados'); ?></span>
                        <span class="summary-value" data-summary-key="not_operated"><?php echo text($notOperatedCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Pacientes nuevos'); ?></span>
                        <span class="summary-value" data-summary-key="new_patient"><?php echo text($newPatientCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Pacientes recurrentes'); ?></span>
                        <span class="summary-value" data-summary-key="returning_patient"><?php echo text($returningPatientCount); ?></span>
                    </div>
                </div>
                <div class="provider-stats-wrap">
                    <div class="d-flex align-items-center" style="gap:8px; margin-bottom:6px;">
                        <div class="stats-title" style="margin:0;"><?php echo xlt('Referencias asignadas por proveedor'); ?></div>
                        <a href="#providerStatsCollapse" class="btn btn-sm btn-default" data-toggle="collapse" aria-expanded="false" aria-controls="providerStatsCollapse">
                            <?php echo xlt('Mostrar/Ocultar'); ?>
                        </a>
                    </div>
                    <div id="providerStatsCollapse" class="collapse">
                        <?php if (empty($providerStatsRows)) { ?>
                            <div class="text-muted" style="font-size:12px;"><?php echo xlt('No hay referencias asignadas en los filtros actuales.'); ?></div>
                        <?php } else { ?>
                            <table class='table table-bordered table-striped'>
                                <thead>
                                <tr>
                                    <th><?php echo xlt('Proveedor'); ?></th>
                                    <th><?php echo xlt('Referencias asignadas'); ?></th>
                                    <th><?php echo xlt('Pacientes únicos'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($providerStatsRows as $psrow) { ?>
                                    <tr>
                                        <td><?php echo text($psrow['provider_name']); ?></td>
                                        <td><?php echo text($psrow['assigned_referrals']); ?></td>
                                        <td><?php echo text($psrow['unique_patients']); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
                <table width='98%' id='mymaintable' class='table table-bordered table-striped'>
                    <thead>
                    <tr>
                        <th><?php echo xlt('Referido a'); ?></th>
                        <th><?php echo xlt('Fecha de referencia'); ?></th>
                        <th><?php echo xlt('Fecha de ingreso al sistema'); ?></th>
                        <th><?php echo xlt('Tipo de paciente'); ?></th>
                        <th><?php echo xlt('Paciente'); ?></th>
                        <th><?php echo xlt('ID'); ?></th>
                        <th><?php echo xlt('Motivo'); ?></th>
                        <th><?php echo xlt('Servicio solicitado'); ?></th>
                        <th><?php echo xlt('Código de referencia'); ?></th>
                        <th><?php echo xlt('Válida hasta'); ?></th>
                        <th><?php echo xlt('Vigencia'); ?></th>
                        <th><?php echo xlt('Cita'); ?></th>
                        <th><?php echo xlt('Proveedor asignado'); ?></th>
                        <th><?php echo xlt('Próxima cita'); ?></th>
                        <th><?php echo xlt('Cirugía'); ?></th>
                        <th><?php echo xlt('Fecha de cirugía'); ?></th>
                        <th><?php echo xlt('Procedimiento operado'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row) { ?>
	                            <tr data-validity="<?php echo attr($row['validity_key']); ?>" data-appt="<?php echo attr($row['appt_key']); ?>" data-operation="<?php echo attr($row['operation_key']); ?>" data-patient-type="<?php echo attr($row['patient_type_key']); ?>">
	                                <td><?php echo text($row['refer_to_display']); ?></td>
	                                <td data-order="<?php echo attr($row['refer_date']); ?>">
                                    <a href='#' onclick="return show_referral(<?php echo js_escape($row['id']); ?>)">
                                        <?php echo text(oeFormatShortDate($row['refer_date'])); ?>&nbsp;
                                    </a>
                                </td>
                                <?php $createdDate = empty($row['created_date']) ? '' : substr($row['created_date'], 0, 10); ?>
                                <td data-order="<?php echo attr($createdDate); ?>">
                                    <?php
                                    echo text($createdDate ? oeFormatShortDate($createdDate) : '');
                                    ?>
                                </td>
                                <td><span
                                        class="status-pill <?php echo attr($row['patient_type_class']); ?>"><?php echo text($row['patient_type_label']); ?></span>
                                </td>
                                <td><?php echo text($row['patient_name']); ?></td>
                                <td><?php echo text($row['pubpid']); ?></td>
                                <td><?php echo text($row['body']); ?></td>
                                <td data-cpt4="<?php echo attr(implode(';', getReferralRequestedServiceCodes($row['refer_related_code'] ?? ''))); ?>">
                                    <?php echo text(formatReferralRequestedService($row['refer_related_code'] ?? '', $requestedServiceDescriptions)); ?>
                                </td>
                                <td><?php echo text($row['refer_id']); ?></td>
                                <td data-order="<?php echo attr($row['refer_end_date'] ?? ''); ?>">
                                    <?php
                                    if (!empty($row['refer_end_date'])) {
                                        echo text(oeFormatShortDate($row['refer_end_date']));
                                    } else {
                                        echo '<span class="status-pill pill-neutral">' . xlt('Sin fecha fin') . '</span>';
                                    }
                                    ?>
                                </td>
                                <td><span
                                        class="status-pill <?php echo attr($row['validity_class']); ?>"><?php echo text($row['validity_label']); ?></span>
                                </td>
                                <td><span
                                        class="status-pill <?php echo attr($row['appt_class']); ?>"><?php echo text($row['appt_label']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $apptProvider = trim((string)($row['effective_provider_name'] ?? ''));
                                    if ($apptProvider === '') {
                                    $apptProvider = trim((string)($row['next_appt_provider'] ?? ''));
                                    }
                                    if ($apptProvider === '') {
                                        $apptProvider = trim((string)($row['assigned_provider_name'] ?? ''));
                                    }
                                    echo text($apptProvider !== '' ? $apptProvider : xlt('Sin asignar'));
                                    ?>
                                </td>
                                <td data-order="<?php echo attr($row['next_appt_date'] ?? ''); ?>"><?php echo text($row['next_appointment']); ?></td>
                                <td><span
                                        class="status-pill <?php echo attr($row['operation_class']); ?>"><?php echo text($row['operation_label']); ?></span>
                                </td>
                                <td data-order="<?php echo attr($row['operation_date'] ?? ''); ?>">
                                    <?php echo !empty($row['operation_date']) ? text(oeFormatShortDate($row['operation_date'])) : ''; ?>
                                </td>
                                <td><?php echo text($row['operation_details']); ?></td>
                            </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div> <!-- end of results -->
        <?php } else { ?>
            <div class='text'>
                <?php echo xlt('Ingrese los criterios de búsqueda y haga clic en Consultar para ver los resultados.'); ?>
            </div>
        <?php } ?>
    </form>
</div>

</body>
</html>
