<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
require_once("$srcdir/report.inc");
preg_match('/^(.*)_(\d+)$/', $key, $res);
$formdir = $res[1];
$form_id = $res[2];


$carePlanDetails = getCarePlanDetails($pid, $form_id);
$fechaProcedimiento = $carePlanDetails['FechaProcedimiento'];
$mesesEnIngles = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$mesesEnEspanol = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

$fechaCompleta = date('d', strtotime($fechaProcedimiento)) . ' de ' . $mesesEnEspanol[date('n', strtotime($fechaProcedimiento)) - 1] . ' de ' . date('Y', strtotime($fechaProcedimiento));
$codigoProcedimiento = $carePlanDetails['CodigoProcedimiento'];
$ojoProcedimiento = $carePlanDetails['OjoProcedimiento'];
$procedimiento = $carePlanDetails['Procedimiento'];
$medicoProcedimiento = $carePlanDetails['MedicoProcedimiento'];

$providerID = getProviderIdOfEncounter($form_encounter);
$providerNAME = getProviderName($providerID);
$proced_id = $carePlanDetails['CodigoProcedimiento'];
$resultadoDX = obtenerCodigosImpPlan($pid, $form_encounter);

if ($proced_id == '65855') {
    $proced_id = 'SLT';
} elseif ($proced_id == '281339') {
    $proced_id = 'yag';
} elseif ($proced_id == '66761') {
    $proced_id = 'irido';
} elseif ($proced_id == '281362') {
    $proced_id = 'frp';
} elseif ($proced_id == '281351') {
    $proced_id = 'prp';
} elseif ($proced_id == '281340') {
    $proced_id = 'prp';
}

?>
<html>
<head>
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
</head>
<body>
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
        <td colspan="8" class="verde" style="border-right: none; border-bottom: none">CONDICIÓN EDAD (MARCAR)</td>
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
        <td colspan="3"
            class="blanco"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime($fechaProcedimiento))); ?></td>
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
            echo $ojoProcedimiento;
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
            foreach ($resultadoDX as $resultado) {
                echo $resultado['codedesc'] . "<br>";
            }
            ?>
        </td>
        <td colspan="4" class="verde">CIE 10:</td>
        <td colspan="11" class="blanco_left">
            <?php
            foreach ($resultadoDX as $resultado) {
                echo $resultado['code'] . "<br>";
            } ?>
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
            }
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
                <?php echo date("d/m/Y", strtotime($fechaProcedimiento)); ?>
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
                <?php echo date("d/m/Y", strtotime($fechaProcedimiento)); ?>
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
                <?php echo date("d/m/Y", strtotime($fechaProcedimiento)); ?>
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
<?php
generatePageHeader($facilityService, $web_root);
?>
<P style="text-align: center"><B>INFORME PROCEDIMIENTO</B></P>
<P style="text-align: justify">
    <B>Fecha</B>: <?php echo $fechaCompleta; ?><br>
    <B>Paciente:</B> <?php echo text($titleres['fname'] . " " . $titleres['lname'] . " " . $titleres['lname2']); ?><br>
    <B>Ojo:</B> <?php echo ucfirst($ojoProcedimiento); ?><br>
    <B>Procedimiento:</B> <?php echo ucfirst($procedimiento); ?>.
    (COD. <?php echo $codigoProcedimiento; ?>)<br>
</P><br><br><br>
<p style="text-align: justify">
    <?php
    echo generateProcedureDescription($codigoProcedimiento, $ojoProcedimiento);
    ?>
</p>
<br>
<p style="text-align: justify">
    Atentamente,
</p>
<br><br><br><br><br>
<P style="text-align: justify">
    <B>
        <?php
        echo getProviderName($providerID);
        ?>
        <br>
        <?php
        echo getProviderEspecialidad($providerID);
        ?>
        <br>
        Centro Oftalmol&oacute;gico AltaVisi&oacute;n
        <br>
        Guayaquil &ndash; Ecuador</B></P>
</body>
</html>
