<?php
/**
 * Custom invoice range report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FacilityService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!acl_check('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}

$facilityService = new FacilityService();

$startdate = empty($_POST['start']) ? date('Y-m-d', (time() - 30 * 24 * 60 * 60)) : DateToYYYYMMDD($_POST['start']);
$enddate = empty($_POST['end']) ? date('Y-m-d') : DateToYYYYMMDD($_POST['end']);
$form_patient = isset($_POST['form_patient']) ? trim($_POST['form_patient']) : '';
$form_pid = isset($_POST['form_pid']) ? trim($_POST['form_pid']) : '';
$form_facility = isset($_POST['form_facility']) ? trim($_POST['form_facility']) : '';
$form_provider = isset($_POST['form_provider']) ? trim($_POST['form_provider']) : '';
$form_pricelevel = isset($_POST['form_pricelevel']) ? trim($_POST['form_pricelevel']) : '';
$form_vendor = isset($_POST['form_vendor']) ? trim($_POST['form_vendor']) : '';
$form_invoice = isset($_POST['form_invoice']) ? trim($_POST['form_invoice']) : '';
$form_code_type = isset($_POST['form_code_type']) ? trim($_POST['form_code_type']) : '';
$form_refresh = !empty($_POST['form_refresh']);

if ($form_patient === '') {
    $form_pid = '';
}

function reportMoney($amount)
{
    return oeFormatMoney((float) $amount);
}

function invoiceDisplayNumber($row)
{
    if (!empty($row['invoice_refno'])) {
        return $row['invoice_refno'];
    }

    return $row['pid'] . '.' . $row['encounter'];
}

function patientDisplayName($row)
{
    $name = trim($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']);
    return $name === ',' ? '' : $name;
}

function providerDisplayName($row)
{
    $name = trim($row['provider_lname'] . ', ' . $row['provider_fname']);
    return $name === ',' ? '' : $name;
}

function pricelevelTitle($pricelevel)
{
    static $cache = array();

    if ($pricelevel === '') {
        return '';
    }

    if (!array_key_exists($pricelevel, $cache)) {
        $row = sqlQuery(
            "SELECT title FROM list_options WHERE list_id = 'pricelevel' AND option_id = ? AND activity = 1 LIMIT 1",
            array($pricelevel)
        );
        $cache[$pricelevel] = !empty($row['title']) ? $row['title'] : $pricelevel;
    }

    return $cache[$pricelevel];
}

function codeTypeClass($codeType)
{
    $codeType = strtoupper(trim((string) $codeType));
    if (strpos($codeType, 'CPT4A') === 0) {
        return 'code-anesthesia';
    }

    if (strpos($codeType, 'CPT42') === 0) {
        return 'code-secondary';
    }

    if (strpos($codeType, 'CPT4') === 0) {
        return 'code-procedure';
    }

    if ($codeType === 'RXCUI') {
        return 'code-product';
    }

    if ($codeType === 'INSUM') {
        return 'code-supply';
    }

    return 'code-other';
}

function productCodeClass($name, $fee)
{
    if ((float) $fee == 0.00) {
        return 'code-zero';
    }

    return 'code-product';
}

function isProcedureCategory($category)
{
    return strtoupper(trim((string) $category)) === 'PROCED';
}

function normalizedCategory($category)
{
    return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $category)));
}

function billingGroup($line)
{
    $codeType = strtoupper(trim((string) $line['code_type']));
    $category = normalizedCategory(isset($line['code_category']) ? $line['code_category'] : '');

    if ($codeType === 'CPT4' && $category === 'PROCED') {
        return array('rank' => 10, 'label' => '1. CPT4 Proced', 'class' => 'group-procedure');
    }

    if ($codeType === 'CPT42') {
        return array('rank' => 20, 'label' => '2. CPT42 Ayudante', 'class' => 'group-secondary');
    }

    if ($codeType === 'CPT4A') {
        return array('rank' => 30, 'label' => '3. CPT4A Anestesia', 'class' => 'group-anesthesia');
    }

    if ($codeType === 'CPT4' && ($category === 'DERECHOSALA' || $category === 'MATERIALFUNGIBLE')) {
        return array('rank' => 40, 'label' => '4. CPT4 DerechoSala / MaterialFungible', 'class' => 'group-room-material');
    }

    if ($codeType === 'INSUM') {
        return array('rank' => 50, 'label' => '5. INSUM', 'class' => 'group-supply');
    }

    if ($codeType === 'CPT4' && $category === 'EQUIPOS') {
        return array('rank' => 60, 'label' => '6. CPT4 Equipos', 'class' => 'group-equipment');
    }

    if ($codeType === 'RXCUI') {
        return array('rank' => 70, 'label' => '7. RXCUI', 'class' => 'group-product');
    }

    return array('rank' => 90, 'label' => xl('Other'), 'class' => 'group-other');
}

function sortBillingLinesForDisplay($lines)
{
    foreach ($lines as $idx => $line) {
        $group = billingGroup($line);
        $lines[$idx]['display_group_rank'] = $group['rank'];
        $lines[$idx]['display_group_label'] = $group['label'];
        $lines[$idx]['display_group_class'] = $group['class'];
        $lines[$idx]['display_original_index'] = $idx;
    }

    usort($lines, function ($left, $right) {
        if ($left['display_group_rank'] != $right['display_group_rank']) {
            return $left['display_group_rank'] < $right['display_group_rank'] ? -1 : 1;
        }

        if ($left['display_original_index'] == $right['display_original_index']) {
            return 0;
        }

        return $left['display_original_index'] < $right['display_original_index'] ? -1 : 1;
    });

    return $lines;
}

function billingFactor($codeType, $sequenceByType, $category)
{
    $codeType = strtoupper(trim((string) $codeType));
    if ($codeType === 'CPT4') {
        if (!isProcedureCategory($category)) {
            return 1.00;
        }

        return $sequenceByType === 1 ? 1.00 : 0.50;
    }

    if ($codeType === 'CPT42') {
        return $sequenceByType === 1 ? 0.20 : 0.10;
    }

    if ($codeType === 'CPT4A') {
        return 1.00;
    }

    return 1.00;
}

function formatFactor($factor)
{
    return rtrim(rtrim(number_format((float) $factor, 2, '.', ''), '0'), '.') . 'x';
}

function applyBillingRules($lines)
{
    $sequenceByType = array();
    foreach ($lines as $idx => $line) {
        $codeType = strtoupper(trim((string) $line['code_type']));
        $category = isset($line['code_category']) ? $line['code_category'] : '';
        $usesSequence = ($codeType === 'CPT42') || ($codeType === 'CPT4' && isProcedureCategory($category));
        if ($usesSequence && !isset($sequenceByType[$codeType])) {
            $sequenceByType[$codeType] = 0;
        }

        if ($usesSequence) {
            ++$sequenceByType[$codeType];
            $sequence = $sequenceByType[$codeType];
        } else {
            $sequence = 0;
        }

        $factor = billingFactor($codeType, $sequence, $category);
        $units = (float) $line['units'];
        if ($units == 0.00) {
            $units = 1.00;
        }

        $base = (float) $line['fee'];
        $lines[$idx]['billing_factor'] = $factor;
        $lines[$idx]['billing_sequence'] = $sequence;
        $lines[$idx]['calculated_total'] = $base * $units * $factor;
    }

    return $lines;
}

function getBillingLines($pid, $encounter, $codeType = '', $pricelevel = '')
{
    $bind = array($pricelevel, $pid, $encounter);
    $query = "SELECT b.date, b.code_type, b.code, b.modifier, b.code_text, b.units, " .
        "b.fee AS stored_fee, COALESCE(pr.pr_price, b.fee) AS fee, b.pricelevel AS line_pricelevel, " .
        "c.id AS codes_id, " .
        "COALESCE(NULLIF(c.superbill, ''), '') AS code_category, " .
        "CONCAT(COALESCE(NULLIF(u.fname, ''), eu.fname, ''), ' ', COALESCE(NULLIF(u.lname, ''), eu.lname, '')) AS provider_name " .
        "FROM billing AS b " .
        "LEFT JOIN code_types AS ct ON ct.ct_key = b.code_type " .
        "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND COALESCE(c.modifier, '') = COALESCE(b.modifier, '') " .
        "LEFT JOIN prices AS pr ON pr.pr_id = c.id AND pr.pr_selector = '' AND pr.pr_level = ? " .
        "LEFT JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
        "LEFT JOIN users AS u ON u.id = b.provider_id " .
        "LEFT JOIN users AS eu ON eu.id = fe.provider_id " .
        "WHERE b.pid = ? AND b.encounter = ? AND b.activity = 1 AND b.code_type != 'COPAY' ";

    if ($codeType !== '') {
        $query .= "AND b.code_type = ? ";
        $bind[] = $codeType;
    }

    $query .= "ORDER BY b.date, b.id";
    $res = sqlStatement($query, $bind);
    $lines = array();
    while ($row = sqlFetchArray($res)) {
        $lines[] = $row;
    }

    return $lines;
}

function getProductLines($pid, $encounter, $vendorId = '', $pricelevel = '')
{
    $bind = array($pricelevel, $pid, $encounter);
    $query = "SELECT s.sale_date, s.quantity, s.fee AS stored_fee, COALESCE(pr.pr_price, s.fee) AS fee, s.pricelevel, d.name, di.lot_number, di.vendor_id, " .
        "COALESCE(NULLIF(v.organization, ''), CONCAT(v.lname, ', ', v.fname)) AS vendor_name " .
        "FROM drug_sales AS s " .
        "JOIN drugs AS d ON d.drug_id = s.drug_id " .
        "LEFT JOIN drug_inventory AS di ON di.inventory_id = s.inventory_id " .
        "LEFT JOIN users AS v ON v.id = di.vendor_id " .
        "LEFT JOIN prices AS pr ON pr.pr_id = s.drug_id AND pr.pr_selector = s.selector AND pr.pr_level = ? " .
        "WHERE s.pid = ? AND s.encounter = ? AND (s.fee != 0 OR pr.pr_price IS NOT NULL) ";

    if ($vendorId !== '') {
        $query .= "AND di.vendor_id = ? ";
        $bind[] = $vendorId;
    }

    $query .= "ORDER BY s.sale_date, s.sale_id";
    $res = sqlStatement($query, $bind);
    $lines = array();
    while ($row = sqlFetchArray($res)) {
        $lines[] = $row;
    }

    return $lines;
}

function sumLines($lines)
{
    $total = 0.00;
    foreach ($lines as $line) {
        if (isset($line['calculated_total'])) {
            $total += (float) $line['calculated_total'];
        } else {
            $total += (float) $line['fee'];
        }
    }

    return $total;
}

function buildInvoiceRows($startdate, $enddate, $filters)
{
    $bind = array($startdate . ' 00:00:00', $enddate . ' 23:59:59');
    $query = "SELECT fe.pid, fe.encounter, fe.date, fe.facility_id, fe.invoice_refno, " .
        "p.pubpid, p.fname, p.mname, p.lname, p.pricelevel, " .
        "COALESCE(NULLIF(pl.title, ''), NULLIF(p.pricelevel, ''), '') AS pricelevel_title, " .
        "u.fname AS provider_fname, u.lname AS provider_lname, f.name AS facility_name " .
        "FROM form_encounter AS fe " .
        "JOIN patient_data AS p ON p.pid = fe.pid " .
        "LEFT JOIN users AS u ON u.id = fe.provider_id " .
        "LEFT JOIN facility AS f ON f.id = fe.facility_id " .
        "LEFT JOIN list_options AS pl ON pl.list_id = 'pricelevel' AND pl.option_id = p.pricelevel AND pl.activity = 1 " .
        "WHERE fe.date >= ? AND fe.date <= ? ";

    if ($filters['pid'] !== '') {
        $query .= "AND fe.pid = ? ";
        $bind[] = $filters['pid'];
    }

    if ($filters['facility'] !== '') {
        $query .= "AND fe.facility_id = ? ";
        $bind[] = $filters['facility'];
    }

    if ($filters['provider'] !== '') {
        $query .= "AND fe.provider_id = ? ";
        $bind[] = $filters['provider'];
    }

    if ($filters['pricelevel'] !== '') {
        $query .= "AND (p.pricelevel = ? OR EXISTS (SELECT 1 FROM billing AS bx WHERE bx.pid = fe.pid AND bx.encounter = fe.encounter AND bx.activity = 1 AND bx.pricelevel = ?) " .
            "OR EXISTS (SELECT 1 FROM drug_sales AS sx WHERE sx.pid = fe.pid AND sx.encounter = fe.encounter AND sx.pricelevel = ?)) ";
        $bind[] = $filters['pricelevel'];
        $bind[] = $filters['pricelevel'];
        $bind[] = $filters['pricelevel'];
    }

    if ($filters['invoice'] !== '') {
        $query .= "AND (fe.invoice_refno LIKE ? OR CONCAT(fe.pid, '.', fe.encounter) LIKE ?) ";
        $bind[] = '%' . $filters['invoice'] . '%';
        $bind[] = '%' . $filters['invoice'] . '%';
    }

    if ($filters['code_type'] !== '') {
        $query .= "AND EXISTS (SELECT 1 FROM billing AS bc WHERE bc.pid = fe.pid AND bc.encounter = fe.encounter AND bc.activity = 1 AND bc.code_type = ?) ";
        $bind[] = $filters['code_type'];
    }

    if ($filters['vendor'] !== '') {
        $query .= "AND EXISTS (SELECT 1 FROM drug_sales AS sv JOIN drug_inventory AS div ON div.inventory_id = sv.inventory_id " .
            "WHERE sv.pid = fe.pid AND sv.encounter = fe.encounter AND div.vendor_id = ?) ";
        $bind[] = $filters['vendor'];
    }

    $query .= "ORDER BY fe.date DESC, fe.id DESC";

    $res = sqlStatement($query, $bind);
    $rows = array();
    while ($row = sqlFetchArray($res)) {
        $effectivePricelevel = $filters['pricelevel'] !== '' ? $filters['pricelevel'] : $row['pricelevel'];
        $billingLines = sortBillingLinesForDisplay(applyBillingRules(getBillingLines($row['pid'], $row['encounter'], '', $effectivePricelevel)));
        $productLines = getProductLines($row['pid'], $row['encounter'], '', $effectivePricelevel);
        $billingTotal = sumLines($billingLines);
        $productTotal = sumLines($productLines);

        $copay = abs((float) BillingUtilities::getPatientCopay($row['pid'], $row['encounter']));
        $row['billing_lines'] = $billingLines;
        $row['product_lines'] = $productLines;
        $row['billing_total'] = $billingTotal;
        $row['product_total'] = $productTotal;
        $row['charges_total'] = $billingTotal + $productTotal;
        $row['copay_total'] = $copay;
        $row['invoice_total'] = $billingTotal + $productTotal;
        $row['effective_pricelevel'] = $effectivePricelevel;
        $row['effective_pricelevel_title'] = pricelevelTitle($effectivePricelevel);
        $rows[] = $row;
    }

    return $rows;
}

$filters = array(
    'pid' => $form_pid,
    'facility' => $form_facility,
    'provider' => $form_provider,
    'pricelevel' => $form_pricelevel,
    'vendor' => $form_vendor,
    'invoice' => $form_invoice,
    'code_type' => $form_code_type,
);
$invoiceRows = $form_refresh ? buildInvoiceRows($startdate, $enddate, $filters) : array();
?>
<html>
<head>
    <title><?php echo xlt('Custom Invoice Report'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css">
    <style>
        @media print {
            #report_parameters { display: none; }
            details.report-detail { display: block; }
            details.report-detail > summary { display: none; }
        }
        table.report-table, table.report-table td, table.report-table th {
            border: 1px solid #aaaaaa;
            border-collapse: collapse;
        }
        table.report-table td, table.report-table th {
            padding: 4px 6px;
            vertical-align: top;
        }
        table.report-table th {
            background: #e9edf2;
            color: #1f2933;
            font-weight: bold;
        }
        .summary-row {
            background: #f8fafc;
        }
        .summary-row:nth-child(even) {
            background: #ffffff;
        }
        .summary-row:hover {
            background: #eef6ff;
        }
        .detail-table {
            margin: 8px 0 10px 0;
            width: 100%;
        }
        .detail-table td {
            line-height: 1.35;
        }
        .amount {
            text-align: right;
            white-space: nowrap;
        }
        .money-strong {
            font-weight: bold;
            color: #0f5132;
        }
        .filters td {
            padding: 3px 5px;
        }
        details.report-detail summary {
            cursor: pointer;
            color: #0000cc;
            font-weight: bold;
            white-space: nowrap;
        }
        .invoice-link {
            font-weight: bold;
            color: #1d4ed8;
        }
        .muted {
            color: #667085;
            font-size: 0.9em;
        }
        .detail-panel {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-top: 8px;
            padding: 10px;
            min-width: 900px;
        }
        .detail-title {
            display: inline-block;
            margin: 4px 0;
            color: #1f2933;
        }
        .code-chip {
            display: inline-block;
            min-width: 52px;
            padding: 2px 7px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.86em;
            text-align: center;
            color: #1f2933;
            border: 1px solid transparent;
        }
        .code-text {
            display: block;
            margin-top: 3px;
        }
        .code-number {
            font-weight: bold;
            white-space: nowrap;
        }
        .description-cell {
            max-width: 620px;
        }
        .code-procedure {
            background: #e8f1ff;
        }
        .code-procedure .code-chip {
            background: #cfe2ff;
            border-color: #93c5fd;
            color: #0b3b75;
        }
        .code-secondary {
            background: #fbe3ff;
        }
        .code-secondary .code-chip {
            background: #f2c2fb;
            border-color: #a5b4fc;
            color: #3730a3;
        }
        .code-anesthesia {
            background: #fff4e6;
        }
        .code-anesthesia .code-chip {
            background: #fed7aa;
            border-color: #fdba74;
            color: #7c2d12;
        }
        .code-product {
            background: #ecfdf3;
        }
        .code-product .code-chip {
            background: #bbf7d0;
            border-color: #86efac;
            color: #14532d;
        }
        .code-supply {
            background: #f0fdfa;
        }
        .code-supply .code-chip {
            background: #ccfbf1;
            border-color: #5eead4;
            color: #134e4a;
        }
        .code-zero {
            background: #f8fafc;
            color: #64748b;
        }
        .code-zero .code-chip {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #475569;
        }
        .code-other {
            background: #f7f7f7;
        }
        .code-other .code-chip {
            background: #e5e7eb;
            border-color: #d1d5db;
            color: #374151;
        }
        .legend {
            margin: 10px 0;
        }
        .legend .code-chip {
            margin-right: 4px;
        }
        .legend span {
            margin-right: 12px;
            white-space: nowrap;
        }
        .rule-note {
            color: #475569;
            margin: 4px 0 10px 0;
        }
        .billing-group-row td {
            border-top: 2px solid #94a3b8;
            color: #111827;
            font-weight: bold;
            padding: 7px 8px;
        }
        .group-procedure td {
            background: #dbeafe;
        }
        .group-secondary td {
            background: #e0e7ff;
        }
        .group-anesthesia td {
            background: #ffedd5;
        }
        .group-room-material td {
            background: #fef3c7;
        }
        .group-product td {
            background: #dcfce7;
        }
        .group-equipment td {
            background: #ede9fe;
        }
        .group-supply td {
            background: #ccfbf1;
        }
        .group-other td {
            background: #f1f5f9;
        }
        .summary-cards {
            display: table;
            width: 98%;
            margin: 10px 0;
            border-spacing: 8px 0;
        }
        .summary-card {
            display: table-cell;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #2563eb;
            padding: 8px 10px;
            min-width: 130px;
        }
        .summary-card .label {
            color: #64748b;
            display: block;
            font-size: 0.9em;
        }
        .summary-card .value {
            color: #111827;
            display: block;
            font-size: 1.15em;
            font-weight: bold;
            margin-top: 2px;
        }
    </style>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js"></script>
    <script language="Javascript">
        $(function() {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });

        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }

        function setpatient(pid, lname, fname, dob) {
            var f = document.theform;
            f.form_patient.value = lname + ', ' + fname;
            f.form_pid.value = pid;
        }
    </script>
</head>

<body class="body_top">
<span class="title"><?php echo xlt('Reports'); ?> - <?php echo xlt('Custom Invoice Report'); ?></span>

<form method="post" name="theform" id="theform" action="custom_report_range.php" onsubmit="return top.restoreSession()">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type="hidden" name="form_refresh" id="form_refresh" value="" />

    <div id="report_parameters">
        <table>
            <tr>
                <td>
                    <table class="text filters">
                        <tr>
                            <td class="label_custom"><?php echo xlt('Start Date'); ?>:</td>
                            <td><input type="text" class="datepicker" name="start" id="form_from_date" size="10" value="<?php echo attr(oeFormatShortDate($startdate)); ?>"></td>
                            <td class="label_custom"><?php echo xlt('End Date'); ?>:</td>
                            <td><input type="text" class="datepicker" name="end" id="form_to_date" size="10" value="<?php echo attr(oeFormatShortDate($enddate)); ?>"></td>
                            <td class="label_custom"><?php echo xlt('Patient'); ?>:</td>
                            <td>
                                <input type="text" size="22" name="form_patient" style="cursor:pointer" value="<?php echo ($form_patient !== '') ? attr($form_patient) : xla('Click To Select'); ?>" onclick="sel_patient()" title="<?php echo xla('Click to select patient'); ?>" />
                                <input type="hidden" name="form_pid" value="<?php echo attr($form_pid); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label_custom"><?php echo xlt('Facility'); ?>:</td>
                            <td>
                                <?php dropdown_facility($form_facility, 'form_facility', true); ?>
                            </td>
                            <td class="label_custom"><?php echo xlt('Provider'); ?>:</td>
                            <td>
                                <select name="form_provider">
                                    <option value="">-- <?php echo xlt('All Providers'); ?> --</option>
                                    <?php
                                    $pres = sqlStatement("SELECT id, lname, fname FROM users WHERE authorized = 1 ORDER BY lname, fname");
                                    while ($prow = sqlFetchArray($pres)) {
                                        $selected = ((string) $form_provider === (string) $prow['id']) ? ' selected' : '';
                                        echo "<option value='" . attr($prow['id']) . "'" . $selected . ">" . text($prow['lname']) . ", " . text($prow['fname']) . "</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="label_custom"><?php echo xlt('Price Level'); ?>:</td>
                            <td>
                                <select name="form_pricelevel">
                                    <option value="">-- <?php echo xlt('All'); ?> --</option>
                                    <?php
                                    $priceRes = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
                                    while ($priceRow = sqlFetchArray($priceRes)) {
                                        $selected = ((string) $form_pricelevel === (string) $priceRow['option_id']) ? ' selected' : '';
                                        echo "<option value='" . attr($priceRow['option_id']) . "'" . $selected . ">" . text($priceRow['title']) . "</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label_custom"><?php echo xlt('Product Vendor'); ?>:</td>
                            <td>
                                <select name="form_vendor">
                                    <option value="">-- <?php echo xlt('All'); ?> --</option>
                                    <?php
                                    $vendorRes = sqlStatement("SELECT id, fname, lname, organization FROM users WHERE abook_type LIKE 'vendor%' ORDER BY organization, lname, fname");
                                    while ($vendorRow = sqlFetchArray($vendorRes)) {
                                        $vendorName = !empty($vendorRow['organization']) ? $vendorRow['organization'] : trim($vendorRow['lname'] . ', ' . $vendorRow['fname']);
                                        $selected = ((string) $form_vendor === (string) $vendorRow['id']) ? ' selected' : '';
                                        echo "<option value='" . attr($vendorRow['id']) . "'" . $selected . ">" . text($vendorName) . "</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="label_custom"><?php echo xlt('Invoice'); ?>:</td>
                            <td><input type="text" name="form_invoice" size="14" value="<?php echo attr($form_invoice); ?>"></td>
                            <td class="label_custom"><?php echo xlt('Code Type'); ?>:</td>
                            <td>
                                <select name="form_code_type">
                                    <option value="">-- <?php echo xlt('All'); ?> --</option>
                                    <?php
                                    $codeRes = sqlStatement("SELECT ct_key, ct_label FROM code_types ORDER BY ct_seq, ct_key");
                                    while ($codeRow = sqlFetchArray($codeRes)) {
                                        $selected = ((string) $form_code_type === (string) $codeRow['ct_key']) ? ' selected' : '';
                                        echo "<option value='" . attr($codeRow['ct_key']) . "'" . $selected . ">" . text($codeRow['ct_label']) . "</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border-left:1px solid; padding-left:15px">
                    <a href="#" class="css_button" onclick="$('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('Submit'); ?></span></a>
                    <?php if ($form_refresh) { ?>
                        <a href="#" class="css_button" id="printbutton"><span><?php echo xlt('Print'); ?></span></a>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
</form>

<?php if ($form_refresh) { ?>
    <div id="report_results">
        <?php
        $previewCharges = 0.00;
        $previewCopays = 0.00;
        $previewTotal = 0.00;
        foreach ($invoiceRows as $previewRow) {
            $previewCharges += $previewRow['charges_total'];
            $previewCopays += $previewRow['copay_total'];
            $previewTotal += $previewRow['invoice_total'];
        }
        ?>
        <div class="summary-cards">
            <div class="summary-card">
                <span class="label"><?php echo xlt('Invoices'); ?></span>
                <span class="value"><?php echo text(count($invoiceRows)); ?></span>
            </div>
            <div class="summary-card">
                <span class="label"><?php echo xlt('Charges'); ?></span>
                <span class="value"><?php echo text(reportMoney($previewCharges)); ?></span>
            </div>
            <div class="summary-card">
                <span class="label"><?php echo xlt('Copay Paid'); ?></span>
                <span class="value"><?php echo text(reportMoney($previewCopays)); ?></span>
            </div>
            <div class="summary-card">
                <span class="label"><?php echo xlt('Total'); ?></span>
                <span class="value"><?php echo text(reportMoney($previewTotal)); ?></span>
            </div>
        </div>
        <div class="legend">
            <span class="code-procedure"><span class="code-chip">CPT4</span> <?php echo xlt('Procedures'); ?></span>
            <span class="code-secondary"><span class="code-chip">CPT42</span> <?php echo xlt('Secondary'); ?></span>
            <span class="code-anesthesia"><span class="code-chip">CPT4A</span> <?php echo xlt('Anesthesia'); ?></span>
            <span class="code-product"><span class="code-chip">RXCUI</span> <?php echo xlt('Products'); ?></span>
            <span class="code-supply"><span class="code-chip">INSUM</span> <?php echo xlt('Supplies'); ?></span>
            <span class="code-zero"><span class="code-chip">0.00</span> <?php echo xlt('No charge'); ?></span>
        </div>
        <div class="rule-note">
            <?php echo xlt('Billing rules'); ?>:
            <?php echo xlt('only for category'); ?> Proced -
            CPT4 <?php echo xlt('main'); ?> 1x / <?php echo xlt('additional'); ?> 0.5x,
            CPT42 <?php echo xlt('main'); ?> 0.2x / <?php echo xlt('additional'); ?> 0.1x,
            CPT4A 1x. <?php echo xlt('All other categories remain at'); ?> 1x.
        </div>
        <table width="98%" class="report-table">
            <thead>
                <tr>
                    <th><?php echo xlt('Date'); ?></th>
                    <th><?php echo xlt('Invoice'); ?></th>
                    <th><?php echo xlt('Patient'); ?></th>
                    <th><?php echo xlt('Provider'); ?></th>
                    <th><?php echo xlt('Facility'); ?></th>
                    <th><?php echo xlt('Price Level'); ?></th>
                    <th class="amount"><?php echo xlt('Charges'); ?></th>
                    <th class="amount"><?php echo xlt('Copay Paid'); ?></th>
                    <th class="amount"><?php echo xlt('Total'); ?></th>
                    <th><?php echo xlt('Details'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandCharges = 0.00;
                $grandCopays = 0.00;
                $grandTotal = 0.00;
                foreach ($invoiceRows as $idx => $row) {
                    $grandCharges += $row['charges_total'];
                    $grandCopays += $row['copay_total'];
                    $grandTotal += $row['invoice_total'];
                    ?>
                    <tr class="summary-row">
                        <td><?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))); ?></td>
                        <td><span class="invoice-link"><?php echo text(invoiceDisplayNumber($row)); ?></span></td>
                        <td><?php echo text(patientDisplayName($row)); ?><br><span class="muted"><?php echo text($row['pubpid']); ?></span></td>
                        <td><?php echo text(providerDisplayName($row)); ?></td>
                        <td><?php echo text($row['facility_name']); ?></td>
                        <td>
                            <?php echo text($row['effective_pricelevel_title']); ?>
                            <?php if ($row['effective_pricelevel_title'] !== $row['pricelevel_title']) { ?>
                                <br><span class="muted"><?php echo xlt('Patient'); ?>: <?php echo text($row['pricelevel_title']); ?></span>
                            <?php } ?>
                        </td>
                        <td class="amount"><?php echo text(reportMoney($row['charges_total'])); ?></td>
                        <td class="amount"><?php echo text(reportMoney($row['copay_total'])); ?></td>
                        <td class="amount money-strong"><?php echo text(reportMoney($row['invoice_total'])); ?></td>
                        <td>
                            <details class="report-detail">
                                <summary><?php echo xlt('View Detail'); ?></summary>
                                <div class="detail-panel">
                                <strong class="detail-title"><?php echo xlt('Billing'); ?></strong>
                                <table class="report-table detail-table">
                                    <tr>
                                        <th><?php echo xlt('Date'); ?></th>
                                        <th><?php echo xlt('Provider'); ?></th>
                                        <th><?php echo xlt('Type'); ?></th>
                                        <th><?php echo xlt('Category'); ?></th>
                                        <th><?php echo xlt('Code'); ?></th>
                                        <th><?php echo xlt('Description'); ?></th>
                                        <th class="amount"><?php echo xlt('Units'); ?></th>
                                        <th class="amount"><?php echo xlt('Base'); ?></th>
                                        <th class="amount"><?php echo xlt('Factor'); ?></th>
                                        <th class="amount"><?php echo xlt('Total'); ?></th>
                                    </tr>
                                    <?php if (empty($row['billing_lines'])) { ?>
                                        <tr><td colspan="10"><?php echo xlt('No billing lines.'); ?></td></tr>
                                    <?php } ?>
                                    <?php $lastDisplayGroup = ''; ?>
                                    <?php foreach ($row['billing_lines'] as $line) { ?>
                                        <?php if ($lastDisplayGroup !== $line['display_group_label']) { ?>
                                            <?php $lastDisplayGroup = $line['display_group_label']; ?>
                                            <tr class="billing-group-row <?php echo attr($line['display_group_class']); ?>">
                                                <td colspan="10"><?php echo text($lastDisplayGroup); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php $lineClass = codeTypeClass($line['code_type']); ?>
                                        <tr class="<?php echo attr($lineClass); ?>">
                                            <td><?php echo text(oeFormatShortDate(substr($line['date'], 0, 10))); ?></td>
                                            <td><?php echo text($line['provider_name']); ?>&nbsp;</td>
                                            <td>
                                                <span class="code-chip"><?php echo text($line['code_type']); ?></span>
                                            </td>
                                            <td>
                                                <?php echo text($line['code_category']); ?>&nbsp;
                                            </td>
                                            <td class="code-number">
                                                <?php echo text(trim($line['code'] . ' ' . $line['modifier'])); ?>
                                            </td>
                                            <td class="description-cell">
                                                <?php echo text($line['code_text']); ?>
                                            </td>
                                            <td class="amount"><?php echo text($line['units']); ?></td>
                                            <td class="amount"><?php echo text(reportMoney($line['fee'])); ?></td>
                                            <td class="amount"><?php echo text(formatFactor($line['billing_factor'])); ?></td>
                                            <td class="amount<?php echo ((float) $line['calculated_total'] != 0.00) ? ' money-strong' : ''; ?>"><?php echo text(reportMoney($line['calculated_total'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>

                                <strong class="detail-title"><?php echo xlt('Products'); ?></strong>
                                <table class="report-table detail-table">
                                    <tr>
                                        <th><?php echo xlt('Date'); ?></th>
                                        <th><?php echo xlt('Product'); ?></th>
                                        <th><?php echo xlt('Lot'); ?></th>
                                        <th><?php echo xlt('Vendor'); ?></th>
                                        <th class="amount"><?php echo xlt('Qty'); ?></th>
                                        <th class="amount"><?php echo xlt('Fee'); ?></th>
                                    </tr>
                                    <?php if (empty($row['product_lines'])) { ?>
                                        <tr><td colspan="6"><?php echo xlt('No product lines.'); ?></td></tr>
                                    <?php } ?>
                                    <?php foreach ($row['product_lines'] as $line) { ?>
                                        <?php $lineClass = productCodeClass($line['name'], $line['fee']); ?>
                                        <tr class="<?php echo attr($lineClass); ?>">
                                            <td><?php echo text(oeFormatShortDate($line['sale_date'])); ?></td>
                                            <td>
                                                <span class="code-chip"><?php echo text(((float) $line['fee'] == 0.00) ? '0.00' : 'PROD'); ?></span>
                                                <span class="code-text"><?php echo text($line['name']); ?></span>
                                            </td>
                                            <td><?php echo text($line['lot_number']); ?></td>
                                            <td><?php echo text($line['vendor_name']); ?></td>
                                            <td class="amount"><?php echo text($line['quantity']); ?></td>
                                            <td class="amount<?php echo ((float) $line['fee'] != 0.00) ? ' money-strong' : ''; ?>"><?php echo text(reportMoney($line['fee'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                                </div>
                            </details>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td colspan="6" class="amount"><strong><?php echo xlt('Grand Total'); ?></strong></td>
                    <td class="amount"><strong><?php echo text(reportMoney($grandCharges)); ?></strong></td>
                    <td class="amount"><strong><?php echo text(reportMoney($grandCopays)); ?></strong></td>
                    <td class="amount"><strong><?php echo text(reportMoney($grandTotal)); ?></strong></td>
                    <td><?php echo text(count($invoiceRows)); ?> <?php echo xlt('invoices'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php } ?>

</body>
</html>
