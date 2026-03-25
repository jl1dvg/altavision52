<?php
/**
 * This report cross-references appointments with encounters.
 * For a given date, show a line for each appointment with the
 * matching encounter, and also for each encounter that has no
 * matching appointment.  This helps to catch these errors:
 *
 * * Appointments with no encounter
 * * Encounters with no appointment
 * * Codes not justified
 * * Codes not authorized
 * * Procedure codes without a fee
 * * Fees assigned to diagnoses (instead of procedures)
 * * Encounters not billed
 *
 * For decent performance the following indexes are highly recommended:
 *   openemr_postcalendar_events.pc_eventDate
 *   forms.encounter
 *   billing.pid_encounter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../../custom/code_types.inc.php");


use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

$errmsg = "";
$alertmsg = ''; // not used yet but maybe later
$grand_total_charges = 0;
$grand_total_collected = 0;
$grand_total_encounters = 0;

$ORDERHASH = array(
    'doctor' => 'docname',
);

function postError($msg)
{
    global $errmsg;
    if ($errmsg) {
        $errmsg .= '<br />';
    }

    $errmsg .= text($msg);
}

function appendError(&$messages, $msg)
{
    if (!in_array($msg, $messages, true)) {
        $messages[] = $msg;
    }
}

function bucks($amount)
{
    if ($amount) {
        return oeFormatMoney($amount);
    }
}

function monthLabelEs($dateValue)
{
    $months = array(
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    );

    $monthNumber = (int) date('n', strtotime($dateValue));
    return $months[$monthNumber] ?? date('F', strtotime($dateValue));
}

function endDoctor(&$docrow)
{
    global $grand_total_charges, $grand_total_collected, $grand_total_encounters;
    if (!$docrow['docname']) {
        return;
    }

    echo " <tr class='report_totals'>\n";
    echo "  <td colspan='5'>\n";
    echo "   &nbsp;" . xlt('Totals for') . ' ' . text($docrow['docname']) . "\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;" . text($docrow['encounters']) . "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;";
    echo text(bucks($docrow['charges']));
    echo "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;";
    echo text(bucks($docrow['collected']));
    echo "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo " </tr>\n";

    $grand_total_charges += $docrow['charges'];
    $grand_total_collected += $docrow['collected'];
    $grand_total_encounters += $docrow['encounters'];

    $docrow['charges'] = 0;
    $docrow['collected'] = 0;
    $docrow['encounters'] = 0;
}

function endDate(&$daterow)
{
    if (empty($daterow['datekey'])) {
        return;
    }

    echo " <tr class='report_totals'>\n";
    echo "  <td colspan='5'>\n";
    echo "   &nbsp;" . xlt('Date subtotal') . ' ' . text(oeFormatShortDate($daterow['datekey'])) . "\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;" . text($daterow['encounters']) . "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;" . text(bucks($daterow['charges'])) . "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;" . text(bucks($daterow['collected'])) . "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo "  <td >\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo " </tr>\n";

    $daterow['datekey'] = '';
    $daterow['charges'] = 0;
    $daterow['collected'] = 0;
    $daterow['encounters'] = 0;
}

$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_provider = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_month = isset($_POST['form_month']) ? preg_replace('/[^0-9\-]/', '', $_POST['form_month']) : date('Y-m');
if (!preg_match('/^\d{4}\-\d{2}$/', $form_month)) {
    $form_month = date('Y-m');
}

$form_from_date = $form_month . '-01';
$form_to_date = date('Y-m-t', strtotime($form_from_date));
$form_details = !isset($_POST['form_details']) || !empty($_POST['form_details']);
$form_refresh = !empty($_POST['form_refresh']);
$form_excelexport = !empty($_POST['form_excelexport']);

$form_orderby = (isset($_REQUEST['form_orderby']) && isset($ORDERHASH[$_REQUEST['form_orderby']])) ?
    $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

// MySQL doesn't grok full outer joins so we do it the hard way.
//
$sqlBindArray = array();
$query = "( " .
    "SELECT " .
    "e.pc_eventDate, e.pc_startTime, " .
    "fe.encounter, fe.date AS encdate, " .
    "fe.provider_id AS operador, " .
    "p.fname, p.lname, p.lname2, p.pid, p.pubpid, " .
    "CONCAT( u.lname, ', ', u.fname ) AS docname " .
    "FROM openemr_postcalendar_events AS e " .
    "LEFT OUTER JOIN form_encounter AS fe " .
    "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
    "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
if ($form_to_date) {
    $query .= "e.pc_eventDate >= ? AND e.pc_eventDate <= ? ";
    array_push($sqlBindArray, $form_from_date, $form_to_date);
} else {
    $query .= "e.pc_eventDate = ? ";
    array_push($sqlBindArray, $form_from_date);
}

if ($form_facility !== '') {
    $query .= "AND e.pc_facility = ? ";
    array_push($sqlBindArray, $form_facility);
}

$query .= "AND UPPER(COALESCE(p.pricelevel, '')) != ? ";
array_push($sqlBindArray, 'IESS');

// $query .= "AND ( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
$query .= "AND e.pc_pid != '' AND e.pc_apptstatus != ? " .
    ") UNION ( " .
    "SELECT " .
    "e.pc_eventDate, e.pc_startTime, " .
    "fe.encounter, fe.date AS encdate, " .
    "fe.provider_id AS operador, " .
    "p.fname, p.lname, p.lname2, p.pid, p.pubpid, " .
    "CONCAT( u.lname, ', ', u.fname ) AS docname " .
    "FROM form_encounter AS fe " .
    "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
    "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
    // "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
    "e.pc_pid != '' AND e.pc_apptstatus != ? " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
    "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
array_push($sqlBindArray, '?', '?');
if ($form_to_date) {
    // $query .= "LEFT(fe.date, 10) >= '$form_from_date' AND LEFT(fe.date, 10) <= '$form_to_date' ";
    $query .= "fe.date >= ? AND fe.date <= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
} else {
    // $query .= "LEFT(fe.date, 10) = '$form_from_date' ";
    $query .= "fe.date >= ? AND fe.date <= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_from_date . ' 23:59:59');
}

if ($form_facility !== '') {
    $query .= "AND fe.facility_id = ? ";
    array_push($sqlBindArray, $form_facility);
}

$query .= "AND UPPER(COALESCE(p.pricelevel, '')) != ? ";
array_push($sqlBindArray, 'IESS');

$query .= ") ORDER BY " . $orderby . ", IFNULL(pc_eventDate, encdate), pc_startTime";

$res = sqlStatement($query, $sqlBindArray);

if ($form_excelexport) {
    $excelBindArray = array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
    $excelQuery = "SELECT fe.date AS service_date, p.pubpid, p.fname, p.lname, p.lname2, " .
        "COALESCE(NULLIF(p.pricelevel, ''), 'Particular') AS payer_name, " .
        "b.code, b.modifier, b.code_text, b.fee, COALESCE(NULLIF(b.units, 0), 1) AS units " .
        "FROM form_encounter AS fe " .
        "INNER JOIN patient_data AS p ON p.pid = fe.pid " .
        "INNER JOIN billing AS b ON b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 " .
        "INNER JOIN code_types AS ct ON ct.ct_key = b.code_type AND ct.ct_fee = 1 " .
        "WHERE fe.date >= ? AND fe.date <= ? " .
        "AND UPPER(COALESCE(p.pricelevel, '')) != ? ";
    $excelBindArray[] = 'IESS';

    if ($form_facility !== '') {
        $excelQuery .= "AND fe.facility_id = ? ";
        $excelBindArray[] = $form_facility;
    }

    if ($form_provider !== '') {
        $excelQuery .= "AND COALESCE(NULLIF(b.provider_id, 0), fe.provider_id) = ? ";
        $excelBindArray[] = $form_provider;
    }

    $excelQuery .= "ORDER BY fe.date, p.lname, p.fname, b.id";
    $excelRes = sqlStatement($excelQuery, $excelBindArray);

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=provider_fees_" . $form_month . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    $monthSubtotal = 0;
    $grandTotalExcel = 0;
    $currentMonthLabel = '';

    echo "<html><head><meta charset='utf-8'>";
    echo "<style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
        td, th { border: 1px solid #7f7f7f; padding: 6px; }
        th { background: #d9e2f3; font-weight: bold; text-align: center; }
        .money { text-align: right; white-space: nowrap; }
        .subtotal td { background: #ffd966; font-weight: bold; }
    </style></head><body>";
    echo "<table>";
    echo "<tr><th>Mes</th><th>Paciente</th><th>Procedimiento</th><th>Cantidad</th><th>Pagador</th><th>Valor</th><th>Total</th></tr>";

    while ($excelRow = sqlFetchArray($excelRes)) {
        $monthLabel = monthLabelEs(substr($excelRow['service_date'], 0, 10));
        if ($currentMonthLabel !== '' && $currentMonthLabel !== $monthLabel) {
            echo "<tr class='subtotal'><td colspan='6'>Subtotal " . text($currentMonthLabel) . "</td><td class='money'>" . text(bucks($monthSubtotal)) . "</td></tr>";
            $monthSubtotal = 0;
        }

        $currentMonthLabel = $monthLabel;
        $patientName = trim($excelRow['fname'] . ' ' . $excelRow['lname'] . ' ' . $excelRow['lname2']);
        $procedureCode = $excelRow['code'];
        if (!empty($excelRow['modifier'])) {
            $procedureCode .= '-' . $excelRow['modifier'];
        }

        $procedureText = trim((string) $excelRow['code_text']);
        if ($procedureText === '') {
            $procedureText = $procedureCode;
        }

        $lineTotal = (float) $excelRow['fee'];
        $monthSubtotal += $lineTotal;
        $grandTotalExcel += $lineTotal;

        echo "<tr>";
        echo "<td>" . text($monthLabel) . "</td>";
        echo "<td>" . text($patientName) . "</td>";
        echo "<td>" . text($procedureText) . "</td>";
        echo "<td>" . text($excelRow['units']) . "</td>";
        echo "<td>" . text($excelRow['payer_name']) . "</td>";
        echo "<td class='money'>" . text(bucks($lineTotal)) . "</td>";
        echo "<td class='money'>" . text(bucks($lineTotal)) . "</td>";
        echo "</tr>";
    }

    if ($currentMonthLabel !== '') {
        echo "<tr class='subtotal'><td colspan='6'>Subtotal " . text($currentMonthLabel) . "</td><td class='money'>" . text(bucks($monthSubtotal)) . "</td></tr>";
    }

    echo "<tr class='subtotal'><td colspan='6'>Total general</td><td class='money'>" . text(bucks($grandTotalExcel)) . "</td></tr>";
    echo "</table></body></html>";
    exit;
}
?>
<html>
<head>
    <title><?php echo xlt('Provider Fees by Encounter'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <style type="text/css">
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
                margin-top: 0px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }

        #report_parameters {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #f8fafc;
            padding: 10px 12px;
            margin: 10px 0 14px;
        }

        #report_results table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        #report_results thead th {
            background: #1f2937;
            color: #f9fafb;
            padding: 8px;
            border: 1px solid #374151;
            font-weight: 600;
            white-space: nowrap;
        }

        #report_results tbody td {
            padding: 7px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        #report_results tbody tr.detail-row:hover {
            background: #f1f5f9;
        }

        #report_results tbody tr.row-has-error {
            background: #fff5f5;
        }

        #report_results tbody tr.row-settled {
            background: #f2fbf6;
        }

        #report_results tbody tr.report_totals {
            background: #e8f2ff;
            font-weight: 700;
        }

        .report_hint {
            margin-top: 6px;
            margin-bottom: 8px;
            color: #334155;
            font-size: 12px;
        }

        .patient-link,
        .encounter-link {
            color: #0b63d1;
            text-decoration: none;
            font-weight: 600;
        }

        .patient-link:hover,
        .encounter-link:hover {
            text-decoration: underline;
        }

        .status-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-pill.ok {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pill.pending {
            background: #fee2e2;
            color: #991b1b;
        }

        .col-generated,
        .col-collected {
            font-weight: 600;
            white-space: nowrap;
        }

        .cell-codes,
        .cell-paymethods {
            font-size: 12px;
            line-height: 1.3;
        }
    </style>

    <script LANGUAGE="JavaScript">
        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
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

        function dosort(orderby) {
            var f = document.forms[0];
            f.form_orderby.value = orderby;
            f.submit();
            return false;
        }

        function refreshme() {
            document.forms[0].submit();
        }

        function topatient(newpid, enc) {
            top.restoreSession();
            var target = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
            if (enc && parseInt(enc, 10) > 0) {
                target += "&set_encounterid=" + encodeURIComponent(enc);
            }

            if (top.RTop) {
                top.RTop.location = target;
            } else {
                window.location = target;
            }

            return false;
        }
    </script>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Provider Fees by Encounter'); ?></span>
<div class='report_hint'><?php echo xlt('Tip: click the patient name or encounter number to open the visit.'); ?></div>

<div id="report_parameters_daterange">
    <?php echo text(monthLabelEs($form_from_date) . ' ' . date('Y', strtotime($form_from_date))); ?>
</div>

<form method='post' id='theform' action='doc_encounter_report.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>

    <div id="report_parameters">

        <table>
            <tr>
                <td width='630px'>
                    <div style='float:left'>

                        <table class='text'>
                            <tr>
                                <td class='control-label'>
                                    <?php echo xlt('Facility'); ?>:
                                </td>
                                <td>
                                    <?php
                                    // Build a drop-down list of facilities.
                                    //
                                    $fres = $facilityService->getAll();
                                    echo "   <select name='form_facility' class='form-control'>\n";
                                    echo "    <option value=''>-- " . xlt('All Facilities') . " --\n";
                                    foreach ($fres as $frow) {
                                        $facid = $frow['id'];
                                        echo "    <option value='" . attr($facid) . "'";
                                        if ($facid == $form_facility) {
                                            echo " selected";
                                        }
                                        echo ">" . text($frow['name']) . "\n";
                                    }

                                    echo "    <option value='0'";
                                    if ($form_facility === '0') {
                                        echo " selected";
                                    }

                                    echo ">-- " . xlt('Unspecified') . " --\n";
                                    echo "   </select>\n";
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class='control-label'>
                                    <?php echo xlt('Operador'); ?>:
                                </td>
                                <td>
                                    <?php

                                    $queryp = "select id, lname, fname from users where " .
                                        "authorized = 1 order by lname, fname";
                                    $resp = sqlStatement($queryp);
                                    echo "   &nbsp;<select name='form_provider' class='form-control'>\n";
                                    echo "    <option value=''>-- " . xlt('All Providers') . " --\n";
                                    while ($row = sqlFetchArray($resp)) {
                                        $provid = $row['id'];
                                        echo "    <option value='" . attr($provid) . "'";
                                        if ($provid == $form_provider) {
                                            echo " selected";
                                        }

                                        echo ">" . text($row['lname']) . ", " . text($row['fname']) . "\n";
                                    }

                                    echo "   </select>\n";
                                    ?>
                                    &nbsp;
                                </td>
                                <td class='control-label'>
                                    <?php echo xlt('Month'); ?>:
                                </td>
                                <td>
                                    <input type='month' class='form-control' name='form_month'
                                           id="form_month" size='10'
                                           value='<?php echo attr($form_month); ?>'>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <div class="checkbox">
                                        <input type='hidden' name='form_details' value='0'>
                                        <label><input type='checkbox' name='form_details'
                                                      value='1'<?php echo ($form_details) ? " checked" : ""; ?>><?php echo xlt('Details') ?>
                                        </label>
                                    </div>
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
                                           onclick='$("#form_refresh").attr("value","true"); $("#form_excelexport").val(""); $("#theform").submit();'>
                                            <?php echo xlt('Submit'); ?>
                                        </a>
                                        <?php if ($form_refresh || !empty($_POST['form_orderby'])) { ?>
                                            <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                                <?php echo xlt('Print'); ?>
                                            </a>
                                            <a href='#' class='btn btn-default btn-transmit'
                                               onclick='$("#form_refresh").val(""); $("#form_excelexport").val("1"); $("#theform").submit();'>
                                                <?php echo xlt('Export Excel'); ?>
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

    </div> <!-- end apptenc_report_parameters -->

    <?php
    if ($form_refresh || !empty($_POST['form_orderby'])) {
        ?>
        <div id="report_results">
            <table id='mymaintable' class='fees-table'>

                <thead>
                <th>
                    <a href="nojs.php" onclick="return dosort('doctor')"
                        <?php echo ($form_orderby == "doctor") ? " style=\"color:#00cc00\"" : ""; ?>>
                        <?php echo xlt('Practitioner'); ?> </a>
                </th>
                <th> &nbsp;<?php echo xlt('Date/Appt'); ?> </th>
                <th> &nbsp;<?php echo xlt('Patient'); ?> </th>
                <th> &nbsp;<?php echo xlt('ID'); ?> </th>
                <th> &nbsp;<?php echo xlt('Operador'); ?> </th>
                <th> <?php echo xlt('Encounter'); ?>&nbsp;</th>
                <th> <?php echo xlt('Code(s)'); ?>&nbsp;</th>
                <th> <?php echo xlt('Procedure Detail'); ?>&nbsp;</th>
                <th> <?php echo xlt('Generated'); ?>&nbsp;</th>
                <th> <?php echo xlt('Collected'); ?>&nbsp;</th>
                <th> <?php echo xlt('Payment Method(s)'); ?>&nbsp;</th>
                <th> <?php echo xlt('Billed'); ?> </th>
                </thead>
                <tbody>
                <?php
                if ($res) {
                    $docrow = array('docname' => '', 'charges' => 0, 'collected' => 0, 'encounters' => 0);
                    $daterow = array('datekey' => '', 'charges' => 0, 'collected' => 0, 'encounters' => 0);
                    $seenEncounters = array();
                    while ($row = sqlFetchArray($res)) {
                        $patient_id = $row['pid'];
                        $encounter = $row['encounter'];
                        $patname = $row['fname'] . ", " . $row['lname'] . " " . $row['lname2'];
                        $serviceDate = empty($row['pc_eventDate']) ? substr($row['encdate'], 0, 10) : $row['pc_eventDate'];

                        if ($encounter) {
                            $encounterKey = $patient_id . ':' . $encounter;
                            if (isset($seenEncounters[$encounterKey])) {
                                continue;
                            }

                            $seenEncounters[$encounterKey] = true;
                        }

                        $encounterProviderId = (int) $row['operador'];
                        $encounterProviderName = $row['docname'] ? $row['docname'] : xl('Unknown');
                        $groupedRows = array();
                        $encounterCollected = 0;
                        $paymentMethods = array();
                        $encounterHasMixedProviders = false;
                        $encounterHasBillingRows = false;
                        $gcac_related_visit = false;

                        if ($encounter) {
                            $queryb = "SELECT code_type, code, code_text, modifier, authorized, billed, fee, justify, provider_id " .
                                "FROM billing WHERE " .
                                "pid = ? AND encounter = ? AND activity = 1 " .
                                "ORDER BY id";
                            $bres = sqlStatement($queryb, array($patient_id, $encounter));
                            while ($brow = sqlFetchArray($bres)) {
                                $code_type = $brow['code_type'];
                                $encounterHasBillingRows = true;
                                $effectiveProviderId = empty($brow['provider_id']) ? $encounterProviderId : (int) $brow['provider_id'];
                                if ($form_provider !== '' && (string) $effectiveProviderId !== (string) $form_provider) {
                                    continue;
                                }

                                if (empty($groupedRows[$effectiveProviderId])) {
                                    $groupedRows[$effectiveProviderId] = array(
                                        'provider_id' => $effectiveProviderId,
                                        'docname' => $effectiveProviderId ? getProviderName($effectiveProviderId) : $encounterProviderName,
                                        'operador' => $effectiveProviderId,
                                        'billed' => true,
                                        'charges' => 0,
                                        'collected' => 0,
                                        'paymentMethodList' => '-',
                                        'codes' => array(),
                                        'procedureDetails' => array(),
                                        'errors' => array(),
                                    );
                                }

                                if (!empty($code_types[$code_type]['fee']) && !$brow['billed']) {
                                    $groupedRows[$effectiveProviderId]['billed'] = false;
                                }

                                if (!$GLOBALS['simplified_demographics'] && !$brow['authorized']) {
                                    appendError($groupedRows[$effectiveProviderId]['errors'], xl('Needs Auth'));
                                }

                                if (!empty($code_types[$code_type]['just']) && !$brow['justify']) {
                                    appendError($groupedRows[$effectiveProviderId]['errors'], xl('Needs Justify'));
                                }

                                if (!empty($code_types[$code_type]['fee'])) {
                                    $groupedRows[$effectiveProviderId]['charges'] += (float) $brow['fee'];
                                    $codedLabel = $brow['code'];
                                    if (!empty($brow['modifier'])) {
                                        $codedLabel .= '-' . $brow['modifier'];
                                    }

                                    $groupedRows[$effectiveProviderId]['codes'][$codedLabel] = true;
                                    $procedureText = trim((string) $brow['code_text']);
                                    if ($procedureText === '') {
                                        $procedureText = $codedLabel;
                                    }

                                    $groupedRows[$effectiveProviderId]['procedureDetails'][$codedLabel . '|' . $procedureText] = $codedLabel . ' - ' . $procedureText;
                                    if ((float) $brow['fee'] == 0.0 && !$GLOBALS['ippf_specific']) {
                                        appendError($groupedRows[$effectiveProviderId]['errors'], xl('Missing Fee'));
                                    }
                                } elseif ((float) $brow['fee'] != 0.0) {
                                    appendError($groupedRows[$effectiveProviderId]['errors'], xl('Fee is not allowed'));
                                }

                                if ($GLOBALS['ippf_specific'] && !empty($code_types[$code_type]['fee'])) {
                                    $sqlBindArray = array();
                                    $query = "SELECT related_code FROM codes WHERE code_type = ? AND code = ? AND ";
                                    array_push($sqlBindArray, $code_types[$code_type]['id'], $brow['code']);
                                    if ($brow['modifier']) {
                                        $query .= "modifier = ?";
                                        array_push($sqlBindArray, $brow['modifier']);
                                    } else {
                                        $query .= "(modifier IS NULL OR modifier = '')";
                                    }

                                    $query .= " LIMIT 1";
                                    $tmp = sqlQuery($query, $sqlBindArray);
                                    $relcodes = explode(';', $tmp['related_code']);
                                    foreach ($relcodes as $codestring) {
                                        if ($codestring === '') {
                                            continue;
                                        }

                                        list($codetype, $code) = explode(':', $codestring);
                                        if ($codetype !== 'IPPF') {
                                            continue;
                                        }

                                        if (preg_match('/^25222/', $code)) {
                                            $gcac_related_visit = true;
                                        }
                                    }
                                }
                            }

                            $encounterHasMixedProviders = count($groupedRows) > 1;

                            $payRes = sqlStatement(
                                "SELECT COALESCE(NULLIF(s.payment_method, ''), NULLIF(a.account_code, ''), ?) AS payment_method, " .
                                "COALESCE(SUM(a.pay_amount), 0) AS total_paid " .
                                "FROM ar_activity AS a " .
                                "LEFT JOIN ar_session AS s ON s.session_id = a.session_id " .
                                "WHERE a.pid = ? AND a.encounter = ? AND a.pay_amount != 0 " .
                                "GROUP BY COALESCE(NULLIF(s.payment_method, ''), NULLIF(a.account_code, ''), ?)",
                                array('N/A', $patient_id, $encounter, 'N/A')
                            );
                            while ($payRow = sqlFetchArray($payRes)) {
                                $methodCode = $payRow['payment_method'];
                                $methodLabel = $methodCode;
                                if (function_exists('getListItemTitle')) {
                                    $listMethod = getListItemTitle('payment_method', $methodCode);
                                    if (!empty($listMethod)) {
                                        $methodLabel = $listMethod;
                                    }
                                }

                                $methodAmount = (float) $payRow['total_paid'];
                                $encounterCollected += $methodAmount;
                                $paymentMethods[] = $methodLabel . ': ' . oeFormatMoney($methodAmount);
                            }
                        }

                        if ($gcac_related_visit) {
                            $grow = sqlQuery("SELECT COUNT(*) AS count FROM forms " .
                                "WHERE pid = ? AND encounter = ? AND " .
                                "deleted = 0 AND formdir = 'LBFgcac'", array($patient_id, $encounter));
                            if (empty($grow['count'])) {
                                foreach ($groupedRows as &$groupedRow) {
                                    appendError($groupedRow['errors'], xl('GCAC visit form is missing'));
                                }

                                unset($groupedRow);
                            }
                        }

                        if (!$encounter) {
                            if ($form_provider !== '' && (string) $encounterProviderId !== (string) $form_provider) {
                                continue;
                            }

                            $groupedRows[$encounterProviderId] = array(
                                'provider_id' => $encounterProviderId,
                                'docname' => $encounterProviderName,
                                'operador' => $encounterProviderId,
                                'billed' => false,
                                'charges' => 0,
                                'collected' => 0,
                                'paymentMethodList' => '-',
                                'codes' => array(),
                                'procedureDetails' => array(),
                                'errors' => array(xl('No visit')),
                            );
                        } elseif (empty($groupedRows)) {
                            if ($encounterHasBillingRows && $form_provider !== '') {
                                continue;
                            }

                            if ($form_provider !== '' && (string) $encounterProviderId !== (string) $form_provider) {
                                continue;
                            }

                            $groupedRows[$encounterProviderId] = array(
                                'provider_id' => $encounterProviderId,
                                'docname' => $encounterProviderName,
                                'operador' => $encounterProviderId,
                                'billed' => false,
                                'charges' => 0,
                                'collected' => 0,
                                'paymentMethodList' => '-',
                                'codes' => array(),
                                'procedureDetails' => array(),
                                'errors' => array($GLOBALS['simplified_demographics'] ? xl('Not checked out') : xl('Not billed')),
                            );
                        }

                        if (!$encounterHasMixedProviders || $form_provider !== '') {
                            $paymentMethodList = empty($paymentMethods) ? '-' : implode(', ', $paymentMethods);
                            foreach ($groupedRows as &$groupedRow) {
                                $groupedRow['collected'] = $encounterCollected;
                                $groupedRow['paymentMethodList'] = $paymentMethodList;
                            }

                            unset($groupedRow);
                        }

                        foreach ($groupedRows as $groupedRow) {
                            $docname = $groupedRow['docname'] ? $groupedRow['docname'] : xl('Unknown');
                            if ($docname != $docrow['docname']) {
                                endDate($daterow);
                                endDoctor($docrow);
                            }

                            if ($serviceDate != $daterow['datekey']) {
                                endDate($daterow);
                                $daterow['datekey'] = $serviceDate;
                            }

                            $charges = $groupedRow['charges'];
                            $collected = $groupedRow['collected'];
                            $codeList = empty($groupedRow['codes']) ? '-' : implode(', ', array_keys($groupedRow['codes']));
                            $procedureDetailList = '-';
                            if (!empty($groupedRow['procedureDetails'])) {
                                $procedureDetailList = implode("<br />", array_map(function ($detail) {
                                    $parts = explode(' - ', $detail, 2);
                                    $description = isset($parts[1]) ? $parts[1] : $parts[0];
                                    return text($description);
                                }, array_values($groupedRow['procedureDetails'])));
                            }
                            $paymentMethodList = $groupedRow['paymentMethodList'];
                            $errmsg = empty($groupedRow['errors']) ? '' : implode('<br />', array_map('text', $groupedRow['errors']));
                            $billed = $groupedRow['billed'] && $charges > 0;

                            $docrow['charges'] += $charges;
                            $docrow['collected'] += $collected;
                            if ($encounter) {
                                ++$docrow['encounters'];
                            }
                            $daterow['charges'] += $charges;
                            $daterow['collected'] += $collected;
                            if ($encounter) {
                                ++$daterow['encounters'];
                            }

                            if ($form_details) {
                                $rowClass = 'detail-row';
                                if (!empty($errmsg)) {
                                    $rowClass .= ' row-has-error';
                                } elseif ($charges > 0 && $collected >= $charges) {
                                    $rowClass .= ' row-settled';
                                }

                                $statusClass = $billed ? 'ok' : 'pending';
                                $statusLabel = $billed ? xlt('Billed') : xlt('Pending');
                                ?>
                                <tr class='<?php echo attr($rowClass); ?>'>
                                    <td>
                                        &nbsp;<?php echo ($docname == $docrow['docname']) ? "" : text($docname); ?>
                                    </td>
                                    <td>
                                        &nbsp;<?php
                                        if (empty($row['pc_eventDate'])) {
                                            echo text(oeFormatShortDate(substr($row['encdate'], 0, 10)));
                                        } else {
                                            echo text(oeFormatShortDate($row['pc_eventDate'])) . ' ' . text(substr($row['pc_startTime'], 0, 5));
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        &nbsp;<a href="#"
                                                 class="patient-link"
                                                 onclick="return topatient(<?php echo attr_js($patient_id); ?>, <?php echo attr_js($encounter); ?>);"><?php echo text($patname); ?></a>
                                    </td>
                                    <td>
                                        &nbsp;<?php echo text($row['pubpid']); ?>
                                    </td>
                                    <td>
                                        <?php echo text($docname); ?>&nbsp;
                                    </td>
                                    <td>
                                        <?php if ($encounter) { ?>
                                            <a href="#"
                                               class="encounter-link"
                                               onclick="return topatient(<?php echo attr_js($patient_id); ?>, <?php echo attr_js($encounter); ?>);"><?php echo text($encounter); ?></a>
                                        <?php } else { ?>
                                            &nbsp;
                                        <?php } ?>
                                    </td>
                                    <td class='cell-codes'>
                                        <?php echo text($codeList); ?>&nbsp;
                                    </td>
                                    <td class='cell-codes'>
                                        <?php echo $procedureDetailList; ?>&nbsp;
                                    </td>
                                    <td class='col-generated'>
                                        <?php echo text(bucks($charges)); ?>&nbsp;
                                    </td>
                                    <td class='col-collected'>
                                        <?php echo text(bucks($collected)); ?>&nbsp;
                                    </td>
                                    <td class='cell-paymethods'>
                                        <?php echo text($paymentMethodList); ?>&nbsp;
                                    </td>
                                    <td>
                                        <span class='status-pill <?php echo attr($statusClass); ?>'><?php echo text($statusLabel); ?></span>
                                        <?php if ($encounterHasMixedProviders && $paymentMethodList === '-') { ?>
                                            <br><span class='text-muted'><?php echo xlt('Mixed provider payments'); ?></span>
                                        <?php } ?>
                                        <?php if (!empty($errmsg)) { ?>
                                            <br><span class='text-danger'><?php echo $errmsg; ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            }

                            $docrow['docname'] = $docname;
                        }
                    } // end of row

                    endDate($daterow);
                    endDoctor($docrow);

                    echo " <tr class='report_totals'>\n";
                    echo "  <td colspan='5'>\n";
                    echo "   &nbsp;" . xlt('Grand Totals') . "\n";
                    echo "  </td>\n";
                    echo "  <td >\n";
                    echo "   &nbsp;" . text($grand_total_encounters) . "&nbsp;\n";
                    echo "  </td>\n";
                    echo "  <td >\n";
                    echo "   &nbsp;\n";
                    echo "  </td>\n";
                    echo "  <td >\n";
                    echo "   &nbsp;\n";
                    echo "  </td>\n";
                    echo "  <td >\n";
                    echo "   &nbsp;";
                    echo text(bucks($grand_total_charges));
                    echo "&nbsp;\n";
                    echo "  </td>\n";
                    echo "  <td >\n";
                    echo "   &nbsp;";
                    echo text(bucks($grand_total_collected));
                    echo "&nbsp;\n";
                    echo "  </td>\n";
                    echo "  <td >\n";
                    echo "   &nbsp;\n";
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                ?>
                </tbody>
            </table>
        </div> <!-- end the apptenc_report_results -->
    <?php } else { ?>
        <div class='text'>
            <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
        </div>
    <?php } ?>

    <input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>"/>
    <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
    <input type='hidden' name='form_excelexport' id='form_excelexport' value=''/>
</form>
<script>
    <?php if ($alertmsg) {
        echo " alert(" . js_escape($alertmsg) . ");\n";
    } ?>
</script>
</body>

</html>
