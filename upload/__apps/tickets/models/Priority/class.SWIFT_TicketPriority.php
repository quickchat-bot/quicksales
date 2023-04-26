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

namespace Tickets\Models\Priority;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Base\Models\User\SWIFT_UserGroupAssign;
use Tickets\Models\Escalation\SWIFT_EscalationPath;

/**
 * The Ticket Priority Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketPriority extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketpriorities';
    const PRIMARY_KEY        =    'priorityid';

    const TABLE_STRUCTURE    =    "priorityid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                type C(50) DEFAULT 'public' NOTNULL,
                                frcolorcode C(100) DEFAULT '' NOTNULL,
                                bgcolorcode C(100) DEFAULT '' NOTNULL,
                                iscustom I2 DEFAULT '1' NOTNULL,
                                ismaster I2 DEFAULT '1' NOTNULL,

                                uservisibilitycustom I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'uservisibilitycustom, priorityid';
    const INDEX_2            =    'title';



    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @throws SWIFT_Priority_Exception If the Class is not Loaded
     */
    public function __construct($_ticketPriorityID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketPriorityID))
        {
            throw new SWIFT_Priority_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketpriorities', $this->GetUpdatePool(), 'UPDATE', "priorityid = '" .
                (int) ($this->GetTicketPriorityID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Priority ID
     *
     * @author Varun Shoor
     * @return mixed "ticketpriorityid" on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If the Class is not Loaded
     */
    public function GetTicketPriorityID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Priority_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['priorityid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketPriorityID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities WHERE priorityid = '" .
                $_ticketPriorityID . "'");
        if (isset($_dataStore['priorityid']) && !empty($_dataStore['priorityid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Priority_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Priority_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Priority_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve a new Ticket Priority Object
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return object SWIFT_TicketPriority Object
     */
    public static function Retrieve($_ticketPriorityID)
    {
        return new SWIFT_TicketPriority($_ticketPriorityID);
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param string $_priorityType The Priority Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_priorityType)
    {
        if ($_priorityType != SWIFT_PUBLIC && $_priorityType != SWIFT_PRIVATE)
        {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the User Group ID's linked with this Ticket Priority
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY, $this->GetTicketPriorityID());
    }

    /**
     * Create a new Ticket Priority Record
     *
     * @author Varun Shoor
     * @param string $_title The Ticket Priority Title
     * @param int $_displayOrder The Ticket Display Order
     * @param string $_priorityType The Priority Visibility Type (PUBLIC/PRIVATE)
     * @param string $_foregroundColorCode The Foreground Color Code
     * @param string $_backgroundColorCode The Background Color Code
     * @param bool $_userVisibilityCustom Whether it is only visible to select user groups
     * @param array $_userGroupIDList The User Group ID List Container
     * @param bool $_isMaster (OPTIONAL) Whether this ticket priority is a master priority which cannot be deleted
     * @return mixed "ticketPriorityID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_title, $_displayOrder, $_priorityType, $_foregroundColorCode, $_backgroundColorCode,
            $_userVisibilityCustom, $_userGroupIDList = array(), $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_priorityType) || empty($_title))
        {
            throw new SWIFT_Priority_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketpriorities', array('title' => $_title, 'displayorder' => ($_displayOrder),
            'type' => $_priorityType, 'frcolorcode' => $_foregroundColorCode, 'bgcolorcode' => $_backgroundColorCode,
            'uservisibilitycustom' => (int) ($_userVisibilityCustom), 'ismaster' => (int) ($_isMaster)), 'INSERT');
        $_ticketPriorityID = $_SWIFT->Database->Insert_ID();
        if (!$_ticketPriorityID)
        {
            throw new SWIFT_Priority_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == 1)
        {
            foreach ($_userGroupIDList as $_key => $_val)
            {
                SWIFT_UserGroupAssign::Insert($_ticketPriorityID, SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        return $_ticketPriorityID;
    }

    /**
     * Update the Ticket Priority Record
     *
     * @author Varun Shoor
     * @param string $_title The Ticket Priority Title
     * @param int $_displayOrder The Ticket Display Order
     * @param string $_priorityType The Priority Visibility Type (PUBLIC/PRIVATE)
     * @param string $_foregroundColorCode The Foreground Color Code
     * @param string $_backgroundColorCode The Background Color Code
     * @param bool $_userVisibilityCustom Whether it is only visible to select user groups
     * @param array $_userGroupIDList The User Group ID List Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_displayOrder, $_priorityType, $_foregroundColorCode, $_backgroundColorCode,
            $_userVisibilityCustom, $_userGroupIDList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Priority_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidType($_priorityType) || empty($_title)) {
            throw new SWIFT_Priority_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('displayorder', ($_displayOrder));
        $this->UpdatePool('type', $_priorityType);
        $this->UpdatePool('frcolorcode', $_foregroundColorCode);
        $this->UpdatePool('bgcolorcode', $_backgroundColorCode);
        $this->UpdatePool('uservisibilitycustom', (int) ($_userVisibilityCustom));

        $this->ProcessUpdatePool();

        SWIFT_UserGroupAssign::DeleteList(array($this->GetTicketPriorityID()), SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY, false);

        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == 1)
        {
            foreach ($_userGroupIDList as $_key => $_val)
            {
                SWIFT_UserGroupAssign::Insert($this->GetTicketPriorityID(), SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        // Rebuild other properties
        SWIFT_Ticket::UpdateGlobalProperty('prioritytitle', $_title, 'priorityid', $this->GetTicketPriorityID());

        SWIFT_EscalationPath::UpdateGlobalProperty('prioritytitle', $_title, 'priorityid', $this->GetTicketPriorityID());

        return true;
    }

    /**
     * Delete the Ticket Priority record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Priority_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Priority_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketPriorityID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of Ticket Priorities
     *
     * @author Varun Shoor
     * @param array $_ticketPriorityIDList The Ticket Priority ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketPriorityIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPriorityIDList))
        {
            return false;
        }

        $_finalTicketPriorityIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities WHERE priorityid IN (" . BuildIN($_ticketPriorityIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketPriorityIDList[] = $_SWIFT->Database->Record['priorityid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        if (!count($_finalTicketPriorityIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelticketpriority'), count($_finalTicketPriorityIDList)),
                $_SWIFT->Language->Get('msgdelticketpriority') . '<br />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketpriorities WHERE priorityid IN (" . BuildIN($_finalTicketPriorityIDList) .
                ")");

        SWIFT_UserGroupAssign::DeleteList($_finalTicketPriorityIDList, SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY);

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Ticket Priority Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['priorityid']] = $_SWIFT->Database->Record3;

            $_cache[$_SWIFT->Database->Record3['priorityid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('prioritycache', $_cache);

        return true;
    }

    /**
     * Retrieve the Last Possible Display Order for a Ticket Priority
     *
     * @author Varun Shoor
     * @return int The Last Possible Display Order
     * @throws SWIFT_Exception
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('prioritycache');

        $_priorityCache = $_SWIFT->Cache->Get('prioritycache');

        if (!$_priorityCache)
        {
            return 1;
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_priorityCache));

        return ($_priorityCache[$_lastInsertID]['displayorder'] + 1);
    }

    /**
     * Retrieve a list of ticket priorities based on a given user group id
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return array The final ticket priority container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUserGroup($_userGroupID) {
        $_SWIFT = SWIFT::GetInstance();

        $_userGroupID = $_userGroupID;
        if (empty($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');

        $_ticketPriorityIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY);

        $_finalTicketPriorityContainer = array();

        foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriorityContainer) {
            if ($_ticketPriorityContainer['uservisibilitycustom'] == '0' ||
                    ($_ticketPriorityContainer['uservisibilitycustom'] == '1' && in_array($_ticketPriorityID, $_ticketPriorityIDList))) {
                $_finalTicketPriorityContainer[$_ticketPriorityID] = $_ticketPriorityContainer;
            }
        }

        return $_finalTicketPriorityContainer;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_ticketPriorityIDSortList The Ticket Priority ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_ticketPriorityIDSortList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPriorityIDSortList)) {
            return false;
        }

        foreach ($_ticketPriorityIDSortList as $_ticketPriorityID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketpriorities', array('displayorder' => (int) ($_displayOrder)), 'UPDATE',
                    "priorityid = '" . $_ticketPriorityID . "'");
        }

        self::RebuildCache();

        return true;
    }
}
?>
