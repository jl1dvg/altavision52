<?php
/**
 * Altavision ophthalmology KPI report.
 */

require_once("../globals.php");
require_once("$srcdir/altavision_kpi_indicators.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-01');
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$selected_segment = isset($_POST['form_segment']) ? $_POST['form_segment'] : 'todos';
if (!array_key_exists($selected_segment, altavision_kpi_segments())) {
    $selected_segment = 'todos';
}

$metrics = altavision_kpi_fetch_all_metrics($form_from_date, $form_to_date);
$rows = altavision_kpi_build_indicator_rows($metrics);
$summary = altavision_kpi_build_summary($rows);
$period = array('from' => $form_from_date, 'to' => $form_to_date);

if (!empty($_POST['form_export_xlsx'])) {
    $spreadsheet = altavision_kpi_build_workbook($rows, $summary, $period);

    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename=indicadores_360_oftalmologicos_' . $form_from_date . '_' . $form_to_date . '.xlsx');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function altavision_kpi_display_value($row, $segment)
{
    if ($segment === 'iess') {
        return $row['iess_value'];
    }

    if ($segment === 'resto') {
        return $row['resto_value'];
    }

    return $row['total_value'];
}

function altavision_kpi_status_class($status)
{
    if ($status === 'automatic') {
        return 'kpi-status-ok';
    }

    if ($status === 'not_applicable') {
        return 'kpi-status-muted';
    }

    return 'kpi-status-gap';
}
?>
<html>
<head>
    <title><?php echo xlt('Indicadores Oftalmologicos'); ?></title>
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

        function submitKpiReport(exportXlsx) {
            document.getElementById('form_export_xlsx').value = exportXlsx ? '1' : '';
            document.forms['theform'].submit();
            return false;
        }
    </script>
    <style>
        .kpi-toolbar {
            margin: 12px 0 14px;
            padding: 12px;
            border: 1px solid #d7dce2;
            background: #f8f9fa;
        }
        .kpi-toolbar table td {
            padding: 4px 8px 4px 0;
            vertical-align: middle;
        }
        .kpi-card-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }
        .kpi-card {
            border: 1px solid #d7dce2;
            background: #fff;
            padding: 12px;
            min-height: 92px;
        }
        .kpi-card-label {
            color: #5d6775;
            font-size: 12px;
            text-transform: uppercase;
        }
        .kpi-card-value {
            font-size: 24px;
            font-weight: 700;
            margin-top: 6px;
        }
        .kpi-card-note {
            color: #5d6775;
            font-size: 12px;
            margin-top: 4px;
        }
        .kpi-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 14px;
        }
        .kpi-panel {
            border: 1px solid #d7dce2;
            background: #fff;
            padding: 12px;
        }
        .kpi-panel h3 {
            margin-top: 0;
            font-size: 16px;
        }
        .kpi-status {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }
        .kpi-status-ok {
            background: #dff0d8;
            color: #2b542c;
        }
        .kpi-status-gap {
            background: #fcf8e3;
            color: #66512c;
        }
        .kpi-status-muted {
            background: #eeeeee;
            color: #555555;
        }
        .kpi-table th,
        .kpi-table td {
            vertical-align: top !important;
        }
        .kpi-table .kpi-long {
            min-width: 220px;
        }
        .kpi-actions ol {
            padding-left: 18px;
            margin-bottom: 0;
        }
        @media (max-width: 1000px) {
            .kpi-card-grid,
            .kpi-layout {
                display: block;
            }
            .kpi-card,
            .kpi-panel {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body class="body_top">
<span class="title"><?php echo xlt('Report'); ?> - <?php echo xlt('Indicadores Oftalmologicos'); ?></span>

<form method="post" name="theform" id="theform" action="ophthalmology_kpi_report.php" onsubmit="return top.restoreSession()">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type="hidden" name="form_export_xlsx" id="form_export_xlsx" value="" />

    <div class="kpi-toolbar" id="report_parameters">
        <table>
            <tr>
                <td class="control-label"><?php echo xlt('From'); ?>:</td>
                <td><input type="text" class="datepicker form-control" name="form_from_date" value="<?php echo attr(oeFormatShortDate($form_from_date)); ?>"></td>
                <td class="control-label"><?php echo xlt('To'); ?>:</td>
                <td><input type="text" class="datepicker form-control" name="form_to_date" value="<?php echo attr(oeFormatShortDate($form_to_date)); ?>"></td>
                <td class="control-label"><?php echo xlt('Segment'); ?>:</td>
                <td>
                    <select name="form_segment" class="form-control">
                        <?php foreach (altavision_kpi_segments() as $segmentKey => $segmentLabel) { ?>
                            <option value="<?php echo attr($segmentKey); ?>"<?php echo $selected_segment === $segmentKey ? ' selected' : ''; ?>>
                                <?php echo text($segmentLabel); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <a href="#" class="btn btn-default btn-save" onclick="return submitKpiReport(false);"><?php echo xlt('Submit'); ?></a>
                    <a href="#" class="btn btn-primary" onclick="return submitKpiReport(true);"><?php echo xlt('Export Excel'); ?></a>
                </td>
            </tr>
        </table>
    </div>

    <div class="kpi-card-grid">
        <div class="kpi-card">
            <div class="kpi-card-label"><?php echo xlt('Espera consulta'); ?></div>
            <div class="kpi-card-value"><?php echo text(altavision_kpi_display_value($rows[1], $selected_segment)); ?></div>
            <div class="kpi-card-note"><?php echo text(altavision_kpi_segments()[$selected_segment]); ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-label"><?php echo xlt('Cirugias suspendidas'); ?></div>
            <div class="kpi-card-value"><?php echo text(altavision_kpi_display_value($rows[6], $selected_segment)); ?></div>
            <div class="kpi-card-note"><?php echo text(altavision_kpi_segments()[$selected_segment]); ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-label"><?php echo xlt('Requieren data'); ?></div>
            <div class="kpi-card-value"><?php echo text($summary['gap']); ?></div>
            <div class="kpi-card-note"><?php echo xlt('Sin valores inventados'); ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-label"><?php echo xlt('No aplican'); ?></div>
            <div class="kpi-card-value"><?php echo text($summary['not_applicable']); ?></div>
            <div class="kpi-card-note"><?php echo xlt('Modelo ambulatorio'); ?></div>
        </div>
    </div>

    <div class="kpi-layout">
        <div class="kpi-panel">
            <h3><?php echo xlt('Matriz de 17 indicadores'); ?></h3>
            <table class="table table-bordered table-striped kpi-table">
                <thead>
                <tr>
                    <th><?php echo xlt('N'); ?></th>
                    <th class="kpi-long"><?php echo xlt('Indicador'); ?></th>
                    <th><?php echo xlt('Estado'); ?></th>
                    <th><?php echo xlt('IESS'); ?></th>
                    <th><?php echo xlt('Resto'); ?></th>
                    <th><?php echo xlt('Todos'); ?></th>
                    <th class="kpi-long"><?php echo xlt('Fuente / limitacion'); ?></th>
                    <th class="kpi-long"><?php echo xlt('Siguiente accion'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td><?php echo text($row['id']); ?></td>
                        <td>
                            <strong><?php echo text($row['name']); ?></strong><br>
                            <span class="text-muted"><?php echo text($row['original']); ?></span>
                        </td>
                        <td><span class="kpi-status <?php echo attr(altavision_kpi_status_class($row['status'])); ?>"><?php echo text($row['status_label']); ?></span></td>
                        <td><?php echo text($row['iess_value']); ?></td>
                        <td><?php echo text($row['resto_value']); ?></td>
                        <td><?php echo text($row['total_value']); ?></td>
                        <td>
                            <strong><?php echo xlt('Fuente'); ?>:</strong> <?php echo text($row['source']); ?><br>
                            <strong><?php echo xlt('Brecha'); ?>:</strong> <?php echo text($row['gap']); ?>
                        </td>
                        <td><?php echo text($row['action']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="kpi-panel kpi-actions">
            <h3><?php echo xlt('Wins v1'); ?></h3>
            <ol>
                <li><?php echo xlt('Automatizar espera de consulta usando agenda y patient tracker.'); ?></li>
                <li><?php echo xlt('Automatizar cirugias suspendidas desde agenda quirurgica/campos de cirugia.'); ?></li>
                <li><?php echo xlt('Descargar Excel con IESS, Resto, Todos y Brechas.'); ?></li>
            </ol>
            <hr>
            <h3><?php echo xlt('Notas'); ?></h3>
            <p><?php echo xlt('Los indicadores pendientes muestran la brecha de captura y no calculan valores estimados.'); ?></p>
            <p><?php echo xlt('La siguiente etapa debe revisar cada indicador uno por uno para normalizar la data clinica.'); ?></p>
        </div>
    </div>
</form>
</body>
</html>
