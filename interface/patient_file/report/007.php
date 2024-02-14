<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
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
                formdir = 'newpatient' and
                deleted = 0";

$fechaINGRESO = sqlQuery($queryform, array($pid, $form_encounter));
?>
?>
<html>
<HEAD>
    <link rel="stylesheet" type="text/css" href="reports.css">
</HEAD>
<body>
<?php
$dxResult = getDXoftalmo($form_id, $pid, "0");

// Verificamos si la variable $dxResult no está vacía
if (!empty($dxResult)) {
    ?>
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
            <td colspan="3"
                class="blanco"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime($plan['date']))); ?></td>
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
            <?php
            if ($formdir === 'eye_mag') {
                $encounter_data = getEyeMagEncounterData($form_encounter, $pid);
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
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
            <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                    echo "x";
                } ?></td>
            <td class="verde">4.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
            <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                    echo "x";
                } ?></td>
        </TR>
        <TR>
            <td class="verde">2.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
            <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "1")) {
                    echo "x";
                } ?></td>
            <td class="verde">5.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
            <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                    echo "x";
                } ?></td>
        </TR>
        <TR>
            <td class="verde">3.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
            <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                    echo "x";
                } ?></td>
            <td class="verde">6.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
            <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "5")) {
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
                echo getPlanTerapeuticoOD($form_id, $pid);
                echo getPlanTerapeuticoOI($form_id, $pid);
                ?>
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
            <td colspan="8" class="blanco"><?php echo date("Y/m/d", strtotime($fechaINGRESO['date'])); ?></td>
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
            <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1
                                                                     COLOR="#000000">SNS-MSP/HCU-form.007/2021</FONT></B>
            </TD>
            <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">INTERCONSULTA - INFORME</FONT></B>
            </TD>
        </TR>
        ]
    </TABLE>
    <?php
}
$query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
$result = sqlStatement($query, array($form_id, $pid));

if (sqlNumRows($result) > 0) {
    ?>
    <pagebreak>
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
                <td colspan="3"
                    class="blanco"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime($plan['date']))); ?></td>
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
                <td colspan="71" class="morado">B. SERVICIO Y PRIORIDAD DE ATENCIÓN</td>
            </tr>
            <tr>
                <td colspan="25" class="verde" style="width: 30%">SERVICIO</td>
                <td colspan="17" class="verde" style="width: 25%">ESPECIALIDAD</td>
                <td colspan="6" class="verde" style="width: 10%">CAMA</td>
                <td colspan="6" class="verde" style="width: 10%">SALA</td>
                <td colspan="17" class="verde" style="border-right: none">PRIORIDAD</td>
            </tr>
            <tr>
                <td colspan="5" class="verde">EMERGENCIA</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="7" class="verde">CONSULTA EXTERNA</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="7" class="verde">HOSPITALIZACIÓN</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="17" class="blanco">OFTALMOLOGIA</td>
                <td colspan="6" class="blanco">&nbsp;</td>
                <td colspan="6" class="blanco">&nbsp;</td>
                <td colspan="4" class="verde">URGENTE</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="3" class="verde">RUTINA</td>
                <td colspan="2" class="blanco">X</td>
                <td colspan="4" class="verde">CONTROL</td>
                <td colspan="2" class="blanco" style="border-right: none"></td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="71" class="morado">C. ESTUDIO DE IMAGENOLOGÍA SOLICITADO</td>
            </tr>
            <tr>
                <td colspan="6" class="verde">RX<br>CONVENCIONAL</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="4" class="verde">RX<br>PORTÁTIL</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="6" class="verde">TOMOGRAFÍA</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="5" class="verde">RESONANCIA</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="5" class="verde">ECOGRAFÍA</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="6" class="verde">MAMOGRAFÍA</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="7" class="verde">PROCEDIMIENTO</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="3" class="verde">OTRO</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="5" class="verde">SEDACIÓN</td>
                <td colspan="2" class="verde">SI</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="2" class="verde">NO</td>
                <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="8" class="verde">DESCRIPCIÓN</td>
                <td colspan="63" class="blanco" style="border-right: none; text-align: left">
                    <?php
                    echo fetchEyeMagOrders($form_id, $pid);
                    ?>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="40" class="morado">D. MOTIVO DE LA SOLICITUD</td>
                <td colspan="31" class="morado" style="font-weight: normal; font-size: 6pt; text-align: right">REGISTRAR
                    LAS
                    RAZONES PARA SOLICITAR
                    EL ESTUDIO
                </td>
            </tr>
            <tr>
                <td colspan="10" class="verde"><font class="font6">FUM</font><font class="font5"><br>(aaaa-mm-dd)</font>
                </td>
                <td colspan="12" class="blanco">&nbsp;</td>
                <td colspan="14" class="verde">PACIENTE CONTAMINADO</td>
                <td colspan="2" class="verde">SI</td>
                <td colspan="2" class="blanco">&nbsp;</td>
                <td colspan="2" class="verde">NO</td>
                <td colspan="2" class="blanco">X</td>
                <td colspan="27" class="blanco">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="71" class="blanco" style="text-align: left;">SE SOLICITA EXAMENES PARA
                    CONTINUAR TRATAMIENTO
                </td>
            </tr>
            <tr>
                <td colspan="71" class="blanco">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="71" class="blanco">&nbsp;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="morado">E. RESUMEN CLÍNICO ACTUAL
                    <span style="font-weight: normal; font-size: 6pt; text-align: right">
                REGISTRAR DE MANERA OBLIGATORIA EL CUADRO CLÍNICO ACTUAL DEL PACIENTE
            </span>
                </td>
            </tr>
            <tr>
                <?php
                if ($formdir === 'eye_mag') {
                    $encounter_data = getEyeMagEncounterData($form_encounter, $pid);
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
                }
                ?>
                </td>
            </tr>
        </table>
        <table>
            <TR>
                <TD class="morado" width="2%">F.</TD>
                <TD class="morado" width="17.5%">DIAGN&Oacute;STICOS</TD>
                <TD class="morado" width="17.5%" style="font-weight: normal; font-size: 6pt">PRE= PRESUNTIVO DEF=
                    DEFINITIVO
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
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
                <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                        echo "x";
                    } ?></td>
                <td class="verde">4.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
                <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                        echo "x";
                    } ?></td>
            </TR>
            <TR>
                <td class="verde">2.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
                <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "1")) {
                        echo "x";
                    } ?></td>
                <td class="verde">5.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
                <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                        echo "x";
                    } ?></td>
            </TR>
            <TR>
                <td class="verde">3.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
                <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                        echo "x";
                    } ?></td>
                <td class="verde">6.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
                <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "5")) {
                        echo "x";
                    } ?></td>
            </TR>
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
                <td colspan="8" class="blanco"><?php echo date("Y/m/d", strtotime($fechaINGRESO['date'])); ?></td>
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
                <td colspan="15" class="blanco"
                    style="height: 40px"><?php echo getProviderRegistro($providerID); ?></td>
                <td colspan="26" class="blanco">&nbsp;</td>
                <td colspan="30" class="blanco">&nbsp;</td>
            </tr>
        </table>

        <table style="border: none">
            <TR>
                <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP / HCU-form.012A
                            /
                            2008</FONT></B>
                </TD>
                <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">IMAGENOLOGIA SOLICITUD</FONT></B>
                </TD>
            </TR>
            ]
        </TABLE>
    </pagebreak>
    <?php
}
?>
</body>
</html>
