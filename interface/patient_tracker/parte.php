<?php
require_once "../globals.php";
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use Mpdf\Mpdf;

// Font size in points for table cell data.
$FONTSIZE = 9;
$formid = $_GET['provider'];

// Html2pdf fails to generate checked checkboxes properly, so write plain HTML
// if we are doing a visit-specific form to be completed.
// TODO - now use mPDF, so should test if still need this fix
$PDF_OUTPUT = $formid;
//$PDF_OUTPUT = false; // debugging

if ($PDF_OUTPUT) {
    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => 'A4-L',
        'default_font_size' => '',
        'default_font' => '',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
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

    $fecha1 = $_GET['from']; // Obviamente se cambia por $_POST['fecha1'];
    $FROM = date('Y/m/d', strtotime(str_replace('/', '-', $fecha1)));
    $fecha2 = $_GET['to']; // Obviamente se cambia por $_POST['fecha1'];
    $TO = date('Y/m/d', strtotime(str_replace('/', '-', $fecha2)));
    $medico = $_GET['provider'];
    $Query = sqlStatement("SELECT * FROM `openemr_postcalendar_events` AS `ope`
                       LEFT JOIN `patient_data` AS `pd` ON (`ope`.`pc_pid` = `pd`.`pid`)
                       WHERE  `ope`.`pc_eventDate` BETWEEN '$FROM' AND '$TO' AND `ope`.`pc_aid` LIKE '%$medico%'
                       ORDER BY `ope`.`pc_eventDate`, `ope`.`pc_startTime` ASC");
    $i='0';
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
        td.encabezado{
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            height: 16px;
        }
        td.encabezado2{
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            height: 14px;
        }
        td.contenido{
            text-align: center;
            font-size: 10px;
            height: 16px;
        }
        td.procedimiento{
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f5f5f5;}
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</HEAD>
<BODY>
<table>
<?php
    $calendario = array();
    while ($ip_list = sqlFetchArray($Query)) {
        $fechaEvent = $ip_list['pc_eventDate'];
        $provider = $ip_list['pc_aid'];
        $newdata =  array (
            'pid'           => $ip_list['pc_pid'],
            'lname'         => $ip_list['lname'],
            'lname2'        => $ip_list['lname2'],
            'mname'         => $ip_list['mname'],
            'fname'         => $ip_list['fname'],
            'pricelevel'         => $ip_list['pricelevel'],
            'title'         => $ip_list['pc_title'],
            'hometext'      => $ip_list['pc_hometext'],
            'cirugia'      => $ip_list['pc_apptqx'],
            'cirugiaOI'      => $ip_list['pc_apptqxOI'],
            'LIOod'      => $ip_list['pc_LIOOD'],
            'LIOoi'      => $ip_list['pc_LIOOI'],
            'Event'         => $ip_list['pc_eventDate'],
            'Hora'         => $ip_list['pc_startTime'],
            'provider'      => $ip_list['pc_aid'],
        );
        $calendario[$fechaEvent] = $newdata;
        $cirujano[$provider] = $newdata;
        $PARTE_items[$i] =$newdata;
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
                        echo "<tr><td>";
                        echo substr($key['Hora'],0,5) . "(" . text($key['pricelevel']) . ")";
                        echo "</td>";
                        echo "<td>";
                        echo text($key['lname']) . " " . text($key['lname2']) . ", " . text($key['fname']);
                        echo "</td>";
                        echo "<td>";
                        if ($key['cirugia'] == $key['cirugiaOI']) {
                            echo $key['cirugia'];
                        }
                        else {
                            if ($key['cirugia']) {
                                echo text($key['cirugia']);
                            }
                            if ($key['cirugia'] && $key['cirugiaOI']) {
                                echo "<br />";
                            }
                            if ($key['cirugiaOI']) {
                                echo text($key['cirugiaOI']);
                            }
                        }
                        echo "</td>";
                        echo "<td>";
                        if ($key['cirugia'] == $key['cirugiaOI']) {
                            echo "ODI";
                        }
                        else {
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
                        echo"<td>";
                        if ($key['cirugia']) {
                            echo text($key['LIOod']);
                        }
                        if ($key['cirugiaOI']) {
                            echo text($key['LIOoi']);
                        }
                        echo "</td>";
                        echo"<td>" . text($key['hometext']) . "</td></tr>";
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
}
?>
