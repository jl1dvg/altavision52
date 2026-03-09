<?php

require_once '../../globals.php';
require_once "$srcdir/patient.inc";
require_once "$srcdir/MedEx/API.php";
require_once "$srcdir/../../library/WhatsAppMetaService.php";

use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

$service = new WhatsAppMetaService();
$action = $_REQUEST['action'] ?? '';

if ($action === 'search_patient') {
    $term = trim($_GET['term'] ?? '');
    $param = '%' . $term . '%';
    $results = array();
    if ($term !== '') {
        $query = "SELECT pid, fname, lname, phone_cell, hipaa_allowsms FROM patient_data WHERE fname LIKE ? OR lname LIKE ? LIMIT 20";
        $stmt = sqlStatement($query, array($param, $param));
        while ($row = sqlFetchArray($stmt)) {
            $results[] = array(
                'label' => trim($row['fname'] . ' ' . $row['lname']),
                'pid' => $row['pid'],
                'mobile' => $row['phone_cell'],
                'allow' => $row['hipaa_allowsms']
            );
        }
    }
    echo json_encode($results);
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_REQUEST['csrf_token_form'] ?? '')) {
    echo json_encode(array('ok' => false, 'error' => 'csrf'));
    exit;
}

if ($action === 'list') {
    $pid = (int)($_REQUEST['pid'] ?? 0);
    $rows = array();
    if ($pid > 0) {
        $sql = "SELECT msg_uid, msg_date, msg_reply, msg_extra_text FROM medex_outgoing WHERE msg_pid = ? AND msg_type = 'WHATSAPP' ORDER BY msg_date ASC LIMIT 200";
        $stmt = sqlStatement($sql, array($pid));
        while ($row = sqlFetchArray($stmt)) {
            $extra = json_decode($row['msg_extra_text'], true);
            $rows[] = array(
                'id' => $row['msg_uid'],
                'date' => $row['msg_date'],
                'direction' => $row['msg_reply'],
                'message' => $extra['message'] ?? '',
                'media_type' => $extra['media_type'] ?? '',
                'media_name' => $extra['media_name'] ?? ''
            );
        }
    }
    echo json_encode(array('ok' => true, 'items' => $rows));
    exit;
}

if ($action === 'send') {
    if (!$service->isConfigured()) {
        echo json_encode(array('ok' => false, 'error' => 'Meta WhatsApp no configurado'));
        exit;
    }

    $pid = (int)($_POST['pid'] ?? 0);
    $to = $_POST['to'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (empty($to)) {
        echo json_encode(array('ok' => false, 'error' => 'Telefono requerido'));
        exit;
    }

    $sendResponse = null;
    $extra = array('channel' => 'whatsapp', 'message' => $message);

    if (!empty($_FILES['attachment']['tmp_name'])) {
        $tmpPath = $_FILES['attachment']['tmp_name'];
        $mime = $_FILES['attachment']['type'] ?: 'application/octet-stream';
        $name = $_FILES['attachment']['name'] ?? 'file';

        $upload = $service->uploadMedia($tmpPath, $mime);
        if (!$upload['ok'] || empty($upload['response']['id'])) {
            echo json_encode(array('ok' => false, 'error' => 'Error subiendo adjunto a Meta', 'meta' => $upload));
            exit;
        }

        $mediaId = $upload['response']['id'];
        $type = 'document';
        if (strpos($mime, 'image/') === 0) {
            $type = 'image';
        } elseif (strpos($mime, 'audio/') === 0) {
            $type = 'audio';
        } elseif (strpos($mime, 'video/') === 0) {
            $type = 'video';
        }

        $sendResponse = $service->sendMediaById($to, $type, $mediaId, $message, $name);
        $extra['media_type'] = $type;
        $extra['media_name'] = $name;
        $extra['media_id'] = $mediaId;
    } else {
        if ($message === '') {
            echo json_encode(array('ok' => false, 'error' => 'Mensaje o adjunto requerido'));
            exit;
        }
        $sendResponse = $service->sendText($to, $message);
    }

    if (!$sendResponse['ok']) {
        echo json_encode(array('ok' => false, 'error' => 'Meta envio error', 'meta' => $sendResponse));
        exit;
    }

    $providerMessageId = $sendResponse['response']['messages'][0]['id'] ?? '';
    $extra['provider_message_id'] = $providerMessageId;

    $sql = "INSERT INTO medex_outgoing (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_extra_text, msg_date, medex_uid)
            VALUES (?, ?, '', 'WHATSAPP', 'OUT', ?, NOW(), ?)";
    sqlStatement($sql, array('wa_' . $pid, $pid, json_encode($extra), $providerMessageId));

    echo json_encode(array('ok' => true, 'meta' => $sendResponse['response']));
    exit;
}

echo json_encode(array('ok' => false, 'error' => 'accion invalida'));
