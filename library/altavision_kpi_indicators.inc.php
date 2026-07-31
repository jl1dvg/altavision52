<?php
/**
 * Altavision ophthalmology KPI helpers.
 *
 * V1 intentionally keeps data capture unchanged. It computes the quick wins
 * that are already represented in OpenEMR and documents the remaining gaps.
 */

function altavision_kpi_workbook_sheet_names()
{
    return array('Resumen', 'IESS', 'Resto', 'Todos', 'Brechas');
}

function altavision_kpi_segments()
{
    return array(
        'iess' => 'IESS',
        'resto' => 'Resto',
        'todos' => 'Todos',
    );
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
            'status' => 'gap',
            'formula' => 'Suma de minutos en observacion o recuperacion / pacientes egresados de observacion o recuperacion.',
            'source' => 'Pendiente: registro estructurado de entrada/salida de observacion.',
            'gap' => 'Crear captura de hora de entrada y salida de recuperacion/observacion ambulatoria.',
            'action' => 'Definir si aplica a todos los procedimientos o solo postquirurgicos.',
        ),
        2 => array(
            'name' => 'Tiempo promedio de espera en consulta externa oftalmologica',
            'original' => 'Promedio del tiempo de espera de la atencion por especialidad en consulta externa.',
            'status' => 'automatic',
            'formula' => 'Promedio de minutos entre hora agendada y primer registro real en patient tracker.',
            'source' => 'openemr_postcalendar_events + patient_tracker + patient_tracker_element + patient_data.pricelevel.',
            'gap' => 'Depende de que el flujo registre la llegada/inicio de atencion en patient tracker.',
            'action' => 'Auditar uso consistente de estados del tracker.',
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
            'status' => 'gap',
            'formula' => 'Retornos no programados por igual CIE-10 / atenciones o procedimientos cerrados.',
            'source' => 'Potencial: form_encounter + billing/lists diagnosticos.',
            'gap' => 'Falta regla de retorno no programado y ventana de dias.',
            'action' => 'Definir ventana clinica: 24h, 72h, 7 dias o 30 dias.',
        ),
        5 => array(
            'name' => 'Infecciones asociadas a atencion/procedimiento oftalmologico',
            'original' => 'Porcentaje de infecciones Asociadas a atencion sanitaria en salud.',
            'status' => 'gap',
            'formula' => 'Casos nuevos de infeccion post atencion/procedimiento / total de atenciones o procedimientos.',
            'source' => 'Pendiente: registro estructurado de infeccion postoperatoria o asociada a atencion.',
            'gap' => 'No hay campo normalizado de infeccion asociada a atencion.',
            'action' => 'Crear clasificacion de infeccion y vincularla al encuentro/procedimiento.',
        ),
        6 => array(
            'name' => 'Estancia prequirurgica hospitalaria',
            'original' => 'Promedio de Dias de estancia hospitalaria pre quirurgica.',
            'status' => 'not_applicable',
            'formula' => 'No aplica a cirugia ambulatoria.',
            'source' => 'No aplica.',
            'gap' => 'Indicador hospitalario.',
            'action' => 'Excluir del tablero ambulatorio.',
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
            'status' => 'gap',
            'formula' => 'Minutos en recuperacion postquirurgica / cirugias realizadas.',
            'source' => 'Pendiente: registro de entrada/salida de recuperacion.',
            'gap' => 'No hay hora estructurada de salida de recuperacion.',
            'action' => 'Definir captura postquirurgica ambulatoria.',
        ),
        9 => array(
            'name' => 'Infeccion de sitio quirurgico en cirugia oftalmologica limpia',
            'original' => 'Porcentaje de infeccion de sitio quirurgico con Herida quirurgica limpia.',
            'status' => 'gap',
            'formula' => 'Infecciones de sitio quirurgico en herida limpia / cirugias limpias * 100.',
            'source' => 'Pendiente: clasificacion herida limpia + infeccion postoperatoria.',
            'gap' => 'No hay registro estructurado de herida limpia e infeccion.',
            'action' => 'Crear campos de complicacion/infeccion para cirugia.',
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
            'status' => 'not_applicable',
            'formula' => 'No aplica a unidad ambulatoria.',
            'source' => 'No aplica.',
            'gap' => 'Indicador hospitalario.',
            'action' => 'Excluir del tablero ambulatorio.',
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
            'status' => 'not_applicable',
            'formula' => 'No aplica al giro oftalmologico ambulatorio.',
            'source' => 'No aplica.',
            'gap' => 'Indicador obstetrico.',
            'action' => 'Excluir del tablero ambulatorio.',
        ),
        15 => array(
            'name' => 'Complicaciones en procedimientos ambulatorios oftalmologicos por especialidad',
            'original' => 'Porcentaje de casos con complicaciones en procedimientos ambulatorios por especialidad.',
            'status' => 'gap',
            'formula' => 'Procedimientos ambulatorios con complicacion / total de procedimientos ambulatorios * 100.',
            'source' => 'Potencial: citas/procedimientos + campo futuro de complicacion.',
            'gap' => 'Falta captura normalizada de complicaciones ambulatorias.',
            'action' => 'Definir procedimientos incluidos y catalogo de complicaciones.',
        ),
        16 => array(
            'name' => 'Complicaciones post dialisis',
            'original' => 'Porcentaje de casos con complicaciones post dialisis.',
            'status' => 'not_applicable',
            'formula' => 'No aplica a unidad oftalmologica.',
            'source' => 'No aplica.',
            'gap' => 'Servicio no ofertado.',
            'action' => 'Excluir del tablero ambulatorio.',
        ),
        17 => array(
            'name' => 'Reintervenciones oftalmologicas',
            'original' => 'Porcentaje de re intervenciones odontologicas.',
            'status' => 'gap',
            'formula' => 'Reintervenciones oftalmologicas / intervenciones oftalmologicas realizadas * 100.',
            'source' => 'Potencial: cirugias/procedimientos + regla de reintervencion.',
            'gap' => 'La redaccion original es odontologica; falta definir reintervencion oftalmologica.',
            'action' => 'Reformular oficialmente el indicador y definir ventana temporal.',
        ),
    );
}

function altavision_kpi_status_label($status)
{
    $labels = array(
        'automatic' => 'Automatico v1',
        'gap' => 'Requiere captura/normalizacion',
        'not_applicable' => 'No aplicable',
    );

    return isset($labels[$status]) ? $labels[$status] : $status;
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

function altavision_kpi_value_for_segment($indicatorId, $segmentMetrics)
{
    if ($indicatorId === 2) {
        return altavision_kpi_format_minutes(
            isset($segmentMetrics['avg_wait_minutes']) ? $segmentMetrics['avg_wait_minutes'] : null,
            isset($segmentMetrics['wait_count']) ? $segmentMetrics['wait_count'] : 0
        );
    }

    if ($indicatorId === 7) {
        return altavision_kpi_format_percent(
            isset($segmentMetrics['surgery_suspended']) ? $segmentMetrics['surgery_suspended'] : 0,
            isset($segmentMetrics['surgery_scheduled']) ? $segmentMetrics['surgery_scheduled'] : 0
        );
    }

    $definitions = altavision_kpi_indicator_definitions();
    if (isset($definitions[$indicatorId]) && $definitions[$indicatorId]['status'] === 'not_applicable') {
        return 'No aplica';
    }

    return 'Pendiente';
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
        );
    }

    return $rows;
}

function altavision_kpi_build_summary($rows)
{
    $summary = array(
        'total_indicators' => count($rows),
        'automatic' => 0,
        'gap' => 0,
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

function altavision_kpi_fetch_segment_metrics($fromDate, $toDate, $segment)
{
    $filter = altavision_kpi_segment_filter($segment);
    $metrics = array(
        'avg_wait_minutes' => null,
        'wait_count' => 0,
        'surgery_scheduled' => 0,
        'surgery_suspended' => 0,
    );

    $waitSql = "SELECT
                    AVG(TIMESTAMPDIFF(MINUTE, CONCAT(e.pc_eventDate, ' ', e.pc_startTime), first_tracker.first_seen)) AS avg_wait_minutes,
                    COUNT(*) AS wait_count
                FROM openemr_postcalendar_events e
                JOIN patient_data p ON p.pid = e.pc_pid
                JOIN (
                    SELECT t.eid, MIN(q.start_datetime) AS first_seen
                    FROM patient_tracker t
                    JOIN patient_tracker_element q ON q.pt_tracker_id = t.id
                    WHERE q.start_datetime IS NOT NULL
                    GROUP BY t.eid
                ) first_tracker ON first_tracker.eid = e.pc_eid
                WHERE e.pc_eventDate >= ?
                  AND e.pc_eventDate <= ?
                  AND e.pc_pid IS NOT NULL
                  AND e.pc_pid <> ''
                  AND e.pc_startTime IS NOT NULL
                  AND e.pc_apptstatus NOT IN ('x', '?')
                  AND " . $filter['sql'];
    $waitRow = sqlQuery($waitSql, array($fromDate, $toDate));
    if (!empty($waitRow)) {
        $metrics['avg_wait_minutes'] = $waitRow['avg_wait_minutes'];
        $metrics['wait_count'] = (int) $waitRow['wait_count'];
    }

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
                    AND (
                        COALESCE(e.pc_apptqx, '') <> ''
                        OR COALESCE(e.pc_apptqxOI, '') <> ''
                        OR LOWER(COALESCE(c.pc_catname, '')) LIKE '%cirug%'
                        OR LOWER(COALESCE(e.pc_title, '')) LIKE '%cirug%'
                        OR LOWER(COALESCE(e.pc_hometext, '')) LIKE '%cirug%'
                    )
                    AND " . $filter['sql'];
    $surgeryRow = sqlQuery($surgerySql, array($fromDate, $toDate));
    if (!empty($surgeryRow)) {
        $metrics['surgery_scheduled'] = (int) $surgeryRow['surgery_scheduled'];
        $metrics['surgery_suspended'] = (int) $surgeryRow['surgery_suspended'];
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

    altavision_kpi_add_summary_sheet($spreadsheet, $summary, $period);
    altavision_kpi_add_segment_sheet($spreadsheet, 'IESS', $rows, 'iess_value');
    altavision_kpi_add_segment_sheet($spreadsheet, 'Resto', $rows, 'resto_value');
    altavision_kpi_add_segment_sheet($spreadsheet, 'Todos', $rows, 'total_value');
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
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Resumen');
    $sheet->setCellValue('A1', 'Indicadores 360 Oftalmologicos');
    $sheet->setCellValue('A2', 'Periodo');
    $sheet->setCellValue('B2', $period['from'] . ' a ' . $period['to']);
    $sheet->fromArray(array('Categoria', 'Cantidad'), null, 'A4');
    $sheet->fromArray(array(
        array('Total indicadores', $summary['total_indicators']),
        array('Automaticos v1', $summary['automatic']),
        array('Requieren captura/normalizacion', $summary['gap']),
        array('No aplicables', $summary['not_applicable']),
    ), null, 'A5');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    altavision_kpi_apply_header_style($sheet, 'A4:B4');
    altavision_kpi_autosize($sheet, 'B');
}

function altavision_kpi_add_segment_sheet($spreadsheet, $title, $rows, $valueKey)
{
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($title);
    $headers = array('N', 'Indicador', 'Estado', 'Valor', 'Formula', 'Fuente', 'Limitacion', 'Siguiente accion');
    $sheet->fromArray($headers, null, 'A1');

    $rowNumber = 2;
    foreach ($rows as $row) {
        $sheet->fromArray(array(
            $row['id'],
            $row['name'],
            $row['status_label'],
            $row[$valueKey],
            $row['formula'],
            $row['source'],
            $row['gap'],
            $row['action'],
        ), null, 'A' . $rowNumber);
        ++$rowNumber;
    }

    altavision_kpi_apply_header_style($sheet, 'A1:H1');
    $sheet->freezePane('A2');
    $sheet->getStyle('A1:H' . ($rowNumber - 1))->getAlignment()->setWrapText(true);
    altavision_kpi_autosize($sheet, 'H');
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
