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
?>
<HTML>
<HEAD>
    <STYLE TYPE="text/css">
        p.texto{
            text-align: justify;
            font-family: Arial;
            font-size: 12px;
        }
        p.titulo{
            text-align: center;
            font-family: Arial;
            font-style: oblique;
            font-weight: bold;
            font-size: 20px;
        }
    </STYLE>
</HEAD>
<BODY>
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

<P class="titulo">INFORME MÉDICO</P>
<?php
echo "choice=" . $choice;
echo "pid=" . $pid;
echo "encounter=" . $encounter;
echo "form_id=" .  $form_id;
echo "formname=" . $form_name;
echo "id=" . $id;
?>

<P class="texto">Hoy
    <?php echo($dateddia); ?> de  <?php echo($nombreMes); ?> del <?php echo($datedano); ?> atend&iacute; a <?php echo text($titleres['lname'] . " " . $titleres['lname2'] . " " . $titleres['fname'] . " " . $titleres['mname'] . " ") ?>
    con C.I. <?php echo text($titleres['pubpid']) . "." ; ?>
    <?php
    echo text($reason);
    ?>
</P>
<P class="texto"><B>MOTIVO DE CONSULTA: </B><?php echo text($CC1); ?>.
</P>
<P class="texto"><B>EXAMEN FÍSICO: </B>
    <?php
    if ($SCODVA||$SCOSVA){
        echo text("AVSC ");
    }
    if ($SCODVA){
        echo text("OD: " . $SCODVA . " ");
    }
    if ($SCOSVA){
        echo text("OI: " . $SCOSVA);
    }
    if ($ODIOPAP||$OSIOPAP){
        echo text(" PIO ");
    }
    if ($ODIOPAP){
        echo text("OD: " . $ODIOPAP);
    }
    if ($OSIOPAP){
        echo text("OI: " . $OSIOPAP);
    }


    //Variables Segmento anterior
    if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS||$OSCONJ||$ODCONJ||$ODCORNEA||$OSCORNEA||$ODAC||$OSAC||$ODLENS||$OSLENS||$ODIRIS||$OSIRIS||$ODDISC||$OSDISC||$ODCUP||$OSCUP||
        $ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
        ?>
        <!-- start of Anterior Segment exam -->
        <span>Luego de realizar examen físico oftalmológico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrías bajo dilatación con gotas de tropicamida y fenilefrina a la Biomicroscopía se observa:</span>

    <?php }
    if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS) {
        echo "Examen Externo:";
        if ($RBROW||$RUL||$RLL||$RMCT||$RADNEXA) {
            echo "OD " . $RBROW . " " . $RUL . " " . $RLL . " " . $RMCT . " " . $RADNEXA . " ";
        }
        if ($LBROW||$LUL||$LLL||$LMCT||$LADNEXA) {
            echo "OI " . $LBROW . " " . $LUL . " " . $LLL . " " . $LMCT . " " . $LADNEXA . " ";
        }
        echo $EXT_COMMENTS;
    }
    //Segmento Anterior Ojo Derecho
    if ($ODCONJ||$ODCORNEA||$ODAC||$ODLENS||$ODIRIS) {
        ?>
        <br><span><b>OD: </b></span>
    <?php }
    if ($ODCONJ) {
        ?>
        <span><?php echo text("Conjuntiva " . $ODCONJ); ?></span>
    <?php }
    if ($ODCORNEA) {
        ?>
        <span><?php echo text("Córnea " . $ODCORNEA); ?></span>
    <?php }
    if ($ODAC) {
        ?>
        <span><?php echo text("Cámara Anterior " . $ODAC . " "); ?></span>
    <?php }
    if ($ODLENS) {
        ?>
        <span><?php echo text("Cristalino " . $ODLENS . " "); ?></span>
    <?php }
    if ($ODIRIS) {
        ?>
        <span><?php echo text("Iris " . $ODIRIS . " "); ?></span>
    <?php }
    //Segmento Anterior Ojo Izquierdo
    if ($OSCONJ||$OSCORNEA||$OSAC||$OSLENS||$OSIRIS) {
        ?>
        <br><span><b>OI: </b></span>
    <?php }
    if ($OSCONJ) {
        ?>
        <span><?php echo text("Conjuntiva " . $OSCONJ); ?></span>
    <?php }
    if ($OSCORNEA) {
        ?>
        <span><?php echo text("Córnea " . $OSCORNEA); ?></span>
    <?php }
    if ($OSAC) {
        ?>
        <span><?php echo text("Cámara Anterior " . $OSAC . " "); ?></span>
    <?php }
    if ($OSLENS) {
        ?>
        <span><?php echo text("Cristalino " . $OSLENS . " "); ?></span>
    <?php }
    if ($OSIRIS) {
        ?>
        <span><?php echo text("Iris " . $OSIRIS . " "); ?></span>
    <?php }
    if ($ODDISC||$OSDISC||$ODCUP||$OSCUP||$ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
        ?>
        <br><span>Al fondo de ojo:</span>
    <?php }
    //Retina Ojo Derecho
    if ($ODDISC||$ODCUP||$ODMACULA||$ODVESSELS||$ODPERIPH||$ODVITREOUS) {
        ?>
        <br><span><b>OD: </b></span>
    <?php }
    if ($ODDISC) {
        ?>
        <span><?php echo text("Disco " . $ODDISC . " "); ?></span>
    <?php }
    if ($ODCUP) {
        ?>
        <span><?php echo text("Copa " . $ODCUP . " "); ?></span>
    <?php }
    if ($ODMACULA) {
        ?>
        <span><?php echo text("Mácula " . $ODMACULA . " "); ?></span>
    <?php }
    if ($ODVESSELS) {
        ?>
        <span><?php echo text("Vasos " . $ODVESSELS . " "); ?></span>
    <?php }
    if ($ODPERIPH) {
        ?>
        <span><?php echo text("Periferia " . $ODPERIPH . " "); ?></span>
    <?php }
    if ($ODVITREOUS) {
        ?>
        <span><?php echo text("Vítreo " . $ODVITREOUS . " "); ?></span>
    <?php }

    //Retina Ojo Izquierdo
    if ($OSDISC||$OSCUP||$OSMACULA||$OSVESSELS||$OSPERIPH||$OSVITREOUS) {
        ?>
        <br><span><b>OI: </b></span>
    <?php }
    if ($OSDISC) {
        ?>
        <span><?php echo text("Disco " . $OSDISC . " "); ?></span>
    <?php }
    if ($OSCUP) {
        ?>
        <span><?php echo text("Copa " . $OSCUP . " "); ?></span>
    <?php }
    if ($OSMACULA) {
        ?>
        <span><?php echo text("Mácula " . $OSMACULA . " "); ?></span>
    <?php }
    if ($OSVESSELS) {
        ?>
        <span><?php echo text("Vasos " . $OSVESSELS . " "); ?></span>
    <?php }
    if ($OSPERIPH) {
        ?>
        <span><?php echo text("Periferia " . $OSPERIPH . " "); ?></span>
    <?php }
    if ($OSVITREOUS) {
        ?>
        <span><?php echo text("Vítreo " . $OSVITREOUS . " "); ?></span>
    <?php }
    ?>
    .</P>
<P class="texto"><B>IMPRESI&Oacute;N DIAGNOSTICA:</B>
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
            echo $item['codedesc'].". ";
        }

    }

    if ($PLAN && $PLAN != '0') { ?>
</p>
<p class="texto"><b>RECOMENDACIÓN: </b>
    <?php
    $PLAN_items = explode('|', $PLAN);
    foreach ($PLAN_items as $item) {
        echo  $item.", ";
    }

    if ($PLAN2) {
        echo $PLAN2."<br />";
    }
    }
    ?>

</P>
<P class="texto">Atentamente,</P>
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
$pdf->Output('informe_medico_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
