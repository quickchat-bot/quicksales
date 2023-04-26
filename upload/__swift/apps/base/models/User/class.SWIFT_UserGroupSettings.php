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
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\User\SWIFT_UserPermissionContainer;

/**
 * Manages User Group Settings
 *
 * @author Varun Shoor
 */
class SWIFT_UserGroupSettings extends SWIFT_Model
{
    const TABLE_NAME = 'usergroupsettings';
    const PRIMARY_KEY = 'ugroupsettingid';

    const TABLE_STRUCTURE = "ugroupsettingid I PRIMARY AUTO NOTNULL,
                                usergroupid I DEFAULT '0' NOTNULL,
                                name C(100) DEFAULT '' NOTNULL,
                                value C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'usergroupid';

    const INDEX_2 = 'usergroupid, name';
    const INDEXTYPE_2 = 'UNIQUE';


    private $_userGroupID = 0;
    private $_settingsContainer = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_userGroupID)
    {
        parent::__construct();

        if (!$this->SetUserGroupID($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_settingsContainer = $this->GetSettings();
    }

    /**
     * Sets the User Group ID
     *
     * @author Varun Shoor
     * @param string $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetUserGroupID($_userGroupID)
    {
        if (empty($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_userGroupID = $_userGroupID;

        return true;
    }

    /**
     * Retrieve the User Group ID Value
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

        return $this->_userGroupID;
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

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroupsettings ORDER BY ugroupsettingid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['usergroupid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Cache->Update("usergroupsettingcache", $_cache);

        return true;
    }

    /**
     * Clears the settings cache for the given user group
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearSettings()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "usergroupsettings WHERE usergroupid = '" . (int)($this->GetUserGroupID()) . "'");

        return true;
    }

    /**
     * Delete the User Group Settings on User Group ID List
     *
     * @author Varun Shoor
     * @param array $_userGroupIDList The User Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUserGroup($_userGroupIDList)
    {
        if (!_is_array($_userGroupIDList)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usergroupsettings WHERE usergroupid IN (" . BuildIN($_userGroupIDList) . ")");

        return true;
    }

    /**
     * Rebuilds the user group settings from the base code
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildUserGroupSettings()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Get all registered apps
        $_appList = SWIFT_App::GetInstalledApps();

        // Get all User Group IDs
        $_userGroupIDList = SWIFT_UserGroup::RetrieveUserGroupIDList();

        $_settingCache = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroupsettings");
        while ($_SWIFT->Database->NextRecord()) {
            $_settingCache[$_SWIFT->Database->Record['usergroupid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usergroupsettings");

        // Rebuild all permissions
        self::ProcessSettingContainer(SWIFT_UserPermissionContainer::GetDefault(), $_settingCache, $_appList, $_userGroupIDList);

        return true;
    }

    /**
     * Process the Setting Container
     *
     * @author Varun Shoor
     * @param array $_settingContainer The Setting Container
     * @param array $_settingCache The Existing Setting Cache
     * @param array $_appList The Registered App List
     * @param array $_userGroupIDList The User Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function ProcessSettingContainer($_settingContainer, $_settingCache, $_appList, $_userGroupIDList)
    {
        foreach ($_settingContainer as $_key => $_val) {
            if (in_array($_key, $_appList)) {
                foreach ($_val as $_permissionKey => $_permissionVal) {
                    for ($ii = 0; $ii < count($_userGroupIDList); $ii++) {
                        if (is_array($_permissionVal) && count($_permissionVal[1])) {
                            foreach ($_permissionVal[1] as $_subPermissionKey => $_subPermissionVal) {
                                self::ReplaceGroupSettingGlobal($_userGroupIDList[$ii], $_subPermissionVal, self::GetDefaultPermissionValue($_settingCache, $_userGroupIDList[$ii], $_subPermissionVal));
                            }
                        } else {
                            self::ReplaceGroupSettingGlobal($_userGroupIDList[$ii], $_permissionVal, self::GetDefaultPermissionValue($_settingCache, $_userGroupIDList[$ii], $_permissionVal));
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
     * @param int $_userGroupID The User Group ID
     * @param string $_name The Permission Name
     * @return string
     */
    public static function GetDefaultPermissionValue($_settingCache, $_userGroupID, $_name)
    {
        if (isset($_settingCache[$_userGroupID][$_name])) {
            $_settingValue = $_settingCache[$_userGroupID][$_name];
        } else {
            $_settingValue = 1;
        }

        return $_settingValue;
    }

    /**
     * Replace the Group Setting with the given value
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @param string $_name The Permission Name
     * @param string $_value The Permission Value
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ReplaceGroupSettingGlobal($_userGroupID, $_name, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'usergroupsettings', array('usergroupid' => $_userGroupID, 'name' => $_name, 'value' => (int)($_value)), array('usergroupid', 'name'));

        return true;
    }

    /**
     * Replace the Group Setting with the given value
     *
     * @author Varun Shoor
     * @param string $_name The Permission Name
     * @param int $_value The Permission Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function ReplaceGroupSetting($_name, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_value = (int)($_value);

        // We are changing a previous value from 0 > 1?
        if (isset($this->_settingsContainer[$_name]) && $this->_settingsContainer[$_name] == '0' && $_value == '1') {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usergroupsettings WHERE usergroupid = '" . (int)($this->GetUserGroupID()) . "' AND name = '" . $this->Database->Escape($_name) . "'");

            // We just ignore the '1' values for now
        } else if ($_value == '1') {

        } else if ($_value == '0') {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'usergroupsettings', array('usergroupid' => (int)($this->GetUserGroupID()), 'name' => $_name, 'value' => $_value), array('usergroupid', 'name'));
        }


        return true;
    }

    /**
     * Reprocess the Group Setting Array
     *
     * @author Varun Shoor
     * @param array $_permissionContainer The Permission Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReprocessGroupSettingArray($_permissionContainer, $_groupPostSettings)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
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
                                $this->ReplaceGroupSetting($_subPermissionVal, 0);
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReprocessGroupSettings($_groupSettings)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ClearSettings();

        // Rebuild all User permissions
        if (_is_array($_groupSettings)) {
            $this->ReprocessGroupSettingArray(SWIFT_UserPermissionContainer::GetDefault(), $_groupSettings);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the Settings on basis of a user group
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
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroupsettings WHERE usergroupid = '" . (int)($this->GetUserGroupID()) . "'");
        while ($this->Database->NextRecord()) {
            $_settingsContainer[$this->Database->Record['name']] = $this->Database->Record['value'];
        }

        return $_settingsContainer;
    }
}

?>
