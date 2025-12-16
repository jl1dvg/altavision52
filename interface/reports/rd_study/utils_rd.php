<?php
// rd_study/utils_rd.php

function fetchData($pid, $formdir, $fieldIdsToPrint)
{
    $query = sqlStatement("SELECT f.form_id, f.encounter, f.date, l.field_id, l.field_value
                        FROM forms AS f
                        LEFT JOIN lbf_data AS l ON f.form_id = l.form_id
                       WHERE f.pid = ? AND f.formdir = ? AND f.deleted = 0", [$pid, $formdir]);

    if ($query) {
        $firstEncounter = null;
        $firstEncounterData = [];

        foreach ($query as $linea) {
            if ($firstEncounter === null) {
                $firstEncounter = $linea['encounter'];
            }

            if ($linea['encounter'] === $firstEncounter) {
                $form_id = $linea['form_id'];
                $field_id = $linea['field_id'];
                $field_value = $linea['field_value'];

                if (!isset($firstEncounterData[$form_id])) {
                    $firstEncounterData[$form_id] = [];
                }
                $firstEncounterData[$form_id][$field_id] = $field_value;
            }
        }

        $output = '';
        foreach ($firstEncounterData as $formData) {
            foreach ($formData as $field_id => $field_value) {
                if (in_array($field_id, $fieldIdsToPrint)) {
                    $output .= $field_value;
                }
            }
        }
        return $output;
    } else {
        return "";
    }
}

function determinarTipoRD($pid)
{
    // Helper para buscar sinónimos en un texto
    $sinonimos = [
        'proliferativa' => ['proliferativa', 'proliferativo'],
        'no proliferativa' => ['no proliferativa', 'no-proliferativa', 'no proliferativo', 'no-proliferativo'],
        'isquemia' => ['isquemia', 'isquémica'],
        'edema' => ['edema'],
        'difuso' => ['difuso'],
        'quistico' => ['quistico', 'quístico'],
        'moderada' => ['moderada', 'moderado'],
        'severa' => ['severa', 'severo', 'grave'],
        'leve' => ['leve'],
    ];
    $contiene = function ($texto, $grupo) use ($sinonimos) {
        foreach ($sinonimos[$grupo] as $palabra) {
            if (stripos($texto, $palabra) !== false) {
                return true;
            }
        }
        return false;
    };
    // AF (LBFaf) campos
    $af_od = strtolower(text(fetchData($pid, 'LBFaf', ['Angio_OD'])));
    $af_oi = strtolower(text(fetchData($pid, 'LBFaf', ['Angio_OI'])));
    // OCT (LBFoct_mac) campos
    $oct_od = strtolower(text(fetchData($pid, 'LBFoct_mac', ['OCT_od'])));
    $oct_oi = strtolower(text(fetchData($pid, 'LBFoct_mac', ['OCT_oi'])));
    // TM mácula
    $raw_tm_macula_od = text(fetchData($pid, 'LBFoct_mac', ['TM_macula']));
    $raw_tm_macula_oi = text(fetchData($pid, 'LBFoct_mac', ['TM_macula_OI']));
    $tm_macula_od = '';
    $tm_macula_oi = '';
    if (preg_match('/\d+/', $raw_tm_macula_od, $matches)) $tm_macula_od = intval($matches[0]);
    if (preg_match('/\d+/', $raw_tm_macula_oi, $matches)) $tm_macula_oi = intval($matches[0]);

    $fondo_od = strtolower(text(fetchData($pid, 'form_eye_postseg', ['ODMACULA'])));
    $fondo_oi = strtolower(text(fetchData($pid, 'form_eye_postseg', ['OSMACULA'])));
    $fondo_text = $fondo_od . ' ' . $fondo_oi;

    // --- Procesamiento de vítreo ---
    $vitreo_od = strtolower(text(fetchData($pid, 'form_eye_postseg', ['ODVITREOUS'])));
    $vitreo_oi = strtolower(text(fetchData($pid, 'form_eye_postseg', ['OSVITREOUS'])));
    $vitreo_vars_od = extraerVariablesVitreous($vitreo_od);
    $vitreo_vars_oi = extraerVariablesVitreous($vitreo_oi);

    // --- Procesamiento de periferia ---
    $periph_od = strtolower(text(fetchData($pid, 'form_eye_postseg', ['ODPERIPH'])));
    $periph_oi = strtolower(text(fetchData($pid, 'form_eye_postseg', ['OSPERIPH'])));
    $periph_vars_od = extraerVariablesPeriph($periph_od);
    $periph_vars_oi = extraerVariablesPeriph($periph_oi);

    // Datos binarios ya procesados del fondo de ojo
    $macula_vars = [
        'edema_macular_od' => intval(fetchData($pid, 'form_eye_postseg', ['Edema macular OD'])) === 1,
        'edema_macular_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Edema macular OI'])) === 1,
        'exudados_od' => intval(fetchData($pid, 'form_eye_postseg', ['Exudados mácula OD'])) === 1,
        'exudados_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Exudados mácula OI'])) === 1,
        'hemorragia_od' => intval(fetchData($pid, 'form_eye_postseg', ['Hemorragia mácula OD'])) === 1,
        'hemorragia_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Hemorragia mácula OI'])) === 1,
        'fibrosis_od' => intval(fetchData($pid, 'form_eye_postseg', ['Fibrosis mácula OD'])) === 1,
        'fibrosis_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Fibrosis mácula OI'])) === 1,
    ];

    $vessels_vars = [
        'proliferativa_od' => intval(fetchData($pid, 'form_eye_postseg', ['Proliferativa OD'])) === 1,
        'proliferativa_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Proliferativa OI'])) === 1,
        'no_proliferativa_od' => intval(fetchData($pid, 'form_eye_postseg', ['Retinopatía no diabética OD'])) === 1,
        'no_proliferativa_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Retinopatía no diabética OI'])) === 1,
        'severa_od' => intval(fetchData($pid, 'form_eye_postseg', ['Severa OD'])) === 1,
        'severa_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Severa OI'])) === 1,
        'moderada_od' => intval(fetchData($pid, 'form_eye_postseg', ['Moderada OD'])) === 1,
        'moderada_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Moderada OI'])) === 1,
        'leve_od' => intval(fetchData($pid, 'form_eye_postseg', ['Leve OD'])) === 1,
        'leve_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Leve OI'])) === 1,
        'neovascularizacion_od' => intval(fetchData($pid, 'form_eye_postseg', ['Neovascularización OD'])) === 1,
        'neovascularizacion_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Neovascularización OI'])) === 1,
        'microaneurismas_od' => intval(fetchData($pid, 'form_eye_postseg', ['Microaneurismas OD'])) === 1,
        'microaneurismas_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Microaneurismas OI'])) === 1,
        'hemorragias_od' => intval(fetchData($pid, 'form_eye_postseg', ['Hemorragias vasos OD'])) === 1,
        'hemorragias_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Hemorragias vasos OI'])) === 1,
        'exudados_od' => intval(fetchData($pid, 'form_eye_postseg', ['Exudados vasos OD'])) === 1,
        'exudados_oi' => intval(fetchData($pid, 'form_eye_postseg', ['Exudados vasos OI'])) === 1,
    ];

    // INTEGRACIÓN: concatenar todos los textos para búsqueda global
    $alltext = $af_od . ' ' . $af_oi . ' ' . $oct_od . ' ' . $oct_oi . ' ' . $fondo_text;

    // --- Asignación de nivel ETDRS ---
    $nivel_etdrs = 10; // Por defecto: sin RD

    if ($vessels_vars['microaneurismas_od'] || $vessels_vars['microaneurismas_oi']) {
        $nivel_etdrs = 20;
    }

    if (
        ($macula_vars['exudados_od'] || $macula_vars['exudados_oi']) &&
        ($macula_vars['hemorragia_od'] || $macula_vars['hemorragia_oi'])
    ) {
        $nivel_etdrs = 35;
    }

    if ($vessels_vars['moderada_od'] || $vessels_vars['moderada_oi']) {
        $nivel_etdrs = 43;
    }

    if ($vessels_vars['severa_od'] || $vessels_vars['severa_oi']) {
        $nivel_etdrs = 53;
    }

    if (
        $vessels_vars['proliferativa_od'] || $vessels_vars['proliferativa_oi'] ||
        $vitreo_vars_od['hemorragia_vitreana'] || $vitreo_vars_oi['hemorragia_vitreana']
    ) {
        $nivel_etdrs = 61;
    }

    if (
        $vitreo_vars_od['aceite_silicon'] || $vitreo_vars_oi['aceite_silicon'] ||
        $vitreo_vars_od['desprendimiento_vitreos'] || $vitreo_vars_oi['desprendimiento_vitreos']
    ) {
        $nivel_etdrs = 85;
    }

    // 1. Proliferativa por hallazgos en vessels
    if (
        $vessels_vars['proliferativa_od'] || $vessels_vars['proliferativa_oi'] ||
        $vessels_vars['neovascularizacion_od'] || $vessels_vars['neovascularizacion_oi']
    ) {
        return ['clasificacion' => 'PDR (Proliferativa)', 'etdrs_nivel' => $nivel_etdrs];
    }

    // 2. NPDR Severa: hemorragias + exudados en ambos ojos, o severa marcada
    if (
        ($vessels_vars['hemorragias_od'] && $vessels_vars['hemorragias_oi']) &&
        ($vessels_vars['exudados_od'] && $vessels_vars['exudados_oi'])
    ) {
        return ['clasificacion' => 'NPDR Severa (Hallazgos combinados)', 'etdrs_nivel' => $nivel_etdrs];
    }

    if ($vessels_vars['severa_od'] || $vessels_vars['severa_oi']) {
        return ['clasificacion' => 'NPDR Severa', 'etdrs_nivel' => $nivel_etdrs];
    }

    // 3. NPDR Moderada: hemorragias o exudados unilaterales, o marcada como moderada
    if (
        $vessels_vars['moderada_od'] || $vessels_vars['moderada_oi'] ||
        ($vessels_vars['hemorragias_od'] || $vessels_vars['hemorragias_oi']) ||
        ($vessels_vars['exudados_od'] || $vessels_vars['exudados_oi'])
    ) {
        return ['clasificacion' => 'NPDR Moderada', 'etdrs_nivel' => $nivel_etdrs];
    }

    // 4. NPDR Leve: sólo microaneurismas sin hemorragias ni exudados
    if (
        ($vessels_vars['microaneurismas_od'] || $vessels_vars['microaneurismas_oi']) &&
        !$vessels_vars['hemorragias_od'] && !$vessels_vars['hemorragias_oi'] &&
        !$vessels_vars['exudados_od'] && !$vessels_vars['exudados_oi']
    ) {
        return ['clasificacion' => 'NPDR Muy Leve (Nivel 20)', 'etdrs_nivel' => $nivel_etdrs];
    }
    // 1. PDR (Proliferativa): si en AF hay proliferativa en algún ojo
    if ($contiene($af_od, 'proliferativa') || $contiene($af_oi, 'proliferativa')) {
        return ['clasificacion' => 'PDR (Proliferativa)', 'etdrs_nivel' => $nivel_etdrs];
    }

    // Evaluar usando variables extraídas del estudio (simulación)
    // NOTA: Estas variables deben estar previamente definidas si las vas a usar directamente.
    // Ejemplo para determinar NPDR Leve, Moderada o Severa usando las variables del estudio

    // Pseudocódigo extendido para una lógica más precisa basada en tus variables actuales
    if (
        // Proliferativa por AF
        $contiene($af_od, 'proliferativa') || $contiene($af_oi, 'proliferativa') ||
        ($contiene($af_od, 'diabetica') && $contiene($af_od, 'proliferativa')) ||
        ($contiene($af_oi, 'diabetica') && $contiene($af_oi, 'proliferativa'))
    ) {
        return ['clasificacion' => 'PDR (Proliferativa)', 'etdrs_nivel' => $nivel_etdrs];
    }

    // NPDR Severa si hay isquemia, severa, o combinación
    if (
        $contiene($af_od, 'isquemia') || $contiene($af_oi, 'isquemia') ||
        $contiene($af_od, 'severa') || $contiene($af_oi, 'severa') ||
        ($contiene($af_od, 'no proliferativa') && $contiene($af_od, 'severa')) ||
        ($contiene($af_oi, 'no proliferativa') && $contiene($af_oi, 'severa'))
    ) {
        return ['clasificacion' => 'NPDR Severa (Isquemia)', 'etdrs_nivel' => $nivel_etdrs];
    }

    // RD con Edema Macular según OCT o descripción
    if (
        $contiene($oct_od, 'edema') || $contiene($oct_oi, 'edema') ||
        $contiene($oct_od, 'quistico') || $contiene($oct_oi, 'quistico') ||
        $contiene($fondo_text, 'edema') || $contiene($fondo_text, 'quistico') ||
        ($tm_macula_od !== '' && $tm_macula_od > 300) ||
        ($tm_macula_oi !== '' && $tm_macula_oi > 300)
    ) {
        return ['clasificacion' => 'RD con Edema Macular', 'etdrs_nivel' => $nivel_etdrs];
    }

    // NPDR Moderada
    if (
        $contiene($af_od, 'moderada') || $contiene($af_oi, 'moderada') ||
        ($contiene($af_od, 'no proliferativa') && $contiene($af_od, 'moderada')) ||
        ($contiene($af_oi, 'no proliferativa') && $contiene($af_oi, 'moderada'))
    ) {
        return ['clasificacion' => 'NPDR Moderada', 'etdrs_nivel' => $nivel_etdrs];
    }

    // NPDR Leve
    if (
        $contiene($af_od, 'leve') || $contiene($af_oi, 'leve') ||
        ($contiene($af_od, 'no proliferativa') && $contiene($af_od, 'leve')) ||
        ($contiene($af_oi, 'no proliferativa') && $contiene($af_oi, 'leve'))
    ) {
        return ['clasificacion' => 'NPDR Leve', 'etdrs_nivel' => $nivel_etdrs];
    }

    if ($macula_vars['edema_macular_od'] || $macula_vars['edema_macular_oi']) {
        return ['clasificacion' => 'RD con Edema Macular', 'etdrs_nivel' => $nivel_etdrs];
    }

    if (
        ($macula_vars['fibrosis_od'] || $macula_vars['fibrosis_oi']) ||
        ($macula_vars['hemorragia_od'] || $macula_vars['hemorragia_oi'])
    ) {
        return ['clasificacion' => 'NPDR Severa (Isquemia)', 'etdrs_nivel' => $nivel_etdrs];
    }

    if (
        ($macula_vars['exudados_od'] || $macula_vars['exudados_oi']) &&
        ($macula_vars['hemorragia_od'] || $macula_vars['hemorragia_oi'])
    ) {
        return ['clasificacion' => 'NPDR Leve (Nivel 35)', 'etdrs_nivel' => $nivel_etdrs];
    }

    if ($macula_vars['exudados_od'] || $macula_vars['exudados_oi']) {
        return ['clasificacion' => 'NPDR Muy Leve (Nivel 20)', 'etdrs_nivel' => $nivel_etdrs];
    }
    // Considerar hallazgos severos en vítreo
    if (
        $vitreo_vars_od['hemorragia_vitreana'] || $vitreo_vars_oi['hemorragia_vitreana'] ||
        $vitreo_vars_od['desprendimiento_vitreos'] || $vitreo_vars_oi['desprendimiento_vitreos']
    ) {
        return ['clasificacion' => 'PDR (Proliferativa)', 'etdrs_nivel' => $nivel_etdrs];
    }

    // PDR por hallazgo de aceite de silicón en vítreo (manejo previo)
    if (
        $vitreo_vars_od['aceite_silicon'] || $vitreo_vars_oi['aceite_silicon']
    ) {
        return ['clasificacion' => 'PDR (Manejo previo con aceite de silicón)', 'etdrs_nivel' => $nivel_etdrs];
    }

    // 6. Indeterminado o sin hallazgos críticos
    return ['clasificacion' => 'Indeterminado o sin hallazgos críticos', 'etdrs_nivel' => $nivel_etdrs];
}

function getFilteredPatients($from_date, $to_date, $form_provider, $form_pricelevel)
{
    $sqlArrayBind = [];
    $query = "SELECT
                p.fname, p.mname, p.lname, p.sex, p.city, p.DOB, p.pricelevel,
                p.phone_home, p.phone_biz, p.phone_cell, p.phone_contact, p.pid, p.pubpid,
                COUNT(e.date) AS ecount, MAX(e.date) AS edate, MIN(e.date) AS fdate,
                postseg.id as fo, postseg.ODMACULA, postseg.OSMACULA, postseg.ODVESSELS, postseg.OSVESSELS,
                postseg.ODVITREOUS, postseg.OSVITREOUS, postseg.ODPERIPH, postseg.OSPERIPH
              FROM
                patient_data AS p

               JOIN form_eye_postseg AS postseg ON i.id = postseg.id ";

    // Agregar condiciones a la consulta según los parámetros recibidos
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON e.pid = p.pid AND e.date >= ? AND e.date <= ? ";
        $sqlArrayBind[] = $from_date . ' 00:00:00';
        $sqlArrayBind[] = $to_date . ' 23:59:59';
        if ($form_provider) {
            $query .= "AND e.provider_id = ? ";
            $sqlArrayBind[] = $form_provider;
        }
    } elseif ($form_provider) {
        $query .= "JOIN form_encounter AS e ON e.pid = p.pid AND e.provider_id = ? ";
        $sqlArrayBind[] = $form_provider;
    } else {
        $query .= "LEFT OUTER JOIN form_encounter AS e ON e.pid = p.pid ";
    }

    // Agregar filtro por nivel de precio si está definido
    if ($form_pricelevel) {
        $query .= "AND p.pricelevel = ? ";
        $sqlArrayBind[] = $form_pricelevel;
    }

    $query .= "GROUP BY p.lname, p.fname, p.mname, p.pid ORDER BY p.lname, p.fname, p.mname, p.pid DESC";

    // Ejecutar la consulta SQL y devolver los resultados como array de arrays asociativos
    $res = sqlStatement($query, $sqlArrayBind);
    $resultRows = [];
    while ($row = sqlFetchArray($res)) {
        $resultRows[] = $row;
    }
    return $resultRows;
}
