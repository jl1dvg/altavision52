<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
require_once("$srcdir/report.inc");
preg_match('/^(.*)_(\d+)$/', $key, $res);
$formdir = $res[1];
$form_id = $res[2];

$providerID = getProviderIdOfEncounter($form_encounter);
$providerNAME = getProviderNameConcat($providerID);

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
$diastolica = rand(70, 85);
$fc = rand(75, 105);
$fr = rand(15, 22);
$spo2 = rand(95, 100);
$glucosa = rand(70, 110);
$temperatura = rand(36, 37); // Valor actual de Temperatura (entero)

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
            border: 3px solid #808080;
            border-collapse: collapse;
            margin-bottom: 3px;
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

        td.cyan_left {
            text-align: left;
            vertical-align: middle;
            background: #CCFFFF;
            font-size: 5pt;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.cyan_vert {
            height: 20px;
            vertical-align: middle;
            background: #CCFFFF;
            font-size: 5pt;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            writing-mode: vertical-rl;
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

        td.blanco_left_mini {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 11px;
            text-align: left;
            vertical-align: middle;
            font-size: 6pt;
        }

        td.blanco_left_remini {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 5px;
            text-align: left;
            vertical-align: middle;
            font-size: 6pt;
        }

        td.moradopro {
            text-align: left;
            vertical-align: middle;
            background-color: #CCCCFF;
            font-size: 9pt;
            font-weight: bold;
            height: 18px;
        }

        td.verdepro {
            height: 18px;
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.verde_leftpro {
            height: 18px;
            text-align: left;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.verde_normalpro {
            height: 18px;
            text-align: center;
            vertical-align: middle;
            background-color: #CCFFCC;
            font-size: 7pt;
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
        }

        td.blancopro {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 18px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }

        td.blanco_breakpro {
            border-left: 3px solid #808080;
            border-right: 3px solid #808080;
            height: 20px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }

        td.blanco_leftpro {
            border-top: 1px solid #808080;
            border-right: 1px solid #808080;
            height: 18px;
            text-align: left;
            vertical-align: middle;
            font-size: 7pt;
        }
    </style>
</HEAD>
<BODY>
<!--Consentimiento A-->
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
        <td colspan="6"
            class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3"
            class="blanco"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter)))); ?></td>
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
            foreach ($items as $item) {
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
<!--[DECLARACIÓN DE CONSENTIMIENTO INFORMADO]-->
<pagebreak>
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
<!--[PROTOCOLO A]-->
<pagebreak>
    <TABLE>
        <tr>
            <td colspan="71" class="moradopro">A. DATOS DEL ESTABLECIMIENTO
                Y USUARIO / PACIENTE
            </td>
        </tr>
        <tr>
            <td colspan="15" height="27" class="verdepro">INSTITUCIÓN DEL SISTEMA</td>
            <td colspan="6" class="verdepro">UNICÓDIGO</td>
            <td colspan="18" class="verdepro">ESTABLECIMIENTO DE SALUD</td>
            <td colspan="18" class="verdepro">NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
            <td colspan="14" class="verdepro" style="border-right: none">NÚMERO DE ARCHIVO</td>
        </tr>
        <tr>
            <td colspan="15" height="27" class="blancopro"><?php echo $titleres['pricelevel']; ?></td>
            <td colspan="6" class="blancopro">&nbsp;</td>
            <td colspan="18" class="blancopro">ALTA VISION</td>
            <td colspan="18" class="blancopro"><?php echo $titleres['pubpid']; ?></td>
            <td colspan="14" class="blancopro" style="border-right: none"><?php echo $titleres['pubpid']; ?></td>
        </tr>
        <tr>
            <td colspan="15" rowspan="2" height="41" class="verdepro" style="height:31.0pt;">PRIMER APELLIDO</td>
            <td colspan="13" rowspan="2" class="verdepro">SEGUNDO APELLIDO</td>
            <td colspan="13" rowspan="2" class="verdepro">PRIMER NOMBRE</td>
            <td colspan="10" rowspan="2" class="verdepro">SEGUNDO NOMBRE</td>
            <td colspan="3" rowspan="2" class="verdepro">SEXO</td>
            <td colspan="6" rowspan="2" class="verdepro">FECHA NACIMIENTO</td>
            <td colspan="3" rowspan="2" class="verdepro">EDAD</td>
            <td colspan="8" class="verdepro" style="border-right: none; border-bottom: none">CONDICIÓN EDAD <font
                    class="font7">(MARCAR)</font></td>
        </tr>
        <tr>
            <td colspan="2" height="17" class="verdepro">H</td>
            <td colspan="2" class="verdepro">D</td>
            <td colspan="2" class="verdepro">M</td>
            <td colspan="2" class="verdepro" style="border-right: none">A</td>
        </tr>
        <tr>
            <td colspan="15" height="27" class="blancopro"><?php echo $titleres['lname']; ?></td>
            <td colspan="13" class="blancopro"><?php echo $titleres['lname2']; ?></td>
            <td colspan="13" class="blancopro"><?php echo $titleres['fname']; ?></td>
            <td colspan="10" class="blancopro"><?php echo $titleres['mname']; ?></td>
            <td colspan="3" class="blancopro"><?php echo substr($titleres['sex'], 0, 1); ?>
            </td>
            <td colspan="6" class="blancopro"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
            <td colspan="3"
                class="blancopro"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter)))); ?></td>
            <td colspan="2" class="blancopro">&nbsp;</td>
            <td colspan="2" class="blancopro">&nbsp;</td>
            <td colspan="2" class="blancopro">&nbsp;</td>
            <td colspan="2" class="blancopro" style="border-right: none">X</td>
        </tr>
    </TABLE>
    <table>
        <tr>
            <td colspan="10" class="moradopro">B. DIAGNÓSTICOS</td>
            <td colspan="2" class="moradopro" style="text-align: center">CIE</td>
        </tr>
        <tr>
            <td colspan="2" width="18%" rowspan="3" class="verde_leftpro">Pre Operatorio:</td>
            <td class="verde_leftpro" width="2%">1.</td>
            <td class="blanco_leftpro"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre")); ?>
            </td>
            <td class="blanco" width="20%"
                colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre"), 6); ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro" width="2%">2.</td>
            <td class="blanco_leftpro"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre2")); ?>
            </td>
            <td class="blanco" width="20%"
                colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre2"), 6); ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro" width="2%">3.</td>
            <td class="blanco_leftpro"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre3")); ?>
            </td>
            <td class="blanco" width="20%"
                colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpre3"), 6); ?></td>
        </tr>
        <tr>
            <td colspan="2" rowspan="3" class="verde_leftpro">Post Operatorio:</td>
            <td class="verde_leftpro">1.</td>
            <td class="blanco_leftpro"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost")); ?></td>
            <td class="blanco" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpost"), 6); ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro">2.</td>
            <td class="blanco_leftpro"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost2")); ?></td>
            <td class="blanco" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpost2"), 6); ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro">3.</td>
            <td class="blanco_leftpro"
                colspan="7"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost3")); ?></td>
            <td class="blanco" colspan="2"><?php echo substr(getFieldValue($form_id, "Prot_dxpost3"), 6); ?></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="11" class="moradopro">C. PROCEDIMIENTO</td>
            <td colspan="2" class="verde_leftpro" style="text-align: center">Electiva</td>
            <td colspan="1" class="blancopro" style="text-align: center">X</td>
            <td colspan="2" class="verde_leftpro" style="text-align: center">Emergencia</td>
            <td colspan="1" class="blancopro" style="text-align: center"></td>
            <td colspan="2" class="verde_leftpro" style="text-align: center">Urgencia</td>
            <td colspan="1" class="blancopro" style="text-align: center"></td>
        </tr>
        <tr>
            <td colspan="2" class="verde_leftpro">Proyectado:</td>
            <td class="blanco_leftpro"
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
            <td colspan="2" class="verde_leftpro">Realizado:</td>
            <td class="blanco_leftpro"
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
            <td class="moradopro" colspan="20">D. INTEGRANTES DEL EQUIPO QUIRÚRGICO</td>
        </tr>
        <tr>
            <td class="verde_leftpro" colspan="2">Cirujano 1:</td>
            <td class="blancopro" colspan="8"><?php echo $providerNAME; ?></td>
            <td class="verde_leftpro" colspan="2">Instrumentista:</td>
            <td class="blancopro" colspan="8"><?php
                $instrumentistaOK = getFieldValue($form_id, "Prot_Instrumentistas");
                if ($instrumentistaOK == 'Si') {
                    echo "Dr. Jorge Luis de Vera Gutiérrez";
                } ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro" colspan="2">Cirujano 2:</td>
            <td class="blancopro"
                colspan="8"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_Cirujano")); ?></td>
            <td class="verde_leftpro" colspan="2">Circulante:</td>
            <td class="blancopro" colspan="8"><?php
                echo getFieldValue($form_id, "Prot_opr") == "avastin" ? " " : "Lcda. Solange Antonella Vega Pilco";
                ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro" colspan="3">Primer Ayudante:</td>
            <td class="blancopro"
                colspan="7"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_ayudante")); ?></td>
            <td class="verde_leftpro" colspan="3">Anestesiologo/a:</td>
            <td class="blancopro"
                colspan="7"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
        </tr>
        <tr>
            <td class="verde_leftpro" colspan="3">Segundo Ayudante:</td>
            <td class="blancopro" colspan="7"></td>
            <td class="verde_leftpro" colspan="3">Ayudante Anestesia:</td>
            <td class="blancopro" colspan="7"></td>
        </tr>
        <tr>
            <td class="verde_leftpro" colspan="3">Tercer Ayudante:</td>
            <td class="blancopro" colspan="7"></td>
            <td class="verde_leftpro" colspan="1">Otros:</td>
            <td class="blancopro" colspan="9"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="70" class="moradopro">F. TIEMPOS QUIRÚRGICOS</td>
        </tr>
        <tr>
            <td colspan="19" rowspan="2" class="verdepro">FECHA DE OPERACIÓN</td>
            <td colspan="5" class="verdepro">DIA</td>
            <td colspan="5" class="verdepro">MES</td>
            <td colspan="5" class="verdepro">AÑO</td>
            <td colspan="18" class="verdepro">HORA DE INICIO</td>
            <td colspan="18" class="verdepro">HORA DE TERMINACIÓN</td>
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
            } elseif (strpos($prot_opr_value, 'extaceite') !== false) {
                $prot_hfinal_timestamp = $prot_hini_timestamp + 10800;
            } elseif (strpos($prot_opr_value, 'facovpp') !== false) {
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
            <td colspan="5" class="blancopro"><?php echo $dateddia; ?></td>
            <td colspan="5" class="blancopro"><?php echo $datedmes; ?></td>
            <td colspan="5" class="blancopro"><?php echo $datedano; ?></td>
            <td colspan="18" class="blancopro"><?php echo $pot_hinicio; ?></td>
            <td colspan="18" class="blancopro"><?php echo $pot_hfinal; ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde_leftpro">Dieresis:</td>
            <td colspan="55"
                class="blanco_leftpro"><?php echo obtenerDieresis(getFieldValue($form_id, "Prot_dieresis")); ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde_leftpro">Exposición y Exploración:</td>
            <td colspan="55"
                class="blanco_leftpro"><?php echo obtenerExposicion(getFieldValue($form_id, "Prot_expo")); ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde_leftpro">Hallazgos Quirúrgicos:</td>
            <td colspan="55" class="blanco_leftpro"><?php echo getFieldValue($form_id, "Prot_halla"); ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde_leftpro">Procedimiento Quirúrgicos:</td>
            <td colspan="55"
                class="blanco_leftpro"><?php echo html_entity_decode(html_entity_decode(getFieldValue($form_id, "Prot_proced"))); ?></td>
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
<!--[PROTOCOLO B]-->
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
                colspan="5"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_ayudante")); ?></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderEspecialidad(getFieldValue($form_id, "Prot_ayudante")) ?></td>
            <td class="blanco"
                colspan="5"></td>
            <td class="blanco"
                colspan="5"><?php echo getProviderRegistro(getFieldValue($form_id, "Prot_ayudante")) ?></td>
        </tr>
        <tr>
            <td class="blanco" style="height: 60"
                colspan="5"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
</BODY>
</html>
