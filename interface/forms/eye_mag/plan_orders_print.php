<?php

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");
require_once(__DIR__ . "/php/eye_mag_functions.php");

$pid = $_POST['pid'] ?? $_GET['pid'] ?? null;
$encounter = $_POST['encounter'] ?? $_GET['encounter'] ?? null;
$formId = $_POST['form_id'] ?? $_GET['form_id'] ?? null;
$postedVisitDate = $_POST['visit_date'] ?? $_GET['visit_date'] ?? '';
$postedProviderName = trim($_POST['provider_name'] ?? $_GET['provider_name'] ?? '');
$postedProviderSuffix = trim($_POST['provider_suffix'] ?? $_GET['provider_suffix'] ?? '');
$planTitles = $_POST['plan_titles'] ?? array();
$planSubtypes = $_POST['plan_subtypes'] ?? array();
$planNotes = $_POST['plan_notes'] ?? array();
$planEyes = $_POST['plan_eyes'] ?? array();
$freeText = trim($_POST['free_text'] ?? $_GET['free_text'] ?? '');

if (!$pid || !$encounter) {
    die(xlt('Missing patient or encounter information.'));
}

$query = "SELECT form_encounter.provider_id, form_encounter.date AS encounter_date
    FROM forms
    INNER JOIN form_encounter ON forms.encounter = form_encounter.encounter
    WHERE forms.deleted != '1'
      AND forms.formdir = 'eye_mag'
      AND forms.pid = ?
      AND forms.encounter = ?";

$params = array($pid, $encounter);
if (!empty($formId)) {
    $query .= " AND forms.form_id = ?";
    $params[] = $formId;
}
$query .= " LIMIT 1";

$encounterData = sqlQuery($query, $params);
$providerId = $encounterData['provider_id'] ?? null;
$visit_date = $postedVisitDate ?: ($encounterData['encounter_date'] ?? null);
$prov_data = array(
    'fname' => $postedProviderName,
    'lname' => '',
    'suffix' => $postedProviderSuffix
);
if ($providerId && $postedProviderName === '') {
    $prov_data = sqlQuery("SELECT * FROM users WHERE id = ?", array($providerId));
}

$groupedPlans = array();
foreach ($planTitles as $index => $planTitle) {
    $planTitle = trim($planTitle);
    if ($planTitle === '') {
        continue;
    }

    $subtype = trim($planSubtypes[$index] ?? '');
    $notes = trim($planNotes[$index] ?? '');
    $eye = eyeMagNormalizeOrderEye($planEyes[$index] ?? '');
    $bucket = $subtype ?: xlt('General');

    if (empty($groupedPlans[$bucket])) {
        $groupedPlans[$bucket] = array();
    }

    $groupedPlans[$bucket][] = array(
        'title' => $planTitle,
        'notes' => $notes,
        'eye' => $eye
    );
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('Exam Orders'); ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            margin: 24px;
        }

        .document-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            margin: 8px 0 18px;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin: 18px 0 8px;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }

        ul.plan-list {
            margin: 0;
            padding-left: 22px;
        }

        ul.plan-list li {
            margin: 0 0 8px;
            line-height: 1.4;
        }

        .plan-note {
            display: block;
            font-size: 12px;
            color: #444;
            margin-top: 2px;
        }

        .free-text {
            margin-top: 22px;
            padding: 10px 12px;
            border: 1px solid #999;
            white-space: pre-wrap;
        }

        .signature-block {
            margin-top: 42px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 280px;
            margin: 0 auto 8px;
            padding-top: 8px;
        }

        .signature-image {
            width: 240px;
            height: 85px;
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
        }
    </style>
</head>
<body onload="window.print()">
<?php echo rx_header($pid, "web"); ?>

<div class="document-title"><?php echo xlt('Solicitud de exámenes y autorizaciones'); ?></div>

<?php if (empty($groupedPlans) && $freeText === '') { ?>
    <p><?php echo xlt('There are no selected exams to print.'); ?></p>
<?php } ?>

<?php foreach ($groupedPlans as $subtype => $items) { ?>
    <div class="section-title"><?php echo text($subtype); ?></div>
    <ul class="plan-list">
        <?php foreach ($items as $item) { ?>
            <li>
                <?php echo text($item['title']); ?>
                <?php if (!empty($item['eye'])) { ?>
                    <strong><?php echo ' (' . xlt('Eye') . ': ' . text($item['eye']) . ')'; ?></strong>
                <?php } ?>
                <?php if (!empty($item['notes']) && mb_strtolower($item['notes']) !== mb_strtolower($item['title'])) { ?>
                    <span class="plan-note"><?php echo text($item['notes']); ?></span>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
<?php } ?>

<?php if ($freeText !== '') { ?>
    <div class="section-title"><?php echo xlt('Additional Notes'); ?></div>
    <div class="free-text"><?php echo text($freeText); ?></div>
<?php } ?>

<div class="signature-block">
    <?php
    $signature = $GLOBALS["webserver_root"] . "/interface/forms/eye_mag/images/sign_" . attr($_SESSION['authUserID']) . ".jpg";
    if (file_exists($signature)) {
        ?>
        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sign_<?php echo attr($_SESSION['authUserID']); ?>.jpg"
             alt="Firma"
             class="signature-image">
    <?php } else { ?>
        <div class="signature-line"></div>
    <?php } ?>

    <div>
        <?php echo xlt('Provider'); ?>:
        <?php
        $providerDisplayName = trim(($prov_data['fname'] ?? '') . ' ' . ($prov_data['lname'] ?? ''));
        echo text($providerDisplayName);
        ?>
        <?php if (!empty($prov_data['suffix'])) {
            echo ', ' . text($prov_data['suffix']);
        } ?>
    </div>
</div>
</body>
</html>
