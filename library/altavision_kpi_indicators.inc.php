<?php
/**
 * Altavision ophthalmology KPI helpers.
 *
 * V1 intentionally keeps data capture unchanged. It computes the quick wins
 * that are already represented in OpenEMR and documents the remaining gaps.
 */

function altavision_kpi_workbook_sheet_names()
{
    return array('IESS', 'Resto', 'Todos', 'Resumen', 'Brechas');
}

function altavision_kpi_segments()
{
    return array(
        'iess' => 'IESS',
        'resto' => 'Resto',
        'todos' => 'Todos',
    );
}

function altavision_kpi_month_labels()
{
    return array(
        1 => 'ENE',
        2 => 'FEB',
        3 => 'MAR',
        4 => 'ABR',
        5 => 'MAY',
        6 => 'JUN',
        7 => 'JUL',
        8 => 'AGO',
        9 => 'SEP',
        10 => 'OCT',
        11 => 'NOV',
        12 => 'DIC',
    );
}

function altavision_kpi_empty_month_metrics()
{
    $months = array();
    foreach (array_keys(altavision_kpi_month_labels()) as $monthNumber) {
        $months[$monthNumber] = array(
            'observation_avg_minutes' => null,
            'observation_cases' => 0,
            'avg_wait_minutes' => null,
            'wait_count' => 0,
            'repeat_referral_returns' => 0,
            'referral_patients' => 0,
            'preop_avg_hours' => null,
            'preop_cases' => 0,
            'surgery_scheduled' => 0,
            'surgery_suspended' => 0,
            'complication_proxy_cases' => 0,
            'faco_lio_cases' => 0,
        );
    }

    return $months;
}

function altavision_kpi_segment_filter($segment)
{
    if ($segment === 'iess') {
        return array('label' => 'IESS', 'sql' => "p.pricelevel = 'IESS'");
    }

    if ($segment === 'resto') {
        return array('label' => 'Resto', 'sql' => "(p.pricelevel IS NULL OR p.pricelevel <> 'IESS')");
    }

    return array('label' => 'Todos', 'sql' => '1 = 1');
}

function altavision_kpi_indicator_definitions()
{
    return array(
        1 => array(
            'name' => 'Tiempo promedio de permanencia en observacion/recuperacion ambulatoria',
            'original' => 'Tiempo promedio de estancia en sala de observacion.',
            'status' => 'automatic',
            'formula' => 'Promedio de 15% del tiempo transcurrido entre inicio de quirofano y estado final atendido/cierre, solo en casos quirurgicos.',
            'source' => 'openemr_postcalendar_events + patient_tracker + patient_tracker_element + list_options + patient_data.pricelevel.',
            'gap' => 'Depende de identificar correctamente el estado local de quirofano y el cierre/atendido del tracker.',
            'action' => 'Validar nombres locales de estados de tracker para afinar el corte quirofano > atendido.',
        ),
        2 => array(
            'name' => 'Tiempo promedio de espera en consulta externa oftalmologica',
            'original' => 'Promedio del tiempo de espera de la atencion por especialidad en consulta externa.',
            'status' => 'automatic',
            'formula' => 'Promedio de minutos entre la mayor entre hora agendada y hora de llegada, y el primer estado real de atencion.',
            'source' => 'openemr_postcalendar_events + patient_tracker + patient_tracker_element + patient_data.pricelevel.',
            'gap' => 'Depende de que el flujo registre llegada y un estado real de atencion en patient tracker.',
            'action' => 'Auditar el uso consistente de llegada y del estado que marca inicio de atencion.',
        ),
        3 => array(
            'name' => 'Tiempo promedio de estancia hospitalaria',
            'original' => 'Tiempo promedio de estancia Hospitalaria.',
            'status' => 'not_applicable',
            'formula' => 'No aplica a una unidad oftalmologica ambulatoria.',
            'source' => 'No aplica.',
            'gap' => 'Indicador hospitalario sin denominador ambulatorio.',
            'action' => 'Excluir o reemplazar por permanencia ambulatoria.',
        ),
        4 => array(
            'name' => 'Retorno no programado por igual diagnostico CIE-10',
            'original' => 'Porcentaje de Reingreso Hospitalario por igual diagnostico CIE-10.',
            'status' => 'automatic',
            'formula' => 'Pacientes con mas de un referral y retorno posterior por el mismo diagnostico / pacientes con referrals del mismo diagnostico * 100.',
            'source' => 'transactions(LBTref) + lbt_data.refer_diag/refer_date + form_encounter + issue_encounter + lists.diagnosis.',
            'gap' => 'La coincidencia de diagnostico depende de texto normalizado; no siempre equivale a CIE-10 codificado.',
            'action' => 'Normalizar el diagnostico de referral a codigo estandar para subir precision.',
        ),
        5 => array(
            'name' => 'Infecciones asociadas a atencion/procedimiento oftalmologico',
            'original' => 'Porcentaje de infecciones Asociadas a atencion sanitaria en salud.',
            'status' => 'temporary_zero',
            'formula' => '0 temporal en v1 hasta definir captura estructurada de infeccion.',
            'source' => 'Sin captura estructurada actual.',
            'gap' => 'No hay campo normalizado de infeccion asociada a atencion.',
            'action' => 'Diseñar captura clinica antes de automatizar.',
            'fixed_value' => '0.0%',
            'fixed_month_value' => 0.0,
        ),
        6 => array(
            'name' => 'Tiempo prequirurgico ambulatorio',
            'original' => 'Promedio de Dias de estancia hospitalaria pre quirurgica.',
            'status' => 'not_applicable',
            'formula' => 'No aplica / unidad ambulatoria.',
            'source' => 'No aplica al modelo ambulatorio en dias.',
            'gap' => 'El indicador original mide estancia hospitalaria en dias.',
            'action' => 'Si se requiere seguimiento operativo, controlarlo aparte en horas.',
        ),
        7 => array(
            'name' => 'Cirugias oftalmologicas programadas suspendidas',
            'original' => 'Porcentaje de Cirugias programadas suspendidas por causa de gestion interna.',
            'status' => 'automatic',
            'formula' => 'Cirugias programadas con estado cancelado / total de cirugias programadas * 100.',
            'source' => 'openemr_postcalendar_events + campos de cirugia + patient_data.pricelevel.',
            'gap' => 'V1 no distingue causa gestion interna si no esta capturada.',
            'action' => 'Normalizar motivo de suspension para separar causa interna, paciente y aseguradora.',
        ),
        8 => array(
            'name' => 'Tiempo en recuperacion postquirurgica ambulatoria',
            'original' => 'Promedio de dias de estancia Hospitalaria Postquirurgica por especialidad.',
            'status' => 'not_applicable',
            'formula' => 'No aplica / unidad ambulatoria.',
            'source' => 'No aplica al modelo ambulatorio en dias.',
            'gap' => 'El indicador original mide estancia hospitalaria postquirurgica en dias.',
            'action' => 'Si se requiere seguimiento operativo, controlarlo aparte en horas/minutos.',
        ),
        9 => array(
            'name' => 'Infeccion de sitio quirurgico en cirugia oftalmologica limpia',
            'original' => 'Porcentaje de infeccion de sitio quirurgico con Herida quirurgica limpia.',
            'status' => 'temporary_zero',
            'formula' => '0 temporal en v1 hasta definir infeccion de sitio quirurgico y herida limpia.',
            'source' => 'Sin captura estructurada actual.',
            'gap' => 'No hay registro estructurado de herida limpia e infeccion.',
            'action' => 'Crear campos de complicacion/infeccion para cirugia.',
            'fixed_value' => '0.0%',
            'fixed_month_value' => 0.0,
        ),
        10 => array(
            'name' => 'Complicaciones en procedimientos quirurgicos oftalmologicos por especialidad',
            'original' => 'Porcentaje de casos con complicaciones en procedimientos quirurgicos por especialidad.',
            'status' => 'gap',
            'formula' => 'Procedimientos quirurgicos con complicacion / total de procedimientos quirurgicos * 100.',
            'source' => 'Potencial: surgery_case por especialidad; pendiente campo de complicacion.',
            'gap' => 'Falta registrar complicacion de forma normalizada.',
            'action' => 'Definir catalogo de complicaciones por especialidad.',
        ),
        11 => array(
            'name' => 'Mortalidad hospitalaria posterior a 48 horas',
            'original' => 'Porcentaje de Mortalidad Hospitalaria 48h.',
            'status' => 'temporary_zero',
            'formula' => '0 temporal en v1 por no corresponder al flujo ambulatorio actual.',
            'source' => 'No aplica al flujo observado.',
            'gap' => 'Indicador hospitalario sin uso operativo local.',
            'action' => 'Mantener en 0 mientras se conserve como requisito de plantilla.',
            'fixed_value' => '0.0%',
            'fixed_month_value' => 0.0,
        ),
        12 => array(
            'name' => 'Eventos adversos resueltos',
            'original' => 'Porcentaje de eventos adversos resueltos.',
            'status' => 'gap',
            'formula' => 'Eventos adversos resueltos / eventos adversos notificados * 100.',
            'source' => 'Pendiente: registro estructurado de evento adverso y cierre.',
            'gap' => 'Falta modulo/captura de eventos adversos notificados y resueltos.',
            'action' => 'Definir flujo de notificacion, responsable y cierre.',
        ),
        13 => array(
            'name' => 'Satisfaccion del usuario',
            'original' => 'Porcentaje promedio de satisfaccion del usuario.',
            'status' => 'gap',
            'formula' => 'Promedio de resultado de encuesta de satisfaccion por periodo y segmento.',
            'source' => 'Pendiente: encuesta vinculada a paciente, cita o encuentro.',
            'gap' => 'No hay encuesta estructurada ligada a pricelevel.',
            'action' => 'Crear encuesta o carga mensual con segmento.',
        ),
        14 => array(
            'name' => 'Muerte materna prevenible',
            'original' => 'Porcentaje de muerte materna prevenible.',
            'status' => 'temporary_zero',
            'formula' => '0 temporal en v1 por no corresponder al giro oftalmologico.',
            'source' => 'No aplica al servicio.',
            'gap' => 'Indicador obstetrico.',
            'action' => 'Mantener 0 mientras siga siendo solo una exigencia de plantilla.',
            'fixed_value' => '0.0%',
            'fixed_month_value' => 0.0,
        ),
        15 => array(
            'name' => 'Complicaciones en procedimientos ambulatorios oftalmologicos por especialidad',
            'original' => 'Porcentaje de casos con complicaciones en procedimientos ambulatorios por especialidad.',
            'status' => 'automatic',
            'formula' => 'Casos faco/LIO con diagnostico posterior de afaquia/aphakia / total de casos faco/LIO * 100.',
            'source' => 'openemr_postcalendar_events + form_encounter + issue_encounter + lists.diagnosis/title.',
            'gap' => 'Es un proxy; no reemplaza un registro formal de complicaciones.',
            'action' => 'Validar cobertura del proxy y luego crear captura formal de complicacion.',
        ),
        16 => array(
            'name' => 'Complicaciones post dialisis',
            'original' => 'Porcentaje de casos con complicaciones post dialisis.',
            'status' => 'temporary_zero',
            'formula' => '0 temporal en v1 por no corresponder al servicio oftalmologico.',
            'source' => 'Servicio no ofertado.',
            'gap' => 'Servicio no ofertado.',
            'action' => 'Mantener 0 mientras permanezca en la plantilla general.',
            'fixed_value' => '0.0%',
            'fixed_month_value' => 0.0,
        ),
        17 => array(
            'name' => 'Reintervenciones oftalmologicas',
            'original' => 'Porcentaje de re intervenciones odontologicas.',
            'status' => 'temporary_zero',
            'formula' => '0 temporal en v1 porque la redaccion actual es odontologica y no aplica al alcance operativo.',
            'source' => 'Indicador fuera del dominio oftalmologico en su redaccion actual.',
            'gap' => 'Falta redefinicion oficial de reintervencion oftalmologica.',
            'action' => 'Reformular el indicador antes de intentar automatizarlo.',
            'fixed_value' => '0.0%',
            'fixed_month_value' => 0.0,
        ),
    );
}

function altavision_kpi_status_label($status)
{
    $labels = array(
        'automatic' => 'Automatico v1',
        'gap' => 'Requiere captura/normalizacion',
        'temporary_zero' => '0 temporal v1',
        'not_applicable' => 'No aplicable',
    );

    return isset($labels[$status]) ? $labels[$status] : $status;
}

function altavision_kpi_effective_wait_minutes($scheduledDateTime, $arrivedDateTime, $careDateTime)
{
    if (empty($scheduledDateTime) || empty($careDateTime)) {
        return null;
    }

    $scheduledTs = strtotime($scheduledDateTime);
    $careTs = strtotime($careDateTime);
    if ($scheduledTs === false || $careTs === false) {
        return null;
    }

    $effectiveStartTs = $scheduledTs;
    if (!empty($arrivedDateTime)) {
        $arrivedTs = strtotime($arrivedDateTime);
        if ($arrivedTs !== false && $arrivedTs > $effectiveStartTs) {
            $effectiveStartTs = $arrivedTs;
        }
    }

    return max(0, (int) round(($careTs - $effectiveStartTs) / 60));
}

function altavision_kpi_format_minutes($value, $count)
{
    if ((int) $count <= 0 || $value === null || $value === '') {
        return 'Sin datos';
    }

    return number_format((float) $value, 1) . ' min';
}

function altavision_kpi_format_percent($numerator, $denominator)
{
    if ((int) $denominator <= 0) {
        return 'Sin datos';
    }

    return number_format(((float) $numerator / (float) $denominator) * 100, 1) . '%';
}

function altavision_kpi_format_hours($value, $count)
{
    if ((int) $count <= 0 || $value === null || $value === '') {
        return 'Sin datos';
    }

    return number_format((float) $value, 1) . ' h';
}

function altavision_kpi_value_for_segment($indicatorId, $segmentMetrics)
{
    $definitions = altavision_kpi_indicator_definitions();

    if (isset($definitions[$indicatorId]) && $definitions[$indicatorId]['status'] === 'not_applicable') {
        return 'No aplica';
    }

    if (isset($definitions[$indicatorId]['fixed_value'])) {
        return $definitions[$indicatorId]['fixed_value'];
    }

    if ($indicatorId === 1 || $indicatorId === 8) {
        return altavision_kpi_format_minutes(
            isset($segmentMetrics['observation_avg_minutes']) ? $segmentMetrics['observation_avg_minutes'] : null,
            isset($segmentMetrics['observation_cases']) ? $segmentMetrics['observation_cases'] : 0
        );
    }

    if ($indicatorId === 2) {
        return altavision_kpi_format_minutes(
            isset($segmentMetrics['avg_wait_minutes']) ? $segmentMetrics['avg_wait_minutes'] : null,
            isset($segmentMetrics['wait_count']) ? $segmentMetrics['wait_count'] : 0
        );
    }

    if ($indicatorId === 4) {
        return altavision_kpi_format_percent(
            isset($segmentMetrics['repeat_referral_returns']) ? $segmentMetrics['repeat_referral_returns'] : 0,
            isset($segmentMetrics['referral_patients']) ? $segmentMetrics['referral_patients'] : 0
        );
    }

    if ($indicatorId === 7) {
        return altavision_kpi_format_percent(
            isset($segmentMetrics['surgery_suspended']) ? $segmentMetrics['surgery_suspended'] : 0,
            isset($segmentMetrics['surgery_scheduled']) ? $segmentMetrics['surgery_scheduled'] : 0
        );
    }

    if ($indicatorId === 15) {
        return altavision_kpi_format_percent(
            isset($segmentMetrics['complication_proxy_cases']) ? $segmentMetrics['complication_proxy_cases'] : 0,
            isset($segmentMetrics['faco_lio_cases']) ? $segmentMetrics['faco_lio_cases'] : 0
        );
    }

    return 'Pendiente';
}

function altavision_kpi_month_value_for_segment($indicatorId, $segmentMetrics, $monthNumber)
{
    $definitions = altavision_kpi_indicator_definitions();
    $monthly = isset($segmentMetrics['monthly'][$monthNumber]) ? $segmentMetrics['monthly'][$monthNumber] : null;
    if (empty($monthly)) {
        return 'N/A';
    }

    if (isset($definitions[$indicatorId]) && $definitions[$indicatorId]['status'] === 'not_applicable') {
        return 'N/A';
    }

    if (isset($definitions[$indicatorId]['fixed_month_value'])) {
        return $definitions[$indicatorId]['fixed_month_value'];
    }

    if ($indicatorId === 1 || $indicatorId === 8) {
        if ((int) $monthly['observation_cases'] <= 0 || $monthly['observation_avg_minutes'] === null) {
            return 'N/A';
        }

        return round((float) $monthly['observation_avg_minutes'], 1);
    }

    if ($indicatorId === 2) {
        if ((int) $monthly['wait_count'] <= 0 || $monthly['avg_wait_minutes'] === null) {
            return 'N/A';
        }

        return round((float) $monthly['avg_wait_minutes'], 1);
    }

    if ($indicatorId === 4) {
        if ((int) $monthly['referral_patients'] <= 0) {
            return 'N/A';
        }

        return round(((float) $monthly['repeat_referral_returns'] / (float) $monthly['referral_patients']) * 100, 1);
    }

    if ($indicatorId === 7) {
        if ((int) $monthly['surgery_scheduled'] <= 0) {
            return 'N/A';
        }

        return round(((float) $monthly['surgery_suspended'] / (float) $monthly['surgery_scheduled']) * 100, 1);
    }

    if ($indicatorId === 15) {
        if ((int) $monthly['faco_lio_cases'] <= 0) {
            return 'N/A';
        }

        return round(((float) $monthly['complication_proxy_cases'] / (float) $monthly['faco_lio_cases']) * 100, 1);
    }

    return 'N/A';
}

function altavision_kpi_build_indicator_rows($metricsBySegment)
{
    $rows = array();

    foreach (altavision_kpi_indicator_definitions() as $id => $definition) {
        $rows[] = array(
            'id' => $id,
            'name' => $definition['name'],
            'original' => $definition['original'],
            'status' => $definition['status'],
            'status_label' => altavision_kpi_status_label($definition['status']),
            'formula' => $definition['formula'],
            'source' => $definition['source'],
            'gap' => $definition['gap'],
            'action' => $definition['action'],
            'iess_value' => altavision_kpi_value_for_segment($id, isset($metricsBySegment['iess']) ? $metricsBySegment['iess'] : array()),
            'resto_value' => altavision_kpi_value_for_segment($id, isset($metricsBySegment['resto']) ? $metricsBySegment['resto'] : array()),
            'total_value' => altavision_kpi_value_for_segment($id, isset($metricsBySegment['todos']) ? $metricsBySegment['todos'] : array()),
            'monthly_values' => array(
                'iess' => array(),
                'resto' => array(),
                'todos' => array(),
            ),
        );

        foreach (array_keys(altavision_kpi_month_labels()) as $monthNumber) {
            $rows[count($rows) - 1]['monthly_values']['iess'][$monthNumber] = altavision_kpi_month_value_for_segment($id, isset($metricsBySegment['iess']) ? $metricsBySegment['iess'] : array(), $monthNumber);
            $rows[count($rows) - 1]['monthly_values']['resto'][$monthNumber] = altavision_kpi_month_value_for_segment($id, isset($metricsBySegment['resto']) ? $metricsBySegment['resto'] : array(), $monthNumber);
            $rows[count($rows) - 1]['monthly_values']['todos'][$monthNumber] = altavision_kpi_month_value_for_segment($id, isset($metricsBySegment['todos']) ? $metricsBySegment['todos'] : array(), $monthNumber);
        }
    }

    return $rows;
}

function altavision_kpi_build_summary($rows)
{
    $summary = array(
        'total_indicators' => count($rows),
        'automatic' => 0,
        'gap' => 0,
        'temporary_zero' => 0,
        'not_applicable' => 0,
    );

    foreach ($rows as $row) {
        if (isset($summary[$row['status']])) {
            ++$summary[$row['status']];
        }
    }

    return $summary;
}

function altavision_kpi_has_table($tableName)
{
    $row = sqlQuery(
        "SELECT COUNT(*) AS found_count
           FROM information_schema.tables
          WHERE table_schema = DATABASE()
            AND table_name = ?",
        array($tableName)
    );

    return !empty($row) && (int) $row['found_count'] > 0;
}

function altavision_kpi_surgery_case_sql($eventAlias, $categoryAlias)
{
    return "(" .
        "COALESCE(" . $eventAlias . ".pc_apptqx, '') <> '' " .
        "OR COALESCE(" . $eventAlias . ".pc_apptqxOI, '') <> '' " .
        "OR LOWER(COALESCE(" . $categoryAlias . ".pc_catname, '')) LIKE '%cirug%' " .
        "OR LOWER(COALESCE(" . $eventAlias . ".pc_title, '')) LIKE '%cirug%' " .
        "OR LOWER(COALESCE(" . $eventAlias . ".pc_hometext, '')) LIKE '%cirug%'" .
        ")";
}

function altavision_kpi_tracker_summary_sql()
{
    return "SELECT
                t.eid,
                MIN(CASE
                    WHEN COALESCE(lo.toggle_setting_1, 0) = 1
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%arrived%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%llegad%'
                    THEN q.start_datetime
                END) AS arrived_at,
                MIN(CASE
                    WHEN LOWER(COALESCE(lo.title, '')) LIKE '%quirof%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%quir\xf3f%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%sala de cirug%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%operat%'
                    THEN q.start_datetime
                END) AS or_at,
                MIN(CASE
                    WHEN q.status = '<'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%exam room%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%atendid%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%atencion%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%consulta%'
                    THEN q.start_datetime
                END) AS care_at,
                MAX(CASE
                    WHEN LOWER(COALESCE(lo.title, '')) LIKE '%atendid%'
                      OR LOWER(COALESCE(lo.title, '')) LIKE '%atencion%'
                      OR COALESCE(lo.toggle_setting_2, 0) = 1
                    THEN q.start_datetime
                END) AS attended_at,
                MAX(q.start_datetime) AS last_seen_at
            FROM patient_tracker t
            JOIN patient_tracker_element q ON q.pt_tracker_id = t.id
       LEFT JOIN list_options lo
              ON lo.list_id = 'apptstat'
             AND lo.option_id = q.status
             AND lo.activity = 1
           WHERE q.start_datetime IS NOT NULL
           GROUP BY t.eid";
}

function altavision_kpi_fetch_segment_metrics($fromDate, $toDate, $segment)
{
    $filter = altavision_kpi_segment_filter($segment);
    $metrics = array(
        'observation_avg_minutes' => null,
        'observation_cases' => 0,
        'avg_wait_minutes' => null,
        'wait_count' => 0,
        'repeat_referral_returns' => 0,
        'referral_patients' => 0,
        'preop_avg_hours' => null,
        'preop_cases' => 0,
        'surgery_scheduled' => 0,
        'surgery_suspended' => 0,
        'complication_proxy_cases' => 0,
        'faco_lio_cases' => 0,
        'monthly' => altavision_kpi_empty_month_metrics(),
    );

    $trackerSummarySql = altavision_kpi_tracker_summary_sql();
    $waitSql = "SELECT
                    AVG(
                        GREATEST(
                            0,
                            TIMESTAMPDIFF(
                                MINUTE,
                                GREATEST(
                                    CONCAT(e.pc_eventDate, ' ', e.pc_startTime),
                                    COALESCE(tracker.arrived_at, CONCAT(e.pc_eventDate, ' ', e.pc_startTime))
                                ),
                                tracker.care_at
                            )
                        )
                    ) AS avg_wait_minutes,
                    COUNT(*) AS wait_count
                FROM openemr_postcalendar_events e
                JOIN patient_data p ON p.pid = e.pc_pid
                JOIN (" . $trackerSummarySql . ") tracker ON tracker.eid = e.pc_eid
                WHERE e.pc_eventDate >= ?
                  AND e.pc_eventDate <= ?
                  AND e.pc_pid IS NOT NULL
                  AND e.pc_pid <> ''
                  AND e.pc_startTime IS NOT NULL
                  AND e.pc_apptstatus NOT IN (?, ?)
                  AND tracker.care_at IS NOT NULL
                  AND " . $filter['sql'];
    $waitRow = sqlQuery($waitSql, array($fromDate, $toDate, 'x', '?'));
    if (!empty($waitRow)) {
        $metrics['avg_wait_minutes'] = $waitRow['avg_wait_minutes'];
        $metrics['wait_count'] = (int) $waitRow['wait_count'];
    }

    $waitMonthlySql = "SELECT
                           MONTH(e.pc_eventDate) AS month_number,
                           AVG(
                               GREATEST(
                                   0,
                                   TIMESTAMPDIFF(
                                       MINUTE,
                                       GREATEST(
                                           CONCAT(e.pc_eventDate, ' ', e.pc_startTime),
                                           COALESCE(tracker.arrived_at, CONCAT(e.pc_eventDate, ' ', e.pc_startTime))
                                       ),
                                       tracker.care_at
                                   )
                               )
                           ) AS avg_wait_minutes,
                           COUNT(*) AS wait_count
                       FROM openemr_postcalendar_events e
                       JOIN patient_data p ON p.pid = e.pc_pid
                       JOIN (" . $trackerSummarySql . ") tracker ON tracker.eid = e.pc_eid
                       WHERE e.pc_eventDate >= ?
                         AND e.pc_eventDate <= ?
                         AND e.pc_pid IS NOT NULL
                         AND e.pc_pid <> ''
                         AND e.pc_startTime IS NOT NULL
                         AND e.pc_apptstatus NOT IN (?, ?)
                         AND tracker.care_at IS NOT NULL
                         AND " . $filter['sql'] . "
                       GROUP BY MONTH(e.pc_eventDate)";
    $waitMonthlyResult = sqlStatement($waitMonthlySql, array($fromDate, $toDate, 'x', '?'));
    while ($waitMonthlyRow = sqlFetchArray($waitMonthlyResult)) {
        $monthNumber = (int) $waitMonthlyRow['month_number'];
        if (!isset($metrics['monthly'][$monthNumber])) {
            continue;
        }

        $metrics['monthly'][$monthNumber]['avg_wait_minutes'] = $waitMonthlyRow['avg_wait_minutes'];
        $metrics['monthly'][$monthNumber]['wait_count'] = (int) $waitMonthlyRow['wait_count'];
    }

    $surgeryFilterSql = altavision_kpi_surgery_case_sql('e', 'c');
    $surgerySql = "SELECT
                      COUNT(*) AS surgery_scheduled,
                      SUM(CASE WHEN e.pc_apptstatus = 'x' THEN 1 ELSE 0 END) AS surgery_suspended
                   FROM openemr_postcalendar_events e
                   JOIN patient_data p ON p.pid = e.pc_pid
              LEFT JOIN openemr_postcalendar_categories c ON c.pc_catid = e.pc_catid
                  WHERE e.pc_eventDate >= ?
                    AND e.pc_eventDate <= ?
                    AND e.pc_pid IS NOT NULL
                    AND e.pc_pid <> ''
                    AND " . $surgeryFilterSql . "
                    AND " . $filter['sql'];
    $surgeryRow = sqlQuery($surgerySql, array($fromDate, $toDate));
    if (!empty($surgeryRow)) {
        $metrics['surgery_scheduled'] = (int) $surgeryRow['surgery_scheduled'];
        $metrics['surgery_suspended'] = (int) $surgeryRow['surgery_suspended'];
    }

    $surgeryMonthlySql = "SELECT
                             MONTH(e.pc_eventDate) AS month_number,
                             COUNT(*) AS surgery_scheduled,
                             SUM(CASE WHEN e.pc_apptstatus = 'x' THEN 1 ELSE 0 END) AS surgery_suspended
                          FROM openemr_postcalendar_events e
                          JOIN patient_data p ON p.pid = e.pc_pid
                     LEFT JOIN openemr_postcalendar_categories c ON c.pc_catid = e.pc_catid
                         WHERE e.pc_eventDate >= ?
                           AND e.pc_eventDate <= ?
                           AND e.pc_pid IS NOT NULL
                           AND e.pc_pid <> ''
                           AND " . $surgeryFilterSql . "
                           AND " . $filter['sql'] . "
                         GROUP BY MONTH(e.pc_eventDate)";
    $surgeryMonthlyResult = sqlStatement($surgeryMonthlySql, array($fromDate, $toDate));
    while ($surgeryMonthlyRow = sqlFetchArray($surgeryMonthlyResult)) {
        $monthNumber = (int) $surgeryMonthlyRow['month_number'];
        if (!isset($metrics['monthly'][$monthNumber])) {
            continue;
        }

        $metrics['monthly'][$monthNumber]['surgery_scheduled'] = (int) $surgeryMonthlyRow['surgery_scheduled'];
        $metrics['monthly'][$monthNumber]['surgery_suspended'] = (int) $surgeryMonthlyRow['surgery_suspended'];
    }

    $observationSql = "SELECT
                          AVG(TIMESTAMPDIFF(MINUTE, tracker.or_at, COALESCE(tracker.attended_at, tracker.last_seen_at)) * 0.15) AS observation_avg_minutes,
                          COUNT(*) AS observation_cases,
                          AVG(TIMESTAMPDIFF(MINUTE, tracker.arrived_at, tracker.or_at) / 60) AS preop_avg_hours,
                          SUM(CASE WHEN tracker.arrived_at IS NOT NULL AND tracker.or_at IS NOT NULL AND tracker.or_at >= tracker.arrived_at THEN 1 ELSE 0 END) AS preop_cases
                       FROM openemr_postcalendar_events e
                       JOIN patient_data p ON p.pid = e.pc_pid
                  LEFT JOIN openemr_postcalendar_categories c ON c.pc_catid = e.pc_catid
                       JOIN (" . $trackerSummarySql . ") tracker ON tracker.eid = e.pc_eid
                      WHERE e.pc_eventDate >= ?
                        AND e.pc_eventDate <= ?
                        AND e.pc_pid IS NOT NULL
                        AND e.pc_pid <> ''
                        AND " . $surgeryFilterSql . "
                        AND tracker.or_at IS NOT NULL
                        AND COALESCE(tracker.attended_at, tracker.last_seen_at) IS NOT NULL
                        AND COALESCE(tracker.attended_at, tracker.last_seen_at) >= tracker.or_at
                        AND " . $filter['sql'];
    $observationRow = sqlQuery($observationSql, array($fromDate, $toDate));
    if (!empty($observationRow)) {
        $metrics['observation_avg_minutes'] = $observationRow['observation_avg_minutes'];
        $metrics['observation_cases'] = (int) $observationRow['observation_cases'];
        $metrics['preop_avg_hours'] = $observationRow['preop_avg_hours'];
        $metrics['preop_cases'] = (int) $observationRow['preop_cases'];
    }

    $observationMonthlySql = "SELECT
                                 MONTH(e.pc_eventDate) AS month_number,
                                 AVG(TIMESTAMPDIFF(MINUTE, tracker.or_at, COALESCE(tracker.attended_at, tracker.last_seen_at)) * 0.15) AS observation_avg_minutes,
                                 COUNT(*) AS observation_cases,
                                 AVG(TIMESTAMPDIFF(MINUTE, tracker.arrived_at, tracker.or_at) / 60) AS preop_avg_hours,
                                 SUM(CASE WHEN tracker.arrived_at IS NOT NULL AND tracker.or_at IS NOT NULL AND tracker.or_at >= tracker.arrived_at THEN 1 ELSE 0 END) AS preop_cases
                              FROM openemr_postcalendar_events e
                              JOIN patient_data p ON p.pid = e.pc_pid
                         LEFT JOIN openemr_postcalendar_categories c ON c.pc_catid = e.pc_catid
                              JOIN (" . $trackerSummarySql . ") tracker ON tracker.eid = e.pc_eid
                             WHERE e.pc_eventDate >= ?
                               AND e.pc_eventDate <= ?
                               AND e.pc_pid IS NOT NULL
                               AND e.pc_pid <> ''
                               AND " . $surgeryFilterSql . "
                               AND tracker.or_at IS NOT NULL
                               AND COALESCE(tracker.attended_at, tracker.last_seen_at) IS NOT NULL
                               AND COALESCE(tracker.attended_at, tracker.last_seen_at) >= tracker.or_at
                               AND " . $filter['sql'] . "
                             GROUP BY MONTH(e.pc_eventDate)";
    $observationMonthlyResult = sqlStatement($observationMonthlySql, array($fromDate, $toDate));
    while ($observationMonthlyRow = sqlFetchArray($observationMonthlyResult)) {
        $monthNumber = (int) $observationMonthlyRow['month_number'];
        if (!isset($metrics['monthly'][$monthNumber])) {
            continue;
        }

        $metrics['monthly'][$monthNumber]['observation_avg_minutes'] = $observationMonthlyRow['observation_avg_minutes'];
        $metrics['monthly'][$monthNumber]['observation_cases'] = (int) $observationMonthlyRow['observation_cases'];
        $metrics['monthly'][$monthNumber]['preop_avg_hours'] = $observationMonthlyRow['preop_avg_hours'];
        $metrics['monthly'][$monthNumber]['preop_cases'] = (int) $observationMonthlyRow['preop_cases'];
    }

    if (altavision_kpi_has_table('transactions') && altavision_kpi_has_table('lbt_data')) {
        $referralBaseSql = "SELECT
                                t.id,
                                t.pid,
                                LOWER(TRIM(MAX(CASE WHEN d.field_id = 'refer_diag' THEN d.field_value END))) AS normalized_diag,
                                DATE(MAX(CASE WHEN d.field_id = 'refer_date' THEN d.field_value END)) AS referral_date
                            FROM transactions t
                            JOIN patient_data p ON p.pid = t.pid
                       LEFT JOIN lbt_data d ON d.form_id = t.id
                           WHERE t.title = 'LBTref'
                             AND " . $filter['sql'] . "
                           GROUP BY t.id, t.pid
                          HAVING normalized_diag <> ''
                             AND referral_date >= ?
                             AND referral_date <= ?";
        $referralSql = "SELECT
                           COUNT(*) AS referral_patients,
                           SUM(CASE
                               WHEN referral_count > 1
                                AND EXISTS (
                                    SELECT 1
                                      FROM form_encounter fe
                                      JOIN issue_encounter ie ON ie.pid = fe.pid AND ie.encounter = fe.encounter
                                      JOIN lists l ON l.id = ie.list_id
                                     WHERE fe.pid = referral_summary.pid
                                       AND DATE(fe.date) >= referral_summary.first_referral_date
                                       AND DATE(fe.date) <= ?
                                       AND LOWER(TRIM(COALESCE(NULLIF(l.diagnosis, ''), NULLIF(l.title, ''), ''))) = referral_summary.normalized_diag
                                )
                               THEN 1 ELSE 0
                           END) AS repeat_referral_returns
                        FROM (
                            SELECT pid, normalized_diag, COUNT(*) AS referral_count, MIN(referral_date) AS first_referral_date
                              FROM (" . $referralBaseSql . ") referral_rows
                             GROUP BY pid, normalized_diag
                        ) referral_summary";
        $referralRow = sqlQuery($referralSql, array($fromDate, $toDate, $toDate));
        if (!empty($referralRow)) {
            $metrics['referral_patients'] = (int) $referralRow['referral_patients'];
            $metrics['repeat_referral_returns'] = (int) $referralRow['repeat_referral_returns'];
        }

        $referralMonthlySql = "SELECT
                                  MONTH(first_referral_date) AS month_number,
                                  COUNT(*) AS referral_patients,
                                  SUM(CASE
                                      WHEN referral_count > 1
                                       AND EXISTS (
                                           SELECT 1
                                             FROM form_encounter fe
                                             JOIN issue_encounter ie ON ie.pid = fe.pid AND ie.encounter = fe.encounter
                                             JOIN lists l ON l.id = ie.list_id
                                            WHERE fe.pid = referral_summary.pid
                                              AND DATE(fe.date) >= referral_summary.first_referral_date
                                              AND DATE(fe.date) <= ?
                                              AND LOWER(TRIM(COALESCE(NULLIF(l.diagnosis, ''), NULLIF(l.title, ''), ''))) = referral_summary.normalized_diag
                                       )
                                      THEN 1 ELSE 0
                                  END) AS repeat_referral_returns
                               FROM (
                                   SELECT pid, normalized_diag, COUNT(*) AS referral_count, MIN(referral_date) AS first_referral_date
                                     FROM (" . $referralBaseSql . ") referral_rows
                                    GROUP BY pid, normalized_diag
                               ) referral_summary
                              GROUP BY MONTH(first_referral_date)";
        $referralMonthlyResult = sqlStatement($referralMonthlySql, array($toDate, $fromDate, $toDate));
        while ($referralMonthlyRow = sqlFetchArray($referralMonthlyResult)) {
            $monthNumber = (int) $referralMonthlyRow['month_number'];
            if (!isset($metrics['monthly'][$monthNumber])) {
                continue;
            }

            $metrics['monthly'][$monthNumber]['referral_patients'] = (int) $referralMonthlyRow['referral_patients'];
            $metrics['monthly'][$monthNumber]['repeat_referral_returns'] = (int) $referralMonthlyRow['repeat_referral_returns'];
        }
    }

    $aphakiaCondition = "(" .
        "LOWER(COALESCE(NULLIF(l.diagnosis, ''), NULLIF(l.title, ''), '')) LIKE '%afaquia%' " .
        "OR LOWER(COALESCE(NULLIF(l.diagnosis, ''), NULLIF(l.title, ''), '')) LIKE '%aphakia%' " .
        "OR LOWER(COALESCE(NULLIF(l.diagnosis, ''), NULLIF(l.title, ''), '')) LIKE '%h27%'" .
        ")";
    $facoLioSql = "SELECT
                      COUNT(*) AS faco_lio_cases,
                      SUM(CASE
                          WHEN EXISTS (
                              SELECT 1
                                FROM form_encounter fe
                                JOIN issue_encounter ie ON ie.pid = fe.pid AND ie.encounter = fe.encounter
                                JOIN lists l ON l.id = ie.list_id
                               WHERE fe.pid = e.pc_pid
                                 AND DATE(fe.date) >= e.pc_eventDate
                                 AND " . $aphakiaCondition . "
                          )
                          THEN 1 ELSE 0
                      END) AS complication_proxy_cases
                   FROM openemr_postcalendar_events e
                   JOIN patient_data p ON p.pid = e.pc_pid
              LEFT JOIN openemr_postcalendar_categories c ON c.pc_catid = e.pc_catid
                  WHERE e.pc_eventDate >= ?
                    AND e.pc_eventDate <= ?
                    AND e.pc_pid IS NOT NULL
                    AND e.pc_pid <> ''
                    AND (
                        LOWER(COALESCE(e.pc_apptqx, '')) LIKE '%faco%'
                        OR LOWER(COALESCE(e.pc_apptqxOI, '')) LIKE '%faco%'
                        OR LOWER(COALESCE(e.pc_apptqx, '')) LIKE '%lio%'
                        OR LOWER(COALESCE(e.pc_apptqxOI, '')) LIKE '%lio%'
                    )
                    AND " . $filter['sql'];
    $facoLioRow = sqlQuery($facoLioSql, array($fromDate, $toDate));
    if (!empty($facoLioRow)) {
        $metrics['faco_lio_cases'] = (int) $facoLioRow['faco_lio_cases'];
        $metrics['complication_proxy_cases'] = (int) $facoLioRow['complication_proxy_cases'];
    }

    $facoLioMonthlySql = "SELECT
                             MONTH(e.pc_eventDate) AS month_number,
                             COUNT(*) AS faco_lio_cases,
                             SUM(CASE
                                 WHEN EXISTS (
                                     SELECT 1
                                       FROM form_encounter fe
                                       JOIN issue_encounter ie ON ie.pid = fe.pid AND ie.encounter = fe.encounter
                                       JOIN lists l ON l.id = ie.list_id
                                      WHERE fe.pid = e.pc_pid
                                        AND DATE(fe.date) >= e.pc_eventDate
                                        AND " . $aphakiaCondition . "
                                 )
                                 THEN 1 ELSE 0
                             END) AS complication_proxy_cases
                          FROM openemr_postcalendar_events e
                          JOIN patient_data p ON p.pid = e.pc_pid
                     LEFT JOIN openemr_postcalendar_categories c ON c.pc_catid = e.pc_catid
                         WHERE e.pc_eventDate >= ?
                           AND e.pc_eventDate <= ?
                           AND e.pc_pid IS NOT NULL
                           AND e.pc_pid <> ''
                           AND (
                               LOWER(COALESCE(e.pc_apptqx, '')) LIKE '%faco%'
                               OR LOWER(COALESCE(e.pc_apptqxOI, '')) LIKE '%faco%'
                               OR LOWER(COALESCE(e.pc_apptqx, '')) LIKE '%lio%'
                               OR LOWER(COALESCE(e.pc_apptqxOI, '')) LIKE '%lio%'
                           )
                           AND " . $filter['sql'] . "
                         GROUP BY MONTH(e.pc_eventDate)";
    $facoLioMonthlyResult = sqlStatement($facoLioMonthlySql, array($fromDate, $toDate));
    while ($facoLioMonthlyRow = sqlFetchArray($facoLioMonthlyResult)) {
        $monthNumber = (int) $facoLioMonthlyRow['month_number'];
        if (!isset($metrics['monthly'][$monthNumber])) {
            continue;
        }

        $metrics['monthly'][$monthNumber]['faco_lio_cases'] = (int) $facoLioMonthlyRow['faco_lio_cases'];
        $metrics['monthly'][$monthNumber]['complication_proxy_cases'] = (int) $facoLioMonthlyRow['complication_proxy_cases'];
    }

    return $metrics;
}

function altavision_kpi_fetch_all_metrics($fromDate, $toDate)
{
    return array(
        'iess' => altavision_kpi_fetch_segment_metrics($fromDate, $toDate, 'iess'),
        'resto' => altavision_kpi_fetch_segment_metrics($fromDate, $toDate, 'resto'),
        'todos' => altavision_kpi_fetch_segment_metrics($fromDate, $toDate, 'todos'),
    );
}

function altavision_kpi_build_workbook($rows, $summary, $period)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    altavision_kpi_add_template_sheet($spreadsheet, 'IESS', $rows, 'iess', $period);
    altavision_kpi_add_template_sheet($spreadsheet, 'Resto', $rows, 'resto', $period);
    altavision_kpi_add_template_sheet($spreadsheet, 'Todos', $rows, 'todos', $period);
    altavision_kpi_add_summary_sheet($spreadsheet, $summary, $period);
    altavision_kpi_add_gaps_sheet($spreadsheet, $rows);

    $spreadsheet->setActiveSheetIndex(0);
    return $spreadsheet;
}

function altavision_kpi_apply_header_style($sheet, $range)
{
    $sheet->getStyle($range)->applyFromArray(array(
        'font' => array('bold' => true),
        'fill' => array(
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => array('argb' => 'FFD9E2F3'),
        ),
        'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER),
        'borders' => array(
            'allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => array('argb' => 'FF7F7F7F')),
        ),
    ));
}

function altavision_kpi_autosize($sheet, $lastColumn)
{
    foreach (range('A', $lastColumn) as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
}

function altavision_kpi_add_summary_sheet($spreadsheet, $summary, $period)
{
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Resumen');
    $sheet->setCellValue('A1', 'Indicadores 360 Oftalmologicos');
    $sheet->setCellValue('A2', 'Periodo');
    $sheet->setCellValue('B2', $period['from'] . ' a ' . $period['to']);
    $sheet->fromArray(array('Categoria', 'Cantidad'), null, 'A4');
    $sheet->fromArray(array(
        array('Total indicadores', $summary['total_indicators']),
        array('Automaticos v1', $summary['automatic']),
        array('Requieren captura/normalizacion', $summary['gap']),
        array('0 temporal v1', isset($summary['temporary_zero']) ? $summary['temporary_zero'] : 0),
        array('No aplicables', $summary['not_applicable']),
    ), null, 'A5');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    altavision_kpi_apply_header_style($sheet, 'A4:B4');
    altavision_kpi_autosize($sheet, 'B');
}

function altavision_kpi_template_month_value($row, $segmentKey, $monthNumber, $period)
{
    $year = (int) substr($period['from'], 0, 4);
    $monthDate = sprintf('%04d-%02d-01', $year, $monthNumber);
    if ($monthDate < substr($period['from'], 0, 7) . '-01' || $monthDate > date('Y-m-t', strtotime($period['to']))) {
        return null;
    }

    return $row['monthly_values'][$segmentKey][$monthNumber];
}

function altavision_kpi_add_template_sheet($spreadsheet, $title, $rows, $segmentKey, $period)
{
    $sheet = $title === 'IESS' ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
    $sheet->setTitle($title);

    $year = substr($period['from'], 0, 4);
    $sheet->mergeCells('A1:O3');
    $sheet->setCellValue('A1', '                                REGISTRO DE INDICADORES DE GESTION DE CALIDAD ' . $year . ' - ' . $title);
    $sheet->fromArray(array('N.-', 'INDICADOR ', 'FORMULA', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'), null, 'A4');

    $rowNumber = 5;
    foreach ($rows as $row) {
        $sheet->setCellValue('A' . $rowNumber, $row['id']);
        $sheet->setCellValue('B' . $rowNumber, $row['original']);
        $sheet->setCellValue('C' . $rowNumber, $row['formula']);
        foreach (array_keys(altavision_kpi_month_labels()) as $monthNumber) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($monthNumber + 3);
            $value = altavision_kpi_template_month_value($row, $segmentKey, $monthNumber, $period);
            $sheet->setCellValue($column . $rowNumber, $value);
        }
        ++$rowNumber;
    }

    $sheet->setCellValue('C23', 'SEGMENTO: ' . $title);
    $sheet->setCellValue('B25', 'ELABORADO POR: ');

    $sheet->getStyle('A1:O25')->getAlignment()->setWrapText(true);
    $sheet->getStyle('A1:O25')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A4:O4')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A4:O21')->applyFromArray(array(
        'borders' => array(
            'allBorders' => array(
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => array('argb' => 'FF7F7F7F'),
            ),
        ),
    ));

    $sheet->getColumnDimension('A')->setWidth(3);
    $sheet->getColumnDimension('B')->setWidth(31);
    $sheet->getColumnDimension('C')->setWidth(33);
    foreach (range('D', 'O') as $columnLetter) {
        $sheet->getColumnDimension($columnLetter)->setWidth(5.3);
    }

    $rowHeights = array(
        3 => 42.0,
        5 => 76.5,
        6 => 54.75,
        7 => 51.75,
        8 => 63.75,
        9 => 53.25,
        10 => 57.75,
        11 => 65.25,
        12 => 66.75,
        13 => 54.0,
        14 => 77.25,
        15 => 51.0,
        16 => 51.0,
        17 => 38.25,
        18 => 51.0,
        19 => 76.5,
        20 => 51.0,
        21 => 66.75,
        23 => 19.5,
        25 => 33.0,
    );
    foreach ($rowHeights as $rowIndex => $height) {
        $sheet->getRowDimension($rowIndex)->setRowHeight($height);
    }

    $sheet->freezePane('D5');
}

function altavision_kpi_add_gaps_sheet($spreadsheet, $rows)
{
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Brechas');
    $headers = array('N', 'Indicador original', 'Indicador adaptado', 'Estado', 'Brecha', 'Siguiente accion');
    $sheet->fromArray($headers, null, 'A1');

    $rowNumber = 2;
    foreach ($rows as $row) {
        if ($row['status'] === 'automatic') {
            continue;
        }

        $sheet->fromArray(array(
            $row['id'],
            $row['original'],
            $row['name'],
            $row['status_label'],
            $row['gap'],
            $row['action'],
        ), null, 'A' . $rowNumber);
        ++$rowNumber;
    }

    altavision_kpi_apply_header_style($sheet, 'A1:F1');
    $sheet->freezePane('A2');
    $sheet->getStyle('A1:F' . max(1, $rowNumber - 1))->getAlignment()->setWrapText(true);
    altavision_kpi_autosize($sheet, 'F');
}
