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

namespace Tickets\Library\Search;

use SWIFT;
use SWIFT_App;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Library\CustomField\SWIFT_CustomFieldSearch;
use SWIFT_DataID;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_StringHighlighter;
use SWIFT_StringHTMLToText;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Models\User\SWIFT_UserEmail;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Tickets\Library\Flag\SWIFT_TicketFlag;

/**
 * The Ticket Search Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketSearchManager extends SWIFT_Library
{
    /**
     * Make owner staff ID where clause
     *
     * @author Parminder Singh
     *
     * @return String Prepared where clause
     */
    protected static function GetOwnerIDClause()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ownerIDClause    = $_ownerNotFlag = '';
        $_ownerStaffIDList = array();

        if ($_SWIFT->Staff->GetPermission('staff_tcanviewall') == '0') {
            $_ownerStaffIDList[] = $_SWIFT->Staff->GetStaffID();

            if ($_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '1') {
                $_ownerStaffIDList[] = '0';
            }
        } else {
            if ($_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '0') {
                $_ownerStaffIDList[] = '0';
                $_ownerNotFlag       = 'NOT';
            }
        }

        if (count($_ownerStaffIDList) > 0) {
            $_ownerIDClause = " ownerstaffid " . $_ownerNotFlag . " IN (" . BuildIN($_ownerStaffIDList, true) . ")";
        }

        return $_ownerIDClause;
    }

    /**
     * Retrieve the tickets for a given owner
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_ownerStaffID The Owner Staff ID
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnOwner(SWIFT_Staff $_SWIFT_StaffObject, $_ownerStaffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        if (($_ownerStaffID == '0' && $_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '0') || ($_ownerStaffID != '0' && $_ownerStaffID != $_SWIFT->Staff->GetStaffID() && $_SWIFT->Staff->GetPermission('staff_tcanviewall') == '0')) {
            return array();
        }

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")
            AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList) .") AND ownerstaffid = '" . ($_ownerStaffID) . "'
            ORDER BY lastactivity DESC", ($_SWIFT->Settings->Get('t_resultlimit')));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve the tickets for a given ticket status
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_ticketStatusID The Ticket Status ID
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnStatus(SWIFT_Staff $_SWIFT_StaffObject, $_ticketStatusID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        //$_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL     = '';
        $_ownerIDClause = self::GetOwnerIDClause();

        if ($_ownerIDClause != '') {
            $_extendSQL .= ' AND ' . $_ownerIDClause;
        }

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE departmentid IN (" . BuildIN($_assignedDepartmentIDList, true) . ")
            " . $_extendSQL . "
            AND ticketstatusid = '" . ($_ticketStatusID) . "'
            ORDER BY lastactivity DESC", ($_SWIFT->Settings->Get('t_resultlimit')));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve the tickets for a given ticket type
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_ticketTypeID The Ticket Type ID
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnType(SWIFT_Staff $_SWIFT_StaffObject, $_ticketTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL     = '';
        $_ownerIDClause = self::GetOwnerIDClause();

        if ($_ownerIDClause != '') {
            $_extendSQL  .= ' AND ' . $_ownerIDClause;
        }

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")
            " . $_extendSQL . "
            AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList, true) . ") AND tickettypeid = '" . ($_ticketTypeID) . "'
            ORDER BY lastactivity DESC", ($_SWIFT->Settings->Get('t_resultlimit')));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve the tickets for a given ticket priority
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnPriority(SWIFT_Staff $_SWIFT_StaffObject, $_ticketPriorityID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL     = '';
        $_ownerIDClause = self::GetOwnerIDClause();

        if ($_ownerIDClause != '') {
            $_extendSQL .= ' AND ' . $_ownerIDClause;
        }

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")
            " . $_extendSQL . "
            AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList, true) .") AND priorityid = '" . ($_ticketPriorityID) . "'
            ORDER BY lastactivity DESC", ($_SWIFT->Settings->Get('t_resultlimit')));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve the overdue for a given staff
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOverdueTickets(SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL     = '';
        $_ownerIDClause = self::GetOwnerIDClause();

        if ($_ownerIDClause != '') {
            $_extendSQL .= ' AND ' . $_ownerIDClause;
        }

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")
            " . $_extendSQL . "
            AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList, true) .") AND ((duetime != '0' AND duetime < '" . DATENOW . "') OR (resolutionduedateline != '0' AND resolutionduedateline < '" . DATENOW . "'))
            ORDER BY lastactivity DESC", ($_SWIFT->Settings->Get('t_resultlimit')));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve the new or updated tickets for a given staff
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param bool $_isUpdated (OPTIONAL) Whether to display updated tickets
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveNewUpdatedTickets(SWIFT_Staff $_SWIFT_StaffObject, $_isUpdated = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL     = '';
        $_ownerIDClause = self::GetOwnerIDClause();

        if ($_ownerIDClause != '') {
            $_extendSQL .= ' AND ' . $_ownerIDClause;
        }

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE departmentid IN (" . BuildIN($_assignedDepartmentIDList, true) . ")
            " . $_extendSQL . "
            AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList, true) .") AND " . (!$_isUpdated ? 'dateline' : 'lastactivity') . " > " . ($_SWIFT_StaffObject->GetProperty('lastvisit')) . "
            ORDER BY " . (!$_isUpdated ? 'dateline' : 'lastactivity') . " DESC", ($_SWIFT->Settings->Get('t_resultlimit')));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_ticketIDList;
    }

    /**
     * Search the Ticket IDs
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool|SWIFT_Staff  $_SWIFT_StaffObject (OPTIONAL)
     * @param array $_userTicketIDList
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SearchTicketID($_searchQuery, $_SWIFT_StaffObject = false, $_userTicketIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '')
        {
            return array();
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL = '';

        if (_is_array($_userTicketIDList)) {
            $_extendSQL .= " AND ticketid IN (" . BuildIN($_userTicketIDList, true) . ")";
        }

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_ownerIDClause = self::GetOwnerIDClause();
            if ($_ownerIDClause != '') {
                $_extendSQL .= ' AND ' . $_ownerIDClause;
            }
        }

        // If its a numerical value, then we become specific
        if (is_numeric($_searchQuery))
        {
            $_ticketIDSearchContainer = $_SWIFT->Database->QueryFetch("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketid = '" . ($_searchQuery) . "'" . $_extendSQL);
            if (isset($_ticketIDSearchContainer['ticketid']) && !empty($_ticketIDSearchContainer['ticketid']))
            {
                return array($_ticketIDSearchContainer['ticketid']);
            }

            // Also search merge logs
            $_ticketIDSearchContainer = $_SWIFT->Database->QueryFetch("SELECT ticketid FROM " . TABLE_PREFIX . "ticketmergelog WHERE oldticketid = '" . ($_searchQuery) . "'" . $_extendSQL);
            if (isset($_ticketIDSearchContainer['ticketid']) && !empty($_ticketIDSearchContainer['ticketid']))
            {
                return array($_ticketIDSearchContainer['ticketid']);
            }
        // Otherwise, we need to do a LIKE match
        } else {
            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-2814 Support center searching improvements
             *
             * Comments: Adding subject like in query
             */
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE (ticketmaskid LIKE '%" . $_SWIFT->Database->Escape($_searchQuery) . "%' OR subject LIKE '%" . $_SWIFT->Database->Escape($_searchQuery) . "%' ) " . $_extendSQL, $_SWIFT->Settings->Get('t_resultlimit'));
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            // Also search merge logs
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketmergelog WHERE oldticketmaskid LIKE '%" . $_SWIFT->Database->Escape($_searchQuery) . "%'"  . $_extendSQL, $_SWIFT->Settings->Get('t_resultlimit'));
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

        }

        return $_ticketIDList;
    }

    /**
     * Search the Creator and Last Replier
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param SWIFT_Staff|bool  $_SWIFT_StaffObject (OPTIONAL)
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SearchCreator($_searchQuery, $_SWIFT_StaffObject = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '')
        {
            return array();
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
            $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);
            $_extendSQL .= " AND (tickets.departmentid IN (" . BuildIN($_assignedDepartmentIDList, true) . ") AND tickets.ticketstatusid IN (" . BuildIN($_ticketStatusIDList, true) . "))";

            $_ownerIDClause = self::GetOwnerIDClause();
            if ($_ownerIDClause != '') {
                $_extendSQL .= ' AND ' . $_ownerIDClause;
            }
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE ((" . BuildSQLSearch('fullname', $_searchQuery) . ") OR (" . BuildSQLSearch('lastreplier', $_searchQuery) . ") OR (" . BuildSQLSearch('email', $_searchQuery) . "))
                " . $_extendSQL . "
            ORDER BY ticketid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        // Now search ticket posts
        $_SWIFT->Database->QueryLimit("SELECT ticketposts.ticketid FROM " . TABLE_PREFIX . "ticketposts AS ticketposts
            LEFT JOIN " . TABLE_PREFIX . "tickets AS tickets ON (ticketposts.ticketid = tickets.ticketid)
            WHERE ((" . BuildSQLSearch('ticketposts.fullname', $_searchQuery) . ") OR (" . BuildSQLSearch('ticketposts.email', $_searchQuery) . "))
                " . $_extendSQL . "
            ORDER BY ticketposts.ticketpostid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Search the Emails
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param SWIFT_Staff|bool $_SWIFT_StaffObject (OPTIONAL)
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SearchEmail($_searchQuery, $_SWIFT_StaffObject = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '')
        {
            return array();
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_ownerIDClause = self::GetOwnerIDClause();
            if ($_ownerIDClause != '') {
                $_extendSQL .= ' AND ' . $_ownerIDClause;
            }
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE (" . BuildSQLSearch('email', $_searchQuery) . ")
            " . $_extendSQL . "
            ORDER BY ticketid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        // Now search ticket posts
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts
            WHERE (" . BuildSQLSearch('email', $_searchQuery) . ")
            ORDER BY ticketpostid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Search the Subject
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param SWIFT_Staff|bool $_SWIFT_StaffObject (OPTIONAL)
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SearchSubject($_searchQuery, $_SWIFT_StaffObject = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '')
        {
            return array();
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_ownerIDClause = self::GetOwnerIDClause();
            if ($_ownerIDClause != '') {
                $_extendSQL .= ' AND ' . $_ownerIDClause;
            }
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE (" . BuildSQLSearch('subject', $_searchQuery) . ")
            " . $_extendSQL . "
            ORDER BY ticketid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        // Now search ticket posts
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts
            WHERE (" . BuildSQLSearch('subject', $_searchQuery) . ")
            ORDER BY ticketpostid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Search the Full Name and Last Replier
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param SWIFT_Staff|bool $_SWIFT_StaffObject (OPTIONAL)
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SearchFullName($_searchQuery, $_SWIFT_StaffObject = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '')
        {
            return array();
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_ownerIDClause = self::GetOwnerIDClause();
            if ($_ownerIDClause != '') {
                $_extendSQL .= ' AND ' . $_ownerIDClause;
            }
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE (" . BuildSQLSearch('fullname', $_searchQuery) . ") OR (" . BuildSQLSearch('lastreplier', $_searchQuery) . ")
            " . $_extendSQL . "
            ORDER BY ticketid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        // Now search ticket posts
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts
            WHERE (" . BuildSQLSearch('fullname', $_searchQuery) . ")
            ORDER BY ticketpostid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Do a quick search
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param SWIFT_Staff|bool $_SWIFT_StaffObject (OPTIONAL)
     * @param int|bool $_maxResults (OPTIONAL)
     * @param int|bool $_useOR Search whole phrase or individual words
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function QuickSearch($_searchQuery, $_SWIFT_StaffObject = false, $_maxResults = false, $_useOR = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Empty query?
        if (trim($_searchQuery) == '')
        {
            return array();
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_ownerIDClause = self::GetOwnerIDClause();
            if ($_ownerIDClause != '') {
                $_extendSQL .= ' AND ' . $_ownerIDClause;
            }
        }

        $_finalMaxResults = $_SWIFT->Settings->Get('t_resultlimit');
        if ($_maxResults !== false && is_numeric($_maxResults) && ($_maxResults) > 0) {
            $_finalMaxResults = $_maxResults;
        }

        $_departmentWhere = '';
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
            $_ticketStatusIDList = SWIFT_TicketViewRenderer::GetTicketStatusIDList($_assignedDepartmentIDList);
            $_extendSQL .= "AND (departmentid IN (" . BuildIN($_assignedDepartmentIDList, true) . ") AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList, true) . ")) ";
        }

        $_searchTicketIDList = $_ticketIDList = array();

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
            WHERE ((" . BuildSQLSearch('subject', $_searchQuery, false, $_useOR) . ") OR (" . BuildSQLSearch('fullname', $_searchQuery, false, $_useOR) . ") OR (" . BuildSQLSearch('email', $_searchQuery, false, $_useOR) . "))
            " . $_extendSQL . "
            ORDER BY ticketid DESC", $_finalMaxResults);
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        /**
         * BUG FIX: Pankaj Garg
         *
         * SWIFT-2747: "Include ticket notes in search
         *
         */
        if (count($_ticketIDList) < $_finalMaxResults) {
            $_SWIFT->Database->QueryLimit("SELECT DISTINCT linktypeid as ticketid FROM " . TABLE_PREFIX . SWIFT_TicketNote::TABLE_NAME . "
                                       WHERE (" . BuildSQLSearch('note', $_searchQuery, false, $_useOR) . ")
                                       ORDER BY ticketid DESC", ($_finalMaxResults - count($_ticketIDList)));

            while ($_SWIFT->Database->NextRecord()) {
                if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList)) {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }
        }

        // Now search with the search engine
        $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();
        $_SWIFT_SearchEngineObject->SetMaxResults($_finalMaxResults);
        $_searchEngineResult = $_SWIFT_SearchEngineObject->Find($_searchQuery, SWIFT_SearchEngine::TYPE_TICKET, [], $_useOR);

        if (_is_array($_searchEngineResult))
        {
            foreach ($_searchEngineResult as $_searchContainer)
            {
                if (!in_array($_searchContainer['objid'], $_searchTicketIDList))
                {
                    $_searchTicketIDList[] = $_searchContainer['objid'];
                }
            }

            if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                if (count($_searchTicketIDList))
                {
                    $_SWIFT->Database->Query("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_searchTicketIDList) . ")
                        " . $_extendSQL);
                    while ($_SWIFT->Database->NextRecord())
                    {
                        if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
                        {
                            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                        }
                    }
                }
            } else {
                if (count($_searchTicketIDList))
                {
                    $_SWIFT->Database->Query("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_searchTicketIDList) . ")");
                    while ($_SWIFT->Database->NextRecord())
                    {
                        if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
                        {
                            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                        }
                    }
                }
            }
        }

        return $_ticketIDList;
    }

    /**
     * Search the tickets using rules (either from advanced search or filter)
     *
     * @author Varun Shoor
     * @param array $_ruleCriteria The Rule Criteria
     * @param mixed $_criteriaOptions
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return array
     */
    public static function SearchRules($_ruleCriteria, $_criteriaOptions, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('ticketmanagegrid', 'tickets.lastactivity', 'desc');

        $_fieldPointer = SWIFT_TicketSearch::GetFieldPointer();
        $_sqlContainer = array();

        $_ticketIDJoinList = array();

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_extendSQL     = '';
        $_ownerIDClause = self::GetOwnerIDClause();

        if ($_ownerIDClause != '') {
            $_extendSQL .= ' AND ' . $_ownerIDClause;
        }

        if (_is_array($_ruleCriteria))
        {
            foreach ($_ruleCriteria as $_key => $_val)
            {
                // Is it date type?
                if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_DUE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_RESOLUTIONDUE ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_CREATIONDATE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTACTIVITY ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTSTAFFREPLY || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTUSERREPLY ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_ESCALATEDDATE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_RESOLUTIONDATE ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_REOPENDATE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_EDITEDDATE)
                {
                    if (empty($_val[2]))
                    {
                        $_val[2] = DATENOW;
                    } else {
                        $_val[2] = GetCalendarDateline($_val[2]);
                    }
                }

                // Make sure its not a date range..
                if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_DUERANGE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_RESOLUTIONDUERANGE ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_CREATIONDATERANGE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTACTIVITYRANGE ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTSTAFFREPLYRANGE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTUSERREPLYRANGE ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_ESCALATEDDATERANGE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_RESOLUTIONDATERANGE ||
                        $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_REOPENDATERANGE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_EDITEDDATERANGE)
                {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQLDateRange($_fieldPointer[$_val[0]], $_val[2]);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_MESSAGE) {
                    $_ticketIDJoinList[] = self::GetSearchMessage($_val[2]);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_MESSAGELIKE) {
                    $_ticketIDJoinList[] = self::GetSearchMessageLike($_val[2]);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_USER) {
                    $_ticketIDJoinList[] = self::GetSearchUser($_val[2], false);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_USERORGANIZATION) {
                    $_ticketIDJoinList[] = self::GetSearchUserOrganization($_val[2], false);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_USERGROUP) {
                    $_ticketIDJoinList[] = self::GetSearchUserGroup($_val[2], false);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_TICKETNOTES) {
                    $_ticketIDJoinList[] = self::GetSearchTicketNotes($_val[2], false);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_FULLNAME) {
                    $_ticketIDJoinList[] = self::GetSearchFullname($_val[2], false);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_TAG) {
                    $_ticketIDJoinList[] = SWIFT_TagLink::RetrieveLinkIDListOnTagList(SWIFT_TagLink::TYPE_TICKET, array($_val[2]));

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_EMAIL) {
                    $_ticketIDJoinList[] = self::GetSearchEmail($_val[2], false);

                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_OWNER && $_val[2] == '-1') {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQL($_fieldPointer[$_val[0]], $_val[1], $_SWIFT->Staff->GetStaffID());

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1624 "Creation Date" criteria does not work under Filters
                 * SWIFT-1375 "Last Staff Reply" criteria is not working under Advanced Search
                 *
                 */
                } else if ($_val[0] == SWIFT_TicketSearch::TICKETSEARCH_DUE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_RESOLUTIONDUE ||
                    $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_CREATIONDATE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTACTIVITY ||
                    $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTSTAFFREPLY || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_LASTUSERREPLY ||
                    $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_ESCALATEDDATE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_RESOLUTIONDATE ||
                    $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_REOPENDATE || $_val[0] == SWIFT_TicketSearch::TICKETSEARCH_EDITEDDATE) {
                    $_sqlContainer[] = SWIFT_Rules::BuildOneDayDateRange($_fieldPointer[$_val[0]], $_val[1], $_val[2]);

                } else if (substr($_val[0], 0, 13) == SWIFT_TicketSearch::TICKETSEARCH_CUSTOMFIELDS) {
                    $_customFieldID = substr($_val[0], 13);
                    $_ticketIDJoinList[] = SWIFT_CustomFieldSearch::SearchCustomFieldByRules($_customFieldID, $_val[2], $_val[1]);

                } else {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQL($_fieldPointer[$_val[0]], $_val[1], $_val[2]);
                }
            }
        }

        if (count($_ticketIDJoinList))
        {
            $_sqlContainer[] = "tickets.ticketid IN (" . BuildIN(self::ParseTicketJoinIDList($_ticketIDJoinList, $_criteriaOptions)) . ")";
        }

        $_filterJoiner = ' OR ';
        if ($_criteriaOptions == SWIFT_Rules::RULE_MATCHALL)
        {
            $_filterJoiner = ' AND ';
        }

        $_departmentFilter = "tickets.departmentid IN (" . BuildIN($_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS)) . ")";

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1673 "Filters" do not work if we enable the sorting for user organization under Views settings
         *
         * Comments: Added the three table joins and the fields
         */

        $_searchTicketIDList = array();
        if (count($_sqlContainer)) {
            $_SWIFT->Database->QueryLimit("SELECT tickets.ticketid FROM " . TABLE_PREFIX . "tickets AS tickets
                LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
                LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
                LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
                WHERE (" . $_departmentFilter . ") AND (" . implode($_filterJoiner, $_sqlContainer) . ')' . "
                " . $_extendSQL . "
                ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], $_SWIFT->Settings->Get('t_resultlimit'));
            while ($_SWIFT->Database->NextRecord()) {
                $_searchTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_searchTicketIDList;
    }

    /**
     * Parse the Ticket Joins based on criteria options
     *
     * @author Varun Shoor
     * @param array $_ticketIDJoinList
     * @param mixed $_criteriaOptions
     * @return array Ticket ID List
     */
    protected static function ParseTicketJoinIDList($_ticketIDJoinList, $_criteriaOptions)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalTicketIDList = array();

        // AND
        if ($_criteriaOptions == SWIFT_Rules::RULE_MATCHALL)
        {
            $_levelCount = count($_ticketIDJoinList);
            $_ticketIDCounter = array();
            foreach ($_ticketIDJoinList as $_ticketIDList)
            {
                foreach ($_ticketIDList as $_ticketID)
                {
                    if (!isset($_ticketIDCounter[$_ticketID]))
                    {
                        $_ticketIDCounter[$_ticketID] = 0;
                    }

                    $_ticketIDCounter[$_ticketID]++;
                }
            }

            foreach ($_ticketIDCounter as $_ticketID => $_hitCount)
            {
                if ($_hitCount >= $_levelCount && !in_array($_ticketID, $_finalTicketIDList))
                {
                    $_finalTicketIDList[] = $_ticketID;
                }
            }

        // OR
        } else {
            foreach ($_ticketIDJoinList as $_ticketIDList)
            {
                $_finalTicketIDList = array_merge($_finalTicketIDList, $_ticketIDList);
            }
        }

        return $_finalTicketIDList;
    }

    /**
     * Retrieve results based on: Message
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param array $_ticketIDList
     * @return array Ticket ID List
     * @throws SWIFT_Exception
     */
    protected static function GetSearchMessage($_searchQuery, $_ticketIDList = array())
    {
        $_SWIFT              = SWIFT::GetInstance();
        $_SearchEngine       = new SWIFT_SearchEngine();
        $_searchEngineResult = $_SearchEngine->Find($_searchQuery, SWIFT_SearchEngine::TYPE_TICKET, $_ticketIDList);

        $_excludeTicketPostIDList = $_finalTicketIDList = array();

        // Now don't forget, if "show thirdparty post" is disable, prepare extended where clause to pull out all thirdparty ticket posts
        $_whereExtended = '';
        if ($_SWIFT->Settings->Get('t_cthirdparty') == '0') {
            $_whereExtended = ' OR creator = '. SWIFT_TicketPost::CREATOR_THIRDPARTY . ' OR isthirdparty = 1';
        }

        // Get all ticketpostid which actually are thirdparty or private
        $_SWIFT->Database->Query("SELECT ticketpostid FROM " . TABLE_PREFIX . "ticketposts
                                  WHERE (ticketid IN (" . BuildIN($_ticketIDList, true) . ")
                                    AND (isprivate = 1 " . $_whereExtended .'))');
        while ($_SWIFT->Database->NextRecord()) {
            $_excludeTicketPostIDList[] =  $_SWIFT->Database->Record['ticketpostid'];
        }

        // Now exclude thirdparty and private ticketpost
        if (_is_array($_searchEngineResult)) {
            foreach ($_searchEngineResult as $_searchContainer) {
                if (!in_array($_searchContainer['subobjid'], $_excludeTicketPostIDList)) {
                    $_finalTicketIDList[$_searchContainer['objid']] = $_searchContainer['objid'];
                }
            }
        }

        return $_finalTicketIDList;
    }

    /**
     * Retrieve results based on: Message (SQL Like)
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @return array Ticket ID List
     */
    protected static function GetSearchMessageLike($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts WHERE (" . BuildSQLSearch('contents', $_searchQuery) . ") ORDER BY ticketid DESC", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: User
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool $_enforceLimit (OPTIONAL)
     * @return array Ticket ID List
     */
    public static function GetSearchUser($_searchQuery, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_ticketIDList = $_userIDList = $_userEmailList = array();

        // First get all the user ids that match
        $_SWIFT->Database->QueryLimit("SELECT userid FROM " . TABLE_PREFIX . "users WHERE (" . BuildSQLSearch('fullname', $_searchQuery) . ") OR (" . BuildSQLSearch('phone', $_searchQuery) . ")", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            $_userIDList[] = $_SWIFT->Database->Record['userid'];
        }

        // Search user emails
        $_SWIFT->Database->QueryLimit("SELECT linktypeid FROM " . TABLE_PREFIX . "useremails WHERE (" . BuildSQLSearch('email', $_searchQuery) . ") AND linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            $_userIDList[] = $_SWIFT->Database->Record['linktypeid'];
        }

        if ( _is_array($_userIDList) )
        {
            // Retrieve all user emails
            $_SWIFT->Database->QueryLimit("SELECT email FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_userEmailList[] = $_SWIFT->Database->Record['email'];
            }

            // Now get all tickets by the given user id
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE userid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            // Now get all tickets by the given emails
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE email IN (" . BuildIN($_userEmailList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList)) {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }

            // Dont forget to search ticket posts!
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts WHERE email IN (" . BuildIN($_userEmailList) . ") AND creator = '" . SWIFT_TicketPost::CREATOR_USER . "'", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList)) {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: User Organization
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool $_enforceLimit (OPTIONAL)
     * @return array Ticket ID List
     */
    public static function GetSearchUserOrganization($_searchQuery, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_ticketIDList = $_userOrganizationIDList = $_userIDList = $_userEmailList = array();

        // Get User Organization
        $_SWIFT->Database->QueryLimit("SELECT userorganizationid FROM " . TABLE_PREFIX . "userorganizations WHERE (" . BuildSQLSearch('organizationname', $_searchQuery) . ")", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            $_userOrganizationIDList[] = $_SWIFT->Database->Record['userorganizationid'];
        }

        /*
         * BUG FIX - Mahesh Salaria
         *
         * SWIFT-571 Organisation search returning irrelevant results
         *
         * Comments: Made it proceed only if there is a organization returned, otherwise it would load all users with organizationid = '0'
         */

        if (_is_array($_userOrganizationIDList)) {

            // Now get all the users under the searched organizations
            $_SWIFT->Database->QueryLimit("SELECT userid FROM " . TABLE_PREFIX . "users WHERE userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_userIDList[] = $_SWIFT->Database->Record['userid'];
            }

            // Retrieve all user emails
            $_SWIFT->Database->QueryLimit("SELECT email FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_userEmailList[] = $_SWIFT->Database->Record['email'];
            }

            // Now get all tickets by the given user id
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE userid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            // Now get all tickets by the given emails
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE email IN (" . BuildIN($_userEmailList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            // Dont forget to search ticket posts!
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts WHERE email IN (" . BuildIN($_userEmailList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: User Group
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool $_enforceLimit (OPTIONAL)
     * @return array Ticket ID List
     */
    public static function GetSearchUserGroup($_searchQuery, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_ticketIDList = $_userIDList = $_userEmailList = array();

        // Retrieve all users under the given user group
        $_SWIFT->Database->QueryLimit("SELECT userid FROM " . TABLE_PREFIX . "users WHERE usergroupid = '" . ($_searchQuery) . "'", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['userid'], $_userIDList))
            {
                $_userIDList[] = $_SWIFT->Database->Record['userid'];
            }
        }

        if ( _is_array($_userIDList) )
        {
            // Retrieve all user emails
            $_SWIFT->Database->QueryLimit("SELECT email FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                if (!in_array($_SWIFT->Database->Record['email'], $_userEmailList))
                {
                    $_userEmailList[] = $_SWIFT->Database->Record['email'];
                }
            }

            // Now get all tickets by the given user id
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE userid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
                {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }

            // Now get all tickets by the given emails
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE email IN (" . BuildIN($_userEmailList) . ")", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
                {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }

            // Dont forget to search ticket posts!
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts WHERE email IN (" . BuildIN($_userEmailList) . ") AND creator = '" . SWIFT_TicketPost::CREATOR_USER . "'", $_limitCount);
            while ($_SWIFT->Database->NextRecord())
            {
                if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
                {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: Ticket Notes
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool $_enforceLimit (OPTIONAL)
     * @return array Ticket ID List
     */
    public static function GetSearchTicketNotes($_searchQuery, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_ticketIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT linktypeid FROM " . TABLE_PREFIX . "ticketnotes WHERE linktype = '" . (SWIFT_TicketNote::LINKTYPE_TICKET) . "' AND (" . BuildSQLSearch('note', $_searchQuery) . ")", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['linktypeid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['linktypeid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: Fullname
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool $_enforceLimit (OPTIONAL)
     * @return array Ticket ID List
     */
    public static function GetSearchFullname($_searchQuery, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_ticketIDList = array();

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE (" . BuildSQLSearch('fullname', $_searchQuery, false, false) . ")", $_enforceLimit);
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts WHERE (" . BuildSQLSearch('fullname', $_searchQuery, false, false) . ")", $_enforceLimit);
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: Email
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param bool $_enforceLimit (OPTIONAL)
     * @return array Ticket ID List
     */
    public static function GetSearchEmail($_searchQuery, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_ticketIDList = array();

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE (" . BuildSQLSearch('email', $_searchQuery) . ")", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts WHERE (" . BuildSQLSearch('email', $_searchQuery) . ") OR (" . BuildSQLSearch('emailto', $_searchQuery) . ")", $_limitCount);
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve results based on: Email
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @return array Ticket ID List
     */
    public static function GetSearchCreatorEmail($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = array();

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE (" . BuildSQLSearch('email', $_searchQuery) . ")", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        return $_ticketIDList;
    }

    /**
     * Search on Tags
     *
     * @author Varun Shoor
     * @param string $_searchQuery
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetSearchTags($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_searchQuery = mb_strtolower(trim($_searchQuery));
        $_stopData = array("#\s+#s", "#(\r\n|\r|\n)#s", "/[^a-zA-Z0-9\-\_\s\x80-\xff\&\#;]/"); // POSIX Regexp clause to strip white spaces, words containing asterisks, new lines and all symbols
        $_replaceSpacePreg = array(" ", " ", ""); // replace above clauses with a space, a space or emptiness, respectively

        $_cleanedQuery = preg_replace($_stopData, $_replaceSpacePreg, $_searchQuery);

        $_tagChunks = explode(' ', $_cleanedQuery);
        if (!count($_tagChunks)) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $_tagChunks = array($_cleanedQuery);
        }
        // @codeCoverageIgnoreEnd

        $_tagIDList = array();
        $_SWIFT->Database->Query("SELECT tagid FROM " . TABLE_PREFIX . "tags WHERE tagname IN (" . BuildIN($_tagChunks) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagIDList[] = $_SWIFT->Database->Record['tagid'];
        }

        $_ticketIDList = SWIFT_TagLink::RetrieveLinkIDListOnTagList(SWIFT_TagLink::TYPE_TICKET, $_tagIDList);
        if (!_is_array($_ticketIDList)) {
            return array();
        }

        return $_ticketIDList;
    }

    /**
     * Support Center Search
     *
     * @author Mahesh Salaria
     *
     * @param string $_searchQuery Search Query
     *
     * @return array Search result container on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SupportCenterSearch($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_StringHighlighter     = new SWIFT_StringHighlighter();
        $_lastTicketID          = 0;
        $_maxResults            = $_SWIFT->Settings->Get('g_maxsearchresults');
        $_searchResultContainer = array();

        do {
            $_userTicketIDList           = $_ticketContainer = array();
            $_ticketObjectContainer      = SWIFT_Ticket::RetrieveSCTicketsOnUser($_SWIFT->User, 'ticketid', 'DESC', 0, $_maxResults, false, $_lastTicketID);
            $_ticketObjectContainerCount = count($_ticketObjectContainer);

            foreach ($_ticketObjectContainer as $_ticketID => $_Ticket) {
                $_userTicketIDList[]                     = $_ticketID;
                $_ticketContainer[$_ticketID]['subject'] = htmlspecialchars($_SWIFT->Emoji->Decode($_Ticket->Get('subject')));

                /**
                 * BUG FIX - Saloni Dhall
                 *
                 * SWIFT-2276 : Not able to search ticket with numeric number at support center
                 */
                $_ticketContainer[$_ticketID]['displayticketid'] = $_Ticket->GetTicketDisplayID();
                $_ticketContainer[$_ticketID]['lastactivity']    = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_Ticket->Get('lastactivity'));
                $_lastTicketID                                   = $_ticketID;
            }
            unset($_ticketObjectContainer);

            // Now search with the search engine
            $_ticketSearchIDList = array_merge(self::GetSearchMessage($_searchQuery, $_userTicketIDList), self::SearchTicketID($_searchQuery, false, $_userTicketIDList));

            foreach ($_ticketSearchIDList as $_ticketID) {
                if (isset($_ticketContainer[$_ticketID])) {
                    $_ticketPostContent = '';

                    $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

                    foreach ($_Ticket->GetTicketPosts() as $_ticketPost) {
                        /* Bug Fix : Saloni Dhall
                            *
                            * SWIFT-4037 : Third party replies are shown in the search results of client support center
                            *
                            * Comments : Exclude the third party replies from displaying in search results post contents
                            */
                        if (($_SWIFT->Settings->Get('t_cthirdparty') == '0' && ($_ticketPost->creator == SWIFT_TicketPost::CREATOR_THIRDPARTY || $_ticketPost->isthirdparty == '1'))) {
                            continue;
                        }

                        if (empty($_ticketPostObjectData['isprivate'])) {
                            $_HTML2Text         = new SWIFT_StringHTMLToText();
                            $_ticketPostContent = $_ticketPostContent . ' ' . $_HTML2Text->Convert($_ticketPost->contents);
                        }
                    }

                    $_searchTicketPostContent = $_StringHighlighter->GetHighlightedRange($_ticketPostContent, $_searchQuery, $_SWIFT->Settings->Get('kb_climit'));

                    if (empty($_searchTicketPostContent)) {
                        $_searchTicketPostContent = $_ticketPostContent;
                    } else {
                        $_searchTicketPostContent = $_searchTicketPostContent[0];
                    }

                    $_highlightedSubject = $_StringHighlighter->GetHighlightedRange($_ticketContainer[$_ticketID]['subject'], $_searchQuery, 80);

                    if (empty($_highlightedSubject)) {
                        $_highlightedSubject = $_ticketContainer[$_ticketID]['subject'];
                    } else {
                        $_highlightedSubject = $_highlightedSubject[0];
                    }

                    $_searchResultContainer[$_ticketID]['subject']      = $_ticketContainer[$_ticketID]['displayticketid'] . ': ' . $_highlightedSubject;
                    $_searchResultContainer[$_ticketID]['contentstext'] = sprintf($_SWIFT->Language->Get('tsupdate'), $_ticketContainer[$_ticketID]['lastactivity']) . ' - ' . $_SWIFT->Emoji->Decode($_searchTicketPostContent);
                    $_searchResultContainer[$_ticketID]['cssprefix']    = 'ticketsearch';
                    $_searchResultContainer[$_ticketID]['url']          = SWIFT::Get('basename') . $_SWIFT->Template->GetTemplateGroupPrefix() . '/Tickets/Ticket/View/' . $_ticketID;
                }
                if (count($_searchResultContainer) >= $_maxResults) {
                    return $_searchResultContainer;
                }
            }
// @codeCoverageIgnoreStart
// this code will never be executed
        } while ((count($_searchResultContainer) <= $_maxResults) && ($_ticketObjectContainerCount > 0));

        return $_searchResultContainer;
// @codeCoverageIgnoreEnd
    }
}
