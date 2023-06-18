<?php
include_once("../../globals.php");
include_once("$srcdir/forms.inc");

//function called to set the global session variable for encounter number
function setencounter($enc)
{

    // Escape $enc by forcing it to an integer to protect from sql injection
    $enc = intval($enc);

    $return_val = 1;
    global $encounter;
    global $pid;
    global $attendant_type;

    $attendant_id = $attendant_type == 'pid' ? $pid : $_SESSION['therapy_group'];
    if ($enc == "") {
        $enc = date("Ymd");
        if (getFormByEncounter($attendant_id, $enc)) {
            //there is an encounter enterred for today
        } else {
            //addForm($enc, "New Patient Encounter", 0, $pid, 1);
            $return_val = 0;
        }
    }

    $_SESSION['encounter'] = $enc;
    $encounter = $enc;


    //returns 1 on successful global set, or 0 if there was no
    //current encounter, signifying that the interface should load
    //the screen for a new encounter
    return $return_val;
}


//fetches encounter pc_catid by encounter number
function fetchCategoryIdByEncounter($encounter)
{
    global $attendant_type;
    $table = $attendant_type == 'pid' ? 'form_encounter' : 'form_groups_encounter';
    $sql = "SELECT pc_catid FROM " . escape_table_name($table) . " WHERE encounter = ? limit 1";
    $result = sqlQuery($sql, array($encounter));
    return $result['pc_catid'];
}

function fetchDateByEncounter($encounter)
{
    global $attendant_type;
    $table = $attendant_type == 'pid' ? 'form_encounter' : 'form_groups_encounter';
    $sql = "SELECT date FROM " . escape_table_name($table) . " WHERE encounter = ? limit 1";
    $result = sqlQuery($sql, array($encounter));
    return $result['date'];
}

function fetchNameByEncounter($pc_catid)
{
    global $attendant_type;
    $table = $attendant_type == 'pid' ? 'openemr_postcalendar_categories' : 'form_groups_encounter';
    $sql = "SELECT pc_catname FROM " . escape_table_name($table) . " WHERE pc_catid = ? limit 1";
    $result = sqlQuery($sql, array($pc_catid));
    return $result['pc_catname'];
}

//fetches encounter pc_catid by encounter number
function fetchEventIdByEncounter($encounter)
{
    global $attendant_type;
    $table = $attendant_type == 'pid' ? 'patient_tracker' : 'form_groups_encounter';
    $sql = "SELECT * FROM " . escape_table_name($table) . " WHERE encounter = ? limit 1";
    $result = sqlQuery($sql, array($encounter));
    return $result['eid'];
}

function getEventDetails($eventId)
{
    $sql = "SELECT pc_apptqx, pc_apptqxOI FROM openemr_postcalendar_events WHERE pc_eid = ?";
    $result = sqlQuery($sql, array($eventId));

    if ($result) {
        $eventDetails = array(
            'pc_apptqx' => $result['pc_apptqx'],
            'pc_apptqxOI' => $result['pc_apptqxOI']
        );

        return $eventDetails;
    } else {
        return false; // No se encontró el evento con el ID proporcionado
    }
}


