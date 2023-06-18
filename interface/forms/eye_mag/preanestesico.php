<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
include_once($GLOBALS["srcdir"] . "/api.inc");
require_once(dirname(__FILE__) . "/../../../library/lists.inc");

use OpenEMR\Services\FacilityService;

$form_name = "eye_mag";
$form_folder = "eye_mag";

$facilityService = new FacilityService();

require_once("../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");

if ($_REQUEST['ptid']) {
    $pid = $_REQUEST['ptid'];
}

if ($_REQUEST['encid']) {
    $encounter = $_REQUEST['encid'];
}

if ($_REQUEST['formid']) {
    $form_id = $_REQUEST['formid'];
}

if ($_REQUEST['formname']) {
    $form_name = $_REQUEST['formname'];
}

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname,lname2,sex,pricelevel, providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

//Fecha del form_eye_mag
$query = "select form_encounter.date as encounter_date,form_eye_mag.id as form_id,form_encounter.*, form_eye_mag.*
        from form_eye_mag ,forms,form_encounter
        where
        form_encounter.encounter =? and
        form_encounter.encounter = forms.encounter and
        form_eye_mag.id=forms.form_id and
        forms.deleted != '1' and
        form_eye_mag.pid=? ";
$queryform = "select * from forms
                where
                pid=? and
                encounter=? and
                formdir = 'eye_mag' and
                deleted = 0";

$fechaINGRESO = sqlQuery($queryform, array($_GET['patientid'], $_GET['visitid']));
$encounter_data = sqlQuery($query, array($encounter, $pid));
@extract($encounter_data);
$providerID = getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);
$providerRegistro = getProviderRegistro($providerID);
$dated = new DateTime($encounter_date);
$dateddia = date("d", strtotime($fechaINGRESO['date']));
$datedmes = date("F", strtotime($fechaINGRESO['date']));
$datedano = date("Y", strtotime($fechaINGRESO['date']));
$visit_date = oeFormatShortDate($dated);
$mes = date('F', $timestamp);
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$nombreMes = str_replace($meses_EN, $meses_ES, $datedmes);

$facility = null;
if ($_SESSION['pc_facility']) {
    $facility = $facilityService->getById($_SESSION['pc_facility']);
} else {
    $facility = $facilityService->getPrimaryBillingLocation();
}
//Fin fecha del form_eye_mag

//Obtener en que consiste el procedimiento
function getPropositos($form_id, $pid)
{
    $query1 = "SELECT id, form_id, pid, ORDER_DETAILS, option_id, notes FROM form_eye_mag_ordenqxod
               LEFT JOIN list_options on ORDER_DETAILS = title
               WHERE form_id=? and pid=? and list_id=? ORDER BY id ASC";
    $PLAN_results1 = sqlStatement($query1, array($form_id, $pid, 'cirugia_propuesta_defaults'));

    $propositos = array();

    if (!empty($PLAN_results1)) {
        while ($plan_row1 = sqlFetchArray($PLAN_results1)) {
            $Proposito = "SELECT * FROM list_options
                          WHERE list_id = 'Proposito_Riesgo' and option_id = ? ";
            $propositoITEM = sqlQuery($Proposito, array($plan_row1['option_id']));

            if (!empty($propositoITEM)) {
                $propositos[] = $propositoITEM['title'];
            }
        }
    }

    return $propositos;
}

function extractItemsFromQuery($form_id, $pid)
{
    $query = "SELECT c.name, c.consiste, c.realiza, c.realiza, c.grafico, c.duracion, c.beneficios, c.riesgos, c.riesgos_graves, c.alternativas, c.post, c.consecuencias FROM form_eye_mag_ordenqxod AS o
           LEFT JOIN list_options AS l on o.ORDER_DETAILS = l.title
           LEFT JOIN consentimiento_informado AS c on c.name = l.notes
           WHERE o.form_id=? and o.pid=? and l.list_id='cirugia_propuesta_defaults' ORDER BY o.id ASC";

    $results = sqlStatement($query, array($form_id, $pid));

    $items = array();

    if (!empty($results)) {
        while ($row = sqlFetchArray($results)) {
            // Extraer los datos de cada item
            $name = $row['name'];
            $consiste = $row['consiste'];
            $realiza = $row['realiza'];
            $grafico = $row['grafico'];
            $duracion = $row['duracion'];
            $beneficios = $row['beneficios'];
            $riesgos = $row['riesgos'];
            $riesgos_graves = $row['riesgos_graves'];
            $alternativas = $row['alternativas'];
            $post = $row['post'];
            $consecuencias = $row['consecuencias'];

            // Crear un array con los datos extraídos del item
            $item = array(
                'name' => $name,
                'consiste' => $consiste,
                'realiza' => $realiza,
                'grafico' => $grafico,
                'duracion' => $duracion,
                'beneficios' => $beneficios,
                'riesgos' => $riesgos,
                'riesgos_graves' => $riesgos_graves,
                'alternativas' => $alternativas,
                'post' => $post,
                'consecuencias' => $consecuencias
            );

            // Agregar el item al array de items
            $items[] = $item;
        }
    }

    return $items;
}

function obtenerCodigosImpPlan($form_folder, $form_id, $pid)
{
    $query = "SELECT * FROM form_" . $form_folder . "_impplan WHERE form_id=? AND pid=? ORDER BY IMPPLAN_order ASC";
    $result = sqlStatement($query, array($form_id, $pid));
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    $codigosImpPlan = array();

    while ($ip_list = sqlFetchArray($result)) {
        $newdata = array(
            'form_id' => $ip_list['form_id'],
            'pid' => $ip_list['pid'],
            'title' => $ip_list['title'],
            'code' => $ip_list['code'],
            'codetype' => $ip_list['codetype'],
            'codetext' => $ip_list['codetext'],
            'codedesc' => $ip_list['codedesc'],
            'plan' => str_replace($order, $replace, $ip_list['plan']),
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        );

        $pattern = '/Code/';
        if (preg_match($pattern, $newdata['code'])) {
            $newdata['code'] = '';
        }

        if ($newdata['codetext'] > '') {
            $codigosImpPlan['code'][] = $newdata['code'];
            $codigosImpPlan['codedesc'][] = $newdata['codedesc'];
        }
    }

    return $codigosImpPlan;
}

$resultado = obtenerCodigosImpPlan($form_folder, $form_id, $pid);
$codes = $resultado['code']; // Array con los valores de 'code'
$codedescs = $resultado['codedesc']; // Array con los valores de 'codedesc'

//Inicio PDF
$FONTSIZE = 9;
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    // Would use max-height here but html2pdf does not support it.
    // TODO - now use mPDF, so should test if still need this fix
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round($FONTSIZE * 7.50)) . "pt' />";
} else {
    $logo = "<!-- '$ma_logo_path' does not exist. -->";
}

use Mpdf\Mpdf;

// Font size in points for table cell data.
$FONTSIZE = 9;
$formid = $_GET['formid'];

// Html2pdf fails to generate checked checkboxes properly, so write plain HTML
// if we are doing a visit-specific form to be completed.
// TODO - now use mPDF, so should test if still need this fix
$PDF_OUTPUT = $formid;
//$PDF_OUTPUT = false; // debugging

if ($PDF_OUTPUT) {
$config_mpdf = array(
    'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
    'mode' => $GLOBALS['pdf_language'],
    'format' => 'A4-P',
    'default_font_size' => '10',
    'default_font' => '"Arial","sans-serif"',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 8,
    'margin_bottom' => 8,
    'margin_header' => '',
    'margin_footer' => '',
    'orientation' => $GLOBALS['pdf_layout'],
    'shrink_tables_to_fit' => 1,
    'use_kwt' => true,
    'autoScriptToLang' => true,
    'keep_table_proportions' => true
);
$pdf = new mPDF($config_mpdf);
$pdf->SetDisplayMode('real');
if ($_SESSION['language_direction'] == 'rtl') {
    $pdf->SetDirectionality('rtl');
}
ob_start();
?>
<html>
<head>
    <style>
        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: fixed;
        }

        td.morado {
            text-align: left;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 9pt;
            font-weight: bold;
            height: 23px;
        }

        td.verde {
            height: 23px;
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.verde_normal {
            height: 23px;
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.verde_left {
            height: 23px;
            text-align: left;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.blanco {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 21px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }

        td.blanco_left {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 21px;
            text-align: left;
            vertical-align: middle;
            font-size: 7pt;
        }
    </style>
</head>
<body>
<TABLE>
    <tr>
        <td colspan="71" class="morado">A. DATOS DEL ESTABLECIMIENTO
            Y USUARIO / PACIENTE
        </td>
    </tr>
    <tr>
        <td colspan="15" height="27" class="verde">INSTITUCIÓN DEL SISTEMA</td>
        <td colspan="6" class="verde">UNICÓDIGO</td>
        <td colspan="18" class="verde">ESTABLECIMIENTO DE SALUD</td>
        <td colspan="18" class="verde">NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
        <td colspan="14" class="verde" style="border-right: none">NÚMERO DE ARCHIVO</td>
    </tr>
    <tr>
        <td colspan="15" height="27" class="blanco"><?php echo $titleres['pricelevel']; ?></td>
        <td colspan="6" class="blanco">&nbsp;</td>
        <td colspan="18" class="blanco">ALTA VISION</td>
        <td colspan="18" class="blanco"><?php echo $titleres['pubpid']; ?></td>
        <td colspan="14" class="blanco" style="border-right: none"><?php echo $titleres['pubpid']; ?></td>
    </tr>
    <tr>
        <td colspan="15" rowspan="2" height="41" class="verde" style="height:31.0pt;">PRIMER APELLIDO</td>
        <td colspan="13" rowspan="2" class="verde">SEGUNDO APELLIDO</td>
        <td colspan="13" rowspan="2" class="verde">PRIMER NOMBRE</td>
        <td colspan="10" rowspan="2" class="verde">SEGUNDO NOMBRE</td>
        <td colspan="3" rowspan="2" class="verde">SEXO</td>
        <td colspan="6" rowspan="2" class="verde">FECHA NACIMIENTO</td>
        <td colspan="3" rowspan="2" class="verde">EDAD</td>
        <td colspan="8" class="verde" style="border-right: none; border-bottom: none">CONDICIÓN EDAD <font
                    class="font7">(MARCAR)</font></td>
    </tr>
    <tr>
        <td colspan="2" height="17" class="verde">H</td>
        <td colspan="2" class="verde">D</td>
        <td colspan="2" class="verde">M</td>
        <td colspan="2" class="verde" style="border-right: none">A</td>
    </tr>
    <tr>
        <td colspan="15" height="27" class="blanco"><?php echo $titleres['lname']; ?></td>
        <td colspan="13" class="blanco"><?php echo $titleres['lname2']; ?></td>
        <td colspan="13" class="blanco"><?php echo $titleres['fname']; ?></td>
        <td colspan="10" class="blanco"><?php echo $titleres['mname']; ?></td>
        <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">X</td>
    </tr>
</TABLE>
<table>
    <tr>
        <td class="morado" colspan="67">B. REGISTRO DE VALORACIÓN PREANESTÉSICA</td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="2">DIAGNÓSTICOS</td>
        <td class="blanco_left" colspan="48"></td>
        <td class="verde" colspan="3" rowspan="2">CIE</td>
        <td class="blanco_left" colspan="5"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="48"></td>
        <td class="blanco_left" colspan="5"></td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="2">PROCEDIMIENTO/S PROPUESTO /S:</td>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="verde" colspan="6">Electiva</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="verde" colspan="8">Emergencia</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="verde" colspan="6">Urgencia</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="verde" colspan="9">RIESGO QUIRÚRGICO:</td>
        <td class="verde" colspan="6">Bajo</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="verde" colspan="8">Moderado</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="verde" colspan="6">Alto</td>
        <td class="blanco_left" colspan="3"></td>
    </tr>

</table>
<table>
    <tr>
        <td class="morado" colspan="67">C. ANAMNESIS</td>
    </tr>
    <tr>
        <td class="verde" colspan="67">ANTECEDENTES PATOLÓGICOS PERSONALES</td>
    </tr>
    <tr>
        <td class="blanco" colspan="2"></td>
        <td class="verde" colspan="11">DIAGNÓSTICOS</td>
        <td class="verde" colspan="12">TIEMPO DE EVOLUCIÓN</td>
        <td class="verde" colspan="42">TRATAMIENTO</td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">1.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">2.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">3.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">4.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">5.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">6.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">7.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">8.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">9.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="blanco" colspan="2">10.</td>
        <td class="blanco" colspan="11"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="42"></td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="3">ANESTÉSICOS</td>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="3">QUIRÚRGICOS</td>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="3">ALÉRGICOS</td>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="3">TRANSFUSIONES</td>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="verde" colspan="11" rowspan="3">HÁBITOS</td>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="56"></td>
    </tr>
    <tr>
        <td class="verde" colspan="67">ANTECEDENTES PATOLÓGICOS FAMILIARES</td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="67"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="67"></td>
    </tr>
    <tr>
        <td class="blanco_left" colspan="67"></td>
    </tr>

</table>
<table style="border: none">
    <TR>
        <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP/HCU-form.018/2021</B>
        </TD>
        <TD colspan="3" class="blanco" style="border: none; text-align: right"><B> PRE ANESTÉSICO (1)</B>
        </TD>
    </TR>
</TABLE>
<pagebreak>
    <table>
        <tr>
            <td class="morado" colspan="67">D. EXAMEN FÍSICO</td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">CONSTANTES VITALES</td>
            <td class="verde" colspan="3" style="height: 15px">TA</td>
            <td class="blanco" colspan="6" style="height: 15px"></td>
            <td class="verde" colspan="4" style="height: 15px">FC</td>
            <td class="blanco" colspan="6" style="height: 15px"></td>
            <td class="verde" colspan="4" style="height: 15px">FR</td>
            <td class="blanco" colspan="6" style="height: 15px"></td>
            <td class="verde" colspan="4" style="height: 15px">T°</td>
            <td class="blanco" colspan="3" style="height: 15px"></td>
            <td class="verde" colspan="5" style="height: 15px">SAT 02</td>
            <td class="blanco" colspan="4" style="height: 15px"></td>
            <td class="verde" colspan="7" style="height: 15px">GLASGOW</td>
            <td class="blanco" colspan="3" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">ANTROPOMETRÍA</td>
            <td class="verde" colspan="8" style="height: 15px">PESO (kg)</td>
            <td class="blanco" colspan="11" style="height: 15px"></td>
            <td class="verde" colspan="8" style="height: 15px">TALLA (cm)</td>
            <td class="blanco" colspan="11" style="height: 15px"></td>
            <td class="verde" colspan="7" style="height: 15px">IMC (kg/m2)</td>
            <td class="blanco" colspan="10" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="12" rowspan="6">VÍA AÉREA</td>
            <td class="verde" colspan="22" style="height: 15px">APERTURA BUCAL (cm)</td>
            <td class="verde" colspan="17" style="height: 15px">DISTANCIA TIROMENTONEANA (cm)</td>
            <td class="verde" colspan="16" style="height: 15px">MALLAMPATI</td>
        </tr>
        <tr>
            <td class="blanco" colspan="3" style="height: 15px">&lt;2</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">2 - 2,5</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">2,6 - 3</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&gt;3</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&lt;6</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">6 - 6,5</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;6,5</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">I</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">II</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">III</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">IV</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="17" style="height: 15px">PROTRUSIÓN MANDIBULAR</td>
            <td class="verde" colspan="11" style="height: 15px">PERÍMETRO CERVICAL (cm)</td>
            <td class="verde" colspan="11" style="height: 15px">MOVILIDAD CERVICAL (°)</td>
            <td class="verde" colspan="8" style="height: 15px">HISTORIA DE INTUBACIÓN DIFÍCIL</td>
            <td class="verde" colspan="8" style="height: 15px">PATOLOGÍA ASOCIADA A INTUBACIÓN DIFÍCIL</td>
        </tr>
        <tr>
            <td class="blanco" colspan="3" style="height: 15px">&lt;0</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">0</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;0</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&lt;40</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;40</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&lt;35</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;35</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">SI</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">NO</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">SI</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">NO</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="9" rowspan="2">OTROS</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">TÓRAX</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">CORAZÓN</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">PULMONES</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">ABDOMEN</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">EXTREMIDADES</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">SISTEMA NERVIOSO CENTRAL</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">EQUIVALENTE METABÓLICO (METS)</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="67">E. RESULTADOS DE EXÁMENES DE LABORATORIO, GABINETE E
                IMAGEN<span style="font-size:9pt;font-family:Arial;font-weight:bold;">                  </span><span
                        style="font-size:7pt;font-family:Arial;font-weight:bold;">(REGISTRAR LO QUE APLIQUE)</span></td>
        </tr>
        <tr>
            <td class="verde" colspan="11" style="height: 15px;">HEMOGRAMA</td>
            <td class="verde" colspan="12" style="height: 15px;">TIPIFICACIÓN</td>
            <td class="verde" colspan="8" style="height: 15px;">PERFIL HEPÁTICO</td>
            <td class="verde" colspan="7" style="height: 15px;">IONOGRAMA</td>
            <td class="verde" colspan="9" style="height: 15px;">GASOMETRÍA</td>
            <td class="verde" colspan="6" style="height: 15px;">HORMONAS</td>
            <td class="verde" colspan="14" style="height: 15px;">ORINA</td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">HCTO</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">GRUPO</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">AST</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">Na</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">pH</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">T4</td>
            <td class="blanco" colspan="4" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">pH</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">HB</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">FACTOR</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">ALT</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">K</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">PO2</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" rowspan="2" style="height: 15px;">PRUEBA
                EMBARAZO
            </td>
            <td class="verde_normal" colspan="6" style="height: 15px;">BACTERIAS</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">TP</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">GLUCOSA</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">LDH</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">Ca</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">HCO3</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">LEUCOCITOS</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">TTP</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">UREA</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">BT</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">Mg</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">EB</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">SI</td>
            <td class="blanco" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">NO</td>
            <td class="blanco" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">PIOCITOS</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">INR</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">CREATININA</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">BD</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="blanco" colspan="7" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">SAT. 02</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="blanco" colspan="2" style="height: 15px;"></td>
            <td class="blanco" colspan="4" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">GLUCOSA</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">LEUCOCITOS</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">OTROS:</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">BI</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="blanco" colspan="7" style="height: 15px"></td>
            <td class="verde_normal" colspan="4" style="height: 15px">LACTATO</td>
            <td class="blanco" colspan="5" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px"></td>
            <td class="verde_normal" colspan="6" style="height: 15px">GLUCOSA</td>
            <td class="blanco" colspan="8" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" style="height: 15px">EKG</td>
            <td class="blanco" colspan="56" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" style="height: 15px">RX TÓRAX</td>
            <td class="blanco" colspan="56" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" style="height: 15px">ESPIROMETRÍA</td>
            <td class="blanco" colspan="56" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" rowspan="2" style="height: 15px">OTROS</td>
            <td class="blanco" colspan="56" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="67">F. ESCALAS E ÍNDICES <span
                        style="font-size:7pt;font-family:Arial;font-weight:bold;">(REGISTRAR LO QUE APLIQUE)</span></td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">ESTADO FÍSICO ASA</td>
            <td class="verde_normal" colspan="2" style="height: 15px">I</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">II</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">III</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">IV</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">V</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">VI</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde" colspan="15" style="height: 15px">RIESGO CARDÍACO</td>
            <td class="blanco" colspan="16" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">RIESGO PULMONAR</td>
            <td class="blanco" colspan="24" style="height: 15px"></td>
            <td class="verde" colspan="15" style="height: 15px">RIESGO TROMBOEMBÓLICO</td>
            <td class="blanco" colspan="16" style="height: 15px"></td>
        </tr>
        <tr style="height: 17px">
            <td class="verde" colspan="12" style="height: 15px">OTROS</td>
            <td class="blanco" colspan="55" style="height: 15px"></td>
        </tr>

    </table>
    <table>
        <tr>
            <td class="morado" colspan="67" style="height: 15px">F. TIEMPO DE ULTIMA INGESTA</td>
        </tr>
        <tr>
            <td class="verde" colspan="16" style="height: 15px">LÍQUIDOS CLAROS</td>
            <td class="blanco" colspan="18" style="height: 15px"></td>
            <td class="verde" colspan="16" style="height: 15px">LECHE DE FÓRMULA</td>
            <td class="blanco" colspan="17" style="height: 15px"></td>
        </tr>
        <tr style="height: 17px">
            <td class="verde" colspan="16" style="height: 15px">LECHE MATERNA</td>
            <td class="blanco" colspan="18" style="height: 15px"></td>
            <td class="verde" colspan="16" style="height: 15px">SÓLIDOS</td>
            <td class="blanco" colspan="17" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="67" style="height: 15px">G. INDICACIONES</td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">1.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">2.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">3.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">4.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">5.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">6.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">7.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">8.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" style="height: 15px;" colspan="67">H. PLAN ANESTÉSICO</td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" style="height: 15px;" colspan="67">I. OBSERVACIONES</td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="71" class="morado" style="height: 15px;">F. DATOS DEL PROFESIONAL RESPONSABLE</td>
        </tr>
        <tr class="xl78">
            <td colspan="8" class="verde" style="height: 15px;">FECHA

                <font class="font5">(aaaa-mm-dd)</font>
            </td>
            <td colspan="7" class="verde" style="height: 15px;">HORA

                <font class="font5">(hh:mm)</font>
            </td>
            <td colspan="21" class="verde" style="height: 15px;">PRIMER NOMBRE</td>
            <td colspan="19" class="verde" style="height: 15px;">PRIMER APELLIDO</td>
            <td colspan="16" class="verde" style="height: 15px;">SEGUNDO APELLIDO</td>
        </tr>
        <tr>
            <td colspan="8" class="blanco" style="height: 15px;"><?php echo date("d/m/Y", $timestamp); ?></td>
            <td colspan="7" class="blanco" style="height: 15px;"></td>
            <td colspan="21" class="blanco" style="height: 15px;">Mario</td>
            <td colspan="19" class="blanco" style="height: 15px;">Pólit</td>
            <td colspan="16" class="blanco" style="height: 15px;">Macias</td>
        </tr>
        <tr>
            <td colspan="15" class="verde" style="height: 15px;">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
            <td colspan="26" class="verde" style="height: 15px;">FIRMA</td>
            <td colspan="30" class="verde" style="height: 15px;">SELLO</td>
        </tr>
        <tr>
            <td colspan="15" class="blanco" style="height: 40px"></td>
            <td colspan="26" class="blanco" style="height: 15px;">&nbsp;</td>
            <td colspan="30" class="blanco" style="height: 15px;">&nbsp;</td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP/HCU-form.018/2021</B>
            </TD>
            <TD colspan="3" class="blanco" style="border: none; text-align: right"><B> PRE ANESTÉSICO (2)</B>
            </TD>
        </TR>
    </TABLE>
</body>
</html>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('consentimiento_od' . '.pdf', 'I'); // D = Download, I = Inline
}
?>
