<?php
/**
 * Surgery costing report (case / surgeon / day).
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Core\Header;

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-01');
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_refresh = !empty($_POST['form_refresh']);

?>
<html>
<head>
    <title><?php echo xlt('Surgery Costing Report'); ?></title>
    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
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
<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Surgery Costing'); ?></span>
<form method="post" action="surgery_costing_report.php" onsubmit="return top.restoreSession()">
    <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
    <table>
        <tr>
            <td><?php echo xlt('From'); ?>:</td>
            <td><input type='text' class='datepicker form-control' name='form_from_date' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'></td>
            <td><?php echo xlt('To'); ?>:</td>
            <td><input type='text' class='datepicker form-control' name='form_to_date' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'></td>
            <td>
                <a href="#" class="btn btn-default btn-save" onclick="$('#form_refresh').val('1'); this.closest('form').submit();">
                    <?php echo xlt('Submit'); ?>
                </a>
            </td>
        </tr>
    </table>

<?php if ($form_refresh) {
    $sql = "SELECT
                sd.surgery_date,
                sc.id AS case_id,
                sc.procedure_name,
                sc.specialty,
                CONCAT(COALESCE(u.fname,''),' ',COALESCE(u.lname,'')) AS surgeon_name,
                CONCAT(COALESCE(p.fname,''),' ',COALESCE(p.lname,'')) AS patient_name,
                COALESCE(direct.direct_cost, 0) AS direct_cost,
                COALESCE(shared.shared_cost, 0) AS shared_cost,
                (COALESCE(direct.direct_cost, 0) + COALESCE(shared.shared_cost, 0)) AS total_cost
            FROM surgery_case sc
            JOIN surgery_day sd ON sd.id = sc.surgery_day_id
            LEFT JOIN users u ON u.id = sc.surgeon_id
            LEFT JOIN patient_data p ON p.pid = sc.pid
            LEFT JOIN (
                SELECT case_id, SUM(total_cost) AS direct_cost
                FROM inventory_issue
                WHERE usage_type = 'direct_case'
                GROUP BY case_id
            ) direct ON direct.case_id = sc.id
            LEFT JOIN (
                SELECT ca.case_id, SUM(ca.allocated_cost) AS shared_cost
                FROM cost_allocation ca
                GROUP BY ca.case_id
            ) shared ON shared.case_id = sc.id
            WHERE sd.surgery_date >= ? AND sd.surgery_date <= ?
              AND sc.status = 'done'
            ORDER BY sd.surgery_date DESC, surgeon_name, sc.id DESC";

    $res = sqlStatement($sql, array($form_from_date, $form_to_date));
    ?>
    <table class="table table-bordered table-striped" style="margin-top:10px;">
        <thead>
        <tr>
            <th><?php echo xlt('Date'); ?></th>
            <th><?php echo xlt('Case'); ?></th>
            <th><?php echo xlt('Patient'); ?></th>
            <th><?php echo xlt('Surgeon'); ?></th>
            <th><?php echo xlt('Procedure'); ?></th>
            <th><?php echo xlt('Specialty'); ?></th>
            <th><?php echo xlt('Direct Cost'); ?></th>
            <th><?php echo xlt('Shared Cost'); ?></th>
            <th><?php echo xlt('Total Cost'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = sqlFetchArray($res)) { ?>
            <tr>
                <td><?php echo text(oeFormatShortDate($row['surgery_date'])); ?></td>
                <td><?php echo text($row['case_id']); ?></td>
                <td><?php echo text($row['patient_name']); ?></td>
                <td><?php echo text($row['surgeon_name']); ?></td>
                <td><?php echo text($row['procedure_name']); ?></td>
                <td><?php echo text($row['specialty']); ?></td>
                <td><?php echo text(number_format((float)$row['direct_cost'], 2)); ?></td>
                <td><?php echo text(number_format((float)$row['shared_cost'], 2)); ?></td>
                <td><strong><?php echo text(number_format((float)$row['total_cost'], 2)); ?></strong></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
</form>
</body>
</html>
