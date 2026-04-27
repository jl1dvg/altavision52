<?php

/**
 * Compatibility wrapper for newer ACL helper calls on older OpenEMR codebases.
 */

namespace OpenEMR\Common\Acl;

class AclExtended
{
    /**
     * Generate grouped HTML <option> tags for all ACOs.
     *
     * @param string $default
     * @return string
     */
    public static function genAcoHtmlOptions($default = '')
    {
        require_once dirname(__DIR__, 3) . '/library/acl.inc';

        global $phpgacl_location;

        if (empty($phpgacl_location)) {
            return '';
        }

        include_once $phpgacl_location . '/gacl_api.class.php';

        $gacl = new \gacl_api();
        $sections = $gacl->get_objects(null, 0, 'ACO');
        if (!is_array($sections)) {
            return '';
        }

        ksort($sections);

        $html = '';
        foreach ($sections as $sectionKey => $objects) {
            if (empty($objects) || !is_array($objects)) {
                continue;
            }

            asort($objects);
            $sectionData = $gacl->get_section_data($sectionKey, 'ACO');
            $sectionTitle = $sectionData[3] ?? $sectionKey;

            $html .= "<optgroup label='" . attr(xl_gacl_group($sectionTitle)) . "'>\n";
            foreach ($objects as $acoKey) {
                $optionValue = $sectionKey . '|' . $acoKey;
                $acoId = $gacl->get_object_id($sectionKey, $acoKey, 'ACO');
                $acoData = $gacl->get_object_data($acoId, 'ACO');
                $acoTitle = $acoData[0][3] ?? $acoKey;
                $selected = ((string) $default === $optionValue) ? " selected='selected'" : '';

                $html .= "<option value='" . attr($optionValue) . "'" . $selected . ">" .
                    text(xl_gacl_group($acoTitle)) .
                    "</option>\n";
            }
            $html .= "</optgroup>\n";
        }

        return $html;
    }
}
