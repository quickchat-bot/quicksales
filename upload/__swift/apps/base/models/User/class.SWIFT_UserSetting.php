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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The User Setting Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserSetting extends SWIFT_Model
{
    const TABLE_NAME = 'usersettings';
    const PRIMARY_KEY = 'usersettingid';

    const TABLE_STRUCTURE = "usersettingid I PRIMARY AUTO NOTNULL,
                                name C(255) DEFAULT '' NOTNULL,
                                value C(255) DEFAULT '' NOTNULL,
                                userid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'userid';

    const INDEX_2 = 'userid, name';
    const INDEXTYPE_2 = 'UNIQUE';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load User Setting Object');
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
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'usersettings', $this->GetUpdatePool(), 'UPDATE', "usersettingid = '" . (int)($this->GetUserSettingID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Setting ID
     *
     * @author Varun Shoor
     * @return mixed "usersettingid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserSettingID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['usersettingid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "usersettings WHERE usersettingid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['usersettingid']) && !empty($_dataStore['usersettingid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['usersettingid']) || empty($this->_dataStore['usersettingid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create or Update a User Setting record
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param string $_name The Setting Name
     * @param string $_value The Setting Value
     * @return int The User Setting Record
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Replace($_userID, $_name, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_name)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'usersettings', array('userid' => $_userID, 'name' => $_name, 'value' => $_value), array('userid', 'name'));
        $_userSettingID = $_SWIFT->Database->Insert_ID();

        if (!$_userSettingID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_userSettingID;
    }

    /**
     * Delete the User Setting record
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

        self::DeleteList(array($this->GetUserSettingID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of User Settings
     *
     * @author Varun Shoor
     * @param array $_userSettingIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userSettingIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userSettingIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usersettings WHERE usersettingid IN (" . BuildIN($_userSettingIDList) . ")");

        return true;
    }

    /**
     * Delete the User Settings on the User Record
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUser($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_userSettingIDList = array();
        $_SWIFT->Database->Query("SELECT usersettingid FROM " . TABLE_PREFIX . "usersettings WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userSettingIDList[] = (int)($_SWIFT->Database->Record['usersettingid']);
        }

        if (!count($_userSettingIDList)) {
            return false;
        }

        self::DeleteList($_userSettingIDList);

        return true;
    }

    /**
     * Retrieve the Ticket List on User
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object
     * @return array The User Setting List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUser(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userSettingList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usersettings WHERE userid = '" . $_SWIFT_UserObject->GetUserID() . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_userSettingList[$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        return $_userSettingList;
    }
}

?>
