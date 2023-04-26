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

namespace Base\Models\CustomField;

use SWIFT;
use Base\Library\CustomField\SWIFT_CustomField_Exception;
use SWIFT_Model;

/**
 * The Custom Field Group Permission (Staff/StaffGroup) Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldGroupPermission extends SWIFT_Model
{
    const TABLE_NAME = 'customfieldgrouppermissions';
    const PRIMARY_KEY = 'customfieldgrouppermissionsid';

    const TABLE_STRUCTURE = "customfieldgrouppermissionsid I PRIMARY AUTO NOTNULL,
                                typeid I DEFAULT '0' NOTNULL,
                                customfieldgroupid I DEFAULT '0' NOTNULL,
                                cfgrouptype C(20) DEFAULT '' NOTNULL,
                                accessmask C(20) DEFAULT '' NOTNULL";

    const INDEX_1 = 'customfieldgroupid, cfgrouptype';
    const INDEX_2 = 'cfgrouptype, typeid';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_STAFF = 'staff';
    const TYPE_STAFFGROUP = 'staffgroup';

    const ACCESS_YES = 'yes';
    const ACCESS_NO = 'no';
    const ACCESS_NOTSET = 'notset';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupPermissionID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Record could not be loaded
     */
    public function __construct($_customFieldGroupPermissionID)
    {
        parent::__construct();

        if (!$this->LoadData($_customFieldGroupPermissionID)) {
            throw new SWIFT_CustomField_Exception('Failed to load Custom Field Group Permission ID: ' . $_customFieldGroupPermissionID);
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
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldgrouppermissions', $this->GetUpdatePool(), 'UPDATE', "customfieldgrouppermissions = '" . (int)($this->GetCustomFieldGroupPermissionID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field Group Permission ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldgrouppermissionid" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetCustomFieldGroupPermissionID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldgroupermissionid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupPermissionID The Custom Field Group Permission ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_customFieldGroupPermissionID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldgrouppermissions WHERE customfieldgrouppermissionid = '" . $_customFieldGroupPermissionID . "'");
        if (isset($_dataStore['customfieldgrouppermissionid']) && !empty($_dataStore['customfieldgrouppermissionid'])) {
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
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid permission type
     *
     * @author Varun Shoor
     * @param mixed $_permissionType The Permission Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_permissionType)
    {
        if ($_permissionType == self::TYPE_STAFF || $_permissionType == self::TYPE_STAFFGROUP) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid access mask
     *
     * @author Varun Shoor
     * @param mixed $_accessMask The Access Mask
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAccessMask($_accessMask)
    {
        if ($_accessMask == self::ACCESS_YES || $_accessMask == self::ACCESS_NO || $_accessMask == self::ACCESS_NOTSET) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Custom Field Group Permission Entry
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @param int $_typeID The Type ID the Group is linked to
     * @param mixed $_permissionType The permission Type
     * @param mixed $_accessMask The Access Mask
     * @param bool $_rebuildCache Whether the Cache should be rebuilt automatically
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If Invalid Data is Provided
     */
    public static function Create($_customFieldGroupID, $_typeID, $_permissionType, $_accessMask, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldGroupID) || empty($_typeID) || !self::IsValidType($_permissionType) || !self::IsValidAccessMask($_accessMask)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldgrouppermissions', array('typeid' => $_typeID, 'customfieldgroupid' => $_customFieldGroupID, 'cfgrouptype' => $_permissionType, 'accessmask' => $_accessMask), 'INSERT');

        if ($_rebuildCache) {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Delete the Permission Link record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCustomFieldPermissionID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Field Group Permissions
     *
     * @author Varun Shoor
     * @param array $_customFieldGroupPermissionIDList Delete a list of Permissions
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldGroupPermissionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldGroupPermissionIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldgrouppermissions WHERE customfieldgrouppermissionid IN (" . BuildIN($_customFieldGroupPermissionIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Permissions based on Custom Field Group
     *
     * @author Varun Shoor
     * @param array $_customFieldGroupIDList The Custom Field Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnCustomFieldGroup($_customFieldGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldGroupIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldgrouppermissions WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Permissions based on Staff
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaff($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldgrouppermissions WHERE cfgrouptype = '" . self::TYPE_STAFF . "' AND typeid IN (" . BuildIN($_staffIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Permissions based on Staff Group
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaffGroup($_staffGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldgrouppermissions WHERE cfgrouptype = '" . self::TYPE_STAFFGROUP . "' AND typeid IN (" . BuildIN($_staffGroupIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Custom Field Group Permission Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgrouppermissions ORDER BY customfieldgrouppermissionsid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_cache[$_SWIFT->Database->Record3['cfgrouptype']][$_SWIFT->Database->Record3['customfieldgroupid']][$_SWIFT->Database->Record3['typeid']] = $_SWIFT->Database->Record3['accessmask'];
        }

        $_SWIFT->Cache->Update('cfgrouppermissioncache', $_cache);

        return true;
    }

    /**
     * KAYAKOC-5722
     * Added as a fill-in method to clear phpstan error since I'm not certain what function
     * the supposed method was intended to have performed
     *
     * @author Banjo Mofesola Paul <banjo.paul@aurea.com>
     */
    private function GetCustomFieldPermissionID()
    {
    }
}

?>
