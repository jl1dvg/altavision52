<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once(dirname(__FILE__) . "/../../../library/lists.inc");

use OpenEMR\Services\FacilityService;
use Mpdf\Mpdf;

$form_name = "eye_mag";
$form_folder = "eye_mag";

$facilityService = new FacilityService();

// Incluye funciones específicas del formulario
require_once(__DIR__ . "/../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");

if ($_REQUEST['CHOICE'] ?? '') {
    $choice = $_REQUEST['choice'];
}

if ($_REQUEST['ptid'] ?? '') {
    $pid = $_REQUEST['ptid'];
}

if ($_REQUEST['encid'] ?? '') {
    $encounter = $_REQUEST['encid'];
}

if ($_REQUEST['formid'] ?? '') {
    $form_id = $_REQUEST['formid'];
}

if ($_REQUEST['formname'] ?? '') {
    $form_name = $_REQUEST['formname'];
}

if (!($id ?? '')) {
    $id = $form_id ?? '';
}

//Datos del PACIENTE
$titleres = getPatientData($pid, "pubpid,fname,mname,lname, lname2, pricelevel, providerID,DATE_FORMAT(DOB,'%Y/%m/%d') as DOB_TS");

// Consulta para obtener los datos del encounter
$query = "SELECT *, form_encounter.date as encounter_date
          FROM forms
          JOIN form_encounter ON forms.encounter = form_encounter.encounter
          JOIN form_eye_base ON forms.form_id = form_eye_base.id
          -- Añade los otros JOINs aquí
          WHERE forms.deleted != '1'
            AND forms.formdir = ?
            AND forms.encounter = ?
            AND forms.pid = ?";

$encounter_data = sqlQuery($query, array($form_folder, $encounter, $pid));
@extract($encounter_data); // Evita usar extract si es posible, define cada variable explícitamente

// Fecha del formulario
$queryform = "SELECT * FROM forms WHERE pid=? AND encounter=? AND formdir = ? AND deleted = 0";
$fechaINGRESO = sqlQuery($queryform, array($pid, $encounter, $form_folder));

$providerID = getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);

// Procesar fecha en formato
$dated = new DateTime($encounter_date);
$dateddia = date("d", strtotime($fechaINGRESO['date']));
$datedmes = date("F", strtotime($fechaINGRESO['date']));
$datedano = date("Y", strtotime($fechaINGRESO['date']));

// Mes en español
$meses_ES = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$meses_EN = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$nombreMes = str_replace($meses_EN, $meses_ES, $datedmes);

// Obtener la instalación (facility)
$facility = $_SESSION['pc_facility'] ? $facilityService->getById($_SESSION['pc_facility']) : $facilityService->getPrimaryBillingLocation();

// Generar el logo
$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    $logo = "<img src='$web_root/$ma_logo_path' align='left' style='width:100px;margin:0px 10px;'>";
} else {
    $logo = "<!-- '$ma_logo_path' does not exist. -->";
}

// Configuración de PDF
$config_mpdf = [
    'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
    'mode' => $GLOBALS['pdf_language'],
    'format' => 'A4-P',
    'margin_left' => $GLOBALS['pdf_left_margin'],
    'margin_right' => $GLOBALS['pdf_right_margin'],
    'margin_top' => '15',
    'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
    'orientation' => $GLOBALS['pdf_layout']
];
$pdf = new Mpdf($config_mpdf);
$pdf->SetDisplayMode('real');
if ($_SESSION['language_direction'] == 'rtl') {
    $pdf->SetDirectionality('rtl');
}

// Generar el contenido HTML para el PDF
ob_start();
?>
<HTML>
<HEAD>
    <STYLE TYPE="text/css">
        span {
            text-align: justify;
            font-family: Arial;
            font-size: 12px;
        }

        p.texto {
            text-align: justify;
            font-family: Arial;
            font-size: 12px;
        }

        p.titulo {
            text-align: center;
            font-family: Montserrat;
            font-style: oblique;
            font-weight: bold;
            font-size: 20px;
        }
    </STYLE>
</HEAD>
<BODY>
<page_header>
    <table>
        <tr>
            <td>
                <SPAN CLASS="sd-abs-pos" STYLE="position: absolute; top: -0.67in; left: 1.81in; width: 249px">
                    <?php
                    echo $logo;
                    ?>
                </SPAN>
            </td>
            <td>
                <h2><?php echo $facility['name'] ?></h2>
                <p class="texto">
                    <?php echo $facility['street'] ?><br>
                    <?php echo $facility['city'] ?>
                    , <?php echo $facility['country_code'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
                    <b>Telfs: </b><?php echo $facility['phone'] ?><br>
                    <b>E-mail: </b><?php echo $facility['email'] ?>
                </p>

            </td>
        </tr>
    </table>
    <hr>
</page_header>

<P class="titulo">CERTIFICADO DE ASISTENCIA</P>
<p class="texto">A quien corresponda,</p>
<P class="texto">El que suscribe, Dr(a). <b><?php echo htmlspecialchars($providerNAME); ?></b>, certifica que el(la)
    señor(a)
    <b><?php echo htmlspecialchars($titleres['lname'] . " " . $titleres['lname2'] . " " . $titleres['fname'] . " " . $titleres['mname']); ?></b>,
    portador(a) de la cédula de identidad <b><?php echo htmlspecialchars($titleres['pubpid']); ?></b>, fue atendido(a)
    en
    consulta médica en
    <b><?php echo htmlspecialchars($facility['name']); ?></b> el día
    <b><?php echo $dateddia . " de " . $nombreMes . " del " . $datedano; ?></b>.
</P>

<P class="texto"><B>IMPRESIÓN DIAGNÓSTICA:</B></P>
<p class="texto">
    <?php
    // Consulta y muestra la impresión diagnóstica
    $query = "SELECT * FROM form_" . $form_folder . "_impplan WHERE form_id=? AND pid=? ORDER BY IMPPLAN_order ASC";
    $result = sqlStatement($query, array($form_id, $pid));

    $IMPPLAN_items = [];
    while ($ip_list = sqlFetchArray($result)) {
        $IMPPLAN_items[] = [
            'code' => $ip_list['code'],
            'codetext' => $ip_list['codetext'],
            'codedesc' => $ip_list['codedesc'],
            'plan' => nl2br(htmlspecialchars($ip_list['plan'])) // Saltos de línea con nl2br
        ];
    }

    foreach ($IMPPLAN_items as $item) {
        echo (!empty($item['code']) ? htmlspecialchars($item['code']) . " " : "") . htmlspecialchars($item['codedesc']) . ".<br>";
    }

    // Consulta para las recomendaciones (PLAN)
    $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));

    if (sqlNumRows($PLAN_results) > 0) {
        echo "<b>RECOMENDACIÓN: </b>";
        while ($plan_row = sqlFetchArray($PLAN_results)) {
            echo htmlspecialchars($plan_row['ORDER_DETAILS']) . ", ";
        }
    }
    ?>
</p>

<P class="texto">Este certificado se expide a petición del interesado para los fines que estime convenientes.</P>
<P class="texto">Atentamente,</P>
<P class="texto"><br><br></P>
<br>
<P><B><?php echo htmlspecialchars($providerNAME); ?><br><?php echo htmlspecialchars($facility['name']); ?><br>Guayaquil
        &ndash; Ecuador</B></P>
</BODY>
</HTML>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('informe_medico_' . htmlspecialchars($titleres['lname']) . '_' . htmlspecialchars($titleres['fname']) . '.pdf', 'I');
?>
