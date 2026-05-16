<?php
/**
 * Dedicated package builder for fee_sheet_options.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenAI Codex
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (!acl_check('lists', 'qpeditor')) {
    die(xlt('Not authorized'));
}

$savedSuccessfully = false;

if (!empty($_POST['formaction']) && $_POST['formaction'] === 'save') {
    $opt = isset($_POST['opt']) ? $_POST['opt'] : array();
    sqlStatement("DELETE FROM fee_sheet_options");
    for ($lino = 1; isset($opt["$lino"]); ++$lino) {
        $iter = $opt["$lino"];
        $category = trim(isset($iter['category']) ? $iter['category'] : '');
        $option = trim(isset($iter['option']) ? $iter['option'] : '');
        $codes = trim(isset($iter['codes']) ? $iter['codes'] : '');
        if ($category !== '' && $option !== '') {
            sqlStatement(
                "INSERT INTO fee_sheet_options (fs_category, fs_option, fs_codes) VALUES (?, ?, ?)",
                array($category, $option, $codes)
            );
        }
    }
    $savedSuccessfully = true;
}

function feeSheetCodeDescriptions($codes)
{
    global $code_types;

    $arrcodes = explode('~', $codes);
    $descriptions = array();
    foreach ($arrcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }
        $arrcode = explode('|', $codestring);
        $codeType = isset($arrcode[0]) ? $arrcode[0] : '';
        $code = isset($arrcode[1]) ? $arrcode[1] : '';
        $selector = isset($arrcode[2]) ? $arrcode[2] : '';
        if ($codeType === 'PROD') {
            $row = sqlQuery("SELECT name FROM drugs WHERE drug_id = ?", array($code));
            $desc = $code . ':' . $selector . ' ' . $row['name'];
        } else {
            $row = sqlQuery(
                "SELECT code_text FROM codes WHERE code_type = ? AND code = ? ORDER BY modifier LIMIT 1",
                array($code_types[$codeType]['id'], $code)
            );
            $desc = $codeType . ':' . $code . ' ' . ucfirst(strtolower($row['code_text']));
        }
        $descriptions[] = str_replace('~', ' ', $desc);
    }

    return implode('~', $descriptions);
}

$rows = array();
$result = sqlStatement("SELECT * FROM fee_sheet_options ORDER BY fs_category, fs_option");
while ($row = sqlFetchArray($result)) {
    $row['fs_descs'] = feeSheetCodeDescriptions($row['fs_codes']);
    $rows[] = $row;
}

for ($i = 0; $i < 3; ++$i) {
    $rows[] = array(
        'fs_category' => '',
        'fs_option' => '',
        'fs_codes' => '',
        'fs_descs' => '',
    );
}
?>
<html>
<head>
    <?php echo Header::setupHeader(['select2']); ?>
    <title><?php echo xlt('Plantillas de Hoja de Cargos'); ?></title>
    <style>
        body {
            background: #eef3f7;
        }

        .package-shell {
            margin: 24px auto;
            max-width: 1500px;
            padding: 0 18px 32px;
        }

        .package-toolbar {
            align-items: center;
            background: linear-gradient(135deg, #11324d 0%, #235789 100%);
            border-radius: 8px;
            color: #fff;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin-bottom: 18px;
            padding: 18px 22px;
        }

        .package-toolbar h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .package-toolbar p {
            color: rgba(255, 255, 255, 0.82);
            margin: 0;
        }

        .package-toolbar-actions {
            align-items: center;
            display: flex;
            gap: 10px;
        }

        .package-help-card {
            background: #ffffff;
            border: 1px solid #d8e0e8;
            border-left: 5px solid #235789;
            border-radius: 8px;
            color: #314559;
            margin-bottom: 18px;
            padding: 14px 18px;
        }

        .package-help-card strong {
            color: #17324d;
            display: block;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .package-help-card p {
            margin: 0;
        }

        .package-save-alert {
            background: #e8f7ee;
            border: 1px solid #b7e1c2;
            border-left: 5px solid #2d9d55;
            border-radius: 8px;
            color: #236d3a;
            font-weight: 700;
            margin-bottom: 18px;
            padding: 12px 18px;
        }

        .package-panel {
            background: #fff;
            border: 1px solid #d8e0e8;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(17, 50, 77, 0.08);
            overflow: hidden;
        }

        .package-panel-head {
            align-items: center;
            border-bottom: 1px solid #e3e9ef;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 16px 18px;
        }

        .package-panel-head strong {
            color: #17324d;
            display: block;
            font-size: 16px;
        }

        .package-panel-head span {
            color: #68788a;
            display: block;
            font-size: 13px;
            margin-top: 2px;
        }

        .package-filter {
            min-width: 260px;
            width: 360px;
        }

        .package-card-list {
            display: grid;
            gap: 14px;
            padding: 16px;
        }

        .package-group {
            border: 1px solid #d8e0e8;
            border-radius: 10px;
            overflow: hidden;
        }

        .package-group-header {
            align-items: center;
            background: linear-gradient(180deg, #f8fbfd 0%, #eef4f8 100%);
            border-bottom: 1px solid #d8e0e8;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            padding: 12px 14px;
        }

        .package-group-title-wrap {
            min-width: 0;
        }

        .package-group-title {
            color: #17324d;
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .package-group-subtitle {
            color: #66788a;
            font-size: 12px;
            margin-top: 2px;
        }

        .package-group-body {
            display: grid;
            gap: 14px;
            padding: 14px;
        }

        .package-group.is-collapsed .package-group-body {
            display: none;
        }

        .package-card {
            background: #ffffff;
            border: 1px solid #d8e0e8;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(17, 50, 77, 0.06);
            overflow: hidden;
        }

        .package-card:hover {
            border-color: #b8c5d1;
            box-shadow: 0 6px 16px rgba(17, 50, 77, 0.1);
        }

        .package-card-header {
            align-items: center;
            background: #f5f8fb;
            border-bottom: 1px solid #e3e9ef;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px 14px;
        }

        .package-card-title {
            align-items: center;
            color: #17324d;
            display: flex;
            font-size: 15px;
            font-weight: 700;
            gap: 8px;
            margin: 0;
        }

        .package-card-title i {
            color: #235789;
        }

        .package-card-summary {
            align-items: center;
            color: #66788a;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
        }

        .package-chip {
            background: #edf4fa;
            border: 1px solid #d0deea;
            border-radius: 999px;
            color: #2e4b63;
            display: inline-flex;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            padding: 6px 8px;
            text-transform: uppercase;
        }

        .package-chip.package-chip-category {
            background: #e7f0fb;
            border-color: #bfd4ee;
            color: #235789;
        }

        .package-chip.package-chip-count {
            background: #eef7ec;
            border-color: #cfe4c7;
            color: #31724a;
        }

        .package-row-remove {
            color: #a42c2c;
        }

        .package-card-body {
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(180px, 240px) minmax(220px, 280px) minmax(360px, 1fr);
            padding: 14px;
        }

        .package-field label,
        .package-codes-label {
            color: #536271;
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .package-codes-area {
            min-width: 0;
        }

        .package-empty-hint {
            background: #fff8e7;
            border: 1px dashed #dba94b;
            border-radius: 6px;
            color: #7a5818;
            font-size: 13px;
            margin-bottom: 8px;
            padding: 8px 10px;
        }

        @media (max-width: 980px) {
            .package-panel-head,
            .package-toolbar,
            .package-card-header {
                align-items: stretch;
                flex-direction: column;
            }

            .package-filter {
                min-width: 0;
                width: 100%;
            }

            .package-card-body {
                grid-template-columns: 1fr;
            }
        }

        .feesheet-code-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 360px;
        }

        .feesheet-code-row {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #d8e0e8;
            border-left: 5px solid #7b8794;
            border-radius: 6px;
            display: flex;
            gap: 8px;
            padding: 7px 8px;
        }

        .feesheet-code-row:hover {
            background: #ffffff;
            border-color: #b8c5d1;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }

        .feesheet-code-type-cpt4 {
            border-left-color: #2f80ed;
        }

        .feesheet-code-type-cpt42 {
            border-left-color: #00b894;
        }

        .feesheet-code-type-cpt4a {
            border-left-color: #9b59b6;
        }

        .feesheet-code-type-rxcui {
            border-left-color: #e67e22;
        }

        .feesheet-code-type-insum {
            border-left-color: #e74c3c;
        }

        .feesheet-code-type-hcpcs {
            border-left-color: #00a878;
        }

        .feesheet-code-type-prod {
            border-left-color: #d98324;
        }

        .feesheet-code-type-icd,
        .feesheet-code-type-icd9,
        .feesheet-code-type-icd10 {
            border-left-color: #8e44ad;
        }

        .feesheet-code-type-ma {
            border-left-color: #c0392b;
        }

        .feesheet-code-badge {
            border-radius: 4px;
            color: #fff;
            flex: 0 0 auto;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            min-width: 50px;
            padding: 5px 7px;
            text-align: center;
        }

        .feesheet-code-type-cpt4 .feesheet-code-badge {
            background: #2f80ed;
        }

        .feesheet-code-type-cpt42 .feesheet-code-badge {
            background: #00b894;
        }

        .feesheet-code-type-cpt4a .feesheet-code-badge {
            background: #9b59b6;
        }

        .feesheet-code-type-rxcui .feesheet-code-badge {
            background: #e67e22;
        }

        .feesheet-code-type-insum .feesheet-code-badge {
            background: #e74c3c;
        }

        .feesheet-code-type-hcpcs .feesheet-code-badge {
            background: #00a878;
        }

        .feesheet-code-type-prod .feesheet-code-badge {
            background: #d98324;
        }

        .feesheet-code-type-icd .feesheet-code-badge,
        .feesheet-code-type-icd9 .feesheet-code-badge,
        .feesheet-code-type-icd10 .feesheet-code-badge {
            background: #8e44ad;
        }

        .feesheet-code-type-ma .feesheet-code-badge {
            background: #c0392b;
        }

        .feesheet-code-type-other .feesheet-code-badge {
            background: #7b8794;
        }

        .feesheet-code-main {
            flex: 1 1 auto;
            min-width: 140px;
        }

        .feesheet-code-desc {
            color: #22303c;
            display: block;
            font-weight: 600;
            line-height: 1.25;
        }

        .feesheet-code-value {
            color: #66788a;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }

        .feesheet-code-qty-group {
            align-items: center;
            display: flex;
            flex: 0 0 auto;
            gap: 3px;
        }

        .feesheet-code-qty-label {
            color: #536271;
            font-size: 12px;
            font-weight: 700;
            margin-right: 3px;
        }

        .feesheet-code-quantity {
            max-width: 58px;
            min-width: 58px;
            text-align: center;
        }

        .feesheet-code-step,
        .feesheet-code-order,
        .feesheet-code-edit,
        .feesheet-code-delete {
            align-items: center;
            border-radius: 4px;
            display: inline-flex;
            height: 28px;
            justify-content: center;
            padding: 0;
            width: 28px;
        }

        .feesheet-code-order {
            color: #4f667c;
        }

        .feesheet-code-edit {
            color: #2f6fa7;
        }

        .feesheet-code-delete {
            color: #a42c2c;
        }

        .feesheet-add-code {
            display: inline-block;
            margin-top: 6px;
        }

        .empty-row td {
            opacity: 0.92;
        }

        .package-card-actions {
            align-items: center;
            display: flex;
            gap: 8px;
        }

        .package-card-toggle {
            color: #235789;
            font-weight: 700;
        }

        .package-card.is-collapsed .package-card-body {
            display: none;
        }

        .package-card.is-collapsed .package-card-header {
            border-bottom: 0;
        }
    </style>
    <script type="text/javascript">
        var current_lino = 0;
        var current_replace_index = null;
        var next_fee_sheet_line = <?php echo js_escape(count($rows)); ?>;

        function cleanPackageLabel(value) {
            var text = $.trim(value || '');
            text = text.replace(/^\d+\s*/, '');
            text = text.replace(/^[-_.\s]+/, '');
            return text || <?php echo xlj('Nuevo paquete'); ?>;
        }

        function cleanCategoryLabel(value) {
            var text = $.trim(value || '');
            text = text.replace(/^\d+\s*/, '');
            text = text.replace(/^[-_.\s]+/, '');
            return text || <?php echo xlj('Sin categoría'); ?>;
        }

        function getPackageKeywords(value) {
            var text = (value || '').toUpperCase();
            var map = [
                {match: 'IESS', label: 'IESS'},
                {match: 'VPP', label: 'VPP'},
                {match: 'FACO', label: 'FACO'},
                {match: 'LIO', label: 'LIO'},
                {match: 'SIN LIO', label: 'SIN LIO'},
                {match: 'AVASTIN', label: 'AVASTIN'},
                {match: 'SILICON', label: 'SILICON'},
                {match: 'GAS', label: 'GAS'},
                {match: 'LASER', label: 'LASER'}
            ];
            var tags = [];
            for (var i = 0; i < map.length; ++i) {
                if (text.indexOf(map[i].match) !== -1) {
                    tags.push(map[i].label);
                }
            }
            return tags.slice(0, 3);
        }

        function getPackageCodeCount(lino) {
            return getFeeSheetCodeGroups(lino).length;
        }

        function updatePackageCardHeader(lino) {
            var card = $('#package-row-' + lino);
            if (!card.length) {
                return;
            }

            var category = cleanCategoryLabel(card.find('input[name="opt[' + lino + '][category]"]').val());
            var option = cleanPackageLabel(card.find('input[name="opt[' + lino + '][option]"]').val());
            var count = getPackageCodeCount(lino);
            var keywords = getPackageKeywords(option);

            card.attr('data-category-label', category);
            card.attr('data-package-label', option);

            card.find('.package-card-title-text').text(option);
            card.find('.package-card-summary').empty()
                .append("<span class='package-chip package-chip-category'>" + $('<div>').text(category).html() + "</span>")
                .append("<span class='package-chip package-chip-count'>" + count + " <?php echo attr(xl('códigos')); ?></span>");

            for (var i = 0; i < keywords.length; ++i) {
                card.find('.package-card-summary').append("<span class='package-chip'>" + $('<div>').text(keywords[i]).html() + "</span>");
            }
        }

        function togglePackageGroup(groupKey) {
            var group = $('#package-group-' + groupKey);
            var button = $('#package-group-toggle-' + groupKey);
            if (!group.length || !button.length) {
                return false;
            }
            group.toggleClass('is-collapsed');
            button.html(group.hasClass('is-collapsed')
                ? '<i class="fa fa-chevron-down" aria-hidden="true"></i> <?php echo attr(xl('Ver paquetes')); ?>'
                : '<i class="fa fa-chevron-up" aria-hidden="true"></i> <?php echo attr(xl('Ocultar')); ?>');
            return false;
        }

        function slugifyCategory(value) {
            var normalized = cleanCategoryLabel(value).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            return normalized || 'sin-categoria';
        }

        function rebuildPackageGroups() {
            var container = $('#package-builder-body');
            var cards = container.children('.package-card').get();
            var groups = {};
            var order = [];

            for (var i = 0; i < cards.length; ++i) {
                var card = $(cards[i]);
                if (!card.find('input[name*="[category]"]').length) {
                    continue;
                }
                var line = card.data('line');
                updatePackageCardHeader(line);
                var category = card.attr('data-category-label') || <?php echo xlj('Sin categoría'); ?>;
                if (!groups[category]) {
                    groups[category] = [];
                    order.push(category);
                }
                groups[category].push(cards[i]);
            }

            container.empty();

            for (var j = 0; j < order.length; ++j) {
                var categoryLabel = order[j];
                var groupCards = groups[categoryLabel];
                var groupKey = slugifyCategory(categoryLabel) + '-' + j;
                var group = $("<section class='package-group'></section>").attr('id', 'package-group-' + groupKey);
                var body = $("<div class='package-group-body'></div>");
                for (var k = 0; k < groupCards.length; ++k) {
                    body.append(groupCards[k]);
                }
                group.append(
                    "<div class='package-group-header'>" +
                    "<div class='package-group-title-wrap'>" +
                    "<h2 class='package-group-title'>" + $('<div>').text(categoryLabel).html() + "</h2>" +
                    "<div class='package-group-subtitle'>" + groupCards.length + " <?php echo attr(xl('paquetes')); ?></div>" +
                    "</div>" +
                    "<button type='button' id='package-group-toggle-" + groupKey + "' class='btn btn-link btn-xs package-card-toggle' onclick='return togglePackageGroup(\"" + groupKey + "\")'>" +
                    "<i class='fa fa-chevron-up' aria-hidden='true'></i> <?php echo attr(xl('Ocultar')); ?>" +
                    "</button>" +
                    "</div>"
                );
                group.append(body);
                container.append(group);
            }
        }

        function getFeeSheetCodeGroups(lino) {
            var form = document.forms[0];
            var codesField = form['opt[' + lino + '][codes]'];
            var descsField = form['opt[' + lino + '][descs]'];
            if (!codesField || !codesField.value.length) {
                return [];
            }

            var arrcodes = codesField.value.split('~');
            var arrdescs = descsField && descsField.value.length ? descsField.value.split('~') : [];
            var grouped = [];
            var groupedIndex = {};

            for (var i = 0; i < arrcodes.length; ++i) {
                if (!arrcodes[i]) {
                    continue;
                }
                var desc = arrdescs[i] || arrcodes[i];
                var key = arrcodes[i] + "\u0000" + desc;
                if (groupedIndex[key] === undefined) {
                    groupedIndex[key] = grouped.length;
                    grouped.push({code: arrcodes[i], desc: desc, quantity: 0});
                }
                grouped[groupedIndex[key]].quantity++;
            }

            return grouped;
        }

        function getFeeSheetCodeType(code) {
            return (code.split('|')[0] || '').toUpperCase();
        }

        function getFeeSheetCodeValue(code) {
            var parts = code.split('|');
            return parts.length > 1 ? parts[1] : code;
        }

        function getFeeSheetFinderValue(code) {
            var parts = code.split('|');
            if (parts.length < 2) {
                return code;
            }
            return parts[0] + ':' + parts[1];
        }

        function getFeeSheetCodeTypeClass(codeType) {
            var normalized = codeType.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (normalized == 'icd9' || normalized == 'icd10') {
                return 'feesheet-code-type-' + normalized;
            }
            if (
                normalized == 'cpt4' ||
                normalized == 'cpt42' ||
                normalized == 'cpt4a' ||
                normalized == 'rxcui' ||
                normalized == 'insum' ||
                normalized == 'hcpcs' ||
                normalized == 'prod' ||
                normalized == 'ma'
            ) {
                return 'feesheet-code-type-' + normalized;
            }
            if (normalized.indexOf('icd') === 0) {
                return 'feesheet-code-type-icd';
            }
            return 'feesheet-code-type-other';
        }

        function writeFeeSheetCodeGroups(lino, groups) {
            var form = document.forms[0];
            var arrcodes = [];
            var arrdescs = [];

            for (var i = 0; i < groups.length; ++i) {
                var quantity = Math.max(1, parseInt(groups[i].quantity, 10) || 1);
                for (var j = 0; j < quantity; ++j) {
                    arrcodes.push(groups[i].code);
                    arrdescs.push(groups[i].desc);
                }
            }

            form['opt[' + lino + '][codes]'].value = arrcodes.join('~');
            form['opt[' + lino + '][descs]'].value = arrdescs.join('~');
        }

        function rebuildFeeSheetLine(lino) {
            var groups = [];
            $('#codelist_' + lino + ' .feesheet-code-quantity').each(function () {
                groups.push({
                    code: this.getAttribute('data-code'),
                    desc: this.getAttribute('data-desc'),
                    quantity: this.value
                });
            });
            writeFeeSheetCodeGroups(lino, groups);
        }

        function changeFeeSheetQuantity(lino, input, amount) {
            var current = parseInt(input.value, 10) || 1;
            input.value = Math.max(1, current + amount);
            rebuildFeeSheetLine(lino);
            displayCodes(lino);
        }

        function moveFeeSheetCode(lino, seqno, direction) {
            rebuildFeeSheetLine(lino);
            var groups = getFeeSheetCodeGroups(lino);
            var target = seqno + direction;
            if (target < 0 || target >= groups.length) {
                return false;
            }
            var item = groups.splice(seqno, 1)[0];
            groups.splice(target, 0, item);
            writeFeeSheetCodeGroups(lino, groups);
            displayCodes(lino);
            return false;
        }

        function prepareFeeSheetOptions() {
            var form = document.forms[0];
            for (var lino = 1; form['opt[' + lino + '][codes]']; ++lino) {
                rebuildFeeSheetLine(lino);
            }
        }

        function displayCodes(lino) {
            var list = document.getElementById('codelist_' + lino);
            var groups = getFeeSheetCodeGroups(lino);

            list.className = 'feesheet-code-list';
            list.innerHTML = '';
            var emptyHint = document.getElementById('package-empty-hint-' + lino);
            if (emptyHint) {
                emptyHint.style.display = groups.length ? 'none' : 'block';
            }
            for (var i = 0; i < groups.length; ++i) {
                var codeType = getFeeSheetCodeType(groups[i].code);
                var codeValue = getFeeSheetCodeValue(groups[i].code);
                var row = document.createElement('div');
                row.className = 'feesheet-code-row ' + getFeeSheetCodeTypeClass(codeType);

                var badge = document.createElement('span');
                badge.className = 'feesheet-code-badge';
                badge.appendChild(document.createTextNode(codeType || <?php echo xlj('Code'); ?>));

                var main = document.createElement('div');
                main.className = 'feesheet-code-main';

                var desc = document.createElement('strong');
                desc.className = 'feesheet-code-desc';
                desc.appendChild(document.createTextNode(groups[i].desc));

                var value = document.createElement('span');
                value.className = 'feesheet-code-value';
                value.appendChild(document.createTextNode(codeValue));

                main.appendChild(desc);
                main.appendChild(value);

                var qtyGroup = document.createElement('div');
                qtyGroup.className = 'feesheet-code-qty-group';

                var qtyLabel = document.createElement('label');
                qtyLabel.className = 'feesheet-code-qty-label';
                qtyLabel.appendChild(document.createTextNode(<?php echo xlj('Cantidad'); ?>));

                var minus = document.createElement('button');
                minus.type = 'button';
                minus.className = 'btn btn-default btn-xs feesheet-code-step';
                minus.title = <?php echo xlj('Disminuir cantidad'); ?>;
                minus.appendChild(document.createTextNode('-'));

                var qty = document.createElement('input');
                qty.type = 'number';
                qty.min = '1';
                qty.step = '1';
                qty.className = 'form-control input-sm feesheet-code-quantity';
                qty.value = groups[i].quantity;
                qty.setAttribute('data-code', groups[i].code);
                qty.setAttribute('data-desc', groups[i].desc);
                qty.onchange = (function (lineNumber, input) {
                    return function () {
                        input.value = Math.max(1, parseInt(input.value, 10) || 1);
                        rebuildFeeSheetLine(lineNumber);
                        displayCodes(lineNumber);
                    };
                })(lino, qty);

                var plus = document.createElement('button');
                plus.type = 'button';
                plus.className = 'btn btn-default btn-xs feesheet-code-step';
                plus.title = <?php echo xlj('Aumentar cantidad'); ?>;
                plus.appendChild(document.createTextNode('+'));

                minus.onclick = (function (lineNumber, input) {
                    return function () {
                        changeFeeSheetQuantity(lineNumber, input, -1);
                    };
                })(lino, qty);

                plus.onclick = (function (lineNumber, input) {
                    return function () {
                        changeFeeSheetQuantity(lineNumber, input, 1);
                    };
                })(lino, qty);

                var editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'btn btn-link btn-xs feesheet-code-edit';
                editButton.title = <?php echo xlj('Cambiar código'); ?>;
                editButton.innerHTML = '<i class="fa fa-pencil" aria-hidden="true"></i>';
                editButton.onclick = (function (lineNumber, groupIndex) {
                    return function () {
                        return replace_code(lineNumber, groupIndex);
                    };
                })(lino, i);

                var upButton = document.createElement('button');
                upButton.type = 'button';
                upButton.className = 'btn btn-link btn-xs feesheet-code-order';
                upButton.title = <?php echo xlj('Subir código'); ?>;
                upButton.innerHTML = '<i class="fa fa-arrow-up" aria-hidden="true"></i>';
                upButton.disabled = i === 0;
                upButton.onclick = (function (lineNumber, groupIndex) {
                    return function () {
                        return moveFeeSheetCode(lineNumber, groupIndex, -1);
                    };
                })(lino, i);

                var downButton = document.createElement('button');
                downButton.type = 'button';
                downButton.className = 'btn btn-link btn-xs feesheet-code-order';
                downButton.title = <?php echo xlj('Bajar código'); ?>;
                downButton.innerHTML = '<i class="fa fa-arrow-down" aria-hidden="true"></i>';
                downButton.disabled = i === (groups.length - 1);
                downButton.onclick = (function (lineNumber, groupIndex) {
                    return function () {
                        return moveFeeSheetCode(lineNumber, groupIndex, 1);
                    };
                })(lino, i);

                var deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-link btn-xs feesheet-code-delete';
                deleteButton.title = <?php echo xlj('Quitar código'); ?>;
                deleteButton.innerHTML = '<i class="fa fa-trash" aria-hidden="true"></i>';
                deleteButton.onclick = (function (lineNumber, groupIndex) {
                    return function () {
                        return delete_code(lineNumber, groupIndex);
                    };
                })(lino, i);

                qtyGroup.appendChild(qtyLabel);
                qtyGroup.appendChild(minus);
                qtyGroup.appendChild(qty);
                qtyGroup.appendChild(plus);

                row.appendChild(badge);
                row.appendChild(main);
                row.appendChild(qtyGroup);
                row.appendChild(upButton);
                row.appendChild(downButton);
                row.appendChild(editButton);
                row.appendChild(deleteButton);
                list.appendChild(row);
            }
            updatePackageCardHeader(lino);
        }

        function delete_code(lino, seqno) {
            if (!confirm(<?php echo xlj('¿Desea quitar este código del paquete?'); ?>)) {
                return false;
            }
            rebuildFeeSheetLine(lino);
            var groups = getFeeSheetCodeGroups(lino);
            groups.splice(seqno, 1);
            writeFeeSheetCodeGroups(lino, groups);
            displayCodes(lino);
            return false;
        }

        function replace_code(lino, seqno) {
            rebuildFeeSheetLine(lino);
            current_lino = lino;
            current_replace_index = seqno;
            dlgopen('../patient_file/encounter/find_code_dynamic.php', '_blank', 900, 600);
            return false;
        }

        function select_code(lino) {
            current_lino = lino;
            current_replace_index = null;
            dlgopen('../patient_file/encounter/find_code_dynamic.php', '_blank', 900, 600);
            return false;
        }

        function set_related(codetype, code, selector, codedesc) {
            var form = document.forms[0];
            rebuildFeeSheetLine(current_lino);
            var codesField = form['opt[' + current_lino + '][codes]'];
            var descsField = form['opt[' + current_lino + '][descs]'];

            while (codedesc.indexOf('~') >= 0) {
                codedesc = codedesc.replace('~', ' ');
            }

            if (code) {
                var newCode = codetype + '|' + code + '|' + selector;
                var newDesc = codetype == 'PROD' ? (code + ':' + selector + ' ' + codedesc) : (codetype + ':' + code + ' ' + codedesc);
                if (current_replace_index !== null) {
                    var groups = getFeeSheetCodeGroups(current_lino);
                    if (groups[current_replace_index]) {
                        groups[current_replace_index].code = newCode;
                        groups[current_replace_index].desc = newDesc;
                        writeFeeSheetCodeGroups(current_lino, groups);
                    }
                } else {
                    if (codesField.value) {
                        codesField.value += '~';
                        descsField.value += '~';
                    }
                    codesField.value += newCode;
                    descsField.value += newDesc;
                }
            } else if (current_replace_index === null) {
                codesField.value = '';
                descsField.value = '';
            }

            displayCodes(current_lino);
            current_replace_index = null;
        }

        function del_related() {
            var form = document.forms[0];
            rebuildFeeSheetLine(current_lino);
            if (current_replace_index !== null) {
                var groups = getFeeSheetCodeGroups(current_lino);
                groups.splice(current_replace_index, 1);
                writeFeeSheetCodeGroups(current_lino, groups);
            } else {
                form['opt[' + current_lino + '][codes]'].value = '';
                form['opt[' + current_lino + '][descs]'].value = '';
            }
            displayCodes(current_lino);
            current_replace_index = null;
        }

        function get_related() {
            if (current_replace_index !== null) {
                var groups = getFeeSheetCodeGroups(current_lino);
                if (groups[current_replace_index]) {
                    return [getFeeSheetFinderValue(groups[current_replace_index].code)];
                }
            }
            return [];
        }

        function togglePackageCard(lino) {
            var card = document.getElementById('package-row-' + lino);
            var button = document.getElementById('package-toggle-' + lino);

            if (!card || !button) {
                return false;
            }

            if ($(card).hasClass('is-collapsed')) {
                $(card).removeClass('is-collapsed');
                button.innerHTML = '<i class="fa fa-chevron-up"></i> Ocultar';
            } else {
                $(card).addClass('is-collapsed');
                button.innerHTML = '<i class="fa fa-chevron-down"></i> Ver detalles';
            }

            return false;
        }

        function addPackageRow(category, option, codes, descs) {
            next_fee_sheet_line++;
            var line = next_fee_sheet_line;
            var list = document.getElementById('package-builder-body');
            var card = document.createElement('div');
            card.id = 'package-row-' + line;
            card.className = 'package-card empty-row';
            card.setAttribute('data-line', line);

            card.innerHTML =
                "<div class='package-card-header'>" +
                "<div><h3 class='package-card-title'><i class='fa fa-folder-open' aria-hidden='true'></i> <span class='package-card-title-text'><?php echo attr(xl('Nuevo paquete')); ?></span></h3><div class='package-card-summary'></div></div>" +
                "<button type='button' class='btn btn-link btn-xs package-row-remove' title='<?php echo xla('Eliminar paquete'); ?>' onclick='return removePackageRow(" + line + ")'><i class='fa fa-times' aria-hidden='true'></i> <?php echo attr(xl('Eliminar')); ?></button>" +
                "</div>" +
                "<div class='package-card-body'>" +
                "<div class='package-field'>" +
                "<label><?php echo attr(xl('Categoría')); ?></label>" +
                "<input type='text' class='form-control' name='opt[" + line + "][category]' placeholder='<?php echo attr(xl('Ej: Catarata, Retina, Insumos')); ?>' value='" + $('<div>').text(category || '').html() + "' />" +
                "</div>" +
                "<div class='package-field'>" +
                "<label><?php echo attr(xl('Nombre del paquete')); ?></label>" +
                "<input type='text' class='form-control' name='opt[" + line + "][option]' placeholder='<?php echo attr(xl('Ej: Faco + LIO')); ?>' value='" + $('<div>').text(option || '').html() + "' />" +
                "</div>" +
                "<div class='package-codes-area'>" +
                "<span class='package-codes-label'><?php echo attr(xl('Códigos generados')); ?></span>" +
                "<div id='package-empty-hint-" + line + "' class='package-empty-hint'><?php echo attr(xl('Este paquete aún no tiene códigos. Agregue al menos uno antes de guardar.')); ?></div>" +
                "<div id='codelist_" + line + "'></div>" +
                "<a href='' class='btn btn-default btn-xs feesheet-add-code' onclick='return select_code(" + line + ")'><i class='fa fa-plus' aria-hidden='true'></i> <?php echo attr(xl('Agregar código')); ?></a>" +
                "<input type='hidden' name='opt[" + line + "][codes]' value='" + $('<div>').text(codes || '').html() + "' />" +
                "<input type='hidden' name='opt[" + line + "][descs]' value='" + $('<div>').text(descs || '').html() + "' />" +
                "</div>" +
                "</div>";

            list.appendChild(card);
            displayCodes(line);
            rebuildPackageGroups();
            filterPackageRows();
            card.scrollIntoView({behavior: 'smooth', block: 'nearest'});
        }

        function removePackageRow(lino) {
            if (!confirm(<?php echo xlj('¿Desea eliminar este paquete?'); ?>)) {
                return false;
            }
            var row = document.getElementById('package-row-' + lino);
            if (row) {
                row.className = 'empty-row';
                $(row).hide();
                var form = document.forms[0];
                form['opt[' + lino + '][category]'].value = '';
                form['opt[' + lino + '][option]'].value = '';
                form['opt[' + lino + '][codes]'].value = '';
                form['opt[' + lino + '][descs]'].value = '';
                var codeList = document.getElementById('codelist_' + lino);
                if (codeList) {
                    codeList.innerHTML = '';
                }
            }
            rebuildPackageGroups();
            filterPackageRows();
            return false;
        }

        function filterPackageRows() {
            var query = ($('#package-filter').val() || '').toLowerCase().trim();
            $('#package-builder-body .package-card').each(function () {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(query) !== -1);
            });
            $('#package-builder-body .package-group').each(function () {
                var visibleCards = $(this).find('.package-card:visible').length;
                $(this).toggle(visibleCards > 0);
            });
        }

        function submitPackages() {
            prepareFeeSheetOptions();
            document.getElementById('formaction').value = 'save';
            document.getElementById('package-builder-form').submit();
        }

        $(function () {
            $('.package-card').each(function () {
                var line = $(this).data('line');
                updatePackageCardHeader(line);
            });
            $(document).on('input', 'input[name*="[category]"], input[name*="[option]"]', function () {
                var match = this.name.match(/opt\[(\d+)\]/);
                if (!match) {
                    return;
                }
                var line = parseInt(match[1], 10);
                updatePackageCardHeader(line);
                rebuildPackageGroups();
                filterPackageRows();
            });
            $('#package-filter').on('input', filterPackageRows);
            rebuildPackageGroups();
        });
    </script>
</head>
<body class="body_top">
<div class="package-shell">
    <?php if ($savedSuccessfully) { ?>
        <div class="package-save-alert">
            <i class="fa fa-check-circle" aria-hidden="true"></i>
            <?php echo xlt('Cambios guardados correctamente.'); ?>
        </div>
    <?php } ?>
    <form method="post" id="package-builder-form" action="fee_sheet_packages.php">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
        <input type="hidden" name="formaction" id="formaction" value=""/>

        <div class="package-toolbar">
            <div>
                <h1><?php echo xlt('Plantillas de Hoja de Cargos'); ?></h1>
                <p><?php echo xlt('Cree paquetes de procedimientos, insumos o códigos para agregarlos rápidamente en la hoja de cargos.'); ?></p>
            </div>
            <div class="package-toolbar-actions">
                <button type="button" class="btn btn-default" onclick="addPackageRow('', '', '', '')">
                    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo xlt('Nuevo paquete'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-save" onclick="submitPackages()">
                    <i class="fa fa-save" aria-hidden="true"></i> <?php echo xlt('Guardar cambios'); ?>
                </button>
            </div>
        </div>

        <div class="package-help-card">
            <strong><?php echo xlt('¿Cómo usar esta pantalla?'); ?></strong>
            <p><?php echo xlt('Cada paquete pertenece a una categoría, tiene un nombre visible y contiene los códigos que se cargarán automáticamente en la hoja de cargos. Use la cantidad para repetir un mismo código sin duplicarlo manualmente.'); ?></p>
        </div>

        <div class="package-panel">
            <div class="package-panel-head">
                <div>
                    <strong><?php echo xlt('Paquetes configurados'); ?></strong>
                    <span><?php echo xlt('Defina la categoría, el nombre del paquete y los códigos que se generarán.'); ?></span>
                </div>
                <input
                    type="text"
                    id="package-filter"
                    class="form-control package-filter"
                    placeholder="<?php echo attr(xl('Buscar por categoría, paquete, código o descripción')); ?>"
                />
            </div>
            <div class="package-card-list" id="package-builder-body">
                <?php $line = 0;
                foreach ($rows as $row) {
                    ++$line; ?>
                    <?php $isEmptyRow = ($row['fs_category'] === '' && $row['fs_option'] === '' && $row['fs_codes'] === ''); ?>
                    <div id="package-row-<?php echo attr($line); ?>"
                         data-line="<?php echo attr($line); ?>"
                         class="package-card is-collapsed<?php echo $isEmptyRow ? ' empty-row' : ''; ?>">
                        <div class="package-card-header">
                            <div>
                                <h3 class="package-card-title">
                                    <i class="fa fa-folder-open" aria-hidden="true"></i>
                                    <span class="package-card-title-text"><?php echo $isEmptyRow ? xlt('Nuevo paquete') : text($row['fs_option']); ?></span>
                                </h3>
                                <div class="package-card-summary"></div>
                            </div>
                            <div class="package-card-actions">
                                <button
                                    type="button"
                                    id="package-toggle-<?php echo attr($line); ?>"
                                    class="btn btn-link btn-xs package-card-toggle"
                                    onclick="return togglePackageCard(<?php echo attr($line); ?>)"
                                >
                                    <i class="fa fa-chevron-down"
                                       aria-hidden="true"></i> <?php echo xlt('Ver detalles'); ?>
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-link btn-xs package-row-remove"
                                    title="<?php echo xla('Eliminar paquete'); ?>"
                                    onclick="return removePackageRow(<?php echo attr($line); ?>)"
                                >
                                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo xlt('Eliminar'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="package-card-body">
                            <div class="package-field">
                                <label><?php echo xlt('Categoría'); ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="opt[<?php echo attr($line); ?>][category]"
                                    placeholder="<?php echo attr(xl('Ej: Catarata, Retina, Insumos')); ?>"
                                    value="<?php echo attr($row['fs_category']); ?>"
                                />
                            </div>
                            <div class="package-field">
                                <label><?php echo xlt('Nombre del paquete'); ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="opt[<?php echo attr($line); ?>][option]"
                                    placeholder="<?php echo attr(xl('Ej: Faco + LIO')); ?>"
                                    value="<?php echo attr($row['fs_option']); ?>"
                                />
                            </div>
                            <div class="package-codes-area">
                                <span class="package-codes-label"><?php echo xlt('Códigos generados'); ?></span>
                                <div id="package-empty-hint-<?php echo attr($line); ?>" class="package-empty-hint">
                                    <?php echo xlt('Este paquete aún no tiene códigos. Agregue al menos uno antes de guardar.'); ?>
                                </div>
                                <div id="codelist_<?php echo attr($line); ?>"></div>
                                <a href="" class="btn btn-default btn-xs feesheet-add-code"
                                   onclick="return select_code(<?php echo attr($line); ?>)">
                                    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo xlt('Agregar código'); ?>
                                </a>
                                <input type="hidden" name="opt[<?php echo attr($line); ?>][codes]"
                                       value="<?php echo attr($row['fs_codes']); ?>"/>
                                <input type="hidden" name="opt[<?php echo attr($line); ?>][descs]"
                                       value="<?php echo attr($row['fs_descs']); ?>"/>
                                <script>displayCodes(<?php echo attr($line); ?>);</script>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </form>
</div>
</body>
</html>
