<?php
require_once("identifiers.php");
require_once("oct_angio.php");
require_once("fluorescein.php");
require_once("derived_fields.php");
require_once("fundus.php");
require_once("utils_rd.php");


header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=estudio_diabetes.csv");

$output = fopen('php://output', 'w');

// ENCABEZADOS (ajusta según tus columnas reales)
$headers = [
    'ID Unico', 'Last Visit', 'Patient', 'ID', 'Sex', 'City', 'Edad', 'DOB', 'Primera Visita', 'Primer OCT',
    'Edema OD', 'Edema OI', 'Difuso OD', 'Difuso OI', 'Quístico OD', 'Quístico OI',
    'EPR OD', 'EPR OI', 'Exudados OD', 'Exudados OI', 'Lipídico OD', 'Lipídico OI',
    'Epiretinal OD', 'Epiretinal OI', 'DR OD', 'DR OI', 'Fibrosis OD', 'Fibrosis OI',
    'TM mácula OD', 'TM mácula OI',
    'Primer AF', 'Diabetica OD', 'Diabetica OI', 'Proliferativa OD', 'Proliferativa OI',
    'No Proliferativa OD', 'No Proliferativa OI', 'Isquemia OD', 'Isquemia OI',
    'Leve OD', 'Leve OI', 'Moderada OD', 'Moderada OI', 'Severa OD', 'Severa OI',
    'Opacidad OD', 'Opacidad OI', 'No val OD', 'No val OI', 'Fondo de ojo',
    'Edema macular OD', 'Edema macular OI',
    'Exudados mácula OD', 'Exudados mácula OI',
    'DMAE húmeda OD', 'DMAE húmeda OI',
    'DMAE seca OD', 'DMAE seca OI',
    'Brillo foveal conservado OD', 'Brillo foveal conservado OI',
    'Hemorragia mácula OD', 'Hemorragia mácula OI',
    'Fibrosis mácula OD', 'Fibrosis mácula OI',
    'Mácula no valorable OD', 'Mácula no valorable OI',
    'Mácula normal OD', 'Mácula normal OI',
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
    'Hemorragia vítrea OD', 'Hemorragia vítrea OI',
    'Opacidades vítreas OD', 'Opacidades vítreas OI',
    'Desprendimiento vítreo OD', 'Desprendimiento vítreo OI',
    'Hialosis asteroidea OD', 'Hialosis asteroidea OI',
    'Aceite silicón OD', 'Aceite silicón OI',
    'Vítreo normal OD', 'Vítreo normal OI',
    'Vítreo no valorable OD', 'Vítreo no valorable OI',
    'Desprendimiento retina OD', 'Desprendimiento retina OI',
    'Degeneración retinal OD', 'Degeneración retinal OI',
    'Hemorragias periféricas OD', 'Hemorragias periféricas OI',
    'Atrofia retinal OD', 'Atrofia retinal OI',
    'Lesiones periféricas OD', 'Lesiones periféricas OI',
    'Láser periférico OD', 'Láser periférico OI',
    'Cuadrantes afectados OD', 'Cuadrantes afectados OI',
    'Periferia normal OD', 'Periferia normal OI',
    'Periferia no valorable OD', 'Periferia no valorable OI',
    'Pricelevel',
    'Tipo RD OD', 'ETDRS OD', 'Tipo RD OI', 'ETDRS OI',
    'Tipo RD integral',
    'ETDRS integral',
];
fputcsv($output, $headers);

// Consulta de pacientes
$sql = "SELECT
    p.fname, p.mname, p.lname, p.sex, p.city, p.DOB, p.pricelevel,
    p.phone_home, p.phone_biz, p.phone_cell, p.phone_contact, p.pid, p.pubpid,
    COUNT(e.date) AS ecount, MAX(e.date) AS edate, MIN(e.date) AS fdate
  FROM
    patient_data AS p
  JOIN
    form_eye_mag_impplan AS i ON p.pid = i.pid AND i.code = 'H360'
  LEFT OUTER JOIN
    form_encounter AS e ON e.pid = p.pid
  GROUP BY p.lname, p.fname, p.mname, p.pid ORDER BY p.lname, p.fname, p.mname, p.pid DESC";
// --- Función para obtener todos los datos RD de un paciente ---
function obtenerDatosCompletosRD($pid)
{

    $macula = extraerVariablesMacula($pid);
    $vessels = extraerVariablesVessels($pid);
    $vitreo = extraerVariablesVitreous($pid);
    $periferia = extraerVariablesPeriph($pid);
    $rd = determinarTipoRD($pid);

    return array_merge(
        array_values($macula),
        array_values($vessels),
        array_values($vitreo),
        array_values($periferia),
        [
            $rd['pricelevel'] ?? '',
            $rd['tipo_od'] ?? '',
            $rd['etdrs_od'] ?? '',
            $rd['tipo_oi'] ?? '',
            $rd['etdrs_oi'] ?? '',
            $rd['tipo'] ?? '',
            $rd['etdrs'] ?? ''
        ]
    );
}

$res = sqlStatement($sql);

while ($row = sqlFetchArray($res)) {
    $pid = $row['pid'];

    // Datos derivados
    $rd = determinarTipoRD($pid); // Esta debe ser tu función integrada

    $edad = calcularEdad($row['DOB']);
    $dob = $row['DOB'];
    $etdrs_od = $rd['etdrs_od'] ?? '';
    $etdrs_oi = $rd['etdrs_oi'] ?? '';
    $etdrs = $rd['etdrs'] ?? '';

    $datos_completos = obtenerDatosCompletosRD($pid); // Esta función debería devolver un array en el mismo orden que los headers adicionales

    $linea = array_merge([
        $pid,
        $row['edate'] ?? '',
        $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'],
        $row['pubpid'],
        $row['sex'],
        $row['city'],
        $edad,
        $dob,
        $row['fdate'] ?? '',
        obtenerPrimerOCT($pid), // Suponiendo que esta función ya existe
    ], $datos_completos);

    fputcsv($output, $linea);
}
fclose($output);
exit;

function calcularEdad($dob)
{
    if (!$dob) return '';
    $dob = new DateTime($dob);
    $now = new DateTime();
    return $now->diff($dob)->y;
}

function obtenerPrimerOCT($pid) {
    $sql = "SELECT MIN(date) as primer_oct FROM forms WHERE pid = ? AND formdir = 'LBFoct_mac' AND deleted = 0";
    $res = sqlQuery($sql, [$pid]);
    return $res['primer_oct'] ?? '';
}
