<?php
require_once("../../globals.php");
require_once("$srcdir/patient.inc");

$pid = $_GET['patientid'];
$pat_data = getPatientData($pid, "pubpid,fname,mname,lname, lname2, pricelevel, providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

$FONTSIZE = 9;
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    // Would use max-height here but html2pdf does not support it.
    // TODO - now use mPDF, so should test if still need this fix
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round($FONTSIZE * 6.50)) . "pt' />";
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
    if ($pat_data['pricelevel'] == "IESS"){
        $acta = require_once ("acta_IESS.php");
    }
    elseif ($pat_data['pricelevel'] == "MSP"){
        $acta = require_once ("acta_MSP.php");
    }
    echo $acta;
    $html = ob_get_clean();
    $pdf->writeHTML($html);
    $pdf->Output('parte_quirurgico_del_' . '.pdf', 'I'); // D = Download, I = Inline
}
?>
