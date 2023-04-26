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

namespace Tickets\Library\View;

use SWIFT;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_DataStore;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use SWIFT_FileManager;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use SWIFT_Library;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_Staff;
use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Models\View\SWIFT_TicketViewField;
use Tickets\Models\View\SWIFT_TicketViewLink;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Tickets\Library\Flag\SWIFT_TicketFlag;

/**
 * The Ticket View Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_TicketViewRenderer extends SWIFT_Library
{

    static protected $_ticketFetchQuery = '';

    // Core Constants
    const OWNER_MYTICKETS = 1;
    const OWNER_UNASSIGNED = 2;

    /**
     * Retrieve the default ticket object
     *
     * @author Varun Shoor
     * @return SWIFT_TicketView
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetDefaultTicketViewObject()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffTicketPropertiesCache = (array) $_SWIFT->Cache->Get('staffticketpropertiescache');
        $_ticketViewCache = (array) $_SWIFT->Cache->Get('ticketviewcache');

        // Session ticket view ALWAYS takes precedence
        $_sessionTicketViewID = $_SWIFT->Session->GetProperty('ticketviewid');
        if (!empty($_sessionTicketViewID) && isset($_ticketViewCache[$_sessionTicketViewID])) {
            $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataStore($_ticketViewCache[$_sessionTicketViewID]));
            if ($_SWIFT_TicketViewObject->CanStaffView()) {
                return $_SWIFT_TicketViewObject;
            }
        }

        // Otherwise check for active ticket view and see if it can be used with provided department (IMPORTANT)
        if (isset($_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()]) &&
                isset($_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()]['ticketviewid'])) {
            $_defaultTicketViewID = $_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()]['ticketviewid'];

            if (isset($_ticketViewCache[$_defaultTicketViewID])) {
                $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataStore($_ticketViewCache[$_defaultTicketViewID]));
                if ($_SWIFT_TicketViewObject->CanStaffView()) {
                    return $_SWIFT_TicketViewObject;
                }
            }
        }

        // Get the master ticket view object or ticket view associated with the given department
        foreach ($_ticketViewCache as $_key => $_ticketViewContainer) {
            if ($_ticketViewContainer['ismaster'] == '1') {
                $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataStore($_ticketViewContainer));

                return $_SWIFT_TicketViewObject;
            }
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Retrieve the Next/Previous Ticket IDs
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param string $_resultType
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_incomingDepartmentID
     * @param int $_incomingTicketStatusID
     * @param int $_incomingTicketTypeID
     * @return bool|int
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetNextPreviousTicketID(SWIFT_Ticket $_SWIFT_TicketObject, $_resultType = 'next', $_listType = 'inbox', $_incomingDepartmentID = -1, $_incomingTicketStatusID = -1, $_incomingTicketTypeID = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketViewObject = self::GetTicketViewObject($_incomingDepartmentID);
        $_ticketFieldsContainer = SWIFT_TicketViewField::GetFieldContainer();
        $_ticketViewFieldsContainer = $_SWIFT_TicketViewObject->GetFieldsContainer();
        $_ticketViewLinksContainer = $_SWIFT_TicketViewObject->GetLinksContainer();

        $_incomingOwnerFilter = false;
        if ($_listType == 'mytickets') {
            $_incomingOwnerFilter = self::OWNER_MYTICKETS;
        } else if ($_listType == 'unassigned') {
            $_incomingOwnerFilter = self::OWNER_UNASSIGNED;
        }

        $_searchStoreID = false;
        $_searchStoreContainer = $_SWIFT->Database->QueryFetch("SELECT searchstoreid FROM " . TABLE_PREFIX . "searchstores WHERE storetype = '" . SWIFT_SearchStore::TYPE_TICKETS . "'");
        if (isset($_searchStoreContainer['searchstoreid']) && !empty($_searchStoreContainer['searchstoreid'])) {
            $_searchStoreID = $_searchStoreContainer['searchstoreid'];
        }

        $_previousTicketID = false;
        $_finalTicketID = 0;
        if (!empty($_searchStoreID)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "searchstoredata WHERE searchstoreid = '" . (int) ($_searchStoreID) . "'");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['dataid'] == $_SWIFT_TicketObject->GetTicketID() && $_resultType == 'previous') {
                    $_finalTicketID = $_previousTicketID;

                    break;
                } else if ($_previousTicketID == $_SWIFT_TicketObject->GetTicketID() && $_resultType == 'next') {
                    $_finalTicketID = $_SWIFT->Database->Record['dataid'];

                    break;
                }

                $_previousTicketID = $_SWIFT->Database->Record['dataid'];
            }

            if (!empty($_finalTicketID)) {
                return $_finalTicketID;
            }
        }

        if (empty($_finalTicketID)) {
            $_variableContainer = self::ParseVariables($_SWIFT_TicketViewObject, $_ticketViewLinksContainer, $_incomingDepartmentID, $_incomingTicketStatusID, $_incomingOwnerFilter, $_incomingTicketTypeID);
            // will be overwritten by extract
            $_sqlArrayContainer = [];
            extract($_variableContainer, EXTR_OVERWRITE);
            $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('ticketmanagegrid', 'tickets.lastactivity', 'desc');

            $_SWIFT->Database->Query('SELECT tickets.ticketid FROM ' . TABLE_PREFIX . 'tickets AS tickets WHERE (' . implode(' AND ', $_sqlArrayContainer) . ') ORDER BY ' . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1]);
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ticketid'] == $_SWIFT_TicketObject->GetTicketID() && $_resultType == 'previous') {
                    $_finalTicketID = $_previousTicketID;

                    break;
                } else if ($_previousTicketID == $_SWIFT_TicketObject->GetTicketID() && $_resultType == 'next') {

                    $_finalTicketID = $_SWIFT->Database->Record['ticketid'];

                    break;
                }

                $_previousTicketID = $_SWIFT->Database->Record['ticketid'];
            }

            return $_finalTicketID;
        }
        // @codeCoverageIgnoreStart
        // Will never be reached
        return 0;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Parse Variables and return
     *
     * @author Varun Shoor
     * @return array
     */
    protected static function ParseVariables(SWIFT_TicketView $_SWIFT_TicketViewObject, $_ticketViewLinksContainer, $_incomingDepartmentID, $_incomingTicketStatusID, $_incomingOwnerFilter, $_incomingTicketTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketViewCache = (array) $_SWIFT->Cache->Get('ticketviewcache');
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');

        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');

        // We begin wtih departments
        $_departmentIDList = array();
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if ($_incomingDepartmentID != -1 && is_numeric($_incomingDepartmentID) &&
                (in_array($_incomingDepartmentID, $_assignedDepartmentIDList) || $_incomingDepartmentID == 0)) {
            $_departmentIDList[] = $_incomingDepartmentID;
        } else {
            // See if we filter the departments in the view..
            if (isset($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT])) {
                foreach ($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT] as $_linkType => $_linkTypeList) {
                    foreach ($_linkTypeList as $_key => $_SWIFT_TicketViewLinkObject) {
                        if (in_array($_SWIFT_TicketViewLinkObject->GetProperty('linktypeid'), $_assignedDepartmentIDList)) {
                            $_departmentIDList[] = $_SWIFT_TicketViewLinkObject->GetProperty('linktypeid');
                        }
                    }
                }
            }

            if (!count($_departmentIDList)) {
                $_departmentIDList = $_assignedDepartmentIDList;
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2039 Clicking on Tag under Tag Cloud does not show tickets moved to Trash
         *
         * If we click to Tag could item, include trash department also.
         * Parminder took responsibility for changing this, so if you have to revisit this.. you know who to grab by the neck.
         */
        if (isset($_POST['_searchQuery']) && !empty($_POST['_searchQuery']) && !in_array(0, $_departmentIDList)) {
            $_departmentIDList[] = '0';
        }

        // Build a list of default ticket status ids
        $_ticketStatusIDList = array();
        if ($_incomingTicketStatusID != -1 && is_numeric($_incomingTicketStatusID) && isset($_ticketStatusCache[$_incomingTicketStatusID]) &&
                ($_ticketStatusCache[$_incomingTicketStatusID]['departmentid'] == '0' ||
                in_array($_ticketStatusCache[$_incomingTicketStatusID]['departmentid'], $_departmentIDList))) {
            $_ticketStatusIDList[] = $_incomingTicketStatusID;
        } else {
            $_ticketStatusIDList = self::GetTicketStatusIDList($_departmentIDList);
        }

        // Now we need to build a list of default owners
        $_ownerStaffIDList = array();
        $_ownerNotFlag     = '';

        if ($_incomingOwnerFilter == false) {
            if ($_SWIFT_TicketViewObject->GetProperty('viewalltickets') == '1') {
                /*
                 * Improvement - Bishwanath Jha
                 *
                 * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
                 *
                 * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
                 */
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
            } else {
                if ($_SWIFT_TicketViewObject->GetProperty('viewassigned') == '1') {
                    $_ownerStaffIDList[] = $_SWIFT->Staff->GetStaffID();
                }

                if ($_SWIFT_TicketViewObject->GetProperty('viewunassigned') == '1' && $_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '1') {
                    $_ownerStaffIDList[] = '0';
                }
            }
        } else {
            if ($_incomingOwnerFilter == self::OWNER_MYTICKETS) {
                $_ownerStaffIDList[] = $_SWIFT->Staff->GetStaffID();
            } else if ($_incomingOwnerFilter == self::OWNER_UNASSIGNED) {
                $_ownerStaffIDList[] = '0';
            }
        }

        // Then comes ticket types
        $_ticketTypeIDList = array();
        if ($_incomingTicketTypeID != -1 && is_numeric($_incomingTicketTypeID) && isset($_ticketTypeCache[$_incomingTicketTypeID]) &&
                ($_ticketTypeCache[$_incomingTicketTypeID]['departmentid'] == '0' ||
                in_array($_ticketTypeCache[$_incomingTicketTypeID]['departmentid'], $_departmentIDList))) {
            $_ticketTypeIDList[] = $_incomingTicketTypeID;
        } else {
            /*             foreach ($_ticketTypeCache as $_ticketTypeID => $_ticketTypeContainer) {
              if ($_ticketTypeContainer['departmentid'] == '0' || in_array($_ticketTypeContainer['departmentid'], $_departmentIDList)) {
              $_ticketTypeIDList[] = $_ticketTypeID;
              }
              } */
        }

        // Build the SQL Query Extension
        $_sqlArrayContainer = array();

        // Always include the global ticket status id
        $_ticketStatusIDList[] = '0';

        // Is it a trash query?
        if (count($_departmentIDList) == 1 && $_departmentIDList[0] == '0') {
            $_sqlArrayContainer[] = "tickets.departmentid IN (" . BuildIN($_departmentIDList) . ")";
            $_sqlArrayContainer[] = "tickets.trasholddepartmentid IN (0," . BuildIN($_assignedDepartmentIDList) . ")";
        } else {
            $_sqlArrayContainer[] = "tickets.departmentid IN (" . BuildIN($_departmentIDList) . ")";
        }

        // Are we looking up for trashed tickets?
        if (in_array('0', $_departmentIDList)) {
            $_sqlArrayContainer[] = "tickets.ticketstatusid != '-1'";
        } else {
            $_sqlArrayContainer[] = "tickets.ticketstatusid IN (" . BuildIN($_ticketStatusIDList) . ")";
        }

        if (!count($_ownerStaffIDList)) {
            $_sqlArrayContainer[] = "tickets.ownerstaffid != '-1'";
        } else {
            $_sqlArrayContainer[] = "tickets.ownerstaffid " . $_ownerNotFlag . " IN (" . BuildIN($_ownerStaffIDList) . ")";
        }

        if (count($_ticketTypeIDList)) {
            $_sqlArrayContainer[] = "tickets.tickettypeid IN (" . BuildIN($_ticketTypeIDList) . ")";
        }

        return array('_sqlArrayContainer' => $_sqlArrayContainer, '_departmentIDList' => $_departmentIDList, '_ticketStatusIDList' => $_ticketStatusIDList, '_ownerStaffIDList' => $_ownerStaffIDList,
            '_ticketTypeIDList' => $_ticketTypeIDList);
    }

    /**
     * Retrieve the active ticket view object
     *
     * @author Varun Shoor
     * @param int $_incomingDepartmentID The Incoming Department ID
     * @return SWIFT_TicketView
     * @throws SWIFT_Exception
     */
    public static function GetTicketViewObject($_incomingDepartmentID)
    {
        $_SWIFT = SWIFT::GetInstance();


        $_SWIFT_TicketViewObject = self::GetDefaultTicketViewObject();
        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if (SWIFT::Get('forceViewChange')) {
            return $_SWIFT_TicketViewObject;
        }

        /**
         * ---------------------------------------------
         * CHECK FOR TICKET VIEW <> DEPARTMENT LINKS
         * ---------------------------------------------
         */
        $_ticketViewCache = (array) $_SWIFT->Cache->Get('ticketviewcache');
        $_ticketViewDepartmentLinksCache = (array) $_SWIFT->Cache->Get('ticketviewdepartmentlinkcache');
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');
        $ticketViews = isset($_ticketViewDepartmentLinksCache[$_incomingDepartmentID]) ?
            (array)$_ticketViewDepartmentLinksCache[$_incomingDepartmentID] : [];
        if ($_incomingDepartmentID != -1 && is_numeric($_incomingDepartmentID) && !empty($ticketViews)) {
            foreach ($ticketViews as $_ticketViewID) {
                if (isset($_ticketViewCache[$_ticketViewID])) {
                    // Check for view scope
                    $_SWIFT_TicketViewObject_Linked = new SWIFT_TicketView(new SWIFT_DataStore($_ticketViewCache[$_ticketViewID]));
                    if ($_SWIFT_TicketViewObject_Linked instanceof SWIFT_TicketView && $_SWIFT_TicketViewObject_Linked->GetIsClassLoaded() &&
                            ($_SWIFT_TicketViewObject_Linked->GetProperty('viewscope') == SWIFT_TicketView::VIEWSCOPE_GLOBAL ||
                            ($_SWIFT_TicketViewObject_Linked->GetProperty('viewscope') == SWIFT_TicketView::VIEWSCOPE_PRIVATE && $_SWIFT_TicketViewObject_Linked->GetProperty('staffid') == $_SWIFT->Staff->GetStaffID()) ||
                            ($_SWIFT_TicketViewObject_Linked->GetProperty('viewscope') == SWIFT_TicketView::VIEWSCOPE_TEAM && isset($_staffCache[$_SWIFT_TicketViewObject_Linked->GetProperty('staffid')]) && $_staffCache[$_SWIFT_TicketViewObject_Linked->GetProperty('staffid')]['staffgroupid'] == $_SWIFT->Staff->GetProperty('staffgroupid')))) {
                        $_SWIFT_TicketViewObject = $_SWIFT_TicketViewObject_Linked;
                    }
                }
            }
        }

        return $_SWIFT_TicketViewObject;
    }

    /**
     * The Column Renderer
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterface $_SWIFT_UserInterfaceObject The SWIFT_UserInterface Object Pointer
     * @param SWIFT_UserInterfaceGrid $_SWIFT_UserInterfaceGridObject The Grid Object
     * @param int $_incomingDepartmentID (OPTIONAL) The Department ID
     * @param int $_incomingTicketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_incomingTicketTypeID (OPTIONAL) The Ticket Type ID
     * @param mixed $_incomingOwnerFilter (OPTIONAL) The Incoming Owner Filter
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Render(SWIFT_UserInterface $_SWIFT_UserInterfaceObject, SWIFT_UserInterfaceGrid $_SWIFT_UserInterfaceGridObject, $_incomingDepartmentID = -1, $_incomingTicketStatusID = -1, $_incomingTicketTypeID = -1, $_incomingOwnerFilter = false, $_searchStoreID = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_TicketViewObject = self::GetTicketViewObject($_incomingDepartmentID);

        $_ticketViewCache = (array) $_SWIFT->Cache->Get('ticketviewcache');
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');

        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');

        $_ticketFieldsContainer = SWIFT_TicketViewField::GetFieldContainer();
        $_ticketViewFieldsContainer = $_SWIFT_TicketViewObject->GetFieldsContainer();
        $_ticketViewLinksContainer = $_SWIFT_TicketViewObject->GetLinksContainer();


        /**
         * ---------------------------------------------
         * Begin Filteration Logic
         * ---------------------------------------------
         */
        $_variableContainer = self::ParseVariables($_SWIFT_TicketViewObject, $_ticketViewLinksContainer, $_incomingDepartmentID, $_incomingTicketStatusID, $_incomingOwnerFilter, $_incomingTicketTypeID);

        // will be overwritten by extract
        $_sqlArrayContainer = [];

        extract($_variableContainer);


        /**
         * ---------------------------------------------
         * Grid Rendering Logic
         * ---------------------------------------------
         */
        // Prepare the Mass Action Panel
        $_SWIFT_UserInterfaceGridObject->SetMassActionPanel(self::RenderMassActionPanel($_SWIFT_UserInterfaceObject), array('Tickets\Staff\Controller_Manage', 'MassActionPanel'));

        // Prepare the Extended Buttons
        /**
        * BUG FIX: Parminder Singh
        *
        * SWIFT-1664: Restricting of Views settings for staff teams still show the Views selection to the staff members
        *
        */
        if ($_SWIFT->Staff->GetPermission('staff_tcanview_views') != '0') {
            $_extendedButtonContainer = array();
            $_extendedButtonContainer[0]['title'] = sprintf($_SWIFT->Language->Get('menuviews'), htmlspecialchars($_SWIFT_TicketViewObject->GetProperty('title')));
            $_extendedButtonContainer[0]['type'] = SWIFT_UserInterfaceGrid::BUTTON_MENU;
            $_extendedButtonContainer[0]['link'] = 'UIDropDown(\'ticketviewmenu\', event, \'ticketviewdropbutton\', \'gridextendedtoolbar\');';
            $_extendedButtonContainer[0]['icon'] = 'fa-list-alt';
            $_extendedButtonContainer[0]['id'] = 'ticketviewdropbutton';
            $_SWIFT_UserInterfaceGridObject->SetExtendedButtons($_extendedButtonContainer);
        }

        /**
         * BUG FIX: Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-3297: Pagination is not stable when searching tickets in staff control panel.
         *
         * Comments: Select and count the Distinct Ticket ID.
         */
        // Prepare the Queries
        if ($_SWIFT_UserInterfaceGridObject->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $_SWIFT_UserInterfaceGridObject->SetSearchQuery("
            SELECT tickets.* FROM " . TABLE_PREFIX . "tickets AS tickets
            LEFT JOIN " . TABLE_PREFIX . "ticketposts AS ticketposts ON (tickets.ticketid = ticketposts.ticketid)
            LEFT JOIN " . TABLE_PREFIX . "ticketlocks AS ticketlocks ON (tickets.ticketid = ticketlocks.ticketid)
            LEFT JOIN " . TABLE_PREFIX . "ticketwatchers AS ticketwatchers ON (tickets.ticketid = ticketwatchers.ticketid AND ticketwatchers.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
            LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
            WHERE ((" . $_SWIFT_UserInterfaceGridObject->BuildFullTextSearch('tickets.subject') . ")
            OR (tickets.ticketid IN (SELECT ticketnotes.linktypeid FROM " . TABLE_PREFIX . "ticketnotes AS ticketnotes
        WHERE " . $_SWIFT_UserInterfaceGridObject->BuildFullTextSearch('ticketnotes.note') . "
            AND ticketnotes.linktype = 1 GROUP BY ticketnotes.linktypeid))
            OR (" . $_SWIFT_UserInterfaceGridObject->BuildFullTextSearch('ticketposts.contents') . " AND ".$_SWIFT_UserInterfaceGridObject->skipInlineImagesFromPostContentsSearchQuery('ticketposts.contents')."))
            AND " . implode(' AND ', $_sqlArrayContainer) . " GROUP BY tickets.ticketid",
                "
            SELECT count(distinct tickets.ticketid) AS ticketsCount, count(distinct tickets.ticketid) AS totalitems FROM " . TABLE_PREFIX . "tickets AS tickets
            LEFT JOIN " . TABLE_PREFIX . "ticketposts AS ticketposts ON (tickets.ticketid = ticketposts.ticketid)
            LEFT JOIN " . TABLE_PREFIX . "ticketlocks AS ticketlocks ON (tickets.ticketid = ticketlocks.ticketid)
            LEFT JOIN " . TABLE_PREFIX . "ticketwatchers AS ticketwatchers ON (tickets.ticketid = ticketwatchers.ticketid AND ticketwatchers.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
            LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
            WHERE ((" . $_SWIFT_UserInterfaceGridObject->BuildFullTextSearch('tickets.subject') . ")
            OR (tickets.ticketid IN (SELECT ticketnotes.linktypeid FROM " . TABLE_PREFIX . "ticketnotes AS ticketnotes
        WHERE " . $_SWIFT_UserInterfaceGridObject->BuildFullTextSearch('ticketnotes.note') . "
            AND ticketnotes.linktype = 1 GROUP BY ticketnotes.linktypeid))
            OR (" . $_SWIFT_UserInterfaceGridObject->BuildFullTextSearch('ticketposts.contents') . " AND ".$_SWIFT_UserInterfaceGridObject->skipInlineImagesFromPostContentsSearchQuery('ticketposts.contents')."))
            AND " . implode(' AND ', $_sqlArrayContainer));
        }

        self::$_ticketFetchQuery = "SELECT tickets.ticketid FROM " . TABLE_PREFIX . "tickets AS tickets
            LEFT JOIN " . TABLE_PREFIX . "ticketlocks AS ticketlocks ON (tickets.ticketid = ticketlocks.ticketid)
            LEFT JOIN " . TABLE_PREFIX . "ticketwatchers AS ticketwatchers ON (tickets.ticketid = ticketwatchers.ticketid AND ticketwatchers.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
            LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
            WHERE " . implode(' AND ', $_sqlArrayContainer);

        $_SWIFT_UserInterfaceGridObject->SetQuery(self::$_ticketFetchQuery,
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'tickets AS tickets WHERE ' . implode(' AND ', $_sqlArrayContainer));

        $_SWIFT_UserInterfaceGridObject->SetSearchStoreOptions($_searchStoreID,
                "SELECT tickets.ticketid FROM " . TABLE_PREFIX . "tickets AS tickets
                LEFT JOIN " . TABLE_PREFIX . "ticketlocks AS ticketlocks ON (tickets.ticketid = ticketlocks.ticketid)
                LEFT JOIN " . TABLE_PREFIX . "ticketwatchers AS ticketwatchers ON (tickets.ticketid = ticketwatchers.ticketid AND ticketwatchers.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
                LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
                LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
                WHERE tickets.ticketid IN (%s)", SWIFT_SearchStore::TYPE_TICKETS, '/Tickets/Manage/Index/-1');

        // Set Tag Lookup Queries..
        $_SWIFT_UserInterfaceGridObject->SetTagOptions(SWIFT_TagLink::TYPE_TICKET,
        "SELECT tickets.ticketid FROM " . TABLE_PREFIX . "tickets AS tickets
                LEFT JOIN " . TABLE_PREFIX . "ticketlocks AS ticketlocks ON (tickets.ticketid = ticketlocks.ticketid)
                LEFT JOIN " . TABLE_PREFIX . "ticketwatchers AS ticketwatchers ON (tickets.ticketid = ticketwatchers.ticketid AND ticketwatchers.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
                LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
                LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
        WHERE " . implode(' AND ', $_sqlArrayContainer) . " AND tickets.ticketid IN (%s)",

        "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets AS tickets
        WHERE " . implode(' AND ', $_sqlArrayContainer) . " AND tickets.ticketid IN (%s)");

        $_SWIFT_UserInterfaceGridObject->AddField(new SWIFT_UserInterfaceGridField('ticketid', 'ticketid',
                        SWIFT_UserInterfaceGridField::TYPE_ID));

        $_SWIFT_UserInterfaceGridObject->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                        SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $_SWIFT_UserInterfaceGridObject->AddField(new SWIFT_UserInterfaceGridField('propertyicon', '&nbsp;',
                        SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        // Add the required columns
        foreach ($_ticketViewFieldsContainer as $_key => $_SWIFT_TicketFieldsObject) {
            $_fieldPointer = array('align' => 'left', 'title' => '', 'width' => '100');

            $_fieldName = $_SWIFT_TicketFieldsObject->GetProperty('fieldtypeid');
            if ($_SWIFT_TicketFieldsObject->GetProperty('fieldtype') == SWIFT_TicketViewField::TYPE_CUSTOM) {
                $_fieldName = 'c_' . $_SWIFT_TicketFieldsObject->GetProperty('fieldtypeid');
            }

            $_fieldPointer = $_ticketFieldsContainer[$_fieldName];

            if (!isset($_ticketFieldsContainer[$_fieldName]) || empty($_ticketFieldsContainer[$_fieldName])) {
                continue;
            }

            $_fieldTitle = $_fieldPointer['title'];
            if (isset($_fieldPointer['gridtitle'])) {
                // @codeCoverageIgnoreStart
                // Will not be reached
                $_fieldTitle = $_fieldPointer['gridtitle'];
            }
            // @codeCoverageIgnoreEnd

            $_fieldType = SWIFT_UserInterfaceGridField::TYPE_DB;
            if (isset($_fieldPointer['type']) && $_fieldPointer['type'] == 'custom') {
                $_fieldType = SWIFT_UserInterfaceGridField::TYPE_CUSTOM;
            }

            $_SWIFT_UserInterfaceGridObject->AddField(new SWIFT_UserInterfaceGridField($_fieldPointer['name'], $_fieldTitle,
                            $_fieldType, $_fieldPointer['width'],
                            IIF($_fieldPointer['align'] == 'center', SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::ALIGN_LEFT)));
        }

        $_sortFieldPointer = array();
        if (!isset($_ticketFieldsContainer[$_SWIFT_TicketViewObject->GetProperty('sortby')])) {
            $_sortFieldPointer = $_ticketFieldsContainer[SWIFT_TicketViewField::FIELD_LASTACTIVITY];
        } else {
            $_sortFieldPointer = $_ticketFieldsContainer[$_SWIFT_TicketViewObject->GetProperty('sortby')];
        }

        // Set Default Sorting Option
        $_SWIFT_UserInterfaceGridObject->SetSortFieldObject(new SWIFT_UserInterfaceGridField($_sortFieldPointer['name'],
                        $_sortFieldPointer['title'], SWIFT_UserInterfaceGridField::TYPE_DB, $_sortFieldPointer['width'],
                        IIF($_sortFieldPointer['align'] == 'center', SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::ALIGN_LEFT),
                        IIF($_SWIFT_TicketViewObject->GetProperty('sortorder') == SWIFT_TicketView::SORT_ASC, SWIFT_UserInterfaceGridField::SORT_ASC, SWIFT_UserInterfaceGridField::SORT_DESC)));

        // Set Tickets Per Page
        $_SWIFT_UserInterfaceGridObject->SetRecordsPerPage($_SWIFT_TicketViewObject->GetProperty('ticketsperpage'));

        // Set the Query Callback
        $_SWIFT_UserInterfaceGridObject->SetItemCallbackContainer(array('Tickets\Library\View\SWIFT_TicketViewRenderer', 'GridItemCallback'));

        // Auto Refresh Logic
        if ((int) ($_SWIFT_TicketViewObject->GetProperty('autorefresh')) > 0) {
            $_javascriptAppendHTML = '<script type="text/javascript">if (window.$UIObject) { window.$UIObject.Queue(function(){';
        //    $_javascriptAppendHTML .= 'AutoRefreshGrid(\'ticketmanagegrid\', \'' . (int) ($_SWIFT_TicketViewObject->GetProperty('autorefresh')) . '\', \'' . $_SWIFT->Router->GetCurrentURL() . '\');';
            $_javascriptAppendHTML .= 'AutoRefreshGrid(\'ticketmanagegrid\', \'' . (int) ($_SWIFT_TicketViewObject->GetProperty('autorefresh')) . '\');';
            $_javascriptAppendHTML .= '}); }</script>';

            if ($_SWIFT->UserInterface->IsAjax()) {
                echo $_javascriptAppendHTML;
            } else {
                $_SWIFT->Template->Assign('_extendedRefreshScript', $_javascriptAppendHTML);
            }
        }

        return true;
    }

    /**
     * Renders the Mass Action Panel and returns the HTML
     *
     * @author Varun Shoor
     * @return string "Mass Action Panel HTML" on Success, "false" otherwise
     */
    protected static function RenderMassActionPanel(SWIFT_UserInterface $_SWIFT_UserInterfaceObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');
        $_ticketLinkTypeCache = (array) $_SWIFT->Cache->Get('ticketlinktypecache');
        $_bayesCategoryCache = (array) $_SWIFT->Cache->Get('bayesiancategorycache');

        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        $_ticketFlagContainer = $_SWIFT_TicketFlagObject->GetFlagContainer();

        $_TicketViewPropertyManagerObject = new SWIFT_TicketViewPropertyManager();

//        $_TicketViewPropertyManagerObject->IncrementTicketFlag(1);
//        $_TicketViewPropertyManagerObject->IncrementTicketFlag(2);
//        $_TicketViewPropertyManagerObject->IncrementTicketFlag(3);

        $_GeneralTabObject = new SWIFT_UserInterfaceTab($_SWIFT_UserInterfaceObject, $_SWIFT->Language->Get('tabmassaction'), 'icon_form.gif', 0,
                        'general', true, false, 4);

        $_GeneralTabObject->LoadToolbar();
        $_GeneralTabObject->Toolbar->AddButton($_SWIFT->Language->Get('update'), 'fa-check-circle', 'GridMassActionPanel(\'' . 'ticketmanagegrid' .
                '\', \'\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_GeneralTabObject->Toolbar->AddButton($_SWIFT->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketview'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /**
         * ---------------------------------------------
         * Field Rendering Logic
         * ---------------------------------------------
         */
        // -- BEGIN DEPARTMENT
        $_extendedDepartmentHTML = '<div class="matopitemcontainer">';
        $_topDepartmentIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_DEPARTMENT, 10);

        $_index = 0;
        foreach ($_topDepartmentIDList as $_key => $_departmentID) {
            if (!isset($_departmentCache[$_departmentID]) || $_departmentCache[$_departmentID]['parentdepartmentid'] != '0' ||
                    $_departmentCache[$_departmentID]['departmentapp'] != APP_TICKETS) {
                continue;
            } else if ($_index > 4) {
                break;
            }

            $_departmentContainer = $_departmentCache[$_departmentID];
            $_displayIconImage = '<img src="' . SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif' . '" align="absmiddle" border="0" /> ';

            $_extendedDepartmentHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'departmentid\', \'' .
                    $_departmentID . '\')">' . $_displayIconImage . text_to_html_entities($_departmentContainer['title']) . '</div>';

            $_index++;
        }

        $_extendedDepartmentHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        $_index = 1;
        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-839 , Permission to restrict staff from moving tickets to departments they're not assigned to
         *
         * Comments: Fix for Mass Action Panel.
         */
        $_assignedDepartmentList        = (array) SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_SWIFT->Staff->GetStaffID(), APP_TICKETS);
        $_canChangeUnAssignedDepartment = $_SWIFT->Staff->GetPermission('staff_tcanchangeunassigneddepartment');

        /**
         * @var int $_departmentID
         * @var array $_departmentContainer
         */
        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if (!$_canChangeUnAssignedDepartment && !in_array($_departmentID, $_assignedDepartmentList)) {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_departmentContainer['title'];
            $_optionsContainer[$_index]['value'] = $_departmentID;

            $_index++;
            $subdepartments = $_departmentContainer['subdepartments'];
            if (isset($subdepartments) && _is_array($subdepartments)) {
                /**
                 * @var int $_subDepartmentID
                 * @var array $_subDepartmentContainer
                 */
                foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!$_canChangeUnAssignedDepartment && !in_array($_subDepartmentID, $_assignedDepartmentList)) {
                        continue;
                    }
                    $_optionsContainer[$_index]['title'] = '|- ' . $_subDepartmentContainer['title'];
                    $_optionsContainer[$_index]['value'] = $_subDepartmentID;
                    $_index++;
                }
            }
        }

        $_GeneralTabObject->Select('departmentid', $_SWIFT->Language->Get('madepartment'), '', $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', false, false, \'\', true); UpdateTicketTypeDiv(this, \'tickettypeid\', true, false); UpdateTicketOwnerDiv(this, \'staffid\', true, false);', '', $_extendedDepartmentHTML, '150');

        // -- BEGIN OWNER
        $_extendedStaffHTML = '<div class="matopitemcontainer">';
        $_topStaffIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_STAFF, 10);

        $_index = 0;
        foreach ($_topStaffIDList as $_key => $_staffID) {
            if (!isset($_staffCache[$_staffID]) || (isset($_staffCache[$_staffID]) && $_staffCache[$_staffID]['isenabled'] == '0')) {
                continue;
            } else if ($_index > 4) {
                break;
            }

            $_staffContainer = $_staffCache[$_staffID];

            $_extendedStaffHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'staffid\', \'' .
                    $_staffID . '\')">' . text_to_html_entities($_staffContainer['fullname']) . '</div>';

            $_index++;
        }

        $_extendedStaffHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_optionsContainer[1] = array();
        $_optionsContainer[1]['title'] = $_SWIFT->Language->Get('unassigned');
        $_optionsContainer[1]['value'] = '0';

        $_index = 2;
        foreach ($_staffCache as $_staffID => $_staffContainer) {
            if ($_staffContainer['isenabled'] == '0'){
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_staffContainer['fullname'];
            $_optionsContainer[$_index]['value'] = $_staffID;

            $_index++;
        }

        $_GeneralTabObject->Select('staffid', $_SWIFT->Language->Get('maowner'), '', $_optionsContainer, '', 'staffid_container', $_extendedStaffHTML, '150');

        // -- BEGIN TICKET TYPE
        $_extendedTicketTypeHTML = '<div class="matopitemcontainer">';
        $_topTicketTypeIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_TYPE, 10);

        $_index = 0;
        foreach ($_topTicketTypeIDList as $_key => $_ticketTypeID) {
            if (!isset($_ticketTypeCache[$_ticketTypeID]) || $_ticketTypeCache[$_ticketTypeID]['departmentid'] != '0') {
                continue;
            } else if ($_index > 4) {
                break;
            }

            $_ticketTypeContainer = $_ticketTypeCache[$_ticketTypeID];

            $_displayIconImage = '';
            if (!empty($_ticketTypeContainer['displayicon'])) {
                $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketTypeContainer['displayicon']) . '" align="absmiddle" border="0" /> ';
            }

            $_extendedTicketTypeHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'tickettypeid\', \'' .
                    $_ticketTypeID . '\')">' . $_displayIconImage . htmlspecialchars($_ticketTypeContainer['title']) . '</div>';

            $_index++;
        }

        $_extendedTicketTypeHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        foreach ($_ticketTypeCache as $_ticketTypeID => $_ticketTypeContainer) {
            if ($_ticketTypeContainer['departmentid'] != '0') {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_ticketTypeContainer['title'];
            $_optionsContainer[$_index]['value'] = $_ticketTypeID;

            $_index++;
        }

        $_GeneralTabObject->Select('tickettypeid', $_SWIFT->Language->Get('matickettype'), '', $_optionsContainer, '', 'tickettypeid_container', $_extendedTicketTypeHTML, '150');

        // -- BEGIN TICKET STATUS
        $_staffGroupTicketStatusIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_SWIFT->Staff->GetProperty('staffgroupid'));

        $_extendedTicketStatusHTML = '<div class="matopitemcontainer">';
        $_topTicketStatusIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_STATUS, 10);

        $_index = 0;
        foreach ($_topTicketStatusIDList as $_key => $_ticketStatusID) {
            if (!isset($_ticketStatusCache[$_ticketStatusID]) || $_ticketStatusCache[$_ticketStatusID]['departmentid'] != '0') {
                continue;
            } else if ($_index > 4) {
                break;
            } else if ($_ticketStatusCache[$_ticketStatusID]['staffvisibilitycustom'] == '1' && !in_array($_ticketStatusID, $_staffGroupTicketStatusIDList)) {
                continue;
            }

            $_ticketStatusContainer = $_ticketStatusCache[$_ticketStatusID];

            $_displayIconImage = '';
            if (!empty($_ticketStatusContainer['displayicon'])) {
                $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketStatusContainer['displayicon']) . '" align="absmiddle" border="0" /> ';
            }

            $_extendedTicketStatusHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'ticketstatusid\', \''
                    . $_ticketStatusID . '\')" >'
                    . $_displayIconImage . htmlspecialchars($_ticketStatusContainer['title']) . '</div>';

            $_index++;
        }

        $_extendedTicketStatusHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            if ($_ticketStatusContainer['departmentid'] != '0' ||
                    ($_ticketStatusCache[$_ticketStatusID]['staffvisibilitycustom'] == '1' && !in_array($_ticketStatusID, $_staffGroupTicketStatusIDList))) {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_ticketStatusContainer['title'];
            $_optionsContainer[$_index]['value'] = $_ticketStatusID;

            $_index++;
        }

        $_GeneralTabObject->Select('ticketstatusid', $_SWIFT->Language->Get('maticketstatus'), '', $_optionsContainer, '', 'ticketstatusid_container', $_extendedTicketStatusHTML, '150');

        // -- BEGIN TICKET PRIORITY
        $_extendedTicketPriorityHTML = '<div class="matopitemcontainer">';
        $_topTicketPriorityIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_PRIORITY, 10);

        $_index = 0;
        foreach ($_topTicketPriorityIDList as $_key => $_ticketPriorityID) {
            if (!isset($_ticketPriorityCache[$_ticketPriorityID])) {
                continue;
            } else if ($_index > 4) {
                break;
            }

            $_ticketPriorityContainer = $_ticketPriorityCache[$_ticketPriorityID];

            $_displayIconImage = '';
            if (!empty($_ticketPriorityContainer['displayicon'])) {
                $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketPriorityContainer['displayicon']) .
                        '" align="absmiddle" border="0" /> ';
            }

            $_extendedTicketPriorityHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'ticketpriorityid\', \'' .
                    $_ticketPriorityID . '\')" style="background-color: ' . $_ticketPriorityContainer['bgcolorcode'] .
                    ' !important; color: ' . $_ticketPriorityContainer['frcolorcode'] . ' !important;">' .
                    $_displayIconImage . htmlspecialchars($_ticketPriorityContainer['title']) . '</div>';

            $_index++;
        }

        $_extendedTicketPriorityHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriorityContainer) {
            $_optionsContainer[$_index]['title'] = $_ticketPriorityContainer['title'];
            $_optionsContainer[$_index]['value'] = $_ticketPriorityID;

            $_index++;
        }

        $_GeneralTabObject->Select('ticketpriorityid', $_SWIFT->Language->Get('maticketpriority'), '', $_optionsContainer, '', '', $_extendedTicketPriorityHTML, '150');

        // -- BEGIN BAYESIAN
        $_extendedBayesianHTML = '<div class="matopitemcontainer">';
        $_topBayesCategoryIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_BAYES, 10);

        $_index = 0;
        foreach ($_topBayesCategoryIDList as $_key => $_bayesCategoryID) {
            if (!isset($_bayesCategoryCache[$_bayesCategoryID])) {
                continue;
            } else if ($_index > 4) {
                break;
            }

            $_bayesCategoryContainer = $_bayesCategoryCache[$_bayesCategoryID];

            $_extendedBayesianHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'bayescategoryid\', \'' .
                    $_bayesCategoryID . '\')">' . htmlspecialchars($_bayesCategoryContainer['category']) . '</div>';

            $_index++;
        }

        $_extendedBayesianHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        foreach ($_bayesCategoryCache as $_bayesCategoryID => $_bayesCategoryContainer) {
            $_optionsContainer[$_index]['title'] = $_bayesCategoryContainer['category'];
            $_optionsContainer[$_index]['value'] = $_bayesCategoryID;

            $_index++;
        }

        $_GeneralTabObject->Select('bayescategoryid', $_SWIFT->Language->Get('mabayescategory'), '', $_optionsContainer, '', '', $_extendedBayesianHTML, '150');


        // -- BEGIN TICKET LINK
        $_extendedTicketLinkHTML = '<div class="matopitemcontainer">';
        $_topTicketLinkTypeIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_LINK, 10);

        $_index = 0;
        foreach ($_topTicketLinkTypeIDList as $_key => $_ticketLinkTypeID) {
            if (!isset($_ticketLinkTypeCache[$_ticketLinkTypeID])) {
                continue;
            } else if ($_index > 3) {
                break;
            }

            $_ticketLinkTypeContainer = $_ticketLinkTypeCache[$_ticketLinkTypeID];

            $_displayIconImage = '<img src="' . SWIFT::Get('themepathimages') . 'icon_link.png' . '" align="absmiddle" border="0" /> ';

            $_extendedTicketLinkHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'ticketlinktypeid\', \'' .
                    $_ticketLinkTypeID . '\');">' . $_displayIconImage . htmlspecialchars($_ticketLinkTypeContainer['linktypetitle']) . '</div>';

            $_index++;
        }

        $_extendedTicketLinkHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        foreach ($_ticketLinkTypeCache as $_ticketLinkTypeID => $_ticketLinkTypeContainer) {
            $_optionsContainer[$_index]['title'] = $_ticketLinkTypeContainer['linktypetitle'];
            $_optionsContainer[$_index]['value'] = $_ticketLinkTypeID;

            $_index++;
        }

        $_GeneralTabObject->Select('ticketlinktypeid', $_SWIFT->Language->Get('maticketlink'), '', $_optionsContainer, '', '', $_extendedTicketLinkHTML, '150');


        // -- BEGIN TICKET FLAG
        $_extendedTicketFlagHTML = '<div class="matopitemcontainer">';
        $_topTicketFlagIDList = $_TicketViewPropertyManagerObject->GetTopTicketItems(SWIFT_TicketViewPropertyManager::PROPERTY_FLAG, 10);

        $_index = 0;
        foreach ($_topTicketFlagIDList as $_key => $_ticketFlagID) {
            if (!isset($_ticketFlagContainer[$_ticketFlagID])) {
                continue;
            } else if ($_index > 3) {
                break;
            }

            $_ticketFlagActiveContainer = $_ticketFlagContainer[$_ticketFlagID];

            $_displayIconImage = '<img src="' . SWIFT::Get('themepathimages') . $_ticketFlagActiveContainer[2] . '" align="absmiddle" border="0" /> ';

            $_extendedTicketFlagHTML .= '<div class="matopitem" onclick="javascript: UpdateMassActionSelectBox(\'ticketflagid\', \'' .
                    $_ticketFlagID . '\');" style="background-color: ' . $_ticketFlagActiveContainer[1] . '; color: white;">' . $_displayIconImage .
                    htmlspecialchars($_ticketFlagActiveContainer[0]) . '</div>';

            $_index++;
        }

        $_extendedTicketFlagHTML .= '</div>';

        $_optionsContainer = array();
        $_optionsContainer[0] = array();
        $_optionsContainer[0]['title'] = $_SWIFT->Language->Get('manochange');
        $_optionsContainer[0]['value'] = '-1';
        $_optionsContainer[0]['selected'] = true;

        $_optionsContainer[1]['title'] = $_SWIFT->Language->Get('manoflag');
        $_optionsContainer[1]['value'] = '0';

        $_index = 2;
        foreach ($_ticketFlagContainer as $_ticketFlagID => $_ticketFlagActiveContainer) {
            $_optionsContainer[$_index]['title'] = $_ticketFlagActiveContainer[0];
            $_optionsContainer[$_index]['value'] = $_ticketFlagID;

            $_index++;
        }

        $_GeneralTabObject->Select('ticketflagid', $_SWIFT->Language->Get('maticketflag'), '', $_optionsContainer, '', '', $_extendedTicketFlagHTML, '150');

        // -- BEGIN TAGS
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_GeneralTabObject->TextMultipleAutoComplete('addtags', $_SWIFT->Language->Get('maaddtags'), false, '/Base/Tags/QuickSearch', array(), 'fa-tags', false, true, false, '150');
            $_GeneralTabObject->TextMultipleAutoComplete('removetags', $_SWIFT->Language->Get('maremovetags'), false, '/Base/Tags/QuickSearch', array(), 'fa-tags', false, true, false, '150');
        }


        /**
         * ---------------------------------------------
         * Tab Rendering Logic
         * ---------------------------------------------
         */
        $_tabContainer = array();
        $_tabContainer[] = $_GeneralTabObject;

        $_formName = 'ticketgrid';

        return SWIFT_UserInterfaceControlPanel::RenderMassActionPanelTabs($_formName, $_tabContainer);
    }

    /**
     * The grid callback function
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceGrid $_SWIFT_UserInterfaceGridObject The Grid Object
     * @param string $_selectQuery The SELECT Query Statement
     * @param bool $_fetchAll (OPTIONAL) Whether to fetch all items
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GridItemCallback(SWIFT_UserInterfaceGrid $_SWIFT_UserInterfaceGridObject, $_selectQuery, $_fetchAll = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_GridSortObject = $_SWIFT_UserInterfaceGridObject->GetSortFieldObject();
        if (!$_GridSortObject instanceof SWIFT_UserInterfaceGridField || !$_GridSortObject->GetIsClassLoaded()) {
            return false;
        }

        $_itemContainer = $_ticketIDList = array();

        $_sortStatement = '';
        if ($_GridSortObject->GetType() !== SWIFT_UserInterfaceGridField::TYPE_CUSTOM) {
            $_sortStatement = ' ORDER BY ' . $_GridSortObject->GetName() . ' ' . Clean($_GridSortObject->GetSortOrder());
        }

        $_finalQuery = $_selectQuery . $_sortStatement;

        if ($_fetchAll == true) {
            $_queryResult = $_SWIFT->Database->Query($_finalQuery, 3);
        } else {
            $_queryResult = $_SWIFT->Database->QueryLimit($_finalQuery, $_SWIFT_UserInterfaceGridObject->GetRecordsPerPage(), $_SWIFT_UserInterfaceGridObject->GetOffset(), 3);
        }

        if (!$_queryResult) {
            return $_itemContainer;
        }

        while ($_SWIFT->Database->NextRecord(3)) {
            $_ticketIDList[] = $_SWIFT->Database->Record3['ticketid'];
        }

        // Now that we have the ticket ids run the final queries
        $_stringTicketIDList = BuildIN($_ticketIDList);

        $_tempCustomFieldList = self::RetrieveCustomFieldIDList();

        // will be overwritten by extract
        $_ticketCustomFieldIDList = [];
        $_userCustomFieldIDList = [];
        $_userOrganizationCustomFieldIDList = [];
        extract($_tempCustomFieldList, EXTR_OVERWRITE);

        $_fetchQuery = "SELECT tickets.*, ticketlocks.dateline AS lockdateline, ticketlocks.staffid AS lockstaffid,
                userorganizations.organizationname AS userorganizationname, usergroups.title AS usergrouptitle, userorganizations.userorganizationid AS userorganizationid,
                ticketwatchers.staffid AS ticketwatcherstaffid FROM " . TABLE_PREFIX . "tickets AS tickets
                LEFT JOIN " . TABLE_PREFIX . "ticketlocks AS ticketlocks ON (tickets.ticketid = ticketlocks.ticketid)
                LEFT JOIN " . TABLE_PREFIX . "ticketwatchers AS ticketwatchers ON (tickets.ticketid = ticketwatchers.ticketid AND ticketwatchers.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
                LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                LEFT JOIN " . TABLE_PREFIX . "ticketpriorities AS ticketpriorities ON (tickets.priorityid = ticketpriorities.priorityid)
                LEFT JOIN " . TABLE_PREFIX . "ticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)
                WHERE tickets.ticketid IN (" . $_stringTicketIDList . ") " . $_sortStatement;

        $_userIDList = $_userOrganizationIDList = array();
        $_SWIFT->Database->Query($_fetchQuery, 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_itemContainer[$_SWIFT->Database->Record3['ticketid']] = $_SWIFT->Database->Record3;

            if ($_SWIFT->Database->Record3['userid'] != '0' && !in_array($_SWIFT->Database->Record3['userid'], $_userIDList)) {
                $_userIDList[] = $_SWIFT->Database->Record3['userid'];
            }

            if ($_SWIFT->Database->Record3['userorganizationid'] != '0' && !in_array($_SWIFT->Database->Record3['userorganizationid'], $_userOrganizationIDList)) {
                $_userOrganizationIDList[] = $_SWIFT->Database->Record3['userorganizationid'];
            }
        }

        $_userTicketListCustomFieldMap = $_userOrganizationTicketListCustomFieldMap = array();

        // Ticket Custom Fields
        if (_is_array($_ticketCustomFieldIDList)) {
            // @codeCoverageIgnoreStart
            // this code will never be executed, variable not assigned
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues LEFT JOIN " . TABLE_PREFIX . "customfields AS customfields ON (customfields.customfieldid = customfieldvalues.customfieldid)
                WHERE customfieldvalues.customfieldid IN (" . BuildIN($_ticketCustomFieldIDList) . ") AND customfieldvalues.typeid IN (" . $_stringTicketIDList . ")", 4);
            while ($_SWIFT->Database->NextRecord(4)) {
                $_itemContainer[$_SWIFT->Database->Record4['typeid']]['custom' . $_SWIFT->Database->Record4['customfieldid']] = self::GetCustomFieldValues($_SWIFT->Database->Record4);
            }
        }
        // @codeCoverageIgnoreEnd

        // User Custom Fields
        if (_is_array($_userCustomFieldIDList) && _is_array($_userIDList)) {
            // @codeCoverageIgnoreStart
            // this code will never be executed, variable not assigned
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues LEFT JOIN " . TABLE_PREFIX . "customfields AS customfields ON (customfields.customfieldid = customfieldvalues.customfieldid)
                WHERE customfieldvalues.customfieldid IN (" . BuildIN($_userCustomFieldIDList) . ") AND customfieldvalues.typeid IN (" . BuildIN($_userIDList) . ")", 4);
            while ($_SWIFT->Database->NextRecord(4)) {
                $_userTicketListCustomFieldMap[$_SWIFT->Database->Record4['typeid']]['custom' . $_SWIFT->Database->Record4['customfieldid']] = self::GetCustomFieldValues($_SWIFT->Database->Record4);
            }
        }
        // @codeCoverageIgnoreEnd

        // User Organization Custom Fields
        if (_is_array($_userOrganizationCustomFieldIDList) && _is_array($_userOrganizationIDList)) {
            // @codeCoverageIgnoreStart
            // this code will never be executed, variable not assigned
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues LEFT JOIN " . TABLE_PREFIX . "customfields AS customfields ON (customfields.customfieldid = customfieldvalues.customfieldid)
                WHERE customfieldvalues.customfieldid IN (" . BuildIN($_userOrganizationCustomFieldIDList) . ") AND customfieldvalues.typeid IN (" . BuildIN($_userOrganizationIDList) . ")", 4);
            while ($_SWIFT->Database->NextRecord(4)) {
                $_userOrganizationTicketListCustomFieldMap[$_SWIFT->Database->Record4['typeid']]['custom' . $_SWIFT->Database->Record4['customfieldid']] = self::GetCustomFieldValues($_SWIFT->Database->Record4);
            }
        }
        // @codeCoverageIgnoreEnd

        SWIFT::Set('_userTicketListCustomFieldMap', $_userTicketListCustomFieldMap);
        SWIFT::Set('_userOrganizationTicketListCustomFieldMap', $_userOrganizationTicketListCustomFieldMap);

        return $_itemContainer;
    }

    /**
     * Retrieve the Custom field values from Database values array.
     *
     * @author Mahesh Salaria
     * @param array $_customFieldsDataValues
     * @return string Fields Values
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetCustomFieldValues($_customFieldsDataValues)
    {
        $_fieldValue = htmlspecialchars($_customFieldsDataValues['fieldvalue']);
        $_customFieldID = $_customFieldsDataValues['customfieldid'];
        $_uniqueHash = $_customFieldsDataValues['uniquehash'];
        $_typeID = $_customFieldsDataValues['typeid'];
        $_isSerialized = $_customFieldsDataValues['isserialized'];
        $_fieldType = $_customFieldsDataValues['fieldtype'];

        switch ($_fieldType) {
            case SWIFT_CustomField::TYPE_FILE:
                $_fileLink = '';
                if (!empty($_fieldValue)) {
                    try {
                        $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fieldValue);
                        $_filePath = $_SWIFT_FileManagerObject->GetPath();

                        if (file_exists($_filePath)) {
                            $_fieldValue = '<img src="' . SWIFT::Get('themepathimages') . 'icon_file.gif" align="absmiddle" border="0" /> <a href="' . SWIFT::Get('basename') . '/Base/CustomField/Dispatch/' . $_customFieldID . '/' . $_uniqueHash . '" target="_blank">' . htmlspecialchars($_SWIFT_FileManagerObject->GetProperty('originalfilename')) . ' (' . FormattedSize(filesize($_filePath)) . ')</a>';
                        }
                        unset($_SWIFT_FileManagerObject);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                    }
                }
                break;

            case SWIFT_CustomField::TYPE_SELECTLINKED:
                $_fieldValue = self::GetConcatenatedCustomFieldOptions($_customFieldsDataValues);
                break;

            case SWIFT_CustomField::TYPE_PASSWORD:
                $_fieldValue = htmlspecialchars(SWIFT_CustomFieldManager::Decrypt($_customFieldsDataValues['fieldvalue']));
                ;
                break;

            case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                $_fieldValue = self::GetConcatenatedCustomFieldOptions($_customFieldsDataValues);
                break;
            case SWIFT_CustomField::TYPE_CHECKBOX:
                $_fieldValue = self::GetConcatenatedCustomFieldOptions($_customFieldsDataValues);
                break;
            case SWIFT_CustomField::TYPE_RADIO:
                $_fieldValue = self::GetConcatenatedCustomFieldOptions($_customFieldsDataValues);
                break;
            case SWIFT_CustomField::TYPE_SELECT:
                $_fieldValue = self::GetConcatenatedCustomFieldOptions($_customFieldsDataValues);
                break;

            case SWIFT_CustomField::TYPE_DATE:
                $_customFieldValue = GetCalendarDateline($_fieldValue);
                if (!empty($_customFieldValue)) {
                    $_fieldValue = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, (int) ($_customFieldValue), false, true);
                }
                break;
        }

        $_itemContainer[$_typeID]['custom' . $_customFieldID] = $_fieldValue;

        return $_fieldValue;
    }

    /**
     * Retrieve the contactenated custom field option list
     *
     * @author Varun Shoor
     * @param array $_customFieldOptionIDList
     * @return string The Concatenated Custom Field Option List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetConcatenatedCustomFieldOptions($_customFieldOptionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldOptionIDList)) {
            return '';
        }
        $_fieldValue = '';

        if ($_customFieldOptionIDList['isserialized'] == '1' && $_customFieldOptionIDList['isencrypted'] == '1') {
            $_fieldValueDecrypted = SWIFT_CustomFieldManager::Decrypt($_customFieldOptionIDList['fieldvalue']);
            $_fieldValue = @mb_unserialize($_fieldValueDecrypted);
        } else if ($_customFieldOptionIDList['isserialized'] == '1') {
            $_fieldValue = @mb_unserialize($_customFieldOptionIDList['fieldvalue']);
        } else {
            $_fieldValue = $_customFieldOptionIDList['fieldvalue'];
        }

        $_customFieldID = $_customFieldOptionIDList['customfieldid'];
        $_filedType = $_customFieldOptionIDList['fieldtype'];

        $_customFieldMapCache = (array) $_SWIFT->Cache->Get('customfieldmapcache');

        $_customFieldOptionList = $_customFieldOptionMapList = array();
        $_storeCustomFieldOptionID = "";

        if (_is_array($_fieldValue)) {
            foreach ($_fieldValue as $_customFieldOptionID) {
                if (!is_array($_customFieldOptionID)) {
                    if (isset($_customFieldMapCache[$_customFieldID]['_options'][$_customFieldOptionID]['optionvalue'])) {
                        $_customFieldOptionList[] = $_customFieldMapCache[$_customFieldID]['_options'][$_customFieldOptionID]['optionvalue'];
                    }

                    $_storeCustomFieldOptionID = $_customFieldOptionID;
                } else {
                    foreach ($_customFieldOptionID as $_key => $_val) {
                        if ($_key == $_storeCustomFieldOptionID) {

                            if (isset($_customFieldMapCache[$_customFieldID]['_options'])) {
                                $_optionPrefix = '';

                                if (isset($_customFieldMapCache[$_customFieldID]['_options'][$_val])) {
                                    if ($_customFieldMapCache[$_customFieldID]['_options'][$_val]['parentcustomfieldoptionid'] != '0') {
                                        $_optionPrefix = '<img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow_blue.gif" align="absmiddle" border="0" /> ';
                                    }
                                    $_customFieldOptionList[] = $_optionPrefix . $_customFieldMapCache[$_customFieldID]['_options'][$_val]['optionvalue'];
                                }
                            }
                        }
                    }
                }
            }
        } else if (isset($_customFieldMapCache[$_customFieldID]['_options'][$_fieldValue]['optionvalue'])) {
            $_customFieldOptionList[] = $_customFieldMapCache[$_customFieldID]['_options'][$_fieldValue]['optionvalue'];
        }

        return implode("<br/>", $_customFieldOptionList);
    }

    /**
     * Dispatches the XML Menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DispatchMenu($_defaultDepartmentID = -1, $_defaultTicketStatusID = -1, $_defaultTicketTypeID = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketViewCache = (array) $_SWIFT->Cache->Get('ticketviewcache');

        echo '<ul class="swiftdropdown swiftdropdownposition" id="ticketviewmenu">';

        foreach ($_ticketViewCache as $_ticketViewID => $_ticketViewContainer) {
            $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataStore($_ticketViewContainer));
            if (!$_SWIFT_TicketViewObject->CanStaffView()) {
                continue;
            }

            echo '<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData(\'' . SWIFT::Get('basename') .
            '/Tickets/Manage/View/' . $_ticketViewID . '/' .
            $_defaultDepartmentID . '/' . $_defaultTicketStatusID . '/' . $_defaultTicketTypeID .
            '/\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' .
            htmlspecialchars(StripName($_ticketViewContainer['title'], 22)) . '</div></div></li>';
        }

        echo '</ul>';

        return true;
    }

    /**
     * Switch the active ticket view
     *
     * @author Varun Shoor
     * @param SWIFT_TicketView $_SWIFT_TicketViewObject The New Ticket View Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ChangeView(SWIFT_TicketView $_SWIFT_TicketViewObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffTicketPropertiesCache = (array) $_SWIFT->Cache->Get('staffticketpropertiescache');

        if (!isset($_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()])) {
            $_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()] = array();
        }

        $_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()]['ticketviewid'] = $_SWIFT_TicketViewObject->GetTicketViewID();

        $_SWIFT->Cache->Update('staffticketpropertiescache', $_staffTicketPropertiesCache);

        SWIFT::Set('forceViewChange', true);

        // Now update the ticket view in session
        $_SWIFT->Session->UpdateTicketView($_SWIFT_TicketViewObject->GetTicketViewID());

        return true;
    }

    /**
     * Render the Ticket Tree
     *
     * @author Varun Shoor
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_incomingDepartmentID (OPTIONAL) The Department ID
     * @param int $_incomingTicketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_incomingTicketTypeID (OPTIONAL) The Ticket Type ID
     * @return string The Rendered HTML
     */
    public static function RenderTree($_listType = 'inbox', $_incomingDepartmentID = -1, $_incomingTicketStatusID = -1, $_incomingTicketTypeID = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_listType)) {
            $_listType = 'inbox';
        }

        if ($_incomingDepartmentID == '0') {
            $_listType = 'trash';
        }

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        $_totalUnresolvedItems = 0;

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);
        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);

        $_inboxClass = 'inbox';
        $_inboxExtend = $_flaggedExtend = $_watchedExtend = $_renderDepartmentHTML = '';

        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if (!in_array($_departmentID, $_assignedDepartmentIDList)) {
                continue;
            }

            $_renderContainer = self::RenderTreeDepartment($_departmentContainer, false, $_listType, $_incomingDepartmentID, $_incomingTicketStatusID, $_incomingTicketTypeID);
            $_renderDepartmentHTML .= $_renderContainer[0];
            $_totalUnresolvedItems += $_renderContainer[1];

            $subdepartments = (array)$_departmentContainer['subdepartments'];
            if (_is_array($subdepartments)) {
                /**
                 * @var int $_subDepartmentID
                 * @var array $_subDepartmentContainer
                 */
                foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!in_array($_subDepartmentID, $_assignedDepartmentIDList)) {
                        continue;
                    }

                    $_renderContainer = self::RenderTreeDepartment($_subDepartmentContainer, true, $_listType, $_incomingDepartmentID, $_incomingTicketStatusID, $_incomingTicketTypeID);

                    $_renderDepartmentHTML .= $_renderContainer[0];
                    $_renderDepartmentHTML .= '</ul></li>';

                    $_totalUnresolvedItems += $_renderContainer[1];
                }
            }
            $_renderDepartmentHTML .= '</ul></li>';
        }

        if (!empty($_totalUnresolvedItems)) {
            $_inboxExtend = '&nbsp;<font color=\'darkgreen\'>' . number_format($_totalUnresolvedItems, 0) . '</font>';
            $_inboxClass = 'inboxfull';
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="' . $_inboxClass . '"><a href="' . SWIFT::Get('basename') . '/Tickets/Manage/Index/-1" viewport="1" title="'. htmlspecialchars($_SWIFT->Language->Get('treeinbox')) . '">' . IIF($_listType == 'inbox' && $_incomingDepartmentID == -1, '<b>') .
                htmlspecialchars($_SWIFT->Language->Get('treeinbox')) . IIF($_listType == 'inbox' && $_incomingDepartmentID == -1, '</b>') . $_inboxExtend . '</a></span></li>';

        $_myTicketsExtend = $_unassignedExtend = $_trashExtend = '';
        if (isset($_ticketCountCache['ownerstaff'][$_SWIFT->Staff->GetStaffID()])) {
            $_myTicketsContainer = $_ticketCountCache['ownerstaff'][$_SWIFT->Staff->GetStaffID()];

            if ($_myTicketsContainer['totalunresolveditems'] > 0) {
                $_myTicketsExtend = '&nbsp;<font color=\'darkgreen\'>' . number_format($_myTicketsContainer['totalunresolveditems'], 0) . '</font>';
            }
        }

        if (isset($_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()])) {
            $_unassignedTicketsContainer = $_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()];

            if ($_unassignedTicketsContainer['totalunresolveditems'] > 0) {
                $_unassignedExtend = '&nbsp;<font color=\'darkgreen\'>' . number_format($_unassignedTicketsContainer['totalunresolveditems'], 0) . '</font>';
            }
        }

        $_trashCount = 0;
        if (isset($_ticketCountCache['departments'][0]) && $_ticketCountCache['departments'][0]['totalitems'] > 0) {
            $_trashCount = $_ticketCountCache['departments'][0]['totalitems'];
            $_trashExtend = '&nbsp;<font color=\'darkgreen\'>' . number_format($_ticketCountCache['departments'][0]['totalitems'], 0) . '</font>';
        }

        $_renderHTML .= '<li><span class="mytickets"><a href="' . SWIFT::Get('basename') . '/Tickets/Manage/MyTickets/-1" viewport="1" title="'. htmlspecialchars($_SWIFT->Language->Get('treemytickets')) .'">' . IIF($_listType == 'mytickets', '<b>') .
                htmlspecialchars($_SWIFT->Language->Get('treemytickets')) . IIF($_listType == 'mytickets', '</b>') . $_myTicketsExtend . '</a></span></li>';

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        if ($_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '1') {
            $_renderHTML .= '<li><span class="unassignedtickets"><a href="' . SWIFT::Get('basename') . '/Tickets/Manage/Unassigned/-1" viewport="1" title="' . htmlspecialchars($_SWIFT->Language->Get('treeunassigned')) . '">' . IIF($_listType == 'unassigned', '<b>') .
                htmlspecialchars($_SWIFT->Language->Get('treeunassigned')) . IIF($_listType == 'unassigned', '</b>') . $_unassignedExtend . '</a></span></li>';
        }

        $_renderHTML .= '<li><span class="trash' . IIF($_trashCount > 0, 'full') . '"><a href="' . SWIFT::Get('basename') . '/Tickets/Manage/Filter/0/-1/-1/-1" viewport="1" title="' . htmlspecialchars($_SWIFT->Language->Get('treetrash')) . '">' . IIF($_listType == 'trash', '<b>') .
                htmlspecialchars($_SWIFT->Language->Get('treetrash')) . IIF($_listType == 'trash', '</b>') . $_trashExtend . '</a></span></li>';

        // Begin Hook: staff_ticket_tree
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_tree')) ? eval($_hookCode) : false;
        // End Hook

        // Process the departments
        $_renderHTML .= $_renderDepartmentHTML;

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the Tree Department Node
     *
     * @author Varun Shoor
     * @param array $_departmentContainer The Department Container
     * @param bool $_isSubDepartment Whether this is a sub department
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_incomingDepartmentID (OPTIONAL) The Department ID
     * @param int $_incomingTicketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_incomingTicketTypeID (OPTIONAL) The Ticket Type ID
     * @return array array(The Rendered HTML, Total Unresolved Items)
     */
    public static function RenderTreeDepartment($_departmentContainer, $_isSubDepartment = false, $_listType = 'inbox', $_incomingDepartmentID = -1, $_incomingTicketStatusID = -1, $_incomingTicketTypeID = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        $_departmentClass = 'folder';

        $_totalUnresolvedItems = 0;

        $_departmentTitleExtend = '';

        $_ticketCount = 0;
        if (isset($_ticketCountCache['departments'][$_departmentContainer['departmentid']])) {
            $_ticketCount = (int) ($_ticketCountCache['departments'][$_departmentContainer['departmentid']]['totalitems']);

            if ($_ticketCountCache['departments'][$_departmentContainer['departmentid']]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                $_departmentClass = 'folderred';
            }

            $_totalUnresolvedItems = $_ticketCountCache['departments'][$_departmentContainer['departmentid']]['totalunresolveditems'];
            $_totalUnresolvedItemsParsed =  number_format($_totalUnresolvedItems, 0);

            if ($_totalUnresolvedItemsParsed == '(0)') {
                // @codeCoverageIgnoreStart
                // Will never be parsed to the format (0)
                $_totalUnresolvedItemsParsed = '';
            } else {
                // @codeCoverageIgnoreEnd
                $_departmentTitleExtend = '&nbsp;<font color=\'darkgreen\'>' . $_totalUnresolvedItemsParsed . '</font>';
            }
        }

        $_renderHTML = '<li><span class="' . $_departmentClass . '"><a href="' . SWIFT::Get('basename') . '/Tickets/Manage/Filter/' . $_departmentContainer['departmentid'] . '/-1/-1/-1' . '" viewport="1" title="'.text_to_html_entities($_departmentContainer['title']).'">' .
                IIF($_incomingDepartmentID == $_departmentContainer['departmentid'], '<b>') . IIF (mb_strlen(text_to_html_entities($_departmentContainer['title'])) > 25 , mb_substr(text_to_html_entities($_departmentContainer['title']), 0, 25) . '...', text_to_html_entities($_departmentContainer['title'])) .
                IIF($_incomingDepartmentID == $_departmentContainer['departmentid'], '</b>') . $_departmentTitleExtend . '</a></span>';

        $_renderHTML .= '<ul>';

        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            if ($_ticketStatusContainer['departmentid'] != '0' && $_ticketStatusContainer['departmentid'] != $_departmentContainer['departmentid']) {
                continue;
            }

            $_statusClass = 'yellowdot';
            $_statusTitleExtend = '';

            if (isset($_ticketCountCache['departments'][$_departmentContainer['departmentid']])) {
                $_parentPropertyContainer = $_ticketCountCache['departments'][$_departmentContainer['departmentid']]['ticketstatus'];

                if (isset($_parentPropertyContainer[$_ticketStatusID])) {
                    if ($_parentPropertyContainer[$_ticketStatusID]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                        $_statusClass = 'reddot';
                    }

                    $_statusCount = number_format($_parentPropertyContainer[$_ticketStatusID]['totalitems'], 0);
                    if ($_statusCount == '0' || $_ticketStatusContainer['displaycount'] != 1) {
                        $_statusCount = '';
                    } else {
                        $_statusTitleExtend = '&nbsp;<font color=\'darkgreen\'>' . $_statusCount . '</font>';
                    }
                }
            }

            $_renderHTML .= '<li><span class="' . $_statusClass . '"><a href="' . SWIFT::Get('basename') . '/Tickets/Manage/Filter/' . $_departmentContainer['departmentid'] . '/' . $_ticketStatusID . '/-1/-1' . '" viewport="1" title="'.htmlspecialchars($_ticketStatusContainer['title']).'">' .
                    IIF($_ticketStatusID == $_incomingTicketStatusID && $_incomingDepartmentID == $_departmentContainer['departmentid'], '<b>') . IIF (mb_strlen(htmlspecialchars($_ticketStatusContainer['title'])) > 25 , mb_substr(htmlspecialchars($_ticketStatusContainer['title']), 0, 25) . '...', htmlspecialchars($_ticketStatusContainer['title'])) .
                    IIF($_ticketStatusID == $_incomingTicketStatusID && $_incomingDepartmentID == $_departmentContainer['departmentid'], '</b>') . $_statusTitleExtend . '</a></span></li>';
        }

        return array($_renderHTML, $_totalUnresolvedItems);
    }

    /**
     * Retrieve the Ticket Status ID List
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List
     * @return array The Ticket Status ID List
     */
    public static function GetTicketStatusIDList($_departmentIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketStatusIDList = array();

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            // Is a search, include both resolved and unresolved tickets
            if ((isset($_POST['_searchQuery']) && !empty($_POST['_searchQuery'])) || (isset($_POST['query']) && !empty($_POST['query'])) || (isset($_POST['criteriaoptions']) && !empty($_POST['criteriaoptions']))) {
                if ($_ticketStatusContainer['departmentid'] == '0' || in_array($_ticketStatusContainer['departmentid'], $_departmentIDList)) {
                    $_ticketStatusIDList[] = $_ticketStatusID;
                }
            } else {
                // Not a search, don't show resolved tickets by default
                if (($_ticketStatusContainer['departmentid'] == '0' || in_array($_ticketStatusContainer['departmentid'], $_departmentIDList)) && $_ticketStatusContainer['markasresolved'] == '0') {
                    $_ticketStatusIDList[] = $_ticketStatusID;
                }
            }
        }

        return $_ticketStatusIDList;
    }

    /**
     * Retrieve the My Tickets Counter
     *
     * @author Varun Shoor
     * @return array The My Tickets Counter
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetMyTicketsCounter()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');

        $_counter = false;

        if (isset($_ticketCountCache['ownerstaff'][$_SWIFT->Staff->GetStaffID()])) {
            $_myTicketsContainer = $_ticketCountCache['ownerstaff'][$_SWIFT->Staff->GetStaffID()];

            if ($_myTicketsContainer['totalunresolveditems'] > 0) {
                $_counter = array($_SWIFT->Language->Get('dashmytickets'), number_format($_myTicketsContainer['totalunresolveditems'], 0), '/Tickets/Manage/MyTickets');
            }
        }

        return $_counter;
    }

    /**
     * Retrieve the Unassigned Counter
     *
     * @author Varun Shoor
     * @return array The Unassigned Counter
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetUnassignedCounter()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');

        $_counter = false;

        if (isset($_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()])) {
            $_unassignedTicketsContainer = $_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()];

            if ($_unassignedTicketsContainer['totalunresolveditems'] > 0) {
                $_counter = array($_SWIFT->Language->Get('dashunassigned'), number_format($_unassignedTicketsContainer['totalunresolveditems'], 0), '/Tickets/Manage/Unassigned');
            }
        }

        return $_counter;
    }

    /**
     * Retrieve the Overdue Tickets Container
     *
     * @author Varun Shoor
     * @return array The Overdue Tickets Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOverdueContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        $_ticketsContainer = array();

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        $_ticketStatusIDList = array();

        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
            if ($_ticketStatus['markasresolved'] == '0' && ($_ticketStatus['departmentid'] == '0' || in_array($_ticketStatus['departmentid'], $_assignedDepartmentIDList))) {
                $_ticketStatusIDList[] = $_ticketStatusID;
            }
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
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
            $_ownerIDClause = " AND ownerstaffid " . $_ownerNotFlag . " IN (" . BuildIN($_ownerStaffIDList, true) . ")";
        }

        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets
            WHERE departmentid IN (". BuildIN($_assignedDepartmentIDList) . ")
                AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList) . ")
                " . $_ownerIDClause . "
                AND ((duetime != '0' AND duetime < '" . DATENOW . "') OR (resolutionduedateline != '0' AND resolutionduedateline < '" . DATENOW . "'))
            ORDER BY lastactivity DESC", 30);
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems']))
        {
            $_totalRecordCount = (int) ($_countContainer['totalitems']);
        }

        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets
            WHERE departmentid IN (". BuildIN($_assignedDepartmentIDList) . ")
                AND ticketstatusid IN (" . BuildIN($_ticketStatusIDList) . ")
                " . $_ownerIDClause . "
                AND ((duetime != '0' AND duetime < '" . DATENOW . "') OR (resolutionduedateline != '0' AND resolutionduedateline < '" . DATENOW . "'))
            ORDER BY lastactivity DESC", 30);
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketsContainer[$_SWIFT->Database->Record['ticketid']] = $_SWIFT->Database->Record;
        }

        return array($_totalRecordCount, $_ticketsContainer);
    }

    /**
     * Return the Department Progress Container
     *
     * @author Varun Shoor
     * @return array The Department Progress Container
     */
    public static function GetDashboardDepartmentProgress()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_progressContainer = array();

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);
        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);

        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if (!in_array($_departmentID, $_assignedDepartmentIDList)) {
                continue;
            }

            if (isset($_ticketCountCache['departments'][$_departmentContainer['departmentid']]['totalunresolveditems'])) {
                $_ticketCount = (int) ($_ticketCountCache['departments'][$_departmentContainer['departmentid']]['totalunresolveditems']);
                if (!empty($_ticketCount)) {
                    $_progressContainer[] = array('title' => $_departmentContainer['title'], 'count' => $_ticketCount, 'link' => '/Tickets/Manage/Filter/' .  ($_departmentID));
                }
            }

            $subdepartments = (array)$_departmentContainer['subdepartments'];
            if (_is_array($subdepartments)) {
                /** Bug Fix : Saloni Dhall
                 *
                 * SWIFT-2813 : Help desk only shows the last added sub department under the Tickets overview section on dashboard but not all.
                 *
                 * Comments : Adjusted the foreach(), so that $_subDepartmentContainer array comes into count.
                 */
                /**
                 * @var int $_subDepartmentID
                 * @var array $_subDepartmentContainer
                 */
                foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!in_array($_subDepartmentID, $_assignedDepartmentIDList)) {
                        continue;
                    }

                    if (isset($_ticketCountCache['departments'][$_subDepartmentContainer['departmentid']]['totalunresolveditems'])) {
                        $_ticketCount = (int) ($_ticketCountCache['departments'][$_subDepartmentContainer['departmentid']]['totalunresolveditems']);
                        if (!empty($_ticketCount)) {
                            $_progressContainer[] = array('title' => $_subDepartmentContainer['title'], 'count' => $_ticketCount, 'link' => '/Tickets/Manage/Filter/' . ($_subDepartmentID));
                        }
                    }
                }
            }
        }

        return $_progressContainer;
    }

    /**
     * Return the Status Progress Container
     *
     * @author Varun Shoor
     * @return array The Status Progress Container
     */
    public static function GetDashboardStatusProgress()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_progressContainer = array();

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
            if ($_ticketStatus['departmentid'] != '0' && !in_array($_ticketStatus['departmentid'], $_assignedDepartmentIDList)) {
                continue;
            } else if ($_ticketStatus['markasresolved'] == '1' || !isset($_ticketCountCache['ticketstatus'][$_ticketStatusID])) {
                continue;
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-792 Count of tickets in Staff CP Dashboard (Status wise breakup) shows incorrect count
             *
             */
            $_ticketCount = 0;
            foreach ($_ticketCountCache['departments'] as $_departmentID => $_departmentContainer) {
                if ($_departmentID == '0' || !isset($_departmentContainer['ticketstatus'][$_ticketStatusID]) || !in_array($_departmentID, $_assignedDepartmentIDList)) {
                    continue;
                }

                $_ticketCount += $_departmentContainer['ticketstatus'][$_ticketStatusID]['totalitems'];
            }

            $_statusContainer = array('title' => $_ticketStatus['title'], 'count' => $_ticketCount, 'link' => '/Tickets/Search/UnresolvedStatus/' .  ($_ticketStatusID));
            if (!empty($_ticketStatus['statusbgcolor'])) {
                $_statusContainer['color'] = $_ticketStatus['statusbgcolor'];
            }

            $_progressContainer[] = $_statusContainer;
        }

        return $_progressContainer;
    }

    /**
     * Return the Owner Progress Container
     *
     * @author Varun Shoor
     * @return array The Owner Progress Container
     */
    public static function GetDashboardOwnerProgress()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_progressContainer = array();

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_staffCache       = (array) $_SWIFT->Cache->Get('staffcache');

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        if (isset($_ticketCountCache['ownerstaff'][0]) && isset($_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()]) && $_SWIFT->Staff->GetPermission('staff_tcanviewunassign') != '0') {
            $_unassignedTicketsContainer = $_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()];
            if ($_unassignedTicketsContainer['totalunresolveditems'] > 0) {
                $_progressContainer[] = array('title' => $_SWIFT->Language->Get('unassigneddash'), 'count' => number_format($_unassignedTicketsContainer['totalunresolveditems'], 0), 'link' => '/Tickets/Manage/Unassigned');
            }
        }

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        foreach ($_staffCache as $_staffID => $_staff) {
            /*
             * Improvement - Bishwanath Jha
             *
             * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
             *
             * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
             */
            if (!isset($_ticketCountCache['ownerstaff'][$_staffID]) || ($_staffID != '0' && $_staffID != $_SWIFT->Staff->GetStaffID() && $_SWIFT->Staff->GetPermission('staff_tcanviewall') == '0')) {
                continue;
            }
                $_staffAssignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID, APP_TICKETS);
            $_ticketCount = 0;
            $departments = (array) $_ticketCountCache['departments'];
            /**
             * @var int $_departmentID
             * @var array $_departmentContainer
             */
            foreach ($departments as $_departmentID => $_departmentContainer) {
                if ($_departmentID == '0' || !isset($_departmentContainer['ownerstaff'][$_staffID]) || !in_array($_departmentID, $_assignedDepartmentIDList)  || !in_array($_departmentID, $_staffAssignedDepartmentIDList)) {
                    continue;
                }

                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-2942: Count of tickets assigned to Staff shows closed ticket count as well at Staff CP > Home > Dashboard > Unresolved
                 *
                 */
                $_ticketCount += $_departmentContainer['ownerstaff'][$_staffID]['totalunresolveditems'];
            }

            $_progressContainer[] = array('title' => $_staff['fullname'], 'count' => $_ticketCount, 'link' => '/Tickets/Search/UnresolvedOwner/' .  ($_staffID));
        }

        return $_progressContainer;
    }

    /**
     * Return the Type Progress Container
     *
     * @author Varun Shoor
     * @return array The Type Progress Container
     */
    public static function GetDashboardTypeProgress()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_progressContainer = array();

        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        $_typeCountContainer = array();

        $_SWIFT->Database->Query("SELECT tickettypeid, COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets
            WHERE isresolved = '0' AND departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ") GROUP BY tickettypeid");
        while ($_SWIFT->Database->NextRecord()) {
            $_typeCountContainer[$_SWIFT->Database->Record['tickettypeid']] = (int) ($_SWIFT->Database->Record['totalitems']);
        }

        foreach ($_ticketTypeCache as $_ticketTypeID => $_ticketType) {
            if ($_ticketType['departmentid'] != '0' && !in_array($_ticketType['departmentid'], $_assignedDepartmentIDList)) {
                continue;
            } else if (!isset($_typeCountContainer[$_ticketTypeID])) {
                continue;
            }

            $_ticketCount =  ($_typeCountContainer[$_ticketTypeID]);
            $_progressContainer[] = array('title' => $_ticketType['title'], 'count' => $_ticketCount, 'link' => '/Tickets/Search/UnresolvedType/' .  ($_ticketTypeID));
        }

        return $_progressContainer;
    }

    /**
     * Return the Priority Container
     *
     * @author Varun Shoor
     * @return array The Priority Container
     */
    public static function GetDashboardPriorityProgress()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_progressContainer = array();

        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        $_priorityCountContainer = array();

        $_SWIFT->Database->Query("SELECT priorityid, COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets
            WHERE isresolved = '0' AND departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ") GROUP BY priorityid");
        while ($_SWIFT->Database->NextRecord()) {
            $_priorityCountContainer[$_SWIFT->Database->Record['priorityid']] = (int) ($_SWIFT->Database->Record['totalitems']);
        }

        foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriority) {
            if (!isset($_priorityCountContainer[$_ticketPriorityID])) {
                continue;
            }

            $_ticketCount =  ($_priorityCountContainer[$_ticketPriorityID]);

            $_priorityContainer = array('title' => $_ticketPriority['title'], 'count' => $_ticketCount, 'link' => '/Tickets/Search/UnresolvedPriority/' .  ($_ticketPriorityID));
            if (!empty($_ticketPriority['bgcolorcode'])) {
                $_priorityContainer['color'] = $_ticketPriority['bgcolorcode'];
            }
            $_progressContainer[] = $_priorityContainer;
        }

        return $_progressContainer;
    }

    /**
     * Retrieve the Custom Field ID List
     *
     * @author Varun Shoor
     * @return array The Custom Field ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveCustomFieldIDList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_customFieldIDCache = $_SWIFT->Cache->Get('customfieldidcache');

        $_returnContainer = array();
        $_returnContainer['_ticketCustomFieldIDList'] = $_returnContainer['_userCustomFieldIDList'] = $_returnContainer['_userOrganizationCustomFieldIDList'] = array();

        if (isset($_customFieldIDCache['ticketcustomfieldidlist']) && _is_array($_customFieldIDCache['ticketcustomfieldidlist'])) {
            $_returnContainer['_ticketCustomFieldIDList'] = $_customFieldIDCache['ticketcustomfieldidlist'];
        }

        if (isset($_customFieldIDCache['usercustomfieldidlist']) && _is_array($_customFieldIDCache['usercustomfieldidlist'])) {
            $_returnContainer['_userCustomFieldIDList'] = $_customFieldIDCache['usercustomfieldidlist'];
        }

        if (isset($_customFieldIDCache['userorganizationcustomfieldidlist']) && _is_array($_customFieldIDCache['userorganizationcustomfieldidlist'])) {
            $_returnContainer['_userOrganizationCustomFieldIDList'] = $_customFieldIDCache['userorganizationcustomfieldidlist'];
        }

        return $_returnContainer;
    }

}

?>
