<?php
/**
 * Surgical supply catalog sync and review.
 *
 * Seeds and reviews or_supply_catalog records from codes without
 * requiring manual inserts for every supply item.
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

function orSupplyFetchAffectedRows()
{
    $row = sqlQuery("SELECT ROW_COUNT() AS affected");
    return (int) ($row['affected'] ?? 0);
}

function orSupplyNormalizeSearch($value)
{
    return trim((string) $value);
}

function orSupplySyncCatalog($codeType)
{
    $summary = array(
        'inserted' => 0,
        'classified_lens' => 0,
        'classified_visco' => 0,
        'classified_day' => 0,
    );

    sqlStatement(
        "INSERT INTO or_supply_catalog (
            code_id,
            default_level,
            requires_lot,
            requires_serial,
            is_reusable,
            is_template_enabled,
            specialty_scope,
            active
        )
        SELECT
            c.id,
            'flexible',
            0,
            0,
            0,
            1,
            'all',
            1
        FROM codes AS c
        LEFT JOIN or_supply_catalog AS osc ON osc.code_id = c.id
        WHERE c.code_type = ?
          AND c.active = 1
          AND osc.code_id IS NULL",
        array($codeType)
    );
    $summary['inserted'] = orSupplyFetchAffectedRows();

    sqlStatement(
        "UPDATE or_supply_catalog AS osc
        INNER JOIN codes AS c ON c.id = osc.code_id
        SET
            osc.default_level = 'surgery',
            osc.requires_lot = 1,
            osc.is_template_enabled = 1,
            osc.specialty_scope = 'all'
        WHERE c.code_type = ?
          AND c.active = 1
          AND UPPER(c.code_text) LIKE '%LENTE INTRAOCULAR%'",
        array($codeType)
    );
    $summary['classified_lens'] = orSupplyFetchAffectedRows();

    sqlStatement(
        "UPDATE or_supply_catalog AS osc
        INNER JOIN codes AS c ON c.id = osc.code_id
        SET
            osc.default_level = 'surgery',
            osc.is_template_enabled = 1
        WHERE c.code_type = ?
          AND c.active = 1
          AND (
            UPPER(c.code_text) LIKE '%VISCO%'
            OR UPPER(c.code_text) LIKE '%TRYPAN%'
            OR UPPER(c.code_text) LIKE '%SUTURA%'
            OR UPPER(c.code_text) LIKE '%ACEITE%'
            OR UPPER(c.code_text) LIKE '%GAS%'
          )",
        array($codeType)
    );
    $summary['classified_visco'] = orSupplyFetchAffectedRows();

    sqlStatement(
        "UPDATE or_supply_catalog AS osc
        INNER JOIN codes AS c ON c.id = osc.code_id
        SET
            osc.default_level = 'day',
            osc.is_template_enabled = 1
        WHERE c.code_type = ?
          AND c.active = 1
          AND (
            UPPER(c.code_text) LIKE '%BSS%'
            OR UPPER(c.code_text) LIKE '%CAMPO%'
            OR UPPER(c.code_text) LIKE '%SOLUCION%'
            OR UPPER(c.code_text) LIKE '%GUANTE%'
            OR UPPER(c.code_text) LIKE '%PACK%'
          )",
        array($codeType)
    );
    $summary['classified_day'] = orSupplyFetchAffectedRows();

    return $summary;
}

$formRefresh = !empty($_POST['form_refresh']);
$formAction = $_POST['form_action'] ?? '';
$formCodeType = isset($_POST['form_code_type']) && ctype_digit((string) $_POST['form_code_type'])
    ? (int) $_POST['form_code_type']
    : 115;
$formSearch = orSupplyNormalizeSearch($_POST['form_search'] ?? '');
$flashMessage = '';
$flashSummary = array();

if ($formAction === 'sync') {
    $flashSummary = orSupplySyncCatalog($formCodeType);
    $flashMessage = xlt('Catalog synchronized from codes.');
    $formRefresh = true;
}

$stats = array(
    'codes_count' => 0,
    'catalog_count' => 0,
    'active_catalog_count' => 0,
);

$statsRow = sqlQuery(
    "SELECT
        SUM(CASE WHEN c.code_type = ? AND c.active = 1 THEN 1 ELSE 0 END) AS codes_count,
        SUM(CASE WHEN c.code_type = ? AND osc.code_id IS NOT NULL THEN 1 ELSE 0 END) AS catalog_count,
        SUM(CASE WHEN c.code_type = ? AND osc.active = 1 THEN 1 ELSE 0 END) AS active_catalog_count
    FROM codes AS c
    LEFT JOIN or_supply_catalog AS osc ON osc.code_id = c.id",
    array($formCodeType, $formCodeType, $formCodeType)
);

if (!empty($statsRow)) {
    $stats['codes_count'] = (int) ($statsRow['codes_count'] ?? 0);
    $stats['catalog_count'] = (int) ($statsRow['catalog_count'] ?? 0);
    $stats['active_catalog_count'] = (int) ($statsRow['active_catalog_count'] ?? 0);
}

$binds = array($formCodeType);
$where = " WHERE c.code_type = ? ";

if ($formSearch !== '') {
    $where .= " AND (
        c.code LIKE CONCAT('%', ?, '%')
        OR c.code_text LIKE CONCAT('%', ?, '%')
        OR COALESCE(c.code_text_short, '') LIKE CONCAT('%', ?, '%')
    )";
    $binds[] = $formSearch;
    $binds[] = $formSearch;
    $binds[] = $formSearch;
}

$catalogSql = "SELECT
        c.id,
        c.code,
        c.code_text,
        c.code_text_short,
        c.units,
        c.fee,
        c.active AS code_active,
        osc.default_level,
        osc.requires_lot,
        osc.requires_serial,
        osc.is_reusable,
        osc.active AS catalog_active
    FROM codes AS c
    LEFT JOIN or_supply_catalog AS osc ON osc.code_id = c.id
    " . $where . "
    ORDER BY c.code_text
    LIMIT 200";

$catalogRes = $formRefresh ? sqlStatement($catalogSql, $binds) : null;
?>
<html>
<head>
    <title>Catálogo de Insumos de Quirófano</title>
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
        .or-toolbar {
            border: 1px solid rgba(187, 203, 219, 0.72);
            border-radius: 24px;
            padding: 20px 22px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(28, 74, 106, 0.09);
            backdrop-filter: blur(8px);
        }
        .or-toolbar-table td {
            padding: 8px 10px 8px 0;
            vertical-align: middle;
        }
        .or-toolbar-table td:first-child,
        .or-toolbar-table td:nth-child(3) {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #5f7386;
        }
        .or-summary-card {
            display: inline-block;
            min-width: 180px;
            margin: 0 12px 12px 0;
            padding: 14px 16px;
            border: 1px solid rgba(187, 203, 219, 0.72);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 14px 30px rgba(28, 74, 106, 0.08);
        }
        .or-summary-card strong {
            display: block;
            font-size: 24px;
            margin-top: 4px;
        }
        .or-flash {
            margin: 12px 0;
            padding: 14px 16px;
            border-radius: 18px;
            background: #eef7ee;
            border: 1px solid #b9d7b9;
            box-shadow: 0 12px 24px rgba(91, 154, 96, 0.12);
        }
        .or-help {
            margin: 12px 0 16px;
            color: #5c7388;
            font-size: 14px;
            line-height: 1.5;
        }
        .or-table {
            margin-top: 12px;
            border-radius: 18px;
            overflow: hidden;
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
        .or-pill {
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
        .or-toolbar .btn,
        .or-toolbar .form-control {
            min-height: 46px;
            border-radius: 14px;
        }
        .or-toolbar .btn {
            padding: 10px 16px;
            font-weight: 700;
        }
        .or-toolbar .form-control {
            border-color: #c8d7e4;
            font-size: 16px;
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
            .or-toolbar {
                padding: 16px;
                border-radius: 20px;
            }
            .or-toolbar-table td,
            .or-toolbar-table tr,
            .or-toolbar-table tbody {
                display: block;
                width: 100%;
            }
            .or-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
        function submitCatalogForm(actionName) {
            document.getElementById('form_action').value = actionName;
            document.getElementById('form_refresh').value = '1';
            document.getElementById('catalog_form').submit();
        }
    </script>
</head>
<body class="body_top">
<div class="or-shell">
<div class="or-page-header">
    <div>
        <p class="or-page-kicker">Quirófano</p>
        <h1 class="or-page-title">Catálogo de Insumos de Quirófano</h1>
        <p class="or-page-subtitle">Sincroniza insumos desde codes y revisa rápidamente qué queda habilitado para quirófano.</p>
    </div>
</div>

<form method="post" id="catalog_form" action="or_supply_catalog.php" onsubmit="return top.restoreSession()">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
    <input type="hidden" name="form_refresh" id="form_refresh" value="<?php echo attr($formRefresh ? '1' : ''); ?>"/>
    <input type="hidden" name="form_action" id="form_action" value=""/>

    <div class="or-toolbar">
    <table class="or-toolbar-table">
        <tr>
            <td>Tipo de código:</td>
            <td><input type="text" class="form-control" name="form_code_type" value="<?php echo attr((string) $formCodeType); ?>"/></td>
            <td>Buscar:</td>
            <td><input type="text" class="form-control" name="form_search" value="<?php echo attr($formSearch); ?>" placeholder="Código o descripción"/></td>
            <td>
                <a href="#" class="btn btn-default btn-save" onclick="submitCatalogForm('refresh'); return false;">
                    Buscar
                </a>
            </td>
            <td>
                <a href="#" class="btn btn-primary btn-save" onclick="submitCatalogForm('sync'); return false;">
                    Sincronizar desde codes
                </a>
            </td>
        </tr>
    </table>
    </div>

    <div class="or-help">
        Esta pantalla carga los insumos quirúrgicos desde `codes` hacia `or_supply_catalog` y aplica reglas básicas de clasificación para lentes, viscoelásticos e insumos comunes de uso diario.
    </div>

    <?php if ($flashMessage !== '') { ?>
        <div class="or-flash">
            <strong><?php echo text($flashMessage); ?></strong>
            Insertados: <?php echo text((string) $flashSummary['inserted']); ?> |
            Lentes clasificados: <?php echo text((string) $flashSummary['classified_lens']); ?> |
            Insumos de cirugía clasificados: <?php echo text((string) $flashSummary['classified_visco']); ?> |
            Insumos de día clasificados: <?php echo text((string) $flashSummary['classified_day']); ?>
        </div>
    <?php } ?>

    <div>
        <div class="or-summary-card">
            Códigos activos
            <strong><?php echo text((string) $stats['codes_count']); ?></strong>
        </div>
        <div class="or-summary-card">
            Filas del catálogo
            <strong><?php echo text((string) $stats['catalog_count']); ?></strong>
        </div>
        <div class="or-summary-card">
            Activos en catálogo OR
            <strong><?php echo text((string) $stats['active_catalog_count']); ?></strong>
        </div>
    </div>

    <?php if ($formRefresh && $catalogRes) { ?>
        <table class="table table-bordered table-striped or-table">
            <thead>
            <tr>
                <th><?php echo xlt('ID'); ?></th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Corto</th>
                <th>Unidad</th>
                <th>Nivel por defecto</th>
                <th><?php echo xlt('Lot'); ?></th>
                <th><?php echo xlt('Serial'); ?></th>
                <th>Reutilizable</th>
                <th>Catálogo</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = sqlFetchArray($catalogRes)) { ?>
                <tr>
                    <td><?php echo text($row['id']); ?></td>
                    <td><?php echo text($row['code']); ?></td>
                    <td><?php echo text($row['code_text']); ?></td>
                    <td><?php echo text($row['code_text_short']); ?></td>
                    <td><?php echo text($row['units']); ?></td>
                    <td><span class="or-pill"><?php echo text($row['default_level'] ?: 'sin sembrar'); ?></span></td>
                    <td><?php echo text(((int) ($row['requires_lot'] ?? 0)) ? 'Sí' : 'No'); ?></td>
                    <td><?php echo text(((int) ($row['requires_serial'] ?? 0)) ? 'Sí' : 'No'); ?></td>
                    <td><?php echo text(((int) ($row['is_reusable'] ?? 0)) ? 'Sí' : 'No'); ?></td>
                    <td><span class="or-pill"><?php echo text(isset($row['catalog_active']) ? (((int) $row['catalog_active']) ? 'Activo' : 'Inactivo') : 'Falta'); ?></span></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</form>
</div>
</body>
</html>
