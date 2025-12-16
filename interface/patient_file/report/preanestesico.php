<?php
require_once("common_vars.php");
include("common_header.php");
?>
<!--[PREANESTESICO A]-->
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
        <td colspan="3"
            class="blanco"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter)))); ?></td>
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

            $items = extractItemsFromQuery($form_id, $pid, $form_encounter, $proced_id);
            $ojoValue = getFieldValue($form_id, "Prot_ojo");

            // Realizar acciones con los items extraídos
            foreach ($items

            as $item) {
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
<pagebreak>
    <!--[PREANESTESICO B]-->
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
