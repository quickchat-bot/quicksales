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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The Unified Search Library for Reports App
 *
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_reports extends SWIFT_UnifiedSearchBase
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults)
    {
        parent::__construct($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);
    }

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
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
            // Reports
            $_finalSearchResults[$this->Language->Get('us_reports') . '::' . $this->Language->Get('us_used')] = $this->SearchReports();
        }

        return $_finalSearchResults;
    }

    /**
     * Search the Reports
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchReports()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_rcanviewreports') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "reports
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY executedateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Reports/Report/Edit/' . $this->Database->Record['reportid'], SWIFT_Date::EasyDate($this->Database->Record['executedateline']));
        }

        return $_searchResults;
    }
}
