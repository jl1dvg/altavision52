<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/iess.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/encounter.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
include_once($GLOBALS["srcdir"] . "/api.inc");
require_once(dirname(__FILE__) . "/../../../library/lists.inc");

use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

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
    'format' => 'A4',
    'default_font_size' => '10',
    'default_font' => '"Arial","sans-serif"',
    'margin_left' => 5,
    'margin_right' => 5,
    'margin_top' => 5,
    'margin_bottom' => 5,
    'margin_header' => '',
    'margin_footer' => '',
    'orientation' => 'L', // Cambiar a 'L' para orientación horizontal
    'shrink_tables_to_fit' => 1,
    'use_kwt' => true,
    'autoScriptToLang' => true,
    'keep_table_proportions' => true,
    'table_layout' => 'fixed',
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
    <style type="text/css">
        table {
            width: 100%;
            border: 3px solid #808080;
            border-collapse: collapse;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        td.morado {
            text-align: left;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 6pt;
            font-weight: bold;
        }

        td.verde {
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 5pt;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.verde_left {
            text-align: left;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 5pt;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.blanco {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            text-align: center;
            vertical-align: middle;
            font-size: 5pt;
        }

        td.blanco_break {
            border-left: 3px solid #808080;
            border-right: 3px solid #808080;
            height: 21px;
            text-align: center;
            vertical-align: middle;
            font-size: 5pt;
        }

        td.blanco_left {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            text-align: left;
            vertical-align: middle;
            font-size: 5pt;
        }

        a {
            color: inherit;
        }

        .s110 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s127 {
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s39 {
            border-bottom: 1px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Noto Sans Symbols', Arial;
            font-size: 8pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s44 {
            border-right: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 5pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s42 {
            border-bottom: 1px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s51 {
            border-bottom: 1px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s74 {
            border-bottom: 3px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s49 {
            border-bottom: 1px SOLID #808080;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 7pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s71 {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s75 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 5pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s48 {
            border-bottom: 1px SOLID #c0c0c0;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s55 {
            border-right: none;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 5pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s124 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s73 {
            border-bottom: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s70 {
            border-bottom: 3px SOLID #000000;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s61 {
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s126 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s108 {
            border-bottom: 3px SOLID #a5a5a5;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s37 {
            border-bottom: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s45 {
            border-bottom: 1px SOLID #808080;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s107 {
            border-bottom: 3px SOLID #a5a5a5;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s69 {
            border-bottom: 3px SOLID #000000;
            border-right: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s54 {
            border-bottom: 1px SOLID #000000;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Noto Sans Symbols', Arial;
            font-size: 8pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s63 {
            border-bottom: 1px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s57 {
            border-left: none;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s62 {
            border-right: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s64 {
            border-bottom: 1px SOLID #000000;
            border-right: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s72 {
            border-bottom: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s43 {
            border-bottom: 1px SOLID #000000;
            border-right: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s40 {
            border-bottom: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Noto Sans Symbols', Arial;
            font-size: 8pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s66 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0;
        }

        .s65 {
            border-bottom: 1px SOLID #000000;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s52 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s46 {
            border-bottom: 1px SOLID #c0c0c0;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s38 {
            border-bottom: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s92 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s60 {
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s68 {
            border-bottom: 3px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s67 {
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: right;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s50 {
            border-bottom: 1px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s109 {
            border-bottom: 3px SOLID #a5a5a5;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s53 {
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Calibri', Arial;
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s47 {
            border-bottom: 1px SOLID #c0c0c0;
            border-right: 1px SOLID #c0c0c0;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s138 {
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 4pt;
            vertical-align: top;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s140 {
            background-color: #ffffff;
            text-align: right;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 4pt;
            vertical-align: top;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s56 {
            border-left: none;
            border-right: none;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s41 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 3px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s59 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'docs-Noto Sans Symbols', Arial;
            font-size: 11pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s36 {
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 5pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s125 {
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s58 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s77 {
            border-bottom: 3px SOLID #000000;
            border-right: 1px SOLID #000000;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 5pt;
            vertical-align: bottom;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }
    </style>
</head>
<body>
<table>
    <tr style="height: 18px">
        <td class="morado" colspan="138">A. DATOS DEL ESTABLECIMIENTO Y USUARIO</td>
    </tr>
    <tr style="height: 16px">
        <td class="verde" colspan="27">INSTITUCIÓN DEL SISTEMA</td>
        <td class="verde" colspan="45">ESTABLECIMIENTO DE SALUD</td>
        <td class="verde" colspan="40">NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
        <td class="verde" colspan="26">NÚMERO DE ARCHIVO</td>
    </tr>
    <tr style="height: 16px">
        <td class="blanco" colspan="27"><?php echo $titleres['pricelevel']; ?></td>
        <td class="blanco" colspan="45">AlTA VISION</td>
        <td class="blanco" colspan="40"><?php echo $titleres['pubpid']; ?></td>
        <td class="blanco" colspan="26"><?php echo $titleres['pubpid']; ?></td>
    </tr>
    <tr style="height: 16px">
        <td class="verde" colspan="23" rowspan="2">PRIMER APELLIDO</td>
        <td class="verde" colspan="21" rowspan="2">SEGUNDO APELLIDO</td>
        <td class="verde" colspan="25" rowspan="2">PRIMER NOMBRE</td>
        <td class="verde" colspan="24" rowspan="2">SEGUNDO NOMBRE</td>
        <td class="verde" colspan="8" rowspan="2">SEXO</td>
        <td class="verde" colspan="16" rowspan="2">FECHA NACIMIENTO</td>
        <td class="verde" colspan="12" rowspan="2">EDAD</td>
        <td class="verde" colspan="9">CONDICIÓN EDAD</td>
    </tr>
    <tr style="height: 11px">
        <td class="blanco" colspan="2">H</td>
        <td class="blanco" colspan="2">D</td>
        <td class="blanco" colspan="2">M</td>
        <td class="blanco" colspan="3">A</td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco" colspan="23"><?php echo $titleres['lname']; ?></td>
        <td class="blanco" colspan="21"><?php echo $titleres['lname2']; ?></td>
        <td class="blanco" colspan="25"><?php echo $titleres['fname']; ?></td>
        <td class="blanco" colspan="24"><?php echo $titleres['mname']; ?></td>
        <td class="blanco" colspan="8"><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td class="blanco" colspan="16"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td class="blanco" colspan="12"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td class="blanco" colspan="2"></td>
        <td class="blanco" colspan="2"></td>
        <td class="blanco" colspan="2"></td>
        <td class="blanco" colspan="3">X</td>
    </tr>
    <tr style="height: 17px">
        <td class="verde" colspan="9">FECHA:</td>
        <td class="blanco" colspan="18">
            <?php echo date('d/m/Y', strtotime(fetchDateByEncounter($encounter))); ?>
        </td>
        <td class="verde" colspan="6">TALLA (cm)</td>
        <td class="blanco" colspan="14"></td>
        <td class="verde" colspan="14">PESO (kg)</td>
        <td class="blanco" colspan="11"></td>
        <td class="verde" colspan="4">IMC</td>
        <td class="blanco" colspan="18"></td>
        <td class="verde" colspan="14">GRUPO Y FACTOR</td>
        <td class="blanco" colspan="4"></td>
        <td class="verde" colspan="20">CONSENTIMIENTO INFORMADO</td>
        <td class="verde">SI</td>
        <td class="blanco">X</td>
        <td class="verde" colspan="2">NO</td>
        <td class="blanco" colspan="2"></td>
    </tr>
</table>
<table>
    <tr style="height: 18px">
        <td class="morado" colspan="138">B. SERVICIO Y PRIORIDAD DE ATENCIÓN</td>
    </tr>
    <tr style="height: 12px">
        <td class="verde_left" colspan="13">DIAGNÓSTICO PREOPERATORIO</td>
        <td class="blanco" style="text-align: left"
            colspan="29">
            <?php
            $prot_dxpre = (getFieldValue($formid, "Prot_dxpre"));
            $prot_dxpre2 = getFieldValue($formid, "Prot_dxpre2");
            $prot_dxpre3 = getFieldValue($formid, "Prot_dxpre3");

            echo lookup_code_short_descriptions($prot_dxpre);

            if ($prot_dxpre2 !== null) {
                echo ", " . lookup_code_short_descriptions($prot_dxpre2);
            }

            if ($prot_dxpre3 !== null) {
                echo ", " . lookup_code_short_descriptions($prot_dxpre3);
            }
            ?>
        </td>
        <td class="verde_left" colspan="3">CIE</td>
        <td class="blanco" style="text-align: left" colspan="16">
            <?php
            echo substr($prot_dxpre, 6);
            if ($prot_dxpre2 !== null) {
                echo ", " . substr($prot_dxpre2, 6);
            }

            if ($prot_dxpost3 !== null) {
                echo ", " . substr($prot_dxpre3, 6);
            }
            ?>
        </td>
        <td class="verde_left" colspan="11">CIRUGÍA PROPUESTA</td>
        <td class="blanco" style="text-align: left" colspan="38">
            <?php echo obtenerIntervencionesPropuestas(getFieldValue($formid, "Prot_opp")) . " ";
            $ojoValue = getFieldValue($formid, "Prot_ojo");

            if ($ojoValue == 'OI') {
                echo "Ojo izquierdo";
            } elseif ($ojoValue == 'OD') {
                echo "Ojo derecho";
            } elseif ($ojoValue == 'AO') {
                echo "Ambos ojos";
            } else {
                echo "Valor no válido";
            } ?>
        </td>
        <td class="verde_left" colspan="8">ESPECIALIDAD</td>
        <td class="verde_left" colspan="11" rowspan="4">PRIORIDAD</td>
        <td class="blanco_left" colspan="6">EMERGENTE</td>
        <td class="blanco" colspan="3"></td>
    </tr>
    <tr style="height: 11px">
        <td class="verde_left" colspan="13">DIAGNÓSTICO POSTOPERATORIO</td>
        <td class="blanco_left" style="text-align: left" colspan="29"><?php
            $prot_dxpost = (getFieldValue($formid, "Prot_dxpost"));
            $prot_dxpost2 = getFieldValue($formid, "Prot_dxpost2");
            $prot_dxpost3 = getFieldValue($formid, "Prot_dxpost3");

            echo lookup_code_short_descriptions($prot_dxpost);

            if ($prot_dxpost2 !== null) {
                echo lookup_code_short_descriptions($prot_dxpost2);
            }

            if ($prot_dxpost3 !== null) {
                echo lookup_code_short_descriptions($prot_dxpost3);
            }
            ?>
        </td>
        <td class="verde_left" colspan="3">CIE</td>
        <td class="blanco_left" style="text-align: left" colspan="16"><?php
            echo substr($prot_dxpost, 6);
            if ($prot_dxpost2 !== null) {
                echo ", " . substr($prot_dxpost2, 6);
            }

            if ($prot_dxpost3 !== null) {
                echo ", " . substr($prot_dxpost3, 6);
            }
            ?>
        </td>
        <td class="verde_left" colspan="11">CIRUGÍA REALIZADA</td>
        <td class="blanco_left" style="text-align: left" colspan="38">
            <?php echo obtenerIntervencionesPropuestas(getFieldValue($formid, "Prot_opr")) . " ";
            if ($ojoValue == 'OI') {
                echo "Ojo izquierdo";
            } elseif ($ojoValue == 'OD') {
                echo "Ojo derecho";
            } elseif ($ojoValue == 'AO') {
                echo "Ambos ojos";
            } else {
                echo "Valor no válido";
            }
            ?>
        </td>
        <td class="blanco_left" colspan="8">Oftalmología</td>
        <td class="blanco_left" colspan="6">URGENTE</td>
        <td class="blanco_left" colspan="3"></td>
    </tr>
    <tr style="height: 11px">
        <td class="verde_left" colspan="7">ANESTESIÓLOGO</td>
        <td class="blanco_left" style="text-align: left"
            colspan="35"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
        <td class="verde_left" colspan="6">AYUDANTE (S)</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="verde_left" colspan="19">INSTRUMENTISTA</td>
        <td class="blanco_left" colspan="19">
            <?php
            $instrumentistaOK = getFieldValue($form_id, "Prot_Instrumentistas");
            if ($instrumentistaOK == 'Si') {
                echo "Dr. Jorge Luis de Vera";
            } ?>
        </td>
        <td class="verde_left" colspan="8">QUIRÓFANO</td>
        <td class="blanco_left" colspan="6">ELECTIVO</td>
        <td class="blanco_left" colspan="3">X</td>
    </tr>
    <tr style="height: 11px">
        <td class="verde_left" colspan="7">CIRUJANO</td>
        <td class="blanco_left" style="text-align: left" colspan="35"><?php echo $providerNAME; ?></td>
        <td class="verde_left" colspan="6">AYUDANTE (S)</td>
        <td class="blanco_left"
            colspan="24"><?php echo getProviderName(getFieldValue($form_id, "Prot_ayudante")); ?></td>
        <td class="verde_left" colspan="19">CIRCULANTE</td>
        <td class="blanco_left" colspan="19"><?php
            echo getFieldValue($form_id, "Prot_opr") == "avastin" ? " " : "Lcda. Solange Vega";
            ?></td>
        </td>
        <td class="blanco_left" colspan="8">1</td>
        <td class="blanco_left" colspan="6"></td>
        <td class="blanco_left" colspan="3"></td>
    </tr>
</table>
<table>
    <tr style="height: 18px">
        <td class="morado" colspan="138">C. REGIÓN OPERATORIA</td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="6"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="8"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="5">CABEZA</td>
        <td class="blanco_left" colspan="2">X</td>
        <td class="blanco_left" colspan="3">ORGANOS DE LOS SENTIDOS</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="5">CUELLO</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="8">COLUMNA</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="10">TORAX</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="6">ABDOMEN</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="7">PELVIS</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="7">EXTREMIDADES SUPERIORES</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="15">EXTREMIDADES INFERIORES</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="8"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4">PERINEAL</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="6"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="7"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="3"></td>
    </tr>
</table>
<table>
    <tr style="height: 18px">
        <td class="morado" colspan="138">D. REGISTRO TRANSANESTÉSICO</td>
    </tr>
    <tr style="height: 12px">
        <td class="s36" colspan="30">AGENTE INHALATORIO / INFUSIÓN CONTINUA</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon"
                             style="height: 6px"></img></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s40"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
    </tr>
    <tr style="height: 12px">
        <td class="s41" colspan="30"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s41" colspan="30"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s41" colspan="30"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s41" colspan="30"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s41" colspan="30"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s41" colspan="30"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s36" colspan="30">PARAMETROS DE MONITOREO ANESTÉSICO</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s39"></td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s38" colspan="2"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2"></td>
        <td class="s38"></td>
        <td class="s40"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s44" colspan="30">ONDA DELTA PP</td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s44" colspan="30">SATURACION O2</td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s44" colspan="30">CAPNOMETRÍA</td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s42"></td>
        <td class="s38"></td>
        <td class="s38"></td>
        <td class="s43"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s44" colspan="30">RELAJACIÓN NEUROMUSCULAR</td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s44" colspan="30">PROFUNDIDAD ANESTÉSICA</td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s42" colspan="3"></td>
        <td class="s43" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s45"></td>
        <td class="s46"></td>
        <td class="s46"></td>
        <td class="s47"></td>
        <td class="s48" colspan="108"></td>
    </tr>
    <tr style="height: 15px">
        <td class="s49" colspan="27" rowspan="2">SIMBOLOGÍA</td>
        <td class="s50" rowspan="2">T°</td>
        <td class="s51" rowspan="2">PV</td>
        <td class="s51" rowspan="2">TA<br>P. / R.</td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s52"></td>
        <td class="s53"></td>
    </tr>
    <tr style="height: 15px">
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s54"><img src="https://cdn-icons-png.flaticon.com/512/626/626075.png" alt="Icon" style="height: 6px">
        </td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:89px;left:-1px">INICIO ANESTESIA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s59"><img src="https://cdn-icons-png.flaticon.com/512/545/545678.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">240</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:63px;left:-1px">INDUCCIÓN</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/32/32178.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:76px;left:-1px">INICIO CIRUGÍA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/565/565762.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">220</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:76px;left:-1px">FIN DE CIRUGÍA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/565/565762.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:89px;left:-1px">FIN DE ANESTESIA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/2198/2198359.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">200</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:24px;left:-1px">TAS</div>
        </td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/32/32195.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:24px;left:-1px">TAD</div>
        </td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/3838/3838683.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">180</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:24px;left:-1px">TAM</div>
        </td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s66">
            <img src="https://cdn-icons-png.flaticon.com/512/32/32178.png" style="height: 8px">
        </td>
        <td class="s58"></td>
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:76px;left:-1px">SIN NEGRITAS</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s67">17</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:102px;left:-1px">FRECUENCIA CARDÍACA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s66">
            <img src="https://cdn-icons-png.flaticon.com/512/0/14.png" style="height: 8px">
        </td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">160</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:76px;left:-1px">TEMPERATURA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s66"><img src="https://cdn-icons-png.flaticon.com/512/649/649731.png" style="height: 8px">
        </td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">42</td>
        <td class="s67">15</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:24px;left:-1px">PVC</div>
        </td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"><img src="https://cdn-icons-png.flaticon.com/512/702/702845.png" style="height: 8px"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">140</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:128px;left:-1px">RESPIRACIÓN ESPONTÁNEA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">41</td>
        <td class="s67">13</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:115px;left:-1px">RESPIRACIÓN ASISTIDA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">120</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:128px;left:-1px">RESPIRACIÓN CONTROLADA</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">40</td>
        <td class="s67">11</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:63px;left:-1px">TORNIQUETE</div>
        </td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">100</td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s70"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s55 softmerge">
            <div class="softmerge-inner" style="width:37px;left:-1px">FETO</div>
        </td>
        <td class="s56"></td>
        <td class="s57"></td>
        <td class="s57"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">38</td>
        <td class="s67">9</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">80</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">37</td>
        <td class="s67">7</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s58"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">60</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s58"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">36</td>
        <td class="s67">5</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s58"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">40</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s67">35</td>
        <td class="s67">3</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">20</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s67">1</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s62" rowspan="2">0</td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s60"></td>
        <td class="s61"></td>
        <td class="s61"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s64"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s63"></td>
        <td class="s65"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s72"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s73"></td>
        <td class="s74"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s69"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s68"></td>
        <td class="s70"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s75" colspan="30" rowspan="3">DROGAS ADMINISTRADAS</td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
        <td class="blanco" colspan="12" rowspan="3"></td>
    </tr>
    <tr style="height: 12px">
    </tr>
    <tr style="height: 12px">
    </tr>
    <tr style="height: 12px">
        <td class="s77" colspan="30">POSICIÓN</td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
        <td class="blanco" colspan="12"></td>
    </tr>
</table>
<table>
    <tr style="height: 18px">
        <td class="morado" colspan="138">E. DROGAS ADMINISTRADAS</td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left">1</td>
        <td class="blanco_left" colspan="18">MIDAZOLAM 1 MG</td>
        <td class="blanco_left">5</td>
        <td class="blanco_left" colspan="22">KETOROLACO 60MG</td>
        <td class="blanco_left">9</td>
        <td class="blanco_left" colspan="24">GENTAMICINA 80MG/ML</td>
        <td class="blanco_left">13</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">17</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left">21</td>
        <td class="blanco_left" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left">2</td>
        <td class="blanco_left" colspan="18">FENTALINO 25MG</td>
        <td class="blanco_left">6</td>
        <td class="blanco_left" colspan="22">ONDASETRON 4MG</td>
        <td class="blanco_left">10</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">14</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">18</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left">22</td>
        <td class="blanco_left" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left">3</td>
        <td class="blanco_left" colspan="18">LIDOCAINA 2% 40MG</td>
        <td class="blanco_left">7</td>
        <td class="blanco_left" colspan="22">DEXAMETASONA 4MG</td>
        <td class="blanco_left">11</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">15</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">19</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left">23</td>
        <td class="blanco_left" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left">4</td>
        <td class="blanco_left" colspan="18">BUPIVACAINA 0,5% 10MG</td>
        <td class="blanco_left">8</td>
        <td class="blanco_left" colspan="22">CEXFTRIAXONA 1G</td>
        <td class="blanco_left">12</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">16</td>
        <td class="blanco_left" colspan="24"></td>
        <td class="blanco_left">20</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left">24</td>
        <td class="blanco_left" colspan="23"></td>
    </tr>
</table>
<table>
    <tr style="height: 17px">
        <td class="morado" colspan="138">F. TÉCNICA ANESTÉSICA</td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco" colspan="49"><b>GENERAL</b></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco" colspan="50"><b>REGIONAL</b></td>
        <td class="blanco"></td>
        <td class="blanco"></td>
        <td class="blanco_left"></td>
        <td class="blanco" colspan="33"><b>SEDO - ANALGESIA</b></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="25">SISTEMA</td>
        <td class="blanco_left" colspan="24">APARATO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="9">ASEPSIA CON:</td>
        <td class="blanco" colspan="21"><b>ALCOHOL</b></td>
        <td class="blanco_left" colspan="5">HABÓN CON:</td>
        <td class="blanco_left" colspan="15"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="4">ABIERTO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="6">SEMICERRADO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="12">CERRADO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="9">CIRCUITO CIRCULAR</td>
        <td class="blanco_left" colspan="5"></td>
        <td class="blanco_left" colspan="6">UNIDIRECCIONAL</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="9">LOCAL ASISTIDA</td>
        <td class="blanco_left" colspan="21"><b>BLOQUEO RETROBULBAR</b></td>
        <td class="blanco_left" colspan="5">INTRAVENOSA</td>
        <td class="blanco" colspan="15"><b>X</b></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="49">MANEJO DE VÍA AÉREA</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco" colspan="50"><b>TRONCULAR</b></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="13">MÁSCARA FACIAL</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="13">SUPRAGLÓTICA</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="13">TRAQUEOTOMO</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11">BLOQUEO DE NERVIO</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left" colspan="9">No. INTENTOS</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="49">INTUBACIÓN</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11">BLOQUEO DEL PLEXO</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left" colspan="9">No. INTENTOS</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="5">NASAL</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="5">ORAL</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="9">SUBMENTONEANA</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="9">VISIÓN DIRECTA</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="5">A CIEGAS</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11">ANESTÉSICO LOCAL</td>
        <td class="blanco" colspan="21"><b>REGIONAL</b></td>
        <td class="blanco_left" colspan="9">COADYUVANTE</td>
        <td class="blanco_left" colspan="9"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="49">TIPO DE TUBO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11">TIPO DE AGUJA</td>
        <td class="blanco" colspan="21"><b>25X1</b></td>
        <td class="blanco_left" colspan="9">EQUIPO</td>
        <td class="blanco_left" colspan="9"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="8">CONVENCIONAL</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="10">PREFORMADO ORAL</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="9">PREFORMADO NASAL</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="7">REFORZADO</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco" colspan="50"><b>NEUROAXIAL</b></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="8">DOBLE LUMEN</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="7">DIÁMETRO</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="3">BALÓN</td>
        <td class="blanco_left">SI</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="3">NO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11">TAPONAMIENTO</td>
        <td class="blanco_left">SI</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2">NO</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="6">RAQUÍDEA</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="5">EPIDURAL</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="4">CAUDAL</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="5"></td>
        <td class="blanco_left" colspan="4">CATETER</td>
        <td class="blanco_left" colspan="5"></td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="5"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left">
            <div class="softmerge-inner" style="width:128px;left:-1px">EQUIPO PARA INTUBACIÓN</div>
        </td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="39"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="6">TIPO AGUJA</td>
        <td class="blanco_left" colspan="9"></td>
        <td class="blanco_left" colspan="19">NÚMERO AGUJA</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="9">No. INTENTOS</td>
        <td class="blanco_left" colspan="5"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="5">CORMACK:</td>
        <td class="blanco_left" colspan="2">I</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="2">II</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2">III</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2">IV</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="9">NÚMERO DE INTENTOS</td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="6">BORBOTAJE</td>
        <td class="blanco_left" colspan="3">SI</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2">NO</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4">ACCESO</td>
        <td class="blanco_left" colspan="4">MEDIAL</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4">LATERAL</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="7">SITIO DE PUNCIÓN</td>
        <td class="blanco_left" colspan="11"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="21">INDUCCIÓN</td>
        <td class="blanco_left" colspan="28">MANTENIMIENTO</td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11"></td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left" colspan="9"></td>
        <td class="blanco_left" colspan="9"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="33">ESCALA DE RAMSAY</td>
    </tr>
    <tr style="height: 12px">
        <td class="blanco_left" colspan="9">INHALATORIA</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="6">INTRAVENOSA</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="6">INHALATORIA</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="6">INTRAVENOSA</td>
        <td class="blanco_left" colspan="4"></td>
        <td class="blanco_left" colspan="6">BALANCEADA</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="11">DERMATOMA</td>
        <td class="blanco_left" colspan="21"></td>
        <td class="blanco_left" colspan="9">POSICIÓN</td>
        <td class="blanco_left" colspan="9"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left"></td>
        <td class="blanco_left" colspan="4">1</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4">2</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4">3</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="4">4</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="3">5</td>
        <td class="blanco_left" colspan="2"></td>
        <td class="blanco_left" colspan="2">6</td>
        <td class="blanco_left" colspan="2"></td>
    </tr>
    <tr style="height: 12px">
        <td class="s107"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s107"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s107"></td>
        <td class="s109"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s108"></td>
        <td class="s110"></td>
    </tr>
</table>
<table style="border: none">
    <tr style="height: 21px">
        <td class="s138">SNS-MSP / HCU-form.018A/2021</td>
        <td class="s140">TRANSANESTÉSICO (1)</td>
    </tr>
</table>
<pagebreak>
    <table>
        <tr style="height: 18px">
            <td class="morado" colspan="42">G. ACCESOS VASCULARES</td>
            <td class="morado" colspan="48">H. REPOSICIÓN VOLÉMICA (ml)</td>
            <td class="morado" colspan="48">I. PERDIDAS</td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6"><b>TIPO</b></td>
            <td class="blanco" colspan="7"><b>CALIBRE</b></td>
            <td class="blanco" colspan="29"><b>SITIO</b></td>
            <td class="blanco_left" colspan="12">DEXTROSA 5%</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">SANGRE</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">SANGRADO</td>
            <td class="blanco_left" colspan="12"><b>MINIMO</b></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6">IV PERIFÉRICO 1</td>
            <td class="blanco" colspan="7"><b>"22"</b></td>
            <td class="blancO" colspan="29"><b>ANTEBRAZO</b></td>
            <td class="blanco_left" colspan="12">DEXTROSA 10%</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">PLASMA</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">DIURESIS</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6">IV PERIFÉRICO 2</td>
            <td class="blanco_left" colspan="7"></td>
            <td class="blanco_left" colspan="29"></td>
            <td class="blanco_left" colspan="12">DEXTROSA 50%</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">PLAQUETAS</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">OTROS</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6">IV PERIFÉRICO 3</td>
            <td class="blanco_left" colspan="7"></td>
            <td class="blanco_left" colspan="29"></td>
            <td class="blanco_left" colspan="12">DEXTROSA EN SS</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">CRIOPRECIPITADOS</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12">TOTAL</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6">IV CENTRAL</td>
            <td class="blanco_left" colspan="7"></td>
            <td class="blanco_left" colspan="29"></td>
            <td class="blanco_left" colspan="12">SS 0.9%</td>
            <td class="blanco" colspan="12"><b>1000 ML</b></td>
            <td class="blanco_left" colspan="12" rowspan="2">OTROS</td>
            <td class="blanco_left" colspan="12"><b>MANITOL 20% 100ML</b></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6">INTRA ARTERIAL</td>
            <td class="blanco_left" colspan="7"></td>
            <td class="blanco_left" colspan="29"></td>
            <td class="blanco_left" colspan="12">LACTATO RINGER</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco_left" colspan="6">OTRO</td>
            <td class="blanco_left" colspan="7"></td>
            <td class="blanco_left" colspan="29"></td>
            <td class="blanco_left" colspan="12">EXPANSORES</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"><b>TOTAL</b></td>
            <td class="blanco" colspan="12"><b>1100 ML</b></td>
            <td class="blanco_left" colspan="12">BALANCE</td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s124"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s124"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s124"></td>
            <td class="s126"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s125"></td>
            <td class="s127"></td>
        </tr>
    </table>
    <table>
        <tr style="height: 18px">
            <td class="morado" colspan="22">J. DATOS DEL RECIÉN NACIDO</td>
            <td class="morado" colspan="116">K. TIEMPOS TRANSCURRIDOS</td>
        </tr>
        <tr style="height: 16px">
            <td class="blanco_left" colspan="4" rowspan="2">APGAR</td>
            <td class="blanco_left" colspan="6">FETO MUERTO</td>
            <td class="blanco_left" colspan="2"></td>
            <td class="blanco_left" colspan="8">5 MINUTOS</td>
            <td class="blanco_left" colspan="2"></td>
            <td class="verde" colspan="14">DURACIÓN ANESTESIA</td>
            <td class="blanco_left" colspan="14">
                <?php
                $horaInicio = strtotime(getFieldValue($formid, "Prot_hini"));
                $horaFin = $horaInicio + 7200;

                $prot_opr_value = getFieldValue($formid, "Prot_opr");
                if ($prot_opr_value === 'avastin') {
                    $horaFin = $horaInicio + 3600;
                } elseif (strpos($prot_opr_value, 'vpp') !== false) {
                    $horaFin = $horaInicio + 10800;
                }

                $diferencia = $horaFin - $horaInicio;

                // Restar 15 minutos a la diferencia
                $diferencia -= (15 * 60); // 15 minutos en segundos

                // Convertir la diferencia a formato horas:minutos
                $horas = floor($diferencia / 3600);
                $minutos = floor(($diferencia % 3600) / 60);

                echo "<b>" . $horas . " horas y " . $minutos . " minutos.</b>";
                ?>
            </td>
            <td class="verde" colspan="13">DURACIÓN DE CIRUGÍA</td>
            <td class="blanco_left" colspan="27">
                <?php
                $diferencia = $horaFin - $horaInicio;

                // Convertir la diferencia a formato horas:minutos
                $horas = floor($diferencia / 3600);
                $minutos = floor(($diferencia % 3600) / 60);

                echo "<b>" . $horas . " horas y " . $minutos . " minutos.</b>";
                ?>
            </td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 16px">
            <td class="blanco_left" colspan="6">1 MINUTO</td>
            <td class="blanco_left" colspan="2"></td>
            <td class="blanco_left" colspan="8">10 MINUTOS</td>
            <td class="blanco_left" colspan="2"></td>
            <td class="blanco_left" colspan="8"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
            <td class="blanco_left" colspan="12"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
            <td class="s92"></td>
        </tr>
    </table>
    <table>
        <tr style="height: 12px">
            <td class="morado" colspan="68">L. TÉCNICAS ESPECIALES</td>
        </tr>
        <tr style="height: 23px">
            <td class="blanco" colspan="11">HEMODILUCIÓN</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">AUTOTRANSFUSIÓN</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">HIPOTENSIÓN</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">HIPOTERMIA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="13">CIRCULACIÓN EXTRACORPÓREA</td>
            <td class="blanco" colspan="3"></td>
        </tr>
    </table>
    <table>
        <tr style="height: 12px">
            <td class="morado" colspan="68">M. MANTENIMIENTO TEMPERATURA CORPORAL</td>
        </tr>
        <tr style="height: 17px">
            <td class="blanco" colspan="11">MANTA TÉRMICA</td>
            <td class="blanco" colspan="2">X</td>
            <td class="blanco" colspan="11">CALENTAMIENTO DE FLUIDOS</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="4">OTROS</td>
            <td class="blanco" colspan="38"></td>
        </tr>
    </table>
    <table>
        <tr style="height: 20px">
            <td class="morado" colspan="68">N. INCIDENTES</td>
        </tr>
        <tr style="height: 23px">
            <td class="blanco" colspan="11">ACTIVIDAD ELÉCTRICA SIN PULSO</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">ARRITMIA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">ASISTOLIA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">BRADICARDIA INESTABLE</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="13">TROMBOEMBOLIA PULMONAR</td>
            <td class="blanco" colspan="3"></td>
        </tr>
        <tr style="height: 16px">
            <td class="blanco" colspan="11">HIPERTERMIA MALIGNA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">ANAFILAXIA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">ISQUEMIA MIOCÁRDICA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">HIPOXEMIA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="13">NEUMOTÓRAX</td>
            <td class="blanco" colspan="3"></td>
        </tr>
        <tr style="height: 16px">
            <td class="blanco" colspan="11">BRONCOESPASMO</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">DESPERTAR PROLONGADO</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">EMBOLIA AÉREA VENOSA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="11">REACCIÓN A LA TRANSFUSIÓN</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="13">LARINGOESPASMO</td>
            <td class="blanco" colspan="3"></td>
        </tr>
        <tr style="height: 23px">
            <td class="blanco" colspan="11">DIFICULTAD DE LA TÉCNICA</td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="55">OTROS</td>
        </tr>
    </table>
    <table>
        <tr style="height: 20px">
            <td class="morado" colspan="68">O. RESULTADO DE EXÁMENES DE LABORATORIO</td>
        </tr>
        <tr style="height: 16px">
            <td class="blanco"></td>
            <td class="blanco" colspan="4">HORA</td>
            <td class="blanco" colspan="5">pH</td>
            <td class="blanco" colspan="5">PO2</td>
            <td class="blanco" colspan="5">PCO2</td>
            <td class="blanco" colspan="5">HCO3</td>
            <td class="blanco" colspan="5">EB</td>
            <td class="blanco" colspan="5">SAT. O2</td>
            <td class="blanco" colspan="5">LACTATO</td>
            <td class="blanco" colspan="5">GLUCOSA</td>
            <td class="blanco" colspan="4">Na</td>
            <td class="blanco" colspan="3">K</td>
            <td class="blanco" colspan="3">Cl</td>
            <td class="blanco" colspan="3">HCTO</td>
            <td class="blanco" colspan="2">HB</td>
            <td class="blanco" colspan="8">OTRO</td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">1</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">2</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">3</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">4</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">5</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">6</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">7</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">7</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">9</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="blanco">10</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="3"></td>
            <td class="blanco" colspan="2"></td>
            <td class="blanco" colspan="8"></td>
        </tr>
    </table>
    <table>
        <tr style="height: 20px">
            <td class="morado" colspan="68">P. OBSERVACIONES</td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
        <tr style="height: 15px">
            <td class="blanco" colspan="68"><br></td>
        </tr>
    </table>
    <table>
        <tr style="height: 20px">
            <td class="morado" colspan="68">R. CONDICIÓN DE EGRESO</td>
        </tr>
        <tr style="height: 23px">
            <td class="blanco" colspan="13" rowspan="2">CONDICIONES AL SALIR</td>
            <td class="blanco" colspan="7">EXTUBADO</td>
            <td class="blanco" colspan="4"></td>
            <td class="blanco" colspan="7" rowspan="2">CONDUCIDO A:</td>
            <td class="blanco" colspan="7" rowspan="2">UNIDAD DE CUIDADOS POST ANESTÉSICOS</td>
            <td class="blanco" colspan="3" rowspan="2">X</td>
            <td class="blanco" colspan="6" rowspan="2">UNIDAD CUIDADOS INTENSIVOS</td>
            <td class="blanco" colspan="3" rowspan="2"></td>
            <td class="blanco" colspan="7" rowspan="2">CRÍTICOS DE EMERGENCIA</td>
            <td class="blanco" colspan="3" rowspan="2"></td>
            <td class="blanco" colspan="5" rowspan="2">MORGUE</td>
            <td class="blanco" colspan="3" rowspan="2"></td>
        </tr>
        <tr style="height: 23px">
            <td class="blanco" colspan="7">INTUBADO</td>
            <td class="blanco" colspan="4"></td>
        </tr>
        <tr style="height: 23px">
            <td class="blanco" colspan="13">CONSTANTES VITALES DE ENTREGA</td>
            <td class="blanco" colspan="14">TA: 103/96</td>
            <td class="blanco" colspan="10">FC: 74</td>
            <td class="blanco" colspan="10">FR: 20</td>
            <td class="blanco" colspan="12">SAT. O2: 99%</td>
            <td class="blanco" colspan="9">T: 37.5°</td>
        </tr>
    </table>
    <table>
        <tr style="height: 20px">
            <td class="morado" colspan="68">S. DATOS DEL PROFESIONAL RESPONSABLE</td>
        </tr>
        <tr style="height: 50px">
            <td class="verde" style="height: 40px" colspan="3">HORA</td>
            <td class="blanco" colspan="5"></td>
            <td class="verde" colspan="12">NOMBRE Y APELLIDO DEL PROFESIONAL</td>
            <td class="blanco"
                colspan="18"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
            <td class="verde" colspan="4">FIRMA</td>
            <td class="blanco" colspan="12"></td>
            <td class="verde" colspan="4">SELLO Y CÓDIGO</td>
            <td class="blanco" colspan="10"></td>
        </tr>
    </table>
    <table style="border: none">
        <tr style="height: 21px">
            <td class="s138">SNS-MSP / HCU-form.018A/2021</td>
            <td class="s140">TRANSANESTÉSICO (2)</td>
        </tr>
    </table>
</pagebreak>
</body>
</html>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('consentimiento_od' . '.pdf', 'I'); // D = Download, I = Inline
}
?>
