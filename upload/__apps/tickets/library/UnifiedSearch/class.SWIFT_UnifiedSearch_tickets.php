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

namespace Tickets\Library\UnifiedSearch;

use SWIFT;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Loader;
use SWIFT_StringHighlighter;
use SWIFT_StringHTMLToText;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Filter\SWIFT_TicketFilter;
use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;
use Base\Models\User\SWIFT_UserEmail;
use Tickets\Library\Search\SWIFT_TicketSearchManager;

/**
 * The Unified Search Library for Tickets App
 *
 * @property SWIFT_StringHTMLToText $StringHTMLToText
 * @property SWIFT_StringHighlighter $StringHighlighter
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_tickets extends SWIFT_UnifiedSearchBase
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
         * ADMIN SPECIFIC
         * ---------------------------------------------
         */
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            // Ticket Types
            $_finalSearchResults[$this->Language->Get('us_tickettypes')] = $this->SearchTypes();

            // Ticket Statuses
            $_finalSearchResults[$this->Language->Get('us_ticketstatus')] = $this->SearchStatuses();

            // Ticket Priorities
            $_finalSearchResults[$this->Language->Get('us_ticketpriorities')] = $this->SearchPriorities();

            // Ticket Auto Close Rules
            $_finalSearchResults[$this->Language->Get('us_ticketautoclose')] = $this->SearchAutoClose();

            // Workflows
            $_finalSearchResults[$this->Language->Get('us_workflow')] = $this->SearchWorkflow();

            // SLA Plans
            $_finalSearchResults[$this->Language->Get('us_slaplans')] = $this->SearchSLAPlans();

            // Escalations
            $_finalSearchResults[$this->Language->Get('us_escalations')] = $this->SearchEscalations();

            // Bayesian
            $_finalSearchResults[$this->Language->Get('us_bayesian')] = $this->SearchBayesian();




        /**
         * ---------------------------------------------
         * STAFF SPECIFIC
         * ---------------------------------------------
         */
        } else if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
            // Tickets
            $_finalSearchResults[$this->Language->Get('us_tickets') . '::' . $this->Language->Get('us_updated')] = $this->SearchTickets();

            // Filters
            $_finalSearchResults[$this->Language->Get('us_filters') . '::' . $this->Language->Get('us_used')] = $this->SearchFilters();
        }

        return $_finalSearchResults;
    }

    /**
     * Search the Ticket Types
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchTypes()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewtypes') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickettypes
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/Type/Edit/' . $this->Database->Record['tickettypeid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Ticket Statuses
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchStatuses()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewstatus') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketstatus
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/Status/Edit/' . $this->Database->Record['ticketstatusid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Ticket Priorities
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchPriorities()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewpriorities') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/Priority/Edit/' . $this->Database->Record['priorityid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Ticket Auto Close Rules
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchAutoClose()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewautoclose') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "autocloserules
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/AutoClose/Edit/' . $this->Database->Record['autocloseruleid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Workflow
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchWorkflow()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewworkflows') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketworkflows
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/Workflow/Edit/' . $this->Database->Record['ticketworkflowid']);
        }

        return $_searchResults;
    }

    /**
     * Search the SLA Plans
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchSLAPlans()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewslaplans') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "slaplans
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/SLA/Edit/' . $this->Database->Record['slaplanid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Escalations
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchEscalations()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewescalations') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "escalationrules
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/Escalation/Edit/' . $this->Database->Record['escalationruleid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Bayesian
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchBayesian()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tcanviewbayescategories') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "bayescategories
            WHERE (" . BuildSQLSearch('category', $this->GetQuery(), false, false) . ")
            ORDER BY category ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['category']), SWIFT::Get('basename') . '/Tickets/BayesianCategory/Edit/' . $this->Database->Record['bayescategoryid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Ticket Filters
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchFilters()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_tcanviewfilters') == '0' || $this->GetStaff()->GetPermission('staff_tcanviewtickets') == '0') {
            return array();
        }

        SWIFT_Loader::LoadModel('Filter:TicketFilter', APP_TICKETS);

        $_finalTicketFilterIDList = array();

        $_ticketFilterCache = (array) $_SWIFT->Cache->Get('ticketfiltercache');
        foreach ($_ticketFilterCache as $_ticketFilterID => $_ticketFilterContainer)
        {
            if (($_ticketFilterContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC && $_ticketFilterContainer['restrictstaffgroupid'] == '0') ||
                    ($_ticketFilterContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC && $_ticketFilterContainer['restrictstaffgroupid'] == $_SWIFT->Staff->GetProperty('staffgroupid')) ||
                    ($_ticketFilterContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PRIVATE && $_ticketFilterContainer['staffid'] == $_SWIFT->Staff->GetStaffID()))
            {
                $_finalTicketFilterIDList[] = $_ticketFilterID;
            }
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketfilters
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
                AND ticketfilterid IN (" . BuildIN($_finalTicketFilterIDList) . ")
            ORDER BY lastactivity DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Tickets/Search/Filter/' . $this->Database->Record['ticketfilterid'], SWIFT_Date::EasyDate($this->Database->Record['lastactivity']));
        }

        return $_searchResults;
    }

    /**
     * Search the Tickets
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchTickets()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_tcanviewtickets') == '0') {
            return array();
        }

        $_staffCache = (array) $this->Cache->Get('staffcache');
        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');
        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');

        $_ticketIDList = SWIFT_TicketSearchManager::QuickSearch($this->GetQuery(), $_SWIFT->Staff);

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        $_otherTicketIDList = $_userIDList = $_userEmailList = array();
        $this->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE ((" . BuildSQLSearch('ticketmaskid', $this->GetQuery(), false, false) . ") OR (ticketid = '" . (int) ($this->GetQuery()) . "'))
                AND departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")
            ORDER BY lastactivity DESC", 1);
        while ($this->Database->NextRecord()) {
            $_otherTicketIDList[] = $this->Database->Record['ticketid'];
        }

        $this->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE (ticketmaskid = '" . $this->GetQuery() . "' OR ticketid = '" . (int) ($this->GetQuery()) . "')
                AND departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")
            ORDER BY lastactivity DESC", 3);
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['ticketid'], $_otherTicketIDList)) {
                $_otherTicketIDList[] = $this->Database->Record['ticketid'];
            }
        }


        $_finalTicketIDList = array_merge($_ticketIDList, $_otherTicketIDList);
        $this->Database->Query("SELECT ticketid, userid, email FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_finalTicketIDList) . ")");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['userid'] != '0') {
                $_userIDList[] = $this->Database->Record['userid'];

                if (!isset($_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']] = array();
                }

                if ($this->Database->Record['email'] != '' && !in_array($this->Database->Record['email'], $_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']][] = $this->Database->Record['email'];
                }
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails
            WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (isset($_userEmailList[$this->Database->Record['linktypeid']]) && !in_array($this->Database->Record['email'], $_userEmailList[$this->Database->Record['linktypeid']])) {
                $_userEmailList[$this->Database->Record['linktypeid']][] = $this->Database->Record['email'];
            }
        }

        $_searchResults = $_ticketPostsContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid IN (" . BuildIN($_finalTicketIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (!isset($_ticketPostsContainer[$this->Database->Record['ticketid']])) {
                $_ticketPostsContainer[$this->Database->Record['ticketid']] = array();
            }

            if (self::HasQuery($this->Database->Record['contents'], $this->GetQuery()) && count($_ticketPostsContainer[$this->Database->Record['ticketid']]) <= 5) {
                $_highlightResult = implode(' ... ', $this->StringHighlighter->GetHighlightedRange($this->StringHTMLToText->Convert($this->Database->Record['contents']), $this->GetQuery(), 20));

                if (trim($_highlightResult) != '') {
                    $_ticketPostsContainer[$this->Database->Record['ticketid']][] = '... ' . $_highlightResult . ' ...';
                }
            }
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets
            WHERE ticketid IN (" . BuildIN($_finalTicketIDList) . ")
            ORDER BY lastactivity DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));

            $_extendedInfo = '';
            if (isset($_userEmailList[$this->Database->Record['userid']])) {
                $_extendedInfo .= implode(', ', $_userEmailList[$this->Database->Record['userid']]) . '<br />';
            }

            $_infoBar = '';

            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_infoBar .= sprintf($this->Language->Get('usi_department'), $_departmentCache[$this->Database->Record['departmentid']]['title']) . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if (isset($_staffCache[$this->Database->Record['ownerstaffid']])) {
                $_infoBar .= sprintf($this->Language->Get('usi_owner'), $_staffCache[$this->Database->Record['ownerstaffid']]['fullname']) . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if (isset($_ticketStatusCache[$this->Database->Record['ticketstatusid']])) {
                $_infoBar .= sprintf($this->Language->Get('usi_status'), $_ticketStatusCache[$this->Database->Record['ticketstatusid']]['title']) . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if (isset($_ticketTypeCache[$this->Database->Record['tickettypeid']])) {
                $_infoBar .= sprintf($this->Language->Get('usi_type'), $_ticketTypeCache[$this->Database->Record['tickettypeid']]['title']) . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if (isset($_ticketPriorityCache[$this->Database->Record['priorityid']])) {
                $_infoBar .= sprintf($this->Language->Get('usi_priority'), $_ticketPriorityCache[$this->Database->Record['priorityid']]['title']) . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            $_extendedInfo .= $_infoBar . '<br />';

            if (isset($_ticketPostsContainer[$this->Database->Record['ticketid']])) {
                $_extendedInfo .= '<br />' . implode('<br />' . SWIFT_CRLF, $_ticketPostsContainer[$this->Database->Record['ticketid']]);
            }

            $_searchResults[] = array(text_to_html_entities($this->Database->Record['fullname']) . ': ' . htmlspecialchars($this->Database->Record['subject']), SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $this->Database->Record['ticketid'], SWIFT_Date::EasyDate($this->Database->Record['lastactivity']), $_extendedInfo);
        }

        return $_searchResults;
    }
}
