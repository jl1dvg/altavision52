<?php
require_once("common_vars.php");
include("common_header.php");
?>
<!--[CONSTANTES VITALES / BALANCE HÍDRICO (1)]-->
<TABLE>
    <tr>
        <td colspan='71' class='morado'>A. DATOS DEL ESTABLECIMIENTO
            Y USUARIO / PACIENTE
        </td>
    </tr>
    <tr>
        <td colspan='15' height='27' class='verde'>INSTITUCIÓN DEL SISTEMA</td>
        <td colspan='6' class='verde'>UNICÓDIGO</td>
        <td colspan='18' class='verde'>ESTABLECIMIENTO DE SALUD</td>
        <td colspan='18' class='verde'>NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
        <td colspan='14' class='verde' style='border-right: none'>NÚMERO DE ARCHIVO</td>
    </tr>
    <tr>
        <td colspan='15' height='27' class='blanco'><?php echo $titleres['pricelevel']; ?></td>
        <td colspan='6' class='blanco'>&nbsp;</td>
        <td colspan='18' class='blanco'>ALTAVISION</td>
        <td colspan='18' class='blanco'><?php echo $titleres['pubpid']; ?></td>
        <td colspan='14' class='blanco' style='border-right: none'><?php echo $titleres['pubpid']; ?></td>
    </tr>
    <tr>
        <td colspan='15' rowspan='2' height='41' class='verde' style='height:31.0pt;'>PRIMER APELLIDO</td>
        <td colspan='13' rowspan='2' class='verde'>SEGUNDO APELLIDO</td>
        <td colspan='13' rowspan='2' class='verde'>PRIMER NOMBRE</td>
        <td colspan='10' rowspan='2' class='verde'>SEGUNDO NOMBRE</td>
        <td colspan='3' rowspan='2' class='verde'>SEXO</td>
        <td colspan='6' rowspan='2' class='verde'>FECHA NACIMIENTO</td>
        <td colspan='3' rowspan='2' class='verde'>EDAD</td>
        <td colspan='8' class='verde' style='border-right: none; border-bottom: none'>CONDICIÓN EDAD <font
                class='font7'>(MARCAR)</font></td>
    </tr>
    <tr>
        <td colspan='2' height='17' class='verde'>H</td>
        <td colspan='2' class='verde'>D</td>
        <td colspan='2' class='verde'>M</td>
        <td colspan='2' class='verde' style='border-right: none'>A</td>
    </tr>
    <tr>
        <td colspan='15' height='27' class='blanco'><?php echo $titleres['lname']; ?></td>
        <td colspan='13' class='blanco'><?php echo $titleres['lname2']; ?></td>
        <td colspan='13' class='blanco'><?php echo $titleres['fname']; ?></td>
        <td colspan='10' class='blanco'><?php echo $titleres['mname']; ?></td>
        <td colspan='3' class='blanco'><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3"
            class="blanco"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter)))); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan='2' class='blanco'>&nbsp;</td>
        <td colspan='2' class='blanco'>&nbsp;</td>
        <td colspan='2' class='blanco' style='border-right: none'>&nbsp;</td>
    </tr>
</TABLE>
<table>
    <tr>
        <td class="morado" colspan="24">B. CONSTANTES VITALES</td>
    </tr>
    <tr>
        <td class="verde" colspan="3">FECHA</td>
        <td class="blanco_left" colspan="3"><?php echo $dateddia . "/" . $datedmes . "/" . $datedano; ?></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
    </tr>
    <tr>
        <td class="verde" colspan="3">DÍA DE INTERNACIÓN</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
    </tr>
    <tr>
        <td class="verde" colspan="3">DÍA POST QUIRÚRGICO</td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
        <td class="blanco_left" colspan="3"></td>
    </tr>
    <tr>
        <td class="verde" rowspan="2">PULSO</td>
        <td class="verde" rowspan="2">TEMP</td>
        <td class="verde"></td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
        <td class="verde">AM</td>
        <td class="verde">PM</td>
        <td class="verde">HS</td>
    </tr>
    <tr>
        <td class="verde" rowspan="2">HORA</td>
        <?php
        // Determinar si la hora es AM o PM
        $pot_hpre_period = date("A", $prot_hpre_timestamp);
        ?>

        <td class="blanco_left" rowspan="2">
            <?php echo ($pot_hpre_period == "AM") ? $pot_hpre : ""; ?>
        </td>
        <td class="blanco_left" rowspan="2">
            <?php echo ($pot_hpre_period == "PM") ? $pot_hpre : ""; ?>
        </td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
        <td class="blanco_left" rowspan="2"></td>
    </tr>
    <tr>
        <td class="verde" rowspan="2">•</td>
        <td class="verde" rowspan="2">△</td>
    </tr>
    <tr>
        <td class="verde"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
        <td class="blanco_left_remini"></td>
    </tr>
    <?php
    // Opciones para FC y para Temperatura (valores enteros según diseño)
    $fc_values = [140, 130, 120, 110, 100, 90, 80, 70, 60, 50]; // Valores posibles de FC
    $temp_values = [44, 43, 42, 41, 40, 39, 38, 37, 36, 35];      // Valores posibles de Temperatura
    $cell = 21; // Número de celdas a generar en cada fila

    // Redondear el valor actual de FC a uno de los de $fc_values
    $fc_random = getClosest($fc, $fc_values);
    // Definir la columna donde se colocarán los marcadores dentro del bucle (0-indexado)
    // Dependiendo de la hora, el marcador se ubicará:
    // Si es AM: en la primera celda (índice 0)
    // Si es PM: en la segunda celda (índice 1)
    $marker_column = ($pot_hpre_period == "AM") ? 0 : 1;
    ?>

    <?php foreach ($fc_values as $index => $value): ?>
        <tr>
            <td rowspan="2" class="cyan_left" style="border: none"><?php echo $value; ?></td>
            <td rowspan="2" class="cyan_left" style="border: none"><?php echo $temp_values[$index]; ?></td>
            <td class="cyan_left"></td>

            <?php for ($i = 0; $i < $cell; $i++): ?>
                <td class="blanco_left_remini">
                    <?php
                    if ($i == $marker_column) {
                        $output = '';
                        if ($value == $fc_random) {
                            $output .= '•';
                        }
                        if ($temp_values[$index] == $temperatura) {
                            $output .= '△';
                        }
                        echo $output;
                    } else {
                        echo '';
                    }
                    ?>
                </td>
            <?php endfor; ?>

        </tr>
        <tr>
            <td class="cyan_left" style="border-top: 2px solid #808080"></td>

            <?php for ($i = 0; $i < $cell; $i++): ?>
                <td class="blanco_left_remini"></td>
            <?php endfor; ?>

        </tr>
        <tr>
            <td class="cyan_left" style="border: none"></td>
            <td class="cyan_left" style="border: none"></td>
            <td class="cyan_left"></td>
            <?php for ($i = 0; $i < $cell; $i++): ?>
                <td class="blanco_left_remini"></td>
            <?php endfor; ?>

        </tr>
        <tr>
            <td class="cyan_left" style="border: none"></td>
            <td class="cyan_left" style="border: none"></td>
            <td class="cyan_left"></td>
            <?php for ($i = 0; $i < $cell; $i++): ?>
                <td class="blanco_left_remini"></td>
            <?php endfor; ?>

        </tr>
    <?php endforeach; ?>
</table>
<table>
    <tr>
        <td class="cyan_left" width="14.5%">F. RESPIRATORIA X min</td>
        <td class="blanco_left_mini"><?php echo $fr; ?></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">PULSIOXIMETRÍA %</td>
        <td class="blanco_left_mini"><?php echo $spo2; ?></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">PRESIÓN SISTÓLICA</td>
        <td class="blanco_left_mini"><?php echo $sistolica; ?></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">PRESIÓN DIASTÓLICA</td>
        <td class="blanco_left_mini"><?php echo $diastolica; ?></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">RESPONSABLE</td>
        <td class="blanco_left_mini">Lcda. Solange Antonella Vega Pilco</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan='8' class='morado'>C. MEDIDAS ANTROPOMÉTRICAS</td>
    </tr>
    <tr>
        <td class='cyan_left' width="14.5%">PESO (kg)</td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
    </tr>
    <tr>
        <td class='cyan_left'>TALLA (cm)</td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
    </tr>
    <tr>
        <td class='cyan_left'>PERÍMETRO CEFÁLICO (cm)</td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
    </tr>
    <tr>
        <td class='cyan_left'>PERÍMETRO ABDOMINAL (cm)</td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
    </tr>
    <tr>
        <td class='cyan_left'>OTROS ESPECIFIQUE</td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
        <td class='blanco_left_mini'></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado" colspan="9">D. INGESTA - ELIMINACIÓN / BALANCE HÍDRICO</td>
    </tr>
    <tr>
        <td class="cyan_left" rowspan="4" width="2%">INGRESOS ML</td>
        <td class="cyan_left" width="12.5%">ENTERAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">PARENTERAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">VÍA ORAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">TOTAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left" rowspan="6">ELIMINACIONES ML</td>
        <td class="cyan_left">ORINA</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">DRENAJE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">VÓMITO</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">DIARREAS</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">OTROS ESPECIFIQUE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">TOTAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left" colspan="2"><b>BALANCE HÍDRICO TOTAL</b></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left" colspan="2">DIETA PRESCRITA</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left" colspan="2">NÚMERO DE COMIDAS</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left" colspan="2">NÚMERO DE MICCIONES</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left" colspan="2">NÚMERO DE DEPOSICIONES</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado" colspan="8">E. CUIDADOS GENERALES</td>
    </tr>
    <tr>
        <td class="cyan_left" width="12.5%">ASEO</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">BAÑO</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">REPOSO ESPECIFIQUE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">POSICIÓN ESPECIFIQUE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">OTROS ESPECIFIQUE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado" colspan="8">F. FECHA DE COLOCACIÓN DE DISPOSITIVOS MÉDICOS (aaaa-mm-dd)</td>
    </tr>
    <tr>
        <td class="cyan_left" width="12.5%">VÍA CENTRAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">VÍA PERIFÉRICA</td>
        <td class="blanco_left_mini"><?php echo $datedano . "/" . $datedmes . "/" . $dateddia; ?></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">SONDA NASOGÁSTRICA</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">SONDA VESICAL</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">OTROS ESPECIFIQUE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
    <tr>
        <td class="cyan_left">RESPONSABLE</td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
        <td class="blanco_left_mini"></td>
    </tr>
</table>
<table style='border: none'>
    <TR>
        <TD colspan='6' HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                               COLOR='#000000'>SNS-MSP/HCU-form.020/2021</FONT></B>
        </TD>
        <TD colspan='3' ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR='#000000'>CONSTANTES VITALES / BALANCE HÍDRICO
                    (1)</FONT></B>
        </TD>
    </TR>
    ]
</TABLE>
