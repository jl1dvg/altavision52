<?php
require_once("utils_rd.php"); // Por si necesitas fetchData o helpers

function render_derived_fields($pid)
{
    // Asumiendo que tienes estas funciones (ajusta si tus funciones son diferentes)
    // determinarTipoRD($pid, $ojo) y determinarTipoRDIntegral($pid)
    // Si no existen, reemplázalas con tu lógica/consultas.

    $rd_od = function_exists('determinarTipoRD') ? determinarTipoRD($pid, 'OD') : ['clasificacion' => '', 'etdrs' => ''];
    $rd_oi = function_exists('determinarTipoRD') ? determinarTipoRD($pid, 'OI') : ['clasificacion' => '', 'etdrs' => ''];
    $rd_integral = function_exists('determinarTipoRDIntegral') ? determinarTipoRDIntegral($pid) : ['clasificacion' => '', 'etdrs' => ''];

    $html = '';

// Depuración visual (mostrar el array como comentario HTML)
    $html .= '<!-- RD OD: ' . htmlspecialchars(print_r($rd_od, true)) . ' -->';
    $html .= '<!-- RD OI: ' . htmlspecialchars(print_r($rd_oi, true)) . ' -->';
    $html .= '<!-- RD Integral: ' . htmlspecialchars(print_r($rd_integral, true)) . ' -->';

    $html .= '<td class="rd-derived">' . text($rd_od['clasificacion']) . '</td>';
    $html .= '<td class="rd-derived">' . text($rd_od['etdrs']) . '</td>';
    $html .= '<td class="rd-derived">' . text($rd_oi['clasificacion']) . '</td>';
    $html .= '<td class="rd-derived">' . text($rd_oi['etdrs']) . '</td>';
    $html .= '<td class="rd-derived">' . text($rd_integral['clasificacion']) . '</td>';
    $html .= '<td class="rd-derived">' . text($rd_integral['etdrs']) . '</td>';

    return $html;
}

?>
