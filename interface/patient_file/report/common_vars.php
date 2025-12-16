<?php
require_once("$srcdir/iess.inc.php");
require_once("$srcdir/report.inc");

if (!isset($key) || empty($key)) {
    die('Error: la variable $key no está definida o está vacía.');
}
preg_match('/^(.*)_(\d+)$/', $key, $res);
if (!isset($res[2])) {
    die('Error: el formato de $key es incorrecto. Se esperaba "formdir_formid".');
}
$formdir = $res[1];
$form_id = $res[2];
// ...

$providerID = getProviderIdOfEncounter($form_encounter);
$providerNAME = getProviderNameConcat($providerID);

$resultado = getProtocolDate($form_id, $form_encounter);

if ($resultado) {
    $dateddia = $resultado['dia'];
    $datedmes = $resultado['mes'];
    $datedano = $resultado['ano'];
} else {
    // Manejo de error o valores por defecto
}

$codes = getCPT4Codes($titleres['pricelevel'], $form_id);

$sistolica = rand(110, 130);
$diastolica = rand(70, 85);
$fc = rand(75, 105);
$fr = rand(15, 22);
$spo2 = rand(95, 100);
$glucosa = rand(70, 110);
$temperatura = rand(36, 37);

$proced_id = getFieldValue($form_id, "Prot_opp");
$resultadoDX = obtenerCodigosImpPlan($pid, $form_encounter);

// Función para obtener el valor más cercano del array
function getClosest($num, $array)
{
    $closest = $array[0];
    foreach ($array as $value) {
        if (abs($num - $value) < abs($num - $closest)) {
            $closest = $value;
        }
    }
    return $closest;
}

?>
