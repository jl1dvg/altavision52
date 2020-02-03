<?php
require_once "../globals.php";



ob_start();
require_once 'Recordtemplate.php';
$html = ob_get_clean();

$mpdf = new mPDF('es', 'A4-L', '', '5', '5');
$mpdf->writeHTML($html);
$mpdf->Output('consentimiento.pdf', 'I');
?>
