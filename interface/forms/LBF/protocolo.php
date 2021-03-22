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
$titleres = getPatientData($pid, "pubpid,fname,mname,lname,lname2,pricelevel,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

//Fecha del form_eye_mag
$query = "select form_encounter.date as encounter_date,form_eye_mag.id as form_id,form_encounter.*, form_eye_mag.*
        from form_eye_mag ,forms,form_encounter
        where
        form_encounter.encounter =? and
        form_encounter.encounter = forms.encounter and
        form_eye_mag.id=forms.form_id and
        forms.deleted != '1' and
        form_eye_mag.pid=? ";

$encounter_data = sqlQuery($query, array($encounter, $pid));
@extract($encounter_data);
$providerID = getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);
$dated = new DateTime($encounter_date);
$visit_date = oeFormatShortDate($dated);
$mes = date('F', $timestamp);
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$nombreMes = str_replace($meses_EN, $meses_ES, $datedmes);

//Data Protocolo
function lookup_lbf_desc($desc_lbf)
{
    $querylbf = "select field_value from lbf_data WHERE form_id=? AND field_id=? ";
    $lbf_query = sqlStatement($querylbf, array($_GET['formid'], $desc_lbf));
    return $lbf_query;
}

$lbfid = $_GET['formid'];
$querylbfcirujano = sqlQuery("select * from lbf_data WHERE form_id=$lbfid AND field_id='Prot_Cirujano'");
$querylbfayudante = sqlQuery("select * from lbf_data WHERE form_id=$lbfid AND field_id='Prot_ayudante'");
$querylbfproced = sqlQuery("select * from lbf_data WHERE form_id=$lbfid AND field_id='Prot_proced'");
$querylbfopp = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_opp'");
$PROYECTADA = $querylbfopp['field_value'];
$querylbfopr = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_opr'");
$REALIZADA = $querylbfopr['field_value'];
$querylbfdxpre = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dxpre'");
$DX_PRE = $querylbfdxpre['field_value'];
$querylbfdxpre = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dxpre2'");
$DX_PRE2 = $querylbfdxpre['field_value'];
$querylbfdxpre = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dxpre3'");
$DX_PRE3 = $querylbfdxpre['field_value'];
$querylbfdxpost = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dxpost'");
$DX_POS = $querylbfdxpost['field_value'];
$querylbfdxpost = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dxpost2'");
$DX_POS2 = $querylbfdxpost['field_value'];
$querylbfdxpost = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dxpost3'");
$DX_POS3 = $querylbfdxpost['field_value'];
$querylbfdieresis = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_dieresis'");
$DIERESIS = $querylbfdieresis['field_value'];
$querylbfexposicion = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_expo'");
$EXPOSICION = $querylbfexposicion['field_value'];
$querylbfojo = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_ojo'");
$OJO = $querylbfojo['field_value'];
//Fin fecha del form_eye_mag
$fechaPROTOCOLO = sqlQuery("select * from forms WHERE form_id=? AND encounter=?", array($_GET['formid'], $_GET['visitid']));
$sqlENCUENTRO = sqlQuery("select * from form_encounter WHERE encounter=?", array($_GET['visitid']));
$datedprotocolo = new DateTime($fechaPROTOCOLO['date']);
$dateddia = $datedprotocolo->format('d');
$datedmes = $datedprotocolo->format('m');
$datedano = $datedprotocolo->format('Y');


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
    <style>
        td.encabezado {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            height: 16px;
        }

        td.encabezado2 {
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            height: 14px;
        }

        td.contenido {
            text-align: center;
            font-size: 10px;
            height: 16px;
        }

        td.procedimiento {
            font-size: 10px;
        }
    </style>
</HEAD>
<BODY>
<table border="1" width="100%" style="border-collapse: collapse;">
    <tr>
        <td class="encabezado" width=25% colspan="3">APELLIDO PATERNO</td>
        <td class="encabezado" width=25% colspan="3">APELLIDO MATERNO</td>
        <td class="encabezado" width=25% colspan="3">NOMBRES</td>
        <td class="encabezado" width=25% colspan="3">Nº HISTORIA CLÍNICA</td>
    </tr>
    <tr>
        <td class="contenido" colspan="3"><?php echo text($titleres['lname']) ?></td>
        <td class="contenido" colspan="3"><?php echo text($titleres['lname2']) ?></td>
        <td class="contenido" colspan="3"><?php echo text($titleres['fname']) ?></td>
        <td class="contenido" colspan="3"><?php echo text($titleres['pubpid']) ?></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="4">SERVICIO</td>
        <td class="encabezado" colspan="4">SALA</td>
        <td class="encabezado" colspan="4">CAMA No.</td>
    </tr>
    <tr>
        <td class="contenido" colspan="4">CIRUGIA</td>
        <td class="contenido" colspan="4">SALA DE OFTALMOLOGIA</td>
        <td class="contenido" colspan="4"></td>
    </tr>
    <tr>
        <td class="encabezado" width=50% colspan="6">DIAGNOSTICO</td>
        <td class="encabezado" width=50% colspan="6">OPERACION</td>
    </tr>
    <tr>
        <td class="encabezado" colspan="2">PRE-OPERATORIO:</td>
        <td colspan="4"></td>
        <td class="encabezado" colspan="2">POYECTADA:</td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td class="contenido" colspan="6"><?php
            echo lookup_code_short_descriptions($DX_PRE);
            if ($DX_PRE2) {
                echo " + " . lookup_code_short_descriptions($DX_PRE2);
            }
            if ($DX_PRE3) {
                echo " + " . lookup_code_short_descriptions($DX_PRE3);
            }
            ?></td>
        <td class="contenido" colspan="6"><?php
            if ($PROYECTADA && $PROYECTADA != '0') {
                $PROYECTADA_items = explode('|', $PROYECTADA);
                foreach ($PROYECTADA_items as $item) {
                    $QXpropuesta = ($item);
                    $IntervencionPropuesta = sqlquery("SELECT notes FROM `list_options`
                  WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` = '$QXpropuesta' ");
                    echo $IntervencionPropuesta['notes'] . " + ";
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="contenido" colspan="6">
            <?php $queryOjo = sqlQuery("SELECT * FROM `list_options`
              WHERE `list_id` = 'OD' AND `option_id` = ? ", array($OJO));
            echo $queryOjo['title']; ?>
        </td>
        <td class="contenido" colspan="6">
            <?php $queryOjo = sqlQuery("SELECT * FROM `list_options`
              WHERE `list_id` = 'OD' AND `option_id` = ? ", array($OJO));
            echo $queryOjo['title']; ?>
        </td>
    </tr>
    <tr>
        <td class="contenido" width=8.3% style="border-right:0;"></td>
        <td width=8.3% style="border-left:0;border-right:0;"></td>
        <td width=8.3% style="border-left:0;border-right:0;"></td>
        <td width=8.3% style="border-left:0;border-right:0;"></td>
        <td width=8.3% style="border-left:0;border-right:0;"></td>
        <td width=8.3% style="border-left:0;border-right:0;"></td>
        <td class="encabezado2" width=8.3%>ELECTIVA</td>
        <td width=8.3%></td>
        <td class="encabezado2" width=8.3%>EMERGENCIA</td>
        <td width=8.3%></td>
        <td class="encabezado2" width=8.3%>PALEATIVA</td>
        <td width=8.3%></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="2">POST-OPERATORIO:</td>
        <td colspan="4"></td>
        <td class="encabezado" colspan="2">REALIZADA:</td>
        <td class="contenido" colspan="4">
            <?php
            ///////////////IESS////////////////
            function codigos_QXIESS($convenio, $lbfID)
            {
                $return = [];
                $querylbfopr = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfID AND field_id='Prot_opr'");
                $REALIZADA = $querylbfopr['field_value'];
                if ($convenio == 'IESS') {
                    if ($REALIZADA && $REALIZADA != '0') {
                        $REALIZADA_items = explode('|', $REALIZADA);
                        foreach ($REALIZADA_items as $item) {
                            $QXpropuesta = ($item);
                            $IntervencionPropuesta = sqlquery("SELECT codes FROM `list_options`
                    WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` = '$QXpropuesta' ");
                            $return[] = $IntervencionPropuesta['codes'];
                        }
                        $result = str_replace(";", "", $return);
                        return $result;
                    }
                }
            }

            foreach (codigos_QXIESS($titleres['pricelevel'], $lbfid) as $key) {
                $CodeExp = explode('CPT4:', $key);
                foreach ($CodeExp as $val) {
                    $value[] = $val;
                }
            }
            $codeUNI = (array_unique($value));
            foreach ($codeUNI as $CPT4) {
                if ($CPT4 > 0) {
                    echo ($CPT4) . "/";
                }
            }
            ///////////////MSP/////////////////
            function codigos_QXMSP($convenio, $lbfID)
            {
                $return = [];
                $querylbfopr = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfID AND field_id='Prot_opr'");
                $REALIZADA = $querylbfopr['field_value'];
                if ($convenio == 'MSP') {
                    if ($REALIZADA && $REALIZADA != '0') {
                        $REALIZADA_items = explode('|', $REALIZADA);
                        foreach ($REALIZADA_items as $item) {
                            $QXpropuesta = ($item);
                            $IntervencionPropuesta = sqlquery("SELECT codes FROM `list_options`
                    WHERE `list_id` = 'cirugia_propuesta_MSP' AND `option_id` = '$QXpropuesta' ");
                            $return[] = $IntervencionPropuesta['codes'];
                        }
                        $result = str_replace(";", "", $return);
                        return $result;
                    }
                }
            }

            foreach (codigos_QXMSP($titleres['pricelevel'], $lbfid) as $key) {
                $CodeExpMSP = explode('CPT4:', $key);
                foreach ($CodeExpMSP as $valMSP) {
                    $valueMSP[] = $valMSP;
                }
            }
            $codeUNIMSP = (array_unique($valueMSP));
            foreach ($codeUNIMSP as $CPT4MSP) {
                if ($CPT4MSP > 0) {
                    echo ($CPT4MSP) . "/";
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="contenido" colspan="6"><?php
            echo lookup_code_short_descriptions($DX_POS);
            if ($DX_POS2) {
                echo " + " . lookup_code_short_descriptions($DX_POS2);
            }
            if ($DX_POS3) {
                echo " + " . lookup_code_short_descriptions($DX_POS3);
            }
            ?></td>
        <td class="contenido" colspan="6"><?php
            if ($REALIZADA && $REALIZADA != '0') {
                $REALIZADA_items = explode('|', $REALIZADA);
                foreach ($REALIZADA_items as $item) {
                    $QXpropuesta = ($item);
                    $IntervencionPropuesta = sqlquery("SELECT notes FROM `list_options`
                  WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` = '$QXpropuesta' ");
                    echo($IntervencionPropuesta['notes'] . " + ");
                }
            }

            ?></td>
    </tr>
    <tr>
        <td class="contenido" colspan="6">
            <?php $queryOjo = sqlQuery("SELECT * FROM `list_options`
              WHERE `list_id` = 'OD' AND `option_id` = ? ", array($OJO));
            echo $queryOjo['title']; ?>
        </td>
        <td class="contenido" colspan="6">
            <?php $queryOjo = sqlQuery("SELECT * FROM `list_options`
              WHERE `list_id` = 'OD' AND `option_id` = ? ", array($OJO));
            echo $queryOjo['title']; ?>
        </td>
    </tr>
    <tr>
        <td class="encabezado" colspan="12">EQUIPO QUIRURGICO</td>
    </tr>
    <tr>
        <td class="encabezado" colspan="2">CIRUJANO 1:</td>
        <td class="contenido" colspan="4"><?php echo $providerNAME; ?></td>
        <td class="encabezado" colspan="2">INSTRUMENTISTA:</td>
        <td class="contenido" colspan="4"><?php
            $querylbfInstrumentista = sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_Instrumentistas'");
            $instrumentistaOK = $querylbfInstrumentista['field_value'];
            if ($instrumentistaOK == 'Si') {
                echo "Dr. Jorge Luis de Vera";
            } ?></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="2">CIRUJANO 2:</td>
        <td class="contenido" colspan="4"><?php echo getProviderName($querylbfcirujano['field_value']); ?> </td>
        <td class="encabezado" colspan="2">CIRCULANTE:</td>
        <td class="contenido" colspan="4">Kelly Mora</td>
    </tr>
    <tr>
        <td class="encabezado" colspan="2">PRIMER AYUDANTE:</td>
        <td class="contenido" colspan="4"><?php echo getProviderName($querylbfayudante['field_value']); ?></td>
        <td class="encabezado" colspan="2">ANESTESIOLOGO:</td>
        <td class="contenido" colspan="4">Dr. Cesar Sanchez</td>
    </tr>
    <tr>
        <td class="encabezado" colspan="2">SEGUNDO AYUDANTE:</td>
        <td colspan="4"></td>
        <td class="encabezado" colspan="2">AYUDANTE:</td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="3">FECHA DE OPERACION</td>
        <td class="encabezado" colspan="3" rowspan="2">HORA DE INICIO</td>
        <td class="encabezado" colspan="3" rowspan="2">HORA DE TERMINACION</td>
        <td class="encabezado" colspan="3" rowspan="2">TIPO DE ANESTESIA</td>
    </tr>
    <tr>
        <td class="encabezado" colspan="1">DIA</td>
        <td class="encabezado" colspan="1">MES</td>
        <td class="encabezado" colspan="1">AÑO</td>
    </tr>
    <tr>
        <td class="contenido" colspan="1"><?php echo $dateddia; ?></td>
        <td class="contenido" colspan="1"><?php echo $datedmes; ?></td>
        <td class="contenido" colspan="1"><?php echo $datedano; ?></td>
        <td class="contenido"
            colspan="3"><?php echo str_replace("field_value", "", lookup_lbf_desc('Prot_hini')); ?></td>
        <td class="contenido"
            colspan="3"><?php echo str_replace("field_value", "", lookup_lbf_desc('Prot_hfin')); ?></td>
        <td class="contenido" colspan="3">REGIONAL</td>
    </tr>
    <tr>
        <td class="encabezado" colspan="12">DIÉRESIS:</td>
    </tr>
    <tr>
        <td class="contenido" colspan="12"><?php
            if ($DIERESIS && $DIERESIS != '0') {
                $DIERESIS_items = explode('|', $DIERESIS);
                foreach ($DIERESIS_items as $item) {
                    $QXdieresis = ($item);
                    $queryDieresis = sqlquery("SELECT * FROM `list_options`
                  WHERE `option_id` = '$QXdieresis' ");
                    echo($queryDieresis['title'] . ", ");
                }
            }
            ?>
    </tr>
    <tr>
        <td class="contenido" colspan="12"><BR/></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="12">EXPOSICIÓN:</td>
    </tr>
    <tr>
        <td class="contenido" colspan="12"><?php
            if ($EXPOSICION && $EXPOSICION != '0') {
                $EXPOSICION_items = explode('|', $EXPOSICION);
                foreach ($EXPOSICION_items as $item) {
                    $QXexpo = ($item);
                    $queryExpo = sqlquery("SELECT * FROM `list_options`
                  WHERE `option_id` = '$QXexpo' ");
                    echo($queryExpo['title'] . ", ");
                }
            }
            ?></td>
    </tr>
    <tr>
        <td class="contenido" colspan="12"><BR/></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="12">EXPLORACION Y HALLAZGOS QUIRÚRGICOS:</td>
    </tr>
    <tr>
        <td class="contenido"
            colspan="12"><?php echo str_replace("field_value", "", lookup_lbf_desc('Prot_halla')); ?></td>
    </tr>
    <tr>
        <td class="contenido" colspan="12"><BR/></td>
    </tr>
    <tr>
        <td class="encabezado" colspan="12">PROCEDIMIENTO OPERATORIO:</td>
    </tr>
    <tr>
        <td class="procedimiento" colspan="12"><?php
            $procedimientoQX = str_replace("&amp;lt;b&amp;gt;", "<b>", $querylbfproced['field_value']);
            echo str_replace("&amp;lt;/b&amp;gt;", "</b>", $procedimientoQX);;
            ?></td>
    </tr>


</table>
<pagebreak>
    <table border="1" width="100%" style="border-collapse: collapse;">
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="encabezado" colspan="12">SINTESIS</td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="encabezado" colspan="12">COMPLICACIONES DEL ACTO OPERATORIO</td>
        </tr>
        <tr>
            <td class="contenido" colspan="12">NINGUNA</td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="encabezado" colspan="12">EXAMEN HISTOPATOLOGICO</td>
        </tr>
        <tr>
            <td class="contenido" colspan="12">SI NO X</td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="encabezado" colspan="12">HISTOPATOLOGICO DIAGNOSTICO</td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" colspan="12"><BR/></td>
        </tr>
    </table>
    <br/>
    <table border="1" width="100%" style="border-collapse: collapse;">
        <tr>
            <td class="encabezado" colspan="1">DICTADA POR:</td>
            <td class="contenido" colspan="2"><?php echo $providerNAME; ?></td>
        </tr>
        <tr>
            <td class="encabezado" colspan="3">FECHA DEL DICTADO:</td>
        </tr>
        <tr>
            <td class="encabezado" colspan="1">DIA</td>
            <td class="encabezado" colspan="1">MES</td>
            <td class="encabezado" colspan="1">AÑO</td>
        </tr>
        <tr>
            <td class="contenido" colspan="1"><?php echo $dateddia; ?></td>
            <td class="contenido" colspan="1"><?php echo $datedmes; ?></td>
            <td class="contenido" colspan="1"><?php echo $datedano; ?></td>

        </tr>
        <tr>
            <td class="encabezado" colspan="1">ESCRITA POR:</td>
            <td class="contenido" colspan="2"><?php
                echo getResponsableName($sqlENCUENTRO['responsable_id']);
                ?></td>
            <td class="contenido" style="border:0;" colspan="1"></td>
            <td class="contenido" colspan="1" rowspan="2"><span>
</span></td>
        </tr>
        <tr>
            <td class="contenido" style="border:0;" colspan="4"><BR/></td>
        </tr>
        <tr>
            <td class="contenido" style="border:0;" colspan="4"><BR/></td>
            <td class="encabezado" colspan="1" width="40%"><?php echo $providerNAME; ?></td>
        </tr>
    </table>


</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('informe_medico_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
