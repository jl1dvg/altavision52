<?php
require_once("common_vars.php");
include("common_header.php");
?>
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
            <?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?>
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
