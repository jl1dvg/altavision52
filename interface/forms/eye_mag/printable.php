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
    'margin_left' => $GLOBALS['pdf_left_margin'],
    'margin_right' => $GLOBALS['pdf_right_margin'],
    'margin_top' => '15',
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
    <STYLE TYPE="text/css">
        span {
            text-align: justify;
            font-family: Arial;
            font-size: 12px;
        }

        p.texto {
            text-align: justify;
            font-family: Arial;
            font-size: 12px;
        }

        p.titulo {
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
                    <?php echo $facility['city'] ?>
                    , <?php echo $facility['country_code'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
                    <b>Telfs: </b><?php echo $facility['phone'] ?><br>
                    <b>E-mail: </b><?php echo $facility['email'] ?>
                </p>

            </td>
        </tr>
    </table>
    <hr>
</page_header>

<P class="titulo">INFORME MÉDICO</P>
<P class="texto">Hoy
    <?php echo($dateddia); ?> de <?php echo($nombreMes); ?> del <?php echo($datedano); ?> atend&iacute;
    a <?php echo text($titleres['lname'] . " " . $titleres['lname2'] . " " . $titleres['fname'] . " " . $titleres['mname'] . " ") ?>
    con C.I. <?php echo text($titleres['pubpid']) . "."; ?>
    <?php
    echo text($reason);
    ?>
</P>
<P class="texto"><B>MOTIVO DE CONSULTA: </B><?php echo text($CC1); ?>.
</P>
<P class="texto"><B>EXAMEN FÍSICO: </B>

    <?php
    if ($SCODVA || $SCOSVA) {
        echo text("Agudeza Visual Sin Corrección ");
    }
    if ($SCODVA) {
        echo text("OD: " . $SCODVA . " ");
    }
    if ($SCOSVA) {
        echo text("OI: " . $SCOSVA);
    }
    if ($ODIOPAP || $OSIOPAP) {
        echo text(" PIO ");
    }
    if ($ODIOPAP) {
        echo text("OD: " . $ODIOPAP . "mmHG ");
    }
    if ($OSIOPAP) {
        echo text("OI: " . $OSIOPAP . "mmHG");
    }
    ?>


    <?php
    if ($ODVA || $OSVA || $ARODSPH || $AROSSPH || $MRODSPH || $MROSSPH || $CRODSPH || $CROSSPH || $CTLODSPH || $CTLOSSPH) { ?>
    <br/>
    <table>
        <tr>
            <td colspan="9">
<P
        class="texto"><b><?php echo xlt('Refracción'); ?>
    </b></P>
</td>
</tr>
<tr style="text-align:center;padding:5px;text-decoration:underline;">
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Type') . $count_rx; ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Eye'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Sph{{Sphere}}'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Prism'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Acuity'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Mid{{Middle Distance Add}}'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('ADD{{Near Add}}'); ?></td>
    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('Acuity'); ?></td>
</tr>
<?php
$count_rx = '0';

$query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? ORDER BY RX_NUMBER";
$wear = sqlStatement($query, array($pid, $form_id, $encounter));
while ($wearing = sqlFetchArray($wear)) {
    $count_rx++;
    ${"display_W_$count_rx"} = '';
    ${"ODSPH_$count_rx"} = $wearing['ODSPH'];
    ${"ODCYL_$count_rx"} = $wearing['ODCYL'];
    ${"ODAXIS_$count_rx"} = $wearing['ODAXIS'];
    ${"OSSPH_$count_rx"} = $wearing['OSSPH'];
    ${"OSCYL_$count_rx"} = $wearing['OSCYL'];
    ${"OSAXIS_$count_rx"} = $wearing['OSAXIS'];
    ${"ODMIDADD_$count_rx"} = $wearing['ODMIDADD'];
    ${"OSMIDADD_$count_rx"} = $wearing['OSMIDADD'];
    ${"ODADD_$count_rx"} = $wearing['ODADD'];
    ${"OSADD_$count_rx"} = $wearing['OSADD'];
    ${"ODVA_$count_rx"} = $wearing['ODVA'];
    ${"OSVA_$count_rx"} = $wearing['OSVA'];
    ${"ODNEARVA_$count_rx"} = $wearing['ODNEARVA'];
    ${"OSNEARVA_$count_rx"} = $wearing['OSNEARVA'];
    ${"ODPRISM_$count_rx"} = $wearing['ODPRISM'];
    ${"OSPRISM_$count_rx"} = $wearing['OSPRISM'];
    ${"COMMENTS_$count_rx"} = $wearing['COMMENTS'];
    ${"W_$count_rx"} = '1';
    ${"RX_TYPE_$count_rx"} = $wearing['RX_TYPE'];
}
for ($i = 1; $i <= $count_rx; $i++) {
    if (${"RX_TYPE_$i"} == "0") {
        $RX_TYPE = '';
    } else if (${"RX_TYPE_$i"} == "1") {
        $RX_TYPE = xlt('Bifocals');
    } else if (${"RX_TYPE_$i"} == "2") {
        $RX_TYPE = xlt('Trifocals');
    } else if (${"RX_TYPE_$i"} == "3") {
        $RX_TYPE = xlt('Progressive');
    }
    ?>
    <tr>
        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo xlt('Subjetivo') . " #" . $i . ": "; ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODSPH_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODCYL_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODAXIS_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODPRISM_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODVA_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODMIDADD_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODADD_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODNEARVA_$i"}) ?: "-"); ?></td>
    </tr>
    <tr>
        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo $RX_TYPE; ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OS{{left eye}}'); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSSPH_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSCYL_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSAXIS_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSPRISM_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSVA_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSMIDADD_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSADD_$i"}) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSNEARVA_$i"}) ?: "-"); ?></td>
    </tr>
    <?php
    if (${"COMMENTS_$i"}) {
        ?>
        <tr>
            <td></td>
            <td colspan="2"><?php echo xlt('Comments'); ?>:</td>
            <td colspan="7"><?php echo text(${"COMMENTS_$i"}); ?></td>
        </tr>
        <?php
    }
}
if ($ARODSPH || $AROSSPH) { ?>
    <tr style="border-bottom:1pt solid black;">
        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo xlt('Auto Refracción'); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODSPH) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODCYL) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODAXIS) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODPRISM) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODVA) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODADD) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARNEARODVA) ?: "-"); ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td style="font-weight:400;font-size:10px;text-align:right;"><?php echo xlt('OS{{left eye}}'); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSSPH) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSCYL) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSAXIS) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSPRISM) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSVA) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSADD) ?: "-"); ?></td>
        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARNEAROSVA) ?: "-"); ?></td>
    </tr>
    <?php
}
?>
</table>
<?php } ?>


<?php

//Variables Segmento anterior
if ($RBROW || $LBROW || $RUL || $LUL || $RLL || $LLL || $RMCT || $LMCT || $RADNEXA || $LADNEXA || $EXT_COMMENTS || $OSCONJ || $ODCONJ || $ODCORNEA || $OSCORNEA || $ODAC || $OSAC || $ODLENS || $OSLENS || $ODIRIS || $OSIRIS || $ODDISC || $OSDISC || $ODCUP || $OSCUP ||
    $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
    ?>
    <!-- start of Anterior Segment exam -->
    <p class="texto">Luego de realizar examen físico oftalmológico y fondo de ojo con oftalmoscopia indirecta con lupa
        de 20 Dioptrías bajo dilatación con gotas de tropicamida y fenilefrina a la Biomicroscopía se observa:</p>

<?php }
if ($RBROW || $LBROW || $RUL || $LUL || $RLL || $LLL || $RMCT || $LMCT || $RADNEXA || $LADNEXA || $EXT_COMMENTS) {
    ?>
    <span><?php echo text("Examen Externo:"); ?></span>
<?php }
if ($RBROW || $RUL || $RLL || $RMCT || $RADNEXA) {
    ?>
    <span><b>OD: </b></span>
<?php }
if ($RBROW) {
    ?>
    <span><?php echo text("Ceja " . $RBROW . ", "); ?></span>
<?php }
if ($RUL) {
    ?>
    <span><?php echo text("Párpado superior " . $RUL . ", "); ?></span>
<?php }
if ($RLL) {
    ?>
    <span><?php echo text("Párpado inferior " . $RLL . ", "); ?></span>
<?php }
if ($RMCT) {
    ?>
    <span><?php echo text("Canto medio " . $RMCT . ", "); ?></span>
<?php }
if ($RADNEXA) {
    ?>
    <span><?php echo text("Anexos " . $RADNEXA . ", "); ?></span>
    <?php
}
if ($LBROW || $LUL || $LLL || $LMCT || $LADNEXA) {
    ?>
    <br><span><b>OI: </b></span>
<?php }
if ($LBROW) {
    ?>
    <span><?php echo text("Ceja " . $LBROW . ", "); ?></span>
<?php }
if ($LUL) {
    ?>
    <span><?php echo text("Párpado superior " . $LUL . ", "); ?></span>
<?php }
if ($LLL) {
    ?>
    <span><?php echo text("Párpado inferior " . $LLL . ", "); ?></span>
<?php }
if ($LMCT) {
    ?>
    <span><?php echo text("Canto medio " . $LMCT . ", "); ?></span>
<?php }
if ($LADNEXA) {
    ?>
    <span><?php echo text("Anexos " . $LADNEXA . ", "); ?></span>
    <?php
} ?>
<span><?php echo text($EXT_COMMENTS); ?></span>
<?php
//Segmento Anterior Ojo Derecho
if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS || $OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
    ?>
    <br><span><?php echo text("Segmento Anterior:"); ?></span>
<?php }
if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS) {
    ?>
    <span><b>OD: </b></span>
<?php }
if ($ODCONJ) {
    ?>
    <span><?php echo text("Conjuntiva " . $ODCONJ . ", "); ?></span>
<?php }
if ($ODCORNEA) {
    ?>
    <span><?php echo text("Córnea " . $ODCORNEA . ", "); ?></span>
<?php }
if ($ODAC) {
    ?>
    <span><?php echo text("Cámara Anterior " . $ODAC . ", "); ?></span>
<?php }
if ($ODLENS) {
    ?>
    <span><?php echo text("Cristalino " . $ODLENS . ", "); ?></span>
<?php }
if ($ODIRIS) {
    ?>
    <span><?php echo text("Iris " . $ODIRIS . ", "); ?></span>
<?php }
//Segmento Anterior Ojo Izquierdo
if ($OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
    ?>
    <br><span><b>OI: </b></span>
<?php }
if ($OSCONJ) {
    ?>
    <span><?php echo text("Conjuntiva " . $OSCONJ . ", "); ?></span>
<?php }
if ($OSCORNEA) {
    ?>
    <span><?php echo text("Córnea " . $OSCORNEA . ", "); ?></span>
<?php }
if ($OSAC) {
    ?>
    <span><?php echo text("Cámara Anterior " . $OSAC . ", "); ?></span>
<?php }
if ($OSLENS) {
    ?>
    <span><?php echo text("Cristalino " . $OSLENS . ", "); ?></span>
<?php }
if ($OSIRIS) {
    ?>
    <span><?php echo text("Iris " . $OSIRIS . ", "); ?></span>
<?php }
if ($ODDISC || $OSDISC || $ODCUP || $OSCUP || $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
    ?>
    <br><span>Al fondo de ojo:</span>
<?php }
//Retina Ojo Derecho
if ($ODDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
    ?>
    <br><span><b>OD: </b></span>
<?php }
if ($ODDISC) {
    ?>
    <span><?php echo text("Disco " . $ODDISC . ", "); ?></span>
<?php }
if ($ODCUP) {
    ?>
    <span><?php echo text("Copa " . $ODCUP . ", "); ?></span>
<?php }
if ($ODMACULA) {
    ?>
    <span><?php echo text("Mácula " . $ODMACULA . ", "); ?></span>
<?php }
if ($ODVESSELS) {
    ?>
    <span><?php echo text("Vasos " . $ODVESSELS . ", "); ?></span>
<?php }
if ($ODPERIPH) {
    ?>
    <span><?php echo text("Periferia " . $ODPERIPH . ", "); ?></span>
<?php }
if ($ODVITREOUS) {
    ?>
    <span><?php echo text("Vítreo " . $ODVITREOUS . ", "); ?></span>
<?php }

//Retina Ojo Izquierdo
if ($OSDISC || $OSCUP || $OSMACULA || $OSVESSELS || $OSPERIPH || $OSVITREOUS) {
    ?>
    <br><span><b>OI: </b></span>
<?php }
if ($OSDISC) {
    ?>
    <span><?php echo text("Disco " . $OSDISC . ", "); ?></span>
<?php }
if ($OSCUP) {
    ?>
    <span><?php echo text("Copa " . $OSCUP . ", "); ?></span>
<?php }
if ($OSMACULA) {
    ?>
    <span><?php echo text("Mácula " . $OSMACULA . ", "); ?></span>
<?php }
if ($OSVESSELS) {
    ?>
    <span><?php echo text("Vasos " . $OSVESSELS . ", "); ?></span>
<?php }
if ($OSPERIPH) {
    ?>
    <span><?php echo text("Periferia " . $OSPERIPH . ", "); ?></span>
<?php }
if ($OSVITREOUS) {
    ?>
    <span><?php echo text("Vítreo " . $OSVITREOUS . ", "); ?></span>
<?php }
?>
.</P>
<P class="texto"><B>IMPRESI&Oacute;N DIAGNOSTICA:</B>
    <?php
    /**
     *  Retrieve and Display the IMPPLAN_items for the Impression/Plan zone.
     */
    $query = "select * from form_" . $form_folder . "_impplan where form_id=? and pid=? order by IMPPLAN_order ASC";
    $result = sqlStatement($query, array($form_id, $pid));
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
            echo $item['code'] . " " . $item['codedesc'] . ". ";
        }

    }

    $query = "SELECT * FROM form_eye_mag_orders where form_id=? and pid=? ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) { ?>
</p>
<p class="texto"><b>RECOMENDACIÓN: </b>
    <?php
    while ($plan_row = sqlFetchArray($PLAN_results)) {
        echo $plan_row['ORDER_DETAILS'] . ", ";
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
