<?php /* Smarty version 2.6.31, created on 2019-09-18 14:04:12
         compiled from C:/MAMP/htdocs/altavision/templates/documents/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'headerTemplate', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 13, false),array('function', 'xlj', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 47, false),array('function', 'xlt', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 49, false),array('function', 'xla', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 61, false),array('function', 'datetimepickerSupport', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 248, false),array('modifier', 'attr', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 73, false),array('modifier', 'text', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 90, false),array('modifier', 'js_escape', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 104, false),array('modifier', 'js_url', 'C:/MAMP/htdocs/altavision/templates/documents/general_list.html', 117, false),)), $this); ?>
<html>
<head>

<?php echo smarty_function_headerTemplate(array('assets' => 'datetime-picker|jquery-ui|jquery-ui-lightness'), $this);?>

<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone/dist/dropzone.css">
<?php echo '
<style type="text/css">
.warn_diagnostic {
    margin: 10 auto 10 auto;
    color: rgb(255, 0, 0);
    font-size: 1.5em;
}
.ui-autocomplete {
    position: absolute;
    top: 0;
    left: 0;
    min-width:200px;
    cursor: default;
}
.ui-menu-item{
     min-width:200px;
}
.fixed-height{
min-width:200px;
padding: 1px;
max-height: 35%;
overflow: auto;
}
</style>
'; ?>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/js/DocumentTreeMenu.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone/dist/dropzone.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/js/scanner.js"></script>


	<script type="text/javascript">
    // dropzone javascript asset translation(s)
    Dropzone.prototype.defaultOptions.dictDefaultMessage = <?php echo smarty_function_xlj(array('t' => 'Drop files here to upload'), $this);?>
;
</script>
<title><?php echo smarty_function_xlt(array('t' => 'Documents'), $this);?>
</title>
</head>
<!-- ViSolve - Call expandAll function on loading of the page if global value 'expand_document' is set -->
<?php if ($this->_tpl_vars['GLOBALS']['expand_document_tree']): ?>
  <body class="body_top" onload="javascript:objTreeMenu_1.expandAll();return false;">
<?php else: ?>
  <body class="body_top">
<?php endif; ?>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<div class="page-header">
					<h2><?php echo smarty_function_xlt(array('t' => 'Documents'), $this);?>
&nbsp;&nbsp;  <a href='interface/patient_file/summary/demographics.php' onclick='top.restoreSession()'  title="<?php echo smarty_function_xla(array('t' => 'Go Back'), $this);?>
" ><i id='advanced-tooltip' class='fa fa-undo fa-2x small' aria-hidden='true'></i></a></h2>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3">
			<div id="documents_list">
				<fieldset>
				<legend><?php echo smarty_function_xlt(array('t' => 'Documents List'), $this);?>
</legend>
                <div style="padding: 0 10px">
                    <div class="ui-widget"style="float:right;">
                        <button id='pid' class="pBtn" type="button" style="float:right;">0</button>
                         <input id="selectPatient" type="text" placeholder="<?php echo ((is_array($_tmp=$this->_tpl_vars['place_hld'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                    </div>
                    <a id="list_collapse" href="#" onclick="javascript:objTreeMenu_1.collapseAll();return false;">&nbsp;(<?php echo smarty_function_xlt(array('t' => 'Collapse all'), $this);?>
)</a>
                    <?php echo $this->_tpl_vars['tree_html']; ?>

                </div>
			</div>
			</fieldset>
		</div>

		<div class="col-sm-9">
			<div id="documents_actions">

				<fieldset>

					<legend><?php echo smarty_function_xlt(array('t' => 'Document Uploader/Viewer'), $this);?>
</legend>
                    <div style="padding: 0 10px">
						<?php if ($this->_tpl_vars['message']): ?>
							<div class='text' style="margin-bottom:-10px; margin-top:-8px; padding:10px;"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</i></div><br>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['messages']): ?>
							<div class='text' style="margin-bottom:-10px; margin-top:-8px; padding:10px;"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['messages'])) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</i></div><br>
						<?php endif; ?>
						<?php echo $this->_tpl_vars['activity']; ?>

					</div>
				</fieldset>

			</div>
		</div>
	</div>
</div><!--end of container div-->
<script type="text/javascript">
var curpid = <?php echo ((is_array($_tmp=$this->_tpl_vars['cur_pid'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var newVersion= <?php echo ((is_array($_tmp=$this->_tpl_vars['is_new'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var demoPid = <?php echo ((is_array($_tmp=$this->_tpl_vars['demo_pid'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var inUseMsg = <?php echo ((is_array($_tmp=$this->_tpl_vars['used_msg'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
<?php echo '
if(curpid == demoPid && !newVersion){
    $(".ui-widget").hide();
}
else{
    $("#pid").text(curpid);
}
$(function() {
    $( "#selectPatient" ).autocomplete({
    	source: "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/ajax/document_helpers.php?csrf_token_form=" + <?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('js_url', true, $_tmp) : js_url($_tmp)); ?>
<?php echo ',
    	focus: function(event, sel) {
            event.preventDefault();
        },
        select: function(event, sel) {
            event.preventDefault();
            if (sel.item.value == \'00\' && ! sel.item.label.match('; ?>
<?php echo smarty_function_xlj(array('t' => 'Reset'), $this);?>
<?php echo ')){
            	alert(inUseMsg);
            	return false;
            }
            $(this).val(sel.item.label);
            location.href = "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/controller.php?document&list&patient_id=" + encodeURIComponent(sel.item.value) + "&patient_name=" + encodeURIComponent(sel.item.label);
            $("#pid").text(sel.item.value);
        },
        minLength: 3
    }).autocomplete("widget").addClass("fixed-height");
 });
$(".pBtn").click(function(event) {
    var $input = $("#selectPatient");
        $input.val(\'*\');
        $input.autocomplete(\'search\'," ");
        $input.val(\'\');
});
$("#list_collapse").detach().appendTo("#objTreeMenu_1_node_1 nobr");

/////////
//
// Please read scanner.js developer\'s guide at: http://asprise.com/document-scan-upload-image-browser/ie-chrome-firefox-scanner-docs.html
//

/** Scan and upload in one go */
function scanAndUploadDirectly() {
    scanner.scan(displayServerResponse,
        {
            "output_settings": [
                {
                    "type": "upload",
                    "format": "pdf",
                    "discard_blank_pages": "false", /** Default value: false */
                    "blank_page_threshold": "0.02", /** Max ink coverage consider as blank */
                    "upload_target": {
                        "url": "http://192.168.85.126/altavision/templates/documents/upload.php?action=dump"
                    }
                }
            ]
        }
    );
}


function scanWithoutAspriseDialog() {
    scanner.scan(displayServerResponse,
        {
            "use_asprise_dialog": false,
            "output_settings": [
                {
                    "type": "upload",
                    "format": "pdf",
                    "discard_blank_pages": "true", /** Default value: false */
                    "blank_page_threshold": "0.02", /** Max ink coverage consider as blank */
                    "upload_target": {
                        "url": "http://192.168.85.126/altavision/templates/documents/upload.php?action=dump"
                    }
                }
            ]
        }
    );
}

function displayServerResponse(successful, mesg, response) {
    if(!successful) { // On error
        document.getElementById(\'server_response\').innerHTML = \'Failed: \' + mesg;
        return;
    }

    if(successful && mesg != null && mesg.toLowerCase().indexOf(\'user cancel\') >= 0) { // User cancelled.
        document.getElementById(\'server_response\').innerHTML = \'User cancelled\';
        return;
    }

    document.getElementById(\'server_response\').innerHTML = scanner.getUploadResponse(response);
}

// functions to view and pop out documents as needed.
//
$(function () {
    $("img[id^=\'icon_objTreeMenu_\']").tooltip({
        items: $("img[src*=\'file3.png\']"),
        content: '; ?>
<?php echo smarty_function_xlj(array('t' => "Double Click on this icon to pop up document in a new viewer."), $this);?>
<?php echo '
    });

    $("img[id^=\'icon_objTreeMenu_\']").on(\'dblclick\', function (e) {
        let popsrc = $(this).next("a").attr(\'href\') || \'\';
        let diview = $(this).next("a").text();
        let dflg = false;
        if (!popsrc.includes(\'&view&\')) {
            return false;
        } else if (diview.toLowerCase().includes(\'.dcm\') || diview.toLowerCase().includes(\'.zip\')) {
            popsrc = "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/library/dicom_frame.php?web_path=" + encodeURIComponent(popsrc);
            dflg = true;
        }
        popsrc = popsrc.replace(\'&view&\', \'&retrieve&\') + \'as_file=false\';
        let poContentModal = function () {
            let wname = \'_\' + Math.random().toString(36).substr(2, 6);
            let opt = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=no";
            window.open(popsrc, wname, opt);
        };

        let btnText = '; ?>
<?php echo smarty_function_xlj(array('t' => 'Full Screen'), $this);?>
<?php echo ';
        let btnClose = '; ?>
<?php echo smarty_function_xlj(array('t' => 'Close'), $this);?>
<?php echo ';
        let size = \'modal-xl\';
        let sizeHeight = \'full\';
        if (dflg) {
            size = \'modal-md\';
        }
        dlgopen(popsrc, \'popdoc\', size, 600, \'\', \'\', {
            buttons: [
                {text: btnText, close: true, style: \'primary btn-xs\', click: poContentModal},
                {text: btnClose, close: true, style: \'default btn-xs\'}
            ],
            sizeHeight: sizeHeight,
            allowResize: true,
            allowDrag: true,
            dialogId: \'\',
            type: \'iframe\'
        });
        return false;
    });
});

$(function(){'; ?>

    <?php echo smarty_function_datetimepickerSupport(array(), $this);?>

<?php echo '});'; ?>


</script>
</body>
</html>