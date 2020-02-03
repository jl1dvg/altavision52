<!DOCTYPE html>
<?php
require_once("../../globals.php");
require_once("$srcdir/patient.inc");

$pid = $_GET['patientid'];
$pat_data = getPatientData($pid, "pubpid,fname,mname,lname, lname2, pricelevel, providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

$FONTSIZE = 9;
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    // Would use max-height here but html2pdf does not support it.
    // TODO - now use mPDF, so should test if still need this fix
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round($FONTSIZE * 6.50)) . "pt' />";
} else {
    $logo = "<!-- '$ma_logo_path' does not exist. -->";
}
?>
<html>
<HEAD>
    <TITLE></TITLE>
    <STYLE>
        BODY, DIV, TABLE, THEAD, TBODY, TFOOT, TR , TH, TD, P {
            font-size: 9.00;
        }

        div.Observaciones{
            border: 1;
        }
        td.casilla{
            border: 1;
            width: 10%;
        }

    </STYLE>

</HEAD>
<BODY>
<P ALIGN=CENTER STYLE="margin-bottom: 0in; font-style: normal; text-decoration: none">
    <FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 10pt"><B><SPAN STYLE="text-decoration: none">ACTA
DE ENTREGA RECEPCION DE SERVICIOS</SPAN></B></FONT></font>
<P ALIGN=CENTER STYLE="margin-bottom: 0in; font-style: normal; text-decoration: none">
    <?php
    echo $logo;
    ?>
    <BR CLEAR=LEFT><BR>
</P>
<DL>
    <DD>
        <TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=1 CELLSPACING=0 FRAME=VOID RULES=GROUPS>
            <TBODY>
            <TR VALIGN=TOP>
                <TD width=50%>PRESTADOR</TD>
                <TD >ALTAVISION</TD>
            </TR>
            <TR VALIGN=TOP>
                <TD>PERSONA DE CONTACTO</TD>
                <TD>LUCRECIA SAA</TD>
            </TR>
            <TR VALIGN=TOP>
                <TD >TELEFONO: 2286080</TD>
                <TD >E-MAIL: <A HREF="mailto:visilas@hotmail.com">visilas@hotmail.com</A></TD>
            </TR>
            <TR VALIGN=TOP>
                <TD >MES Y A&Ntilde;O DE PRESTACION</TD>
                <TD >CODIGO CIE 10</TD>
            </TR>
            <TR>
                <TD COLSPAN=2 VALIGN=TOP >NUMERO DE HISTORIA CLINICA: <FONT COLOR="#000000"><SPAN STYLE="text-decoration: none"><SPAN STYLE="font-style: normal"><SPAN STYLE="font-weight: normal"><SPAN STYLE="text-decoration: none"><?php echo text($pat_data["pubpid"]) ?></SPAN></SPAN></SPAN></SPAN></FONT></TD>
            </TR>
            <TR VALIGN=TOP>
                <TD >SERVICIO ENTREGADO</TD>
                <TD >AMBULATORIO</TD>
            </TR>
            </TBODY>
        </TABLE>

        <div>
            <br>
            <div class="Observaciones" height=30>
                <B>OBSERVACIONES:</B>
            </div>
            <P ALIGN=CENTER><font size=2 ><B>ACUSE ENTREGA DEL SERVICIO</B>
            <p>
                <LI>
                    Como prestador de la RPIS, conozco el cumplimiento obligatorio del
                    TPSNS y sus procedimientos que est&aacute;n regulados en la
                    normativa legal vigente.
            <LI>
                Adem&aacute;s tengo conocimiento el ac&aacute;pite que refiere a la
                Coordinaci&oacute;n de pagos y tarifas que indica textualmente:
            </p>
            <div class="Observaciones">
                &quot;En	caso de procedimientos observados que no fueren justificados y
                produzcan d&eacute;bitos definitivos, la unidad de salud no
                podr&aacute; requerir por
                ning&uacute;n motivo el pago al paciente o familiares de los
                valores objetados&quot;. Por lo que me comprometo a entregar la
                documentaci&oacute;n seg&uacute;n la norma.
            </div>
            <br><br><br>
            <P STYLE="margin-bottom: 0in; font-weight: normal">LUCRECIA SAA ESTEVES<br>C.I. 0912481595
            </P>
            <P ALIGN=CENTER><font size=2><B>ACUSE ENTREGA DEL SERVICIO</B>
            <P STYLE="font-weight: normal">
                Guayaquil, a los &hellip;&hellip;&hellip;d&iacute;as del mes de
                &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;...&hellip;.
                Del a&ntilde;o &hellip;&hellip;&hellip;&hellip;..<br>
                Yo <?php echo text($pat_data["fname"] . " " . $pat_data["lname"] . " " . $pat_data["lname2"] . " ") ?>con C.I. <?php echo text($pat_data["pubpid"] . " ") ?>
                certifico haber recibido conforme los servicios de forma gratuita, correspondiente a:
            </P>
            <table width="40%" align="CENTER">
                <tr>
                    <td>Exámenes Oftalmológicos diagnósticos</td>
                    <td class="casilla"></td>
                </tr>
                <tr>
                    <td>Consultas Oftalmológicas</td>
                    <td class="casilla"></td>
                </tr>
                <tr>
                    <td>Tratamientos Clínicos</td>
                    <td class="casilla"></td>
                </tr>
                <tr>
                    <td>Tratamientos Quirúrgicos</td>
                    <td class="casilla"></td>
                </tr>
                <tr>
                    <td>Insumos y medicamentos</td>
                    <td class="casilla"></td>
                </tr>
            </table>
            <P STYLE="font-weight: normal">
                En Alta Visión desde &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip; del 2019, hasta el
                &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;del 2019<br>
            </P>
            <br><br><br>
            <P STYLE="font-weight: normal">
                Firma del Beneficiario
            <P STYLE="font-weight: normal"><B>Observaciones: </B>
                Yo&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.. En mi calidad de &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;
                y/o representante o acompa&ntilde;ante, del paciente &hellip;&hellip;&hellip;&hellip;&hellip;..&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.
                Certifico que el mencionado usuario/paciente recibi&oacute; el
                servicio registrado en la presente acta
            </P>
            <br><br><br>
            <P STYLE="font-weight: normal; text-decoration: none">
                Firma del Representante o Acompa&ntilde;ante
            </p>
            <P STYLE="font-weight: normal">
                EN MI CALIDAD DE PRESTADOR DE SERVICIOS, CERTIFICO QUE LAS FIRMAS CONSTANTES EN EL PRESENTE DOCUMENTO, CORRESPONDEN A LA FIRMA
                DEL PACIENTE O SU REPRESENTANTE DE SER EL CASO, MISMA QUE FUE RECEPTADA EN ESTA INSTITUCION, POR LO TANTO ME RESPONSABILIZO
                POR EL CONTENIDO DE DICHO CERTIFICADO, ASUMIENTO TODA LA RESPONSABILIDAD TANTO ADMINISTRATIVA, CIVIL O PENAL POR LA
                VERACIDAD DE LA INFORMACI&Oacute;N ENTREGADA.
            </p>
            <br><br><br>
            <P align="center">LUCRECIA SAA ESTEVES<br>C.I. 0912481595
        </div>
</DL>
</BODY>
</HTML>