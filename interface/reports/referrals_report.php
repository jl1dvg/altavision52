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

$form_refresh = !empty($_POST['form_refresh']);
$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = isset($_POST['form_facility']) ? trim((string)$_POST['form_facility']) : '';
$form_validity = isset($_POST['form_validity']) ? trim((string)$_POST['form_validity']) : '';
$form_appt_state = isset($_POST['form_appt_state']) ? trim((string)$_POST['form_appt_state']) : '';
$form_assigned_provider = isset($_POST['form_assigned_provider']) ? trim((string)$_POST['form_assigned_provider']) : '';

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

$allowedAppointmentFilters = array('', 'assigned', 'pending');
if (!in_array($form_appt_state, $allowedAppointmentFilters, true)) {
    $form_appt_state = '';
}

if ($form_assigned_provider !== '' && !ctype_digit($form_assigned_provider)) {
    $form_assigned_provider = '';
}

$providerOptions = array();
$providerRes = sqlStatement("SELECT id, fname, lname FROM users WHERE active = 1 ORDER BY lname, fname");
while ($prow = sqlFetchArray($providerRes)) {
    $providerOptions[] = $prow;
}
?>
<html>
<head>
    <title><?php echo xlt('Referrals'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script language="JavaScript">
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

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

        // The OnClick handler for referral display.
        function show_referral(transid) {
            dlgopen('../patient_file/transaction/print_referral.php?transid=' + encodeURIComponent(transid),
                '_blank', 550, 400, true); // Force new window rather than iframe because of the dynamic generation of the content in print_referral.php
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
    <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Referrals'); ?></span>

    <div id="report_parameters_daterange">
        <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
    </div>

    <form name='theform' id='theform' method='post' action='referrals_report.php'
          onsubmit='return top.restoreSession()'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>

        <div id="report_parameters">
            <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
            <table>
                <tr>
                    <td width='640px'>
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
                                        <?php echo xlt('From'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_from_date' id="form_from_date" size='10'
                                               value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'
                                               class='datepicker form-control'>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('To'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_to_date' id="form_to_date" size='10'
                                               value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'
                                               class='datepicker form-control'>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='control-label'>
                                        <?php echo xlt('Validity'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_validity' id='form_validity' class='form-control'>
                                            <option value=''>-- <?php echo xlt('All'); ?> --</option>
                                            <option value='vigente'<?php echo ($form_validity === 'vigente') ? ' selected' : ''; ?>><?php echo xlt('Active (Any)'); ?></option>
                                            <option value='active'<?php echo ($form_validity === 'active') ? ' selected' : ''; ?>><?php echo xlt('Active (With End Date)'); ?></option>
                                            <option value='no_end'<?php echo ($form_validity === 'no_end') ? ' selected' : ''; ?>><?php echo xlt('No End Date'); ?></option>
                                            <option value='expired'<?php echo ($form_validity === 'expired') ? ' selected' : ''; ?>><?php echo xlt('Expired'); ?></option>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Appointment'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_appt_state' id='form_appt_state' class='form-control'>
                                            <option value=''>-- <?php echo xlt('All'); ?> --</option>
                                            <option value='assigned'<?php echo ($form_appt_state === 'assigned') ? ' selected' : ''; ?>><?php echo xlt('Assigned'); ?></option>
                                            <option value='pending'<?php echo ($form_appt_state === 'pending') ? ' selected' : ''; ?>><?php echo xlt('Pending'); ?></option>
                                        </select>
                                    </td>
                                    <td class='control-label'>
                                        <?php echo xlt('Assigned Provider'); ?>:
                                    </td>
                                    <td>
                                        <select name='form_assigned_provider' id='form_assigned_provider' class='form-control'>
                                            <option value=''>-- <?php echo xlt('All'); ?> --</option>
                                            <?php foreach ($providerOptions as $providerOption) { ?>
                                                <option value='<?php echo attr($providerOption['id']); ?>'<?php echo ($form_assigned_provider === (string)$providerOption['id']) ? ' selected' : ''; ?>>
                                                    <?php echo text(trim(($providerOption['lname'] ?? '') . ', ' . ($providerOption['fname'] ?? ''))); ?>
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
                                                <?php echo xlt('Submit'); ?>
                                            </a>
                                            <?php if ($form_refresh) { ?>
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
        </div> <!-- end of parameters -->

        <?php
        if ($form_refresh) {
            $today = date('Y-m-d');
            $totalReferrals = 0;
            $vigenteCount = 0;
            $caducadaCount = 0;
            $openEndCount = 0;
            $assignedCount = 0;
            $pendingCount = 0;

            $query = "SELECT t.id, t.pid, t.date AS created_date, " .
                "d1.field_value AS refer_date, " .
                "d2.field_value AS refer_end_date, " .
                "d3.field_value AS reply_date, " .
                "d4.field_value AS body, " .
                "d5.field_value AS refer_id, " .
                "ut.organization, uf.facility_id, p.pubpid, " .
                "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
                "CONCAT(ut.fname,' ', ut.lname) AS referer_to, " .
                "CONCAT(p.fname,' ', p.lname) AS patient_name " .
                "FROM transactions AS t " .
                "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
                "JOIN      lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
                "LEFT JOIN lbt_data AS d2 ON d2.form_id = t.id AND d2.field_id = 'refer_end_date' " .
                "LEFT JOIN lbt_data AS d3 ON d3.form_id = t.id AND d3.field_id = 'reply_date' " .
                "LEFT JOIN lbt_data AS d4 ON d4.form_id = t.id AND d4.field_id = 'body' " .
                "LEFT JOIN lbt_data AS d5 ON d5.form_id = t.id AND d5.field_id = 'refer_id' " .
                "LEFT JOIN lbt_data AS d7 ON d7.form_id = t.id AND d7.field_id = 'refer_to' " .
                "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
                "LEFT JOIN users AS ut ON ut.id = d7.field_value " .
                "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
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

            $query .= "ORDER BY ut.organization, d1.field_value, t.id";
            $res = sqlStatement($query, $queryParams);
            ?>

            <?php
            $allRows = array();
            $pidSet = array();
            $minReferDate = '';
            while ($row = sqlFetchArray($res)) {
                $referToDisplay = !empty($row['organization']) ? $row['organization'] : $row['referer_to'];

                $isExpired = !empty($row['refer_end_date']) && $row['refer_end_date'] < $today;
                if ($isExpired) {
                    $validityKey = 'expired';
                    $validityClass = 'pill-expired';
                    $validityLabel = xlt('Expired');
                } elseif (empty($row['refer_end_date'])) {
                    $validityKey = 'no_end';
                    $validityClass = 'pill-neutral';
                    $validityLabel = xlt('No End Date');
                } else {
                    $validityKey = 'active';
                    $validityClass = 'pill-active';
                    $validityLabel = xlt('Active');
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
                $allRows[] = $row;

                if (!empty($row['pid'])) {
                    $pidSet[(string)$row['pid']] = (string)$row['pid'];
                }

                if (!empty($row['refer_date']) && ($minReferDate === '' || $row['refer_date'] < $minReferDate)) {
                    $minReferDate = $row['refer_date'];
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

            foreach ($allRows as &$row) {
                $pidKey = (string)($row['pid'] ?? '');
                if ($pidKey === '' || empty($appointmentsByPid[$pidKey])) {
                    continue;
                }

                $referDate = (string)($row['refer_date'] ?? '');
                $referEndDate = trim((string)($row['refer_end_date'] ?? ''));
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
                    break;
                }
            }
            unset($row);

            foreach ($allRows as &$row) {
                $hasAppointment = !empty($row['next_appt_date']);
                if ($hasAppointment) {
                    $apptKey = 'assigned';
                    $apptClass = 'pill-assigned';
                    $apptLabel = xlt('Assigned');
                } else {
                    $apptKey = 'pending';
                    $apptClass = 'pill-pending';
                    $apptLabel = xlt('Pending');
                }

                $nextAppointment = xlt('No appointment');
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

                $rowProviderId = trim((string)($row['next_appt_provider_id'] ?? ''));
                if ($form_assigned_provider !== '' && $rowProviderId !== $form_assigned_provider) {
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

                if ($row['appt_key'] === 'assigned') {
                    $assignedCount++;
                } else {
                    $pendingCount++;
                }

                if ($row['appt_key'] === 'assigned') {
                    $providerName = trim((string)($row['next_appt_provider'] ?? ''));
                    if ($providerName === '') {
                        $providerName = xlt('Unassigned');
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
                <div class="text-muted" style="margin-bottom:8px; font-size:12px;">
                    <?php echo xlt('Appointment status considers appointments from referral date and within referral validity range.'); ?>
                </div>
                <div class="referral-summary">
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Total Referrals'); ?></span>
                        <span class="summary-value"><?php echo text($totalReferrals); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Active'); ?></span>
                        <span class="summary-value"><?php echo text($vigenteCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Expired'); ?></span>
                        <span class="summary-value"><?php echo text($caducadaCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('No End Date'); ?></span>
                        <span class="summary-value"><?php echo text($openEndCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Assigned'); ?></span>
                        <span class="summary-value"><?php echo text($assignedCount); ?></span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label"><?php echo xlt('Pending'); ?></span>
                        <span class="summary-value"><?php echo text($pendingCount); ?></span>
                    </div>
                </div>
                <div class="provider-stats-wrap">
                    <div class="stats-title"><?php echo xlt('Assigned Referrals By Provider'); ?></div>
                    <?php if (empty($providerStatsRows)) { ?>
                        <div class="text-muted" style="font-size:12px;"><?php echo xlt('No assigned referrals in current filters.'); ?></div>
                    <?php } else { ?>
                        <table class='table table-bordered table-striped'>
                            <thead>
                            <tr>
                                <th><?php echo xlt('Provider'); ?></th>
                                <th><?php echo xlt('Assigned Referrals'); ?></th>
                                <th><?php echo xlt('Unique Patients'); ?></th>
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
                <table width='98%' id='mymaintable' class='table table-bordered table-striped'>
                    <thead>
                    <tr>
                        <th><?php echo xlt('Refer To'); ?></th>
                        <th><?php echo xlt('Refer Date'); ?></th>
                        <th><?php echo xlt('System Entry Date'); ?></th>
                        <th><?php echo xlt('Reply Date'); ?></th>
                        <th><?php echo xlt('Patient'); ?></th>
                        <th><?php echo xlt('ID'); ?></th>
                        <th><?php echo xlt('Reason'); ?></th>
                        <th><?php echo xlt('Referral Code'); ?></th>
                        <th><?php echo xlt('Valid Until'); ?></th>
                        <th><?php echo xlt('Validity'); ?></th>
                        <th><?php echo xlt('Appointment'); ?></th>
                        <th><?php echo xlt('Assigned Provider'); ?></th>
                        <th><?php echo xlt('Next Appointment'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)) { ?>
                        <tr>
                            <td colspan="13"
                                class="text-center text-muted"><?php echo xlt('No referrals found for this criteria.'); ?></td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($rows as $row) { ?>
                            <tr>
                                <td><?php echo text($row['refer_to_display']); ?></td>
                                <td>
                                    <a href='#' onclick="return show_referral(<?php echo js_escape($row['id']); ?>)">
                                        <?php echo text(oeFormatShortDate($row['refer_date'])); ?>&nbsp;
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $createdDate = empty($row['created_date']) ? '' : substr($row['created_date'], 0, 10);
                                    echo text($createdDate ? oeFormatShortDate($createdDate) : '');
                                    ?>
                                </td>
                                <td><?php echo text(oeFormatShortDate($row['reply_date'])); ?></td>
                                <td><?php echo text($row['patient_name']); ?></td>
                                <td><?php echo text($row['pubpid']); ?></td>
                                <td><?php echo text($row['body']); ?></td>
                                <td><?php echo text($row['refer_id']); ?></td>
                                <td>
                                    <?php
                                    if (!empty($row['refer_end_date'])) {
                                        echo text(oeFormatShortDate($row['refer_end_date']));
                                    } else {
                                        echo '<span class="status-pill pill-neutral">' . xlt('No End Date') . '</span>';
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
                                    $apptProvider = trim((string)($row['next_appt_provider'] ?? ''));
                                    echo text($apptProvider !== '' ? $apptProvider : xlt('Unassigned'));
                                    ?>
                                </td>
                                <td><?php echo text($row['next_appointment']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div> <!-- end of results -->
        <?php } else { ?>
            <div class='text'>
                <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
            </div>
        <?php } ?>
    </form>
</div>

</body>
</html>
