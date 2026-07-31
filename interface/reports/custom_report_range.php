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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
$form_iess_mode = !empty($_POST['form_iess_mode']);
$form_export = isset($_POST['form_export']) ? trim($_POST['form_export']) : '';
$form_prefactura_pid = isset($_POST['form_prefactura_pid']) ? trim($_POST['form_prefactura_pid']) : '';
$form_prefactura_month = isset($_POST['form_prefactura_month']) ? trim($_POST['form_prefactura_month']) : '';
$form_refresh = !empty($_POST['form_refresh']);

if ($form_export !== '') {
    $form_iess_mode = true;
    $form_refresh = true;
    @ini_set('memory_limit', '512M');
    @set_time_limit(0);
}

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
    $name = trim($row['lname'] . ' ' . (isset($row['lname2']) ? $row['lname2'] : '') . ', ' . $row['fname'] . ' ' . $row['mname']);
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

function normalizeIessText($value)
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/\s+/', ' ', $value);
    return strtr($value, array(
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ñ' => 'n',
    ));
}

function iessAffiliationMap()
{
    return array(
        'contribuyente voluntario' => 'SV',
        'conyuge' => 'CY',
        'conyuge pensionista' => 'CJ',
        'seguro campesino' => 'CA',
        'seguro campesino jubilado' => 'JC',
        'seguro general' => 'SG',
        'seguro general jubilado' => 'JU',
        'seguro general por montepio' => 'MO',
        'seguro general tiempo parcial' => 'SG',
        'hijos dependientes' => 'HD',
    );
}

function iessAffiliationSqlValues()
{
    return array(
        'contribuyente voluntario',
        'conyuge',
        'cónyuge',
        'conyuge pensionista',
        'cónyuge pensionista',
        'seguro campesino',
        'seguro campesino jubilado',
        'seguro general',
        'seguro general jubilado',
        'seguro general por montepio',
        'seguro general tiempo parcial',
        'hijos dependientes',
        'seguro_general',
        'seguro_general_jubilado',
        'seguro_general_por_montepio',
        'seguro_general_tiempo_parcial',
        'contribuyente_voluntario',
        'conyuge_pensionista',
        'seguro_campesino',
        'seguro_campesino_jubilado',
        'hijos_dependientes',
        'sg',
        'sv',
        'cy',
        'cj',
        'ca',
        'jc',
        'ju',
        'mo',
        'hd',
    );
}

function iessAffiliationAbbreviation($subAffiliation, $subAffiliationName = '')
{
    $map = iessAffiliationMap();
    $normalized = normalizeIessText($subAffiliation);
    if ($normalized !== '' && isset($map[$normalized])) {
        return $map[$normalized];
    }

    $normalizedName = normalizeIessText($subAffiliationName);
    if ($normalizedName !== '' && isset($map[$normalizedName])) {
        return $map[$normalizedName];
    }

    if ($subAffiliationName !== '') {
        return strtoupper(trim((string) $subAffiliationName));
    }

    return strtoupper(trim((string) $subAffiliation));
}

function iessPlanillaSortInfo($patientGroup)
{
    $abbr = normalizeIessText(isset($patientGroup['sub_affiliation_abbr']) ? $patientGroup['sub_affiliation_abbr'] : '');
    $value = normalizeIessText(isset($patientGroup['sub_affiliation']) ? $patientGroup['sub_affiliation'] : '');
    $name = normalizeIessText(isset($patientGroup['sub_affiliation_name']) ? $patientGroup['sub_affiliation_name'] : '');
    $combined = trim($abbr . ' ' . $value . ' ' . $name);

    if (preg_match('/\b(mo|montepio|huerfano)/', $combined)) {
        return array('rank' => 20, 'code' => 'MO');
    }

    if (preg_match('/\b(ju|jubilado|riesgo de trabajo)/', $combined)) {
        return array('rank' => 30, 'code' => 'JU');
    }

    if (preg_match('/\b(sg|sv|cy|seguro general|seguro voluntario|contribuyente voluntario|conyuge)/', $combined)) {
        return array('rank' => 10, 'code' => 'SG');
    }

    return array('rank' => 90, 'code' => strtoupper((string) (isset($patientGroup['sub_affiliation_abbr']) ? $patientGroup['sub_affiliation_abbr'] : '')));
}

function monthDisplay($monthKey)
{
    if ($monthKey === '' || $monthKey === 'unknown') {
        return xl('Unknown');
    }

    $time = strtotime($monthKey . '-01');
    return $time ? date('m/Y', $time) : $monthKey;
}

function iessExportColumns($format)
{
    return range(1, $format === 'soam' ? 33 : 44);
}

function iessFormatDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return '';
    }

    $time = strtotime($value);
    return $time ? date('d/m/Y', $time) : '';
}

function iessPatientAge($dob)
{
    $dob = trim((string) $dob);
    if ($dob === '' || $dob === '0000-00-00') {
        return '';
    }

    try {
        $birth = new DateTime($dob);
        $today = new DateTime('today');
        return (string) $birth->diff($today)->y;
    } catch (Exception $exception) {
        return '';
    }
}

function iessPatientSex($sex)
{
    $sex = strtoupper(trim((string) $sex));
    return $sex !== '' ? substr($sex, 0, 1) : '--';
}

function iessImageCodeLookup()
{
    return array_fill_keys(array(
        '76512',
        '92081',
        '92225',
        '281010',
        '281021',
        '281032',
        '281229',
        '281186',
        '281197',
        '281230',
        '281306',
        '281295',
    ), true);
}

function iessExtractCie10($diagnosis)
{
    $diagnosis = trim((string) $diagnosis);
    if ($diagnosis === '') {
        return '';
    }

    $first = trim(explode(';', $diagnosis)[0]);
    if (preg_match('/^\s*([A-Z][0-9]{2}[0-9A-Z]{0,4}(?:\.[0-9A-Z]+)?)\s*-/i', $first, $matches)) {
        return strtoupper($matches[1]);
    }

    if (preg_match('/([A-Z][0-9]{2}[0-9A-Z]{0,4}(?:\.[0-9A-Z]+)?)/i', $first, $matches)) {
        return strtoupper($matches[1]);
    }

    return '';
}

function iessUpperText($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
}

function getIessDerivationForDate($pid, $date)
{
    $rows = getIessDerivationsForDate($pid, $date);
    return !empty($rows[0]) ? $rows[0] : array();
}

function getIessDerivationsForDate($pid, $date)
{
    static $cache = array();

    $cacheKey = $pid . '|' . $date;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $res = sqlStatement(
        "SELECT t.id,
                MAX(CASE WHEN d.field_id = 'refer_id' THEN d.field_value END) AS refer_id,
                MAX(CASE WHEN d.field_id = 'refer_date' THEN d.field_value END) AS refer_date,
                MAX(CASE WHEN d.field_id = 'refer_end_date' THEN d.field_value END) AS refer_end_date,
                MAX(CASE WHEN d.field_id = 'refer_to' THEN d.field_value END) AS refer_to,
                MAX(CASE WHEN d.field_id = 'refer_from' THEN d.field_value END) AS refer_from,
                MAX(CASE WHEN d.field_id = 'refer_diag' THEN d.field_value END) AS refer_diag,
                MAX(CASE WHEN d.field_id = 'refer_related_code' THEN d.field_value END) AS refer_related_code
         FROM transactions AS t
         LEFT JOIN lbt_data AS d ON d.form_id = t.id
         WHERE t.title = 'LBTref' AND t.pid = ?
         GROUP BY t.id
         HAVING (refer_date IS NULL OR refer_date = '' OR refer_date <= ?) AND (refer_end_date IS NULL OR refer_end_date = '' OR refer_end_date >= ?)
         ORDER BY refer_date DESC, t.id DESC",
        array($pid, $date, $date)
    );

    $cache[$cacheKey] = array();
    while ($row = sqlFetchArray($res)) {
        if (!empty($row['id'])) {
            $cache[$cacheKey][] = $row;
        }
    }

    return $cache[$cacheKey];
}

function iessLineType($line, $format)
{
    $codeType = strtoupper(trim((string) $line['code_type']));
    $code = trim((string) $line['code']);

    if ($codeType === 'RXCUI') {
        return 'FAR';
    }

    if ($codeType === 'INSUM') {
        return 'IMM';
    }

    if ($format === 'soam') {
        if ($codeType === 'CPT4A') {
            return 'AMB';
        }

        $imageLookup = iessImageCodeLookup();
        return isset($imageLookup[$code]) ? 'IMA' : 'AMB';
    }

    $imageLookup = iessImageCodeLookup();
    return isset($imageLookup[$code]) ? 'IMAGEN' : 'PRO/INTERV';
}

function iessLineAo($line)
{
    $codeType = strtoupper(trim((string) $line['code_type']));
    if ($codeType === 'CPT42') {
        return '3';
    }

    if ($codeType === 'CPT4A') {
        return '6';
    }

    if ($codeType === 'RXCUI' || $codeType === 'INSUM') {
        return '';
    }

    return '1';
}

function iessLineAn($line)
{
    $codeType = strtoupper(trim((string) $line['code_type']));
    if ($codeType === 'RXCUI') {
        return 'M';
    }

    if ($codeType === 'INSUM') {
        return 'I';
    }

    return 'P';
}

function iessLineAd($line)
{
    return strtoupper(trim((string) $line['code_type'])) === 'INSUM' ? '1' : '0';
}

function iessFormatNumber($value, $commaDecimal = false)
{
    return number_format((float) $value, 2, $commaDecimal ? ',' : '.', '');
}

function iessStringifyQuantity($value)
{
    $number = (float) $value;
    if (floor($number) === $number) {
        return (string) (int) $number;
    }

    return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
}

function prefacturaServiceLabel($line)
{
    $codeType = strtoupper(trim((string) $line['code_type']));
    $category = normalizedCategory(isset($line['code_category']) ? $line['code_category'] : '');

    if ($codeType === 'RXCUI' || !empty($line['is_product_line'])) {
        return 'FAR';
    }

    if ($codeType === 'INSUM') {
        return 'INH';
    }

    if ($codeType === 'CPT4' && $category === 'EQUIPOS') {
        return 'PRO/ESP';
    }

    if ($codeType === 'CPT4' && ($category === 'DERECHOSALA' || $category === 'MATERIALFUNGIBLE')) {
        return 'HD';
    }

    return 'HOSP/QUIR';
}

function prefacturaInsuranceTypeLabel($patientGroup)
{
    $name = trim((string) ($patientGroup['sub_affiliation_name'] ?? ''));
    $value = trim((string) ($patientGroup['sub_affiliation'] ?? ''));
    $abbr = trim((string) ($patientGroup['sub_affiliation_abbr'] ?? ''));
    $label = $name !== '' ? $name : $value;

    if ($label === '') {
        return $abbr;
    }

    if ($abbr !== '' && normalizeIessText($label) !== normalizeIessText($abbr)) {
        return $abbr . ' - ' . iessUpperText($label);
    }

    return iessUpperText($label);
}

function prefacturaSafeFilename($value)
{
    $value = strtoupper(trim((string) $value));
    $value = preg_replace('/[^A-Z0-9]+/', '_', $value);
    return trim($value, '_') ?: 'PREFACTURA';
}

function findIessPatientGroup($iessGroups, $pid, $month)
{
    if ($month !== '' && isset($iessGroups[$month]['patients'][$pid])) {
        return $iessGroups[$month]['patients'][$pid];
    }

    foreach ($iessGroups as $monthGroup) {
        if (isset($monthGroup['patients'][$pid])) {
            return $monthGroup['patients'][$pid];
        }
    }

    return null;
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

function normalizedAuditText($value)
{
    return preg_replace('/[^A-Z0-9]/', '', strtoupper(normalizeIessText($value)));
}

function iessAffiliationMatch($subAffiliation, $subAffiliationName)
{
    $values = array_fill_keys(array_map('normalizeIessText', iessAffiliationSqlValues()), true);
    $normalizedValue = normalizeIessText($subAffiliation);
    $normalizedName = normalizeIessText($subAffiliationName);
    return ($normalizedValue !== '' && isset($values[$normalizedValue])) || ($normalizedName !== '' && isset($values[$normalizedName]));
}

function isIessPricelevel($pricelevel)
{
    return strtoupper(trim((string) $pricelevel)) === 'IESS';
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

function productLineTotal($line)
{
    $quantity = (float) (isset($line['quantity']) ? $line['quantity'] : 0);
    if ($quantity == 0.00) {
        $quantity = 1.00;
    }

    return (float) $line['fee'] * $quantity;
}

function isDuplicateProductLine($productLine, $billingLines)
{
    $productName = normalizedAuditText(isset($productLine['name']) ? $productLine['name'] : '');
    $productTotal = productLineTotal($productLine);
    $productQuantity = (float) (isset($productLine['quantity']) ? $productLine['quantity'] : 0);
    if ($productQuantity == 0.00) {
        $productQuantity = 1.00;
    }

    foreach ($billingLines as $billingLine) {
        $billingCodeType = strtoupper(trim((string) (isset($billingLine['code_type']) ? $billingLine['code_type'] : '')));
        if ($billingCodeType !== 'RXCUI' && $billingCodeType !== 'INSUM') {
            continue;
        }

        $billingText = normalizedAuditText(trim((string) (isset($billingLine['code']) ? $billingLine['code'] : '') . ' ' . (isset($billingLine['code_text']) ? $billingLine['code_text'] : '')));
        if ($productName === '' || $billingText === '' || strpos($billingText, $productName) === false) {
            continue;
        }

        $billingQuantity = (float) (isset($billingLine['units']) ? $billingLine['units'] : 0);
        if ($billingQuantity == 0.00) {
            $billingQuantity = 1.00;
        }

        $billingTotal = isset($billingLine['calculated_total']) ? (float) $billingLine['calculated_total'] : ((float) $billingLine['fee'] * $billingQuantity);
        if (abs($billingQuantity - $productQuantity) <= 0.01 && abs($billingTotal - $productTotal) <= 0.01) {
            return true;
        }
    }

    return false;
}

function filterDuplicateProductLines($productLines, $billingLines)
{
    $kept = array();
    $duplicates = array();
    foreach ($productLines as $productLine) {
        if (isDuplicateProductLine($productLine, $billingLines)) {
            $productLine['audit_exclusion_reason'] = 'possible_duplicate_billing_drug_sales';
            $duplicates[] = $productLine;
        } else {
            $kept[] = $productLine;
        }
    }

    return array($kept, $duplicates);
}

function sumLines($lines)
{
    $total = 0.00;
    foreach ($lines as $line) {
        if (isset($line['calculated_total'])) {
            $total += (float) $line['calculated_total'];
        } elseif (isset($line['quantity'])) {
            $total += productLineTotal($line);
        } else {
            $total += (float) $line['fee'];
        }
    }

    return $total;
}

function collectCandidateEncounters($startdate, $enddate, $filters)
{
    $bind = array($startdate . ' 00:00:00', $enddate . ' 23:59:59');
    $query = "SELECT fe.pid, fe.encounter, fe.date, fe.facility_id, fe.invoice_refno, " .
        "p.pubpid, p.fname, p.mname, p.lname, p.lname2, p.pricelevel, p.genericval1, p.genericname1, p.DOB, p.sex, " .
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

    if (!empty($filters['iess_mode']) && !empty($filters['strict_iess_query'])) {
        $iessValues = iessAffiliationSqlValues();
        $placeholders = implode(',', array_fill(0, count($iessValues), '?'));
        $query .= "AND (UPPER(COALESCE(p.pricelevel, '')) = 'IESS' " .
            "OR LOWER(TRIM(COALESCE(p.genericval1, ''))) IN (" . $placeholders . ") " .
            "OR LOWER(TRIM(COALESCE(p.genericname1, ''))) IN (" . $placeholders . ")) ";
        foreach ($iessValues as $iessValue) {
            $bind[] = $iessValue;
        }
        foreach ($iessValues as $iessValue) {
            $bind[] = $iessValue;
        }
    }

    $query .= "ORDER BY fe.date DESC, fe.id DESC";

    $res = sqlStatement($query, $bind);
    $rows = array();
    while ($row = sqlFetchArray($res)) {
        $effectivePricelevel = $filters['pricelevel'] !== '' ? $filters['pricelevel'] : $row['pricelevel'];
        $billingLines = sortBillingLinesForDisplay(applyBillingRules(getBillingLines($row['pid'], $row['encounter'], '', $effectivePricelevel)));
        $rawProductLines = getProductLines($row['pid'], $row['encounter'], '', $effectivePricelevel);
        list($productLines, $duplicateProductLines) = filterDuplicateProductLines($rawProductLines, $billingLines);
        $billingTotal = sumLines($billingLines);
        $productTotal = sumLines($productLines);
        $rawProductTotal = sumLines($rawProductLines);
        $duplicateProductTotal = sumLines($duplicateProductLines);

        $copay = abs((float) BillingUtilities::getPatientCopay($row['pid'], $row['encounter']));
        $row['billing_lines'] = $billingLines;
        $row['product_lines'] = $productLines;
        $row['raw_product_lines'] = $rawProductLines;
        $row['duplicate_product_lines'] = $duplicateProductLines;
        $row['billing_total'] = $billingTotal;
        $row['product_total'] = $productTotal;
        $row['raw_product_total'] = $rawProductTotal;
        $row['duplicate_product_total'] = $duplicateProductTotal;
        $row['charges_total'] = $billingTotal + $productTotal;
        $row['copay_total'] = $copay;
        $row['invoice_total'] = $billingTotal + $productTotal;
        $row['effective_pricelevel'] = $effectivePricelevel;
        $row['effective_pricelevel_title'] = pricelevelTitle($effectivePricelevel);
        $row['iess_affiliation_abbr'] = iessAffiliationAbbreviation($row['genericval1'], $row['genericname1']);
        $row['iess_pricelevel_match'] = isIessPricelevel($row['pricelevel']);
        $row['iess_subaffiliation_match'] = iessAffiliationMatch($row['genericval1'], $row['genericname1']);
        $rows[] = $row;
    }

    return $rows;
}

function buildInvoiceRows($startdate, $enddate, $filters)
{
    $filters['strict_iess_query'] = true;
    return collectCandidateEncounters($startdate, $enddate, $filters);
}

function classifyIessEncounter($row)
{
    $date = substr($row['date'], 0, 10);
    $derivations = getIessDerivationsForDate($row['pid'], $date);
    $derivationCodes = array();
    foreach ($derivations as $derivation) {
        if (!empty($derivation['refer_id'])) {
            $derivationCodes[$derivation['refer_id']] = true;
        }
    }

    $isIess = !empty($row['iess_pricelevel_match']) || !empty($row['iess_subaffiliation_match']);
    $billableLineCount = count($row['billing_lines']) + count($row['product_lines']);
    $reasons = array();
    if (!$isIess) {
        $reasons[] = 'no_iess_real';
    }

    if (empty($derivations)) {
        $reasons[] = 'missing_active_derivation';
    }

    if ($billableLineCount === 0) {
        $reasons[] = 'no_billable_lines';
    }

    if ((float) $row['invoice_total'] <= 0.00) {
        $reasons[] = 'zero_total';
    }

    if (!empty($row['duplicate_product_lines'])) {
        $reasons[] = 'possible_duplicate_billing_drug_sales';
    }

    $blockingReasons = array();
    foreach ($reasons as $reason) {
        if ($reason !== 'possible_duplicate_billing_drug_sales') {
            $blockingReasons[] = $reason;
        }
    }

    if (!empty($row['iess_pricelevel_match'])) {
        $source = 'pricelevel';
    } elseif (!empty($row['iess_subaffiliation_match'])) {
        $source = 'subaffiliation';
    } else {
        $source = 'none';
    }

    $row['audit_included'] = empty($blockingReasons);
    $row['audit_reasons'] = $reasons;
    $row['audit_blocking_reasons'] = $blockingReasons;
    $row['audit_reason'] = empty($reasons) ? 'included' : implode(', ', $reasons);
    $row['audit_inclusion_source'] = $source;
    $row['audit_derivation_count'] = count($derivations);
    $row['audit_derivation_codes'] = implode(', ', array_keys($derivationCodes));
    $row['audit_billable_line_count'] = $billableLineCount;
    $row['audit_duplicate_product_count'] = count($row['duplicate_product_lines']);

    return $row;
}

function buildIessAudit($candidateRows)
{
    $auditRows = array();
    $includedRows = array();
    $summary = array(
        'candidate_encounters' => 0,
        'candidate_patients' => 0,
        'included_encounters' => 0,
        'included_patients' => 0,
        'excluded_encounters' => 0,
        'included_total' => 0.00,
        'included_billing_lines' => 0,
        'included_product_lines' => 0,
        'duplicate_product_lines' => 0,
        'included_by_pricelevel' => 0,
        'included_by_subaffiliation' => 0,
        'reason_counts' => array(),
    );
    $candidatePatients = array();
    $includedPatients = array();

    foreach ($candidateRows as $row) {
        $summary['candidate_encounters']++;
        $candidatePatients[$row['pid']] = true;
        $row = classifyIessEncounter($row);
        $auditRows[] = $row;

        foreach ($row['audit_reasons'] as $reason) {
            if (!isset($summary['reason_counts'][$reason])) {
                $summary['reason_counts'][$reason] = 0;
            }
            $summary['reason_counts'][$reason]++;
        }

        $summary['duplicate_product_lines'] += (int) $row['audit_duplicate_product_count'];
        if (!empty($row['audit_included'])) {
            $includedRows[] = $row;
            $includedPatients[$row['pid']] = true;
            $summary['included_encounters']++;
            $summary['included_total'] += (float) $row['invoice_total'];
            $summary['included_billing_lines'] += count($row['billing_lines']);
            $summary['included_product_lines'] += count($row['product_lines']);
            if ($row['audit_inclusion_source'] === 'pricelevel') {
                $summary['included_by_pricelevel']++;
            } elseif ($row['audit_inclusion_source'] === 'subaffiliation') {
                $summary['included_by_subaffiliation']++;
            }
        } else {
            $summary['excluded_encounters']++;
        }
    }

    $summary['candidate_patients'] = count($candidatePatients);
    $summary['included_patients'] = count($includedPatients);
    ksort($summary['reason_counts']);

    return array(
        'rows' => $auditRows,
        'included_rows' => $includedRows,
        'summary' => $summary,
    );
}

function groupInvoiceRowsForIess($rows)
{
    $groups = array();
    foreach ($rows as $row) {
        $date = substr($row['date'], 0, 10);
        $monthKey = strtotime($date) ? date('Y-m', strtotime($date)) : 'unknown';
        $pid = (string) $row['pid'];

        if (!isset($groups[$monthKey])) {
            $groups[$monthKey] = array(
                'month' => $monthKey,
                'patients' => array(),
                'invoice_count' => 0,
                'total' => 0.00,
            );
        }

        if (!isset($groups[$monthKey]['patients'][$pid])) {
            $groups[$monthKey]['patients'][$pid] = array(
                'pid' => $row['pid'],
                'pubpid' => $row['pubpid'],
                'patient_name' => patientDisplayName($row),
                'pricelevel_title' => $row['pricelevel_title'],
                'sub_affiliation' => $row['genericval1'],
                'sub_affiliation_name' => $row['genericname1'],
                'sub_affiliation_abbr' => $row['iess_affiliation_abbr'],
                'first_date' => $date,
                'last_date' => $date,
                'invoice_count' => 0,
                'charges_total' => 0.00,
                'copay_total' => 0.00,
                'invoice_total' => 0.00,
                'providers' => array(),
                'facilities' => array(),
                'invoices' => array(),
            );
        }

        $patient = &$groups[$monthKey]['patients'][$pid];
        $patient['first_date'] = min($patient['first_date'], $date);
        $patient['last_date'] = max($patient['last_date'], $date);
        $patient['invoice_count']++;
        $patient['charges_total'] += (float) $row['charges_total'];
        $patient['copay_total'] += (float) $row['copay_total'];
        $patient['invoice_total'] += (float) $row['invoice_total'];
        $provider = providerDisplayName($row);
        if ($provider !== '') {
            $patient['providers'][$provider] = true;
        }

        if (!empty($row['facility_name'])) {
            $patient['facilities'][$row['facility_name']] = true;
        }

        $patient['invoices'][] = $row;
        unset($patient);

        $groups[$monthKey]['invoice_count']++;
        $groups[$monthKey]['total'] += (float) $row['invoice_total'];
    }

    ksort($groups);
    foreach ($groups as $monthKey => $monthGroup) {
        uasort($groups[$monthKey]['patients'], function ($left, $right) {
            $leftSort = iessPlanillaSortInfo($left);
            $rightSort = iessPlanillaSortInfo($right);
            if ($leftSort['rank'] !== $rightSort['rank']) {
                return $leftSort['rank'] < $rightSort['rank'] ? -1 : 1;
            }

            $nameCompare = strcasecmp($left['patient_name'], $right['patient_name']);
            if ($nameCompare !== 0) {
                return $nameCompare;
            }

            return strcasecmp((string) $left['pubpid'], (string) $right['pubpid']);
        });

        $planilla = 1;
        foreach ($groups[$monthKey]['patients'] as $pid => $patientGroup) {
            $sortInfo = iessPlanillaSortInfo($patientGroup);
            $groups[$monthKey]['patients'][$pid]['planilla_group'] = $sortInfo['code'];
            $groups[$monthKey]['patients'][$pid]['planilla_number'] = str_pad((string) $planilla, 6, '0', STR_PAD_LEFT);
            ++$planilla;
        }
    }

    return $groups;
}

function iessExportRow($format, $row, $line, $patientGroup)
{
    $isSoam = $format === 'soam';
    $dateSource = !empty($line['date']) ? $line['date'] : $row['date'];
    $date = substr($dateSource, 0, 10);
    $derivation = getIessDerivationForDate($row['pid'], $date);
    $diagnosis = !empty($derivation['refer_related_code']) ? $derivation['refer_related_code'] : (!empty($derivation['refer_diag']) ? $derivation['refer_diag'] : '');
    $cie10 = iessExtractCie10($diagnosis);
    $quantity = (float) $line['units'];
    if ($quantity == 0.00) {
        $quantity = 1.00;
    }

    $total = isset($line['calculated_total']) ? (float) $line['calculated_total'] : ((float) $line['fee'] * $quantity);
    $unitValue = $total;
    $codeType = strtoupper(trim((string) $line['code_type']));
    if ($codeType === 'RXCUI' || $codeType === 'INSUM') {
        $unitValue = (float) $line['fee'];
    }

    $patientName = trim($row['lname'] . ' ' . $row['lname2'] . ' ' . $row['fname'] . ' ' . $row['mname']);
    $abbreviation = $row['iess_affiliation_abbr'];
    $code = trim((string) $line['code']);
    $description = str_replace(array("\r", "\n"), ' ', (string) $line['code_text']);
    $lineType = iessLineType($line, $format);
    $invoiceDate = iessFormatDate($date);
    $birthDate = iessFormatDate($row['DOB']);
    $age = iessPatientAge($row['DOB']);
    $sex = iessPatientSex($row['sex']);
    $derivationCode = !empty($derivation['refer_id']) ? $derivation['refer_id'] : '';
    $ad = iessLineAd($line);
    $ae = '0';
    $an = iessLineAn($line);
    $ao = iessLineAo($line);

    if ($isSoam) {
        return array(
            '0000000135',
            $codeType === 'CPT4' ? '1' : '',
            $invoiceDate,
            $abbreviation,
            $row['pubpid'],
            $patientName,
            $sex,
            $birthDate,
            $age,
            $lineType,
            $code,
            $description,
            $cie10,
            '',
            '',
            iessStringifyQuantity($quantity),
            iessFormatNumber($unitValue, true),
            '',
            'T',
            $row['pubpid'],
            $patientName,
            'CVA',
            $derivationCode,
            '1',
            'D',
            '0',
            '',
            '',
            '',
            $ad,
            $ae,
            'F',
            iessFormatNumber($total, true),
        );
    }

    return array(
        '0000000135',
        isset($patientGroup['planilla_number']) ? $patientGroup['planilla_number'] : '',
        $invoiceDate,
        $abbreviation,
        $row['pubpid'],
        $patientName,
        $sex,
        $birthDate,
        $age,
        $lineType,
        $code,
        $description,
        $cie10,
        '',
        '',
        iessStringifyQuantity($quantity),
        iessFormatNumber($unitValue, false),
        '',
        'T',
        $row['pubpid'],
        $patientName,
        '',
        $derivationCode,
        '1',
        'D',
        '',
        '',
        '',
        '',
        $ad,
        $ae,
        iessFormatNumber($total, false),
        '',
        iessFormatDate($patientGroup['first_date']),
        iessFormatDate($patientGroup['last_date']),
        '',
        'NO',
        '',
        'NO',
        $an,
        $ao,
        '',
        '',
        'F',
    );
}

function buildIessExportRows($iessGroups, $format)
{
    $rows = array();
    foreach ($iessGroups as $monthGroup) {
        foreach ($monthGroup['patients'] as $patientGroup) {
            foreach ($patientGroup['invoices'] as $invoiceRow) {
                foreach ($invoiceRow['billing_lines'] as $line) {
                    $rows[] = iessExportRow($format, $invoiceRow, $line, $patientGroup);
                }

                foreach ($invoiceRow['product_lines'] as $productLine) {
                    $quantity = (float) $productLine['quantity'];
                    if ($quantity == 0.00) {
                        $quantity = 1.00;
                    }

                    $rows[] = iessExportRow(
                        $format,
                        $invoiceRow,
                        array(
                            'date' => $productLine['sale_date'],
                            'code_type' => 'RXCUI',
                            'code' => '',
                            'code_text' => $productLine['name'],
                            'units' => $quantity,
                            'fee' => (float) $productLine['fee'],
                            'calculated_total' => (float) $productLine['fee'] * $quantity,
                        ),
                        $patientGroup
                    );
                }
            }
        }
    }

    return $rows;
}

function outputIessXlsx($iessGroups, $format, $startdate, $enddate)
{
    $filename = ($format === 'soam' ? 'iess_soam_' : 'iess_plano_') . $startdate . '_' . $enddate . '.xlsx';
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($format === 'soam' ? 'SOAM' : 'PLANO');

    $columns = iessExportColumns($format);
    $sheet->fromArray($columns, null, 'A1');
    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));

    $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray(array(
        'font' => array('bold' => true),
        'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('argb' => 'FFD9E2F3')),
        'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER),
        'borders' => array('allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('argb' => 'FF7F7F7F'))),
    ));

    $rowNumber = 2;
    foreach ($iessGroups as $monthGroup) {
        foreach ($monthGroup['patients'] as $patientGroup) {
            foreach ($patientGroup['invoices'] as $invoiceRow) {
                foreach ($invoiceRow['billing_lines'] as $line) {
                    $exportRow = iessExportRow($format, $invoiceRow, $line, $patientGroup);
                    $columnNumber = 1;
                    foreach ($exportRow as $value) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnNumber);
                        $sheet->setCellValueExplicit($columnLetter . $rowNumber, (string) $value, DataType::TYPE_STRING);
                        ++$columnNumber;
                    }
                    ++$rowNumber;
                }

                foreach ($invoiceRow['product_lines'] as $productLine) {
                    $quantity = (float) $productLine['quantity'];
                    if ($quantity == 0.00) {
                        $quantity = 1.00;
                    }

                    $exportRow = iessExportRow(
                        $format,
                        $invoiceRow,
                        array(
                            'date' => $productLine['sale_date'],
                            'code_type' => 'RXCUI',
                            'code' => '',
                            'code_text' => $productLine['name'],
                            'units' => $quantity,
                            'fee' => (float) $productLine['fee'],
                            'calculated_total' => (float) $productLine['fee'] * $quantity,
                        ),
                        $patientGroup
                    );
                    $columnNumber = 1;
                    foreach ($exportRow as $value) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnNumber);
                        $sheet->setCellValueExplicit($columnLetter . $rowNumber, (string) $value, DataType::TYPE_STRING);
                        ++$columnNumber;
                    }
                    ++$rowNumber;
                }
            }
        }
    }

    $sheet->freezePane('A2');
    for ($columnIndex = 1; $columnIndex <= count($columns); ++$columnIndex) {
        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
        $sheet->getColumnDimension($columnLetter)->setWidth($columnIndex === 12 ? 45 : 14);
    }

    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);
    $writer->save('php://output');
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;
}

function outputIessAuditXlsx($auditRows, $summary, $startdate, $enddate)
{
    $filename = 'auditoria_iess_' . $startdate . '_' . $enddate . '.xlsx';
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('AUDITORIA IESS');

    $sheet->setCellValueExplicit('A1', 'Auditoria IESS', DataType::TYPE_STRING);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    $summaryRows = array(
        array('Candidatos encuentros', $summary['candidate_encounters']),
        array('Candidatos pacientes unicos', $summary['candidate_patients']),
        array('Incluidos encuentros', $summary['included_encounters']),
        array('Incluidos pacientes unicos', $summary['included_patients']),
        array('Excluidos encuentros', $summary['excluded_encounters']),
        array('Total incluido', iessFormatNumber($summary['included_total'], false)),
        array('Lineas billing incluidas', $summary['included_billing_lines']),
        array('Lineas drug_sales incluidas', $summary['included_product_lines']),
        array('Duplicados drug_sales excluidos', $summary['duplicate_product_lines']),
        array('Incluidos por pricelevel', $summary['included_by_pricelevel']),
        array('Incluidos por subafiliacion', $summary['included_by_subaffiliation']),
    );
    $rowNumber = 2;
    foreach ($summaryRows as $summaryRow) {
        $sheet->setCellValueExplicit('A' . $rowNumber, $summaryRow[0], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $rowNumber, (string) $summaryRow[1], DataType::TYPE_STRING);
        ++$rowNumber;
    }

    $rowNumber++;
    $sheet->setCellValueExplicit('A' . $rowNumber, 'Motivo', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B' . $rowNumber, 'Encuentros', DataType::TYPE_STRING);
    $sheet->getStyle('A' . $rowNumber . ':B' . $rowNumber)->getFont()->setBold(true);
    ++$rowNumber;
    foreach ($summary['reason_counts'] as $reason => $count) {
        $sheet->setCellValueExplicit('A' . $rowNumber, $reason, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $rowNumber, (string) $count, DataType::TYPE_STRING);
        ++$rowNumber;
    }

    $rowNumber += 2;
    $headers = array(
        'Incluido',
        'Motivo',
        'Origen inclusion',
        'PID',
        'Cedula/HC',
        'Paciente',
        'Encounter',
        'Fecha',
        'Factura',
        'Pricelevel paciente',
        'Subafiliacion',
        'Subafiliacion nombre',
        'Derivaciones',
        'Codigos derivacion',
        'Lineas billing',
        'Lineas drug_sales',
        'Lineas drug_sales duplicadas',
        'Total billing',
        'Total drug_sales incluido',
        'Total drug_sales original',
        'Total duplicado excluido',
        'Total final',
        'Proveedor',
        'Facility',
    );
    $sheet->fromArray($headers, null, 'A' . $rowNumber);
    $detailHeaderRow = $rowNumber;
    $sheet->getStyle('A' . $rowNumber . ':X' . $rowNumber)->applyFromArray(array(
        'font' => array('bold' => true),
        'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('argb' => 'FFD9E2F3')),
        'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER),
        'borders' => array('allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('argb' => 'FF7F7F7F'))),
    ));
    ++$rowNumber;
    foreach ($auditRows as $row) {
        $values = array(
            !empty($row['audit_included']) ? 'SI' : 'NO',
            $row['audit_reason'],
            $row['audit_inclusion_source'],
            $row['pid'],
            $row['pubpid'],
            trim($row['lname'] . ' ' . $row['lname2'] . ' ' . $row['fname'] . ' ' . $row['mname']),
            $row['encounter'],
            iessFormatDate(substr($row['date'], 0, 10)),
            invoiceDisplayNumber($row),
            $row['pricelevel'],
            $row['genericval1'],
            $row['genericname1'],
            $row['audit_derivation_count'],
            $row['audit_derivation_codes'],
            count($row['billing_lines']),
            count($row['product_lines']),
            $row['audit_duplicate_product_count'],
            iessFormatNumber($row['billing_total'], false),
            iessFormatNumber($row['product_total'], false),
            iessFormatNumber($row['raw_product_total'], false),
            iessFormatNumber($row['duplicate_product_total'], false),
            iessFormatNumber($row['invoice_total'], false),
            providerDisplayName($row),
            $row['facility_name'],
        );
        $columnNumber = 1;
        foreach ($values as $value) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnNumber);
            $sheet->setCellValueExplicit($columnLetter . $rowNumber, (string) $value, DataType::TYPE_STRING);
            ++$columnNumber;
        }
        ++$rowNumber;
    }

    $sheet->freezePane('A' . ($detailHeaderRow + 1));
    for ($columnIndex = 1; $columnIndex <= count($headers); ++$columnIndex) {
        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
        $sheet->getColumnDimension($columnLetter)->setWidth($columnIndex === 2 || $columnIndex === 6 ? 32 : 16);
    }

    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);
    $writer->save('php://output');
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;
}

function buildPrefacturaSpreadsheet($patientGroup, $startdate, $enddate)
{
    if (!$patientGroup) {
        return array('spreadsheet' => null, 'filename' => '');
    }

    $firstInvoice = !empty($patientGroup['invoices'][0]) ? $patientGroup['invoices'][0] : array();
    $firstDate = $patientGroup['first_date'];
    $derivations = getIessDerivationsForDate($patientGroup['pid'], $firstDate);
    $derivation = !empty($derivations[0]) ? $derivations[0] : array();
    $diagnosis = !empty($derivation['refer_related_code']) ? $derivation['refer_related_code'] : (!empty($derivation['refer_diag']) ? $derivation['refer_diag'] : '');
    $diagnosisCie10 = iessExtractCie10($diagnosis);
    $patientName = iessUpperText(trim(($firstInvoice['lname'] ?? '') . ' ' . ($firstInvoice['lname2'] ?? '') . ' ' . ($firstInvoice['fname'] ?? '') . ' ' . ($firstInvoice['mname'] ?? '')));
    $patientId = (string) ($firstInvoice['pubpid'] ?? $patientGroup['pubpid']);
    $derivationRows = max(1, count($derivations));
    $detailHeaderRow = 9 + $derivationRows;
    $detailFirstRow = $detailHeaderRow + 1;

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('PREFACTURA');

    $sheet->getColumnDimension('A')->setWidth(16);
    $sheet->getColumnDimension('B')->setWidth(28);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(62);
    $sheet->getColumnDimension('E')->setWidth(16);
    $sheet->getColumnDimension('F')->setWidth(22);
    $sheet->getColumnDimension('G')->setWidth(17);
    $sheet->getColumnDimension('H')->setWidth(16);

    $sheet->mergeCells('B4:D4');
    $sheet->mergeCells('B5:D5');
    $sheet->mergeCells('B6:D6');
    $sheet->mergeCells('B8:D8');
    $sheet->setCellValueExplicit('D2', 'ALTAVISION', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('A4', 'NOMBRE DEL PACIENTE', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B4', $patientName, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E4', 'FECHA DE INGRESO', DataType::TYPE_STRING);
    $sheet->setCellValue('F4', iessFormatDate($patientGroup['first_date']));
    $sheet->setCellValueExplicit('G4', 'FECHA DE EGRESO', DataType::TYPE_STRING);
    $sheet->setCellValue('H4', iessFormatDate($patientGroup['last_date']));

    $sheet->setCellValueExplicit('A5', 'GARANTE', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B5', 'INSTITUTO ECUATORIANO DE SEGURIDAD SOCIAL', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E5', 'TIPO', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F5', prefacturaInsuranceTypeLabel($patientGroup), DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G5', 'EDAD', DataType::TYPE_STRING);
    $sheet->setCellValue('H5', iessPatientAge($firstInvoice['DOB'] ?? ''));

    $sheet->setCellValueExplicit('A6', 'DIAGNOSTICO', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B6', $diagnosisCie10 !== '' ? $diagnosisCie10 : $diagnosis, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E6', 'SEXO', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F6', iessPatientSex($firstInvoice['sex'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G6', 'FECHA NACIMIENTO', DataType::TYPE_STRING);
    $sheet->setCellValue('H6', iessFormatDate($firstInvoice['DOB'] ?? ''));

    $sheet->setCellValueExplicit('A7', 'CEDULA IDENTIDAD', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B7', $patientId, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E7', 'HISTORIA CLINICA', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F7', $patientId, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G7', 'TOTAL', DataType::TYPE_STRING);

    $sheet->setCellValueExplicit('A8', 'TITULAR', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B8', $patientName, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E8', 'CEDULA TITULAR', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F8', $patientId, DataType::TYPE_STRING);

    if (empty($derivations)) {
        $sheet->setCellValueExplicit('A9', 'DERIVACION', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B9', '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E9', 'FECHA', DataType::TYPE_STRING);
        $sheet->setCellValue('F9', iessFormatDate($firstDate));
    } else {
        $derivationRow = 9;
        foreach ($derivations as $derivationInfo) {
            $sheet->setCellValueExplicit('A' . $derivationRow, 'DERIVACION', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $derivationRow, !empty($derivationInfo['refer_id']) ? $derivationInfo['refer_id'] : '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $derivationRow, 'SECUENCIAL', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $derivationRow, !empty($derivationInfo['id']) ? (string) $derivationInfo['id'] : '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $derivationRow, 'FECHA', DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $derivationRow, iessFormatDate(!empty($derivationInfo['refer_date']) ? $derivationInfo['refer_date'] : $firstDate));
            ++$derivationRow;
        }
    }

    $headers = array('Fecha', 'Servicio', 'Codigo', 'Descripción', 'Cantidad', 'Costo por unidad', 'IVA', 'Total');
    $sheet->fromArray($headers, null, 'A' . $detailHeaderRow);

    $headerStyle = array(
        'font' => array('bold' => true),
        'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('argb' => 'FFD9E2F3')),
        'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER),
        'borders' => array('allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('argb' => 'FF7F7F7F'))),
    );
    $sheet->getStyle('A' . $detailHeaderRow . ':H' . $detailHeaderRow)->applyFromArray($headerStyle);
    $sheet->getStyle('A2:H' . ($detailHeaderRow - 1))->applyFromArray(array(
        'borders' => array('allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('argb' => 'FFBFBFBF'))),
    ));
    $sheet->getStyle('A4:A' . ($detailHeaderRow - 1))->getFont()->setBold(true);
    $sheet->getStyle('C9:C' . ($detailHeaderRow - 1))->getFont()->setBold(true);
    $sheet->getStyle('E4:E' . ($detailHeaderRow - 1))->getFont()->setBold(true);
    $sheet->getStyle('G4:G7')->getFont()->setBold(true);
    $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B4:D6')->getAlignment()->setWrapText(true);

    $rowNumber = $detailFirstRow;
    foreach ($patientGroup['invoices'] as $invoiceRow) {
        foreach ($invoiceRow['billing_lines'] as $line) {
            $quantity = (float) $line['units'];
            if ($quantity == 0.00) {
                $quantity = 1.00;
            }

            $unitValue = (float) $line['fee'];
            $total = isset($line['calculated_total']) ? (float) $line['calculated_total'] : $unitValue * $quantity;
            $service = prefacturaServiceLabel($line);

            $sheet->setCellValue('A' . $rowNumber, iessFormatDate($line['date']));
            $sheet->setCellValueExplicit('B' . $rowNumber, $service, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNumber, trim((string) $line['code']), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNumber, (string) $line['code_text'], DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNumber, $quantity);
            $sheet->setCellValue('F' . $rowNumber, $unitValue);
            $sheet->setCellValue('G' . $rowNumber, 0);
            $sheet->setCellValue('H' . $rowNumber, $total);
            ++$rowNumber;
        }

        foreach ($invoiceRow['product_lines'] as $productLine) {
            $quantity = (float) $productLine['quantity'];
            if ($quantity == 0.00) {
                $quantity = 1.00;
            }

            $unitValue = (float) $productLine['fee'];
            $sheet->setCellValue('A' . $rowNumber, iessFormatDate($productLine['sale_date']));
            $sheet->setCellValueExplicit('B' . $rowNumber, 'FAR', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNumber, '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNumber, (string) $productLine['name'], DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNumber, $quantity);
            $sheet->setCellValue('F' . $rowNumber, $unitValue);
            $sheet->setCellValue('G' . $rowNumber, 0);
            $sheet->setCellValue('H' . $rowNumber, $unitValue * $quantity);
            ++$rowNumber;
        }
    }

    $totalRow = $rowNumber;
    $sheet->setCellValueExplicit('F' . $totalRow, 'TOTAL', DataType::TYPE_STRING);
    if ($totalRow > $detailFirstRow) {
        $sheet->setCellValue('H' . $totalRow, '=SUM(H' . $detailFirstRow . ':H' . ($totalRow - 1) . ')');
    } else {
        $sheet->setCellValue('H' . $totalRow, 0);
    }
    $sheet->setCellValue('H7', '=H' . $totalRow);
    $sheet->getStyle('F' . $totalRow . ':H' . $totalRow)->getFont()->setBold(true);
    $sheet->getStyle('F' . $totalRow . ':H' . $totalRow)->applyFromArray(array(
        'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('argb' => 'FFFFF2CC')),
        'borders' => array('allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('argb' => 'FF7F7F7F'))),
    ));

    $lastDetailRow = max($detailHeaderRow, $totalRow);
    $sheet->getStyle('A' . $detailHeaderRow . ':H' . $lastDetailRow)->applyFromArray(array(
        'borders' => array('allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('argb' => 'FF7F7F7F'))),
        'alignment' => array('vertical' => Alignment::VERTICAL_TOP),
    ));
    $sheet->getStyle('D' . $detailFirstRow . ':D' . $lastDetailRow)->getAlignment()->setWrapText(true);
    $sheet->getStyle('E' . $detailFirstRow . ':H' . $lastDetailRow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E' . $detailFirstRow . ':H' . $lastDetailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A' . $detailFirstRow . ':A' . $lastDetailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B' . $detailFirstRow . ':C' . $lastDetailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->freezePane('A' . $detailFirstRow);

    for ($row = 1; $row <= $lastDetailRow; ++$row) {
        $sheet->getRowDimension($row)->setRowHeight($row === 8 ? 24 : 18);
    }

    $filename = 'prefactura_' . prefacturaSafeFilename($patientName) . '_' . $startdate . '_' . $enddate . '.xlsx';
    return array('spreadsheet' => $spreadsheet, 'filename' => $filename);
}

function outputPrefacturaXlsx($iessGroups, $pid, $month, $startdate, $enddate)
{
    $patientGroup = findIessPatientGroup($iessGroups, $pid, $month);
    if (!$patientGroup) {
        die(xlt('No IESS patient group found for prefactura.'));
    }

    $prefactura = buildPrefacturaSpreadsheet($patientGroup, $startdate, $enddate);
    $spreadsheet = $prefactura['spreadsheet'];
    $filename = $prefactura['filename'];

    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);
    $writer->save('php://output');
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;
}

function outputPrefacturasZip($iessGroups, $startdate, $enddate)
{
    if (!class_exists('ZipArchive')) {
        die(xlt('ZIP extension is not available.'));
    }

    $zipPath = tempnam(sys_get_temp_dir(), 'prefacturas_iess_');
    if ($zipPath === false) {
        die(xlt('Unable to create temporary ZIP file.'));
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
        @unlink($zipPath);
        die(xlt('Unable to create ZIP file.'));
    }

    $temporaryFiles = array();
    $fileCount = 0;
    foreach ($iessGroups as $monthGroup) {
        foreach ($monthGroup['patients'] as $patientGroup) {
            $prefactura = buildPrefacturaSpreadsheet($patientGroup, $startdate, $enddate);
            if (empty($prefactura['spreadsheet'])) {
                continue;
            }

            $xlsxPath = tempnam(sys_get_temp_dir(), 'prefactura_iess_');
            if ($xlsxPath === false) {
                continue;
            }

            $writer = new Xlsx($prefactura['spreadsheet']);
            $writer->setPreCalculateFormulas(false);
            $writer->save($xlsxPath);
            $prefactura['spreadsheet']->disconnectWorksheets();
            unset($prefactura['spreadsheet']);

            $zipName = $monthGroup['month'] . '/' . $prefactura['filename'];
            $zip->addFile($xlsxPath, $zipName);
            $temporaryFiles[] = $xlsxPath;
            ++$fileCount;
        }
    }

    if ($fileCount === 0) {
        $zip->close();
        foreach ($temporaryFiles as $temporaryFile) {
            @unlink($temporaryFile);
        }
        @unlink($zipPath);
        die(xlt('No IESS patient groups found for prefactura ZIP.'));
    }

    $zip->close();

    if (ob_get_length()) {
        ob_end_clean();
    }

    $filename = 'prefacturas_iess_' . $startdate . '_' . $enddate . '.zip';
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    readfile($zipPath);
    foreach ($temporaryFiles as $temporaryFile) {
        @unlink($temporaryFile);
    }
    @unlink($zipPath);
    exit;
}

$filters = array(
    'pid' => $form_pid,
    'facility' => $form_facility,
    'provider' => $form_provider,
    'pricelevel' => $form_pricelevel,
    'vendor' => $form_vendor,
    'invoice' => $form_invoice,
    'code_type' => $form_code_type,
    'iess_mode' => $form_iess_mode,
);
$iessAudit = array(
    'rows' => array(),
    'included_rows' => array(),
    'summary' => array(
        'candidate_encounters' => 0,
        'candidate_patients' => 0,
        'included_encounters' => 0,
        'included_patients' => 0,
        'excluded_encounters' => 0,
        'included_total' => 0.00,
        'included_billing_lines' => 0,
        'included_product_lines' => 0,
        'duplicate_product_lines' => 0,
        'included_by_pricelevel' => 0,
        'included_by_subaffiliation' => 0,
        'reason_counts' => array(),
    ),
);

if ($form_refresh && $form_iess_mode) {
    $candidateRows = collectCandidateEncounters($startdate, $enddate, $filters);
    $iessAudit = buildIessAudit($candidateRows);
    $invoiceRows = $iessAudit['included_rows'];
} else {
    $invoiceRows = $form_refresh ? buildInvoiceRows($startdate, $enddate, $filters) : array();
}
$iessGroups = ($form_refresh && $form_iess_mode) ? groupInvoiceRowsForIess($invoiceRows) : array();

if ($form_export === 'iess_flat') {
    outputIessXlsx($iessGroups, 'flat', $startdate, $enddate);
}

if ($form_export === 'iess_soam') {
    outputIessXlsx($iessGroups, 'soam', $startdate, $enddate);
}

if ($form_export === 'iess_audit') {
    outputIessAuditXlsx($iessAudit['rows'], $iessAudit['summary'], $startdate, $enddate);
}

if ($form_export === 'prefacturas_zip') {
    outputPrefacturasZip($iessGroups, $startdate, $enddate);
}

if ($form_export === 'prefactura') {
    outputPrefacturaXlsx($iessGroups, $form_prefactura_pid, $form_prefactura_month, $startdate, $enddate);
}
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
        .iess-month-row td {
            background: #1f2937;
            color: #ffffff;
            font-weight: bold;
            padding: 9px 10px;
        }
        .iess-patient-row {
            background: #f8fafc;
        }
        .iess-patient-row:hover {
            background: #eef6ff;
        }
        .iess-affiliation {
            display: inline-block;
            background: #e0f2fe;
            border: 1px solid #7dd3fc;
            color: #075985;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 3px;
            margin-left: 4px;
        }
        .invoice-subtable {
            margin: 8px 0 12px 0;
            width: 100%;
        }
        .invoice-subtable th {
            background: #f1f5f9;
        }
        .audit-rejected-row {
            background: #fff7ed;
        }
        .audit-rejected-row:hover {
            background: #ffedd5;
        }
        .audit-reason {
            color: #9a3412;
            font-weight: bold;
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
    <input type="hidden" name="form_export" id="form_export" value="" />
    <input type="hidden" name="form_prefactura_pid" id="form_prefactura_pid" value="" />
    <input type="hidden" name="form_prefactura_month" id="form_prefactura_month" value="" />

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
                        <tr>
                            <td class="label_custom"><?php echo xlt('IESS'); ?>:</td>
                            <td colspan="5">
                                <label>
                                    <input type="checkbox" name="form_iess_mode" id="form_iess_mode" value="1" <?php echo $form_iess_mode ? 'checked' : ''; ?>>
                                    <?php echo xlt('Planillaje IESS agrupado por mes y paciente'); ?>
                                </label>
                                <span class="muted">
                                    <?php echo xlt('Uses patient sub-affiliation from'); ?> genericval1 / genericname1.
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border-left:1px solid; padding-left:15px">
                    <a href="#" class="css_button" onclick="$('#form_export').val(''); $('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('Submit'); ?></span></a>
                    <?php if ($form_refresh) { ?>
                        <a href="#" class="css_button" id="printbutton"><span><?php echo xlt('Print'); ?></span></a>
                    <?php } ?>
                    <?php if ($form_iess_mode && $form_refresh) { ?>
                        <br><br>
                        <a href="#" class="css_button" onclick="$('#form_iess_mode').prop('checked', true); $('#form_export').val('iess_flat'); $('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('Plano 44 XLSX'); ?></span></a>
                        <br><br>
                        <a href="#" class="css_button" onclick="$('#form_iess_mode').prop('checked', true); $('#form_export').val('iess_soam'); $('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('SOAM 33 XLSX'); ?></span></a>
                        <br><br>
                        <a href="#" class="css_button" onclick="$('#form_iess_mode').prop('checked', true); $('#form_export').val('iess_audit'); $('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('Auditoría IESS XLSX'); ?></span></a>
                        <br><br>
                        <a href="#" class="css_button" onclick="$('#form_iess_mode').prop('checked', true); $('#form_export').val('prefacturas_zip'); $('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('Prefacturas ZIP'); ?></span></a>
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
        <?php if ($form_iess_mode) { ?>
            <div class="summary-cards">
                <div class="summary-card">
                    <span class="label"><?php echo xlt('Candidate Encounters'); ?></span>
                    <span class="value"><?php echo text($iessAudit['summary']['candidate_encounters']); ?></span>
                </div>
                <div class="summary-card">
                    <span class="label"><?php echo xlt('Candidate Patients'); ?></span>
                    <span class="value"><?php echo text($iessAudit['summary']['candidate_patients']); ?></span>
                </div>
                <div class="summary-card">
                    <span class="label"><?php echo xlt('Included Patients'); ?></span>
                    <span class="value"><?php echo text($iessAudit['summary']['included_patients']); ?></span>
                </div>
                <div class="summary-card">
                    <span class="label"><?php echo xlt('Excluded Encounters'); ?></span>
                    <span class="value"><?php echo text($iessAudit['summary']['excluded_encounters']); ?></span>
                </div>
                <div class="summary-card">
                    <span class="label"><?php echo xlt('Duplicate Drug Sales'); ?></span>
                    <span class="value"><?php echo text($iessAudit['summary']['duplicate_product_lines']); ?></span>
                </div>
            </div>
        <?php } ?>
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
        <?php if ($form_iess_mode) { ?>
        <details class="report-detail" open>
            <summary><?php echo xlt('IESS Audit / Excluded Encounters'); ?></summary>
            <div class="detail-panel">
                <strong class="detail-title"><?php echo xlt('Exclusion Reasons'); ?></strong>
                <table class="report-table detail-table">
                    <tr>
                        <th><?php echo xlt('Reason'); ?></th>
                        <th class="amount"><?php echo xlt('Encounters'); ?></th>
                    </tr>
                    <?php if (empty($iessAudit['summary']['reason_counts'])) { ?>
                        <tr><td colspan="2"><?php echo xlt('No audit warnings or exclusions.'); ?></td></tr>
                    <?php } ?>
                    <?php foreach ($iessAudit['summary']['reason_counts'] as $reason => $count) { ?>
                        <tr>
                            <td class="audit-reason"><?php echo text($reason); ?></td>
                            <td class="amount"><?php echo text($count); ?></td>
                        </tr>
                    <?php } ?>
                </table>

                <strong class="detail-title"><?php echo xlt('Rejected / Review Encounters'); ?></strong>
                <table class="report-table detail-table">
                    <tr>
                        <th><?php echo xlt('Date'); ?></th>
                        <th><?php echo xlt('Patient'); ?></th>
                        <th><?php echo xlt('ID'); ?></th>
                        <th><?php echo xlt('Encounter'); ?></th>
                        <th><?php echo xlt('Source'); ?></th>
                        <th><?php echo xlt('Derivation'); ?></th>
                        <th><?php echo xlt('Reason'); ?></th>
                        <th class="amount"><?php echo xlt('Billing'); ?></th>
                        <th class="amount"><?php echo xlt('Drug Sales'); ?></th>
                        <th class="amount"><?php echo xlt('Total'); ?></th>
                    </tr>
                    <?php $hasAuditRows = false; ?>
                    <?php foreach ($iessAudit['rows'] as $auditRow) { ?>
                        <?php if (!empty($auditRow['audit_included']) && empty($auditRow['duplicate_product_lines'])) {
                            continue;
                        } ?>
                        <?php $hasAuditRows = true; ?>
                        <tr class="audit-rejected-row">
                            <td><?php echo text(oeFormatShortDate(substr($auditRow['date'], 0, 10))); ?></td>
                            <td><?php echo text(patientDisplayName($auditRow)); ?></td>
                            <td><?php echo text($auditRow['pubpid']); ?></td>
                            <td><?php echo text($auditRow['encounter']); ?></td>
                            <td><?php echo text($auditRow['audit_inclusion_source']); ?></td>
                            <td><?php echo text($auditRow['audit_derivation_codes']); ?>&nbsp;</td>
                            <td class="audit-reason"><?php echo text($auditRow['audit_reason']); ?></td>
                            <td class="amount"><?php echo text(reportMoney($auditRow['billing_total'])); ?></td>
                            <td class="amount">
                                <?php echo text(reportMoney($auditRow['product_total'])); ?>
                                <?php if ((float) $auditRow['duplicate_product_total'] > 0.00) { ?>
                                    <br><span class="muted"><?php echo xlt('Excluded duplicate'); ?>: <?php echo text(reportMoney($auditRow['duplicate_product_total'])); ?></span>
                                <?php } ?>
                            </td>
                            <td class="amount"><?php echo text(reportMoney($auditRow['invoice_total'])); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (!$hasAuditRows) { ?>
                        <tr><td colspan="10"><?php echo xlt('No rejected or review encounters.'); ?></td></tr>
                    <?php } ?>
                </table>
            </div>
        </details>

        <table width="98%" class="report-table">
            <thead>
                <tr>
                    <th><?php echo xlt('Planilla'); ?></th>
                    <th><?php echo xlt('Group'); ?></th>
                    <th><?php echo xlt('Patient'); ?></th>
                    <th><?php echo xlt('ID'); ?></th>
                    <th><?php echo xlt('IESS Affiliation'); ?></th>
                    <th><?php echo xlt('Dates'); ?></th>
                    <th><?php echo xlt('Invoices'); ?></th>
                    <th><?php echo xlt('Providers'); ?></th>
                    <th><?php echo xlt('Facility'); ?></th>
                    <th class="amount"><?php echo xlt('Charges'); ?></th>
                    <th class="amount"><?php echo xlt('Copay Paid'); ?></th>
                    <th class="amount"><?php echo xlt('Total'); ?></th>
                    <th><?php echo xlt('Details'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($iessGroups)) { ?>
                    <tr><td colspan="13"><?php echo xlt('No IESS records found for this range.'); ?></td></tr>
                <?php } ?>
                <?php foreach ($iessGroups as $monthGroup) { ?>
                    <tr class="iess-month-row">
                        <td colspan="13">
                            <?php echo text(monthDisplay($monthGroup['month'])); ?>
                            - <?php echo text(count($monthGroup['patients'])); ?> <?php echo xlt('patients'); ?>
                            - <?php echo text($monthGroup['invoice_count']); ?> <?php echo xlt('invoices'); ?>
                            - <?php echo text(reportMoney($monthGroup['total'])); ?>
                        </td>
                    </tr>
                    <?php foreach ($monthGroup['patients'] as $patientGroup) { ?>
                        <tr class="iess-patient-row">
                            <td><?php echo text($patientGroup['planilla_number']); ?></td>
                            <td><?php echo text($patientGroup['planilla_group']); ?></td>
                            <td><?php echo text($patientGroup['patient_name']); ?></td>
                            <td><?php echo text($patientGroup['pubpid']); ?></td>
                            <td>
                                <?php echo text($patientGroup['sub_affiliation']); ?>
                                <?php if ($patientGroup['sub_affiliation_abbr'] !== '') { ?>
                                    <span class="iess-affiliation"><?php echo text($patientGroup['sub_affiliation_abbr']); ?></span>
                                <?php } ?>
                                <?php if ($patientGroup['sub_affiliation_name'] !== '') { ?>
                                    <br><span class="muted"><?php echo text($patientGroup['sub_affiliation_name']); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo text(oeFormatShortDate($patientGroup['first_date'])); ?>
                                -
                                <?php echo text(oeFormatShortDate($patientGroup['last_date'])); ?>
                            </td>
                            <td class="amount"><?php echo text($patientGroup['invoice_count']); ?></td>
                            <td><?php echo text(implode(', ', array_keys($patientGroup['providers']))); ?>&nbsp;</td>
                            <td><?php echo text(implode(', ', array_keys($patientGroup['facilities']))); ?>&nbsp;</td>
                            <td class="amount"><?php echo text(reportMoney($patientGroup['charges_total'])); ?></td>
                            <td class="amount"><?php echo text(reportMoney($patientGroup['copay_total'])); ?></td>
                            <td class="amount money-strong"><?php echo text(reportMoney($patientGroup['invoice_total'])); ?></td>
                            <td>
                                <a href="#" class="css_button" onclick="$('#form_iess_mode').prop('checked', true); $('#form_prefactura_pid').val('<?php echo attr($patientGroup['pid']); ?>'); $('#form_prefactura_month').val('<?php echo attr($monthGroup['month']); ?>'); $('#form_export').val('prefactura'); $('#form_refresh').val('true'); $('#theform').submit();"><span><?php echo xlt('Prefactura XLSX'); ?></span></a>
                                <br><br>
                                <details class="report-detail">
                                    <summary><?php echo xlt('View Planillaje'); ?></summary>
                                    <table class="report-table invoice-subtable">
                                        <tr>
                                            <th><?php echo xlt('Date'); ?></th>
                                            <th><?php echo xlt('Invoice'); ?></th>
                                            <th><?php echo xlt('Provider'); ?></th>
                                            <th><?php echo xlt('Facility'); ?></th>
                                            <th><?php echo xlt('Source'); ?></th>
                                            <th><?php echo xlt('Derivation'); ?></th>
                                            <th class="amount"><?php echo xlt('Charges'); ?></th>
                                            <th class="amount"><?php echo xlt('Total'); ?></th>
                                            <th><?php echo xlt('Details'); ?></th>
                                        </tr>
                                        <?php foreach ($patientGroup['invoices'] as $row) { ?>
                                            <tr>
                                                <td><?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))); ?></td>
                                                <td><span class="invoice-link"><?php echo text(invoiceDisplayNumber($row)); ?></span></td>
                                                <td><?php echo text(providerDisplayName($row)); ?></td>
                                                <td><?php echo text($row['facility_name']); ?></td>
                                                <td><?php echo text($row['audit_inclusion_source']); ?></td>
                                                <td><?php echo text($row['audit_derivation_codes']); ?>&nbsp;</td>
                                                <td class="amount"><?php echo text(reportMoney($row['charges_total'])); ?></td>
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
                                                                        <td><span class="code-chip"><?php echo text($line['code_type']); ?></span></td>
                                                                        <td><?php echo text($line['code_category']); ?>&nbsp;</td>
                                                                        <td class="code-number"><?php echo text(trim($line['code'] . ' ' . $line['modifier'])); ?></td>
                                                                        <td class="description-cell"><?php echo text($line['code_text']); ?></td>
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
                                        <?php } ?>
                                    </table>
                                </details>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
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
        <?php } ?>
    </div>
<?php } ?>

</body>
</html>
