<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Library\UnifiedSearch;

use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * The Unified Search Library for Parser App
 *
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_parser extends SWIFT_UnifiedSearchBase
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string      $_query         The Search Query
     * @param mixed       $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int         $_maxResults
     *
     * @return bool "true" on Success, "false" otherwise
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
         * ADMIN SPECIFIC
         * ---------------------------------------------
         */
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            // Email Queues
            $_finalSearchResults[$this->Language->Get('us_parserqueue')] = $this->SearchQueues();

            // Rules
            $_finalSearchResults[$this->Language->Get('us_parserrules')] = $this->SearchRules();
        }

        return $_finalSearchResults;
    }

    /**
     * Search the Rules
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchRules()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_mpcanviewrules') == '0') {
            // @codeCoverageIgnoreStart
            return array();
            // @codeCoverageIgnoreEnd
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "parserrules
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Parser/Rule/Edit/' . $this->Database->Record['parserruleid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Queues
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchQueues()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_mpcanviewqueues') == '0') {
            // @codeCoverageIgnoreStart
            return array();
            // @codeCoverageIgnoreEnd
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "emailqueues
            WHERE (" . BuildSQLSearch('email', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('customfromname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('customfromemail', $this->GetQuery(), false, false) . ")
            ORDER BY email ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['email'], SWIFT::Get('basename') . '/Parser/EmailQueue/Edit/' . $this->Database->Record['emailqueueid']);
        }

        return $_searchResults;
    }
}

?>
