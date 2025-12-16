<?php
require_once("common_vars.php");
include("common_header.php");
?>
<!--[POSTANESTESICO A]-->
<TABLE>
    <tr>
        <td colspan="71" class="morado_landscape">A. DATOS DEL ESTABLECIMIENTO
            Y USUARIO / PACIENTE
        </td>
    </tr>
    <tr>
        <td colspan="15" class="verde_landscape">INSTITUCIÓN DEL SISTEMA</td>
        <td colspan="6" class="verde_landscape">UNICÓDIGO</td>
        <td colspan="18" class="verde_landscape">ESTABLECIMIENTO DE SALUD</td>
        <td colspan="18" class="verde_landscape">NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
        <td colspan="14" class="verde_landscape" style="border-right: none">NÚMERO DE ARCHIVO</td>
    </tr>
    <tr>
        <td colspan="15" class="blanco_landscape"><?php echo $titleres['pricelevel']; ?></td>
        <td colspan="6" class="blanco_landscape">&nbsp;</td>
        <td colspan="18" class="blanco_landscape">ALTA VISION</td>
        <td colspan="18" class="blanco_landscape"><?php echo $titleres['pubpid']; ?></td>
        <td colspan="14" class="blanco_landscape" style="border-right: none"><?php echo $titleres['pubpid']; ?></td>
    </tr>
    <tr>
        <td colspan="15" rowspan="2" height="41" class="verde_landscape" style="height:31.0pt;">PRIMER APELLIDO</td>
        <td colspan="13" rowspan="2" class="verde_landscape">SEGUNDO APELLIDO</td>
        <td colspan="13" rowspan="2" class="verde_landscape">PRIMER NOMBRE</td>
        <td colspan="10" rowspan="2" class="verde_landscape">SEGUNDO NOMBRE</td>
        <td colspan="3" rowspan="2" class="verde_landscape">SEXO</td>
        <td colspan="6" rowspan="2" class="verde_landscape">FECHA NACIMIENTO</td>
        <td colspan="3" rowspan="2" class="verde_landscape">EDAD</td>
        <td colspan="8" class="verde_landscape" style="border-right: none; border-bottom: none">CONDICIÓN EDAD <font
                class="font7">(MARCAR)</font></td>
    </tr>
    <tr>
        <td colspan="2" height="17" class="verde_landscape">H</td>
        <td colspan="2" class="verde_landscape">D</td>
        <td colspan="2" class="verde_landscape">M</td>
        <td colspan="2" class="verde_landscape" style="border-right: none">A</td>
    </tr>
    <tr>
        <td colspan="15" class="blanco_landscape"><?php echo $titleres['lname']; ?></td>
        <td colspan="13" class="blanco_landscape"><?php echo $titleres['lname2']; ?></td>
        <td colspan="13" class="blanco_landscape"><?php echo $titleres['fname']; ?></td>
        <td colspan="10" class="blanco_landscape"><?php echo $titleres['mname']; ?></td>
        <td colspan="3" class="blanco_landscape"><?php echo substr($titleres['sex'], 0, 1); ?></td>
        <td colspan="6" class="blanco_landscape"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3"
            class="blanco_landscape"><?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter)))); ?></td>
        <td colspan="2" class="blanco_landscape">&nbsp;</td>
        <td colspan="2" class="blanco_landscape">&nbsp;</td>
        <td colspan="2" class="blanco_landscape">&nbsp;</td>
        <td colspan="2" class="blanco_landscape" style="border-right: none">X</td>
    </tr>
</TABLE>
<table>
    <tr>
        <td colspan="9" class="morado_landscape">B. PROCEDIMIENTO QUIRÚRGICO ANESTÉSICO</td>
    </tr>
    <tr>
        <td class="verde_landscape" rowspan="2">ESPECIALIDAD</td>
        <td class="blanco_left_landscape">Oftalmología</td>
        <td class="verde_landscape" rowspan="2">DIAGNÓSTICO</td>
        <td class="blanco_left_landscape"
            colspan="6"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre")); ?></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape"><br></td>
        <td class="blanco_left_landscape"
            colspan="6"><?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpre2")); ?></td>
    </tr>
    <tr>
        <td class="verde_landscape" rowspan="2">CIRUGÍA REALIZADA</td>
        <td class="blanco_left_landscape"></td>
        <td class="verde_landscape" rowspan="2">TÉCNICA ANESTÉSICA</td>
        <td class="blanco_left_landscape">GENERAL</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">LOCAL</td>
        <td class="blanco_left_landscape">x</td>
        <td class="blanco_left_landscape">TRONCULAR</td>
        <td class="blanco_left_landscape"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">NEUROAXIAL</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">INTRAVENOSA</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">SEDACIÓN</td>
        <td class="blanco_left_landscape"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado_landscape" colspan="39">C. ENTREGA - RECEPCIÓN DEL PACIENTE</td>
    </tr>
    <tr>
        <td class="blanco_landscape" rowspan="3" width="10%">INGRESO A UNIDAD CUIDADOS POSTANESTÉSICOS</td>
        <td class="blanco_left_landscape" rowspan="2" colspan="36">ESCALAS / ÍNDICES / PUNTAJES</td>
        <td class="blanco_landscape" colspan="2">RESPONSABLES</td>
    </tr>
    <tr>
        <td class="blanco_landscape" colspan="2">ENTREGA</td>
    </tr>
    <tr>
        <td class="verde_landscape" colspan="8">ALDRETE MODIFICADO</td>
        <td class="verde_landscape" colspan="8">BROMAGE</td>
        <td class="verde_landscape" colspan="14">RAMSAY</td>
        <td class="verde_landscape" colspan="6"></td>
        <td class="blanco_landscape">NOMBRE</td>
        <td class="blanco_landscape" width="25%"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape" rowspan="2">FECHA:</td>
        <td class="blanco_left_landscape">7</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">8</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">9</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">10</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">100%</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">66%</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">23%</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">0%</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">0</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">1</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">2</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">3</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">4</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">5</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">6</td>
        <td class="blanco_left_landscape"></td>
        <td class="verde_landscape" colspan="6"><br></td>
        <td class="blanco_landscape">FIRMA</td>
        <td class="blanco_landscape"></td>
    </tr>
    <tr>
        <td class="verde_landscape" colspan="22">EVA</td>
        <td class="verde_landscape" colspan="14">STEWARD</td>
        <td class="blanco_landscape" colspan="2">RECIBE</td>
    </tr>
    <tr>
        <td class="blanco_left_landscape" rowspan="2">HORA:</td>
        <td class="blanco_left_landscape">0</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">1</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">2</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">3</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">4</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">5</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">6</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">7</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">8</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">9</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">10</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">0</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">1</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">2</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">3</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">4</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">5</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">6</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_landscape">NOMBRE</td>
        <td class="blanco_landscape"></td>
    </tr>
    <tr>
        <td class="verde_landscape" colspan="2">OTRO</td>
        <td class="blanco_left_landscape" colspan="34"></td>
        <td class="blanco_landscape">FIRMA</td>
        <td class="blanco_landscape"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado_landscape" colspan="83" style="border-bottom: solid 1px">D. MONITORIZACIÓN</td>
    </tr>
    <!-- Encabezado (Nivel 1): Agrupar cada hora en 13 columnas -->
    <tr>
        <td class="blanco_left_landscape" style="border: none;"></td>
        <td class="blanco_left_landscape" style="border: none;"></td>
        <td class="verde_landscape" rowspan="2">T°</td>
        <td class="verde_landscape" rowspan="2">PV</td>
        <td class="verde_landscape" rowspan="2">TA P. / R.</td>
        <?php
        $hours = 6;
        for ($h = 0; $h < $hours; $h++) {
            // 2 celdas vacías
            echo "<td class='blanco_left_remini_post' style='border: none'></td>";
            echo "<td class='blanco_left_remini_post' style='border: none'></td>";
            // 2 celdas fusionadas con label "15"
            echo "<td class='blanco_left_remini_post' colspan='2' style='border: none; text-align: center;'>15</td>";
            // 1 celda vacía
            echo "<td class='blanco_left_remini_post' style='border: none'></td>";
            // 2 celdas fusionadas con label "30"
            echo "<td class='blanco_left_remini_post' colspan='2' style='border: none; text-align: center;'>30</td>";
            // 1 celda vacía
            echo "<td class='blanco_left_remini_post' style='border: none'></td>";
            // 2 celdas fusionadas con label "45"
            echo "<td class='blanco_left_remini_post' colspan='2' style='border: none; text-align: center;'>45</td>";
            // 1 celda vacía
            echo "<td class='blanco_left_remini_post' style='border: none'></td>";
            // 2 celdas fusionadas con la flecha hacia arriba (representa 60)
            echo "<td class='blanco_left_remini_post' colspan='2' style='border: none; text-align: center;'>&#8593;</td>";
        }
        ?>
    </tr>
    <!-- Encabezado (Nivel 2): 78 celdas vacías para alinear con el cuerpo -->
    <tr>
        <td class="blanco_left_landscape" style="border: none;"></td>
        <td class="blanco_left_landscape" style="border: none;"></td>
        <?php
        $totalCols = $hours * 13; // 78 columnas
        for ($i = 0; $i < $totalCols; $i++):
            ?>
            <td class="blanco_left_remini_post" style="border: none;"></td>
        <?php endfor; ?>
    </tr>
    <!-- Cuerpo de la tabla -->
    <?php
    // Valores de FC, Temperatura y PV según el diseño.
    $titulos = ["TAS", "TAM", "TAD", "FRECUENCIA CARDÍACA", "FRECUENCIA CARDÍACA", "TEMPERATURA", "PVC", "SATURACIÓN O2"];
    $iconos = [">", "<", "X", "●", "△", "+", ""];
    $fc_values = [220, 200, 180, 160, 140, 120, 100, 80, 60, 40, 20, 0]; // 12 valores de FC
    $temp_values = [42, 41, 40, 39, 38, 37, 36, 35];                      // 8 valores de Temperatura
    $pv = [17, 15, 13, 11, 9, 7, 5, 3, 1];                                  // 9 valores para PV
    ?>

    <?php foreach ($fc_values as $index => $value): ?>
        <!-- Se generan 2 filas para cada valor de FC -->
        <tr>
            <td rowspan="2" class="blanco_left_landscape" style="border: none">
                <?php echo ($index >= 3 && $index < 1 + count($titulos)) ? $titulos[$index - 1] : ""; ?>
            </td>
            <td rowspan="2" class="blanco_left_landscape" style="border: none">
                <?php echo ($index >= 3 && $index < 1 + count($iconos)) ? $iconos[$index - 1] : ""; ?>
            </td>
            <!-- Celda de Temperatura: se muestra desde el índice 3 hasta 3+count($temp_values)-1 -->
            <td rowspan="2" class="cyan_left" style="border: none">
                <?php echo ($index >= 3 && $index < 3 + count($temp_values)) ? $temp_values[$index - 3] : ""; ?>
            </td>
            <!-- Celda para PV: se muestra desde el índice 2 hasta 2+count($pv)-1 -->
            <td rowspan="2" class="cyan_left" style="border: none">
                <?php echo ($index >= 2 && $index < 2 + count($pv)) ? $pv[$index - 2] : ""; ?>
            </td>
            <!-- Celda de FC -->
            <td rowspan="2" class="cyan_left" style="border: none"><?php echo $value; ?></td>
            <!-- Generar 78 celdas vacías para la primera fila -->
            <?php for ($i = 0; $i < $totalCols; $i++): ?>
                <td class="blanco_left_remini_post"></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <!-- Segunda fila: 78 celdas vacías -->
            <?php for ($i = 0; $i < $totalCols; $i++): ?>
                <td class="blanco_left_remini_post"></td>
            <?php endfor; ?>

        </tr>
    <?php endforeach; ?>
    <tr>
        <td class="verde_landscape" colspan="5">ALDRETE MODIFICADO</td>
        <td class="blanco_left_landscape" colspan="12"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="14"></td>

    </tr>
    <tr>
        <td class="verde_landscape" colspan="5">BROMAGE</td>
        <td class="blanco_left_landscape" colspan="12"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="14"></td>
    <tr>
        <td class="verde_landscape" colspan="5">RAMSAY</td>
        <td class="blanco_left_landscape" colspan="12"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="14"></td>
    </tr>
    <tr>
        <td class="verde_landscape" colspan="5">EVA</td>
        <td class="blanco_left_landscape" colspan="12"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="14"></td>
    <tr>
        <td class="verde_landscape" colspan="5">STEWARD</td>
        <td class="blanco_left_landscape" colspan="12"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="14"></td>
    </tr>
    <tr>
        <td class="verde_landscape" colspan="5">OTRO</td>
        <td class="blanco_left_landscape" colspan="12"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="13"></td>
        <td class="blanco_left_landscape" colspan="14"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado_landscape" colspan="2">E. INGRESOS</td>
        <td class="morado_landscape" colspan="2">F. ELIMINACIÓN</td>
    </tr>
    <tr>
        <td class="blanco_left_landscape" width="15%">ORAL</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape" width="15%">DIURESIS O SONDA VESICAL</td>
        <td class="blanco_left_landscape"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape">INFUSIONES</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">VÓMITO O SONDA NASOGÁSTRICO</td>
        <td class="blanco_left_landscape"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape">SANGRE Y DERIVADOS</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">TORÁCICO</td>
        <td class="blanco_left_landscape"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape">OTROS</td>
        <td class="blanco_left_landscape"></td>
        <td class="blanco_left_landscape">DRENAJES</td>
        <td class="blanco_left_landscape"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape" rowspan="2"><b>TOTAL</b></td>
        <td class="blanco_left_landscape" rowspan="2"></td>
        <td class="blanco_left_landscape">OTRAS</td>
        <td class="blanco_left_landscape"></td>
    </tr>
    <tr>
        <td class="blanco_left_landscape"><b>TOTAL</b></td>
        <td class="blanco_left_landscape"></td>
    </tr>
</table>
<table style='border: none'>
    <TR>
        <TD colspan='6' HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                               COLOR='#000000'>SNS-MSP / HCU-form.019/2021</FONT></B>
        </TD>
        <TD colspan='3' ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR='#000000'>CUIDADOS POSTANESTÉSICO (1)</FONT></B>
        </TD>
    </TR>
    ]
</TABLE>

