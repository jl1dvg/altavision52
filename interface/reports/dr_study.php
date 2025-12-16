<?php

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("rd_study/utils_rd.php");
require_once("rd_study/identifiers.php");
require_once("rd_study/oct_angio.php");
require_once("rd_study/fluorescein.php");
require_once("rd_study/derived_fields.php");
require_once("rd_study/fundus.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Verificación del token CSRF
if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Exportación a CSV: Se debe hacer antes de cualquier salida HTML
if ($_POST['form_csvexport']) {
    require_once 'rd_study/datos_a_csv.php'; // Este archivo debe generar el CSV completo
    exit;
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

// (la exportación a CSV ahora se realiza antes de cualquier salida HTML)
{
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

        .oct-angio {
            background-color: #e0f3fa !important; /* azul clarito, o el color que prefieras */
        }

        .af-fluor {
            background-color: #fff7e0 !important; /* amarillo suave, puedes cambiarlo */
        }

        .rd-derived {
            background-color: #f4e2ff !important; /* lila suave, cambia el color si prefieres */
        }

        .fondo-ojo {
            background-color: #e8ffe8 !important; /* Verde clarito, cambia a tu gusto */
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
                'ID Unico', 'Last Visit', 'Patient', 'ID', 'Sex', 'City', 'Edad', 'DOB', 'Primera Visita', 'Primer OCT',
                'Edema OD', 'Edema OI', 'Difuso OD', 'Difuso OI', 'Quístico OD', 'Quístico OI',
                'EPR OD', 'EPR OI', 'Exudados OD', 'Exudados OI', 'Lipídico OD', 'Lipídico OI',
                'Epiretinal OD', 'Epiretinal OI', 'DR OD', 'DR OI', 'Fibrosis OD', 'Fibrosis OI',
                // Agregar columnas para TM mácula OD y OI
                'TM mácula OD', 'TM mácula OI',
                'Primer AF', 'Diabetica OD', 'Diabetica OI', 'Proliferativa OD', 'Proliferativa OI',
                'No Proliferativa OD', 'No Proliferativa OI', 'Isquemia OD', 'Isquemia OI',
                'Leve OD', 'Leve OI', 'Moderada OD', 'Moderada OI', 'Severa OD', 'Severa OI',
                'Opacidad OD', 'Opacidad OI', 'No val OD', 'No val OI', 'Fondo de ojo',
                // Variables para mácula
                'Edema macular OD', 'Edema macular OI',
                'Exudados mácula OD', 'Exudados mácula OI',
                'DMAE húmeda OD', 'DMAE húmeda OI',
                'DMAE seca OD', 'DMAE seca OI',
                'Brillo foveal conservado OD', 'Brillo foveal conservado OI',
                'Hemorragia mácula OD', 'Hemorragia mácula OI',
                'Fibrosis mácula OD', 'Fibrosis mácula OI',
                'Mácula no valorable OD', 'Mácula no valorable OI',
                'Mácula normal OD', 'Mácula normal OI',

                // Variables para vessels
                'Retinopatía diabética OD', 'Retinopatía diabética OI',
                'Retinopatía no diabética OD', 'Retinopatía no diabética OI',
                'Proliferativa OD', 'Proliferativa OI',
                'Severa OD', 'Severa OI',
                'Moderada OD', 'Moderada OI',
                'Leve OD', 'Leve OI',
                'Calibre alterado OD', 'Calibre alterado OI',
                'Fotocoagulación láser OD', 'Fotocoagulación láser OI',
                'Neovascularización OD', 'Neovascularización OI',
                'Microaneurismas OD', 'Microaneurismas OI',
                'Hemorragias vasos OD', 'Hemorragias vasos OI',
                'Exudados vasos OD', 'Exudados vasos OI',
                'Vessels normal OD', 'Vessels normal OI',

                // Variables para vítreo
                'Hemorragia vítrea OD', 'Hemorragia vítrea OI',
                'Opacidades vítreas OD', 'Opacidades vítreas OI',
                'Desprendimiento vítreo OD', 'Desprendimiento vítreo OI',
                'Hialosis asteroidea OD', 'Hialosis asteroidea OI',
                'Aceite silicón OD', 'Aceite silicón OI',
                'Vítreo normal OD', 'Vítreo normal OI',
                'Vítreo no valorable OD', 'Vítreo no valorable OI',

                // Variables para periferia
                'Desprendimiento retina OD', 'Desprendimiento retina OI',
                'Degeneración retinal OD', 'Degeneración retinal OI',
                'Hemorragias periféricas OD', 'Hemorragias periféricas OI',
                'Atrofia retinal OD', 'Atrofia retinal OI',
                'Lesiones periféricas OD', 'Lesiones periféricas OI',
                'Láser periférico OD', 'Láser periférico OI',
                'Cuadrantes afectados OD', 'Cuadrantes afectados OI',
                'Periferia normal OD', 'Periferia normal OI',
                'Periferia no valorable OD', 'Periferia no valorable OI',
                // Variables para retinopatía diabética integral
                'Pricelevel',
                'Tipo RD OD', 'ETDRS OD', 'Tipo RD OI', 'ETDRS OI',
                'Tipo RD integral',
                'ETDRS integral',
            ];
            // --- INICIO: Nueva función para determinar el tipo de retinopatía diabética integral ---
            /**
             * Determina el tipo de retinopatía diabética integral para un paciente,
             * integrando hallazgos de AF (LBFaf) y OCT (LBFoct_mac, incluyendo TM mácula).
             * @param int $pid
             * @return string
             */

            // --- FIN: Nueva función determinarTipoRD integral ---

            foreach ($headers as $header) {
                echo '<th>' . xlt($header) . '</th>';
            }

            echo '</thead><tbody>';
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
                // (La exportación CSV ahora se maneja antes de la salida HTML)
            } else {
                echo '<tr>';
                // --- ID Unico: pid + edate (YYYYMMDD) ---
                echo render_identifiers($row);

                $pid = $row['pid'];
                echo render_oct_angio($pid);

                // Buscar si existe al menos un formulario LBFaf (AF)
                echo render_fluorescein($pid);

                echo render_fundus($pid);

                echo '<td>' . text($row['pricelevel']) . '</td>';
                // Añadir columnas de "Tipo RD OD" y "Tipo RD OI"
                // (mantener por compatibilidad)
                echo render_derived_fields($pid);
            }
            ++$totalpts;
        } // end foreach
        if (!$_POST['form_csvexport']) {
            ?>

            <tr class="report_totals">
                <td colspan='9'>
                    <?php echo xlt('Total Number of Patients'); ?>
                    :
                    <?php echo text($totalpts);
                    ?>
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
