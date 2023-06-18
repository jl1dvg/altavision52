<!DOCTYPE html>
<html>
<head>
    <title>ACTA DE ENTREGA RECEPCION DE SERVICIOS</title>
    <style>
        body, div, table, thead, tbody, tfoot, tr, th, td, p {
            font-size: 9pt;
        }

        div.Observaciones {
            border: 1px solid;
        }

        td.casilla {
            border: 1px solid;
            width: 10%;
        }
    </style>
</head>
<body>
<?php
require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/options.inc.php");


// Variables para los parámetros de la consulta
$pid = $_GET['patientid'];
$encounter = $_GET['visitid'];
$form_id = $_GET['formid'];

$pat_data = getPatientData($pid, "pubpid, fname, mname, lname, lname2, pricelevel, providerID, DATE_FORMAT(DOB, '%m/%d/%Y') as DOB_TS");
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";

if (is_file("$webserver_root/$ma_logo_path")) {
    // Path to the logo file exists
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round(9 * 6.50)) . "pt' />";
} else {
    // Path to the logo file does not exist
    $logo = "<!-- '$ma_logo_path' does not exist. -->";
}


// Consulta preparada con extracción de día, mes y año
$query = "SELECT *, DAY(date) AS day, MONTHNAME(date) AS month, YEAR(date) AS year, DATE_FORMAT(date, '%Y') AS year FROM forms WHERE pid = ? AND encounter = ? AND form_id = ?";
$stmt = sqlQuery($query, array($pid, $encounter, $form_id));
// Obtener los resultados
$day = $stmt['day'];
$month = $stmt['month'];
$year = $stmt['year'];
// Convertir el nombre del mes a español
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$nombreMes = str_replace($meses_EN, $meses_ES, $month);

function getReferral($pid, $field_id)
{
    $referralQuery = sqlStatement("SELECT lbt_data.field_id, lbt_data.field_value
                                   FROM transactions
                                   JOIN lbt_data ON transactions.id = lbt_data.form_id
                                   WHERE pid = ? AND field_id = ?
                                   ORDER BY transactions.date DESC
                                   LIMIT 1", array($pid, $field_id));

    if ($referralQuery) {
        $row = sqlFetchArray($referralQuery);
        $fieldValue = $row['field_value'];
        return $fieldValue;
    }

    return "No se encontraron resultados";
}

// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
    "pid = ? AND enddate IS NULL " .
    "ORDER BY type, begdate", array($pid));

$selectedIssues = array(); // Array para almacenar los valores seleccionados

while ($irow = sqlFetchArray($ires)) {
    $list_id = $irow['id'];
    $tcode = $irow['type'];
    if ($ISSUE_TYPES[$tcode]) {
        $tcode = $ISSUE_TYPES[$tcode][2];
    }


    $perow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
        "pid = ? AND encounter = ? AND list_id = ?", array($pid, $encounter, $list_id));
    if ($perow['count']) {
        $selectedIssues[] = text($tcode) . ": " . text($irow['begdate']) . " " . text(substr($irow['title'], 0, 40));
    }
}


?>
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
                    echo $nombreMes . " - " . $year;
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
                <td><?php echo htmlentities($pat_data["pubpid"]); ?></td>
            </tr>
            <tr valign="top">
                <td>TIPO DE SERVICIO ENTREGADO</td>
                <td><?php
                    $pc_catid = fetchCategoryIdByEncounter($encounter);
                    echo ucwords(fetchNameByEncounter($pc_catid)) . " Ambulatorio";
                    ?></td>
            </tr>
            </tbody>
        </table>
    </dt>
</dl>

<p align="center" style="margin-bottom: 0in; font-style: normal; text-decoration: none; font-weight: bold">
    ACUSE ENTREGA DE SERVICIO
</p>
<div class="Observaciones">
    <b>OBSERVACIONES:</b><br><br><br></div>
<p style="font-size: 7pt; font-family: Arial; text-align: justify">Como prestador de la RPIS, conozco el cumplimiento
    obligatorio del TPSNS y sus procedimientos que están regulados en
    el presente Reglamento de relacionamiento.<br>
    Además, tengo conocimiento el acápite que refiere a la coordinación de pagos y tarifas que indica textualmente:</p>
<div class="Observaciones">
        <span style="text-decoration: none; font-style: normal; font-weight: normal;">
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
    <p align="center" style="font-weight: bold; text-decoration: underline">
        ACUSE ENTREGA DE SERVICIO
    </p>
    Ciudad de Guayaquil a los _______ días del mes de__________________
    del año______________
    <br>
    <br>
    <br>
    <br>
    _______________________________<br>
    <?php
    echo text($pat_data['fname']) . " " . text($pat_data['lname']) . " " . text($pat_data['lname2']);
    ?>
</div>
<br>
<div class="Observaciones">
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
<div>
    <b>Certificación de firmas</b><br><br>
    <span style="text-align: justify; font-family: Arial; font-size: 7pt">En mi calidad de prestador de servicios, certifico que la firmas constantes en el presente documento, corresponden a
    la firma del usuario/paciente o su representante, misma que fue receptar en esta casa de salud; por lo tanto, me
    responsabilizo por el contenido de dicho certificado, asumiendo toda la responsabilidad tanto administrativa, civil
        o penal por la veracidad de la información entregada.</span>
</div>
</body>
</html>
