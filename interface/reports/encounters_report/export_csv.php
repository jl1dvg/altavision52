<?php
// Incluir la configuración necesaria y la conexión a la base de datos
require_once 'path/to/your/configuration/file.php';

function cleanData(&$str)
{
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

// Get the form parameters (adjust as needed)
$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_provider = $_POST['form_provider'];
$form_operador = $_POST['form_operador'];
$form_facility = $_POST['form_facility'];
$form_pricelevel = $_POST['form_pricelevel'];
$form_apptcat = $_POST['form_apptcat'];
$form_details = $_POST['form_details'] ? true : false;
$form_new_patients = $_POST['form_new_patients'] ? true : false;
$form_esigned = $_POST['form_esigned'] ? true : false;
$form_not_esigned = $_POST['form_not_esigned'] ? true : false;
$form_encounter_esigned = $_POST['form_encounter_esigned'] ? true : false;

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

$esign_fields = '';
$esign_joins = '';
if ($form_encounter_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = fe.encounter ";
}

if ($form_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = fe.encounter ";
}

if ($form_not_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT JOIN esign_signatures AS es on es.tid = fe.encounter ";
}

$sqlBindArray = array();
$query = "SELECT " .
    "fe.encounter, fe.date, fe.reason, fe.pc_catid, fe.responsable_id, " .
    "f.formdir, f.form_name, f.provider_id, " .
    "p.fname, p.mname, p.lname, p.lname2, p.pid, p.pubpid, p.pricelevel,  " .
    "u.lname AS ulname, u.fname AS ufname, u.mname AS umname, " .
    "ca.pc_catdesc " .
    "$esign_fields" .
    "FROM ( form_encounter AS fe, forms AS f ) " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
    "LEFT JOIN openemr_postcalendar_categories AS ca ON ca.pc_catid = fe.pc_catid " .
    "LEFT JOIN users AS u ON u.id = fe.provider_id " .
    "$esign_joins" .
    "WHERE f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' ";
if ($form_to_date) {
    $query .= "AND fe.date >= ? AND fe.date <= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
} else {
    $query .= "AND fe.date >= ? AND fe.date <= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_from_date . ' 23:59:59');
}

if ($form_provider) {
    $query .= "AND fe.provider_id = ? ";
    array_push($sqlBindArray, $form_provider);
}

if ($form_operador) {
    $query .= "AND f.provider_id = ? ";
    array_push($sqlBindArray, $form_operador);
}

if ($form_facility) {
    $query .= "AND fe.facility_id = ? ";
    array_push($sqlBindArray, $form_facility);
}

if ($form_pricelevel) {
    $query .= "AND p.pricelevel = ? ";
    array_push($sqlBindArray, $form_pricelevel);
}

if ($form_apptcat) {
    $query .= "AND fe.pc_catid = ? ";
    array_push($sqlBindArray, $form_apptcat);
}

if ($form_new_patients) {
    $query .= "AND fe.date = (SELECT MIN(fe2.date) FROM form_encounter AS fe2 WHERE fe2.pid = fe.pid) ";
}

if ($form_encounter_esigned) {
    $query .= "AND es.tid = fe.encounter AND es.table = 'form_encounter' ";
}

if ($form_esigned) {
    $query .= "AND es.tid = fe.encounter ";
}

if ($form_not_esigned) {
    $query .= "AND es.tid IS NULL ";
}

$query .= "ORDER BY $orderby";

$res = sqlStatement($query, $sqlBindArray);

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=encounters_report.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('Provider', 'Date', 'Patient', 'ID', 'Status', 'Encounter', 'Encounter Number', 'Form', 'Operador', 'Responsable', 'Coding'));

while ($row = sqlFetchArray($res)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
