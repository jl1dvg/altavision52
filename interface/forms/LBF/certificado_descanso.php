<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/iess.inc.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Services\FacilityService;
use Mpdf\Mpdf;

function firstValue(array $values)
{
    foreach ($values as $value) {
        if (isset($value) && trim((string) $value) !== '') {
            return trim((string) $value);
        }
    }
    return '';
}

function h($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES);
}

function getLbfFieldById($formId, array $fieldIds)
{
    foreach ($fieldIds as $fieldId) {
        $row = sqlQuery("SELECT field_value FROM lbf_data WHERE form_id = ? AND field_id = ?", array($formId, $fieldId));
        if (!empty($row['field_value'])) {
            return trim((string) $row['field_value']);
        }
    }
    return '';
}

function getLbfFieldByTitle($formId, array $titlePatterns)
{
    foreach ($titlePatterns as $titlePattern) {
        $row = sqlQuery(
            "SELECT lbf.field_value
               FROM lbf_data AS lbf
               INNER JOIN layout_options AS lo ON lo.field_id = lbf.field_id
              WHERE lbf.form_id = ?
                AND lo.title LIKE ?
              LIMIT 1",
            array($formId, '%' . $titlePattern . '%')
        );
        if (!empty($row['field_value'])) {
            return trim((string) $row['field_value']);
        }
    }
    return '';
}

function getLbfValue($formId, array $fieldIds, array $titlePatterns)
{
    return firstValue(array(
        getLbfFieldById($formId, $fieldIds),
        getLbfFieldByTitle($formId, $titlePatterns)
    ));
}

function getLatestProtocolFormId($pid, $encounter, $currentFormName = '', $currentFormId = 0)
{
    if ($currentFormName === 'LBFprotocolo') {
        return intval($currentFormId);
    }

    $row = sqlQuery(
        "SELECT form_id
           FROM forms
          WHERE pid = ?
            AND formdir = 'LBFprotocolo'
            AND deleted = 0
            AND encounter <= ?
          ORDER BY date DESC, id DESC
          LIMIT 1",
        array($pid, $encounter)
    );

    return intval($row['form_id'] ?? 0);
}

function normalizeDateValue($value, $fallback)
{
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }
    $timestamp = strtotime(str_replace('/', '-', $value));
    if ($timestamp) {
        return date('d/m/Y', $timestamp);
    }
    return $value;
}

function parseDiagnosis($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $description = lookup_code_short_descriptions($value);
    if (!empty($description)) {
        $code = $value;
        if (strpos($value, ':') !== false) {
            $parts = explode(':', $value, 2);
            $code = $parts[1];
        }
        return trim($description) . " (" . trim($code) . ")";
    }
    return $value;
}

function eyeLabel($eyeCode)
{
    $eyeCode = trim((string) $eyeCode);
    if ($eyeCode === 'OD') {
        return 'OJO DERECHO';
    }
    if ($eyeCode === 'OI') {
        return 'OJO IZQUIERDO';
    }
    if ($eyeCode === 'AO') {
        return 'AMBOS OJOS';
    }
    return '';
}

function monthNameUpper($month)
{
    static $months = array(
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    );

    return $months[intval($month)] ?? '';
}

function numberToSpanishWords($number)
{
    $number = intval($number);
    if ($number < 0) {
        return '';
    }

    $units = array(
        0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
        6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
        11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
        16 => 'dieciseis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
        20 => 'veinte', 21 => 'veintiuno', 22 => 'veintidos', 23 => 'veintitres',
        24 => 'veinticuatro', 25 => 'veinticinco', 26 => 'veintiseis', 27 => 'veintisiete',
        28 => 'veintiocho', 29 => 'veintinueve'
    );
    $tens = array(
        30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta', 60 => 'sesenta',
        70 => 'setenta', 80 => 'ochenta', 90 => 'noventa'
    );
    $hundreds = array(
        100 => 'cien', 200 => 'doscientos', 300 => 'trescientos', 400 => 'cuatrocientos',
        500 => 'quinientos', 600 => 'seiscientos', 700 => 'setecientos',
        800 => 'ochocientos', 900 => 'novecientos'
    );

    if ($number <= 29) {
        return $units[$number];
    }

    if ($number < 100) {
        $base = intval(floor($number / 10) * 10);
        $rest = $number % 10;
        return $rest ? $tens[$base] . ' y ' . $units[$rest] : $tens[$base];
    }

    if ($number === 100) {
        return 'cien';
    }

    if ($number < 200) {
        return 'ciento ' . numberToSpanishWords($number - 100);
    }

    if ($number < 1000) {
        $base = intval(floor($number / 100) * 100);
        $rest = $number % 100;
        return $rest ? $hundreds[$base] . ' ' . numberToSpanishWords($rest) : $hundreds[$base];
    }

    if ($number === 1000) {
        return 'mil';
    }

    if ($number < 2000) {
        return 'mil ' . numberToSpanishWords($number - 1000);
    }

    if ($number < 1000000) {
        $base = intval(floor($number / 1000));
        $rest = $number % 1000;
        $prefix = numberToSpanishWords($base) . ' mil';
        return $rest ? $prefix . ' ' . numberToSpanishWords($rest) : $prefix;
    }

    return (string) $number;
}

function numberToSpanishWordsUpper($number)
{
    return strtoupper(numberToSpanishWords($number));
}

function formatDateLine(DateTime $date, $city)
{
    return strtoupper(trim((string) $city)) . ', ' . monthNameUpper($date->format('n')) . ' ' . $date->format('d') . ' DE ' . $date->format('Y');
}

function formatDateWordsRange(DateTime $startDate, DateTime $endDate)
{
    $startDay = ltrim($startDate->format('d'), '0');
    $endDay = ltrim($endDate->format('d'), '0');

    return sprintf(
        '(DESDE %s de %s del %s HASTA %s de %s del %s)',
        numberToSpanishWords((int) $startDay),
        strtolower(monthNameUpper($startDate->format('n'))),
        numberToSpanishWords((int) $startDate->format('Y')),
        numberToSpanishWords((int) $endDay),
        strtolower(monthNameUpper($endDate->format('n'))),
        numberToSpanishWords((int) $endDate->format('Y'))
    );
}

$pid = isset($_GET['patientid']) ? intval($_GET['patientid']) : 0;
$encounter = isset($_GET['visitid']) ? intval($_GET['visitid']) : 0;
$formId = isset($_GET['formid']) ? intval($_GET['formid']) : 0;
$formName = isset($_GET['formname']) ? trim((string) $_GET['formname']) : '';
$restDays = isset($_GET['rest_days']) ? intval($_GET['rest_days']) : 15;
$restDays = max(1, min(120, $restDays));
$restStartRaw = isset($_GET['rest_start']) ? trim((string) $_GET['rest_start']) : '';

if (!$pid || !$encounter || !$formId) {
    http_response_code(400);
    echo xlt('Faltan parámetros requeridos para generar el certificado.');
    exit;
}

$encounterDate = getEncounterDateByEncounter($encounter);
$encounterDateValue = !empty($encounterDate['date']) ? $encounterDate['date'] : date('Y-m-d');
$encounterDateObj = new DateTime($encounterDateValue);

$restStartDate = DateTime::createFromFormat('Y-m-d', $restStartRaw);
if (!$restStartDate) {
    $restStartDate = clone $encounterDateObj;
}
$restEndDate = clone $restStartDate;
$restEndDate->modify('+' . ($restDays - 1) . ' day');

$facilityService = new FacilityService();
$facility = $_SESSION['pc_facility'] ? $facilityService->getById($_SESSION['pc_facility']) : $facilityService->getPrimaryBillingLocation();

$patient = getPatientData($pid, "pubpid,fname,mname,lname,lname2,street,city,phone_home,phone_biz,occupation");
$employer = getEmployerData($pid, "name");
$providerId = getProviderIdOfEncounter($encounter);
$providerName = getProviderName($providerId);
$protocolFormId = getLatestProtocolFormId($pid, $encounter, $formName, $formId);

$nombrePaciente = trim(
    firstValue(array($patient['lname'] ?? '')) . ' ' .
    firstValue(array($patient['lname2'] ?? '')) . ' ' .
    firstValue(array($patient['fname'] ?? '')) . ' ' .
    firstValue(array($patient['mname'] ?? ''))
);
$historiaClinica = firstValue(array($patient['pubpid'] ?? ''));
$cedula = firstValue(array($patient['pubpid'] ?? ''));
$domicilio = trim(firstValue(array($patient['street'] ?? '')) . ' ' . firstValue(array($patient['city'] ?? '')));
$telefono = firstValue(array($patient['phone_home'] ?? '', $patient['phone_biz'] ?? ''));

$empresa = firstValue(array($employer['name'] ?? ''));
$puestoTrabajo = firstValue(array($patient['occupation'] ?? ''));
$tipoContingencia = getLbfValue($formId, array('tipo_contingencia', 'contingencia', 'Prot_contingencia'), array('CONTINGENCIA'));
$fechaIngreso = getLbfValue($formId, array('fecha_ingreso', 'f_ingreso', 'Prot_fecha_ingreso'), array('FECHA DE INGRESO'));
$fechaEgreso = getLbfValue($formId, array('fecha_egreso', 'f_egreso', 'Prot_fecha_egreso'), array('FECHA DE EGRESO'));
$tratamiento = getLbfValue($formId, array('tratamiento', 'Prot_tratamiento'), array('TRATAMIENTO'));
$procedimientoLbf = getLbfValue($formId, array('procedimiento', 'Prot_proced'), array('PROCEDIMIENTO'));
$diagnosticoIngresoLbf = getLbfValue($formId, array('diagnostico_ingreso', 'dx_ingreso', 'Prot_dxpre'), array('DIAGNOSTICO DE INGRESO'));
$diagnosticoEgresoLbf = getLbfValue($formId, array('diagnostico_egreso', 'dx_egreso', 'Prot_dxpost'), array('DIAGNOSTICO DE EGRESO'));

$diagnosticoIngresoProt = '';
$diagnosticoEgresoProt = '';
$procedimientoProt = '';
if ($protocolFormId > 0) {
    $diagnosticoIngresoProt = parseDiagnosis(getFieldValue($protocolFormId, 'Prot_dxpre'));
    $diagnosticoEgresoProt = parseDiagnosis(getFieldValue($protocolFormId, 'Prot_dxpost'));
    $procedimientoProt = trim(
        obtenerIntervencionesPropuestas(getFieldValue($protocolFormId, 'Prot_opr')) . ' ' .
        eyeLabel(getFieldValue($protocolFormId, 'Prot_ojo'))
    );
}

$diagnosticoIngreso = firstValue(array($diagnosticoIngresoLbf, $diagnosticoIngresoProt));
$diagnosticoEgreso = firstValue(array($diagnosticoEgresoLbf, $diagnosticoEgresoProt));
$procedimiento = firstValue(array($procedimientoLbf, $procedimientoProt));

$fechaIngreso = normalizeDateValue($fechaIngreso, $encounterDateObj->format('d/m/Y'));
$fechaEgreso = normalizeDateValue($fechaEgreso, $restStartDate->format('d/m/Y'));
$tratamiento = firstValue(array($tratamiento, 'AMBULATORIO QUIRURGICO'));
$tipoContingencia = firstValue(array($tipoContingencia, 'ENFERMEDAD GENERAL'));
$empresa = firstValue(array($empresa, '-'));
$puestoTrabajo = firstValue(array($puestoTrabajo, '-'));
$domicilio = firstValue(array($domicilio, '-'));
$telefono = firstValue(array($telefono, '-'));
$diagnosticoIngreso = firstValue(array($diagnosticoIngreso, '-'));
$diagnosticoEgreso = firstValue(array($diagnosticoEgreso, '-'));
$procedimiento = firstValue(array($procedimiento, '-'));

$facilityCity = strtoupper(firstValue(array($facility['city'] ?? '', 'GUAYAQUIL')));
$fechaLinea = formatDateLine($restStartDate, $facilityCity);
$descansoTexto = numberToSpanishWordsUpper($restDays) . ' (' . $restDays . ')';
$descansoEtiqueta = $restDays === 1 ? 'DIA DE REPOSO ABSOLUTO:' : 'DIAS DE REPOSO ABSOLUTO:';
$descansoRangoTexto = formatDateWordsRange($restStartDate, $restEndDate);
$providerSpecialty = trim((string) getProviderEspecialidad($providerId));
$providerSpecialty = $providerSpecialty !== '' ? strtoupper($providerSpecialty) : strtoupper((string) ($facility['name'] ?? ''));
$providerRegistro = trim((string) getProviderRegistro($providerId));
$providerIdentification = trim((string) getProviderIdentification($providerId));

$logo = '';
$maLogoPath = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file($webserver_root . "/" . $maLogoPath)) {
    $logo = "<img src='" . h($web_root . "/" . $maLogoPath) . "' style='width:140px;'>";
}

$configMpdf = array(
    'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
    'mode' => $GLOBALS['pdf_language'],
    'format' => 'A4-P',
    'margin_left' => $GLOBALS['pdf_left_margin'],
    'margin_right' => $GLOBALS['pdf_right_margin'],
    'margin_top' => 15,
    'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
    'orientation' => $GLOBALS['pdf_layout']
);

$pdf = new Mpdf($configMpdf);
$pdf->SetDisplayMode('real');
if ($_SESSION['language_direction'] == 'rtl') {
    $pdf->SetDirectionality('rtl');
}

ob_start();
?>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
        .sheet { width: 100%; }
        .brand { width: 100%; margin-bottom: 4px; }
        .brand-left { width: 45%; font-size: 12px; font-weight: bold; vertical-align: middle; }
        .brand-right { width: 55%; text-align: right; vertical-align: top; font-size: 10px; line-height: 1.35; }
        .title { font-size: 16px; font-weight: bold; text-align: left; margin: 6px 0 4px 0; }
        .date-line { font-size: 12px; margin: 0 0 8px 0; }
        .grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid td { padding: 3px 0; vertical-align: top; font-size: 11px; }
        .label { width: 31%; font-weight: bold; }
        .value { width: 69%; }
        .spacer td { padding: 6px 0; font-size: 4px; }
        .rest-line { margin-top: 12px; font-size: 11px; }
        .rest-line strong { font-weight: bold; }
        .rest-range { margin-top: 4px; font-size: 11px; }
        .signature { margin-top: 42px; width: 100%; }
        .signature-line { width: 44%; border-top: 1px solid #000; margin-bottom: 6px; }
        .signature-name { font-size: 11px; font-weight: bold; }
        .signature-role { font-size: 11px; }
        .signature-meta { font-size: 10px; }
    </style>
</head>
<body>
<div class="sheet">
<table class="brand">
    <tr>
        <td class="brand-left">
            <?php echo $logo ? $logo : h(strtoupper((string) ($facility['name'] ?? ''))); ?>
        </td>
        <td class="brand-right">
            <strong><?php echo h($facility['name'] ?? ''); ?></strong><br>
            <?php echo h($facility['street'] ?? ''); ?><br>
            <?php echo h($facility['city'] ?? ''); ?> <?php echo h($facility['postal_code'] ?? ''); ?><br>
            <?php echo h($facility['phone'] ?? ''); ?>
        </td>
    </tr>
</table>

<div class="title">CERTIFICADO MEDICO</div>
<div class="date-line"><?php echo h($fechaLinea); ?></div>

<table class="grid">
    <tr><td class="label">HISTORIA CLINICA:</td><td class="value"><?php echo h($historiaClinica); ?></td></tr>
    <tr><td class="label">NOMBRE DEL PACIENTE:</td><td class="value"><?php echo h($nombrePaciente); ?></td></tr>
    <tr><td class="label">DOMICILIO:</td><td class="value"><?php echo h($domicilio); ?></td></tr>
    <tr><td class="label">TELEFONO DE CONTACTO:</td><td class="value"><?php echo h($telefono); ?></td></tr>
    <tr><td class="label">CEDULA DEL PACIENTE:</td><td class="value"><?php echo h($cedula); ?></td></tr>
    <tr><td class="label">EMPRESA:</td><td class="value"><?php echo h($empresa); ?></td></tr>
    <tr><td class="label">PUESTO DE TRABAJO:</td><td class="value"><?php echo h($puestoTrabajo); ?></td></tr>
    <tr class="spacer"><td colspan="2">&nbsp;</td></tr>
    <tr><td class="label">TIPO CONTINGENCIA:</td><td class="value"><?php echo h($tipoContingencia); ?></td></tr>
    <tr class="spacer"><td colspan="2">&nbsp;</td></tr>
    <tr><td class="label">DIAGNOSTICO DE INGRESO:</td><td class="value"><?php echo h($diagnosticoIngreso); ?></td></tr>
    <tr class="spacer"><td colspan="2">&nbsp;</td></tr>
    <tr><td class="label">TRATAMIENTO:</td><td class="value"><?php echo h($tratamiento); ?></td></tr>
    <tr><td class="label">FECHA DE INGRESO:</td><td class="value"><?php echo h($fechaIngreso); ?></td></tr>
    <tr class="spacer"><td colspan="2">&nbsp;</td></tr>
    <tr><td class="label">PROCEDIMIENTO:</td><td class="value"><?php echo h($procedimiento); ?></td></tr>
    <tr class="spacer"><td colspan="2">&nbsp;</td></tr>
    <tr><td class="label">FECHA DE EGRESO:</td><td class="value"><?php echo h($fechaEgreso); ?></td></tr>
    <tr class="spacer"><td colspan="2">&nbsp;</td></tr>
    <tr><td class="label">DIAGNOSTICO DE EGRESO:</td><td class="value"><?php echo h($diagnosticoEgreso); ?></td></tr>
</table>

<p class="rest-line">
    <strong><?php echo h($descansoEtiqueta); ?></strong> <?php echo h($descansoTexto); ?>
    <strong>DESDE</strong> <?php echo h($restStartDate->format('d/m/Y')); ?>
    <strong>HASTA</strong> <?php echo h($restEndDate->format('d/m/Y')); ?>
</p>
<p class="rest-range"><?php echo h($descansoRangoTexto); ?></p>

<div class="signature">
    <div class="signature-line"></div>
    <div class="signature-name"><?php echo h(strtoupper($providerName)); ?></div>
    <div class="signature-role"><?php echo h($providerSpecialty); ?></div>
    <?php if ($providerRegistro !== '') { ?>
        <div class="signature-meta">REGISTRO MEDICO: <?php echo h($providerRegistro); ?></div>
    <?php } ?>
    <?php if ($providerIdentification !== '') { ?>
        <div class="signature-meta">IDENTIFICACION: <?php echo h($providerIdentification); ?></div>
    <?php } ?>
</div>
</div>
</body>
</html>
<?php
$html = ob_get_clean();
$pdf->WriteHTML($html);
$pdf->Output('certificado_descanso_' . preg_replace('/\s+/', '_', $nombrePaciente) . '.pdf', 'I');
