<?php
/**
 * OR consumptions by level.
 *
 * Supports day, block and surgery targets, with a minimal searchable
 * catalog sourced from codes + or_supply_catalog.
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

function orConsumptionAllowedLevelTypes()
{
    return array('day', 'block', 'surgery');
}

function orConsumptionFetchTarget($levelType, $levelId)
{
    if ($levelType === 'day') {
        return sqlQuery(
            "SELECT
                ds.id,
                ds.session_date,
                ds.facility_id,
                ds.room_name,
                ds.status,
                f.name AS facility_name,
                CONCAT('Day ', ds.session_date, ' / ', COALESCE(f.name, ''), ' / ', ds.room_name) AS target_label,
                CONCAT('or_day_sessions.php', CHAR(63), 'session_id=', ds.id) AS back_link
            FROM or_day_sessions AS ds
            LEFT JOIN facility AS f ON f.id = ds.facility_id
            WHERE ds.id = ?",
            array($levelId)
        );
    }

    if ($levelType === 'block') {
        return sqlQuery(
            "SELECT
                b.id,
                b.day_session_id,
                b.status,
                b.block_name,
                b.block_type,
                CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS provider_name,
                CONCAT('Block ', COALESCE(b.block_name, ''), ' / ', COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS target_label,
                CONCAT('or_surgeries.php', CHAR(63), 'block_id=', b.id) AS back_link
            FROM or_blocks AS b
            LEFT JOIN users AS u ON u.id = b.provider_id
            WHERE b.id = ?",
            array($levelId)
        );
    }

    if ($levelType === 'surgery') {
        return sqlQuery(
            "SELECT
                s.id,
                s.block_id,
                s.status,
                s.procedure_name,
                s.eye,
                CONCAT(COALESCE(p.fname, ''), ' ', COALESCE(p.lname, '')) AS patient_name,
                CONCAT('Surgery ', COALESCE(s.procedure_name, ''), ' / ', COALESCE(p.fname, ''), ' ', COALESCE(p.lname, '')) AS target_label,
                CONCAT('or_surgeries.php', CHAR(63), 'block_id=', s.block_id) AS back_link
            FROM or_surgeries AS s
            LEFT JOIN patient_data AS p ON p.pid = s.pid
            WHERE s.id = ?",
            array($levelId)
        );
    }

    return null;
}

function orConsumptionFetchExisting($levelType, $levelId)
{
    $rows = array();
    $sql = "SELECT
            oc.id,
            oc.level_type,
            oc.level_id,
            oc.code_id,
            oc.qty,
            oc.lot_number,
            oc.serial_number,
            oc.unit_snapshot,
            oc.code_snapshot,
            oc.description_snapshot,
            oc.unit_cost_snapshot,
            oc.total_cost_snapshot,
            oc.source,
            oc.status,
            oc.notes,
            oc.created_at,
            CONCAT(COALESCE(u.fname, ''), ' ', COALESCE(u.lname, '')) AS created_by_name
        FROM or_consumptions AS oc
        LEFT JOIN users AS u ON u.id = oc.created_by
        WHERE oc.level_type = ?
          AND oc.level_id = ?
        ORDER BY oc.created_at DESC, oc.id DESC";
    $res = sqlStatement($sql, array($levelType, $levelId));
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orConsumptionSearchCatalog($term)
{
    $rows = array();
    $term = trim((string) $term);
    if ($term === '') {
        return $rows;
    }

    $sql = "SELECT
            c.id,
            c.code,
            c.code_text,
            c.code_text_short,
            c.units,
            c.fee,
            osc.default_level,
            osc.requires_lot,
            osc.requires_serial,
            osc.is_reusable
        FROM codes AS c
        INNER JOIN or_supply_catalog AS osc ON osc.code_id = c.id
        WHERE osc.active = 1
          AND c.active = 1
          AND (
              c.code LIKE CONCAT('%', ?, '%')
              OR c.code_text LIKE CONCAT('%', ?, '%')
              OR COALESCE(c.code_text_short, '') LIKE CONCAT('%', ?, '%')
          )
        ORDER BY c.code_text
        LIMIT 50";
    $res = sqlStatement($sql, array($term, $term, $term));
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    return $rows;
}

function orConsumptionBuildUpdatedNotes($existingNotes, $newNotes)
{
    $existingNotes = trim((string) $existingNotes);
    $newNotes = trim((string) $newNotes);

    if ($newNotes === '') {
        return $existingNotes;
    }

    if ($existingNotes === '') {
        return $newNotes;
    }

    if ($existingNotes === $newNotes) {
        return $existingNotes;
    }

    return $existingNotes . ' | ' . $newNotes;
}

function orConsumptionFetchRow($consumptionId)
{
    return sqlQuery(
        "SELECT
            id,
            level_type,
            level_id,
            qty,
            unit_cost_snapshot
        FROM or_consumptions
        WHERE id = ?",
        array($consumptionId)
    );
}

$levelType = trim((string) ($_REQUEST['level_type'] ?? ''));
$levelId = isset($_REQUEST['level_id']) && ctype_digit((string) $_REQUEST['level_id'])
    ? (int) $_REQUEST['level_id']
    : 0;
$searchTerm = trim((string) ($_REQUEST['search_term'] ?? ''));
$flashMessage = '';
$flashError = '';

if (!in_array($levelType, orConsumptionAllowedLevelTypes(), true) || $levelId <= 0) {
    $levelType = '';
    $levelId = 0;
}

if (($_POST['form_action'] ?? '') === 'add_consumption') {
    $levelType = trim((string) ($_POST['form_level_type'] ?? ''));
    $levelId = isset($_POST['form_level_id']) && ctype_digit((string) $_POST['form_level_id'])
        ? (int) $_POST['form_level_id']
        : 0;
    $codeId = isset($_POST['form_code_id']) && ctype_digit((string) $_POST['form_code_id'])
        ? (int) $_POST['form_code_id']
        : 0;
    $qtyRaw = trim((string) ($_POST['form_qty'] ?? '1'));
    $lotNumber = trim((string) ($_POST['form_lot_number'] ?? ''));
    $serialNumber = trim((string) ($_POST['form_serial_number'] ?? ''));
    $notes = trim((string) ($_POST['form_notes'] ?? ''));
    $searchTerm = trim((string) ($_POST['form_search_term'] ?? ''));

    $target = ($levelId > 0 && in_array($levelType, orConsumptionAllowedLevelTypes(), true))
        ? orConsumptionFetchTarget($levelType, $levelId)
        : null;

    if (empty($target)) {
        $flashError = xlt('Consumption target is invalid.');
    } elseif ($codeId <= 0) {
        $flashError = xlt('Supply item is required.');
    } elseif (!is_numeric($qtyRaw) || (float) $qtyRaw <= 0) {
        $flashError = xlt('Quantity must be greater than zero.');
    } else {
        $catalogItem = sqlQuery(
            "SELECT
                c.id,
                c.code,
                c.code_text,
                c.units,
                c.fee,
                osc.requires_lot,
                osc.requires_serial
            FROM codes AS c
            INNER JOIN or_supply_catalog AS osc ON osc.code_id = c.id
            WHERE c.id = ?
              AND c.active = 1
              AND osc.active = 1",
            array($codeId)
        );

        if (empty($catalogItem['id'])) {
            $flashError = xlt('Selected supply item is not available in the OR catalog.');
        } elseif ((int) ($catalogItem['requires_lot'] ?? 0) === 1 && $lotNumber === '') {
            $flashError = xlt('This item requires a lot number.');
        } elseif ((int) ($catalogItem['requires_serial'] ?? 0) === 1 && $serialNumber === '') {
            $flashError = xlt('This item requires a serial number.');
        } else {
            $qty = (float) $qtyRaw;
            $unitCost = isset($catalogItem['fee']) ? (float) $catalogItem['fee'] : 0.0;
            $totalCost = $qty * $unitCost;
            $existingConsumption = sqlQuery(
                "SELECT
                    id,
                    qty,
                    notes
                FROM or_consumptions
                WHERE level_type = ?
                  AND level_id = ?
                  AND code_id = ?
                  AND COALESCE(lot_number, '') = ?
                  AND COALESCE(serial_number, '') = ?
                  AND source = 'manual'
                  AND status = 'used'
                ORDER BY id DESC
                LIMIT 1",
                array(
                    $levelType,
                    $levelId,
                    $codeId,
                    $lotNumber,
                    $serialNumber
                )
            );

            if (!empty($existingConsumption['id'])) {
                $newQty = (float) $existingConsumption['qty'] + $qty;
                $newNotes = orConsumptionBuildUpdatedNotes($existingConsumption['notes'] ?? '', $notes);
                sqlStatement(
                    "UPDATE or_consumptions
                    SET qty = ?,
                        total_cost_snapshot = ?,
                        notes = ?
                    WHERE id = ?",
                    array(
                        $newQty,
                        ($newQty * $unitCost),
                        $newNotes !== '' ? $newNotes : null,
                        $existingConsumption['id']
                    )
                );
                $flashMessage = xlt('Consumption quantity updated.');
            } else {
                sqlInsert(
                    "INSERT INTO or_consumptions (
                        level_type,
                        level_id,
                        code_id,
                        qty,
                        lot_number,
                        serial_number,
                        unit_snapshot,
                        code_snapshot,
                        description_snapshot,
                        unit_cost_snapshot,
                        total_cost_snapshot,
                        source,
                        status,
                        notes,
                        created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'manual', 'used', ?, ?)",
                    array(
                        $levelType,
                        $levelId,
                        $codeId,
                        $qty,
                        $lotNumber !== '' ? $lotNumber : null,
                        $serialNumber !== '' ? $serialNumber : null,
                        (string) ($catalogItem['units'] ?? ''),
                        (string) ($catalogItem['code'] ?? ''),
                        (string) ($catalogItem['code_text'] ?? ''),
                        $unitCost,
                        $totalCost,
                        $notes,
                        $_SESSION['authUserID'] ?? null
                    )
                );
                $flashMessage = xlt('Consumption saved.');
            }
        }
    }
}

if (($_POST['form_action'] ?? '') === 'adjust_consumption_qty') {
    $levelType = trim((string) ($_POST['form_level_type'] ?? ''));
    $levelId = isset($_POST['form_level_id']) && ctype_digit((string) $_POST['form_level_id'])
        ? (int) $_POST['form_level_id']
        : 0;
    $consumptionId = isset($_POST['form_consumption_id']) && ctype_digit((string) $_POST['form_consumption_id'])
        ? (int) $_POST['form_consumption_id']
        : 0;
    $direction = trim((string) ($_POST['form_direction'] ?? ''));
    $step = 1.0;

    $target = ($levelId > 0 && in_array($levelType, orConsumptionAllowedLevelTypes(), true))
        ? orConsumptionFetchTarget($levelType, $levelId)
        : null;

    if (empty($target)) {
        $flashError = xlt('Consumption target is invalid.');
    } elseif ($consumptionId <= 0 || !in_array($direction, array('increase', 'decrease'), true)) {
        $flashError = xlt('Consumption adjustment is invalid.');
    } else {
        $consumption = orConsumptionFetchRow($consumptionId);
        if (
            empty($consumption['id']) ||
            $consumption['level_type'] !== $levelType ||
            (int) $consumption['level_id'] !== $levelId
        ) {
            $flashError = xlt('Consumption row was not found for this target.');
        } else {
            $currentQty = (float) $consumption['qty'];
            $unitCost = (float) ($consumption['unit_cost_snapshot'] ?? 0);

            if ($direction === 'increase') {
                $newQty = $currentQty + $step;
                sqlStatement(
                    "UPDATE or_consumptions
                    SET qty = ?,
                        total_cost_snapshot = ?
                    WHERE id = ?",
                    array($newQty, ($newQty * $unitCost), $consumptionId)
                );
                $flashMessage = xlt('Consumption quantity increased.');
            } else {
                $newQty = $currentQty - $step;
                if ($newQty <= 0) {
                    sqlStatement("DELETE FROM or_consumptions WHERE id = ?", array($consumptionId));
                    $flashMessage = xlt('Consumption removed.');
                } else {
                    sqlStatement(
                        "UPDATE or_consumptions
                        SET qty = ?,
                            total_cost_snapshot = ?
                        WHERE id = ?",
                        array($newQty, ($newQty * $unitCost), $consumptionId)
                    );
                    $flashMessage = xlt('Consumption quantity decreased.');
                }
            }
        }
    }
}

$target = ($levelType !== '' && $levelId > 0) ? orConsumptionFetchTarget($levelType, $levelId) : null;
$existingConsumptions = !empty($target) ? orConsumptionFetchExisting($levelType, $levelId) : array();
$catalogMatches = !empty($target) ? orConsumptionSearchCatalog($searchTerm) : array();
?>
<html>
<head>
    <title>Consumos de Quirófano</title>
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
        .or-search-form td,
        .or-add-form td {
            padding: 7px 10px 7px 0;
            vertical-align: top;
        }
        .or-muted {
            color: #666;
        }
        .or-panel h3 {
            margin: 0 0 18px;
            font-size: 22px;
            font-weight: 700;
            color: #113a5c;
        }
        .or-target-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            border-radius: 999px;
            padding: 8px 14px;
            background: #e5f0fb;
            color: #175a96;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
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
            min-height: 40px;
            padding: 8px 13px;
            border-radius: 12px;
        }
        .or-panel .form-control {
            min-height: 48px;
            border-radius: 14px;
            border-color: #c8d7e4;
            box-shadow: none;
            font-size: 16px;
        }
        .or-panel textarea.form-control {
            min-height: 96px;
            resize: vertical;
        }
        .or-quick-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .or-quick-actions .btn {
            min-width: 60px;
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
            .or-search-form td,
            .or-add-form td,
            .or-search-form tr,
            .or-add-form tr,
            .or-search-form tbody,
            .or-add-form tbody {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body class="body_top">
<div class="or-shell">
<div class="or-page-header">
    <div>
        <p class="or-page-kicker">Quirófano</p>
        <h1 class="or-page-title">Consumos de Quirófano</h1>
        <p class="or-page-subtitle">Carga y ajusta insumos con una interfaz más rápida para tablet, con incremento directo desde la misma tabla.</p>
    </div>
    <?php if (!empty($target['back_link'])) { ?>
        <div>
            <a class="btn btn-default" href="<?php echo attr($target['back_link']); ?>">
                Volver
            </a>
        </div>
    <?php } ?>
</div>

<?php if ($flashMessage !== '') { ?>
    <div class="or-flash-ok"><?php echo text($flashMessage); ?></div>
<?php } ?>
<?php if ($flashError !== '') { ?>
    <div class="or-flash-error"><?php echo text($flashError); ?></div>
<?php } ?>

<?php if (empty($target)) { ?>
    <div class="or-panel">Selecciona primero un día, bloque o cirugía válido.</div>
<?php } else { ?>
    <div class="or-panel">
        <h3>Destino</h3>
        <p><strong><?php echo text($target['target_label']); ?></strong></p>
        <div class="or-target-pill">
            Nivel: <?php echo text($levelType); ?>
            <span>•</span>
            Estado: <?php echo text($target['status'] ?? ''); ?>
        </div>
    </div>

    <div class="or-panel">
        <h3>Buscar en catálogo</h3>
        <form method="get" action="or_consumptions.php">
            <input type="hidden" name="level_type" value="<?php echo attr($levelType); ?>"/>
            <input type="hidden" name="level_id" value="<?php echo attr((string) $levelId); ?>"/>
            <table class="or-search-form">
                <tr>
                    <td>Buscar</td>
                    <td><input type="text" class="form-control" name="search_term" value="<?php echo attr($searchTerm); ?>" placeholder="Código o descripción"/></td>
                    <td><button type="submit" class="btn btn-primary">Buscar</button></td>
                </tr>
            </table>
        </form>
    </div>

    <?php if ($searchTerm !== '') { ?>
        <div class="or-panel">
            <h3>Coincidencias del catálogo</h3>
            <table class="table table-bordered table-striped or-table">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Unidad</th>
                    <th>Por defecto</th>
                    <th><?php echo xlt('Lot'); ?></th>
                    <th><?php echo xlt('Serial'); ?></th>
                    <th>Agregar</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($catalogMatches as $item) { ?>
                    <tr>
                        <td><?php echo text($item['code']); ?></td>
                        <td><?php echo text($item['code_text']); ?></td>
                        <td><?php echo text($item['units']); ?></td>
                        <td><?php echo text($item['default_level']); ?></td>
                        <td><?php echo text(((int) ($item['requires_lot'] ?? 0)) ? 'Sí' : 'No'); ?></td>
                        <td><?php echo text(((int) ($item['requires_serial'] ?? 0)) ? 'Sí' : 'No'); ?></td>
                        <td>
                            <form method="post" action="or_consumptions.php?level_type=<?php echo urlencode($levelType); ?>&level_id=<?php echo urlencode((string) $levelId); ?>" onsubmit="return top.restoreSession()" style="margin:0;">
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
                                <input type="hidden" name="form_action" value="add_consumption"/>
                                <input type="hidden" name="form_level_type" value="<?php echo attr($levelType); ?>"/>
                                <input type="hidden" name="form_level_id" value="<?php echo attr((string) $levelId); ?>"/>
                                <input type="hidden" name="form_code_id" value="<?php echo attr((string) $item['id']); ?>"/>
                                <input type="hidden" name="form_search_term" value="<?php echo attr($searchTerm); ?>"/>
                                <table class="or-add-form">
                                    <tr>
                                        <td><input type="text" class="form-control" name="form_qty" value="1" style="width:70px;" placeholder="Cant."/></td>
                                        <td><input type="text" class="form-control" name="form_lot_number" style="width:130px;" placeholder="<?php echo attr(xl('Lot')); ?>"/></td>
                                        <td><input type="text" class="form-control" name="form_serial_number" style="width:130px;" placeholder="<?php echo attr(xl('Serial')); ?>"/></td>
                                        <td><input type="text" class="form-control" name="form_notes" style="width:180px;" placeholder="Notas"/></td>
                                        <td><button type="submit" class="btn btn-xs btn-primary">Agregar / aumentar</button></td>
                                    </tr>
                                </table>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <div class="or-panel">
        <h3>Consumos registrados</h3>
        <table class="table table-bordered table-striped or-table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Lote</th>
                <th>Notas</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($existingConsumptions as $consumption) { ?>
                <tr>
                    <td><?php echo text($consumption['created_at']); ?></td>
                    <td><?php echo text($consumption['code_snapshot']); ?></td>
                    <td><?php echo text($consumption['description_snapshot']); ?></td>
                    <td><?php echo text($consumption['qty']); ?></td>
                    <td><?php echo text($consumption['lot_number']); ?></td>
                    <td><?php echo text($consumption['notes']); ?></td>
                    <td>
                        <div class="or-quick-actions">
                            <form method="post" action="or_consumptions.php?level_type=<?php echo urlencode($levelType); ?>&level_id=<?php echo urlencode((string) $levelId); ?>" onsubmit="return top.restoreSession()" style="display:inline-block; margin:0;">
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
                                <input type="hidden" name="form_action" value="adjust_consumption_qty"/>
                                <input type="hidden" name="form_level_type" value="<?php echo attr($levelType); ?>"/>
                                <input type="hidden" name="form_level_id" value="<?php echo attr((string) $levelId); ?>"/>
                                <input type="hidden" name="form_consumption_id" value="<?php echo attr((string) $consumption['id']); ?>"/>
                                <input type="hidden" name="form_direction" value="increase"/>
                                <button type="submit" class="btn btn-xs btn-success">+1</button>
                            </form>
                            <form method="post" action="or_consumptions.php?level_type=<?php echo urlencode($levelType); ?>&level_id=<?php echo urlencode((string) $levelId); ?>" onsubmit="return top.restoreSession()" style="display:inline-block; margin:0;">
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
                                <input type="hidden" name="form_action" value="adjust_consumption_qty"/>
                                <input type="hidden" name="form_level_type" value="<?php echo attr($levelType); ?>"/>
                                <input type="hidden" name="form_level_id" value="<?php echo attr((string) $levelId); ?>"/>
                                <input type="hidden" name="form_consumption_id" value="<?php echo attr((string) $consumption['id']); ?>"/>
                                <input type="hidden" name="form_direction" value="decrease"/>
                                <button type="submit" class="btn btn-xs btn-danger">-1</button>
                            </form>
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
