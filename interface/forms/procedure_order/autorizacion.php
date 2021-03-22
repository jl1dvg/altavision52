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
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, genericval1, providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

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
$queryProced="select * from procedure_order_code AS PO
                left join procedure_order as POC on (PO.procedure_order_id = POC.procedure_order_id)
                where
                patient_id=? and
                encounter_id=? and
                POC.procedure_order_id = ? and
                activity = 1
                ORDER BY ojo";

$OI = "OI";
$order_dataOD = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_dataOI = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_dataAO = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_dataF = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_fecha = sqlFetchArray($order_dataF);
$order_OD = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_OI = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_AO = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));


$providerID  =  getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);


$CarePlanSQL = sqlQuery("SELECT * FROM procedure_order_code AS PO
  LEFT JOIN procedure_order AS POC ON (PO.procedure_order_id = POC.procedure_order_id) WHERE " .
    "patient_id = ? AND encounter_id = ? ", array($pid, $encounter));

//Datos de la fecha
$FechaProcedimiento = $CarePlanSQL['date_ordered'];
$dated = new DateTime($FechaProcedimiento);
$dateddia = $dated->format('d');
$datedmes = $dated->format('F');
$datedano = $dated->format('Y');
$visit_date = oeFormatShortDate($dated);
$mes = date('F', $timestamp);
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$nombreMes = str_replace($meses_EN, $meses_ES, $datedmes);

//Procedimientos solicitados
$NombreProcedimiento =  $CarePlanSQL['procedure_name'];
$CodigoProcedimiento = $CarePlanSQL['procedure_code'];
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
</HEAD>
<BODY LANG="es-EC" TEXT="#000000" DIR="LTR">
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
<P STYLE="margin-bottom: 0.14in" align="right"><FONT SIZE=2 STYLE="font-size: 9pt">Guayaquil,
        <?php echo($dateddia); ?> de  <?php echo($nombreMes); ?> del <?php echo($datedano); ?><br/><br/></FONT></P>
<P STYLE="margin-bottom: 0.14in"><FONT SIZE=2 STYLE="font-size: 9pt">Se&ntilde;orita
        Magister</FONT></P>
<P STYLE="margin-bottom: 0.14in"><FONT SIZE=2 STYLE="font-size: 9pt"><B>ANITA MARIA JOUVIN HENRIQUEZ</B></FONT></P>
<P STYLE="margin-bottom: 0.14in"><FONT SIZE=2 STYLE="font-size: 9pt"><B>COORDINADOR
            PROVINCIAL DE PRESTACIONES DEL SEGURO DE SALUD GUAYAS</B></FONT></P>
<P STYLE="margin-bottom: 0.14in"><A NAME="_GoBack"></A><FONT SIZE=2 STYLE="font-size: 9pt">Cuidad.
        -</FONT></P>

<P STYLE="margin-bottom: 0.14in"><FONT SIZE=2 STYLE="font-size: 9pt">De
        mis consideraciones:</FONT></P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.14in"><FONT SIZE=2 STYLE="font-size: 9pt">Por
        medio de la presente comunico que mantenemos el siguiente expediente
        del paciente </FONT><FONT SIZE=2><B><?php echo text($titleres['lname']) . " " . text($titleres['lname2']) . " " . text($titleres['fname']) . " " . text($titleres['mname']) ?> CON
            C.I. <?php  echo text($titleres['pubpid']) ?></B></FONT><FONT SIZE=2>, </FONT><FONT SIZE=2><B>CON
            SEGURO IESS <?php echo text($titleres['genericval1']) ?>, </B></FONT><FONT SIZE=2>Paciente que necesita:</FONT><FONT COLOR="#000000"><FONT FACE="Arial, serif"><FONT SIZE=1 STYLE="font-size: 8pt"><B>
                    <?php
                    $i='0';
                    while ($order_list = sqlFetchArray($order_dataOD)) {
                        $newORDERdata =  array (
                            'procedure_name'       => $order_list['procedure_name'],
                            'procedure_code'       => $order_list['procedure_code'],
                            'ojo'                  => $order_list['ojo'],
                            'dosis'                  => $order_list['dosis'],
                        );
                        if ($newORDERdata['ojo'] == 'OD') {
                            print($newORDERdata['procedure_name']) . " (" . ($newORDERdata['procedure_code']) . "), ";
                            if ($newORDERdata['dosis']) {
                                print ($newORDERdata['dosis']) . " dosis ";
                            }
                            $ORDER_items[$i] =$newORDERdata;
                            $i++;
                        }
                    }
                    while ($a = sqlFetchArray($order_OD)) {
                        if ($a['ojo'] == 'OD') {
                            echo "Ojo Derecho. ";
                            break;
                        }
                    }
                    $i='0';
                    while ($order_list = sqlFetchArray($order_dataOI)) {
                        $newORDERdata =  array (
                            'procedure_name'       => $order_list['procedure_name'],
                            'procedure_code'       => $order_list['procedure_code'],
                            'ojo'                  => $order_list['ojo'],
                            'dosis'                  => $order_list['dosis'],
                        );
                        if ($newORDERdata['ojo'] == 'OI') {
                            print($newORDERdata['procedure_name']) . " (" . ($newORDERdata['procedure_code']) . "), ";
                            if ($newORDERdata['dosis']) {
                                print ($newORDERdata['dosis']) . " dosis ";
                            }
                            $ORDER_items[$i] =$newORDERdata;
                            $i++;
                        }
                    }
                    while ($a = sqlFetchArray($order_OI)) {
                        if ($a['ojo'] == 'OI') {
                            echo "Ojo Izquierdo. ";
                            break;
                        }
                    }
                    $i='0';
                    while ($order_list = sqlFetchArray($order_dataAO)) {
                        $newORDERdata =  array (
                            'procedure_name'       => $order_list['procedure_name'],
                            'procedure_code'       => $order_list['procedure_code'],
                            'ojo'                  => $order_list['ojo'],
                            'dosis'                  => $order_list['dosis'],
                        );
                        if ($newORDERdata['ojo'] == 'AO') {
                            print($newORDERdata['procedure_name']) . " (" . ($newORDERdata['procedure_code']) . "), ";
                            if ($newORDERdata['dosis']) {
                                print ($newORDERdata['dosis']) . " dosis ";
                            }
                            $ORDER_items[$i] =$newORDERdata;
                            $i++;
                        }
                    }
                    while ($a = sqlFetchArray($order_AO)) {
                        if ($a['ojo'] == 'AO') {
                            echo "Ambos Ojos. ";
                            break;
                        }
                    }
                    echo $order_fecha['clinical_hx'] . "<br />";

                    ?> </B></FONT></FONT></FONT><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt"><B>
            </B></FONT><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt">Solicitamos su respectiva aprobaci&oacute;n para dicho acto quir&uacute;rgico. </FONT></FONT>
</P>
<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.14in"><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt">Por
            la atenci&oacute;n otorgada a la presente le manifiesto mi
            agradecimiento y compromiso permanente por la atenci&oacute;n con
            celeridad, calidad esmerada y eficiente a los afiliados del IESS.</FONT></FONT></P>

<P ALIGN=JUSTIFY STYLE="margin-bottom: 0.14in"><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt">Atentamente,</FONT></FONT></P>
<P class="texto"><BR><BR>
</P>
<br>
<P>
    <B>
        <?php
        echo getProviderName($providerID);
        ?>
        <br>
        <?php
        echo getProviderEspecialidad($providerID);
        ?>
        <br>
        Centro Oftalmol&oacute;gico AltaVisi&oacute;n
        <br>
        Guayaquil &ndash; Ecuador</B></P>
</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('plan_egreso_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
