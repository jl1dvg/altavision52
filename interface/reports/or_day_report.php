<?php
/**
 * Reporte operativo final del día de quirófano.
 */

require_once("../globals.php");

use OpenEMR\Core\Header;

function orDayReportCsvValue($value)
{
    return is_scalar($value) || $value === null ? (string)$value : '';
}

function orDayReportFetchSession($sessionId)
{
    return sqlQuery(
        "SELECT
            ds.id,
            ds.session_date,
            ds.room_name,
            ds.status,
            ds.opened_at,
            ds.closed_at,
            ds.notes,
            f.name AS facility_name,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS opened_by_name
        FROM or_day_sessions AS ds
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        LEFT JOIN users AS u ON u.id = ds.opened_by
        WHERE ds.id = ?",
        array($sessionId)
    );
}

function orDayReportFetchSummary($sessionId)
{
    $summary = array(
        'blocks_count' => 0,
        'surgeries_count' => 0,
        'day_qty' => 0,
        'block_qty' => 0,
        'surgery_qty' => 0,
        'day_cost' => 0,
        'block_cost' => 0,
        'surgery_cost' => 0,
        'distinct_items' => 0,
    );

    $row = sqlQuery(
        "SELECT
            (SELECT COUNT(*) FROM or_blocks WHERE day_session_id = ?) AS blocks_count,
            (
                SELECT COUNT(*)
                FROM or_surgeries AS s
                INNER JOIN or_blocks AS b ON b.id = s.block_id
                WHERE b.day_session_id = ?
            ) AS surgeries_count,
            (
                SELECT COALESCE(SUM(qty), 0)
                FROM or_consumptions
                WHERE level_type = 'day' AND level_id = ?
            ) AS day_qty,
            (
                SELECT COALESCE(SUM(oc.qty), 0)
                FROM or_consumptions AS oc
                INNER JOIN or_blocks AS b ON b.id = oc.level_id
                WHERE oc.level_type = 'block'
                  AND b.day_session_id = ?
            ) AS block_qty,
            (
                SELECT COALESCE(SUM(oc.qty), 0)
                FROM or_consumptions AS oc
                INNER JOIN or_surgeries AS s ON s.id = oc.level_id
                INNER JOIN or_blocks AS b ON b.id = s.block_id
                WHERE oc.level_type = 'surgery'
                  AND b.day_session_id = ?
            ) AS surgery_qty,
            (
                SELECT COALESCE(SUM(oc.qty * COALESCE(p.pr_price, 0)), 0)
                FROM or_consumptions AS oc
                LEFT JOIN prices AS p
                    ON p.pr_id = oc.code_id
                   AND p.pr_selector = ''
                   AND p.pr_level = 'costo'
                WHERE oc.level_type = 'day' AND oc.level_id = ?
            ) AS day_cost,
            (
                SELECT COALESCE(SUM(oc.qty * COALESCE(p.pr_price, 0)), 0)
                FROM or_consumptions AS oc
                INNER JOIN or_blocks AS b ON b.id = oc.level_id
                LEFT JOIN prices AS p
                    ON p.pr_id = oc.code_id
                   AND p.pr_selector = ''
                   AND p.pr_level = 'costo'
                WHERE oc.level_type = 'block'
                  AND b.day_session_id = ?
            ) AS block_cost,
            (
                SELECT COALESCE(SUM(oc.qty * COALESCE(p.pr_price, 0)), 0)
                FROM or_consumptions AS oc
                INNER JOIN or_surgeries AS s ON s.id = oc.level_id
                INNER JOIN or_blocks AS b ON b.id = s.block_id
                LEFT JOIN prices AS p
                    ON p.pr_id = oc.code_id
                   AND p.pr_selector = ''
                   AND p.pr_level = 'costo'
                WHERE oc.level_type = 'surgery'
                  AND b.day_session_id = ?
            ) AS surgery_cost",
        array($sessionId, $sessionId, $sessionId, $sessionId, $sessionId, $sessionId, $sessionId, $sessionId)
    );

    if (!empty($row)) {
        $summary['blocks_count'] = (int)($row['blocks_count'] ?? 0);
        $summary['surgeries_count'] = (int)($row['surgeries_count'] ?? 0);
        $summary['day_qty'] = (float)($row['day_qty'] ?? 0);
        $summary['block_qty'] = (float)($row['block_qty'] ?? 0);
        $summary['surgery_qty'] = (float)($row['surgery_qty'] ?? 0);
        $summary['day_cost'] = (float)($row['day_cost'] ?? 0);
        $summary['block_cost'] = (float)($row['block_cost'] ?? 0);
        $summary['surgery_cost'] = (float)($row['surgery_cost'] ?? 0);
    }

    $distinct = sqlQuery(
        "SELECT COUNT(*) AS distinct_items
        FROM (
            SELECT oc.code_id
            FROM or_consumptions AS oc
            WHERE oc.level_type = 'day' AND oc.level_id = ?
            UNION
            SELECT oc.code_id
            FROM or_consumptions AS oc
            INNER JOIN or_blocks AS b ON b.id = oc.level_id
            WHERE oc.level_type = 'block' AND b.day_session_id = ?
            UNION
            SELECT oc.code_id
            FROM or_consumptions AS oc
            INNER JOIN or_surgeries AS s ON s.id = oc.level_id
            INNER JOIN or_blocks AS b ON b.id = s.block_id
            WHERE oc.level_type = 'surgery' AND b.day_session_id = ?
        ) AS all_items",
        array($sessionId, $sessionId, $sessionId)
    );
    $summary['distinct_items'] = (int)($distinct['distinct_items'] ?? 0);

    return $summary;
}

function orDayReportFetchBlockRows($sessionId)
{
    $rows = array();
    $res = sqlStatement(
        "SELECT
            b.id,
            b.block_name,
            b.block_type,
            b.start_time,
            b.end_time,
            b.status,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name,
            (
                SELECT COUNT(*)
                FROM or_surgeries AS s
                WHERE s.block_id = b.id
            ) AS surgeries_count,
            (
                SELECT COALESCE(SUM(qty), 0)
                FROM or_consumptions
                WHERE level_type = 'block' AND level_id = b.id
            ) AS block_qty,
            (
                SELECT COALESCE(SUM(oc.qty), 0)
                FROM or_consumptions AS oc
                INNER JOIN or_surgeries AS s ON s.id = oc.level_id
                WHERE oc.level_type = 'surgery'
                  AND s.block_id = b.id
            ) AS surgery_qty,
            (
                SELECT COALESCE(SUM(oc.qty * COALESCE(p.pr_price, 0)), 0)
                FROM or_consumptions AS oc
                LEFT JOIN prices AS p
                    ON p.pr_id = oc.code_id
                   AND p.pr_selector = ''
                   AND p.pr_level = 'costo'
                WHERE oc.level_type = 'block' AND oc.level_id = b.id
            ) AS block_cost,
            (
                SELECT COALESCE(SUM(oc.qty * COALESCE(p.pr_price, 0)), 0)
                FROM or_consumptions AS oc
                INNER JOIN or_surgeries AS s ON s.id = oc.level_id
                LEFT JOIN prices AS p
                    ON p.pr_id = oc.code_id
                   AND p.pr_selector = ''
                   AND p.pr_level = 'costo'
                WHERE oc.level_type = 'surgery'
                  AND s.block_id = b.id
            ) AS surgery_cost
        FROM or_blocks AS b
        LEFT JOIN users AS u ON u.id = b.provider_id
        WHERE b.day_session_id = ?
        ORDER BY b.start_time, b.id",
        array($sessionId)
    );

    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }

    return $rows;
}

function orDayReportFetchSurgeryRows($sessionId)
{
    $rows = array();
    $res = sqlStatement(
        "SELECT
            s.id,
            s.appointment_event_id,
            s.procedure_name,
            s.eye,
            s.status,
            s.scheduled_time,
            e.pc_apptstatus AS agenda_status_id,
            lo.title AS agenda_status,
            CONCAT(COALESCE(p.fname, ''), ' ', COALESCE(p.lname, '')) AS patient_name,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name,
            (
                SELECT COALESCE(SUM(qty), 0)
                FROM or_consumptions
                WHERE level_type = 'surgery' AND level_id = s.id
            ) AS surgery_qty,
            (
                SELECT COALESCE(SUM(oc.qty * COALESCE(pr.pr_price, 0)), 0)
                FROM or_consumptions AS oc
                LEFT JOIN prices AS pr
                    ON pr.pr_id = oc.code_id
                   AND pr.pr_selector = ''
                   AND pr.pr_level = 'costo'
                WHERE oc.level_type = 'surgery' AND oc.level_id = s.id
            ) AS surgery_cost
        FROM or_surgeries AS s
        INNER JOIN or_blocks AS b ON b.id = s.block_id
        LEFT JOIN openemr_postcalendar_events AS e ON e.pc_eid = s.appointment_event_id
        LEFT JOIN list_options AS lo
            ON lo.list_id = 'apptstat'
            AND lo.option_id = e.pc_apptstatus
        LEFT JOIN patient_data AS p
            ON p.pid = s.pid
        LEFT JOIN users AS u ON u.id = s.primary_provider_id
        WHERE b.day_session_id = ?
        ORDER BY s.scheduled_time, s.id",
        array($sessionId)
    );

    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }

    return $rows;
}

function orDayReportFetchConsolidatedItems($sessionId)
{
    $rows = array();
    $sql = "SELECT
            base.code_snapshot,
            base.description_snapshot,
            base.unit_snapshot,
            SUM(base.day_qty) AS day_qty,
            SUM(base.block_qty) AS block_qty,
            SUM(base.surgery_qty) AS surgery_qty,
            SUM(base.total_qty) AS total_qty,
            SUM(base.day_cost) AS day_cost,
            SUM(base.block_cost) AS block_cost,
            SUM(base.surgery_cost) AS surgery_cost,
            SUM(base.total_cost) AS total_cost
        FROM (
            SELECT
                oc.code_snapshot,
                oc.description_snapshot,
                oc.unit_snapshot,
                oc.qty AS day_qty,
                0 AS block_qty,
                0 AS surgery_qty,
                oc.qty AS total_qty,
                (oc.qty * COALESCE(p.pr_price, 0)) AS day_cost,
                0 AS block_cost,
                0 AS surgery_cost,
                (oc.qty * COALESCE(p.pr_price, 0)) AS total_cost
            FROM or_consumptions AS oc
            LEFT JOIN prices AS p
                ON p.pr_id = oc.code_id
               AND p.pr_selector = ''
               AND p.pr_level = 'costo'
            WHERE oc.level_type = 'day'
              AND oc.level_id = ?

            UNION ALL

            SELECT
                oc.code_snapshot,
                oc.description_snapshot,
                oc.unit_snapshot,
                0 AS day_qty,
                oc.qty AS block_qty,
                0 AS surgery_qty,
                oc.qty AS total_qty,
                0 AS day_cost,
                (oc.qty * COALESCE(p.pr_price, 0)) AS block_cost,
                0 AS surgery_cost,
                (oc.qty * COALESCE(p.pr_price, 0)) AS total_cost
            FROM or_consumptions AS oc
            INNER JOIN or_blocks AS b ON b.id = oc.level_id
            LEFT JOIN prices AS p
                ON p.pr_id = oc.code_id
               AND p.pr_selector = ''
               AND p.pr_level = 'costo'
            WHERE oc.level_type = 'block'
              AND b.day_session_id = ?

            UNION ALL

            SELECT
                oc.code_snapshot,
                oc.description_snapshot,
                oc.unit_snapshot,
                0 AS day_qty,
                0 AS block_qty,
                oc.qty AS surgery_qty,
                oc.qty AS total_qty,
                0 AS day_cost,
                0 AS block_cost,
                (oc.qty * COALESCE(p.pr_price, 0)) AS surgery_cost,
                (oc.qty * COALESCE(p.pr_price, 0)) AS total_cost
            FROM or_consumptions AS oc
            INNER JOIN or_surgeries AS s ON s.id = oc.level_id
            INNER JOIN or_blocks AS b ON b.id = s.block_id
            LEFT JOIN prices AS p
                ON p.pr_id = oc.code_id
               AND p.pr_selector = ''
               AND p.pr_level = 'costo'
            WHERE oc.level_type = 'surgery'
              AND b.day_session_id = ?
        ) AS base
        GROUP BY base.code_snapshot, base.description_snapshot, base.unit_snapshot
        ORDER BY base.description_snapshot, base.code_snapshot";

    $res = sqlStatement($sql, array($sessionId, $sessionId, $sessionId));
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }

    return $rows;
}

function orDayReportFetchLevelItems($levelType, $levelId)
{
    $rows = array();
    $res = sqlStatement(
        "SELECT
            code_snapshot,
            description_snapshot,
            unit_snapshot,
            lot_number,
            SUM(qty) AS total_qty,
            MAX(COALESCE(p.pr_price, 0)) AS unit_cost,
            SUM(qty * COALESCE(p.pr_price, 0)) AS total_cost,
            MAX(notes) AS notes
        FROM or_consumptions AS oc
        LEFT JOIN prices AS p
            ON p.pr_id = oc.code_id
           AND p.pr_selector = ''
           AND p.pr_level = 'costo'
        WHERE level_type = ?
          AND level_id = ?
        GROUP BY code_snapshot, description_snapshot, unit_snapshot, lot_number
        ORDER BY description_snapshot, code_snapshot, lot_number",
        array($levelType, $levelId)
    );

    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }

    return $rows;
}

function orDayReportFetchExportRows($sessionId)
{
    $rows = array();
    $sql = "SELECT
            ds.session_date,
            COALESCE(f.name, '') AS facility_name,
            ds.room_name,
            ds.status AS day_status,
            'day' AS level_type,
            'Día quirúrgico' AS level_label,
            '' AS block_name,
            '' AS block_type,
            '' AS block_status,
            '' AS provider_name,
            '' AS patient_name,
            '' AS appointment_event_id,
            '' AS procedure_name,
            '' AS eye,
            '' AS surgery_status,
            oc.created_at,
            oc.code_snapshot,
            oc.description_snapshot,
            oc.unit_snapshot,
            oc.lot_number,
            oc.serial_number,
            oc.qty,
            COALESCE(p.pr_price, 0) AS unit_cost_costo,
            (oc.qty * COALESCE(p.pr_price, 0)) AS total_cost_costo,
            oc.notes
        FROM or_day_sessions AS ds
        INNER JOIN or_consumptions AS oc
            ON oc.level_type = 'day' AND oc.level_id = ds.id
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        LEFT JOIN prices AS p
            ON p.pr_id = oc.code_id
           AND p.pr_selector = ''
           AND p.pr_level = 'costo'
        WHERE ds.id = ?

        UNION ALL

        SELECT
            ds.session_date,
            COALESCE(f.name, '') AS facility_name,
            ds.room_name,
            ds.status AS day_status,
            'block' AS level_type,
            'Bloque / médico' AS level_label,
            COALESCE(b.block_name, '') AS block_name,
            COALESCE(b.block_type, '') AS block_type,
            COALESCE(b.status, '') AS block_status,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name,
            '' AS patient_name,
            '' AS appointment_event_id,
            '' AS procedure_name,
            '' AS eye,
            '' AS surgery_status,
            oc.created_at,
            oc.code_snapshot,
            oc.description_snapshot,
            oc.unit_snapshot,
            oc.lot_number,
            oc.serial_number,
            oc.qty,
            COALESCE(p.pr_price, 0) AS unit_cost_costo,
            (oc.qty * COALESCE(p.pr_price, 0)) AS total_cost_costo,
            oc.notes
        FROM or_day_sessions AS ds
        INNER JOIN or_blocks AS b ON b.day_session_id = ds.id
        INNER JOIN or_consumptions AS oc
            ON oc.level_type = 'block' AND oc.level_id = b.id
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        LEFT JOIN users AS u ON u.id = b.provider_id
        LEFT JOIN prices AS p
            ON p.pr_id = oc.code_id
           AND p.pr_selector = ''
           AND p.pr_level = 'costo'
        WHERE ds.id = ?

        UNION ALL

        SELECT
            ds.session_date,
            COALESCE(f.name, '') AS facility_name,
            ds.room_name,
            ds.status AS day_status,
            'surgery' AS level_type,
            'Paciente / cirugía' AS level_label,
            COALESCE(b.block_name, '') AS block_name,
            COALESCE(b.block_type, '') AS block_type,
            COALESCE(b.status, '') AS block_status,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name,
            CONCAT(COALESCE(pd.fname, ''), ' ', COALESCE(pd.lname, '')) AS patient_name,
            COALESCE(s.appointment_event_id, '') AS appointment_event_id,
            COALESCE(s.procedure_name, '') AS procedure_name,
            COALESCE(s.eye, '') AS eye,
            COALESCE(s.status, '') AS surgery_status,
            oc.created_at,
            oc.code_snapshot,
            oc.description_snapshot,
            oc.unit_snapshot,
            oc.lot_number,
            oc.serial_number,
            oc.qty,
            COALESCE(p.pr_price, 0) AS unit_cost_costo,
            (oc.qty * COALESCE(p.pr_price, 0)) AS total_cost_costo,
            oc.notes
        FROM or_day_sessions AS ds
        INNER JOIN or_blocks AS b ON b.day_session_id = ds.id
        INNER JOIN or_surgeries AS s ON s.block_id = b.id
        INNER JOIN or_consumptions AS oc
            ON oc.level_type = 'surgery' AND oc.level_id = s.id
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        LEFT JOIN users AS u ON u.id = s.primary_provider_id
        LEFT JOIN patient_data AS pd ON pd.pid = s.pid
        LEFT JOIN prices AS p
            ON p.pr_id = oc.code_id
           AND p.pr_selector = ''
           AND p.pr_level = 'costo'
        WHERE ds.id = ?

        ORDER BY session_date, level_type, block_name, provider_name, patient_name, description_snapshot, created_at";

    $res = sqlStatement($sql, array($sessionId, $sessionId, $sessionId));
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }

    return $rows;
}

$sessionId = isset($_REQUEST['day_session_id']) && ctype_digit((string)$_REQUEST['day_session_id'])
    ? (int)$_REQUEST['day_session_id']
    : 0;
$exportMode = isset($_GET['export']) ? trim((string)$_GET['export']) : '';

$session = $sessionId > 0 ? orDayReportFetchSession($sessionId) : null;

if (!empty($session) && in_array($exportMode, array('csv', 'xls'), true)) {
    $rows = orDayReportFetchExportRows($sessionId);
    $filenameBase = 'reporte_quirofano_' . preg_replace('/[^0-9\-]/', '', (string)$session['session_date']) . '_sesion_' . (int)$sessionId;
    $headers = array(
        'Fecha',
        'Sede',
        'Sala',
        'Estado día',
        'Nivel',
        'Etiqueta nivel',
        'Bloque',
        'Tipo bloque',
        'Estado bloque',
        'Médico',
        'Paciente',
        'Cita agenda',
        'Procedimiento',
        'Ojo',
        'Estado cirugía',
        'Fecha registro',
        'Código',
        'Descripción',
        'Unidad',
        'Lote',
        'Serie',
        'Cantidad',
        'Costo unitario',
        'Costo total',
        'Notas',
    );

    if ($exportMode === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filenameBase . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, array(
                orDayReportCsvValue($row['session_date']),
                orDayReportCsvValue($row['facility_name']),
                orDayReportCsvValue($row['room_name']),
                orDayReportCsvValue($row['day_status']),
                orDayReportCsvValue($row['level_type']),
                orDayReportCsvValue($row['level_label']),
                orDayReportCsvValue($row['block_name']),
                orDayReportCsvValue($row['block_type']),
                orDayReportCsvValue($row['block_status']),
                orDayReportCsvValue($row['provider_name']),
                orDayReportCsvValue($row['patient_name']),
                orDayReportCsvValue($row['appointment_event_id']),
                orDayReportCsvValue($row['procedure_name']),
                orDayReportCsvValue($row['eye']),
                orDayReportCsvValue($row['surgery_status']),
                orDayReportCsvValue($row['created_at']),
                orDayReportCsvValue($row['code_snapshot']),
                orDayReportCsvValue($row['description_snapshot']),
                orDayReportCsvValue($row['unit_snapshot']),
                orDayReportCsvValue($row['lot_number']),
                orDayReportCsvValue($row['serial_number']),
                number_format((float)($row['qty'] ?? 0), 4, '.', ''),
                number_format((float)($row['unit_cost_costo'] ?? 0), 4, '.', ''),
                number_format((float)($row['total_cost_costo'] ?? 0), 4, '.', ''),
                orDayReportCsvValue($row['notes']),
            ));
        }
        fclose($output);
        exit;
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filenameBase . '.xls');
    echo "<table border='1'>";
    echo "<tr>";
    foreach ($headers as $headerLabel) {
        echo '<th>' . text($headerLabel) . '</th>';
    }
    echo "</tr>";
    foreach ($rows as $row) {
        echo "<tr>";
        echo '<td>' . text($row['session_date']) . '</td>';
        echo '<td>' . text($row['facility_name']) . '</td>';
        echo '<td>' . text($row['room_name']) . '</td>';
        echo '<td>' . text($row['day_status']) . '</td>';
        echo '<td>' . text($row['level_type']) . '</td>';
        echo '<td>' . text($row['level_label']) . '</td>';
        echo '<td>' . text($row['block_name']) . '</td>';
        echo '<td>' . text($row['block_type']) . '</td>';
        echo '<td>' . text($row['block_status']) . '</td>';
        echo '<td>' . text($row['provider_name']) . '</td>';
        echo '<td>' . text($row['patient_name']) . '</td>';
        echo '<td>' . text($row['appointment_event_id']) . '</td>';
        echo '<td>' . text($row['procedure_name']) . '</td>';
        echo '<td>' . text($row['eye']) . '</td>';
        echo '<td>' . text($row['surgery_status']) . '</td>';
        echo '<td>' . text($row['created_at']) . '</td>';
        echo '<td>' . text($row['code_snapshot']) . '</td>';
        echo '<td>' . text($row['description_snapshot']) . '</td>';
        echo '<td>' . text($row['unit_snapshot']) . '</td>';
        echo '<td>' . text($row['lot_number']) . '</td>';
        echo '<td>' . text($row['serial_number']) . '</td>';
        echo '<td style="mso-number-format:\'0.0000\';">' . text(number_format((float)($row['qty'] ?? 0), 4, '.', '')) . '</td>';
        echo '<td style="mso-number-format:\'0.0000\';">' . text(number_format((float)($row['unit_cost_costo'] ?? 0), 4, '.', '')) . '</td>';
        echo '<td style="mso-number-format:\'0.0000\';">' . text(number_format((float)($row['total_cost_costo'] ?? 0), 4, '.', '')) . '</td>';
        echo '<td>' . text($row['notes']) . '</td>';
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

$summary = !empty($session) ? orDayReportFetchSummary($sessionId) : array();
$blocks = !empty($session) ? orDayReportFetchBlockRows($sessionId) : array();
$surgeries = !empty($session) ? orDayReportFetchSurgeryRows($sessionId) : array();
$items = !empty($session) ? orDayReportFetchConsolidatedItems($sessionId) : array();
$dayItems = !empty($session) ? orDayReportFetchLevelItems('day', $sessionId) : array();
?>
<html>
<head>
    <title>Reporte final del día</title>
    <?php Header::setupHeader(array('report-helper')); ?>
    <style>
        body.body_top {
            background: radial-gradient(circle at top left, rgba(20, 126, 251, 0.14), transparent 28%),
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 24%),
            linear-gradient(180deg, #f4f8fb 0%, #eaf1f7 100%);
            color: #16324f;
            padding: 18px 0 32px;
        }

        .or-shell {
            max-width: 1480px;
            margin: 0 auto;
            padding: 0 18px;
        }

        .or-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
            padding: 22px 24px;
            border-radius: 24px;
            background: linear-gradient(135deg, #0f4c81 0%, #1768ac 55%, #2da3b0 100%);
            color: #fff;
            box-shadow: 0 20px 45px rgba(19, 73, 120, 0.18);
        }

        .or-page-kicker {
            margin: 0 0 6px;
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.78;
        }

        .or-page-title {
            margin: 0;
            font-size: 32px;
            line-height: 1.1;
            font-weight: 700;
        }

        .or-page-subtitle {
            margin: 8px 0 0;
            max-width: 760px;
            font-size: 15px;
            line-height: 1.5;
            opacity: 0.92;
        }

        .or-layout {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
        }

        .or-panel {
            border: 1px solid rgba(187, 203, 219, 0.72);
            border-radius: 24px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.9);
            margin-top: 18px;
            box-shadow: 0 18px 40px rgba(28, 74, 106, 0.09);
            /* backdrop-filter removed because it creates a stacking context that can place Bootstrap modals behind the backdrop */
        }

        .or-panel h3 {
            margin: 0 0 18px;
            font-size: 22px;
            font-weight: 700;
            color: #113a5c;
        }

        .or-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .or-card {
            border-radius: 20px;
            padding: 16px 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f5f9fc 100%);
            border: 1px solid rgba(187, 203, 219, 0.72);
            box-shadow: 0 14px 30px rgba(28, 74, 106, 0.08);
        }

        .or-card-label {
            font-size: 12px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #61788c;
        }

        .or-card-value {
            display: block;
            margin-top: 8px;
            font-size: 28px;
            font-weight: 700;
            color: #143d60;
        }

        .or-summary-table th {
            width: 150px;
            background: #f7fafc;
            color: #46627c;
            font-size: 12px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .or-summary-table th,
        .or-summary-table td {
            padding: 12px 14px !important;
        }

        .or-table {
            margin-bottom: 0;
            overflow: hidden;
            border-radius: 18px;
        }

        .or-table thead th {
            background: #eaf3fb;
            border-bottom: 0 !important;
            color: #3c5d79;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .or-table td,
        .or-table th {
            padding: 14px 12px !important;
            vertical-align: middle !important;
        }

        .or-table tbody tr:nth-child(odd) {
            background: rgba(246, 250, 252, 0.72);
        }

        .or-table tbody tr:hover {
            background: rgba(220, 237, 250, 0.75);
        }

        .or-status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: #e2eefb;
            color: #175a96;
        }

        .or-panel .btn {
            border-radius: 14px;
            min-height: 44px;
            padding: 10px 16px;
            font-weight: 700;
        }

        .or-details {
            margin-top: 14px;
            border: 1px solid rgba(187, 203, 219, 0.72);
            border-radius: 18px;
            background: #fbfdff;
            overflow: hidden;
        }

        .or-details summary {
            cursor: pointer;
            list-style: none;
            padding: 16px 18px;
            font-weight: 700;
            color: #15486f;
            background: #eef5fb;
        }

        .or-details summary::-webkit-details-marker {
            display: none;
        }

        .or-details-body {
            padding: 16px 18px 18px;
        }

        .or-mini-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .or-mini-card {
            border-radius: 16px;
            padding: 12px 14px;
            background: #f4f9fd;
            border: 1px solid rgba(187, 203, 219, 0.6);
        }

        .or-mini-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #627a8e;
        }

        .or-mini-value {
            display: block;
            margin-top: 6px;
            font-size: 20px;
            font-weight: 700;
            color: #143d60;
        }

        .or-action-cell {
            white-space: nowrap;
            text-align: center;
        }

        .or-detail-button {
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            background: #1768ac;
            border: 1px solid #1768ac;
            color: #fff;
        }

        .or-detail-button:hover,
        .or-detail-button:focus {
            background: #0f4c81;
            border-color: #0f4c81;
            color: #fff;
        }

        .or-modal-header {
            border-bottom: 1px solid #dce8f2;
            background: #eef5fb;
            color: #143d60;
        }

        .or-modal-title-main {
            display: block;
            font-size: 18px;
            font-weight: 700;
        }

        .or-modal-title-sub {
            display: block;
            margin-top: 4px;
            font-size: 13px;
            color: #5d7488;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }

        @media (max-width: 1200px) {
            .or-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .or-layout {
                grid-template-columns: 1fr;
            }

            .or-mini-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .or-shell {
                padding: 0 12px;
            }

            .or-page-header {
                padding: 18px;
                border-radius: 20px;
                flex-direction: column;
            }

            .or-page-title {
                font-size: 26px;
            }

            .or-summary-grid {
                grid-template-columns: 1fr;
            }

            .or-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body class="body_top">
<div class="or-shell">
    <div class="or-page-header">
        <div>
            <p class="or-page-kicker">Quirófano</p>
            <h1 class="or-page-title">Reporte final del día</h1>
            <p class="or-page-subtitle">Resumen operativo del día quirúrgico con bloques, cirugías activadas e insumos
                consolidados.</p>
        </div>
        <?php if (!empty($session['id'])) { ?>
            <div>
                <a class="btn btn-default"
                   href="or_day_sessions.php?session_id=<?php echo urlencode((string)$session['id']); ?>">Volver a la
                    jornada</a>
                <a class="btn btn-default"
                   href="or_day_report.php?day_session_id=<?php echo urlencode((string)$session['id']); ?>&export=csv">Exportar
                    CSV</a>
                <a class="btn btn-default"
                   href="or_day_report.php?day_session_id=<?php echo urlencode((string)$session['id']); ?>&export=xls">Exportar
                    Excel</a>
            </div>
        <?php } ?>
    </div>

    <?php if (empty($session)) { ?>
        <div class="or-panel">Selecciona primero un día quirúrgico válido.</div>
    <?php } else { ?>
        <div class="or-summary-grid">
            <div class="or-card"><span class="or-card-label">Bloques</span><strong
                    class="or-card-value"><?php echo text((string)$summary['blocks_count']); ?></strong></div>
            <div class="or-card"><span class="or-card-label">Cirugías</span><strong
                    class="or-card-value"><?php echo text((string)$summary['surgeries_count']); ?></strong></div>
            <div class="or-card"><span class="or-card-label">Costo día</span><strong
                    class="or-card-value">$<?php echo text(number_format((float)$summary['day_cost'], 2)); ?></strong>
            </div>
            <div class="or-card"><span class="or-card-label">Costo bloques</span><strong
                    class="or-card-value">$<?php echo text(number_format((float)$summary['block_cost'], 2)); ?></strong>
            </div>
            <div class="or-card"><span class="or-card-label">Costo cirugías</span><strong
                    class="or-card-value">$<?php echo text(number_format((float)$summary['surgery_cost'], 2)); ?></strong>
            </div>
            <div class="or-card"><span class="or-card-label">Consumo día</span><strong
                    class="or-card-value"><?php echo text(number_format((float)$summary['day_qty'], 2)); ?></strong>
            </div>
            <div class="or-card"><span class="or-card-label">Consumo bloques</span><strong
                    class="or-card-value"><?php echo text(number_format((float)$summary['block_qty'], 2)); ?></strong>
            </div>
            <div class="or-card"><span class="or-card-label">Consumo cirugías</span><strong
                    class="or-card-value"><?php echo text(number_format((float)$summary['surgery_qty'], 2)); ?></strong>
            </div>
        </div>

        <div class="or-layout">
            <div class="or-panel">
                <h3>Resumen de jornada</h3>
                <table class="table table-bordered or-summary-table">
                    <tr>
                        <th>ID</th>
                        <td><?php echo text($session['id']); ?></td>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <td><?php echo text($session['session_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Sede</th>
                        <td><?php echo text($session['facility_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Sala</th>
                        <td><?php echo text($session['room_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Responsable</th>
                        <td><?php echo text($session['opened_by_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td><span class="or-status-pill"><?php echo text($session['status']); ?></span></td>
                    </tr>
                    <tr>
                        <th>Abierto el</th>
                        <td><?php echo text($session['opened_at']); ?></td>
                    </tr>
                    <tr>
                        <th>Cerrado el</th>
                        <td><?php echo text($session['closed_at']); ?></td>
                    </tr>
                    <tr>
                        <th>Insumos distintos</th>
                        <td><?php echo text((string)$summary['distinct_items']); ?></td>
                    </tr>
                    <tr>
                        <th>Costo total del día</th>
                        <td>
                            $<?php echo text(number_format((float)($summary['day_cost'] + $summary['block_cost'] + $summary['surgery_cost']), 2)); ?></td>
                    </tr>
                    <tr>
                        <th>Notas</th>
                        <td><?php echo text($session['notes']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="or-panel">
                <h3>Bloques del día</h3>
                <table class="table table-bordered table-striped or-table">
                    <thead>
                    <tr>
                        <th>Médico</th>
                        <th>Bloque</th>
                        <th>Tipo</th>
                        <th>Cirugías</th>
                        <th>Consumo bloque</th>
                        <th>Consumo cirugías</th>
                        <th>Costo bloque</th>
                        <th>Costo cirugías</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($blocks as $block) { ?>
                        <tr>
                            <td><?php echo text($block['provider_name']); ?></td>
                            <td><?php echo text($block['block_name']); ?></td>
                            <td><?php echo text($block['block_type']); ?></td>
                            <td><?php echo text($block['surgeries_count']); ?></td>
                            <td><?php echo text(number_format((float)$block['block_qty'], 2)); ?></td>
                            <td><?php echo text(number_format((float)$block['surgery_qty'], 2)); ?></td>
                            <td>$<?php echo text(number_format((float)$block['block_cost'], 2)); ?></td>
                            <td>$<?php echo text(number_format((float)$block['surgery_cost'], 2)); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="or-panel">
            <h3>1. Consumo del día quirúrgico</h3>
            <p>Lo que se abrió para que funcione el quirófano durante el día.</p>
            <table class="table table-bordered table-striped or-table">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Unidad</th>
                    <th>Lote</th>
                    <th>Cantidad</th>
                    <th>Costo unitario</th>
                    <th>Costo total</th>
                    <th>Notas</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($dayItems as $item) { ?>
                    <tr>
                        <td><?php echo text($item['code_snapshot']); ?></td>
                        <td><?php echo text($item['description_snapshot']); ?></td>
                        <td><?php echo text($item['unit_snapshot']); ?></td>
                        <td><?php echo text($item['lot_number']); ?></td>
                        <td><strong><?php echo text(number_format((float)$item['total_qty'], 2)); ?></strong></td>
                        <td>$<?php echo text(number_format((float)$item['unit_cost'], 2)); ?></td>
                        <td>$<?php echo text(number_format((float)$item['total_cost'], 2)); ?></td>
                        <td><?php echo text($item['notes']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="or-panel">
            <h3>2. Consumo por jornada / médico</h3>
            <p>Lo que se consumió a nivel de bloque y lo que arrastraron sus cirugías.</p>
            <table class="table table-bordered table-striped or-table">
                <thead>
                <tr>
                    <th>Médico</th>
                    <th>Bloque</th>
                    <th>Tipo</th>
                    <th>Horario</th>
                    <th>Cirugías</th>
                    <th>Consumo bloque</th>
                    <th>Consumo cirugías</th>
                    <th>Costo bloque</th>
                    <th>Costo cirugías</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($blocks as $block) { ?>
                    <tr>
                        <td><?php echo text($block['provider_name']); ?></td>
                        <td><?php echo text($block['block_name']); ?></td>
                        <td><?php echo text($block['block_type']); ?></td>
                        <td><?php echo text($block['start_time']); ?> - <?php echo text($block['end_time']); ?></td>
                        <td><?php echo text($block['surgeries_count']); ?></td>
                        <td><?php echo text(number_format((float)$block['block_qty'], 2)); ?></td>
                        <td><?php echo text(number_format((float)$block['surgery_qty'], 2)); ?></td>
                        <td>$<?php echo text(number_format((float)$block['block_cost'], 2)); ?></td>
                        <td>$<?php echo text(number_format((float)$block['surgery_cost'], 2)); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <?php foreach ($blocks as $block) { ?>
                <?php $blockItems = orDayReportFetchLevelItems('block', (int)$block['id']); ?>
                <details class="or-details">
                    <summary><?php echo text($block['provider_name']); ?>
                        | <?php echo text($block['block_name']); ?></summary>
                    <div class="or-details-body">
                        <div class="or-mini-grid">
                            <div class="or-mini-card"><span class="or-mini-label">Tipo</span><strong
                                    class="or-mini-value"><?php echo text($block['block_type']); ?></strong></div>
                            <div class="or-mini-card"><span class="or-mini-label">Cirugías</span><strong
                                    class="or-mini-value"><?php echo text($block['surgeries_count']); ?></strong></div>
                            <div class="or-mini-card"><span class="or-mini-label">Cantidad bloque</span><strong
                                    class="or-mini-value"><?php echo text(number_format((float)$block['block_qty'], 2)); ?></strong>
                            </div>
                            <div class="or-mini-card"><span class="or-mini-label">Costo bloque</span><strong
                                    class="or-mini-value">$<?php echo text(number_format((float)$block['block_cost'], 2)); ?></strong>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped or-table">
                            <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Costo unitario</th>
                                <th>Costo total</th>
                                <th>Notas</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($blockItems as $item) { ?>
                                <tr>
                                    <td><?php echo text($item['code_snapshot']); ?></td>
                                    <td><?php echo text($item['description_snapshot']); ?></td>
                                    <td><?php echo text($item['unit_snapshot']); ?></td>
                                    <td><?php echo text($item['lot_number']); ?></td>
                                    <td>
                                        <strong><?php echo text(number_format((float)$item['total_qty'], 2)); ?></strong>
                                    </td>
                                    <td>$<?php echo text(number_format((float)$item['unit_cost'], 2)); ?></td>
                                    <td>$<?php echo text(number_format((float)$item['total_cost'], 2)); ?></td>
                                    <td><?php echo text($item['notes']); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </details>
            <?php } ?>
        </div>

        <div class="or-panel">
            <h3>3. Consumo por paciente</h3>
            <p>Detalle de lo que se usó específicamente en cada cirugía. Usa el botón de detalle para revisar los
                insumos sin duplicar la tabla principal.</p>
            <table class="table table-bordered table-striped or-table">
                <thead>
                <tr>
                    <th>Cita</th>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Procedimiento</th>
                    <th>Ojo</th>
                    <th>Estado agenda</th>
                    <th>Estado</th>
                    <th>Cantidad consumida</th>
                    <th>Costo</th>
                    <th>Detalle</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($surgeries as $surgery) { ?>
                    <?php $modalId = 'surgeryItemsModal' . (int)$surgery['id']; ?>
                    <tr>
                        <td><?php echo text($surgery['appointment_event_id']); ?></td>
                        <td><?php echo text($surgery['patient_name']); ?></td>
                        <td><?php echo text($surgery['provider_name']); ?></td>
                        <td><?php echo text($surgery['procedure_name']); ?></td>
                        <td><?php echo text($surgery['eye']); ?></td>
                        <td><?php echo text($surgery['agenda_status']); ?></td>
                        <td><span class="or-status-pill"><?php echo text($surgery['status']); ?></span></td>
                        <td><?php echo text(number_format((float)$surgery['surgery_qty'], 2)); ?></td>
                        <td>$<?php echo text(number_format((float)$surgery['surgery_cost'], 2)); ?></td>
                        <td class="or-action-cell">
                            <button
                                type="button"
                                class="btn btn-sm or-detail-button"
                                data-toggle="modal"
                                data-target="#<?php echo attr($modalId); ?>">
                                Ver insumos
                            </button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <?php foreach ($surgeries as $surgery) { ?>
                <?php
                $surgeryItems = orDayReportFetchLevelItems('surgery', (int)$surgery['id']);
                $modalId = 'surgeryItemsModal' . (int)$surgery['id'];
                ?>
                <div class="modal fade" id="<?php echo attr($modalId); ?>" tabindex="-1" role="dialog"
                     aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header or-modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <span
                                    class="or-modal-title-main"><?php echo text($surgery['patient_name']); ?> | <?php echo text($surgery['procedure_name']); ?></span>
                                <span class="or-modal-title-sub">
                                    Cita <?php echo text($surgery['appointment_event_id']); ?> · <?php echo text($surgery['provider_name']); ?> · Ojo <?php echo text($surgery['eye']); ?> · Total <?php echo text(number_format((float)$surgery['surgery_qty'], 2)); ?> · Costo $<?php echo text(number_format((float)$surgery['surgery_cost'], 2)); ?>
                                </span>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered table-striped or-table">
                                    <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Unidad</th>
                                        <th>Lote</th>
                                        <th>Cantidad</th>
                                        <th>Costo unitario</th>
                                        <th>Costo total</th>
                                        <th>Notas</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($surgeryItems as $item) { ?>
                                        <tr>
                                            <td><?php echo text($item['code_snapshot']); ?></td>
                                            <td><?php echo text($item['description_snapshot']); ?></td>
                                            <td><?php echo text($item['unit_snapshot']); ?></td>
                                            <td><?php echo text($item['lot_number']); ?></td>
                                            <td>
                                                <strong><?php echo text(number_format((float)$item['total_qty'], 2)); ?></strong>
                                            </td>
                                            <td>$<?php echo text(number_format((float)$item['unit_cost'], 2)); ?></td>
                                            <td>$<?php echo text(number_format((float)$item['total_cost'], 2)); ?></td>
                                            <td><?php echo text($item['notes']); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="or-panel">
            <h3>4. Consolidado general del día</h3>
            <table class="table table-bordered table-striped or-table">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Unidad</th>
                    <th>Día</th>
                    <th>Bloques</th>
                    <th>Cirugías</th>
                    <th>Total</th>
                    <th>Costo día</th>
                    <th>Costo bloques</th>
                    <th>Costo cirugías</th>
                    <th>Costo total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item) { ?>
                    <tr>
                        <td><?php echo text($item['code_snapshot']); ?></td>
                        <td><?php echo text($item['description_snapshot']); ?></td>
                        <td><?php echo text($item['unit_snapshot']); ?></td>
                        <td><?php echo text(number_format((float)$item['day_qty'], 2)); ?></td>
                        <td><?php echo text(number_format((float)$item['block_qty'], 2)); ?></td>
                        <td><?php echo text(number_format((float)$item['surgery_qty'], 2)); ?></td>
                        <td><strong><?php echo text(number_format((float)$item['total_qty'], 2)); ?></strong></td>
                        <td>$<?php echo text(number_format((float)$item['day_cost'], 2)); ?></td>
                        <td>$<?php echo text(number_format((float)$item['block_cost'], 2)); ?></td>
                        <td>$<?php echo text(number_format((float)$item['surgery_cost'], 2)); ?></td>
                        <td><strong>$<?php echo text(number_format((float)$item['total_cost'], 2)); ?></strong></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
</body>
</html>
