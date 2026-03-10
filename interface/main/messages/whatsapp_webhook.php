<?php

require_once '../../globals.php';
require_once "$srcdir/../../library/WhatsAppMetaService.php";

$service = new WhatsAppMetaService();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $challenge = $service->verifyWebhook(
        $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? '',
        $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '',
        $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? ''
    );

    if ($challenge !== false) {
        header('Content-Type: text/plain');
        echo $challenge;
        exit;
    }

    http_response_code(403);
    echo 'forbidden';
    exit;
}

$raw = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!$service->verifySignature($raw, $signature)) {
    http_response_code(403);
    echo 'invalid signature';
    exit;
}

$payload = json_decode($raw, true);

if (empty($payload['entry'])) {
    http_response_code(200);
    echo 'ok';
    exit;
}

foreach ($payload['entry'] as $entry) {
    foreach (($entry['changes'] ?? array()) as $change) {
        $value = $change['value'] ?? array();

        if (!empty($value['messages'])) {
            foreach ($value['messages'] as $msg) {
                $from = preg_replace('/\D+/', '', $msg['from'] ?? '');
                $type = $msg['type'] ?? 'text';
                $text = $msg['text']['body'] ?? '';

                $pidRow = sqlQuery("SELECT pid FROM patient_data WHERE REPLACE(REPLACE(REPLACE(phone_cell,'-',''),'(',''),')','') LIKE ? LIMIT 1", array('%' . $from . '%'));
                $pid = (int)($pidRow['pid'] ?? 0);

                $extra = array(
                    'channel' => 'whatsapp',
                    'provider_message_id' => $msg['id'] ?? '',
                    'from' => $from,
                    'message' => $text,
                    'media_type' => $type
                );

                sqlStatement(
                    "INSERT INTO medex_outgoing (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_extra_text, msg_date, medex_uid)
                     VALUES (?, ?, '', 'WHATSAPP', 'IN', ?, NOW(), ?)",
                    array('wa_' . $pid, $pid, json_encode($extra), $msg['id'] ?? '')
                );
            }
        }

        if (!empty($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $providerId = $status['id'] ?? '';
                $state = $status['status'] ?? '';
                if (!empty($providerId)) {
                    $sql = "UPDATE medex_outgoing SET msg_extra_text = ? WHERE medex_uid = ? AND msg_type = 'WHATSAPP' LIMIT 1";
                    $row = sqlQuery("SELECT msg_extra_text FROM medex_outgoing WHERE medex_uid = ? AND msg_type = 'WHATSAPP' LIMIT 1", array($providerId));
                    $extra = json_decode($row['msg_extra_text'] ?? '{}', true);
                    $extra['status'] = $state;
                    sqlStatement($sql, array(json_encode($extra), $providerId));
                }
            }
        }
    }
}

http_response_code(200);
echo 'ok';
