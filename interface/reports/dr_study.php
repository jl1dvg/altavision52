<?php

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Verificación del token CSRF
if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Procesamiento de fechas
$from_date = DateToYYYYMMDD($_POST['form_from_date']);
$to_date = DateToYYYYMMDD($_POST['form_to_date']);
if (empty($to_date) && !empty($from_date)) {
    $to_date = date('Y-12-31');
}

if (empty($from_date) && !empty($to_date)) {
    $from_date = date('Y-01-01');
}

$form_provider = empty($_POST['form_provider']) ? 0 : intval($_POST['form_provider']);
$form_pricelevel = $_POST['form_pricelevel'];

// Exportación a CSV
if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list.csv");
    header("Content-Description: File Transfer");
} else {
?>
<html>
<head>

    <title><?php echo xlt('Estudio Diabetes'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script language="JavaScript">

        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
            top.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });

        function topatient(newpid) {
            if ($('#setting_new_window').val() === 'checked') {
                openNewTopWindow(newpid);
            } else {
                top.restoreSession();
                top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid;
            }
        }

    </script>

    <style type="text/css">
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }

            #report_parameters_daterange {
                visibility: visible;
                display: inline;
                margin-bottom: 10px;
            }

            #report_results table {
                margin-top: 0px;
            }
        }

        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }

            #report_results {
                width: 100%;
            }
        }

    </style>

</head>

<body class="body_top">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Estudio Diabetes'); ?></span>

<div id="report_parameters_daterange">
    <?php if (!(empty($to_date) && empty($from_date))) { ?>
        <?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
    <?php } ?>
</div>

<form name='theform' id='theform' method='post' action='dr_study.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>

    <div id="report_parameters">

        <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
        <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

        <table>
            <tr>
                <td width='60%'>
                    <div style='float:left'>

                        <table class='text'>
                            <tr>
                                <td class='control-label'>
                                    <?php echo xlt('Provider'); ?>:
                                </td>
                                <td>
                                    <?php
                                    generate_form_field(array('data_type' => 10, 'field_id' => 'provider',
                                        'empty_title' => '-- All --'), $_POST['form_provider']);
                                    ?>
                                </td>
                                <td>
                                    <?php

                                    // Build a drop-down list of providers.
                                    //

                                    $queryp = "SELECT option_id, title FROM list_options WHERE " .
                                        "list_id = 'pricelevel' ORDER BY seq"; //(CHEMED) facility filter

                                    $price = sqlStatement($queryp);

                                    echo "   <select name='form_pricelevel' class='form-control'>\n";
                                    echo "    <option value=''>-- " . xlt('All') . " --\n";

                                    while ($prirow = sqlFetchArray($price)) {
                                        $price1 = $prirow['title'];
                                        echo "    <option value='" . attr($price1) . "'";
                                        if ($price1 == $_POST['form_pricelevel']) {
                                            echo " selected";
                                        }

                                        echo ">" . text($prirow['title']) . "\n";
                                    }

                                    echo "   </select>\n";

                                    ?>
                                </td>
                                <td class='control-label'>
                                    <?php echo xlt('Visits From'); ?>:
                                </td>
                                <td>
                                    <input class='datepicker form-control' type='text' name='form_from_date'
                                           id="form_from_date" size='10'
                                           value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                                </td>
                                <td class='control-label'>
                                    <?php echo xlt('To'); ?>:
                                </td>
                                <td>
                                    <input class='datepicker form-control' type='text' name='form_to_date'
                                           id="form_to_date" size='10'
                                           value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                                </td>
                            </tr>
                        </table>

                    </div>

                </td>
                <td align='left' valign='middle' height="100%">
                    <table style='border-left:1px solid; width:100%; height:100%'>
                        <tr>
                            <td>
                                <div class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href='#' class='btn btn-default btn-save'
                                           onclick='$("#form_csvexport").val(""); $("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                            <?php echo xlt('Submit'); ?>
                                        </a>
                                        <a href='#' class='btn btn-default btn-transmit'
                                           onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                                            <?php echo xlt('Export to CSV'); ?>
                                        </a>
                                        <?php if ($_POST['form_refresh']) { ?>
                                            <a href='#' id='printbutton' class='btn btn-default btn-print'>
                                                <?php echo xlt('Print'); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div> <!-- end of parameters -->

    <?php
    }

    if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
        if (!$_POST['form_csvexport']) {
            echo '<div id="report_results"><table id="mymaintable"><thead>';
            $headers = [
                'Last Visit', 'Patient', 'ID', 'Sex', 'City', 'DOB', 'Primera Visita', 'Primer OCT',
                'Edema OD', 'Edema OI', 'Difuso OD', 'Difuso OI', 'Quístico OD', 'Quístico OI',
                'EPR OD', 'EPR OI', 'Exudados OD', 'Exudados OI', 'Lipídico OD', 'Lipídico OI',
                'Epiretinal OD', 'Epiretinal OI', 'DR OD', 'DR OI', 'Fibrosis OD', 'Fibrosis OI',
                'Primer AF', 'Diabetica OD', 'Diabetica OI', 'Proliferativa OD', 'Proliferativa OI',
                'No Proliferativa OD', 'No Proliferativa OI', 'Isquemia OD', 'Isquemia OI',
                'Leve OD', 'Leve OI', 'Moderada OD', 'Moderada OI', 'Severa OD', 'Severa OI',
                'Opacidad OD', 'Opacidad OI', 'No val OD', 'No val OI', 'Pricelevel'
            ];

            foreach ($headers as $header) {
                echo '<th>' . xlt($header) . '</th>';
            }

            echo '</thead><tbody>';
        }

        function fetchData($pid, $formdir, $fieldIdsToPrint)
        {
            $query = sqlStatement("SELECT f.form_id, f.encounter, f.date, l.field_id, l.field_value
                                FROM forms AS f
                                LEFT JOIN lbf_data AS l ON f.form_id = l.form_id
                               WHERE f.pid = ? AND f.formdir = ? AND f.deleted = 0", [$pid, $formdir]);

            if ($query) {
                $firstEncounter = null;
                $firstEncounterData = [];

                foreach ($query as $linea) {
                    if ($firstEncounter === null) {
                        $firstEncounter = $linea['encounter'];
                    }

                    if ($linea['encounter'] === $firstEncounter) {
                        $form_id = $linea['form_id'];
                        $field_id = $linea['field_id'];
                        $field_value = $linea['field_value'];

                        // Si no existe la clave form_id en el array, se crea
                        if (!isset($firstEncounterData[$form_id])) {
                            $firstEncounterData[$form_id] = [];
                        }

                        // Se agrega el field_id y su valor al array
                        $firstEncounterData[$form_id][$field_id] = $field_value;
                    }
                }

                $output = '';
                foreach ($firstEncounterData as $formData) {
                    foreach ($formData as $field_id => $field_value) {
                        // Verificar si el field_id está en la lista de los que se deben imprimir
                        if (in_array($field_id, $fieldIdsToPrint)) {
                            $output .= $field_value;
                        }
                    }
                }

                // Devolver la cadena de texto
                return $output;
            } else {
                return "No se encontraron resultados.";
            }
        }

        // Inicializar variables
        $totalpts = 0;
        $sqlArrayBind = [];

// Construir la consulta SQL
        $query = "SELECT
            p.fname, p.mname, p.lname, p.sex, p.city, p.DOB, p.pricelevel,
            p.phone_home, p.phone_biz, p.phone_cell, p.phone_contact, p.pid, p.pubpid,
            COUNT(e.date) AS ecount, MAX(e.date) AS edate, MIN(e.date) AS fdate
          FROM
            patient_data AS p
          JOIN
            form_eye_mag_impplan AS i ON p.pid = i.pid AND i.code = 'H360' ";

// Agregar condiciones a la consulta según los parámetros recibidos
        if (!empty($from_date)) {
            $query .= "JOIN form_encounter AS e ON e.pid = p.pid AND e.date >= ? AND e.date <= ? ";
            $sqlArrayBind[] = $from_date . ' 00:00:00';
            $sqlArrayBind[] = $to_date . ' 23:59:59';
            if ($form_provider) {
                $query .= "AND e.provider_id = ? ";
                $sqlArrayBind[] = $form_provider;
            }
        } elseif ($form_provider) {
            $query .= "JOIN form_encounter AS e ON e.pid = p.pid AND e.provider_id = ? ";
            $sqlArrayBind[] = $form_provider;
        } else {
            $query .= "LEFT OUTER JOIN form_encounter AS e ON e.pid = p.pid ";
        }

// Agregar filtro por nivel de precio si está definido
        if ($form_pricelevel) {
            $query .= "AND p.pricelevel = ? ";
            $sqlArrayBind[] = $form_pricelevel;
        }

        $query .= "GROUP BY p.lname, p.fname, p.mname, p.pid ORDER BY p.lname, p.fname, p.mname, p.pid DESC";

// Ejecutar la consulta SQL
        $res = sqlStatement($query, $sqlArrayBind);

        $prevpid = 0;
        while ($row = sqlFetchArray($res)) {
            if ($row['pid'] == $prevpid) {
                continue;
            }

            $prevpid = $row['pid'];
            $age = '';
            if ($row['DOB']) {
                $dob = $row['DOB'];
                $tdy = $row['edate'] ? $row['edate'] : date('Y-m-d');
                $ageInMonths = (substr($tdy, 0, 4) * 12) + substr($tdy, 5, 2) -
                    (substr($dob, 0, 4) * 12) - substr($dob, 5, 2);
                $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
                if ($dayDiff < 0) {
                    --$ageInMonths;
                }

                $age = intval($ageInMonths / 12);
            }

            if ($_POST['form_csvexport']) {
                // Configurar las cabeceras para indicar que se enviará un archivo CSV
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="resultados.csv"');

                // Abre el buffer de salida como un recurso de archivo
                $salida = fopen('php://output', 'w');

                // Escribe la cabecera del archivo CSV
                fputcsv($salida, array(
                    'Fecha de Encuentro',
                    'Nombre',
                    'ID Público',
                    'Sexo',
                    'Ciudad',
                    'Fecha de Nacimiento',
                    'Fecha de Primera visita',
                    'OCT',
                    'Edema OD',
                    'Edema OI',
                    'Ojo con edema',
                    'Difuso OD',
                    'Difuso OI',
                    'Quístico OD',
                    'Quístico OI',
                    'EPR OD',
                    'EPR OI',
                    'Exudados OD',
                    'Exudados OI',
                    'Lipídico OD',
                    'Lipídico OI',
                    'Epiretinal OD',
                    'Epiretinal OI',
                    'DR OD',
                    'DR OI',
                    'Fibrosis OD',
                    'Fibrosis OI',
                    'Angio',
                    'Diabetica OD',
                    'Diabetica OI',
                    'Proliferativa OD',
                    'Proliferativa OI',
                    'No Proliferativa OD',
                    'No Proliferativa OI',
                    'Isquemia OD',
                    'Isquemia OI',
                    'Leve OD',
                    'Leve OI',
                    'Moderada OD',
                    'Moderada OI',
                    'Severa OD',
                    'Severa OI',
                    'Opacidad OD',
                    'Opacidad OI',
                    'No val OD',
                    'No val OI',
                    'Nivel de Precio'
                ));

                // Itera sobre cada fila de los resultados
                while ($row = sqlFetchArray($res)) {
                    // Arma los datos de la fila
                    $hasEdemaOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'edema') !== false;
                    $hasEdemaOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'edema') !== false;
                    $hasDifusoOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'difuso') !== false;
                    $hasDifusoOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'difuso') !== false;
                    $hasQuisticoOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'quistico') !== false;
                    $hasQuisticoOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'quistico') !== false;
                    $hasEprOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'epitelio') !== false;
                    $hasEprOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'epitelio') !== false;
                    $hasExudadosOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'exudados') !== false;
                    $hasExudadosOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'exudados') !== false;
                    $hasLipidicoOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'lipidico') !== false;
                    $hasLipidicoOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'lipidico') !== false;
                    $hasDrOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'desprendimiento') !== false;
                    $hasDrOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'desprendimiento') !== false;
                    $hasFibrosisOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'fibrosis') !== false;
                    $hasFibrosisOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'fibrosis') !== false;
                    $hasTraccionOD = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_od'])), 'traccion') !== false;
                    $hasTraccionOI = stripos(text(fetchData($row['pid'], 'LBFoct_mac', ['OCT_oi'])), 'traccion') !== false;
                    $hasDiabeticaOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'diabetica') !== false;
                    $hasDiabeticaOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'diabetica') !== false;
                    $hasProliferativaOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'proliferativa') !== false;
                    $hasProliferativaOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'proliferativa') !== false;
                    $hasNoProliferativaOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'no proliferativa') !== false;
                    $hasNoProliferativaOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'no proliferativa') !== false;
                    $hasIsquemiaOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'isquemia') !== false;
                    $hasIsquemiaOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'isquemia') !== false;
                    $hasLeveOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'leve') !== false;
                    $hasLeveOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'leve') !== false;
                    $hasModeradaOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'moderada') !== false;
                    $hasModeradaOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'moderada') !== false;
                    $hasSeveraOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'severa') !== false;
                    $hasSeveraOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'severa') !== false;
                    $hasOpacidadOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'opacidad') !== false;
                    $hasOpacidadOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'opacidad') !== false;
                    $hasNoValOD = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OD'])), 'no valorable') !== false;
                    $hasNoValOI = stripos(text(fetchData($row['pid'], 'LBFaf', ['Angio_OI'])), 'no valorable') !== false;

                    // Obtener la fecha del último OCT
                    $queryOCT = sqlQuery("SELECT date FROM forms WHERE pid = ? AND formdir = 'LBFoct_mac' AND deleted = 0", array($row['pid']));
                    $lastOCTDate = $queryOCT ? 'Si' : 'No';
                    // Obtener la fecha del último OCT
                    $queryAF = sqlQuery("SELECT date FROM forms WHERE pid = ? AND formdir = 'LBFaf' AND deleted = 0", array($row['pid']));
                    $lastAFDate = $queryAF ? 'Si' : 'No';

                    // Agrega la fila al archivo CSV
                    fputcsv($salida, array(
                        $row['enc_date'],  // Fecha de Encuentro
                        $row['name'],      // Nombre
                        $row['pid'],       // Público
                        $row['sex'],       // Sexo
                        $row['city'],      // Ciudad
                        $row['dob'],       // Fecha de Nacimiento
                        $row['last_oct'],  // Fecha del Último OCT
                        $lastOCTDate,
                        $hasEdemaOD ? 'Sí' : '',
                        $hasEdemaOI ? 'Sí' : '',
                        $hasEdemaOD || $hasEdemaOI ? 'Sí' : '',
                        $hasDifusoOD ? 'Sí' : '',
                        $hasDifusoOI ? 'Sí' : '',
                        $hasQuisticoOD ? 'Sí' : '',
                        $hasQuisticoOI ? 'Sí' : '',
                        $hasEprOD ? 'Sí' : '',
                        $hasEprOI ? 'Sí' : '',
                        $hasExudadosOD ? 'Sí' : '',
                        $hasExudadosOI ? 'Sí' : '',
                        $hasLipidicoOD ? 'Sí' : '',
                        $hasLipidicoOI ? 'Sí' : '',
                        $hasTraccionOD ? 'Sí' : '',
                        $hasTraccionOI ? 'Sí' : '',
                        $hasDrOD ? 'Sí' : '',
                        $hasDrOI ? 'Sí' : '',
                        $hasFibrosisOD ? 'Sí' : '',
                        $hasFibrosisOI ? 'Sí' : '',
                        $lastAFDate,
                        $hasDiabeticaOD ? 'Sí' : '',
                        $hasDiabeticaOI ? 'Sí' : '',
                        $hasProliferativaOD ? 'Sí' : '',
                        $hasProliferativaOI ? 'Sí' : '',
                        $hasNoProliferativaOD ? 'Sí' : '',
                        $hasNoProliferativaOI ? 'Sí' : '',
                        $hasIsquemiaOD ? 'Sí' : '',
                        $hasIsquemiaOI ? 'Sí' : '',
                        $hasLeveOD ? 'Sí' : '',
                        $hasLeveOI ? 'Sí' : '',
                        $hasModeradaOD ? 'Sí' : '',
                        $hasModeradaOI ? 'Sí' : '',
                        $hasSeveraOD ? 'Sí' : '',
                        $hasSeveraOI ? 'Sí' : '',
                        $hasOpacidadOD ? 'Sí' : '',
                        $hasOpacidadOI ? 'Sí' : '',
                        $hasNoValOD ? 'Sí' : '',
                        $hasNoValOI ? 'Sí' : '',
                        $row['price_level']  // Nivel de Precio
                    ));
                }

                // Cierra el buffer de salida
                fclose($salida);

                // Detiene el script para evitar la salida adicional
                exit;
            } else {
                echo '<tr>';
                echo '<td>' . text(oeFormatShortDate(substr($row['edate'], 0, 10))) . '</td>';
                echo '<td><a href="#" onclick="return topatient(\'' . attr($row['pid']) . '\')">' . text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']) . '</a></td>';
                echo '<td>' . text($row['pubpid']) . '</td>';
                echo '<td>' . xlt($row['sex']) . '</td>';
                echo '<td>' . xlt($row['city']) . '</td>';
                echo '<td>' . xlt(oeFormatShortDate($row['DOB'])) . '</td>';
                echo '<td>' . text(oeFormatShortDate(substr($row['fdate'], 0, 10))) . '</td>';

                // Obtener fecha del último OCT
                $queryOCT = sqlQuery("SELECT date FROM forms WHERE pid = ? AND formdir = 'LBFoct_mac' AND deleted = 0", array($row['pid']));
                if ($queryOCT) {
                    foreach ($queryOCT as $result) {
                        echo '<td>Sí</td>';
                    }
                } else {
                    echo '<td>No</td>';
                }

                // Función fetchData
                $pid = $row['pid'];
                $fieldsOCT = ['OCT_od', 'OCT_oi'];
                $fieldsAngio = ['Angio_OD', 'Angio_OI'];

                $conditionsOCT = [
                    'edema',
                    'difuso',
                    'quistico',
                    'epitelio',
                    'exudados',
                    'lipidico',
                    'epiretinal',
                    'desprendimiento',
                    'fibrosis'
                ];

                $conditionsAngio = [
                    'diabetica',
                    'proliferativa',
                    'no proliferativa',
                    'isquemia',
                    'leve',
                    'moderada',
                    'severa',
                    'opacidad',
                    'no valorable'
                ];

                // Verificar condiciones OCT
                foreach ($conditionsOCT as $condition) {
                    foreach ($fieldsOCT as $field) {
                        echo '<td>' . (stripos(text(fetchData($pid, 'LBFoct_mac', [$field])), $condition) !== false ? 'Sí' : '') . '</td>';
                    }
                }

                // Obtener fecha del último OCT
                $queryAF = sqlQuery("SELECT date FROM forms WHERE pid = ? AND formdir = 'LBFaf' AND deleted = 0", array($row['pid']));
                if ($queryAF) {
                    foreach ($queryAF as $result) {
                        echo '<td>Sí</td>';
                    }
                } else {
                    echo '<td>No</td>';
                }

                // Verificar condiciones Angio
                foreach ($conditionsAngio as $condition) {
                    foreach ($fieldsAngio as $field) {
                        echo '<td>' . (stripos(text(fetchData($pid, 'LBFaf', [$field])), $condition) !== false ? 'Sí' : '') . '</td>';
                    }
                }

                echo '<td>' . text($row['pricelevel']) . '</td>';
                echo '</tr>';
            }
            ++$totalpts;
        } // end while
        if (!$_POST['form_csvexport']) {
            ?>

            <tr class="report_totals">
                <td colspan='9'>
                    <?php echo xlt('Total Number of Patients'); ?>
                    :
                    <?php echo text($totalpts); ?>
                </td>
            </tr>

            </tbody>
            </table>
            </div> <!-- end of results -->
            <?php
        } // end not export
    } // end if refresh or export

    if (!$_POST['form_refresh'] && !$_POST['form_csvexport']) {
        ?>
        <div class='text'>
            <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
        </div>
        <?php
    }

    if (!$_POST['form_csvexport']) {
    ?>

</form>
</body>

</html>
<?php
} // end not export
?>
