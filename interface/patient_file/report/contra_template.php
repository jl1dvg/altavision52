<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
?>

<html>
<head>
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

            <TABLE>
                <tr>
                    <td class="verde" colspan="16">INSTITUCI&Oacute;N DEL SISTEMA</td>
                    <td class="verde" colspan="19">UNIDAD OPERATIVA</td>
                    <td class="verde" colspan="5">COD. UO</td>
                    <td class="verde" colspan="12">COD. LOCALIZACI&Oacute;N</td>
                    <td class="verde" colspan="12" rowspan="2">NUMERO DE HISTORIA CL&Iacute;NICA</td>
                </tr>
                <tr>
                    <td class="blanco" colspan="16" rowspan="2">
                        <b><?php echo $titleres['pricelevel']; ?></b>
                    </td>
                    <td class="blanco" colspan="19" rowspan="2"><b>ALTA VISION</b>
                    </td>
                    <td class="blanco" colspan="5" rowspan="2">
                        <br>
                    </td>
                    <td class="verde" colspan="4">
                        PARROQUIA
                    </td>
                    <td class="verde" colspan="4">
                        CANT&Oacute;N
                    </td>
                    <td class="verde" colspan="4">
                        PROVINCIA
                    </td>
                </tr>
                <tr>
                    <td class="blanco" colspan="4">
                        TARQUI
                    </td>
                    <td class="blanco" colspan="4">
                        GYE
                    </td>
                    <td class="blanco" colspan="4">
                        GUAYAS
                    </td>
                    <td class="blanco" colspan="12">
                        <b>
                            <?php echo $titleres['pubpid']; ?>
                        </b>
                    </td>
                </tr>
                <tr>
                    <td class="verde" colspan="13" height="21">
                        APELLIDO PATERNO
                    </td>
                    <td class="verde" colspan="13">
                        APELLIDO MATERNO
                    </td>
                    <td class="verde" colspan="13">
                        PRIMER NOMBRE
                    </td>
                    <td class="verde" colspan="13">
                        SEGUNDO NOMBRE
                    </td>
                    <td class="verde" colspan="12" style="border-right: 5px solid #808080">
                        C&Eacute;DULA DE CIUDADAN&Iacute;A
                    </td>
                </tr>
                <tr>
                    <td class="blanco" colspan="13">
                        <?php echo $titleres['lname']; ?>
                    </td>
                    <td class="blanco" colspan="13">
                        <?php echo $titleres['lname2']; ?>
                    </td>
                    <td class="blanco" colspan="13">
                        <?php echo $titleres['fname']; ?>
                    </td>
                    <td class="blanco" colspan="13">
                        <?php echo $titleres['mname']; ?>
                    </td>
                    <td class="blanco" colspan="12">
                        <b><?php echo $titleres['pubpid']; ?></b>
                    </td>
                </tr>
                <tr>
                    <td class="verde" colspan="8" rowspan="2">
                        Fecha de Referencia
                    </td>
                    <td class="verde" colspan="5" rowspan="2">
                        Hora
                    </td>
                    <td class="verde" colspan="5" rowspan="2">
                        Edad
                    </td>
                    <td class="verde" colspan="4">
                        Género
                    </td>
                    <td class="verde" colspan="10">
                        Estado Civil
                    </td>
                    <td class="verde" colspan="10">
                        Instrucción
                    </td>
                    <td class="verde" colspan="10" rowspan="2">
                        Empresa donde Trabaja
                    </td>
                    <td class="verde" colspan="12" rowspan="2">
                        Seguro de Salud
                    </td>
                </tr>
                <tr>
                    <td class="verde" colspan="2">
                        <b>M</b>
                    </td>
                    <td class="verde" colspan="2">
                        <b>F</b>
                    </td>
                    <td class="verde" colspan="2">
                        <b>SOL</b>
                    </td>
                    <td class="verde" colspan="2">
                        <b>CAS</b>
                    </td>
                    <td class="verde" colspan="2">
                        <b>DIV</b>
                    </td>
                    <td class="verde" colspan="2">
                        <b>VIU</b>
                    </td>
                    <td class="verde" colspan="2">
                        <b>U-L</b>
                    </td>
                    <td class="verde" colspan="10">
                        Último Año Aprobado
                    </td>
                </tr>
                <tr>
                    <td class="blanco" colspan="8" rowspan="1" height="28">
                        <?php
                        // Imprimir UNA sola fecha de referencia: la fecha del último formulario `newpatient`
                        $max_newpatient_form_id = -1;
                        $max_newpatient_date = null;

                        foreach ($ar as $key => $val) {
                            if ($key === 'pdf' || $key === 'include_demographics') {
                                continue;
                            }

                            // Asegurar formato esperado: formdir_formid
                            if (!preg_match('/^(.*)_(\d+)$/', $key, $res)) {
                                continue;
                            }

                            $formdir = $res[1];
                            $form_id = (int)$res[2];

                            if ($formdir !== 'newpatient') {
                                continue;
                            }

                            // Quedarnos con el mayor form_id de newpatient
                            if ($form_id > $max_newpatient_form_id) {
                                $max_newpatient_form_id = $form_id;

                                $plan_sql = "SELECT `date` FROM `forms` WHERE `form_id` = ? AND `formdir` = 'newpatient' LIMIT 1";
                                $plan = sqlQuery($plan_sql, array($form_id));

                                if (!empty($plan['date'])) {
                                    $max_newpatient_date = $plan['date'];
                                }
                            }
                        }

                        if ($max_newpatient_date) {
                            echo date('d/m/Y', strtotime($max_newpatient_date));
                        }
                        ?>
                    </td>
                    <td class="blanco" colspan="5" rowspan="1"></td>
                    <td class="blanco" colspan="5" rowspan="1" align="center">
                        <?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime($plan['date']))); ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php if ($titleres['sex'] == "Male") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php
                        if ($titleres['sex'] == "Female") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php
                        if ($titleres['status'] == "single") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php
                        if ($titleres['status'] == "married") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php
                        if ($titleres['status'] == "divorced") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php
                        if ($titleres['status'] == "widowed") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="amarillo" colspan="2" rowspan="1">
                        <?php
                        if ($titleres['status'] == "ul") {
                            echo text("x");
                        }
                        ?>
                    </td>
                    <td class="blanco" colspan="10" rowspan="1" align="center">
                        <?php echo text($titleres['race']); ?>
                    </td>
                    <td class="blanco" colspan="10" rowspan="1"><br></td>
                    <td class="blanco" colspan="12" rowspan="1">
                        <b><?php echo text($titleres['genericval1']); ?></b>
                    </td>
                </tr>
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
                    <TD class="morado">1 RESUMEN DEL CUADRO CLÍNICO</TD>
                </TR>
                <TR>
                    <TD class="blanco_left">
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
    <tr>
        <td class="morado">2 HALLAZGOS RELEVANTES DE EXAMENES Y PROCEDIMIENTOS DIAGNOSTICOS</td>
    </tr>
    <?php
    $items = [];

    foreach ($ar as $key => $val) {
        // Ignorar claves 'pdf'
        if ($key == 'pdf') {
            continue;
        }

        // Comprobar si la clave comienza con 'eye_mag' o 'LBF'
        if (strpos($key, 'eye_mag') === 0 || strpos($key, 'LBF') === 0 || strpos($key, 'care_plan') === 0) {
            // Extraer form_id y formdir de la clave
            preg_match('/^(.*)_(\d+)$/', $key, $res);
            $form_id = $res[2];
            $formdir = $res[1];

            $dateArray = getEncounterDateByEncounter($val);
            $date = $dateArray['date'];

            // Almacenar en el arreglo con la fecha como clave
            if (!isset($items[$date])) {
                $items[$date] = [];
            }
            $items[$date][] = ['val' => $val, 'formdir' => $formdir, 'form_id' => $form_id];
        }
    }

    // Ordenar el arreglo por clave (que son fechas)
    ksort($items);

    // Procesar y mostrar cada elemento ordenado
    foreach ($items as $date => $entries) {
        // Inicia una nueva fila para cada fecha
        //echo "<tr><td class='blanco_left'><strong>Fecha: $date</strong></td></tr>";

        foreach ($entries as $entry) {
            $val = $entry['val'];
            $formdir = $entry['formdir'];
            $form_id = $entry['form_id'];

            // Obtener datos adicionales
            if ($formdir === 'eye_mag') {
                $encounter_data = getEyeMagEncounterData($val, $pid);
                if ($encounter_data) {
                    extract($encounter_data);
                    $examOutput = ExamOftal($val, $CC1 ?? '', $RBROW ?? '', $LBROW ?? '', $RUL ?? '', $LUL ?? '', $RLL ?? '', $LLL ?? '', $RMCT ?? '', $LMCT ?? '', $RADNEXA ?? '', $LADNEXA ?? '', $EXT_COMMENTS ?? '',
                        $SCODVA ?? '', $SCOSVA ?? '', $ODVA ?? '', $OSVA ?? '', $ODIOPAP ?? '', $OSIOPAP ?? '', $ODCONJ ?? '', $OSCONJ ?? '', $ODCORNEA ?? '', $OSCORNEA ?? '', $ODAC ?? '', $OSAC ?? '', $ODLENS ?? '', $OSLENS ?? '', $ODIRIS ?? '', $OSIRIS ?? '',
                        $ODDISC ?? '', $OSDISC ?? '', $ODCUP ?? '', $OSCUP ?? '', $ODMACULA ?? '', $OSMACULA ?? '', $ODVESSELS ?? '', $OSVESSELS ?? '', $ODPERIPH ?? '', $OSPERIPH ?? '', $ODVITREOUS ?? '', $OSVITREOUS ?? '');
                    if (!empty($examOutput)) {
                        // Establecer el límite de caracteres por línea (ajustar según el diseño)
                        $line_limit = 165; // Ajusta este valor según el ancho disponible en tu formulario

                        // Dividir el texto en líneas controladas por el límite de caracteres
                        $wrappedText = wordwrap($examOutput, $line_limit, "\n", true);

                        // Reemplazar los saltos de línea con la estructura de tabla
                        $lines = explode("\n", $wrappedText);
                        foreach ($lines as $line) {
                            echo "<tr><td class='blanco_left'>$line</td></tr>";
                        }
                    } else {
                        echo 'no hay examen';
                    }
                }
            } elseif (substr($formdir, 0, 3) == 'LBF' && substr($formdir, 0, 12) !== 'LBFprotocolo') {
                // Llamar a la función unificada para obtener el informe completo (nombre del estudio + descripción)
                $imageReport = ImageStudyReport($pid, $val, $form_id, $formdir);

                // Definir el límite de caracteres por línea
                $line_limit = 165; // Ajusta este valor según el ancho disponible en tu formulario

                // Aplicar `wordwrap` para ajustar el texto al límite de caracteres por línea
                $wrappedText = wordwrap($imageReport, $line_limit, "\n", true);

                // Dividir el texto en líneas según el límite de caracteres
                $lines = explode("\n", $wrappedText);

                // Iterar sobre las líneas y mostrarlas en la tabla
                foreach ($lines as $line) {
                    echo "<tr><td class='blanco_left'>$line</td></tr>";
                }
            }
        }
    }
    ?>
</table>
<table>
    <TR>
        <TD class="morado"><B>3 TRATAMIENTO Y PROCEDIMIENTOS TERAPÉUTICOS REALIZADOS</B></TD>
    </TR>
    <TR>
        <TD class="blanco_left" COLSPAN=1>
            <?php
            foreach ($items as $date => $entries) {
                foreach ($entries as $entry) {
                    $val = $entry['val'];
                    $formdir = $entry['formdir'];
                    $form_id = $entry['form_id'];

                    // Obtener datos adicionales
                    if ($formdir === 'LBFprotocolo') {
                        echo protocolo($form_id, $val, 'LBFprotocolo');
                    } elseif ($formdir === 'care_plan') {
                        echo noInvasivos($form_id, $val);
                    }
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
                    echo getProviderNameConcat($providerID['provider_id']);
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
