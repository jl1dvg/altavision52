<?php
require_once '../../globals.php';

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

function waGetGlobal($name, $default = '')
{
    $row = sqlQuery('SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1', array($name));
    return $row['gl_value'] ?? $default;
}

$defaultTemplate = waGetGlobal('whatsapp_meta_default_template', '');
$defaultTemplateLang = waGetGlobal('whatsapp_meta_default_template_lang', 'es');

$pid = (int)($_GET['pid'] ?? 0);
$mobile = trim($_GET['m'] ?? '');
?>
<!doctype html>
<html>
<head>
    <title><?php echo xlt('WhatsApp Bot'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
        #wa-log { height: 340px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; margin-bottom: 10px; }
        .wa-item { margin-bottom: 8px; }
        .wa-in { color: #0a58ca; }
        .wa-out { color: #198754; }
    </style>
</head>
<body class="body_top">
<div class="container-fluid" style="padding-top:10px;">
    <h4><?php echo xlt('WhatsApp Chat'); ?></h4>
    <div id="wa-log"></div>

    <form id="wa-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
        <input type="hidden" name="action" value="send">
        <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">

        <div class="form-group">
            <label><?php echo xlt('Telefono'); ?></label>
            <input class="form-control" type="text" name="to" value="<?php echo attr($mobile); ?>">
        </div>
        <div class="form-group">
            <label><?php echo xlt('Mensaje'); ?></label>
            <textarea class="form-control" name="message" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label><?php echo xlt('Adjunto'); ?></label>
            <input class="form-control" type="file" name="attachment">
        </div>
        <button type="submit" class="btn btn-primary"><?php echo xlt('Enviar'); ?></button>
        <button type="button" id="wa-send-template" class="btn btn-default"><?php echo xlt('Enviar plantilla'); ?></button>
        <input type="text" id="wa-template-name" class="form-control" style="margin-top:8px;" placeholder="template_name" value="<?php echo attr($defaultTemplate); ?>" />
        <input type="text" id="wa-template-lang" class="form-control" style="margin-top:8px;" placeholder="es" value="<?php echo attr($defaultTemplateLang); ?>" />
    </form>
</div>

<script>
function loadLog() {
    $.post('whatsapp_api.php', {
        action: 'list',
        pid: <?php echo js_escape($pid); ?>,
        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
    }).done(function(r){
        if (!r.ok) return;
        var html = '';
        r.items.forEach(function(i){
            var cls = i.direction === 'IN' ? 'wa-in' : 'wa-out';
            html += '<div class="wa-item '+cls+'">['+i.date+'] <b>'+i.direction+'</b>: '+(i.message || '');
            if (i.media_name) html += ' <em>(' + i.media_name + ')</em>';
            html += '</div>';
        });
        $('#wa-log').html(html);
        $('#wa-log').scrollTop($('#wa-log')[0].scrollHeight);
    });
}

$('#wa-send-template').on('click', function(){
    $.post('whatsapp_api.php', {
        action: 'send_template',
        to: $('input[name="to"]').val(),
        template_name: $('#wa-template-name').val(),
        template_lang: $('#wa-template-lang').val(),
        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
    }).done(function(r){
        if (!r.ok) {
            alert(r.error || 'Error plantilla');
            return;
        }
        loadLog();
    });
});

$('#wa-form').on('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);
    $.ajax({
        url: 'whatsapp_api.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false
    }).done(function(r){
        if (!r.ok) {
            alert(r.error || 'Error');
            return;
        }
        $('textarea[name="message"]').val('');
        $('input[name="attachment"]').val('');
        loadLog();
    });
});

loadLog();
setInterval(loadLog, 8000);
</script>
</body>
</html>
