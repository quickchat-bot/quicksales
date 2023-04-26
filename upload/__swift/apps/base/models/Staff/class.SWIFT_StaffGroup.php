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

namespace Base\Models\Staff;

use Base\Models\CustomField\SWIFT_CustomFieldGroupPermission;
use SWIFT;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * Staff Group Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_StaffGroup extends SWIFT_Model
{
    const TABLE_NAME = 'staffgroup';
    const PRIMARY_KEY = 'staffgroupid';

    const TABLE_STRUCTURE = "staffgroupid I PRIMARY AUTO NOTNULL,
                                title C(100) DEFAULT '' NOTNULL,
                                isadmin I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'isadmin';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function __construct($_staffGroupID)
    {
        if (!$this->LoadData($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::__construct();
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
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'staffgroup', $this->GetUpdatePool(), 'UPDATE', "staffgroupid = '" . (int)($this->GetStaffGroupID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Staff Group ID
     *
     * @author Varun Shoor
     * @return mixed "staffgroupid" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffgroupid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_staffGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffgroup WHERE staffgroupid = '" .$_staffGroupID . "'");
        if (isset($_dataStore['staffgroupid']) && !empty($_dataStore['staffgroupid'])) {
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
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Insert a new staff group
     *
     * @author Varun Shoor
     * @param string $_title The Staff Group Title
     * @param bool $_isAdmin Whether the given group is an admin group
     * @return SWIFT_StaffGroup
     * @throws SWIFT_Staff_Exception If the Creation Fails or If Invalid Data Provided
     */
    public static function Insert($_title, $_isAdmin)
    {
        if (empty($_title)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffgroup', array('title' => $_title, 'isadmin' => (int)($_isAdmin)), 'INSERT');
        $_staffGroupID = $_SWIFT->Database->Insert_ID();
        if (!$_staffGroupID) {
            throw new SWIFT_Staff_Exception(SWIFT_CREATEFAILED);
        }

        self::RebuildCache();

        return new SWIFT_StaffGroup($_staffGroupID);
    }

    /**
     * Update the Staff Group
     *
     * @author Varun Shoor
     * @param string $_title The Staff Group Title
     * @param bool $_isAdmin Whether the given group is an admin group
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_isAdmin)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_title)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('isadmin', (int)($_isAdmin));

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }


    /**
     * Delete staff group record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->SetIsClassLoaded(false);

        self::DeleteList(array($this->GetStaffGroupID()));

        return true;
    }

    /**
     * Delete a List of Staff Group ID
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_staffGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgroup WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");

        SWIFT_Staff::DeleteOnStaffGroup($_staffGroupIDList);
        SWIFT_StaffGroupSettings::DeleteOnStaffGroup($_staffGroupIDList);
        SWIFT_StaffGroupAssign::DeleteOnStaffGroup($_staffGroupIDList);

        SWIFT_CustomFieldGroupPermission::DeleteOnStaffGroup($_staffGroupIDList);

        SWIFT_StaffGroupLink::DeleteOnStaffGroupList($_staffGroupIDList);

        self::RebuildCache();
        SWIFT_Staff::RebuildCache();
        SWIFT_StaffGroupSettings::RebuildCache();
        SWIFT_StaffGroupAssign::RebuildCache();
        SWIFT_StaffAssign::RebuildCache();

        return true;
    }

    /**
     * Retrieve a list of Staff Groups as Array
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return mixed "_staffContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveList($_staffGroupIDList)
    {
        if (!_is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffGroupContainer[$_SWIFT->Database->Record['staffgroupid']] = $_SWIFT->Database->Record;
        }

        if (!count($_staffGroupContainer)) {
            return false;
        }

        return $_staffGroupContainer;
    }

    /**
     * Retrieve Complete List of Staff Group ID's
     *
     * @author Varun Shoor
     * @return array The Staff Group ID List
     */
    public static function RetrieveStaffGroupIDList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupIDList = array();
        $_SWIFT->Database->Query("SELECT staffgroupid FROM " . TABLE_PREFIX . "staffgroup ORDER BY staffgroupid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffGroupIDList[] = $_SWIFT->Database->Record['staffgroupid'];
        }

        return $_staffGroupIDList;
    }

    /**
     * Rebuild the Staff GroupCache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_groupCache = array();
        $_groupSettings = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroupsettings ORDER BY sgroupsettingid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_groupSettings[$_SWIFT->Database->Record['staffgroupid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY staffgroupid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_groupCache[$_SWIFT->Database->Record['staffgroupid']] = $_SWIFT->Database->Record;

            if (isset($_groupSettings[$_SWIFT->Database->Record['staffgroupid']])) {
                $_groupCache[$_SWIFT->Database->Record['staffgroupid']]['settings'] = $_groupSettings[$_SWIFT->Database->Record['staffgroupid']];
            } else {
                $_groupCache[$_SWIFT->Database->Record['staffgroupid']]['settings'] = array();
            }
        }

        $_SWIFT->Cache->Update('staffgroupcache', $_groupCache);

        return true;
    }

    /**
     * Check to see if its a valid Staff Group ID
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidStaffGroupID($_staffGroupID)
    {

        if (empty($_staffGroupID)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupCache = $_SWIFT->Cache->Get('staffgroupcache');

        if (!isset($_staffGroupCache[$_staffGroupID])) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the Staff Group ID based on Title
     *
     * @author Varun Shoor
     * @param string $_groupTitle The Staff Group Title
     * @return int|bool The Staff Group ID
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTitle($_groupTitle)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupContainer = $_SWIFT->Database->QueryFetch("SELECT staffgroupid FROM " . TABLE_PREFIX . "staffgroup
            WHERE title = '" . $_SWIFT->Database->Escape($_groupTitle) . "' ORDER BY staffgroupid ASC");
        if (!$_staffGroupContainer || !isset($_staffGroupContainer['staffgroupid']) || empty($_staffGroupContainer['staffgroupid'])) {
            return false;
        }

        return $_staffGroupContainer['staffgroupid'];
    }
}

?>
