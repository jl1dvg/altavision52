<?php
require_once("common_vars.php");
include("common_header.php");
?>
<!--[ADMINISTRACION DE MEDICAMENTOS (1)]-->
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
            colspan="9"><?php echo getProviderNameConcat(getFieldValue($form_id, "Prot_anestesiologo")); ?></td>
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
        <TD colspan="6" class="blanco_left" style="border: none"><B>SNS-MSP / HCU-form.022/2021</B>
        </TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">ADMINISTRACION DE MEDICAMENTOS
                    (1)</FONT></B>
        </TD>
    </TR>
</table>
