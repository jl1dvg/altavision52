<?php
require_once("common_vars.php");
include("common_header.php");
?>
<!--[005 A]-->
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
        <td class="verde" colspan="3">HORA<br><span
                style="font-size:6pt;font-family:Arial;font-weight:normal;">(hh:mm)</span>
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
        <td class="blanco_left" colspan="29">Paciente
            de <?php echo getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter)))); ?>
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
        <td class="blanco_left" colspan="29">
            T.A.: <?php echo $sistolica . '/' . $diastolica . ' F.C.: ' . $fc . ' SATOS: ' . $spo2 . '%'; ?>
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
            T.A.: <?php echo $sistolica . '/' . $diastolica . ' F.C.: ' . $fc . ' SATOS: ' . $spo2 . '%'; ?>
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
</TABLE>
