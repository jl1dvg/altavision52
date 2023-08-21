<?php
/**
 * forms.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/amc.php");
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';
require_once("$srcdir/../controllers/C_Document.class.php");

use ESign\Api;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$reviewMode = false;
if (!empty($_REQUEST['review_id'])) {
    $reviewMode = true;
    $encounter = sanitizeNumber($_REQUEST['review_id']);
}

$is_group = ($attendant_type == 'gid') ? true : false;
if ($attendant_type == 'gid') {
    $groupId = $therapy_group;
}
$attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
if ($is_group && !acl_check("groups", "glog", false, array('view', 'write'))) {
    echo xlt("access not allowed");
    exit();
}

function createFormWithFieldValues($patient_id, $visitid, $formtitle, $formname, $userauthorized, $fieldValueList)
{
    // Creating a new form. Get the new form_id by inserting and deleting a dummy row.
    // This is necessary to create the form instance even if it has no native data.
    $newid = sqlInsert("INSERT INTO lbf_data (field_id, field_value) VALUES (?, ?)", array('', ''));

    sqlStatement("DELETE FROM lbf_data WHERE form_id = ? AND field_id = ''", array($newid));

    // Assuming addForm function exists and provides necessary parameters
    addForm($visitid, $formtitle, $newid, $formname, $patient_id, $userauthorized);

    // Insert the provided field_id and field_value pairs
    foreach ($fieldValueList as $field_id => $field_value) {
        sqlStatement("INSERT INTO lbf_data (form_id, field_id, field_value) VALUES (?, ?, ?)",
            array($newid, $field_id, $field_value));
    }
}

?>
<html>

<head>

    <?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

    <?php Header::setupHeader(['common', 'esign', 'dygraphs']); ?>

    <?php
    $esignApi = new Api();
    ?>

    <?php // if the track_anything form exists, then include the styling and js functions (and js variable) for graphing
    if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
        <script type="text/javascript">
            var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
        </script>
        <script type="text/javascript"
                src="<?php echo $GLOBALS['web_root'] ?>/interface/forms/track_anything/report.js"></script>
        <link rel="stylesheet" href="<?php echo $GLOBALS['web_root'] ?>/interface/forms/track_anything/style.css"
              type="text/css">
    <?php } ?>

    <?php
    // If the user requested attachment of any orphaned procedure orders, do it.
    if (!empty($_GET['attachid'])) {
        $attachid = explode(',', $_GET['attachid']);
        foreach ($attachid as $aid) {
            $aid = intval($aid);
            if (!$aid) {
                continue;
            }
            $tmp = sqlQuery(
                "SELECT COUNT(*) AS count FROM procedure_order WHERE " .
                "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
                array($aid, $pid)
            );
            if (!empty($tmp['count'])) {
                sqlStatement(
                    "UPDATE procedure_order SET encounter_id = ? WHERE " .
                    "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
                    array($encounter, $aid, $pid)
                );
                addForm($encounter, "Procedure Order", $aid, "procedure_order", $pid, $userauthorized);
            }
        }
    }
    ?>

    <script type="text/javascript">
        $.noConflict();
        jQuery(document).ready(function ($) {
            var formConfig = <?php echo $esignApi->formConfigToJson(); ?>;
            $(".esign-button-form").esign(
                formConfig,
                {
                    afterFormSuccess: function (response) {
                        if (response.locked) {
                            var editButtonId = "form-edit-button-" + response.formDir + "-" + response.formId;
                            $("#" + editButtonId).replaceWith(response.editButtonHtml);
                        }

                        var logId = "esign-signature-log-" + response.formDir + "-" + response.formId;
                        $.post(formConfig.logViewAction, response, function (html) {
                            $("#" + logId).replaceWith(html);
                        });
                    }
                }
            );

            var encounterConfig = <?php echo $esignApi->encounterConfigToJson(); ?>;
            $(".esign-button-encounter").esign(
                encounterConfig,
                {
                    afterFormSuccess: function (response) {
                        // If the response indicates a locked encounter, replace all
                        // form edit buttons with a "disabled" button, and "disable" left
                        // nav visit form links
                        if (response.locked) {
                            // Lock the form edit buttons
                            $(".form-edit-button").replaceWith(response.editButtonHtml);
                            // Disable the new-form capabilities in left nav
                            top.window.parent.left_nav.syncRadios();
                            // Disable the new-form capabilities in top nav of the encounter
                            $(".encounter-form-category-li").remove();
                        }

                        var logId = "esign-signature-log-encounter-" + response.encounterId;
                        $.post(encounterConfig.logViewAction, response, function (html) {
                            $("#" + logId).replaceWith(html);
                        });
                    }
                }
            );

            $("#prov_edu_res").click(function () {
                if ($('#prov_edu_res').prop('checked')) {
                    var mode = "add";
                } else {
                    var mode = "remove";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "patient_edu_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo js_escape($pid); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo js_escape($encounter); ?>,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    }
                );
            });

            $("#provide_sum_pat_flag").click(function () {
                if ($('#provide_sum_pat_flag').prop('checked')) {
                    var mode = "add";
                } else {
                    var mode = "remove";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "provide_sum_pat_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo js_escape($pid); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo js_escape($encounter); ?>,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    }
                );
            });

            $("#trans_trand_care").click(function () {
                if ($('#trans_trand_care').prop('checked')) {
                    var mode = "add";
                    // Enable the reconciliation checkbox
                    $("#med_reconc_perf").removeAttr("disabled");
                    $("#soc_provided").removeAttr("disabled");
                } else {
                    var mode = "remove";
                    //Disable the reconciliation checkbox (also uncheck it if applicable)
                    $("#med_reconc_perf").attr("disabled", true);
                    $("#med_reconc_perf").prop("checked", false);
                    $("#soc_provided").attr("disabled", true);
                    $("#soc_provided").prop("checked", false);
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "med_reconc_amc",
                        complete: false,
                        mode: mode,
                        patient_id: <?php echo js_escape($pid); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo js_escape($encounter); ?>,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    }
                );
            });

            $("#med_reconc_perf").click(function () {
                if ($('#med_reconc_perf').prop('checked')) {
                    var mode = "complete";
                } else {
                    var mode = "uncomplete";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "med_reconc_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo js_escape($pid); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo js_escape($encounter); ?>,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    }
                );
            });
            $("#soc_provided").click(function () {
                if ($('#soc_provided').prop('checked')) {
                    var mode = "soc_provided";
                } else {
                    var mode = "no_soc_provided";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "med_reconc_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo js_escape($pid); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo js_escape($encounter); ?>,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    }
                );
            });

            $(".deleteme").click(function (evt) {
                deleteme();
                evt.stopPropogation();
            });

            <?php
            // If the user was not just asked about orphaned orders, build javascript for that.
            if (!isset($_GET['attachid'])) {
                $ares = sqlStatement(
                    "SELECT procedure_order_id, date_ordered " .
                    "FROM procedure_order WHERE " .
                    "patient_id = ? AND encounter_id = 0 AND activity = 1 " .
                    "ORDER BY procedure_order_id",
                    array($pid)
                );
                echo "  // Ask about attaching orphaned orders to this encounter.\n";
                echo "  var attachid = '';\n";
                while ($arow = sqlFetchArray($ares)) {
                    $orderid = $arow['procedure_order_id'];
                    $orderdate = $arow['date_ordered'];
                    echo "  if (confirm(" . xlj('There is a lab order') . " + ' ' + " . js_escape($orderid) . " + ' ' + " .
                        xlj('dated') . " + ' ' + " . js_escape($orderdate) . " + ' ' + " .
                        xlj('for this patient not yet assigned to any encounter.') . " + ' ' + " .
                        xlj('Assign it to this one?') . ")) attachid += " . js_escape($orderid . ",") . ";\n";
                }
                echo "  if (attachid) location.href = 'forms.php?attachid=' + encodeURIComponent(attachid);\n";
            }
            ?>

            <?php if ($reviewMode) { ?>
            $("body table:first").hide();
            $(".encounter-summary-column").hide();
            $(".css_button").hide();
            $(".css_button_small").hide();
            $(".encounter-summary-column:first").show();
            $(".title:first").text(<?php echo xlj("Review"); ?> +" " + $(".title:first").text() + " ( " + <?php echo js_escape($encounter); ?> +" )");
            <?php } ?>
        });

        // Process click on Delete link.
        function deleteme() {
            dlgopen('../deleter.php?encounterid=' + <?php echo js_url($encounter); ?> +'&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 200, '', '', {
                buttons: [
                    {text: <?php echo xlj('Done'); ?>, close: true, style: 'primary btn-sm'}
                ],
                allowResize: false,
                allowDrag: true,
            });
            return false;
        }

        // Called by the deleter.php window on a successful delete.
        function imdeleted(EncounterId) {
            top.window.parent.left_nav.removeOptionSelected(EncounterId);
            top.window.parent.left_nav.clearEncounter();
            if (top.tab_mode) {
                top.encounterList();
            } else {
                top.window.parent.left_nav.loadFrame('ens1', window.parent.name, 'patient_file/history/encounters.php');
            }
        }

        // Called to open the data entry form a specified encounter form instance.
        function openEncounterForm(formdir, formname, formid) {
            var url = <?php echo js_escape($rootdir); ?> +'/patient_file/encounter/view_form.php?formname=' +
                encodeURIComponent(formdir) + '&id=' + encodeURIComponent(formid);
            if (formdir == 'newpatient' || !parent.twAddFrameTab) {
                top.restoreSession();
                location.href = url;
            } else {
                parent.twAddFrameTab('enctabs', formname, url);
            }
            return false;
        }

        // Called when an encounter form may changed something that requires a refresh here.
        function refreshVisitDisplay() {
            location.href = <?php echo js_escape($rootdir); ?> +'/patient_file/encounter/forms.php';
        }

        function openPopup() {
            // Mostrar el pop-up
            document.getElementById("popup-overlay").style.display = "flex";
        }

        function closePopup() {
            // Cerrar el pop-up
            document.getElementById("popup-overlay").style.display = "none";
        }

    </script>

    <script language="javascript">
        function expandcollapse(atr) {
            for (var i = 1; i < 15; ++i) {
                var mydivid = "divid_" + i;
                var myspanid = "spanid_" + i;
                var ele = document.getElementById(mydivid);
                var text = document.getElementById(myspanid);
                if (!ele) continue;
                if (atr == "expand") {
                    ele.style.display = "block";
                    text.innerHTML = <?php echo xlj('Collapse'); ?>;
                } else {
                    ele.style.display = "none";
                    text.innerHTML = <?php echo xlj('Expand'); ?>;
                }
            }
        }

        function divtoggle(spanid, divid) {
            var ele = document.getElementById(divid);
            var text = document.getElementById(spanid);
            if (ele.style.display == "block") {
                ele.style.display = "none";
                text.innerHTML = <?php echo xlj('Expand'); ?>;
            } else {
                ele.style.display = "block";
                text.innerHTML = <?php echo xlj('Collapse'); ?>;
            }
        }
    </script>

    <style type="text/css">
        .procedure-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .procedure-table td {
            padding: 8px;
            border: none;
            text-align: left;
        }

        .procedure-label {
            font-weight: bold;
            text-align: left;
        }

        div.tab {
            min-height: 50px;
            padding: 8px;
        }

        div.form_header {
            float: left;
            min-width: 300pt;
        }

        div.form_header_controls {
            float: left;
            margin-bottom: 2px;
            margin-left: 6px;
        }

        div.formname {
            float: left;
            min-width: 120pt;
            font-weight: bold;
            padding: 0px;
            margin: 0px;
        }

        .encounter-summary-container {
            float: left;
            width: 100%;
        }

        .encounter-summary-column {
            width: 33.3%;
            float: left;
            display: inline;
            margin-top: 10px;
        }

        /* Estilos CSS para el pop-up */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 999;
            display: none;
        }

        .popup-content {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            max-width: 400px;
        }

        .popup-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .popup-link {
            display: block;
            margin-bottom: 10px;
        }

        .popup-link:last-child {
            margin-bottom: 0;
        }

        .popup-link button {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            border: none;
            background-color: #f2f2f2;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .popup-link button:hover {
            background-color: #e0e0e0;
        }

        .popup-close {
            text-align: right;
            margin-top: 20px;
        }

        .popup-close button {
            background-color: transparent;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
    </style>

    <!-- *************** -->
    <!-- Form menu start -->
    <script language="JavaScript">

        function openNewForm(sel, label) {
            top.restoreSession();
            var FormNameValueArray = sel.split('formname=');
            if (FormNameValueArray[1] == 'newpatient') {
                // TBD: Make this work when it's not the first frame.
                parent.frames[0].location.href = sel;
            } else {
                parent.twAddFrameTab('enctabs', label, sel);
            }
        }

        function toggleFrame1(fnum) {
            top.frames['left_nav'].document.forms[0].cb_top.checked = false;
            top.window.parent.left_nav.toggleFrame(fnum);
        }
    </script>
    <style type="text/css">
        #sddm {
            margin: 0;
            padding: 0;
            z-index: 30;
        }

    </style>
    <script type="text/javascript" language="javascript">

        var timeout = 500;
        var closetimer = 0;
        var ddmenuitem = 0;
        var oldddmenuitem = 0;
        var flag = 0;

        // open hidden layer
        function mopen(id) {
            // cancel close timer
            //mcancelclosetime();

            flag = 10;

            // close old layer
            //if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
            //if(ddmenuitem) ddmenuitem.style.display = 'none';

            // get new layer and show it
            oldddmenuitem = ddmenuitem;
            ddmenuitem = document.getElementById(id);
            if ((ddmenuitem.style.visibility == '') || (ddmenuitem.style.visibility == 'hidden')) {
                if (oldddmenuitem) oldddmenuitem.style.visibility = 'hidden';
                if (oldddmenuitem) oldddmenuitem.style.display = 'none';
                ddmenuitem.style.visibility = 'visible';
                ddmenuitem.style.display = 'block';
            } else {
                ddmenuitem.style.visibility = 'hidden';
                ddmenuitem.style.display = 'none';
            }
        }

        // close showed layer
        function mclose() {
            if (flag == 10) {
                flag = 11;
                return;
            }
            if (ddmenuitem) ddmenuitem.style.visibility = 'hidden';
            if (ddmenuitem) ddmenuitem.style.display = 'none';
        }

        // close layer when click-out
        document.onclick = mclose;

        //=================================================
        function findPosX(id) {
            obj = document.getElementById(id);
            var curleft = 0;
            if (obj.offsetParent)
                while (1) {
                    curleft += obj.offsetLeft;
                    if (!obj.offsetParent)
                        break;
                    obj = obj.offsetParent;
                }
            else if (obj.x)
                curleft += obj.x;
            PropertyWidth = document.getElementById(id).offsetWidth;
            if (PropertyWidth > curleft) {
                document.getElementById(id).style.left = 0;
            }
        }

        function findPosY(obj) {
            var curtop = 0;
            if (obj.offsetParent)
                while (1) {
                    curtop += obj.offsetTop;
                    if (!obj.offsetParent)
                        break;
                    obj = obj.offsetParent;
                }
            else if (obj.y)
                curtop += obj.y;
            return curtop;
        }
    </script>

</head>
<body class="bgcolor2">
<dl>
    <?php //DYNAMIC FORM RETREIVAL
    include_once("$srcdir/registry.inc");

    function myGetRegistered($state = "1", $limit = "unlimited", $offset = "0")
    {
        global $attendant_type;
        $sql = "SELECT category, nickname, name, state, directory, id, sql_run, " .
            "unpackaged, date, aco_spec FROM registry WHERE ";
        // select different forms for groups
        if ($attendant_type == 'pid') {
            $sql .= "patient_encounter = 1 AND ";
        } else {
            $sql .= "therapy_group_encounter = 1 AND ";
        }
        $sql .= "state LIKE ? ORDER BY category, priority, name";
        if ($limit != "unlimited") {
            $sql .= " limit " . escape_limit($limit) . ", " . escape_limit($offset);
        }
        $res = sqlStatement($sql, array($state));
        if ($res) {
            for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                $all[$iter] = $row;
            }
        } else {
            return false;
        }
        return $all;
    }

    $reg = myGetRegistered();
    $old_category = '';

    $DivId = 1;

    // To see if the encounter is locked. If it is, no new forms can be created
    $encounterLocked = false;
    if ($esignApi->lockEncounters() &&
        isset($GLOBALS['encounter']) &&
        !empty($GLOBALS['encounter'])) {
        $esign = $esignApi->createEncounterESign($GLOBALS['encounter']);
        if ($esign->isLocked()) {
            $encounterLocked = true;
        }
    }

    if (!empty($reg)) {
        $StringEcho = '<ul id="sddm">';
        if ($encounterLocked === false) {
            foreach ($reg as $entry) {
                // Check permission to create forms of this type.
                $tmp = explode('|', $entry['aco_spec']);
                if (!empty($tmp[1])) {
                    if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                        continue;
                    }
                }
                $new_category = trim($entry['category']);
                $new_nickname = trim($entry['nickname']);
                if ($new_category == '') {
                    $new_category = xl('Miscellaneous');
                } else {
                    $new_category = xl($new_category);
                }
                if ($new_nickname != '') {
                    $nickname = $new_nickname;
                } else {
                    $nickname = trim($entry['name']);
                }
                if ($old_category != $new_category) {
                    $new_category_ = $new_category;
                    $new_category_ = str_replace(' ', '_', $new_category_);
                    if ($old_category != '') {
                        $StringEcho .= "</table></div></li>";
                    }
                    $StringEcho .= "<li class=\"encounter-form-category-li\"><a href='JavaScript:void(0);' onClick=\"mopen(" . attr_js($DivId) . ");\" >" . text($new_category) . "</a><div id='" . attr($DivId) . "' ><table border='0' cellspacing='0' cellpadding='0'>";
                    $old_category = $new_category;
                    $DivId++;
                }
                $StringEcho .= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=\"openNewForm(" .
                    attr_js($rootdir . "/patient_file/encounter/load_form.php?formname=" . urlencode($entry['directory'])) .
                    ", " . attr_js(xl_form_title($nickname)) . ")\" href='JavaScript:void(0);'>" .
                    text(xl_form_title($nickname)) . "</a></td></tr>";
            }
        }
        $StringEcho .= '</table></div></li>';
    }

    if ($StringEcho) {
        $StringEcho2 = '<div style="clear:both"></div>';
    } else {
        $StringEcho2 = "";
    }

    // This shows Layout Based Form names just like the above.
    //
    if ($encounterLocked === false) {
        $lres = sqlStatement("SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec " .
            "FROM layout_group_properties WHERE " .
            "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 " .
            "ORDER BY grp_seq, grp_title");

        if (sqlNumRows($lres)) {
            if (!$StringEcho) {
                $StringEcho = '<ul id="sddm">';
            }
            $StringEcho .= "<li class=\"encounter-form-category-li\"><a href='JavaScript:void(0);' onClick=\"mopen('lbf');\" >" .
                xl('Informes') . "</a><div id='lbf' ><table border='0' cellspacing='0' cellpadding='0'>";
            while ($lrow = sqlFetchArray($lres)) {
                $option_id = $lrow['option_id']; // should start with LBF
                $title = $lrow['title'];
                // Check ACO attribute, if any, of this LBF.
                if (!empty($lrow['grp_aco_spec'])) {
                    $tmp = explode('|', $lrow['grp_aco_spec']);
                    if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                        continue;
                    }
                }
                $StringEcho .= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=\"openNewForm(" .
                    attr_js($rootdir . "/patient_file/encounter/load_form.php?formname=" . urlencode($option_id)) .
                    ", " . attr_js(xl_form_title($title)) . ")\" href='JavaScript:void(0);'>" .
                    text(xl_form_title($title)) . "</a></td></tr>";
            }
        }
    }
    ?>
    <!-- DISPLAYING HOOKS STARTS HERE -->
    <?php
    $module_query = sqlStatement("SELECT msh.*,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh LEFT OUTER JOIN modules_settings AS ms ON
                                    obj_name=enabled_hooks AND ms.mod_id=msh.mod_id LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='encounter' ORDER BY mod_id");
    $DivId = 'mod_installer';
    if (sqlNumRows($module_query)) {
        $jid = 0;
        $modid = '';
        while ($modulerow = sqlFetchArray($module_query)) {
            $DivId = 'mod_' . $modulerow['mod_id'];
            $new_category = $modulerow['mod_ui_name'];
            $modulePath = "";
            $added = "";
            if ($modulerow['type'] == 0) {
                $modulePath = $GLOBALS['customModDir'];
                $added = "";
            } else {
                $added = "index";
                $modulePath = $GLOBALS['zendModDir'];
            }
            $relative_link = "../../modules/" . $modulePath . "/" . $modulerow['path'];
            $nickname = $modulerow['menu_name'] ? $modulerow['menu_name'] : 'Noname';
            if ($jid == 0 || ($modid != $modulerow['mod_id'])) {
                if ($modid != '') {
                    $StringEcho .= '</table></div></li>';
                }
                $StringEcho .= "<li><a href='JavaScript:void(0);' onClick=\"mopen(" . attr_js($DivId) . ");\" >" . text($new_category) . "</a><div id='" . attr($DivId) . "' ><table border='0' cellspacing='0' cellpadding='0'>";
            }
            $jid++;
            $modid = $modulerow['mod_id'];
            $StringEcho .= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=" .
                "\"openNewForm(" . attr_js($relative_link) . ", " . attr_js(xl_form_title($nickname)) . ")\" " .
                "href='JavaScript:void(0);'>" . text(xl_form_title($nickname)) . "</a></td></tr>";
        }
    }
    ?>
    <!-- DISPLAYING HOOKS ENDS HERE -->
    <?php
    if ($StringEcho) {
        $StringEcho .= "</table></div></li></ul>" . $StringEcho2;
    }
    ?>
    <table cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td valign="top"><?php echo $StringEcho; ?></td>
        </tr>
    </table>
</dl>
<!-- Form menu stop -->
<!-- *************** -->

<div id="encounter_forms">

    <?php
    $dateres = getEncounterDateByEncounter($encounter);
    $encounter_date = date("Y-m-d", strtotime($dateres["date"]));
    $providerIDres = getProviderIdOfEncounter($encounter);
    $providerNameRes = getProviderName($providerIDres, false);
    ?>

    <div class='encounter-summary-container'>
        <div class='encounter-summary-column'>
            <div>
                <?php
                $pass_sens_squad = true;

                //fetch acl for category of given encounter
                $pc_catid = fetchCategoryIdByEncounter($encounter);
                $pc_eid = fetchEventIdByEncounter($encounter);
                $eventDetails = getEventDetails($pc_eid);
                $postCalendarCategoryACO = fetchPostCalendarCategoryACO($pc_catid);
                if ($postCalendarCategoryACO) {
                    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                    $authPostCalendarCategory = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1]);
                    $authPostCalendarCategoryWrite = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
                } else { // if no aco is set for category
                    $authPostCalendarCategory = true;
                    $authPostCalendarCategoryWrite = true;
                }
                ?>
                <table>
                    <tr>
                        <td>
                            <?php
                            if ($pc_catid == 15 || $pc_catid == 19) {
                                echo "<h2>Día Quirúrgico</h2>";

                                if ($eventDetails) {
                                    ?>
                                    <table class="procedure-table">
                                        <?php if (!empty($eventDetails['pc_apptqx'])) { ?>
                                            <tr>
                                                <td class="procedure-label">OD:</td>
                                                <td><?php echo $eventDetails['pc_apptqx']; ?></td>
                                                <td>
                                                    <a target='_blank'
                                                       href='<?php echo "$rootdir/forms/eye_mag/consentimiento_od.php?" .
                                                           "catid=" . urlencode($pc_catid) .
                                                           "&formid=" . urlencode(getLatestEyeFormID($pid)) .
                                                           "&visitid=" . urlencode($encounter) .
                                                           "&patientid=" . urlencode($pid) .
                                                           "&procedid=" . urlencode($eventDetails['pc_apptqx']); ?>'
                                                       class='css_button_small'
                                                       title='<?php echo xl('Documentos OD'); ?>'
                                                       onclick='top.restoreSession()'>
                                                        <span><?php echo xlt('Documentos OD'); ?></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <?php if (!empty($eventDetails['pc_apptqxOI'])) { ?>
                                            <tr>
                                                <td class="procedure-label">OI:</td>
                                                <td><?php echo $eventDetails['pc_apptqxOI']; ?></td>
                                                <td>
                                                    <a target='_blank'
                                                       href='<?php echo "$rootdir/forms/eye_mag/consentimiento_oi.php?" .
                                                           "catid=" . urlencode($pc_catid) .
                                                           "&formid=" . urlencode(getLatestEyeFormID($pid)) .
                                                           "&visitid=" . urlencode($encounter) .
                                                           "&patientid=" . urlencode($pid) .
                                                           "&procedid=" . urlencode($eventDetails['pc_apptqxOI']); ?>'
                                                       class='css_button_small'
                                                       title='<?php echo xl('Documentos OI'); ?>'
                                                       onclick='top.restoreSession()'>
                                                        <span><?php echo xlt('Documentos OI'); ?></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                    <?php
                                } else {
                                    echo "No se encontraron datos del procedimiento programado.";
                                }
                                // Verifica si se ha hecho clic en el botón
                                if ($eventDetails['pc_apptqx'] == 'faco' || $eventDetails['pc_apptqxOI'] == 'faco') {
                                    if (isset($_POST['agregar_formulario'])) {
                                        // Llama a la función addForm con los valores adecuados
                                        $newid = 0;
                                        $formname_prot = 'LBFprotocolo';
                                        $newid = sqlInsert("INSERT INTO lbf_data (field_id, field_value) VALUES (?, ?)", array('', ''));
                                        addForm($encounter, 'Protocolos', $newid, $formname_prot, $pid, $userauthorized);

                                        // Agregar los field_id y field_value específicos al nuevo formulario
                                        $fieldValueList = array(
                                            "Prot_anestesiologo" => "61",
                                            "Prot_Cirujano" => "22",
                                            "Prot_dieresis" => "incisioncroneal",
                                            "Prot_dxpost" => "ICD10:Z96.1",
                                            "Prot_dxpre" => "ICD10:H25",
                                            "Prot_expo" => "CamAnt",
                                            "Prot_halla" => "Cristalino Cataratoso",
                                            "Prot_hfin" => "9:00",
                                            "Prot_hini" => "7:00",
                                            "Prot_Instrumentistas" => "Si",
                                            "Prot_ojo" => "OD",
                                            "Prot_opp" => "faco",
                                            "Prot_opr" => "faco",
                                            "Prot_proced" => "ASEPSIA Y ANTISEPSIA<br>CAMPOS BLEFARO Y LAVADO<br>BAJO &amp;lt;b&amp;gt;MICROSCOPIO QUIRURGICO Y SISTEMA DE VISUALIZACION&amp;lt;/b&amp;gt; ... (continúa)"
                                        );

                                        // Insertar los field_id y field_value al nuevo formulario
                                        foreach ($fieldValueList as $field_id => $field_value) {
                                            sqlStatement("INSERT INTO lbf_data (form_id, field_id, field_value) VALUES (?, ?, ?)",
                                                array($newid, $field_id, $field_value));
                                        }

                                        // Actualizar el provider_id si es necesario
                                        if ($providerIDres > 0) {
                                            sqlStatement(
                                                "UPDATE forms SET provider_id = ? WHERE formdir = ? AND form_id = ? AND deleted = 0",
                                                array($providerIDres, $formname_prot, $newid)
                                            );
                                        }

                                        echo 'Formulario agregado con éxito.' . text($providerIDres);
                                    }


                                    ?>
                                    <form method="post">
                                        <input type="submit" name="agregar_formulario" value="Agregar Formulario">
                                    </form>
                                    <?php
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if ($attendant_type == 'pid' && is_numeric($pid)) {
                                echo '<span class="title">' . text(oeFormatShortDate($encounter_date)) . " " . xlt("Encounter");

                                // Check for no access to the patient's squad.
                                $result = getPatientData($pid, "fname,lname,lname2,squad");
                                echo " " . xlt('for') . " " . text($result['fname']) . " " . text($result['lname']) . " " . text($result['lname2']) . '</span>';
                                if ($result['squad'] && !acl_check('squads', $result['squad'])) {
                                    $pass_sens_squad = false;
                                }

                                // Check for no access to the encounter's sensitivity level.
                                $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
                                    "pid = ? AND encounter = ? LIMIT 1", array($pid, $encounter));
                                if (($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) || !$authPostCalendarCategory) {
                                    $pass_sens_squad = false;
                                }
                                // for therapy group
                            } else {
                                echo '<span class="title">' . text(oeFormatShortDate($encounter_date)) . " " . xlt("Group Encounter");
                                // Check for no access to the patient's squad.
                                $result = getGroup($groupId);
                                echo " " . xlt('for') . " " . text($result['group_name']) . '</span>';
                                if ($result['squad'] && !acl_check('squads', $result['squad'])) {
                                    $pass_sens_squad = false;
                                }
                                // Check for no access to the encounter's sensitivity level.
                                $result = sqlQuery("SELECT sensitivity FROM form_groups_encounter WHERE " .
                                    "group_id = ? AND encounter = ? LIMIT 1", array($groupId, $encounter));
                                if (($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) || !$authPostCalendarCategory) {
                                    $pass_sens_squad = false;
                                }
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div style='margin-top:8px;'>
                <?php
                // ESign for entire encounter
                $esign = $esignApi->createEncounterESign($encounter);
                if ($esign->isButtonViewable()) {
                    echo $esign->buttonHtml();
                }
                ?>
                <?php if (acl_check('admin', 'delete_form')) { ?>
                    <a href='#' class='css_button' onclick='return deleteme()'><span><?php echo xlt('Delete') ?></span></a>
                <?php } ?>
                &nbsp;&nbsp;&nbsp;<a href="#" onClick='expandcollapse("expand");'
                                     style="font-size:80%;"><?php echo xlt('Expand All'); ?></a>
                &nbsp;&nbsp;&nbsp;<a style="font-size:80%;" href="#"
                                     onClick='expandcollapse("collapse");'><?php echo xlt('Collapse All'); ?></a>
            </div>
        </div>
        <br><br>
        <div class='encounter-summary-column'>
            <?php if ($esign->isLogViewable()) {
                $esign->renderLog();
            } ?>
        </div>

    </div>

</div>
<div>
    <a href='<?php echo $web_root . "/controller.php?document&list&patient_id=" . $pid; ?>' class='css_button'
       onclick='top.restoreSession();'><span><?php echo xl('Cargar imágenes') ?></span></a>

</div>

<!-- Get the documents tagged to this encounter and display the links and notes as the tooltip -->
<?php
if ($attendant_type == 'pid') {
    $docs_list = getDocumentsByGroupByEncounter($pid, $_SESSION['encounter']);
} else {
    // already doesn't exist document for therapy groups
    $docs_list = array();
}
if (!empty($docs_list) && count($docs_list) > 0) {
    ?>
    <div class='enc_docs'>
        <table>
            <tr>
                <td>
                    <span class="bold"><?php echo xlt("Document(s)"); ?>:</span>

                </td>
                <td>
                    <a href='<?php echo $web_root . "/controller.php?document&list&patient_id=" . $pid; ?>'
                       class='css_button'
                       onclick='top.restoreSession();'><span><?php echo xl('Cargar imágenes') ?></span></a>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php
                    $doc = new C_Document();
                    foreach ($docs_list as $doc_iter) {
                        $doc_url = $doc->_tpl_vars['CURRENT_ACTION'] . "&view&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_iter['id']) . "&";
                        // Get notes for this document.
                        $queryString = "SELECT GROUP_CONCAT(note ORDER BY date DESC SEPARATOR '|') AS docNotes, GROUP_CONCAT(date ORDER BY date DESC SEPARATOR '|') AS docDates
			FROM notes WHERE foreign_id = ? GROUP BY foreign_id";
                        $noteData = sqlQuery($queryString, array($doc_iter['id']));
                        $note = '';
                        if ($noteData) {
                            $notes = array();
                            $notes = explode("|", $noteData['docNotes']);
                            $dates = explode("|", $noteData['docDates']);
                            for ($i = 0; $i < count($notes); $i++) {
                                $note .= oeFormatShortDate(date('Y-m-d', strtotime($dates[$i]))) . " : " . $notes[$i] . "\n";
                            }
                        }
                        ?>
                        <br>
                        <a href="images_slider.php" class='css_button_small' title='Examenes de Imagenes'
                           target="_blank"
                           style="font-size:small;background-color: orangered;"><?php echo "Imágenes de " . xlt($doc_iter[name]); ?></a>
                        <?php if ($note != '') { ?>
                            <a href="javascript:void(0);" title="<?php echo attr($note); ?>"><img
                                    src="<?php echo $GLOBALS['images_static_relative']; ?>/info.png"/></a>
                        <?php } ?>
                        <?php

                    } ?>
                </td>
            </tr>
        </table>


    </div>
<?php } ?>
<br/>

<?php
if ($pass_sens_squad &&
    ($result = getFormByEncounter(
        $attendant_id,
        $encounter,
        "id, date, form_id, form_name, formdir, user, provider_id, authorized, deleted",
        "",
        "FIND_IN_SET(formdir,'newpatient') DESC, date ASC"
    ))) {
    echo "<table width='100%' id='partable'>";
    $divnos = 1;
    foreach ($result as $iter) {
        $formdir = $iter['formdir'];

        // skip forms whose 'deleted' flag is set to 1
        if ($iter['deleted'] == 1) {
            continue;
        }

        $aco_spec = false;

        if (substr($formdir, 0, 3) == 'LBF') {
            // Skip LBF forms that we are not authorized to see.
            $lrow = sqlQuery(
                "SELECT grp_aco_spec " .
                "FROM layout_group_properties WHERE " .
                "grp_form_id = ? AND grp_group_id = '' AND grp_activity = 1",
                array($formdir)
            );
            if (!empty($lrow)) {
                if (!empty($lrow['grp_aco_spec'])) {
                    $aco_spec = explode('|', $lrow['grp_aco_spec']);
                    if (!acl_check($aco_spec[0], $aco_spec[1])) {
                        continue;
                    }
                }
            }
        } else {
            // Skip non-LBF forms that we are not authorized to see.
            $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
            if (!empty($tmp['aco_spec'])) {
                $aco_spec = explode('|', $tmp['aco_spec']);
                if (!acl_check($aco_spec[0], $aco_spec[1])) {
                    continue;
                }
            }
        }

        // $form_info = getFormInfoById($iter['id']);
        if (strtolower(substr($iter['form_name'], 0, 6)) == 'receta') {
            //RECETA generates links from report.php and these links should
            //be clickable without causing view.php to come up unexpectedly.
            //I feel that the JQuery code in this file leading to a click
            //on the report.php content to bring up view.php steps on a
            //form's autonomy to generate it's own html content in it's report
            //but until any other form has a problem with this, I will just
            //make an exception here for RECETA and allow it to carry out this
            //functionality for all other forms.  --Mark
            echo '<tr title="' . xla('Edit form') . '" ' .
                'id="' . attr($formdir) . '~' . attr($iter['form_id']) . '">';
        } else {
            echo '<tr id="' . attr($formdir) . '~' . attr($iter['form_id']) . '" class="text onerow">';
        }

        $acl_groups = acl_check("groups", "glog", false, 'write') ? true : false;
        $user = getNameFromUsername($iter['user']);

        $form_name = ($formdir == 'newpatient') ? xl('Visit Summary') : xl_form_title($iter['form_name']);

        // Create the ESign instance for this form
        $esign = $esignApi->createFormESign($iter['id'], $formdir, $encounter);

        // echo "<tr>"; // Removed as bug fix.

        echo "<td style='border-bottom:1px solid'>";

        // Figure out the correct author (encounter authors are the '$providerNameRes', while other
        // form authors are the '$user['fname'] . "  " . $user['lname']').
        if ($formdir == 'newpatient') {
            $form_author = $providerNameRes;
        } else {
            $form_author = $user['suffix'] . " " . $user['fname'] . "  " . $user['lname'];
        }
        if (substr($formdir, 0, 3) == 'LBF') {
            $form_author = getProviderName($iter['provider_id']);
        }
        if ($formdir == 'eye_mag') {
            $form_author = getProviderName($iter['authorized']);
        }
        echo "<div class='form_header'>";
        echo "<a href='javascript:void(0);' onclick='divtoggle(" . attr_js('spanid_' . $divnos) . "," . attr_js('divid_' . $divnos) . ");' class='small' id='aid_" . attr($divnos) . "'>" .
            "<div class='formname'>" . text($form_name) . "</div> " .
            xlt('by') . " " . text($form_author) . " - <b>" . date("d-m-Y", strtotime($iter['date'])) . "</b>" .
            "(<span id=spanid_" . attr($divnos) . " class=\"indicator\">" . ($divnos == 1 ? xlt('Collapse') : xlt('Expand')) . "</span>)</a>";
        echo "</div>";

        // a link to edit the form
        echo "<div class='form_header_controls'>";

        // If the form is locked, it is no longer editable
        if ($esign->isLocked()) {
            echo "<a href=# class='css_button_small form-edit-button-locked' id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "'><span>" . xlt('Locked') . "</span></a>";
        } else {
            if ((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write') and $is_group == 0 and $authPostCalendarCategoryWrite)
                or (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and acl_check("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)) {
                echo "<a class='css_button_small form-edit-button' " .
                    "id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "' " .
                    "href='#' " .
                    "title='" . xla('Edit this form') . "' " .
                    "onclick=\"return openEncounterForm(" . attr_js($formdir) . ", " .
                    attr_js($form_name) . ", " . attr_js($iter['form_id']) . ")\">";
                echo "<span>" . xlt('Edit') . "</span></a>";
            }
        }

        if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
            if (!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) {
                echo $esign->buttonHtml();
            }
        }

        if (substr($formdir, 0, 3) == 'LBF') {
            // A link for a nice printout of the LBF
            echo "<a target='_blank' " .
                "href='$rootdir/forms/LBF/printable.php?" .
                "formname=" . attr_url($formdir) .
                "&formid=" . attr_url($iter['form_id']) .
                "&visitid=" . attr_url($encounter) .
                "&patientid=" . attr_url($pid) .
                "' class='css_button_small' title='" . xla('Print this form') .
                "' onclick='top.restoreSession()'><span>" . xlt('Print') . "</span></a>";
        }

        if (substr($formdir, 0, 12) == 'LBFprotocolo') {
            // A link for a nice printout of the LBF
            echo "<a target='_blank' " .
                "href='$rootdir/forms/LBF/protocolo.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Protocolo Quirugico') .
                "' onclick='top.restoreSession()'><span>" . xlt('Protocolo') . "</span></a>";
        }

        if (substr($formdir, 0, 12) == 'LBFprotocolo') {
            // A link for a nice printout of the LBF
            echo "<a target='_blank' " .
                "href='$rootdir/forms/LBF/transanestesico.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Trans-anestésico') .
                "' onclick='top.restoreSession()'><span>" . xlt('Trans-anestésico') . "</span></a>";
        }

        //if (substr($formdir, 0, 7) == 'eye_mag') {
        // A link for a nice printout of the treatment plan
        //    echo "<a target='_blank' " .
        //        "href='$rootdir/forms/eye_mag/consentimiento_od.php?" .
        //        "formname=" . urlencode($formdir) .
        //        "&formid=" . urlencode($iter['form_id']) .
        //        "&visitid=" . urlencode($encounter) .
        //        "&patientid=" . urlencode($pid) .
        //        "' class='css_button_small' title='" . xl('Consentimiento OD') .
        //        "' onclick='top.restoreSession()'><span>" . xlt('Consentimiento OD') . "</span></a>";
        //}

        //if (substr($formdir, 0, 7) == 'eye_mag') {
        // A link for a nice printout of the treatment plan
        //    echo "<a target='_blank' " .
        //        "href='$rootdir/forms/eye_mag/consentimiento_oi.php?" .
        //        "formname=" . urlencode($formdir) .
        //        "&formid=" . urlencode($iter['form_id']) .
        //        "&visitid=" . urlencode($encounter) .
        //        "&patientid=" . urlencode($pid) .
        //        "' class='css_button_small' title='" . xl('Consentimiento OI') .
        //        "' onclick='top.restoreSession()'><span>" . xlt('Consentimiento OI') . "</span></a>";
        //}

        if (substr($formdir, 0, 9) == 'care_plan') {
            // A link for a nice printout of the treatment plan
            echo "<a target='_blank' " .
                "href='$rootdir/forms/care_plan/printable.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Informe Procedimiento') .
                "' onclick='top.restoreSession()'><span>" . xlt('Informe Procedimiento') . "</span></a>";
        }

        if (substr($formdir, 0, 15) == 'procedure_order') {
            // A link for a nice printout of the treatment plan
            echo "<a target='_blank' " .
                "href='$rootdir/forms/procedure_order/autorizacion.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Autorizacion') .
                "' onclick='top.restoreSession()'><span>" . xlt('Autorizacion') . "</span></a>";
        }

        if (substr($formdir, 0, 15) == 'procedure_order') {
            // A link for a nice printout of the treatment plan
            echo "<a target='_blank' " .
                "href='$rootdir/forms/procedure_order/medico_tecnico.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Inf. Médico Técnico') .
                "' onclick='top.restoreSession()'><span>" . xlt('Inf. Médico Técnico') . "</span></a>";
        }

        if (substr($formdir, 0, 15) == 'procedure_order') {
            // A link for a nice printout of the treatment plan
            echo "<a target='_blank' " .
                "href='$rootdir/forms/procedure_order/referencia.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Referencia') .
                "' onclick='top.restoreSession()'><span>" . xlt('Referencia') . "</span></a>";
        }

        if (substr($formdir, 0, 10) == 'newpatient') {
            // A link for a nice printout of the Encuentro
            echo "<a target='_blank' " .
                "href='$rootdir/forms/newpatient/parte.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Acta de entrega de Recepción de Servicios') .
                "' onclick='top.restoreSession()'><span>" . xlt('Acta de Recepción de Servicios') . "</span></a>";
        }

        if (substr($formdir, 0, 7) == 'eye_mag') {
            // A link for a nice printout of the Encuentro
            echo "<a target='_blank' " .
                "href='$rootdir/forms/eye_mag/printable.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Imprimir Informe') .
                "' onclick='top.restoreSession()'><span>" . xlt('Imprimir Informe') . "</span></a>";
        }
        if (substr($formdir, 0, 7) == 'eye_mag') {
            // JavaScript function to open multiple pages
            echo "<script>
            function openMultiplePages() {
                window.open('$rootdir/forms/eye_mag/preanestesico.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "', '_blank');

                window.open('$rootdir/forms/eye_mag/consentimiento_od.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "', '_blank');
            }
          </script>";

            // A link to open multiple pages
            echo "<a href='#' class='css_button_small' onclick='openMultiplePages(); return false;' title='" . xl('Abrir múltiples páginas') . "'><span>" . xlt('Abrir múltiples páginas') . "</span></a>";
        }

        ?>
        <div id="popup-overlay" class="popup-overlay">
            <div class="popup-content">
                <h2 class="popup-title">Seleccionar Documento</h2>

                <div class="popup-link">
                    <a href="ruta/consentimientos.pdf">Consentimientos</a>
                    <button><img src="icono-consentimientos.png" alt="Consentimientos"></button>
                </div>

                <div class="popup-link">
                    <a href="ruta/no_invasivos.pdf">No Invasivos</a>
                    <button><img src="icono-no-invasivos.png" alt="No Invasivos"></button>
                </div>

                <div class="popup-link">
                    <a href="ruta/formulario_012.pdf">Formulario 012</a>
                    <button><img src="icono-formulario-012.png" alt="Formulario 012"></button>
                </div>

                <div class="popup-link">
                    <a href="ruta/informe_medico.pdf">Informe Medico</a>
                    <button><img src="icono-informe-medico.png" alt="Informe Medico"></button>
                </div>

                <div class="popup-link">
                    <a href="ruta/certificados.pdf">Certificados</a>
                    <button><img src="icono-certificados.png" alt="Certificados"></button>
                </div>

                <div class="popup-link">
                    <a href="#">Todos</a>
                    <button><img src="icono-todos.png" alt="Todos"></button>
                </div>

                <div id="popup-close">
                    <button onclick="closePopup()">Cerrar</button>
                </div>
            </div>
        </div>

        <?php

        if (substr($formdir, 0, 7) == 'eye_mag') {
            // A link for a nice printout of the Encuentro
            echo "<a target='_blank' " .
                "href='$rootdir/forms/eye_mag/Formulario_012.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Formulario de Imagenes 012') .
                "' onclick='top.restoreSession()'><span>" . xlt('F012') . "</span></a>";
        }

        if (substr($formdir, 0, 7) == 'eye_mag') {
            // A link for a nice printout of the Encuentro
            echo "<a target='_blank' " .
                "href='$rootdir/forms/eye_mag/007.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Formulario 007') .
                "' onclick='top.restoreSession()'><span>" . xlt('F007') . "</span></a>";
        }

        if (substr($formdir, 0, 15) == 'treatment_plan') {
            // A link for a nice printout of the Encuentro
            echo "<a target='_blank' " .
                "href='$rootdir/forms/treatment_plan/egreso.php?" .
                "formname=" . urlencode($formdir) .
                "&formid=" . urlencode($iter['form_id']) .
                "&visitid=" . urlencode($encounter) .
                "&patientid=" . urlencode($pid) .
                "' class='css_button_small' title='" . xl('Plan de Egreso') .
                "' onclick='top.restoreSession()'><span>" . xlt('Plan de Egreso') . "</span></a>";
        }

        if (acl_check('admin', 'delete_form')) {
            if ($formdir != 'newpatient' && $formdir != 'newGroupEncounter') {
                // a link to delete the form from the encounter
                echo "<a href='$rootdir/patient_file/encounter/delete_form.php?" .
                    "formname=" . attr_url($formdir) .
                    "&id=" . attr_url($iter['id']) .
                    "&encounter=" . attr_url($encounter) .
                    "&pid=" . attr_url($pid) .
                    "' class='css_button_small' title='" . xla('Delete this form') . "' onclick='top.restoreSession()'><span>" . xlt('Delete') . "</span></a>";
            } else {
                // do not show delete button for main encounter here since it is displayed at top
            }
        }
        echo "</div>\n"; // Added as bug fix.

        echo "</td>\n";
        echo "</tr>";
        echo "<tr>";
        echo "<td valign='top' class='formrow'><div class='tab' id='divid_" . attr($divnos) . "' ";
        echo "style='display:" . ($divnos == 1 ? 'block' : 'none') . "'>";

        // Use the form's report.php for display.  Forms with names starting with LBF
        // are list-based forms sharing a single collection of code.
        //
        if (substr($formdir, 0, 3) == 'LBF') {
            include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

            call_user_func("lbf_report", $attendant_id, $encounter, 2, $iter['form_id'], $formdir, true);
        } else {
            include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
            call_user_func($formdir . "_report", $attendant_id, $encounter, 2, $iter['form_id']);
        }

        if ($esign->isLogViewable()) {
            $esign->renderLog();
        }

        echo "</div></td></tr>";
        $divnos = $divnos + 1;
    }
    echo "</table>";
}
if (!$pass_sens_squad) {
    echo xlt("Not authorized to view this encounter");
}
?>
<div>
    <?php
    echo "<a target='_blank' " .
        "href='$rootdir/patient_file/report/contrareferencia.php?" .
        "visitid=" . urlencode($encounter) .
        "&patientid=" . urlencode($pid) .
        "' class='css_button' title='" . xl('Contrarreferencia PDF') .
        "' onclick='top.restoreSession()'><span>" . xlt('Contrarreferencia PDF') . "</span></a>";
    ?>
</div>
</div> <!-- end large encounter_forms DIV -->
</body>
</html>
