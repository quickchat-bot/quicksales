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
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The User Property Model
 *
 * @author Varun Shoor
 */
class SWIFT_UserProperty extends SWIFT_Model
{
    const TABLE_NAME = 'userproperties';
    const PRIMARY_KEY = 'userpropertyid';

    const TABLE_STRUCTURE = "userpropertyid I PRIMARY AUTO NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                updatedateline I DEFAULT '0' NOTNULL,
                                keyname C(255) DEFAULT '' NOTNULL,
                                keyvalue C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'userid, keyname';


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
            throw new SWIFT_Exception('Failed to load the User Property Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'userproperties', $this->GetUpdatePool(), 'UPDATE', "userpropertyid = '" . (int)($this->GetUserPropertyID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Property ID
     *
     * @author Varun Shoor
     * @return mixed "userpropertyid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserPropertyID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['userpropertyid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userproperties WHERE userpropertyid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['userpropertyid']) && !empty($_dataStore['userpropertyid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['userpropertyid']) || empty($this->_dataStore['userpropertyid'])) {
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
     * Create a new User Property
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param string $_keyName
     * @param string $_keyValue
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateOrUpdate(SWIFT_User $_SWIFT_UserObject, $_keyName, $_keyValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded() || empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userPropertyContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userproperties
            WHERE userid = '" . $_SWIFT_UserObject->GetUserID() . "' AND keyname = '" . $_SWIFT->Database->Escape($_keyName) . "'");
        if (isset($_userPropertyContainer['userpropertyid']) && !empty($_userPropertyContainer['userpropertyid'])) {
            $_SWIFT_UserPropertyObject = new SWIFT_UserProperty(new SWIFT_DataStore($_userPropertyContainer));
            if (!$_SWIFT_UserPropertyObject instanceof SWIFT_UserProperty || !$_SWIFT_UserPropertyObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_UserPropertyObject->Update($_keyName, $_keyValue);

            return true;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userproperties', array('userid' => $_SWIFT_UserObject->GetUserID(), 'dateline' => DATENOW, 'updatedateline' => DATENOW, 'keyname' => $_keyName, 'keyvalue' => $_keyValue), 'INSERT');
        $_userPropertyID = $_SWIFT->Database->Insert_ID();

        if (!$_userPropertyID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_userPropertyID;
    }

    /**
     * Update the User Property Record
     *
     * @author Varun Shoor
     * @param string $_keyName
     * @param string $_keyValue
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_keyName, $_keyValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('keyvalue', $_keyValue);
        $this->UpdatePool('updatedateline', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the User Property record
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

        self::DeleteList(array($this->GetUserPropertyID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of User Properties
     *
     * @author Varun Shoor
     * @param array $_userPropertyIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userPropertyIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userPropertyIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "userproperties WHERE userpropertyid IN (" . BuildIN($_userPropertyIDList) . ")");

        return true;
    }

    /**
     * Delete the Properties on a User
     *
     * @author Varun Shoor
     * @param array $_userIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnUser($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_userPropertyIDList = array();
        $_SWIFT->Database->Query("SELECT userpropertyid FROM " . TABLE_PREFIX . "userproperties WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userPropertyIDList[] = $_SWIFT->Database->Record['userpropertyid'];
        }

        if (!count($_userPropertyIDList)) {
            return false;
        }

        self::DeleteList($_userPropertyIDList);

        return true;
    }

    /**
     * Delete the Property on Key Name
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param string $_keyName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnName(SWIFT_User $_SWIFT_UserObject, $_keyName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded() || empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userPropertyContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userproperties
            WHERE userid = '" . $_SWIFT_UserObject->GetUserID() . "' AND keyname = '" . $_SWIFT->Database->Escape($_keyName) . "'");
        if (!isset($_userPropertyContainer['userpropertyid'])) {
            return false;
        }

        $_SWIFT_UserPropertyObject = new SWIFT_UserProperty(new SWIFT_DataStore($_userPropertyContainer));
        if (!$_SWIFT_UserPropertyObject instanceof SWIFT_UserProperty || !$_SWIFT_UserPropertyObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserPropertyObject->Delete();

        return true;
    }

    /**
     * Retrieve the Properties on User
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @return array The User Properties Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAllOnUser(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userPropertiesContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userproperties
            WHERE userid = '" . $_SWIFT_UserObject->GetUserID() . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_userPropertiesContainer[$_SWIFT->Database->Record['keyname']] = $_SWIFT->Database->Record['keyvalue'];
        }

        return $_userPropertiesContainer;
    }

    /**
     * Retrieve a key value on User
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param string $_keyName
     * @return string "Key Value" on Success, "" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUser(SWIFT_User $_SWIFT_UserObject, $_keyName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded() || empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userPropertyContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userproperties
            WHERE userid = '" . $_SWIFT_UserObject->GetUserID() . "' AND keyname = '" . $_SWIFT->Database->Escape($_keyName) . "'");
        if (!isset($_userPropertyContainer['userpropertyid'])) {
            return '';
        }

        return $_userPropertyContainer['keyvalue'];
    }

    /**
     * Update Bulk Key Values on a User
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param array $_propertiesContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateBulkOnUser(SWIFT_User $_SWIFT_UserObject, $_propertiesContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_propertiesContainer)) {
            return false;
        }

        foreach ($_propertiesContainer as $_keyName => $_keyValue) {
            SWIFT_UserProperty::CreateOrUpdate($_SWIFT_UserObject, $_keyName, $_keyValue);
        }

        return true;
    }
}

?>
