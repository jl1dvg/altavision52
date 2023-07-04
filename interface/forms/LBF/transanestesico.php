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
    <style type="text/css">
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

        .s10 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s84 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s91 {
            border-right: 1px SOLID #7f7f7f;
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

        .s102 {
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s135 {
            border-bottom: 2px SOLID #808080;
            border-right: 1px SOLID #808080;
            background-color: #ccffcc;
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

        .s86 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s34 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s100 {
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s104 {
            border-left: none;
            border-right: none;
            border-bottom: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
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
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s31 {
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 7pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s35 {
            border-bottom: 1px SOLID #c0c0c0;
            border-right: 2px SOLID #7f7f7f;
            border-left: 2px SOLID #7f7f7f;
            border-top: 2px SOLID #7f7f7f;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s137 {
            border-bottom: 2px SOLID #808080;
            border-right: 2px SOLID #808080;
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

        .s20 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s26 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s136 {
            border-bottom: 2px SOLID #808080;
            border-right: 1px SOLID #808080;
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

        .s101 {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
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

        .s6 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s14 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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
            font-size: 10pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s116 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 3px SOLID #a5a5a5;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s123 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 3px SOLID #a5a5a5;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
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
            font-size: 8pt;
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

        .s96 {
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s32 {
            background-color: #ffffff;
            text-align: right;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s55 {
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

        .s130 {
            border-bottom: 1px SOLID #808080;
            border-right: 2px SOLID #808080;
            background-color: #ccccff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
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

        .s83 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s119 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 1px SOLID #7f7f7f;
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

        .s112 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 3px SOLID #a5a5a5;
            background-color: #ccccff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
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

        .s13 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s15 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s3 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            height: 15px;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
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

        .s33 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s106 {
            border-left: none;
            border-bottom: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s97 {
            border-right: 1px SOLID #7f7f7f;
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

        .s12 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
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

        .s76 {
            border-bottom: 1px SOLID #7f7f7f;
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

        .s5 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s115 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 3px SOLID #a5a5a5;
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

        .s134 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 2px SOLID #808080;
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

        .s113 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s133 {
            border-bottom: 1px SOLID #808080;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s87 {
            border-bottom: 2px SOLID #7f7f7f;
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

        .s118 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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

        .s23 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
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

        .s7 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s30 {
            border-left: none;
            border-right: none;
            border-bottom: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 7pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s111 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 3px SOLID #a5a5a5;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s4 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s80 {
            border-bottom: 2px SOLID #7f7f7f;
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

        .s128 {
            border-bottom: 2px SOLID #808080;
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

        .s121 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 1px SOLID #7f7f7f;
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

        .s132 {
            border-bottom: 1px SOLID #808080;
            border-right: 1px SOLID transparent;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s1 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
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

        .s95 {
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s117 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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

        .s21 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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

        .s9 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s28 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID transparent;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 7pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s82 {
            border-bottom: 1px SOLID #7f7f7f;
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

        .s78 {
            border-bottom: 3px SOLID #000000;
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

        .s89 {
            border-bottom: 2px SOLID #7f7f7f;
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

        .s24 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s25 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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

        .s16 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s17 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px solid #7f7f7f;
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

        .s22 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ccffcc;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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

        .s122 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s79 {
            border-bottom: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
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

        .s11 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
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

        .s85 {
            border-bottom: 2px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s88 {
            border-bottom: 2px SOLID #7f7f7f;
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

        .s29 {
            border-right: none;
            border-bottom: 2px SOLID #7f7f7f;
            background-color: #ffcc00;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 7pt;
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

        .s114 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 3px SOLID #a5a5a5;
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

        .s139 {
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: top;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s18 {
            border-bottom: 2px SOLID #7f7f7f;
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

        .s19 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s81 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
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

        .s0 {
            border-bottom: 1px SOLID #7f7f7f;
            border-top: 2px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            border-left: 2px SOLID #7f7f7f;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s99 {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s129 {
            border-bottom: 2px SOLID #808080;
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

        .s120 {
            border-bottom: 3px SOLID #a5a5a5;
            border-right: 3px SOLID #a5a5a5;
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

        .s138 {
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 8pt;
            vertical-align: top;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s8 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s27 {
            border-bottom: 2px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            color: #000000;
            font-family: 'Arial';
            font-size: 7pt;
            vertical-align: bottom;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s105 {
            border-left: none;
            border-bottom: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s140 {
            background-color: #ffffff;
            text-align: right;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
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

        .s98 {
            border-bottom: 1px SOLID #7f7f7f;
            border-right: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
            vertical-align: middle;
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
            font-size: 6pt;
            vertical-align: middle;
            white-space: nowrap;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s103 {
            border-right: none;
            border-bottom: 1px SOLID #7f7f7f;
            background-color: #ffffff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 6pt;
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

        .s94 {
            border-bottom: 1px SOLID #7f7f7f;
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
            font-size: 6pt;
            vertical-align: bottom;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s131 {
            border-bottom: 1px SOLID #808080;
            border-right: 2px SOLID #808080;
            background-color: #ccccff;
            text-align: left;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 12pt;
            vertical-align: middle;
            white-space: normal;
            overflow: hidden;
            word-wrap: break-word;
            direction: ltr;
            padding: 0px 3px 0px 3px;
        }

        .s2 {
            border-top: 2px SOLID #7f7f7f;
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
            height: 10px;
        }

        .s93 {
            border-right: 1px SOLID #7f7f7f;
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

        .s90 {
            border-bottom: 2px SOLID #7f7f7f;
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
    </style>
</head>
<body>
<table cellspacing="0"
       cellpadding="0">
    <thead>
    <tr style="display: none">
        <th class="row-header freezebar-origin-ltr"></th>
        <th id="1547362917C0" style="width:13px;color: white;" class="column-headers-background">A</th>
        <th id="1547362917C1" style="width:13px;color: white;" class="column-headers-background">B</th>
        <th id="1547362917C2" style="width:13px;color: white;" class="column-headers-background">C</th>
        <th id="1547362917C3" style="width:13px;color: white;" class="column-headers-background">D</th>
        <th id="1547362917C4" style="width:13px;color: white;" class="column-headers-background">E</th>
        <th id="1547362917C5" style="width:13px;color: white;" class="column-headers-background">F</th>
        <th id="1547362917C6" style="width:13px;color: white;" class="column-headers-background">G</th>
        <th id="1547362917C7" style="width:13px;color: white;" class="column-headers-background">H</th>
        <th id="1547362917C8" style="width:13px;color: white;" class="column-headers-background">I</th>
        <th id="1547362917C9" style="width:13px;color: white;" class="column-headers-background">J</th>
        <th id="1547362917C10" style="width:13px;color: white;" class="column-headers-background">K</th>
        <th id="1547362917C11" style="width:13px;color: white;" class="column-headers-background">L</th>
        <th id="1547362917C12" style="width:13px;color: white;" class="column-headers-background">M</th>
        <th id="1547362917C13" style="width:13px;color: white;" class="column-headers-background">N</th>
        <th id="1547362917C14" style="width:13px;color: white;" class="column-headers-background">O</th>
        <th id="1547362917C15" style="width:13px;color: white;" class="column-headers-background">P</th>
        <th id="1547362917C16" style="width:13px;color: white;" class="column-headers-background">Q</th>
        <th id="1547362917C17" style="width:13px;color: white;" class="column-headers-background">R</th>
        <th id="1547362917C18" style="width:13px;color: white;" class="column-headers-background">S</th>
        <th id="1547362917C19" style="width:13px;color: white;" class="column-headers-background">T</th>
        <th id="1547362917C20" style="width:13px;color: white;" class="column-headers-background">U</th>
        <th id="1547362917C21" style="width:13px;color: white;" class="column-headers-background">V</th>
        <th id="1547362917C22" style="width:13px;color: white;" class="column-headers-background">W</th>
        <th id="1547362917C23" style="width:13px;color: white;" class="column-headers-background">X</th>
        <th id="1547362917C24" style="width:13px;color: white;" class="column-headers-background">Y</th>
        <th id="1547362917C25" style="width:13px;color: white;" class="column-headers-background">Z</th>
        <th id="1547362917C26" style="width:16px;color: white;" class="column-headers-background">AA</th>
        <th id="1547362917C27" style="width:18px;color: white;" class="column-headers-background">AB</th>
        <th id="1547362917C28" style="width:18px;color: white;" class="column-headers-background">AC</th>
        <th id="1547362917C29" style="width:24px;color: white;" class="column-headers-background">AD</th>
        <th id="1547362917C30" style="width:13px;color: white;" class="column-headers-background">AE</th>
        <th id="1547362917C31" style="width:13px;color: white;" class="column-headers-background">AF</th>
        <th id="1547362917C32" style="width:13px;color: white;" class="column-headers-background">AG</th>
        <th id="1547362917C33" style="width:13px;color: white;" class="column-headers-background">AH</th>
        <th id="1547362917C34" style="width:13px;color: white;" class="column-headers-background">AI</th>
        <th id="1547362917C35" style="width:13px;color: white;" class="column-headers-background">AJ</th>
        <th id="1547362917C36" style="width:13px;color: white;" class="column-headers-background">AK</th>
        <th id="1547362917C37" style="width:13px;color: white;" class="column-headers-background">AL</th>
        <th id="1547362917C38" style="width:13px;color: white;" class="column-headers-background">AM</th>
        <th id="1547362917C39" style="width:13px;color: white;" class="column-headers-background">AN</th>
        <th id="1547362917C40" style="width:13px;color: white;" class="column-headers-background">AO</th>
        <th id="1547362917C41" style="width:13px;color: white;" class="column-headers-background">AP</th>
        <th id="1547362917C42" style="width:16px;color: white;" class="column-headers-background">AQ</th>
        <th id="1547362917C43" style="width:13px;color: white;" class="column-headers-background">AR</th>
        <th id="1547362917C44" style="width:13px;color: white;" class="column-headers-background">AS</th>
        <th id="1547362917C45" style="width:13px;color: white;" class="column-headers-background">AT</th>
        <th id="1547362917C46" style="width:13px;color: white;" class="column-headers-background">AU</th>
        <th id="1547362917C47" style="width:13px;color: white;" class="column-headers-background">AV</th>
        <th id="1547362917C48" style="width:13px;color: white;" class="column-headers-background">AW</th>
        <th id="1547362917C49" style="width:13px;color: white;" class="column-headers-background">AX</th>
        <th id="1547362917C50" style="width:13px;color: white;" class="column-headers-background">AY</th>
        <th id="1547362917C51" style="width:13px;color: white;" class="column-headers-background">AZ</th>
        <th id="1547362917C52" style="width:13px;color: white;" class="column-headers-background">BA</th>
        <th id="1547362917C53" style="width:13px;color: white;" class="column-headers-background">BB</th>
        <th id="1547362917C54" style="width:13px;color: white;" class="column-headers-background">BC</th>
        <th id="1547362917C55" style="width:13px;color: white;" class="column-headers-background">BD</th>
        <th id="1547362917C56" style="width:13px;color: white;" class="column-headers-background">BE</th>
        <th id="1547362917C57" style="width:13px;color: white;" class="column-headers-background">BF</th>
        <th id="1547362917C58" style="width:13px;color: white;" class="column-headers-background">BG</th>
        <th id="1547362917C59" style="width:13px;color: white;" class="column-headers-background">BH</th>
        <th id="1547362917C60" style="width:13px;color: white;" class="column-headers-background">BI</th>
        <th id="1547362917C61" style="width:13px;color: white;" class="column-headers-background">BJ</th>
        <th id="1547362917C62" style="width:13px;color: white;" class="column-headers-background">BK</th>
        <th id="1547362917C63" style="width:13px;color: white;" class="column-headers-background">BL</th>
        <th id="1547362917C64" style="width:13px;color: white;" class="column-headers-background">BM</th>
        <th id="1547362917C65" style="width:13px;color: white;" class="column-headers-background">BN</th>
        <th id="1547362917C66" style="width:13px;color: white;" class="column-headers-background">BO</th>
        <th id="1547362917C67" style="width:16px;color: white;" class="column-headers-background">BP</th>
        <th id="1547362917C68" style="width:13px;color: white;" class="column-headers-background">BQ</th>
        <th id="1547362917C69" style="width:13px;color: white;" class="column-headers-background">BR</th>
        <th id="1547362917C70" style="width:13px;color: white;" class="column-headers-background">BS</th>
        <th id="1547362917C71" style="width:13px;color: white;" class="column-headers-background">BT</th>
        <th id="1547362917C72" style="width:13px;color: white;" class="column-headers-background">BU</th>
        <th id="1547362917C73" style="width:13px;color: white;" class="column-headers-background">BV</th>
        <th id="1547362917C74" style="width:13px;color: white;" class="column-headers-background">BW</th>
        <th id="1547362917C75" style="width:13px;color: white;" class="column-headers-background">BX</th>
        <th id="1547362917C76" style="width:13px;color: white;" class="column-headers-background">BY</th>
        <th id="1547362917C77" style="width:13px;color: white;" class="column-headers-background">BZ</th>
        <th id="1547362917C78" style="width:13px;color: white;" class="column-headers-background">CA</th>
        <th id="1547362917C79" style="width:13px;color: white;" class="column-headers-background">CB</th>
        <th id="1547362917C80" style="width:13px;color: white;" class="column-headers-background">CC</th>
        <th id="1547362917C81" style="width:13px;color: white;" class="column-headers-background">CD</th>
        <th id="1547362917C82" style="width:13px;color: white;" class="column-headers-background">CE</th>
        <th id="1547362917C83" style="width:13px;color: white;" class="column-headers-background">CF</th>
        <th id="1547362917C84" style="width:13px;color: white;" class="column-headers-background">CG</th>
        <th id="1547362917C85" style="width:13px;color: white;" class="column-headers-background">CH</th>
        <th id="1547362917C86" style="width:13px;color: white;" class="column-headers-background">CI</th>
        <th id="1547362917C87" style="width:13px;color: white;" class="column-headers-background">CJ</th>
        <th id="1547362917C88" style="width:13px;color: white;" class="column-headers-background">CK</th>
        <th id="1547362917C89" style="width:13px;color: white;" class="column-headers-background">CL</th>
        <th id="1547362917C90" style="width:13px;color: white;" class="column-headers-background">CM</th>
        <th id="1547362917C91" style="width:13px;color: white;" class="column-headers-background">CN</th>
        <th id="1547362917C92" style="width:15px;color: white;" class="column-headers-background">CO</th>
        <th id="1547362917C93" style="width:13px;color: white;" class="column-headers-background">CP</th>
        <th id="1547362917C94" style="width:13px;color: white;" class="column-headers-background">CQ</th>
        <th id="1547362917C95" style="width:13px;color: white;" class="column-headers-background">CR</th>
        <th id="1547362917C96" style="width:13px;color: white;" class="column-headers-background">CS</th>
        <th id="1547362917C97" style="width:13px;color: white;" class="column-headers-background">CT</th>
        <th id="1547362917C98" style="width:13px;color: white;" class="column-headers-background">CU</th>
        <th id="1547362917C99" style="width:13px;color: white;" class="column-headers-background">CV</th>
        <th id="1547362917C100" style="width:13px;color: white;" class="column-headers-background">CW</th>
        <th id="1547362917C101" style="width:13px;color: white;" class="column-headers-background">CX</th>
        <th id="1547362917C102" style="width:13px;color: white;" class="column-headers-background">CY</th>
        <th id="1547362917C103" style="width:13px;color: white;" class="column-headers-background">CZ</th>
        <th id="1547362917C104" style="width:13px;color: white;" class="column-headers-background">DA</th>
        <th id="1547362917C105" style="width:13px;color: white;" class="column-headers-background">DB</th>
        <th id="1547362917C106" style="width:13px;color: white;" class="column-headers-background">DC</th>
        <th id="1547362917C107" style="width:13px;color: white;" class="column-headers-background">DD</th>
        <th id="1547362917C108" style="width:13px;color: white;" class="column-headers-background">DE</th>
        <th id="1547362917C109" style="width:13px;color: white;" class="column-headers-background">DF</th>
        <th id="1547362917C110" style="width:13px;color: white;" class="column-headers-background">DG</th>
        <th id="1547362917C111" style="width:13px;color: white;" class="column-headers-background">DH</th>
        <th id="1547362917C112" style="width:13px;color: white;" class="column-headers-background">DI</th>
        <th id="1547362917C113" style="width:13px;color: white;" class="column-headers-background">DJ</th>
        <th id="1547362917C114" style="width:16px;color: white;" class="column-headers-background">DK</th>
        <th id="1547362917C115" style="width:13px;color: white;" class="column-headers-background">DL</th>
        <th id="1547362917C116" style="width:13px;color: white;" class="column-headers-background">DM</th>
        <th id="1547362917C117" style="width:13px;color: white;" class="column-headers-background">DN</th>
        <th id="1547362917C118" style="width:13px;color: white;" class="column-headers-background">DO</th>
        <th id="1547362917C119" style="width:13px;color: white;" class="column-headers-background">DP</th>
        <th id="1547362917C120" style="width:13px;color: white;" class="column-headers-background">DQ</th>
        <th id="1547362917C121" style="width:13px;color: white;" class="column-headers-background">DR</th>
        <th id="1547362917C122" style="width:13px;color: white;" class="column-headers-background">DS</th>
        <th id="1547362917C123" style="width:13px;color: white;" class="column-headers-background">DT</th>
        <th id="1547362917C124" style="width:13px;color: white;" class="column-headers-background">DU</th>
        <th id="1547362917C125" style="width:13px;color: white;" class="column-headers-background">DV</th>
        <th id="1547362917C126" style="width:13px;color: white;" class="column-headers-background">DW</th>
        <th id="1547362917C127" style="width:13px;color: white;" class="column-headers-background">DX</th>
        <th id="1547362917C128" style="width:13px;color: white;" class="column-headers-background">DY</th>
        <th id="1547362917C129" style="width:13px;color: white;" class="column-headers-background">DZ</th>
        <th id="1547362917C130" style="width:13px;color: white;" class="column-headers-background">EA</th>
        <th id="1547362917C131" style="width:13px;color: white;" class="column-headers-background">EB</th>
        <th id="1547362917C132" style="width:13px;color: white;" class="column-headers-background">EC</th>
        <th id="1547362917C133" style="width:13px;color: white;" class="column-headers-background">ED</th>
        <th id="1547362917C134" style="width:13px;color: white;" class="column-headers-background">EE</th>
        <th id="1547362917C135" style="width:13px;color: white;" class="column-headers-background">EF</th>
        <th id="1547362917C136" style="width:13px;color: white;" class="column-headers-background">EG</th>
        <th id="1547362917C137" style="width:13px;color: white;" class="column-headers-background">EH</th>
    </tr>
    </thead>
    <tbody>
    <tr style="height: 18px">
        <th id="1547362917R0" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">1</div>
        </th>
        <td class="s35" colspan="138">A. DATOS DEL ESTABLECIMIENTO Y USUARIO</td>
    </tr>
    <tr style="height: 16px">
        <th id="1547362917R1" style="height: 16px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 16px">2</div>
        </th>
        <td class="s1" colspan="27">INSTITUCIÓN DEL SISTEMA</td>
        <td class="s1" colspan="45">ESTABLECIMIENTO DE SALUD</td>
        <td class="s1" colspan="40">NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
        <td class="s1" colspan="26">NÚMERO DE ARCHIVO</td>
    </tr>
    <tr style="height: 16px">
        <th id="1547362917R2" style="height: 16px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 16px">3</div>
        </th>
        <td class="s3" colspan="27"><?php echo $titleres['pricelevel']; ?></td>
        <td class="s3" colspan="45">AlTA VISION</td>
        <td class="s3" colspan="40"><?php echo $titleres['pubpid']; ?></td>
        <td class="s3" colspan="26"><?php echo $titleres['pubpid']; ?></td>
    </tr>
    <tr style="height: 16px">
        <th id="1547362917R3" style="height: 16px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 16px">4</div>
        </th>
        <td class="s5" colspan="23" rowspan="2">PRIMER APELLIDO</td>
        <td class="s5" colspan="21" rowspan="2">SEGUNDO APELLIDO</td>
        <td class="s5" colspan="25" rowspan="2">PRIMER NOMBRE</td>
        <td class="s5" colspan="24" rowspan="2">SEGUNDO NOMBRE</td>
        <td class="s5" colspan="8" rowspan="2">SEXO</td>
        <td class="s1" colspan="16" rowspan="2">FECHA NACIMIENTO</td>
        <td class="s5" colspan="12" rowspan="2">EDAD</td>
        <td class="s6" colspan="9">CONDICIÓN EDAD</td>
    </tr>
    <tr style="height: 11px">
        <th id="1547362917R4" style="height: 11px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 11px">5</div>
        </th>
        <td class="s7" colspan="2">H</td>
        <td class="s7" colspan="2">D</td>
        <td class="s7" colspan="2">M</td>
        <td class="s8" colspan="3">A</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R5" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">6</div>
        </th>
        <td class="s3" colspan="23"><?php echo $titleres['lname']; ?></td>
        <td class="s3" colspan="21"><?php echo $titleres['lname2']; ?></td>
        <td class="s3" colspan="25"><?php echo $titleres['fname']; ?></td>
        <td class="s3" colspan="24"><?php echo $titleres['mname']; ?></td>
        <td class="s3" colspan="8"><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td class="s3" colspan="16"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td class="s3" colspan="12"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td class="s3" colspan="2"></td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="2"></td>
        <td class="s4" colspan="3">X</td>
    </tr>
    <tr style="height: 17px">
        <th id="1547362917R6" style="height: 17px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 17px">7</div>
        </th>
        <td class="s10" colspan="9">FECHA:</td>
        <td class="s11" colspan="18">
            <?php echo date('d/m/Y', strtotime(fetchDateByEncounter($encounter))); ?>
        </td>
        <td class="s12" colspan="6">TALLA (cm)</td>
        <td class="s13" colspan="14"></td>
        <td class="s12" colspan="14">PESO (kg)</td>
        <td class="s13" colspan="11"></td>
        <td class="s12" colspan="4">IMC</td>
        <td class="s11" colspan="18"></td>
        <td class="s12" colspan="14">GRUPO Y FACTOR</td>
        <td class="s13" colspan="4"></td>
        <td class="s12" colspan="20">CONSENTIMIENTO INFORMADO</td>
        <td class="s14">SI</td>
        <td class="s15">X</td>
        <td class="s10" colspan="2">NO</td>
        <td class="s16" colspan="2"></td>
    </tr>
    <!-- break -->
    <tr style="height: 12px">
        <th id="1547362917R7" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">8</div>
        </th>
        <td class="s17" colspan="138"></td>
    </tr>
    <tr style="height: 18px">
        <th id="1547362917R8" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">9</div>
        </th>
        <td class="s19" colspan="138">B. SERVICIO Y PRIORIDAD DE ATENCIÓN</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R9" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">10</div>
        </th>
        <td class="s20" colspan="13">DIAGNÓSTICO<br> PREOPERATORIO</td>
        <td class="s21" style="text-align: left"
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
        <td class="s20" colspan="3">CIE</td>
        <td class="s9" style="text-align: left" colspan="16">
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
        <td class="s20" colspan="11">CIRUGÍA PROPUESTA</td>
        <td class="s3" style="text-align: left" colspan="38">
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
        <td class="s22" colspan="8">ESPECIALIDAD</td>
        <td class="s23" colspan="11" rowspan="4">PRIORIDAD</td>
        <td class="s3" colspan="6">EMERGENTE</td>
        <td class="s4" colspan="3"></td>
    </tr>
    <tr style="height: 11px">
        <th id="1547362917R10" style="height: 11px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 11px">11</div>
        </th>
        <td class="s20" colspan="13">DIAGNÓSTICO POSTOPERATORIO</td>
        <td class="s24" style="text-align: left" colspan="29"><?php
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
        <td class="s20" colspan="3">CIE</td>
        <td class="s9" style="text-align: left" colspan="16"><?php
            echo substr($prot_dxpost, 6);
            if ($prot_dxpost2 !== null) {
                echo ", " . substr($prot_dxpost2, 6);
            }

            if ($prot_dxpost3 !== null) {
                echo ", " . substr($prot_dxpost3, 6);
            }
            ?>
        </td>
        <td class="s20" colspan="11">CIRUGÍA REALIZADA</td>
        <td class="s9" style="text-align: left" colspan="38">
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
        <td class="s3" colspan="8">Oftalmología</td>
        <td class="s3" colspan="6">URGENTE</td>
        <td class="s4" colspan="3"></td>
    </tr>
    <tr style="height: 11px">
        <th id="1547362917R11" style="height: 11px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 11px">12</div>
        </th>
        <td class="s20" colspan="7">ANESTESIÓLOGO</td>
        <td class="s21" style="text-align: left"
            colspan="35"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
        <td class="s20" colspan="6">AYUDANTE (S)</td>
        <td class="s9" colspan="24"></td>
        <td class="s20" colspan="19">INSTRUMENTISTA</td>
        <td class="s9" colspan="19">
            <?php
            $instrumentistaOK = getFieldValue($form_id, "Prot_Instrumentistas");
            if ($instrumentistaOK == 'Si') {
                echo "Dr. Jorge Luis de Vera";
            } ?>
        </td>
        <td class="s22" colspan="8">QUIRÓFANO</td>
        <td class="s3" colspan="6">ELECTIVO</td>
        <td class="s4" colspan="3">X</td>
    </tr>
    <tr style="height: 11px">
        <th id="1547362917R12" style="height: 11px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 11px">13</div>
        </th>
        <td class="s25" colspan="7">CIRUJANO</td>
        <td class="s11" style="text-align: left" colspan="35"><?php echo $providerNAME; ?></td>
        <td class="s25" colspan="6">AYUDANTE (S)</td>
        <td class="s26" colspan="24"><?php echo getProviderName(getFieldValue($form_id, "Prot_ayudante")); ?></td>
        <td class="s25" colspan="19">CIRCULANTE</td>
        <td class="s26" colspan="19"><?php
            echo getFieldValue($form_id, "Prot_opr") == "avastin" ? " " : "Lcda. Solange Vega";
            ?></td>
        </td>
        <td class="s11" colspan="8"></td>
        <td class="s11" colspan="6"></td>
        <td class="s16" colspan="3"></td>
    </tr>
    <!-- break -->
    <tr style="height: 12px">
        <th id="1547362917R13" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">14</div>
        </th>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s28"></td>
        <td class="s29 softmerge">
            <div class="softmerge-inner" style="width:40px;left:-1px">OTROS</div>
        </td>
        <td class="s30"></td>
        <td class="s31"></td>
        <td class="s31"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
        <td class="s27"></td>
    </tr>
    <tr style="height: 18px">
        <th id="1547362917R14" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">15</div>
        </th>
        <td class="s35" colspan="138">C. REGIÓN OPERATORIA</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R15" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">16</div>
        </th>
        <td class="s9" colspan="6"></td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="8"></td>
        <td class="s9" colspan="2"></td>
        <td class="s3" colspan="5">CABEZA</td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="3">ORGANOS DE LOS SENTIDOS</td>
        <td class="s3" colspan="2"></td>
        <td class="s9" colspan="5">CUELLO</td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="8">COLUMNA</td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="10">TORAX</td>
        <td class="s9" colspan="2"></td>
        <td class="s3" colspan="6">ABDOMEN</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="7">PELVIS</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="7">EXTREMIDADES SUPERIORES</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="15">EXTREMIDADES INFERIORES</td>
        <td class="s32" colspan="2"></td>
        <td class="s3" colspan="8"></td>
        <td class="s32" colspan="2"></td>
        <td class="s3" colspan="4">PERINEAL</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="6"></td>
        <td class="s32" colspan="2"></td>
        <td class="s3" colspan="7"></td>
        <td class="s32" colspan="2"></td>
        <td class="s33" colspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R16" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">17</div>
        </th>
        <td class="s11" colspan="8"></td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="8"></td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="3"></td>
        <td class="s11" colspan="10"></td>
        <td class="s34" colspan="105"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R17" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">18</div>
        </th>
        <td class="s17" colspan="138"></td>
    </tr>
    <tr style="height: 18px">
        <th id="1547362917R18" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">19</div>
        </th>
        <td class="s35" colspan="138">D. REGISTRO TRANSANESTÉSICO</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R19" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">20</div>
        </th>
        <td class="s36" colspan="30">AGENTE INHALATORIO / INFUSIÓN CONTINUA</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s40">é</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R20" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">21</div>
        </th>
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
        <th id="1547362917R21" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">22</div>
        </th>
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
        <th id="1547362917R22" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">23</div>
        </th>
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
        <th id="1547362917R23" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">24</div>
        </th>
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
        <th id="1547362917R24" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">25</div>
        </th>
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
        <th id="1547362917R25" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">26</div>
        </th>
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
        <th id="1547362917R26" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">27</div>
        </th>
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
        <th id="1547362917R27" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">28</div>
        </th>
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
        <th id="1547362917R28" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">29</div>
        </th>
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
        <th id="1547362917R29" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">30</div>
        </th>
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
        <th id="1547362917R30" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">31</div>
        </th>
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
        <th id="1547362917R31" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">32</div>
        </th>
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
        <th id="1547362917R32" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">33</div>
        </th>
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
        <th id="1547362917R33" style="height: 15px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 15px">34</div>
        </th>
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
        <th id="1547362917R34" style="height: 15px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 15px">35</div>
        </th>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s39">é</td>
        <td class="s37"></td>
        <td class="s37"></td>
        <td class="s38" colspan="2">15</td>
        <td class="s38"></td>
        <td class="s38" colspan="2">30</td>
        <td class="s37"></td>
        <td class="s38" colspan="2">45</td>
        <td class="s38"></td>
        <td class="s54">é</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R35" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">36</div>
        </th>
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
        <td class="s59">¯</td>
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
        <th id="1547362917R36" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">37</div>
        </th>
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
        <th id="1547362917R37" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">38</div>
        </th>
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
        <th id="1547362917R38" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">39</div>
        </th>
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
        <th id="1547362917R39" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">40</div>
        </th>
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
        <th id="1547362917R40" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">41</div>
        </th>
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
        <th id="1547362917R41" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">42</div>
        </th>
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
        <th id="1547362917R42" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">43</div>
        </th>
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
            <div style="width:13px;height:12px;"><img
                    src="https://lh3.googleusercontent.com/docs/ADP-6oHRGjDp4l1KgR_H5SO4wO2x5CouoHgVqawQ225rWyzNYgOSySZUiR6boOKR-63SXSm6AZLYo3qH_YB3ZkDJKJR-wRrEW0chrsJz-KfbjkH0YXNsesC070wdVb8RTGY3835hX-B50slattGdjaUeNFaHFV-puhPkzuN1IwbJ6VJ3tZX4HMLPwX0m9c6b4_BbNw1tm1Y9Z1KcpEfWzzCqL8rHehsReYIGAbuggOVCGwx2UXGH1rwgmckm3MexOgkaO5QMwbaGedJQGRiO1B34xNsGH82GpAkTz_ZM_zY3fS9zHG5bvMaslL0tBdS8CdrYSo36Xqms5p_JIkhoUBVfzPT43MAQ_qefRqdH6XQRwbiCADJfEvxLYeBcXyjgLVO0wPOfdCpZY0YhA82CVqmcCisQ4LCg85US4vkL9-P2YF7i3K-fgX_9aKGaMz_yB_HTNSZsdCaI0mdvJ6zXWCvtd9ZO4DMv3idH4t_3M6fOQdyxI0VBuyrtxb7ul0ks-n7NRUJcvYNRkiPfUYtYKxzexFzRg6uce2G5krYsiWLIbne9l7TcXCvJcLqyJB2QsOGd1Kxaeclc_DDIJfApp_mq2wP-NLvRQEW7NO5mZuVKOHBHqJm1KV8Wdyh3VO9JDtNx3a4FBG-9CGvL7rtaDvO79J24E_fhJ8LJP-WYUucDTjvJYeHF5uYBo_snRJz3kz2UxRMSbwdxBrzJt53dTpTnJOuVL-ZTEfVE_SuUCA1NzkeUGCOvZODwkB0TMwTQTvc9A36aBUmi2XsZTPWtnoBETiZxfwAVwvgI2jnWNS8T3MwiZe_LpuL4wx_ikOKpK_OvONZADtqJsBFgkjaOpzOZUrvGaGGhuOzUd0uppK5LN_OWs8tGZZyMjNoHCZnXWAO6iiOS83f4EdyFIb36wqTqiWd8GckjXh6DLMeIY9FgRbcTTnBCXb8P1JqbEy6Mk7SL0MoaWWxZMOyj2kQ6La3kODnEHX6Tb9U9vNYGv2k0t9_nsA0fMRAc9MTGA_62t3-J44K60ytMX1Fkr_Geu5b_Gh9uyXpx4G56gymihflGMo3tXeXSLDVhxjTdn8q4=w13-h12"
                    style="width:inherit;height:inherit;object-fit:scale-down;object-position:left bottom;"/>
            </div>
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
        <th id="1547362917R43" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">44</div>
        </th>
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
            <div style="width:13px;height:12px;"><img
                    src="https://lh3.googleusercontent.com/docs/ADP-6oGN0UU9bjF-oXPv24dOtjcrXMEX5fktx9OrHoP5jplIrukHy35Xa097Q7umwo8jJNvpu30WBOWi3FhpTtWBlUnH-OB_aO_qQagWocKEw7s2w8kvoWyzQH5Gy3nX2nOWQXEPejb_JffxH7HpB0BFoyOFYNeGjEH1Z7nRFNuJcrYvK2s_HNpqyfLuFU4cOLr6J9QIb78K7VkDht6sXEoipLAifjidnJC8j0iQkkM_1MsvE2f0zFWy3yUhP8X3H9rl9YO6dBlGPd3bl3u5sR7zfG-CIOAV527ecsmGT0aix5ItnONIVUWGNaMLyqvf6n7xQ1RUZO6WM5EYphSl2h4Um7Tyvnds5jaraZu8aeExBNMjRa7HglCrWFOZrhAGUDr8guV1JSzWPOtXxt3nw9A-c2gn7BBWRPLFRaYFLibxiz31VXnRIhEbjzPbm-vq20b9-T3xal9PNMgYRngaLzVMdRJnHsIs23hCsyAQzUCccKUbcw-YiMp7Ae5kAnWDX1r2yGZbbNw4ELasIvWCoByr14BO-mVB3b_EmFMRckasoPy3fejnkvvL1SZ-QTebVxVxFUHNCiNGBejEkJsDinzoJMdAUnEGMKKa6aouBeu6OkzmVqGG30z4vrsx5J6PNsoc5fv2lNCGm3buw3RjDEroJIDUpQseDuXTxHAkJ390bRjhiQW4K1XIpHt03SOIUWsbTig8iDe6kxyM-aYNP-oM5HOKg3a25WEmLnOQl4t7GN-NCsa-mG_VoULALzyYB9j6ou5nGXePEruVz-uXo6JU2vMgrXPTRTY-lWwkeSCL6BrQu_nkJeKaEy8RH6Mtz65Ey8U_NzqR-f4oZPHcpjvqXzvPqF6iXco5aax-vCpTqlAN1cC6MCKPRhe4YbRVsml45SxWuP_e_mQA8Dqc96I0en04gA4EiWpQ-GrEGOfCenvz3iGjESS_Nov9xJ-zpNuvao76zMONikQ7QXALhyr9Cni5CjKlDGB6DSGpkueWqVkT0Tn4u5_3RtRFHpQkmduJ95jaAHB9IZPFiBlcKZIDZrU_-sX4a_P9ZRPgpQZQZMbRD37-2HIval4KA7wq=w13-h12"
                    style="width:inherit;height:inherit;object-fit:scale-down;object-position:left bottom;"/>
            </div>
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
        <th id="1547362917R44" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">45</div>
        </th>
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
        <td class="s66">
            <div style="width:13px;height:12px;"><img
                    src="https://lh3.googleusercontent.com/docs/ADP-6oGrK2dBemsjMUH6U-gJjGC8_DHJWtgbJXqowQemQ-BlJPC0tj1XPyKG9jv8_1Wn8Y7FkwVZlKqOMfYO0lzi1kjD9HUbsJjM837gf_nk3WT0Fbe5m3-EXSdUku3Y8kgqfI9gMuoaiHnIEpUR_12GsfJMghH7EQAVGB_bChs-1XfGzOSG3N9zZTrrGeTBlYJhw-3uwPnD0uO7Z1cyTkpO6L4X9pbuGmQn2_YUJk8dcEN5oBiezVq5lAeWx5C6olyy96SMnPH7XfWFMqVKi3lgxgHC6-64nLKYpaJh8urhWFOz2djDpbZcEce0aDy0cZSEC4FqEvglw1F2P_TzPAnnIeJiZVCCGZLpRU1zNxG-873lKv64vonIFa1sLiZ_hQ8rsawto41J0Z-p2tQDf4BSdTIupOSRFO-qu8LR8DSImhnkvwlo-oC0dJ3aImqYHIqm0wOioomvBHsIpuvdQOy-nIQjDnnsBbcrUiC2spAbDtygs6kRk_gfmCgGouIiDC9p8_kf0jJe1a0Y0z9V0J_sbpavceqxUsTGyVsnZ7bCVMkXDmxqjktVjS0vbwxkoP2-FsIa2teZJiBTk7K8njXkUWpXuAeWPo8FJ3lGY40zyjXF6ThuukK9OrIG6tDKQJpevP_Xo1rCY6UtH44B9Bp8_SGZyMIms6-tuG3urIgru0mH150Rv_VVTg1JtBhF9mxCMkzdR_1NLV82FhQpy3XBP5EEnqviZ1ylCY-kMMMqFH3QHt0hHTMTxYCitLkQrtvPohK4pl4QDVKFfOQMpVkziNK4qeP6hGsy5NaZnv5UCslBoNfCFQBjRWoezi0HhHOb0ScJPP_O3TNex0cRAMvM8Cyj-Xqr_kfrlSfxgcCGiqnvJNLGUc2g8SkM_2b26WtvWgjLUSdGrwPw5RN7nIUVI1AJt-D0EPymATg98zIyQV90doHnMaB3ZVwnjBO7AHlNuzwAvxduHz_g6Dcfg9QWZMLs38MqEs7zpZF6HI31foLXBydfgLXvkemMZ3agalBQ98BKhgCrvu2EbkliGWc0qu09t87OaFopEny4LTNQ8yY3tA-AaT79Xa16wm4t=w13-h12"
                    style="width:inherit;height:inherit;object-fit:scale-down;object-position:left bottom;"/>
            </div>
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
        <th id="1547362917R45" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">46</div>
        </th>
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
        <th id="1547362917R46" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">47</div>
        </th>
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
        <th id="1547362917R47" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">48</div>
        </th>
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
        <th id="1547362917R48" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">49</div>
        </th>
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
        <th id="1547362917R49" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">50</div>
        </th>
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
        <th id="1547362917R50" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">51</div>
        </th>
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
        <th id="1547362917R51" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">52</div>
        </th>
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
        <th id="1547362917R52" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">53</div>
        </th>
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
        <th id="1547362917R53" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">54</div>
        </th>
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
        <th id="1547362917R54" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">55</div>
        </th>
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
        <th id="1547362917R55" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">56</div>
        </th>
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
        <th id="1547362917R56" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">57</div>
        </th>
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
        <th id="1547362917R57" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">58</div>
        </th>
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
        <th id="1547362917R58" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">59</div>
        </th>
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
        <th id="1547362917R59" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">60</div>
        </th>
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
        <th id="1547362917R60" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">61</div>
        </th>
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
        <th id="1547362917R61" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">62</div>
        </th>
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
        <th id="1547362917R62" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">63</div>
        </th>
        <td class="s75" colspan="30" rowspan="3">DROGAS ADMINISTRADAS</td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
        <td class="s76" colspan="12" rowspan="3"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R63" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">64</div>
        </th>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R64" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">65</div>
        </th>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R65" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">66</div>
        </th>
        <td class="s77" colspan="30">POSICIÓN</td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
        <td class="s78" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R66" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">67</div>
        </th>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s79"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
        <td class="s80"></td>
    </tr>
    <tr style="height: 18px">
        <th id="1547362917R67" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">68</div>
        </th>
        <td class="s35" colspan="138">E. DROGAS ADMINISTRADAS</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R68" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">69</div>
        </th>
        <td class="s81">1</td>
        <td class="s24" colspan="18"></td>
        <td class="s81">5</td>
        <td class="s24" colspan="22"></td>
        <td class="s81">9</td>
        <td class="s81" colspan="24"></td>
        <td class="s81">13</td>
        <td class="s81" colspan="24"></td>
        <td class="s81">17</td>
        <td class="s81" colspan="21"></td>
        <td class="s81">21</td>
        <td class="s82" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R69" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">70</div>
        </th>
        <td class="s81">2</td>
        <td class="s24" colspan="18"></td>
        <td class="s81">6</td>
        <td class="s24" colspan="22"></td>
        <td class="s81">10</td>
        <td class="s81" colspan="24"></td>
        <td class="s81">14</td>
        <td class="s81" colspan="24"></td>
        <td class="s81">18</td>
        <td class="s81" colspan="21"></td>
        <td class="s81">22</td>
        <td class="s82" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R70" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">71</div>
        </th>
        <td class="s81">3</td>
        <td class="s24" colspan="18"></td>
        <td class="s81">7</td>
        <td class="s24" colspan="22"></td>
        <td class="s81">11</td>
        <td class="s81" colspan="24"></td>
        <td class="s81">15</td>
        <td class="s81" colspan="24"></td>
        <td class="s81">19</td>
        <td class="s81" colspan="21"></td>
        <td class="s83">23</td>
        <td class="s82" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R71" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">72</div>
        </th>
        <td class="s84">4</td>
        <td class="s85" colspan="18"></td>
        <td class="s84">8</td>
        <td class="s85" colspan="22"></td>
        <td class="s84">12</td>
        <td class="s84" colspan="24"></td>
        <td class="s84">16</td>
        <td class="s84" colspan="24"></td>
        <td class="s84">20</td>
        <td class="s84" colspan="21"></td>
        <td class="s86">24</td>
        <td class="s87" colspan="23"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R72" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">73</div>
        </th>
        <td class="s88"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s88"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s90"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s87"></td>
    </tr>
    <tr style="height: 17px">
        <th id="1547362917R73" style="height: 17px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 17px">74</div>
        </th>
        <td class="s35" colspan="138">F. TÉCNICA ANESTÉSICA</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R74" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">75</div>
        </th>
        <td class="s2" colspan="49">GENERAL</td>
        <td class="s71"></td>
        <td class="s71"></td>
        <td class="s91"></td>
        <td class="s2" colspan="50">REGIONAL</td>
        <td class="s92"></td>
        <td class="s92"></td>
        <td class="s93"></td>
        <td class="s94" colspan="33">SEDO - ANALGESIA</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R75" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">76</div>
        </th>
        <td class="s7" colspan="25">SISTEMA</td>
        <td class="s7" colspan="24">APARATO</td>
        <td class="s95"></td>
        <td class="s95"></td>
        <td class="s96"></td>
        <td class="s3" colspan="9">ASEPSIA CON:</td>
        <td class="s3" colspan="21"></td>
        <td class="s3" colspan="5">HABÓN CON:</td>
        <td class="s3" colspan="15"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s94" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R76" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">77</div>
        </th>
        <td class="s9" colspan="4">ABIERTO</td>
        <td class="s9"></td>
        <td class="s9" colspan="6">SEMICERRADO</td>
        <td class="s9"></td>
        <td class="s3" colspan="12">CERRADO</td>
        <td class="s9"></td>
        <td class="s9" colspan="9">CIRCUITO CIRCULAR</td>
        <td class="s3" colspan="5"></td>
        <td class="s3" colspan="6">UNIDIRECCIONAL</td>
        <td class="s3" colspan="4"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s24" colspan="9">LOCAL ASISTIDA</td>
        <td class="s24" colspan="21"></td>
        <td class="s3" colspan="5">INTRAVENOSA</td>
        <td class="s3" colspan="15"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s33" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R77" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">78</div>
        </th>
        <td class="s98" colspan="49">MANEJO DE VÍA AÉREA</td>
        <td class="s99"></td>
        <td class="s99"></td>
        <td class="s100"></td>
        <td class="s2" colspan="50">TRONCULAR</td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s33" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R78" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">79</div>
        </th>
        <td class="s21" colspan="13">MÁSCARA FACIAL</td>
        <td class="s3" colspan="4"></td>
        <td class="s3" colspan="13">SUPRAGLÓTICA</td>
        <td class="s3" colspan="3"></td>
        <td class="s3" colspan="13">TRAQUEOTOMO</td>
        <td class="s3" colspan="3"></td>
        <td class="s95"></td>
        <td class="s95"></td>
        <td class="s96"></td>
        <td class="s9" colspan="11">BLOQUEO DE NERVIO</td>
        <td class="s3" colspan="21"></td>
        <td class="s9" colspan="9">No. INTENTOS</td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s8" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R79" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">80</div>
        </th>
        <td class="s98" colspan="49">INTUBACIÓN</td>
        <td class="s99"></td>
        <td class="s99"></td>
        <td class="s100"></td>
        <td class="s9" colspan="11">BLOQUEO DEL PLEXO</td>
        <td class="s3" colspan="21"></td>
        <td class="s9" colspan="9">No. INTENTOS</td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s9"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s4" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R80" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">81</div>
        </th>
        <td class="s21" colspan="5">NASAL</td>
        <td class="s3" colspan="3"></td>
        <td class="s3" colspan="5">ORAL</td>
        <td class="s3" colspan="3"></td>
        <td class="s3" colspan="9">SUBMENTONEANA</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="9">VISIÓN DIRECTA</td>
        <td class="s3" colspan="4"></td>
        <td class="s3" colspan="5">A CIEGAS</td>
        <td class="s3" colspan="4"></td>
        <td class="s95"></td>
        <td class="s95"></td>
        <td class="s96"></td>
        <td class="s9" colspan="11">ANESTÉSICO LOCAL</td>
        <td class="s3" colspan="21"></td>
        <td class="s9" colspan="9">COADYUVANTE</td>
        <td class="s3" colspan="9"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s4" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R81" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">82</div>
        </th>
        <td class="s7" colspan="49">TIPO DE TUBO</td>
        <td class="s101"></td>
        <td class="s101"></td>
        <td class="s102"></td>
        <td class="s9" colspan="11">TIPO DE AGUJA</td>
        <td class="s3" colspan="21"></td>
        <td class="s9" colspan="9">EQUIPO</td>
        <td class="s3" colspan="9"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s8" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R82" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">83</div>
        </th>
        <td class="s3" colspan="8">CONVENCIONAL</td>
        <td class="s3" colspan="4"></td>
        <td class="s3" colspan="10">PREFORMADO ORAL</td>
        <td class="s3" colspan="4"></td>
        <td class="s9" colspan="9">PREFORMADO NASAL</td>
        <td class="s3" colspan="3"></td>
        <td class="s3" colspan="7">REFORZADO</td>
        <td class="s3" colspan="4"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s98" colspan="50">NEUROAXIAL</td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s4" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R83" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">84</div>
        </th>
        <td class="s3" colspan="8">DOBLE LUMEN</td>
        <td class="s3" colspan="3"></td>
        <td class="s3" colspan="7">DIÁMETRO</td>
        <td class="s3" colspan="4"></td>
        <td class="s3" colspan="3">BALÓN</td>
        <td class="s9">SI</td>
        <td class="s9"></td>
        <td class="s3" colspan="3">NO</td>
        <td class="s9"></td>
        <td class="s3" colspan="11">TAPONAMIENTO</td>
        <td class="s9">SI</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2">NO</td>
        <td class="s3" colspan="2"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s9" colspan="6">RAQUÍDEA</td>
        <td class="s9" colspan="4"></td>
        <td class="s9" colspan="5">EPIDURAL</td>
        <td class="s9" colspan="4"></td>
        <td class="s9" colspan="4">CAUDAL</td>
        <td class="s9" colspan="4"></td>
        <td class="s9" colspan="5"></td>
        <td class="s9" colspan="4">CATETER</td>
        <td class="s9" colspan="5"></td>
        <td class="s9" colspan="4"></td>
        <td class="s9" colspan="5"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s4" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R84" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">85</div>
        </th>
        <td class="s103 softmerge">
            <div class="softmerge-inner" style="width:128px;left:-1px">EQUIPO PARA INTUBACIÓN</div>
        </td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s104"></td>
        <td class="s105"></td>
        <td class="s106" colspan="39"></td>
        <td class="s99"></td>
        <td class="s99"></td>
        <td class="s100"></td>
        <td class="s9" colspan="6">TIPO AGUJA</td>
        <td class="s9" colspan="9"></td>
        <td class="s9" colspan="19">NÚMERO AGUJA</td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="9">No. INTENTOS</td>
        <td class="s9" colspan="5"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s8" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R85" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">86</div>
        </th>
        <td class="s3" colspan="5">CORMACK:</td>
        <td class="s3" colspan="2">I</td>
        <td class="s9"></td>
        <td class="s3" colspan="2">II</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2">III</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2">IV</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="9">NÚMERO DE INTENTOS</td>
        <td class="s9"></td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="4"></td>
        <td class="s3" colspan="3"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s9" colspan="6">BORBOTAJE</td>
        <td class="s9" colspan="3">SI</td>
        <td class="s9" colspan="2"></td>
        <td class="s9" colspan="2">NO</td>
        <td class="s9" colspan="2"></td>
        <td class="s3" colspan="4">ACCESO</td>
        <td class="s3" colspan="4">MEDIAL</td>
        <td class="s3" colspan="2"></td>
        <td class="s3" colspan="4">LATERAL</td>
        <td class="s3" colspan="3"></td>
        <td class="s3" colspan="7">SITIO DE PUNCIÓN</td>
        <td class="s3" colspan="11"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s8" colspan="33"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R86" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">87</div>
        </th>
        <td class="s7" colspan="21">INDUCCIÓN</td>
        <td class="s7" colspan="28">MANTENIMIENTO</td>
        <td class="s101"></td>
        <td class="s101"></td>
        <td class="s102"></td>
        <td class="s9" colspan="11"></td>
        <td class="s9" colspan="21"></td>
        <td class="s9" colspan="9"></td>
        <td class="s9" colspan="9"></td>
        <td class="s58"></td>
        <td class="s58"></td>
        <td class="s97"></td>
        <td class="s8" colspan="33">ESCALA DE RAMSAY</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R87" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">88</div>
        </th>
        <td class="s11" colspan="9">INHALATORIA</td>
        <td class="s11" colspan="3"></td>
        <td class="s11" colspan="6">INTRAVENOSA</td>
        <td class="s11" colspan="3"></td>
        <td class="s11" colspan="6">INHALATORIA</td>
        <td class="s11" colspan="3"></td>
        <td class="s11" colspan="6">INTRAVENOSA</td>
        <td class="s11" colspan="4"></td>
        <td class="s11" colspan="6">BALANCEADA</td>
        <td class="s11" colspan="3"></td>
        <td class="s17"></td>
        <td class="s17"></td>
        <td class="s26"></td>
        <td class="s26" colspan="11">DERMATOMA</td>
        <td class="s26" colspan="21"></td>
        <td class="s26" colspan="9">POSICIÓN</td>
        <td class="s26" colspan="9"></td>
        <td class="s17"></td>
        <td class="s17"></td>
        <td class="s26"></td>
        <td class="s11" colspan="4">1</td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="4">2</td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="4">3</td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="4">4</td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="3">5</td>
        <td class="s11" colspan="2"></td>
        <td class="s11" colspan="2">6</td>
        <td class="s16" colspan="2"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R88" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">89</div>
        </th>
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
    <tr style="height: 18px">
        <th id="1547362917R89" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">90</div>
        </th>
        <td class="s35" colspan="42">G. ACCESOS VASCULARES</td>
        <td class="s35" colspan="48">H. REPOSICIÓN VOLÉMICA (ml)</td>
        <td class="s35" colspan="48">I. PERDIDAS</td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R90" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">91</div>
        </th>
        <td class="s113" colspan="6">TIPO</td>
        <td class="s2" colspan="7">CALIBRE</td>
        <td class="s114" colspan="29">SITIO</td>
        <td class="s24" colspan="12">DEXTROSA 5%</td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12">SANGRE</td>
        <td class="s115" colspan="12"></td>
        <td class="s24" colspan="12">SANGRADO</td>
        <td class="s24" colspan="12"></td>
        <td class="s32" colspan="12"></td>
        <td class="s116" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R91" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">92</div>
        </th>
        <td class="s117" colspan="6">IV PERIFÉRICO 1</td>
        <td class="s24" colspan="7"></td>
        <td class="s115" colspan="29"></td>
        <td class="s24" colspan="12">DEXTROSA 10%</td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12">PLASMA</td>
        <td class="s115" colspan="12"></td>
        <td class="s24" colspan="12">DIURESIS</td>
        <td class="s24" colspan="12"></td>
        <td class="s32" colspan="12"></td>
        <td class="s116" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R92" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">93</div>
        </th>
        <td class="s117" colspan="6">IV PERIFÉRICO 2</td>
        <td class="s24" colspan="7"></td>
        <td class="s115" colspan="29"></td>
        <td class="s24" colspan="12">DEXTROSA 50%</td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12">PLAQUETAS</td>
        <td class="s115" colspan="12"></td>
        <td class="s24" colspan="12">OTROS</td>
        <td class="s24" colspan="12"></td>
        <td class="s32" colspan="12"></td>
        <td class="s116" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R93" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">94</div>
        </th>
        <td class="s117" colspan="6">IV PERIFÉRICO 3</td>
        <td class="s24" colspan="7"></td>
        <td class="s115" colspan="29"></td>
        <td class="s24" colspan="12">DEXTROSA EN SS</td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12">CRIOPRECIPITADOS</td>
        <td class="s115" colspan="12"></td>
        <td class="s24" colspan="12">TOTAL</td>
        <td class="s24" colspan="12"></td>
        <td class="s32" colspan="12"></td>
        <td class="s116" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R94" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">95</div>
        </th>
        <td class="s117" colspan="6">IV CENTRAL</td>
        <td class="s24" colspan="7"></td>
        <td class="s115" colspan="29"></td>
        <td class="s24" colspan="12">SS 0.9%</td>
        <td class="s24" colspan="12"></td>
        <td class="s85" colspan="12" rowspan="2">OTROS</td>
        <td class="s115" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s32" colspan="12"></td>
        <td class="s116" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R95" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">96</div>
        </th>
        <td class="s117" colspan="6">INTRA ARTERIAL</td>
        <td class="s24" colspan="7"></td>
        <td class="s115" colspan="29"></td>
        <td class="s24" colspan="12">LACTATO RINGER</td>
        <td class="s24" colspan="12"></td>
        <td class="s115" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s32" colspan="12"></td>
        <td class="s116" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R96" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">97</div>
        </th>
        <td class="s118" colspan="6">OTRO</td>
        <td class="s119" colspan="7"></td>
        <td class="s120" colspan="29"></td>
        <td class="s119" colspan="12">EXPANSORES</td>
        <td class="s119" colspan="12"></td>
        <td class="s121" colspan="12">TOTAL</td>
        <td class="s120" colspan="12"></td>
        <td class="s119" colspan="12">BALANCE</td>
        <td class="s119" colspan="12"></td>
        <td class="s122" colspan="12"></td>
        <td class="s123" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R97" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">98</div>
        </th>
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
    <tr style="height: 12px">
        <th id="1547362917R98" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">99</div>
        </th>
        <td class="s88"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s88"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s128"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s129"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s88"></td>
        <td class="s90"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s89"></td>
        <td class="s87"></td>
    </tr>
    <tr style="height: 18px">
        <th id="1547362917R99" style="height: 18px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 18px">100</div>
        </th>
        <td class="s35" colspan="22">J. DATOS DEL RECIÉN NACIDO</td>
        <td class="s35" colspan="116">K. TIEMPOS TRANSCURRIDOS</td>
    </tr>
    <tr style="height: 16px">
        <th id="1547362917R100" style="height: 16px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 16px">101</div>
        </th>
        <td class="s2" colspan="4" rowspan="2">APGAR</td>
        <td class="s24" colspan="6">FETO MUERTO</td>
        <td class="s24" colspan="2"></td>
        <td class="s24" colspan="8">5 MINUTOS</td>
        <td class="s134" colspan="2"></td>
        <td class="s135" colspan="14">DURACIÓN ANESTESIA</td>
        <td class="s136" colspan="14"></td>
        <td class="s135" colspan="13">DURACIÓN DE CIRUGÍA</td>
        <td class="s137" colspan="27"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s82" colspan="12"></td>
    </tr>
    <tr style="height: 16px">
        <th id="1547362917R101" style="height: 16px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 16px">102</div>
        </th>
        <td class="s24" colspan="6">1 MINUTO</td>
        <td class="s24" colspan="2"></td>
        <td class="s24" colspan="8">10 MINUTOS</td>
        <td class="s24" colspan="2"></td>
        <td class="s24" colspan="8"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s24" colspan="12"></td>
        <td class="s82" colspan="12"></td>
    </tr>
    <tr style="height: 12px">
        <th id="1547362917R102" style="height: 12px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 12px">103</div>
        </th>
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
    <tr style="height: 21px">
        <th id="1547362917R105" style="height: 21px;color: white;" class="row-headers-background">
            <div class="row-header-wrapper" style="line-height: 21px">106</div>
        </th>
        <td class="s138" colspan="48">SNS-MSP / HCU-form.018A/2021</td>
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
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s138" colspan="28"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s139"></td>
        <td class="s140" colspan="26">TRANSANESTÉSICO (1)</td>
    </tr>
    </tbody>
</table>
<pagebreak>
    <table cellspacing="0" cellpadding="0">
        <tr style="height: 12px">
            <td class="s0" colspan="68">L. TÉCNICAS ESPECIALES</td>
        </tr>
        <tr style="height: 23px">
            <td class="s3" colspan="11">HEMODILUCIÓN</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">AUTOTRANSFUSIÓN</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">HIPOTENSIÓN</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">HIPOTERMIA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="13">CIRCULACIÓN EXTRACORPÓREA</td>
            <td class="s3" colspan="3"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s0" colspan="68">M. MANTENIMIENTO TEMPERATURA CORPORAL</td>
        </tr>
        <tr style="height: 17px">
            <td class="s3" colspan="11">MANTA TÉRMICA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">CALENTAMIENTO DE FLUIDOS</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="4">OTROS</td>
            <td class="s3" colspan="38"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s0" colspan="68">N. INCIDENTES</td>
        </tr>
        <tr style="height: 23px">
            <td class="s3" colspan="11">ACTIVIDAD ELÉCTRICA SIN PULSO</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">ARRITMIA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">ASISTOLIA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">BRADICARDIA INESTABLE</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="13">TROMBOEMBOLIA PULMONAR</td>
            <td class="s3" colspan="3"></td>
        </tr>
        <tr style="height: 16px">
            <td class="s3" colspan="11">HIPERTERMIA MALIGNA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">ANAFILAXIA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">ISQUEMIA MIOCÁRDICA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">HIPOXEMIA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="13">NEUMOTÓRAX</td>
            <td class="s3" colspan="3"></td>
        </tr>
        <tr style="height: 16px">
            <td class="s3" colspan="11">BRONCOESPASMO</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">DESPERTAR PROLONGADO</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">EMBOLIA AÉREA VENOSA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="11">REACCIÓN A LA TRANSFUSIÓN</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="13">LARINGOESPASMO</td>
            <td class="s3" colspan="3"></td>
        </tr>
        <tr style="height: 23px">
            <td class="s3" colspan="11">DIFICULTAD DE LA TÉCNICA</td>
            <td class="s3" colspan="2"></td>
            <td class="s3" colspan="55">OTROS</td>
        </tr>
        <tr style="height: 12px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s0" colspan="68">O. RESULTADO DE EXÁMENES DE LABORATORIO</td>
        </tr>
        <tr style="height: 16px">
            <td class="s17"></td>
            <td class="s17" colspan="4">HORA</td>
            <td class="s17" colspan="5">pH</td>
            <td class="s17" colspan="5">PO2</td>
            <td class="s17" colspan="5">PCO2</td>
            <td class="s17" colspan="5">HCO3</td>
            <td class="s17" colspan="5">EB</td>
            <td class="s17" colspan="5">SAT. O2</td>
            <td class="s17" colspan="5">LACTATO</td>
            <td class="s17" colspan="5">GLUCOSA</td>
            <td class="s17" colspan="4">Na</td>
            <td class="s17" colspan="3">K</td>
            <td class="s17" colspan="3">Cl</td>
            <td class="s17" colspan="3">HCTO</td>
            <td class="s17" colspan="2">HB</td>
            <td class="s17" colspan="8">OTRO</td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">1</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">2</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">3</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">4</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">5</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">6</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">7</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">7</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">9</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s17">10</td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="5"></td>
            <td class="s17" colspan="4"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="3"></td>
            <td class="s17" colspan="2"></td>
            <td class="s17" colspan="8"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s0" colspan="68">P. OBSERVACIONES</td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 15px">
            <td class="s3" colspan="68"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s0" colspan="68">R. CONDICIÓN DE EGRESO</td>
        </tr>
        <tr style="height: 23px">
            <td class="s3" colspan="13" rowspan="2">CONDICIONES AL SALIR</td>
            <td class="s3" colspan="7">EXTUBADO</td>
            <td class="s3" colspan="4"></td>
            <td class="s3" colspan="7" rowspan="2">CONDUCIDO A:</td>
            <td class="s3" colspan="7" rowspan="2">UNIDAD DE CUIDADOS POST ANESTÉSICOS</td>
            <td class="s3" colspan="3" rowspan="2"></td>
            <td class="s3" colspan="6" rowspan="2">UNIDAD CUIDADOS INTENSIVOS</td>
            <td class="s3" colspan="3" rowspan="2"></td>
            <td class="s3" colspan="7" rowspan="2">CRÍTICOS DE EMERGENCIA</td>
            <td class="s3" colspan="3" rowspan="2"></td>
            <td class="s3" colspan="5" rowspan="2">MORGUE</td>
            <td class="s3" colspan="3" rowspan="2"></td>
        </tr>
        <tr style="height: 23px">
            <td class="s3" colspan="7">INTUBADO</td>
            <td class="s3" colspan="4"></td>
        </tr>
        <tr style="height: 23px">
            <td class="s3" colspan="13">CONSTANTES VITALES DE ENTREGA</td>
            <td class="s3" colspan="14">TA</td>
            <td class="s3" colspan="10">FC</td>
            <td class="s3" colspan="10">FR</td>
            <td class="s3" colspan="12">SAT. O2</td>
            <td class="s3" colspan="9">T°</td>
        </tr>
        <tr style="height: 12px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s0" colspan="68">S. DATOS DEL PROFESIONAL RESPONSABLE</td>
        </tr>
        <tr style="height: 23px">
            <td class="s14" colspan="3">HORA</td>
            <td class="s28" colspan="5"></td>
            <td class="s14" colspan="12">NOMBRE Y APELLIDO DEL PROFESIONAL</td>
            <td class="s28"
                colspan="18"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
            <td class="s14" colspan="4">FIRMA</td>
            <td class="s28" colspan="12"></td>
            <td class="s14" colspan="4">SELLO Y CÓDIGO</td>
            <td class="s28" colspan="10"></td>
        </tr>
        <tr style="height: 12px">
            <td class="s2" colspan="68"></td>
        </tr>
        <tr style="height: 20px">
            <td class="s31" colspan="29">SNS-MSP/HCU-form.018A/2021</td>
            <td class="s31"></td>
            <td class="s31"></td>
            <td class="s31"></td>
            <td class="s32" colspan="36">TRANSANESTÉSICO (2)</td>
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
