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
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, pricelevel, providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

//Indispensable para el eye_mag
$query = "  select  *,form_encounter.date as encounter_date, form_encounter.reason as reason
               from forms,form_encounter,form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external, form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
                    where
                    forms.deleted != '1'  and
                    forms.formdir='eye_mag' and
                    forms.encounter=form_encounter.encounter  and
                    forms.form_id=form_eye_base.id and
                    forms.form_id=form_eye_hpi.id and
                    forms.form_id=form_eye_ros.id and
                    forms.form_id=form_eye_vitals.id and
                    forms.form_id=form_eye_acuity.id and
                    forms.form_id=form_eye_refraction.id and
                    forms.form_id=form_eye_biometrics.id and
                    forms.form_id=form_eye_external.id and
                    forms.form_id=form_eye_antseg.id and
                    forms.form_id=form_eye_postseg.id and
                    forms.form_id=form_eye_neuro.id and
                    forms.form_id=form_eye_locking.id and
                    forms.encounter=? and
                    forms.pid=? ";
$encounter_data = sqlQuery($query, array($encounter, $pid));
@extract($encounter_data);

//Fecha del form_eye_mag
$queryform = "select * from forms
                where
                pid=? and
                encounter=? and
                formdir = 'eye_mag' and
                deleted = 0";

$fechaINGRESO = sqlQuery($queryform, array($_GET['patientid'], $_GET['visitid']));
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

//Funciones
function fetchEyeMagOrders($form_id, $pid)
{
    $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($plan_row = sqlFetchArray($PLAN_results)) {
            $IMAGENPropuesta = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'Eye_todo_done_' AND `title` LIKE ? ";
            $code_item = sqlQuery($IMAGENPropuesta, array($plan_row['ORDER_DETAILS']));
            if ($code_item['codes']) {
                echo $code_item['notes'] . " (" . substr($code_item['codes'], 5) . ")";
                echo "</td></tr><tr><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">";
            }
        }
    }
}

//Funcion Resumen de historia
function ExamOftal($RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                   $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS)
{
    $ExamOFT = "";

    if ($SCODVA) {
        $ExamOFT .= "Ojo Derecho: $SCODVA, ";
    }
    if ($SCOSVA) {
        $ExamOFT .= "Ojo Izquierdo: $SCOSVA, ";
    }
    if ($ODIOPAP) {
        $ExamOFT .= "Ojo Derecho: $ODIOPAP, ";
    }
    if ($OSIOPAP) {
        $ExamOFT .= "Ojo Izquierdo: $OSIOPAP, ";
    }

    $ExamOFT .= "Luego de realizar examen físico oftalmológico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrías bajo dilatación con gotas de tropicamida y fenilefrina, a la Biomicroscopia se observa: ";

    if ($RBROW || $LBROW || $RUL || $LUL || $RLL || $LLL || $RMCT || $LMCT || $RADNEXA || $LADNEXA || $EXT_COMMENTS) {
        $ExamOFT .= "Examen Externo: ";
        if ($RBROW || $RUL || $RLL || $RMCT || $RADNEXA) {
            $ExamOFT .= "Ojo Derecho: $RBROW $RUL $RLL $RMCT $RADNEXA ";
        }
        if ($LBROW || $LUL || $LLL || $LMCT || $LADNEXA) {
            $ExamOFT .= "Ojo Izquierdo: $LBROW $LUL $LLL $LMCT $LADNEXA ";
        }
        $ExamOFT .= $EXT_COMMENTS;
    }

    if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS || $OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
        $ExamOFT .= "En la evaluación de los ojos:";
    }

    if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS) {
        $ExamOFT .= " Ojo Derecho:";
        if ($ODCONJ) {
            $ExamOFT .= " Conjuntiva $ODCONJ,";
        }
        if ($ODCORNEA) {
            $ExamOFT .= " Córnea $ODCORNEA,";
        }
        if ($ODAC) {
            $ExamOFT .= " Cámara Anterior $ODAC,";
        }
        if ($ODLENS) {
            $ExamOFT .= " Cristalino $ODLENS,";
        }
        if ($ODIRIS) {
            $ExamOFT .= " Iris $ODIRIS,";
        }
    }

    if ($OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
        $ExamOFT .= " Ojo Izquierdo:";
        if ($OSCONJ) {
            $ExamOFT .= " Conjuntiva $OSCONJ,";
        }
        if ($OSCORNEA) {
            $ExamOFT .= " Córnea $OSCORNEA,";
        }
        if ($OSAC) {
            $ExamOFT .= " Cámara Anterior $OSAC,";
        }
        if ($OSLENS) {
            $ExamOFT .= " Cristalino $OSLENS,";
        }
        if ($OSIRIS) {
            $ExamOFT .= " Iris $OSIRIS,";
        }
    }

    if ($ODDISC || $OSDISC || $ODCUP || $OSCUP || $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
        $ExamOFT .= "Al fondo de ojo: ";
    }

    if ($ODDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
        $ExamOFT .= "Ojo Derecho:";
        if ($ODDISC) {
            $ExamOFT .= " Disco $ODDISC,";
        }
        if ($ODCUP) {
            $ExamOFT .= " Copa $ODCUP,";
        }
        if ($ODMACULA) {
            $ExamOFT .= " Mácula $ODMACULA,";
        }
        if ($ODVESSELS) {
            $ExamOFT .= " Vasos $ODVESSELS,";
        }
        if ($ODPERIPH) {
            $ExamOFT .= " Periferia $ODPERIPH,";
        }
        if ($ODVITREOUS) {
            $ExamOFT .= " Vítreo $ODVITREOUS,";
        }
    }

    if ($OSDISC || $OSCUP || $OSMACULA || $OSVESSELS || $OSPERIPH || $OSVITREOUS) {
        $ExamOFT .= "Ojo Izquierdo:";
        if ($OSDISC) {
            $ExamOFT .= " Disco $OSDISC,";
        }
        if ($OSCUP) {
            $ExamOFT .= " Copa $OSCUP,";
        }
        if ($OSMACULA) {
            $ExamOFT .= " Mácula $OSMACULA,";
        }
        if ($OSVESSELS) {
            $ExamOFT .= " Vasos $OSVESSELS,";
        }
        if ($OSPERIPH) {
            $ExamOFT .= " Periferia $OSPERIPH,";
        }
        if ($OSVITREOUS) {
            $ExamOFT .= " Vítreo $OSVITREOUS,";
        }
    }

    // Eliminar la coma final si existe
    $ExamOFT = rtrim($ExamOFT, ", ");

    return $ExamOFT;
}

function getDXoftalmo($form_id, $pid, $dxnum)
{
    $query = "select * from form_eye_mag_impplan where form_id=? and pid=? AND IMPPLAN_order = ? order by IMPPLAN_order ASC LIMIT 1";
    $result = sqlStatement($query, array($form_id, $pid, $dxnum));
    $i = '0';
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    // echo '<ol>';
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
        $IMPPLAN_items[$i] = $newdata;
        $i++;
    }

    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
    foreach ($IMPPLAN_items as $item) {
        $pattern = '/Code/';
        if (preg_match($pattern, $item['code'])) {
            $item['code'] = '';
        }

        if ($item['codetext'] > '') {
            return $item['codedesc'] . ". ";
        }

    }
}

function getDXoftalmoCIE10($form_id, $pid, $dxnum)
{
    $query = "select * from form_eye_mag_impplan where form_id=? and pid=? AND IMPPLAN_order = ? order by IMPPLAN_order ASC LIMIT 1";
    $result = sqlStatement($query, array($form_id, $pid, $dxnum));
    $i = '0';
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    // echo '<ol>';
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
        $IMPPLAN_items[$i] = $newdata;
        $i++;
    }

    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
    foreach ($IMPPLAN_items as $item) {
        $pattern = '/Code/';
        if (preg_match($pattern, $item['code'])) {
            $item['code'] = '';
        }

        if ($item['codetext'] > '') {
            return $item['code'] . ". ";
        }

    }
}

function extractItemsFromQuery($form_id, $pid)
{
    $query = "SELECT l.title FROM form_eye_mag_ordenqxod AS od
              LEFT JOIN list_options AS l on od.ORDER_DETAILS = l.title
              WHERE od.form_id=? and od.pid=? and l.list_id='cirugia_propuesta_defaults' ORDER BY od.id ASC";

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

function getPlanTerapeuticoOD($form_id, $pid)
{
    $query = "SELECT * FROM form_eye_mag_ordenqxod
              WHERE form_id = ? AND pid = ?
              ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($row = sqlFetchArray($PLAN_results)) {
            $Plan_propuesto = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` LIKE ?";
            $item = sqlQuery($Plan_propuesto, array($row['ORDER_DETAILS']));
            if (!empty($item)) {
                echo $item['notes'] . " en ojo derecho";
                echo "</td></tr><tr><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">";
            }
        }
    }
}

function getPlanTerapeuticoOI($form_id, $pid)
{
    $query = "SELECT * FROM form_eye_mag_ordenqxoi
              WHERE form_id = ? AND pid = ?
              ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($row = sqlFetchArray($PLAN_results)) {
            $Plan_propuesto = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` LIKE ?";
            $item = sqlQuery($Plan_propuesto, array($row['ORDER_DETAILS']));
            if (!empty($item)) {
                echo $item['notes'] . " en ojo izquierdo";
                echo "</td></tr><tr><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">";
            }
        }
    }
}

//provider name explode
$fullName = getProviderNameConcat($providerID);

// Extraer los componentes del nombre
$nameComponents = explode(" ", $fullName);

// Obtener los componentes individuales
$mname = isset($nameComponents[0]) ? $nameComponents[0] : '';
$fname = isset($nameComponents[1]) ? $nameComponents[1] : '';
$lname = isset($nameComponents[2]) ? $nameComponents[2] : '';
$suffix = isset($nameComponents[3]) ? $nameComponents[3] : '';

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
    'default_font' => 'Arial',
    'margin_left' => '10',
    'margin_right' => '10',
    'margin_top' => '10',
    'margin_bottom' => '10',
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
<HTML>
<HEAD>
    <style>
        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 10px;
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
</HEAD>
<BODY>
<TABLE>
    <colgroup>
        <col class="xl76" span="71" style="width:10pt">
    </colgroup>
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
        <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
    </tr>
</TABLE>
<table>
    <colgroup>
        <col class="xl76" span="71">
    </colgroup>
    <tr>
        <td colspan="71" class="morado">B. CUADRO CLÍNICO DE INTERCONSULTA</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco_left"><?php
            echo wordwrap($reason, 165, "</td>
    </tr>
    <tr>
        <td colspan=\"71\" class=\"blanco_left\">"); ?></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco_left"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">C. RESUMEN DEL CRITERIO CLÍNICO</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"><?php
            echo wordwrap(ExamOftal($RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS), 165, "</TD></TR><TR><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">"); ?></td>
        </td>
    </tr>
</table>
<table>
    <TR>
        <TD class="morado" width="2%">D.</TD>
        <TD class="morado" width="17.5%">DIAGN&Oacute;STICOS</TD>
        <TD class="morado" width="17.5%" style="font-weight: normal; font-size: 6pt">PRE= PRESUNTIVO DEF= DEFINITIVO
        </TD>
        <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
        <TD class="morado" width="2%"><BR></TD>
        <TD class="morado" width="17.5%"><BR></TD>
        <TD class="morado" width="17.5%"><BR></TD>
        <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
    </TR>
    <TR>
        <td class="verde">1.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                echo "x";
            } ?></td>
        <td class="verde">4.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                echo "x";
            } ?></td>
    </TR>
    <TR>
        <td class="verde">2.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                echo "x";
            } ?></td>
        <td class="verde">5.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                echo "x";
            } ?></td>
    </TR>
    <TR>
        <td class="verde">3.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                echo "x";
            } ?></td>
        <td class="verde">6.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "5")) {
                echo "x";
            } ?></td>
    </TR>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">E. PLAN DE DIAGNÓSTICO PROPUESTO</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left">
            <?php
            echo fetchEyeMagOrders($form_id, $pid);
            ?>
    </tr>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">F. PLAN TERAPEÚTICO PROPUESTO</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco_left">
            <?php
            echo getPlanTerapeuticoOD($form_id, $pid);
            echo getPlanTerapeuticoOI($form_id, $pid);
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">G. DATOS DEL PROFESIONAL RESPONSABLE</td>
    </tr>
    <tr class="xl78">
        <td colspan="8" class="verde">FECHA<br>
            <font class="font5">(aaaa-mm-dd)</font>
        </td>
        <td colspan="7" class="verde">HORA<br>
            <font class="font5">(hh:mm)</font>
        </td>
        <td colspan="21" class="verde">PRIMER NOMBRE</td>
        <td colspan="19" class="verde">PRIMER APELLIDO</td>
        <td colspan="16" class="verde">SEGUNDO APELLIDO</td>
    </tr>
    <tr>
        <td colspan="8" class="blanco"><?php echo date("d/m/Y", strtotime($fechaINGRESO['date'])); ?></td>
        <td colspan="7" class="blanco"></td>
        <td colspan="21" class="blanco"><?php echo $mname; ?></td>
        <td colspan="19" class="blanco"><?php echo $fname; ?></td>
        <td colspan="16" class="blanco"><?php echo $lname; ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
        <td colspan="26" class="verde">FIRMA</td>
        <td colspan="30" class="verde">SELLO</td>
    </tr>
    <tr>
        <td colspan="15" class="blanco" style="height: 40px"><?php echo getProviderRegistro($providerID); ?></td>
        <td colspan="26" class="blanco">&nbsp;</td>
        <td colspan="30" class="blanco">&nbsp;</td>
    </tr>
</table>
<table style="border: none">
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP / HCU-form.012A /
                    2008</FONT></B></TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">IMAGENOLOGIA SOLICITUD</FONT></B></TD>
    </TR>
    ]
</TABLE>
</BODY>
</HTML>

<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('consentimiento_oi' . '.pdf', 'I'); // D = Download, I = Inline
}
?>
