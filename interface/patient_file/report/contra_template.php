<!DOCTYPE HTML>
<?php
function ExamOftal($form_encounter, $form_id, $formdir,$RBROW,$LBROW,$RUL,$LUL,$RLL,$LLL,$RMCT,$LMCT,$RADNEXA,$LADNEXA,$EXT_COMMENTS,$SCODVA,$SCOSVA,$ODIOPAP,$OSIOPAP,$ODCONJ,$OSCONJ,$ODCORNEA,$OSCORNEA,$ODAC,$OSAC,$ODLENS,$OSLENS,$ODIRIS,$OSIRIS,$ODDISC,$OSDISC,$ODCUP,$OSCUP,
                   $ODMACULA,$OSMACULA,$ODVESSELS,$OSVESSELS,$ODPERIPH,$OSPERIPH,$ODVITREOUS,$OSVITREOUS){
    $dateform = getEncounterDateByFormID($form_encounter, $form_id, $formdir);
    $ExamOFT = "<b>" . "(" . text(oeFormatSDFT(strtotime($dateform["date"]))) . ") " . "</b>";

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
        $ExamOFT = $ExamOFT . "Biomicroscopía: ";
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
        return wordwrap($ExamOFT, 148, "</TD></TR><TR><TD class='linearesumen'>");
    }


}

function ExamenesImagenes($pid, $encounter, $formid, $formdir)
{
    $queryform = sqlQuery("SELECT * FROM forms AS f
                           WHERE f.pid=? AND f.encounter=? AND form_id = ? AND f.formdir = ? 
                           AND f.formdir NOT LIKE 'LBFprotocolo' AND f.deleted = 0 
                           GROUP BY f.formdir ", array($pid, $encounter, $formid, $formdir));
            $Examen = "<b>" . $queryform['form_name'] . " </b>";

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
            $ExamenContent = $inf['etiqueta'] . ": " . $inf['informe'] . " ";
        }
    }
    $Exam = $Examen . $ExamenContent;
    return wordwrap($Exam, 148, "</TD></TR><TR><TD class='linearesumen'>");
}
function protocolo($form_id,$form_encounter,$formdir){
    $querylbfopr = sqlQuery("SELECT field_value from lbf_data 
                                    WHERE form_id = $form_id 
                                    AND field_id='Prot_opr'");
    $REALIZADA = $querylbfopr['field_value'];
    $dateform = getEncounterDateByFormID($form_encounter, $form_id, $formdir);
    echo "<b>" . "(" . text(oeFormatSDFT(strtotime($dateform["date"]))) . ") " . "</b>";
    if ($REALIZADA && $REALIZADA != '0') {
        $REALIZADA_items = explode('|', $REALIZADA);
              foreach ($REALIZADA_items as $item => $value) {
                  $QXpropuesta = ($value);
                  $IntervencionPropuesta = sqlquery("SELECT notes FROM `list_options`
                                                                 WHERE `list_id` = 'cirugia_propuesta_defaults' 
                                                                 AND `option_id` = '$QXpropuesta' ");
                  echo $IntervencionPropuesta['notes'] . " + ";
              }
        echo "</TD></TR><TR><TD class='linearesumen'>";
    }
}

?>

<html>
<head>
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

        td.lineatitulo{
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            text-align: left;
            font-size: 11;
            border-top: 5px solid #808080;
            border-bottom: 1px solid #808080;
            border-left: 5px solid #808080;
            border-right: 5px solid #808080;
            padding-left: 10px;
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
            font-size: 8.5;
        }
        td.ultimalinea{
            border-top: 5px solid #808080;
            height: 10;
            background-color: #fff;
            font-size: 1;
        }
        td.lineatituloDX{
            border-top: 5px solid #808080;
            border-bottom: 1px solid #808080;
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 11;
            padding-left: 10px;
        }
        td.lineatituloCIE{
            border-top: 5px solid #808080;
            border-bottom: 1px solid #808080;
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 8;
            padding-left: 10px;
        }

    </STYLE>
</head>
<body>
   <?php
   $arr = array_reverse($ar);
   foreach ($ar as $key => $val) {

   if ($key == 'pdf') {
       continue;
   }
   if (stristr($key, "include_")) {
   if ($val == "demographics") {
   $titleres = getPatientData($pid, "pubpid,fname,mname,lname,pricelevel, lname2,race, sex,status,genericval1,genericname1,providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");
   ?>

   <TABLE CELLSPACING=0 COLS=64 RULES=NONE BORDER=0>
       <COLGROUP>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
           <COL WIDTH=16>
       </COLGROUP>
       <TR>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080"
               COLSPAN=16 HEIGHT=23 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>INSTITUCI&Oacute;N DEL
                       SISTEMA</FONT></B></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" COLSPAN=19 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>UNIDAD OPERATIVA</FONT></B></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. UO</FONT></B></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD.
                       LOCALIZACI&Oacute;N</FONT></B></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=12 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>NUMERO DE HISTORIA CL&Iacute;NICA</FONT></B>
           </TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080"
               COLSPAN=16 ROWSPAN=2 HEIGHT=55 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">
                       <?php
                       echo $titleres['pricelevel'];
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=19 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">ALTA VISION</FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=5 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=1><BR></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PARROQUIA</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CANT&Oacute;N</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PROVINCIA</FONT></TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>TARQUI</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GYE</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>GUAYAS</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=4>
                       <?php
                       echo $titleres['pubpid'];
                       ?>
                   </FONT></B></TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080"
               COLSPAN=13 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO PATERNO</FONT>
           </TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=13 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO MATERNO</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=13 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PRIMER NOMBRE</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=13 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SEGUNDO NOMBRE</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>C&Eacute;DULA DE
                   CIUDADAN&Iacute;A</FONT></TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080"
               COLSPAN=13 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                   <?php
                   echo $titleres['lname'];
                   ?>
               </FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                   <?php
                   echo $titleres['lname2'];
                   ?>
               </FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                   <?php
                   echo $titleres['fname'];
                   ?>
               </FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=13 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                   <?php
                   echo $titleres['mname'];
                   ?>
               </FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE><B>
                   <?php
                   echo $titleres['pubpid'];
                   ?>
               </B></TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080"
               COLSPAN=8 ROWSPAN=2 HEIGHT=43 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>FECHA DE
                   REFERENCIA</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>HORA</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ROWSPAN=2 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>EDAD</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>GENERO</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>ESTADO CIVIL</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT
                   SIZE=1>INSTRUCCI&Oacute;N</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=10 ROWSPAN=2 ALIGN=CENTER
               VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>EMPRESA DONDE TRABAJA</FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=12 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SEGURO DE SALUD</FONT>
           </TD>
       </TR>
       <TR>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>M</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>F</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>SOL</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>CAS</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>DIV</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>VIU</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>U-L</FONT></B></TD>
           <TD STYLE="border-bottom: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT
                   SIZE=1>ULTIMO A&Ntilde;O APROBADO</FONT></TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080"
               COLSPAN=8 HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE SDVAL="43056" SDNUM="1033;1033;D-MMM-YY">
               <?php
               echo date("d/m/Y", strtotime($plan['alta']));
               ?>
           </TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=5 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;H:MM AM/PM"><BR></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=5 ALIGN=CENTER VALIGN=MIDDLE SDVAL="50"
               SDNUM="1033;"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4
                                                                               COLOR="#DD0806"><?php if ($titleres['sex'] == "Male") {
                           echo text("x");
                       }
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                       if ($titleres['sex'] == "Female") {
                           echo text("x");
                       }
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                       if ($titleres['status'] == "single") {
                           echo text("x");
                       }
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                       if ($titleres['status'] == "married") {
                           echo text("x");
                       }
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                       if ($titleres['status'] == "divorced") {
                           echo text("x");
                       }
                       ?>
                   </FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                       if ($titleres['status'] == "widowed") {
                           echo text("x");
                       }
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFCC"><B><FONT SIZE=4 COLOR="#DD0806"><?php
                       if ($titleres['status'] == "ul") {
                           echo text("x");
                       }
                       ?></FONT></B></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><B><FONT SIZE=4
                                                              COLOR="#DD0806"><?php echo text($titleres['race']); ?></FONT></B>
           </TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1><BR></FONT></TD>
           <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;0;000-00-0000">
               <B><?php echo text($titleres['genericval1']); ?></B></TD>
       </TR>
       <TR>
           <TD colspan="64" HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE></TD>
       </TR>
       <TR>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080"
               COLSPAN=12 HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>ESTABLECIMIENTO AL QUE SE
                   ENV&Iacute;A LA CONTRARREFERENCIA</FONT></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=16 ALIGN=LEFT VALIGN=MIDDLE><FONT SIZE=1><?php
                   echo text($titleres['genericname1']);
                   ?></FONT></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SERVICIO QUE CONTRAREFIERE</FONT>
           </TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=10 ALIGN=LEFT VALIGN=MIDDLE><FONT SIZE=1>OFTALMOLOGIA</FONT></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=6 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1><BR></FONT></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B>
           </TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
               COLSPAN=6 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1><BR></FONT></TD>
           <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080"
               COLSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFF99"><B><FONT SIZE=4 COLOR="#DD0806"><BR></FONT></B>
           </TD>
       </TR>
       <TR>
           <TD colspan="64" HEIGHT=5 ALIGN=CENTER VALIGN=MIDDLE></TD>
       </TR>
   </table>

   <?php
   $first_encounter = end($ar);
   $reason_sql = "SELECT * FROM form_encounter WHERE encounter = ?";
   $reason = sqlQuery($reason_sql, array($first_encounter));
   ?>

   <table CELLSPACING=0 COLS=1 RULES=NONE BORDER=0 WIDTH=100%>
       <TR>
           <TD class="lineatitulo" COLSPAN=1 ><B>1 RESUMEN DEL CUADRO CLÍNICO</B></TD>
       </TR>
       <TR>
           <TD class="linearesumen" colspan=1>
                   <?php
                   echo wordwrap($reason['reason'], 148, "</TD></TR><TR><TD class='linearesumen'>");
                   ?>
           </TD>
       </TR>
       <TR>
           <TD class="ultimalinea" colspan="1"><BR></TD>
       </TR>
   </table>
                   <?php
                   }
                   }
   }
   ?>
   <table CELLSPACING=0 COLS=1 RULES=NONE BORDER=0 WIDTH=100%>
       <TR>
           <TD class="lineatitulo"><b>2 HALLAZGOS RELEVANTES DE EXAMENES Y PROCEDIMIENTOS DIAGNOSTICOS</b></TD>
       </TR>
       <TR>
           <TD class="linearesumen">
   <?php
   natsort($ar);
   foreach ($ar as $key => $val) {
              // Aqui los hallazgos relevantes de la contrarreferencia
               // in the format: <formdirname_formid>=<encounterID>
            if ($key == 'pdf') {
                continue;
            }
               if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                   $form_encounter = $val;
                   preg_match('/^(.*)_(\d+)$/', $key, $res);
                   $form_id = $res[2];
                   $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
                   $dateres = getEncounterDateByEncounter($form_encounter);
                   $formId = getFormIdByFormdirAndFormid($res[1], $form_id);
                       if ($res[1] == 'eye_mag') {
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
                           $encounter_data = sqlQuery($query, array($val, $pid));
                           @extract($encounter_data);
                           echo ExamOftal($form_encounter, $form_id, $res[1], $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                               $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS);
                       }
                       if (substr($res[1], 0, 3) == 'LBF') {
                           echo "<tr><td class='linearesumen'>";
                           echo ExamenesImagenes($pid,$form_encounter,$form_id,$res[1]);
                       }
               }
        }
   ?>
               </TD>
       </TR>
       <TR>
           <TD class="ultimalinea" colspan="1"><BR></TD>
       </TR>
   </table>

   <table CELLSPACING=0 COLS=13 RULES=NONE BORDER=0 WIDTH=100%>
       <TR>
           <TD class="lineatitulo"><B>3 TRATAMIENTO Y PROCEDIMIENTOS TERAPÉUTICOS REALIZADOS</B></TD>
       </TR>
       <TR>
           <TD class="linearesumen" COLSPAN=1>
      <?php
            foreach ($ar as $key => $val) {
              if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                  $form_encounter = $val;
                  preg_match('/^(.*)_(\d+)$/', $key, $res);
                  $form_id = $res[2];
                  $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
                  $dateres = getEncounterDateByEncounter($form_encounter);
                  $dateform = getEncounterDateByFormID($form_encounter, $form_id, $res[1]);
                  $formId = getFormIdByFormdirAndFormid($res[1], $form_id);
                  if ($res[1] == 'LBFprotocolo') {
                      echo protocolo($form_id,$form_encounter,'LBFprotocolo');
                  }
              }
            }
      ?>
           </TD>
       </TR>
       <TR>
           <TD class="ultimalinea" colspan="13" ALIGN=LEFT><BR></TD>
       </TR>
   </table>

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
       krsort($ar);
       foreach ($ar as $key => $val) {
           // Aqui los hallazgos relevantes de la contrarreferencia
           // in the format: <formdirname_formid>=<encounterID>
           if ($key == 'pdf') {
               continue;
           }
           if ($key == 'include_demographics') {
               continue;
           }
                   if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                       $form_encounter = $val;
                       preg_match('/^(.*)_(\d+)$/', $key, $res);
                       $form_id = $res[2];
                       $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
                       $dateres = getEncounterDateByEncounter($form_encounter);
                       $formId = getFormIdByFormdirAndFormid($res[1], $form_id);
                       if ($res[1] == 'eye_mag') {
                           ?>

   <table CELLSPACING=0 RULES=NONE BORDER=0 WIDTH=100%>
       <TR>
           <TD class="lineatituloDX" width="2%"><B>4</B></TD>
           <TD class="lineatituloDX" width="17.5%"><B>DIAGN&Oacute;STICOS</B></TD>
           <TD class="lineatituloCIE" width="17.5%"><B>PRE= PRESUNTIVO DEF= DEFINITIVO</B></TD>
           <TD class="lineatituloCIE" width="6%"><B>CIE</B></TD>
           <TD class="lineatituloCIE" width="3.5%"><B>PRE</B></TD>
           <TD class="lineatituloCIE" width="3.5%"><B>DEF</B></TD>
           <TD class="lineatituloDX" width="2%"><B><BR></B></TD>
           <TD class="lineatituloDX" width="17.5%"><B><BR></B></TD>
           <TD class="lineatituloDX" width="17.5%"><BR></TD>
           <TD class="lineatituloCIE" width="6%"><B>CIE</B></TD>
           <TD class="lineatituloCIE" width="3.5%"><B>PRE</B></TD>
           <TD class="lineatituloCIE" width="3.5%"><B>DEF</B></TD>
       </TR>
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



                           <?php
                           break;
                       }
                       if ($res[1] == 'treatment_plan'){
                           echo $res[1] . $res[2];
                       }
                   }
       }
   ?>
   <?php
   //5 PLAN DE TRATAMIENTO RECOMENDADO
   foreach ($ar as $key => $val) {
       // Aqui los hallazgos relevantes de la contrarreferencia
       // in the format: <formdirname_formid>=<encounterID>
       if ($key == 'pdf') {
           continue;
       }
       if ($key == 'include_demographics') {
           continue;
       }
       if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
           $form_encounter = $val;
           preg_match('/^(.*)_(\d+)$/', $key, $res);
           $form_id = $res[2];
           $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
           $dateres = getEncounterDateByEncounter($form_encounter);
           $formId = getFormIdByFormdirAndFormid($res[1], $form_id);
           if ($res[1] == 'treatment_plan') {
               ?>
               <table CELLSPACING=0 COLS=1 RULES=NONE BORDER=0 WIDTH=100%>
                   <TR>
                       <TD class="lineatitulo"><b>5 PLAN DE TRATAMIENTO RECOMENDADO</B></TD>
                   </TR>
                   <TR>
                       <TD class="linearesumen">
                           <?php
                           $plan_sql = "SELECT * FROM form_treatment_plan WHERE id = ?";
                           $plan = sqlQuery($plan_sql, array($form_id));
                           echo wordwrap($plan['recommendation_for_follow_up'], 145, "</TD></TR><TR><TD class='linearesumen'>");
                           ?>
                       </TD>
                   </TR>
                   <TR>
                       <TD class="linearesumen"></TD>
                   </TR>
                   <TR>
                       <TD class="linearesumen"></TD>
                   </TR>
                   <TR>
                       <TD class="ultimalinea"><BR></TD>
                   </TR>
               </table>
               <?php
               break;
           }
       }
   }
   ?>

   <?php
   //5 FIRMA Y SELLO DEL MEDICO
   foreach ($ar as $key => $val) {
       // Aqui los hallazgos relevantes de la contrarreferencia
       // in the format: <formdirname_formid>=<encounterID>
       if ($key == 'pdf') {
           continue;
       }
       if ($key == 'include_demographics') {
           continue;
       }
       if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
           $form_encounter = $val;
           preg_match('/^(.*)_(\d+)$/', $key, $res);
           $form_id = $res[2];
           $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
           $dateres = getEncounterDateByEncounter($form_encounter);
           $formId = getFormIdByFormdirAndFormid($res[1], $form_id);
           if ($res[1] == 'eye_mag') {
               $providerID_sql = "SELECT * FROM form_encounter WHERE encounter = ?";
               $providerID = sqlQuery($providerID_sql, array($form_encounter));
               ?>
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
                               echo getProviderName($providerID['provider_id']);
                               ?>
                           </FONT></TD>
                       <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="10%" ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                               <?php
                               echo getProviderRegistro($providerID['provider_id']);
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
               <?php
               break;
           }
       }
   }
   ?>
</body>
</html>
