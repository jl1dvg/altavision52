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
function getFormsForPatient($pid, $fromDate, $toDate, $providerId, $pricelevel)
{
    $bind = array($pid, $fromDate . ' 00:00:00', $toDate . ' 23:59:59');
    $sql = "SELECT f.form_id, f.formdir, f.encounter
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

    // Orden igual que patient_report: por encounter descendente, fecha de encounter descendente,
    // y fecha del form ascendente (f.date puede venir como dd-mm-YYYY).
    $sql .= " ORDER BY fe.encounter DESC, fe.date DESC, STR_TO_DATE(f.date, '%d-%m-%Y') ASC";

    return sqlStatement($sql, $bind);
}

/**
 * Get derivation (LBTref) active for a given date.
 *
 * @param int $pid
 * @param string $encDate
 * @return array|null
 */
function getDerivationForDate($pid, $encDate)
{
    $sql = "SELECT t.id,
                   MAX(CASE WHEN d.field_id = 'refer_id' THEN d.field_value END) AS refer_id,
                   MAX(CASE WHEN d.field_id = 'refer_date' THEN d.field_value END) AS refer_date,
                   MAX(CASE WHEN d.field_id = 'refer_end_date' THEN d.field_value END) AS refer_end_date,
                   MAX(CASE WHEN d.field_id = 'refer_to' THEN d.field_value END) AS refer_to,
                   MAX(CASE WHEN d.field_id = 'refer_from' THEN d.field_value END) AS refer_from,
                   MAX(CASE WHEN d.field_id = 'refer_diag' THEN d.field_value END) AS refer_diag,
                   MAX(CASE WHEN d.field_id = 'refer_related_code' THEN d.field_value END) AS refer_related_code,
                   MAX(CASE WHEN d.field_id = 'body' THEN d.field_value END) AS body
            FROM transactions AS t
            LEFT JOIN lbt_data AS d ON d.form_id = t.id
            WHERE t.title = 'LBTref' AND t.pid = ?
            GROUP BY t.id
            HAVING (refer_date IS NULL OR refer_date <= ?) AND (refer_end_date IS NULL OR refer_end_date >= ?)
            ORDER BY refer_date DESC
            LIMIT 1";

    $row = sqlQuery($sql, array($pid, $encDate, $encDate));
    if (!empty($row['id'])) {
        return $row;
    }

    return null;
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

        .encounter-table th, .encounter-table td {
            font-size: 12px;
        }

        .out-of-range {
            color: #b30000;
            font-weight: bold;
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
                                <table class="table table-condensed encounter-table">
                                    <thead>
                                    <tr>
                                        <th><?php echo xlt('Fecha/Detalle'); ?></th>
                                        <th><?php echo xlt('Derivación'); ?></th>
                                        <th><?php echo xlt('Vigencia'); ?></th>
                                        <th><?php echo xlt('Diag'); ?></th>
                                        <th><?php echo xlt('Relacionado'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($patient['encounters'] as $enc) {
                                        $encDate = substr($enc['date'], 0, 10);
                                        $deriv = getDerivationForDate($patient['pid'], $encDate);
                                        $hasDerivation = ($deriv && !empty($deriv['refer_id']));
                                        $vigencia = '';
                                        if ($hasDerivation) {
                                            $vigencia = trim($deriv['refer_date'] . ' - ' . $deriv['refer_end_date']);
                                        }
                                        $rowClass = $hasDerivation ? '' : 'out-of-range';
                                        ?>
                                        <tr class="<?php echo attr($rowClass); ?>">
                                            <td>
                                                <strong><?php echo text(oeFormatShortDate($encDate)); ?></strong>
                                                <?php if (!empty($enc['provider'])) { ?>
                                                    - <?php echo text($enc['provider']); ?>
                                                <?php } ?>
                                                <?php if (!empty($enc['reason'])) { ?>
                                                    : <?php echo text($enc['reason']); ?>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($hasDerivation) {
                                                    echo text($deriv['refer_id']);
                                                } else {
                                                    echo xlt('Sin derivación vigente');
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo $vigencia ? text($vigencia) : '&mdash;'; ?>
                                            </td>
                                            <td>
                                                <?php echo $hasDerivation && $deriv['refer_diag'] ? text($deriv['refer_diag']) : '&mdash;'; ?>
                                            </td>
                                            <td>
                                                <?php echo $hasDerivation && $deriv['refer_related_code'] ? text($deriv['refer_related_code']) : '&mdash;'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
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
