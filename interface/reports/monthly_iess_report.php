<?php
/**
 * Monthly patient IESS report.
 *
 * Lists all encounters per patient for a selected month with filters by provider and price level.
 * Provides a button to generate the IESS PDF bundle through custom_report.php (pdf=3 / PDF_CONTRA).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenAI Assistant
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// CSRF check for all POST actions (filters or PDF generation)
if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Defaults: current month range
$defaultFrom = date('Y-m-01');
$defaultTo = date('Y-m-t');

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : $defaultFrom;
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : $defaultTo;
$form_provider = isset($_POST['form_provider']) ? trim($_POST['form_provider']) : '';
$form_pricelevel = isset($_POST['form_pricelevel']) ? trim($_POST['form_pricelevel']) : '';

$alertmsg = '';

/**
 * Collect forms for a patient in the given range/filters.
 *
 * @param int $pid
 * @param string $fromDate
 * @param string $toDate
 * @param string $providerId
 * @param string $pricelevel
 * @return array
 */
function getFormsForPatient($pid, $fromDate, $toDate, $providerId, $pricelevel, $lookbackDays = 45)
{
    $fromExtended = date('Y-m-d', strtotime($fromDate . " -{$lookbackDays} days"));

    $bind = array($pid, $fromExtended . ' 00:00:00', $toDate . ' 23:59:59');

    $sql = "SELECT f.form_id, f.formdir, f.encounter, fe.date
            FROM forms AS f
            INNER JOIN form_encounter AS fe ON fe.pid = f.pid AND fe.encounter = f.encounter
            INNER JOIN patient_data AS p ON p.pid = f.pid
            WHERE f.deleted = 0 AND f.pid = ? AND fe.date BETWEEN ? AND ?";

    if ($providerId) {
        $sql .= " AND fe.provider_id = ?";
        $bind[] = $providerId;
    }

    if ($pricelevel) {
        $sql .= " AND p.pricelevel = ?";
        $bind[] = $pricelevel;
    }

    // IMPORTANTÍSIMO: traer SOLO los formularios relevantes (para no imprimir “todo”)
    $sql .= " AND (
                f.formdir IN ('newpatient','treatment_plan','eye_mag')
                OR f.formdir LIKE 'LBF%'
             )";

    $sql .= " ORDER BY fe.date ASC";

    return sqlStatement($sql, $bind);
}

// Fetch encounters grouped by patient with applied filters
$bind = array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
$encounterSql = "SELECT fe.pid, fe.encounter, fe.date, fe.reason, fe.provider_id,
                        p.fname, p.mname, p.lname, p.lname2, p.pubpid, p.pricelevel,
                        u.fname AS ufname, u.lname AS ulname
                 FROM form_encounter AS fe
                 INNER JOIN patient_data AS p ON p.pid = fe.pid
                 LEFT JOIN users AS u ON u.id = fe.provider_id
                 WHERE fe.date BETWEEN ? AND ?";

if ($form_provider) {
    $encounterSql .= " AND fe.provider_id = ?";
    $bind[] = $form_provider;
}

if ($form_pricelevel) {
    $encounterSql .= " AND p.pricelevel = ?";
    $bind[] = $form_pricelevel;
}

$encounterSql .= " ORDER BY p.lname, p.fname, fe.date";

$patients = array();
$encRes = sqlStatement($encounterSql, $bind);
while ($row = sqlFetchArray($encRes)) {
    $pid = $row['pid'];
    if (!isset($patients[$pid])) {
        $patients[$pid] = array(
            'pid' => $pid,
            'name' => trim($row['lname'] . ' ' . $row['lname2'] . ' ' . $row['fname'] . ' ' . $row['mname']),
            'pubpid' => $row['pubpid'],
            'pricelevel' => $row['pricelevel'],
            'encounters' => array(),
        );
    }

    $patients[$pid]['encounters'][] = array(
        'date' => $row['date'],
        'reason' => $row['reason'],
        'provider' => trim($row['ulname'] . ' ' . $row['ufname']),
        'encounter' => $row['encounter'],
    );
}
?>
<html>
<head>
    <title><?php echo xlt('Reporte mensual IESS'); ?></title>
    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
    <style>
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
        }

        .encounter-list div {
            margin-bottom: 4px;
        }
    </style>
    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });
    </script>
</head>
<body class="body_top">
<div id="report_parameters">
    <span class='title'><?php echo xlt('Reporte mensual IESS'); ?></span>
    <form method='post' id='theform' action='monthly_iess_report.php' onsubmit='return top.restoreSession()'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
        <table class='text'>
            <tr>
                <td class='control-label'><?php echo xlt('Desde'); ?>:</td>
                <td>
                    <input type='text' class='datepicker form-control' name='form_from_date' size='10'
                           value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
                </td>
                <td class='control-label'><?php echo xlt('Hasta'); ?>:</td>
                <td>
                    <input type='text' class='datepicker form-control' name='form_to_date' size='10'
                           value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
                </td>
                <td class='control-label'><?php echo xlt('Proveedor'); ?>:</td>
                <td>
                    <?php
                    $providerQuery = "SELECT id, lname, fname FROM users WHERE authorized = 1 ORDER BY lname, fname";
                    $providerRes = sqlStatement($providerQuery);
                    ?>
                    <select name='form_provider' class='form-control'>
                        <option value=''>-- <?php echo xlt('All'); ?> --</option>
                        <?php while ($urow = sqlFetchArray($providerRes)) { ?>
                            <option
                                value='<?php echo attr($urow['id']); ?>'<?php echo ($form_provider == $urow['id']) ? ' selected' : ''; ?>>
                                <?php echo text($urow['lname'] . ', ' . $urow['fname']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td class='control-label'><?php echo xlt('Convenio'); ?>:</td>
                <td>
                    <?php
                    $priceRes = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'pricelevel' ORDER BY seq");
                    ?>
                    <select name='form_pricelevel' class='form-control'>
                        <option value=''>-- <?php echo xlt('All'); ?> --</option>
                        <?php while ($prow = sqlFetchArray($priceRes)) { ?>
                            <option
                                value='<?php echo attr($prow['title']); ?>'<?php echo ($form_pricelevel == $prow['title']) ? ' selected' : ''; ?>>
                                <?php echo text($prow['title']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <a href='#' class='btn btn-default btn-save'
                       onclick='$("#theform").submit();'><?php echo xlt('Submit'); ?></a>
                </td>
            </tr>
        </table>

        <div id="report_results" style="margin-top: 15px;">
            <?php if (!empty($patients)) { ?>
                <table class='table table-bordered' id='mymaintable'>
                    <thead>
                    <tr>
                        <th><?php echo xlt('Paciente'); ?></th>
                        <th><?php echo xlt('ID'); ?></th>
                        <th><?php echo xlt('Convenio'); ?></th>
                        <th><?php echo xlt('Total atenciones'); ?></th>
                        <th><?php echo xlt('Detalle del mes'); ?></th>
                        <th><?php echo xlt('PDF IESS'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($patients as $patient) { ?>
                        <tr>
                            <td><?php echo text($patient['name']); ?></td>
                            <td><?php echo text($patient['pubpid']); ?></td>
                            <td><?php echo text($patient['pricelevel']); ?></td>
                            <td><?php echo text(count($patient['encounters'])); ?></td>
                            <td class="encounter-list">
                                <?php foreach ($patient['encounters'] as $enc) { ?>
                                    <div>
                                        <strong><?php echo text(oeFormatShortDate(substr($enc['date'], 0, 10))); ?></strong>
                                        <?php if (!empty($enc['provider'])) { ?>
                                            - <?php echo text($enc['provider']); ?>
                                        <?php } ?>
                                        <?php if (!empty($enc['reason'])) { ?>
                                            : <?php echo text($enc['reason']); ?>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </td>
                            <td>
                                <?php
                                $formsRes = getFormsForPatient($patient['pid'], $form_from_date, $form_to_date, $form_provider, $form_pricelevel);
                                $hasForms = false;
                                ?>
                                <form method="post"
                                      action="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/report/custom_report.php"
                                      target="_blank" onsubmit="top.restoreSession();">
                                    <input type="hidden" name="pid" value="<?php echo attr($patient['pid']); ?>">
                                    <input type="hidden" name="pdf" value="3">
                                    <input type="hidden" name="printable" value="1">
                                    <!-- Mimic patient_report defaults so contra PDF includes básicos -->
                                    <input type="hidden" name="include_demographics" value="demographics">
                                    <input type="hidden" name="include_billing" value="billing">
                                    <?php while ($formRow = sqlFetchArray($formsRes)) {
                                        $hasForms = true; ?>
                                        <input type="hidden"
                                               name="<?= attr($formRow['formdir'] . '_' . $formRow['form_id']); ?>"
                                               value="<?= attr($formRow['encounter']); ?>">
                                    <?php } ?>
                                    <button type="submit"
                                            class="btn btn-primary" <?php echo $hasForms ? '' : 'disabled'; ?>>
                                        <?php echo xlt('Generar PDF'); ?>
                                    </button>
                                </form>
                                <?php if (!$hasForms) { ?>
                                    <div class="text-muted"
                                         style="font-size: 12px;"><?php echo xlt('Sin formularios en el rango.'); ?></div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class='text'><?php echo xlt('No se encontraron atenciones en el rango seleccionado.'); ?></div>
            <?php } ?>
        </div>

        <input type='hidden' name='form_refresh' id='form_refresh' value='true'/>
    </form>
</div>

<script>
    <?php if ($alertmsg) {
        echo "alert(" . js_escape($alertmsg) . ");";
    } ?>
</script>
</body>
</html>
