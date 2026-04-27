<?php
/**
 * OR day sessions and block management.
 *
 * Minimal operational screen to open surgical days and create provider blocks.
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

function orDayIsValidDate($value)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
        return false;
    }

    $parts = explode('-', $value);
    return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}

function orDayBuildDateTime($date, $time)
{
    $date = trim((string) $date);
    $time = trim((string) $time);
    if (!orDayIsValidDate($date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
        return null;
    }

    return $date . ' ' . $time . ':00';
}

function orDayFetchFacilities()
{
    $rows = array();
    $res = sqlStatement("SELECT id, name FROM facility ORDER BY name");
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orDayFetchProviders()
{
    $rows = array();
    $res = sqlStatement("SELECT id, fname, lname FROM users WHERE active = 1 ORDER BY lname, fname");
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orDayFetchRecentSessions()
{
    $rows = array();
    $sql = "SELECT
            ds.id,
            ds.session_date,
            ds.facility_id,
            ds.room_name,
            ds.opened_by,
            ds.opened_at,
            ds.closed_at,
            ds.status,
            ds.notes,
            f.name AS facility_name,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS opened_by_name
        FROM or_day_sessions AS ds
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        LEFT JOIN users AS u ON u.id = ds.opened_by
        ORDER BY ds.session_date DESC, ds.id DESC
        LIMIT 50";
    $res = sqlStatement($sql);
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orDayFetchSession($sessionId)
{
    return sqlQuery(
        "SELECT
            ds.*,
            f.name AS facility_name
        FROM or_day_sessions AS ds
        LEFT JOIN facility AS f ON f.id = ds.facility_id
        WHERE ds.id = ?",
        array($sessionId)
    );
}

function orDayFetchBlocks($sessionId)
{
    $rows = array();
    $sql = "SELECT
            b.id,
            b.day_session_id,
            b.provider_id,
            b.block_name,
            b.block_type,
            b.start_time,
            b.end_time,
            b.status,
            b.notes,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name
        FROM or_blocks AS b
        LEFT JOIN users AS u ON u.id = b.provider_id
        WHERE b.day_session_id = ?
        ORDER BY b.start_time, b.id";
    $res = sqlStatement($sql, array($sessionId));
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

$formAction = $_POST['form_action'] ?? '';
$selectedSessionId = isset($_REQUEST['session_id']) && ctype_digit((string) $_REQUEST['session_id'])
    ? (int) $_REQUEST['session_id']
    : 0;
$flashMessage = '';
$flashError = '';

if ($formAction === 'open_day') {
    $sessionDate = trim((string) ($_POST['form_session_date'] ?? ''));
    $facilityId = isset($_POST['form_facility_id']) && ctype_digit((string) $_POST['form_facility_id'])
        ? (int) $_POST['form_facility_id']
        : 0;
    $roomName = trim((string) ($_POST['form_room_name'] ?? ''));
    $openedBy = isset($_POST['form_opened_by']) && ctype_digit((string) $_POST['form_opened_by'])
        ? (int) $_POST['form_opened_by']
        : 0;
    $openedTime = trim((string) ($_POST['form_opened_time'] ?? ''));
    $notes = trim((string) ($_POST['form_notes'] ?? ''));
    $openedAt = orDayBuildDateTime($sessionDate, $openedTime);

    if (!orDayIsValidDate($sessionDate)) {
        $flashError = xlt('Invalid surgery date.');
    } elseif ($facilityId <= 0) {
        $flashError = xlt('Facility is required.');
    } elseif ($roomName === '') {
        $flashError = xlt('Room is required.');
    } elseif ($openedBy <= 0) {
        $flashError = xlt('Responsible user is required.');
    } elseif ($openedAt === null) {
        $flashError = xlt('Opening time is invalid.');
    } else {
        $existing = sqlQuery(
            "SELECT id
            FROM or_day_sessions
            WHERE session_date = ?
              AND facility_id = ?
              AND room_name = ?",
            array($sessionDate, $facilityId, $roomName)
        );

        if (!empty($existing['id'])) {
            $selectedSessionId = (int) $existing['id'];
            $flashError = xlt('A day session already exists for this date, facility, and room.');
        } else {
            $selectedSessionId = (int) sqlInsert(
                "INSERT INTO or_day_sessions (
                    session_date,
                    facility_id,
                    room_name,
                    opened_by,
                    opened_at,
                    status,
                    notes
                ) VALUES (?, ?, ?, ?, ?, 'open', ?)",
                array($sessionDate, $facilityId, $roomName, $openedBy, $openedAt, $notes)
            );
            $flashMessage = xlt('Surgery day opened successfully.');
        }
    }
}

if ($formAction === 'create_block') {
    $daySessionId = isset($_POST['form_day_session_id']) && ctype_digit((string) $_POST['form_day_session_id'])
        ? (int) $_POST['form_day_session_id']
        : 0;
    $providerId = isset($_POST['form_provider_id']) && ctype_digit((string) $_POST['form_provider_id'])
        ? (int) $_POST['form_provider_id']
        : 0;
    $blockName = trim((string) ($_POST['form_block_name'] ?? ''));
    $blockType = trim((string) ($_POST['form_block_type'] ?? 'custom'));
    $blockNotes = trim((string) ($_POST['form_block_notes'] ?? ''));
    $blockDate = trim((string) ($_POST['form_block_date'] ?? ''));
    $startTimeRaw = trim((string) ($_POST['form_start_time'] ?? ''));
    $endTimeRaw = trim((string) ($_POST['form_end_time'] ?? ''));
    $startTime = orDayBuildDateTime($blockDate, $startTimeRaw);
    $endTime = orDayBuildDateTime($blockDate, $endTimeRaw);
    $allowedBlockTypes = array('morning', 'afternoon', 'extra', 'custom');

    if ($daySessionId <= 0) {
        $flashError = xlt('Select a surgery day first.');
    } elseif ($providerId <= 0) {
        $flashError = xlt('Provider is required.');
    } elseif (!in_array($blockType, $allowedBlockTypes, true)) {
        $flashError = xlt('Block type is invalid.');
    } elseif ($startTime === null || $endTime === null) {
        $flashError = xlt('Block start and end time are required.');
    } elseif ($endTime <= $startTime) {
        $flashError = xlt('Block end time must be later than start time.');
    } else {
        sqlInsert(
            "INSERT INTO or_blocks (
                day_session_id,
                provider_id,
                block_name,
                block_type,
                start_time,
                end_time,
                status,
                notes
            ) VALUES (?, ?, ?, ?, ?, ?, 'open', ?)",
            array($daySessionId, $providerId, $blockName, $blockType, $startTime, $endTime, $blockNotes)
        );
        $selectedSessionId = $daySessionId;
        $flashMessage = xlt('Block created successfully.');
    }
}

$facilityOptions = orDayFetchFacilities();
$providerOptions = orDayFetchProviders();
$recentSessions = orDayFetchRecentSessions();

if ($selectedSessionId <= 0 && !empty($recentSessions)) {
    $selectedSessionId = (int) $recentSessions[0]['id'];
}

$selectedSession = $selectedSessionId > 0 ? orDayFetchSession($selectedSessionId) : null;
$sessionBlocks = $selectedSessionId > 0 ? orDayFetchBlocks($selectedSessionId) : array();

$defaultSessionDate = date('Y-m-d');
$defaultOpenedTime = date('H:i');
$defaultBlockDate = !empty($selectedSession['session_date']) ? $selectedSession['session_date'] : $defaultSessionDate;
?>
<html>
<head>
    <title>Jornadas de Quirófano</title>
    <?php Header::setupHeader(array('datetime-picker', 'report-helper')); ?>
    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
        $(function () {
            $('.datepicker').datetimepicker({
                timepicker: false,
                format: 'Y-m-d',
                formatDate: 'Y-m-d',
                scrollInput: false
            });
            $('.timepicker').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 15,
                scrollInput: false
            });
        });
    </script>
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
        .or-page-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .or-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 18px;
        }
        .or-panel {
            border: 1px solid rgba(187, 203, 219, 0.72);
            border-radius: 24px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(28, 74, 106, 0.09);
            backdrop-filter: blur(8px);
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
        .or-inline-form td {
            padding: 7px 12px 7px 0;
            vertical-align: top;
        }
        .or-inline-form td:first-child {
            width: 140px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #5f7386;
            padding-top: 15px;
        }
        .or-session-list {
            max-height: 520px;
            overflow-y: auto;
        }
        .or-links {
            margin: 0 0 16px;
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
        .or-panel .form-control {
            min-height: 46px;
            border-radius: 14px;
            border-color: #c8d7e4;
            box-shadow: none;
            font-size: 16px;
        }
        .or-panel textarea.form-control {
            min-height: 96px;
            resize: vertical;
        }
        .or-action-stack {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        @media (max-width: 1100px) {
            .or-layout {
                grid-template-columns: 1fr;
            }
            .or-page-header {
                flex-direction: column;
            }
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
            }
            .or-page-title {
                font-size: 26px;
            }
            .or-panel {
                padding: 16px;
                border-radius: 20px;
            }
            .or-inline-form td,
            .or-inline-form tr,
            .or-inline-form tbody {
                display: block;
                width: 100%;
            }
            .or-inline-form td:first-child {
                width: auto;
                padding-bottom: 4px;
                padding-top: 6px;
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
        <h1 class="or-page-title">Jornadas de Quirófano</h1>
        <p class="or-page-subtitle">Abre el día, crea bloques por médico y navega rápido a consumos o cirugías desde una interfaz más cómoda para tablet.</p>
    </div>
    <div class="or-page-actions">
        <a class="btn btn-default" href="or_supply_catalog.php">Abrir catálogo de insumos</a>
    </div>
</div>

<?php if ($flashMessage !== '') { ?>
    <div class="or-flash-ok"><?php echo text($flashMessage); ?></div>
<?php } ?>
<?php if ($flashError !== '') { ?>
    <div class="or-flash-error"><?php echo text($flashError); ?></div>
<?php } ?>

<div class="or-layout">
    <div class="or-panel">
        <h3>Abrir día quirúrgico</h3>
        <form method="post" action="or_day_sessions.php" onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
            <input type="hidden" name="form_action" value="open_day"/>
            <table class="or-inline-form">
                <tr>
                    <td>Fecha</td>
                    <td><input type="text" class="form-control datepicker" name="form_session_date" value="<?php echo attr($defaultSessionDate); ?>"/></td>
                </tr>
                <tr>
                    <td>Sede</td>
                    <td>
                        <select class="form-control" name="form_facility_id">
                            <option value="">Seleccionar</option>
                            <?php foreach ($facilityOptions as $facility) { ?>
                                <option value="<?php echo attr($facility['id']); ?>"><?php echo text($facility['name']); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Sala</td>
                    <td><input type="text" class="form-control" name="form_room_name" placeholder="Quirófano"/></td>
                </tr>
                <tr>
                    <td>Responsable</td>
                    <td>
                        <select class="form-control" name="form_opened_by">
                            <option value="">Seleccionar</option>
                            <?php foreach ($providerOptions as $provider) { ?>
                                <option value="<?php echo attr($provider['id']); ?>">
                                    <?php echo text(trim(($provider['lname'] ?? '') . ' ' . ($provider['fname'] ?? ''))); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Hora de apertura</td>
                    <td><input type="text" class="form-control timepicker" name="form_opened_time" value="<?php echo attr($defaultOpenedTime); ?>"/></td>
                </tr>
                <tr>
                    <td>Notas</td>
                    <td><textarea class="form-control" name="form_notes" rows="3"></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td><button type="submit" class="btn btn-primary">Abrir día</button></td>
                </tr>
            </table>
        </form>
    </div>

    <div class="or-panel">
        <h3>Días quirúrgicos recientes</h3>
        <div class="or-session-list">
            <table class="table table-bordered table-striped or-table">
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Sede</th>
                    <th>Sala</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentSessions as $session) { ?>
                    <tr>
                        <td><?php echo text($session['session_date']); ?></td>
                        <td><?php echo text($session['facility_name']); ?></td>
                        <td><?php echo text($session['room_name']); ?></td>
                        <td><?php echo text($session['status']); ?></td>
                        <td>
                            <a class="btn btn-xs btn-default" href="or_day_sessions.php?session_id=<?php echo urlencode((string) $session['id']); ?>">
                                Abrir
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($selectedSession)) { ?>
    <div class="or-layout">
        <div class="or-panel">
            <h3>Día seleccionado</h3>
            <table class="table table-bordered or-summary-table">
                <tr><th>ID</th><td><?php echo text($selectedSession['id']); ?></td></tr>
                <tr><th>Fecha</th><td><?php echo text($selectedSession['session_date']); ?></td></tr>
                <tr><th>Sede</th><td><?php echo text($selectedSession['facility_name']); ?></td></tr>
                <tr><th>Sala</th><td><?php echo text($selectedSession['room_name']); ?></td></tr>
                <tr><th>Estado</th><td><span class="or-status-pill"><?php echo text($selectedSession['status']); ?></span></td></tr>
                <tr><th>Abierto el</th><td><?php echo text($selectedSession['opened_at']); ?></td></tr>
                <tr><th>Notas</th><td><?php echo text($selectedSession['notes']); ?></td></tr>
                <tr>
                    <th>Consumos</th>
                    <td>
                        <a class="btn btn-default btn-sm" href="or_consumptions.php?level_type=day&level_id=<?php echo urlencode((string) $selectedSession['id']); ?>">
                            Abrir consumos del día
                        </a>
                        <a class="btn btn-default btn-sm" href="or_day_report.php?day_session_id=<?php echo urlencode((string) $selectedSession['id']); ?>">
                            Reporte final del día
                        </a>
                    </td>
                </tr>
            </table>
        </div>

        <div class="or-panel">
            <h3>Crear bloque</h3>
            <form method="post" action="or_day_sessions.php?session_id=<?php echo urlencode((string) $selectedSession['id']); ?>" onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
                <input type="hidden" name="form_action" value="create_block"/>
                <input type="hidden" name="form_day_session_id" value="<?php echo attr((string) $selectedSession['id']); ?>"/>
                <input type="hidden" name="form_block_date" value="<?php echo attr($defaultBlockDate); ?>"/>
                <table class="or-inline-form">
                    <tr>
                        <td>Médico</td>
                        <td>
                            <select class="form-control" name="form_provider_id">
                                <option value="">Seleccionar</option>
                                <?php foreach ($providerOptions as $provider) { ?>
                                    <option value="<?php echo attr($provider['id']); ?>">
                                        <?php echo text(trim(($provider['lname'] ?? '') . ' ' . ($provider['fname'] ?? ''))); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Nombre del bloque</td>
                        <td><input type="text" class="form-control" name="form_block_name" placeholder="Retina mañana"/></td>
                    </tr>
                    <tr>
                        <td>Tipo de bloque</td>
                        <td>
                            <select class="form-control" name="form_block_type">
                                <option value="morning">Mañana</option>
                                <option value="afternoon">Tarde</option>
                                <option value="extra">Extra</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Hora inicio</td>
                        <td><input type="text" class="form-control timepicker" name="form_start_time" value="08:00"/></td>
                    </tr>
                    <tr>
                        <td>Hora fin</td>
                        <td><input type="text" class="form-control timepicker" name="form_end_time" value="12:00"/></td>
                    </tr>
                    <tr>
                        <td>Notas</td>
                        <td><textarea class="form-control" name="form_block_notes" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button type="submit" class="btn btn-primary">Crear bloque</button></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

    <div class="or-panel" style="margin-top: 18px;">
        <h3>Bloques del día seleccionado</h3>
        <table class="table table-bordered table-striped or-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Médico</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
                <th>Notas</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sessionBlocks as $block) { ?>
                <tr>
                    <td><?php echo text($block['id']); ?></td>
                    <td><?php echo text($block['provider_name']); ?></td>
                    <td><?php echo text($block['block_name']); ?></td>
                    <td><?php echo text($block['block_type']); ?></td>
                    <td><?php echo text($block['start_time']); ?></td>
                    <td><?php echo text($block['end_time']); ?></td>
                    <td><span class="or-status-pill"><?php echo text($block['status']); ?></span></td>
                    <td><?php echo text($block['notes']); ?></td>
                    <td>
                        <div class="or-action-stack">
                            <a class="btn btn-xs btn-default" href="or_surgeries.php?block_id=<?php echo urlencode((string) $block['id']); ?>">
                                Abrir
                            </a>
                            <a class="btn btn-xs btn-default" href="or_consumptions.php?level_type=block&level_id=<?php echo urlencode((string) $block['id']); ?>">
                                Consumos
                            </a>
                        </div>
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
