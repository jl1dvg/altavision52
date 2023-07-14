<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/iess.inc.php");
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
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, sex, pricelevel, providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

$providerID = getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);
$dated = fetchDateByEncounter($encounter);
$visit_date = oeFormatShortDate($dated);
$resultado = getProtocolDate($_GET['formid'], $_GET['visitid']);

if ($resultado) {
    $dateddia = $resultado['dia'];
    $datedmes = $resultado['mes'];
    $datedano = $resultado['ano'];

    // Realizar cualquier otra acción con los componentes de la fecha
} else {
    // La fecha del protocolo no se encontró, manejar este caso según corresponda
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
    'default_font_size' => '10',
    'default_font' => 'Arial',
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
    <style>
        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        td.morado {
            text-align: left;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 9pt;
            font-weight: bold;
            height: 23px;
        }

        td.verde {
            height: 23px;
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.verde_left {
            height: 23px;
            text-align: left;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.blanco {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 21px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }

        td.blanco_left {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 21px;
            text-align: left;
            vertical-align: middle;
            font-size: 7pt;
        }
    </style>
</HEAD>
<BODY>
<TABLE>
    <tr>
        <td colspan="71" class="morado">A. DATOS DEL ESTABLECIMIENTO
            Y USUARIO / PACIENTE
        </td>
    </tr>
    <tr>
        <td colspan="15" height="27" class="verde">INSTITUCIÓN DEL SISTEMA</td>
        <td colspan="6" class="verde">UNICÓDIGO</td>
        <td colspan="18" class="verde">ESTABLECIMIENTO DE SALUD</td>
        <td colspan="18" class="verde">NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
        <td colspan="14" class="verde" style="border-right: none">NÚMERO DE ARCHIVO</td>
    </tr>
    <tr>
        <td colspan="15" height="27" class="blanco"><?php echo $titleres['pricelevel']; ?></td>
        <td colspan="6" class="blanco">&nbsp;</td>
        <td colspan="18" class="blanco">ALTA VISION</td>
        <td colspan="18" class="blanco"><?php echo $titleres['pubpid']; ?></td>
        <td colspan="14" class="blanco" style="border-right: none"><?php echo $titleres['pubpid']; ?></td>
    </tr>
    <tr>
        <td colspan="15" rowspan="2" height="41" class="verde" style="height:31.0pt;">PRIMER APELLIDO</td>
        <td colspan="13" rowspan="2" class="verde">SEGUNDO APELLIDO</td>
        <td colspan="13" rowspan="2" class="verde">PRIMER NOMBRE</td>
        <td colspan="10" rowspan="2" class="verde">SEGUNDO NOMBRE</td>
        <td colspan="3" rowspan="2" class="verde">SEXO</td>
        <td colspan="6" rowspan="2" class="verde">FECHA NACIMIENTO</td>
        <td colspan="3" rowspan="2" class="verde">EDAD</td>
        <td colspan="8" class="verde" style="border-right: none; border-bottom: none">CONDICIÓN EDAD <font
                class="font7">(MARCAR)</font></td>
    </tr>
    <tr>
        <td colspan="2" height="17" class="verde">H</td>
        <td colspan="2" class="verde">D</td>
        <td colspan="2" class="verde">M</td>
        <td colspan="2" class="verde" style="border-right: none">A</td>
    </tr>
    <tr>
        <td colspan="15" height="27" class="blanco"><?php echo $titleres['lname']; ?></td>
        <td colspan="13" class="blanco"><?php echo $titleres['lname2']; ?></td>
        <td colspan="13" class="blanco"><?php echo $titleres['fname']; ?></td>
        <td colspan="10" class="blanco"><?php echo $titleres['mname']; ?></td>
        <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?>
        </td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">X</td>
    </tr>
</TABLE>
<table>
    <tr>
        <td colspan="10" class="morado">B. DIAGNÓSTICOS</td>
        <td colspan="2" class="morado" style="text-align: center">CIE</td>
    </tr>
    <tr>
        <td colspan="2" width="18%" rowspan="3" class="verde_left">Pre Operatorio:</td>
        <td class="verde_left" width="2%">1.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($formid, "Prot_dxpre")); ?>
        </td>
        <td class="blanco" width="20%" colspan="2"><?php echo substr(getFieldValue($formid, "Prot_dxpre"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left" width="2%">2.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($formid, "Prot_dxpre2")); ?>
        </td>
        <td class="blanco" width="20%" colspan="2"><?php echo substr(getFieldValue($formid, "Prot_dxpre2"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left" width="2%">3.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($formid, "Prot_dxpre3")); ?>
        </td>
        <td class="blanco" width="20%" colspan="2"><?php echo substr(getFieldValue($formid, "Prot_dxpre3"), 6); ?></td>
    </tr>
    <tr>
        <td colspan="2" rowspan="3" class="verde_left">Post Operatorio:</td>
        <td class="verde_left">1.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($formid, "Prot_dxpost")); ?></td>
        <td class="blanco" colspan="2"><?php echo substr(getFieldValue($formid, "Prot_dxpost"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left">2.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($formid, "Prot_dxpost2")); ?></td>
        <td class="blanco" colspan="2"><?php echo substr(getFieldValue($formid, "Prot_dxpost2"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left">3.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($formid, "Prot_dxpost3")); ?></td>
        <td class="blanco" colspan="2"><?php echo substr(getFieldValue($formid, "Prot_dxpost3"), 6); ?></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="11" class="morado">C. PROCEDIMIENTO</td>
        <td colspan="2" class="verde_left" style="text-align: center">Electiva</td>
        <td colspan="1" class="blanco" style="text-align: center">X</td>
        <td colspan="2" class="verde_left" style="text-align: center">Emergencia</td>
        <td colspan="1" class="blanco" style="text-align: center"></td>
        <td colspan="2" class="verde_left" style="text-align: center">Urgencia</td>
        <td colspan="1" class="blanco" style="text-align: center"></td>
    </tr>
    <tr>
        <td colspan="2" class="verde_left">Proyectado:</td>
        <td class="blanco_left"
            colspan="18"><?php echo obtenerIntervencionesPropuestas(getFieldValue($formid, "Prot_opp")) . " ";
            $ojoValue = getFieldValue($formid, "Prot_ojo");

            if ($ojoValue == 'OI') {
                echo "Ojo izquierdo";
            } elseif ($ojoValue == 'OD') {
                echo "Ojo derecho";
            } elseif ($ojoValue == 'AO') {
                echo "Ambos ojos";
            } else {
                echo "Valor no válido";
            } ?></td>
    </tr>
    <tr>
        <td colspan="2" class="verde_left">Realizado:</td>
        <td class="blanco_left"
            colspan="18"><?php echo obtenerIntervencionesPropuestas(getFieldValue($formid, "Prot_opr")) . " ";
            if ($ojoValue == 'OI') {
                echo "Ojo izquierdo";
            } elseif ($ojoValue == 'OD') {
                echo "Ojo derecho";
            } elseif ($ojoValue == 'AO') {
                echo "Ambos ojos";
            } else {
                echo "Valor no válido";
            }
            ?></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado" colspan="20">D. INTEGRANTES DEL EQUIPO QUIRÚRGICO</td>
    </tr>
    <tr>
        <td class="verde_left" colspan="2">Cirujano 1:</td>
        <td class="blanco" colspan="8"><?php echo $providerNAME; ?></td>
        <td class="verde_left" colspan="2">Instrumentista:</td>
        <td class="blanco" colspan="8"><?php
            $instrumentistaOK = getFieldValue($formid, "Prot_Instrumentistas");
            if ($instrumentistaOK == 'Si') {
                echo "Dr. Jorge Luis de Vera";
            } ?></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="2">Cirujano 2:</td>
        <td class="blanco" colspan="8"><?php echo getProviderName(getFieldValue($formid, "Prot_Cirujano")); ?></td>
        <td class="verde_left" colspan="2">Circulante:</td>
        <td class="blanco" colspan="8">Lcda. Solange Vega</td>
    </tr>
    <tr>
        <td class="verde_left" colspan="3">Primer Ayudante:</td>
        <td class="blanco" colspan="7"><?php echo getProviderName(getFieldValue($formid, "Prot_ayudante")); ?></td>
        <td class="verde_left" colspan="3">Anestesiologo/a:</td>
        <td class="blanco" colspan="7"><?php echo getProviderName(getFieldValue($formid, "Prot_anestesiologo")); ?></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="3">Segundo Ayudante:</td>
        <td class="blanco" colspan="7"></td>
        <td class="verde_left" colspan="3">Ayudante Anestesia:</td>
        <td class="blanco" colspan="7"></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="3">Tercer Ayudante:</td>
        <td class="blanco" colspan="7"></td>
        <td class="verde_left" colspan="1">Otros:</td>
        <td class="blanco" colspan="9"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="70" class="morado">F. TIEMPOS QUIRÚRGICOS</td>
    </tr>
    <tr>
        <td colspan="19" rowspan="2" class="verde">FECHA DE OPERACIÓN</td>
        <td colspan="5" class="verde">DIA</td>
        <td colspan="5" class="verde">MES</td>
        <td colspan="5" class="verde">AÑO</td>
        <td colspan="18" class="verde">HORA DE INICIO</td>
        <td colspan="18" class="verde">HORA DE TERMINACIÓN</td>
    </tr>
    <tr>
        <td colspan="5" class="blanco"><?php echo $dateddia; ?></td>
        <td colspan="5" class="blanco"><?php echo $datedmes; ?></td>
        <td colspan="5" class="blanco"><?php echo $datedano; ?></td>
        <td colspan="18" class="blanco"><?php echo getFieldValue($formid, "Prot_hini"); ?></td>
        <td colspan="18" class="blanco"><?php echo getFieldValue($formid, "Prot_hfin"); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Dieresis:</td>
        <td colspan="55"
            class="blanco_left"><?php echo obtenerDieresis(getFieldValue($formid, "Prot_dieresis")); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Exposición y Exploración:</td>
        <td colspan="55" class="blanco_left"><?php echo obtenerExposicion(getFieldValue($formid, "Prot_expo")); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Hallazgos Quirúrgicos:</td>
        <td colspan="55" class="blanco_left"><?php echo getFieldValue($formid, "Prot_halla"); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Procedimiento Quirúrgicos:</td>
        <td colspan="55"
            class="blanco_left"><?php echo html_entity_decode(html_entity_decode(getFieldValue($formid, "Prot_proced"))); ?></td>
    </tr>
</table>
<table style="border: none">
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                               COLOR="#000000">SNS-MSP/HCU-form. 017/2021</FONT></B>
        </TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">PROTOCOLO QUIRÚRGICO (1)</FONT></B>
        </TD>
    </TR>
    ]
</TABLE>


<pagebreak>
    <table>
        <tr>
            <td colspan="15" class="verde_left">Procedimiento Quirúrgicos:</td>
            <td colspan="55" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="70" class="morado">G. COMPLICACIONES DEL PROCEDIMIENTO QUIRÚRGICO</td>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        </tr>
        <tr>
            <td colspan="10" class="verde">Pérdida Sanguínea total:</td>
            <td colspan="10" class="blanco"></td>
            <td colspan="5" class="blanco">ml</td>
            <td colspan="10" class="verde">Sangrado aproximado:</td>
            <td colspan="10" class="blanco"></td>
            <td colspan="5" class="blanco">ml</td>
            <td colspan="10" class="verde">Uso de Material Protésico:</td>
            <td colspan="3" class="blanco">SI</td>
            <td colspan="2" class="blanco"></td>
            <td colspan="3" class="blanco">NO</td>
            <td colspan="2" class="blanco"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="70" class="morado">H. EXÁMENES HISTOPATOLÓGICOS</td>
        </tr>
        <tr>
            <td colspan="10" class="verde">Transquirúrgico:</td>
            <td colspan="60" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="10" class="verde">Biopsia por congelación:</td>
            <td colspan="3" class="blanco">SI</td>
            <td colspan="2" class="blanco"></td>
            <td colspan="3" class="blanco">NO</td>
            <td colspan="2" class="blanco">X</td>
            <td colspan="10" class="verde">Resultado:</td>
            <td colspan="40" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="13" class="blanco_left"></td>
            <td colspan="57" class="blanco_left">Patólogo que reporta:</td>
        </tr>
        <tr>
            <td colspan="10" class="verde">Histopatológico:</td>
            <td colspan="3" class="blanco">SI</td>
            <td colspan="2" class="blanco"></td>
            <td colspan="3" class="blanco">NO</td>
            <td colspan="2" class="blanco">X</td>
            <td colspan="10" class="verde">Muestra:</td>
            <td colspan="40" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado">I. DIAGRAMA DEL PROCEDIMIENTO</td>
        </tr>
        <tr>
            <td class="blanco" height="100px">
                <?php
                echo getImageHTML(getFieldValue($formid, "Prot_opr"));
                ?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="20">J. DATOS DEL PROFESIONAL RESPONSABLE</td>
        </tr>
        <tr>
            <td class="verde" style="width: 100" colspan="5">NOMBRE Y APELLIDOS</td>
            <td class="verde" style="width: 100" colspan="5">ESPECIALIDAD</td>
            <td class="verde" style="width: 100" colspan="5">FIRMA</td>
            <td class="verde" style="width: 100" colspan="5">SELLO Y NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
        </tr>
        <tr>
            <td class="blanco" style="height: 40" colspan="5"><?php echo $providerNAME; ?></td>
            <td class="blanco" colspan="5"><?php echo getProviderEspecialidad($providerID) ?></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"><?php echo getProviderRegistro($providerID) ?></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 40"
                colspan="5"><?php echo getProviderName(getFieldValue($formid, "Prot_anestesiologo")); ?></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderEspecialidad(getFieldValue($formid, "Prot_anestesiologo")) ?></td>
            <td class="blanco"
                colspan="5"></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderRegistro(getFieldValue($formid, "Prot_anestesiologo")) ?></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 40" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                                   COLOR="#000000">SNS-MSP/HCU-form. 017/2021</FONT></B>
            </TD>
            <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">PROTOCOLO QUIRÚRGICO (2)</FONT></B>
            </TD>
        </TR>
        ]
    </TABLE>
</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('protocolo_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
