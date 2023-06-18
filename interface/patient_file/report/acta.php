<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
$facility = null;
if ($_SESSION['pc_facility']) {
    $facility = $facilityService->getById($_SESSION['pc_facility']);
} else {
    $facility = $facilityService->getPrimaryBillingLocation();
}

$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
$logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round(9 * 7.50)) . "pt' />";

preg_match('/^(.*)_(\d+)$/', $key, $res);
$form_id = $res[2];

$encounterDate = fetchDateByEncounter($form_encounter);
$mesesEnIngles = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$mesesEnEspanol = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
$fechaCompleta = date($mesesEnEspanol[date('n', strtotime($encounterDate)) - 1]) . ' ' . date('Y', strtotime($encounterDate));

?>
<html>
<body>
<p align="center">
    <?php echo $logo; ?>
    <br>
    <b>ALTAVISION</b>
    <br>
</p>
<div class="Observaciones" style="text-align: center; font-weight: bold">
    ACTA DE ENTREGA RECEPCION DE SERVICIOS
</div>
<dl>
    <dt>
        <table width="100%" border="1" bordercolor="#000000" cellpadding="1" cellspacing="0" frame="void"
               rules="groups">
            <tbody>
            <tr valign="top">
                <td width="50%">PRESTADOR</td>
                <td>ALTAVISION</td>
            </tr>
            <tr valign="top">
                <td>PERSONA DE CONTACTO</td>
                <td>LUCRECIA SAA</td>
            </tr>
            <tr valign="top">
                <td>TELEFONO: 2286080</td>
                <td>E-MAIL: <a href="mailto:visilas@hotmail.com">visilas@hotmail.com</a></td>
            </tr>
            <tr valign="top">
                <td>MES Y A&Ntilde;O DE PRESTACION: <?php
                    echo $fechaCompleta;
                    ?></td>
                <td>CODIGO CIE 10: <?php
                    $ires = sqlStatement("SELECT id, type, title, diagnosis FROM lists WHERE " .
                        "pid = ? AND enddate IS NULL " .
                        "ORDER BY type, begdate", array($pid));

                    $selectedIssues = array(); // Array para almacenar los valores seleccionados

                    while ($irow = sqlFetchArray($ires)) {
                        $list_id = $irow['id'];
                        $tcode = $irow['type'];
                        if ($tcode == "medical_problem") {
                            $selectedIssues[] = text(substr($irow['diagnosis'], 6, 40));
                        }
                    }

                    // Imprimir los valores seleccionados separados por comas
                    echo implode(", ", $selectedIssues);
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" valign="top">CODIGO DE VALIDACION / RPC: <?php
                    echo getReferral($pid, "refer_id");
                    ?>
                </td>
            </tr>
            <tr valign="top">
                <td>NUMERO DE HISTORIA CLINICA:</td>
                <td><?php echo htmlentities($titleres["pubpid"]); ?></td>
            </tr>
            <tr valign="top">
                <td>TIPO DE SERVICIO ENTREGADO</td>
                <td><?php
                    $pc_catid = fetchCategoryIdByEncounter($form_encounter);
                    echo ucwords(fetchNameByEncounter($pc_catid)) . " Ambulatorio";
                    ?></td>
            </tr>
            </tbody>
        </table>
    </dt>
</dl>

<p style="margin-bottom: 0in; font-style: normal; text-decoration: none; font-weight: bold; text-decoration: underline; text-align: center">
    ACUSE ENTREGA DE SERVICIO
</p>
<div class="Observaciones" style="text-align: left">
    <b>OBSERVACIONES:</b><br><br><br></div>
<p style="font-size: 7pt; font-family: Arial; text-align: justify">Como prestador de la RPIS, conozco el cumplimiento
    obligatorio del TPSNS y sus procedimientos que están regulados en
    el presente Reglamento de relacionamiento.<br>
    Además, tengo conocimiento el acápite que refiere a la coordinación de pagos y tarifas que indica textualmente:</p>
<div class="Observaciones">
        <span style="text-decoration: none; font-style: normal; font-weight: normal; text-align: left">
        "En caso de objeción o débito, el prestador no podrá requerir el pago al usuario/paciente,
            familiares o acompañante. Cualquier cobro en este sentido será motivo de la sanción que la Ley prevea"</span>
</div>
<div>
    <br>
    <br>
    <br>
    <br>
    _______________________________<br>
    LUCRECIA SAA<br>
    CI: 0912481595
    <p style="text-align: left">
        <span style="font-weight: bold; text-decoration: underline; text-align: center">ACUSE ENTREGA DE SERVICIO</span><br><br>
        Ciudad de Guayaquil a los _______ días del mes de__________________
        del año______________
        <br>
        <br>
        <br>
        <br>
        _______________________________<br>
        <?php
        echo text($titleres['fname']) . " " . text($titleres['lname']) . " " . text($titleres['lname2']);
        ?>
    </p>

</div>
<br>
<div class="Observaciones" style="text-align: left">
    <b>Observaciones:</b> Yo _______________________ en mi calidad de ____________________ y/o representante o
    acompañante, del usuario/paciente ______________________________ certifico que el
    usuario/paciente recibió el servicio registrado en la presente acta.
    <br>
    <br>
    <br>
    _______________________________<br>
    Firma del Representante/Acompañante
    <b>(Utilizar este campo sólo cuando el usuario/paciente no pueda registrar su firma)</b>
</div>
<br><br><br><br>
<div style="text-align: left">
    <b>Certificación de firmas</b><br><br>
    <span style="text-align: justify; font-family: Arial; font-size: 7pt">En mi calidad de prestador de servicios, certifico que la firmas constantes en el presente documento, corresponden a
    la firma del usuario/paciente o su representante, misma que fue receptar en esta casa de salud; por lo tanto, me
    responsabilizo por el contenido de dicho certificado, asumiendo toda la responsabilidad tanto administrativa, civil
        o penal por la veracidad de la información entregada.</span>
</div>
</body>
</html>
