<?php
require_once "../globals.php";
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use Mpdf\Mpdf;

function extractDataParte($sql, $params)
{
    $result = sqlStatement($sql, $params);
    if (!empty($result)) {
        return sqlFetchArray($result);
    }
    return null;
}

$config_mpdf = array(
    'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
    'mode' => $GLOBALS['pdf_language'],
    'format' => 'A4-L',
    'margin_left' => '5',
    'margin_right' => '5',
    'margin_top' => '5',
    'margin_bottom' => '5',
    'orientation' => $GLOBALS['pdf_layout'],
    'shrink_tables_to_fit' => 1,
    'use_kwt' => true,
    'autoScriptToLang' => true,
    'keep_table_proportions' => true
);

$pdf = new mPDF($config_mpdf);
$pdf->SetDisplayMode('real');
if ($_SESSION['language_direction'] == 'rtl') {
    $pdf->SetDirectionality('rtl');
}
ob_start();
?>
<HTML>
<HEAD>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            text-align: left;
            padding: 8px;
        }

        td.encabezado {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            height: 16px;
        }

        td.encabezado2 {
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            height: 14px;
        }

        td.contenido {
            text-align: center;
            font-size: 10px;
            height: 16px;
        }

        td.procedimiento {
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</HEAD>
<BODY>
<table>
    <?php
    $fecha1 = isset($_GET['from']) ? $_GET['from'] : '';
    $fecha2 = isset($_GET['to']) ? $_GET['to'] : '';
    $medico = isset($_GET['provider']) ? $_GET['provider'] : '';
    $FROM = date('Y-m-d', strtotime(str_replace('/', '-', $fecha1)));
    $TO = date('Y-m-d', strtotime(str_replace('/', '-', $fecha2)));

    // Consulta SQL para obtener los eventos
    $query = "SELECT * FROM `openemr_postcalendar_events` AS `ope`
              LEFT JOIN `patient_data` AS `pd` ON (`ope`.`pc_pid` = `pd`.`pid`)
              WHERE  `ope`.`pc_eventDate` BETWEEN ? AND ? AND `ope`.`pc_aid` = ?
              ORDER BY `ope`.`pc_eventDate`, `ope`.`pc_startTime` ASC";

    $params = array($FROM, $TO, $medico);
    $Query = sqlStatement($query, $params);

    $calendario = array();
    $i = 0;

    while ($ip_list = sqlFetchArray($Query)) {
        $fechaEvent = $ip_list['pc_eventDate'];
        $provider = $ip_list['pc_aid'];
        $newdata = array(
            'pid' => $ip_list['pc_pid'],
            'lname' => $ip_list['lname'],
            'lname2' => $ip_list['lname2'],
            'mname' => $ip_list['mname'],
            'fname' => $ip_list['fname'],
            'pricelevel' => $ip_list['pricelevel'],
            'title' => $ip_list['pc_title'],
            'hometext' => $ip_list['pc_hometext'],
            'cirugia' => $ip_list['pc_apptqx'],
            'cirugiaOI' => $ip_list['pc_apptqxOI'],
            'LIOod' => $ip_list['pc_LIOOD'],
            'LIOoi' => $ip_list['pc_LIOOI'],
            'LIObrandOD' => $ip_list['pc_LIO_type_OD'],
            'LIObrandOI' => $ip_list['pc_LIO_type_OI'],
            'Event' => $ip_list['pc_eventDate'],
            'Hora' => $ip_list['pc_startTime'],
            'provider' => $ip_list['pc_aid'],
        );
        $calendario[$fechaEvent] = $newdata;
        $cirujano[$provider] = $newdata;
        $PARTE_items[$i] = $newdata;
        $i++;
    }

    foreach ($calendario as $key2) {
        echo "<tr><td colspan='6' align='center'><h2>";
        echo date('d/M/Y', strtotime($key2['Event']));
        echo "</h2></td></tr>";
        foreach ($cirujano as $key1) {
            if ($key2['Event'] == $key2['Event']) {
                echo "<tr><td colspan='6' align='center'><h3>";
                echo getProviderName($key1['provider']);
                echo "</h3></td></tr>";
                echo "<tr><th>HORA</th>";
                echo "<th>NOMBRE</th>";
                echo "<th>CIRUGIA</th>";
                echo "<th>OJO</th>";
                echo "<th>LIO</th>";
                echo "<th>OBSERVACIONES</th>";
                echo "</tr>";
                foreach ($PARTE_items as $key) {
                    if ($key2['Event'] == $key['Event'] && $key1['provider'] == $key['provider'] && $key['title'] == 'Quirúrgico') {

                        $cirugiaSQL = "SELECT title FROM list_options WHERE list_id = 'cirugia_propuesta_defaults' AND option_id = ?";
                        $LIOsql = "SELECT title FROM list_options WHERE list_id = 'LIO_power' AND option_id = ?";
                        $LIObrandSQL = "SELECT title FROM list_options WHERE list_id = 'Lista_de_fabricantes_de_lentes_intraoculares' AND option_id = ?";

                        $cirugiaOD = "OD: ";
                        $cirugiaOI = "OI: ";

                        $lioOD = extractDataParte($LIOsql, array($key['LIOod']));
                        $lioOI = extractDataParte($LIOsql, array($key['LIOoi']));
                        $lioBrandOD = extractDataParte($LIObrandSQL, array($key['LIObrandOD']));
                        $lioBrandOI = extractDataParte($LIObrandSQL, array($key['LIObrandOI']));
                        $explodeOD = explode(",", $key['cirugia']);
                        $explodeOI = explode(",", $key['cirugiaOI']);

                        echo "<tr><td>";
                        if ($key['pricelevel'] == 'standard') {
                            $ptpricelevel = 'Particular';
                        } else {
                            $ptpricelevel = $key['pricelevel'];
                        }
                        echo substr($key['Hora'], 0, 5) . "(" . text($ptpricelevel) . ")";
                        echo "</td>";
                        echo "<td>";
                        echo text($key['lname']) . " " . text($key['lname2']) . ", " . text($key['fname']);
                        echo "</td>";
                        echo "<td>";
                        if (!empty($key['cirugia']) && !empty($key['cirugiaOI'])) {
                            echo "OD: ";
                            foreach ($explodeOD as $value) {
                                $surgeryOD = sqlQuery($cirugiaSQL, array($value));
                                echo $surgeryOD['title'] . ", ";
                            }
                            echo "<br>";
                            echo "OI: ";
                            foreach ($explodeOI as $value) {
                                $surgeryOI = sqlQuery($cirugiaSQL, array($value));
                                echo $surgeryOI['title'] . ", ";
                            }
                        } elseif (!empty($key['cirugia'])) {
                            echo "OD: ";
                            foreach ($explodeOD as $value) {
                                $surgeryOD = sqlQuery($cirugiaSQL, array($value));
                                echo $surgeryOD['title'] . ", ";
                            }
                        } elseif (!empty($key['cirugiaOI'])) {
                            echo "OI: ";
                            foreach ($explodeOI as $value) {
                                $surgeryOI = sqlQuery($cirugiaSQL, array($value));
                                echo $surgeryOI['title'] . ", ";
                            }
                        }
                        echo "</td>";
                        echo "<td>";
                        if ($key['cirugia'] == $key['cirugiaOI']) {
                            echo "ODI";
                        } else {
                            if ($key['cirugia']) {
                                echo "OD";
                            }
                            if ($key['cirugia'] && $key['cirugiaOI']) {
                                echo "<br />";
                            }
                            if ($key['cirugiaOI']) {
                                echo "OI";
                            }
                        }
                        echo "</td>";
                        echo "<td>";
                        if ($key['cirugia']) {
                            echo "<b>" . text($lioOD['title']) . "</b> " . text($lioBrandOD['title']);
                        }
                        if ($key['cirugiaOI']) {
                            echo "<b>" . text($lioOI['title']) . "</b> " . text($lioBrandOI['title']);
                        }
                        echo "</td>";
                        echo "<td>" . text($key['hometext']) . "</td></tr>";
                    }
                }

            }
        }
    }
    ?>
</table>
</BODY>
</HTML>
<?php

$html = ob_get_clean();
$pdf->writeHTML($html);
$pdf->Output('parte_quirurgico_del_' . $FROM . '_al_' . $TO . '.pdf', 'I'); // D = Download, I = Inline
?>
