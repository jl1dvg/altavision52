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

if ($_REQUEST['procedid']) {
    $proced_id = $_REQUEST['procedid'];
}


//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname,lname2,sex,pricelevel, providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

//Fecha del form_eye_mag
$query = "select form_encounter.date as encounter_date,form_eye_mag.id as form_id,form_encounter.*, form_eye_mag.*
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

$fechaINGRESO = sqlQuery($queryform, array($_GET['patientid'], $_GET['visitid']));
$encounter_data = sqlQuery($query, array($encounter, $pid));
@extract($encounter_data);
$providerID = getProviderIdOfEncounter($encounter);
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

//Obtener en que consiste el procedimiento
function getPropositos($form_id, $pid)
{
    $query1 = "SELECT id, form_id, pid, ORDER_DETAILS, option_id, notes FROM form_eye_mag_ordenqxod
               LEFT JOIN list_options on ORDER_DETAILS = title
               WHERE form_id=? and pid=? and list_id=? ORDER BY id ASC";
    $PLAN_results1 = sqlStatement($query1, array($form_id, $pid, 'cirugia_propuesta_defaults'));

    $propositos = array();

    if (!empty($PLAN_results1)) {
        while ($plan_row1 = sqlFetchArray($PLAN_results1)) {
            $Proposito = "SELECT * FROM list_options
                          WHERE list_id = 'Proposito_Riesgo' and option_id = ? ";
            $propositoITEM = sqlQuery($Proposito, array($plan_row1['option_id']));

            if (!empty($propositoITEM)) {
                $propositos[] = $propositoITEM['title'];
            }
        }
    }

    return $propositos;
}

function extractItemsFromQuery($form_id, $pid)
{
    $query = "SELECT c.name, c.consiste, c.realiza, c.grafico, c.duracion, c.beneficios,
              c.riesgos, c.riesgos_graves, c.alternativas, c.post, c.consecuencias
              FROM form_eye_mag_ordenqxod AS o
              LEFT JOIN consentimiento_informado AS c ON c.Id = o.ORDER_DETAILS
              WHERE o.form_id=? AND o.pid=? ORDER BY o.id ASC";

    $results = sqlStatement($query, array($form_id, $pid));

    $items = array();

    if (!empty($results)) {
        while ($row = sqlFetchArray($results)) {
            // Extraer los datos de cada item
            $name = $row['name'];
            $consiste = $row['consiste'];
            $realiza = $row['realiza'];
            $grafico = $row['grafico'];
            $duracion = $row['duracion'];
            $beneficios = $row['beneficios'];
            $riesgos = $row['riesgos'];
            $riesgos_graves = $row['riesgos_graves'];
            $alternativas = $row['alternativas'];
            $post = $row['post'];
            $consecuencias = $row['consecuencias'];

            // Crear un array con los datos extraídos del item
            $item = array(
                'name' => $name,
                'consiste' => $consiste,
                'realiza' => $realiza,
                'grafico' => $grafico,
                'duracion' => $duracion,
                'beneficios' => $beneficios,
                'riesgos' => $riesgos,
                'riesgos_graves' => $riesgos_graves,
                'alternativas' => $alternativas,
                'post' => $post,
                'consecuencias' => $consecuencias
            );

            // Agregar el item al array de items
            $items[] = $item;
        }
    }

    return $items;
}

function obtenerCodigosImpPlan($form_folder, $form_id, $pid)
{
    $query = "SELECT * FROM form_" . $form_folder . "_impplan WHERE form_id=? AND pid=? ORDER BY IMPPLAN_order ASC";
    $result = sqlStatement($query, array($form_id, $pid));
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    $codigosImpPlan = array();

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

        $pattern = '/Code/';
        if (preg_match($pattern, $newdata['code'])) {
            $newdata['code'] = '';
        }

        if ($newdata['codetext'] > '') {
            $codigosImpPlan['code'][] = $newdata['code'];
            $codigosImpPlan['codedesc'][] = $newdata['codedesc'];
        }
    }

    return $codigosImpPlan;
}

$resultado = obtenerCodigosImpPlan($form_folder, $form_id, $pid);
$codes = $resultado['code']; // Array con los valores de 'code'
$codedescs = $resultado['codedesc']; // Array con los valores de 'codedesc'

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
    'default_font_size' => '10',
    'default_font' => '"Arial","sans-serif"',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 8,
    'margin_bottom' => 8,
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
<head>
    <style>
        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 5px;
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
</head>
<body>
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
        <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
    </tr>
</TABLE>
<table style="margin-bottom: unset">
    <tr>
        <td class="morado" colspan="67">B. CONSENTIMIENTO INFORMADO
        </td>
    </tr>
    <tr>
        <td colspan="18" class="verde">CONSENTIMIENTO
            INFORMADO PARA:
        </td>
        <td colspan="49" class="blanco_left">
            <?php
            $items = extractItemsFromQuery($form_id, $pid);

            // Realizar acciones con los items extraídos
            foreach ($items

            as $item) {
            echo $item['name'];
            ?> en ojo derecho.
        </td>
    </tr>
    <tr>
        <td colspan="6"
            class="verde">SERVICIO:
        </td>
        <td colspan="26" class="blanco">OFTALMOLOGÍA</td>
        <td colspan="11" class="verde">TIPO DE
            ATENCIÓN:
        </td>
        <td colspan="8" class="blanco">
            AMBULATORIO
        </td>
        <td colspan="3" class="blanco">X</td>
        <td colspan="10" class="blanco">
            HOSPITALIZACIÓN
        </td>
        <td colspan="3" class="blanco"></td>
    </tr>
    <tr>
        <td colspan="8"
            class="verde">DIAGNÓSTICO:
        </td>
        <td colspan="44" class="blanco_left">
            <?php
            foreach ($codedescs as $codedesc) {
                echo $codedesc . "<br>";
            }
            ?>
        </td>
        <td colspan="4" class="verde">CIE 10:</td>
        <td colspan="11" class="blanco_left">
            <?php foreach ($codes as $code) {
                echo $code . "<br>";
            } ?>
        </td>
    </tr>
    <tr>
        <td colspan="23"
            class="verde">NOMBRE DEL
            PROCEDIMIENTO RECOMENDADO:
        </td>
        <td colspan="44" CLASS="blanco"></td>
    </tr>
    <tr id="r13">
        <td colspan="11"
            class="verde">EN QUÉ CONSISTE:
        </td>
        <td colspan="56" class="blanco_left">
            <?php
            echo wordwrap($item['consiste'], 125, "</td></tr><tr><td colspan='67' class='blanco_left'>");;
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="11"
            class="verde">CÓMO SE REALIZA:
        </td>
        <td colspan="56" class="blanco_left">
            <?php
            echo wordwrap($item['realiza'], 125, "</td></tr><tr><td colspan='67' class='blanco_left'>");
            ?>
        </td>
    </tr>
    <tr id="r17">
        <td colspan="67" class="verde">GRÁFICO DE LA
            INTERVENCIÓN (incluya un gráfico previamente seleccionado que facilite la comprensión al paciente)
        </td>
    </tr>
    <tr id="r18">
        <td colspan="67" height="200" class="blanco">
            <?php
            echo '<img src="' . $item['grafico'] . '" alt="Imagen" style="max-height: 200px;" /><br>';
            ?>
        </td>
    </tr>
    <tr id="r34">
        <td colspan="21" class="verde">DURACIÓN ESTIMADA DE
            LA INTERVENCIÓN:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['duracion'] . " minutos";
            ?>
        </td>
    </tr>
    <tr id="r35">
        <td colspan="21" class="verde">BENEFICIOS DEL
            PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['beneficios'];
            ?>
        </td>
    </tr>
    <tr id="r36">
        <td colspan="21" class="verde">RIEGOS FRECUENTES
            (POCO GRAVES):
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['riesgos'];
            ?>
        </td>
    </tr>
    <tr id="r37">
        <td colspan="21" class="verde">RIESGOS POCO
            FRECUENTES (GRAVES):
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['riesgos_graves'];
            ?>
        </td>
    </tr>
    <tr id="r38">
        <td colspan="67"
            class="verde">DE EXISTIR, ESCRIBA
            LOS RIESGOS ESPECÍFICOS RELACIONADOS CON EL PACIENTE (edad, estado de salud, creencias, valores, etc):
        </td>
    </tr>
    <tr id="r39">
        <td colspan="67" class="blanco"></td>
    </tr>
    <tr id="r40">
        <td colspan="67"
            class="blanco"></td>
    </tr>
    <tr id="r41">
        <td colspan="21" class="verde">ALTERNATIVAS AL
            PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['alternativas'];
            ?>
        </td>
    </tr>
    <tr id="r42">
        <td colspan="21" class="verde">DESCRIPCIÓN DEL MANEJO
            POSTERIOR AL PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['post'];
            ?>
        </td>
    </tr>
    <tr id="r43">
        <td colspan="21" style="border-left: 5px solid #808080; border-bottom: 5px solid #808080;"
            class="verde">CONSECUENCIAS POSIBLES
            SI NO SE REALIZA EL PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left" style="border-right: 5px solid #808080; border-bottom: 5px solid #808080;">
            <?php
            echo $item['consecuencias'];
            }
            ?>
        </td>
    </tr>
</table>
<table style="border: none">
    <TR>
        <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP / HCU-form.024/2016</B>
        </TD>
        <TD colspan="3" class="blanco" style="border: none; text-align: right"><B>CONSENTIMIENTO INFORMADO (1)</B>
        </TD>
    </TR>
</TABLE>

<pagebreak>
    <!--[DECLARACIÓN DE CONSENTIMIENTO INFORMADO]-->
    <table>
        <tr>
            <td colspan="67" height="40" class="morado">C. DECLARACIÓN DE
                CONSENTIMIENTO INFORMADO
            </td>
        </tr>
        <tr>
            <td colspan="11" class="verde">FECHA
            </td>
            <td colspan="56" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="67" style="font-size: 6pt">
                He facilitado la información completa que conozco, y me ha sido solicitada, sobre los antecedentes
                personales, familiares y de mi estado de salud. Soy consciente que de omitir estos datos puede afectarse
                los resultados del tratamiento. Estoy de acuerdo con el procedimiento que se me ha propuesto; he sido
                informado de las ventajas e inconvenientes del mismo; se me ha explicado de forma clara en qué consiste,
                los
                beneficios y posibles riesgos del procedimiento. He escuchado, leído y comprendido la información
                recibida y se me ha dado la oportunidad de preguntar sobre el procedimiento. He tomado consciente y
                libremente de
                decisión de autorizar el procedimiento adicional, si es considerado necesario según el juicio del
                profesional de la salud, para mi beneficio. También conozco que puedo retirar mi consentimiento cuando
                lo estime oportuno.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black"><?php echo text(xlt($titleres['fname'])
                    . " " . $titleres['mname'] . " " . $titleres['lname'] . " " . $titleres['lname2']); ?>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14"
                style="font-size: 7pt; border-top: 1px solid black"><?php echo text($titleres['pubpid']); ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del paciente o huella, según el
                caso.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">
                <?php
                echo getProviderName($providerID);
                ?>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="36" style="font-size: 7pt; border-top: 1px solid black">Firma, sello y código del profesional
                de la salud que realizará el procedimiento.
            </td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no está en capacidad para
                firmar el consentimiento informado:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" height="18" style="font-size: 7pt; border-top: 1px solid black">Parentesco</td>
            <td colspan="40"></td>
        </tr>
    </table>
    <!--[NEGATIVA DEL CONSENTIMIENTO INFORMADO]-->
    <table>
        <tr>
            <td colspan="67" height="40" class="morado">D. NEGATIVA DEL
                CONSENTIMIENTO INFORMADO
            </td>
        </tr>
        <tr>
            <td colspan="11" class="verde">FECHA
            </td>
            <td colspan="56" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="67" style="font-size: 6pt">
                Una vez que he entendido claramente el procedimiento propuesto, así como las consecuencias posibles si
                no se realiza la intervención, no autorizo y me niego a que se me realice el procedimiento propuesto y
                desvinculo de responsabilidades futuras de
                cualquier índole al establecimiento de salud y al profesional sanitario que me atiende, por no realizar
                la intervención sugerida.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre
                completo del paciente.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del paciente o huella, según el
                caso.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">
                Nombre de profesional que realiza el
                procedimiento.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="36" style="font-size: 7pt; border-top: 1px solid black">Firma, sello y código del profesional
                de la salud que realizará el procedimiento.
            </td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no está en capacidad para
                firmar el consentimiento informado:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" height="18" style="font-size: 7pt; border-top: 1px solid black">Parentesco</td>
            <td colspan="40"></td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no acepta el procedimiento sugerido por el
                profesional y se niega a firmar este acápite:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
    </table>
    <!--[REVOCATORIA DEL CONSENTIMIENTO INFORMADO]-->
    <table style="margin-bottom: unset">
        <tr>
            <td colspan="67" height="40" class="morado">E. REVOCATORIA DEL CONSENTIMIENTO INFORMADO
            </td>
        </tr>
        <tr>
            <td colspan="11" class="verde">FECHA
            </td>
            <td colspan="56" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="67" style="font-size: 6pt">
                De forma libre y voluntaria, revoco el consentimiento realizado en fecha y manifiesto expresamente mi
                deseo de no continuar con el procedimiento médico que doy por finalizado en esta fecha:
                Libero de responsabilidades futuras de cualquier índole al establecimiento de salud y al profesional
                sanitario que me atiende.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre
                completo del paciente.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del paciente o huella, según el
                caso.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">
                Nombre de profesional que realiza el
                procedimiento.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="36" style="font-size: 7pt; border-top: 1px solid black">Firma, sello y código del profesional
                de la salud que realizará el procedimiento.
            </td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no está en capacidad para
                firmar el consentimiento informado:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" height="18" style="font-size: 7pt; border-top: 1px solid black">Parentesco</td>
            <td colspan="40"></td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP / HCU-form.024/2016</B>
            </TD>
            <TD colspan="3" class="blanco" style="border: none; text-align: right"><B>CONSENTIMIENTO INFORMADO (2)</B>
            </TD>
        </TR>
    </TABLE>
    <pagebreak>
        <P ALIGN=CENTER>
            <?php
            echo $logo;
            ?>
        </P>
        <P ALIGN=CENTER STYLE="margin-bottom: 0.11in"><FONT SIZE=4><U><B>PLAN
                        DE EGRESO</B></U></FONT></P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm"><B>CIRUGIA
                OCULAR</B></P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">Diagn&oacute;stico
            egreso:</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm"><A
                NAME="_GoBack"></A>Fecha: </P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">Egresado a: Casa</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">Instrucciones para
            el
            paciente <?php echo text(xlt($titleres['title']) . " " . $titleres['fname'] . " " . $titleres['lname']); ?>
            y familia:</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">MEDICAMENTOS
            RECETADOS: <U><B>Tobracort (Tobramicina+Dexametazona) 1 gota cada 3
                    horas por 21 d&iacute;as</B></U><U>.</U></P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">ACTIVIDAD: Se debe
            mantener reposo en la postura de acuerdo a la indicaci&oacute;n del
            m&eacute;dico.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">HIGIENE: Debe ba&ntilde;arse
            el cuerpo con agua y jab&oacute;n incluyendo la cara.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">ALIMENTACI&Oacute;N:
            No hay restricci&oacute;n de dieta. Evite fumar o tomar alcohol hasta
            que est&eacute; completamente recuperado.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">CUIDADOS
            ESPECIALES: Mantenga parche y protector ocular durante 24 horas,
            seg&uacute;n prescripci&oacute;n m&eacute;dica. Controle sangrado
            (Observe si mancha la gasa).</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">EDUCACION AL
            PACIENTE:
            Pueden sentir picor, sensaci&oacute;n de cuerpo extra&ntilde;o,
            pinchazos espor&aacute;dicos: Son consecuencia de los punto
            conjuntivales.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">Cumpla con el
            tratamiento ambulatorio ya sea con colirios o pomadas de acuerdo a la
            prescripci&oacute;n de su m&eacute;dico.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">Un paciente sometido
            a
            cirug&iacute;a ocular <U><B>NO DEBE</B></U> en ning&uacute;n caso:
            Conducir, realizar actividades peligrosas, ni levantar pesos.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">La lectura y la
            televisi&oacute;n no est&aacute;n contraindicadas, excepto si
            producen molestias o impiden la posici&oacute;n recomendada.</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">OTROS:</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">M&eacute;dico
            tratante: <?php
            echo getProviderName($providerID);
            ?>
            Tel&eacute;fono: 2286080</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm"><U><B>INFORME DE
                    EGRESO DE ENFERMERIA</B></U>:</P>
        <P ALIGN=JUSTIFY STYLE="margin-bottom: 0.11in; margin-left: 2.5cm; margin-right: 2.5cm">PACIENTE EGRESA EN
            CONDICIONES FAVORABLES PARA SU SALUD, CON INDICACIONES MEDICA, SI
            LLEVA LA MEDICACI&Oacute;N.</P>
</body>
</html>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('consentimiento_od' . '.pdf', 'I'); // D = Download, I = Inline
}
?>
