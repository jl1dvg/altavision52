<?php
/**
 * OR surgery import and review for a selected block.
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

function orSurgeryFetchBlock($blockId)
{
    return sqlQuery(
        "SELECT
            b.id,
            b.day_session_id,
            b.provider_id,
            b.block_name,
            b.block_type,
            b.start_time,
            b.end_time,
            b.status,
            b.notes,
            ds.session_date,
            ds.facility_id,
            ds.room_name,
            ds.status AS day_status,
            f.name AS facility_name,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name
        FROM or_blocks AS b
        INNER JOIN or_day_sessions AS ds ON ds.id = b.day_session_id
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        LEFT JOIN users AS u ON u.id = b.provider_id
        WHERE b.id = ?",
        array($blockId)
    );
}

function orSurgeryFetchImported($blockId)
{
    $rows = array();
    $sql = "SELECT
            s.id,
            s.appointment_event_id,
            s.pid,
            s.hc_number,
            s.procedure_code,
            s.procedure_name,
            s.eye,
            s.scheduled_time,
            s.start_time,
            s.end_time,
            s.primary_provider_id,
            s.status,
            s.notes,
            e.pc_apptstatus AS appointment_status,
            CONCAT(COALESCE(p.fname, ''), ' ', COALESCE(p.lname, '')) AS patient_name,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name
        FROM or_surgeries AS s
        LEFT JOIN openemr_postcalendar_events AS e ON e.pc_eid = s.appointment_event_id
        LEFT JOIN patient_data AS p ON p.pid = s.pid
        LEFT JOIN users AS u ON u.id = s.primary_provider_id
        WHERE s.block_id = ?
        ORDER BY s.scheduled_time, s.id";
    $res = sqlStatement($sql, array($blockId));
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orSurgeryFetchAgendaCandidates($block)
{
    $rows = array();
    if (empty($block)) {
        return $rows;
    }

    $sql = "SELECT
            e.pc_eid,
            e.pc_pid,
            e.pc_aid,
            e.pc_eventDate,
            e.pc_startTime,
            e.pc_room,
            e.pc_facility,
            e.pc_title,
            e.pc_hometext,
            e.pc_apptqx,
            e.pc_apptqxOI,
            e.pc_LIOOD,
            e.pc_LIOOI,
            e.pc_apptstatus,
            CONCAT(COALESCE(p.fname, ''), ' ', COALESCE(p.lname, '')) AS patient_name,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name
        FROM openemr_postcalendar_events AS e
        LEFT JOIN patient_data AS p ON p.pid = e.pc_pid
        LEFT JOIN users AS u ON u.id = CAST(e.pc_aid AS UNSIGNED)
        WHERE e.pc_catid = 15
          AND e.pc_eventDate = ?
          AND CAST(e.pc_aid AS UNSIGNED) = ?
          AND NOT EXISTS (
              SELECT 1
              FROM or_surgeries AS s
              WHERE s.appointment_event_id = e.pc_eid
          )
        ORDER BY e.pc_startTime, e.pc_eid";
    $res = sqlStatement(
        $sql,
        array($block['session_date'], $block['provider_id'])
    );
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orSurgeryNormalizeRoom($value)
{
    $value = trim(mb_strtolower((string) $value));
    if ($value === '') {
        return '';
    }

    return preg_replace('/\s+/', ' ', $value);
}

function orSurgeryMatchesFacility($block, $candidate)
{
    if (empty($block['facility_id'])) {
        return true;
    }

    return (int) $block['facility_id'] === (int) ($candidate['pc_facility'] ?? 0);
}

function orSurgeryMatchesRoom($block, $candidate)
{
    $blockRoom = orSurgeryNormalizeRoom($block['room_name'] ?? '');
    $candidateRoom = orSurgeryNormalizeRoom($candidate['pc_room'] ?? '');

    if ($blockRoom === '' || $candidateRoom === '') {
        return false;
    }

    return $blockRoom === $candidateRoom;
}

function orSurgeryIsStatusEligible($status)
{
    $status = trim((string) $status);
    if ($status === '') {
        return false;
    }

    $row = sqlQuery(
        "SELECT toggle_setting_1, toggle_setting_2
        FROM list_options
        WHERE list_id = 'apptstat' AND option_id = ? AND activity = 1",
        array($status)
    );

    if (!empty($row['toggle_setting_1']) || !empty($row['toggle_setting_2'])) {
        return true;
    }

    return in_array($status, array('<', '$'), true);
}

function orSurgeryStatusLabel($status)
{
    $status = trim((string) $status);
    if ($status === '') {
        return '';
    }

    $row = sqlQuery(
        "SELECT title
        FROM list_options
        WHERE list_id = 'apptstat' AND option_id = ? AND activity = 1",
        array($status)
    );

    return trim((string) ($row['title'] ?? ''));
}

function orSurgeryActivateAppointment($blockId, $block, $appointmentId)
{
    $appointment = sqlQuery(
        "SELECT *
        FROM openemr_postcalendar_events
        WHERE pc_eid = ?",
        array($appointmentId)
    );

    if (empty($appointment['pc_eid'])) {
        return array('ok' => false, 'error' => xlt('Appointment was not found.'));
    }

    if ((int) ($appointment['pc_catid'] ?? 0) !== 15) {
        return array('ok' => false, 'error' => xlt('Only surgical agenda events can be activated.'));
    }

    if (($appointment['pc_eventDate'] ?? '') !== ($block['session_date'] ?? '')) {
        return array('ok' => false, 'error' => xlt('The appointment date does not match the selected OR day.'));
    }

    if (!orSurgeryMatchesFacility($block, $appointment)) {
        return array('ok' => false, 'error' => xlt('The appointment facility does not match the selected OR day.'));
    }

    if ((int) $block['provider_id'] > 0 && (int) $appointment['pc_aid'] !== (int) $block['provider_id']) {
        return array('ok' => false, 'error' => xlt('The appointment provider does not match the selected block.'));
    }

    if (!orSurgeryIsStatusEligible($appointment['pc_apptstatus'] ?? '')) {
        return array('ok' => false, 'error' => xlt('The appointment status is not eligible yet for OR activation.'));
    }

    $exists = sqlQuery(
        "SELECT id
        FROM or_surgeries
        WHERE appointment_event_id = ?",
        array($appointmentId)
    );

    if (!empty($exists['id'])) {
        return array('ok' => false, 'error' => xlt('This appointment was already activated.'));
    }

    $procedureName = orSurgeryDeriveProcedureName($appointment);
    $eye = orSurgeryDeriveEye($appointment);
    $scheduledTime = $appointment['pc_eventDate'] . ' ' . ($appointment['pc_startTime'] ?? '00:00:00');
    $notes = trim((string) ($appointment['pc_hometext'] ?? ''));

    sqlInsert(
        "INSERT INTO or_surgeries (
            block_id,
            appointment_event_id,
            pid,
            procedure_name,
            eye,
            scheduled_time,
            primary_provider_id,
            status,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)",
        array(
            $blockId,
            $appointmentId,
            $appointment['pc_pid'] ?: null,
            $procedureName,
            $eye,
            $scheduledTime,
            $appointment['pc_aid'] !== '' ? (int) $appointment['pc_aid'] : null,
            $notes
        )
    );

    return array('ok' => true);
}

function orSurgeryDeriveProcedureName($row)
{
    if (!empty($row['pc_apptqx']) && !empty($row['pc_apptqxOI'])) {
        return trim($row['pc_apptqx']) . ' / ' . trim($row['pc_apptqxOI']);
    }
    if (!empty($row['pc_apptqx'])) {
        return trim($row['pc_apptqx']);
    }
    if (!empty($row['pc_apptqxOI'])) {
        return trim($row['pc_apptqxOI']);
    }
    return trim((string) ($row['pc_title'] ?? ''));
}

function orSurgeryDeriveEye($row)
{
    if (!empty($row['pc_apptqx']) && !empty($row['pc_apptqxOI'])) {
        return 'OU';
    }
    if (!empty($row['pc_apptqx'])) {
        return 'OD';
    }
    if (!empty($row['pc_apptqxOI'])) {
        return 'OS';
    }
    return null;
}

$blockId = isset($_REQUEST['block_id']) && ctype_digit((string) $_REQUEST['block_id'])
    ? (int) $_REQUEST['block_id']
    : 0;
$flashMessage = '';
$flashError = '';

if (($_POST['form_action'] ?? '') === 'import_appointment') {
    $blockId = isset($_POST['form_block_id']) && ctype_digit((string) $_POST['form_block_id'])
        ? (int) $_POST['form_block_id']
        : 0;
    $appointmentId = isset($_POST['form_appointment_id']) && ctype_digit((string) $_POST['form_appointment_id'])
        ? (int) $_POST['form_appointment_id']
        : 0;

    $block = $blockId > 0 ? orSurgeryFetchBlock($blockId) : null;
    if (empty($block)) {
        $flashError = xlt('Selected block was not found.');
    } else {
        $activation = orSurgeryActivateAppointment($blockId, $block, $appointmentId);
        if (!$activation['ok']) {
            $flashError = $activation['error'];
        } else {
            $flashMessage = xlt('Agenda surgery activated.');
        }
    }
}

if (($_POST['form_action'] ?? '') === 'sync_eligible_appointments') {
    $blockId = isset($_POST['form_block_id']) && ctype_digit((string) $_POST['form_block_id'])
        ? (int) $_POST['form_block_id']
        : 0;
    $block = $blockId > 0 ? orSurgeryFetchBlock($blockId) : null;

    if (empty($block)) {
        $flashError = xlt('Selected block was not found.');
    } else {
        $agendaCandidates = orSurgeryFetchAgendaCandidates($block);
        $activatedCount = 0;
        foreach ($agendaCandidates as $candidate) {
            if (!orSurgeryMatchesFacility($block, $candidate)) {
                continue;
            }

            if (!orSurgeryIsStatusEligible($candidate['pc_apptstatus'] ?? '')) {
                continue;
            }

            $activation = orSurgeryActivateAppointment($blockId, $block, (int) $candidate['pc_eid']);
            if (!empty($activation['ok'])) {
                $activatedCount++;
            }
        }

        if ($activatedCount > 0) {
            $flashMessage = sprintf(xlt('%s agenda surgeries were activated.'), text((string) $activatedCount));
        } else {
            $flashError = xlt('No eligible agenda surgeries were available to activate.');
        }
    }
}

$block = $blockId > 0 ? orSurgeryFetchBlock($blockId) : null;
$importedSurgeries = !empty($block) ? orSurgeryFetchImported($blockId) : array();
$agendaCandidates = !empty($block) ? orSurgeryFetchAgendaCandidates($block) : array();
?>
<html>
<head>
    <title>Cirugías de Quirófano</title>
    <?php Header::setupHeader(array('report-helper')); ?>
    <style>
        body.body_top {
            background:
                radial-gradient(circle at top left, rgba(20, 126, 251, 0.14), transparent 28%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 24%),
                linear-gradient(180deg, #f4f8fb 0%, #eaf1f7 100%);
            color: #16324f;
            padding: 18px 0 32px;
        }
        .or-shell {
            max-width: 1420px;
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
        .or-flash-ok {
            margin: 12px 0;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid #b9d7b9;
            background: #eef7ee;
            box-shadow: 0 12px 24px rgba(91, 154, 96, 0.12);
        }
        .or-flash-error {
            margin: 12px 0;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid #e0b4b4;
            background: #faecec;
            box-shadow: 0 12px 24px rgba(180, 78, 78, 0.1);
        }
        .or-panel {
            border: 1px solid rgba(187, 203, 219, 0.72);
            border-radius: 24px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.9);
            margin-top: 18px;
            box-shadow: 0 18px 40px rgba(28, 74, 106, 0.09);
            backdrop-filter: blur(8px);
        }
        .or-panel h3 {
            margin: 0 0 18px;
            font-size: 22px;
            font-weight: 700;
            color: #113a5c;
        }
        .or-summary-table th {
            width: 145px;
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
        .or-meta-note {
            margin: 0 0 14px;
            color: #5c7388;
            font-size: 14px;
        }
        .or-panel .btn {
            border-radius: 14px;
            min-height: 44px;
            padding: 10px 16px;
            font-weight: 700;
        }
        .or-panel .btn-xs {
            min-height: 36px;
            padding: 8px 12px;
            border-radius: 12px;
        }
        @media (max-width: 768px) {
            body.body_top {
                padding-top: 12px;
            }
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
            .or-panel {
                padding: 16px;
                border-radius: 20px;
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
        <h1 class="or-page-title">Cirugías de Quirófano</h1>
        <p class="or-page-subtitle">Sincroniza las cirugías quirúrgicas elegibles desde agenda y pasa rápido al consumo por paciente.</p>
    </div>
    <div>
        <a class="btn btn-default" href="or_day_sessions.php<?php echo !empty($block['day_session_id']) ? '?session_id=' . urlencode((string) $block['day_session_id']) : ''; ?>">
            Volver a jornadas
        </a>
    </div>
</div>

<?php if ($flashMessage !== '') { ?>
    <div class="or-flash-ok"><?php echo text($flashMessage); ?></div>
<?php } ?>
<?php if ($flashError !== '') { ?>
    <div class="or-flash-error"><?php echo text($flashError); ?></div>
<?php } ?>

<?php if (empty($block)) { ?>
    <div class="or-panel">Selecciona un bloque primero.</div>
<?php } else { ?>
    <div class="or-panel">
        <h3>Bloque seleccionado</h3>
        <table class="table table-bordered or-summary-table">
            <tr><th>Bloque</th><td><?php echo text($block['block_name']); ?></td></tr>
            <tr><th>Médico</th><td><?php echo text($block['provider_name']); ?></td></tr>
            <tr><th>Fecha</th><td><?php echo text($block['session_date']); ?></td></tr>
            <tr><th>Sede</th><td><?php echo text($block['facility_name']); ?></td></tr>
            <tr><th>Sala</th><td><?php echo text($block['room_name']); ?></td></tr>
            <tr><th>Horario</th><td><?php echo text($block['start_time']); ?> - <?php echo text($block['end_time']); ?></td></tr>
        </table>
    </div>

    <div class="or-panel">
        <h3>Candidatos desde agenda</h3>
        <p class="or-meta-note">
            Los eventos quirúrgicos se toman por fecha y médico. La sede debe coincidir con el día de quirófano.
        </p>
        <div style="margin-bottom: 12px;">
            <form method="post" action="or_surgeries.php?block_id=<?php echo urlencode((string) $blockId); ?>" onsubmit="return top.restoreSession()" style="display:inline-block;">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
                <input type="hidden" name="form_action" value="sync_eligible_appointments"/>
                <input type="hidden" name="form_block_id" value="<?php echo attr((string) $blockId); ?>"/>
                <button type="submit" class="btn btn-primary">Sincronizar cirugías elegibles desde agenda</button>
            </form>
        </div>
        <table class="table table-bordered table-striped or-table">
            <thead>
            <tr>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Procedimiento</th>
                <th>Ojo</th>
                <th><?php echo xlt('LIO'); ?></th>
                <th>Sede</th>
                <th>Estado</th>
                <th>Elegible</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($agendaCandidates as $candidate) { ?>
                <?php
                $candidateProcedure = orSurgeryDeriveProcedureName($candidate);
                $candidateEye = orSurgeryDeriveEye($candidate);
                $candidateFacilityMatch = orSurgeryMatchesFacility($block, $candidate);
                $candidateEligible = $candidateFacilityMatch && orSurgeryIsStatusEligible($candidate['pc_apptstatus'] ?? '');
                $candidateStatusLabel = orSurgeryStatusLabel($candidate['pc_apptstatus'] ?? '');
                $candidateLio = trim((string) ($candidate['pc_LIOOD'] ?? ''));
                if (!empty($candidate['pc_LIOOI'])) {
                    $candidateLio .= ($candidateLio !== '' ? ' / ' : '') . trim((string) $candidate['pc_LIOOI']);
                }
                ?>
                <tr>
                    <td><?php echo text($candidate['pc_startTime']); ?></td>
                    <td><?php echo text($candidate['patient_name']); ?></td>
                    <td><?php echo text($candidate['provider_name']); ?></td>
                    <td><?php echo text($candidateProcedure); ?></td>
                    <td><?php echo text($candidateEye); ?></td>
                    <td><?php echo text($candidateLio); ?></td>
                    <td>
                        <span class="or-status-pill"><?php echo text($candidateFacilityMatch ? 'Sede OK' : 'Sede distinta'); ?></span>
                    </td>
                    <td>
                        <?php //echo text($candidate['pc_apptstatus']); ?>
                        <?php if ($candidateStatusLabel !== '') { ?>
                            <span class="text-muted"><?php echo text($candidateStatusLabel); ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php
                        $candidateReasons = array();
                        if (!$candidateFacilityMatch) {
                            $candidateReasons[] = 'Sede distinta';
                        }
                        if (!orSurgeryIsStatusEligible($candidate['pc_apptstatus'] ?? '')) {
                            $candidateReasons[] = 'Estado no operativo';
                        }
                        echo text($candidateEligible ? 'Sí' : 'No');
                        if (!$candidateEligible && !empty($candidateReasons)) {
                            echo ' <span class="text-muted">' . text(implode(' / ', $candidateReasons)) . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($candidateEligible) { ?>
                            <span>Lista para sincronizar</span>
                        <?php } else { ?>
                            <span class="text-muted">Revisar diferencia</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="or-panel">
        <h3>Cirugías activadas</h3>
        <table class="table table-bordered table-striped or-table">
            <thead>
            <tr>
                <th><?php echo xlt('ID'); ?></th>
                <th>Cita</th>
                <th>Paciente</th>
                <th>Procedimiento</th>
                <th>Ojo</th>
                <th>Programada</th>
                <th>Médico</th>
                <th>Estado agenda</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($importedSurgeries as $surgery) { ?>
                <tr>
                    <td><?php echo text($surgery['id']); ?></td>
                    <td><?php echo text($surgery['appointment_event_id']); ?></td>
                    <td><?php echo text($surgery['patient_name']); ?></td>
                    <td><?php echo text($surgery['procedure_name']); ?></td>
                    <td><?php echo text($surgery['eye']); ?></td>
                    <td><?php echo text($surgery['scheduled_time']); ?></td>
                    <td><?php echo text($surgery['provider_name']); ?></td>
                    <td>
                        <?php echo text($surgery['appointment_status']); ?>
                        <?php $importedStatusLabel = orSurgeryStatusLabel($surgery['appointment_status'] ?? ''); ?>
                        <?php if ($importedStatusLabel !== '') { ?>
                            <br><span class="text-muted"><?php echo text($importedStatusLabel); ?></span>
                        <?php } ?>
                    </td>
                    <td><span class="or-status-pill"><?php echo text($surgery['status']); ?></span></td>
                    <td>
                        <a class="btn btn-xs btn-default" href="or_consumptions.php?level_type=surgery&level_id=<?php echo urlencode((string) $surgery['id']); ?>">
                            Consumos
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
</div>
</body>
</html>
