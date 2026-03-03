<?php
/**
 * Eye Mag IMPPLAN JavaScript helpers.
 *
 * @package OpenEMR
 */
?>
function normalizeIMPPLANCodeList(codeValue) {
    var rawCodes = [];
    if (Array.isArray(codeValue)) {
        rawCodes = codeValue;
    } else if (typeof codeValue === "string") {
        rawCodes = codeValue.split(",");
    } else if (typeof codeValue !== "undefined" && codeValue !== null) {
        rawCodes = [String(codeValue)];
    }

    var deduped = [];
    var seen = {};
    $.each(rawCodes, function(_, codeItem) {
        var code = $.trim(String(codeItem));
        if (!code) {
            return;
        }
        if (!seen[code]) {
            seen[code] = true;
            deduped.push(code);
        }
    });
    return deduped;
}

function normalizeIMPPLANItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    var normalized = [];
    var seenRows = {};
    $.each(items, function(_, value) {
        if (typeof value !== "object" || value === null) {
            return;
        }

        var row = $.extend({}, value);
        var codeList = normalizeIMPPLANCodeList(row.code);
        row.code = codeList.join(", ");
        row.codes = codeList;
        if (typeof row.title === "undefined" || row.title === null) {
            row.title = '';
        }
        if (typeof row.plan === "undefined" || row.plan === null) {
            row.plan = '';
        }
        if (typeof row.codetext === "undefined" || row.codetext === null) {
            row.codetext = '';
        }
        if (typeof row.codedesc === "undefined" || row.codedesc === null) {
            row.codedesc = '';
        }
        if (typeof row.PMSFH_link === "undefined" || row.PMSFH_link === null) {
            row.PMSFH_link = '';
        }

        var dedupeKey = row.title + "|" + row.code + "|" + $.trim(row.plan) + "|" + row.PMSFH_link;
        if (seenRows[dedupeKey]) {
            return;
        }
        seenRows[dedupeKey] = true;
        normalized.push(row);
    });
    return normalized;
}

function escapeIMPPLANHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getPlanTemplateSuggestion(codeValue, diagnosisText) {
    var codeList = normalizeIMPPLANCodeList(codeValue);
    var templates = [
        { prefix: 'Z96.1', text: 'Lente intraocular estable. Control visual y seguimiento de capsula posterior.' },
        { prefix: 'H25', text: 'Valorar progresion de catarata, impacto funcional y plan quirurgico segun sintomas.' },
        { prefix: 'H40', text: 'Control de PIO, adherencia al tratamiento, OCT/campo visual y ajuste terapeutico.' },
        { prefix: 'H52', text: 'Actualizar refraccion y reevaluar necesidad de correccion optica.' },
        { prefix: 'H16', text: 'Evaluar superficie ocular, lubricacion y respuesta al manejo antiinflamatorio.' }
    ];

    var suggestions = [];
    $.each(codeList, function(_, code) {
        var matched = null;
        $.each(templates, function(__, template) {
            if (code.indexOf(template.prefix) === 0) {
                matched = template.text;
                return false;
            }
        });
        if (matched && suggestions.indexOf(matched) === -1) {
            suggestions.push(matched);
        }
    });

    if (!suggestions.length && diagnosisText) {
        suggestions.push('Describir evolucion clinica, examenes relevantes y plan de control del diagnostico.');
    }

    return suggestions.join(' ');
}
