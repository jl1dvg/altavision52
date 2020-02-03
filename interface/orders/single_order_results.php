<?php
/**
 * Script to display results for a given procedure order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . '/../globals.php');
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lab.inc");

use Mpdf\Mpdf;
use OpenEMR\Core\Header;

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

$orderid = intval($_GET['orderid']);

$sqlpid = sqlQuery("SELECT * FROM procedure_order WHERE procedure_order_id =" . $_GET['orderid']);
$pid = $sqlpid['patient_id'];

$finals_only = empty($_POST['form_showall']);

if (!empty($_POST['form_sign']) && !empty($_POST['form_sign_list'])) {
    if (!acl_check('patients', 'sign')) {
        die(xlt('Not authorized to sign results'));
    }

  // When signing results we are careful to sign only those reports that were
  // in the sending form. While this will usually be all the reports linked to
  // the order it's possible for a new report to come in while viewing these,
  // and it would be very bad to sign results that nobody has seen!
    $arrSign = explode(',', $_POST['form_sign_list']);
    foreach ($arrSign as $id) {
        sqlStatement("UPDATE procedure_report SET " .
        "review_status = 'reviewed' WHERE " .
        "procedure_report_id = ?", array($id));
    }
    if ($orderid) {
        sqlStatement("UPDATE procedure_order SET " .
            "order_status = 'complete' WHERE " .
            "procedure_order_id = ?", array($orderid));
    }
}

// This mess generates a PDF report and sends it to the patient.
if (!empty($_POST['form_send_to_portal'])) {
  // Borrowing the general strategy here from custom_report.php.
  // See also: http://wiki.spipu.net/doku.php?id=html2pdf:en:v3:output
    require_once($GLOBALS["include_root"] . "/cmsportal/portal.inc.php");
    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => 'Letter',
        'default_font_size' => '9',
        'default_font' => 'dejavusans',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
        'orientation' => 'P',
        'shrink_tables_to_fit' => 1,
        'use_kwt' => true,
        'autoScriptToLang' => true,
        'keep_table_proportions' => true
    );
    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    ob_start();
    echo "<link rel='stylesheet' type='text/css' href='$webserver_root/interface/themes/style_pdf.css'>\n";
    echo "<link rel='stylesheet' type='text/css' href='$webserver_root/library/ESign/css/esign_report.css'>\n";
    $GLOBALS['PATIENT_REPORT_ACTIVE'] = true;
    generate_order_report($orderid, false, true, $finals_only);
    $GLOBALS['PATIENT_REPORT_ACTIVE'] = false;
  // echo ob_get_clean(); exit(); // debugging
    $pdf->writeHTML(ob_get_clean());
    $contents = $pdf->Output('', true);
  // Send message with PDF as attachment.
    $result = cms_portal_call(array(
    'action'   => 'putmessage',
    'user'     => $_POST['form_send_to_portal'],
    'title'    => xl('Your Lab Results'),
    'message'  => xl('Please see the attached PDF.'),
    'filename' => 'results.pdf',
    'mimetype' => 'application/pdf',
    'contents' => base64_encode($contents),
    ));
    if ($result['errmsg']) {
        die(text($result['errmsg']));
    }
}

// Indicates if we are entering in batch mode.
$form_batch = 0;

// Indicates if we are entering in review mode.
$form_review = 1;

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

// Check authorization for pending review.
$reviewauth = acl_check('patients', 'sign');
if ($form_review and !$reviewauth and !$thisauth) {
    die(xlt('Not authorized'));
}

// Set pid for pending review.
if ($_GET['set_pid'] && $form_review) {
    require_once("$srcdir/pid.inc");
    require_once("$srcdir/patient.inc");

    setpid($_GET['set_pid']);

    $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    ?>
    <script language='JavaScript'>
        parent.left_nav.setPatient(<?php echo js_escape($result['fname'] . " " . $result['lname']) . "," . js_escape($pid) . "," . js_escape($result['pubpid']) . ",''," . js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($result['DOB_YMD'])); ?>);
    </script>
    <?php
}

if (!$form_batch && !$pid && !$form_review) {
    die(xlt('There is no current patient'));
}

function oresRawData($name, $index)
{
    $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
    return trim($s);
}

function oresData($name, $index)
{
    $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
    return add_escape_custom(trim($s));
}

function QuotedOrNull($fld)
{
    if (empty($fld)) {
        return "NULL";
    }

    return "'$fld'";
}

$current_report_id = 0;

if ($_POST['form_submit'] && !empty($_POST['form_line'])) {
    foreach ($_POST['form_line'] as $lino => $line_value) {
        list($order_id, $order_seq, $report_id, $result_id) = explode(':', $line_value);

        // Not using xl() here because this is for debugging only.
        if (empty($order_id)) {
            die("Order ID is missing from line " . text($lino) . ".");
        }

        // If report data exists for this line, save it.
        $date_report = oresData("form_date_report", $lino);

        if (!empty($date_report)) {
            $sets =
                "procedure_order_id = '" . add_escape_custom($order_id) . "', " .
                "procedure_order_seq = '" . add_escape_custom($order_seq) . "', " .
                "date_report = '" . add_escape_custom($date_report) . "', " .
                "date_collected = " . QuotedOrNull(oresData("form_date_collected", $lino)) . ", " .
                "specimen_num = '" . oresData("form_specimen_num", $lino) . "', " .
                "report_status = '" . oresData("form_report_status", $lino) . "'";

            // Set the review status to reviewed.
            if ($form_review) {
                $sets .= ", review_status = 'reviewed'";
            }

            if ($report_id) { // Report already exists.
                sqlStatement("UPDATE procedure_report SET $sets "  .
                    "WHERE procedure_report_id = '" . add_escape_custom($report_id) . "'");
            } else { // Add new report.
                $report_id = sqlInsert("INSERT INTO procedure_report SET $sets");
            }
        }

        // If this line had report data entry fields, filled or not, set the
        // "current report ID" which the following result data will link to.
        if (isset($_POST["form_date_report"][$lino])) {
            $current_report_id = $report_id;
        }

        // If there's a report, save corresponding results.
        if ($current_report_id) {
            // Comments and notes will be combined into one comments field.
            $form_comments = oresRawData("form_comments", $lino);
            $form_comments = str_replace("\n", '~', $form_comments);
            $form_comments = str_replace("\r", '', $form_comments);
            $form_notes = oresRawData("form_notes", $lino);
            if ($form_notes !== '') {
                $form_comments .= "\n" . $form_notes;
            }

            $sets =
                "procedure_report_id = '" . add_escape_custom($current_report_id) . "', " .
                "result_code = '" . oresData("form_result_code", $lino) . "', " .
                "result_text = '" . oresData("form_result_text", $lino) . "', " .
                "abnormal = '" . oresData("form_result_abnormal", $lino) . "', " .
                "result = '" . oresData("form_result_result", $lino) . "', " .
                "`range` = '" . oresData("form_result_range", $lino) . "', " .
                "units = '" . oresData("form_result_units", $lino) . "', " .
                "facility = '" . oresData("form_facility", $lino) . "', " .
                "comments = '" . $form_comments . "', " .
                "result_status = '" . oresData("form_result_status", $lino) . "'";
            if ($result_id) { // result already exists
                sqlStatement("UPDATE procedure_result SET $sets "  .
                    "WHERE procedure_result_id = '" . add_escape_custom($result_id) . "'");
            } else { // Add new result.
                $result_id = sqlInsert("INSERT INTO procedure_result SET $sets");
            }
        }
    } // end foreach
}
?>
<html>
<head>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css">


    <style>

        tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
        tr.detail { font-size:10pt; }
        a, a:visited, a:hover { color:#0000cc; }

        .celltext {
            font-size:10pt;
            font-weight:normal;
            border-style:solid;
            border-top-width:0px;
            border-bottom-width:0px;
            border-left-width:0px;
            border-right-width:0px;
            border-color: #aaaaaa;
            background-color:transparent;
            width:100%;
            color:#0000cc;
        }

        .celltextfw {
            font-size:10pt;
            font-weight:normal;
            border-style:solid;
            border-top-width:0px;
            border-bottom-width:0px;
            border-left-width:0px;
            border-right-width:0px;
            border-color: #aaaaaa;
            background-color:transparent;
            color:#0000cc;
        }

        .cellselect {
            font-size:10pt;
            background-color:transparent;
            color:#0000cc;
        }

        .reccolor {
            color:#008800;
        }

    </style>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="../../library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js"></script>

    <script language="JavaScript">

        // This invokes the find-procedure-type popup.
        var ptvarname;
        function sel_proc_type(varname) {
            var f = document.forms[0];
            if (typeof varname == 'undefined') varname = 'form_proc_type';
            ptvarname = varname;
            dlgopen('types.php?popup=1&order=' + encodeURIComponent(f[ptvarname].value), '_blank', 800, 500);
        }

        // This is for callback by the find-procedure-type popup.
        // Sets both the selected type ID and its descriptive name.
        function set_proc_type(typeid, typename) {
            var f = document.forms[0];
            f[ptvarname].value = typeid;
            f[ptvarname + '_desc'].value = typename;
        }

        // Helper functions.
        function extGetX(elem) {
            var x = 0;
            while(elem != null) {
                x += elem.offsetLeft;
                elem = elem.offsetParent;
            }
            return x;
        }
        function extGetY(elem) {
            var y = 0;
            while(elem != null) {
                y += elem.offsetTop;
                elem = elem.offsetParent;
            }
            return y;
        }

        // Show or hide the "extras" div for a result.
        var extdiv = null;
        function extShow(lino, show) {
            var thisdiv = document.getElementById("ext_" + lino);
            if (extdiv) {
                extdiv.style.visibility = 'hidden';
                extdiv.style.left = '-1000px';
                extdiv.style.top = '0px';
            }
            if (show && thisdiv != extdiv) {
                extdiv = thisdiv;
                var dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
                x = dw - extdiv.offsetWidth;
                if (x < 0) x = 0;
                var y = extGetY(show) + show.offsetHeight;
                extdiv.style.left = x;
                extdiv.style.top  = y;
                extdiv.style.visibility = 'visible';
            }
            else {
                extdiv = null;
            }
        }

        // Helper function for validate.
        function prDateRequired(rlino) {
            var f = document.forms[0];
            if (f['form_date_report['+rlino+']'].value.length < 10) {
                alert(<?php echo xlj('Missing report date'); ?>);
                if (f['form_date_report['+rlino+']'].focus)
                    f['form_date_report['+rlino+']'].focus();
                return false;
            }
            return true;
        }

        // Validation at submit time.
        function validate(f) {
            var rlino = 0;
            for (var lino = 0; f['form_line['+lino+']']; ++lino) {
                if (f['form_date_report['+lino+']']) {
                    rlino = lino;
                    if (f['form_report_status['+rlino+']'].selectedIndex > 0) {
                        if (!prDateRequired(rlino)) return false;
                    }
                }
                var abnstat = f['form_result_abnormal['+lino+']'].selectedIndex > 0;
                if (abnstat && !prDateRequired(rlino)) return false;
            }
            top.restoreSession();
            return true;
        }

        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

    </script>
    <?php Header::setupHeader(['jquery-ui']); ?>
<title><?php echo xlt('Order Results'); ?></title>
<style>
body {
 margin: 9pt;
 font-family: sans-serif;
 font-size: 1em;
}
</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script language="JavaScript">
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>

</head>
<body>
<?php
if (empty($_POST['form_sign'])) {
    generate_order_report($orderid, true, true, $finals_only);
} else {
    ?>
<script language='JavaScript'>
 if (opener.document.forms && opener.document.forms[0]) {
  // Opener should be list_reports.php. Make it refresh.
  var f = opener.document.forms[0];
  if (f.form_external_refresh) {
   f.form_external_refresh.value = '1';
   f.submit();
  }
 }
 let stayHere = './single_order_results.php?orderid=' + <?php echo js_escape($orderid); ?>;
 window.location.assign(stayHere);
</script>
    <?php
}
?>

</body>
</html>
