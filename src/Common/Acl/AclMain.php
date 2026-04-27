<?php

/**
 * Compatibility wrapper for newer ACL class references on older OpenEMR codebases.
 *
 * This project still uses the legacy functions from library/acl.inc, but some
 * screens reference the newer namespaced ACL classes. Provide a thin bridge so
 * those screens keep working without changing the underlying ACL engine.
 */

namespace OpenEMR\Common\Acl;

class AclMain
{
    /**
     * Proxy to the legacy ACL check implementation.
     *
     * @param string $section
     * @param string $value
     * @param string $user
     * @param string|array $return_value
     * @return bool
     */
    public static function aclCheckCore($section, $value, $user = '', $return_value = '')
    {
        require_once dirname(__DIR__, 3) . '/library/acl.inc';

        return acl_check($section, $value, $user, $return_value);
    }

    /**
     * Check permissions for an aco_spec in "section|aco" format.
     *
     * @param string $aco_spec
     * @param string $user
     * @param string|array $return_value
     * @return bool
     */
    public static function aclCheckAcoSpec($aco_spec, $user = '', $return_value = '')
    {
        if (empty($aco_spec)) {
            return true;
        }

        $parts = explode('|', (string) $aco_spec, 2);
        if (count($parts) < 2) {
            return false;
        }

        if (!is_array($return_value)) {
            $return_value = [$return_value];
        }

        foreach ($return_value as $rv) {
            if (self::aclCheckCore($parts[0], $parts[1], $user, $rv)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check permissions for a registered form directory.
     *
     * @param string $formdir
     * @param string $user
     * @param string|array $return_value
     * @return bool
     */
    public static function aclCheckForm($formdir, $user = '', $return_value = '')
    {
        require_once dirname(__DIR__, 3) . '/library/registry.inc';

        $entry = getRegistryEntryByDirectory($formdir, 'aco_spec');

        return self::aclCheckAcoSpec($entry['aco_spec'] ?? '', $user, $return_value);
    }

    /**
     * Check permissions for an issue type.
     *
     * @param string $type
     * @param string $user
     * @param string|array $return_value
     * @return bool
     */
    public static function aclCheckIssue($type, $user = '', $return_value = '')
    {
        require_once dirname(__DIR__, 3) . '/library/lists.inc.php';

        global $ISSUE_TYPES;

        if (empty($ISSUE_TYPES[$type][5])) {
            return true;
        }

        return self::aclCheckAcoSpec($ISSUE_TYPES[$type][5], $user, $return_value);
    }

    /**
     * Compatibility helper used by newer calendar ACL code.
     *
     * @param int|string $pc_catid
     * @return string
     */
    public static function fetchPostCalendarCategoryACO($pc_catid)
    {
        $aco = sqlQuery(
            "SELECT aco_spec FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1",
            [$pc_catid]
        );

        return $aco['aco_spec'] ?? '';
    }
}
