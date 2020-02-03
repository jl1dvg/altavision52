<!DOCTYPE html>
<?php
require_once "../globals.php";
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";

$fecha1 = $_GET['from']; // Obviamente se cambia por $_POST['fecha1'];
$FROM = date('Y/m/d', strtotime(str_replace('/', '-', $fecha1)));
$fecha2 = $_GET['to']; // Obviamente se cambia por $_POST['fecha1'];
$TO = date('Y/m/d', strtotime(str_replace('/', '-', $fecha2)));
$medico = $_GET['provider'];
$Query = sqlStatement("SELECT * FROM `openemr_postcalendar_events` AS `ope`
                       LEFT JOIN `patient_data` AS `pd` ON (`ope`.`pc_pid` = `pd`.`id`)
                       WHERE  `ope`.`pc_eventDate` BETWEEN '$FROM' AND '$TO' AND `ope`.`pc_aid` LIKE '%$medico%'
                       ORDER BY `ope`.`pc_eventDate` ASC");
$i='0';


?>
<HTML>
  <HEAD>
  	<style>
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
  	</style>
  </HEAD>
<BODY>
<table border="1" width="100%" style="border-collapse: collapse;">
<tr>
  <td colspan="3">
    <?php
    
    echo $fecha1 . "\n";
    
    ?>
  </td>
</tr>
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
      'title'         => $ip_list['pc_title'],
      'hometext'      => $ip_list['pc_hometext'],
      'cirugia'      => $ip_list['pc_apptqx'],
      'cirugiaOI'      => $ip_list['pc_apptqxOI'],
      'Event'         => $ip_list['pc_eventDate'],
      'provider'      => $ip_list['pc_aid'],
      );
      $calendario[$fechaEvent] = $newdata;
      $cirujano[$provider] = $newdata;
      $PARTE_items[$i] =$newdata;
      $i++;
  }
  
  foreach ($calendario as $key2) {
    echo "<tr><td colspan='5' align='center'><h2>";
    echo text($key2['Event']);
    echo "</h2></td></tr>";
  foreach ($cirujano as $key1) {
    if ($key2['Event'] == $key2['Event']) {
    echo "<tr><td colspan='5' align='center'><h3>";
    echo getProviderName($key1['provider']);
    echo "</h3></td></tr>";
      foreach ($PARTE_items as $key) {
      if ($key2['Event'] == $key['Event'] && $key1['provider'] == $key['provider']) {
      echo "<tr><td>";
      echo text($key['lname']) . " " . text($key['lname2']) . ", " . text($key['fname']) . " " . text($key['mname']);
      echo "</td>";
      echo "<td>";
      echo text($key['title']);
      echo "</td>";
      echo "<td>";
      echo text($key['hometext']);
      echo "</td>";
      echo "<td>";
      if ($key['cirugia']) {
        echo text($key['cirugia']);
      }
      if ($key['cirugia'] && $key['cirugiaOI']) {
        echo "<br />";
      }
      if ($key['cirugiaOI']) {
        echo text($key['cirugiaOI']);
      }
      echo "</td>";
      echo "<td>";
      if ($key['cirugia']) {
        echo "OD";
      }
      if ($key['cirugia'] && $key['cirugiaOI']) {
        echo "<br />";
      }
      if ($key['cirugiaOI']) {
        echo "OI";
      }
      echo "</td></tr>";
    }
    }
  }
  }
}

  ?>

</table>
<?php  ?>
<pre>


</pre>
</BODY>
</HTML>
