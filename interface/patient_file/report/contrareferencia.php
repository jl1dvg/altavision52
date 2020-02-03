<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
include_once($GLOBALS["srcdir"]."/api.inc");
include($GLOBALS['webroot'] . '/interface/patient_file/report/custom_report.php');
require_once(dirname(__FILE__) ."/../../../library/lists.inc");

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

$queryformidOFT = "select * from forms where pid=? and encounter=? and formdir = 'eye_mag' and deleted = 0 ORDER BY date DESC ";
$OFTid_SQL = sqlQuery($queryformidOFT, array($_GET['patientid'], $_GET['visitid']));


if ($_REQUEST['formid']) {
    $form_id = $_REQUEST['formid'];
}
else{
    $form_id = $OFTid_SQL['form_id'];
}

if ($_REQUEST['formname']) {
    $form_name=$_REQUEST['formname'];
}

if (!$id) {
    $id = $form_id;
}
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
//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname,pricelevel, lname2,race, sex,status,genericval1,genericname1,providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

//Fecha del form_eye_mag
$queryform = "select * from forms where pid=? and encounter=? and deleted = 0 ORDER BY date DESC ";
$queryPlan = "select tp.admit_date AS alta, tp.recommendation_for_follow_up AS recomendacion, tp.treatment_received AS tratamiento
              from forms AS f
              LEFT JOIN form_treatment_plan AS tp ON (tp.id = f.form_id)
              where
              f.pid=? and
              f.encounter=? and
              f.formdir='treatment_plan' and
              f.deleted = 0 ";
$reasonQuery = "SELECT * FROM form_encounter WHERE encounter=? AND pid=? ";


$reasonSQL = sqlQuery($reasonQuery, array($_GET['visitid'], $_GET['patientid']));
$reason = $reasonSQL['reason'];
$plan = sqlQuery($queryPlan, array($_GET['patientid'],$_GET['visitid']));
$fechaINGRESO = sqlQuery($queryform, array($_GET['patientid'],$_GET['visitid']));
$providerID  =  getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);
$dated = new DateTime($encounter_date);
$dateddia = $dated->format('d');
$datedmes = $dated->format('F');
$datedano = $dated->format('Y');
$visit_date = oeFormatShortDate($dated);


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
$formid = $_GET['visitid'];

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
    'default_font_size' => '9',
    'default_font' => 'Arial',
    'margin_left' => $GLOBALS['5'],
    'margin_right' => $GLOBALS['5'],
    'margin_top' => $GLOBALS['5'],
    'margin_bottom' => $GLOBALS['5'],
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
        div.relative {
            position: relative;
            width: 400px;
            height: 200px;
            border: 3px solid #73AD21;
        }

        div.absolute {
            position: absolute;
            top: 80px;
            right: 0;
            width: 200px;
            height: 100px;
            border: 3px solid #73AD21;
        }
        img {
            position: absolute;
            left: 0px;
            top: 0px;
        }
        td.linearesumen{
            border-top: 1px solid #808080;
            border-bottom: 1px solid #808080;
            border-left: 5px solid #808080;
            border-right: 5px solid #808080;
            height: 20;
            vertical-align: middle;
            background-color: #fff;
            text-align: justify;
            font-size: 1;
        }
        td.ultimalinea{
            border-top: 5px solid #808080;
            height: 10;
            background-color: #fff;
            font-size: 1;
        }

    </STYLE>
</HEAD>
<BODY>

<TABLE CELLSPACING=0 COLS=64 RULES=NONE BORDER=0>
    <COLGROUP><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16><COL WIDTH=16></COLGROUP>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=16 HEIGHT=23 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>INSTITUCI&Oacute;N DEL SISTEMA</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" COLSPAN=19 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>UNIDAD OPERATIVA</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. UO</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. LOCALIZACI&Oacute;N</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>NUMERO DE                       HISTORIA CL&Iacute;NICA</FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=16 ROWSPAN=2 HEIGHT=55 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">
                    <?php
                    echo $titleres['pricelevel'];
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=19 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">ALTA VISION</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PARROQUIA</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CANT&Oacute;N</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PROVINCIA</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TARQUI</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GYE</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GUAYAS</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=4>
                    <?php
                    echo $titleres['pubpid'];
                    ?>
                </FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=13 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO PATERNO</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO MATERNO</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PRIMER NOMBRE</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SEGUNDO NOMBRE</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>C&Eacute;DULA DE CIUDADAN&Iacute;A</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=13 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                <?php
                echo $titleres['lname'];
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                <?php
                echo $titleres['lname2'];
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                <?php
                echo $titleres['fname'];
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                <?php
                echo $titleres['mname'];
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><B>
                <?php
                echo $titleres['pubpid'];
                ?>
            </B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=8 ROWSPAN=2 HEIGHT=43 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>FECHA DE REFERENCIA</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>HORA</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>EDAD</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>GENERO</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>ESTADO CIVIL</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>INSTRUCCI&Oacute;N</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=10 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>EMPRESA DONDE TRABAJA</FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SEGURO DE SALUD</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>M</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>F</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>SOL</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>CAS</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>DIV</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>VIU</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>U-L</FONT></B></TD>
        <TD STYLE="border-bottom: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>ULTIMO A&Ntilde;O APROBADO</FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=8 HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE SDVAL="43056" SDNUM="1033;1033;D-MMM-YY">
            <?php
            echo date("d/m/Y", strtotime($plan['alta']));
            ?>
        </TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=5 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;H:MM AM/PM"><BR></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=5 ALIGN=CENTER VALIGN=MIDDLE SDVAL="50" SDNUM="1033;"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php if ($titleres['sex'] == "Male") {
                        echo text("x");
                    }
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                    if ($titleres['sex'] == "Female") {
                        echo text("x");
                    }
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                    if ($titleres['status'] == "single") {
                        echo text("x");
                    }
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                    if ($titleres['status'] == "married") {
                        echo text("x");
                    }
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                    if ($titleres['status'] == "divorced") {
                        echo text("x");
                    }
                    ?>
                </FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                    if ($titleres['status'] == "widowed") {
                        echo text("x");
                    }
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                    if ($titleres['status'] == "ul") {
                        echo text("x");
                    }
                    ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=4 COLOR="#DD0806"><?php echo text($titleres['race']); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;0;000-00-0000"><B><?php echo text($titleres['genericval1']); ?></B></TD>
    </TR>
    <TR>
        <TD colspan="64" HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" COLSPAN=12 HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>ESTABLECIMIENTO AL QUE SE ENV&Iacute;A LA CONTRARREFERENCIA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=16 ALIGN=LEFT VALIGN=MIDDLE><FONT SIZE=1><?php
                echo text($titleres['genericname1']);
                ?></FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SERVICIO QUE CONTRAREFIERE</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=10 ALIGN=LEFT VALIGN=MIDDLE><FONT SIZE=1>OFTALMOLOGIA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=6 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1><BR></FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" COLSPAN=6 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1><BR></FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
    </TR>
    <TR>
        <TD colspan="64" HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE></TD>
    </TR>
</table>


<table CELLSPACING=0 COLS=13 RULES=NONE BORDER=0 WIDTH=100%>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=1 HEIGHT=24 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="1" SDNUM="1033;"><B><FONT SIZE=3>1</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>RESUMEN DEL CUADRO CLÍNICO</FONT></B></TD>
    </TR>
    <TR>
        <TD class="linearesumen" colspan=13><FONT SIZE=1>
                <?php
                echo wordwrap($reason,165,"</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080\" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR=\"#FFFFFF\">
           <FONT SIZE=1 COLOR=\"#000000\">");
                ?>
            </FONT></TD>

    </TR>
    <tr>
        <td class="linearesumen" colspan="13"></td>
    </tr>
    <tr>
        <td class="linearesumen" colspan="13"></td>
    </tr>
    <TR>
        <TD class="ultimalinea" colspan="13" ALIGN=LEFT><BR></TD>
    </TR>
</table>

<table CELLSPACING=0 COLS=13 RULES=NONE BORDER=0 WIDTH=100%>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=1 HEIGHT=24 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="3" SDNUM="1033;"><B><FONT SIZE=3>2</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>HALLAZGOS RELEVANTES DE EXAMENES Y PROCEDIMIENTOS DIAGNOSTICOS</FONT></B></TD>
    </TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13><FONT SIZE=1>
                <?php

                function ExamOftal($RBROW,$LBROW,$RUL,$LUL,$RLL,$LLL,$RMCT,$LMCT,$RADNEXA,$LADNEXA,$EXT_COMMENTS,$SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                                   $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS){
                    if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS||$SCODVA||$SCOSVA||$ODIOPAP||$OSIOPAP||$OSCONJ||$ODCONJ||$ODCORNEA||$OSCORNEA||$ODAC||$OSAC||$ODLENS||$OSLENS||$ODIRIS||$OSIRIS||$ODDISC||$OSDISC||$ODCUP||$OSCUP||
                        $ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                        if ($SCODVA){
                            $ExamOFT = $ExamOFT .  ("OD: " . $SCODVA . ", ");
                        }
                        if ($SCOSVA){
                            $ExamOFT = $ExamOFT .  ("OI: " . $SCOSVA . ", ");
                        }
                        if ($ODIOPAP){
                            $ExamOFT = $ExamOFT .  ("OD: " . $ODIOPAP . ", ");
                        }
                        if ($OSIOPAP){
                            $ExamOFT = $ExamOFT .  ("OI: " . $OSIOPAP . ", ");
                        }
                        $ExamOFT = $ExamOFT . "Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa: ";
                        if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS) {
                            $ExamOFT = $ExamOFT .  "Examen Externo: ";
                            if ($RBROW||$RUL||$RLL||$RMCT||$RADNEXA) {
                                $ExamOFT = $ExamOFT . "OD " . $RBROW . " " . $RUL . " " . $RLL . " " . $RMCT . " " . $RADNEXA . " ";
                            }
                            if ($LBROW||$LUL||$LLL||$LMCT||$LADNEXA) {
                                $ExamOFT = $ExamOFT . "OI " . $LBROW . " " . $LUL . " " . $LLL . " " . $LMCT . " " . $LADNEXA . " ";
                            }
                            $ExamOFT = $ExamOFT . $EXT_COMMENTS;
                        }
                        if ($ODCONJ||$ODCORNEA||$ODAC||$ODLENS||$ODIRIS) {
                            $ExamOFT = $ExamOFT .  "OD: ";
                        }
                        if ($ODCONJ) {
                            $ExamOFT = $ExamOFT .  ("Conjuntiva " . $ODCONJ . ", ");
                        }
                        if ($ODCORNEA) {
                            $ExamOFT = $ExamOFT .  ("Córnea " . $ODCORNEA . ", ");
                        }
                        if ($ODAC) {
                            $ExamOFT = $ExamOFT .  ("Cámara Anterior " . $ODAC . ", ");
                        }
                        if ($ODLENS) {
                            $ExamOFT = $ExamOFT .  ("Cristalino " . $ODLENS . ", ");
                        }
                        if ($ODIRIS) {
                            $ExamOFT = $ExamOFT .  ("Iris " . $ODIRIS . ", ");
                        }
                        if ($OSCONJ||$OSCORNEA||$OSAC||$OSLENS||$OSIRIS) {
                            $ExamOFT = $ExamOFT .  "OI: ";
                        }
                        if ($OSCONJ) {
                            $ExamOFT = $ExamOFT .  ("Conjuntiva " . $OSCONJ . ", ");
                        }
                        if ($OSCORNEA) {
                            $ExamOFT = $ExamOFT .  ("Córnea " . $OSCORNEA . ", ");
                        }
                        if ($OSAC) {
                            $ExamOFT = $ExamOFT .  ("Cámara Anterior " . $OSAC . ", ");
                        }
                        if ($OSLENS) {
                            $ExamOFT = $ExamOFT .  ("Cristalino " . $OSLENS . ", ");
                        }
                        if ($OSIRIS) {
                            $ExamOFT = $ExamOFT .  ("Iris " . $OSIRIS . ", ");
                        }
                        if ($ODDISC||$OSDISC||$ODCUP||$OSCUP||$ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                            $ExamOFT = $ExamOFT .  "Al fondo de ojo: ";
                        }
                        //Retina Ojo Derecho
                        if ($ODDISC||$ODCUP||$ODMACULA||$ODVESSELS||$ODPERIPH||$ODVITREOUS) {
                            $ExamOFT = $ExamOFT .  "OD: ";
                        }
                        if ($ODDISC) {
                            $ExamOFT = $ExamOFT .  ("Disco " . $ODDISC . ", ");
                        }
                        if ($ODCUP) {
                            $ExamOFT = $ExamOFT .  ("Copa " . $ODCUP . ", ");
                        }
                        if ($ODMACULA) {
                            $ExamOFT = $ExamOFT .  ("Mácula " . $ODMACULA . ", ");
                        }
                        if ($ODVESSELS) {
                            $ExamOFT = $ExamOFT .  ("Vasos " . $ODVESSELS . ", ");
                        }
                        if ($ODPERIPH) {
                            $ExamOFT = $ExamOFT .  ("Periferia " . $ODPERIPH . ", ");
                        }
                        if ($ODVITREOUS) {
                            $ExamOFT = $ExamOFT .  ("Vítreo " . $ODVITREOUS . ", ");
                        }
                        //Retina Ojo Izquierdo
                        if ($OSDISC||$OSCUP||$OSMACULA||$OSVESSELS||$OSPERIPH||$OSVITREOUS) {
                            $ExamOFT = $ExamOFT .  "OI: ";
                        }
                        if ($OSDISC) {
                            $ExamOFT = $ExamOFT .  ("Disco " . $OSDISC . ", ");
                        }
                        if ($OSCUP) {
                            $ExamOFT = $ExamOFT .  ("Copa " . $OSCUP . ", ");
                        }
                        if ($OSMACULA) {
                            $ExamOFT = $ExamOFT .  ("Mácula " . $OSMACULA . ", ");
                        }
                        if ($OSVESSELS) {
                            $ExamOFT = $ExamOFT .  ("Vasos " . $OSVESSELS . ", ");
                        }
                        if ($OSPERIPH) {
                            $ExamOFT = $ExamOFT .  ("Periferia " . $OSPERIPH . ", ");
                        }
                        if ($OSVITREOUS) {
                            $ExamOFT = $ExamOFT .  ("Vítreo " . $OSVITREOUS . ", ");
                        }
                        return $ExamOFT;
                    }


                }
                function SegAntOD($SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                                  $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS){
                    if ($OSCONJ||$ODCONJ||$ODCORNEA||$OSCORNEA||$ODAC||$OSAC||$ODLENS||$OSLENS||$ODIRIS||$OSIRIS||$ODDISC||$OSDISC||$ODCUP||$OSCUP||
                        $ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                        $SegAntOD = "Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa:";
                        if ($ODCONJ||$ODCORNEA||$ODAC||$ODLENS||$ODIRIS) {
                            $SegAntOD = $SegAntOD . "OD: ";
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

                echo wordwrap(ExamOftal($RBROW,$LBROW,$RUL,$LUL,$RLL,$LLL,$RMCT,$LMCT,$RADNEXA,$LADNEXA,$EXT_COMMENTS,$SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                    $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS),165,"</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080\" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR=\"#FFFFFF\">
           <FONT SIZE=1 COLOR=\"#000000\">");
                ?>
            </FONT></TD></TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13><FONT SIZE=1>

                <?php
                function Examenes($pid, $encounter)
                {
                    $queryform = sqlStatement("SELECT * FROM forms AS f
                                           WHERE f.pid=? AND f.encounter=? AND f.formdir LIKE '%LBF%' AND f.formdir NOT LIKE 'LBFprotocolo' AND f.deleted = 0 GROUP BY f.formdir ", array($pid, $encounter));
                    $e = '0';
                    $examenes = array();
                    while ($forms = sqlFetchArray($queryform)) {
                        $informe = array(
                            'formulario' => $forms['form_name'],
                            'formdir' => $forms['formdir'],
                            'form_id' => $forms['form_id']
                        );
                        $examenes[$formdir] = $informe;
                        foreach ($examenes as $examen) {
                            echo "<b>" . $examen['formulario'] . " </b>";
                        }
                        $formid = $examen['form_id'];
                        $query = sqlStatement("SELECT * FROM forms AS f
                                           LEFT JOIN lbf_data AS lbf ON (lbf.form_id = f.form_id)
                                           LEFT JOIN layout_options AS lo ON (lo.field_id = lbf.field_id)
                                           WHERE f.pid=? AND f.encounter=? AND f.form_id=? AND f.formdir LIKE '%LBF%' AND f.formdir NOT LIKE 'LBFprotocolo'
                                           AND f.deleted = 0 AND lbf.field_id NOT LIKE 'p1' AND lbf.field_id NOT LIKE 'p2' AND lbf.field_id NOT LIKE 'OCTNO_Equi'
                                           AND lbf.field_id NOT LIKE 'equipo'
                                           ORDER BY lo.group_id ASC, lo.seq ASC ", array($pid, $encounter, $formid));
                        while ($info = sqlFetchArray($query)) {
                            $lbf = array(
                                'etiqueta' => $info['title'],
                                'informe' => $info['field_value'],
                            );
                            $lb[$formdir] = $lbf;
                            foreach ($lb as $inf) {
                                echo $inf['etiqueta'] . ": " . $inf['informe'] . " ";
                            }
                        }
                        echo "</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080\" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR=\"#FFFFFF\"><FONT SIZE=1 COLOR=\"#000000\">";
                    }
                }

                echo wordwrap(Examenes($pid,$encounter),165,"</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080\" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR=\"#FFFFFF\">
           <FONT SIZE=1 COLOR=\"#000000\">");

                ?>
            </FONT></td>
    </tr>
    <TR>
        <TD class="ultimalinea" colspan="13" HEIGHT=5></TD>
    </TR>
</table>


<table CELLSPACING=0 COLS=13 RULES=NONE BORDER=0 WIDTH=100%>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=1 HEIGHT=24 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="2" SDNUM="1033;"><B><FONT SIZE=3>3</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>TRATAMIENTO Y PROCEDIMIENTOS TERAPÉUTICOS REALIZADOS</FONT></B></TD>
    </TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1>
                <?php

                echo wordwrap($plan['tratamiento'],165,"</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080\" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR=\"#FFFFFF\">
           <FONT SIZE=1 COLOR=\"#000000\">");
                ?><FONT SIZE=1></FONT></TD>
    </TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><BR></FONT></TD>
    </TR>

    <TR>
        <TD class="ultimalinea" colspan="13" ALIGN=LEFT><BR></TD>
    </TR>
</table>


<table CELLSPACING=0 RULES=NONE BORDER=0 WIDTH=100%>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" width="2%" HEIGHT=24 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>4</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="17.5%" ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>DIAGN&Oacute;STICOS</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="17.5%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><FONT SIZE=1>PRE= PRESUNTIVO DEF= DEFINITIVO</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="6%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=1>CIE</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="3.5%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=1>PRE</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="3.5%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=1>DEF</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="2%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3><BR></FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="17.5%" ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3><BR></FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="17.5%"ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><FONT SIZE=1><BR></FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="6%"ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=1>CIE</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" width="3.5%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=1>PRE</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" width="3.5%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=1>DEF</FONT></B></TD>
    </TR>
    <?php
    function getDXoftalmo($form_id,$pid,$dxnum){
        $query = "select * from form_eye_mag_impplan where form_id=? and pid=? AND IMPPLAN_order = ? order by IMPPLAN_order ASC LIMIT 1";
        $result =  sqlStatement($query, array($form_id,$pid,$dxnum));
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
                return $item['codedesc'].". ";
            }

        }
    }
    function getDXoftalmoCIE10($form_id,$pid,$dxnum){
        $query = "select * from form_eye_mag_impplan where form_id=? and pid=? AND IMPPLAN_order = ? order by IMPPLAN_order ASC LIMIT 1";
        $result =  sqlStatement($query, array($form_id,$pid,$dxnum));
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
                return $item['code'].". ";
            }

        }
    }
    ?>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" HEIGHT=30 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC" SDVAL="1" SDNUM="1033;"><B><FONT SIZE=1>1</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" colspan="2" ALIGN=LEFT VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmo($form_id,$pid,"0"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmoCIE10($form_id,$pid,"0"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><?php if (getDXoftalmo($form_id,$pid,"0")) {
                        echo "x";
                    } ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC" SDVAL="4" SDNUM="1033;"><B><FONT SIZE=1>4</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" colspan="2" ALIGN=LEFT VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmo($form_id,$pid,"3"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmoCIE10($form_id,$pid,"3"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><?php if (getDXoftalmo($form_id,$pid,"3")) {
                        echo "x";
                    } ?></FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" HEIGHT=30 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC" SDVAL="2" SDNUM="1033;"><B><FONT SIZE=1>2</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" colspan="2" ALIGN=LEFT VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmo($form_id,$pid,"1"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmoCIE10($form_id,$pid,"1"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><?php if (getDXoftalmo($form_id,$pid,"1")) {
                        echo "x";
                    } ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC" SDVAL="5" SDNUM="1033;"><B><FONT SIZE=1>5</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" colspan="2" ALIGN=LEFT VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmo($form_id,$pid,"4"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><?php echo getDXoftalmoCIE10($form_id,$pid,"4"); ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><?php if (getDXoftalmo($form_id,$pid,"4")) {
                        echo "x";
                    } ?></FONT></B></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC" SDVAL="3" SDNUM="1033;"><B><FONT SIZE=1>3</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" colspan="2" ALIGN=LEFT VALIGN=MIDDLE><FONT SIZE=1><B><?php echo getDXoftalmo($form_id,$pid,"2"); ?></B></FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><B><?php echo getDXoftalmoCIE10($form_id,$pid,"2"); ?></B></FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><?php if (getDXoftalmo($form_id,$pid,"2")) {
                        echo "x";
                    } ?></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC" SDVAL="6" SDNUM="1033;"><B><FONT SIZE=1>6</FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" colspan="2" ALIGN=LEFT VALIGN=MIDDLE><FONT SIZE=1><B><?php echo getDXoftalmo($form_id,$pid,"5"); ?></B></FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><B><?php echo getDXoftalmoCIE10($form_id,$pid,"5"); ?></B></FONT></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B></TD>
        <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><?php if (getDXoftalmo($form_id,$pid,"5")) {
                        echo "x";
                    } ?></FONT></B></TD>
    </TR>
    <tr>
        <td class="ultimalinea" colspan="12">

        </td>
    </tr>

</table>

<table CELLSPACING=0 COLS=13 RULES=NONE BORDER=0 WIDTH=100%>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080" COLSPAN=1 HEIGHT=24 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCCCFF" SDVAL="2" SDNUM="1033;"><B><FONT SIZE=3>5</FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080" COLSPAN=12 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#CCCCFF"><B><FONT SIZE=3>PLAN DE TRATAMIENTO RECOMENDADO</FONT></B></TD>
    </TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1>
                <?php

                echo wordwrap($plan['recomendacion'],165,"</FONT></TD></TR><TR><TD STYLE=\"border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 5px solid #808080\" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR=\"#FFFFFF\">
           <FONT SIZE=1 COLOR=\"#000000\">");
                ?>
            </FONT></TD>
    </TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><BR></FONT></TD>
    </TR>
    <TR>
        <TD class="linearesumen" COLSPAN=13 HEIGHT=20 ALIGN=LEFT VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1><BR></FONT></TD>
    </TR>
    <TR>
        <TD class="ultimalinea" colspan="13" ALIGN=LEFT><BR></TD>
    </TR>
</table>

<table CELLSPACING=0 RULES=NONE BORDER=0 WIDTH=100%>
    <TR>
        <TD colspan="8" ALIGN=LEFT><BR></TD>
        <TD rowspan="3" ALIGN=CENTER valign="top" BGCOLOR="#FFFFFF"><FONT SIZE=1>
            </FONT></TD>
    </TR>
    <TR>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" width="7%" HEIGHT=20 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SALA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;D-MMM-YY"><B><FONT SIZE=1><BR></FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CAMA</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;0;[H]:MM:SS"><B><FONT SIZE=1><BR></FONT></B></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PROFESIONAL</FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1>
                <?php
                echo getProviderName($providerID);
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                <?php
                echo getProviderRegistro($providerID);
                ?>
            </FONT></TD>
        <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>FIRMA</FONT></TD>

    </TR>
    <TR>
        <TD colspan="8" ALIGN=LEFT><BR></TD>
    </TR>
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP / HCU-form.053 / 2008</FONT></B></TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">CONTRAREFERENCIA</FONT></B></TD>
    </TR>
    </TBODY>
</TABLE>

</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('informe_medico_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
