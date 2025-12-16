<?php
require_once("common_vars.php");
include("common_header.php");

generatePageHeader($facilityService, $web_root);

?>
<!-- Resto del contenido de egreso_template.php -->
<p style="text-align: center"><b>PLAN DE EGRESO</b></p>
<p><b>CIRUGIA OCULAR</b></p>
<p><b>Diagn&oacute;stico de egreso: </b>
    <?php echo lookup_code_short_descriptions(getFieldValue($form_id, "Prot_dxpost")); ?>
</p>
<p><b>Fecha: </b>
    <?php
    echo $dateddia . " de " . $datedmes . " del " . $datedano;
    ?>
</p>
<p><b>Egresado a: </b>Casa</p>
<p>Instrucciones para el
    paciente <?php echo text(xlt($titleres['title']) . " " . $titleres['fname'] . " " . $titleres['lname']); ?> y
    familia:</p>
<p>MEDICAMENTOS RECETADOS: <u><b>Tobracort (Tobramicina+Dexametazona) 1 gota cada 3 horas por 21
            d&iacute;as</b></u><u>.</u></p>
<p>ACTIVIDAD: Se debe mantener reposo en la postura de acuerdo a la indicaci&oacute;n del m&eacute;dico.</p>
<p>HIGIENE: Debe ba&ntilde;arse el cuerpo con agua y jab&oacute;n incluyendo la cara.</p>
<p>ALIMENTACI&Oacute;N: No hay restricci&oacute;n de dieta. Evite fumar o tomar alcohol hasta que est&eacute;
    completamente recuperado.</p>
<p>CUIDADOS ESPECIALES: Mantenga parche y protector ocular durante 24 horas, seg&uacute;n prescripci&oacute;n m&eacute;dica.
    Controle sangrado (Observe si mancha la gasa).</p>
<p>EDUCACION AL PACIENTE: Pueden sentir picor, sensaci&oacute;n de cuerpo extra&ntilde;o, pinchazos espor&aacute;dicos:
    Son consecuencia de los punto conjuntivales.</p>
<p>Cumpla con el tratamiento ambulatorio ya sea con colirios o pomadas de acuerdo a la prescripci&oacute;n de su m&eacute;dico.</p>
<p>Un paciente sometido a cirug&iacute;a ocular <u><b>NO DEBE</b></u> en ning&uacute;n caso: Conducir, realizar
    actividades peligrosas, ni levantar pesos.</p>
<p>La lectura y la televisi&oacute;n no est&aacute;n contraindicadas, excepto si producen molestias o impiden la posici&oacute;n
    recomendada.</p>
<p>OTROS:</p>
<p><u><b>INFORME DE EGRESO DE ENFERMERIA</b></u>:</p>
<p>PACIENTE EGRESA EN CONDICIONES FAVORABLES PARA SU SALUD, CON INDICACIONES MEDICA, SI LLEVA LA MEDICACI&Oacute;N.</p>
<br><br><br><br><br>
<p><b>M&eacute;dico tratante: </b><?php echo getProviderNameConcat($providerID); ?>
    <b>Tel&eacute;fono: </b>2286080
</p>
