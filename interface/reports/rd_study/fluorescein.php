<?php
require_once("utils_rd.php"); // Helpers, fetchData, etc.

function render_fluorescein($pid)
{
    $fieldsAngio = ['Angio_OD', 'Angio_OI'];
    $conditionsAngio = [
        'diabetica',
        'proliferativa',
        'no proliferativa',
        'isquemia',
        'leve',
        'moderada',
        'severa',
        'opacidad',
        'no valorable'
    ];
    // Sinónimos para mejorar robustez
    $sinonimosAngio = [
        'diabetica' => ['diabetica', 'diabética'],
        'proliferativa' => ['proliferativa', 'proliferativo'],
        'no proliferativa' => ['no proliferativa', 'no-proliferativa', 'no proliferativo', 'no-proliferativo'],
        'isquemia' => ['isquemia', 'isquémica'],
        'leve' => ['leve'],
        'moderada' => ['moderada', 'moderado'],
        'severa' => ['severa', 'severo', 'grave'],
        'opacidad' => ['opacidad', 'opaco'],
        'no valorable' => ['no valorable', 'no val', 'no evaluable'],
    ];
    // Helper para buscar sinónimos
    $contieneAngio = function ($texto, $grupo) use ($sinonimosAngio) {
        foreach ($sinonimosAngio[$grupo] as $palabra) {
            if (stripos($texto, $palabra) !== false) {
                return true;
            }
        }
        return false;
    };

    // Buscar si existe un formulario AF
    $queryAF = sqlQuery("SELECT form_id FROM forms WHERE pid = ? AND formdir = 'LBFaf' AND deleted = 0 LIMIT 1", array($pid));
    $hasAF = ($queryAF && isset($queryAF['form_id']));

    $html = '';
    // Primer AF (1/0)
    $html .= '<td class="af-fluor">' . ($hasAF ? '1' : '0') . '</td>';

    // Si tiene AF, analizamos condiciones; si no, todo en cero
    if ($hasAF) {
        foreach ($conditionsAngio as $condition) {
            foreach ($fieldsAngio as $field) {
                $texto = strtolower(text(fetchData($pid, 'LBFaf', [$field])));
                $html .= '<td class="af-fluor">' . ($contieneAngio($texto, $condition) ? '1' : '0') . '</td>';
            }
        }
    } else {
        // Sin AF: 9 condiciones x 2 ojos = 18 celdas en cero
        for ($i = 0; $i < count($conditionsAngio) * count($fieldsAngio); $i++) {
            $html .= '<td class="af-fluor">0</td>';
        }
    }
    return $html;
}
?>
