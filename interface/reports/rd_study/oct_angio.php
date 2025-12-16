<?php
require_once("utils_rd.php"); // Para fetchData, helpers, etc.

function render_oct_angio($pid)
{
    // Lista de campos y condiciones
    $fieldsOCT = ['OCT_od', 'OCT_oi'];
    $conditionsOCT = [
        'edema',
        'difuso',
        'quistico',
        'epitelio',
        'exudados',
        'lipidico',
        'epiretinal',
        'desprendimiento',
        'fibrosis'
    ];

    // Buscar si existe un formulario OCT
    $queryOCT = sqlQuery("SELECT form_id FROM forms WHERE pid = ? AND formdir = 'LBFoct_mac' AND deleted = 0 LIMIT 1", array($pid));
    $hasOCT = ($queryOCT && isset($queryOCT['form_id']));

    $html = '';
    // Imprimir celda de Primer OCT (1/0)
    $html .= '<td class="oct-angio">' . ($hasOCT ? '1' : '0') . '</td>';

    // Imprimir hallazgos derivados del OCT solo si $hasOCT es verdadero
    if ($hasOCT) {
        foreach ($conditionsOCT as $condition) {
            foreach ($fieldsOCT as $field) {
                $hay = (stripos(text(fetchData($pid, 'LBFoct_mac', [$field])), $condition) !== false);
                $html .= '<td class="oct-angio">' . ($hay ? '1' : '0') . '</td>';
            }
        }
        // --- Recoger y mostrar TM mácula OD y OI ---
        // Extraer solo el valor numérico de TM_macula y TM_macula_OI
        $raw_tm_macula_od = text(fetchData($pid, 'LBFoct_mac', ['TM_macula']));
        $tm_macula_od = '';
        if (preg_match('/\d+/', $raw_tm_macula_od, $matches)) {
            $tm_macula_od = $matches[0];
        }
        $raw_tm_macula_oi = text(fetchData($pid, 'LBFoct_mac', ['TM_macula_OI']));
        $tm_macula_oi = '';
        if (preg_match('/\d+/', $raw_tm_macula_oi, $matches)) {
            $tm_macula_oi = $matches[0];
        }
        $html .= '<td class="oct-angio">' . $tm_macula_od . '</td>';
        $html .= '<td class="oct-angio">' . $tm_macula_oi . '</td>';
    } else {
        // Hallazgos OCT: 9 condiciones x 2 ojos = 18 celdas vacías
        for ($i = 0; $i < count($conditionsOCT) * count($fieldsOCT); $i++) {
            $html .= '<td class="oct-angio">0</td>';
        }
        // Si no hay OCT, columnas TM mácula OD y OI vacías
        $html .= '<td class="oct-angio"></td>';
        $html .= '<td class="oct-angio"></td>';
    }
    return $html;
}

?>
