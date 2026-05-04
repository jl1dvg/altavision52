<?php
 // Copyright (C) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("drugs.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

// Check authorization.
$thisauth = acl_check('admin', 'drugs');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'prod' => 'd.name, d.drug_id, di.expiration, di.lot_number',
  'ndc'  => 'd.ndc_number, d.name, d.drug_id, di.expiration, di.lot_number',
  'form' => 'lof.title, d.name, d.drug_id, di.expiration, di.lot_number',
  'lot'  => 'di.lot_number, d.name, d.drug_id, di.expiration',
  'wh'   => 'lo.title, d.name, d.drug_id, di.expiration, di.lot_number',
  'qoh'  => 'di.on_hand, d.name, d.drug_id, di.expiration, di.lot_number',
  'exp'  => 'di.expiration, d.name, d.drug_id, di.lot_number',
);

// Get the order hash array value and key for this request.
$form_orderby = (!empty($_REQUEST['form_orderby']) && !empty($ORDERHASH[$_REQUEST['form_orderby']])) ? $_REQUEST['form_orderby'] : 'prod';
$orderby = $ORDERHASH[$form_orderby];

 // get drugs
 $res = sqlStatement("SELECT d.*, " .
  "di.inventory_id, di.lot_number, di.expiration, di.manufacturer, " .
  "di.on_hand, lo.title " .
  "FROM drugs AS d " .
  "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
  "AND di.destroy_date IS NULL " .
  "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
  "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
  "LEFT JOIN list_options AS lof ON lof.list_id = 'drug_form' AND " .
  "lof.option_id = d.form AND lof.activity = 1 " .
  "ORDER BY d.active DESC, $orderby");

$inventoryRows = array();
$drugIds = array();
$activeDrugIds = array();
$lotCount = 0;
$totalOnHand = 0;
$expiredLotCount = 0;
$expiringSoonCount = 0;
$todayTs = strtotime(date('Y-m-d'));
$soonTs = strtotime('+30 days', $todayTs);

while ($row = sqlFetchArray($res)) {
    $inventoryRows[] = $row;
    $drugIds[$row['drug_id']] = true;
    if (!empty($row['active'])) {
        $activeDrugIds[$row['drug_id']] = true;
    }

    if (!empty($row['inventory_id'])) {
        ++$lotCount;
        $totalOnHand += (float) $row['on_hand'];

        if (!empty($row['expiration'])) {
            $expirationTs = strtotime($row['expiration']);
            if ($expirationTs !== false) {
                if ($expirationTs < $todayTs) {
                    ++$expiredLotCount;
                } elseif ($expirationTs <= $soonTs) {
                    ++$expiringSoonCount;
                }
            }
        }
    }
}

if (!function_exists('inventorySortHeader')) {
    function inventorySortHeader($key, $label, $form_orderby)
    {
        $class = 'inventory-sort';
        if ($form_orderby == $key) {
            $class .= ' is-active';
        }

        return "<a href=\"#\" class=\"" . attr($class) . "\" onclick=\"return dosort(" . attr_js($key) . ")\">" .
            text($label) .
            ($form_orderby == $key ? " <span aria-hidden=\"true\">&#9650;</span>" : '') .
            "</a>";
    }
}
    ?>
<html>

<head>

<title><?php echo xlt('Drug Inventory'); ?></title>

<style>
body.body_top {
 background: #f4f7fb;
 color: #1f2937;
 font-family: Arial, Helvetica, sans-serif;
 font-size: 13px;
 margin: 0;
}

.inventory-page {
 padding: 18px;
}

.inventory-header {
 align-items: center;
 background: #ffffff;
 border: 1px solid #d8e1ec;
 border-radius: 8px;
 box-shadow: 0 2px 8px rgba(31, 41, 55, 0.08);
 display: flex;
 gap: 14px;
 justify-content: space-between;
 margin-bottom: 14px;
 padding: 14px 16px;
}

.inventory-title {
 margin: 0;
 font-size: 22px;
 font-weight: 700;
 color: #172033;
}

.inventory-subtitle {
 color: #64748b;
 margin-top: 3px;
}

.inventory-actions {
 display: flex;
 gap: 8px;
 flex-wrap: wrap;
 justify-content: flex-end;
}

.inventory-btn {
 background: #2563eb;
 border: 1px solid #1d4ed8;
 border-radius: 6px;
 color: #ffffff !important;
 cursor: pointer;
 display: inline-block;
 font-weight: 700;
 line-height: 1.2;
 padding: 8px 12px;
 text-decoration: none;
}

.inventory-btn:hover,
.inventory-btn:focus {
 background: #1d4ed8;
 color: #ffffff !important;
 text-decoration: none;
}

.inventory-summary {
 display: grid;
 grid-template-columns: repeat(5, minmax(130px, 1fr));
 gap: 10px;
 margin-bottom: 14px;
}

.inventory-stat {
 background: #ffffff;
 border: 1px solid #d8e1ec;
 border-radius: 8px;
 padding: 10px 12px;
}

.inventory-stat-label {
 color: #64748b;
 display: block;
 font-size: 11px;
 font-weight: 700;
 letter-spacing: 0;
 text-transform: uppercase;
}

.inventory-stat-value {
 color: #111827;
 display: block;
 font-size: 20px;
 font-weight: 700;
 margin-top: 4px;
}

.inventory-table-wrap {
 background: #ffffff;
 border: 1px solid #d8e1ec;
 border-radius: 8px;
 box-shadow: 0 2px 8px rgba(31, 41, 55, 0.08);
 overflow-x: auto;
}

table.mymaintable {
 border-collapse: separate;
 border-spacing: 0;
 min-width: 980px;
 width: 100%;
}

table.mymaintable th,
table.mymaintable td {
 border-bottom: 1px solid #e5edf6;
 padding: 10px 12px;
 vertical-align: middle;
}

table.mymaintable th {
 background: #eef4fb;
 color: #334155;
 font-size: 12px;
 font-weight: 700;
 position: sticky;
 text-align: left;
 text-transform: uppercase;
 top: 0;
 z-index: 1;
}

table.mymaintable tbody tr:hover td {
 background: #f8fbff;
}

.inventory-group-start td {
 border-top: 2px solid #d8e1ec;
}

.inventory-muted {
 color: #94a3b8;
}

.inventory-link {
 color: #1d4ed8 !important;
 cursor: pointer;
 font-weight: 700;
 text-decoration: none;
}

.inventory-link:hover,
.inventory-link:focus {
 color: #1e40af !important;
 text-decoration: underline;
}

.inventory-pill {
 border-radius: 999px;
 display: inline-block;
 font-size: 11px;
 font-weight: 700;
 line-height: 1;
 padding: 5px 8px;
}

.inventory-pill-active {
 background: #dcfce7;
 color: #166534;
}

.inventory-pill-inactive {
 background: #fee2e2;
 color: #991b1b;
}

.inventory-date {
 border-radius: 5px;
 display: inline-block;
 font-variant-numeric: tabular-nums;
 padding: 4px 7px;
}

.inventory-date-expired {
 background: #fee2e2;
 color: #991b1b;
 font-weight: 700;
}

.inventory-date-soon {
 background: #fef3c7;
 color: #92400e;
 font-weight: 700;
}

.inventory-sort,
.inventory-sort:visited {
 color: #334155;
 text-decoration: none;
}

.inventory-sort:hover,
.inventory-sort:focus,
.inventory-sort.is-active {
 color: #1d4ed8;
 text-decoration: none;
}

.inventory-number {
 font-variant-numeric: tabular-nums;
 text-align: right;
}

.inventory-empty {
 color: #64748b;
 padding: 24px !important;
 text-align: center;
}

@media (max-width: 900px) {
 .inventory-header {
  align-items: flex-start;
  flex-direction: column;
 }

 .inventory-actions {
  justify-content: flex-start;
 }

 .inventory-summary {
  grid-template-columns: repeat(2, minmax(130px, 1fr));
 }
}
</style>

<?php Header::setupHeader('report-helper'); ?>

<script language="JavaScript">

// callback from add_edit_drug.php or add_edit_drug_inventory.php:
function refreshme() {
 location.reload();
}

// Process click on drug title.
function dodclick(id) {
 dlgopen('add_edit_drug.php?drug=' + id, '_blank', 725, 475);
}

// Process click on drug QOO or lot.
function doiclick(id, lot) {
 dlgopen('add_edit_lot.php?drug=' + id + '&lot=' + lot, '_blank', 600, 475);
}

// Process click on a column header for sorting.
function dosort(orderby) {
 var f = document.forms[0];
 f.form_orderby.value = orderby;
 top.restoreSession();
 f.submit();
 return false;
}

$(function() {
  oeFixedHeaderSetup(document.getElementById('mymaintable'));
});

</script>

</head>

<body class="body_top">
<form method='get' action='drug_inventory.php'>
<div class="inventory-page">
<div class="inventory-header">
 <div>
  <h1 class="inventory-title"><?php echo xlt('Drug Inventory'); ?></h1>
  <div class="inventory-subtitle"><?php echo xlt('Review stock by drug, lot, warehouse and expiration date.'); ?></div>
 </div>
 <div class="inventory-actions">
  <a href="#" class="inventory-btn" onclick="dodclick(0); return false;"><?php echo xlt('Add Drug'); ?></a>
 </div>
</div>

<div class="inventory-summary">
 <div class="inventory-stat">
  <span class="inventory-stat-label"><?php echo xlt('Drugs'); ?></span>
  <span class="inventory-stat-value"><?php echo text(count($drugIds)); ?></span>
 </div>
 <div class="inventory-stat">
  <span class="inventory-stat-label"><?php echo xlt('Active'); ?></span>
  <span class="inventory-stat-value"><?php echo text(count($activeDrugIds)); ?></span>
 </div>
 <div class="inventory-stat">
  <span class="inventory-stat-label"><?php echo xlt('Lots'); ?></span>
  <span class="inventory-stat-value"><?php echo text($lotCount); ?></span>
 </div>
 <div class="inventory-stat">
  <span class="inventory-stat-label"><?php echo xlt('On Hand'); ?></span>
  <span class="inventory-stat-value"><?php echo text(sprintf('%0.2f', $totalOnHand)); ?></span>
 </div>
 <div class="inventory-stat">
  <span class="inventory-stat-label"><?php echo xlt('Expiry Alerts'); ?></span>
  <span class="inventory-stat-value"><?php echo text($expiredLotCount + $expiringSoonCount); ?></span>
 </div>
</div>

<div class="inventory-table-wrap">
<table id='mymaintable' class='mymaintable'>
 <thead>
 <tr class='head'>
  <th scope="col" title='<?php echo xla('Click to edit'); ?>'><?php echo inventorySortHeader('prod', xl('Name'), $form_orderby); ?></th>
  <th scope="col"><?php echo xlt('Status'); ?></th>
  <th scope="col"><?php echo inventorySortHeader('ndc', xl('NDC'), $form_orderby); ?></th>
  <th scope="col"><?php echo inventorySortHeader('form', xl('Form'), $form_orderby); ?></th>
  <th scope="col"><?php echo xlt('Size'); ?></th>
  <th scope="col"><?php echo xlt('Unit'); ?></th>
  <th scope="col" title='<?php echo xla('Click to receive (add) new lot'); ?>'><?php echo xlt('Actions'); ?></th>
  <th scope="col" title='<?php echo xla('Click to edit'); ?>'><?php echo inventorySortHeader('lot', xl('Lot'), $form_orderby); ?></th>
  <th scope="col"><?php echo inventorySortHeader('wh', xl('Warehouse'), $form_orderby); ?></th>
  <th scope="col" class="inventory-number"><?php echo inventorySortHeader('qoh', xl('QOH'), $form_orderby); ?></th>
  <th scope="col"><?php echo inventorySortHeader('exp', xl('Expires'), $form_orderby); ?></th>
 </tr>
 </thead>
 <tbody>
<?php
 $lastid = "";
 $encount = 0;
if (empty($inventoryRows)) {
    echo " <tr><td colspan='11' class='inventory-empty'>" . xlt('No inventory records found.') . "</td></tr>\n";
}
foreach ($inventoryRows as $row) {
    if ($lastid != $row['drug_id']) {
        ++$encount;
        $lastid = $row['drug_id'];
        echo " <tr class='detail inventory-group-start'>\n";
        echo "  <td>" .
        "<a href='#' class='inventory-link' onclick='dodclick(" . attr_js($lastid) . "); return false;'>" .
        text($row['name']) . "</a></td>\n";
        echo "  <td><span class='inventory-pill " . ($row['active'] ? "inventory-pill-active'>" . xlt('Active') : "inventory-pill-inactive'>" . xlt('Inactive')) . "</span></td>\n";
        echo "  <td>" . text($row['ndc_number']) . "</td>\n";
        echo "  <td>" .
        generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) .
        "</td>\n";
        echo "  <td>" . text($row['size']) . "</td>\n";
        echo "  <td>" .
        generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']) .
        "</td>\n";
        echo "  <td title='" . xla('Add new lot and transaction') . "'>" .
        "<a href='#' class='inventory-link' onclick='doiclick(" . attr_js($lastid) . ",0); return false;'>" . xlt('Add Lot') . "</a></td>\n";
    } else {
        echo " <tr class='detail'>\n";
        echo "  <td colspan='7' class='inventory-muted'>&nbsp;</td>\n";
    }

    if (!empty($row['inventory_id'])) {
        echo "  <td>" .
        "<a href='#' class='inventory-link' onclick='doiclick(" . attr_js($lastid) . "," . attr_js($row['inventory_id']) . "); return false;'>" . text($row['lot_number']) . "</a></td>\n";
        echo "  <td>" . text($row['title']) . "</td>\n";
        echo "  <td class='inventory-number'>" . text($row['on_hand']) . "</td>\n";
        $expirationClass = '';
        if (!empty($row['expiration'])) {
            $expirationTs = strtotime($row['expiration']);
            if ($expirationTs !== false) {
                if ($expirationTs < $todayTs) {
                    $expirationClass = ' inventory-date-expired';
                } elseif ($expirationTs <= $soonTs) {
                    $expirationClass = ' inventory-date-soon';
                }
            }
        }
        echo "  <td><span class='inventory-date" . attr($expirationClass) . "'>" . text(oeFormatShortDate($row['expiration'])) . "</span></td>\n";
    } else {
        echo "  <td colspan='4' class='inventory-muted'>" . xlt('No lots') . "</td>\n";
    }

    echo " </tr>\n";
}
?>
 </tbody>
</table>
</div>

<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>" />

</div>
</form>
</body>
</html>
