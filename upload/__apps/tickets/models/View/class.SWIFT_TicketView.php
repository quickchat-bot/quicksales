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

namespace Tickets\Models\View;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The Ticket View Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketView extends SWIFT_Model {
    const TABLE_NAME        =    'ticketviews';
    const PRIMARY_KEY        =    'ticketviewid';

    const TABLE_STRUCTURE    =    "ticketviewid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                viewscope I2 DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                viewalltickets I2 DEFAULT '0' NOTNULL,
                                viewunassigned I2 DEFAULT '0' NOTNULL,
                                viewassigned I2 DEFAULT '0' NOTNULL,
                                sortby I2 DEFAULT '0' NOTNULL,
                                sortorder I2 DEFAULT '0' NOTNULL,
                                ismaster I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ticketsperpage I DEFAULT '0' NOTNULL,
                                autorefresh I2 DEFAULT '0' NOTNULL,
                                setasowner I2 DEFAULT '0' NOTNULL,
                                defaultstatusonreply I2 DEFAULT '0' NOTNULL,
                                afterreplyaction I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'viewscope, staffid';


    protected $_dataStore = array();

    static protected $_rebuildCacheQueued = false;

    // Core Constants
    const VIEWSCOPE_GLOBAL = '1';
    const VIEWSCOPE_TEAM = '2';
    const VIEWSCOPE_PRIVATE = '3';

    const SORT_ASC = '1';
    const SORT_DESC = '2';

    const AFTERREPLY_TOPTICKETLIST = '1';
    const AFTERREPLY_ACTIVETICKETLIST = '2';
    const AFTERREPLY_TICKET = '3';
    const AFTERREPLY_NEXTTICKET = '4';

    const VIEW_UNASSIGNED = 'viewunassigned';
    const VIEW_ASSIGNED = 'viewassigned';
    const VIEW_ALLTICKETS = 'alltickets';

    const VIEW_MAX_PER_PAGE = 100;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Ticket Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct() {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketviews', $this->GetUpdatePool(), 'UPDATE', "ticketviewid = '" .
                ($this->GetTicketViewID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket View ID
     *
     * @author Varun Shoor
     * @return mixed "ticketviewid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketViewID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketviewid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketviews WHERE ticketviewid = '" .
                    ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketviewid']) && !empty($_dataStore['ticketviewid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketviewid']) || empty($this->_dataStore['ticketviewid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid view scope
     *
     * @author Varun Shoor
     * @param mixed $_viewScope The View Scope
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidViewScope($_viewScope) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_viewScope == self::VIEWSCOPE_GLOBAL || $_viewScope == self::VIEWSCOPE_TEAM || $_viewScope == self::VIEWSCOPE_PRIVATE)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid sort order
     *
     * @author Varun Shoor
     * @param mixed $_sortOrder The Sort Order
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSortOrder($_sortOrder) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_sortOrder == self::SORT_ASC || $_sortOrder == self::SORT_DESC) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid after reply action
     *
     * @author Varun Shoor
     * @param mixed $_afterReplyAction The After Reply Action
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidAfterReplyAction($_afterReplyAction) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_afterReplyAction == self::AFTERREPLY_TOPTICKETLIST || $_afterReplyAction == self::AFTERREPLY_ACTIVETICKETLIST ||
                $_afterReplyAction == self::AFTERREPLY_TICKET || $_afterReplyAction == self::AFTERREPLY_NEXTTICKET) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Ticket View
     *
     * @author Varun Shoor
     * @param string $_title The View Title
     * @param mixed $_viewScope The View Scope
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param bool $_viewAllTickets Whether to display all tickets (unassigned, assigned to self, assigned to others)
     * @param bool $_viewUnassigned Whether to display unassigned tickets
     * @param bool $_viewAssigned Whether to display tickets assigned to self
     * @param int $_sortBy The Field Index to Sort By
     * @param mixed $_sortOrder The Sort Order
     * @param int $_ticketsPerPage The # of Tickets to display per page
     * @param int $_autoRefresh The Auto Refresh Seconds. 0 = no refresh
     * @param bool $_setAsOwner Whether to set the current staff as owner by default when replying
     * @param int $_defaultStatusOnReply The Default status to set on reply
     * @param mixed $_afterReplyAction The After Reply action to take
     * @param array $_ticketViewFieldsContainer The Fields Container. array(0 => array(fieldtype, fieldindex), ...);
     * @param array $_ticketViewLinkContainer The Link Container. array(linktype => array(linktypeid, linktypeid);
     * @param bool $_isMaster Whether this is a master view that cannot be deleted
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_title, $_viewScope, SWIFT_Staff $_SWIFT_StaffObject, $_viewAllTickets, $_viewUnassigned, $_viewAssigned,
            $_sortBy, $_sortOrder, $_ticketsPerPage, $_autoRefresh, $_setAsOwner, $_defaultStatusOnReply, $_afterReplyAction,
            $_ticketViewFieldsContainer, $_ticketViewLinkContainer, $_isMaster = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !self::IsValidViewScope($_viewScope) || !$_SWIFT_StaffObject instanceof SWIFT_Staff ||
                !$_SWIFT_StaffObject->GetIsClassLoaded() || !self::IsValidSortOrder($_sortOrder) ||
                !self::IsValidAfterReplyAction($_afterReplyAction)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketviews', array('title' => $_title, 'viewscope' => ($_viewScope),
            'staffid' => ($_SWIFT_StaffObject->GetStaffID()), 'viewalltickets' => ($_viewAllTickets),
                    'viewunassigned' => ($_viewUnassigned), 'viewassigned' => ($_viewAssigned), 'sortby' => ($_sortBy),
                    'sortorder' => ($_sortOrder), 'ticketsperpage' => ($_ticketsPerPage), 'autorefresh' => ($_autoRefresh),
                    'setasowner' => ($_setAsOwner), 'defaultstatusonreply' => ($_defaultStatusOnReply),
                    'afterreplyaction' => ($_afterReplyAction), 'ismaster' => ($_isMaster)), 'INSERT');
        $_ticketViewID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketViewID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataID($_ticketViewID));
        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        // Create the ticket view fields
        foreach ($_ticketViewFieldsContainer as $_fieldTypeID) {
            if (substr($_fieldTypeID, 0, 2) == 'c_') {
                SWIFT_TicketViewField::Create($_SWIFT_TicketViewObject, SWIFT_TicketViewField::TYPE_CUSTOM, substr($_fieldTypeID, 2));
            } else {
                SWIFT_TicketViewField::Create($_SWIFT_TicketViewObject, SWIFT_TicketViewField::TYPE_TICKET, $_fieldTypeID);
            }
        }

        // Create the ticket view links
        foreach ($_ticketViewLinkContainer as $_linkType => $_linkTypeIDList) {
            foreach ($_linkTypeIDList as $_key => $_linkTypeID) {
                SWIFT_TicketViewLink::Create($_SWIFT_TicketViewObject, $_linkType, $_linkTypeID);
            }
        }

        self::QueueRebuildCache();

        return $_ticketViewID;
    }

    /**
     * Update the Ticket View Record
     *
     * @author Varun Shoor
     * @param string $_title The View Title
     * @param mixed $_viewScope The View Scope
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param bool $_viewAllTickets Whether to display all tickets (unassigned, assigned to self, assigned to others)
     * @param bool $_viewUnassigned Whether to display unassigned tickets
     * @param bool $_viewAssigned Whether to display tickets assigned to self
     * @param int $_sortBy The Field Index to Sort By
     * @param mixed $_sortOrder The Sort Order
     * @param int $_ticketsPerPage The # of Tickets to display per page
     * @param int $_autoRefresh The Auto Refresh Seconds. 0 = no refresh
     * @param bool $_setAsOwner Whether to set the current staff as owner by default when replying
     * @param int $_defaultStatusOnReply The Default status to set on reply
     * @param mixed $_afterReplyAction The After Reply action to take
     * @param array $_ticketViewFieldsContainer The Fields Container. array(0 => array(fieldtype, fieldindex), ...);
     * @param array $_ticketViewLinkContainer The Link Container. array(linktype => array(linktypeid, linktypeid);
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_viewScope, SWIFT_Staff $_SWIFT_StaffObject, $_viewAllTickets, $_viewUnassigned, $_viewAssigned,
            $_sortBy, $_sortOrder, $_ticketsPerPage, $_autoRefresh, $_setAsOwner, $_defaultStatusOnReply, $_afterReplyAction,
            $_ticketViewFieldsContainer, $_ticketViewLinkContainer) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || !self::IsValidViewScope($_viewScope) || !$_SWIFT_StaffObject instanceof SWIFT_Staff ||
                !$_SWIFT_StaffObject->GetIsClassLoaded() || !self::IsValidSortOrder($_sortOrder) ||
                !self::IsValidAfterReplyAction($_afterReplyAction)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('viewscope', ($_viewScope));
        $this->UpdatePool('staffid', ($_SWIFT_StaffObject->GetStaffID()));
        $this->UpdatePool('viewalltickets', ($_viewAllTickets));
        $this->UpdatePool('viewunassigned', ($_viewUnassigned));
        $this->UpdatePool('viewassigned', ($_viewAssigned));
        $this->UpdatePool('sortby', ($_sortBy));
        $this->UpdatePool('sortorder', ($_sortOrder));
        $this->UpdatePool('ticketsperpage', ($_ticketsPerPage));
        $this->UpdatePool('autorefresh', ($_autoRefresh));
        $this->UpdatePool('setasowner', ($_setAsOwner));
        $this->UpdatePool('defaultstatusonreply', ($_defaultStatusOnReply));
        $this->UpdatePool('afterreplyaction', ($_afterReplyAction));
        $this->ProcessUpdatePool();

        // Clear the ticket view fields
        SWIFT_TicketViewField::DeleteOnTicketView(array($this->GetTicketViewID()));

        // Create the ticket view fields
        foreach ($_ticketViewFieldsContainer as $_fieldTypeID) {
            if (substr($_fieldTypeID, 0, 2) == 'c_') {
                SWIFT_TicketViewField::Create($this, SWIFT_TicketViewField::TYPE_CUSTOM, substr($_fieldTypeID, 2));
            } else {
                SWIFT_TicketViewField::Create($this, SWIFT_TicketViewField::TYPE_TICKET, $_fieldTypeID);
            }
        }

        // Clear the ticket view links
        SWIFT_TicketViewLink::DeleteOnTicketView(array($this->GetTicketViewID()));

        // Create the ticket view links
        foreach ($_ticketViewLinkContainer as $_linkType => $_linkTypeIDList) {
            foreach ($_linkTypeIDList as $_key => $_linkTypeID) {
                SWIFT_TicketViewLink::Create($this, $_linkType, $_linkTypeID);
            }
        }

        self::QueueRebuildCache();


        return true;
    }

    /**
     * Delete the Ticket View record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketViewID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Views
     *
     * @author Varun Shoor
     * @param array $_ticketViewIDList The Ticket View ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketViewIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketViewIDList)) {
            return false;
        }

        self::QueueRebuildCache();

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketviews WHERE ticketviewid IN (" . BuildIN($_ticketViewIDList) . ")");

        // Clear up the fields
        SWIFT_TicketViewField::DeleteOnTicketView($_ticketViewIDList);

        // Clear up the links
        SWIFT_TicketViewLink::DeleteOnTicketView($_ticketViewIDList);

        return true;
    }

    /**
     * Queue the Rebuild Cache Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function QueueRebuildCache() {
        $_SWIFT = SWIFT::GetInstance();

        self::$_rebuildCacheQueued = true;

        SWIFT::Shutdown('Tickets\Models\View\SWIFT_TicketView', 'RebuildCache', 1, false, true);

        return true;
    }

    /**
     * Rebuild the Ticket View Cache
     *
     * @author Varun Shoor
     * @param bool $_noQueue Whether to ignore queue
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache($_noQueue = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (self::$_rebuildCacheQueued && !$_noQueue) {
            return true;
        }

        $_ticketViewsContainer = $_ticketViewIDList = $_ticketViewDepartmentLinks = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketviews ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketViewsContainer[$_SWIFT->Database->Record['ticketviewid']] = $_SWIFT->Database->Record;
            $_ticketViewsContainer[$_SWIFT->Database->Record['ticketviewid']]['links'] = array();
            $_ticketViewsContainer[$_SWIFT->Database->Record['ticketviewid']]['fields'] = array();

            $_ticketViewIDList[] = ($_SWIFT->Database->Record['ticketviewid']);
        }

        if (count($_ticketViewIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketviewfields WHERE ticketviewid IN (" . BuildIN($_ticketViewIDList) .
                    ") ORDER BY ticketviewfieldid ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_ticketViewsContainer[$_SWIFT->Database->Record['ticketviewid']]['fields'][$_SWIFT->Database->Record['ticketviewfieldid']] =
                        $_SWIFT->Database->Record;
            }

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketviewlinks WHERE ticketviewid IN (" . BuildIN($_ticketViewIDList) .
                    ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['linktype'] == SWIFT_TicketViewLink::LINK_DEPARTMENT)
                {
                    $_ticketViewDepartmentLinks[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record['ticketviewid'];
                }
                $_ticketViewsContainer[$_SWIFT->Database->Record['ticketviewid']]['links'][$_SWIFT->Database->Record['ticketviewlinkid']] =
                        $_SWIFT->Database->Record;
            }
        }

        $_SWIFT->Cache->Update('ticketviewcache', $_ticketViewsContainer);
        $_SWIFT->Cache->Update('ticketviewdepartmentlinkcache', $_ticketViewDepartmentLinks);

        return true;
    }

    /**
     * Check to see if the current staff can load this view
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CanStaffView() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_staffGroupID = false;
        if (isset($_staffCache[$this->GetProperty('staffid')])) {
            $_staffGroupID = $_staffCache[$this->GetProperty('staffid')]['staffgroupid'];
        }

        if ($this->GetProperty('viewscope') == SWIFT_TicketView::VIEWSCOPE_GLOBAL ||
                ($this->GetProperty('viewscope') == SWIFT_TicketView::VIEWSCOPE_TEAM && $_staffGroupID == $_SWIFT->Staff->GetProperty('staffgroupid'))
                || ($this->GetProperty('viewscope') == SWIFT_TicketView::VIEWSCOPE_PRIVATE &&
                $this->GetProperty('staffid') == $_SWIFT->Staff->GetStaffID())) {
            return true;
        }

        return false;
    }

    /**
     * Get the View Scope Label
     *
     * @author Varun Shoor
     * @param mixed $_viewScope The View Scope
     * @return mixed "View Scope Label" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetViewScopeLabel($_viewScope) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidViewScope($_viewScope)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_viewScope) {
            case self::VIEWSCOPE_GLOBAL:
                return $_SWIFT->Language->Get('viewscope_global');

                break;

            case self::VIEWSCOPE_PRIVATE:
                return $_SWIFT->Language->Get('viewscope_private');

                break;

            case self::VIEWSCOPE_TEAM:
                return $_SWIFT->Language->Get('viewscope_team');

                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Get the Sort Order Label
     *
     * @author Varun Shoor
     * @param mixed $_sortOrder The Sort Order
     * @return mixed "Sort Order Label" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetSortOrderLabel($_sortOrder) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidSortOrder($_sortOrder)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_sortOrder) {
            case self::SORT_ASC:
                return $_SWIFT->Language->Get('sort_asc');

                break;

            case self::SORT_DESC:
                return $_SWIFT->Language->Get('sort_desc');

                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Get the After Reply Action Label
     *
     * @author Varun Shoor
     * @param mixed $_afterReplyAction The After Reply Action
     * @return mixed "After Reply Action Label" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetAfterReplyActionLabel($_afterReplyAction) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidAfterReplyAction($_afterReplyAction)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_afterReplyAction) {
            case self::AFTERREPLY_TOPTICKETLIST:
                return $_SWIFT->Language->Get('afterreply_topticketlist');

                break;

            case self::AFTERREPLY_ACTIVETICKETLIST:
                return $_SWIFT->Language->Get('afterreply_activeticketlist');

                break;

            case self::AFTERREPLY_TICKET:
                return $_SWIFT->Language->Get('afterreply_ticket');

                break;

            case self::AFTERREPLY_NEXTTICKET:
                return $_SWIFT->Language->Get('afterreply_nextticket');

                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Retrieve the ticket fields associated with this view
     *
     * @author Varun Shoor
     * @return array The Ticket Fields Container
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetFieldsContainer() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketViewCache = $this->Cache->Get('ticketviewcache');

        $_ticketFieldsContainer = $_ticketViewCache[$this->GetTicketViewID()]['fields'];
        if (!_is_array($_ticketFieldsContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_finalTicketFieldsContainer = array();
        foreach ($_ticketFieldsContainer as $_key => $_val) {
            $_finalTicketFieldsContainer[$_key] = new SWIFT_TicketViewField(new SWIFT_DataStore($_val));
        }

        return $_finalTicketFieldsContainer;
    }

    /**
     * Retrieve the ticket links associated with this view
     *
     * @author Varun Shoor
     * @return array The Ticket Links Container
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetLinksContainer() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketViewCache = $this->Cache->Get('ticketviewcache');

        $_ticketLinksContainer = $_ticketViewCache[$this->GetTicketViewID()]['links'];
        if (!_is_array($_ticketLinksContainer)) {
            return array();
        }

        $_finalTicketLinksContainer = array();
        foreach ($_ticketLinksContainer as $_key => $_val) {
            $_finalTicketLinksContainer[$_val['linktype']][] = new SWIFT_TicketViewLink(new SWIFT_DataStore($_val));
        }

        return $_finalTicketLinksContainer;
    }
}
