<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once(dirname(__FILE__) ."/../../../library/acl.inc");
require_once(dirname(__FILE__) ."/../../../library/api.inc");
require_once(dirname(__FILE__) ."/../../../library/lists.inc");
require_once(dirname(__FILE__) ."/../../../library/forms.inc");
require_once(dirname(__FILE__) ."/../../../library/patient.inc");
require_once(dirname(__FILE__) ."/../../../controllers/C_Document.class.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once(dirname(__FILE__) ."/../../../library/lists.inc");
require_once ("report.php");
use OpenEMR\Services\FacilityService;
$form_name = "eye_mag";
$form_folder = "eye_mag";
$facilityService = new FacilityService();
require_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
if ($_REQUEST['CHOICE']) {
    $choice = $_REQUEST['choice'];
}
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
if (!$id) {
    $id=$form_id;
}

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

//Fecha del form_eye_mag
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

$queryform = "select * from forms
                where
                pid=? and
                encounter=? and
                formdir = 'eye_mag' and
                deleted = 0";

$fechaINGRESO = sqlQuery($queryform, array($_GET['patientid'],$_GET['visitid']));

$providerID  =  getProviderIdOfEncounter($encounter);
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
    'margin_left' => $GLOBALS['pdf_left_margin'],
    'margin_right' => $GLOBALS['pdf_right_margin'],
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
<HTML>
<HEAD>
</HEAD>
<BODY TEXT="#000000">
<?php
$CarePlanSQL = sqlquery("SELECT * FROM form_care_plan WHERE " .
                        "pid = ? AND encounter = ? AND id = ? AND activity = 1 ",array($_GET["patientid"], $_GET["visitid"],$_GET['formid']));
$FechaProcedimiento = $CarePlanSQL['date'];
$CodigoProcedimiento = $CarePlanSQL['code'];
$dated = new DateTime($FechaProcedimiento);
$dateddia = $dated->format('d');
$datedmes = $dated->format('F');
$datedano = $dated->format('Y');
$visit_date = oeFormatShortDate($dated);
$mes = date('F', $timestamp);
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$nombreMes = str_replace($meses_EN, $meses_ES, $datedmes);
$OjoProcedimiento = $CarePlanSQL['description'];
$Procedimiento = $CarePlanSQL['codetext'];
$MedicoProcedimiento = $CarePlanSQL['care_plan_type'];
?>
<page_header>
    <table>
        <tr>
            <td>
                <SPAN CLASS="sd-abs-pos" STYLE="position: absolute; top: -0.67in; left: 1.81in; width: 249px">
                    <?php
                    echo $logo;
                    ?>
                </SPAN>
            </td>
            <td>
                <h2><?php echo $facility['name'] ?></h2>
                <p class="texto">
                    <?php echo $facility['street'] ?><br>
                    <?php echo $facility['city'] ?>, <?php echo $facility['country_code'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
                    <b>Telfs: </b><?php echo $facility['phone'] ?><br>
                    <b>E-mail: </b><?php echo $facility['email'] ?>
                </p>

            </td>
        </tr>
    </table>
    <hr>
</page_header>
<P ALIGN=CENTER ><FONT SIZE=4><B>INFORME
            PROCEDIMIENTO</B></FONT></P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <FONT FACE="Times New Roman, serif"><FONT SIZE=3><B>Fecha</B>: <?php echo($dateddia); ?> de  <?php echo($nombreMes); ?> del <?php echo($datedano); ?></FONT></FONT></P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <FONT FACE="Times New Roman, serif"><FONT SIZE=3><B>Paciente: </B><?php echo text($titleres['fname'] . " " . $titleres['lname'] . " " . $titleres['lname2']); ?></FONT></FONT></P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <FONT FACE="Times New Roman, serif"><FONT SIZE=3><B>Ojo:</B>
            <?php echo $OjoProcedimiento; ?></FONT></FONT></P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%"><A NAME="_GoBack"></A>
    <FONT FACE="Times New Roman, serif"><FONT SIZE=3><B>Procedimiento:</B>
            <?php echo $Procedimiento; ?>. (COD. <?php echo $CodigoProcedimiento; ?>)</FONT></FONT></P>

<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <FONT FACE="Times New Roman, serif"><FONT SIZE=3>
            <?php

            if ($CodigoProcedimiento == '66761') {
                echo ("Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
        Se posiciona lente de contacto Abraham con soluci&oacute;n de viscoelastico Eyecoat.<br /><br />
        Se realiza Iridotomia YAG Laser, poder 6.0 MW, ojo " . text($OjoProcedimiento) . " 6 disparos, sin complicaciones.");
            }
            elseif ($CodigoProcedimiento == '281362') {
                echo("Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
        Se posiciona lente de contacto mainster central fundus con solución de viscoelastico Eyecoat.<br /><br />
        Una vez visualizado el fondo de ojo se realiza la aplicación de láser Multispot dentro
        del área nasal superior.");
            }
            elseif ($CodigoProcedimiento == '281351') {
                echo("Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
        Se posiciona lente de contacto mainster central fundus con solución de viscoelastico Eyecoat.<br /><br />
        Una vez visualizado el fondo de ojo se realiza la aplicación de láser Multispot por fuera
        de las arcadas vasculares de la retina en los cuatro cuadrantes respetando el nervio
        óptico y proliferaciones vítreo-retinianas.");
            }
            elseif ($CodigoProcedimiento == '281340') {
                echo("Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
        Se posiciona lente de contacto mainster central fundus con solución de viscoelastico Eyecoat.<br /><br />
        Una vez visualizado el fondo de ojo se realiza la aplicación de láser Multispot por fuera
        de las arcadas vasculares de la retina en los cuatro cuadrantes respetando el nervio
        óptico y proliferaciones vítreo-retinianas.");
            }
            elseif ($CodigoProcedimiento == '281339') {
                echo("Bajo anestesia tópica de clorhidrato de Proximetacaina al 0,5%.<br /><br />
        Con Yag laser modelo SuperQ de la marca Ellex se realizan disparos sobre la capsula
        posterior opaca logrando obtener disrupción de la misma y dejando el eje visual libre
        de opacidades.");
            }
            ?>
        </FONT></FONT></P>

<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <FONT FACE="Times New Roman, serif"><FONT SIZE=3><SPAN >Atentamente,</SPAN></FONT></FONT></P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P  ALIGN=JUSTIFY STYLE="margin-bottom: 0in; line-height: 100%">
    <BR>
</P>
<P STYLE="margin-bottom: 0.14in; line-height: 100%"><FONT FACE="Arial, sans-serif"><B>
            <?php
            $MedicoSQL = sqlquery("SELECT * FROM users WHERE " .
                "lname = '$MedicoProcedimiento' ");
            ?>
            <span>
</span><br>
            <?php echo ($MedicoSQL['suffix'] . " " . $MedicoSQL['fname'] . " " . $MedicoSQL['lname']);
            ?>
        </B></FONT></P>
<P STYLE="margin-bottom: 0.14in; line-height: 100%"><FONT FACE="Arial, sans-serif"><B><?php
            echo getProviderEspecialidad($MedicoSQL['id']);
            ?></B></FONT></FONT></P>
<P STYLE="margin-bottom: 0.14in; line-height: 100%"><FONT FACE="Arial, sans-serif"><B>Centro
            Oftalmol&oacute;gico AltaVisi&oacute;n</B></FONT></FONT></P>
<P STYLE="margin-bottom: 0.14in; line-height: 100%"><FONT FACE="Arial, sans-serif"><B>Guayaquil
            &ndash; Ecuador</B></FONT></FONT></P>
</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('informe_medico_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
