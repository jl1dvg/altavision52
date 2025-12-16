<?php
// rd_study/fundus.php

// Función para normalizar texto
function normalizar($texto)
{
    $texto = strtolower($texto);
    $texto = strtr($texto, 'áéíóúüñ', 'aeiouun');
    return $texto;
}

// Función para extraer variables de la mácula en fondo de ojo
function extraerVariablesMacula($descripcion)
{
    $descripcion = normalizar($descripcion);
    return [
        // Edema
        'edema_macular' => preg_match('/edema|engrosamiento|quistico|cmo|liquido.*retina|liquido.*capas|acumulacion.*liquido|sobreelevada|mancha.*blanca|manchas.*blancas|significativo|macular edema|retinal edema/', $descripcion) ? 1 : 0,
        // Exudados
        'exudados' => preg_match('/exudados?|duros|blandos/', $descripcion) ? 1 : 0,
        // DMAE
        'dmae_humeda' => preg_match('/degeneracion.*humeda|humeda|humed[ao]|exudativa/', $descripcion) ? 1 : 0,
        'dmae_seca' => preg_match('/degeneracion.*seca|seca|drusas/', $descripcion) ? 1 : 0,
        // Brillo
        'brillo_foveal_conservado' => preg_match('/brillo foveal conservado|fovea aplicada|brillante|foveal aplicada|fovea brillante|aplicada|brillante foveal|foveal brillante/', $descripcion) ? 1 : 0,
        // Hemorragia
        'hemorragia_macular' => preg_match('/hemorragia|sangrado|microaneurisma|microhemorragia|hemorragnua/', $descripcion) ? 1 : 0,
        // Fibrosis
        'fibrosis_macular' => preg_match('/fibrosis|cicatriz|fibrotica/', $descripcion) ? 1 : 0,
        // No valorable / opacidad
        'macula_no_valorable' => preg_match('/no valorable|opacidad de medios|no evaluable|no se observa|no visible|opacidad/', $descripcion) ? 1 : 0,
        // Macula normal
        'macula_normal' => preg_match('/normal|sin alteraciones|sin cambios|macula normal|fovea normal/', $descripcion) ? 1 : 0,
    ];
}

// Nueva función para extraer variables de vasos (vessels)
function extraerVariablesVessels($descripcion)
{
    $descripcion = normalizar($descripcion);

    // Primero detectamos "no proliferativa" (evita falsos positivos de "proliferativa")
    $no_proliferativa = preg_match('/no[ -]?proliferativa/', $descripcion) ? 1 : 0;
    $proliferativa = 0;
    if (!$no_proliferativa) {
        // Solo si NO es no proliferativa, busca proliferativa
        $proliferativa = preg_match('/proliferativa/', $descripcion) ? 1 : 0;
    }

    return [
        'retinopatia_diabetica' => preg_match('/retinopatia|retinopatnua|diabetica|diabnotica/', $descripcion) ? 1 : 0,
        // Añadimos la diferencia clara:
        'no_proliferativa' => $no_proliferativa,
        'proliferativa' => $proliferativa,
        'severa' => preg_match('/severa/', $descripcion) ? 1 : 0,
        'moderada' => preg_match('/moderada/', $descripcion) ? 1 : 0,
        'leve' => preg_match('/leve/', $descripcion) ? 1 : 0,
        'calibre_alterado' => preg_match('/adelgazados?|adelgazamiento|calibre|arterias engrosadas|arterias dilatadas|venas tortuosas|venas dilatadas|artereolar|esclerosados|tortuosos/', $descripcion) ? 1 : 0,
        'fotocoagulacion_laser' => preg_match('/panfotocoagulada|fotocoagulada|laser|lneser|quemaduras/', $descripcion) ? 1 : 0,
        'neovascularizacion' => preg_match('/neovascularizacion|neovascularización|neo vasos|neovasos/', $descripcion) ? 1 : 0,
        'microaneurismas' => preg_match('/microaneurisma|microaneurismas/', $descripcion) ? 1 : 0,
        'hemorragias' => preg_match('/hemorragia|hemorragias|hemorragia perivascular|hemorragias perivasculares|hemorragnua/', $descripcion) ? 1 : 0,
        'exudados' => preg_match('/exudado|exudados/', $descripcion) ? 1 : 0,
        'vessels_normal' => preg_match('/normal|vasos normales|sin alteraciones/', $descripcion) ? 1 : 0,
    ];
}

// Nueva función para extraer variables del vítreo
function extraerVariablesVitreous($descripcion)
{
    $descripcion = normalizar($descripcion);
    return [
        'hemorragia_vitreana' => preg_match('/hemorragia vitreana|hemorragia vitrea|hemorragnua|sangre|sangrado/', $descripcion) ? 1 : 0,
        'opacidades_vitreales' => preg_match('/opacidad vitreales|opacidades vitreales|opacidad vitrea|opacidades vitreas|claro|transparente|clara/', $descripcion) ? 1 : 0,
        'desprendimiento_vitreos' => preg_match('/desprendimiento vitreos|desprendimiento vitreo|desprendimiento del vitreos/', $descripcion) ? 1 : 0,
        'hialosis_asteroidea' => preg_match('/asteroidea|hialosis/', $descripcion) ? 1 : 0,
        'aceite_silicon' => preg_match('/silicon|aceite/', $descripcion) ? 1 : 0,
        'vitreo_normal' => preg_match('/vitreo normal|normal|sin alteraciones/', $descripcion) ? 1 : 0,
        'no_valorable' => preg_match('/no valorable|no visible|no evaluable/', $descripcion) ? 1 : 0,
    ];
}

// Nueva función para extraer variables de la periferia
function extraerVariablesPeriph($descripcion)
{
    $descripcion = normalizar($descripcion);
    return [
        'desprendimiento_retina' => preg_match('/desprendimiento retina|desprendimiento retinal|desprendida/', $descripcion) ? 1 : 0,
        'degeneracion_retinal' => preg_match('/degeneracion retinal|degeneracion retiniana/', $descripcion) ? 1 : 0,
        'hemorragias_perifericas' => preg_match('/hemorragia periferica|hemorragias perifericas/', $descripcion) ? 1 : 0,
        'atrofia_retinal' => preg_match('/atrofia retinal|atrofia retiniana/', $descripcion) ? 1 : 0,
        'lesiones_perifericas' => preg_match('/lesion periferica|lesiones perifericas|huellas/', $descripcion) ? 1 : 0,
        'laser_periferico' => preg_match('/laser|lneser|quemaduras|fotocoagulada/', $descripcion) ? 1 : 0,
        'cuadrantes_afectados' => preg_match('/cuadrantes|parcialmente/', $descripcion) ? 1 : 0,
        'periferia_normal' => preg_match('/aplicada|normal|sin alteraciones|periferia aplicada/', $descripcion) ? 1 : 0,
        'no_valorable' => preg_match('/no valorable|no visible|no evaluable|opacidad/', $descripcion) ? 1 : 0,
    ];
}

function render_fundus($pid)
{
    $html = '';
    // Buscar el primer registro de fondo de ojo para el paciente
    $row = sqlQuery("SELECT * FROM form_eye_postseg WHERE pid = ? ORDER BY id ASC LIMIT 1", array($pid));
    $has_fundus = ($row && isset($row['ODMACULA']));

    $macula_od = $has_fundus ? $row['ODMACULA'] : '';
    $macula_oi = $has_fundus ? $row['OSMACULA'] : '';

    $html .= '<td class="fondo-ojo">' . ($has_fundus ? '1' : '0') . '</td>';

    $variables_od = extraerVariablesMacula($macula_od);
    $variables_oi = extraerVariablesMacula($macula_oi);

    $campos = [
        'edema_macular', 'exudados', 'dmae_humeda', 'dmae_seca', 'brillo_foveal_conservado',
        'hemorragia_macular', 'fibrosis_macular', 'macula_no_valorable', 'macula_normal'
    ];
    foreach ($campos as $campo) {
        $html .= '<td class="fondo-ojo">' . ($variables_od[$campo] ? '1' : '0') . '</td>';
        $html .= '<td class="fondo-ojo">' . ($variables_oi[$campo] ? '1' : '0') . '</td>';
    }

    // Obtener valores para vessels, vitreous y periph
    $vessels_od = $has_fundus ? $row['ODVESSELS'] : '';
    $vessels_oi = $has_fundus ? $row['OSVESSELS'] : '';

    $vitreous_od = $has_fundus ? $row['ODVITREOUS'] : '';
    $vitreous_oi = $has_fundus ? $row['OSVITREOUS'] : '';

    $periph_od = $has_fundus ? $row['ODPERIPH'] : '';
    $periph_oi = $has_fundus ? $row['OSPERIPH'] : '';

    $variables_vessels_od = extraerVariablesVessels($vessels_od);
    $variables_vessels_oi = extraerVariablesVessels($vessels_oi);

    foreach (array_keys($variables_vessels_od) as $campo) {
        $html .= '<td class="fondo-ojo">' . ($variables_vessels_od[$campo] ? '1' : '0') . '</td>';
        $html .= '<td class="fondo-ojo">' . ($variables_vessels_oi[$campo] ? '1' : '0') . '</td>';
    }

    $variables_vitreous_od = extraerVariablesVitreous($vitreous_od);
    $variables_vitreous_oi = extraerVariablesVitreous($vitreous_oi);

    foreach (array_keys($variables_vitreous_od) as $campo) {
        $html .= '<td class="fondo-ojo">' . ($variables_vitreous_od[$campo] ? '1' : '0') . '</td>';
        $html .= '<td class="fondo-ojo">' . ($variables_vitreous_oi[$campo] ? '1' : '0') . '</td>';
    }

    $variables_periph_od = extraerVariablesPeriph($periph_od);
    $variables_periph_oi = extraerVariablesPeriph($periph_oi);

    foreach (array_keys($variables_periph_od) as $campo) {
        $html .= '<td class="fondo-ojo">' . ($variables_periph_od[$campo] ? '1' : '0') . '</td>';
        $html .= '<td class="fondo-ojo">' . ($variables_periph_oi[$campo] ? '1' : '0') . '</td>';
    }

    return $html;
}

function minar_frases_por_estructura($estructura = 'macula', $pids = [])
{
    // Define los campos para cada estructura
    $campos_por_estructura = [
        'macula' => ['ODMACULA', 'OSMACULA'],
        'vessels' => ['ODVESSELS', 'OSVESSELS'],
        'vitreous' => ['ODVITREOUS', 'OSVITREOUS'],
        'periph' => ['ODPERIPH', 'OSPERIPH'],
    ];
    $fields = $campos_por_estructura[$estructura] ?? [];
    if (!$fields) return [];

    $frases = [];
    if ($pids && is_array($pids) && count($pids) > 0) {
        $in_pids = implode(',', array_map('intval', $pids));
        $query = "SELECT " . implode(',', $fields) . " FROM form_eye_postseg WHERE pid IN ($in_pids)";
    } else {
        $query = "SELECT " . implode(',', $fields) . " FROM form_eye_postseg";
    }
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
        foreach ($fields as $campo) {
            $txt = strtolower(trim($row[$campo] ?? ''));
            $txt = strtr($txt, 'áéíóúüñ', 'aeiouun');
            $txt = preg_replace('/[^\w\s]/u', '', $txt);
            $palabras = preg_split('/\s+/', $txt);
            foreach ($palabras as $palabra) {
                if (strlen($palabra) > 2) { // Ignora palabras muy cortas
                    if (!isset($frases[$palabra])) $frases[$palabra] = 0;
                    $frases[$palabra]++;
                }
            }
        }
    }
    arsort($frases);
    return array_slice($frases, 0, 20, true);
}

?>
