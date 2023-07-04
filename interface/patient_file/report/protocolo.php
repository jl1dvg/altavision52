<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
preg_match('/^(.*)_(\d+)$/', $key, $res);
$formdir = $res[1];
$form_id = $res[2];

$providerID = getProviderIdOfEncounter($form_encounter);
$providerNAME = getProviderName($providerID);

$resultado = getProtocolDate($form_id, $form_encounter);

if ($resultado) {
    $dateddia = $resultado['dia'];
    $datedmes = $resultado['mes'];
    $datedano = $resultado['ano'];

    // Realizar cualquier otra acción con los componentes de la fecha
} else {
    // La fecha del protocolo no se encontró, manejar este caso según corresponda
}
$prot_hfin = getFieldValue($form_id, "Prot_hfin"); // Obtener el valor de la hora
$prot_hfin_timestamp = strtotime($prot_hfin); // Convertir la hora a un timestamp

// Restar una hora y media (5400 segundos) al timestamp
$prot_hfin_nueva_timestamp = $prot_hfin_timestamp - 5400;
$prot_hfin_fin_timestamp = $prot_hfin_timestamp + 2700;

// Formatear el nuevo timestamp en formato de hora (HH:MM)
$prot_hfin_nueva = date("H:i", $prot_hfin_nueva_timestamp);
$prot_hfin_fin = date("H:i", $prot_hfin_fin_timestamp);

$codes = getCPT4Codes($titleres['pricelevel'], $form_id);

?>
<html>
<HEAD>
    <style>
        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
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

        td.verde_left {
            height: 23px;
            text-align: left;
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

        td.blanco_break {
            border-left: 3px solid #808080;
            border-right: 3px solid #808080;
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
<BODY>
<TABLE>
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
        <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?>
        </td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">X</td>
    </tr>
</TABLE>
<table>
    <tr>
        <td colspan="10" class="morado">B. DIAGNÓSTICOS</td>
        <td colspan="2" class="morado" style="text-align: center">CIE</td>
    </tr>
    <tr>
        <td colspan="2" width="18%" rowspan="3" class="verde_left">Pre Operatorio:</td>
        <td class="verde_left" width="2%">1.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre")); ?>
        </td>
        <td class="blanco" width="20%" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left" width="2%">2.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre2")); ?>
        </td>
        <td class="blanco" width="20%" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre2"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left" width="2%">3.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre3")); ?>
        </td>
        <td class="blanco" width="20%" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre3"), 6); ?></td>
    </tr>
    <tr>
        <td colspan="2" rowspan="3" class="verde_left">Post Operatorio:</td>
        <td class="verde_left">1.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost")); ?></td>
        <td class="blanco" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpost"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left">2.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost2")); ?></td>
        <td class="blanco" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpost2"), 6); ?></td>
    </tr>
    <tr>
        <td class="verde_left">3.</td>
        <td class="blanco_left"
            colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost3")); ?></td>
        <td class="blanco" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpost3"), 6); ?></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="11" class="morado">C. PROCEDIMIENTO</td>
        <td colspan="2" class="verde_left" style="text-align: center">Electiva</td>
        <td colspan="1" class="blanco" style="text-align: center">X</td>
        <td colspan="2" class="verde_left" style="text-align: center">Emergencia</td>
        <td colspan="1" class="blanco" style="text-align: center"></td>
        <td colspan="2" class="verde_left" style="text-align: center">Urgencia</td>
        <td colspan="1" class="blanco" style="text-align: center"></td>
    </tr>
    <tr>
        <td colspan="2" class="verde_left">Proyectado:</td>
        <td class="blanco_left"
            colspan="18"><?php
            echo obtenerIntervencionesPropuestas(getFieldValue($form_id, "Prot_opp")) . " ";
            $ojoValue = getFieldValue($form_id, "Prot_ojo");

            $mensajeOjo = [
                'OI' => 'Ojo izquierdo',
                'OjoIzq' => 'Ojo izquierdo',
                'OD' => 'Ojo derecho',
                'OjoDer' => 'Ojo derecho',
                'AO' => 'Ambos ojos',
                'OjoAmb' => 'Ambos ojos'
            ];

            if (isset($mensajeOjo[$ojoValue])) {
                echo $mensajeOjo[$ojoValue];
            } else {
                echo "Valor no válido";
            }
            ?></td>
    </tr>
    <tr>
        <td colspan="2" class="verde_left">Realizado:</td>
        <td class="blanco_left"
            colspan="18">
            <?php
            echo implode('/', $codes);
            echo "<br>";
            echo obtenerIntervencionesPropuestas(getFieldValue($form_id, "Prot_opr")) . " ";
            $mensajeOjo = [
                'OI' => 'Ojo izquierdo',
                'OjoIzq' => 'Ojo izquierdo',
                'OD' => 'Ojo derecho',
                'OjoDer' => 'Ojo derecho',
                'AO' => 'Ambos ojos',
                'OjoAmb' => 'Ambos ojos'
            ];

            if (isset($mensajeOjo[$ojoValue])) {
                echo $mensajeOjo[$ojoValue];
            } else {
                echo "Valor no válido";
            }
            ?></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado" colspan="20">D. INTEGRANTES DEL EQUIPO QUIRÚRGICO</td>
    </tr>
    <tr>
        <td class="verde_left" colspan="2">Cirujano 1:</td>
        <td class="blanco" colspan="8"><?php echo $providerNAME; ?></td>
        <td class="verde_left" colspan="2">Instrumentista:</td>
        <td class="blanco" colspan="8"><?php
            $instrumentistaOK = getFieldValue($form_id, "Prot_Instrumentistas");
            if ($instrumentistaOK == 'Si') {
                echo "Dr. Jorge Luis de Vera";
            } ?></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="2">Cirujano 2:</td>
        <td class="blanco" colspan="8"><?php echo getProviderName(getFieldValue($form_id, "Prot_Cirujano")); ?></td>
        <td class="verde_left" colspan="2">Circulante:</td>
        <td class="blanco" colspan="8"><?php
            echo getFieldValue($form_id, "Prot_opr") == "avastin" ? " " : "Lcda. Solange Vega";
            ?></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="3">Primer Ayudante:</td>
        <td class="blanco" colspan="7"><?php echo getProviderName(getFieldValue($form_id, "Prot_ayudante")); ?></td>
        <td class="verde_left" colspan="3">Anestesiologo/a:</td>
        <td class="blanco"
            colspan="7"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="3">Segundo Ayudante:</td>
        <td class="blanco" colspan="7"></td>
        <td class="verde_left" colspan="3">Ayudante Anestesia:</td>
        <td class="blanco" colspan="7"></td>
    </tr>
    <tr>
        <td class="verde_left" colspan="3">Tercer Ayudante:</td>
        <td class="blanco" colspan="7"></td>
        <td class="verde_left" colspan="1">Otros:</td>
        <td class="blanco" colspan="9"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="70" class="morado">F. TIEMPOS QUIRÚRGICOS</td>
    </tr>
    <tr>
        <td colspan="19" rowspan="2" class="verde">FECHA DE OPERACIÓN</td>
        <td colspan="5" class="verde">DIA</td>
        <td colspan="5" class="verde">MES</td>
        <td colspan="5" class="verde">AÑO</td>
        <td colspan="18" class="verde">HORA DE INICIO</td>
        <td colspan="18" class="verde">HORA DE TERMINACIÓN</td>
    </tr>
    <tr>
        <td colspan="5" class="blanco"><?php echo $dateddia; ?></td>
        <td colspan="5" class="blanco"><?php echo $datedmes; ?></td>
        <td colspan="5" class="blanco"><?php echo $datedano; ?></td>
        <td colspan="18" class="blanco"><?php echo getFieldValue($form_id, "Prot_hini"); ?></td>
        <td colspan="18" class="blanco"><?php echo getFieldValue($form_id, "Prot_hfin"); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Dieresis:</td>
        <td colspan="55"
            class="blanco_left"><?php echo obtenerDieresis(getFieldValue($form_id, "Prot_dieresis")); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Exposición y Exploración:</td>
        <td colspan="55" class="blanco_left"><?php echo obtenerExposicion(getFieldValue($form_id, "Prot_expo")); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Hallazgos Quirúrgicos:</td>
        <td colspan="55" class="blanco_left"><?php echo getFieldValue($form_id, "Prot_halla"); ?></td>
    </tr>
    <tr>
        <td colspan="15" class="verde_left">Procedimiento Quirúrgicos:</td>
        <td colspan="55"
            class="blanco_left"><?php echo html_entity_decode(html_entity_decode(getFieldValue($form_id, "Prot_proced"))); ?></td>
    </tr>
</table>
<table style="border: none">
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                               COLOR="#000000">SNS-MSP/HCU-form. 017/2021</FONT></B>
        </TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">PROTOCOLO QUIRÚRGICO (1)</FONT></B>
        </TD>
    </TR>
    ]
</TABLE>


<pagebreak>
    <table>
        <tr>
            <td colspan="15" class="verde_left">Procedimiento Quirúrgicos:</td>
            <td colspan="55" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="70" class="morado">G. COMPLICACIONES DEL PROCEDIMIENTO QUIRÚRGICO</td>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco_left"></td>
        </tr>
        </tr>
        <tr>
            <td colspan="10" class="verde">Pérdida Sanguínea total:</td>
            <td colspan="10" class="blanco"></td>
            <td colspan="5" class="blanco">ml</td>
            <td colspan="10" class="verde">Sangrado aproximado:</td>
            <td colspan="10" class="blanco"></td>
            <td colspan="5" class="blanco">ml</td>
            <td colspan="10" class="verde">Uso de Material Protésico:</td>
            <td colspan="3" class="blanco">SI</td>
            <td colspan="2" class="blanco"></td>
            <td colspan="3" class="blanco">NO</td>
            <td colspan="2" class="blanco"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="70" class="morado">H. EXÁMENES HISTOPATOLÓGICOS</td>
        </tr>
        <tr>
            <td colspan="10" class="verde">Transquirúrgico:</td>
            <td colspan="60" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="10" class="verde">Biopsia por congelación:</td>
            <td colspan="3" class="blanco">SI</td>
            <td colspan="2" class="blanco"></td>
            <td colspan="3" class="blanco">NO</td>
            <td colspan="2" class="blanco">X</td>
            <td colspan="10" class="verde">Resultado:</td>
            <td colspan="40" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="13" class="blanco_left"></td>
            <td colspan="57" class="blanco_left">Patólogo que reporta:</td>
        </tr>
        <tr>
            <td colspan="10" class="verde">Histopatológico:</td>
            <td colspan="3" class="blanco">SI</td>
            <td colspan="2" class="blanco"></td>
            <td colspan="3" class="blanco">NO</td>
            <td colspan="2" class="blanco">X</td>
            <td colspan="10" class="verde">Muestra:</td>
            <td colspan="40" class="blanco_left"></td>
        </tr>
        <tr>
            <td colspan="70" class="blanco"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado">I. DIAGRAMA DEL PROCEDIMIENTO</td>
        </tr>
        <tr>
            <td class="blanco" height="100px">
                <?php
                echo getImageHTML(getFieldValue($form_id, "Prot_opr"));
                //echo getFieldValue($form_id, "Prot_opr");
                ?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="20">J. DATOS DEL PROFESIONAL RESPONSABLE</td>
        </tr>
        <tr>
            <td class="verde" style="width: 100" colspan="5">NOMBRE Y APELLIDOS</td>
            <td class="verde" style="width: 100" colspan="5">ESPECIALIDAD</td>
            <td class="verde" style="width: 100" colspan="5">FIRMA</td>
            <td class="verde" style="width: 100" colspan="5">SELLO Y NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
        </tr>
        <tr>
            <td class="blanco" style="height: 60" colspan="5"><?php echo $providerNAME; ?></td>
            <td class="blanco" colspan="5"><?php echo getProviderEspecialidad($providerID) ?></td>
            <td class="blanco" colspan="5"></td>
            <td class="blanco" colspan="5"><?php echo getProviderRegistro($providerID) ?></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 60"
                colspan="5"><?php echo getProviderName(getFieldValue($form_id, "Prot_ayudante")); ?></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderEspecialidad(getFieldValue($form_id, "Prot_ayudante")) ?></td>
            <td class="blanco"
                colspan="5"></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderRegistro(getFieldValue($form_id, "Prot_ayudante")) ?></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 60"
                colspan="5"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderEspecialidad(getFieldValue($form_id, "Prot_anestesiologo")) ?></td>
            <td class="blanco"
                colspan="5"></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderRegistro(getFieldValue($form_id, "Prot_anestesiologo")) ?></td>
        </tr>

    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                                   COLOR="#000000">SNS-MSP/HCU-form. 017/2021</FONT></B>
            </TD>
            <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">PROTOCOLO QUIRÚRGICO (2)</FONT></B>
            </TD>
        </TR>
        ]
    </TABLE>
</pagebreak>
<pagebreak>
    <TABLE>
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
            <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?>
            </td>
            <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
            <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco" style="border-right: none">X</td>
        </tr>
    </TABLE>
    <table>
        <tr>
            <td class="morado" colspan="26" style="border-bottom: 1px solid #808080;">B. EVOLUCIÓN Y
                PRESCRIPCIONES
            </td>
            <td class="morado" colspan="20"
                style="font-size: 4pt; font-weight: lighter; border-bottom: 1px solid #808080;">FIRMAR AL PIE DE
                CADA
                EVOLUCIÓN Y PRESCRIPCIÓN
            </td>
            <td class="morado" colspan="21"
                style="font-size: 4pt; font-weight: lighter; text-align: right; border-bottom: 1px solid #808080;">
                REGISTRAR CON ROJO LA
                ADMINISTRACIÓN DE FÁRMACOS Y COLOCACIÓN DE
                DISPOSITIVOS
                MÉDICOS
            </td>
        </tr>
        <tr>
            <td class="morado" colspan="38" style="text-align: center">1. EVOLUCIÓN</td>
            <td class="blanco_break"></td>
            <td class="morado" colspan="28" style="text-align: center">2. PRESCRIPCIONES</td>
        </tr>
        <tr>
            <td class="verde" colspan="6">FECHA<br><span
                    style="font-size:6pt;font-family:Arial;font-weight:normal;">(aaaa-mm-dd)</span>
            </td>
            <td class="verde" colspan="3">HORA<br><span style="font-size:6pt;font-family:Arial;font-weight:normal;">(hh:mm)</span>
            </td>
            <td class="verde" colspan="29">NOTAS DE EVOLUCIÓN</td>
            <td class="blanco_break"></td>
            <td class="verde" colspan="23">FARMACOTERAPIA E INDICACIONES<span
                    style="font-size:6pt;font-family:Arial;font-weight:normal;"><br>(Para enfermería y otro profesional de salud)</span>
            </td>
            <td class="verde" colspan="5"><span
                    style="font-size:6pt;font-family:Arial;font-weight:normal;">ADMINISTR. <br>FÁRMACOS<br>DISPOSITIVO</span>
            </td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"><?php echo $dateddia . "/" . $datedmes . "/" . $datedano; ?></td>
            <td class="blanco_left" colspan="3"><?php echo $prot_hfin_nueva; // Mostrar el nuevo valor de la hora?>
            </td>
            <td class="blanco_left" colspan="29" style="text-align: center">PRE-OPERATORIO
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23" style="text-align: center">PRE-OPERATORIO</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Paciente de <?php echo text(getPatientAge($titleres['DOB_TS'])); ?>
                años de edad, conciente, orientado
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Se recibe paciente en el área; se procede a:</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">en tiempo y espacio es recibido en el área de preoperatorio
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Colocar anestesia tópica:</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">hemodinámicamente activo, con
                diagnóstico de:
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">-Proximetacaína 0.5%</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left"
                colspan="29"> -<?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre")); ?>
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Cateterización de acceso venoso periférico en</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Se realiza canalización de vía periférica con Cloruro
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Miembro superior con:</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">de Sodio al 0.9%, Manito el 20%
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">-Catéter calibre 22G</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">colocación de anestesia tópica con Proximetacaína al 0.5%
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">-Equipo de venoclisis para administración de</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Se indica oxigenoterapia
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">solución endovenosa</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">bajo anestesia local (Proximetacaína) y sedación se
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Fijación con Tegaderm IV</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">efectúa anestesia retrobulbar
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Glicemia capilar</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Monitoreo de signos vitales:
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">T.A.: 120/70 F.C.: 90 SATO2: 100%
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3"><?php echo getFieldValue($form_id, "Prot_hfin"); ?>
            </td>
            <td class="blanco_left" colspan="29" STYLE="text-align: center">POST-OPERATORIO
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23" STYLE="text-align: center">POST-OPERATORIO</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Paciente que sale de cirugía al momento conciente,
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Ketorolaco líquido parenteral 30mg en 100ml</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">orientado en tiempo y espacio
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">de cloruro de sodio al 0.9%</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Hemodinámicamente activo, se administra analgésico
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Ceftriaxona sólido parenteral 1000 mg</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">más corticoides y antagonista
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">en cloruro de sodio 0.9% líquido parenteral 1000ml</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Cruza post operatorio inmediato sin complicaciones
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Flumazenil líquido parenteral 0.1 mg/ml</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Hidrocortisona succionato sódico</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3"><?php echo $prot_hfin_fin; // Mostrar el nuevo valor de la hora?>
            </td>
            <td class="blanco_left" colspan="29" STYLE="text-align: center">ALTA MEDICA
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23" STYLE="text-align: center">ALTA MEDICA</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Alta médica de la sala de recuperación, apósito limpio y seco
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">-Etoricoxib sólido oral 60 mg (tableta)</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Monitoreo de signos vitales: T.A.: 112/74 F.C.: 85 SATOS: 100%
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">1 tableta diaria por 3 días</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">Se dan indicaciones médicas
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">-Moxalvan sólido oral 400 mg (tabletas)</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">1 tableta diaria por 7 días</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29"><?php
                echo $providerNAME;
                ?>
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>

        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23"></td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                                   COLOR="#000000">SNS-MSP/HCU-form.005/2021</FONT></B>
            </TD>
            <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">EVOLUCIÓN Y PRESCRIPCIONES
                        (1)</FONT></B>
            </TD>
        </TR>
        ]
    </TABLE>
</pagebreak>
<pagebreak>
    <TABLE>
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
            <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?>
            </td>
            <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
            <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco" style="border-right: none">X</td>
        </tr>
    </TABLE>
    <table>
        <tr>
            <td class="verde" colspan="10">ALERGIA A MEDICAMENTOS</td>
            <td class="verde" colspan="2">SI</td>
            <td class="blanco_left" colspan="2"></td>
            <td class="verde" colspan="2">NO</td>
            <td class="blanco_left" colspan="2"></td>
            <td class="verde" colspan="7">DESCRIBA:</td>
            <td class="blanco_left" colspan="52"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="77">B. ADMINISTRACIÓN DE MEDICAMENTOS PRESCRITOS</td>
        </tr>
        <tr>
            <td class="morado" colspan="17" style="border-top: 1px solid #808080; border-right: 1px solid #808080;">1.
                MEDICAMENTO
            </td>
            <td class="morado" colspan="60" style="border-top: 1px solid #808080;">2. ADMINISTRACIÓN</td>
        </tr>
        <tr>
            <td class="verde" colspan="17">FECHA</td>
            <td class="blanco" colspan="15"><?php echo $dateddia . "/" . $datedmes . "/" . $datedano; ?></td>
            <td class="blanco" colspan="15"></td>
            <td class="blanco" colspan="15"></td>
            <td class="blanco" colspan="15"></td>
        </tr>
        <tr>
            <td class="verde" colspan="17">DOSIS, VIA, FRECUENCIA</td>
            <td class="verde" colspan="6">HORA</td>
            <td class="verde" colspan="9">RESPONSABLE</td>
            <td class="verde" colspan="6">HORA</td>
            <td class="verde" colspan="9">RESPONSABLE</td>
            <td class="verde" colspan="6">HORA</td>
            <td class="verde" colspan="9">RESPONSABLE</td>
            <td class="verde" colspan="6">HORA</td>
            <td class="verde" colspan="9">RESPONSABLE</td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                CLORURO DE SODIO 0,9% LIQUIDO PARENTERAL (1000ML) 60 GOTAS POR
                MINUTO, INTRAVENOSA, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                MANITOL 20% LIQUIDO PARENTERAL 500 MILILITROS INTRAVENOSA, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                MIDAZOLAM LIQUIDO PARENTERAL 5MG/ML (3ML) DOSIS: 2,5 MILIGRAMOS/0,5 MILILITRO, INTRAVENOSA,STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                FENTANILO LIQUIDO PARENTERAL 0,05MG/ ML (10ML) DOSIS: 60 MICROGRAMOS/ 1MILILITRO, INTRAVENOSO, STAT
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                CEFTRIAXONA SOLIDO PARENTERAL 1000MG DOSIS: 1000MILIGRAMOS DILUIDO EN 100MILILITROS DE CLORURO DE SODIO
                AL 0,9% INTRAVENOSA, STAT
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                KETOROLACO LIQUIDO PARENTERAL 30MG/ML (1ML) DOSIS: 60MILIGRAMOS DILUIDO EN 100 MILILITROS DE CLORURO DE
                SODIO AL 0,9%, INTRAVENOSA, STAT
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                LIDOCAINA CON EPINEFRINA, LIQUIDO PARENTERAL 2% + 1,200,000 (50ML), DOSIS: 80MILIGRAMO /4 MILILITRO, VIA
                INFILTRATIVA, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                BUPIVACAINA SIN EPINEFRINA, LIQUIDO PARENTERAL 0,5% (20ML), DOSIS: 20 MILIGRAMO /4MILILITRO, VIA
                INFILTRATIVA, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                GENTAMICINA LIQUIDO PARENTERAL 80MG/ML (2ML) DOSIS: 160MILIGRAMOS /2 MILILITROS, SUBCONJUNTIVAL, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                DEXAMETASONA LIQUIDO PARENTERAL 4MG/DL (2ML) DOSIS: 8MILIGRAMOS /2MILILITROS, SUBCONJUNTIVAL, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                DEXAMETASONA + TOBRAMICINA LIQUIDO OFTALMOLOGICO 0,1%+0,3% (5ML) DOSIS: 1 GOTA, VIA TOPICA, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="17" rowspan="3">
                MITOMICINA SOLIDO PARENTERAL 20MG (20MILIGRAMOS DILUIDO EN 10 MILILITROS DE CLORURO DE SODIO AL 0,9%)
                DOSIS: 1MILILITRO, VIA TOPIA, STAT.
            </td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
            <td class="blanco" colspan="6"></td>
            <td class="blanco" colspan="9"></td>
        </tr>
    </table>
</pagebreak>
<pagebreak>
    <h2>REGISTRO DE CONSUMO DE OXIGENO</h2>
    <TABLE>
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
            <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?>
            </td>
            <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
            <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco">&nbsp;</td>
            <td colspan="2" class="blanco" style="border-right: none">X</td>
        </tr>
    </TABLE>
    <table>
        <tr>
            <td class="verde" colspan="10" rowspan="2">FECHA</td>
            <td class="verde" colspan="16">HORAS</td>
            <td class="verde" colspan="20" rowspan="2">FLUJO OXIGENO (lts./min.)</td>
            <td class="verde" colspan="14" rowspan="2">TOTAL LITROS</td>
            <td class="verde" colspan="20" rowspan="2">ANESTESIOLOGO</td>
        </tr>
        <tr>
            <td class="verde" colspan="8">INICIO</td>
            <td class="verde" colspan="8">FINAL</td>
        </tr>
        <tr>
            <td class="blanco" colspan="10">
                <?php echo $dateddia . "/" . $datedmes . "/" . $datedano; ?>
            </td>
            <td class="blanco" colspan="8"><?php echo getFieldValue($form_id, "Prot_hini"); ?></td>
            <td class="blanco" colspan="8"><?php echo getFieldValue($form_id, "Prot_hfin"); ?></td>
            <td class="blanco" colspan="20">3 litros</td>
            <td class="blanco" colspan="14">360</td>
            <td class="blanco" colspan="20">
                <?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?>
            </td>
        </tr>
        <tr>
            <td class="blanco" colspan="10"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="20"></td>
            <td class="blanco" colspan="14"></td>
            <td class="blanco" colspan="20"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="10"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="20"></td>
            <td class="blanco" colspan="14"></td>
            <td class="blanco" colspan="20"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="10"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="20"></td>
            <td class="blanco" colspan="14"></td>
            <td class="blanco" colspan="20"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="10"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="20"></td>
            <td class="blanco" colspan="14"></td>
            <td class="blanco" colspan="20"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="10"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="20"></td>
            <td class="blanco" colspan="14"></td>
            <td class="blanco" colspan="20"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="10"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="8"></td>
            <td class="blanco" colspan="20"></td>
            <td class="blanco" colspan="14"></td>
            <td class="blanco" colspan="20"></td>
        </tr>
    </table>
</pagebreak>

</BODY>
</html>
