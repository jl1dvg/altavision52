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

$patient = getPatientData($pid, "pubpid,fname,mname,lname,lname2,street,city,phone_home,phone_biz");
$providerId = getProviderIdOfEncounter($encounter);
$providerName = getProviderName($providerId);

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

$empresa = getLbfValue($formId, array('empresa', 'empresa_lab', 'empresa_trabajo', 'Prot_empresa'), array('EMPRESA'));
$puestoTrabajo = getLbfValue($formId, array('puesto_trabajo', 'puesto', 'ocupacion', 'Prot_puesto'), array('PUESTO'));
$tipoContingencia = getLbfValue($formId, array('tipo_contingencia', 'contingencia', 'Prot_contingencia'), array('CONTINGENCIA'));
$fechaIngreso = getLbfValue($formId, array('fecha_ingreso', 'f_ingreso', 'Prot_fecha_ingreso'), array('FECHA DE INGRESO'));
$fechaEgreso = getLbfValue($formId, array('fecha_egreso', 'f_egreso', 'Prot_fecha_egreso'), array('FECHA DE EGRESO'));
$tratamiento = getLbfValue($formId, array('tratamiento', 'Prot_tratamiento'), array('TRATAMIENTO'));
$procedimientoLbf = getLbfValue($formId, array('procedimiento', 'Prot_proced'), array('PROCEDIMIENTO'));
$diagnosticoIngresoLbf = getLbfValue($formId, array('diagnostico_ingreso', 'dx_ingreso', 'Prot_dxpre'), array('DIAGNOSTICO DE INGRESO'));
$diagnosticoEgresoLbf = getLbfValue($formId, array('diagnostico_egreso', 'dx_egreso', 'Prot_dxpost'), array('DIAGNOSTICO DE EGRESO'));

$diagnosticoIngresoProt = parseDiagnosis(getFieldValue($formId, 'Prot_dxpre'));
$diagnosticoEgresoProt = parseDiagnosis(getFieldValue($formId, 'Prot_dxpost'));
$procedimientoProt = trim(obtenerIntervencionesPropuestas(getFieldValue($formId, 'Prot_opr')) . ' ' . eyeLabel(getFieldValue($formId, 'Prot_ojo')));

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

$meses = array(
    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
);
$facilityCity = strtoupper(firstValue(array($facility['city'] ?? '', 'GUAYAQUIL')));
$fechaLinea = $facilityCity . ', ' . $meses[intval($restStartDate->format('n'))] . ' ' . $restStartDate->format('d') . ' DEL ' . $restStartDate->format('Y');

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
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .top { width: 100%; margin-bottom: 6px; }
        .title { font-size: 18px; font-weight: bold; text-align: center; margin: 6px 0 12px 0; }
        .date-line { font-size: 12px; margin-bottom: 8px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { padding: 4px 6px; vertical-align: top; border-bottom: 1px solid #ddd; }
        .label { width: 30%; font-weight: bold; }
        .rest-line { margin-top: 14px; font-size: 12px; }
        .sign { margin-top: 40px; }
    </style>
</head>
<body>
<table class="top">
    <tr>
        <td style="width: 35%;"><?php echo $logo; ?></td>
        <td style="width: 65%; text-align: right;">
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
    <tr><td class="label">HISTORIA CLINICA:</td><td><?php echo h($historiaClinica); ?></td></tr>
    <tr><td class="label">NOMBRE DEL PACIENTE:</td><td><?php echo h($nombrePaciente); ?></td></tr>
    <tr><td class="label">DOMICILIO:</td><td><?php echo h($domicilio); ?></td></tr>
    <tr><td class="label">TELEFONO DE CONTACTO:</td><td><?php echo h($telefono); ?></td></tr>
    <tr><td class="label">CEDULA DEL PACIENTE:</td><td><?php echo h($cedula); ?></td></tr>
    <tr><td class="label">EMPRESA:</td><td><?php echo h($empresa); ?></td></tr>
    <tr><td class="label">PUESTO DE TRABAJO:</td><td><?php echo h($puestoTrabajo); ?></td></tr>
    <tr><td class="label">FECHA DE INGRESO:</td><td><?php echo h($fechaIngreso); ?></td></tr>
    <tr><td class="label">TIPO CONTINGENCIA:</td><td><?php echo h($tipoContingencia); ?></td></tr>
    <tr><td class="label">DIAGNOSTICO DE INGRESO:</td><td><?php echo h($diagnosticoIngreso); ?></td></tr>
    <tr><td class="label">TRATAMIENTO:</td><td><?php echo h($tratamiento); ?></td></tr>
    <tr><td class="label">PROCEDIMIENTO:</td><td><?php echo h($procedimiento); ?></td></tr>
    <tr><td class="label">FECHA DE EGRESO:</td><td><?php echo h($fechaEgreso); ?></td></tr>
    <tr><td class="label">DIAGNOSTICO DE EGRESO:</td><td><?php echo h($diagnosticoEgreso); ?></td></tr>
</table>

<p class="rest-line">
    <strong>DIAS DE DESCANSO:</strong> <?php echo h($restDays); ?>
    <strong>DESDE</strong> <?php echo h($restStartDate->format('d/m/Y')); ?>
    <strong>HASTA</strong> <?php echo h($restEndDate->format('d/m/Y')); ?>
</p>

<div class="sign">
    <strong><?php echo h($providerName); ?></strong><br>
    <?php echo h($facility['name'] ?? ''); ?>
</div>
</body>
</html>
<?php
$html = ob_get_clean();
$pdf->WriteHTML($html);
$pdf->Output('certificado_descanso_' . preg_replace('/\s+/', '_', $nombrePaciente) . '.pdf', 'I');

