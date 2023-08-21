<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
require_once("$srcdir/report.inc");
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


$codes = getCPT4Codes($titleres['pricelevel'], $form_id);

$sistolica = rand(110, 130);
$diastolica = rand(110, 130);
$fc = rand(110, 130);

$proced_id = getFieldValue($form_id, "Prot_opp");
$resultadoDX = obtenerCodigosImpPlan($pid, $form_encounter);


?>
<html>
<HEAD>
    <style>
        p {
            text-align: justify;
        }

        table {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 5px;
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

        td.verde_normal {
            height: 23px;
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
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
        <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
    </tr>
</TABLE>
<table style="margin-bottom: unset">
    <tr>
        <td class="morado" colspan="67">B. CONSENTIMIENTO INFORMADO
        </td>
    </tr>
    <tr>
        <td colspan="18" class="verde">CONSENTIMIENTO
            INFORMADO PARA:
        </td>
        <td colspan="49" class="blanco_left">
            <?php
            $items = extractItemsFromQuery($form_id, $pid, $form_encounter, $proced_id);
            $ojoValue = getFieldValue($form_id, "Prot_ojo");

            // Realizar acciones con los items extraídos
            foreach ($items

            as $item) {
            echo $item['name'] . " ";

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
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="6"
            class="verde">SERVICIO:
        </td>
        <td colspan="26" class="blanco">OFTALMOLOGÍA</td>
        <td colspan="11" class="verde">TIPO DE
            ATENCIÓN:
        </td>
        <td colspan="8" class="blanco">
            AMBULATORIO
        </td>
        <td colspan="3" class="blanco">X</td>
        <td colspan="10" class="blanco">
            HOSPITALIZACIÓN
        </td>
        <td colspan="3" class="blanco"></td>
    </tr>
    <tr>
        <td colspan="8"
            class="verde">DIAGNÓSTICO:
        </td>
        <td colspan="44" class="blanco_left">
            <?php
            $prot_dxpre1 = lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre"));
            $prot_dxpre2 = lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre2"));
            $prot_dxpre3 = lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre3"));

            $descriptions = $prot_dxpre1;
            if (!empty($prot_dxpre2)) {
                $descriptions .= "<br>" . $prot_dxpre2;
            }
            if (!empty($prot_dxpre3)) {
                $descriptions .= "<br>" . $prot_dxpre3;
            }

            echo $descriptions;
            ?>
        </td>
        <td colspan="4" class="verde">CIE 10:</td>
        <td colspan="11" class="blanco_left">
            <?php
            $prot_dxpre1 = substr(getFieldValue($form_id, "Prot_dxpre"), 6);
            $prot_dxpre2 = substr(getFieldValue($form_id, "Prot_dxpre2"), 6);
            $prot_dxpre3 = substr(getFieldValue($form_id, "Prot_dxpre3"), 6);
            ?>

            <?php if (!empty($prot_dxpre1)) : ?>
                <?php echo $prot_dxpre1; ?>
            <?php endif; ?>

            <?php if (!empty($prot_dxpre2)) : ?>
                <br><?php echo $prot_dxpre2; ?>
            <?php endif; ?>

            <?php if (!empty($prot_dxpre3)) : ?>
                <br><?php echo $prot_dxpre3; ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td colspan="23"
            class="verde">NOMBRE DEL
            PROCEDIMIENTO RECOMENDADO:
        </td>
        <td colspan="44" CLASS="blanco"></td>
    </tr>
    <tr id="r13">
        <td colspan="11"
            class="verde">EN QUÉ CONSISTE:
        </td>
        <td colspan="56" class="blanco_left">
            <?php
            echo wordwrap($item['consiste'], 125, "</td></tr><tr><td colspan='67' class='blanco_left'>");;
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="11"
            class="verde">CÓMO SE REALIZA:
        </td>
        <td colspan="56" class="blanco_left">
            <?php
            echo wordwrap($item['realiza'], 125, "</td></tr><tr><td colspan='67' class='blanco_left'>");
            ?>
        </td>
    </tr>
    <tr id="r17">
        <td colspan="67" class="verde">GRÁFICO DE LA
            INTERVENCIÓN (incluya un gráfico previamente seleccionado que facilite la comprensión al paciente)
        </td>
    </tr>
    <tr id="r18">
        <td colspan="67" height="200" class="blanco">
            <?php
            echo '<img src="' . $item['grafico'] . '" alt="Imagen" style="max-height: 200px;" /><br>';
            ?>
        </td>
    </tr>
    <tr id="r34">
        <td colspan="21" class="verde">DURACIÓN ESTIMADA DE
            LA INTERVENCIÓN:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['duracion'] . " minutos";
            ?>
        </td>
    </tr>
    <tr id="r35">
        <td colspan="21" class="verde">BENEFICIOS DEL
            PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['beneficios'];
            ?>
        </td>
    </tr>
    <tr id="r36">
        <td colspan="21" class="verde">RIEGOS FRECUENTES
            (POCO GRAVES):
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['riesgos'];
            ?>
        </td>
    </tr>
    <tr id="r37">
        <td colspan="21" class="verde">RIESGOS POCO
            FRECUENTES (GRAVES):
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['riesgos_graves'];
            ?>
        </td>
    </tr>
    <tr id="r38">
        <td colspan="67"
            class="verde">DE EXISTIR, ESCRIBA
            LOS RIESGOS ESPECÍFICOS RELACIONADOS CON EL PACIENTE (edad, estado de salud, creencias, valores, etc):
        </td>
    </tr>
    <tr id="r39">
        <td colspan="67" class="blanco"></td>
    </tr>
    <tr id="r40">
        <td colspan="67"
            class="blanco"></td>
    </tr>
    <tr id="r41">
        <td colspan="21" class="verde">ALTERNATIVAS AL
            PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['alternativas'];
            ?>
        </td>
    </tr>
    <tr id="r42">
        <td colspan="21" class="verde">DESCRIPCIÓN DEL MANEJO
            POSTERIOR AL PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left">
            <?php
            echo $item['post'];
            ?>
        </td>
    </tr>
    <tr id="r43">
        <td colspan="21" style="border-left: 5px solid #808080; border-bottom: 5px solid #808080;"
            class="verde">CONSECUENCIAS POSIBLES
            SI NO SE REALIZA EL PROCEDIMIENTO:
        </td>
        <td colspan="46" class="blanco_left"
            style="border-right: 5px solid #808080; border-bottom: 5px solid #808080;">
            <?php
            echo $item['consecuencias'];
            ?>
        </td>
    </tr>
</table>
<table style="border: none">
    <TR>
        <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP / HCU-form.024/2016</B>
        </TD>
        <TD colspan="3" class="blanco" style="border: none; text-align: right"><B>CONSENTIMIENTO INFORMADO (1)</B>
        </TD>
    </TR>
</TABLE>
<pagebreak>
    <!--[DECLARACIÓN DE CONSENTIMIENTO INFORMADO]-->
    <table>
        <tr>
            <td colspan="67" height="40" class="morado">C. DECLARACIÓN DE
                CONSENTIMIENTO INFORMADO
            </td>
        </tr>
        <tr>
            <td colspan="11" class="verde">FECHA
            </td>
            <td colspan="56" class="blanco_left">
                <?php echo date('d/m/Y', strtotime(fetchDateByEncounter($encounter))); ?>
            </td>
        </tr>
        <tr>
            <td colspan="67" style="font-size: 6pt">
                He facilitado la información completa que conozco, y me ha sido solicitada, sobre los antecedentes
                personales, familiares y de mi estado de salud. Soy consciente que de omitir estos datos puede afectarse
                los resultados del tratamiento. Estoy de acuerdo con el procedimiento que se me ha propuesto; he sido
                informado de las ventajas e inconvenientes del mismo; se me ha explicado de forma clara en qué consiste,
                los
                beneficios y posibles riesgos del procedimiento. He escuchado, leído y comprendido la información
                recibida y se me ha dado la oportunidad de preguntar sobre el procedimiento. He tomado consciente y
                libremente de
                decisión de autorizar el procedimiento adicional, si es considerado necesario según el juicio del
                profesional de la salud, para mi beneficio. También conozco que puedo retirar mi consentimiento cuando
                lo estime oportuno.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black"><?php echo text(xlt($titleres['fname'])
                    . " " . $titleres['mname'] . " " . $titleres['lname'] . " " . $titleres['lname2']); ?>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14"
                style="font-size: 7pt; border-top: 1px solid black"><?php echo text($titleres['pubpid']); ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del paciente o huella, según el
                caso.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">
                <?php
                echo $providerNAME;
                ?>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="36" style="font-size: 7pt; border-top: 1px solid black">Firma, sello y código del profesional
                de la salud que realizará el procedimiento.
            </td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no está en capacidad para
                firmar el consentimiento informado:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" height="18" style="font-size: 7pt; border-top: 1px solid black">Parentesco</td>
            <td colspan="40"></td>
        </tr>
    </table>
    <!--[NEGATIVA DEL CONSENTIMIENTO INFORMADO]-->
    <table>
        <tr>
            <td colspan="67" height="40" class="morado">D. NEGATIVA DEL
                CONSENTIMIENTO INFORMADO
            </td>
        </tr>
        <tr>
            <td colspan="11" class="verde">FECHA
            </td>
            <td colspan="56" class="blanco_left">
                <?php echo date('d/m/Y', strtotime(fetchDateByEncounter($encounter))); ?>
            </td>
        </tr>
        <tr>
            <td colspan="67" style="font-size: 6pt">
                Una vez que he entendido claramente el procedimiento propuesto, así como las consecuencias posibles si
                no se realiza la intervención, no autorizo y me niego a que se me realice el procedimiento propuesto y
                desvinculo de responsabilidades futuras de
                cualquier índole al establecimiento de salud y al profesional sanitario que me atiende, por no realizar
                la intervención sugerida.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre
                completo del paciente.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del paciente o huella, según el
                caso.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">
                Nombre de profesional que realiza el
                procedimiento.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="36" style="font-size: 7pt; border-top: 1px solid black">Firma, sello y código del profesional
                de la salud que realizará el procedimiento.
            </td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no está en capacidad para
                firmar el consentimiento informado:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" height="18" style="font-size: 7pt; border-top: 1px solid black">Parentesco</td>
            <td colspan="40"></td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no acepta el procedimiento sugerido por el
                profesional y se niega a firmar este acápite:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
    </table>
    <!--[REVOCATORIA DEL CONSENTIMIENTO INFORMADO]-->
    <table style="margin-bottom: unset">
        <tr>
            <td colspan="67" height="40" class="morado">E. REVOCATORIA DEL CONSENTIMIENTO INFORMADO
            </td>
        </tr>
        <tr>
            <td colspan="11" class="verde">FECHA
            </td>
            <td colspan="56" class="blanco_left">
                <?php echo date('d/m/Y', strtotime(fetchDateByEncounter($encounter))); ?>
            </td>
        </tr>
        <tr>
            <td colspan="67" style="font-size: 6pt">
                De forma libre y voluntaria, revoco el consentimiento realizado en fecha y manifiesto expresamente mi
                deseo de no continuar con el procedimiento médico que doy por finalizado en esta fecha:
                Libero de responsabilidades futuras de cualquier índole al establecimiento de salud y al profesional
                sanitario que me atiende.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre
                completo del paciente.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del paciente o huella, según el
                caso.
            </td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">
                Nombre de profesional que realiza el
                procedimiento.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="36" style="font-size: 7pt; border-top: 1px solid black">Firma, sello y código del profesional
                de la salud que realizará el procedimiento.
            </td>
        </tr>
        <tr>
            <td colspan="42" style="font-size: 7pt;">Si el paciente no está en capacidad para
                firmar el consentimiento informado:
            </td>
            <td colspan="25"></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" style="font-size: 7pt; border-top: 1px solid black">Nombre del representante
                legal.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14" style="font-size: 7pt; border-top: 1px solid black">Cédula de ciudadanía.</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="18" style="font-size: 7pt; border-top: 1px solid black">Firma del representante legal.</td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="67"><BR></td>
        </tr>
        <tr>
            <td colspan="27" height="18" style="font-size: 7pt; border-top: 1px solid black">Parentesco</td>
            <td colspan="40"></td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP / HCU-form.024/2016</B>
            </TD>
            <TD colspan="3" class="blanco" style="border: none; text-align: right"><B>CONSENTIMIENTO INFORMADO (2)</B>
            </TD>
        </TR>
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
            <td colspan="3" class="blanco"><?php echo substr($titleres['sex'], 0, 1); ?></td>
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
            <td class="morado" colspan="67">B. REGISTRO DE VALORACIÓN PREANESTÉSICA</td>
        </tr>
        <tr>
            <td class="verde" colspan="11">DIAGNÓSTICOS</td>
            <td class="blanco_left" colspan="48">
                <?php
                $prot_dxpre1 = lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre"));
                $prot_dxpre2 = lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre2"));
                $prot_dxpre3 = lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre3"));

                $descriptions = $prot_dxpre1;
                if (!empty($prot_dxpre2)) {
                    $descriptions .= "<br>" . $prot_dxpre2;
                }
                if (!empty($prot_dxpre3)) {
                    $descriptions .= "<br>" . $prot_dxpre3;
                }

                echo $descriptions;
                ?>
            </td>
            <td class="verde" colspan="3">CIE</td>
            <td class="blanco_left" colspan="5">
                <?php
                $prot_dxpre1 = substr(getFieldValue($form_id, "Prot_dxpre"), 6);
                $prot_dxpre2 = substr(getFieldValue($form_id, "Prot_dxpre2"), 6);
                $prot_dxpre3 = substr(getFieldValue($form_id, "Prot_dxpre3"), 6);
                ?>

                <?php if (!empty($prot_dxpre1)) : ?>
                    <?php echo $prot_dxpre1; ?>
                <?php endif; ?>

                <?php if (!empty($prot_dxpre2)) : ?>
                    <br><?php echo $prot_dxpre2; ?>
                <?php endif; ?>

                <?php if (!empty($prot_dxpre3)) : ?>
                    <br><?php echo $prot_dxpre3; ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="verde" colspan="11">PROCEDIMIENTO/S PROPUESTO /S:</td>
            <td class="blanco_left" colspan="56">
                <?php
                echo $item['name'] . " ";
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
                ?>
            </td>
        </tr>
        <tr>
            <td class="verde" colspan="6">Electiva</td>
            <td class="blanco_left" colspan="3">X</td>
            <td class="verde" colspan="8">Emergencia</td>
            <td class="blanco_left" colspan="3"></td>
            <td class="verde" colspan="6">Urgencia</td>
            <td class="blanco_left" colspan="3"></td>
            <td class="verde" colspan="9">RIESGO QUIRÚRGICO:</td>
            <td class="verde" colspan="6">Bajo</td>
            <td class="blanco_left" colspan="3">X</td>
            <td class="verde" colspan="8">Moderado</td>
            <td class="blanco_left" colspan="3"></td>
            <td class="verde" colspan="6">Alto</td>
            <td class="blanco_left" colspan="3"></td>
        </tr>

    </table>
    <table>
        <tr>
            <td class="morado" colspan="67">C. ANAMNESIS</td>
        </tr>
        <tr>
            <td class="verde" colspan="67">ANTECEDENTES PATOLÓGICOS PERSONALES</td>
        </tr>
        <tr>
            <td class="blanco" colspan="2"></td>
            <td class="verde" colspan="11">DIAGNÓSTICOS</td>
            <td class="verde" colspan="12">TIEMPO DE EVOLUCIÓN</td>
            <td class="verde" colspan="42">TRATAMIENTO</td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">1.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">2.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">3.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">4.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">5.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">6.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">7.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">8.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">9.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="2">10.</td>
            <td class="blanco" colspan="11"></td>
            <td class="blanco" colspan="12"></td>
            <td class="blanco" colspan="42"></td>
        </tr>
        <tr>
            <td class="verde" colspan="11" rowspan="3">ANESTÉSICOS</td>
            <td class="blanco_left" colspan="56">No refiere complicaciones</td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="verde" colspan="11" rowspan="3">QUIRÚRGICOS</td>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="verde" colspan="11" rowspan="3">ALÉRGICOS</td>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="verde" colspan="11" rowspan="3">TRANSFUSIONES</td>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="verde" colspan="11" rowspan="3">HÁBITOS</td>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="56"></td>
        </tr>
        <tr>
            <td class="verde" colspan="67">ANTECEDENTES PATOLÓGICOS FAMILIARES</td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="67"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="67"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="67"></td>
        </tr>

    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP/HCU-form.018/2021</B>
            </TD>
            <TD colspan="3" class="blanco" style="border: none; text-align: right"><B> PRE ANESTÉSICO (1)</B>
            </TD>
        </TR>
    </TABLE>
    <?php
    }
    ?>
</pagebreak>
<pagebreak>
    <table>
        <tr>
            <td class="morado" colspan="67">D. EXAMEN FÍSICO</td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">CONSTANTES VITALES</td>
            <td class="verde" colspan="3" style="height: 15px">TA</td>
            <td class="blanco" colspan="6" style="height: 15px">120/80</td>
            <td class="verde" colspan="4" style="height: 15px">FC</td>
            <td class="blanco" colspan="6" style="height: 15px">70</td>
            <td class="verde" colspan="4" style="height: 15px">FR</td>
            <td class="blanco" colspan="6" style="height: 15px">12</td>
            <td class="verde" colspan="4" style="height: 15px">T°</td>
            <td class="blanco" colspan="3" style="height: 15px">36</td>
            <td class="verde" colspan="5" style="height: 15px">SAT 02</td>
            <td class="blanco" colspan="4" style="height: 15px">99</td>
            <td class="verde" colspan="7" style="height: 15px">GLASGOW</td>
            <td class="blanco" colspan="3" style="height: 15px">15</td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">ANTROPOMETRÍA</td>
            <td class="verde" colspan="8" style="height: 15px">PESO (kg)</td>
            <td class="blanco" colspan="11" style="height: 15px"></td>
            <td class="verde" colspan="8" style="height: 15px">TALLA (cm)</td>
            <td class="blanco" colspan="11" style="height: 15px"></td>
            <td class="verde" colspan="7" style="height: 15px">IMC (kg/m2)</td>
            <td class="blanco" colspan="10" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="12" rowspan="6">VÍA AÉREA</td>
            <td class="verde" colspan="22" style="height: 15px; font-size: 6pt">APERTURA BUCAL (cm)</td>
            <td class="verde" colspan="17" style="height: 15px; font-size: 6pt">DISTANCIA TIROMENTONEANA (cm)</td>
            <td class="verde" colspan="16" style="height: 15px; font-size: 6pt">MALLAMPATI</td>
        </tr>
        <tr>
            <td class="blanco" colspan="3" style="height: 15px">&lt;2</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">2 - 2,5</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">2,6 - 3</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&gt;3</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="blanco" colspan="3" style="height: 15px">&lt;6</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">6 - 6,5</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;6,5</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">I</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">II</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="blanco" colspan="2" style="height: 15px">III</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">IV</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="17" style="height: 15px; font-size: 6pt">PROTRUSIÓN MANDIBULAR</td>
            <td class="verde" colspan="11" style="height: 15px; font-size: 6pt">PERÍMETRO CERVICAL (cm)</td>
            <td class="verde" colspan="11" style="height: 15px; font-size: 6pt">MOVILIDAD CERVICAL (°)</td>
            <td class="verde" colspan="8" style="height: 15px; font-size: 6pt">HISTORIA DE INTUBACIÓN DIFÍCIL</td>
            <td class="verde" colspan="8" style="height: 15px; font-size: 6pt">PATOLOGÍA ASOCIADA A INTUBACIÓN DIFÍCIL
            </td>
        </tr>
        <tr>
            <td class="blanco" colspan="3" style="height: 15px">&lt;0</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">0</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;0</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&lt;40</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;40</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="3" style="height: 15px">&lt;35</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px">&gt;35</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="blanco" colspan="2" style="height: 15px">SI</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">NO</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="blanco" colspan="2" style="height: 15px">SI</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px">NO</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
        </tr>
        <tr>
            <td class="verde" colspan="9" rowspan="2">OTROS</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">TÓRAX</td>
            <td class="blanco_left" colspan="46" style="height: 15px">
                Respiración normal sin sonidos adicionales o anormales durante la auscultación
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">CORAZÓN</td>
            <td class="blanco_left" colspan="46" style="height: 15px">
                Se escuchan ruidos cardíacos regulares sin la presencia de soplos adicionales durante la
                auscultación cardíaca
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">PULMONES</td>
            <td class="blanco_left" colspan="46" style="height: 15px">
                Normal
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">ABDOMEN</td>
            <td class="blanco_left" colspan="46" style="height: 15px">
                Normal
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">EXTREMIDADES</td>
            <td class="blanco_left" colspan="46" style="height: 15px">
                Sin Edema
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">SISTEMA NERVIOSO CENTRAL</td>
            <td class="blanco_left" colspan="46" style="height: 15px">
                Glasgow 15/15
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="21" style="height: 15px">EQUIVALENTE METABÓLICO (METS)</td>
            <td class="blanco" colspan="46" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="67">E. RESULTADOS DE EXÁMENES DE LABORATORIO, GABINETE E
                IMAGEN<span
                    style="font-size:9pt;font-family:Arial;font-weight:bold;">                  </span><span
                    style="font-size:7pt;font-family:Arial;font-weight:bold;">(REGISTRAR LO QUE APLIQUE)</span>
            </td>
        </tr>
        <tr>
            <td class="verde" colspan="11" style="height: 15px; font-size: 6pt">HEMOGRAMA</td>
            <td class="verde" colspan="12" style="height: 15px; font-size: 6pt">TIPIFICACIÓN</td>
            <td class="verde" colspan="8" style="height: 15px; font-size: 6pt">PERFIL HEPÁTICO</td>
            <td class="verde" colspan="7" style="height: 15px; font-size: 6pt">IONOGRAMA</td>
            <td class="verde" colspan="9" style="height: 15px; font-size: 6pt">GASOMETRÍA</td>
            <td class="verde" colspan="6" style="height: 15px; font-size: 6pt">HORMONAS</td>
            <td class="verde" colspan="14" style="height: 15px; font-size: 6pt">ORINA</td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">HCTO</td>
            <td class="blanco" colspan="5" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="6" style="height: 15px;">GRUPO</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">AST</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">Na</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">pH</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">T4</td>
            <td class="blanco" colspan="4" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">pH</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">HB</td>
            <td class="blanco" colspan="5" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="6" style="height: 15px;">FACTOR</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">ALT</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">K</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">PO2</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" rowspan="2" style="height: 15px;">PRUEBA
                EMBARAZO
            </td>
            <td class="verde_normal" colspan="6" style="height: 15px;">BACTERIAS</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">TP</td>
            <td class="blanco" colspan="5" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="6" style="height: 15px;">GLUCOSA</td>
            <td class="blanco" colspan="6" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="2" style="height: 15px;">LDH</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">Ca</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">HCO3</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">LEUCOCITOS</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">TTP</td>
            <td class="blanco" colspan="5" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="6" style="height: 15px;">UREA</td>
            <td class="blanco" colspan="6" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="2" style="height: 15px;">BT</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">Mg</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">EB</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">SI</td>
            <td class="blanco" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">NO</td>
            <td class="blanco" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">PIOCITOS</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">INR</td>
            <td class="blanco" colspan="5" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="6" style="height: 15px;">CREATININA</td>
            <td class="blanco" colspan="6" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="2" style="height: 15px;">BD</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="blanco" colspan="7" style="height: 15px;"></td>
            <td class="verde_normal" colspan="4" style="height: 15px;">SAT. 02</td>
            <td class="blanco" colspan="5" style="height: 15px;"></td>
            <td class="blanco" colspan="2" style="height: 15px;"></td>
            <td class="blanco" colspan="4" style="height: 15px;"></td>
            <td class="verde_normal" colspan="6" style="height: 15px;">GLUCOSA</td>
            <td class="blanco" colspan="8" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="verde_normal" colspan="6" style="height: 15px;">LEUCOCITOS</td>
            <td class="blanco" colspan="5" style="height: 15px;">Normal</td>
            <td class="verde_normal" colspan="6" style="height: 15px;">OTROS:</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="verde_normal" colspan="2" style="height: 15px;">BI</td>
            <td class="blanco" colspan="6" style="height: 15px;"></td>
            <td class="blanco" colspan="7" style="height: 15px"></td>
            <td class="verde_normal" colspan="4" style="height: 15px">LACTATO</td>
            <td class="blanco" colspan="5" style="height: 15px"></td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="blanco" colspan="4" style="height: 15px"></td>
            <td class="verde_normal" colspan="6" style="height: 15px">GLUCOSA</td>
            <td class="blanco" colspan="8" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" style="height: 15px">EKG</td>
            <td class="blanco_left" colspan="56" style="height: 15px">
                Ritmo sinusal, ondas P, complejos QRS y ondas T normales, sin cambios significativos en la
                repolarización
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" style="height: 15px">RX TÓRAX</td>
            <td class="blanco_left" colspan="56" style="height: 15px">
                Normal muestra una estructura cardíaca y pulmonar sin anomalías significativas. No se observan
                infiltrados, derrames o colapso pulmonar
            </td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" style="height: 15px">ESPIROMETRÍA</td>
            <td class="blanco" colspan="56" style="height: 15px">No</td>
        </tr>
        <tr>
            <td class="verde_left" colspan="11" rowspan="2" style="height: 15px">OTROS</td>
            <td class="blanco" colspan="56" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="67">F. ESCALAS E ÍNDICES <span
                    style="font-size:7pt;font-family:Arial;font-weight:bold;">(REGISTRAR LO QUE APLIQUE)</span>
            </td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">ESTADO FÍSICO ASA</td>
            <td class="verde_normal" colspan="2" style="height: 15px">I</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">II</td>
            <td class="blanco" colspan="2" style="height: 15px">X</td>
            <td class="verde_normal" colspan="2" style="height: 15px">III</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">IV</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">V</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde_normal" colspan="2" style="height: 15px">VI</td>
            <td class="blanco" colspan="2" style="height: 15px"></td>
            <td class="verde" colspan="15" style="height: 15px">RIESGO CARDÍACO</td>
            <td class="blanco" colspan="16" style="height: 15px">Bajo</td>
        </tr>
        <tr>
            <td class="verde" colspan="12" style="height: 15px">RIESGO PULMONAR</td>
            <td class="blanco" colspan="24" style="height: 15px">Bajo</td>
            <td class="verde" colspan="15" style="height: 15px">RIESGO TROMBOEMBÓLICO</td>
            <td class="blanco" colspan="16" style="height: 15px">Bajo</td>
        </tr>
        <tr style="height: 17px">
            <td class="verde" colspan="12" style="height: 15px">OTROS</td>
            <td class="blanco" colspan="55" style="height: 15px"></td>
        </tr>

    </table>
    <table>
        <tr>
            <td class="morado" colspan="67" style="height: 15px">F. TIEMPO DE ULTIMA INGESTA</td>
        </tr>
        <tr>
            <td class="verde" colspan="16" style="height: 15px">LÍQUIDOS CLAROS</td>
            <td class="blanco" colspan="18" style="height: 15px"></td>
            <td class="verde" colspan="16" style="height: 15px">LECHE DE FÓRMULA</td>
            <td class="blanco" colspan="17" style="height: 15px"></td>
        </tr>
        <tr style="height: 17px">
            <td class="verde" colspan="16" style="height: 15px">LECHE MATERNA</td>
            <td class="blanco" colspan="18" style="height: 15px"></td>
            <td class="verde" colspan="16" style="height: 15px">SÓLIDOS</td>
            <td class="blanco" colspan="17" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" colspan="67" style="height: 15px">G. INDICACIONES</td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">1.</td>
            <td class="blanco" colspan="65" style="height: 15px">NPO 6 horas antes de la cirugía</td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">2.</td>
            <td class="blanco" colspan="65" style="height: 15px">
                No suspender tratamiento habitual
            </td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">3.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">4.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">5.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">6.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">7.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
        <tr>
            <td class="verde" colspan="2" style="height: 15px">8.</td>
            <td class="blanco" colspan="65" style="height: 15px"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" style="height: 15px;" colspan="67">H. PLAN ANESTÉSICO</td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67">
                Anestesia local (Bloqueo periférico)
            </td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="morado" style="height: 15px;" colspan="67">I. OBSERVACIONES</td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 15px;" colspan="67"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="71" class="morado" style="height: 15px;">F. DATOS DEL PROFESIONAL RESPONSABLE</td>
        </tr>
        <tr class="xl78">
            <td colspan="8" class="verde" style="height: 15px;">FECHA

                <font class="font5">(aaaa-mm-dd)</font>
            </td>
            <td colspan="7" class="verde" style="height: 15px;">HORA

                <font class="font5">(hh:mm)</font>
            </td>
            <td colspan="21" class="verde" style="height: 15px;">PRIMER NOMBRE</td>
            <td colspan="19" class="verde" style="height: 15px;">PRIMER APELLIDO</td>
            <td colspan="16" class="verde" style="height: 15px;">SEGUNDO APELLIDO</td>
        </tr>
        <tr>
            <td colspan="8" class="blanco"
                style="height: 15px;">
                <?php echo date('d/m/Y', strtotime(fetchDateByEncounter($encounter))); ?>
            </td>
            <td colspan="7" class="blanco" style="height: 15px;"></td>
            <td colspan="21" class="blanco" style="height: 15px;">María</td>
            <td colspan="19" class="blanco" style="height: 15px;">Jiménez</td>
            <td colspan="16" class="blanco" style="height: 15px;">Coronado</td>
        </tr>
        <tr>
            <td colspan="15" class="verde" style="height: 15px;">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
            <td colspan="26" class="verde" style="height: 15px;">FIRMA</td>
            <td colspan="30" class="verde" style="height: 15px;">SELLO</td>
        </tr>
        <tr>
            <td colspan="15" class="blanco" style="height: 40px">0963691662</td>
            <td colspan="26" class="blanco" style="height: 15px;">&nbsp;</td>
            <td colspan="30" class="blanco" style="height: 15px;">&nbsp;</td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP/HCU-form.018/2021</B>
            </TD>
            <TD colspan="3" class="blanco" style="border: none; text-align: right"><B> PRE ANESTÉSICO (2)</B>
            </TD>
        </TR>
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
            <td colspan="10" class="morado">B. DIAGNÓSTICOS</td>
            <td colspan="2" class="morado" style="text-align: center">CIE</td>
        </tr>
        <tr>
            <td colspan="2" width="18%" rowspan="3" class="verde_left">Pre Operatorio:</td>
            <td class="verde_left" width="2%">1.</td>
            <td class="blanco_left"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre")); ?>
            </td>
            <td class="blanco" width="20%"
                colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre"), 6); ?></td>
        </tr>
        <tr>
            <td class="verde_left" width="2%">2.</td>
            <td class="blanco_left"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre2")); ?>
            </td>
            <td class="blanco" width="20%"
                colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre2"), 6); ?></td>
        </tr>
        <tr>
            <td class="verde_left" width="2%">3.</td>
            <td class="blanco_left"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre3")); ?>
            </td>
            <td class="blanco" width="20%"
                colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre3"), 6); ?></td>
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
            <?php
            $prot_hini = getFieldValue($form_id, "Prot_hini"); // Obtener el valor de la hora
            $prot_hini_timestamp = strtotime($prot_hini); // Convertir la hora a un timestamp

            // Restar una hora y media (5400 segundos) al timestamp

            $prot_opr_value = getFieldValue($form_id, "Prot_opr");
            if ($prot_opr_value == 'avastin') {
                $prot_hfinal_timestamp = $prot_hini_timestamp + 3600;
            } elseif (strpos($prot_opr_value, 'vpp') !== false) {
                $prot_hfinal_timestamp = $prot_hini_timestamp + 10800;
            } else {
                $prot_hfinal_timestamp = $prot_hini_timestamp + 7200;
            }

            $prot_hpre_timestamp = $prot_hini_timestamp - 1800;
            $prot_halta_timestamp = $prot_hfinal_timestamp + 2700;
            // Formatear el nuevo timestamp en formato de hora (HH:MM)
            $pot_hinicio = date("H:i", $prot_hini_timestamp);
            $pot_hfinal = date("H:i", $prot_hfinal_timestamp);
            $pot_hpre = date("H:i", $prot_hpre_timestamp);
            $pot_halta = date("H:i", $prot_halta_timestamp);
            ?>
            <td colspan="5" class="blanco"><?php echo $dateddia; ?></td>
            <td colspan="5" class="blanco"><?php echo $datedmes; ?></td>
            <td colspan="5" class="blanco"><?php echo $datedano; ?></td>
            <td colspan="18" class="blanco"><?php echo $pot_hinicio; ?></td>
            <td colspan="18" class="blanco"><?php echo $pot_hfinal; ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde_left">Dieresis:</td>
            <td colspan="55"
                class="blanco_left"><?php echo obtenerDieresis(getFieldValue($form_id, "Prot_dieresis")); ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde_left">Exposición y Exploración:</td>
            <td colspan="55"
                class="blanco_left"><?php echo obtenerExposicion(getFieldValue($form_id, "Prot_expo")); ?></td>
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
    </TABLE>
</pagebreak>
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
            <td class="blanco_left" colspan="3"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?>
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
            <td class="blanco_left" colspan="29">de Sodio al 0.9%, Manitol el 20%
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
            <td class="blanco_left" colspan="23">Signos Vitales</td>
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
            <td class="blanco_left" colspan="29">T.A.: <?php echo $sistolica . '/' . $diastolica . ' F.C.: ' . $fc; ?>
                SATO2: 100%
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
            <td class="blanco_left" colspan="3"><?php echo $pot_hfinal; ?>
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
            <td class="blanco_left" colspan="23">Dexametasona 4mg líquido parenteral</td>
            <td class="blanco_left" colspan="5"></td>
        </tr>
        <tr>
            <td class="blanco_left" colspan="6"></td>
            <td class="blanco_left" colspan="3">
            </td>
            <td class="blanco_left" colspan="29">
            </td>
            <td class="blanco_break"></td>
            <td class="blanco_left" colspan="23">Ondansetrón 8mg/4ml líquido parenteral</td>
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
            <td class="blanco_left" colspan="3"><?php echo $pot_halta; // Mostrar el nuevo valor de la hora?>
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
            <td class="blanco_left" colspan="29">Monitoreo de signos vitales:
                T.A.: <?php echo $sistolica . '/' . $diastolica . ' F.C.: ' . $fc; ?> SATOS: 100%
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
            <td class="blanco" colspan="17" rowspan="2">
                CLORURO DE SODIO 0,9% LIQUIDO PARENTERAL (1000ML) 60 GOTAS POR
                MINUTO, INTRAVENOSA, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                MANITOL 20% LIQUIDO PARENTERAL 500 MILILITROS INTRAVENOSA, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                MIDAZOLAM LIQUIDO PARENTERAL 5MG/ML (3ML) DOSIS: 2,5 MILIGRAMOS/0,5 MILILITRO, INTRAVENOSA,STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                FENTANILO LIQUIDO PARENTERAL 0,05MG/ ML (10ML) DOSIS: 60 MICROGRAMOS/ 1MILILITRO, INTRAVENOSO, STAT
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                CEFTRIAXONA SOLIDO PARENTERAL 1000MG DOSIS: 1000MILIGRAMOS DILUIDO EN 100MILILITROS DE CLORURO DE SODIO
                AL 0,9% INTRAVENOSA, STAT
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hfinal; ?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                KETOROLACO LIQUIDO PARENTERAL 30MG/ML (1ML) DOSIS: 60MILIGRAMOS DILUIDO EN 100 MILILITROS DE CLORURO DE
                SODIO AL 0,9%, INTRAVENOSA, STAT
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hfinal; ?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                LIDOCAINA CON EPINEFRINA, LIQUIDO PARENTERAL 2% + 1,200,000 (50ML), DOSIS: 80MILIGRAMO /4 MILILITRO, VIA
                INFILTRATIVA, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                BUPIVACAINA SIN EPINEFRINA, LIQUIDO PARENTERAL 0,5% (20ML), DOSIS: 20 MILIGRAMO /4MILILITRO, VIA
                INFILTRATIVA, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hpre; // Mostrar el nuevo valor de la hora?></td>
            <td class="blanco"
                colspan="9"><?php echo getProviderName(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                GENTAMICINA LIQUIDO PARENTERAL 80MG/ML (2ML) DOSIS: 160MILIGRAMOS /2 MILILITROS, SUBCONJUNTIVAL, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hfinal; ?></td>
            <td class="blanco" colspan="9"><?php echo $providerNAME; ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                DEXAMETASONA LIQUIDO PARENTERAL 4MG/DL (2ML) DOSIS: 8MILIGRAMOS /2MILILITROS, SUBCONJUNTIVAL, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hfinal; ?></td>
            <td class="blanco" colspan="9"><?php echo $providerNAME; ?></td>
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
            <td class="blanco" colspan="17" rowspan="2">
                DEXAMETASONA + TOBRAMICINA LIQUIDO OFTALMOLOGICO 0,1%+0,3% (5ML) DOSIS: 1 GOTA, VIA TOPICA, STAT.
            </td>
            <td class="blanco" colspan="6"><?php echo $pot_hfinal; ?></td>
            <td class="blanco" colspan="9"><?php echo $providerNAME; ?></td>
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
        <!--
        <tr>
            <td class="blanco" colspan="17" rowspan="2">
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
        -->
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                                   COLOR="#000000"></FONT></B>
            </TD>
            <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">ADMINISTRACION DE MEDICAMENTOS
                        (1)</FONT></B>
            </TD>
        </TR>
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
            <td class="blanco" colspan="8"><?php echo $pot_hinicio; ?></td>
            <td class="blanco" colspan="8"><?php echo $pot_hfinal; ?></td>
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
<pagebreak>
    <table>
        <tr>
            <td class="morado" colspan="2"><b>QUIRÓFANO</b>
            </td>
        </tr>
        <tr>
            <td class="verde_left"><b>Fecha:</b></td>
            <td class="blanco_left"><?php echo $dateddia . "/" . $datedmes . "/" . $datedano; ?></td>
            ></td></tr>
        <tr>
            <td class="verde_left"><b>Nombre:</b></td>
            <td class="blanco_left"><?php echo $titleres['fname'] . " " . $titleres['mname'] . " " . $titleres['lname'] . " " . $titleres['lname2']; ?></td>
        </tr>
        <tr>
            <td class="verde_left"><b>Cirugía realizada:</b></td>
            <td class="blanco_left"><?php echo obtenerIntervencionesPropuestas(getFieldValue($form_id, "Prot_opr")) . " ";
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
                } ?></td>
        </tr>
        <tr>
            <td class="verde">MEDICAMENTOS/INSUMOS/EQUIPOS UTILIZADOS EN EL PROCEDIMIENTO</td>
            <td class="verde">CANTIDAD</td>
        </tr>
        <?php
        echo printPatientPreBilling($pid, $form_encounter);
        ?>
    </table>
    <br>
    <br>
    <br>
    <br>
    <?php
    echo $providerNAME;
    echo "<br>";
    echo getProviderRegistro($providerID);
    ?>
</pagebreak>
<pagebreak>
    <?php
    generatePageHeader($facilityService, $web_root);
    ?>
    <P style="text-align: center"><B>PLAN DE EGRESO</B></P>
    <P><B>CIRUGIA OCULAR</B></P>
    <P><b>Diagn&oacute;stico de egreso: </b>
        <?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost")); ?></P>
    <P><b>Fecha: </b>
        <?php
        echo $dateddia . " de " . $datedmes . " del " . $datedano;
        ?>
    </P>
    <P><b>Egresado a: </b>Casa</P>
    <P>Instrucciones para el
        paciente <?php echo text(xlt($titleres['title']) . " " . $titleres['fname'] . " " . $titleres['lname']); ?> y
        familia:</P>
    <P>MEDICAMENTOS
        RECETADOS: <U><B>Tobracort (Tobramicina+Dexametazona) 1 gota cada 3
                horas por 21 d&iacute;as</B></U><U>.</U></P>
    <P>ACTIVIDAD: Se debe
        mantener reposo en la postura de acuerdo a la indicaci&oacute;n del
        m&eacute;dico.</P>
    <P>HIGIENE: Debe ba&ntilde;arse
        el cuerpo con agua y jab&oacute;n incluyendo la cara.</P>
    <P>ALIMENTACI&Oacute;N:
        No hay restricci&oacute;n de dieta. Evite fumar o tomar alcohol hasta
        que est&eacute; completamente recuperado.</P>
    <P>CUIDADOS
        ESPECIALES: Mantenga parche y protector ocular durante 24 horas,
        seg&uacute;n prescripci&oacute;n m&eacute;dica. Controle sangrado
        (Observe si mancha la gasa).</P>
    <P>EDUCACION AL PACIENTE:
        Pueden sentir picor, sensaci&oacute;n de cuerpo extra&ntilde;o,
        pinchazos espor&aacute;dicos: Son consecuencia de los punto
        conjuntivales.</P>
    <P>Cumpla con el
        tratamiento ambulatorio ya sea con colirios o pomadas de acuerdo a la
        prescripci&oacute;n de su m&eacute;dico.</P>
    <P>Un paciente sometido a
        cirug&iacute;a ocular <U><B>NO DEBE</B></U> en ning&uacute;n caso:
        Conducir, realizar actividades peligrosas, ni levantar pesos.</P>
    <P>La lectura y la
        televisi&oacute;n no est&aacute;n contraindicadas, excepto si
        producen molestias o impiden la posici&oacute;n recomendada.</P>
    <P>OTROS:</P>
    <P><U><B>INFORME DE
                EGRESO DE ENFERMERIA</B></U>:</P>
    <P>PACIENTE EGRESA EN
        CONDICIONES FAVORABLES PARA SU SALUD, CON INDICACIONES MEDICA, SI
        LLEVA LA MEDICACI&Oacute;N.</P>
    <br><br><br><br><br>
    <P><b>M&eacute;dico
            tratante: </b><?php
        echo getProviderName($providerID);
        ?>
        <b>Tel&eacute;fono: </b>2286080</P>
</pagebreak>

</BODY>
</html>
