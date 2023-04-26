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
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Library\Staff\SWIFT_StaffPermissionContainer;

/**
 * Manages Staff Group Settings
 *
 * @author Varun Shoor
 */
class SWIFT_StaffGroupSettings extends SWIFT_Model
{
    const TABLE_NAME = 'staffgroupsettings';
    const PRIMARY_KEY = 'staffgroupsettings';

    const TABLE_STRUCTURE = "sgroupsettingid I PRIMARY AUTO NOTNULL,
                                staffgroupid I DEFAULT '0' NOTNULL,
                                name C(100) DEFAULT '' NOTNULL,
                                value C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'staffgroupid';

    const INDEX_2 = 'staffgroupid, name';
    const INDEXTYPE_2 = 'UNIQUE';


    private $_staffGroupID = 0;
    private $_settingsContainer = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @throws SWIFT_Exception
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function __construct($_staffGroupID)
    {
        parent::__construct();

        if (!$this->SetStaffGroupID($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_settingsContainer = $this->GetSettings();
    }

    /**
     * Sets the Staff Group ID
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public function SetStaffGroupID($_staffGroupID)
    {
        if (empty($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $this->_staffGroupID = $_staffGroupID;

        return true;
    }

    /**
     * Retrieve the Staff Group ID Value
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

        return $this->_staffGroupID;
    }

    /**
     * Rebuilds the Group Settings Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroupsettings ORDER BY sgroupsettingid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['staffgroupid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Cache->Update('groupsettingcache', $_cache);

        return true;
    }

    /**
     * Clears the settings cache for the given staff group
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function ClearSettings()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgroupsettings WHERE staffgroupid = '" . (int)($this->GetStaffGroupID()) . "'");

        return true;
    }

    /**
     * Delete Staff Group Settings on Staff Group ID List
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaffGroup($_staffGroupIDList)
    {
        if (!_is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgroupsettings WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");

        return true;
    }

    /**
     * Rebuilds the staff group settings from the base code
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildStaffGroupSettings()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Get all registered apps
        $_appList = SWIFT_App::GetInstalledApps();

        // Get all Staff Group IDs
        $_staffGroupIDList = SWIFT_StaffGroup::RetrieveStaffGroupIDList();

        $_settingCache = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroupsettings");
        while ($_SWIFT->Database->NextRecord()) {
            $_settingCache[$_SWIFT->Database->Record['staffgroupid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgroupsettings");

        // Rebuild all Staff permissions
        self::ProcessSettingContainer(SWIFT_StaffPermissionContainer::GetStaff(), $_settingCache, $_appList, $_staffGroupIDList);

        // Rebuild all Admin permissions
        self::ProcessSettingContainer(SWIFT_StaffPermissionContainer::GetAdmin(), $_settingCache, $_appList, $_staffGroupIDList);

        return true;
    }

    /**
     * Process the Setting Container
     *
     * @author Varun Shoor
     * @param array $_settingContainer The Setting Container
     * @param array $_settingCache The Existing Setting Cache
     * @param array $_appList The Registered App List
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function ProcessSettingContainer($_settingContainer, $_settingCache, $_appList, $_staffGroupIDList)
    {
        foreach ($_settingContainer as $_key => $_val) {
            if (in_array($_key, $_appList)) {
                foreach ($_val as $_permissionKey => $_permissionVal) {
                    for ($ii = 0; $ii < count($_staffGroupIDList); $ii++) {
                        if (is_array($_permissionVal) && count($_permissionVal[1])) {
                            foreach ($_permissionVal[1] as $_subPermissionKey => $_subPermissionVal) {
                                self::ReplaceGroupSettingGlobal($_staffGroupIDList[$ii], $_subPermissionVal, self::GetDefaultPermissionValue($_settingCache, $_staffGroupIDList[$ii], $_subPermissionVal));
                            }
                        } else {
                            self::ReplaceGroupSettingGlobal($_staffGroupIDList[$ii], $_permissionVal, self::GetDefaultPermissionValue($_settingCache, $_staffGroupIDList[$ii], $_permissionVal));
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve the setting value for a given permission
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @param string $_name The Permission Name
     * @return int|string
     */
    public static function GetDefaultPermissionValue($_settingCache, $_staffGroupID, $_name)
    {
        if (isset($_settingCache[$_staffGroupID][$_name])) {
            $_settingValue = $_settingCache[$_staffGroupID][$_name];
        } else {
            $_settingValue = 1;
        }

        return $_settingValue;
    }

    /**
     * Replace the Group Setting with the given value
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @param string $_name The Permission Name
     * @param string $_value The Permission Value
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ReplaceGroupSettingGlobal($_staffGroupID, $_name, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffgroupsettings', array('staffgroupid' => $_staffGroupID, 'name' => $_name, 'value' => (int)($_value)), array('staffgroupid', 'name'));

        return true;
    }

    /**
     * Replace the Group Setting with the given value
     *
     * @author Varun Shoor
     * @param string $_name The Permission Name
     * @param string $_value The Permission Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function ReplaceGroupSetting($_name, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_value = (int)($_value);

        // We are changing a previous value from 0 > 1?
        if (isset($this->_settingsContainer[$_name]) && $this->_settingsContainer[$_name] == '0' && $_value == '1') {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgroupsettings WHERE staffgroupid = '" . (int)($this->GetStaffGroupID()) . "' AND name = '" . $this->Database->Escape($_name) . "'");

            // We just ignore the '1' values for now
        } else if ($_value == '1') {

        } else if ($_value == '0') {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffgroupsettings', array('staffgroupid' => (int)($this->GetStaffGroupID()), 'name' => $_name, 'value' => $_value), array('staffgroupid', 'name'));
        }

        return true;
    }

    /**
     * Reprocess the Group Setting Array
     *
     * @author Varun Shoor
     * @param array $_permissionContainer The Permission Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function ReprocessGroupSettingArray($_permissionContainer, $_groupPostSettings)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($_permissionContainer as $_key => $_val) {
            if (SWIFT_App::IsInstalled($_key)) {
                if (!_is_array($_val)) {
                    continue;
                }

                foreach ($_val as $_permissionKey => $_permissionVal) {
                    if (is_array($_permissionVal) && count($_permissionVal[1])) {
                        foreach ($_permissionVal[1] as $_subPermissionKey => $_subPermissionVal) {
                            if (!isset($_groupPostSettings[$_subPermissionVal])) {
                                $this->ReplaceGroupSetting($_subPermissionVal, '0');
                            } else {
                                $this->ReplaceGroupSetting($_subPermissionVal, $_groupPostSettings[$_subPermissionVal]);
                            }
                        }
                    } else {

                        if (isset($_groupPostSettings[$_permissionVal])) {
                            $this->ReplaceGroupSetting($_permissionVal, $_groupPostSettings[$_permissionVal]);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Reprocess the Group Settings
     *
     * @author Varun Shoor
     * @param array $_groupSettings The Group Settings Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function ReprocessGroupSettings($_groupSettings)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ClearSettings();

        // Rebuild all Staff permissions
        if (_is_array($_groupSettings)) {
            $this->ReprocessGroupSettingArray(SWIFT_StaffPermissionContainer::GetStaff(), $_groupSettings);
            $this->ReprocessGroupSettingArray(SWIFT_StaffPermissionContainer::GetAdmin(), $_groupSettings);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the Settings on basis of a staff group
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSettings()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_settingsContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroupsettings WHERE staffgroupid = '" . (int)($this->GetStaffGroupID()) . "'");
        while ($this->Database->NextRecord()) {
            $_settingsContainer[$this->Database->Record['name']] = $this->Database->Record['value'];
        }

        return $_settingsContainer;
    }
}

?>
