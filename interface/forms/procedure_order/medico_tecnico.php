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

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, phone_home, phone_cell, phone_contact, sex,providerID,DATE_FORMAT(DOB,'%m') as DOB_M,DATE_FORMAT(DOB,'%d') as DOB_D,DATE_FORMAT(DOB,'%Y') as DOB_A, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_TS");

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
//Inicio SQL Autorizaion
$queryProced="select * from procedure_order_code AS PO
        left join procedure_order as POC on (PO.procedure_order_id = POC.procedure_order_id)
        where
        patient_id=? and
        encounter_id=? and
        POC.procedure_order_id = ? and
        activity = 1
        ORDER BY ojo";

$order_data = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_data2 = sqlQuery($queryProced, array($pid,$encounter,$_GET['formid']));
$order_dataF = sqlStatement($queryProced, array($pid,$encounter,$_GET['formid']));
$order_fecha = sqlFetchArray($order_dataF);
$providerID  =  getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);


$CarePlanSQL = sqlQuery("SELECT * FROM procedure_order_code AS PO
  LEFT JOIN procedure_order AS POC ON (PO.procedure_order_id = POC.procedure_order_id) WHERE " .
    "patient_id = ? AND encounter_id = ? ", array($pid, $encounter));
$NombreProcedimiento =  $CarePlanSQL['procedure_name'];
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
?>
<HTML>
<HEAD>
    <TITLE>INFORME TECNICO MEDICO PARA JUSTIFICACION DE PRESTACIONES ADICIONALES</TITLE>
</HEAD>
<BODY TEXT="#000000">
<TABLE WIDTH=909 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=6 CELLSPACING=0 FRAME=BELOW>
    <TR>
        <TD COLSPAN=4 WIDTH=897 HEIGHT=11>
            <P CLASS="western"><BR>
            </P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=4 WIDTH=897 HEIGHT=28>
            <P ALIGN=CENTER><FONT SIZE=4><B>
                        Centro Oftalmológico ALTAVISION
                    </B></FONT></P>
        </TD>
    </TR>
    <TR>
        <TD ROWSPAN=2 WIDTH=631 HEIGHT=28>
            <P ALIGN=CENTER><FONT SIZE=4 STYLE="font-size: 15pt"><B>Servicio de
                        <?php
                        echo substr(getProviderEspecialidad($providerID),12);
                        ?></B></FONT></P>
        </TD>
        <TD WIDTH=136>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">Fecha
                    de Elaboraci&oacute;n:</FONT></P>
        </TD>
        <TD COLSPAN=2 WIDTH=105>
            <P ALIGN=CENTER><FONT COLOR="#ff0000"><FONT SIZE=1 STYLE="font-size: 6pt">
                        <?php
                        echo date("d/m/Y", strtotime($order_fecha['date_collected']));
                        ?>
                    </FONT></P>
        </TD>
    </TR>
    <TR>
        <TD WIDTH=136>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">&Aacute;rea-Proceso:</FONT></P>
        </TD>
        <TD COLSPAN=2 WIDTH=105>
            <P ALIGN=CENTER><FONT COLOR="#ff0000"><FONT SIZE=1 STYLE="font-size: 5pt"><B>CONVENIO</B></FONT></FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=3 WIDTH=822 HEIGHT=8>
            <P ALIGN=CENTER STYLE="margin-bottom: 0in">
                <B>INFORME T&Eacute;CNICO-M&Eacute;DICO PARA LA JUSTIFICACI&Oacute;N
                    DE PRESTACIONES ADICIONALES A LAS AUTORIZADAS</B></P>
            <P ALIGN=CENTER><BR>
            </P>
        </TD>
        <TD WIDTH=63>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">P&aacute;gina:
                    <SDFIELD TYPE=PAGE SUBTYPE=RANDOM FORMAT=PAGE>1</SDFIELD></FONT></P>
        </TD>
    </TR>
</TABLE>
<P STYLE="margin-bottom: 0in; line-height: 100%"><BR>
</P>

<TABLE WIDTH=914 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=6 CELLSPACING=0 FRAME=HSIDES RULES=ROWS STYLE="page-break-before: always">
    <TR>
        <TD COLSPAN=6 WIDTH=902 HEIGHT=12 VALIGN=BOTTOM>
            <P ALIGN=CENTER><B>DATOS GENERALES</B></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=43>
            <P CLASS="western">NOMBRES Y APELLIDOS DE PACIENTE:</P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt">
                        <?php
                        echo $titleres['fname'] . " " . $titleres['mname'] . " " . $titleres['lname'] . " " . $titleres['lname2'];
                        ?>
                    </FONT></FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=13>
            <P CLASS="western"><FONT COLOR="#000000"><SPAN>C&Eacute;DULA
			DE IDENTIDAD:</SPAN></FONT></P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">
                    <?php
                    echo $titleres['pubpid'];
                    ?>
                </FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=50>
            <P CLASS="western">C&Oacute;DIGO DE VALIDACI&Oacute;N
                QUE AUTORIZ&Oacute; PRESTACI&Oacute;N:</P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt"></FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=46>
            <P CLASS="western">FECHA DE INGRESO DEL PACIENTE:</P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">
                    <?php
                    echo date("d/m/Y", strtotime($fechaINGRESO['date']));
                    ?>
                </FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=46>
            <P CLASS="western"><FONT COLOR="#000000"><SPAN>M&Eacute;DICO
			SOLICITANTE: (QUIEN REALIZA EL INFORME)</SPAN></FONT></P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">
                    <?php
                    echo getProviderName($providerID);
                    ?>
                </FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=13>
            <P CLASS="western"><FONT COLOR="#000000"><SPAN>ESPECIALIDAD
			/ SUBESPECIALIDAD:</SPAN></FONT></P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt">
                        <?php
                        echo getProviderEspecialidad($providerID);
                        ?>
                    </FONT></FONT></P>
        </TD>
    </TR>

    <TR VALIGN=BOTTOM>
        <TD colspan=6 WIDTH=35 HEIGHT=13>
            <P ALIGN=CENTER><BR>
            </P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=6 WIDTH=902 HEIGHT=12 VALIGN=BOTTOM>
            <P ALIGN=CENTER><B>DIAGNÓSTICOS (CIE-10):</B></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=36>
            <P CLASS="western"><FONT COLOR="#000000"><SPAN>DIAGN&Oacute;STICO(S)
			QUE JUSTIFICARON EL C&Oacute;DIGO DE VALIDACI&Oacute;N:</SPAN></FONT></P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">
                    <?php

                    //DIAGNOSTICO DE INGRESO
                    $DXingreso = "SELECT `l`.`diagnosis` as `diagnostico` FROM `issue_encounter` AS `ie`
                          LEFT JOIN `lists` AS `l` ON (`ie`.`list_id` = `l`.`id`)
                          WHERE `ie`.`pid` = ? AND `ie`.`encounter` = ? ";
                    $result =  sqlStatement($DXingreso, array($_GET['patientid'],$_GET['visitid']));
                    $i='0';
                    // echo '<ol>';
                    while ($dxingreso_list = sqlFetchArray($result)) {
                        $newdata =  array (
                            'code'          => $dxingreso_list['diagnostico'],
                        );
                        $DXINitems[$i] =$newdata;
                        $i++;
                    }

                    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
                    foreach ($DXINitems as $item) {
                        echo "CIE10" . substr($item['code'],5) . " ";

                    }
                    ?>
                </FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=2 WIDTH=301 HEIGHT=59>
            <P CLASS="western"><FONT COLOR="#000000"><SPAN>DIGAN&Oacute;STICOS
			ACTUALES QUE JUSTIFICAR&Aacute;N LA PRESTACI&Oacute;N ADICIONAL:</SPAN></FONT></P>
        </TD>
        <TD COLSPAN=4 WIDTH=589>
            <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">
                    <?php
                    //DIAGNOSTICO DE JUSTIFICACION
                    $DXjustificado = "SELECT `imp`.`code` as `diagnostico`  FROM `forms` AS `f`
                        LEFT JOIN `form_eye_mag_impplan` AS `imp` ON(`f`.`form_id` = `imp`.`form_id`)
                        WHERE `f`.`pid` = ? AND `f`.`encounter` = ? AND `f`.`formdir` = 'eye_mag' AND `deleted` = 0";
                    $result =  sqlStatement($DXjustificado, array($_GET['patientid'],$_GET['visitid']));
                    $i='0';
                    // echo '<ol>';
                    while ($dxjust_list = sqlFetchArray($result)) {
                        $newdata =  array (
                            'code'          => $dxjust_list['diagnostico'],
                        );
                        $DXJSitems[$i] =$newdata;
                        $i++;
                    }

                    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
                    foreach ($DXJSitems as $item) {
                        echo "CIE10:" . $item['code'] . " ";

                    }
                    ?></FONT></P>

        </TD>
    </TR>
    <TR VALIGN=BOTTOM>
        <TD colspan=6 WIDTH=35 HEIGHT=13>
            <P ALIGN=CENTER><BR>
            </P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=6 WIDTH=902 HEIGHT=13 VALIGN=BOTTOM>
            <P ALIGN=CENTER><B>CUADRO CL&Iacute;NICO
                    DEL PACIENTE QUE JUSTIFICAR&Aacute; PRESTACI&Oacute;N ADICIONAL</B></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=6 WIDTH=902 HEIGHT=13>
            <P ALIGN=CENTER><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 9pt">
                        <?php
                        if ($SCODVA||$SCOSVA){
                            echo text("AVSC ");
                        }
                        if ($SCODVA){
                            echo text("OD: " . $SCODVA);
                        }
                        if ($SCOSVA){
                            echo text("OI: " . $SCOSVA);
                        }
                        if ($ODIOPAP||$OSIOPAP){
                            echo text(" PIO ");
                        }
                        if ($ODIOPAP){
                            echo text("OD: " . $ODIOPAP);
                        }
                        if ($OSIOPAP){
                            echo text("OI: " . $OSIOPAP);
                        }


                        //Variables Segmento anterior
                        if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS||$OSCONJ||$ODCONJ||$ODCORNEA||$OSCORNEA||$ODAC||$OSAC||$ODLENS||$OSLENS||$ODIRIS||$OSIRIS||$ODDISC||$OSDISC||$ODCUP||$OSCUP||
                            $ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                            ?>
                            <!-- start of Anterior Segment exam -->
                            <span>Luego de realizar examen fisico oftalmologico y fondo de ojo con oftalmoscopia indirecta con lupa de 20 Dioptrias bajo dilatacion con gotas de tropicamida y fenilefrina a la Biomicroscopia se observa:</span>

                        <?php }
                        if ($RBROW||$LBROW||$RUL||$LUL||$RLL||$LLL||$RMCT||$LMCT||$RADNEXA||$LADNEXA||$EXT_COMMENTS) {
                            echo "Examen Externo:";
                            if ($RBROW||$RUL||$RLL||$RMCT||$RADNEXA) {
                                echo "OD " . $RBROW . " " . $RUL . " " . $RLL . " " . $RMCT . " " . $RADNEXA . " ";
                            }
                            if ($LBROW||$LUL||$LLL||$LMCT||$LADNEXA) {
                                echo "OI " . $LBROW . " " . $LUL . " " . $LLL . " " . $LMCT . " " . $LADNEXA . " ";
                            }
                            echo $EXT_COMMENTS;
                        }
                        //Segmento Anterior Ojo Derecho
                        if ($ODCONJ||$ODCORNEA||$ODAC||$ODLENS||$ODIRIS) {
                            ?>
                            <span>OD: </span>
                        <?php }
                        if ($ODCONJ) {
                            ?>
                            <span><?php echo text("Conjuntiva " . $ODCONJ); ?></span>
                        <?php }
                        if ($ODCORNEA) {
                            ?>
                            <span><?php echo text("Córnea " . $ODCORNEA); ?></span>
                        <?php }
                        if ($ODAC) {
                            ?>
                            <span><?php echo text("Cámara Anterior " . $ODAC . " "); ?></span>
                        <?php }
                        if ($ODLENS) {
                            ?>
                            <span><?php echo text("Cristalino " . $ODLENS . " "); ?></span>
                        <?php }
                        if ($ODIRIS) {
                            ?>
                            <span><?php echo text("Iris " . $ODIRIS . " "); ?></span>
                        <?php }
                        //Segmento Anterior Ojo Izquierdo
                        if ($OSCONJ||$OSCORNEA||$OSAC||$OSLENS||$OSIRIS) {
                            ?>
                            <span>OI: </span>
                        <?php }
                        if ($OSCONJ) {
                            ?>
                            <span><?php echo text("Conjuntiva " . $OSCONJ); ?></span>
                        <?php }
                        if ($OSCORNEA) {
                            ?>
                            <span><?php echo text("Córnea " . $OSCORNEA); ?></span>
                        <?php }
                        if ($OSAC) {
                            ?>
                            <span><?php echo text("Cámara Anterior " . $OSAC . " "); ?></span>
                        <?php }
                        if ($OSLENS) {
                            ?>
                            <span><?php echo text("Cristalino " . $OSLENS . " "); ?></span>
                        <?php }
                        if ($OSIRIS) {
                            ?>
                            <span><?php echo text("Iris " . $OSIRIS . " "); ?></span>
                        <?php }
                        if ($ODDISC||$OSDISC||$ODCUP||$OSCUP||$ODMACULA||$OSMACULA||$ODVESSELS||$OSVESSELS||$ODPERIPH||$OSPERIPH||$ODVITREOUS||$OSVITREOUS) {
                            ?>
                            <span>Al fondo de ojo:</span>
                        <?php }
                        //Retina Ojo Derecho
                        if ($ODDISC||$ODCUP||$ODMACULA||$ODVESSELS||$ODPERIPH||$ODVITREOUS) {
                            ?>
                            <span>OD: </span>
                        <?php }
                        if ($ODDISC) {
                            ?>
                            <span><?php echo text("Disco " . $ODDISC . " "); ?></span>
                        <?php }
                        if ($ODCUP) {
                            ?>
                            <span><?php echo text("Copa " . $ODCUP . " "); ?></span>
                        <?php }
                        if ($ODMACULA) {
                            ?>
                            <span><?php echo text("Mácula " . $ODMACULA . " "); ?></span>
                        <?php }
                        if ($ODVESSELS) {
                            ?>
                            <span><?php echo text("Vasos " . $ODVESSELS . " "); ?></span>
                        <?php }
                        if ($ODPERIPH) {
                            ?>
                            <span><?php echo text("Periferia " . $ODPERIPH . " "); ?></span>
                        <?php }
                        if ($ODVITREOUS) {
                            ?>
                            <span><?php echo text("Vítreo " . $ODVITREOUS . " "); ?></span>
                        <?php }

                        //Retina Ojo Izquierdo
                        if ($OSDISC||$OSCUP||$OSMACULA||$OSVESSELS||$OSPERIPH||$OSVITREOUS) {
                            ?>
                            <span>OI: </span>
                        <?php }
                        if ($OSDISC) {
                            ?>
                            <span><?php echo text("Disco " . $OSDISC . " "); ?></span>
                        <?php }
                        if ($OSCUP) {
                            ?>
                            <span><?php echo text("Copa " . $OSCUP . " "); ?></span>
                        <?php }
                        if ($OSMACULA) {
                            ?>
                            <span><?php echo text("Mácula " . $OSMACULA . " "); ?></span>
                        <?php }
                        if ($OSVESSELS) {
                            ?>
                            <span><?php echo text("Vasos " . $OSVESSELS . " "); ?></span>
                        <?php }
                        if ($OSPERIPH) {
                            ?>
                            <span><?php echo text("Periferia " . $OSPERIPH . " "); ?></span>
                        <?php }
                        if ($OSVITREOUS) {
                            ?>
                            <span><?php echo text("Vítreo " . $OSVITREOUS . " "); ?></span>
                        <?php }
                        ?>
                    </FONT></FONT></P>
        </TD>
    </TR>
    <TR>
        <TD COLSPAN=6 WIDTH=902 HEIGHT=13>
            <P ALIGN=CENTER>PROTOCOLOS UTILIZADOS QUE JUSTIFICAN LA PRESTACI&Oacute;N ADICIONAL REALIZADA:</P>
        </TD>
    </TR>
    <TR>
        <TD ROWSPAN=2 WIDTH=35 HEIGHT=13>
            <P ALIGN=CENTER>NO.</P>
        </TD>
        <TD ROWSPAN=2 WIDTH=254>
            <P ALIGN=CENTER><FONT COLOR="#000000"><SPAN>PROTOCOLOS</SPAN></FONT></P>
        </TD>
        <TD COLSPAN=3 WIDTH=189>
            <P ALIGN=CENTER><FONT COLOR="#000000">TIPO</FONT></P>
        </TD>
        <TD ROWSPAN=2 WIDTH=389>
            <P ALIGN=CENTER>OBERVACIONES</P>
        </TD>
    </TR>
    <TR>
        <TD WIDTH=47>
            <P ALIGN=CENTER>NAC.</P>
        </TD>
        <TD WIDTH=71>
            <P ALIGN=CENTER>INTERN.</P>
        </TD>
        <TD WIDTH=47>
            <P ALIGN=CENTER>MBE</P>
        </TD>
    </TR>

    <?php

    //DIAGNOSTICO DE INGRESO
    $DXjustificado = "SELECT `imp`.`code` as `diagnostico`  FROM `forms` AS `f`
                          LEFT JOIN `form_eye_mag_impplan` AS `imp` ON(`f`.`form_id` = `imp`.`form_id`)
                          WHERE `f`.`pid` = ? AND `f`.`encounter` = ? AND `f`.`formdir` = 'eye_mag' AND `deleted` = 0";
    $result =  sqlStatement($DXjustificado, array($_GET['patientid'],$_GET['visitid']));
    $i='0';
    // echo '<ol>';
    $a='1';
    while ($dxjust_list = sqlFetchArray($result)) {
        $newdata =  array (
            'code'          => $dxjust_list['diagnostico'],
        );
        $DXJSitems[$i] =$newdata;
        $i++;
    }

    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
    foreach ($DXJSitems as $item) {

        $i='0';

        $GuiasManejo = "SELECT * FROM list_options
                                          WHERE list_id = ? AND codes LIKE ? ";
        $GUIA = sqlStatement($GuiasManejo, array("Guias_de_Manejo","%" . substr($item['code'],0,3) . "%"));
        while ($Guias = sqlFetchArray($GUIA)) {
            $newGUIAdata =  array (
                'id'       => $Guias['option_id'],
                'title'       => $Guias['title'],
                'notes'       => $Guias['notes'],
            );
            print "<tr><TD WIDTH=35 HEIGHT=13><P ALIGN=CENTER>".$a++."</P></td>";
            print "<TD WIDTH=254><P ALIGN=CENTER><FONT SIZE=1 STYLE=\"font-size: 8pt\">".($newGUIAdata['notes'])."</FONT></P></td><TD WIDTH=47><P ALIGN=CENTER><BR>
                          			</P></TD><TD WIDTH=71><P ALIGN=CENTER>X</P></TD><TD WIDTH=47><P ALIGN=CENTER><BR></P></TD>";
            print "<TD WIDTH=389 STYLE='word-wrap:break-word'><P ALIGN=CENTER >";

            $GuiasBibliografia = sqlQuery("SELECT * FROM `list_options` WHERE `list_id` = 'Guias_Bibliografia' AND `option_id` = ? ", array($newGUIAdata['id']));
            print(wordwrap($GuiasBibliografia['notes'],60,"<br>\n",TRUE))."</P></TD></TR> ";
            $GUIA_items[$i] =$newGUIAdata;
            $i++;
        }
    }

    ?>
</TABLE>
<pagebreak>
    <TABLE WIDTH=909 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=6 CELLSPACING=0 FRAME=BELOW>
        <TR>
            <TD COLSPAN=4 WIDTH=897 HEIGHT=11>
                <P CLASS="western"><BR>
                </P>
            </TD>
        </TR>
        <TR>
            <TD COLSPAN=4 WIDTH=897 HEIGHT=28>
                <P ALIGN=CENTER><FONT SIZE=4><B>
                            Centro Oftalmológico ALTAVISION</B></FONT></P>
            </TD>
        </TR>
        <TR>
            <TD ROWSPAN=2 WIDTH=631 HEIGHT=28>
                <P ALIGN=CENTER><FONT SIZE=4 STYLE="font-size: 15pt"><B>Servicio de
                            <?php
                            echo substr(getProviderEspecialidad($providerID),12);
                            ?>
                        </B></FONT></P>
            </TD>
            <TD WIDTH=136>
                <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">Fecha
                        de Elaboraci&oacute;n:</FONT></P>
            </TD>
            <TD COLSPAN=2 WIDTH=105>
                <P ALIGN=CENTER><FONT COLOR="#ff0000"><FONT SIZE=1 STYLE="font-size: 6pt">
                            <?php
                            echo date("d/m/Y", strtotime($order_fecha['date_collected']));
                            ?>
                        </FONT></FONT></P>
            </TD>
        </TR>
        <TR>
            <TD WIDTH=136>
                <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">&Aacute;rea-Proceso:</FONT></P>
            </TD>
            <TD COLSPAN=2 WIDTH=105>
                <P ALIGN=CENTER><FONT COLOR="#ff0000"><FONT SIZE=1 STYLE="font-size: 5pt"><B>CONVENIO</B></FONT></FONT></P>
            </TD>
        </TR>
        <TR>
            <TD COLSPAN=3 WIDTH=822 HEIGHT=8>
                <P ALIGN=CENTER STYLE="margin-bottom: 0in">
                    <B>INFORME T&Eacute;CNICO-M&Eacute;DICO PARA LA JUSTIFICACI&Oacute;N
                        DE PRESTACIONES ADICIONALES A LAS AUTORIZADAS</B></P>
                <P ALIGN=CENTER><BR>
                </P>
            </TD>
            <TD WIDTH=63>
                <P ALIGN=CENTER><FONT SIZE=2 STYLE="font-size: 9pt">P&aacute;gina:
                        <SDFIELD TYPE=PAGE SUBTYPE=RANDOM FORMAT=PAGE>2</SDFIELD></FONT></P>
            </TD>
        </TR>
    </TABLE>

    <table BORDER=1 BORDERCOLOR="#000000" CELLPADDING=6 CELLSPACING=0>
        <TR>
            <TD COLSPAN=3 WIDTH=902 HEIGHT=44>
                <P ALIGN=CENTER>PROCEDIMIENTOS
                    ADICIONALES AL AUTORIZADO QUE SER&Aacute;N JUSTIFICADOS CON ESTE
                    INFORME:</P>
            </TD>
        </TR>
        <TR>
            <TD WIDTH=35 HEIGHT=13>
                <P ALIGN=CENTER>NO.</P>
            </TD>
            <TD WIDTH=254>
                <P ALIGN=CENTER>COD. TARIFARIO</P>
            </TD>
            <TD WIDTH=589>
                <P ALIGN=CENTER>DESCRIPCI&Oacute;N</P>
            </TD>
        </TR>
        <?php
        $i='0';
        $a='1';
        while ($order_list = sqlFetchArray($order_data)) {
            $newORDERdata =  array (
                'procedure_name'       => $order_list['procedure_name'],
                'procedure_code'       => $order_list['procedure_code'],
                'ojo'                  => $order_list['ojo'],
                'dosis'                => $order_list['dosis'],
                'clinical_hx'          => $order_list['clinical_hx'],
            );
            print "<tr><td>".$a++."</td>";
            print "<td>".($newORDERdata['procedure_code'])."</td>";

            print "<td>".($newORDERdata['procedure_name']) . " " . ($newORDERdata['ojo']) . " ";
            if ($newORDERdata['dosis']) {
                print ($newORDERdata['dosis']) . " dosis";
            }
            if ($newORDERdata['clinical_hx']) {
                print ($newORDERdata['clinical_hx']);
            }
            print "</td></tr>";
            $ORDER_items[$i] =$newORDERdata;
            $i++;
        }
        ?>
    </TABLE>
    <P STYLE="margin-bottom: 0in"><BR>
    </P>
    <P STYLE="margin-bottom: 0in"><B>Nota:
        </B><I><U><B>Este informe no justifica la prescripci&oacute;n de
                    medicamentos fuera del Cuadro Nacional de Medicamentos B&aacute;sicos
                    sin la autorizaci&oacute;n debida seg&uacute;n la normativa legal
                    vigente para el caso, Homologaciones de procedimientos y/o
                    laboratorios no autorizadas por el Ministerio de Salud P&uacute;blica,
                    facturaci&oacute;n indebida de acuerdo al Tarifario de Prestaciones
                    de Salud pertinente y otras situaciones de igual similitud.</B></U></I></P>
    <P STYLE="margin-bottom: 0in"><BR>
    </P>
    <P STYLE="margin-bottom: 0in"><BR>
    </P>
    <CENTER>
        <TABLE WIDTH=879 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=6 CELLSPACING=0>
            <COL WIDTH=146>
            <COL WIDTH=254>
            <COL WIDTH=260>
            <COL WIDTH=170>
            <TR>
                <TD WIDTH=146 HEIGHT=16>
                    <P ALIGN=CENTER><BR>
                    </P>
                </TD>
                <TD WIDTH=254>
                    <P ALIGN=CENTER><B>NOMBRE</B></P>
                </TD>
                <TD WIDTH=260>
                    <P ALIGN=CENTER><FONT COLOR="#000000"><B>CARGO/ESPECIALIDAD</B></FONT></P>
                </TD>
                <TD WIDTH=170>
                    <P ALIGN=CENTER><B>FIRMA Y SELLO</B></P>
                </TD>
            </TR>
            <TR>
                <TD WIDTH=146 HEIGHT=37>
                    <P ALIGN=CENTER>Elaborado por:</P>
                </TD>
                <TD WIDTH=254>
                    <P ALIGN=CENTER><FONT COLOR="#000000">
                            <?php
                            echo getProviderName($providerID);
                            ?>
                        </FONT></P>
                </TD>
                <TD WIDTH=260>
                    <P ALIGN=CENTER><?php
                        echo getProviderEspecialidad($providerID);
                        ?>
                    </P>
                </TD>
                <TD WIDTH=170>
                </TD>
            </TR>
            <TR>
                <TD WIDTH=146 HEIGHT=38>
                    <P ALIGN=CENTER>Revisado por:</P>
                </TD>
                <TD WIDTH=254>
                    <P ALIGN=CENTER>
                        <?php
                        echo getProviderName($providerID);
                        ?>
                    </P>
                </TD>
                <TD WIDTH=260>
                    <P ALIGN=CENTER><?php
                        echo getProviderEspecialidad($providerID);
                        ?>
                    </P>
                </TD>
                <TD WIDTH=170>
                </TD>
            </TR>
            <TR>
                <TD WIDTH=146 HEIGHT=38>
                    <P ALIGN=CENTER>Aprobado por:</P>
                </TD>
                <TD WIDTH=254>
                    <P ALIGN=CENTER>Dr. Mario Pólit Macias</P>
                </TD>
                <TD WIDTH=260>
                    <P ALIGN=CENTER>Cirujano en Vítreo y Retina
                    </P>
                </TD>
                <TD WIDTH=170>
                </TD>
            </TR>
        </table>

</BODY>
</HTML>

<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('plan_egreso_' . $titleres['lname'] . '_' . $titleres['fname'] . '.pdf', 'I'); // D = Download, I = Inline
}
?>
