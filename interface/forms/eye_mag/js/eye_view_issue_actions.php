<?php
/**
 * Eye Mag view issue actions JavaScript module.
 *
 * @package OpenEMR
 */
?>
/**
 * Function to add a CODE to an IMPRESSION/PLAN item
 * This is for callback by the find-code popup in IMPPLAN area.
 * Appends to or erases the current list of diagnoses.
 */
function set_related(codetype, code, selector, codedesc) {
    //target is the index of IMPRESSION[index].code we are searching for.
    var span = document.getElementById('CODE_' + IMP_target);
    if ('textContent' in span) {
        span.textContent = code;
    } else {
        span.innerText = code;
    }
    $('#CODE_' + IMP_target).attr('title', codetype + ':' + code + ' (' + codedesc + ')');

    obj.IMPPLAN_items[IMP_target].code = code;
    obj.IMPPLAN_items[IMP_target].codetype = codetype;
    obj.IMPPLAN_items[IMP_target].codedesc = codedesc;
    obj.IMPPLAN_items[IMP_target].codetext = codetype + ':' + code + ' (' + codedesc + ')';
    // This lists the text for the CODE at the top of the PLAN_
    // It is already there on mouseover the code itself and is printed in reports//faxes, so it was removed here
    //  obj.IMPPLAN_items[IMP_target].plan = codedesc+"\r"+obj.IMPPLAN_items[IMP_target].plan;

    if (obj.IMPPLAN_items[IMP_target].PMSFH_link > '') {
        var data = obj.IMPPLAN_items[IMP_target].PMSFH_link.match(/(.*)_(.*)/);
        if ((data[1] == "POH") || (data[1] == "PMH")) {
            obj.PMSFH[data[1]][data[2]].code = code;
            obj.PMSFH[data[1]][data[2]].codetype = codetype;
            obj.PMSFH[data[1]][data[2]].codedesc = codedesc;
            obj.PMSFH[data[1]][data[2]].description = codedesc;
            obj.PMSFH[data[1]][data[2]].diagnosis = codetype + ':' + code;
            obj.PMSFH[data[1]][data[2]].codetext = codetype + ':' + code + ' (' + codedesc + ')';
            build_DX_list(obj);
            update_PMSFH_code(obj.PMSFH[data[1]][data[2]].issue, codetype + ':' + code);
        }
    }
    store_IMPPLAN(obj.IMPPLAN_items, '1');
}
<?php require_once("$srcdir/restoreSession.php");
?>
function dopclick(id) {
    <?php if ($canEditIssues) : ?>
    dlgopen('../../patient_file/summary/a_issue.php?issue=0&thistype=' + encodeURIComponent(id), '_blank', 550, 400, '', <?php echo xlj('Issues'); ?> );
    <?php else : ?>
    alert("<?php echo xls('You are not authorized to add/edit issues'); ?>");
    <?php endif; ?>
}

function doscript(type, id, encounter, rx_number) {
    dlgopen('../../forms/eye_mag/SpectacleRx.php?REFTYPE=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id) + '&encounter=' + encodeURIComponent(encounter) + '&form_id=' + <?php echo js_url($form_id); ?> +'&rx_number=' + encodeURIComponent(rx_number), '_blank', 660, 590, '', <?php echo xlj('Dispense Rx'); ?>);
}

function dispensed(pid) {
    dlgopen('../../forms/eye_mag/SpectacleRx.php?dispensed=1&pid=' + encodeURIComponent(pid), '_blank', 560, 590, '', <?php echo xlj('Rx History'); ?>);
}

// This invokes the find-code popup.
function sel_diagnosis(target, term) {
    if (target == '') {
        target = "0";
    }
    IMP_target = target;
    <?php
    if ($irow['type'] == 'PMH') { //or POH
    ?>
    dlgopen('<?php echo $rootdir ?>/patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("medical_problem", "csv")) ?>&search_term=' + encodeURI(term), '_blank', 600, 400, '', <?php echo xlj('Code Search'); ?>);
    <?php
    } else {
    ?>
    dlgopen('<?php echo $rootdir ?>/patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis", "csv")) ?>&search_term=' + encodeURI(term), '_blank', 600, 400, '', <?php echo xlj('Code Search'); ?>);
    <?php
    }
    ?>
}
