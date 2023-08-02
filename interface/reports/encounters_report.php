<?php
/**
 *  Encounters report.
 *
 *  This report shows past encounters with filtering and sorting,
 *  Added filtering to show encounters not e-signed, encounters e-signed and forms e-signed.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/FeeSheetHtml.class.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/patient_tracker.inc.php";
require_once "$srcdir/user.inc";
require_once "$srcdir/MedEx/API.php";


use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
    'doctor' => 'lower(u.lname), lower(u.fname), fe.date',
    'operador' => 'lower(f.provider_id > 0),lower(u.lname), lower(u.fname), fe.date',
    'responsable' => 'lower(fe.responsable_id > 0),lower(u.lname), lower(u.fname), fe.date',
    'patient' => 'lower(p.lname), lower(p.fname), fe.date',
    'pubpid' => 'lower(p.pubpid), fe.date',
    'time' => 'fe.date, lower(u.lname), lower(u.fname)',
    'categoria' => 'ca.pc_catdesc, fe.date, lower(u.lname), lower(u.fname)',
    'encounter' => 'fe.encounter, fe.date, lower(u.lname), lower(u.fname)',
);

function show_doc_total($lastdocname, $doc_encounters)
{
    if ($lastdocname) {
        echo " <tr>\n";
        echo "  <td class='detail'>" . text($lastdocname) . "</td>\n";
        echo "  <td class='detail' align='right'>" . text($doc_encounters) . "</td>\n";
        echo " </tr>\n";
    }
}

function getFieldValue($form_id, $field_id)
{
    $querylbfdxpre = sqlQuery("SELECT field_value FROM lbf_data WHERE form_id=? AND field_id=?", array($form_id, $field_id));

    if ($querylbfdxpre) {
        $field_value = $querylbfdxpre['field_value'];
        return $field_value;
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_provider = $_POST['form_provider'];
$form_operador = $_POST['form_operador'];
$form_facility = $_POST['form_facility'];
$form_pricelevel = $_POST['form_pricelevel'];
$form_apptcat = $_POST['form_apptcat'];
$form_details = $_POST['form_details'] ? true : false;
$form_new_patients = $_POST['form_new_patients'] ? true : false;
$form_esigned = $_POST['form_esigned'] ? true : false;
$form_not_esigned = $_POST['form_not_esigned'] ? true : false;
$form_encounter_esigned = $_POST['form_encounter_esigned'] ? true : false;

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
    $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

// Get the info.
//
$esign_fields = '';
$esign_joins = '';
if ($form_encounter_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = fe.encounter ";
}

if ($form_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = fe.encounter ";
}

if ($form_not_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT JOIN esign_signatures AS es on es.tid = fe.encounter ";
}

$sqlBindArray = array();

$query = "SELECT " .
    "fe.encounter, fe.date, fe.reason, fe.pc_catid, fe.responsable_id, " .
    "f.formdir, f.form_name, f.provider_id, " .
    "p.fname, p.mname, p.lname, p.lname2, p.pid, p.pubpid, p.pricelevel,  " .
    "u.lname AS ulname, u.fname AS ufname, u.mname AS umname, " .
    "ca.pc_catdesc " .
    "$esign_fields" .
    "FROM ( form_encounter AS fe, forms AS f ) " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
    "LEFT JOIN openemr_postcalendar_categories AS ca ON ca.pc_catid = fe.pc_catid " .
    "LEFT JOIN users AS u ON u.id = fe.provider_id " .
    "$esign_joins" .
    "WHERE f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' ";
if ($form_to_date) {
    $query .= "AND fe.date >= ? AND fe.date <= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
} else {
    $query .= "AND fe.date >= ? AND fe.date <= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_from_date . ' 23:59:59');
}

if ($form_provider) {
    $query .= "AND fe.provider_id = ? ";
    array_push($sqlBindArray, $form_provider);
}

if ($form_operador) {
    $query .= "AND f.provider_id = ? ";
    array_push($sqlBindArray, $form_operador);
}

if ($form_facility) {
    $query .= "AND fe.facility_id = ? ";
    array_push($sqlBindArray, $form_facility);
}

if ($form_pricelevel) {
    $query .= "AND p.pricelevel = ? ";
    array_push($sqlBindArray, $form_pricelevel);
}

if ($form_apptcat) {
    $query .= "AND fe.pc_catid = ? ";
    array_push($sqlBindArray, $form_apptcat);
}

if ($form_new_patients) {
    $query .= "AND fe.date = (SELECT MIN(fe2.date) FROM form_encounter AS fe2 WHERE fe2.pid = fe.pid) ";
}

if ($form_encounter_esigned) {
    $query .= "AND es.tid = fe.encounter AND es.table = 'form_encounter' ";
}

if ($form_esigned) {
    $query .= "AND es.tid = fe.encounter ";
}

if ($form_not_esigned) {
    $query .= "AND es.tid IS NULL ";
}

$query .= "ORDER BY $orderby";

$res = sqlStatement($query, $sqlBindArray);
?>
<html>
<head>
    <title><?php echo xlt('Encounters Report'); ?></title>

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
            if ($('#setting_new_window').val() === 'checked') {
                openNewTopWindow(newpid, enc);
            } else {
                top.restoreSession();
                if (enc > 0) {
                    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid + "&set_encounterid=" + enc;
                } else {
                    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid;
                }
            }
        }
    </script>
</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Encounters'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='encounters_report.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>

    <div id="report_parameters">
        <table>
            <tr>
                <td width='550px'>
                    <div style='float:left'>

                        <table class='text'>
                            <tr>
                                <td class='control-label'>
                                    <?php echo xlt('Facility'); ?>:
                                </td>
                                <td>
                                    <?php dropdown_facility($form_facility, 'form_facility', true); ?>
                                </td>
                                <td class='control-label'>
                                    <?php echo xlt('Provider'); ?>:
                                </td>
                                <td>
                                    <?php

                                    // Build a drop-down list of providers.
                                    //

                                    $query = "SELECT id, lname, fname FROM users WHERE " .
                                        "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

                                    $ures = sqlStatement($query);

                                    echo "   <select name='form_provider' class='form-control'>\n";
                                    echo "    <option value=''>-- " . xlt('All') . " --\n";

                                    while ($urow = sqlFetchArray($ures)) {
                                        $provid = $urow['id'];
                                        echo "    <option value='" . attr($provid) . "'";
                                        if ($provid == $_POST['form_provider']) {
                                            echo " selected";
                                        }

                                        echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                    }

                                    echo "   </select>\n";

                                    ?>
                                </td>
                                <td>Tipo de Visita:
                                </td>
                                <td>
                                    <select id="form_apptcat" name="form_apptcat" class="form-control">
                                        <?php
                                        $categories = fetchAppointmentCategories();
                                        echo "<option value=''>--" . xlt("All") . "</option>";
                                        while ($cat = sqlFetchArray($categories)) {
                                            echo "<option value='" . attr($cat['id']) . "'";
                                            if ($cat['id'] == $_POST['form_apptcat']) {
                                                echo " selected='true' ";
                                            }

                                            echo ">" . text(xl_appt_category($cat['category'])) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class='control-label'>
                                    <?php echo xlt('From'); ?>:
                                </td>
                                <td>
                                    <input type='text' class='datepicker form-control' name='form_from_date'
                                           id="form_from_date" size='10'
                                           value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
                                </td>
                                <td class='control-label'>
                                    <?php echo xlt('To'); ?>:
                                </td>
                                <td>
                                    <input type='text' class='datepicker form-control' name='form_to_date'
                                           id="form_to_date" size='10'
                                           value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
                                </td>
                                <td>Tipo de Convenio:
                                </td>
                                <td>
                                    <?php

                                    // Build a drop-down list of providers.
                                    //

                                    $queryp = "SELECT option_id, title FROM list_options WHERE " .
                                        "list_id = 'pricelevel' ORDER BY seq"; //(CHEMED) facility filter

                                    $price = sqlStatement($queryp);

                                    echo "   <select name='form_pricelevel' class='form-control'>\n";
                                    echo "    <option value=''>-- " . xlt('All') . " --\n";

                                    while ($prirow = sqlFetchArray($price)) {
                                        $price1 = $prirow['title'];
                                        echo "    <option value='" . attr($price1) . "'";
                                        if ($price1 == $_POST['form_pricelevel']) {
                                            echo " selected";
                                        }

                                        echo ">" . text($prirow['title']) . "\n";
                                    }

                                    echo "   </select>\n";

                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div class="checkbox">
                                        <label><input type='checkbox'
                                                      name='form_details'<?php echo ($form_details) ? ' checked' : ''; ?>>
                                            <?php echo xlt('Details'); ?></label>
                                    </div>
                                    <div class="checkbox">
                                        <label><input type='checkbox' name='form_new_patients'
                                                      title='<?php echo xla('First-time visits only'); ?>'<?php echo ($form_new_patients) ? ' checked' : ''; ?>>
                                            <?php echo xlt('New'); ?></label>
                                    </div>
                                </td>
                                <td></td>
                                <td>
                                    <div class="checkbox">
                                        <label><input type='checkbox'
                                                      name='form_esigned'<?php echo ($form_esigned) ? ' checked' : ''; ?>>
                                            <?php echo xlt('Forms Esigned'); ?></label>
                                    </div>
                                    <div class="checkbox">
                                        <label><input type='checkbox'
                                                      name='form_encounter_esigned'<?php echo ($form_encounter_esigned) ? ' checked' : ''; ?>>
                                            <?php echo xlt('Encounter Esigned'); ?></label>
                                    </div>
                                    <div class="checkbox">
                                        <label><input type='checkbox'
                                                      name='form_not_esigned'<?php echo ($form_not_esigned) ? ' checked' : ''; ?>>
                                            <?php echo xlt('Not Esigned'); ?></label>
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
                                           onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                            <?php echo xlt('Submit'); ?>
                                        </a>
                                        <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                                            <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                                <?php echo xlt('Print'); ?>
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

    </div> <!-- end report_parameters -->

    <?php
    if ($_POST['form_refresh'] || $_POST['form_orderby']) {
        ?>
        <div id="report_results">
            <table id='mymaintable'>
                <thead>
                <?php if ($form_details) { ?>
                    <th>
                        <a href="nojs.php" onclick="return dosort('doctor')"
                            <?php echo ($form_orderby == "doctor") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Provider'); ?> </a>
                    </th>
                    <th>
                        <a href="nojs.php" onclick="return dosort('time')"
                            <?php echo ($form_orderby == "time") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Date'); ?></a>
                    </th>
                    <th>
                        <a href="nojs.php" onclick="return dosort('patient')"
                            <?php echo ($form_orderby == "patient") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Patient'); ?></a>
                    </th>
                    <th>
                        <a href="nojs.php" onclick="return dosort('pubpid')"
                            <?php echo ($form_orderby == "pubpid") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('ID'); ?></a>
                    </th>
                    <th>
                        <?php echo xlt('Status'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Encounter'); ?>
                    </th>
                    <th>
                        <a href="nojs.php" onclick="return dosort('encounter')"
                            <?php echo ($form_orderby == "encounter") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Encounter Number'); ?></a>
                    </th>
                    <th>
                        <?php echo xlt('Form'); ?>
                    </th>
                    <th>
                        <a href="nojs.php" onclick="return dosort('operador')"
                            <?php echo ($form_orderby == "operador") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Operador'); ?></a>
                    </th>
                    <th>
                        <a href="nojs.php" onclick="return dosort('responsable')"
                            <?php echo ($form_orderby == "responsable") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Responsable'); ?></a>
                    </th>
                    <th>
                        <?php echo xlt('Coding'); ?>
                    </th>
                <?php } else { ?>
                    <th><?php echo xlt('Provider'); ?></td>
                    <th><?php echo xlt('Encounters'); ?></td>
                <?php } ?>
                </thead>
                <tbody>
                <?php
                if ($res) {
                    $lastdocname = "";
                    $doc_encounters = 0;
                    while ($row = sqlFetchArray($res)) {
                        $patient_id = $row['pid'];

                        $docname = '';
                        if (!empty($row['ulname']) || !empty($row['ufname'])) {
                            $docname = $row['ulname'];
                            if (!empty($row['ufname']) || !empty($row['umname'])) {
                                $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
                            }
                        }

                        $errmsg = "";
                        if ($form_details) {
                            // Fetch all other forms for this encounter.
                            $encnames = '';
                            $encarr = getFormByEncounter(
                                $patient_id,
                                $row['encounter'],
                                "formdir, user, form_name, form_id, provider_id"
                            );
                            if ($encarr != '') {
                                foreach ($encarr as $enc) {
                                    if ($enc['formdir'] == 'newpatient') {
                                        continue;
                                    }

                                    if ($enc['formdir'] == 'care_plan') {
                                        $encnames .= '<br><span><img src="Laser.png" height="16" width="32"></span>';
                                    }

                                    if ($encnames) {
                                        $encnames .= '<br />';
                                    }
                                    if ($enc['formdir'] == 'LBFprotocolo') {
                                        $encnames .= '<span><img src="https://cdn-icons-png.flaticon.com/512/8206/8206801.png" height="16" width="20"> </span>';
                                        $encnames .= '<b>' . text(getFieldValue($enc['form_id'], "Prot_opp")) . '</b>';
                                    } else {
                                        $encnames .= text($enc['form_name']);
                                    }

                                }
                            }

                            $encnames2 = '';
                            $encarr2 = getFormByEncounter(
                                $patient_id,
                                $row['encounter'],
                                "formdir, user, form_name, form_id, provider_id"
                            );

                            if ($encarr2 != '') {
                                foreach ($encarr2 as $enc2) {
                                    if ($enc2['formdir'] == 'newpatient') {
                                        continue;
                                    }

                                    if ($enc2['formdir'] == 'LBFprotocolo') {
                                        continue;
                                    }

                                    if ($enc2['provider_id'] == '0') {
                                        continue;
                                    }

                                    if ($encnames2) {
                                        $encnames2 .= '<br />';
                                    }

                                    $encnames2 .= text($enc2['provider_id']); // need to html escape it here for output below
                                }
                            }

                            // Fetch coding and compute billing status.
                            $coded = "";
                            $billed_count = 0;
                            $unbilled_count = 0;
                            if ($billres = BillingUtilities::getBillingByEncounter(
                                $row['pid'],
                                $row['encounter'],
                                "code_type, code, code_text, billed"
                            )) {
                                foreach ($billres as $billrow) {
                                    // $title = addslashes($billrow['code_text']);
                                    if ($billrow['code_type'] != 'COPAY' && $billrow['code_type'] != 'TAX') {
                                        $coded .= $billrow['code'] . ', ';
                                        if ($billrow['billed']) {
                                            ++$billed_count;
                                        } else {
                                            ++$unbilled_count;
                                        }
                                    }
                                }

                                $coded = substr($coded, 0, 100); // Truncar a 100 caracteres
                                $coded = rtrim($coded, ', '); // Eliminar la última coma y el espacio si es necesario
                            }

                            // Si la variable $coded está vacía, mostrar el mensaje 'Encuentro sin codificar'
                            if (empty($coded)) {
                                $coded = 'Encuentro sin codificar';
                            }

                            // Figure product sales into billing status.
                            $sres = sqlStatement("SELECT billed FROM drug_sales " .
                                "WHERE pid = ? AND encounter = ?", array($row['pid'], $row['encounter']));
                            while ($srow = sqlFetchArray($sres)) {
                                if ($srow['billed']) {
                                    ++$billed_count;
                                } else {
                                    ++$unbilled_count;
                                }
                            }

                            // Compute billing status.
                            if ($billed_count && $unbilled_count) {
                                $status = xl('Mixed');
                            } else if ($billed_count) {
                                $status = xl('Closed');
                            } else if ($unbilled_count) {
                                $status = xl('Open');
                            } else {
                                $status = xl('Empty');
                            }
                            ?>
                            <tr bgcolor='<?php echo attr($bgcolor); ?>'>
                                <td>
                                    <?php echo ($docname == $lastdocname) ? "" : text($docname) ?>&nbsp;
                                </td>
                                <td>
                                    <?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))) ?>&nbsp;
                                </td>
                                <td>
                                    <a href="#"
                                       onclick="return topatient('<?php echo attr($row['pid']); ?>','<?php echo attr($row['encounter']); ?>')">
                                        <?php echo text($row['lname'] . ' ' . $row['lname2'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?></a>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo text($row['pubpid']); ?>&nbsp;
                                </td>
                                <td>
                                    <?php echo text($row['pricelevel']); ?>&nbsp;
                                </td>
                                <td>
                                    <?php echo text($row['reason']); ?>&nbsp;
                                </td>
                                <td>
                                    <?php echo text($row['pc_catdesc']); ?>&nbsp;
                                </td>
                                <td>
                                    <?php echo $encnames; //since this variable contains html, have already html escaped it above ?>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo getProviderName($encnames2); //since this variable contains html, have already html escaped it above ?>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php
                                    $ResponsableID = $row['responsable_id'];
                                    $Responsable = sqlQuery("SELECT * FROM users " .
                                        "WHERE id = $ResponsableID");
                                    echo text($Responsable['fname'] . " " . $Responsable['lname']); //since this variable contains html, have already html escaped it above ?>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo $coded; ?>
                                </td>
                            </tr>
                            <?php
                        } else {
                            if ($docname != $lastdocname) {
                                show_doc_total($lastdocname, $doc_encounters);
                                $doc_encounters = 0;
                            }

                            ++$doc_encounters;
                        }

                        $lastdocname = $docname;
                    }

                    if (!$form_details) {
                        show_doc_total($lastdocname, $doc_encounters);
                    }
                }
                ?>
                </tbody>
            </table>
        </div>  <!-- end encresults -->
    <?php } else { ?>
        <div class='text'>
            <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
        </div>
    <?php } ?>

    <input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>"/>
    <input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script language='JavaScript'>
    <?php if ($alertmsg) {
        echo " alert(" . js_escape($alertmsg) . ");\n";
    } ?>
</script>
</html>
