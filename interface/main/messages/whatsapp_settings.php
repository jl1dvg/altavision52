<?php

require_once '../../globals.php';
require_once "$srcdir/acl.inc";

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '')) {
        CsrfUtils::csrfNotVerified();
    }

    setGlobalValue('whatsapp_meta_access_token', trim($_POST['whatsapp_meta_access_token'] ?? ''));
    setGlobalValue('whatsapp_meta_phone_number_id', trim($_POST['whatsapp_meta_phone_number_id'] ?? ''));
    setGlobalValue('whatsapp_meta_verify_token', trim($_POST['whatsapp_meta_verify_token'] ?? ''));
    setGlobalValue('whatsapp_meta_graph_version', trim($_POST['whatsapp_meta_graph_version'] ?? 'v20.0'));
    $saved = true;
}

$vals = array(
    'whatsapp_meta_access_token' => getGlobalValue('whatsapp_meta_access_token', ''),
    'whatsapp_meta_phone_number_id' => getGlobalValue('whatsapp_meta_phone_number_id', ''),
    'whatsapp_meta_verify_token' => getGlobalValue('whatsapp_meta_verify_token', ''),
    'whatsapp_meta_graph_version' => getGlobalValue('whatsapp_meta_graph_version', 'v20.0'),
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
    <form method="post">
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
            <label><?php echo xlt('Graph Version'); ?></label>
            <input class="form-control" name="whatsapp_meta_graph_version" value="<?php echo attr($vals['whatsapp_meta_graph_version']); ?>" />
        </div>

        <button type="submit" class="btn btn-primary"><?php echo xlt('Guardar'); ?></button>
    </form>
</div>
</body>
</html>
