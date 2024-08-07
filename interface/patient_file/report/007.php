<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
// Cargar el autoloader de Composer
require '/var/www/html/altavision/vendor/autoload.php';

use Dotenv\Dotenv;

// Cambiar la ruta a la raíz del proyecto donde está el archivo .env
$dotenv = Dotenv::create('/var/www/html/altavision');
$dotenv->load();

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function improveText($text)
{
    $apiKey = getenv('OPENAI_API_KEY');

    if (!$apiKey) {
        die('API key is missing. Please set your OpenAI API key.');
    }

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente que corrige y mejora textos.'],
            ['role' => 'user', 'content' => "Corrige y mejora el siguiente texto con mejor gramática y ortografía:\n\n" . $text]
        ],
        'max_tokens' => 500
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true // Capturar errores HTTP
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);

    if ($response === FALSE) {
        $error = error_get_last();
        die('Error occurred: ' . $error['message']);
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['error'])) {
        die('API Error: ' . $responseData['error']['message']);
    }

    return $responseData['choices'][0]['message']['content'];
}

function generateEnfermedadProblemaActual($motivoConsulta, $examenFisico)
{
    $apiKey = getenv('OPENAI_API_KEY');

    if (!$apiKey) {
        die('API key is missing. Please set your OpenAI API key.');
    }

    // Definir el prompt
    $prompt = "
    Motivo de consulta: $motivoConsulta
    Examen físico oftalmológico: $examenFisico

    Basándote en el motivo de consulta y los hallazgos del examen físico, redacta el Problema Actual del paciente de manera breve, sencilla y usando un lenguaje cotidiano, como si el paciente mismo estuviera describiendo su problema, sin darme datos de la examinacion sino de la posible sintomatologia del paciente.
    ";

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente médico que ayuda a redactar informes clínicos detallados.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 150
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true // Capturar errores HTTP
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);

    if ($response === FALSE) {
        $error = error_get_last();
        die('Error occurred: ' . $error['message']);
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['error'])) {
        die('API Error: ' . $responseData['error']['message']);
    }

    return $responseData['choices'][0]['message']['content'];
}

function getMedicalProblems($pid)
{
    $query = "SELECT diagnosis FROM lists WHERE pid = ? AND type = ?";
    $result = sqlStatement($query, array($pid, 'medical_problem'));
    $diagnoses = [];

    while ($row = sqlFetchArray($result)) {
        $diagnoses[] = $row['diagnosis'];
    }

    return $diagnoses;
}

// Limpiar (descartar) cualquier salida previa
//ob_end_clean();

function renderPatientInfoTable($titleres, $encounter)
{
    $dobFormatted = date('d/m/Y', strtotime($titleres['DOB_TS']));
    $age = getPatientAgeFromDate($titleres['DOB_TS'], date("Y/m/d", strtotime(fetchDateByEncounter($encounter))));

    echo "
    <TABLE class='formulario'>
        <tr>
            <td colspan='71' class='morado'>A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</td>
        </tr>
        <tr>
            <td colspan='15' height='27' class='verde'>INSTITUCIÓN DEL SISTEMA</td>
            <td colspan='6' class='verde'>UNICÓDIGO</td>
            <td colspan='18' class='verde'>ESTABLECIMIENTO DE SALUD</td>
            <td colspan='18' class='verde'>NÚMERO DE HISTORIA CLÍNICA ÚNICA</td>
            <td colspan='14' class='verde' style='border-right: none'>NÚMERO DE ARCHIVO</td>
        </tr>
        <tr>
            <td colspan='15' height='27' class='blanco'>{$titleres['pricelevel']}</td>
            <td colspan='6' class='blanco'>&nbsp;</td>
            <td colspan='18' class='blanco'>ALTA VISION</td>
            <td colspan='18' class='blanco'>{$titleres['pubpid']}</td>
            <td colspan='14' class='blanco' style='border-right: none'>{$titleres['pubpid']}</td>
        </tr>
        <tr>
            <td colspan='15' rowspan='2' height='41' class='verde'>PRIMER APELLIDO</td>
            <td colspan='13' rowspan='2' class='verde'>SEGUNDO APELLIDO</td>
            <td colspan='13' rowspan='2' class='verde'>PRIMER NOMBRE</td>
            <td colspan='10' rowspan='2' class='verde'>SEGUNDO NOMBRE</td>
            <td colspan='3' rowspan='2' class='verde'>SEXO</td>
            <td colspan='6' rowspan='2' class='verde'>FECHA NACIMIENTO</td>
            <td colspan='3' rowspan='2' class='verde'>EDAD</td>
            <td colspan='8' class='verde' style='border-right: none; border-bottom: none'>CONDICIÓN EDAD (MARCAR)</td>
        </tr>
        <tr>
            <td colspan='2' height='17' class='verde'>H</td>
            <td colspan='2' class='verde'>D</td>
            <td colspan='2' class='verde'>M</td>
            <td colspan='2' class='verde' style='border-right: none'>A</td>
        </tr>
        <tr>
            <td colspan='15' height='27' class='blanco'>{$titleres['lname']}</td>
            <td colspan='13' class='blanco'>{$titleres['lname2']}</td>
            <td colspan='13' class='blanco'>{$titleres['fname']}</td>
            <td colspan='10' class='blanco'>{$titleres['mname']}</td>
            <td colspan='3' class='blanco'>" . substr($titleres['sex'], 0, 1) . "</td>
            <td colspan='6' class='blanco'>$dobFormatted</td>
            <td colspan='3' class='blanco'>$age</td>
            <td colspan='2' class='blanco'>&nbsp;</td>
            <td colspan='2' class='blanco'>&nbsp;</td>
            <td colspan='2' class='blanco'>&nbsp;</td>
            <td colspan='2' class='blanco' style='border-right: none'>&nbsp;</td>
        </tr>
    </TABLE>";
}

preg_match('/^(.*)_(\d+)$/', $key, $res);
$formdir = $res[1];
$form_id = $res[2];

$reason = getReason($form_encounter, $pid);

// Ejemplo de uso
$improvedReason = improveText($reason);
$providerID = getProviderIdOfEncounter($encounter);

//provider name explode
$fullName = getProviderNameConcat($providerID);

// Extraer los componentes del nombre
$nameComponents = explode(" ", $fullName);

// Obtener los componentes individuales
$mname = isset($nameComponents[0]) ? $nameComponents[0] : '';
$fname = isset($nameComponents[1]) ? $nameComponents[1] : '';
$lname = isset($nameComponents[2]) ? $nameComponents[2] : '';
$suffix = isset($nameComponents[3]) ? $nameComponents[3] : '';
$queryform = "select * from forms
                where
                pid=? and
                encounter=? and
                formdir = 'newpatient' and
                deleted = 0";

function getFormDate($pid, $form_encounter)
{
    // Primera consulta para formdir = 'newpatient'
    $queryform = "SELECT * FROM forms
                  WHERE pid = ? AND encounter = ? AND formdir = 'newpatient' AND deleted = 0";
    $fechaINGRESO = sqlQuery($queryform, array($pid, $form_encounter));

    // Si se encuentra la fecha en 'newpatient'
    if ($fechaINGRESO) {
        $time = strtotime($fechaINGRESO['date']);
        $hour = date("H", $time);

        // Verificar si la hora está dentro del rango deseado (7 AM a 7 PM)
        if ($hour >= 7 && $hour < 19) {
            return $fechaINGRESO;
        }

        // Segunda consulta para formdir = 'eye_mag' si la hora no está dentro del rango
    $queryform2 = "SELECT * FROM forms
                   WHERE pid = ? AND encounter = ? AND formdir = 'eye_mag' AND deleted = 0";
    $fechaINGRESO2 = sqlQuery($queryform2, array($pid, $form_encounter));

    if ($fechaINGRESO2) {
            $time2 = strtotime($fechaINGRESO2['date']);
            $hour2 = date("H", $time2);

            if ($hour2 >= 7 && $hour2 < 19) {
                // Combinar la fecha de 'newpatient' con la hora de 'eye_mag'
                $newDate = date("Y-m-d", $time) . ' ' . date("H:i:s", $time2);
                return ['date' => $newDate];
        }
    }

        // Generar una hora al azar dentro del rango de 7 AM a 7 PM
    $randomHour = rand(7, 18); // Generar una hora al azar entre 7 AM (7) y 6 PM (18)
    $randomMinute = rand(0, 59); // Generar un minuto al azar entre 0 y 59
    $randomTime = sprintf('%02d:%02d', $randomHour, $randomMinute);

        // Combinar la fecha de 'newpatient' con el tiempo generado al azar
        $randomDate = date('Y-m-d', $time) . ' ' . $randomTime . ':00';

    return ['date' => $randomDate];
}

    // Si no se encuentra la fecha en 'newpatient', no retornar nada
    return null;
}

$fechaINGRESO = getFormDate($pid, $form_encounter);
?>
<html>
<HEAD>
    <link rel="stylesheet" type="text/css" href="reports.css">
</HEAD>
<body>
<?php
$dxResult = getDXoftalmo($form_id, $pid, "0");

// Verificamos si la variable $dxResult no está vacía
if (!empty($dxResult)) {
renderPatientInfoTable($titleres, $encounter);
?>
<table>
    <tr>
        <td class="morado" WIDTH="50%">B. MOTIVO DE CONSULTA</td>
        <td class="morado_right" width="20%" style="border-right: 1px solid #808080">PRIMERA</td>
        <td class="blanco" width="5%"></td>
        <td class="morado_right" width="20%" style="border-right: 1px solid #808080">SUBSECUENTE</td>
        <td class="blanco" width="5%"></td>
    </tr>
    <tr>
        <td colspan="5" class="blanco_left"><?php
            echo wordwrap($reason, 165, "</td>
    </tr>
    <tr>
        <td colspan=\"5\" class=\"blanco_left\">"); ?></td>
    </tr>
    <tr>
        <td colspan="5" class="blanco_left"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="13" class="morado">C. ANTECEDENTES PATOLÓGICOS PERSONALES</td>
        <td colspan="7" class="morado" style="font-weight: normal; font-size: 4pt">DATOS CLÍNICO - QUIRÚRGICOS,
            OBSTÉTRICOS, ALÉRGICOS RELEVANTES
        </td>
    </tr>
    <tr>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">1. CARDIOPATÍA</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">2. HIPERTENSIÓN</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">3. ENF. C. VASCULAR</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">4. ENDÓCRINO METABÓLICO</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">5. CÁNCER</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">6. TUBERCULOSIS</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">7. ENF. MENTAL</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">8. ENF. INFECCIOSA</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">9. MAL FORMACIÓN</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">10. OTRO</td>
        <td class="blanco" width="2%"></td>
    </tr>
    <?php
    $diagnoses = getMedicalProblems($pid);

    if (!empty($diagnoses)) {
        foreach ($diagnoses as $diagnosis) {
            $problem = lookup_code_short_descriptions($diagnosis);
            $cie10 = substr($diagnosis, 6);
            echo "<tr><td colspan=\"20\" class=\"blanco_left\">$problem CIE10: $cie10<td></td>";
        }
    } else {
        echo "<tr><td colspan=\"20\" class=\"blanco_left\">Niega<td></td>";
    }
    ?>
</table>
<table>
    <tr>
        <td colspan="20" class="morado">D. ANTECEDENTES PATOLÓGICOS FAMILIARES</td>
    </tr>
    <tr>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">1. CARDIOPATÍA</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">2. HIPERTENSIÓN</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">3. ENF. C. VASCULAR</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">4. ENDÓCRINO METABÓLICO</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">5. CÁNCER</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">6. TUBERCULOSIS</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">7. ENF. MENTAL</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">8. ENF. INFECCIOSA</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">9. MAL FORMACIÓN</td>
        <td class="blanco" width="2%"></td>
        <td class="verde_left" width="8%" style="font-weight: normal; font-size: 4pt">10. OTRO</td>
        <td class="blanco" width="2%"></td>
    </tr>
    <tr>
        <td colspan="20" class="blanco_left"></td>
    </tr>
    <tr>
        <td colspan="20" class="blanco_left"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="morado">E. ENFERMEDAD O PROBLEMA ACTUAL</td>
        <td class="morado" style="font-weight: normal; font-size: 4pt">CRONOLOGÍA - LOCALIZACIÓN -
            CARACTERÍSTICAS - INTENSIDAD - FRECUENCIA - FACTORES AGRAVANTES
        </td>
    </tr>
    <tr>
        <td colspan="2" class="blanco_left">
            <?php
            if ($formdir === 'eye_mag') {
                $encounter_data = getEyeMagEncounterData($form_encounter, $pid);
                if ($encounter_data) {
                    extract($encounter_data);
                    $examOutput = ExamOftal($val, $CC1 ?? '', $RBROW ?? '', $LBROW ?? '', $RUL ?? '', $LUL ?? '', $RLL ?? '', $LLL ?? '', $RMCT ?? '', $LMCT ?? '', $RADNEXA ?? '', $LADNEXA ?? '', $EXT_COMMENTS ?? '',
                        $SCODVA ?? '', $SCOSVA ?? '', $ODVA ?? '', $OSVA ?? '', $ODIOPAP ?? '', $OSIOPAP ?? '', $ODCONJ ?? '', $OSCONJ ?? '', $ODCORNEA ?? '', $OSCORNEA ?? '', $ODAC ?? '', $OSAC ?? '', $ODLENS ?? '', $OSLENS ?? '', $ODIRIS ?? '', $OSIRIS ?? '',
                        $ODDISC ?? '', $OSDISC ?? '', $ODCUP ?? '', $OSCUP ?? '', $ODMACULA ?? '', $OSMACULA ?? '', $ODVESSELS ?? '', $OSVESSELS ?? '', $ODPERIPH ?? '', $OSPERIPH ?? '', $ODVITREOUS ?? '', $OSVITREOUS ?? '');
                    if (!empty($examOutput)) {
                        $enfermedadActual = generateEnfermedadProblemaActual($reason, $examOutput);
                        echo wordwrap($enfermedadActual, 165, "</td></tr><tr><td colspan='2' class='blanco_left'>", true);
                    }
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="blanco_left"></td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="13" class="morado">F. CONSTANTES VITALES Y ANTROPOMETRÍA</td>
    </tr>
    <tr>
        <td class="verde" width="7.69%">FECHA</td>
        <td class="verde" width="7.69%">HORA</td>
        <td class="verde" width="7.69%">Temperatura (°C)</td>
        <td class="verde" width="7.69%">Presión Arterial (mmHg)</td>
        <td class="verde" width="7.69%">Pulso / min</td>
        <td class="verde" width="7.69%">Frecuencia Respiratoria/min</td>
        <td class="verde" width="7.69%">Peso (Kg)</td>
        <td class="verde" width="7.69%">Talla (cm)</td>
        <td class="verde" width="7.69%">IMC (Kg / m 2)</td>
        <td class="verde" width="7.69%">Perímetro Abdominal (cm)</td>
        <td class="verde" width="7.69%">Hemoglobina capilar (g/dl)</td>
        <td class="verde" width="7.69%">Glucosa capilar (mg/ dl)</td>
        <td class="verde" width="7.69%">Pulsioximetría (%)</td>
    </tr>
    <tr>
        <?php
        if ($fechaINGRESO) {
            ?>
            <td class="blanco"><?php echo date("Y/m/d", strtotime($fechaINGRESO['date'])); ?></td>
            <td class="blanco">
                <?php
                $time = strtotime($fechaINGRESO['date']);
                echo date("H:i", $time);
                ?>
            </td>
            <?php
        } else {
            echo "No se encontró una fecha válida.";
        }
        ?>
        </td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
        <td class="blanco">N/A</td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="9" class="morado">G. REVISIÓN ACTUAL DE ÓRGANOS Y SISTEMAS</td>
        <td colspan="6" class="morado" style="font-weight: normal; font-size: 4pt">MARCAR "X" CUANDO PRESENTE
            PATOLOGÍA Y DESCRIBA
        </td>
    </tr>
    <tr>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">1</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">PIEL - ANEXOS</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">3</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">RESPIRATORIO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">5</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">DIGESTIVO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">7</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">MÚSCULO - ESQUELÉTICO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">9</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">HEMO - LINFÁTICO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
    </tr>
    <tr>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">2</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">ÓRGANOS DE LOS SENTIDOS</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">4</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">CARDIO - VASCULAR</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">6</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">GENITO - URINARIO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">8</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">ENDOCRINO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">10</td>
        <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">NERVIOSO</td>
        <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
    </tr>
    <tr>
        <td colspan="15" class="blanco"></td>
    </tr>
</table>
<table style="border: none">
    <TR>
        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1
                                                                 COLOR="#000000">SNS-MSP/HCU-form.002/2021</FONT></B>
        </TD>
        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">CONSULTA EXTERNA - ANAMNESIS
                    (1) </FONT></B>
        </TD>
    </TR>
</TABLE>
<pagebreak>
    <table>
        <tr>
            <td colspan="9" class="morado">H. EXAMEN FÍSICO</td>
            <td colspan="6" class="morado" style="font-weight: normal; font-size: 4pt">MARCAR "X" CUANDO PRESENTE
                PATOLOGÍA Y DESCRIBA
            </td>
        </tr>
        <tr>
            <td colspan="9" class="verde" style="background-color: #0ba1b5">REGIONAL</td>
            <td colspan="6" class="verde" style="background-color: #0ba1b5">SISTÉMICO</td>
        </tr>
        <tr>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">1R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">PIEL - FANERAS</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">2R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">BOCA</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">11R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">ABDOMEN</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">1S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">ÓRGANOS DE LOS SENTIDOS</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">6S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">URINARIO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        </tr>
        <tr>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">2R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">CABEZA</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">7R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">OROFARINGE</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">12R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">COLUMNA VERTEBRAL</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">2S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">RESPIRATORIO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">7S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">MÚSCULO - ESQUELÉTICO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        </tr>
        <tr>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">3R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">OJOS</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt">X</td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">8R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">CUELLO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">13R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">INGLE-PERINÉ</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">3S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">CARDIO - VASCULAR</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">8S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">ENDÓCRINO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        </tr>
        <tr>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">4R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">OÍDOS</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">9R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">AXILAS - MAMAS</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">14R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">MIEMBROS SUPERIORES</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">4S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">DIGESTIVO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">9S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">HEMO - LINFÁTICO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        </tr>
        <tr>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">5R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">NARIZ</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">10R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">TÓRAX</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">15R</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">MIEMBROS INFERIORES</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">5S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">GENITAL</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
            <td class="verde" width="1%" style="font-weight: normal; font-size: 5pt">10S</td>
            <td class="verde" width="7%" style="font-weight: normal; font-size: 5pt">NEUROLÓGICO</td>
            <td class="blanco" width="2%" style="font-weight: normal; font-size: 5pt"></td>
        </tr>
        <tr>
            <td colspan="15" class="blanco_left">
                <?php
                if ($formdir === 'eye_mag') {
                    $encounter_data = getEyeMagEncounterData($form_encounter, $pid);
                    if ($encounter_data) {
                        extract($encounter_data);
                        $examOutput = ExamOftal($val, $CC1 ?? '', $RBROW ?? '', $LBROW ?? '', $RUL ?? '', $LUL ?? '', $RLL ?? '', $LLL ?? '', $RMCT ?? '', $LMCT ?? '', $RADNEXA ?? '', $LADNEXA ?? '', $EXT_COMMENTS ?? '',
                            $SCODVA ?? '', $SCOSVA ?? '', $ODVA ?? '', $OSVA ?? '', $ODIOPAP ?? '', $OSIOPAP ?? '', $ODCONJ ?? '', $OSCONJ ?? '', $ODCORNEA ?? '', $OSCORNEA ?? '', $ODAC ?? '', $OSAC ?? '', $ODLENS ?? '', $OSLENS ?? '', $ODIRIS ?? '', $OSIRIS ?? '',
                            $ODDISC ?? '', $OSDISC ?? '', $ODCUP ?? '', $OSCUP ?? '', $ODMACULA ?? '', $OSMACULA ?? '', $ODVESSELS ?? '', $OSVESSELS ?? '', $ODPERIPH ?? '', $OSPERIPH ?? '', $ODVITREOUS ?? '', $OSVITREOUS ?? '');
                        if (!empty($examOutput)) {
                            echo wordwrap($examOutput, 165, "</td></tr><tr><td colspan='15' class='blanco_left'>", true);
                        }
                    }
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="15" class="blanco"></td>
        </tr>
    </table>
    <table>
        <TR>
            <TD class="morado" width="2%">I.</TD>
            <TD class="morado" width="17.5%">DIAGN&Oacute;STICOS</TD>
            <TD class="morado" width="17.5%" style="font-weight: normal; font-size: 6pt">PRE= PRESUNTIVO DEF= DEFINITIVO
            </TD>
            <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
            <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
            <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
            <TD class="morado" width="2%"><BR></TD>
            <TD class="morado" width="17.5%"><BR></TD>
            <TD class="morado" width="17.5%"><BR></TD>
            <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
            <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
            <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
        </TR>
        <TR>
            <td class="verde">1.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
            <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                    echo "x";
                } ?></td>
            <td class="verde">4.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
            <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                    echo "x";
                } ?></td>
        </TR>
        <TR>
            <td class="verde">2.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
            <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "1")) {
                    echo "x";
                } ?></td>
            <td class="verde">5.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
            <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                    echo "x";
                } ?></td>
        </TR>
        <TR>
            <td class="verde">3.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
            <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                    echo "x";
                } ?></td>
            <td class="verde">6.</td>
            <td colspan="2" class="blanco"
                style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
            <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
            <td class="amarillo"></td>
            <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "5")) {
                    echo "x";
                } ?></td>
        </TR>
    </table>
    <table>
        <tr>
            <td colspan="41" class="morado">J. PLAN DE TRATAMIENTO</td>
            <td colspan="30" class="morado" style="font-weight: normal; font-size: 4pt">DIAGNOSTICO, TERAPÉUTICO Y
                EDUCACIONAL
            </td>
        </tr>
        <tr>
            <td colspan="71" class="blanco_left">
                <?php
                echo getPlanTerapeuticoOD($form_id, $pid);
                echo getPlanTerapeuticoOI($form_id, $pid);
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
        </tr>
        <tr>
            <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
        </tr>
        <tr>
            <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
        </tr>
        <tr>
            <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="71" class="morado">K. DATOS DEL PROFESIONAL RESPONSABLE</td>
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
            <td colspan="21" class="blanco"><?php echo $mname; ?></td>
            <td colspan="19" class="blanco"><?php echo $fname; ?></td>
            <td colspan="16" class="blanco"><?php echo $lname; ?></td>
        </tr>
        <tr>
            <td colspan="15" class="verde">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
            <td colspan="26" class="verde">FIRMA</td>
            <td colspan="30" class="verde">SELLO</td>
        </tr>
        <tr>
            <td colspan="15" class="blanco" style="height: 40px"><?php echo getProviderRegistro($providerID); ?></td>
            <td colspan="26" class="blanco">&nbsp;</td>
            <td colspan="30" class="blanco">&nbsp;</td>
        </tr>
    </table>
    <table style="border: none">
        <TR>
            <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1
                                                                     COLOR="#000000">SNS-MSP/HCU-form.002/2021</FONT></B>
            </TD>
            <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">CONSULTA EXTERNA - EXAMEN FÍSICO Y
                        PRESCRIPCIONES (2) </FONT></B>
            </TD>
        </TR>
    </TABLE>
    <?php
    }
    $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
    $result = sqlStatement($query, array($form_id, $pid));

    if (sqlNumRows($result) > 0) {
    ?>
    <pagebreak>
        <?php
        renderPatientInfoTable($titleres, $encounter);
        ?>
        <table>
            <colgroup>
                <col class="xl76" span="71">
            </colgroup>
            <tr>
                <td colspan="71" class="morado">B. CUADRO CLÍNICO DE INTERCONSULTA</td>
            </tr>
            <tr>
                <td colspan="71" class="blanco_left"><?php
                    echo wordwrap($reason, 165, "</td>
    </tr>
    <tr>
        <td colspan=\"71\" class=\"blanco_left\">"); ?></td>
            </tr>
            <tr>
                <td colspan="71" class="blanco_left"></td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="morado">C. RESUMEN DEL CRITERIO CLÍNICO</td>
            </tr>
            <tr>
                <td class="blanco_left">
                    <?php
                    if ($formdir === 'eye_mag') {
                        $encounter_data = getEyeMagEncounterData($form_encounter, $pid);
                        if ($encounter_data) {
                            extract($encounter_data);
                            $examOutput = ExamOftal($val, $CC1 ?? '', $RBROW ?? '', $LBROW ?? '', $RUL ?? '', $LUL ?? '', $RLL ?? '', $LLL ?? '', $RMCT ?? '', $LMCT ?? '', $RADNEXA ?? '', $LADNEXA ?? '', $EXT_COMMENTS ?? '',
                                $SCODVA ?? '', $SCOSVA ?? '', $ODVA ?? '', $OSVA ?? '', $ODIOPAP ?? '', $OSIOPAP ?? '', $ODCONJ ?? '', $OSCONJ ?? '', $ODCORNEA ?? '', $OSCORNEA ?? '', $ODAC ?? '', $OSAC ?? '', $ODLENS ?? '', $OSLENS ?? '', $ODIRIS ?? '', $OSIRIS ?? '',
                                $ODDISC ?? '', $OSDISC ?? '', $ODCUP ?? '', $OSCUP ?? '', $ODMACULA ?? '', $OSMACULA ?? '', $ODVESSELS ?? '', $OSVESSELS ?? '', $ODPERIPH ?? '', $OSPERIPH ?? '', $ODVITREOUS ?? '', $OSVITREOUS ?? '');
                            if (!empty($examOutput)) {
                                echo wordwrap($examOutput, 165, "</td></tr><tr><td class='blanco_left'>", true);
                            }
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
        <table>
            <TR>
                <TD class="morado" width="2%">D.</TD>
                <TD class="morado" width="17.5%">DIAGN&Oacute;STICOS</TD>
                <TD class="morado" width="17.5%" style="font-weight: normal; font-size: 6pt">PRE= PRESUNTIVO DEF=
                    DEFINITIVO
                </TD>
                <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
                <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
                <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
                <TD class="morado" width="2%"><BR></TD>
                <TD class="morado" width="17.5%"><BR></TD>
                <TD class="morado" width="17.5%"><BR></TD>
                <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
                <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
                <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
            </TR>
            <TR>
                <td class="verde">1.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
                <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                        echo "x";
                    } ?></td>
                <td class="verde">4.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
                <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                        echo "x";
                    } ?></td>
            </TR>
            <TR>
                <td class="verde">2.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
                <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "1")) {
                        echo "x";
                    } ?></td>
                <td class="verde">5.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
                <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                        echo "x";
                    } ?></td>
            </TR>
            <TR>
                <td class="verde">3.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
                <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                        echo "x";
                    } ?></td>
                <td class="verde">6.</td>
                <td colspan="2" class="blanco"
                    style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
                <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
                <td class="amarillo"></td>
                <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "5")) {
                        echo "x";
                    } ?></td>
            </TR>
        </table>
        <table>
            <tr>
                <td colspan="71" class="morado">E. PLAN DE DIAGNÓSTICO PROPUESTO</td>
            </tr>
            <tr>
                <td colspan="71" class="blanco" style="border-right: none; text-align: left">
                    <?php
                    echo fetchEyeMagOrders($form_id, $pid, $providerID);
                    ?>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="71" class="morado">F. PLAN TERAPEÚTICO PROPUESTO</td>
            </tr>
            <tr>
                <td colspan="71" class="blanco_left">
                    <?php
                    echo getPlanTerapeuticoOD($form_id, $pid);
                    echo getPlanTerapeuticoOI($form_id, $pid);
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
            </tr>
            <tr>
                <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
            </tr>
            <tr>
                <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
            </tr>
            <tr>
                <td colspan="71" class="blanco" style="border-right: none; text-align: left"></td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="71" class="morado">G. DATOS DEL PROFESIONAL RESPONSABLE</td>
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
                <td colspan="21" class="blanco"><?php echo $mname; ?></td>
                <td colspan="19" class="blanco"><?php echo $fname; ?></td>
                <td colspan="16" class="blanco"><?php echo $lname; ?></td>
            </tr>
            <tr>
                <td colspan="15" class="verde">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
                <td colspan="26" class="verde">FIRMA</td>
                <td colspan="30" class="verde">SELLO</td>
            </tr>
            <tr>
                <td colspan="15" class="blanco"
                    style="height: 40px"><?php echo getProviderRegistro($providerID); ?></td>
                <td colspan="26" class="blanco">&nbsp;</td>
                <td colspan="30" class="blanco">&nbsp;</td>
            </tr>
        </table>

        <table style="border: none">
            <TR>
                <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1
                                                                         COLOR="#000000">SNS-MSP/HCU-form.007/2021</FONT></B>
                </TD>
                <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">INTERCONSULTA -
                            INFORME</FONT></B>
                </TD>
            </TR>
            ]
        </TABLE>
        <?php
        }
        $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
        $result = sqlStatement($query, array($form_id, $pid));

        if (sqlNumRows($result) > 0) {
            ?>
            <pagebreak>
                <?php
                renderPatientInfoTable($titleres, $encounter);
                ?>
                <table>
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
                <table>
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
                            <?php
                            echo fetchEyeMagOrders($form_id, $pid, $providerID);
                            ?>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td colspan="40" class="morado">D. MOTIVO DE LA SOLICITUD</td>
                        <td colspan="31" class="morado" style="font-weight: normal; font-size: 6pt; text-align: right">
                            REGISTRAR
                            LAS
                            RAZONES PARA SOLICITAR
                            EL ESTUDIO
                        </td>
                    </tr>
                    <tr>
                        <td colspan="10" class="verde"><font class="font6">FUM</font><font
                                class="font5"><br>(aaaa-mm-dd)</font>
                        </td>
                        <td colspan="12" class="blanco">&nbsp;</td>
                        <td colspan="14" class="verde">PACIENTE CONTAMINADO</td>
                        <td colspan="2" class="verde">SI</td>
                        <td colspan="2" class="blanco">&nbsp;</td>
                        <td colspan="2" class="verde">NO</td>
                        <td colspan="2" class="blanco">X</td>
                        <td colspan="27" class="blanco">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="71" class="blanco" style="text-align: left;">SE SOLICITA EXAMENES PARA
                            CONTINUAR TRATAMIENTO
                        </td>
                    </tr>
                    <tr>
                        <td colspan="71" class="blanco">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="71" class="blanco">&nbsp;</td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td class="morado">E. RESUMEN CLÍNICO ACTUAL
                            <span style="font-weight: normal; font-size: 6pt; text-align: right">
                REGISTRAR DE MANERA OBLIGATORIA EL CUADRO CLÍNICO ACTUAL DEL PACIENTE
            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="blanco_left">
                            <?php
                            if ($formdir === 'eye_mag') {
                                $encounter_data = getEyeMagEncounterData($form_encounter, $pid);
                                if ($encounter_data) {
                                    extract($encounter_data);
                                    $examOutput = ExamOftal($val, $CC1 ?? '', $RBROW ?? '', $LBROW ?? '', $RUL ?? '', $LUL ?? '', $RLL ?? '', $LLL ?? '', $RMCT ?? '', $LMCT ?? '', $RADNEXA ?? '', $LADNEXA ?? '', $EXT_COMMENTS ?? '',
                                        $SCODVA ?? '', $SCOSVA ?? '', $ODVA ?? '', $OSVA ?? '', $ODIOPAP ?? '', $OSIOPAP ?? '', $ODCONJ ?? '', $OSCONJ ?? '', $ODCORNEA ?? '', $OSCORNEA ?? '', $ODAC ?? '', $OSAC ?? '', $ODLENS ?? '', $OSLENS ?? '', $ODIRIS ?? '', $OSIRIS ?? '',
                                        $ODDISC ?? '', $OSDISC ?? '', $ODCUP ?? '', $OSCUP ?? '', $ODMACULA ?? '', $OSMACULA ?? '', $ODVESSELS ?? '', $OSVESSELS ?? '', $ODPERIPH ?? '', $OSPERIPH ?? '', $ODVITREOUS ?? '', $OSVITREOUS ?? '');
                                    if (!empty($examOutput)) {
                                        echo wordwrap($examOutput, 165, "</td></tr><tr><td class='blanco_left'>", true);
                                    }
                                }
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                <table>
                    <TR>
                        <TD class="morado" width="2%">F.</TD>
                        <TD class="morado" width="17.5%">DIAGN&Oacute;STICOS</TD>
                        <TD class="morado" width="17.5%" style="font-weight: normal; font-size: 6pt">PRE= PRESUNTIVO
                            DEF=
                            DEFINITIVO
                        </TD>
                        <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
                        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
                        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
                        <TD class="morado" width="2%"><BR></TD>
                        <TD class="morado" width="17.5%"><BR></TD>
                        <TD class="morado" width="17.5%"><BR></TD>
                        <TD class="morado" width="6%" style="font-size: 6pt; text-align: center">CIE</TD>
                        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">PRE</TD>
                        <TD class="morado" width="3.5%" style="font-size: 6pt; text-align: center">DEF</TD>
                    </TR>
                    <TR>
                        <td class="verde">1.</td>
                        <td colspan="2" class="blanco"
                            style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "0"); ?></td>
                        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "0"); ?></td>
                        <td class="amarillo"></td>
                        <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "0")) {
                                echo "x";
                            } ?></td>
                        <td class="verde">4.</td>
                        <td colspan="2" class="blanco"
                            style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "3"); ?></td>
                        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "3"); ?></td>
                        <td class="amarillo"></td>
                        <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "3")) {
                                echo "x";
                            } ?></td>
                    </TR>
                    <TR>
                        <td class="verde">2.</td>
                        <td colspan="2" class="blanco"
                            style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "1"); ?></td>
                        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "1"); ?></td>
                        <td class="amarillo"></td>
                        <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "1")) {
                                echo "x";
                            } ?></td>
                        <td class="verde">5.</td>
                        <td colspan="2" class="blanco"
                            style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "4"); ?></td>
                        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "4"); ?></td>
                        <td class="amarillo"></td>
                        <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "4")) {
                                echo "x";
                            } ?></td>
                    </TR>
                    <TR>
                        <td class="verde">3.</td>
                        <td colspan="2" class="blanco"
                            style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "2"); ?></td>
                        <td class="blanco"><?php echo getDXoftalmoCIE10($form_id, $pid, "2"); ?></td>
                        <td class="amarillo"></td>
                        <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "2")) {
                                echo "x";
                            } ?></td>
                        <td class="verde">6.</td>
                        <td colspan="2" class="blanco"
                            style="text-align: left"><?php echo getDXoftalmo($form_id, $pid, "5"); ?></td>
                        <td class=" blanco
        "><?php echo getDXoftalmoCIE10($form_id, $pid, "5"); ?></td>
                        <td class="amarillo"></td>
                        <td class="amarillo"><?php if (getDXoftalmo($form_id, $pid, "5")) {
                                echo "x";
                            } ?></td>
                    </TR>
                </table>
                <table>
                    <tr>
                        <td colspan="71" class="morado">G. DATOS DEL PROFESIONAL RESPONSABLE</td>
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
                        <td colspan="8"
                            class="blanco"><?php echo date("Y/m/d", strtotime($fechaINGRESO['date'])); ?></td>
                        <td colspan="7" class="blanco"></td>
                        <td colspan="21" class="blanco"><?php echo $mname; ?></td>
                        <td colspan="19" class="blanco"><?php echo $fname; ?></td>
                        <td colspan="16" class="blanco"><?php echo $lname; ?></td>
                    </tr>
                    <tr>
                        <td colspan="15" class="verde">NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</td>
                        <td colspan="26" class="verde">FIRMA</td>
                        <td colspan="30" class="verde">SELLO</td>
                    </tr>
                    <tr>
                        <td colspan="15" class="blanco"
                            style="height: 40px"><?php echo getProviderRegistro($providerID); ?></td>
                        <td colspan="26" class="blanco">&nbsp;</td>
                        <td colspan="30" class="blanco">&nbsp;</td>
                    </tr>
                </table>

                <table style="border: none">
                    <TR>
                        <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP /
                                    HCU-form.012A
                                    /
                                    2008</FONT></B>
                        </TD>
                        <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">IMAGENOLOGIA
                                    SOLICITUD</FONT></B>
                        </TD>
                    </TR>
                    ]
                </TABLE>
            </pagebreak>
            <?php
        }
        ?>
</body>
</html>
