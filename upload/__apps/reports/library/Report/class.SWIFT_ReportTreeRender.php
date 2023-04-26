<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Report Tree Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_ReportTreeRender extends SWIFT_Library
{
    /**
     * Render the Report Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     */
    public static function Render()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_reportCategoryCache = $_SWIFT->Cache->Get('reportcategorycache');

        $_renderHTML = '<ul class="swifttree">';


        $_renderHTML .= '<li><span class="userreport"><a href="' . SWIFT::Get('basename') . '/Reports/Report/QuickFilter/MyReports/0" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('treemyreports')) . '</a></span></li>';

        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Reports/Report/QuickFilter/Recent/0" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('treerecentlyused')) . '</a></span></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $_SWIFT->Language->Get('treecategories') . '</a></span>';
        $_renderHTML .= '<ul>';

        $_SWIFT->Database->Query("SELECT reportcategories.* FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
            LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
            LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                    OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                    OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                    )
            ORDER BY reportcategories.title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_categoryClass = 'folder';

            if ($_SWIFT->Database->Record['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PRIVATE) {
                $_categoryClass = 'folderfaded';
            }

            $_renderHTML .= '<li><span class="' . $_categoryClass . '"><a href="' . SWIFT::Get('basename') . '/Reports/Report/QuickFilter/category/' . (int) ($_SWIFT->Database->Record['reportcategoryid']) . '" viewport="1">' . htmlspecialchars($_SWIFT->Database->Record['title']) . '</a></span>';
        }

        $_renderHTML .= '</ul>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }
}
?>
