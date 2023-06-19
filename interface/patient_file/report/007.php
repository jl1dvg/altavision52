<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
require_once("contra_template.php");
preg_match('/^(.*)_(\d+)$/', $key, $res);
$formdir = $res[1];
$form_id = $res[2];

$reason = getReason($form_encounter, $pid);
$providerID = getProviderIdOfEncounter($encounter);

//provider name explode
$fullName = getProviderNameConcat($providerID);

// Extraer los componentes del nombre
$nameComponents = explode(" ", $fullName);

// Obtener los componentes individuales
$mname = isset($nameComponents[0]) ? $nameComponents[0] : '';
$fname = isset($nameComponents[1]) ? $nameComponents[1] : '';
$lname = isset($nameComponents[2]) ? $nameComponents[2] : '';
$suffix = isset($nameComponents[3]) ? $nameComponents[3] : '';
$queryform = "select * from forms
                where
                pid=? and
                encounter=? and
                formdir = 'eye_mag' and
                deleted = 0";

$fechaINGRESO = sqlQuery($queryform, array($pid, $form_encounter));
?>
?>
<html>
<HEAD>
    <style>
        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 10px;
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
</HEAD>
<body>
<TABLE class="formulario">
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
        <td colspan="3" class="blanco"><?php echo $titleres['sex']; ?></td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
    </tr>
</TABLE>
<table>
    <colgroup>
        <col class="xl76" span="71">
    </colgroup>
    <tr>
        <td colspan="71" class="morado">B. CUADRO CLÍNICO DE INTERCONSULTA</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco_left"><?php
            echo wordwrap($reason, 165, "</td>
    </tr>
    <tr>
        <td colspan=\"71\" class=\"blanco_left\">"); ?></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco_left"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado">C. RESUMEN DEL CRITERIO CLÍNICO</td>
    </tr>
    <tr>
        <td class="blanco" style="border-right: none; text-align: left"><?php
            if ($formdir == 'eye_mag') {
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
                $encounter_data = sqlQuery($query, array($form_encounter, $pid));
                @extract($encounter_data);
                echo ExamOftal($form_encounter, $form_id, $formdir, $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $SCODVA, $SCOSVA, $ODIOPAP, $OSIOPAP, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                    $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS);
            }
            ?>
        </td>
    </tr>
</table>
<table>
    <TR>
        <TD class="morado" width="2%">D.</TD>
        <TD class="morado" width="17.5%">DIAGN&Oacute;STICOS</TD>
        <TD class="morado" width="17.5%" style="font-weight: normal; font-size: 6pt">PRE= PRESUNTIVO DEF= DEFINITIVO
        </TD>
        <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
        <TD class="morado" width="2%"><BR></TD>
        <TD class="morado" width="17.5%"><BR></TD>
        <TD class="morado" width="17.5%"><BR></TD>
        <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
    </TR>
    <TR>
        <td class="verde">1.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                echo "x";
            } ?></td>
        <td class="verde">4.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                echo "x";
            } ?></td>
    </TR>
    <TR>
        <td class="verde">2.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                echo "x";
            } ?></td>
        <td class="verde">5.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                echo "x";
            } ?></td>
    </TR>
    <TR>
        <td class="verde">3.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                echo "x";
            } ?></td>
        <td class="verde">6.</td>
        <td colspan="2" class="blanco" style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
        <td class="blanco"></td>
        <td class="blanco"><?php if (getDXoftalmo($form_id, $pid, "5")) {
                echo "x";
            } ?></td>
    </TR>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">E. PLAN DE DIAGNÓSTICO PROPUESTO</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left">
            <?php
            echo fetchEyeMagOrders($form_id, $pid);
            ?>
    </tr>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">F. PLAN TERAPEÚTICO PROPUESTO</td>
    </tr>
    <tr>
        <td colspan="71" class="blanco_left">
            <?php
            $planTerapeuticoOD = getPlanTerapeuticoOD($form_id, $pid);

            // Mostrar los nombres uno por uno
            if (empty($planTerapeuticoOD)) {
                echo "</td></tr>";
            } else {
                // Mostrar los resultados con un bucle foreach
                foreach ($planTerapeuticoOD as $resultado) {
                    echo $resultado . " en ojo derecho</td></tr><tr><td colspan=\"71\" class=\"blanco_left\">";
                }
            }
            $planTerapeuticoOI = getPlanTerapeuticoOI($form_id, $pid);

            // Mostrar los nombres uno por uno
            if (empty($planTerapeuticoOI)) {
                echo "</td></tr>";
            } else {
                // Mostrar los resultados con un bucle foreach
                foreach ($planTerapeuticoOI as $resultado) {
                    echo $resultado . " en ojo izquierdo</td></tr><tr><td colspan=\"71\" class=\"blanco_left\">";
                }
            } ?>
        </td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
    <tr>
        <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="71" class="morado">G. DATOS DEL PROFESIONAL RESPONSABLE</td>
    </tr>
    <tr class="xl78">
        <td colspan="8" class="verde">FECHA<br>
            <font class="font5">(aaaa-mm-dd)</font>
        </td>
        <td colspan="7" class="verde">HORA<br>
            <font class="font5">(hh:mm)</font>
        </td>
        <td colspan="21" class="verde">PRIMER NOMBRE</td>
        <td colspan="19" class="verde">PRIMER APELLIDO</td>
        <td colspan="16" class="verde">SEGUNDO APELLIDO</td>
    </tr>
    <tr>
        <td colspan="8" class="blanco"><?php echo date("d/m/Y", strtotime($fechaINGRESO['date'])); ?></td>
        <td colspan="7" class="blanco"></td>
        <td colspan="21" class="blanco"><?php echo $mname; ?></td>
        <td colspan="19" class="blanco"><?php echo $fname; ?></td>
        <td colspan="16" class="blanco"><?php echo $lname; ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
        <td colspan="26" class="verde">FIRMA</td>
        <td colspan="30" class="verde">SELLO</td>
    </tr>
    <tr>
        <td colspan="15" class="blanco" style="height: 40px"><?php echo getProviderRegistro($providerID); ?></td>
        <td colspan="26" class="blanco">&nbsp;</td>
        <td colspan="30" class="blanco">&nbsp;</td>
    </tr>
</table>

<table style="border: none">
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP/HCU-form.007/2021</FONT></B>
        </TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">INTERCONSULTA - INFORME</FONT></B></TD>
    </TR>
    ]
</TABLE>
</body>
</html>
