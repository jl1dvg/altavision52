<?php

require_once '../../globals.php';
require_once "$srcdir/acl.inc";
require_once "$srcdir/../../library/WhatsAppMetaService.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!acl_check('admin', 'super')) {
    die(xlt('Not authorized'));
}

function getGlobalValue($name, $default = '')
{
    $row = sqlQuery('SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1', array($name));
    return $row['gl_value'] ?? $default;
}

function setGlobalValue($name, $value)
{
    $exists = sqlQuery('SELECT gl_name FROM globals WHERE gl_name = ? LIMIT 1', array($name));
    if ($exists) {
        sqlStatement('UPDATE globals SET gl_value = ? WHERE gl_name = ?', array($value, $name));
    } else {
        sqlStatement('INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', array($name, $value));
    }
}

$saved = false;
$testResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '')) {
        CsrfUtils::csrfNotVerified();
    }

    setGlobalValue('whatsapp_meta_access_token', trim($_POST['whatsapp_meta_access_token'] ?? ''));
    setGlobalValue('whatsapp_meta_phone_number_id', trim($_POST['whatsapp_meta_phone_number_id'] ?? ''));
    setGlobalValue('whatsapp_meta_verify_token', trim($_POST['whatsapp_meta_verify_token'] ?? ''));
    setGlobalValue('whatsapp_meta_app_secret', trim($_POST['whatsapp_meta_app_secret'] ?? ''));
    setGlobalValue('whatsapp_meta_graph_version', trim($_POST['whatsapp_meta_graph_version'] ?? 'v20.0'));
    setGlobalValue('whatsapp_meta_default_template', trim($_POST['whatsapp_meta_default_template'] ?? ''));
    setGlobalValue('whatsapp_meta_default_template_lang', trim($_POST['whatsapp_meta_default_template_lang'] ?? 'es'));

    $action = $_POST['form_action'] ?? 'save';
    if ($action === 'test') {
        $service = new WhatsAppMetaService();
        $testResult = $service->testConnection();
    } else {
        $saved = true;
    }
}

$vals = array(
    'whatsapp_meta_access_token' => getGlobalValue('whatsapp_meta_access_token', ''),
    'whatsapp_meta_phone_number_id' => getGlobalValue('whatsapp_meta_phone_number_id', ''),
    'whatsapp_meta_verify_token' => getGlobalValue('whatsapp_meta_verify_token', ''),
    'whatsapp_meta_app_secret' => getGlobalValue('whatsapp_meta_app_secret', ''),
    'whatsapp_meta_graph_version' => getGlobalValue('whatsapp_meta_graph_version', 'v20.0'),
    'whatsapp_meta_default_template' => getGlobalValue('whatsapp_meta_default_template', ''),
    'whatsapp_meta_default_template_lang' => getGlobalValue('whatsapp_meta_default_template_lang', 'es'),
);
?>
<!doctype html>
<html>
<head>
    <title><?php echo xlt('WhatsApp Meta Settings'); ?></title>
    <?php Header::setupHeader(['common']); ?>
</head>
<body class="body_top">
<div class="container" style="margin-top:20px; max-width:780px;">
    <h3><?php echo xlt('WhatsApp Meta Cloud API Settings'); ?></h3>
    <?php if ($saved) { ?>
        <div class="alert alert-success"><?php echo xlt('Configuracion guardada'); ?></div>
    <?php } ?>
    <?php if (!empty($testResult)) { ?>
        <?php if (!empty($testResult['ok'])) { ?>
            <div class="alert alert-success"><?php echo xlt('Conexion exitosa con Meta Cloud API'); ?>
                <pre style="margin-top:8px;"><?php echo text(json_encode($testResult['response'], JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger"><?php echo xlt('Error probando conexion con Meta'); ?>
                <pre style="margin-top:8px;"><?php echo text($testResult['raw'] ?? $testResult['error'] ?? ''); ?></pre>
            </div>
        <?php } ?>
    <?php } ?>
    <form method="post">
        <input type="hidden" id="form_action" name="form_action" value="save" />
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

        <div class="form-group">
            <label><?php echo xlt('Access Token'); ?></label>
            <textarea class="form-control" name="whatsapp_meta_access_token" rows="4"><?php echo attr($vals['whatsapp_meta_access_token']); ?></textarea>
        </div>
        <div class="form-group">
            <label><?php echo xlt('Phone Number ID'); ?></label>
            <input class="form-control" name="whatsapp_meta_phone_number_id" value="<?php echo attr($vals['whatsapp_meta_phone_number_id']); ?>" />
        </div>
        <div class="form-group">
            <label><?php echo xlt('Verify Token'); ?></label>
            <input class="form-control" name="whatsapp_meta_verify_token" value="<?php echo attr($vals['whatsapp_meta_verify_token']); ?>" />
        </div>
        <div class="form-group">
            <label><?php echo xlt('App Secret (Webhook Signature)'); ?></label>
            <input class="form-control" name="whatsapp_meta_app_secret" value="<?php echo attr($vals['whatsapp_meta_app_secret']); ?>" />
        </div>
        <div class="form-group">
            <label><?php echo xlt('Graph Version'); ?></label>
            <input class="form-control" name="whatsapp_meta_graph_version" value="<?php echo attr($vals['whatsapp_meta_graph_version']); ?>" />
        </div>
        <div class="form-group">
            <label><?php echo xlt('Default Template Name'); ?></label>
            <input class="form-control" name="whatsapp_meta_default_template" value="<?php echo attr($vals['whatsapp_meta_default_template']); ?>" />
        </div>
        <div class="form-group">
            <label><?php echo xlt('Default Template Language'); ?></label>
            <input class="form-control" name="whatsapp_meta_default_template_lang" value="<?php echo attr($vals['whatsapp_meta_default_template_lang']); ?>" />
        </div>

        <button type="submit" class="btn btn-primary" onclick="document.getElementById('form_action').value='save';"><?php echo xlt('Guardar'); ?></button>
        <button type="submit" class="btn btn-default" onclick="document.getElementById('form_action').value='test';"><?php echo xlt('Probar conexion Meta'); ?></button>
    </form>
</div>
</body>
</html>
