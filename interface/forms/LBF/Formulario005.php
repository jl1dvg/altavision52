<!DOCTYPE HTML>
<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
include_once($GLOBALS["srcdir"]."/api.inc");
require_once(dirname(__FILE__) ."/../../../library/lists.inc");

use OpenEMR\Services\FacilityService;

$form_name = "eye_mag";
$form_folder = "eye_mag";

$facilityService = new FacilityService();

require_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");

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

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2,sex,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

//Fecha del form_eye_mag
$query="select form_encounter.date as encounter_date,form_eye_mag.id as form_id,form_encounter.*, form_eye_mag.*
        from form_eye_mag ,forms,form_encounter
        where
        form_encounter.encounter =? and
        form_encounter.encounter = forms.encounter and
        form_eye_mag.id=forms.form_id and
        forms.deleted != '1' and
        form_eye_mag.pid=? ";

$encounter_data =sqlQuery($query, array($encounter,$pid));
@extract($encounter_data);
$providerID  =  getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);
$providerRegistro = getProviderRegistro($providerID);
$dated = new DateTime($encounter_date);
$dateddia = $dated->format('d');
$datedmes = $dated->format('F');
$datedano = $dated->format('Y');
$visit_date = oeFormatShortDate($dated);
$mes = date('F', $timestamp);
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$nombreMes = str_replace($meses_EN, $meses_ES, $datedmes);

//Fin fecha del form_eye_mag

function lookup_lbf_desc($desc_lbf)
{
    $querylbf="select field_value from lbf_data WHERE form_id=? AND field_id=? ";
    $lbf_query = sqlStatement($querylbf, array($_GET['formid'],$desc_lbf));
    return $lbf_query;
}
$lbfid = $_GET['formid'];
$fechaPROTOCOLO = sqlQuery("select * from forms WHERE form_id=? AND encounter=?", array($_GET['formid'],$_GET['visitid']));
$datedprotocolo = new DateTime($fechaPROTOCOLO['date']);
$dateddia = $datedprotocolo->format('d');
$datedmes = $datedprotocolo->format('m');
$datedano = $datedprotocolo->format('Y');

$querylbfopr= sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_opr'");
$REALIZADA = $querylbfopr['field_value'];
$querylbfojo= sqlQuery("select field_value from lbf_data WHERE form_id=$lbfid AND field_id='Prot_ojo'");
$OJO = $querylbfojo['field_value'];



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
        td.evolucion{
            border: 1;
        }
        td.stripe-5 {
            border: 1;
            background-image: url(Formulario 005_html_m7535e702.gif);
        }
    </style>

</HEAD>

<BODY>


<?php
echo "  <header>
        <TABLE WIDTH=966 BORDER=1 CELLPADDING=5 CELLSPACING=0 RULES=ROWS>
          <COLGROUP>
            <COL WIDTH=199>
          </COLGROUP>
          <COLGROUP>
            <COL WIDTH=214>
          </COLGROUP>
          <COLGROUP>
            <COL WIDTH=214>
          </COLGROUP>
          <COLGROUP>
            <COL WIDTH=52>
            <COL WIDTH=52>
          </COLGROUP>
          <COLGROUP>
            <COL WIDTH=169>
          </COLGROUP>
          <TR>
            <TD WIDTH=199 HEIGHT=6 BGCOLOR=\"#ccffcc\">
              <P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">ESTABLECIMIENTO</SPAN></FONT></FONT></P>
            </TD>
            <TD WIDTH=214 BGCOLOR=\"#ccffcc\">
              <P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">NOMBRE</SPAN></FONT></FONT></P>
            </TD>
            <TD WIDTH=214 BGCOLOR=\"#ccffcc\">
              <P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">APELLIDO</SPAN></FONT></FONT></P>
            </TD>
            <TD WIDTH=52 BGCOLOR=\"#ccffcc\">
              <P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">SEXO
              (M-F)</SPAN></FONT></FONT></P>
            </TD>
            <TD WIDTH=52 BGCOLOR=\"#ccffcc\">
              <P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">N&deg;
               HOJA</SPAN></FONT></FONT></P>
            </TD>
            <TD WIDTH=169 BGCOLOR=\"#ccffcc\">
              <P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; text-decoration: none\">
              <FONT COLOR=\"#000000\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><B><SPAN STYLE=\"text-decoration: none\">N&deg;
               HISTORIA CLINICA</SPAN></B></FONT></FONT></FONT></P>
              <P><BR>
              </P>
            </TD>
          </TR>
          <TR>
            <TD WIDTH=199 HEIGHT=12 VALIGN=TOP>
              <P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 8pt\"><SPAN STYLE=\"text-decoration: none\">ALTAVISION</SPAN></FONT></FONT></P>
            </TD>
            <TD WIDTH=214>
              <P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">
              <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 8pt\"><SPAN STYLE=\"text-decoration: none\">";
echo text($titleres['fname'] . " " . $titleres['mname']);
echo " </SPAN></FONT></FONT></P></TD>
                                <TD WIDTH=214>
                                  <P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">
                                  <FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 8pt\"><SPAN STYLE=\"text-decoration: none\">";
echo text($titleres['lname'] . " " . $titleres['lname2']);
echo "</SPAN></FONT></FONT></P></TD>
                                <TD WIDTH=52>
                                  <P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">";
if ($titleres['sex'] == "Female") {
    echo text("F");
} else {
    echo text("M");
}
echo "</P></TD>
              <TD WIDTH=52>
                <P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\"><BR>
                </P></TD>
              <TD WIDTH=169>
                <P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; text-decoration: none\">
                <FONT COLOR=\"#000000\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 8pt\"><B><SPAN STYLE=\"text-decoration: none\">";
echo text($titleres['pubpid']);
echo "</SPAN></B></FONT></FONT></FONT></P>
                    </TD></TR></TABLE>
            </header>";
echo "<br>
    <TABLE WIDTH=966 CELLPADDING=5 CELLSPACING=0 RULES=ROWS>
    	<COLGROUP>
    		<COL WIDTH=79>
    	</COLGROUP>
    	<COLGROUP>
    		<COL WIDTH=36>
    	</COLGROUP>
    	<COLGROUP>
    		<COL WIDTH=428>
    	</COLGROUP>
    	<COLGROUP>
    		<COL WIDTH=4>
    	</COLGROUP>
    	<COLGROUP>
    		<COL WIDTH=239>
    		<COL WIDTH=42>
    	</COLGROUP>
    	<COLGROUP>
    		<COL WIDTH=67>
    	</COLGROUP>
    	<TR>
    		<TD class=\"evolucion\" COLSPAN=3 WIDTH=563 HEIGHT=25 BGCOLOR=\"#ccccff\">
    			<P ALIGN=LEFT STYLE=\"font-style: normal; text-decoration: none\"><SPAN STYLE=\"text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=2 STYLE=\"font-size: 9pt\"><B><FONT SIZE=2>1
    			EVOLUCION</FONT></SPAN></B></FONT></FONT></P>
    		</TD>
    		<TD class=\"central\" WIDTH=4 BGCOLOR=\"#ffffff\" >
    			<P ALIGN=LEFT STYLE=\"font-style: normal; text-decoration: none\"><BR>
    			</P>
    		</TD>
    		<TD class=\"evolucion\" WIDTH=239 BGCOLOR=\"#ccccff\">
    			<P ALIGN=LEFT STYLE=\"font-style: normal; text-decoration: none\"><SPAN STYLE=\"text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=2 STYLE=\"font-size: 9pt\"><B>2<FONT SIZE=2>
    			PRESCRIPCIONES</FONT></SPAN></B></FONT></FONT></P>
    		</TD>
    		<TD class=\"evolucion\" COLSPAN=2 WIDTH=119 BGCOLOR=\"#ccccff\">
    			<P ALIGN=CENTER STYLE=\"font-style: normal; text-decoration: none\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><B><SPAN STYLE=\"text-decoration: none\">FIRMAR
    			AL PIE DE CADA PRESCRIPCION</SPAN></B></FONT></FONT></P>
    		</TD>
    	</TR>
    	<TR>
    		<TD class=\"evolucion\" WIDTH=79 HEIGHT=25 BGCOLOR=\"#ccffcc\">
    			<P ALIGN=LEFT STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">FECHA
    			         <FONT COLOR=\"#000000\">(DIA/MES/A&Ntilde;O)</FONT></SPAN></FONT></FONT></P>
    			<P ALIGN=LEFT STYLE=\"font-style: normal; text-decoration: none\">
    			</P>
    		</TD>
    		<TD class=\"evolucion\" WIDTH=36 BGCOLOR=\"#ccffcc\">
    			<P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 6pt\"><SPAN STYLE=\"text-decoration: none\">HORA</SPAN></FONT></FONT></P>
    		</TD>
    		<TD class=\"evolucion\" WIDTH=428 BGCOLOR=\"#ccffcc\">
    			<P ALIGN=CENTER STYLE=\"font-style: normal; text-decoration: none\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1><B><SPAN STYLE=\"text-decoration: none\">NOTAS
    			DE EVOLUCION</SPAN></B></FONT></FONT></P>
    		</TD>
    		<TD WIDTH=4 BGCOLOR=\"#ffffff\">
    			<P ALIGN=LEFT STYLE=\"font-style: normal; text-decoration: none\"><BR>
    			</P>
    		</TD>
    		<TD class=\"evolucion\" COLSPAN=2 WIDTH=291 BGCOLOR=\"#ccffcc\">
    			<P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; text-decoration: none\">
    			<FONT COLOR=\"#000000\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 4pt\"><B><SPAN STYLE=\"text-decoration: none\"><FONT SIZE=1>FARMACOTERAPIA
    			E INDICACIONES</FONT><FONT SIZE=1 STYLE=\"font-size: 6pt\"> </FONT></SPAN></B></FONT></FONT></FONT>
    			</P>
    			<P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
    			<FONT COLOR=\"#000000\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=2 STYLE=\"font-size: 9pt\"><SPAN STYLE=\"text-decoration: none\">(PARA
    			ENFERMER&Iacute;A Y OTRO PERSONAL)</SPAN></FONT></FONT></FONT></P>
    		</TD>
    		<TD class=\"evolucion\" WIDTH=67 BGCOLOR=\"#ccffcc\">
    			<P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 5pt\"><SPAN STYLE=\"text-decoration: none\">ADMINISTR.
    			</SPAN></FONT></FONT>
    			</P>
    			<P ALIGN=CENTER STYLE=\"margin-bottom: 0in; font-style: normal; font-weight: normal; text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 5pt\"><SPAN STYLE=\"text-decoration: none\">F&Aacute;RMACOS</SPAN></FONT></FONT></P>
    			<P ALIGN=CENTER STYLE=\"font-style: normal; font-weight: normal; text-decoration: none\">
    			<FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1 STYLE=\"font-size: 5pt\"><SPAN STYLE=\"text-decoration: none\">INSUM</SPAN></FONT></FONT></P>
    		</TD>
    	</TR>
    	<TR  VALIGN=TOP>
    		<TD class=\"stripe-5\" WIDTH=79 HEIGHT=1087 valign=top>
        <P ALIGN=LEFT STYLE=\"margin-right: 0.04in; font-style: normal; font-weight: normal; text-decoration: none\">" .
    $dateddia . "/" .
    $datedmes . "/" .
    $datedano .
    "</P>
        </TD>
    		<TD class=\"stripe-5\" WIDTH=36 valign=top>
    			<P ALIGN=LEFT STYLE=\"margin-right: 0.04in; font-style: normal; font-weight: normal; text-decoration: none\">" .
    str_replace("field_value","",lookup_lbf_desc('Prot_hfin')) .
    "</P>
    		</TD>
    		<TD class=\"stripe-5\" WIDTH=428 valign=top>
          <P ALIGN=LEFT STYLE=\"margin-right: 0.04in; font-style: normal; font-weight: normal; text-decoration: none;\">
          POSTOPERATORIO QUE INGRESA A LA SALA DE RECUPERACION POSTERIOR A
    ";
if ($REALIZADA && $REALIZADA != '0') {
    $REALIZADA_items = explode('|', $REALIZADA);
    foreach ($REALIZADA_items as $item) {
        $QXpropuesta = ($item);
        $IntervencionPropuesta = sqlquery("SELECT notes FROM `list_options`
                WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` = '$QXpropuesta' ");
        echo  trim(strtoupper($IntervencionPropuesta['notes'] . ", "));
    }
}
$queryOjo= sqlQuery("SELECT * FROM `list_options`
              WHERE `list_id` = 'OD' AND `option_id` = ? ", array($OJO));
echo " " . trim(strtoupper($queryOjo['title']));

echo "      AL MOMENTO PACIENTE AFEBRIL, HIDRATADO, HEMODINAMICAMENTE ESTABLE CON PARCHE OCULAR.
       </p>
   		</TD>
   		<TD WIDTH=4 BGCOLOR=\"#ffffff\">
   			<P ALIGN=LEFT STYLE=\"margin-right: 0.04in; font-style: normal; font-weight: normal; text-decoration: none\">
         <br />
   			</P>
   		</TD>
   		<TD class=\"stripe-5\" COLSPAN=2 WIDTH=291 valign=top>
   			<P ALIGN=LEFT STYLE=\"margin-right: 0.04in; font-style: normal; font-weight: normal; text-decoration: none; valign: top;\">
         <b>MEDIDAS GENERALES</b><BR />
         CONTROL DE SIGNOS VITALES<BR>
         CUIDADOS DE ENFERMERIA<br />
         TERMINAR VIA PERIFERICA Y RETIRAR<br /><br><br><br><br><br>
   			</P>
   			<span>";

echo "
    </span>
   		</TD>
   		<TD class=\"stripe-5\" WIDTH=67>
   			<P ALIGN=LEFT STYLE=\"margin-right: 0.04in; font-style: normal; font-weight: normal; text-decoration: none\">
   			<BR>
   			</P>
   		</TD>
   	</TR>
   </TABLE>
   <div ALIGN=LEFT STYLE=\"margin-bottom: 0in\"><FONT COLOR=\"#000000\"><SPAN STYLE=\"text-decoration: none\"><FONT FACE=\"Arial, sans-serif\"><FONT SIZE=1><SPAN STYLE=\"font-style: normal\"><B><SPAN STYLE=\"text-decoration: none\">SNS-MSP
   / HCU-form.005 / 2008 EVOLUCION
   Y PRESCRIPCIONES  (2)</SPAN></B></SPAN></FONT></FONT></SPAN></FONT></div><br />";

?>

</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('informe_medico_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
