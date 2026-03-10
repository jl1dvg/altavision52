<?php

/**
 * Meta Cloud API WhatsApp service helper.
 */
class WhatsAppMetaService
{
    private $accessToken;
    private $phoneNumberId;
    private $graphVersion;
    private $appSecret;

    public function __construct()
    {
        $this->accessToken = $this->getConfig('whatsapp_meta_access_token', $GLOBALS['whatsapp_meta_access_token'] ?? '');
        $this->phoneNumberId = $this->getConfig('whatsapp_meta_phone_number_id', $GLOBALS['whatsapp_meta_phone_number_id'] ?? '');
        $this->graphVersion = $this->getConfig('whatsapp_meta_graph_version', $GLOBALS['whatsapp_meta_graph_version'] ?? 'v20.0');
        $this->appSecret = $this->getConfig('whatsapp_meta_app_secret', $GLOBALS['whatsapp_meta_app_secret'] ?? '');
    }

    public function isConfigured()
    {
        return !empty($this->accessToken) && !empty($this->phoneNumberId);
    }

    public function sendText($to, $message)
    {
        $payload = array(
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'text',
            'text' => array('body' => $message)
        );

        return $this->request('POST', $this->baseUrl() . '/messages', $payload);
    }

    public function testConnection()
    {
        $url = 'https://graph.facebook.com/' . $this->graphVersion . '/' . $this->phoneNumberId . '?fields=id,display_phone_number,verified_name';
        return $this->request('GET', $url, null);
    }

    public function uploadMedia($filePath, $mimeType)
    {
        $url = $this->baseUrl() . '/media';
        $fields = array(
            'messaging_product' => 'whatsapp',
            'file' => new CURLFile($filePath, $mimeType, basename($filePath))
        );

        return $this->requestMultipart($url, $fields);
    }

    public function sendMediaById($to, $type, $mediaId, $caption = '', $filename = '')
    {
        $node = array('id' => $mediaId);
        if (!empty($caption) && in_array($type, array('image', 'video', 'document'))) {
            $node['caption'] = $caption;
        }
        if (!empty($filename) && $type === 'document') {
            $node['filename'] = $filename;
        }

        $payload = array(
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => $type,
            $type => $node
        );

        return $this->request('POST', $this->baseUrl() . '/messages', $payload);
    }

    public function verifyWebhook($mode, $token, $challenge)
    {
        $verifyToken = $this->getConfig('whatsapp_meta_verify_token', $GLOBALS['whatsapp_meta_verify_token'] ?? '');
        if ($mode === 'subscribe' && !empty($verifyToken) && hash_equals($verifyToken, (string)$token)) {
            return $challenge;
        }
        return false;
    }

    public function verifySignature($rawPayload, $signatureHeader)
    {
        if (empty($this->appSecret)) {
            return true;
        }
        if (empty($signatureHeader) || strpos($signatureHeader, 'sha256=') !== 0) {
            return false;
        }
        $received = substr($signatureHeader, 7);
        $computed = hash_hmac('sha256', $rawPayload, $this->appSecret);
        return hash_equals($computed, $received);
    }

    public function sendTemplate($to, $templateName, $languageCode = 'es', $components = array())
    {
        $payload = array(
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'template',
            'template' => array(
                'name' => $templateName,
                'language' => array('code' => $languageCode)
            )
        );

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->request('POST', $this->baseUrl() . '/messages', $payload);
    }

    private function getConfig($name, $fallback = '')
    {
        if (function_exists('sqlQuery')) {
            $row = sqlQuery('SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1', array($name));
            if (!empty($row['gl_value'])) {
                return $row['gl_value'];
            }
        }
        return $fallback;
    }

    private function baseUrl()
    {
        return 'https://graph.facebook.com/' . $this->graphVersion . '/' . $this->phoneNumberId;
    }

    private function normalizePhone($phone)
    {
        return preg_replace('/\D+/', '', (string)$phone);
    }

    private function request($method, $url, $payload)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers = array('Authorization: Bearer ' . $this->accessToken);
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($raw, true);
        return array(
            'ok' => empty($err) && $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'error' => $err,
            'response' => $decoded,
            'raw' => $raw
        );
    }

    private function requestMultipart($url, $fields)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->accessToken
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($raw, true);
        return array(
            'ok' => empty($err) && $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'error' => $err,
            'response' => $decoded,
            'raw' => $raw
        );
    }
}
