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
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

//Fecha del form_eye_mag
$query="select * from form_treatment_plan where
        id =? and pid = ?";


$egreso = sqlQuery($query, array($_GET['formid'],$pid));

$dateddia = date("d", strtotime($egreso['admit_date']));
$datedmes = date("F", strtotime($egreso['admit_date']));
$datedano = date("Y", strtotime($egreso['admit_date']));
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
?>
<HTML>
<HEAD>
    <STYLE TYPE="text/css">
        <!--
        @page { size: 8.27in 11.69in; margin-left: 1.18in; margin-right: 1.18in; margin-top: 0.98in; margin-bottom: 0.98in }
        P { margin-bottom: 0.08in; direction: ltr; color: #000000; widows: 2; orphans: 2 }
        -->
    </STYLE>
</HEAD>
<BODY TEXT="#000000">
<P ALIGN=CENTER>
    <?php
    echo $logo;
    ?>
</P>
<P ALIGN=CENTER STYLE="margin-bottom: 0.11in"><FONT SIZE=4><U><B>PLAN
                DE EGRESO</B></U></FONT></P>
<P STYLE="margin-bottom: 0.11in"><B>CIRUGIA OCULAR</B></P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">Diagn&oacute;stico
    egreso:</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in"><A NAME="_GoBack"></A>Fecha:
    <?php
    echo $dateddia . " de " . $nombreMes . " del " . $datedano;
    ?>
</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">Egresado a:	Casa</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">Instrucciones para el
    paciente <?php echo text(xlt($titleres['title']) . " " . $titleres['fname'] . " " . $titleres['lname']);  ?> y familia:</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">MEDICAMENTOS
    RECETADOS: <U><B>Tobracort (Tobramicina+Dexametazona) 1 gota cada 3
            horas por 21 d&iacute;as</B></U><U>.</U></P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">ACTIVIDAD: 	Se debe
    mantener reposo en la postura de acuerdo a la indicaci&oacute;n del
    m&eacute;dico.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">HIGIENE:	Debe ba&ntilde;arse
    el cuerpo con agua y jab&oacute;n incluyendo la cara.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">ALIMENTACI&Oacute;N:
    No hay restricci&oacute;n de dieta. Evite fumar o tomar alcohol hasta
    que est&eacute; completamente recuperado.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">CUIDADOS
    ESPECIALES:	Mantenga parche y protector ocular durante 24 horas,
    seg&uacute;n prescripci&oacute;n m&eacute;dica. Controle sangrado
    (Observe si mancha la gasa).</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">EDUCACION AL PACIENTE:
    Pueden sentir picor, sensaci&oacute;n de cuerpo extra&ntilde;o,
    pinchazos espor&aacute;dicos: Son consecuencia de los punto
    conjuntivales.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">Cumpla con el
    tratamiento ambulatorio ya sea con colirios o pomadas de acuerdo a la
    prescripci&oacute;n de su m&eacute;dico.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">Un paciente sometido a
    cirug&iacute;a ocular <U><B>NO DEBE</B></U> en ning&uacute;n caso:
    Conducir, realizar actividades peligrosas, ni levantar pesos.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">La lectura y la
    televisi&oacute;n no est&aacute;n contraindicadas, excepto si
    producen molestias o impiden la posici&oacute;n recomendada.</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">OTROS:</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">M&eacute;dico
    tratante: <?php
    echo getProviderName($providerID);
    ?>
    Tel&eacute;fono: 2286080</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in"><U><B>INFORME DE
            EGRESO DE ENFERMERIA</B></U>:</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in">PACIENTE EGRESA EN
    CONDICIONES FAVORABLES PARA SU SALUD, CON INDICACIONES MEDICA, SI
    LLEVA LA MEDICACI&Oacute;N.</P>
</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('plan_egreso_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
