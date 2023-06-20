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

function getProtocolDate($formId, $encounter)
{
    // Realizar la consulta a la base de datos
    $fechaPROTOCOLO = sqlQuery("SELECT * FROM forms WHERE form_id = ? AND encounter = ?", array($formId, $encounter));

    // Verificar si se encontró la fecha del protocolo
    if ($fechaPROTOCOLO) {
        // Obtener la fecha y convertirla a un objeto DateTime
        $datedprotocolo = new DateTime($fechaPROTOCOLO['date']);

        // Obtener los componentes de la fecha
        $dateddia = $datedprotocolo->format('d');
        $datedmes = $datedprotocolo->format('m');
        $datedano = $datedprotocolo->format('Y');

        // Retornar los componentes de la fecha en un array
        return array(
            'dia' => $dateddia,
            'mes' => $datedmes,
            'ano' => $datedano
        );
    }

    // En caso de que no se encuentre la fecha, retornar null o un valor por defecto según convenga
    return null;
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

function fetchEyeMagOrders($form_id, $pid)
{
    $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
    $PLAN_results = sqlStatement($query, array($form_id, $pid));
    if (!empty($PLAN_results)) {
        while ($plan_row = sqlFetchArray($PLAN_results)) {
            $IMAGENPropuesta = "SELECT title, codes, notes FROM `list_options`
                                WHERE `list_id` = 'Eye_todo_done_' AND `title` LIKE ? ";
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
    $query = "SELECT c.name
              FROM form_eye_mag_ordenqxod AS o
              LEFT JOIN list_options AS l ON o.ORDER_DETAILS = l.title
              LEFT JOIN consentimiento_informado AS c ON c.name = l.notes
              WHERE o.form_id = ? AND o.pid = ? AND l.list_id = 'cirugia_propuesta_defaults'
              ORDER BY o.id ASC";

    $results = sqlStatement($query, array($form_id, $pid));

    $names = array();

    if (!empty($results)) {
        while ($row = sqlFetchArray($results)) {
            $name = $row['name'];
            $names[] = $name;
        }
    }

    return $names;
}

function getPlanTerapeuticoOI($form_id, $pid)
{
    $query = "SELECT c.name
              FROM form_eye_mag_ordenqxoi AS o
              LEFT JOIN list_options AS l ON o.ORDER_DETAILS = l.title
              LEFT JOIN consentimiento_informado AS c ON c.name = l.notes
              WHERE o.form_id = ? AND o.pid = ? AND l.list_id = 'cirugia_propuesta_defaults'
              ORDER BY o.id ASC";

    $results = sqlStatement($query, array($form_id, $pid));

    $names = array();

    if (!empty($results)) {
        while ($row = sqlFetchArray($results)) {
            $name = $row['name'];
            $names[] = $name;
        }
    }

    return $names;
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
?>
