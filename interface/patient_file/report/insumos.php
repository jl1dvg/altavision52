<?php
require_once("common_vars.php");
include("common_header.php");
?>
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
