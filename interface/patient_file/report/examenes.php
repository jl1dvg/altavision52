<!DOCTYPE HTML>
<?php
require_once "contra_template.php";
require_once("$srcdir/iess.inc.php");
$queryform = "select * from forms
                where
                pid=? and
                encounter=? and
                formdir = 'newpatient' and
                deleted = 0";

$fechaINGRESO = sqlQuery($queryform, array($pid, $form_encounter));
?>
<html>
<head>
    <style>
        table.formulario {
            width: 100%;
            border: 5px solid #808080;
            border-collapse: collapse;
            margin-bottom: 10px;
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
    </style>
</head>
<body>
<?php
//echo "<pre>";
//print_r($ar);
//echo $key;
//echo $val;
//echo "</pre>";
?>
<TABLE class="formulario">
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
        <td colspan="3" class="blanco"><?php echo $titleres['sex']; ?></td>
        <td colspan="6" class="blanco"><?php echo date('d/m/Y', strtotime($titleres['DOB_TS'])); ?></td>
        <td colspan="3" class="blanco"><?php echo text(getPatientAge($titleres['DOB_TS'])); ?></td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
    </tr>
</TABLE>
<table class="formulario">
    <colgroup>
        <col class="xl76" span="71">
    </colgroup>
    <tr>
        <td colspan="71" class="morado">B. SERVICIO Y PRIORIDAD DE ATENCIÓN</td>
    </tr>
    <tr>
        <td colspan="25" class="verde" style="width: 30%">SERVICIO</td>
        <td colspan="17" class="verde" style="width: 25%">ESPECIALIDAD</td>
        <td colspan="6" class="verde" style="width: 10%">CAMA</td>
        <td colspan="6" class="verde" style="width: 10%">SALA</td>
        <td colspan="17" class="verde" style="border-right: none">PRIORIDAD</td>
    </tr>
    <tr>
        <td colspan="5" class="verde">EMERGENCIA</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="7" class="verde">CONSULTA EXTERNA</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="7" class="verde">HOSPITALIZACIÓN</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="17" class="blanco">OFTALMOLOGIA</td>
        <td colspan="6" class="blanco">&nbsp;</td>
        <td colspan="6" class="blanco">&nbsp;</td>
        <td colspan="4" class="verde">URGENTE</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="3" class="verde">RUTINA</td>
        <td colspan="2" class="blanco">X</td>
        <td colspan="4" class="verde">CONTROL</td>
        <td colspan="2" class="blanco" style="border-right: none"></td>
    </tr>
</table>
<table class="formulario">
    <tr>
        <td colspan="71" class="morado">C. ESTUDIO DE IMAGENOLOGÍA SOLICITADO</td>
    </tr>
    <tr>
        <td colspan="6" class="verde">RX<br>CONVENCIONAL</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="4" class="verde">RX<br>PORTÁTIL</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="6" class="verde">TOMOGRAFÍA</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="5" class="verde">RESONANCIA</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="5" class="verde">ECOGRAFÍA</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="6" class="verde">MAMOGRAFÍA</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="7" class="verde">PROCEDIMIENTO</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="3" class="verde">OTRO</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="5" class="verde">SEDACIÓN</td>
        <td colspan="2" class="verde">SI</td>
        <td colspan="2" class="blanco">&nbsp;</td>
        <td colspan="2" class="verde">NO</td>
        <td colspan="2" class="blanco" style="border-right: none">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="8" class="verde">DESCRIPCIÓN</td>
        <td colspan="63" class="blanco" style="border-right: none; text-align: left">
            Informe
            de <?php
            preg_match('/^(.*)_(\d+)$/', $key, $res);
            $formdir = $res[1];
            $form_id = $res[2];
            echo ImageStudyName($pid, $form_encounter, $form_id, $formdir);
            ?>
    </tr>
</table>
<table cellspacing="0" style="border: 5px solid #808080; width: 100%; margin-bottom: 10px">
    <tr>
        <td class="morado">D. HALLAZGOS POR IMAGENOLOGÍA</td>
    </tr>
    <tr>
        <td class="blanco" style="border-right: none; text-align: left">
            <?php
            echo "<b>" . ImageStudyName($pid, $form_encounter, $form_id, $formdir) . ": </b>";
            echo wordwrap(ExamenesImagenes($pid, $form_encounter, $form_id, $formdir), 120, "</td></tr><tr><td>");
            ?>
        </td>
    </tr>
</table>
<table class="formulario">
    <tr>
        <td class="morado">E. CONCLUSIONES Y SUGERENCIAS</td>
    </tr>
    <tr>
        <td class="blanco" style="border-right: none; text-align: left">
            <?php echo wordwrap("En resumen, es fundamental seguir las indicaciones del médico especialista. Si surgieran dudas o
                preguntas, siempre debemos comunicarnos con el médico para obtener
                claridad. Trabajar en colaboración con el médico nos permitirá obtener los mejores resultados y mantener
                una buena salud a largo plazo.", 165, "</TD></TR><TR><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">"); ?>
        </td>
    </tr>
</table>
<table class="formulario">
    <tr>
        <td colspan="71" class="morado">F. DATOS DEL PROFESIONAL RESPONSABLE</td>
    </tr>
    <tr class="xl78">
        <td colspan="8" class="verde">FECHA<br>
            <font class="font5">(aaaa-mm-dd)</font>
        </td>
        <td colspan="7" class="verde">HORA<br>
            <font class="font5">(hh:mm)</font>
        </td>
        <td colspan="21" class="verde">PRIMER NOMBRE</td>
        <td colspan="19" class="verde">PRIMER APELLIDO</td>
        <td colspan="16" class="verde">SEGUNDO APELLIDO</td>
    </tr>
    <tr>
        <td colspan="8" class="blanco"><?php echo date("Y/m/d", strtotime($fechaINGRESO['date'])); ?></td>
        <td colspan="7" class="blanco"></td>
        <td colspan="21" class="blanco">Mario</td>
        <td colspan="19" class="blanco">Pólit</td>
        <td colspan="16" class="blanco">Macias</td>
    </tr>
    <tr>
        <td colspan="15" class="verde">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
        <td colspan="26" class="verde">FIRMA</td>
        <td colspan="30" class="verde">SELLO</td>
    </tr>
    <tr>
        <td colspan="15" class="blanco" style="height: 40px"><?php echo getProviderRegistro(12);
            ?></td>
        <td colspan="26" class="blanco">&nbsp;</td>
        <td colspan="30" class="blanco">&nbsp;</td>
    </tr>
</table>
<table style="border: none">
    <tr>
        <td colspan="9" style="text-align: justify; font-size: 6pt">
            La aproximación diagnóstica emitida en el presente informe, constituye tan solo una prueba
            complementaria al diagnóstico clínico definitivo, motivo por el cual se recomienda correlacionar con
            antecedentes clínicos/quirúrgicos, datos clínicos, exámenes de laboratorio complementarios, así como
            seguimiento imagenológico del paciente.
        </td>
    </tr>
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=M><B><FONT SIZE=1
                                                               COLOR="#000000">SNS-MSP/HCU-form.012B/2021</FONT></B>
        </TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">IMAGENOLOGÍA - INFORME</FONT></B>
        </TD>
    </TR>
    ]
</TABLE>
</body>
