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

namespace Tickets\Library\StaffAPI;

use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_DataID;
use SWIFT_DataStore;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Library;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Filter\SWIFT_TicketFilter;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Models\User\SWIFT_UserNoteManager;
use SWIFT_XML;
use Tickets\Library\Search\SWIFT_TicketSearchManager;

/**
 * The Ticket StaffAPI Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketStaffAPIManager extends SWIFT_Library
{

    static protected $_sortByFieldList = array('department', 'status', 'priority', 'type', 'flagtype', 'due', 'creationdate', 'lastactivity');

    /**
     * Dispatch the additional data for login form
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_SWIFT_XMLObject
     * @param bool $_wantMacros (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DispatchLogin(SWIFT_XML $_SWIFT_XMLObject, $_wantMacros = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_XMLObject instanceof SWIFT_XML || !$_SWIFT_XMLObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_staffAssignedDepartmentIDList = (array) $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);
        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);

        /**
         * ---------------------------------------------
         * Ticket Status
         * ---------------------------------------------
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY ticketstatusid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_canChangeStatus = false;
            $_staffGroupIDList = SWIFT_StaffGroupLink::RetrieveListFromCache(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_SWIFT->Database->Record['ticketstatusid']);
            if ($_SWIFT->Database->Record['staffvisibilitycustom'] == '0'
                    || ($_SWIFT->Database->Record['staffvisibilitycustom'] == '1' && in_array($_SWIFT->Staff->GetProperty('staffgroupid'), $_staffGroupIDList))) {
                $_canChangeStatus = true;
            }

            $_isNew = false;
            if (isset($_ticketCountCache['ticketstatus'][$_SWIFT->Database->Record['ticketstatusid']])
                    && $_ticketCountCache['ticketstatus'][$_SWIFT->Database->Record['ticketstatusid']]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                $_isNew = true;
            }

            $_ticketCount = 0;
            if (isset($_ticketCountCache['ticketstatus'][$_SWIFT->Database->Record['ticketstatusid']]['totalunresolveditems'])) {
                $_ticketCount = $_ticketCountCache['ticketstatus'][$_SWIFT->Database->Record['ticketstatusid']]['totalunresolveditems'];
            }

            $_attributes = array('id' => $_SWIFT->Database->Record['ticketstatusid'], 'title' => $_SWIFT->Database->Record['title'], 'departmentid' => $_SWIFT->Database->Record['departmentid'],
                'fgcolor' => $_SWIFT->Database->Record['statuscolor'], 'bgcolor' => $_SWIFT->Database->Record['statusbgcolor'], 'displayorder' => $_SWIFT->Database->Record['displayorder'],
                'markasresolved' => $_SWIFT->Database->Record['markasresolved'], 'canchangestatus' => ($_canChangeStatus),
                'iconurl' => str_replace('{$themepath}', SWIFT::Get('themepath') . 'images/', $_SWIFT->Database->Record['displayicon']), 'new' => ($_isNew), 'ticketcount' => ($_ticketCount));

            $_SWIFT_XMLObject->AddTag('ticketstatus', '', $_attributes);
        }

        /**
         * ---------------------------------------------
         * Ticket Types
         * ---------------------------------------------
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY tickettypeid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_attributes = array('id' => $_SWIFT->Database->Record['tickettypeid'], 'title' => $_SWIFT->Database->Record['title'], 'departmentid' => $_SWIFT->Database->Record['departmentid'],
                'displayorder' => $_SWIFT->Database->Record['displayorder'], 'iconurl' => str_replace('{$themepath}', SWIFT::Get('themepath') . 'images/', $_SWIFT->Database->Record['displayicon']));

            $_SWIFT_XMLObject->AddTag('tickettype', '', $_attributes);
        }

        /**
         * ---------------------------------------------
         * Ticket Priorities
         * ---------------------------------------------
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY priorityid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_attributes = array('id' => $_SWIFT->Database->Record['priorityid'], 'title' => $_SWIFT->Database->Record['title'], 'fgcolor' => $_SWIFT->Database->Record['frcolorcode'],
                'bgcolor' => $_SWIFT->Database->Record['bgcolorcode'], 'displayorder' => $_SWIFT->Database->Record['displayorder'],
                'iconurl' => '');

            $_SWIFT_XMLObject->AddTag('ticketpriority', '', $_attributes);
        }

        /**
         * ---------------------------------------------
         * Staff
         * ---------------------------------------------
         */
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');
        foreach ($_staffCache as $_staffID => $_staff) {
            $_assignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID);

            $_attributes = array('id' => $_staff['staffid'], 'firstname' => $_staff['firstname'], 'lastname' => $_staff['lastname'], 'fullname' => $_staff['fullname'], 'username' => $_staff['username'],
                'designation' => $_staff['designation'], 'email' => $_staff['email'], 'mobilenumber' => $_staff['mobilenumber'], 'lastvisit' => $_staff['lastvisit'], 'timezone' => $_staff['timezonephp'],
                'departments' => implode(',', $_assignedDepartmentIDList));

            $_SWIFT_XMLObject->AddTag('staff', '', $_attributes);
        }

        /**
         * ---------------------------------------------
         * Ticket Filters
         * ---------------------------------------------
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilters
            WHERE filtertype = '" . SWIFT_TicketFilter::TYPE_PUBLIC . "' OR (filtertype = '" . SWIFT_TicketFilter::TYPE_PRIVATE . "' AND staffid = '" . $_SWIFT->Staff->GetStaffID() . "') ORDER BY ticketfilterid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            // Not visible to the active staff group?
            if ($_SWIFT->Database->Record['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC && $_SWIFT->Database->Record['restrictstaffgroupid'] != '0' && $_SWIFT->Database->Record['restrictstaffgroupid'] != $_SWIFT->Staff->GetProperty('staffgroupid')) {
                continue;
            }

            $_attributes = array('id' => $_SWIFT->Database->Record['ticketfilterid'], 'title' => $_SWIFT->Database->Record['title']);

            $_SWIFT_XMLObject->AddTag('ticketfilter', '', $_attributes);
        }


        /**
         * ---------------------------------------------
         * Email Queues
         * ---------------------------------------------
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY emailqueueid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_attributes = array('id' => $_SWIFT->Database->Record['emailqueueid'], 'email' => $_SWIFT->Database->Record['email'], 'departmentid' => $_SWIFT->Database->Record['departmentid']);
            $_SWIFT_XMLObject->AddTag('emailqueue', '', $_attributes);
        }


        if ($_wantMacros == true) {
            self::ProcessMacroNode($_SWIFT_XMLObject);
        }

        return true;
    }

    /**
     * Process the Macro Node
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_SWIFT_XMLObject
     * @param int $_parentMacroCategoryID (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function ProcessMacroNode(SWIFT_XML $_SWIFT_XMLObject, $_parentMacroCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_parentMacroCategoryContainer = $_SWIFT->Database->QueryFetch("SELECT macrocategories.* FROM " . TABLE_PREFIX . "macrocategories AS macrocategories
            WHERE macrocategories.macrocategoryid = '" . ($_parentMacroCategoryID) . "'
            AND (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PUBLIC . "' OR (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PRIVATE . "' AND macrocategories.staffid = '" . $_SWIFT->Staff->GetStaffID() . "'))");

        if ($_parentMacroCategoryID != 0 && (!isset($_parentMacroCategoryContainer['macrocategoryid']) || empty($_parentMacroCategoryContainer['macrocategoryid']))) {
            return false;
        }

        if (isset($_parentMacroCategoryContainer['macrocategoryid']) && $_parentMacroCategoryContainer['restrictstaffgroupid'] != '0' && $_parentMacroCategoryContainer['restrictstaffgroupid'] != $_SWIFT->Staff->GetProperty('staffgroupid')) {
            return false;
        }

        // Get sub categories
        $_subMacroCategoryContainer = array();
        $_SWIFT->Database->Query("SELECT macrocategories.* FROM " . TABLE_PREFIX . "macrocategories AS macrocategories
            WHERE macrocategories.parentcategoryid = '" .  ($_parentMacroCategoryID) . "'
            AND (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PUBLIC . "' OR (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PRIVATE . "' AND macrocategories.staffid = '" . $_SWIFT->Staff->GetStaffID() . "'))");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['restrictstaffgroupid'] != '0' && $_SWIFT->Database->Record['restrictstaffgroupid'] != $_SWIFT->Staff->GetProperty('staffgroupid')) {
                continue;
            }

            $_subMacroCategoryContainer[$_SWIFT->Database->Record['macrocategoryid']] = $_SWIFT->Database->Record;
        }


        // Get macro replies
        $_macroReplyContainer = array();
        $_SWIFT->Database->Query("SELECT macroreplies.*, macroreplydata.contents AS contents, macroreplydata.tagcontents AS tagcontents FROM " . TABLE_PREFIX . "macroreplies AS macroreplies
            LEFT JOIN " . TABLE_PREFIX . "macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
            WHERE macroreplies.macrocategoryid = '" . ($_parentMacroCategoryID) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_macroReplyContainer[$_SWIFT->Database->Record['macroreplyid']] = $_SWIFT->Database->Record;
        }

        $_parentCategoryTitle = '';
        if (isset($_parentMacroCategoryContainer['title'])) {
            $_parentCategoryTitle = $_parentMacroCategoryContainer['title'];
        }

        // Add as parent node?
        if (count($_macroReplyContainer) || count($_subMacroCategoryContainer)) {
            $_SWIFT_XMLObject->AddParentTag('macrocategory', array('id' => $_parentMacroCategoryID, 'title' => $_parentCategoryTitle));

            // Process Sub Categories
            foreach ($_subMacroCategoryContainer as $_subMacroCategoryID => $_subMacroCategory) {
                self::ProcessMacroNode($_SWIFT_XMLObject, $_subMacroCategoryID);
            }

            // Process Replies
            foreach ($_macroReplyContainer as $_macroReplyID => $_macroReply) {
                $_SWIFT_XMLObject->AddTag('macro', $_macroReply['contents'], array('id' => $_macroReply['macroreplyid'], 'creationdate' => $_macroReply['dateline'], 'totalhits' => $_macroReply['totalhits'],
                    'lastusage' => $_macroReply['lastusage'], 'departmentid' => $_macroReply['departmentid'], 'ownerstaffid' => $_macroReply['ownerstaffid'], 'tickettypeid' => $_macroReply['tickettypeid'],
                    'ticketstatusid' => $_macroReply['ticketstatusid'], 'priorityid' => $_macroReply['priorityid'], 'tags' => implode(' ', mb_unserialize($_macroReply['tagcontents'])), 'subject' => $_macroReply['subject']));
            }

            $_SWIFT_XMLObject->EndParentTag('macrocategory');
        } else if (isset($_parentMacroCategoryContainer['macrocategoryid']) && !empty($_parentMacroCategoryContainer['macrocategoryid'])) {
            $_SWIFT_XMLObject->AddTag('macrocategory', '', array('id' => $_parentMacroCategoryContainer['macrocategoryid'], 'title' => $_parentMacroCategoryContainer['title']));
        }

        return true;
    }

    /**
     * Dispatch Ticket List
     *
     * @author Varun Shoor
     * @param string $_incomingDepartmentIDList The CSV Department ID Values
     * @param string $_incomingTicketStatusIDList The CSV Ticket Status ID Values
     * @param string $_incomingOwnerStaffIDList The CSV Owner Staff ID Values
     * @param string $_incomingTicketFilterID The Ticket Filter ID
     * @param string $_incomingTicketIDList The Incoming Ticket ID List
     * @param string|bool $_sortBy (OPTIONAL)
     * @param string|bool $_sortOrder (OPTIONAL)
     * @param int|bool $_start (OPTIONAL)
     * @param int|bool $_limit (OPTIONAL)
     * @param bool $_ticketInfo (OPTIONAL) Whether to dispatch complete ticket information
     * @param bool $_extendedInfo (OPTIONAL) Whether to dispatch extended information
     * @param bool $_wantAttachmentData (OPTIONAL)
     * @param bool $_wantPostsOnly (OPTIONAL)
     * @param int $_postStart (OPTIONAL) The Post Starting Offset
     * @param int $_postLimit (OPTIONAL) The Limit of Posts
     * @param string $_postSortOrder (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DispatchList($_incomingDepartmentIDList, $_incomingTicketStatusIDList, $_incomingOwnerStaffIDList, $_incomingTicketFilterID, $_incomingTicketIDList, $_sortBy = false, $_sortOrder = false, $_start = false, $_limit = false, $_ticketInfo = true, $_extendedInfo = false, $_wantAttachmentData = false, $_wantPostsOnly = false, $_postStart = 0, $_postLimit = 1000, $_postSortOrder = 'asc')
    {
        $_SWIFT = SWIFT::GetInstance();

        // Calculate the final limit value
        $_finalLimitValue = 1000;
        if ($_limit !== false && is_numeric($_limit) && $_limit > 0 && $_limit <= 1000) {
            $_finalLimitValue = ($_limit);
        }

        // Calculate the start at SQL extended statement
        $_extendedStartAt = '';
        $_finalStartAtValue = false;
        if ($_start !== false && is_numeric($_start)) {
            $_extendedStartAt = " AND ticketid >= '" . ($_start) . "'";

            $_finalStartAtValue = ($_start);
        } else {
            $_start = 0;
        }

        // Calculate the sort statement
        $_extendedOrderBy = '';
        $_finalSortBy = 'tickets.ticketid';
        $_finalSortOrder = 'ASC';
        if ($_sortBy !== false && in_array($_sortBy, self::GetValidSortByValues())) {
            $_finalSortBy = self::GetSortByFieldOnValue($_sortBy);
        }

        if ($_sortOrder !== false && (strtolower($_sortOrder) == 'asc' || strtolower($_sortOrder) == 'desc')) {
            $_finalSortOrder = strtoupper(Clean($_sortOrder));
        }

        $_extendedOrderBy = ' ORDER BY ' . $_finalSortBy . ' ' . $_finalSortOrder;

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');
        $_ticketFilterCache =  $_SWIFT->Cache->Get('ticketfiltercache');
        $_ticketWorkflowCache = (array) $_SWIFT->Cache->Get('ticketworkflowcache');

        if ($_incomingDepartmentIDList == '') {
            $_departmentIDList = array();
        } else {
            $_departmentIDList = self::RetrieveIDListFromCSV($_incomingDepartmentIDList);
        }
        $_ticketStatusIDList = self::RetrieveIDListFromCSV($_incomingTicketStatusIDList);

        $_ownerStaffIDList = array();
        if ($_incomingOwnerStaffIDList != '') {
            $_ownerStaffIDList = self::RetrieveIDListFromCSV($_incomingOwnerStaffIDList);
        }

        $_customTicketIDList = array();
        if (!empty($_incomingTicketIDList)) {
            $_customTicketIDList = self::RetrieveIDListFromCSV($_incomingTicketIDList);
        }

        $_finalDepartmentIDList = $_finalTicketStatusIDList = $_finalOwnerStaffIDList = array();

        $_finalTicketFilterID = 0;

        /**
         * ---------------------------------------------
         * Process Departments
         * ---------------------------------------------
         */
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        foreach ($_departmentIDList as $_departmentID) {
            if (in_array($_departmentID, $_assignedDepartmentIDList) || $_departmentID == '0') {
                $_finalDepartmentIDList[] = $_departmentID;
            }
        }

        // No departments received? We use up all the assigned ones
        if (!count($_finalDepartmentIDList)) {
            $_finalDepartmentIDList = $_assignedDepartmentIDList;
        }

        /**
         * ---------------------------------------------
         * Process Ticket Status
         * ---------------------------------------------
         */
        foreach ($_ticketStatusIDList as $_ticketStatusID) {
            if (isset($_ticketStatusCache[$_ticketStatusID])
                    && ($_ticketStatusCache[$_ticketStatusID]['departmentid'] == '0' || in_array($_ticketStatusCache[$_ticketStatusID]['departmentid'], $_finalDepartmentIDList))) {
                $_finalTicketStatusIDList[] = $_ticketStatusID;
            }
        }

        // If no ticket status'es were received, we use the unresolved ones
        if (!count($_finalTicketStatusIDList)) {
            foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
                if (($_ticketStatus['departmentid'] == '0' || in_array($_ticketStatus['departmentid'], $_finalDepartmentIDList))
                        && $_ticketStatus['markasresolved'] == '0') {
                    $_finalTicketStatusIDList[] = $_ticketStatusID;
                }
            }
        }

        /**
         * ---------------------------------------------
         * Process Owner Staff
         * ---------------------------------------------
         */
        $_finalOwnerStaffIDList = $_ownerStaffIDList;

        /**
         * ---------------------------------------------
         * Process Ticket Filters
         * ---------------------------------------------
         */
        if (!empty($_incomingTicketFilterID)) {
            if (isset($_ticketFilterCache[$_incomingTicketFilterID])
                    && ($_ticketFilterCache[$_incomingTicketFilterID]['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC
                    || ($_ticketFilterCache[$_incomingTicketFilterID]['filtertype'] == SWIFT_TicketFilter::TYPE_PRIVATE && $_ticketFilterCache[$_incomingTicketFilterID]['staffid'] == $_SWIFT->Staff->GetStaffID()))) {
                $_finalTicketFilterID = $_incomingTicketFilterID;
            }
        }

        /**
         * ---------------------------------------------
         * Ticket Processing
         * ---------------------------------------------
         */
        $_ticketIDList = array();

        // Do we have to process a ticket filter?
        if (count($_customTicketIDList)) {
            $_ticketIDList = $_customTicketIDList;
        } else {
            if (!empty($_finalTicketFilterID)) {
                $_SWIFT_TicketFilterObject = new SWIFT_TicketFilter(new SWIFT_DataID($_finalTicketFilterID));
                if (!$_SWIFT_TicketFilterObject instanceof SWIFT_TicketFilter || !$_SWIFT_TicketFilterObject->GetIsClassLoaded()) {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                $_SWIFT_TicketFilterObject->UpdateLastActivity();

                $_ruleCriteria = array();
                $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterid = '" .
                        ($_finalTicketFilterID) . "' ORDER BY ticketfilterfieldid ASC");
                while ($_SWIFT->Database->NextRecord()) {
                    $_ruleCriteria[] = array($_SWIFT->Database->Record['fieldtitle'], $_SWIFT->Database->Record['fieldoper'], $_SWIFT->Database->Record['fieldvalue']);
                }

                $_baseTicketIDList = SWIFT_TicketSearchManager::SearchRules($_ruleCriteria, $_SWIFT_TicketFilterObject->GetProperty('criteriaoptions'), $_SWIFT->Staff);

                $_SWIFT->Database->QueryLimit("SELECT tickets.ticketid, ticketpriorities.displayorder AS prioritydisplayorder FROM " . TABLE_PREFIX . "tickets AS tickets
                    LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
                    WHERE tickets.ticketid IN (" . BuildIN($_baseTicketIDList) . ")" . $_extendedOrderBy, $_finalLimitValue, $_start);
                while ($_SWIFT->Database->NextRecord()) {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }


                // Process using Department ID List and other variables
            } else {
                $_sqlQueryList = array();

                $_sqlQueryList[] = "tickets.departmentid IN (" . BuildIn($_finalDepartmentIDList) . ")";
                $_sqlQueryList[] = "tickets.ticketstatusid IN (" . BuildIn($_finalTicketStatusIDList) . ")";

                if (count($_finalOwnerStaffIDList)) {
                    $_sqlQueryList[] = "tickets.ownerstaffid IN (" . BuildIN($_finalOwnerStaffIDList) . ")";
                }

                $_SWIFT->Database->QueryLimit("SELECT tickets.ticketid, ticketpriorities.displayorder AS prioritydisplayorder FROM " . TABLE_PREFIX . "tickets AS tickets
                    LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
                    WHERE " . implode(" AND ", $_sqlQueryList) . $_extendedOrderBy, $_finalLimitValue, $_start);
                while ($_SWIFT->Database->NextRecord()) {
                    $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                }
            }
        }

        self::DispatchOnTicketIDList($_ticketIDList, $_start, $_extendedStartAt, $_extendedOrderBy, $_ticketInfo, $_extendedInfo, $_wantAttachmentData, $_finalLimitValue, array(), '', $_wantPostsOnly, $_postStart, $_postLimit, array(), $_postSortOrder);

        return true;
    }

    /**
     * Dispatch the Data on Ticket ID List
     *
     * @author Varun Shoor
     * @param array $_ticketIDList
     * @param int $_startOffset (OPTIONAL)
     * @param string $_extendedStartAt (OPTIONAL)
     * @param string $_extendedOrderBy (OPTIONAL)
     * @param bool $_ticketInfo (OPTIONAL)
     * @param bool $_extendedInfo (OPTIONAL)
     * @param bool $_wantAttachmentData (OPTIONAL)
     * @param int $_finalLimitValue (OPTIONAL)
     * @param array $_staffAPIIDMap (OPTIONAL)
     * @param string $_errorInfo (OPTIONAL)
     * @param bool $_wantPostsOnly (OPTIONAL)
     * @param int $_postStart (OPTIONAL) The Post Starting Offset
     * @param int $_postLimit (OPTIONAL) The Limit of Posts
     * @param array $_extendedTicketMapper (OPTIONAL) The Extended Map of Ticket Options
     * @param string $_postSortOrder (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DispatchOnTicketIDList($_ticketIDList, $_startOffset = 0, $_extendedStartAt = '', $_extendedOrderBy = '', $_ticketInfo = true, $_extendedInfo = true, $_wantAttachmentData = false, $_finalLimitValue = 1000, $_staffAPIIDMap = array(), $_errorInfo = '', $_wantPostsOnly = false, $_postStart = 0, $_postLimit = 1000, $_extendedTicketMapper = array(), $_postSortOrder = 'asc')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');
        $_slaPlanCache = (array) $_SWIFT->Cache->Get('slaplancache');
        $_ticketFilterCache = (array) $_SWIFT->Cache->Get('ticketfiltercache');
        $_ticketWorkflowCache = (array) $_SWIFT->Cache->Get('ticketworkflowcache');
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();

        $_extendedAssignedDepartmentIDList = $_assignedDepartmentIDList;
        $_extendedAssignedDepartmentIDList[] = 0;

        if (mb_strtolower($_postSortOrder) != 'asc' && mb_strtolower($_postSortOrder) != 'desc') {
            $_postSortOrder = 'asc';
        }

        $_ticketsContainer = $_userIDList = $_userOrganizationMap = array();

        $_SWIFT->Database->Query("SELECT tickets.*, ticketpriorities.displayorder AS prioritydisplayorder FROM " . TABLE_PREFIX . "tickets AS tickets
            LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
            WHERE (tickets.ticketid IN (" . BuildIN($_ticketIDList) . ")) AND tickets.departmentid IN (" . BuildIN($_extendedAssignedDepartmentIDList) . ")" . $_extendedOrderBy);
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketsContainer[$_SWIFT->Database->Record['ticketid']] = $_SWIFT->Database->Record;

            if (!in_array($_SWIFT->Database->Record['userid'], $_userIDList)) {
                $_userIDList[] = $_SWIFT->Database->Record['userid'];
            }
        }

        // Process Users & Organizations
        $_userOrganizationIDList = array();
        $_SWIFT->Database->Query("SELECT users.userid AS userid, users.userorganizationid AS userorganizationid, userorganizations.organizationname AS organizationname FROM " . TABLE_PREFIX . "users AS users
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE users.userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userOrganizationMap[$_SWIFT->Database->Record['userid']] = array(($_SWIFT->Database->Record['userorganizationid']), $_SWIFT->Database->Record['organizationname']);

            $_userOrganizationIDList[] = $_SWIFT->Database->Record['userorganizationid'];
        }

        // Process Tags
        $_tagContainer = $_tagMap = $_tagIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks
            WHERE linktype = '" . SWIFT_TagLink::TYPE_TICKET . "' AND linkid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['tagid'], $_tagIDList)) {
                $_tagIDList[] = $_SWIFT->Database->Record['tagid'];
            }

            if (!isset($_tagMap[$_SWIFT->Database->Record['linkid']])) {
                $_tagMap[$_SWIFT->Database->Record['linkid']] = array();
            }

            $_tagMap[$_SWIFT->Database->Record['linkid']][] = $_SWIFT->Database->Record['tagid'];
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags
            WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagContainer[$_SWIFT->Database->Record['tagid']] = $_SWIFT->Database->Record['tagname'];
        }



        /**
         * ---------------------------------------------
         * BEGIN EXTENDED INFO PROCESSING
         * ---------------------------------------------
         */
        $_ticketTimeTrackContainer = $_ticketWatcherMap = $_ticketNotesContainer = $_ticketWorkflowMap = $_userNotesContainer = $_userOrganizationNotesContainer = $_ticketPostContainer = $_ticketAttachmentContainer = array();
        $_ticketPostMap = $_ticketPostIDList_Attachments = array();

        if (count($_extendedTicketMapper)) {
            $_extendedInfo = true;
        }

        if ($_extendedInfo === true) {
            // Process Ticket Posts
            $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid IN (" . BuildIN($_ticketIDList) . ") ORDER BY ticketpostid " . mb_strtoupper($_postSortOrder), $_postLimit, $_postStart);
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_ticketPostContainer[$_SWIFT->Database->Record['ticketid']])) {
                    $_ticketPostContainer[$_SWIFT->Database->Record['ticketid']] = array();
                }

                if (!isset($_ticketPostMap[$_SWIFT->Database->Record['ticketid']])) {
                    $_ticketPostMap[$_SWIFT->Database->Record['ticketid']] = array();
                }

                $_ticketPostContainer[$_SWIFT->Database->Record['ticketid']][$_SWIFT->Database->Record['ticketpostid']] = $_SWIFT->Database->Record;

                $_ticketPostMap[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record['ticketpostid'];

                if (isset($_ticketsContainer[$_SWIFT->Database->Record['ticketid']]) && $_ticketsContainer[$_SWIFT->Database->Record['ticketid']]['hasattachments'] == '1') {
                    $_ticketPostIDList_Attachments[] = $_SWIFT->Database->Record['ticketpostid'];
                }
            }

            // Process Attachments
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . SWIFT_Attachment::LINKTYPE_TICKETPOST . "' AND linktypeid IN (" . BuildIN($_ticketPostIDList_Attachments) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_ticketAttachmentContainer[$_SWIFT->Database->Record['linktypeid']])) {
                    $_ticketAttachmentContainer[$_SWIFT->Database->Record['linktypeid']] = array();
                }

                $_attachmentContainer = $_SWIFT->Database->Record;

                $_attachmentContainer['contents'] = '';

                if ($_wantAttachmentData == true) {
                    $_SWIFT_AttachmentObject = new SWIFT_Attachment($_SWIFT->Database->Record['attachmentid']);
                    if ($_SWIFT_AttachmentObject instanceof SWIFT_Attachment && $_SWIFT_AttachmentObject->GetIsClassLoaded()) {
                        $_attachmentContainer['contents'] = base64_encode($_SWIFT_AttachmentObject->Get());
                    }
                }

                $_attachmentContainer['storefilepath'] = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_SWIFT->Database->Record['storefilename'];

                $_ticketAttachmentContainer[$_SWIFT->Database->Record['linktypeid']][$_SWIFT->Database->Record['attachmentid']] = $_attachmentContainer;
            }

            // Process Ticket Watchers
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_ticketWatcherMap[$_SWIFT->Database->Record['ticketid']])) {
                    $_ticketWatcherMap[$_SWIFT->Database->Record['ticketid']] = array();
                }

                $_ticketWatcherMap[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record['staffid'];
            }

            // Process Workflows
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkedtables
                WHERE ticketid IN (" . BuildIN($_ticketIDList) . ") AND linktype = '" . SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW . "'");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_ticketWorkflowMap[$_SWIFT->Database->Record['ticketid']])) {
                    $_ticketWorkflowMap[$_SWIFT->Database->Record['ticketid']] = array();
                }

                $_ticketWorkflowMap[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record['linktypeid'];
            }

            // Process Ticket Notes
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketnotes
                WHERE linktype = '" . SWIFT_TicketNote::LINKTYPE_TICKET . "' AND linktypeid IN (" . BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_ticketNotesContainer[$_SWIFT->Database->Record['linktypeid']])) {
                    $_ticketNotesContainer[$_SWIFT->Database->Record['linktypeid']] = array();
                }

                $_ticketNotesContainer[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record;
            }

            // Process User Notes
            $_SWIFT->Database->Query("SELECT usernotes.*, usernotedata.notecontents AS note FROM " . TABLE_PREFIX . "usernotes AS usernotes
                LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid)
                WHERE linktype = '" . SWIFT_UserNoteManager::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_userNotesContainer[$_SWIFT->Database->Record['linktypeid']])) {
                    $_userNotesContainer[$_SWIFT->Database->Record['linktypeid']] = array();
                }

                $_userNotesContainer[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record;
            }

            // Process User Organization Notes
            $_SWIFT->Database->Query("SELECT usernotes.*, usernotedata.notecontents AS note FROM " . TABLE_PREFIX . "usernotes AS usernotes
                LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid)
                WHERE linktype = '" . SWIFT_UserNoteManager::LINKTYPE_ORGANIZATION . "' AND linktypeid IN (" . BuildIN($_userOrganizationIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_userOrganizationNotesContainer[$_SWIFT->Database->Record['linktypeid']])) {
                    $_userOrganizationNotesContainer[$_SWIFT->Database->Record['linktypeid']] = array();
                }

                $_userOrganizationNotesContainer[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record;
            }

            // Process Ticket Time Tracks
            $_SWIFT->Database->Query("SELECT tickettimetracks.*, tickettimetracknotes.notes AS note FROM " . TABLE_PREFIX . "tickettimetracks AS tickettimetracks
                LEFT JOIN " . TABLE_PREFIX . "tickettimetracknotes AS tickettimetracknotes ON (tickettimetracks.tickettimetrackid = tickettimetracknotes.tickettimetrackid)
                WHERE tickettimetracks.ticketid IN (" . BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (!isset($_ticketTimeTrackContainer[$_SWIFT->Database->Record['ticketid']])) {
                    $_ticketTimeTrackContainer[$_SWIFT->Database->Record['ticketid']] = array();
                }

                $_ticketTimeTrackContainer[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record;
            }
        }

        /**
         * ---------------------------------------------
         * END EXTENDED INFO PROCESSING
         * ---------------------------------------------
         */
        /**
         * ---------------------------------------------
         * Begin XML Rendering
         * ---------------------------------------------
         */
        $_SWIFT_XMLObject = new SWIFT_XML();

        $_SWIFT_XMLObject->AddParentTag('kayako_staffapi');

        $_statusCode = 1;
        if (!empty($_errorInfo)) {
            $_statusCode = 0;
        }

        $_SWIFT_XMLObject->AddTag('status', $_statusCode);
        $_SWIFT_XMLObject->AddTag('error', $_errorInfo);

        $_SWIFT_XMLObject->AddTag('count', count($_ticketsContainer));

        $_SWIFT_XMLObject->AddParentTag('tickets');

        foreach ($_ticketsContainer as $_ticketID => $_ticket) {
            /**
             * @var SWIFT_Ticket
             */
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_ticket));

            $_hasExtendedMap = false;
            $_extendedMapOptions = array();
            if (isset($_extendedTicketMapper[$_ticketID])) {
                $_hasExtendedMap = true;
                $_extendedMapOptions = $_extendedTicketMapper[$_ticketID];
            }

            $_userOrganizationName = '';
            $_userOrganizationID = 0;

            if (isset($_userOrganizationMap[$_ticket['userid']])) {
                $_userOrganizationID = $_userOrganizationMap[$_ticket['userid']][0];
                $_userOrganizationName = $_userOrganizationMap[$_ticket['userid']][1];
            }

            $_ticketTags = '';
            if (isset($_tagMap[$_ticketID])) {
                foreach ($_tagMap[$_ticketID] as $_tagID) {
                    if (isset($_tagContainer[$_tagID])) {
                        $_ticketTags .= ' ' . $_tagContainer[$_tagID];
                    }
                }
            }

            $_ticketTags = trim($_ticketTags);

            $_parentTicketAttributeContainer = array('id' => $_ticket['ticketid']);

            if (isset($_staffAPIIDMap[$_ticket['ticketid']])) {
                $_parentTicketAttributeContainer['staffapiid'] = $_staffAPIIDMap[$_ticket['ticketid']];
            }

            $_SWIFT_XMLObject->AddParentTag('ticket', $_parentTicketAttributeContainer);

            if (($_ticketInfo === true && $_wantPostsOnly == false && $_hasExtendedMap == false) || ($_hasExtendedMap == true && $_extendedMapOptions['basicinfo'] == true)) {
                $_SWIFT_XMLObject->AddTag('displayid', $_SWIFT_TicketObject->GetTicketDisplayID());
                $_SWIFT_XMLObject->AddTag('departmentid', $_ticket['departmentid']);
                $_SWIFT_XMLObject->AddTag('departmenttitle', $_ticket['departmenttitle']);
                $_SWIFT_XMLObject->AddTag('statusid', $_ticket['ticketstatusid']);
                $_SWIFT_XMLObject->AddTag('statustitle', $_ticket['ticketstatustitle']);
                $_SWIFT_XMLObject->AddTag('priorityid', $_ticket['priorityid']);
                $_SWIFT_XMLObject->AddTag('prioritytitle', $_ticket['prioritytitle']);
                $_SWIFT_XMLObject->AddTag('flagtype', $_ticket['flagtype']);
                $_SWIFT_XMLObject->AddTag('typeid', $_ticket['tickettypeid']);
                $_SWIFT_XMLObject->AddTag('typetitle', $_ticket['tickettypetitle']);
                $_SWIFT_XMLObject->AddTag('userid', $_ticket['userid']);
                $_SWIFT_XMLObject->AddTag('userorganization', $_userOrganizationName);
                $_SWIFT_XMLObject->AddTag('userorganizationid', $_userOrganizationID);
                $_SWIFT_XMLObject->AddTag('ownerstaffid', $_ticket['ownerstaffid']);
                $_SWIFT_XMLObject->AddTag('ownerstaffname', $_ticket['ownerstaffname']);
                $_SWIFT_XMLObject->AddTag('fullname', $_ticket['fullname']);
                $_SWIFT_XMLObject->AddTag('email', $_ticket['email']);
                $_SWIFT_XMLObject->AddTag('lastreplier', $_ticket['lastreplier']);
                $_SWIFT_XMLObject->AddTag('subject', $_ticket['subject']);
                $_SWIFT_XMLObject->AddTag('creationtime', $_ticket['dateline']);
                $_SWIFT_XMLObject->AddTag('lastactivity', $_ticket['lastactivity']);
                $_SWIFT_XMLObject->AddTag('laststaffreply', $_ticket['laststaffreplytime']);
                $_SWIFT_XMLObject->AddTag('lastuserreply', $_ticket['lastuserreplytime']);
                $_SWIFT_XMLObject->AddTag('slaplanid', $_ticket['slaplanid']);
                $_SWIFT_XMLObject->AddTag('slaplantitle', (isset($_slaPlanCache[$_ticket['slaplanid']]) ? htmlspecialchars($_slaPlanCache[$_ticket['slaplanid']]['title']) : ''));
                $_SWIFT_XMLObject->AddTag('nextreplydue', $_ticket['duetime']);
                $_SWIFT_XMLObject->AddTag('resolutiondue', $_ticket['resolutionduedateline']);
                $_SWIFT_XMLObject->AddTag('replies', $_ticket['totalreplies']);
                $_SWIFT_XMLObject->AddTag('ipaddress', $_ticket['ipaddress']);
                $_SWIFT_XMLObject->AddTag('creator', $_ticket['creator']);
                $_SWIFT_XMLObject->AddTag('creationmode', $_ticket['creationmode']);
                $_SWIFT_XMLObject->AddTag('creationtype', $_ticket['tickettype']);
                $_SWIFT_XMLObject->AddTag('isescalated', $_ticket['isescalated']);
                $_SWIFT_XMLObject->AddTag('escalationruleid', $_ticket['escalationruleid']);
                $_SWIFT_XMLObject->AddTag('hasattachments', $_ticket['hasattachments']);
                $_SWIFT_XMLObject->AddTag('hasnotes', $_ticket['hasnotes']);
                $_SWIFT_XMLObject->AddTag('hasbilling', $_ticket['hasbilling']);
                $_SWIFT_XMLObject->AddTag('hasfollowup', $_ticket['hasfollowup']);
                $_SWIFT_XMLObject->AddTag('hasdraft', $_ticket['hasdraft']);
                $_SWIFT_XMLObject->AddTag('tags', $_ticketTags);
            }

            if (($_extendedInfo === true && $_hasExtendedMap == false) || ($_hasExtendedMap == true && $_extendedMapOptions['extendedinfo'] == true)) {

                if (isset($_ticketWatcherMap[$_ticketID]) && $_wantPostsOnly == false) {
                    foreach ($_ticketWatcherMap[$_ticketID] as $_staffID) {
                        if (isset($_staffCache[$_staffID])) {
                            $_SWIFT_XMLObject->AddTag('watcher', '', array('staffid' => ($_staffID), 'name' => $_staffCache[$_staffID]['fullname']));
                        }
                    }
                }

                if (isset($_ticketWorkflowMap[$_ticketID]) && $_wantPostsOnly == false) {
                    foreach ($_ticketWorkflowMap[$_ticketID] as $_ticketWorkflowID) {
                        if (isset($_ticketWorkflowCache[$_ticketWorkflowID])) {
                            $_SWIFT_XMLObject->AddTag('workflow', '', array('id' => $_ticketWorkflowID, 'title' => $_ticketWorkflowCache[$_ticketWorkflowID]['title']));
                        }
                    }
                }

                if (isset($_ticketNotesContainer[$_ticketID]) && $_wantPostsOnly == false) {
                    foreach ($_ticketNotesContainer[$_ticketID] as $_ticketNote) {
                        $_SWIFT_XMLObject->AddTag('note', $_ticketNote['note'], array('id' => $_ticketNote['ticketnoteid'], 'type' => 'ticket', 'notecolor' => $_ticketNote['notecolor'],
                            'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => $_ticketNote['forstaffid'], 'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
                    }
                }

                if (isset($_userNotesContainer[$_ticket['userid']]) && $_wantPostsOnly == false) {
                    foreach ($_userNotesContainer[$_ticket['userid']] as $_ticketNote) {
                        $_SWIFT_XMLObject->AddTag('note', $_ticketNote['note'], array('id' => $_ticketNote['usernoteid'], 'type' => 'user', 'notecolor' => $_ticketNote['notecolor'],
                            'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => '0', 'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
                    }
                }

                if (isset($_userOrganizationNotesContainer[$_userOrganizationID]) && $_wantPostsOnly == false) {
                    foreach ($_userOrganizationNotesContainer[$_userOrganizationID] as $_ticketNote) {
                        $_SWIFT_XMLObject->AddTag('note', $_ticketNote['note'], array('id' => $_ticketNote['usernoteid'], 'type' => 'userorganization', 'notecolor' => $_ticketNote['notecolor'],
                            'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => '0', 'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
                    }
                }

                if (isset($_ticketTimeTrackContainer[$_ticketID]) && $_wantPostsOnly == false) {
                    foreach ($_ticketTimeTrackContainer[$_ticketID] as $_ticketTimeTrack) {
                        $_SWIFT_XMLObject->AddTag('billing', $_ticketTimeTrack['note'], array('id' => $_ticketTimeTrack['tickettimetrackid'], 'timeworked' => $_ticketTimeTrack['timespent'], 'timebillable' => $_ticketTimeTrack['timebillable'],
                            'billdate' => $_ticketTimeTrack['dateline'], 'workdate' => $_ticketTimeTrack['workdateline'],
                            'workerstaffid' => $_ticketTimeTrack['workerstaffid'], 'workerstaffname' => $_ticketTimeTrack['workerstaffname'],
                            'creatorstaffid' => $_ticketTimeTrack['creatorstaffid'], 'creatorstaffname' => $_ticketTimeTrack['creatorstaffname'],
                            'notecolor' => $_ticketTimeTrack['notecolor']));
                    }
                }
            }

            if (($_extendedInfo === true && $_hasExtendedMap == false) || ($_hasExtendedMap == true && $_extendedMapOptions['ticketposts'] == true)) {
                self::processTicketPosts($_ticketPostContainer, $_ticketID, $_SWIFT_XMLObject,
                    $_ticketAttachmentContainer);

                // Process Custom Fields
                self::preocessCustomFields($_SWIFT, $_ticketID, $_ticket, $_SWIFT_XMLObject);
            }

            $_SWIFT_XMLObject->EndParentTag('ticket');
        }
        $_SWIFT_XMLObject->EndParentTag('tickets');

        $_SWIFT_XMLObject->EndParentTag('kayako_staffapi');

        if (SWIFT_INTERFACE !== 'tests')
            $_SWIFT_XMLObject->EchoXMLStaffAPI();

        return true;
    }

    /**
     * Retrieve ID List from CSV
     *
     * @author Varun Shoor
     * @param string $_csvIDList
     * @return array The ID List as an Array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveIDListFromCSV($_csvIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (is_numeric($_csvIDList)) {
            return array(($_csvIDList));
        } else if (stristr($_csvIDList, ',')) {
            $_chunkIDList = explode(',', $_csvIDList);

            $_finalChunkIDList = array();

            foreach ($_chunkIDList as $_value) {
                if (is_numeric($_value)) {
                    $_finalChunkIDList[] = ($_value);
                }
            }

            return $_finalChunkIDList;
        }

        return array(0);
    }

    /**
     * Returns the valid sort by field values
     *
     * @author Varun Shoor
     * @return array
     */
    public static function GetValidSortByValues()
    {
        return self::$_sortByFieldList;
    }

    /**
     * Retrieve a sort by field on value
     *
     * @author Varun Shoor
     * @param string $_sortByValue
     * @return string
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetSortByFieldOnValue($_sortByValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        switch ($_sortByValue) {
            case 'department':
                return 'tickets.departmenttitle';

                break;

            case 'status':
                return 'tickets.ticketstatustitle';

                break;

            case 'priority':
                return 'prioritydisplayorder';

                break;

            case 'type':
                return 'tickets.tickettypetitle';

                break;

            case 'flagtype':
                return 'tickets.flagtype';

                break;

            case 'due':
                return 'tickets.duetime';

                break;

            case 'creationdate':
                return 'tickets.dateline';

                break;

            case 'lastactivity':
                return 'tickets.lastactivity';

                break;

            default:
                break;
        }

        throw new SWIFT_Exception('Invalid Sort By Field');
    }

    /**
     * Initiate a Search
     *
     * @author Varun Shoor
     *
     * @param string $_query
     * @param bool $_ticketID                 (OPTIONAL)
     * @param bool $_contents                 (OPTIONAL)
     * @param bool $_author                   (OPTIONAL)
     * @param bool $_email                    (OPTIONAL)
     * @param bool $_fullName                 (OPTIONAL)
     * @param bool $_notes                    (OPTIONAL)
     * @param bool $_userGroup                (OPTIONAL)
     * @param bool $_userOrganization         (OPTIONAL)
     * @param bool $_user                     (OPTIONAL)
     * @param bool $_tags                     (OPTIONAL)
     * @param bool $_subject                  (OPTIONAL)
     * @param array $_departmentIDList          (OPTIONAL)
     * @param array $_ticketStatusIDList        (OPTIONAL)
     * @param array $_ownerStaffIDList           (OPTIONAL)
     * @param int $_start                     (OPTIONAL)
     * @param int $_limit                     (OPTIONAL)
     *
     * @return bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Search($_query, $_ticketID = true, $_contents = true, $_author = true, $_email = true, $_fullName = true, $_notes = true, $_userGroup = true, $_userOrganization = true, $_user = true, $_tags = true, $_subject = true,
                                  $_departmentIDList = array(), $_ticketStatusIDList = array(), $_ownerStaffIDList = array(),
                                  $_start = 0, $_limit = 100)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalTicketIDList = $_ticketIDList = $_sqlQueryList = array();

        // Search Ticket ID?
        if ($_ticketID == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchTicketID($_query, $_SWIFT->Staff));
        }

        // Search Subject?
        if ($_subject == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchSubject($_query, $_SWIFT->Staff));
        }

        // Search Contents?
        if ($_contents == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::QuickSearch($_query, $_SWIFT->Staff));
        }

        // Search Author?
        if ($_author == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchCreator($_query, $_SWIFT->Staff));
        }

        // Search Email?
        if ($_email == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchEmail($_query, $_SWIFT->Staff));
        }

        // Search Full Name?
        if ($_fullName == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchFullName($_query, $_SWIFT->Staff));
        }

        // Search Notes?
        if ($_notes == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchTicketNotes($_query));
        }

        // Search User Group?
        if ($_userGroup == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchUserGroup($_query));
        }

        // Search User Organizations?
        if ($_userOrganization == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchUserOrganization($_query));
        }

        // Search Users?
        if ($_user == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchUser($_query));
        }

        // Search Tags?
        if ($_tags == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchTags($_query));
        }

        // Owner ID
        $_ownerStaffIDList = IIF(_is_array($_ownerStaffIDList) && ($_ownerStaffIDList != array(0)), $_ownerStaffIDList, array());

        // Department ID
        $_finalDepartmentIDList = $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);
        if (_is_array($_departmentIDList)) {
            // Checking whether Trash department is specified
            if (in_array(0, $_departmentIDList)) {
                $_assignedDepartmentIDList[] = 0;
            }

            $_finalDepartmentIDList = array_intersect($_assignedDepartmentIDList, $_departmentIDList);
            $_finalDepartmentIDList = IIF(_is_array($_finalDepartmentIDList), $_finalDepartmentIDList, $_assignedDepartmentIDList);
        }

        // Ticket Status
        $_finalTicketStatusIDList = array();
        if (_is_array($_ticketStatusIDList)) {
            $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
            foreach ($_ticketStatusIDList as $_ticketStatusID) {
                if (isset($_ticketStatusCache[$_ticketStatusID]) && ($_ticketStatusCache[$_ticketStatusID]['departmentid'] == '0' || in_array($_ticketStatusCache[$_ticketStatusID]['departmentid'], $_finalDepartmentIDList))) {
                    $_finalTicketStatusIDList[($_ticketStatusID)] = $_ticketStatusID;
                }
            }

            $_finalTicketStatusIDList = array_keys($_finalTicketStatusIDList);
        }

        $_sqlQueryList = array();
        // Preparing search clauses
        $_sqlQueryList[] = "ticketid IN (" . BuildIN($_ticketIDList, true) . ")";
        $_sqlQueryList[] = "departmentid IN (" . BuildIN($_finalDepartmentIDList, true) . ")";
        if (_is_array($_finalTicketStatusIDList)) {
            $_sqlQueryList[] = "tickets.ticketstatusid IN (" . BuildIn($_finalTicketStatusIDList, true) . ")";
        }
        if (_is_array($_ownerStaffIDList)) {
            $_sqlQueryList[] = "tickets.ownerstaffid IN (" . BuildIN($_ownerStaffIDList, true) . ")";
        }

        // Search
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets AS tickets
                                       WHERE " . implode(" AND ", $_sqlQueryList) . "
                                       ORDER BY lastactivity DESC", $_limit, $_start);
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        return self::DispatchOnTicketIDList($_finalTicketIDList, $_start, '', ' ORDER BY lastactivity DESC', true, false, false, $_limit);
    }

    /**
     * @param SWIFT $_SWIFT
     * @param int $_ticketID
     * @param array $_ticket
     * @param SWIFT_XML $_SWIFT_XMLObject
     * @throws SWIFT_Exception
     */
    protected static function preocessCustomFields($_SWIFT, $_ticketID, $_ticket, $_SWIFT_XMLObject)
    {
        $_customFieldMapCache = (array)$_SWIFT->Cache->Get('customfieldmapcache');
        $_customFieldOptionCache = (array)$_SWIFT->Cache->Get('customfieldoptioncache');
        $_customFieldIDList = $_customArguments = [];

        $_customFieldGroupTypeList = [
            SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
            SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
            SWIFT_CustomFieldGroup::GROUP_USERTICKET
        ];

        $_customFieldGroupContainer = SWIFT_CustomFieldManager::RetrieveOnStaff($_customFieldGroupTypeList,
            $_SWIFT->Staff, $_ticketID, $_ticket['departmentid'], true);

        if (_is_array($_customFieldGroupContainer)) {
            $_rawCustomFieldValueContainer = $_customFieldValueContainer = [];

            foreach ($_customFieldGroupContainer as $_customFieldGroup) {
                foreach (array_keys($_customFieldGroup['_fields']) as $_customFieldID) {
                    $_customFieldIDList[] = $_customFieldID;
                }
            }

            if (count($_customFieldIDList)) {
                $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND typeid = '" . ($_ticketID) . "'");

                while ($_SWIFT->Database->NextRecord()) {
                    if (!isset($_customFieldMapCache[$_SWIFT->Database->Record['customfieldid']])) {
                        continue;
                    }

                    $_rawCustomFieldValueContainer[$_SWIFT->Database->Record['customfieldid']] = $_SWIFT->Database->Record;

                    // If we already have data set then we continue as is
                    if (isset($_customFieldValueContainer[$_SWIFT->Database->Record['customfieldid']])) {
                        continue;
                    }

                    $_fieldValue = '';
                    if ($_SWIFT->Database->Record['isencrypted'] == '1') {
                        $_fieldValue = SWIFT_CustomFieldManager::Decrypt($_SWIFT->Database->Record['fieldvalue']);
                    } else {
                        $_fieldValue = $_SWIFT->Database->Record['fieldvalue'];
                    }

                    if ($_SWIFT->Database->Record['isserialized'] == '1') {
                        $_fieldValue = mb_unserialize($_fieldValue);
                    }

                    $_customField = $_customFieldMapCache[$_SWIFT->Database->Record['customfieldid']];

                    if (_is_array($_fieldValue) && ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE)) {
                        foreach ($_fieldValue as $_val) {
                            if (isset($_customFieldOptionCache[$_val])) {
                                $_fieldValue[$_val] = $_customFieldOptionCache[$_val];
                            }
                        }
                    } else {
                        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT) {
                            if (isset($_customFieldOptionCache[$_fieldValue])) {
                                $_fieldValue = $_customFieldOptionCache[$_fieldValue];
                            }
                        } else {
                            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                                $_fieldValueInterim = [];
                                if (isset($_customFieldOptionCache[$_fieldValue[0]])) {
                                    $_fieldValueInterim[$_fieldValue[0]] = $_customFieldOptionCache[$_fieldValue[0]];

                                    if (isset($_fieldValue[1])) {
                                        foreach ($_fieldValue[1] as $_val) {
                                            if (isset($_customFieldOptionCache[$_val])) {
                                                $_fieldValueInterim[$_val] = $_customFieldOptionCache[$_val];
                                            }
                                        }
                                    }
                                }

                                $_fieldValue = $_fieldValueInterim;
                            } else {
                                if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE) {
                                    $_fieldValueInterim = '';

                                    try {
                                        $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fieldValue);

                                        $_fieldValueInterim = $_SWIFT_FileManagerObject->GetProperty('originalfilename');
                                        $_customArguments[$_customField['customfieldid']]['uniquehash'] = $_rawCustomFieldValueContainer[$_SWIFT->Database->Record['customfieldid']]['uniquehash'];
                                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                                    }

                                    $_fieldValue = $_fieldValueInterim;
                                }
                            }
                        }
                    }
                    $_customFieldValueContainer[$_SWIFT->Database->Record['customfieldid']] = $_fieldValue;
                }
            }
            $_SWIFT_XMLObject->AddParentTag('customfields');
            foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
                if (count($_customFieldGroup['_fields'])) {
                    $_SWIFT_XMLObject->AddParentTag('group',
                        ['id' => $_customFieldGroupID, 'title' => $_customFieldGroup['title']]);

                    foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                        $_customFieldValue = '';
                        $_arrayCustomFieldValue = [];

                        if (isset($_customFieldValueContainer[$_customFieldID])) {
                            if (_is_array($_customFieldValueContainer[$_customFieldID])) {
                                $_arrayCustomFieldValue = $_customFieldValueContainer[$_customFieldID];
                            } else {
                                $_arrayCustomFieldValue[] = $_customFieldValueContainer[$_customFieldID];
                                $_customFieldValue = $_customFieldValueContainer[$_customFieldID];
                            }
                        }

                        $_defaultValue = $_customField['defaultvalue'];
                        $_fieldType = '';
                        $_hasOptions = false;
                        $_extendedFieldArgument = $_optionAttributes = [];

                        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_TEXT) {
                            $_fieldType = "text";
                        } else {
                            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_TEXTAREA) {
                                $_fieldType = "textarea";
                            } else {
                                if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_PASSWORD) {
                                    $_fieldType = "password";
                                } else {
                                    if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX) {
                                        $_defaultValue = '';
                                        $_fieldType = "checkbox";
                                        $_hasOptions = true;
                                    } else {
                                        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO) {
                                            $_defaultValue = '';
                                            $_fieldType = "radio";
                                            $_hasOptions = true;
                                        } else {
                                            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT) {
                                                $_defaultValue = '';
                                                $_fieldType = "select";
                                                $_hasOptions = true;
                                            } else {
                                                if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
                                                    $_extendedFieldArgument = ['multiple' => '1'];
                                                    $_defaultValue = '';
                                                    $_fieldType = "select";
                                                    $_hasOptions = true;
                                                } else {
                                                    if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                                                        $_defaultValue = '';
                                                        $_fieldType = "selectlinked";
                                                        $_hasOptions = true;
                                                    } else {
                                                        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_DATE) {
                                                            $_fieldType = "date";
                                                        } else {
                                                            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE) {
                                                                $_defaultValue = '';
                                                                $_fieldType = "file";
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (empty($_fieldType)) {
                            continue;
                        }

                        $_fieldArguments = [
                            'id' => $_customFieldID,
                            'title' => $_customField['title'],
                            'name' => $_customField['fieldname'],
                            'default' => $_defaultValue,
                            'isrequired' => $_customField['isrequired'],
                            "staffeditable" => $_customField['staffeditable'],
                            "regexpvalidate" => $_customField['regexpvalidate'],
                            "displayorder" => $_customField['displayorder']
                        ];

                        $_fieldArguments = array_merge($_extendedFieldArgument, $_fieldArguments);

                        if (isset($_customArguments[$_customFieldID])) {
                            $_fieldArguments = array_merge($_fieldArguments, $_customArguments[$_customFieldID]);
                        }

                        if ($_hasOptions) {
                            $_SWIFT_XMLObject->AddParentTag($_fieldType, $_fieldArguments);

                            if (_is_array($_customField['_options'])) {

                                if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                                    foreach ($_customField['_options'] as $_optionID => $_optionContainer) {
                                        if (!empty($_optionContainer['parentcustomfieldoptionid'])) {
                                            continue;
                                        }
                                        $_optionAttributes = [
                                            'title' => $_optionContainer['optionvalue'],
                                            'id' => $_optionID,
                                            'displayorder' => $_optionContainer['displayorder'],
                                            'default' => $_optionContainer['isselected'],
                                            'selected' => IIF(in_array($_optionContainer['optionvalue'],
                                                $_arrayCustomFieldValue), "1", "0")
                                        ];

                                        $_SWIFT_XMLObject->AddParentTag('parentoption', $_optionAttributes);

                                        foreach ($_customField['_options'] as $_subOptionID => $_subOptionContainer) {
                                            if ($_optionContainer['customfieldoptionid'] == $_subOptionContainer['parentcustomfieldoptionid']) {
                                                $_subOptionAttributes = [
                                                    'id' => $_subOptionID,
                                                    'displayorder' => $_subOptionContainer['displayorder'],
                                                    'default' => $_subOptionContainer['isselected'],
                                                    'selected' => IIF(in_array($_subOptionContainer['optionvalue'],
                                                            $_arrayCustomFieldValue) && in_array($_optionContainer['optionvalue'],
                                                            $_arrayCustomFieldValue), "1", "0")
                                                ];
                                                $_SWIFT_XMLObject->AddTag('option', $_subOptionContainer['optionvalue'],
                                                    $_subOptionAttributes);
                                            }
                                        }

                                        $_SWIFT_XMLObject->EndParentTag('parentoption');
                                    }
                                } else {
                                    foreach ($_customField['_options'] as $_optionID => $_optionContainer) {
                                        $_optionAttributes = [
                                            'id' => $_optionID,
                                            'displayorder' => $_optionContainer['displayorder'],
                                            'default' => $_optionContainer['isselected'],
                                            'selected' => IIF(in_array($_optionContainer['optionvalue'],
                                                $_arrayCustomFieldValue), "1", "0")
                                        ];

                                        $_SWIFT_XMLObject->AddTag('option', $_optionContainer['optionvalue'],
                                            $_optionAttributes);
                                    }
                                }
                            }

                            $_SWIFT_XMLObject->EndParentTag($_fieldType);
                        } else {
                            $_SWIFT_XMLObject->AddTag($_fieldType, $_customFieldValue, $_fieldArguments);
                        }
                    }

                    $_SWIFT_XMLObject->EndParentTag('group');
                }
            }
            $_SWIFT_XMLObject->EndParentTag('customfields');
        }
    }

    /**
     * @param array $_ticketPostContainer
     * @param int $_ticketID
     * @param SWIFT_XML$_SWIFT_XMLObject
     * @param array $_ticketAttachmentContainer
     * @throws SWIFT_Exception
     */
    protected static function processTicketPosts(
        $_ticketPostContainer,
        $_ticketID,
        $_SWIFT_XMLObject,
        $_ticketAttachmentContainer
    ) {
        if (isset($_ticketPostContainer[$_ticketID])) {
            foreach ($_ticketPostContainer[$_ticketID] as $_ticketPostID => $_ticketPost) {
                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-1949 Issue with non-printable characters in REST API
                 *
                 */
                $_ticketPost['contents'] = preg_replace('/(?![\x{000d}\x{000a}\x{0009}])\p{C}/u', '',
                    $_ticketPost['contents']);

                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                 *
                 * SWIFT-5077 Post content is not formatted correctly - Incorrect HTML Stripping
                 */
                $_processedPostContents = SWIFT_TicketPost::GetParsedContents($_ticketPost['contents'], 'html',
                    $_ticketPost['ishtml'], '');


                $_SWIFT_XMLObject->AddParentTag('post', ['id' => $_ticketPostID]);
                $_SWIFT_XMLObject->AddTag('creationtime', $_ticketPost['dateline']);
                $_SWIFT_XMLObject->AddTag('userid', $_ticketPost['userid']);
                $_SWIFT_XMLObject->AddTag('staffid', $_ticketPost['staffid']);
                $_SWIFT_XMLObject->AddTag('fullname', $_ticketPost['fullname']);
                $_SWIFT_XMLObject->AddTag('email', $_ticketPost['email']);
                $_SWIFT_XMLObject->AddTag('emailto', $_ticketPost['emailto']);
                $_SWIFT_XMLObject->AddTag('subject', $_ticketPost['subject']);
                $_SWIFT_XMLObject->AddTag('ipaddress', $_ticketPost['ipaddress']);
                $_SWIFT_XMLObject->AddTag('editinfo', '', [
                    'edited' => $_ticketPost['edited'],
                    'staffid' => $_ticketPost['editedbystaffid'],
                    'time' => $_ticketPost['editeddateline']
                ]);
                $_SWIFT_XMLObject->AddTag('creator', $_ticketPost['creator']);
                $_SWIFT_XMLObject->AddTag('contents', $_processedPostContents);
                $_SWIFT_XMLObject->AddTag('creationmode', $_ticketPost['creationmode']);
                $_SWIFT_XMLObject->AddTag('issurveycomment', $_ticketPost['issurveycomment']);

                if (isset($_ticketAttachmentContainer[$_ticketPostID])) {
                    foreach ($_ticketAttachmentContainer[$_ticketPostID] as $_attachmentID => $_attachmentContainer) {
                        $_mimeType = $_attachmentContainer['filetype'];
                        if (file_exists($_attachmentContainer['storefilepath'])) {
                            $_mimeType = kc_mime_content_type($_attachmentContainer['storefilepath']);
                        }

                        $_SWIFT_XMLObject->AddTag('attachment', $_attachmentContainer['contents'], [
                            'id' => $_attachmentID,
                            'filename' => $_attachmentContainer['filename'],
                            'filetype' => $_mimeType,
                            'filesize' => $_attachmentContainer['filesize']
                        ]);
                    }
                }
                $_SWIFT_XMLObject->EndParentTag('post');
            }
        }
    }
}
