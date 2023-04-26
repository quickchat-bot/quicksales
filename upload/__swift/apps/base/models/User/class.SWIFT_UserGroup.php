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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * User Group Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserGroup extends SWIFT_Model
{
    const TABLE_NAME = 'usergroups';
    const PRIMARY_KEY = 'usergroupid';

    const TABLE_STRUCTURE = "usergroupid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                grouptype I DEFAULT '0' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'grouptype';
    const INDEX_2 = 'title, grouptype';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_REGISTERED = 1;
    const TYPE_GUEST = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Unable to Load User Group Record
     */
    public function __construct($_userGroupID)
    {
        parent::__construct();

        if (!$this->LoadData($_userGroupID)) {
            throw new SWIFT_Exception('Unable to Load User Group ID: ' . $_userGroupID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'usergroups', $this->GetUpdatePool(), 'UPDATE', "usergroupid = '" . (int)($this->GetUserGroupID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Group ID
     *
     * @author Varun Shoor
     * @return mixed "usergroupid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['usergroupid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_userGroupID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE usergroupid = '" . $_userGroupID . "'");
        if (isset($_dataStore['usergroupid']) && !empty($_dataStore['usergroupid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
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
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Rebuilds the User Group Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_groupCache = array();
        $_groupSettings = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroupsettings");
        while ($_SWIFT->Database->NextRecord()) {
            $_groupSettings[$_SWIFT->Database->Record['usergroupid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_groupCache[$_SWIFT->Database->Record['usergroupid']] = $_SWIFT->Database->Record;

            if (isset($_groupSettings[$_SWIFT->Database->Record['usergroupid']])) {
                $_groupCache[$_SWIFT->Database->Record['usergroupid']]['settings'] = $_groupSettings[$_SWIFT->Database->Record['usergroupid']];
            } else {
                $_groupCache[$_SWIFT->Database->Record['usergroupid']]['settings'] = array();
            }
        }

        $_SWIFT->Cache->Update('usergroupcache', $_groupCache);

        return true;
    }

    /**
     * Insert a new User Group Record
     *
     * @author Varun Shoor
     * @param string $_title User Group Title
     * @param int $_groupType The User Group Type
     * @param bool $_isMaster (OPTIONAL) Whether this is the master user group
     * @return SWIFT_UserGroup
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_title, $_groupType, $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidGroupType($_groupType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'usergroups', array('title' => $_title, 'grouptype' => $_groupType, 'ismaster' => (int)($_isMaster)), 'INSERT');
        $_userGroupID = $_SWIFT->Database->Insert_ID();
        if (!$_userGroupID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        self::RebuildCache();

        return new SWIFT_UserGroup($_userGroupID);
    }

    /**
     * Checks to see if its a valid group type
     *
     * @author Varun Shoor
     * @param int $_groupType User Group Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidGroupType($_groupType)
    {
        if ($_groupType == self::TYPE_REGISTERED || $_groupType == self::TYPE_GUEST) {
            return true;
        }

        return false;
    }

    /**
     * Update The Loaded User Group Record
     *
     * @author Varun Shoor
     * @param string $_title User Group Title
     * @param int $_groupType The User Group Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_groupType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidGroupType($_groupType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('grouptype', $_groupType);
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the existing user group record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetUserGroupID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete the List of User Group IDs
     *
     * @author Varun Shoor
     * @param array $_userGroupIDList The User Group ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_userGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userGroupIDList)) {
            return false;
        }

        $_finalUserGroupIDList = array();
        $_index = 1;
        $_finalText = '';

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE usergroupid IN (" . BuildIN($_userGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['ismaster'] == '1') {
                continue;
            }

            $_finalUserGroupIDList[] = $_SWIFT->Database->Record['usergroupid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

            $_index++;
        }

        if (!count($_finalUserGroupIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleusergroupdel'), count($_finalUserGroupIDList)), $_SWIFT->Language->Get('msgusergroupdel') . '<BR/ >' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usergroups WHERE usergroupid IN (" . BuildIN($_finalUserGroupIDList) . ")");

        SWIFT_UserGroupSettings::DeleteOnUserGroup($_finalUserGroupIDList);

        SWIFT_UserGroupAssign::DeleteListUserGroupID($_finalUserGroupIDList);

        // TODO: Unassign Users here

        self::RebuildCache();

        return true;
    }

    /**
     * Check to see if it is a valid user group id
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the record is invalid or could not be found
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidUserGroupID($_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userGroupID)) {
            return false;
        }

        $_userGroupContainer = $_SWIFT->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups WHERE usergroupid = '" . $_userGroupID . "'");
        if (!$_userGroupContainer || !isset($_userGroupContainer['usergroupid']) || empty($_userGroupContainer['usergroupid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return true;
    }

    /**
     * Retrieve the User Group ID based on Title
     *
     * @author Varun Shoor
     * @param string $_groupTitle The User Group Title
     * @return int|bool The User Group ID
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTitle($_groupTitle)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userGroupContainer = $_SWIFT->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups
            WHERE title = '" . $_SWIFT->Database->Escape($_groupTitle) . "' AND grouptype = '" . self::TYPE_REGISTERED . "' ORDER BY usergroupid ASC");
        if (!$_userGroupContainer || !isset($_userGroupContainer['usergroupid']) || empty($_userGroupContainer['usergroupid'])) {
            return false;
        }

        return $_userGroupContainer['usergroupid'];
    }

    /**
     * Retrieve the Default User Group ID based on Group TYpe
     *
     * @author Varun Shoor
     * @param int $_groupType The User Group Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveDefaultUserGroupID($_groupType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidGroupType($_groupType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userGroupContainer = $_SWIFT->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups WHERE grouptype = '" . $_groupType . "' ORDER BY usergroupid ASC");
        if (!$_userGroupContainer || !isset($_userGroupContainer['usergroupid']) || empty($_userGroupContainer['usergroupid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_userGroupContainer['usergroupid'];
    }

    /**
     * Retrieve the Group Type Label
     *
     * @author Varun Shoor
     * @param mixed $_groupType The User Group Type
     * @return mixed "User Group Label" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetGroupTypeLabel($_groupType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidGroupType($_groupType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_groupType == self::TYPE_REGISTERED) {
            return $_SWIFT->Language->Get('ugregistered');
        } else {
            return $_SWIFT->Language->Get('ugguest');
        }

        return false;
    }

    /**
     * Retrieve the complete User Group ID List
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveUserGroupIDList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userGroupIDList = array();

        $_SWIFT->Database->Query("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups ORDER BY usergroupid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_userGroupIDList[] = $_SWIFT->Database->Record['usergroupid'];
        }

        return $_userGroupIDList;
    }
}

?>
