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
$query = "  select  *,form_encounter.date as encounter_date
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
    'default_font_size' => '',
    'default_font' => '',
    'margin_left' => '0',
    'margin_right' => '0',
    'margin_top' => '0',
    'margin_bottom' => '0',
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
$pdf->SetDefaultBodyCSS('background', "url('4cc4ae00-9c66-11eb-8b25-0cc47a792c0a_id_4cc4ae00-9c66-11eb-8b25-0cc47a792c0a_files/background1.jpg')");
$pdf->SetDefaultBodyCSS('background-image-resize', 6);
if ($_SESSION['language_direction'] == 'rtl') {
    $pdf->SetDirectionality('rtl');
}
ob_start();
?>
<html>
<head>
    <style type="text/css">
        div.page {
            position: static;
            height: 1122px;
            width: 794px;

        }

        span.cls_004 {
            font-family: Arial, serif;
            font-size: 24px;
            color: rgb(254, 255, 255);
            font-weight: bold;
            font-style: normal;
            text-decoration: none;
        }

        div.encabezado {
            position: absolute;
            top: 28px;

            width: 788px;
            font-family: Arial, serif;
            font-size: 24px;
            color: rgb(254, 255, 255);
            font-weight: bold;
            font-style: normal;
            text-decoration: none;
            text-align: center;
        }

        span.cls_005 {
            font-family: Arial, serif;
            font-size: 20.3px;
            color: rgb(254, 255, 255);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_005 {
            font-family: Arial, serif;
            font-size: 20.3px;
            color: rgb(254, 255, 255);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_007 {
            font-family: Arial, serif;
            font-size: 9.8px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_007 {
            font-family: Arial, serif;
            font-size: 9.8px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_009 {
            font-family: Arial, serif;
            font-size: 7.8px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: italic;
            text-decoration: none
        }

        div.cls_009 {
            font-family: Arial, serif;
            font-size: 7.8px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: italic;
            text-decoration: none
        }

        span.cls_002 {
            font-family: Arial, serif;
            font-size: 12.1px;
            color: rgb(45, 57, 97);
            font-weight: bold;
            font-style: normal;
            text-decoration: none
        }

        div.cls_002 {
            position: absolute;
            left: 47px;
            width: 692px;
            border: 3px solid green;
            font-family: Arial, serif;
            font-size: 12.1px;
            color: rgb(45, 57, 97);
            font-weight: bold;
            font-style: normal;
            text-decoration: none;
        }

        span.cls_003 {
            font-family: Arial, serif;
            font-size: 12.1px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_003 {
            font-family: Arial, serif;
            font-size: 12.1px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_006 {
            font-family: Arial, serif;
            font-size: 10.1px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_006 {
            font-family: Arial, serif;
            font-size: 10.1px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_008 {
            font-family: Arial, serif;
            font-size: 5.9px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: italic;
            text-decoration: none
        }

        div.cls_008 {
            font-family: Arial, serif;
            font-size: 5.9px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: italic;
            text-decoration: none
        }

        span.cls_010 {
            font-family: Arial, serif;
            font-size: 8.1px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_010 {
            font-family: Arial, serif;
            font-size: 8.1px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_011 {
            font-family: Arial, serif;
            font-size: 8.1px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_011 {
            font-family: Arial, serif;
            font-size: 8.1px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_012 {
            font-family: Arial, serif;
            font-size: 9.1px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_012 {
            font-family: Arial, serif;
            font-size: 9.1px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_013 {
            font-family: Arial, serif;
            font-size: 12px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_013 {
            position: absolute;

            font-family: Arial, serif;
            font-size: 14px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.firmatitular {
            position: absolute;
            top: 1060px;
            right: 125px;
            font-family: Arial, serif;
            font-size: 14px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_014 {
            font-family: Arial, serif;
            font-size: 4.9px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_014 {
            font-family: Arial, serif;
            font-size: 4.9px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_016 {
            font-family: Arial, serif;
            font-size: 4.9px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_016 {
            font-family: Arial, serif;
            font-size: 4.9px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_015 {
            font-family: Arial, serif;
            font-size: 6.0px;
            color: rgb(67, 67, 66);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_015 {
            position: absolute;
            border: 3px solid green;
            height: 10px;
            width: 475px;
            left: 43px;
            font-family: Arial, serif;
            font-size: 6.0px;
            color: rgb(67, 67, 66);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_017 {
            font-family: Arial, serif;
            font-size: 10px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_017 {
            position: absolute;

            height: 12px;
            width: 67px;
            font-family: Arial, serif;
            font-size: 10px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            text-align: center;
        }

        span.cls_018 {
            font-family: Arial, serif;
            font-size: 10px;
            color: rgb(67, 67, 66);
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            justify-content: center;
        }

        div.cls_018 {
            position: absolute;
            top: 970px;
            left: 47px;
            width: 692px;
            height: 36px;
            font-family: Arial, serif;
            font-size: 10px;
            color: rgb(67, 67, 66);
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
        }

        span.cls_019 {
            font-family: Arial, serif;
            font-size: 9px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_019 {
            position: absolute;
            top: 1043px;

            font-family: Arial, serif;
            font-size: 7.5px;
            color: rgb(43, 42, 41);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        span.cls_020 {
            font-family: Arial, serif;
            font-size: 8.0px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            text-align: center;
        }

        div.footer {
            position: absolute;
            top: 1080px;

            width: 788px;
            font-family: Arial, serif;
            font-size: 6.0px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            text-align: center;
        }

        span.cls_021 {
            font-family: Arial, serif;
            font-size: 11.6px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }

        div.cls_021 {
            position: absolute;
            top: 1095px;
            right: 40px;
            font-family: Arial, serif;
            font-size: 11.6px;
            color: rgb(45, 57, 97);
            font-weight: normal;
            font-style: normal;
            text-decoration: none
        }
    </style>
    <script type="text/javascript"
            src="4cc4ae00-9c66-11eb-8b25-0cc47a792c0a_id_4cc4ae00-9c66-11eb-8b25-0cc47a792c0a_files/wz_jsgraphics.js"></script>
</head>
<body>
<div class="encabezado">
    <span class="cls_004">Formulario de Reembolsos</span><br>
    <span class="cls_005">de Gastos Médicos Individual</span>
</div>
<div style="position:absolute;left:32.41px;top:103.00px" class="cls_007"><span class="cls_007">Alcance (Indique Diagnóstico o</span>
</div>
<div style="position:absolute;left:32.41px;top:115.00px" class="cls_007"><span class="cls_007"># de Liquidación de referencia)</span>
</div>
<div style="position:absolute;left:32.41px;top:132.70px" class="cls_007"><span class="cls_007">Coordinación de Beneficios</span>
</div>
<div style="position:absolute;left:319.28px;top:132.70px" class="cls_007"><span
        class="cls_007">Plan Contratado</span>
</div>
<div style="position:absolute;left:400.12px;top:147.21px" class="cls_009"><span class="cls_009">Ejemplo: Sigma, Azure, Gastos M. Mayores, etc</span>
</div>
<div style="position:absolute;left:236.87px;top:160.28px" class="cls_002"><span
        class="cls_002">Datos del </span><span
        class="cls_003">Contratante</span></div>
<div style="position:absolute;left:32.06px;top:180.54px" class="cls_006"><span
        class="cls_006">Valor presentado:</span>
</div>
<div style="position:absolute;left:473.05px;top:195.38px" class="cls_008"><span class="cls_008">(Sumatoria de todas sus facturas)</span>
</div>
<div style="position:absolute;left:32.50px;top:204.62px" class="cls_006"><span
        class="cls_006">Nombre del titular:</span><span><?php echo text($titleres['lname'] . " " . $titleres['lname2'] . " " . $titleres['fname'] . " " . $titleres['mname'] . " ") ?></span>
</div>
<div style="position:absolute;left:319.28px;top:204.88px" class="cls_007"><span class="cls_007">Cédula:</span></div>
<div style="position:absolute;left:32.50px;top:223.15px" class="cls_006"><span
        class="cls_006">Nombre del paciente:</span></div>
<div style="position:absolute;left:319.28px;top:223.15px" class="cls_006"><span class="cls_006">Parentesco:</span>
</div>
<div style="position:absolute;left:127.86px;top:250.87px" class="cls_002"><span
        class="cls_002">Información Médica </span><span
        class="cls_003">(Debe ser llenada por el médico tratante)</span>
</div>
<div style="position:absolute;left:113.07px;top:268.45px" class="cls_010"><span class="cls_010">Si su reembolso corresponde a coordinación de beneficios o alcance no requiere llenar esta sección</span>
</div>
<div style="position:absolute;left:32.06px;top:295.44px" class="cls_006"><span
        class="cls_006">Motivo de consulta:</span></div>
<div style="position:absolute;left:32.06px;top:334.05px" class="cls_011"><span class="cls_011">Fecha de inicio de Historia</span>
</div>
<div style="position:absolute;left:319.28px;top:334.05px" class="cls_011"><span
        class="cls_011">Inicio de los primeros</span></div>
<div style="position:absolute;left:34.10px;top:343.65px" class="cls_011"><span
        class="cls_011">Clínica (dd/mm/aa)</span>
</div>
<div style="position:absolute;left:319.28px;top:343.65px" class="cls_011"><span
        class="cls_011">síntomas: (dd/mm/aa)</span></div>
<div style="position:absolute;left:32.06px;top:367.86px" class="cls_012"><span
        class="cls_012">Diagnósticos Definitivos:</span></div>
<div style="position:absolute;left:319.28px;top:366.64px" class="cls_006"><span class="cls_006">CIE 10:</span></div>
<div style="position:absolute;left:32.06px;top:418.85px" class="cls_012"><span
        class="cls_012">Nombre del médico</span>
</div>
<div style="position:absolute;left:319.28px;top:424.25px" class="cls_012"><span
        class="cls_012">Fecha de Atención</span>
</div>
<div style="position:absolute;left:32.06px;top:429.65px" class="cls_012"><span class="cls_012">Tratante:</span>
</div>
<div style="position:absolute;left:222.12px;top:484.93px" class="cls_013"><span class="cls_013">Firma Y Sello Del Médico Tratante</span>
</div>
<div style="position:absolute;left:185.23px;top:505.07px" class="cls_002"><span
        class="cls_002">En caso de Accidente</span><span class="cls_003"> llenar esta sección</span></div>
<div style="position:absolute;left:37.06px;top:530.08px" class="cls_011"><span class="cls_011">En caso de accidente detalle como ocurrió, (Lugar, Fecha y Hora)</span>
</div>
<div style="position:absolute;left:265.41px;top:560.08px" class="cls_002"><span class="cls_002">Importante</span>
</div>
<div style="position:absolute;left:153.30px;top:577.62px" class="cls_011"><span class="cls_011">A este formulario usted debe adjuntar los originales de los siguientes documentos:</span>
</div>
<div style="position:absolute;left:186.59px;top:591.20px" class="cls_014"><span class="cls_014">Atención</span>
</div>
<div style="position:absolute;left:408.43px;top:591.20px" class="cls_016"><span class="cls_016">Hospitalaria</span>
</div>
<div style="position:absolute;left:463.89px;top:591.20px" class="cls_016"><span class="cls_016">Ambulatoria</span>
</div>
<div style="position:absolute;left:521.91px;top:591.20px" class="cls_016"><span class="cls_016">Accidente</span>
</div>
<div style="position:absolute;top:798px" class="cls_015"><span class="cls_015">Facturas originales de médicos, farmacia, laboratorio, con el desglose respectivo</span>
</div>
<div style="position:absolute;left:525px;top:798px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:600px;top:798px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:675px;top:798px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;top:818px" class="cls_015"><span class="cls_015">Planilla de clínica, factura con desglose de todos los profesionales</span>
</div>
<div style="position:absolute;left:525px;top:818px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:675px;top:818px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;top:838px" class="cls_015"><span
        class="cls_015">Resultados de exámenes</span></div>
<div style="position:absolute;left:525px;top:838px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:600px;top:838px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:675px;top:838px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;top:858px" class="cls_015"><span
        class="cls_015">Pedidos y órdenes médicas</span></div>
<div style="position:absolute;left:525px;top:858px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:600px;top:858px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:675px;top:858px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;top:878px" class="cls_015"><span
        class="cls_015">Historia clínica completa</span></div>
<div style="position:absolute;left:525px;top:878px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:675px;top:878px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;top:898px" class="cls_015"><span
        class="cls_015">Protocolo Operatorio</span></div>
<div style="position:absolute;left:525px;top:898px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;top:918px" class="cls_015"><span
        class="cls_015">Hoja de emergencia 008</span></div>
<div style="position:absolute;left:675px;top:918px" class="cls_017"><span class="cls_017">x</span></div>
<div style="position:absolute;left:258.58px;top:707.64px" class="cls_002"><span class="cls_002">Autorización</span>
</div>
<div class="cls_018"><span class="cls_018">Autorizo a todos los médicos y/o personas que me atendieron y/o a todas las clínicas o instituciones prestadoras de servicios de salud para que suministren a la Compañía cualquier información médica, incluyendo copias exactas de Historia clínica y/o ficha médica, exámenes de
    laboratorio y rayos X y cualquier otro exámen de diagnóstico correspondiente a esta atención médica.</span>
</div>
<div style="left:50px;" class="cls_019"><span class="cls_019">Lugar</span></div>
<div style="left:180px" class="cls_019"><span class="cls_019">Día</span></div>
<div style="left:270px" class="cls_019"><span class="cls_019">Mes</span></div>
<div style="left:370px" class="cls_019"><span class="cls_019">Año</span></div>
<div class="firmatitular"><span
        class="cls_013">Firma del Titular</span>
</div>
<div class="footer">
    <span class="cls_020">Quito: Av. de los Shyris y Calle Suecia, Edif. Renazzo Plaza, Piso 12<br>
    Cuenca: Autopista Cuenca-Azoguez, Edif. Cardeca Business Center, Planta Baja.<br>
    Guayaquil: Parque Empresarial Colón, Av. Jaime Roldós Aguilera, Edif. Corporativo 2, Piso 1.</span><br>
    <A HREF="http://www.bmi.com.ec/">www.bmi.com.ec</A>
</div>
<div class="cls_021"><span class="cls_021">V. 2020</span></div>


</body>
</html>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('informe_medico_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
