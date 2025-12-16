<?php
function render_identifiers($row) {
    $id_unico = $row['pid'] . date('Ymd', strtotime($row['edate']));
    $age = calcular_edad($row['DOB'], $row['edate']);
    return
        '<td>' . text($id_unico) . '</td>' .
        '<td>' . text(oeFormatShortDate(substr($row['edate'], 0, 10))) . '</td>' .
        '<td><a href="#" onclick="return topatient(\'' . attr($row['pid']) . '\')">'
        . text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']) . '</a></td>' .
        '<td>' . text($row['pubpid']) . '</td>' .
        '<td>' . xlt($row['sex']) . '</td>' .
        '<td>' . xlt($row['city']) . '</td>' .
        '<td>' . $age . '</td>' .
        '<td>' . text(oeFormatShortDate(substr($row['DOB'], 0, 10))) . '</td>' .
        '<td>' . text(oeFormatShortDate(substr($row['fdate'], 0, 10))) . '</td>';
}
function calcular_edad($dob, $tdy) {
    $ageInMonths = (substr($tdy, 0, 4) * 12) + substr($tdy, 5, 2) -
        (substr($dob, 0, 4) * 12) - substr($dob, 5, 2);
    $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
    if ($dayDiff < 0) --$ageInMonths;
    return intval($ageInMonths / 12);
}
?>
