<?php

function assertContainsText($needle, $haystack, $message)
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, $message . "\nMissing: " . $needle . "\n");
        exit(1);
    }
}

$view = file_get_contents(__DIR__ . '/../interface/forms/eye_mag/view.php');
$spectacleRx = file_get_contents(__DIR__ . '/../interface/forms/eye_mag/SpectacleRx.php');

$lensometryTablePos = strpos($view, '<table id="lensometry">');
$autorefractionTablePos = strpos($view, '<table id="autorefraction">');
$lensometryPrintArea = substr($view, $lensometryTablePos - 500, $autorefractionTablePos - $lensometryTablePos + 500);

assertContainsText(
    "doscript('LM'",
    $lensometryPrintArea,
    'Lensometry print action must open SpectacleRx with REFTYPE=LM.'
);

assertContainsText(
    '$REFTYPE == "LM"',
    $spectacleRx,
    'SpectacleRx must handle Lensometry as a distinct refraction type.'
);
assertContainsText('$ODSPH = $data[\'LMODSPH\'];', $spectacleRx, 'Lensometry OD sphere must feed the prescription printout.');
assertContainsText('$ODCYL = $data[\'LMODCYL\'];', $spectacleRx, 'Lensometry OD cylinder must feed the prescription printout.');
assertContainsText('$ODAXIS = $data[\'LMODAXIS\'];', $spectacleRx, 'Lensometry OD axis must feed the prescription printout.');
assertContainsText('$ODPRISM = $data[\'LMODPRISM\'];', $spectacleRx, 'Lensometry OD prism must feed the prescription printout.');
assertContainsText('$ODADD2 = $data[\'LMODADD\'];', $spectacleRx, 'Lensometry OD add must feed the prescription printout.');
assertContainsText('$OSSPH = $data[\'LMOSSPH\'];', $spectacleRx, 'Lensometry OS sphere must feed the prescription printout.');
assertContainsText('$OSCYL = $data[\'LMOSCYL\'];', $spectacleRx, 'Lensometry OS cylinder must feed the prescription printout.');
assertContainsText('$OSAXIS = $data[\'LMOSAXIS\'];', $spectacleRx, 'Lensometry OS axis must feed the prescription printout.');
assertContainsText('$OSPRISM = $data[\'LMOSPRISM\'];', $spectacleRx, 'Lensometry OS prism must feed the prescription printout.');
assertContainsText('$OSADD2 = $data[\'LMOSADD\'];', $spectacleRx, 'Lensometry OS add must feed the prescription printout.');

echo "eye_mag_lensometry_rx_test passed\n";
