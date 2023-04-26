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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Troubleshooter\Library\UnifiedSearch;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use Base\Models\Staff\SWIFT_StaffGroupLink;

/**
 * The Unified Search Library for Troubleshooter App
 *
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_troubleshooter extends \Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase
{

    /**
     * Run the search and return results
     *
     * @author Varun Shoor
     * @return array Container of Result Objects
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalSearchResults = array();

        /**
         * ---------------------------------------------
         * STAFF SPECIFIC
         * ---------------------------------------------
         */
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF ||
            $this->GetInterface() == SWIFT_Interface::INTERFACE_TESTS) {
            // Troubleshooter Categories
            $_finalSearchResults[$this->Language->Get('us_troubleshooter')] = $this->SearchCategories();
        }

        return $_finalSearchResults;
    }

    /**
     * Search the Troubleshooter Categories
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchCategories()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_trcanviewcategories') == '0') {
            return array();
        }

        // First get the list of probable categories
        $_filterStaffTroubleshooterCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, $this->GetStaff()->GetProperty('staffgroupid'));

        $_troubleshooterCategoryIDList = array();

        $this->Database->Query("SELECT troubleshootercategoryid FROM " . TABLE_PREFIX . "troubleshootercategories
            WHERE (staffvisibilitycustom = '0' OR (staffvisibilitycustom = '1' AND troubleshootercategoryid IN (" . BuildIN($_filterStaffTroubleshooterCategoryIDList) . ")))
            ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_troubleshooterCategoryIDList[] = $this->Database->Record['troubleshootercategoryid'];
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT troubleshootercategoryid, title FROM " . TABLE_PREFIX . "troubleshootercategories
            WHERE ((" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('description', $this->GetQuery(), false, false) . "))
                AND troubleshootercategoryid IN (" . BuildIN($_troubleshooterCategoryIDList) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Troubleshooter/Step/ViewSteps/' . $this->Database->Record['troubleshootercategoryid']);
        }

        return $_searchResults;
    }
}
?>