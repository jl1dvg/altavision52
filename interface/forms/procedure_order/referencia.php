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

$form_folder = "eye_mag";

$facilityService = new FacilityService();

require_once("../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, pricelevel, phone_home, phone_cell, phone_contact,DOB, sex,providerID,DATE_FORMAT(DOB,'%m') as DOB_M,DATE_FORMAT(DOB,'%d') as DOB_D,DATE_FORMAT(DOB,'%Y') as DOB_A, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_TS");

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

$encounter_data = sqlQuery($query, array($_GET['visitid'], $_GET['patientid']));
@extract($encounter_data);

//Inicio SQL Autorizaion
$queryProced = "select * from procedure_order_code AS PO
        left join procedure_order as POC on (PO.procedure_order_id = POC.procedure_order_id)
        where
        patient_id=? and
        encounter_id=? and
        POC.procedure_order_id = ? and
        activity = 1
        ORDER BY ojo";

$order_data = sqlStatement($queryProced, array($pid, $encounter, $_GET['formid']));
$order_data2 = sqlQuery($queryProced, array($pid, $encounter, $_GET['formid']));
$order_dataF = sqlStatement($queryProced, array($pid, $encounter, $_GET['formid']));
$order_fecha = sqlFetchArray($order_dataF);
$providerID = getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);


$CarePlanSQL = sqlQuery("SELECT * FROM procedure_order_code AS PO
  LEFT JOIN procedure_order AS POC ON (PO.procedure_order_id = POC.procedure_order_id) WHERE " .
    "patient_id = ? AND encounter_id = ? ", array($pid, $encounter));
$NombreProcedimiento = $CarePlanSQL['procedure_name'];
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
<HTML>
<HEAD>
    <link rel="stylesheet" type="text/css" href="referencia.css">
</HEAD>
<BODY TEXT="#000000">
<div>FORMULARIO DE REFERENCIA, DERIVACION CONTRAREFERENCIA Y REFERENCIA</div>
<table>
    <TR>
        <TD class="morado" colspan="12">l. DATOS DEL USARIO</TD>
    </TR>
    <TR>
        <TD class="verde" COLSPAN=2 rowspan="2">APELLIDO PATERNO</TD>
        <TD class="verde" COLSPAN=2 rowspan="2">APELLIDO MATERNO</TD>
        <TD class="verde" COLSPAN=3 rowspan="2">NOMBRES</TD>
        <TD class="verde" COLSPAN=3>Fecha de Nacimiento</TD>
        <TD class="verde" rowspan="2">EDAD</TD>
        <TD class="verde">SEXO</TD>
    </TR>
    <TR>
        <TD class="verde">Dia</TD>
        <TD class="verde">Mes</TD>
        <TD class="verde">A&ntilde;o</TD>
        <TD class="verde">H/M</TD>
    </TR>
    <TR>
        <TD class="blanco" colspan="2"><?php echo($titleres['lname']) ?></TD>
        <TD class="blanco" COLSPAN=2><?php echo($titleres['lname2']) ?></TD>
        <TD class="blanco" colspan="3"><?php echo ($titleres['fname']) . " " . ($titleres['mname']) ?>
        </TD>
        <TD class="blanco"><?php echo($titleres['DOB_D']) ?>
        </TD>
        <TD class="blanco"><?php echo($titleres['DOB_M']) ?>
        </TD>
        <TD class="blanco"><?php echo($titleres['DOB_A']) ?>
        </TD>
        <TD class="blanco">
            <?php
            echo getPatientAgeFromDate($titleres['DOB'], date("Y-m-d", strtotime($order_fecha['date_collected'])));
            ?>
        </TD>
        <TD class="blanco">
            <?php if ($titleres['sex'] == "Male") {
                echo "H";
            } else {
                echo "M";
            } ?></TD>
    </TR>
    <TR>
        <TD class="verde" colspan="2" rowspan="2">NACIONALIDAD</TD>
        <TD class="verde" COLSPAN=2 rowspan="2">PAIS</TD>
        <TD class="verde" COLSPAN=2 rowspan="2">CEDULA O PASAPORTE</TD>
        <TD class="verde" COLSPAN=3>LUGAR DE RESIDENCIA</TD>
        <TD class="verde" COLSPAN=3 rowspan="2">DIRECCION DE DOMICILIO</TD>
    </TR>
    <TR>
        <TD class="verde">Prov.</TD>
        <TD class="verde">Canton</TD>
        <TD class="verde">Parroq.</TD>
    </TR>
    <TR>
        <TD class="blanco" colspan="2">ECUATORIANA</TD>
        <TD class="blanco" COLSPAN=2>ECUADOR</TD>
        <TD class="blanco" COLSPAN=2><?php echo($titleres['pubpid']) ?></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
    </TR>
    <TR>
        <TD class="verde">E-MAIL:</TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
        <TD class="verde">TELEFONO:</TD>
        <TD class="blanco" COLSPAN=3><?php echo($titleres['phone_cell']); ?></TD>
        <TD class="verde">FECHA:</TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
    </TR>
</table>
<table>
    <TR>
        <TD class="verde" width="40%">ll. REFERENCIA 1</TD>
        <TD class="blanco" width="10%"><BR></TD>
        <TD class="verde" width="40%"> DERIVACION 2</TD>
        <TD class="blanco" width="10%">X</TD>
    </TR>
</table>
<table>
    <TR>
        <TD COLSPAN=12 CLASS="morado">1 DATOS INSTITUCIONALES</TD>
    </TR>
    <TR>
        <TD class="verde" colspan="2">ENTIDAD DEL SISTEMA</TD>
        <TD class="verde" colspan="2">HISTORIA CLINICA</TD>
        <TD class="verde" COLSPAN=3>ESTABLECIMIENTO DE SALUD</TD>
        <TD class="verde" COLSPAN=2>TIPO</TD>
        <TD class="verde" COLSPAN=3>DISTRITO /AREA</TD>
    </TR>
    <TR>
        <TD class="blanco" COLSPAN=2><?php echo $titleres['pricelevel']; ?></TD>
        <TD class="blanco" COLSPAN=2><?php echo $titleres['pubpid']; ?></TD>
        <TD class="blanco" COLSPAN=3>ALTAVISION</TD>
        <TD class="blanco" COLSPAN=2><BR></TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
    </TR>
    <TR>
        <TD class="verde" COLSPAN=8>REFIERE O DERIVA A:</TD>
        <TD class="verde" COLSPAN=4>FECHA</TD>
    </TR>
    <TR>
        <TD class="verde" COLSPAN=2>Entidad del Sistema</TD>
        <TD class="verde" COLSPAN=2>Establecimiento de Salud</TD>
        <TD class="verde" colspan="2">Servico</TD>
        <TD class="verde" COLSPAN=2>Especialidad</TD>
        <TD class="verde">Dia</TD>
        <TD class="verde">Mes</TD>
        <TD class="verde" colspan="2">A&ntilde;o</TD>
    </tr>
    <TR>
        <TD class="blanco" COLSPAN=2>IESS</TD>
        <TD class="blanco" COLSPAN=2>ALTAVISION</TD>
        <TD class="blanco" colspan="2">AMBULATORIO</TD>
        <TD class="blanco" COLSPAN=2><?php echo strtoupper($order_data2['history_order']); ?></TD>
        <TD class="blanco">
            <?php
            echo date("d", strtotime($order_fecha['date_collected']));
            ?></TD>
        <TD class="blanco">
            <?php echo date("m", strtotime($order_fecha['date_collected']));
            ?>
        </TD>
        <TD class="blanco" colspan="2">
            <?php echo date("Y", strtotime($order_fecha['date_collected']));
            ?>
        </TD>
    </TR>
</table>
<table>
    <TR>
        <TD COLSPAN=12 class="morado">2. MOTIVO DE LA REFERENCIA O DERIVACION</TD>
    </TR>
    <TR>
        <TD class="blanco" COLSPAN=4 width="40%">LIMITADA CAPACIDAD RESOLUTIVA</TD>
        <TD class="blanco" width="5%">1</TD>
        <TD class="blanco" width="5%"><BR></TD>
        <TD class="blanco" COLSPAN=4 width="40%">SATURACION DE CAPACIDAD INSTALADA</TD>
        <TD class="blanco" width="5%">4</TD>
        <TD class="blanco" width="5%"><BR></TD>
    </TR>
    <TR>
        <TD class="blanco" COLSPAN=4>AUSENCIA DEL PROFESIONAL</TD>
        <TD class="blanco">2</TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco" COLSPAN=4>CONTINUAR TRATAMIENTO</TD>
        <TD class="blanco">5</TD>
        <TD class="blanco"><BR></TD>
    </TR>
    <tr>
        <TD class="blanco" COLSPAN=4>FALTA DEL PROFESIONAL</TD>
        <TD class="blanco">3</TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco" COLSPAN=4>OTROS ESPECIFIQUE</TD>
        <TD class="blanco"><br></TD>
        <TD class="blanco"><BR></TD>
    </tr>
</table>
<table>
    <TR>
        <TD class="morado">3. RESUMEN DEL CUADRO CLINICO</TD>
    </TR>
    <tr>
        <td class='blanco_left'>
            <?php
            $reasonChunks = $reason . ". " . $CC1;
            $wrappedChunk = wordwrap($reasonChunks, 165, "</td></tr><tr><td class='blanco_left'>");
            echo $wrappedChunk;
            ?>
        </td>
    </tr>
</table>
<table>
    <TR>
        <TD class="morado">4. HALLAZGOS RELEVANTES DE EXAMENES Y PROCEDIMIENTOS DIAGNOSTICOS</TD>
    </TR>
    <TR>
        <TD class="blanco_left">
            <?php

            function ExamOftal($SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                               $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS)
            {
                if ($SCODVA || $SCOSVA || $ODIOPAP || $OSIOPAP || $RBROW || $LBROW || $RUL || $LUL || $RLL || $LLL || $RMCT || $LMCT || $RADNEXA || $LADNEXA || $EXT_COMMENTS || $OSCONJ || $ODCONJ || $ODCORNEA || $OSCORNEA || $ODAC || $OSAC || $ODLENS || $OSLENS || $ODIRIS || $OSIRIS || $ODDISC || $OSDISC || $ODCUP || $OSCUP ||
                    $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
                    if ($SCODVA) {
                        $ExamOFT = $ExamOFT . ("Ojo Derecho: " . $SCODVA . ", ");
                    }
                    if ($SCOSVA) {
                        $ExamOFT = $ExamOFT . ("Ojo Izquierdo: " . $SCOSVA . ", ");
                    }
                    if ($ODIOPAP) {
                        $ExamOFT = $ExamOFT . ("Ojo Derecho: " . $ODIOPAP . ", ");
                    }
                    if ($OSIOPAP) {
                        $ExamOFT = $ExamOFT . ("Ojo Izquierdo: " . $OSIOPAP . ", ");
                    }
                    $ExamOFT = $ExamOFT . "Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa: ";
                    if ($RBROW || $LBROW || $RUL || $LUL || $RLL || $LLL || $RMCT || $LMCT || $RADNEXA || $LADNEXA || $EXT_COMMENTS) {
                        $ExamOFT = $ExamOFT . "Examen Externo:";
                    }
                    if ($RBROW || $RUL || $RLL || $RMCT || $RADNEXA) {
                        $ExamOFT = $ExamOFT . ("Ojo Derecho: " . $RBROW . " " . $RUL . " " . $RLL . " " . $RMCT . " " . $RADNEXA . " ");
                    }
                    if ($LBROW || $LUL || $LLL || $LMCT || $LADNEXA) {
                        $ExamOFT = $ExamOFT . ("Ojo Izquierdo: " . $LBROW . " " . $LUL . " " . $LLL . " " . $LMCT . " " . $LADNEXA . " ");
                    }
                    if ($EXT_COMMENTS) {
                        $ExamOFT = $ExamOFT . $EXT_COMMENTS;
                    }
                    if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS) {
                        $ExamOFT = $ExamOFT . "Ojo Derecho: ";
                    }
                    if ($ODCONJ) {
                        $ExamOFT = $ExamOFT . ("Conjuntiva " . $ODCONJ . ", ");
                    }
                    if ($ODCORNEA) {
                        $ExamOFT = $ExamOFT . ("Córnea " . $ODCORNEA . ", ");
                    }
                    if ($ODAC) {
                        $ExamOFT = $ExamOFT . ("Cámara Anterior " . $ODAC . ", ");
                    }
                    if ($ODLENS) {
                        $ExamOFT = $ExamOFT . ("Cristalino " . $ODLENS . ", ");
                    }
                    if ($ODIRIS) {
                        $ExamOFT = $ExamOFT . ("Iris " . $ODIRIS . ", ");
                    }
                    if ($OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
                        $ExamOFT = $ExamOFT . "Ojo Izquierdo: ";
                    }
                    if ($OSCONJ) {
                        $ExamOFT = $ExamOFT . ("Conjuntiva " . $OSCONJ . ", ");
                    }
                    if ($OSCORNEA) {
                        $ExamOFT = $ExamOFT . ("Córnea " . $OSCORNEA . ", ");
                    }
                    if ($OSAC) {
                        $ExamOFT = $ExamOFT . ("Cámara Anterior " . $OSAC . ", ");
                    }
                    if ($OSLENS) {
                        $ExamOFT = $ExamOFT . ("Cristalino " . $OSLENS . ", ");
                    }
                    if ($OSIRIS) {
                        $ExamOFT = $ExamOFT . ("Iris " . $OSIRIS . ", ");
                    }
                    if ($ODDISC || $OSDISC || $ODCUP || $OSCUP || $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
                        $ExamOFT = $ExamOFT . "Al fondo de ojo: ";
                    }
                    //Retina Ojo Derecho
                    if ($ODDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
                        $ExamOFT = $ExamOFT . "Ojo Derecho: ";
                    }
                    if ($ODDISC) {
                        $ExamOFT = $ExamOFT . ("Disco " . $ODDISC . ", ");
                    }
                    if ($ODCUP) {
                        $ExamOFT = $ExamOFT . ("Copa " . $ODCUP . ", ");
                    }
                    if ($ODMACULA) {
                        $ExamOFT = $ExamOFT . ("Mácula " . $ODMACULA . ", ");
                    }
                    if ($ODVESSELS) {
                        $ExamOFT = $ExamOFT . ("Vasos " . $ODVESSELS . ", ");
                    }
                    if ($ODPERIPH) {
                        $ExamOFT = $ExamOFT . ("Periferia " . $ODPERIPH . ", ");
                    }
                    if ($ODVITREOUS) {
                        $ExamOFT = $ExamOFT . ("Vítreo " . $ODVITREOUS . ", ");
                    }
                    //Retina Ojo Izquierdo
                    if ($OSDISC || $OSCUP || $OSMACULA || $OSVESSELS || $OSPERIPH || $OSVITREOUS) {
                        $ExamOFT = $ExamOFT . "Ojo Izquierdo: ";
                    }
                    if ($OSDISC) {
                        $ExamOFT = $ExamOFT . ("Disco " . $OSDISC . ", ");
                    }
                    if ($OSCUP) {
                        $ExamOFT = $ExamOFT . ("Copa " . $OSCUP . ", ");
                    }
                    if ($OSMACULA) {
                        $ExamOFT = $ExamOFT . ("Mácula " . $OSMACULA . ", ");
                    }
                    if ($OSVESSELS) {
                        $ExamOFT = $ExamOFT . ("Vasos " . $OSVESSELS . ", ");
                    }
                    if ($OSPERIPH) {
                        $ExamOFT = $ExamOFT . ("Periferia " . $OSPERIPH . ", ");
                    }
                    if ($OSVITREOUS) {
                        $ExamOFT = $ExamOFT . ("Vítreo " . $OSVITREOUS . ", ");
                    }
                    return $ExamOFT;
                }


            }

            function SegAntOD($SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                              $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS)
            {
                if ($OSCONJ || $ODCONJ || $ODCORNEA || $OSCORNEA || $ODAC || $OSAC || $ODLENS || $OSLENS || $ODIRIS || $OSIRIS || $ODDISC || $OSDISC || $ODCUP || $OSCUP ||
                    $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
                    $SegAntOD = "Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa:";
                    if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS) {
                        $SegAntOD = $SegAntOD . "Ojo Derecho: ";
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

            echo wordwrap(ExamOftal($SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS), 165, "</TD></TR><TR><TD class='blanco_left'>");
            ?>

        </TD>
    </TR>
</table>
<table>
    <TR>
        <TD class="morado" width="55%">5. DIAGNOSTICO</TD>
        <TD class="morado" width="15%">CIE- 10</TD>
        <TD class="morado" width="15%">PRE</TD>
        <TD class="morado" width="15%">DEF</TD>
    </TR>
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
            echo "<TR>
                              <TD CLASS='blanco_left'>" . $item['codedesc'] . ".</TD>
                              <TD CLASS='blanco'>" . $item['code'] . "</TD>
                              <TD CLASS='blanco'><BR></TD>
                              <TD CLASS='blanco'>X</TD>
                              </TR>";
        }

    }
    ?>
</table>
<table>
    <TR>
        <TD class="morado" width="80%">6. EXAMENES / PROCEDIMIENTOS SOLICITADOS</TD>
        <TD class="morado" width="20%">CODIGO TARIFARIO</TD>
    </TR>

    <?php
    $i = '0';
    while ($order_list = sqlFetchArray($order_data)) {
        $newORDERdata = array(
            'procedure_name' => $order_list['procedure_name'],
            'procedure_code' => $order_list['procedure_code'],
            'ojo' => $order_list['ojo'],
            'dosis' => $order_list['dosis'],
            'clinical_hx' => $order_list['clinical_hx'],
        );
        print "<TR><TD class='blanco_left'>" .
            ($newORDERdata['procedure_name']) . " " . ($newORDERdata['ojo']) . " ";
        if ($newORDERdata['dosis']) {
            print ($newORDERdata['dosis']) . " dosis";
        }
        if ($newORDERdata['clinical_hx']) {
            print ($newORDERdata['clinical_hx']);
        }
        print "</TD><TD class='blanco'>" . ($newORDERdata['procedure_code']) .
            "</TD></TR>";
        $ORDER_items[$i] = $newORDERdata;
        $i++;
    }


    ?>
</table>
<table>
    <TR>
        <TD class="blanco" width="28%"><?php echo getProviderName($providerID); ?></TD>
        <TD class="blanco" width="16%"><?php echo getProviderRegistro($providerID); ?></TD>
        <TD class="blanco" width="28%"></TD>
        <TD class="blanco" width="28%"></TD>
    </TR>
    <TR>
        <TD class="verde">NOMBRE</TD>
        <TD class="verde">COD. MSP. PROF.</TD>
        <TD class="verde">DIRECTOR MEDICO</TD>
        <TD class="verde">MEDICO VERIFICADOR</TD>
    </TR>
</table>
<TABLE>
    <TR>
        <TD COLSPAN=12 class="morado">1. DATOS INSTITUCIONALES</TD>
    </TR>
    <TR>
        <TD class="verde" colspan="2">ENTIDAD DEL SISTEMA</TD>
        <TD class="verde" COLSPAN=2>HIST, CLINICA #</TD>
        <TD class="verde" COLSPAN=2>ESTABLECIMIENTO</TD>
        <TD class="verde">TIPO</TD>
        <TD class="verde" COLSPAN=2>SERVICIO</TD>
        <TD class="verde" COLSPAN=3>ESPECIALIDAD</TD>
    </TR>
    <TR>
        <TD class="blanco" COLSPAN=2><BR></TD>
        <TD class="blanco" COLSPAN=2><BR></TD>
        <TD class="blanco" COLSPAN=2><BR></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco" COLSPAN=2><BR></TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
    </TR>
    <TR>
        <TD class="verde" colspan="4">lll. CONTRAREFERENCIA 3</TD>
        <TD class="blanco"><BR></TD>
        <TD class="verde" colspan="3">REFERENCIA INVERSA 4</TD>
        <TD class="blanco"><BR></TD>
        <TD class="verde" COLSPAN=3>FECHA
        </TD>
    </TR>
    <TR>
        <TD class="blanco" colspan="2"><BR></TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco" COLSPAN=3><BR></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco"><BR></TD>
        <TD class="blanco"><BR></TD>
    </TR>
    <TR>
        <TD class="verde" COLSPAN=2>Entidad del Sistema</TD>
        <TD class="verde" COLSPAN=3>Establecimiento de Salud</TD>
        <TD class="verde">Tipo</TD>
        <TD class="verde" COLSPAN=3>>Districto/Area</TD>
        <TD class="verde">Dia</TD>
        <TD class="verde">Mes</TD>
        <TD class="verde">A&ntilde;o</TD>
    </TR>
    <TR>
        <TD COLSPAN=12 class="morado">2. RESUMEN DELCUADRO CLINICO</TD>
    </TR>
    <TR>
        <TD colspan="12" class="blanco"><BR></TD>
    </TR>
    <TR>
        <TD colspan="12" class="blanco"><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=12 class="morado">3. HALLAZGOS RELEVANTES DE EXAMENES Y PROCEDIMIENTOS DIAGNOSTICOS</TD>
    </TR>
    <TR>
        <TD colspan="12" class="blanco"><BR></TD>
    </TR>
    <TR>
        <TD colspan="12" class="blanco"><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=12 class="morado">4. TRATAMIENTOS Y PROCEDIMIENTOS TERAPEUTICOS REALIZADOS</TD>
    </TR>
    <TR>
        <TD colspan="12" class="blanco"><BR></TD>
    </TR>
    <TR>
        <TD class="morado" COLSPAN=9>5. DIAGNOSTICO</TD>
        <TD class="morado">CIE-10</TD>
        <TD class="morado">PRE</TD>
        <TD class="morado">DEF</TD>
    </TR>
    <TR>
        <TD class="blanco"
            COLSPAN=8><BR>
        </TD>
        <TD class="blanco"
            COLSPAN=2><BR></TD>
        <TD class="blanco"
        ><BR></TD>
        <TD class="blanco"
        ><BR></TD>
    </TR>
    <TR>
        <TD class="blanco"
            COLSPAN=8><BR>
        </TD>
        <TD class="blanco"
            COLSPAN=2><BR></TD>
        <TD class="blanco"
        ><BR></TD>
        <TD class="blanco"
        ><BR></TD>
    </TR>
    <TR>
        <TD COLSPAN=12 class="morado">6.
            TRATAMIENTO RECOMENDADO A SEGUIR EN EL ESTABLECIMIENTO DE SALUD DE MENOR NIVEL DE COMPLEJIDAD
        </TD>
    </TR>
    <TR>
        <TD class="blanco" COLSPAN=8><BR></TD>
        <TD class="blanco" COLSPAN=4><BR></TD>
    </TR>
</TABLE>
<table>
    <TR>
        <TD class="blanco" COLSPAN=4><br></TD>
        <TD class="blanco" COLSPAN=4></TD>
        <TD class="blanco" COLSPAN=4></TD>
    </TR>
    <TR>
        <TD class="verde" COLSPAN=4>NOMBRE</TD>
        <TD class="verde" COLSPAN=4>COD. MSP. PROF.</TD>
        <TD class="verde" COLSPAN=4>FIRMA</TD>
    </TR>
</TABLE>
</BODY>

</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('plan_egreso_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
