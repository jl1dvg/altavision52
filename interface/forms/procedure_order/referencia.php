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

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, phone_home, phone_cell, phone_contact, sex,providerID,DATE_FORMAT(DOB,'%m') as DOB_M,DATE_FORMAT(DOB,'%d') as DOB_D,DATE_FORMAT(DOB,'%Y') as DOB_A, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_TS");

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
//Inicio SQL Autorizaion
$queryProced="select * from procedure_order_code AS PO
        left join procedure_order as POC on (PO.procedure_order_id = POC.procedure_order_id)
        where
        patient_id=? and
        encounter_id=? and
        POC.procedure_order_id = ? and
        activity = 1
        ORDER BY ojo";

$order_data = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_data2 = sqlQuery($queryProced, array($pid,$encounter,$_GET['formid']));
$order_dataF = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_fecha = sqlFetchArray($order_dataF);
$providerID  =  getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);


$CarePlanSQL = sqlQuery("SELECT * FROM procedure_order_code AS PO
  LEFT JOIN procedure_order AS POC ON (PO.procedure_order_id = POC.procedure_order_id) WHERE " .
    "patient_id = ? AND encounter_id = ? ", array($pid, $encounter));
$NombreProcedimiento =  $CarePlanSQL['procedure_name'];
$FechaProcedimiento = $CarePlanSQL['date_ordered'];
$CodigoProcedimiento = $CarePlanSQL['procedure_code'];
$dated = new DateTime($FechaProcedimiento);
$OjoProcedimiento = $CarePlanSQL['description'];
$Procedimiento = $CarePlanSQL['codetext'];
$MedicoProcedimiento = $CarePlanSQL['care_plan_type'];

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
?>
<HTML>
<HEAD>

</HEAD>

<BODY TEXT="#000000">
<div>FORMULARIO    DE       REFERENCIA, DERIVACION CONTRAREFERENCIA  Y REFERENCIA</div>
<TABLE CELLSPACING=0 COLS=12 RULES=NONE BORDER=0>
    <TBODY>
    <TR>
        <TD COLSPAN=1 WIDTH=160 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
            </FONT></TD>
        <TD COLSPAN=4 ROWSPAN=2 WIDTH=366 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=4 COLOR="#000000"></FONT></B></TD>
        <TD WIDTH=60 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT COLOR="#000000"></FONT></B></TD>
        <TD WIDTH=60 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD WIDTH=60 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD WIDTH=48 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD WIDTH=64 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD WIDTH=65 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD HEIGHT=19 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">l. DATOS  DEL USARIO</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=20 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">APELLIDO PATERNO </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">APELLIDO MATERNO</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">NOMBRES</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Fecha de Nacimiento</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">EDAD</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">SEXO</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=18 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo($titleres['lname']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo($titleres['lname2']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo($titleres['fname']). " " .($titleres['mname'])?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="6" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo($titleres['DOB_D']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="9" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo($titleres['DOB_M']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="1943" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo($titleres['DOB_A']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="74" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
                <?php
                //fecha actual

                $dia=date(j);
                $mes=date(n);
                $ano=date(Y);

                //fecha de nacimiento

                $dianaz=$titleres['DOB_D'];
                $mesnaz=$titleres['DOB_M'];
                $anonaz=$titleres['DOB_A'];

                //si el mes es el mismo pero el día inferior aun no ha cumplido años, le quitaremos un año al actual

                if (($mesnaz == $mes) && ($dianaz > $dia)) {
                    $ano=($ano-1); }

                //si el mes es superior al actual tampoco habrá cumplido años, por eso le quitamos un año al actual

                if ($mesnaz > $mes) {
                    $ano=($ano-1);}

                //ya no habría mas condiciones, ahora simplemente restamos los años y mostramos el resultado como su edad

                $edad=($ano-$anonaz);

                print $edad;


                ?>
            </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php if ($titleres['sex'] == "Male") {
                    echo "H";
                }
                else {
                    echo "M";
                } ?></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=7 HEIGHT=18 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Dia</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Mes</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">A&ntilde;o</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">H/M</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" HEIGHT=19 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">NACIONALIDAD</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">PAIS</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">CEDULA O PASAPORTE</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">LUGAR DE RESIDENCIA </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">DIRECCION DE DOMICILIO</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" HEIGHT=19 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ECUATORIANA</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ECUADOR</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo ($titleres['pubpid']) ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=6 HEIGHT=19 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Prov.</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Canton</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Parroq.</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" HEIGHT=21 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">E-MAIL:</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">TELEFONO: <?php echo ($titleres['phone_contact'] . " " . $titleres['phone_cell']);?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php echo ($titleres['phone_home']); ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">FECHA:</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=4 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>

    <TR>
        <TD COLSPAN=2 HEIGHT=19 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">ll. REFERENCIA  1</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">        DERIVACION   2</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">X</FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=3 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">1 DATOS INSTITUCIONALES</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ENTIDAD DEL SISTEMA </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">HISTORIA CLINICA</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ESTABLECIMIENTO DE SALUD </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">TIPO</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">DISTRITO / AREA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=21 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">IESS</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">ALTAVISION</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=9 HEIGHT=18 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">REFIERE  O DERIVA A:</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">FECHA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=21 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">IESS</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">ALTAVISION</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">AMBULATORIO</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php
                echo strtoupper($order_data2['history_order']);
                ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="30" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
                <?php
                echo date("d", strtotime($order_fecha['date_collected']));
                ?></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="10" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
                <?php       echo date("m", strtotime($order_fecha['date_collected']));
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=RIGHT VALIGN=BOTTOM SDVAL="2017" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
                <?php       echo date("Y", strtotime($order_fecha['date_collected']));
                ?></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=18 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Entidad del Sistema</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Establecimiento de Salud</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Servico</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Especialidad</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Dia</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Mes</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">A&ntilde;o</FONT></TD>
    </TR>

    <TR>
        <TD COLSPAN=5 HEIGHT=19 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">2. MOTIVO DE LA REFERENCIA O DERIVACION </FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=3 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">LIMITADA CAPACIDAD RESOLUTIVA </FONT></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDVAL="1" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">1</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=5 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">SATURACION DE CAPACIDAD INSTALADA</FONT></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDVAL="4" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">4</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=3 HEIGHT=16 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">AUSENCIA DEL PROFESIONAL </FONT></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDVAL="2" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">2</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=3 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">OTROS  ESPECIFIQUE</FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=CENTER VALIGN=MIDDLE SDVAL="5" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">5</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">X</FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=3 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">FALTA DEL PROFESIONAL</FONT></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDVAL="3" SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">3</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=5 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">CONTINUAR TRATAMIENTO</FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>

    <TR>
        <TD COLSPAN=4 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">3. RESUMEN DEL CUADRO CLINICO</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php
                echo text(substr($reason,0,200)); ?></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php
                echo text(substr($reason,200,400)); ?></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php
                echo "Al momento paciente presenta " . text(substr($CC1,0,90)); ?></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=15 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=7 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">4. HALLAZGOS RELEVANTES DE EXAMENES  Y PROCEDIMIENTOS DIAGNOSTICOS</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM><FONT SIZE=1 COLOR="#000000">
                <?php

                function ExamOftal($SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$RBROW,$LBROW,$RUL,$LUL,$RLL,$LLL,$RMCT,$LMCT,$RADNEXA,$LADNEXA,$EXT_COMMENTS,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                                   $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS){
                    if ($SCODVA||$SCOSVA||$ODIOPAP||$OSIOPAP||$RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS||$OSCONJ||$ODCONJ||$ODCORNEA||$OSCORNEA||$ODAC||$OSAC||$ODLENS||$OSLENS||$ODIRIS||$OSIRIS||$ODDISC||$OSDISC||$ODCUP||$OSCUP||
                        $ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                        if ($SCODVA){
                            $ExamOFT = $ExamOFT .  ("OD: " . $SCODVA . ", ");
                        }
                        if ($SCOSVA){
                            $ExamOFT = $ExamOFT .  ("OI: " . $SCOSVA . ", ");
                        }
                        if ($ODIOPAP){
                            $ExamOFT = $ExamOFT .  ("OD: " . $ODIOPAP . ", ");
                        }
                        if ($OSIOPAP){
                            $ExamOFT = $ExamOFT .  ("OI: " . $OSIOPAP . ", ");
                        }
                        $ExamOFT = $ExamOFT . "Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa: ";
                        if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS) {
                            $ExamOFT = $ExamOFT . "Examen Externo:";
                        }
                        if ($RBROW||$RUL||$RLL||$RMCT||$RADNEXA) {
                            $ExamOFT = $ExamOFT .  ("OD " . $RBROW . " " . $RUL . " " . $RLL . " " . $RMCT . " " . $RADNEXA . " ");
                        }
                        if ($LBROW||$LUL||$LLL||$LMCT||$LADNEXA) {
                            $ExamOFT = $ExamOFT .  ("OI " . $LBROW . " " . $LUL . " " . $LLL . " " . $LMCT . " " . $LADNEXA . " ");
                        }
                        if($EXT_COMMENTS) {
                            $ExamOFT = $ExamOFT . $EXT_COMMENTS;
                        }
                        if ($ODCONJ||$ODCORNEA||$ODAC||$ODLENS||$ODIRIS) {
                            $ExamOFT = $ExamOFT .  "OD: ";
                        }
                        if ($ODCONJ) {
                            $ExamOFT = $ExamOFT .  ("Conjuntiva " . $ODCONJ . ", ");
                        }
                        if ($ODCORNEA) {
                            $ExamOFT = $ExamOFT .  ("Córnea " . $ODCORNEA . ", ");
                        }
                        if ($ODAC) {
                            $ExamOFT = $ExamOFT .  ("Cámara Anterior " . $ODAC . ", ");
                        }
                        if ($ODLENS) {
                            $ExamOFT = $ExamOFT .  ("Cristalino " . $ODLENS . ", ");
                        }
                        if ($ODIRIS) {
                            $ExamOFT = $ExamOFT .  ("Iris " . $ODIRIS . ", ");
                        }
                        if ($OSCONJ||$OSCORNEA||$OSAC||$OSLENS||$OSIRIS) {
                            $ExamOFT = $ExamOFT .  "OI: ";
                        }
                        if ($OSCONJ) {
                            $ExamOFT = $ExamOFT .  ("Conjuntiva " . $OSCONJ . ", ");
                        }
                        if ($OSCORNEA) {
                            $ExamOFT = $ExamOFT .  ("Córnea " . $OSCORNEA . ", ");
                        }
                        if ($OSAC) {
                            $ExamOFT = $ExamOFT .  ("Cámara Anterior " . $OSAC . ", ");
                        }
                        if ($OSLENS) {
                            $ExamOFT = $ExamOFT .  ("Cristalino " . $OSLENS . ", ");
                        }
                        if ($OSIRIS) {
                            $ExamOFT = $ExamOFT .  ("Iris " . $OSIRIS . ", ");
                        }
                        if ($ODDISC||$OSDISC||$ODCUP||$OSCUP||$ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                            $ExamOFT = $ExamOFT .  "Al fondo de ojo: ";
                        }
                        //Retina Ojo Derecho
                        if ($ODDISC||$ODCUP||$ODMACULA||$ODVESSELS||$ODPERIPH||$ODVITREOUS) {
                            $ExamOFT = $ExamOFT .  "OD: ";
                        }
                        if ($ODDISC) {
                            $ExamOFT = $ExamOFT .  ("Disco " . $ODDISC . ", ");
                        }
                        if ($ODCUP) {
                            $ExamOFT = $ExamOFT .  ("Copa " . $ODCUP . ", ");
                        }
                        if ($ODMACULA) {
                            $ExamOFT = $ExamOFT .  ("Mácula " . $ODMACULA . ", ");
                        }
                        if ($ODVESSELS) {
                            $ExamOFT = $ExamOFT .  ("Vasos " . $ODVESSELS . ", ");
                        }
                        if ($ODPERIPH) {
                            $ExamOFT = $ExamOFT .  ("Periferia " . $ODPERIPH . ", ");
                        }
                        if ($ODVITREOUS) {
                            $ExamOFT = $ExamOFT .  ("Vítreo " . $ODVITREOUS . ", ");
                        }
                        //Retina Ojo Izquierdo
                        if ($OSDISC||$OSCUP||$OSMACULA||$OSVESSELS||$OSPERIPH||$OSVITREOUS) {
                            $ExamOFT = $ExamOFT .  "OI: ";
                        }
                        if ($OSDISC) {
                            $ExamOFT = $ExamOFT .  ("Disco " . $OSDISC . ", ");
                        }
                        if ($OSCUP) {
                            $ExamOFT = $ExamOFT .  ("Copa " . $OSCUP . ", ");
                        }
                        if ($OSMACULA) {
                            $ExamOFT = $ExamOFT .  ("Mácula " . $OSMACULA . ", ");
                        }
                        if ($OSVESSELS) {
                            $ExamOFT = $ExamOFT .  ("Vasos " . $OSVESSELS . ", ");
                        }
                        if ($OSPERIPH) {
                            $ExamOFT = $ExamOFT .  ("Periferia " . $OSPERIPH . ", ");
                        }
                        if ($OSVITREOUS) {
                            $ExamOFT = $ExamOFT .  ("Vítreo " . $OSVITREOUS . ", ");
                        }
                        return $ExamOFT;
                    }


                }
                function SegAntOD($SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                                  $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS){
                    if ($OSCONJ||$ODCONJ||$ODCORNEA||$OSCORNEA||$ODAC||$OSAC||$ODLENS||$OSLENS||$ODIRIS||$OSIRIS||$ODDISC||$OSDISC||$ODCUP||$OSCUP||
                        $ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                        $SegAntOD = "Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa:";
                        if ($ODCONJ||$ODCORNEA||$ODAC||$ODLENS||$ODIRIS) {
                            $SegAntOD = $SegAntOD . "OD: ";
                        }
                        if ($ODCONJ) {
                            $SegAntOD = $SegAntOD . "Conjuntiva " . $ODCONJ . ", ";
                        }
                        if ($ODCORNEA) {
                            $SegAntOD = $SegAntOD . "Córnea " . $ODCORNEA . ", ";
                        }
                        if ($ODAC) {
                            $SegAntOD = $SegAntOD . "Cámara Anterior " . $ODAC . ", ";
                        }
                        if ($ODLENS) {
                            $SegAntOD = $SegAntOD . "Cristalino " . $ODLENS . ", ";
                        }
                        if ($ODIRIS) {
                            $SegAntOD = $SegAntOD . "Iris " . $ODIRIS . ", ";
                        }

                    }
                    return $SegAntOD;
                }

                echo wordwrap(ExamOftal($SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$RBROW,$LBROW,$RUL,$LUL,$RLL,$LLL,$RMCT,$LMCT,$RADNEXA,$LADNEXA,$EXT_COMMENTS,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                    $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS),200,"</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM=\"1033;1033;General\">
         <FONT SIZE=1 COLOR=\"#000000\">");
                ?>

            </FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=2 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">5. DIAGNOSTICO</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">CIE- 10</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">PRE</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">DEF</FONT></B></TD>
    </TR>
    <?php
    /**
     *  Retrieve and Display the IMPPLAN_items for the Impression/Plan zone.
     */
    $query = "select * from form_".$form_folder."_impplan where form_id=? and pid=? order by IMPPLAN_order ASC";
    $result =  sqlStatement($query, array($form_id,$pid));
    $i='0';
    $order   = array("\r\n", "\n", "\r","\v","\f","\x85","\u2028","\u2029");
    $replace = "<br />";
    // echo '<ol>';
    while ($ip_list = sqlFetchArray($result)) {
        $newdata =  array (
            'form_id'       => $ip_list['form_id'],
            'pid'           => $ip_list['pid'],
            'title'         => $ip_list['title'],
            'code'          => $ip_list['code'],
            'codetype'      => $ip_list['codetype'],
            'codetext'      => $ip_list['codetext'],
            'codedesc'      => $ip_list['codedesc'],
            'plan'          => str_replace($order, $replace, $ip_list['plan']),
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        );
        $IMPPLAN_items[$i] =$newdata;
        $i++;
    }

    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
    foreach ($IMPPLAN_items as $item) {
        $pattern = '/Code/';
        if (preg_match($pattern, $item['code'])) {
            $item['code'] = '';
        }

        if ($item['codetext'] > '') {
            echo "<TR>
                              <TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\"
                              COLSPAN=8 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM=\"1033;1033;General\"><FONT SIZE=1 COLOR=\"#000000\">" . $item['codedesc'].".</FONT></TD><TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\"
                              COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM=\"1033;1033;General\"><FONT SIZE=1 COLOR=\"#000000\">" . $item['code'] . "</FONT></TD><TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\"
                              ALIGN=LEFT VALIGN=BOTTOM SDNUM=\"1033;1033;General\"><FONT SIZE=1 COLOR=\"#000000\"><BR></FONT></TD>
                              <TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\"
                              ALIGN=LEFT VALIGN=BOTTOM SDNUM=\"1033;1033;General\"><FONT SIZE=1 COLOR=\"#000000\">X</FONT></TD>
                              </TR>";
        }

    }
    ?>


    <TR>
        <TD COLSPAN=5 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">6. EXAMENES  / PROCEDIMIENTOS  SOLICITADOS</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">CODIGO TARIFARIO</FONT></B></TD>
    </TR>

    <?php
    $i='0';
    while ($order_list = sqlFetchArray($order_data)) {
        $newORDERdata =  array (
            'procedure_name'       => $order_list['procedure_name'],
            'procedure_code'       => $order_list['procedure_code'],
            'ojo'                  => $order_list['ojo'],
            'dosis'                => $order_list['dosis'],
            'clinical_hx'          => $order_list['clinical_hx'],
        );
        print "<TR><TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\" COLSPAN=8 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM=\"1033;1033;General\"><FONT SIZE=1 COLOR=\"#000000\">" .
            ($newORDERdata['procedure_name']) . " " . ($newORDERdata['ojo']) . " ";
        if ($newORDERdata['dosis']) {
            print ($newORDERdata['dosis']) . " dosis";
        }
        if ($newORDERdata['clinical_hx']) {
            print ($newORDERdata['clinical_hx']);
        }
        print "</FONT></TD><TD STYLE=\"border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000\" COLSPAN=4 ALIGN=CENTER VALIGN=BOTTOM SDVAL=\"66982\" SDNUM=\"1033;1033;General\"><FONT SIZE=1 COLOR=\"#000000\">" . ($newORDERdata['procedure_code']).
            "</FONT></TD></TR>";
        $ORDER_items[$i] =$newORDERdata;
        $i++;
    }


    ?>

    <TR>
        <TD COLSPAN=2 HEIGHT=16 ALIGN=center VALIGN=middle SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php
                echo getProviderName($providerID);
                ?></FONT></TD>
        <TD COLSPAN=3 ALIGN=center VALIGN=middle SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><?php
                echo getProviderRegistro($providerID);
                ?></FONT></TD>
        <TD COLSPAN=3 ALIGN=center VALIGN=middle SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
            </FONT></TD>
        <TD COLSPAN=4 ALIGN=center VALIGN=middle SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">
            </FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=2 HEIGHT=19 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">NOMBRE</FONT></B></TD>
        <TD COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">COD. MSP. PROF.</FONT></B></TD>
        <TD COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">DIRECTOR MEDICO</FONT></B></TD>
        <TD COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">MEDICO VERIFICADOR</FONT></B></TD>
    </TR>
    <TR>
        <TD HEIGHT=15 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=5 HEIGHT=19 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">1. DATOS INSTITUCIONALES</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD HEIGHT=19 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">lll. CONTRAREFERENCIA                 3</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=3 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">REFERENCIA INVERSA        4</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ENTIDAD DEL SISTEMA </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">HIST, CLINICA #</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ESTABLECIMIENTO DE SALUD </FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">TIPO</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">SERVICIO</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">ESPECIALIDAD</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=18 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-bottom: 1px solid #000000; border-left: 3px solid #000000; border-right: 1px solid #000000" HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">lll. CONTRAREFERENCIA                 3</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #000000; border-left: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-bottom: 1px solid #000000; border-left: 1px solid #000000" COLSPAN=3 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">REFERENCIA INVERSA        4</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">FECHA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=18 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 HEIGHT=19 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Entidad del Sistema</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Establecimiento de Salud</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Tipo</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Districto/Area</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Dia</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">Mes</FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000">A&ntilde;o</FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=4 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">2. RESUMEN DELCUADRO CLINICO</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>

    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=15 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=7 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">3. HALLAZGOS RELEVANTES DE EXAMENES  Y PROCEDIMIENTOS DIAGNOSTICOS</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=6 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">4. TRATAMIENTOS Y PROCEDIMIENTOS TERAPEUTICOS REALIZADOS</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=12 HEIGHT=21 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=2 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">5. DIAGNOSTICO</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">CIE- 10</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">PRE</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">DEF</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=8 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=8 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=2 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD COLSPAN=12 HEIGHT=18 ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">6. TRATAMIENTO RECOMENDADO A SEGUIR EN EL ESTABLECIMIENTO DE SALUD DE MENOR NIVEL DE COMPLEJIDAD</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=8 HEIGHT=18 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=4 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD HEIGHT=16 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>
    <TR>
        <TD HEIGHT=16 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><FONT SIZE=1 COLOR="#000000"><BR></FONT></TD>
    </TR>

    <TR>
        <TD COLSPAN=3 HEIGHT=16 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">________________________</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD COLSPAN=3 ALIGN=CENTER VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">__________________________</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD COLSPAN=4 ALIGN=LEFT VALIGN=BOTTOM SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">   ________________________________________</FONT></B></TD>
    </TR>
    <TR>
        <TD COLSPAN=3 HEIGHT=19 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">NOMBRE</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD COLSPAN=3 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">COD. MSP. PROF.</FONT></B></TD>
        <TD ALIGN=LEFT VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000"><BR></FONT></B></TD>
        <TD COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;General"><B><FONT SIZE=1 COLOR="#000000">FIRMA</FONT></B></TD>
    </TR>
    </TBODY>
</TABLE>
<!-- ************************************************************************** -->
</BODY>

</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('plan_egreso_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
