<?php
/**
 * Eye Mag IMPPLAN domain functions.
 *
 * @package OpenEMR
 */

/**
 * This builds the IMPPLAN_items variable for a given pid and form_id.
 *
 * @param string $pid patient_id
 * @param string $form_id field id in table form_eye_mag
 * @return array IMPPLAN_items
 */
function build_IMPPLAN_items($pid, $form_id)
{
    global $form_folder;
    $query = "select * from form_" . $form_folder . "_impplan where form_id=? and pid=? ORDER BY IMPPLAN_order";
    $fres = sqlStatement($query, array($form_id, $pid));
    $IMPPLAN_items = array();
    $i = 0;
    while ($frow = sqlFetchArray($fres)) {
        $IMPPLAN_items[$i]['form_id'] = $frow['form_id'];
        $IMPPLAN_items[$i]['pid'] = $frow['pid'];
        $IMPPLAN_items[$i]['id'] = $frow['id'];
        $IMPPLAN_items[$i]['title'] = $frow['title'];
        $IMPPLAN_items[$i]['code'] = $frow['code'];
        $IMPPLAN_items[$i]['codetype'] = $frow['codetype'];
        $IMPPLAN_items[$i]['codedesc'] = $frow['codedesc'];
        $IMPPLAN_items[$i]['codetext'] = $frow['codetext'];
        $IMPPLAN_items[$i]['plan'] = $frow['plan'];
        $IMPPLAN_items[$i]['PMSFH_link'] = $frow['PMSFH_link'];
        $IMPPLAN_items[$i]['IMPPLAN_order'] = $frow['IMPPLAN_order'];
        $i++;
    }

    return $IMPPLAN_items;
}
