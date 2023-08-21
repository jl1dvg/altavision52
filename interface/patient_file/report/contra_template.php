<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
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

        td.lineatitulo {
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

        td.linearesumen {
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

        td.ultimalinea {
            border-top: 5px solid #808080;
            height: 10;
            background-color: #fff;
            font-size: 1;
        }

        td.lineatituloDX {
            border-top: 5px solid #808080;
            border-bottom: 1px solid #808080;
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 11;
            padding-left: 10px;
        }

        td.lineatituloDX1 {
            border-top: 5px solid #808080;
            border-left: 5px solid #808080;
            border-bottom: 1px solid #808080;
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 11;
            padding-left: 10px;
        }

        td.lineatituloCIE {
            border-top: 5px solid #808080;
            border-bottom: 1px solid #808080;
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 8;
            padding-left: 10px;
        }

        td.lineatituloCIEfinal {
            border-top: 5px solid #808080;
            border-bottom: 1px solid #808080;
            border-right: 5px solid #808080;
            height: 24;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 8;
            padding-left: 10px;
        }

    </STYLE>
    <link rel="stylesheet" type="text/css" href="reports.css">
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
                        COLSPAN=16 HEIGHT=23 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>INSTITUCI&Oacute;N
                                DEL
                                SISTEMA</FONT></B></TD>
                    <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" COLSPAN=19 ALIGN=CENTER
                        VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>UNIDAD OPERATIVA</FONT></B></TD>
                    <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ALIGN=CENTER
                        VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD. UO</FONT></B></TD>
                    <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-right: 1px solid #808080"
                        COLSPAN=12 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>COD.
                                LOCALIZACI&Oacute;N</FONT></B></TD>
                    <TD STYLE="border-top: 5px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080"
                        COLSPAN=12 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><B><FONT SIZE=1>NUMERO DE
                                HISTORIA CL&Iacute;NICA</FONT></B>
                    </TD>
                </TR>
                <TR>
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080"
                        COLSPAN=16 ROWSPAN=2 HEIGHT=55 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">
                                <?php
                                echo $titleres['pricelevel'];
                                ?></FONT></B></TD>
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
                        COLSPAN=19 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE><B><FONT FACE="Tahoma">ALTA VISION</FONT></B>
                    </TD>
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
                        COLSPAN=13 HEIGHT=21 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>APELLIDO
                            PATERNO</FONT>
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
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ROWSPAN=2
                        ALIGN=CENTER
                        VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>HORA</FONT></TD>
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=5 ROWSPAN=2
                        ALIGN=CENTER
                        VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>EDAD</FONT></TD>
                    <TD STYLE="border-top: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
                        COLSPAN=4 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>GENERO</FONT></TD>
                    <TD STYLE="border-top: 1px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080"
                        COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>ESTADO CIVIL</FONT></TD>
                    <TD STYLE="border-top: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC">
                        <FONT
                            SIZE=1>INSTRUCCI&Oacute;N</FONT></TD>
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080" COLSPAN=10 ROWSPAN=2
                        ALIGN=CENTER
                        VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>EMPRESA DONDE TRABAJA</FONT></TD>
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-right: 5px solid #808080"
                        COLSPAN=12 ROWSPAN=2 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SEGURO DE
                            SALUD</FONT>
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
                    <TD STYLE="border-bottom: 1px solid #808080" COLSPAN=10 ALIGN=CENTER VALIGN=MIDDLE
                        BGCOLOR="#CCFFCC"><FONT
                            SIZE=1>ULTIMO A&Ntilde;O APROBADO</FONT></TD>
                </TR>
                <TR>
                    <TD STYLE="border-top: 1px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080"
                        COLSPAN=8 HEIGHT=28 ALIGN=CENTER VALIGN=MIDDLE SDVAL="43056" SDNUM="1033;1033;D-MMM-YY">
                        <?php
                        $max_form_id = -1; // Inicializar con un valor negativo para garantizar que se tomará un valor mayor

                        foreach ($ar as $key => $val) {
                            // Aqui los hallazgos relevantes de la contrarreferencia
                            // in the format: <formdirname_formid>=<encounterID>
                            if ($key == 'pdf' || $key == 'include_demographics') {
                                continue; // Ignorar las claves 'pdf' y 'include_demographics'
                            }
                            preg_match('/^(.*)_(\d+)$/', $key, $res);
                            $form_id = $res[2];

                            // Verificar si el form_id actual es mayor al máximo encontrado hasta ahora
                            if ($form_id > $max_form_id) {
                                $max_form_id = $form_id;

                                if ($res[1] == 'newpatient') {
                                    $plan_sql = "SELECT * FROM forms WHERE form_id = ? AND formdir = 'newpatient'
                                                 ORDER BY date DESC LIMIT 1";
                                    $plan = sqlQuery($plan_sql, array($form_id));
                                    echo date("d/m/Y", strtotime($plan['date']));
                                }
                            }
                        }
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
            </TABLE>
            <table>
                <TR>
                    <TD class="verde" width="20%">ESTABLECIMIENTO
                        AL QUE SE
                        ENV&Iacute;A LA CONTRARREFERENCIA
                    </TD>
                    <TD class="blanco" width="20%"><?php
                        echo text($titleres['genericname1']);
                        ?></TD>
                    <TD class="verde" width="20%">SERVICIO QUE
                        CONTRAREFIERE
                    </TD>
                    <TD class="blanco" width="20%">OFTALMOLOGIA</TD>
                    <TD class="verde" width="5%"><BR></FONT></TD>
                    <TD class="blanco" width="5%"><BR></TD>
                    <TD class="verde" width="5%"><BR></FONT></TD>
                    <TD class="blanco" width="5%"><BR></TD>
                </TR>
            </table>

            <?php
            $first_encounter = end($ar);
            $reason_sql = "SELECT * FROM form_encounter WHERE encounter = ?";
            $reason = sqlQuery($reason_sql, array($first_encounter));
            ?>

            <table>
                <TR>
                    <TD class="morado" COLSPAN=1><B>1 RESUMEN DEL CUADRO CLÍNICO</B></TD>
                </TR>
                <TR>
                    <TD class="blanco_left" colspan=1>
                        <?php
                        echo wordwrap($reason['reason'], 160, "</TD></TR><TR><TD class='blanco_left'>");
                        ?>
                    </TD>
                </TR>
            </table>
            <?php
        }
    }
}
?>
<table>
    <TR>
        <TD class="morado"><b>2 HALLAZGOS RELEVANTES DE EXAMENES Y PROCEDIMIENTOS DIAGNOSTICOS</b></TD>
    </TR>
    <?php
    // Ordena el arreglo por clave de manera natural
    natsort($ar);

    foreach ($ar as $key => $val) {
        // Ignorar claves 'pdf'
        if ($key == 'pdf') {
            continue;
        }

        // Extraer form_id y formdir de la clave
        preg_match('/^(.*)_(\d+)$/', $key, $res);
        $form_id = $res[2];
        $formdir = $res[1];

        // Manejar casos específicos
        if ($formdir === 'eye_mag') {
            $encounter_data = getEyeMagEncounterData($val, $pid);
            if ($encounter_data) {
                @extract($encounter_data);
                $examOutput = ExamOftal($val, $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS,
                    $SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $OSCONJ, $ODCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS,
                    $ODDISC, $OSDISC, $ODCUP, $OSCUP, $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS);
                if (!empty($examOutput)) {
                    echo "<tr><td class='blanco_left'>";
                    echo $examOutput;
                }
            }
        } elseif (substr($formdir, 0, 3) == 'LBF' && substr($formdir, 0, 12) !== 'LBFprotocolo') {
            echo "<tr><td class='blanco_left'>";
            echo "<b>" . ImageStudyName($pid, $val, $form_id, $formdir) . ": </b>";
            echo ExamenesImagenes($pid, $val, $form_id, $formdir);
        }
    }

    ?>
    </TD>
    </TR>
</table>
<table>
    <TR>
        <TD class="morado"><B>3 TRATAMIENTO Y PROCEDIMIENTOS TERAPÉUTICOS REALIZADOS</B></TD>
    </TR>
    <TR>
        <TD class="blanco_left" COLSPAN=1>
            <?php
            foreach ($ar as $key => $val) {
                $form_encounter = $val;
                preg_match('/^(.*)_(\d+)$/', $key, $res);
                $form_id = $res[2];
                if ($res[1] == 'LBFprotocolo') {
                    echo protocolo($form_id, $form_encounter, 'LBFprotocolo');
                } elseif ($res[1] == 'care_plan') {
                    echo noInvasivos($form_id, $form_encounter);
                }
            }
            ?>
        </TD>
    </TR>
</table>

<?php
krsort($ar);
foreach ($ar as $key => $val) {
    $form_encounter = $val;
    preg_match('/^(.*)_(\d+)$/', $key, $res);
    $form_id = $res[2];
    if ($res[1] == 'eye_mag') {
        ?>
        <table>
            <TR>
                <TD class="lineatituloDX1" width="2%"><B>4</B></TD>
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
                <TD class="lineatituloCIEfinal" width="3.5%"><B>DEF</B></TD>
            </TR>
            <?php
            $dxTypes = array("0", "1", "2", "3", "4", "5");
            for ($i = 0; $i < count($dxTypes); $i += 2) {
                echo "<tr>";

                // Columna para el índice par
                echo "<td class='verde'>" . ($dxTypes[$i] + 1) . "</td>";
                $dx = getDXoftalmo($form_id, $pid, $dxTypes[$i]);
                $cie10 = getDXoftalmoCIE10($form_id, $pid, $dxTypes[$i]);
                echo "<td class='blanco_left' colspan='2'>" . $dx . "</td>";
                echo "<td class='blanco'>" . $cie10 . "</td>";
                echo "<td class='amarillo'><BR></td>";
                $hasDiagnosis = !empty($dx);
                if ($hasDiagnosis) {
                    echo "<td class='amarillo'>x</td>";
                } else {
                    echo "<td class='amarillo'><br></td>";
                }

                // Columna para el índice impar
                if ($i + 1 < count($dxTypes)) {
                    echo "<td class='verde'>" . ($dxTypes[$i + 1] + 1) . "</td>";
                    $dx = getDXoftalmo($form_id, $pid, $dxTypes[$i + 1]);
                    $cie10 = getDXoftalmoCIE10($form_id, $pid, $dxTypes[$i + 1]);
                    echo "<td class='blanco_left' colspan='2'>" . $dx . "</td>";
                    echo "<td class='blanco'>" . $cie10 . "</td>";
                    echo "<td class='amarillo'><BR></td>";
                    $hasDiagnosis = !empty($dx);
                    if ($hasDiagnosis) {
                        echo "<td class='amarillo'>x</td>";
                    } else {
                        echo "<td class='amarillo'><br></td>";
                    }
                }

                echo "</tr>";
            }
            ?>
        </table>
        <?php
        break;
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
    $form_encounter = $val;
    preg_match('/^(.*)_(\d+)$/', $key, $res);
    $form_id = $res[2];
    if ($res[1] == 'treatment_plan') {
        ?>
        <table>
            <TR>
                <TD class="morado"><b>5 PLAN DE TRATAMIENTO RECOMENDADO</B></TD>
            </TR>
            <TR>
                <TD class="blanco_left">
                    <?php
                    $plan_sql = "SELECT * FROM form_treatment_plan WHERE id = ?";
                    $plan = sqlQuery($plan_sql, array($form_id));
                    echo wordwrap($plan['recommendation_for_follow_up'], 160, "</TD></TR><TR><TD class='blanco_left'>");
                    ?>
                </TD>
            </TR>
            <TR>
                <TD class="blanco_left"></TD>
            </TR>
            <TR>
                <TD class="blanco_left"></TD>
            </TR>
        </table>
        <?php
        break;
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
    $form_encounter = $val;
    preg_match('/^(.*)_(\d+)$/', $key, $res);
    $form_id = $res[2];
    if ($res[1] == 'eye_mag') {
        $providerID_sql = "SELECT * FROM form_encounter WHERE encounter = ?";
        $providerID = sqlQuery($providerID_sql, array($form_encounter));
        ?>
        <table style="width: 75%">
            <TR>
                <TD class="verde" style="height: 40px">SALA</TD>
                <TD class="blanco"><BR></TD>
                <TD class="verde">CAMA</TD>
                <TD class="blanco"><BR></TD>
                <TD class="verde">PROFESIONAL</TD>
                <TD class="blanco">
                    <?php
                    echo getProviderName($providerID['provider_id']);
                    ?>
                </TD>
                <TD class="blanco">
                    <?php
                    echo getProviderRegistro($providerID['provider_id']);
                    ?>
                </TD>
                <td class="verde">FIRMA</TD>
            </TR>
        </table>
        <table style="border: none">
            <TR>
                <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B>SNS-MSP / HCU-form.053 / 2008</B>
                </TD>
                <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">CONTRAREFERENCIA</B>
                </TD>
            </TR>
        </TABLE>
        <?php
        break;
    }
}
?>
</body>
</html>
