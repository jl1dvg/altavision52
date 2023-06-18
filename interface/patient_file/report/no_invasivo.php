<!DOCTYPE HTML>
<?php
require_once("$srcdir/iess.inc.php");
$facility = null;
if ($_SESSION['pc_facility']) {
    $facility = $facilityService->getById($_SESSION['pc_facility']);
} else {
    $facility = $facilityService->getPrimaryBillingLocation();
}

$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
$logo = "<img src='$web_root/$ma_logo_path' style='height:" . attr(round(9 * 7.50)) . "pt' />";

preg_match('/^(.*)_(\d+)$/', $key, $res);
$form_id = $res[2];

$carePlanDetails = getCarePlanDetails($pid, $form_id);
$fechaProcedimiento = $carePlanDetails['FechaProcedimiento'];
$mesesEnIngles = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$mesesEnEspanol = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

$fechaCompleta = date('d', strtotime($fechaProcedimiento)) . ' de ' . $mesesEnEspanol[date('n', strtotime($fechaProcedimiento)) - 1] . ' de ' . date('Y', strtotime($fechaProcedimiento));
$codigoProcedimiento = $carePlanDetails['CodigoProcedimiento'];
$ojoProcedimiento = $carePlanDetails['OjoProcedimiento'];
$procedimiento = $carePlanDetails['Procedimiento'];
$medicoProcedimiento = $carePlanDetails['MedicoProcedimiento'];
$providerID = getProviderIdOfEncounter($encounter);
?>
<html>
<body>
<page_header>
    <table>
        <tr>
            <td>
            <span class="sd-abs-pos" style="position: absolute; top: -0.67in; left: 1.81in; width: 249px">
                <?php echo $logo; ?>
            </span>
            </td>
            <td>
                <h2><?php echo $facility['name']; ?></h2>
                <p class="texto">
                    <?php echo $facility['street']; ?><br>
                    <?php echo $facility['city']; ?>
                    , <?php echo $facility['country_code']; ?> <?php echo $facility['postal_code']; ?><br>
                    <b>Telfs: </b><?php echo $facility['phone']; ?><br>
                    <b>E-mail: </b><?php echo $facility['email']; ?>
                </p>
            </td>
        </tr>
    </table>
    <hr>
</page_header>
<P style="text-align: center"><B>INFORME PROCEDIMIENTO</B></FONT></P>
<P style="text-align: justify">
    <B>Fecha</B>: <?php echo $fechaCompleta; ?><br>
    <B>Paciente:</B> <?php echo text($titleres['fname'] . " " . $titleres['lname'] . " " . $titleres['lname2']); ?><br>
    <B>Ojo:</B> <?php echo ucfirst($ojoProcedimiento); ?><br>
    <B>Procedimiento:</B> <?php echo ucfirst($procedimiento); ?>.
    (COD. <?php echo $codigoProcedimiento; ?>)<br>
</P><br><br><br>
<p style="text-align: justify">
    <?php
    echo generateProcedureDescription($codigoProcedimiento, $ojoProcedimiento);
    ?>
</p>
<br>
<p style="text-align: justify">
    Atentamente,
</p>
<br><br><br><br><br>
<P style="text-align: justify">
    <B>
        <?php
        echo getProviderName($providerID);
        ?>
        <br>
        <?php
        echo getProviderEspecialidad($providerID);
        ?>
        <br>
        Centro Oftalmol&oacute;gico AltaVisi&oacute;n
        <br>
        Guayaquil &ndash; Ecuador</B></P>
</body>
</html>
