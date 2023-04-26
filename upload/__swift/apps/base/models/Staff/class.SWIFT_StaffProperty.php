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

namespace Base\Models\Staff;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Staff Property Model
 *
 * @author Varun Shoor
 */
class SWIFT_StaffProperty extends SWIFT_Model
{
    const TABLE_NAME = 'staffproperties';
    const PRIMARY_KEY = 'staffpropertyid';

    const TABLE_STRUCTURE = "staffpropertyid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                updatedateline I DEFAULT '0' NOTNULL,
                                keyname C(255) DEFAULT '' NOTNULL,
                                keyvalue C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'staffid, keyname';


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
            throw new SWIFT_Exception('Failed to load the Staff Property Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'staffproperties', $this->GetUpdatePool(), 'UPDATE', "staffpropertyid = '" . (int)($this->GetStaffPropertyID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Staff Property ID
     *
     * @author Varun Shoor
     * @return mixed "staffpropertyid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetStaffPropertyID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffpropertyid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffproperties WHERE staffpropertyid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['staffpropertyid']) && !empty($_dataStore['staffpropertyid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['staffpropertyid']) || empty($this->_dataStore['staffpropertyid'])) {
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
     * Create a new Staff Property
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param string $_keyName
     * @param string $_keyValue
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateOrUpdate(SWIFT_Staff $_SWIFT_StaffObject, $_keyName, $_keyValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded() || empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffPropertyContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffproperties
            WHERE staffid = '" . (int)($_SWIFT_StaffObject->GetStaffID()) . "' AND keyname = '" . $_SWIFT->Database->Escape($_keyName) . "'");
        if (isset($_staffPropertyContainer['staffpropertyid']) && !empty($_staffPropertyContainer['staffpropertyid'])) {
            $_SWIFT_StaffPropertyObject = new SWIFT_StaffProperty(new SWIFT_DataStore($_staffPropertyContainer));
            if (!$_SWIFT_StaffPropertyObject instanceof SWIFT_StaffProperty || !$_SWIFT_StaffPropertyObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_StaffPropertyObject->Update($_keyName, $_keyValue);

            return true;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffproperties', array('staffid' => (int)($_SWIFT_StaffObject->GetStaffID()), 'dateline' => DATENOW, 'updatedateline' => DATENOW, 'keyname' => $_keyName, 'keyvalue' => $_keyValue), 'INSERT');
        $_staffPropertyID = $_SWIFT->Database->Insert_ID();

        if (!$_staffPropertyID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_staffPropertyID;
    }

    /**
     * Update the Staff Property Record
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
     * Delete the Staff Property record
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

        self::DeleteList(array($this->GetStaffPropertyID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Staff Properties
     *
     * @author Varun Shoor
     * @param array $_staffPropertyIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_staffPropertyIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffPropertyIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffproperties WHERE staffpropertyid IN (" . BuildIN($_staffPropertyIDList) . ")");

        return true;
    }

    /**
     * Delete the Properties on a Staff
     *
     * @author Varun Shoor
     * @param array $_staffIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnStaff($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_staffPropertyIDList = array();
        $_SWIFT->Database->Query("SELECT staffpropertyid FROM " . TABLE_PREFIX . "staffproperties WHERE staffid IN (" . BuildIN($_staffIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffPropertyIDList[] = $_SWIFT->Database->Record['staffpropertyid'];
        }

        if (!count($_staffPropertyIDList)) {
            return false;
        }

        self::DeleteList($_staffPropertyIDList);

        return true;
    }

    /**
     * Delete the Property on Key Name
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param string $_keyName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnName(SWIFT_Staff $_SWIFT_StaffObject, $_keyName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded() || empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffPropertyContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffproperties
            WHERE staffid = '" . (int)($_SWIFT_StaffObject->GetStaffID()) . "' AND keyname = '" . $_SWIFT->Database->Escape($_keyName) . "'");
        if (!isset($_staffPropertyContainer['staffpropertyid'])) {
            return false;
        }

        $_SWIFT_StaffPropertyObject = new SWIFT_StaffProperty(new SWIFT_DataStore($_staffPropertyContainer));
        if (!$_SWIFT_StaffPropertyObject instanceof SWIFT_StaffProperty || !$_SWIFT_StaffPropertyObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_StaffPropertyObject->Delete();

        return true;
    }

    /**
     * Retrieve the Properties on Staff
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return array The Staff Properties Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAllOnStaff(SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffPropertiesContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffproperties
            WHERE staffid = '" . (int)($_SWIFT_StaffObject->GetStaffID()) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffPropertiesContainer[$_SWIFT->Database->Record['keyname']] = $_SWIFT->Database->Record['keyvalue'];
        }

        return $_staffPropertiesContainer;
    }

    /**
     * Retrieve a key value on Staff
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param string $_keyName
     * @return string|null "Key Value" on Success, "" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnStaff(SWIFT_Staff $_SWIFT_StaffObject, $_keyName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded() || empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffPropertyContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffproperties
            WHERE staffid = '" . (int)($_SWIFT_StaffObject->GetStaffID()) . "' AND keyname = '" . $_SWIFT->Database->Escape($_keyName) . "'");
        if (!isset($_staffPropertyContainer['staffpropertyid'])) {
            return '';
        }

        return $_staffPropertyContainer['keyvalue'];
    }

    /**
     * Update Bulk Key Values on a Staff
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param array $_propertiesContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateBulkOnStaff(SWIFT_Staff $_SWIFT_StaffObject, $_propertiesContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_propertiesContainer)) {
            return false;
        }

        foreach ($_propertiesContainer as $_keyName => $_keyValue) {
            SWIFT_StaffProperty::CreateOrUpdate($_SWIFT_StaffObject, $_keyName, $_keyValue);
        }

        return true;
    }
}

?>
