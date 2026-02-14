<?php
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("user.inc");
require_once("patient.inc");
require_once("lists.inc");
include_once($GLOBALS["srcdir"] . "/api.inc");
require_once(dirname(dirname(__FILE__)) . "/custom/code_types.inc.php");

use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$date_init = "";
$membership_group_number = 0;

function lookup_lbf_desc($desc_lbf)
{
    $querylbf = "select field_value from lbf_data WHERE form_id=? AND field_id=? ";
    $lbf_query = sqlStatement($querylbf, array($_GET['formid'], $desc_lbf));
    return $lbf_query;
}

function getFieldValue($form_id, $field_id)
{
    $querylbfdxpre = sqlQuery("SELECT field_value FROM lbf_data WHERE form_id=? AND field_id=?", array($form_id, $field_id));

    if ($querylbfdxpre) {
        $field_value = $querylbfdxpre['field_value'];
        return $field_value;
    }
}

function obtenerIntervencionesPropuestas($cirugia)
{
    $intervenciones = '';

    if ($cirugia && $cirugia != '0') {
        $cirugia_items = explode('|', $cirugia);
        foreach ($cirugia_items as $item) {
            $QXpropuesta = $item;
            $IntervencionPropuesta = sqlQuery("SELECT notes FROM `list_options` WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` = '$QXpropuesta'");
            if ($IntervencionPropuesta) {
                $intervenciones .= $IntervencionPropuesta['notes'] . " + ";
            }
        }
    }

    return rtrim($intervenciones, " + "); // Elimina el último " + " de la cadena
}

function obtenerDieresis($DIERESIS)
{
    $dieresis = '';

    if ($DIERESIS && $DIERESIS != '0') {
        $DIERESIS_items = explode('|', $DIERESIS);
        foreach ($DIERESIS_items as $item) {
            $QXdieresis = $item;
            $queryDieresis = sqlQuery("SELECT * FROM `list_options` WHERE `option_id` = '$QXdieresis'");
            if ($queryDieresis) {
                $dieresis .= $queryDieresis['title'] . ", ";
            }
        }
    }

    return rtrim($dieresis, ", "); // Elimina la última coma y espacio de la cadena
}

function obtenerExposicion($EXPOSICION)
{
    $exposicion = '';

    if ($EXPOSICION && $EXPOSICION != '0') {
        $EXPOSICION_items = explode('|', $EXPOSICION);
        foreach ($EXPOSICION_items as $item) {
            $QXexpo = $item;
            $queryExpo = sqlQuery("SELECT * FROM `list_options` WHERE `option_id` = '$QXexpo'");
            if ($queryExpo) {
                $exposicion .= $queryExpo['title'] . ", ";
            }
        }
    }

    return rtrim($exposicion, ", "); // Elimina la última coma y espacio de la cadena
}

function getImageHTML($id)
{
    // Verificar si el ID es un arreglo y contiene el carácter "|"
    if (is_array($id) && in_array('|', $id)) {
        $id = array_map(function ($item) {
            return str_replace('|', '', $item);
        }, $id);
    }
    // Realizar la consulta a la base de datos
    $query = "SELECT grafico FROM consentimiento_informado WHERE Id = ?";
    // Ejecutar la consulta y obtener el resultado
    $result = sqlQuery($query, array($id));
    // Verificar si se encontró una imagen
    if ($result && isset($result['grafico'])) {
        $grafico = $result['grafico'];
        // Generar el código HTML para insertar la imagen
        $html = "<img src='" . $grafico . "' alt='Imagen' style='max-height: 140px;' />";
    } else {
        // En caso de que no se encuentre una imagen, se puede mostrar un mensaje alternativo
        $html = $id . "Imagen no encontrada";
    }

    // Retornar el código HTML generado
    return $html;
}

function parseReportKey($key)
{
    if (empty($key) || !preg_match('/^(.*)_(\d+)$/', $key, $matches)) {
        return null;
    }

    return array(
        'formdir' => $matches[1],
        'form_id' => (int)$matches[2]
    );
}

function normalizeReportContext($input = null)
{
    if (!is_array($input)) {
        $input = $_REQUEST;
    }

    $context = array(
        'pid' => null,
        'encounter' => null,
        'form_id' => null,
        'formdir' => null
    );

    $pidCandidates = array(
        $input['pid'] ?? null,
        $input['ptid'] ?? null,
        $input['patientid'] ?? null,
        $_GET['pid'] ?? null,
        $_GET['ptid'] ?? null,
        $_GET['patientid'] ?? null,
        $GLOBALS['pid'] ?? null
    );
    foreach ($pidCandidates as $candidate) {
        if ($candidate !== null && $candidate !== '') {
            $context['pid'] = $candidate;
            break;
        }
    }

    $encounterCandidates = array(
        $input['encounter'] ?? null,
        $input['encid'] ?? null,
        $input['visitid'] ?? null,
        $_GET['encounter'] ?? null,
        $_GET['encid'] ?? null,
        $_GET['visitid'] ?? null,
        $GLOBALS['form_encounter'] ?? null,
        $GLOBALS['encounter'] ?? null
    );
    foreach ($encounterCandidates as $candidate) {
        if ($candidate !== null && $candidate !== '') {
            $context['encounter'] = $candidate;
            break;
        }
    }

    $formIdCandidates = array(
        $input['form_id'] ?? null,
        $input['formid'] ?? null,
        $_GET['form_id'] ?? null,
        $_GET['formid'] ?? null
    );
    foreach ($formIdCandidates as $candidate) {
        if ($candidate !== null && $candidate !== '') {
            $context['form_id'] = $candidate;
            break;
        }
    }

    if (!empty($input['formdir'])) {
        $context['formdir'] = $input['formdir'];
    } elseif (!empty($input['formname'])) {
        $context['formdir'] = $input['formname'];
    } elseif (!empty($_GET['formdir'])) {
        $context['formdir'] = $_GET['formdir'];
    } elseif (!empty($_GET['formname'])) {
        $context['formdir'] = $_GET['formname'];
    }

    $keyCandidates = array(
        $input['key'] ?? null,
        $_GET['key'] ?? null,
        $GLOBALS['key'] ?? null
    );
    foreach ($keyCandidates as $candidate) {
        $parsedKey = parseReportKey($candidate);
        if ($parsedKey) {
            if (empty($context['form_id'])) {
                $context['form_id'] = $parsedKey['form_id'];
            }
            if (empty($context['formdir'])) {
                $context['formdir'] = $parsedKey['formdir'];
            }
            break;
        }
    }

    foreach (array('pid', 'encounter', 'form_id') as $field) {
        if ($context[$field] !== null && $context[$field] !== '' && is_numeric($context[$field])) {
            $context[$field] = (int)$context[$field];
        } elseif ($context[$field] === '') {
            $context[$field] = null;
        }
    }

    return $context;
}

function syncReportContextToRequest($context)
{
    if (!is_array($context)) {
        return;
    }

    if (!empty($context['pid'])) {
        foreach (array('pid', 'ptid', 'patientid') as $key) {
            if (empty($_REQUEST[$key])) {
                $_REQUEST[$key] = $context['pid'];
            }
            if (empty($_GET[$key])) {
                $_GET[$key] = $context['pid'];
            }
        }
    }

    if (!empty($context['encounter'])) {
        foreach (array('encounter', 'encid', 'visitid') as $key) {
            if (empty($_REQUEST[$key])) {
                $_REQUEST[$key] = $context['encounter'];
            }
            if (empty($_GET[$key])) {
                $_GET[$key] = $context['encounter'];
            }
        }
    }

    if (!empty($context['form_id'])) {
        foreach (array('form_id', 'formid') as $key) {
            if (empty($_REQUEST[$key])) {
                $_REQUEST[$key] = $context['form_id'];
            }
            if (empty($_GET[$key])) {
                $_GET[$key] = $context['form_id'];
            }
        }
    }

    if (!empty($context['formdir'])) {
        foreach (array('formdir', 'formname') as $key) {
            if (empty($_REQUEST[$key])) {
                $_REQUEST[$key] = $context['formdir'];
            }
            if (empty($_GET[$key])) {
                $_GET[$key] = $context['formdir'];
            }
        }
    }
}

function parseReportDateValue($dateValue)
{
    if ($dateValue instanceof DateTimeImmutable) {
        return $dateValue;
    }

    if ($dateValue instanceof DateTime) {
        return DateTimeImmutable::createFromMutable($dateValue);
    }

    if ($dateValue === null || $dateValue === '') {
        return null;
    }

    try {
        return new DateTimeImmutable((string)$dateValue);
    } catch (Exception $exception) {
        return null;
    }
}

function getSpanishMonthName($monthNumber)
{
    static $months = array(
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    );

    $monthNumber = (int)$monthNumber;
    return $months[$monthNumber] ?? '';
}

function getClinicalDateTime($formId, $encounter)
{
    if (!empty($formId) && !empty($encounter)) {
        $formDate = sqlQuery("SELECT `date` FROM forms WHERE form_id = ? AND encounter = ? LIMIT 1", array($formId, $encounter));
        if (!empty($formDate['date'])) {
            $dateTime = parseReportDateValue($formDate['date']);
            if ($dateTime) {
                return $dateTime;
            }
        }
    }

    if (!empty($encounter)) {
        $encounterDate = fetchDateByEncounter($encounter);
        $dateTime = parseReportDateValue($encounterDate);
        if ($dateTime) {
            return $dateTime;
        }
    }

    return null;
}

function formatClinicalDate($formId, $encounter, $format = 'd/m/Y', $fallback = '')
{
    $dateTime = getClinicalDateTime($formId, $encounter);
    return $dateTime ? $dateTime->format($format) : $fallback;
}

function formatReportDateValue($dateValue, $format = 'd/m/Y', $fallback = '')
{
    $dateTime = parseReportDateValue($dateValue);
    return $dateTime ? $dateTime->format($format) : $fallback;
}

function getClinicalDateParts($formId, $encounter, $monthStyle = 'numeric')
{
    $dateTime = getClinicalDateTime($formId, $encounter);
    if (!$dateTime) {
        return null;
    }

    $month = ($monthStyle === 'es')
        ? getSpanishMonthName($dateTime->format('n'))
        : $dateTime->format('m');

    return array(
        'dia' => $dateTime->format('d'),
        'mes' => $month,
        'ano' => $dateTime->format('Y'),
        'date' => $dateTime->format('Y-m-d H:i:s')
    );
}

function sortReportSelectionsByEncounterDate(array $selections, $ascending = true)
{
    $nonFormSelections = array();
    $formSelections = array();
    $index = 0;

    foreach ($selections as $key => $value) {
        $parsed = parseReportKey($key);
        if (!$parsed || !is_numeric($value)) {
            $nonFormSelections[$key] = $value;
            continue;
        }

        $encounter = (int)$value;
        $dateTime = parseReportDateValue(fetchDateByEncounter($encounter));
        $timestamp = $dateTime ? $dateTime->getTimestamp() : null;

        $formSelections[] = array(
            'key' => $key,
            'value' => $value,
            'encounter' => $encounter,
            'form_id' => (int)$parsed['form_id'],
            'timestamp' => $timestamp,
            'index' => $index
        );
        $index++;
    }

    usort($formSelections, function ($a, $b) use ($ascending) {
        $at = $a['timestamp'];
        $bt = $b['timestamp'];

        if ($at === null && $bt !== null) {
            return $ascending ? 1 : -1;
        }
        if ($at !== null && $bt === null) {
            return $ascending ? -1 : 1;
        }

        if ($at !== $bt) {
            if ($ascending) {
                return ($at < $bt) ? -1 : 1;
            }
            return ($at > $bt) ? -1 : 1;
        }

        if ($a['encounter'] !== $b['encounter']) {
            if ($ascending) {
                return ($a['encounter'] < $b['encounter']) ? -1 : 1;
            }
            return ($a['encounter'] > $b['encounter']) ? -1 : 1;
        }

        if ($a['form_id'] !== $b['form_id']) {
            if ($ascending) {
                return ($a['form_id'] < $b['form_id']) ? -1 : 1;
            }
            return ($a['form_id'] > $b['form_id']) ? -1 : 1;
        }

        if ($a['index'] === $b['index']) {
            return 0;
        }

        return ($a['index'] < $b['index']) ? -1 : 1;
    });

    $sorted = $nonFormSelections;
    foreach ($formSelections as $item) {
        $sorted[$item['key']] = $item['value'];
    }

    return $sorted;
}

function getProtocolDate($formId, $encounter)
{
    return getClinicalDateParts($formId, $encounter, 'numeric');
}

function ImageStudyName($pid, $encounter, $formid, $formdir)
{
    $query = sqlQuery("SELECT form_name FROM forms
                       WHERE pid = ? AND encounter = ? AND form_id = ? AND formdir = ?
                       AND formdir NOT LIKE 'LBFprotocolo' AND deleted = 0
                       GROUP BY formdir", array($pid, $encounter, $formid, $formdir));

    if ($query) {
        return $query['form_name'];
    }

    return ""; // Retornar una cadena vacía si no se encuentra el formulario
}

function ImageStudyReport($pid, $encounter, $formid, $formdir)
{
    // Obtener el nombre del estudio (en negrita)
    $queryName = sqlQuery("SELECT form_name FROM forms
                           WHERE pid = ? AND encounter = ? AND form_id = ? AND formdir = ?
                           AND formdir NOT LIKE 'LBFprotocolo' AND deleted = 0
                           GROUP BY formdir", array($pid, $encounter, $formid, $formdir));

    $imageName = "";
    if ($queryName) {
        $imageName = "<b>" . $queryName['form_name'] . ": </b>";
    }

    // Obtener la descripción de la imagen (exámenes)
    $queryDescription = sqlStatement("SELECT * FROM forms AS f
                                      LEFT JOIN lbf_data AS lbf ON (lbf.form_id = f.form_id)
                                      LEFT JOIN layout_options AS lo ON (lo.field_id = lbf.field_id)
                                      WHERE f.pid=? AND f.encounter=? AND f.form_id=? AND f.formdir LIKE '%LBF%' AND f.formdir NOT LIKE 'LBFprotocolo'
                                      AND f.deleted = 0 AND lbf.field_id NOT LIKE 'p1' AND lbf.field_id NOT LIKE 'p2' AND lbf.field_id NOT LIKE 'OCTNO_Equi'
                                      AND lbf.field_id NOT LIKE 'equipo'
                                      ORDER BY lo.group_id ASC, lo.seq ASC ", array($pid, $encounter, $formid));

    $imageDescription = "";
    while ($info = sqlFetchArray($queryDescription)) {
        $etiqueta = $info['title'];
        $informe = $info['field_value'];

        // Concatenar la etiqueta y el informe
        $imageDescription .= $etiqueta . ": " . $informe . " ";
    }

    // Concatenar el nombre del estudio con la descripción
    return $imageName . $imageDescription;
}

//Procedimientos no invasivos
function getCarePlanDetails($patientId, $formId)
{
    $carePlanSQL = sqlquery("SELECT * FROM form_care_plan WHERE pid = ? AND id = ? AND activity = 1 ", array($patientId, $formId));

    $carePlanDetails = array(
        'FechaProcedimiento' => $carePlanSQL['date'],
        'CodigoProcedimiento' => $carePlanSQL['code'],
        'OjoProcedimiento' => $carePlanSQL['description'],
        'Procedimiento' => $carePlanSQL['codetext'],
        'MedicoProcedimiento' => $carePlanSQL['care_plan_type']
    );

    return $carePlanDetails;
}

function generateProcedureDescription($codigoProcedimiento, $ojoProcedimiento)
{
    switch ($codigoProcedimiento) {
        case '66761':
            return "Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
                Se posiciona lente de contacto Abraham con soluci&oacute;n de viscoelastico Eyecoat.<br /><br />
                Se realiza Iridotomia YAG Laser, poder 6.0 MW, ojo " . text($ojoProcedimiento) . " 6 disparos, sin complicaciones.";
        case '281362':
            return "Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
                Se posiciona lente de contacto mainster central fundus con solución de viscoelastico Eyecoat.<br /><br />
                Una vez visualizado el fondo de ojo se realiza la aplicación de láser Multispot dentro
                del área nasal superior.";
        case '281351':
            return "Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
                Se posiciona lente de contacto mainster central fundus con solución de viscoelastico Eyecoat.<br /><br />
                Una vez visualizado el fondo de ojo se realiza la aplicación de láser Multispot por fuera
                de las arcadas vasculares de la retina en los cuatro cuadrantes respetando el nervio
                óptico y proliferaciones vítreo-retinianas.";
        case '281340':
            return "Bajo anestesia tópica con clorhidrato de Proximetacaina al 0,5%.<br /><br />
                Se posiciona lente de contacto mainster central fundus con solución de viscoelastico Eyecoat.<br /><br />
                Una vez visualizado el fondo de ojo se realiza la aplicación de láser Multispot por fuera
                de las arcadas vasculares de la retina en los cuatro cuadrantes respetando el nervio
                óptico y proliferaciones vítreo-retinianas.";
        case '281339':
            return "Bajo la aplicación de anestesia tópica de clorhidrato de Proximetacaina al 0,5%.<br>
            Se llevó a cabo el procedimiento utilizando el Yag laser modelo SuperQ de la reconocida marca Ellex.
            Se realizaron disparos dirigidos hacia la cápsula posterior opaca,
            logrando así su disrupción efectiva y permitiendo que el eje visual quedara libre de cualquier tipo de opacidad.";
        case '65855':
            return "Bajo anestesia tópica de clorhidrato de Proximetacaina al 0,5%.<br /><br />
                Se posiciona lente de contacto de goldmann de tres espejos con solución de
                viscoelastico Eyecoat.<br /><br />
                Una vez visualizado la malla trabecular se realiza la aplicación de láser dentro del
                trabéculo en 360°.";
        default:
            return "Procedimiento no definido";
    }
}

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

function getReason($encounter, $pid)
{
    $query = "SELECT reason FROM form_encounter WHERE encounter = ? AND pid = ?";
    $result = sqlQuery($query, array($encounter, $pid));
    return $result['reason'];
}

function fetchEyeMagOrders($form_id, $pid, $providerID)
{
    $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($plan_row = sqlFetchArray($PLAN_results)) {
            $IMAGENPropuesta = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'Eye_todo_done_" . $providerID . "' AND `title` LIKE ? ";
            $code_item = sqlQuery($IMAGENPropuesta, array($plan_row['ORDER_DETAILS']));
            if ($code_item['codes']) {
                echo $code_item['notes'] . " (" . substr($code_item['codes'], 5) . ")";
                echo "</td></tr><tr><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">";
            }
        }
    }
}

function getPlanTerapeuticoOD($form_id, $pid)
{
    $query = "SELECT * FROM form_eye_mag_ordenqxod
              WHERE form_id = ? AND pid = ?
              ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($row = sqlFetchArray($PLAN_results)) {
            $Plan_propuesto = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` LIKE ?";
            $item = sqlQuery($Plan_propuesto, array($row['ORDER_DETAILS']));
            if (!empty($item)) {
                echo $item['notes'] . " en ojo derecho";
                echo "</td></tr><tr><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">";
            }
        }
    }
}

function getPlanTerapeuticoOI($form_id, $pid)
{
    $query = "SELECT * FROM form_eye_mag_ordenqxoi
              WHERE form_id = ? AND pid = ?
              ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($row = sqlFetchArray($PLAN_results)) {
            $Plan_propuesto = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` LIKE ?";
            $item = sqlQuery($Plan_propuesto, array($row['ORDER_DETAILS']));
            if (!empty($item)) {
                echo $item['notes'] . " en ojo izquierdo";
                echo "</td></tr><tr><td colspan=\"71\" class=\"blanco\" style=\"border-right: none; text-align: left\">";
            }
        }
    }
}

function getCPT4Codes($convenio, $lbfID)
{
    $codes = [];

    $querylbfopr = sqlQuery("SELECT field_value FROM lbf_data WHERE form_id=$lbfID AND field_id='Prot_opr'");
    $REALIZADA = $querylbfopr['field_value'];

    if ($convenio == 'IESS' && $REALIZADA && $REALIZADA != '0') {
        $REALIZADA_items = explode('|', $REALIZADA);

        foreach ($REALIZADA_items as $item) {
            $QXpropuesta = $item;
            $IntervencionPropuesta = sqlquery("SELECT codes FROM `list_options` WHERE `list_id` = 'cirugia_propuesta_defaults' AND `option_id` = '$QXpropuesta'");

            if (!empty($IntervencionPropuesta)) {
                $code = $IntervencionPropuesta['codes'];
                $CPT4 = explode('CPT4:', $code);

                foreach ($CPT4 as $val) {
                    $cleanedVal = rtrim($val, ';'); // Eliminar el ";" al final del código
                    if ($cleanedVal > 0) {
                        $codes[] = $cleanedVal;
                    }
                }
            }
        }
    }

    $uniqueCodes = array_unique($codes);
    return $uniqueCodes;
}

function obtenerCodigosImpPlan($pid, $encounter)
{
    // Obtener el form_id más alto para el paciente
    $query = "SELECT MAX(form_id) AS max_form_id FROM forms WHERE pid = ? AND formdir = 'eye_mag' AND encounter <= ? AND deleted = 0";
    $result = sqlFetchArray(sqlStatement($query, array($pid, $encounter)));
    $max_form_id = $result['max_form_id'];

    // Obtener los datos asociados al form_id más alto
    $query = "SELECT * FROM form_eye_mag_impplan WHERE pid=? AND form_id=?";
    $result = sqlStatement($query, array($pid, $max_form_id));
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    $codigosImpPlan = array();

    while ($ip_list = sqlFetchArray($result)) {
        $newdata = array(
            'form_id' => $ip_list['form_id'],
            'pid' => $ip_list['pid'],
            'title' => $ip_list['title'],
            'code' => $ip_list['code'],
            'codetype' => $ip_list['codetype'],
            'codetext' => $ip_list['codetext'],
            'codedesc' => $ip_list['codedesc'],
            'plan' => str_replace($order, $replace, $ip_list['plan']),
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        );

        $pattern = '/Code/';
        if (preg_match($pattern, $newdata['code'])) {
            $newdata['code'] = '';
        }

        if ($newdata['codetext'] > '') {
            $codigosImpPlan[] = $newdata;
        }
    }

    return $codigosImpPlan;
}

function obtenerCIE10issue($pid)
{
    $query = "SELECT title, diagnosis FROM lists WHERE type = 'medical_problem' AND pid=?";
    $result = sqlStatement($query, array($pid));
    $codigosImpPlan = array();

    while ($ip_list = sqlFetchArray($result)) {
        if (!empty($ip_list['title']) && !empty($ip_list['diagnosis'])) {
            $codigosImpPlan[] = array(
                'title' => $ip_list['title'],
                'diagnosis' => $ip_list['diagnosis'],
            );
        }
    }

    return $codigosImpPlan;
}

function extractItemsFromQuery($form_id, $pid, $encounter, $proced_id)
{
    $pc_eid = fetchEventIdByEncounter($encounter);
    $eventDetails = getEventDetails($pc_eid);

    if (!empty($proced_id)) {
        $query = "SELECT name, consiste, realiza, grafico, duracion, beneficios,
              riesgos, riesgos_graves, alternativas, post, consecuencias
              FROM consentimiento_informado
              WHERE Id=? ";
        $results = sqlStatement($query, array($proced_id));

        $items = array();
    } else {
        $query = "SELECT c.name, c.consiste, c.realiza, c.grafico, c.duracion, c.beneficios,
              c.riesgos, c.riesgos_graves, c.alternativas, c.post, c.consecuencias
              FROM form_eye_mag_ordenqxod AS o
              LEFT JOIN consentimiento_informado AS c ON c.Id = o.ORDER_DETAILS
              WHERE o.form_id=? AND o.pid=? ORDER BY o.id ASC";
        $results = sqlStatement($query, array($form_id, $pid));

        $items = array();
    }

    if (!empty($results)) {
        while ($row = sqlFetchArray($results)) {
            // Extraer los datos de cada item
            $name = $row['name'];
            $consiste = $row['consiste'];
            $realiza = $row['realiza'];
            $grafico = $row['grafico'];
            $duracion = $row['duracion'];
            $beneficios = $row['beneficios'];
            $riesgos = $row['riesgos'];
            $riesgos_graves = $row['riesgos_graves'];
            $alternativas = $row['alternativas'];
            $post = $row['post'];
            $consecuencias = $row['consecuencias'];

            // Crear un array con los datos extraídos del item
            $item = array(
                'name' => $name,
                'consiste' => $consiste,
                'realiza' => $realiza,
                'grafico' => $grafico,
                'duracion' => $duracion,
                'beneficios' => $beneficios,
                'riesgos' => $riesgos,
                'riesgos_graves' => $riesgos_graves,
                'alternativas' => $alternativas,
                'post' => $post,
                'consecuencias' => $consecuencias
            );

            // Agregar el item al array de items
            $items[] = $item;
        }
    }

    return $items;
}


function generatePageHeader($facilityService, $web_root)
{
    $facility = null;
    if ($_SESSION['pc_facility']) {
        $facility = $facilityService->getById($_SESSION['pc_facility']);
    } else {
        $facility = $facilityService->getPrimaryBillingLocation();
    }

    $ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round(9 * 7.50)) . "pt' />";

    echo "<page_header>";
    echo "<table>";
    echo "<tr>";
    echo "<td>";
    echo "<span class='sd-abs-pos' style='position: absolute; top: -0.67in; left: 1.81in; width: 249px'>";
    echo $logo;
    echo "</span>";
    echo "</td>";
    echo "<td>";
    echo "<h2>" . $facility['name'] . "</h2>";
    echo "<p class='texto'>";
    echo $facility['street'] . "<br>";
    echo $facility['city'] . ", " . $facility['country_code'] . " " . $facility['postal_code'] . "<br>";
    echo "<b>Telfs: </b>" . $facility['phone'] . "<br>";
    echo "<b>E-mail: </b>" . $facility['email'];
    echo "</p>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "<hr>";
    echo "</page_header>";
}

function getEyeMagEncounterData($encounter, $pid)
{
    $query = "SELECT *, form_encounter.date AS encounter_date
          FROM forms
          JOIN form_encounter ON forms.encounter = form_encounter.encounter
          LEFT JOIN form_eye_base ON forms.form_id = form_eye_base.id
          LEFT JOIN form_eye_hpi ON forms.form_id = form_eye_hpi.id
          LEFT JOIN form_eye_ros ON forms.form_id = form_eye_ros.id
          LEFT JOIN form_eye_vitals ON forms.form_id = form_eye_vitals.id
          LEFT JOIN form_eye_acuity ON forms.form_id = form_eye_acuity.id
          LEFT JOIN form_eye_refraction ON forms.form_id = form_eye_refraction.id
          LEFT JOIN form_eye_biometrics ON forms.form_id = form_eye_biometrics.id
          LEFT JOIN form_eye_external ON forms.form_id = form_eye_external.id
          LEFT JOIN form_eye_antseg ON forms.form_id = form_eye_antseg.id
          LEFT JOIN form_eye_postseg ON forms.form_id = form_eye_postseg.id
          LEFT JOIN form_eye_neuro ON forms.form_id = form_eye_neuro.id
          LEFT JOIN form_eye_locking ON forms.form_id = form_eye_locking.id
          LEFT JOIN form_eye_mag_wearing ON forms.form_id = form_eye_mag_wearing.form_id
          WHERE forms.deleted != '1'
            AND forms.formdir = 'eye_mag'
            AND forms.encounter = ?
            AND forms.pid = ?";

    $encounter_data = sqlQuery($query, array($encounter, $pid));

    return $encounter_data;
}

function ExamenesImagenes($pid, $encounter, $formid, $formdir)
{

    $query = sqlStatement("SELECT * FROM forms AS f
                                           LEFT JOIN lbf_data AS lbf ON (lbf.form_id = f.form_id)
                                           LEFT JOIN layout_options AS lo ON (lo.field_id = lbf.field_id)
                                           WHERE f.pid=? AND f.encounter=? AND f.form_id=? AND f.formdir LIKE '%LBF%' AND f.formdir NOT LIKE 'LBFprotocolo'
                                           AND f.deleted = 0 AND lbf.field_id NOT LIKE 'p1' AND lbf.field_id NOT LIKE 'p2' AND lbf.field_id NOT LIKE 'OCTNO_Equi'
                                           AND lbf.field_id NOT LIKE 'equipo'
                                           ORDER BY lo.group_id ASC, lo.seq ASC ", array($pid, $encounter, $formid));
    while ($info = sqlFetchArray($query)) {
        $lbf = array(
            'etiqueta' => $info['title'],
            'informe' => $info['field_value'],
        );
        $lb[$formdir] = $lbf;
        foreach ($lb as $inf) {
            echo $inf['etiqueta'] . ": " . $inf['informe'] . " ";
        }
    }
    //$Exam = $Examen . $ExamenContent;
    echo "</TD></TR>";
}

function protocolo($form_id, $form_encounter, $formdir)
{
    $REALIZADA = getFieldValue($form_id, 'Prot_opr');
    $ojoValue = getFieldValue($form_id, 'Prot_ojo');
    $dateform = getEncounterDateByFormID($form_encounter, $form_id, $formdir);

    echo "<b>(" . text(oeFormatSDFT(strtotime($dateform['date']))) . ") </b>";

    if ($REALIZADA && $ojoValue != '0') {
        $REALIZADA_items = explode('|', $REALIZADA);
        $notesArray = [];

        foreach ($REALIZADA_items as $value) {
            $QXpropuesta = $value;
            $IntervencionPropuesta = sqlquery("SELECT notes FROM `list_options`
                                               WHERE `list_id` = 'cirugia_propuesta_defaults'
                                               AND `option_id` = '$QXpropuesta' ");

            $notesArray[] = $IntervencionPropuesta['notes'];
        }

        $notesString = implode(" + ", $notesArray);

        if (!empty($notesString)) {
            echo $notesString;
        }

        $mensajeOjo = [
            'OI' => 'Ojo izquierdo',
            'OjoIzq' => 'Ojo izquierdo',
            'OD' => 'Ojo derecho',
            'OjoDer' => 'Ojo derecho',
            'AO' => 'Ambos ojos',
            'OjoAmb' => 'Ambos ojos'
        ];

        if (isset($mensajeOjo[$ojoValue])) {
            echo " " . $mensajeOjo[$ojoValue];
        } else {
            echo " Valor no válido";
        }
        echo "</td></tr><tr><td class='linearesumen'>";
    }
}

function noInvasivos($form_id, $form_encounter)
{
    $NoInvasivoQuery = sqlQuery("SELECT * from form_care_plan
                                    WHERE id = $form_id
                                    AND encounter = $form_encounter ");

    $procedimiento = $NoInvasivoQuery['codetext'];
    $dateform = $NoInvasivoQuery['date'];
    $ojo_atendido = 'ojo ' . $NoInvasivoQuery['description'];
    echo "<b>" . "(" . text(oeFormatSDFT(strtotime($dateform))) . ") " . "</b>";
    echo $procedimiento . ' ' . $ojo_atendido;
    echo "</TD></TR><TR><TD class='linearesumen'>";
}

function getDXoftalmo($form_id, $pid, $dxnum)
{
    $query = "select * from form_eye_mag_impplan where form_id=? and pid=? AND IMPPLAN_order = ? order by IMPPLAN_order ASC LIMIT 1";
    $result = sqlStatement($query, array($form_id, $pid, $dxnum));
    $i = '0';
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    // echo '<ol>';
    while ($ip_list = sqlFetchArray($result)) {
        $newdata = array(
            'form_id' => $ip_list['form_id'],
            'pid' => $ip_list['pid'],
            'title' => $ip_list['title'],
            'code' => $ip_list['code'],
            'codetype' => $ip_list['codetype'],
            'codetext' => $ip_list['codetext'],
            'codedesc' => $ip_list['codedesc'],
            'plan' => str_replace($order, $replace, $ip_list['plan']),
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        );
        $IMPPLAN_items[$i] = $newdata;
        $i++;
    }

    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
    foreach ($IMPPLAN_items as $item) {
        $pattern = '/Code/';
        if (preg_match($pattern, $item['code'])) {
            $item['code'] = '';
        }

        if ($item['codetext'] > '') {
            return $item['codedesc'] . ". ";
        }

    }
}

function getDXcodedesc($form_id, $pid)
{
    $query = "SELECT DISTINCT code, codedesc FROM form_eye_mag_impplan
              WHERE form_id=? AND pid=? ORDER BY form_id ASC, IMPPLAN_order ASC";
    $result = sqlStatement($query, array($form_id, $pid));
    $uniqueItems = array();

    while ($ip_list = sqlFetchArray($result)) {
        $code = $ip_list['code'];
        // Use the 'code' value as the key to store the item in the $uniqueItems array
        if (!isset($uniqueItems[$code])) {
            $newdata = array(
                'code' => $ip_list['code'],
                'codedesc' => $ip_list['codedesc']
            );
            $uniqueItems[$code] = $newdata;
        }
    }
    return $uniqueItems;
}


function getDXoftalmoCIE10($form_id, $pid, $dxnum)
{
    $query = "select * from form_eye_mag_impplan
              where codetype = 'ICD10' and form_id=? and pid=? and IMPPLAN_order = ?
              order by IMPPLAN_order ASC LIMIT 1";
    $result = sqlStatement($query, array($form_id, $pid, $dxnum));
    $i = '0';
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    // echo '<ol>';
    while ($ip_list = sqlFetchArray($result)) {
        $newdata = array(
            'form_id' => $ip_list['form_id'],
            'pid' => $ip_list['pid'],
            'title' => $ip_list['title'],
            'code' => $ip_list['code'],
            'codetype' => $ip_list['codetype'],
            'codetext' => $ip_list['codetext'],
            'codedesc' => $ip_list['codedesc'],
            'plan' => str_replace($order, $replace, $ip_list['plan']),
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        );
        $IMPPLAN_items[$i] = $newdata;
        $i++;
    }

    //for ($i=0; $i < count($IMPPLAN_item); $i++) {
    foreach ($IMPPLAN_items as $item) {
        $pattern = '/Code/';
        if (preg_match($pattern, $item['code'])) {
            $item['code'] = '';
        }

        if ($item['codetext'] > '') {
            return $item['code'] . ". ";
        }

    }
}

function ExamOftal($form_encounter, $CC1, $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS,
                   $SCODVA, $SCOSVA, $ODVA, $OSVA, $ODIOPAP, $OSIOPAP, $ODCONJ, $OSCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP,
                   $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS, $colspan = '')
{
    $dateform = getEncounterDateByEncounter($form_encounter);
    $ExamOFT = "<b>" . "(" . text(oeFormatSDFT(strtotime($dateform["date"]))) . ") " . "</b>";

    $fields = [$CC1, $RBROW, $LBROW, $RUL, $LUL, $RLL, $LLL, $RMCT, $LMCT, $RADNEXA, $LADNEXA, $EXT_COMMENTS, $SCODVA, $SCOSVA, $ODVA, $OSVA, $ODIOPAP, $OSIOPAP, $OSCONJ, $ODCONJ, $ODCORNEA, $OSCORNEA, $ODAC, $OSAC, $ODLENS, $OSLENS, $ODIRIS, $OSIRIS, $ODDISC, $OSDISC, $ODCUP, $OSCUP, $ODMACULA, $OSMACULA, $ODVESSELS, $OSVESSELS, $ODPERIPH, $OSPERIPH, $ODVITREOUS, $OSVITREOUS];

    // Aplica strtolower() y ucfirst() para poner en formato de oración.

    if (array_filter($fields)) {
        if ($CC1) {
            $ExamOFT .= strtolower($CC1) . ", ";
        }
        if ($SCODVA || $SCOSVA) {
            $ExamOFT .= "Agudeza Visual sin Corrección: ";
            if ($SCODVA) {
                $ExamOFT .= "Ojo Derecho: " . strtolower($SCODVA) . ", ";
            }
            if ($SCOSVA) {
                $ExamOFT .= "Ojo Izquierdo: " . strtolower($SCOSVA) . ", ";
            }
        }
        if ($ODVA || $OSVA) {
            $ExamOFT .= "Agudeza Visual con Corrección: ";
            if ($ODVA) {
                $ExamOFT .= "Ojo Derecho: " . strtolower($ODVA) . ", ";
            }
            if ($OSVA) {
                $ExamOFT .= "Ojo Izquierdo: " . strtolower($OSVA) . ", ";
            }
        }

        if ($ODIOPAP || $OSIOPAP) {
            $ExamOFT .= "Presión Intraocular: ";
            if ($ODIOPAP) {
                $ExamOFT .= "Ojo Derecho: " . strtolower($ODIOPAP) . ", ";
            }
            if ($OSIOPAP) {
                $ExamOFT .= "Ojo Izquierdo: " . strtolower($OSIOPAP) . ", ";
            }
        }

        if ($RBROW || $LBROW || $RUL || $LUL || $RLL || $LLL || $RMCT || $LMCT || $RADNEXA || $LADNEXA || $EXT_COMMENTS) {
            $ExamOFT .= "Examen Externo: ";
            if ($RBROW || $RUL || $RLL || $RMCT || $RADNEXA) {
                $ExamOFT .= "Ojo Derecho: " . strtolower($RBROW) . " " . strtolower($RUL) . " " . strtolower($RLL) . " " . strtolower($RMCT) . " " . strtolower($RADNEXA) . " ";
            }
            if ($LBROW || $LUL || $LLL || $LMCT || $LADNEXA) {
                $ExamOFT .= "Ojo Izquierdo: " . strtolower($LBROW) . " " . strtolower($LUL) . " " . strtolower($LLL) . " " . strtolower($LMCT) . " " . strtolower($LADNEXA) . " ";
            }
            $ExamOFT .= strtolower($EXT_COMMENTS);
        }
        if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS || $OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
            $ExamOFT .= "Biomicroscopía: ";
            if ($ODCONJ || $ODCORNEA || $ODAC || $ODLENS || $ODIRIS) {
                $ExamOFT .= "Ojo Derecho: ";
            }
            if ($ODCONJ) {
                $ExamOFT .= "Conjuntiva " . strtolower($ODCONJ) . ", ";
            }
            if ($ODCORNEA) {
                $ExamOFT .= "Córnea " . strtolower($ODCORNEA) . ", ";
            }
            if ($ODAC) {
                $ExamOFT .= "Cámara Anterior " . strtolower($ODAC) . ", ";
            }
            if ($ODLENS) {
                $ExamOFT .= "Cristalino " . strtolower($ODLENS) . ", ";
            }
            if ($ODIRIS) {
                $ExamOFT .= "Iris " . strtolower($ODIRIS) . ", ";
            }
            if ($OSCONJ || $OSCORNEA || $OSAC || $OSLENS || $OSIRIS) {
                $ExamOFT .= "Ojo Izquierdo: ";
            }
            if ($OSCONJ) {
                $ExamOFT .= "Conjuntiva " . strtolower($OSCONJ) . ", ";
            }
            if ($OSCORNEA) {
                $ExamOFT .= "Córnea " . strtolower($OSCORNEA) . ", ";
            }
            if ($OSAC) {
                $ExamOFT .= "Cámara Anterior " . strtolower($OSAC) . ", ";
            }
            if ($OSLENS) {
                $ExamOFT .= "Cristalino " . strtolower($OSLENS) . ", ";
            }
            if ($OSIRIS) {
                $ExamOFT .= "Iris " . strtolower($OSIRIS) . ", ";
            }
        }
        if ($ODDISC || $OSDISC || $ODCUP || $OSCUP || $ODMACULA || $OSMACULA || $ODVESSELS || $OSVESSELS || $ODPERIPH || $OSPERIPH || $ODVITREOUS || $OSVITREOUS) {
            $ExamOFT .= "Al fondo de ojo: ";
        }
        //Retina Ojo Derecho
        if ($ODDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
            $ExamOFT .= "Ojo Derecho: ";
        }
        if ($ODDISC) {
            $ExamOFT .= "Disco " . strtolower($ODDISC) . ", ";
        }
        if ($ODCUP) {
            $ExamOFT .= "Copa " . strtolower($ODCUP) . ", ";
        }
        if ($ODMACULA) {
            $ExamOFT .= "Mácula " . strtolower($ODMACULA) . ", ";
        }
        if ($ODVESSELS) {
            $ExamOFT .= "Vasos " . strtolower($ODVESSELS) . ", ";
        }
        if ($ODPERIPH) {
            $ExamOFT .= "Periferia " . strtolower($ODPERIPH) . ", ";
        }
        if ($ODVITREOUS) {
            $ExamOFT .= "Vítreo " . strtolower($ODVITREOUS) . ", ";
        }
        //Retina Ojo Izquierdo
        if ($OSDISC || $OSCUP || $OSMACULA || $OSVESSELS || $OSPERIPH || $OSVITREOUS) {
            $ExamOFT .= "Ojo Izquierdo: ";
        }
        if ($OSDISC) {
            $ExamOFT .= "Disco " . strtolower($OSDISC) . ", ";
        }
        if ($OSCUP) {
            $ExamOFT .= "Copa " . strtolower($OSCUP) . ", ";
        }
        if ($OSMACULA) {
            $ExamOFT .= "Mácula " . strtolower($OSMACULA) . ", ";
        }
        if ($OSVESSELS) {
            $ExamOFT .= "Vasos " . strtolower($OSVESSELS) . ", ";
        }
        if ($OSPERIPH) {
            $ExamOFT .= "Periferia " . strtolower($OSPERIPH) . ", ";
        }
        if ($OSVITREOUS) {
            $ExamOFT .= "Vítreo " . strtolower($OSVITREOUS) . ", ";
        }
        return $ExamOFT;
    }
    return '';
}


?>
