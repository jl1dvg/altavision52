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
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';
require_once("$srcdir/../controllers/C_Document.class.php");

use ESign\Api;
use OpenEMR\Core\Header;

$reviewMode = false;
if (!empty($_REQUEST['review_id'])) {
    $reviewMode = true;
    $encounter=sanitizeNumber($_REQUEST['review_id']);
}

$is_group = ($attendant_type == 'gid') ? true : false;
if ($attendant_type == 'gid') {
    $groupId = $therapy_group;
}
$attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
if ($is_group && !acl_check("groups", "glog", false, array('view','write'))) {
    echo xlt("access not allowed");
    exit();
}

?>
<html>

<head>

<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php Header::setupHeader(['common','esign','dygraphs']); ?>

<?php
$esignApi = new Api();
?>

<?php // if the track_anything form exists, then include the styling and js functions for graphing
if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
 <script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/report.js"></script>
 <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/style.css" type="text/css">
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
jQuery(document).ready( function($) {
    var formConfig = <?php echo $esignApi->formConfigToJson(); ?>;
    $(".esign-button-form").esign(
        formConfig,
        {
            afterFormSuccess : function( response ) {
                if ( response.locked ) {
                    var editButtonId = "form-edit-button-"+response.formDir+"-"+response.formId;
                    $("#"+editButtonId).replaceWith( response.editButtonHtml );
                }

                var logId = "esign-signature-log-"+response.formDir+"-"+response.formId;
                $.post( formConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
        }
    );

    var encounterConfig = <?php echo $esignApi->encounterConfigToJson(); ?>;
    $(".esign-button-encounter").esign(
        encounterConfig,
        {
            afterFormSuccess : function( response ) {
                // If the response indicates a locked encounter, replace all
                // form edit buttons with a "disabled" button, and "disable" left
                // nav visit form links
                if ( response.locked ) {
                    // Lock the form edit buttons
                    $(".form-edit-button").replaceWith( response.editButtonHtml );
                    // Disable the new-form capabilities in left nav
                    top.window.parent.left_nav.syncRadios();
                    // Disable the new-form capabilities in top nav of the encounter
                    $(".encounter-form-category-li").remove();
                }

                var logId = "esign-signature-log-encounter-"+response.encounterId;
                $.post( encounterConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
        }
    );

    $("#prov_edu_res").click(function() {
        if ( $('#prov_edu_res').prop('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "patient_edu_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });

    $("#provide_sum_pat_flag").click(function() {
        if ( $('#provide_sum_pat_flag').prop('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "provide_sum_pat_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });

    $("#trans_trand_care").click(function() {
        if ( $('#trans_trand_care').prop('checked') ) {
            var mode = "add";
            // Enable the reconciliation checkbox
            $("#med_reconc_perf").removeAttr("disabled");
        $("#soc_provided").removeAttr("disabled");
        }
        else {
            var mode = "remove";
            //Disable the reconciliation checkbox (also uncheck it if applicable)
            $("#med_reconc_perf").attr("disabled", true);
            $("#med_reconc_perf").prop("checked",false);
        $("#soc_provided").attr("disabled",true);
        $("#soc_provided").prop("checked",false);
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: false,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });

    $("#med_reconc_perf").click(function() {
        if ( $('#med_reconc_perf').prop('checked') ) {
            var mode = "complete";
        }
        else {
            var mode = "uncomplete";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });
    $("#soc_provided").click(function(){
        if($('#soc_provided').prop('checked')){
                var mode = "soc_provided";
        }
        else{
                var mode = "no_soc_provided";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
                { amc_id: "med_reconc_amc",
                complete: true,
                mode: mode,
                patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                object_category: "form_encounter",
                object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                }
        );
    });

     $(".deleteme").click(function(evt) { deleteme(); evt.stopPropogation(); });

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
        $orderid   = $arow['procedure_order_id'];
        $orderdate = $arow['date_ordered'];
        echo "  if (confirm('" . xls('There is a lab order') . " $orderid " .
        xls('dated') . " $orderdate " .
        xls('for this patient not yet assigned to any encounter.') . " " .
        xls('Assign it to this one?') . "')) attachid += '$orderid,';\n";
    }
    echo "  if (attachid) location.href = 'forms.php?attachid=' + attachid;\n";
}
?>

    <?php if ($reviewMode) { ?>
        $("body table:first").hide();
        $(".encounter-summary-column").hide();
        $(".css_button").hide();
        $(".css_button_small").hide();
        $(".encounter-summary-column:first").show();
        $(".title:first").text("<?php echo xls("Review"); ?> " + $(".title:first").text() + " ( <?php echo addslashes($encounter); ?> )");
    <?php } ?>
});

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 200, '', '', {
      buttons: [
          {text: '<?php echo xla('Done'); ?>', close: true, style: 'primary btn-sm'}
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
  var url = '<?php echo "$rootdir/patient_file/encounter/view_form.php?formname=" ?>' +
    formdir + '&id=' + formid;
  if (formdir == 'newpatient' || !parent.twAddFrameTab) {
    location.href = url;
  }
  else {
    parent.twAddFrameTab('enctabs', formname, url);
  }
  return false;
}

// Called when an encounter form may changed something that requires a refresh here.
function refreshVisitDisplay() {
  location.href = '<?php echo $rootdir; ?>/patient_file/encounter/forms.php';
}

</script>

<script language="javascript">
function expandcollapse(atr) {
  for (var i = 1; i < 15; ++i) {
    var mydivid="divid_" + i; var myspanid = "spanid_" + i;
    var ele = document.getElementById(mydivid);
    var text = document.getElementById(myspanid);
    if (!ele) continue;
    if (atr == "expand") {
      ele.style.display = "block"; text.innerHTML = "<?php xl('Collapse', 'e'); ?>";
    }
    else {
      ele.style.display = "none" ; text.innerHTML = "<?php xl('Expand', 'e'); ?>";
    }
  }
}

function divtoggle(spanid, divid) {
    var ele = document.getElementById(divid);
    var text = document.getElementById(spanid);
    if(ele.style.display == "block") {
        ele.style.display = "none";
        text.innerHTML = "<?php xl('Expand', 'e'); ?>";
    }
    else {
        ele.style.display = "block";
        text.innerHTML = "<?php xl('Collapse', 'e'); ?>";
    }
}
</script>

<style>
body {
  font-family: Arial;
  margin: 0;
}

* {
  box-sizing: border-box;
}

img {
  vertical-align: middle;
}

/* Position the image container (needed to position the left and right arrows) */
.container {
  position: relative;
	max-width: 720px;
}

/* Hide the images by default */
.mySlides {
  display: none;
}

/* Add a pointer when hovering over the thumbnail images */
.cursor {
  cursor: pointer;
}

/* Next & previous buttons */
.prev,
.next {
  cursor: pointer;
  position: absolute;
  top: 10%;
  width: auto;
  padding: 16px;
  color: black;
  font-weight: bold;
  font-size: 20px;
  border-radius: 0 3px 3px 0;
  user-select: none;
  -webkit-user-select: none;
  background-color: white;
}

/* Position the "next button" to the right */
.next {
  right: 0;
  border-radius: 3px 0 0 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover,
.next:hover {
  background-color: blue;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* Container for image text */
.caption-container {
  text-align: center;
  background-color: #222;
  padding: 2px 16px;
  color: white;
}

.row:after {
  content: "";
  display: table;
  clear: both;
}

/* Six columns side by side */
.column {
  float: left;
  width: 10%;
}

/* Add a transparency effect for thumnbail images */
.demo {
  opacity: 0.6;
}

.active,
.demo:hover {
  opacity: 1;
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
  }
  else {
    parent.twAddFrameTab('enctabs', label, sel);
  }
}

function toggleFrame1(fnum) {
  top.frames['left_nav'].document.forms[0].cb_top.checked=false;
  top.window.parent.left_nav.toggleFrame(fnum);
}
</script>
<style type="text/css">
#sddm
{   margin: 0;
    padding: 0;
    z-index: 30;
}

</style>
<script type="text/javascript" language="javascript">

var timeout = 500;
var closetimer  = 0;
var ddmenuitem  = 0;
var oldddmenuitem = 0;
var flag = 0;

// open hidden layer
function mopen(id)
{
    // cancel close timer
    //mcancelclosetime();

    flag=10;

    // close old layer
    //if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
    //if(ddmenuitem) ddmenuitem.style.display = 'none';

    // get new layer and show it
        oldddmenuitem = ddmenuitem;
    ddmenuitem = document.getElementById(id);
        if((ddmenuitem.style.visibility == '')||(ddmenuitem.style.visibility == 'hidden')){
            if(oldddmenuitem) oldddmenuitem.style.visibility = 'hidden';
            if(oldddmenuitem) oldddmenuitem.style.display = 'none';
            ddmenuitem.style.visibility = 'visible';
            ddmenuitem.style.display = 'block';
        }else{
            ddmenuitem.style.visibility = 'hidden';
            ddmenuitem.style.display = 'none';
        }
}
// close showed layer
function mclose()
{
    if(flag==10)
     {
      flag=11;
      return;
     }
    if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
    if(ddmenuitem) ddmenuitem.style.display = 'none';
}

// close layer when click-out
document.onclick = mclose;
//=================================================
function findPosX(id)
  {
    obj=document.getElementById(id);
    var curleft = 0;
    if(obj.offsetParent)
        while(1)
        {
          curleft += obj.offsetLeft;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.x)
        curleft += obj.x;
   PropertyWidth=document.getElementById(id).offsetWidth;
   if(PropertyWidth>curleft)
    {
     document.getElementById(id).style.left=0;
    }
  }

  function findPosY(obj)
  {
    var curtop = 0;
    if(obj.offsetParent)
        while(1)
        {
          curtop += obj.offsetTop;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.y)
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
    $sql .=  "state LIKE \"$state\" ORDER BY category, priority, name";
    if ($limit != "unlimited") {
        $sql .= " limit $limit, $offset";
    }
    $res = sqlStatement($sql);
    if ($res) {
        for ($iter=0; $row=sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }
    } else {
        return false;
    }
    return $all;
}

$reg = myGetRegistered();
$old_category = '';

  $DivId=1;

// To see if the encounter is locked. If it is, no new forms can be created
$encounterLocked = false;
if ($esignApi->lockEncounters() &&
isset($GLOBALS['encounter']) &&
!empty($GLOBALS['encounter']) ) {
    $esign = $esignApi->createEncounterESign($GLOBALS['encounter']);
    if ($esign->isLocked()) {
        $encounterLocked = true;
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
            $DivId = 'mod_'.$modulerow['mod_id'];
            $new_category = $modulerow['mod_ui_name'];
            $modulePath = "";
            $added      = "";
            if ($modulerow['type'] == 0) {
                $modulePath = $GLOBALS['customModDir'];
                $added      = "";
            } else {
                $added      = "index";
                $modulePath = $GLOBALS['zendModDir'];
            }
            $relative_link = "../../modules/".$modulePath."/".$modulerow['path'];
            $nickname = $modulerow['menu_name'] ? $modulerow['menu_name'] : 'Noname';
            if ($jid==0 || ($modid!=$modulerow['mod_id'])) {
                if ($modid!='') {
                    $StringEcho.= '</table></div></li>';
                }
                $StringEcho.= "<li><a href='JavaScript:void(0);' onClick=\"mopen('$DivId');\" >$new_category</a><div id='$DivId' ><table border='0' cellspacing='0' cellpadding='0'>";
            }
            $jid++;
            $modid = $modulerow['mod_id'];
            $StringEcho.= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=" .
                "\"openNewForm('$relative_link', '" . addslashes(xl_form_title($nickname)) . "')\" " .
                "href='JavaScript:void(0);'>" . xl_form_title($nickname) . "</a></td></tr>";
        }
    }
    ?>
<!-- DISPLAYING HOOKS ENDS HERE -->
<?php
if ($StringEcho) {
    $StringEcho.= "</table></div></li></ul>".$StringEcho2;
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
$providerNameRes = getProviderName($providerIDres);
?>

<div class='encounter-summary-container'>
<div class='encounter-summary-column'>
<div>
<?php
$pass_sens_squad = true;

//fetch acl for category of given encounter
$pc_catid = fetchCategoryIdByEncounter($encounter);
$postCalendarCategoryACO = fetchPostCalendarCategoryACO($pc_catid);
if ($postCalendarCategoryACO) {
    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
    $authPostCalendarCategory = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1]);
    $authPostCalendarCategoryWrite = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
} else { // if no aco is set for category
    $authPostCalendarCategory = true;
    $authPostCalendarCategoryWrite = true;
}

if ($attendant_type == 'pid' && is_numeric($pid)) {
    echo '<span class="title">' . text(oeFormatShortDate($encounter_date)) . " " . xlt("Encounter") . '</span>';

    // Check for no access to the patient's squad.
    $result = getPatientData($pid, "fname,lname,squad");
    echo htmlspecialchars(xl('for', '', ' ', ' ') . $result['fname'] . " " . $result['lname']);
    if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
        $pass_sens_squad = false;
    }

    // Check for no access to the encounter's sensitivity level.
    $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
                        "pid = '$pid' AND encounter = '$encounter' LIMIT 1");
    if (($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) || !$authPostCalendarCategory) {
        $pass_sens_squad = false;
    }
    // for therapy group
} else {
    echo '<span class="title">' . text(oeFormatShortDate($encounter_date)) . " " . xlt("Group Encounter") . '</span>';
    // Check for no access to the patient's squad.
    $result = getGroup($groupId);
    echo htmlspecialchars(xl('for ', '', ' ', ' ') . $result['group_name']);
    if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
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
</div>


<!-- Get the documents tagged to this encounter and display the links and notes as the tooltip -->
<?php
if ($attendant_type == 'pid') {
    $docs_list = getDocumentsByEncounter($pid, $_SESSION['encounter']);
} else {
    // already doesn't exist document for therapy groups
    $docs_list = array();
}
if (!empty($docs_list) && count($docs_list) > 0) {
?>
<span class="bold"><?php echo xlt("Document(s)"); ?>:</span>

    <!-- Container for the image gallery -->
    <div class="container">
  <div>
	<?php
$doc = new C_Document();
sort($docs_list);
foreach ($docs_list as $doc_iter) {
    $doc_url = $doc->_tpl_vars[CURRENT_ACTION]. "&retrieve&patient_id=".attr($pid)."&document_id=" . attr($doc_iter[id]) . "&as_file=false";
    // Get notes for this document.
?>
    <!-- Full-width images with number text -->
    <div class="mySlides">
<img src="<?php echo $doc_url;?>" style="width:100%">
</div>
<?php } ?>

<a class="prev" onclick="plusSlides(-1)">❮</a>
<a class="next" onclick="plusSlides(1)">❯</a>
  </div>
<div class="caption-container">
	<p id="caption"></p>
</div>

    <!-- Thumbnail images -->
<div class="row">
<?php
$doc = new C_Document();
sort($docs_list);
foreach ($docs_list as $doc_iter) {
    $doc_url = $doc->_tpl_vars[CURRENT_ACTION]. "&retrieve&patient_id=".attr($pid)."&document_id=" . attr($doc_iter[id]) . "&as_file=false";
?>
<div class="column">

<img class="demo cursor" src="<?php echo $doc_url;?>" style="width:100%" onclick="currentSlide(<?php echo $i++;?>)">
</div>
<?php } ?>
</div>
</div>
<script>
var slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName("mySlides");
  var dots = document.getElementsByClassName("demo");
  var captionText = document.getElementById("caption");
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
  captionText.innerHTML = dots[slideIndex-1].alt;
}
</script>
<?php } ?>
<br/>



</div> <!-- end large encounter_forms DIV -->
</body>
</html>
