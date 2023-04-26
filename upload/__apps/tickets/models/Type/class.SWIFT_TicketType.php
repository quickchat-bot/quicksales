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

namespace Tickets\Models\Type;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\User\SWIFT_UserGroupAssign;
use Tickets\Models\Escalation\SWIFT_EscalationPath;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Type Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketType extends SWIFT_Model
{
    const TABLE_NAME        =    'tickettypes';
    const PRIMARY_KEY        =    'tickettypeid';

    const TABLE_STRUCTURE    =    "tickettypeid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL,
                                displayicon C(255) DEFAULT '' NOTNULL,
                                type C(100) DEFAULT 'public' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                uservisibilitycustom I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'departmentid';
    const INDEX_2            =    'uservisibilitycustom, tickettypeid';
    const INDEX_3            =    'title'; // Unified Search


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @throws SWIFT_Type_Exception If the Record could not be loaded
     */
    public function __construct($_ticketTypeID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketTypeID)) {
            throw new SWIFT_Type_Exception('Failed to load Ticket Type ID: ' . $_ticketTypeID);
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
     * @throws SWIFT_Type_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'tickettypes', $this->GetUpdatePool(), 'UPDATE', "tickettypeid = '" .
                (int) ($this->GetTicketTypeID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Type ID
     *
     * @author Varun Shoor
     * @return mixed "tickettypeid" on Success, "false" otherwise
     * @throws SWIFT_Type_Exception If the Class is not Loaded
     */
    public function GetTicketTypeID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Type_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['tickettypeid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketTypeID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE tickettypeid = '" .
                $_ticketTypeID . "'");
        if (isset($_dataStore['tickettypeid']) && !empty($_dataStore['tickettypeid']))
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
     * @throws SWIFT_Type_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Type_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Type_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Type_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Type_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Type
     *
     * @author Varun Shoor
     * @param string $_title The Ticket Type Title
     * @param string $_displayIcon The Display Icon
     * @param int $_displayOrder The Ticket Type Display Order
     * @param mixed $_type The Visibility Type
     * @param int $_departmentID The Linked Department ID
     * @param bool $_userVisibilityCustom (OPTIONAL) Whether this type is linked to custom user groups
     * @param array $_userGroupIDList (OPTIONAL) The User Group ID List
     * @param bool $_isMaster (OPTIONAL) Whether the ticket type is master, which cannot be deleted
     * @return mixed "_ticketTypeID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Type_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_title, $_displayIcon, $_displayOrder, $_type, $_departmentID, $_userVisibilityCustom = false,
            $_userGroupIDList = array(), $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title))
        {
            throw new SWIFT_Type_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettypes', array('title' => $_title, 'displayicon' => $_SWIFT->Input->SanitizeForXSS($_displayIcon),
            'departmentid' => $_departmentID, 'type' => $_type, 'displayorder' =>  ($_displayOrder), 'ismaster' => (int) ($_isMaster),
            'uservisibilitycustom' => (int) ($_userVisibilityCustom)), 'INSERT');
        $_ticketTypeID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketTypeID)
        {
            throw new SWIFT_Type_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == 1)
        {
            foreach ($_userGroupIDList as $_key => $_val)
            {
                SWIFT_UserGroupAssign::Insert($_ticketTypeID, SWIFT_UserGroupAssign::TYPE_TICKETTYPE, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        return $_ticketTypeID;
    }

    /**
     * Update the Ticket Type Record
     *
     * @author Varun Shoor
     * @param string $_title The Ticket Type Title
     * @param string $_displayIcon The Display Icon
     * @param int $_displayOrder The Ticket Type Display Order
     * @param mixed $_type The Visibility Type
     * @param int $_departmentID The Linked Department ID
     * @param bool $_userVisibilityCustom (OPTIONAL) Whether this type is linked to custom user groups
     * @param array $_userGroupIDList (OPTIONAL) The User Group ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Type_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_displayIcon, $_displayOrder, $_type, $_departmentID, $_userVisibilityCustom = false,
            $_userGroupIDList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Type_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('displayicon', $this->Input->SanitizeForXSS($_displayIcon));
        $this->UpdatePool('type', $_type);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('displayorder',  ($_displayOrder));
        $this->UpdatePool('uservisibilitycustom', (int) ($_userVisibilityCustom));

        $this->ProcessUpdatePool();

        SWIFT_UserGroupAssign::DeleteList(array($this->GetTicketTypeID()), SWIFT_UserGroupAssign::TYPE_TICKETTYPE, false);

        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == 1)
        {
            foreach ($_userGroupIDList as $_key => $_val)
            {
                SWIFT_UserGroupAssign::Insert($this->GetTicketTypeID(), SWIFT_UserGroupAssign::TYPE_TICKETTYPE, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        // Rebuild other properties
        SWIFT_Ticket::UpdateGlobalProperty('tickettypetitle', $_title, 'tickettypeid', $this->GetTicketTypeID());

        SWIFT_EscalationPath::UpdateGlobalProperty('tickettypetitle', $_title, 'tickettypeid', $this->GetTicketTypeID());

        return true;
    }

    /**
     * Delete the Ticket Type record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Type_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Type_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketTypeID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Types
     *
     * @author Varun Shoor
     * @param array $_ticketTypeIDList The Ticket Type ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketTypeIDList))
        {
            return false;
        }

        $_finalTicketTypeIDList = array();

        $_finalText = '';
        $_index = 1;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE tickettypeid IN (" . BuildIN($_ticketTypeIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalText .= $_index .'. '.IIF(!empty($_SWIFT->Database->Record['displayicon']), '<img src="'. str_replace('{$themepath}',
                    SWIFT::Get('themepath').'images/', $_SWIFT->Database->Record['displayicon']).'" align="absmiddle" border="0" /> ') .
                    htmlspecialchars($_SWIFT->Database->Record['title']).'<br />';

            $_finalTicketTypeIDList[] = $_SWIFT->Database->Record['tickettypeid'];

            $_index++;
        }

        if (!count($_finalTicketTypeIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledeltickettype'), count($_finalTicketTypeIDList)),
                $_SWIFT->Language->Get('msgdeltickettype') . '<br />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickettypes WHERE tickettypeid IN (" . BuildIN($_finalTicketTypeIDList) . ")");

        // Clean up user group assigns
        SWIFT_UserGroupAssign::DeleteList($_finalTicketTypeIDList, SWIFT_UserGroupAssign::TYPE_TICKETTYPE);

        self::RebuildCache();

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

        return SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_TICKETTYPE, $this->GetTicketTypeID());
    }

    /**
     * Rebuild the Ticket Type Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY displayorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;

            $_cache[$_SWIFT->Database->Record3['tickettypeid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['tickettypeid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('tickettypecache', $_cache);

        return true;
    }

    /**
     * Retrieve the Last Possible Display Order for a Ticket Types
     *
     * @author Varun Shoor
     * @return int The Last Possible Display Order
     * @throws SWIFT_Exception
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('tickettypecache');

        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');

        if (!$_ticketTypeCache)
        {
            return 1;
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_ticketTypeCache));

        return ($_ticketTypeCache[$_lastInsertID]['displayorder']+1);
    }

    /**
     * Retrieve a list of ticket types based on a given user group id
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return array The final ticket type container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUserGroup($_userGroupID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');

        $_ticketTypeIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_TICKETTYPE);

        $_finalTicketTypeContainer = array();

        foreach ($_ticketTypeCache as $_ticketTypeID => $_ticketTypeContainer) {
            if ($_ticketTypeContainer['uservisibilitycustom'] == '0' ||
                    ($_ticketTypeContainer['uservisibilitycustom'] == '1' && in_array($_ticketTypeID, $_ticketTypeIDList))) {
                $_finalTicketTypeContainer[$_ticketTypeID] = $_ticketTypeContainer;
            }
        }

        return $_finalTicketTypeContainer;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_ticketTypeIDSortList The Ticket Type ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_ticketTypeIDSortList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketTypeIDSortList)) {
            return false;
        }

        foreach ($_ticketTypeIDSortList as $_ticketTypeID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettypes', array('displayorder' => (int) ($_displayOrder)), 'UPDATE',
                    "tickettypeid = '" . $_ticketTypeID . "'");
        }

        self::RebuildCache();

        return true;
    }
}
?>
