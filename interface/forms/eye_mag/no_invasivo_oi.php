<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
include_once($GLOBALS["srcdir"]."/api.inc");
require_once(dirname(__FILE__) ."/../../../library/lists.inc");

use OpenEMR\Services\FacilityService;

$form_name = "eye_mag";
$form_folder = "eye_mag";

$facilityService = new FacilityService();

require_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");

if ($_REQUEST['ptid']) {
    $pid = $_REQUEST['ptid'];
}

if ($_REQUEST['encid']) {
    $encounter=$_REQUEST['encid'];
}

if ($_REQUEST['formid']) {
    $form_id = $_REQUEST['formid'];
}

if ($_REQUEST['formname']) {
    $form_name=$_REQUEST['formname'];
}

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, pricelevel, providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

//Fecha del form_eye_mag
$query="select form_encounter.date as encounter_date,form_eye_mag.id as form_id,form_encounter.*, form_eye_mag.*
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

$fechaINGRESO = sqlQuery($queryform, array($_GET['patientid'],$_GET['visitid']));
$encounter_data =sqlQuery($query, array($encounter,$pid));
@extract($encounter_data);
$providerID  =  getProviderIdOfEncounter($encounter);
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
    'margin_left' => '10',
    'margin_right' => '10',
    'margin_top' => $GLOBALS['pdf_top_margin'],
    'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
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
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
    <TITLE></TITLE>
    <META NAME="GENERATOR" CONTENT="OpenOffice 4.1.5  (Unix)">
    <META NAME="AUTHOR" CONTENT="User">
    <META NAME="CREATED" CONTENT="20031213;17023100">
    <META NAME="CHANGEDBY" CONTENT="Vision3">
    <META NAME="CHANGED" CONTENT="20180206;11254500">

    <STYLE>
        <!--
        BODY,DIV,TABLE,THEAD,TBODY,TFOOT,TR,TH,TD,P { font-family:"Arial"; font-size:x-small }
        -->
    </STYLE>

</HEAD>
<BODY TEXT="#000000">
<TABLE FRAME=VOID CELLSPACING=0 COLS=64 RULES=NONE BORDER=0>
    <COLGROUP><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15></COLGROUP>
    <TBODY>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 5px solid #808080; border-right: 1px solid #c0c0c0" COLSPAN=16 WIDTH=239 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1></FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=19 WIDTH=283 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>UNIDAD OPERATIVA</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=5 WIDTH=75 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. UO</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=12 WIDTH=179 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. LOCALIZACIÓN</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=12 ROWSPAN=2 WIDTH=179 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>NUMERO DE                       HISTORIA CLÍNICA</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #c0c0c0" COLSPAN=16 ROWSPAN=2 HEIGHT=49 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1 COLOR="#000000"><?php
                echo text($titleres['pricelevel']);
                ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=19 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">ALTA VISION</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1> </FONT></B></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PARROQUIA</FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CANTÓN</FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PROVINCIA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TARQUI</FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GUAYAQUIL</FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GUAYAS</FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE SDNUM="12298;0;0"><B><FONT SIZE=4><?php echo text($titleres['pubpid']) ?></FONT></B></TD>
    </TR>
    <TR>
        <TD HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE colspan=64><FONT SIZE=1 COLOR="#000000"></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 5px solid #808080" COLSPAN=11 HEIGHT=20 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO PATERNO</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=11 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO MATERNO</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=16 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>NOMBRES</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SERVICIO </FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SALA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CAMA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=6 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>FECHA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>HORA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #c0c0c0" COLSPAN=11 HEIGHT=25 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><?php echo text($titleres['lname'])?></FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=11 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><?php echo text($titleres['lname2']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=16 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><?php echo text($titleres['fname']) . " " . text($titleres['mname']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B> </B></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=6 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=4 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD HEIGHT=5 COLSPAN=64 ALIGN=RIGHT VALIGN=MIDDLE style="font-size:8">MARCAR &quot;X&quot; EN LA CELDA QUE CORRESPONDA</TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="1" SDNUM="12298;"><B><FONT SIZE=3>1</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>AUTORIZACIÓN PARA CIRUGÍA, TRATAMIENTO CLÍNICO O PROCEDIMIENTO DIAGNÓSTICO</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>AUTORIZO AL PROFESIONAL TRATANTE DE ESTE ESTABLECIMIENTO DE SALUD PARA REALIZAR LAS OPERACIONES QUIRÚRGICAS, PROCEDIMIENTOS DIAGNÓSTICOS Y TRATAMIENTOS CLÍNICOS PROPUESTOS Y NECESARIOS PARA EL TRATAMIENTO DE MI ENFERMEDAD</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FFFF99">BLANCO</FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL PACIENTE</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER><?php echo text($titleres['fname']) . " " . text($titleres['mname']) . " " . text($titleres['lname']) . " " . text($titleres['lname2'])?></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><?php echo text($titleres['phone_cell']); ?></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER SDNUM="12298;0;0"><?php echo text($titleres['pubpid']) ?></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL REPRESENTANTE LEGAL O TESTIGO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD HEIGHT=8 COLSPAN=64 ALIGN=RIGHT VALIGN=MIDDLE style="font-size:8">MARCAR &quot;X&quot; EN LA CELDA QUE CORRESPONDA</TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="2" SDNUM="12298;"><B><FONT SIZE=3>2</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>EXONERACIÓN DE RESPONSABILIDAD POR ABORTO</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>DECLARO QUE NINGÚN PROFESIONAL O FUNCIONARIO DE ESTE ESTABLECIMIENTO DE SALUD HA REALIZADO PROCEDIMIENTOS PARA PROVOCAR ESTE ABORTO Y QUE INGRESO LIBRE Y VOLUNTARIAMENTE PARA RECIBIR EL TRATAMIENTO NECESARIO PARA MI ENFERMEDAD </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FF0000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL PACIENTE</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL REPRESENTANTE LEGAL O TESTIGO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD HEIGHT=8 COLSPAN=64 ALIGN=RIGHT VALIGN=MIDDLE style="font-size:8">MARCAR &quot;X&quot; EN LA CELDA QUE CORRESPONDA</TD>
    </TR>		<TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="3" SDNUM="12298;"><B><FONT SIZE=3>3</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>EXONERACIÓN DE RESPONSABILIDAD POR ABANDONO DE HOSPITAL SIN AUTORIZACIÓN MÉDICA</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>DECLARO QUE ME RETIRO VOLUNTARIAMENTE DE ESTE ESTABLECIMIENTO DE SALUD Y EXONERO AL PROFESIONAL TRATANTE Y AL PERSONAL ADMINISTRATIVO POR LOS RIESGOS A LA SALUD, QUE ME HAN ADVERTIDO CLARAMENTE</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FF0000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL PACIENTE</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL TESTIGO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL PROFESIONAL DE LA SALUD</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1 COLOR="#FF0000"><BR></FONT></B></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD HEIGHT=8 COLSPAN=64 ALIGN=RIGHT VALIGN=MIDDLE style="font-size:8">MARCAR &quot;X&quot; EN LA CELDA QUE CORRESPONDA</TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="4" SDNUM="12298;"><B><FONT SIZE=3>4</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>RETIRO DE MENOR DE EDAD O PERSONA INCAPACITADA</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>DECLARO QUE RETIRO AL PACIENTE DE ESTE ESTABLECIMIENTO DE SALUD, BAJO MI RESPONSABILIDAD DEBIDAMENTE CERTIFICADA, CON LA AUTORIZACIÓN MÉDICA CORRESPONDIENTE </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FF0000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>DECLARO QUE RETIRO AL PACIENTE DE ESTE ESTABLECIMIENTO, BAJO MI RESPONSABILIDAD Y SIN LA AUTORIZACIÓN DEL PROFESIONAL TRATANTE HE SIDO ADVERTIDO DE LAS CONSECUENCIAS DE ESTE ACTO NO AUTORIZADO Y ASUMO TODA LA RESPONSABILIDAD POR LAS CONSECUENCIAS NEGATIVAS </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FF0000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL REPRESENTANTE LEGAL</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=32 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL TESTIGO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=32 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL MEDICO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=32 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD HEIGHT=8 COLSPAN=64 ALIGN=RIGHT VALIGN=MIDDLE style="font-size:8">MARCAR &quot;X&quot; EN LA CELDA QUE CORRESPONDA</TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="5" SDNUM="12298;"><B><FONT SIZE=3>5</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>AUTORIZACIÓN DE EXTRACCIÓN DE ÓRGANOS PARA DONACIÓN Y/O TRASPLANTE</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>AUTORIZO AL PERSONAL DE SALUD DE ESTE ESTABLECIMIENTO PARA QUE EN VIDA SE ME EXTRAIGA EL O LOS ÓRGANOS CONVENIDOS, DONADOS PARA EL TRASPLANTE EN EL RECEPTOR SEÑALADO</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FF0000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>AUTORIZO PARA QUE, UNA VEZ TRANSCURRIDAS 48 HORAS DE MI MUERTE CEREBRAL, MIS ÓRGANOS SEAN EXTRAÍDOS PARA TRASPLANTE</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#FF0000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=9 HEIGHT=30 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>ÓRGANOS DONADOS</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=15 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>NOMBRE DE LOS RECEPTORES</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=30 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><B><FONT SIZE=1><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL REPRESENTANTE LEGAL</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL TESTIGO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL PROFESIONAL TRATANTE</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD HEIGHT=8 COLSPAN=64 ALIGN=RIGHT VALIGN=MIDDLE style="font-size:8">MARCAR &quot;X&quot; EN LA CELDA QUE CORRESPONDA</TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="6" SDNUM="12298;"><B><FONT SIZE=3>6</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>AUTORIZACIÓN PARA NECROPSIA</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=62 HEIGHT=30 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>AUTORIZO AL MÉDICO AUTORIZADO DE ESTE HOSPITAL PARA QUE PRACTIQUE LA NECROPSIA AL CADÁVER DEL FALLECIDO</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER BGCOLOR="#FFFF99"><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL REPRESENTANTE LEGAL</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL TESTIGO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=22 HEIGHT=16 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL MEDICO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
        <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
        <TD COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER> </TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=28 HEIGHT=26 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP / HCU-form.024 / 2008</FONT></B></TD>
        <TD COLSPAN=36 ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">AUTORIZACIÓN, EXONERACIÓN Y RETIRO</FONT></B></TD>
    </TR>
    </TBODY>
</TABLE>
<pagebreak>
    <TABLE FRAME=VOID CELLSPACING=0 COLS=65 RULES=NONE BORDER=0>
        <COLGROUP><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15><COL WIDTH=15></COLGROUP>
        <TBODY>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 5px solid #808080; border-right: 1px solid #c0c0c0" COLSPAN=16 WIDTH=239 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>INSTITUCIÓN DEL SISTEMA</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=19 WIDTH=283 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>UNIDAD OPERATIVA</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=5 WIDTH=75 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. UO</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=12 WIDTH=179 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. LOCALIZACIÓN</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=12 ROWSPAN=2 WIDTH=179 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>NUMERO DE                       HISTORIA CLÍNICA</FONT></B></TD>
            <TD WIDTH=15 ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #c0c0c0" COLSPAN=16 ROWSPAN=2 HEIGHT=49 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1 COLOR="#000000"><?php
                    echo text($titleres['pricelevel']);
                    ?></FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=19 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">ALTA VISION</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><BR></FONT></B></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PARROQUIA</FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CANTÓN</FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PROVINCIA</FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TARQUI</FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GYE</FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GUAYAS</FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE SDNUM="12298;0;0"><B><FONT SIZE=4><?php echo text($titleres['pubpid']) ?></FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE colspan=64><FONT SIZE=1 COLOR="#000000"></FONT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-left: 5px solid #808080" COLSPAN=11 HEIGHT=20 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO PATERNO</FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=11 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO MATERNO</FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=16 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>NOMBRES</FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SERVICIO </FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SALA</FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CAMA</FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0" COLSPAN=6 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>FECHA</FONT></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>HORA</FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #c0c0c0" COLSPAN=11 HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><?php echo text($titleres['lname'])?></FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=11 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><?php echo text($titleres['lname2'])?></FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=16 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><?php echo text($titleres['fname']) . " " . text($titleres['mname']) ?></FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><BR></FONT></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0" COLSPAN=6 ALIGN=CENTER SDNUM="12298;0;DD/MM/AAAA"><BR></TD>
            <TD STYLE="border-top: 1px solid #c0c0c0; border-bottom: 5px solid #808080; border-left: 1px solid #c0c0c0; border-right: 5px solid #808080" COLSPAN=4 ALIGN=CENTER><BR></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=5 colspan=1 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD COLSPAN=63 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#FFFFFF" STYLE="font-size:8" SDNUM="12298;0;00-0000000-0">TODA LA INFORMACIÓN ENTREGADA POR LOS PROFESIONALES AL PACIENTE SE HARÁ EN EL ÁMBITO DE LA CONFIDENCIALIDAD</TD>

        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="1" SDNUM="12298;"><B><FONT SIZE=3>1</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>INFORMACIÓN ENTREGADA POR EL PROFESIONAL TRATANTE SOBRE EL TRATAMIENTO</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>PROPÓSITOS</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>TERAPIA Y PROCEDIMIENTOS PROPUESTOS</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1 COLOR="#FF0000">
                        <?php
                        $query1 = "SELECT id, form_id, pid, ORDER_DETAILS, option_id, notes FROM form_eye_mag_ordenqxoi 
                           LEFT JOIN list_options on ORDER_DETAILS = title
                           WHERE form_id=? and pid=? and list_id=? ORDER BY id ASC";
                        $PLAN_results1 = sqlStatement($query1, array($form_id, $pid, 'cirugia_propuesta_defaults'));
                        if (!empty($PLAN_results1)) {
                            while ($plan_row1 = sqlFetchArray($PLAN_results1)) {
                                $Proposito = "SELECT * FROM list_options
                                  WHERE list_id = 'Proposito_Riesgo' and option_id = ? ";
                                $propositoITEM = sqlQuery($Proposito, array($plan_row1['option_id']));
                                echo $propositoITEM['title'] . ", ";
                            }
                        }
                        ?>
                    </FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                    <?php
                    $query1 = "SELECT id, form_id, pid, ORDER_DETAILS, option_id, notes FROM form_eye_mag_ordenqxoi 
                           LEFT JOIN list_options on ORDER_DETAILS = title
                           WHERE form_id=? and pid=? and list_id=? ORDER BY id ASC";
                    $PLAN_results1 = sqlStatement($query1, array($form_id, $pid, 'cirugia_propuesta_defaults'));
                    if (!empty($PLAN_results1)) {
                        while ($plan_row1 = sqlFetchArray($PLAN_results1)) {
                            echo $plan_row1['notes'] . ", ";
                        }
                    }
                    ?>
                </FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>Ojo Izquierdo</FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>RESULTADOS ESPERADOS</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>RIESGOS DE COMPLICACIONES CLÍNICAS</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                    <?php
                    $query2 = "SELECT id, pid, PROPOSITOOI FROM form_eye_locking 
                           WHERE id=? and pid=? ORDER BY id ASC";
                    $PLAN_results2 = sqlQuery($query2, array($form_id, $pid));
                    echo text($PLAN_results2['PROPOSITOOI']);
                    ?>
                </FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1 COLOR="#FF0000">
                        <?php
                        $query1 = "SELECT id, form_id, pid, ORDER_DETAILS, option_id, notes FROM form_eye_mag_ordenqxoi 
                           LEFT JOIN list_options on ORDER_DETAILS = title
                           WHERE form_id=? and pid=? and list_id=? ORDER BY id ASC";
                        $PLAN_results1 = sqlStatement($query1, array($form_id, $pid, 'cirugia_propuesta_defaults'));
                        if (!empty($PLAN_results1)) {
                            while ($plan_row1 = sqlFetchArray($PLAN_results1)) {
                                $Proposito = "SELECT * FROM list_options
                                  WHERE list_id = 'Proposito_Riesgo' and option_id = ? ";
                                $propositoITEM = sqlQuery($Proposito, array($plan_row1['option_id']));
                                echo $propositoITEM['notes'] . ", ";
                            }
                        }
                        ?>
                    </FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD COLSPAN=22 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL PROFESIONAL TRATANTE</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>ESPECIALIDAD</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
            <TD  COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÓDIGO</FONT></TD>
            <TD ALIGN=LEFT></TD>
            <TD COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
            <TD ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER><?php echo text($providerNAME);?></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER>OFTALMOLOGO</TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER>2286080</TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=8 ALIGN=CENTER><?php echo text($providerRegistro);?></TD>
            <TD ALIGN=LEFT><BR></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0">
            </TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B></B></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=CENTER></TD>
            <TD ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="2" SDNUM="12298;"><B><FONT SIZE=3>2</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>INFORMACIÓN ENTREGADA POR EL CIRUJANO SOBRE LA INTERVENCIÓN QUIRÚRGICA</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>PROPÓSITOS</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>INTERVENCIONES QUIRÚRGICAS PROPUESTAS</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>

                </FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                </FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>RESULTADOS ESPERADOS</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>RIESGO DE COMPLICACIONES QUIRÚRGICAS</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>

                </FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>

                </FONT>
            </TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD COLSPAN=22 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL CIRUJANO</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>ESPECIALIDAD</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
            <TD  COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÓDIGO</FONT></TD>
            <TD ALIGN=LEFT></TD>
            <TD COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
            <TD ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=28 ALIGN=CENTER><br /></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER SDVAL="2286080" SDNUM="12298;"></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=8 ALIGN=CENTER SDVAL="2520" SDNUM="12298;"></TD>
            <TD ALIGN=LEFT><BR></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="3" SDNUM="12298;"><B><FONT SIZE=3>3</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>INFORMACIÓN ENTREGADA POR EL ANESTESIÓLOGO SOBRE LA ANESTESIA </FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>PROPÓSITOS</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>ANESTESIA PROPUESTA</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT> </TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>RESULTADOS ESPERADOS</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>RIESGOS DE COMPLICACIONES ANESTÉSICAS</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=32 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=32 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1></FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD COLSPAN=22 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL ANESTESIÓLOGO</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>ESPECIALIDAD</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
            <TD  COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÓDIGO</FONT></TD>
            <TD ALIGN=LEFT></TD>
            <TD COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
            <TD ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=29 ALIGN=CENTER><br /></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER SDVAL="2286080" SDNUM="12298;"></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=8 ALIGN=CENTER><BR></TD>
            <TD ALIGN=LEFT><BR></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=5 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="4" SDNUM="12298;"><B><FONT SIZE=3>4</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>CONSENTIMIENTO INFORMADO DEL PACIENTE</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=5 colspan=51 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1></FONT></TD>
            <TD COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" STYLE="font-size:8" SDNUM="12298;0;00-0000000-0">FIRMAS DEL PACIENTE</TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>A</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>EL PROFESIONAL TRATANTE ME HA INFORMADO SATISFACTORIAMENTE ACERCA DE LOS MOTIVOS Y PROPÓSITOS DEL TRATAMIENTO PLANIFICADO PARA MI ENFERMEDAD</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ROWSPAN=3 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><I><FONT FACE="French Script MT" SIZE=6><BR></FONT></I></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 colspan=51 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33\ ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>B</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>EL PROFESIONAL TRATANTE ME HA EXPLICADO ADECUADAMENTE LAS ACTIVIDADES ESENCIALES QUE SE REALIZARÁN DURANTE EL TRATAMIENTO DE MI ENFERMEDAD</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>C</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>CONSIENTO A QUE SE REALICEN LAS INTERVENCIONES QUIRÚRGICAS, PROCEDIMIENTOS DIAGNÓSTICOS Y TRATAMIENTOS NECESARIOS PARA MI ENFERMEDAD</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ROWSPAN=3 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><I><FONT FACE="French Script MT" SIZE=6><BR></FONT></I></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>D</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>CONSIENTO A QUE ME ADMINISTREN LA ANESTESIA PROPUESTA</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>E</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>HE ENTENDIDO BIEN QUE EXISTE GARANTÍA DE LA CALIDAD DE LOS MEDIOS UTILIZADOS PARA EL TRATAMIENTO, PERO NO ACERCA DE LOS RESULTADOS</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ROWSPAN=3 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><I><FONT FACE="French Script MT" SIZE=6><BR></FONT></I></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>F</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>HE COMPRENDIDO PLENAMENTE LOS BENEFICIOS Y LOS RIESGOS DE COMPLICACIONES DERIVADAS DEL TRATAMIENTO </FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>G</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>EL PROFESIONAL TRATANTE ME HA INFORMADO QUE EXISTE GARANTÍA DE RESPETO A MI INTIMIDAD, A MIS CREENCIAS RELIGIOSAS Y A LA CONFIDENCIALIDAD DE LA INFORMACIÓN (INCLUSIVE EN EL CASO DE VIH/SIDA)</FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ROWSPAN=5 ALIGN=CENTER><BR></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=33 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>H</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>HE COMPRENDIDO QUE TENGO EL DERECHO DE ANULAR ESTE CONSENTIMIENTO INFORMADO EN EL MOMENTO QUE YO LO CONSIDERE NECESARIO.</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=48 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFFF"><B><FONT SIZE=1>I</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=48 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>DECLARO QUE HE ENTREGADO AL PROFESIONAL TRATANTE INFORMACIÓN COMPLETA Y FIDEDIGNA SOBRE LOS ANTECEDENTES PERSONALES Y FAMILIARES DE MI ESTADO DE SALUD.  ESTOY CONCIENTE DE QUE MIS OMISIONES O DISTORSIONES DELIBERADAS DE LOS HECHOS PUEDEN AFECTAR LOS RESULTADOS DEL TRATAMIENTO</FONT></TD>
            <TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD HEIGHT=2 ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=2 HEIGHT=17 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="5" SDNUM="12298;"><B><FONT SIZE=3>5</FONT></B></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=62 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>CONSENTIMIENTO INFORMADO DEL REPRESENTANTE LEGAL</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=64 HEIGHT=35 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCFFFF"><FONT SIZE=1>COMO RESPONSABLE LEGAL DEL PACIENTE, QUE HA SIDO CONSIDERADO POR AHORA IMPOSIBILITADO PARA DECIDIR EN FORMA AUTÓNOMA SU CONSENTIMIENTO, AUTORIZO LA REALIZACIÓN DEL TRATAMIENTO SEGÚN LA INFORMACIÓN ENTREGADA POR LOS PROFESIONALES DE LA SALUD EN ESTE DOCUMENTO.</FONT></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD COLSPAN=22 HEIGHT=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>NOMBRE DEL REPRESENTANTE LEGAL</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>PARENTESCO</FONT></TD>
            <TD COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TELÉFONO</FONT></TD>
            <TD  COLSPAN=8 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>CÉDULA DE CIUDADANÍA</FONT></TD>
            <TD ALIGN=LEFT></TD>
            <TD COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>FIRMA</FONT></TD>
            <TD ALIGN=LEFT></TD>
        </TR>
        <TR>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=22 HEIGHT=33 ALIGN=CENTER> </TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER><BR></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER> </TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=8 ALIGN=CENTER><BR></TD>
            <TD ALIGN=LEFT><BR></TD>
            <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF" SDNUM="12298;0;00-0000000-0"><B><BR></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        <TR>
            <TD COLSPAN=28 HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP / HCU-form.024 / 2008</FONT></B></TD>
            <TD COLSPAN=36 ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">CONSENTIMIENTO INFORMADO</FONT></B></TD>
            <TD ALIGN=LEFT><BR></TD>
        </TR>
        </TBODY>
    </TABLE>

</body>
</html>

<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('consentimiento_od' . '.pdf', 'I'); // D = Download, I = Inline
}
?>
