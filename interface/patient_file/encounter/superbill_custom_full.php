<?php
/**
 * Provides manual administration for codes
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

// gacl control
$thisauthview = acl_check('admin', 'superbill', false, 'view');
$thisauthwrite = acl_check('admin', 'superbill', false, 'write');

if (!($thisauthwrite || $thisauthview)) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}
// For revenue codes
$institutional = $GLOBALS['ub04_support'] == "1" ? true : false;

// Translation for form fields.
function ffescape($field)
{
    $field = add_escape_custom($field);
    return trim($field);
}

// Format dollars for display.
//
function bucks($amount)
{
    if ($amount) {
        $amount = oeFormatMoney($amount);
        return $amount;
    }

    return '';
}

$alertmsg = '';
$pagesize = 100;
$mode = $_POST['mode'];
$code_id = 0;
$related_code = '';
$active = 1;
$reportable = 0;
$financial_reporting = 0;
$revenue_code = '';
$code_text_short = '';

if (isset($mode) && $thisauthwrite) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $code_id    = empty($_POST['code_id']) ? '' : $_POST['code_id'] + 0;
    $code       = $_POST['code'];
    $code_type  = $_POST['code_type'];
    $code_text  = $_POST['code_text'];
    $code_text_short = $_POST['code_text_short'];
    $modifier   = $_POST['modifier'];
    $superbill  = $_POST['form_superbill'];
    $related_code = $_POST['related_code'];
    $cyp_factor = is_numeric($_POST['cyp_factor']) ? $_POST['cyp_factor'] + 0 : 0;
    $active     = empty($_POST['active']) ? 0 : 1;
    $reportable = empty($_POST['reportable']) ? 0 : 1; // dx reporting
    $financial_reporting = empty($_POST['financial_reporting']) ? 0 : 1; // financial service reporting
    $revenue_code = $_POST['revenue_code'];

    $taxrates = "";
    if (!empty($_POST['taxrate'])) {
        foreach ($_POST['taxrate'] as $key => $value) {
            $taxrates .= "$key:";
        }
    }

    if ($mode == "delete") {
        sqlStatement("DELETE FROM codes WHERE id = ?", array($code_id));
        $code_id = 0;
    } else if ($mode == "add" || $mode == "modify_complete") { // this covers both adding and modifying
        $crow = sqlQuery("SELECT COUNT(*) AS count FROM codes WHERE " .
            "code_type = '"    . ffescape($code_type)    . "' AND " .
            "code = '"         . ffescape($code)         . "' AND " .
            "modifier = '"     . ffescape($modifier)     . "' AND " .
            "id != '"          . add_escape_custom($code_id) . "'");
        if ($crow['count']) {
            $alertmsg = xl('Cannot add/update this entry because a duplicate already exists!');
        } else {
            $sql =
                "code = '"         . ffescape($code)         . "', " .
                "code_type = '"    . ffescape($code_type)    . "', " .
                "code_text = '"    . ffescape($code_text)    . "', " .
                "code_text_short = '" . ffescape($code_text_short) . "', " .
                "modifier = '"     . ffescape($modifier)     . "', " .
                "superbill = '"    . ffescape($superbill)    . "', " .
                "related_code = '" . ffescape($related_code) . "', " .
                "cyp_factor = '"   . ffescape($cyp_factor)   . "', " .
                "taxrates = '"     . ffescape($taxrates)     . "', " .
                "active = "        . add_escape_custom($active) . ", " .
                "financial_reporting = " . add_escape_custom($financial_reporting) . ", " .
                "revenue_code = '" . ffescape($revenue_code) . "', " .
                "reportable = '"    . add_escape_custom($reportable) . "' ";
            if ($code_id) {
                $query = "UPDATE codes SET $sql WHERE id = ?";
                sqlStatement($query, array($code_id));
                sqlStatement("DELETE FROM prices WHERE pr_id = ? AND " .
                    "pr_selector = ''", array($code_id));
            } else {
                $code_id = sqlInsert("INSERT INTO codes SET $sql");
            }

            if (!$alertmsg) {
                foreach ($_POST['fee'] as $key => $value) {
                    $value = $value + 0;
                    if ($value) {
                        sqlStatement("INSERT INTO prices ( " .
                            "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
                            "?, '', ?, ?)", array($code_id,$key,$value));
                    }
                }

                $code = $code_type = $code_text = $code_text_short = $modifier = $superbill = "";
                $code_id = 0;
                $related_code = '';
                $cyp_factor = 0;
                $taxrates = '';
                $active = 1;
                $reportable = 0;
                $revenue_code = '';
            }
        }
    } else if ($mode == "edit") { // someone clicked [Edit]
        $sql = "SELECT * FROM codes WHERE id = ?";
        $results = sqlStatement($sql, array($code_id));
        while ($row = sqlFetchArray($results)) {
            $code         = $row['code'];
            $code_text    = $row['code_text'];
            $code_text_short = $row['code_text_short'];
            $code_type    = $row['code_type'];
            $modifier     = $row['modifier'];
            // $units        = $row['units'];
            $superbill    = $row['superbill'];
            $related_code = $row['related_code'];
            $revenue_code = $row['revenue_code'];
            $cyp_factor   = $row['cyp_factor'];
            $taxrates     = $row['taxrates'];
            $active       = 0 + $row['active'];
            $reportable   = 0 + $row['reportable'];
            $financial_reporting  = 0 + $row['financial_reporting'];
        }
    } else if ($mode == "modify") { // someone clicked [Modify]
        // this is to modify external code types, of which the modifications
        // are stored in the codes table
        $code_type_name_external = $_POST['code_type_name_external'];
        $code_external = $_POST['code_external'];
        $code_id = $_POST['code_id'];
        $results = return_code_information($code_type_name_external, $code_external, false); // only will return one item
        while ($row = sqlFetchArray($results)) {
            $code         = $row['code'];
            $code_text    = $row['code_text'];
            $code_text_short = $row['code_text_short'];
            $code_type    = $code_types[$code_type_name_external]['id'];
            $modifier     = $row['modifier'];
            // $units        = $row['units'];
            $superbill    = $row['superbill'];
            $related_code = $row['related_code'];
            $revenue_code = $row['revenue_code'];
            $cyp_factor   = $row['cyp_factor'];
            $taxrates     = $row['taxrates'];
            $active       = $row['active'];
            $reportable   = $row['reportable'];
            $financial_reporting  = $row['financial_reporting'];
        }
    }

    // If codes history is enabled in the billing globals save data to codes history table
    if ($GLOBALS['save_codes_history'] && $alertmsg=='' &&
        ( $mode == "add" || $mode == "modify_complete" || $mode == "delete" ) ) {
        $action_type= empty($_POST['code_id']) ? 'new' : $mode;
        $action_type= ($action_type=='add') ? 'update' : $action_type ;
        $code       = $_POST['code'];
        $code_type  = $_POST['code_type'];
        $code_text  = $_POST['code_text'];
        $code_text_short = $_POST['code_text_short'];
        $modifier   = $_POST['modifier'];
        $superbill  = $_POST['form_superbill'];
        $related_code = $_POST['related_code'];
        $revenue_code = $_POST['revenue_code'];
        $cyp_factor = $_POST['cyp_factor'] + 0;
        $active     = empty($_POST['active']) ? 0 : 1;
        $reportable = empty($_POST['reportable']) ? 0 : 1; // dx reporting
        $financial_reporting = empty($_POST['financial_reporting']) ? 0 : 1; // financial service reporting
        $fee=json_encode($_POST['fee']);
        $code_sql= sqlFetchArray(sqlStatement("SELECT (ct_label) FROM code_types WHERE ct_id=?", array($code_type)));
        $code_name='';

        if ($code_sql) {
            $code_name=$code_sql['ct_label'];
        }

        $categorey_id= $_POST['form_superbill'];
        $categorey_sql=sqlFetchArray(sqlStatement("SELECT (title ) FROM list_options WHERE list_id='superbill'".
            " AND option_id=?", array($categorey_id)));

        $categorey_name='';

        if ($categorey_sql) {
            $categorey_name=$categorey_sql['title'];
        }

        $date=date('Y-m-d H:i:s');
        $date=oeFormatShortDate($date);
        $results =  sqlStatement(
            "INSERT INTO codes_history ( " .
            "date, code, modifier, active,diagnosis_reporting,financial_reporting,category,code_type_name,".
            "code_text,code_text_short,prices,action_type, update_by ) VALUES ( " .
            "?, ?,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,?)",
            array($date,$code,$modifier,$active,$reportable,$financial_reporting,$categorey_name,$code_name,$code_text,$code_text_short,$fee,$action_type,$_SESSION['authUser'])
        );
    }
}

$related_desc = '';
if (!empty($related_code)) {
    $related_desc = $related_code;
}

$fstart = $_REQUEST['fstart'] + 0;
if (isset($_REQUEST['filter'])) {
    $filter = array();
    $filter_key = array();
    foreach ($_REQUEST['filter'] as $var) {
        $var = $var+0;
        array_push($filter, $var);
        $var_key = convert_type_id_to_key($var);
        array_push($filter_key, $var_key);
    }
}

$search = $_REQUEST['search'];
$search_reportable = $_REQUEST['search_reportable'];
$search_financial_reporting = $_REQUEST['search_financial_reporting'];

//Build the filter_elements array
$filter_elements = array();
if (!empty($search_reportable)) {
    $filter_elements['reportable'] = $search_reportable;
}

if (!empty($search_financial_reporting)) {
    $filter_elements['financial_reporting'] = $search_financial_reporting;
}

if (isset($_REQUEST['filter'])) {
    $count = main_code_set_search($filter_key, $search, null, null, false, null, true, null, null, $filter_elements);
}

if ($fstart >= $count) {
    $fstart -= $pagesize;
}

if ($fstart < 0) {
    $fstart = 0;
}

$fend = $fstart + $pagesize;
if ($fend > $count) {
    $fend = $count;
}
?>

<html>
<head>
    <title><?php echo xlt("Codes"); ?></title>

    <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
    <script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="../../../library/textformat.js"></script>
    <script type="text/JavaScript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-themes/themes/base/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui/jquery-ui.min.js"></script>
<style>
    .ui-autocomplete { max-height: 350px; max-width: 35%; overflow-y: auto; overflow-x: hidden; }
    body.body_top {
        margin: 0;
        background: #f4f6f8;
        color: #1f2933;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    .page-shell {
        max-width: 1360px;
        margin: 0 auto;
        padding: 24px;
    }
    .page-header {
        margin-bottom: 20px;
    }
    .page-title {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.1;
        color: #102a43;
    }
    .page-subtitle {
        margin: 0;
        font-size: 14px;
        color: #52606d;
    }
    .panel-grid {
        display: grid;
        grid-template-columns: minmax(340px, 420px) minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }
    .panel {
        background: #fff;
        border: 1px solid #d9e2ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .panel-header {
        padding: 18px 20px 10px;
        border-bottom: 1px solid #e5edf5;
    }
    .panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #102a43;
    }
    .panel-copy {
        margin: 6px 0 0;
        font-size: 13px;
        color: #52606d;
    }
    .panel-body {
        padding: 20px;
    }
    .form-section + .form-section,
    .filter-grid + .toolbar-row,
    .results-toolbar + .table-wrap,
    .form-actions {
        margin-top: 18px;
    }
    .section-title {
        margin: 0 0 12px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7b8794;
    }
    .field-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 14px 12px;
    }
    .field {
        grid-column: span 12;
    }
    .field.span-6 {
        grid-column: span 6;
    }
    .field.span-4 {
        grid-column: span 4;
    }
    .field.span-8 {
        grid-column: span 8;
    }
    .field label,
    .checkbox-group-title {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #243b53;
    }
    .field input[type="text"],
    .field select,
    .filter-grid input[type="text"],
    .filter-grid select {
        width: 100%;
        min-height: 40px;
        padding: 9px 12px;
        border: 1px solid #bcccdc;
        border-radius: 10px;
        background: #fff;
        box-sizing: border-box;
    }
    .field-inline {
        display: flex;
        gap: 10px;
        align-items: end;
    }
    .field-inline .field {
        margin: 0;
    }
    .checkbox-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
        align-items: center;
    }
    .checkbox-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #243b53;
    }
    .checkbox-item input {
        margin: 0;
    }
    .fee-grid,
    .tax-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }
    .chip-field {
        padding: 12px;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        background: #f8fbff;
    }
    .chip-field span {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #486581;
    }
    .chip-field input[type="text"] {
        width: 100%;
        min-height: 38px;
        padding: 8px 10px;
        border: 1px solid #bcccdc;
        border-radius: 8px;
        box-sizing: border-box;
    }
    .form-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border: 0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        opacity: 1 !important;
        box-shadow: none;
    }
    .btn-primary {
        background: #0b6e4f !important;
        color: #fff !important;
    }
    .btn-secondary {
        background: #1f5f8b !important;
        color: #fff !important;
    }
    .btn-danger {
        background: #c0392b !important;
        color: #fff !important;
    }
    .btn:hover,
    .btn:focus,
    .btn:visited {
        opacity: 1 !important;
        text-decoration: none;
        color: inherit;
    }
    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:visited {
        color: #fff !important;
    }
    .btn-secondary:hover,
    .btn-secondary:focus,
    .btn-secondary:visited,
    .btn-danger:hover,
    .btn-danger:focus,
    .btn-danger:visited {
        color: #fff !important;
    }
    .filters-card {
        margin-bottom: 20px;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: minmax(180px, 260px) minmax(160px, 220px) minmax(0, 1fr);
        gap: 12px;
        align-items: end;
    }
    .toolbar-row,
    .results-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .results-count {
        font-size: 13px;
        color: #52606d;
    }
    .pager {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pager a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        background: #eef2f6;
        color: #102a43;
        text-decoration: none;
        font-weight: 700;
    }
    .table-wrap {
        overflow-x: auto;
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        background: #fff;
    }
    .results-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1180px;
    }
    .results-table th,
    .results-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #e5edf5;
        vertical-align: top;
        text-align: left;
        font-size: 13px;
    }
    .results-table th {
        position: sticky;
        top: 0;
        background: #f8fbff;
        color: #243b53;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .results-table tr:hover td {
        background: #f8fbff;
    }
    .results-table td.align-right,
    .results-table th.align-right {
        text-align: right;
    }
    .row-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        white-space: nowrap;
    }
    .muted {
        color: #7b8794;
    }
    @media (max-width: 1100px) {
        .panel-grid,
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
    <script>
    <?php if ($institutional) { ?>
    $( function() {
        var cache = {};
        $( ".revcode" ).autocomplete({
            minLength: 1,
            source: function( request, response ) {
                var term = request.term;
                request.code_group = "revenue_code";
                if ( term in cache ) {
                  response( cache[ term ] );
                  return;
                }
                $.getJSON( "<?php echo $GLOBALS['web_root'] ?>/interface/billing/ub04_helpers.php", request, function( data, status, xhr ) {
                  cache[ term ] = data;
                  response( data );
                });
            }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
    });
    <?php } ?>

        // This is for callback by the find-code popup.
        // Appends to or erases the current list of related codes.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = f.related_code.value;
            if (code) {
                if (s.length > 0) s += ';';
                s += codetype + ':' + code;
            } else {
                s = '';
            }
            f.related_code.value = s;
            f.related_desc.value = s;
        }

        // This is for callback by the find-code popup.
        // Returns the array of currently selected codes with each element in codetype:code format.
        function get_related() {
            return document.forms[0].related_code.value.split(';');
        }

        // This is for callback by the find-code popup.
        // Deletes the specified codetype:code from the currently selected list.
        function del_related(s) {
            my_del_related(s, document.forms[0].related_code, false);
            my_del_related(s, document.forms[0].related_desc, false);
        }

        // This invokes the find-code popup.
        function sel_related() {
            var f = document.forms[0];
            var i = f.code_type.selectedIndex;
            var codetype = '';
            if (i >= 0) {
                var myid = f.code_type.options[i].value;
                <?php
                foreach ($code_types as $key => $value) {
                    $codeid = $value['id'];
                    $coderel = $value['rel'];
                    if (!$coderel) {
                        continue;
                    }

                    echo "  if (myid == $codeid) codetype = '$coderel';";
                }
                ?>
            }
            if (!codetype) {
                alert(<?php echo xlj('This code type does not accept relations.'); ?>);
                return;
            }
            dlgopen('find_code_dynamic.php', '_blank', 900, 600);
        }

        // Some validation for saving a new code entry.
        function validEntry(f) {
            if (!f.code.value) {
                alert(<?php echo xlj('No code was specified!'); ?>);
                return false;
            }
            <?php if ($GLOBALS['ippf_specific']) { ?>
            if (f.code_type.value == 12 && !f.related_code.value) {
                alert(<?php echo xlj('A related IPPF code is required!'); ?>);
                return false;
            }
            <?php } ?>
            return true;
        }

        function submitAdd() {
            var f = document.forms[0];
            if (!validEntry(f)) return;
            f.mode.value = 'add';
            f.code_id.value = '';
            f.submit();
        }

        function submitUpdate() {
            var f = document.forms[0];
            if (! parseInt(f.code_id.value)) {
                alert(<?php echo xlj('Cannot update because you are not editing an existing entry!'); ?>);
                return;
            }
            if (!validEntry(f)) return;
            f.mode.value = 'add';
            f.submit();
        }

        function submitModifyComplete() {
            var f = document.forms[0];
            f.mode.value = 'modify_complete';
            f.submit();
        }

        function submitList(offset) {
            var f = document.forms[0];
            var i = parseInt(f.fstart.value) + offset;
            if (i < 0) i = 0;
            f.fstart.value = i;
            f.submit();
        }

        function submitEdit(id) {
            var f = document.forms[0];
            f.mode.value = 'edit';
            f.code_id.value = id;
            f.submit();
        }

        function submitModify(code_type_name,code,id) {
            var f = document.forms[0];
            f.mode.value = 'modify';
            f.code_external.value = code;
            f.code_id.value = id;
            f.code_type_name_external.value = code_type_name;
            f.submit();
        }



        function submitDelete(id) {
            var f = document.forms[0];
            f.mode.value = 'delete';
            f.code_id.value = id;
            f.submit();
        }

        function getCTMask() {
            var ctid = document.forms[0].code_type.value;
            <?php
            foreach ($code_types as $key => $value) {
                $ctid   = $value['id'];
                $ctmask = $value['mask'];
                echo " if (ctid == " . js_escape($ctid) . ") return " . js_escape($ctmask) . ";\n";
            }
            ?>
            return '';
        }

    </script>

</head>
<body class="body_top" >
<div class="page-shell">
    <div class="page-header">
        <h1 class="page-title"><?php echo xlt("Codes"); ?></h1>
        <p class="page-subtitle"><?php echo xlt('Manage superbill codes, pricing levels, reporting flags and related mappings from one screen.'); ?></p>
    </div>

    <form method='post' action='superbill_custom_full.php' name='theform'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <input type='hidden' name='mode' value=''>
        <input type='hidden' name='fstart' value='<?php echo attr($fstart) ?>'>

        <div class="panel filters-card">
            <div class="panel-header">
                <h2 class="panel-title"><?php echo xlt('Search and Filters'); ?></h2>
                <p class="panel-copy"><?php echo xlt('Narrow the list before editing or creating a code.'); ?></p>
            </div>
            <div class="panel-body">
                <div class="filter-grid">
                    <div class="field">
                        <label for="filter-code-types"><?php echo xlt('Code Types'); ?></label>
                        <select id="filter-code-types" name='filter[]' multiple='multiple'>
                            <?php
                            foreach ($code_types as $key => $value) {
                                echo "<option value='" . attr($value['id']) . "'";
                                if (isset($filter) && in_array($value['id'], $filter)) {
                                    echo " selected";
                                }

                                echo ">" . xlt($value['label']) . "</option>\n";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="search-input"><?php echo xlt('Search'); ?></label>
                        <input id="search-input" type="text" name="search" value="<?php echo attr($search) ?>">
                    </div>
                    <div class="field">
                        <div class="checkbox-group-title"><?php echo xlt('Quick Filters'); ?></div>
                        <div class="checkbox-row">
                            <label class="checkbox-item" title="<?php echo xla("Only Show Diagnosis Reporting Codes") ?>">
                                <input type='checkbox' name='search_reportable' value='1'<?php if (!empty($search_reportable)) {
                                    echo ' checked';
                                } ?> />
                                <span><?php echo xlt('Diagnosis Reporting Only'); ?></span>
                            </label>
                            <label class="checkbox-item" title="<?php echo xla("Only Show Service Code Finance Reporting Codes") ?>">
                                <input type='checkbox' name='search_financial_reporting' value='1'<?php if (!empty($search_financial_reporting)) {
                                    echo ' checked';
                                } ?> />
                                <span><?php echo xlt('Service Reporting Only'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="toolbar-row">
                    <div class="results-count">
                        <?php echo text(($fstart + 1)) . " - " . text($fend) . " / " . text($count) . " " . xlt('records'); ?>
                    </div>
                    <button type="submit" name="go" value="1" class="btn btn-secondary"><?php echo xlt('Apply Filters'); ?></button>
                </div>
            </div>
        </div>

        <div class="panel-grid">
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title"><?php echo $mode == "modify" ? xlt('Modify External Code') : xlt('Code Details'); ?></h2>
                    <p class="panel-copy"><?php echo xlt('Not all fields are required for all codes or code types.'); ?></p>
                </div>
                <div class="panel-body">
                    <div class="form-section">
                        <h3 class="section-title"><?php echo xlt('Identification'); ?></h3>
                        <div class="field-grid">
                            <div class="field span-4">
                                <label for="code-type"><?php echo xlt('Type'); ?></label>
                                <?php if ($mode != "modify") { ?>
                                    <select id="code-type" name="code_type">
                                        <?php } ?>

                                        <?php $external_sets = array(); ?>
                                        <?php foreach ($code_types as $key => $value) { ?>
                                            <?php if (!($value['external'])) { ?>
                                                <?php if ($mode != "modify") { ?>
                                                    <option value="<?php echo attr($value['id']) ?>"<?php if ($code_type == $value['id']) {
                                                        echo " selected";
                                                    } ?>><?php echo xlt($value['label']) ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($value['external']) {
                                                array_push($external_sets, $key);
                                            } ?>
                                        <?php } ?>

                                        <?php if ($mode != "modify") { ?>
                                    </select>
                                <?php } ?>

                                <?php if ($mode == "modify") { ?>
                                    <input type='text' size='4' name='code_type' readonly='readonly' style='display:none' value='<?php echo attr($code_type) ?>' />
                                    <input id="code-type" type="text" readonly='readonly' value='<?php echo attr($code_type_name_external) ?>' />
                                <?php } ?>
                            </div>

                            <div class="field span-4">
                                <label for="code-value"><?php echo xlt('Code'); ?></label>
                                <?php if ($mode == "modify") { ?>
                                    <input id="code-value" type='text' name='code' readonly='readonly' value='<?php echo attr($code) ?>' />
                                <?php } else { ?>
                                    <input id="code-value" type='text' name='code' value='<?php echo attr($code) ?>'
                                           onkeyup='maskkeyup(this,getCTMask())'
                                           onblur='maskblur(this,getCTMask())'
                                    />
                                <?php } ?>
                            </div>

                            <?php if (modifiers_are_used()) { ?>
                                <div class="field span-4">
                                    <label for="modifier-value"><?php echo xlt('Modifier'); ?></label>
                                    <?php if ($mode == "modify") { ?>
                                        <input id="modifier-value" type='text' name='modifier' readonly='readonly' value='<?php echo attr($modifier) ?>'>
                                    <?php } else { ?>
                                        <input id="modifier-value" type='text' name='modifier' value='<?php echo attr($modifier) ?>'>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <input type='hidden' name='modifier' value=''>
                            <?php } ?>

                            <div class="field span-12">
                                <label class="checkbox-item">
                                    <input type='checkbox' name='active' value='1'<?php if (!empty($active) || ($mode == 'modify' && $active == null)) {
                                        echo ' checked';
                                    } ?> />
                                    <span><?php echo xlt('Active'); ?></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php echo xlt('Classification'); ?></h3>
                        <div class="field-grid">
                            <div class="field span-8">
                                <label for="code-description"><?php echo xlt('Description'); ?></label>
                                <?php if ($mode == "modify") { ?>
                                    <input id="code-description" type='text' name="code_text" readonly="readonly" value='<?php echo attr($code_text) ?>'>
                                <?php } else { ?>
                                    <input id="code-description" type='text' name="code_text" value='<?php echo attr($code_text) ?>'>
                                <?php } ?>
                            </div>

                            <div class="field span-4">
                                <label for="code-description-short"><?php echo xlt('Short Description'); ?></label>
                                <?php if ($mode == "modify") { ?>
                                    <input id="code-description-short" type='text' name="code_text_short" readonly="readonly" value='<?php echo attr($code_text_short) ?>'>
                                <?php } else { ?>
                                    <input id="code-description-short" type='text' name="code_text_short" value='<?php echo attr($code_text_short) ?>'>
                                <?php } ?>
                            </div>

                            <?php if ($institutional) { ?>
                                <div class="field span-4">
                                    <label for="revenue-code"><?php echo xlt('Revenue Code'); ?></label>
                                    <?php if ($mode == "modify") { ?>
                                        <input id="revenue-code" type='text' name="revenue_code" readonly="readonly" value='<?php echo attr($revenue_code) ?>'>
                                    <?php } else { ?>
                                        <input id="revenue-code" type='text' class='revcode' name="revenue_code" title='<?php echo xla('Type to search and select revenue code'); ?>' value='<?php echo attr($revenue_code) ?>'>
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <div class="field span-6">
                                <label><?php echo xlt('Category'); ?></label>
                                <?php
                                generate_form_field(array('data_type'=>1,'field_id'=>'superbill','list_id'=>'superbill'), $superbill);
                                ?>
                            </div>

                            <?php if (!empty($GLOBALS['ippf_specific'])) { ?>
                                <div class="field span-6">
                                    <label for="cyp-factor"><?php echo xlt('CYP Factor'); ?></label>
                                    <input id="cyp-factor" type='text' maxlength='20' name="cyp_factor" value='<?php echo attr($cyp_factor) ?>'>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php echo xlt('Reporting and Relations'); ?></h3>
                        <div class="field-grid">
                            <div class="field span-12">
                                <div class="checkbox-row">
                                    <label class="checkbox-item" title='<?php echo xla("Syndromic Surveillance Report") ?>'>
                                        <input type='checkbox' name='reportable' value='1'<?php if (!empty($reportable)) {
                                            echo ' checked';
                                        } ?> />
                                        <span><?php echo xlt('Diagnosis Reporting'); ?></span>
                                    </label>
                                    <label class="checkbox-item" title='<?php echo xla("Service Code Finance Reporting") ?>'>
                                        <input type='checkbox' name='financial_reporting' value='1'<?php if (!empty($financial_reporting)) {
                                            echo ' checked';
                                        } ?> />
                                        <span><?php echo xlt('Service Reporting'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <?php if (related_codes_are_used()) { ?>
                                <div class="field span-12">
                                    <label for="related-desc"><?php echo xlt('Relate To'); ?></label>
                                    <input id="related-desc" type='text' name='related_desc'
                                           value='<?php echo attr($related_desc) ?>' onclick="sel_related()"
                                           title='<?php echo xla('Click to select related code'); ?>' readonly />
                                    <input type='hidden' name='related_code' value='<?php echo attr($related_code) ?>' />
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php echo xlt('Fees'); ?></h3>
                        <div class="fee-grid">
                            <?php
                            $pres = sqlStatement("SELECT lo.option_id, lo.title, p.pr_price " .
                                "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                                "p.pr_id = ? AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
                                "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq, lo.title", array($code_id));
                            while ($prow = sqlFetchArray($pres)) {
                                echo "<div class='chip-field'>";
                                echo "<span>" . text(xl_list_label($prow['title'])) . "</span>";
                                echo "<input type='text' name='fee[" . attr($prow['option_id']) . "]' value='" . attr($prow['pr_price']) . "'>";
                                echo "</div>\n";
                            }
                            ?>
                        </div>
                    </div>

                    <?php
                    $taxline = '';
                    $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
                    while ($prow = sqlFetchArray($pres)) {
                        $taxline .= "<label class='checkbox-item'>";
                        $taxline .= "<input type='checkbox' name='taxrate[" . attr($prow['option_id']) . "]' value='1'";
                        if (strpos(":$taxrates", $prow['option_id']) !== false) {
                            $taxline .= " checked";
                        }

                        $taxline .= " />";
                        $taxline .= "<span>" . text(xl_list_label($prow['title'])) . "</span>";
                        $taxline .= "</label>\n";
                    }

                    if ($taxline) {
                        ?>
                        <div class="form-section">
                            <h3 class="section-title"><?php echo xlt('Taxes'); ?></h3>
                            <div class="tax-grid">
                                <?php echo $taxline ?>
                            </div>
                        </div>
                        <?php
                    } ?>

                    <input type="hidden" name="code_id" value="<?php echo attr($code_id) ?>">
                    <input type="hidden" name="code_type_name_external" value="<?php echo attr($code_type_name_external) ?>">
                    <input type="hidden" name="code_external" value="<?php echo attr($code_external) ?>">

                    <?php if ($thisauthwrite) { ?>
                        <div class="form-actions">
                            <?php if ($mode == "modify") { ?>
                                <button type="button" class="btn btn-primary" onclick="submitModifyComplete();"><?php echo xlt('Update External Code'); ?></button>
                            <?php } else { ?>
                                <button type="button" class="btn btn-primary" onclick="submitUpdate();"><?php echo xlt('Update Current Code'); ?></button>
                                <button type="button" class="btn btn-secondary" onclick="submitAdd();"><?php echo xlt('Add as New'); ?></button>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title"><?php echo xlt('Available Codes'); ?></h2>
                    <p class="panel-copy"><?php echo xlt('Browse results and jump directly into editing or deletion actions.'); ?></p>
                </div>
                <div class="panel-body">
                    <div class="results-toolbar">
                        <div class="results-count">
                            <?php echo text(($fstart + 1)) . " - " . text($fend) . " / " . text($count) . " " . xlt('records'); ?>
                        </div>
                        <div class="pager">
                            <?php if ($fstart) { ?>
                                <a href="javascript:submitList(-<?php echo attr_js($pagesize); ?>)">&lt;&lt;</a>
                            <?php } ?>
                            <a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">&gt;&gt;</a>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="results-table">
                            <tr>
                                <th><?php echo xlt('Code'); ?></th>
                                <th><?php echo xlt('Mod'); ?></th>
                                <?php if ($institutional) { ?>
                                    <th><?php echo xlt('Revenue'); ?></th>
                                <?php } ?>
                                <th><?php echo xlt('Act'); ?></th>
                                <th><?php echo xlt('Category'); ?></th>
                                <th><?php echo xlt('Dx Rep'); ?></th>
                                <th><?php echo xlt('Serv Rep'); ?></th>
                                <th><?php echo xlt('Type'); ?></th>
                                <th><?php echo xlt('Description'); ?></th>
                                <th><?php echo xlt('Short Description'); ?></th>
                                <?php if (related_codes_are_used()) { ?>
                                    <th><?php echo xlt('Related'); ?></th>
                                <?php } ?>
                                <?php
                                $pres = sqlStatement("SELECT title FROM list_options " .
                                    "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
                                while ($prow = sqlFetchArray($pres)) {
                                    echo "  <th class='align-right'>" . text(xl_list_label($prow['title'])) . "</th>\n";
                                }
                                ?>
                                <th class="align-right"><?php echo xlt('Actions'); ?></th>
                            </tr>
    <?php

    if (isset($_REQUEST['filter'])) {
        $res = main_code_set_search($filter_key, $search, null, null, false, null, false, $fstart, ($fend - $fstart), $filter_elements);
    }

    for ($i = 0; $row = sqlFetchArray($res); $i++) {
        $all[$i] = $row;
    }

    if (!empty($all)) {
        $count = 0;
        foreach ($all as $iter) {
            $count++;

            $has_fees = false;
            foreach ($code_types as $key => $value) {
                if ($value['id'] == $iter['code_type']) {
                    $has_fees = $value['fee'];
                    break;
                }
            }

            echo " <tr>\n";
            echo "  <td>" . text($iter["code"]) . "</td>\n";
            echo "  <td>" . text($iter["modifier"]) . "</td>\n";
            if ($institutional) {
                echo "  <td>" . ($iter['revenue_code'] > '' ? text($iter['revenue_code']) : "<span class='muted'>" . xlt('none') . "</span>") ."</td>\n";
            }
            if ($iter["code_external"] > 0) {
                // If there is no entry in codes sql table, then default to active
                //  (this is reason for including NULL below)
                echo "  <td>" . ( ($iter["active"] || $iter["active"]==null) ? xlt('Yes') : xlt('No')) . "</td>\n";
            } else {
                echo "  <td>" . ( ($iter["active"]) ? xlt('Yes') : xlt('No')) . "</td>\n";
            }

            $sres = sqlStatement("SELECT title " .
                "FROM list_options AS lo " .
                "WHERE lo.list_id = 'superbill' AND lo.option_id = ?", array($iter['superbill']));
            if ($srow = sqlFetchArray($sres)) {
                echo "  <td>" . text($srow['title']) . "</td>\n";
            } else {
                echo "  <td></td>\n";
            }
            echo "  <td>" . ($iter["reportable"] ? xlt('Yes') : xlt('No')) . "</td>\n";
            echo "  <td>" . ($iter["financial_reporting"] ? xlt('Yes') : xlt('No')) . "</td>\n";
            echo "  <td>" . text($iter['code_type_name']) . "</td>\n";
            echo "  <td>" . text($iter['code_text']) . "</td>\n";
            echo "  <td>" . text($iter['code_text_short']) . "</td>\n";

            if (related_codes_are_used()) {
                // Show related codes.
                echo "  <td>";
                $arel = explode(';', $iter['related_code']);
                foreach ($arel as $tmp) {
                    list($reltype, $relcode) = explode(':', $tmp);
                    $code_description = lookup_code_descriptions($reltype.":".$relcode);
                    echo text($relcode) . ' ' . text(trim($code_description)) . '<br />';
                }

                echo "</td>\n";
            }

            $pres = sqlStatement("SELECT p.pr_price " .
                "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                "p.pr_id = ? AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
                "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq", array($iter['id']));
            while ($prow = sqlFetchArray($pres)) {
                echo "<td class='align-right'>" . text(bucks($prow['pr_price'])) . "</td>\n";
            }

            echo "<td class='align-right'><div class='row-actions'>";
            if ($thisauthwrite) {
                if ($iter["code_external"] > 0) {
                    echo "<button type='button' class='btn btn-secondary' onclick='submitModify(" . attr_js($iter['code_type_name']) . "," . attr_js($iter['code']) . "," . attr_js($iter['id']) . ")'>" . xlt('Modify') . "</button>";
                } else {
                    echo "<button type='button' class='btn btn-danger' onclick='submitDelete(" . attr_js($iter['id']) . ")'>" . xlt('Delete') . "</button>";
                    echo "<button type='button' class='btn btn-secondary' onclick='submitEdit(" . attr_js($iter['id']) . ")'>" . xlt('Edit') . "</button>";
                }
            }
            echo "</div></td>";

            echo " </tr>\n";
        }
    }

    ?>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script language="JavaScript">
    <?php
    if ($alertmsg) {
        echo "alert(" . js_escape($alertmsg) . ");\n";
    }
    ?>
</script>

</body>
</html>
