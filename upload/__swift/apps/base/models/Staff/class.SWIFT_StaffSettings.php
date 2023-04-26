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

namespace Base\Models\Staff;

use SWIFT;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * The Staff Settings Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_StaffSettings extends SWIFT_Model
{
    const TABLE_NAME = 'staffsettings';
    const PRIMARY_KEY = 'staffsettingid';

    const TABLE_STRUCTURE = "staffsettingid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                name C(100) DEFAULT '' NOTNULL,
                                value C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'staffid';

    const INDEX_2 = 'staffid, departmentid, name';
    const INDEXTYPE_2 = 'UNIQUE';

    const INDEX_3 = 'departmentid';


    private $_staffID = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function __construct($_staffID)
    {
        if (!$this->SetStaffID($_staffID)) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::__construct();
    }

    /**
     * The Staff ID
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public function SetStaffID($_staffID)
    {
        if (empty($_staffID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $this->_staffID = $_staffID;

        return true;
    }

    /**
     * Retrieves the Staff ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_staffID;
    }

    /**
     * Rebuilds the Staff Setting Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffsettings ORDER BY staffsettingid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['staffid']][$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        $_SWIFT->Cache->Update('staffpermissionscache', $_cache);

        return true;
    }

    /**
     * Clears all settings with the given staff member
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

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffsettings WHERE staffid = '" . (int)($this->GetStaffID()) . "'");

        return true;
    }

    /**
     * Rebuilds the Department Staff Settings
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param array $_staffSettings The Staff Setting Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RebuildDepartmentSettings($_departmentID, $_staffSettings)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        if (empty($_departmentID) || !isset($_departmentCache[$_departmentID])) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_permissionContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffsettings WHERE departmentid = '" . $_departmentID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_permissionContainer[$_SWIFT->Database->Record['staffid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        if (_is_array($_staffSettings)) {

            foreach ($_staffSettings as $_staffKey => $_staffVal) {
                if (!_is_array($_staffVal)) {
                    continue;
                }

                foreach ($_staffVal as $_permissionKey => $_permissionVal) {

                    if ($_permissionVal == '1' && (isset($_permissionContainer[$_staffKey][$_permissionKey]) && $_permissionContainer[$_staffKey][$_permissionKey] == '')) {
                        // Reserved
                    } else {
                        $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffsettings', array('staffid' => (int)($_staffKey), 'name' => $_permissionKey, 'value' => $_permissionVal, 'departmentid' => $_departmentID), array('staffid', 'departmentid', 'name'));
                    }
                }
            }
        } else {
            self::DeleteOnDepartmentList(array($_departmentID));
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuilds the Staff Settings
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param array $_staffSettings The Staff Setting Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RebuildStaffSettings($_staffID, $_staffSettings)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        if (empty($_staffID) || !isset($_staffCache[$_staffID])) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_permissionContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffsettings WHERE staffid = '" . $_staffID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_permissionContainer[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        self::DeleteOnStaffList(array($_staffID));
        if (_is_array($_staffSettings)) {
            foreach ($_staffSettings as $_departmentKey => $_departmentVal) {
                if (!_is_array($_departmentVal)) {
                    continue;
                }

                foreach ($_departmentVal as $_permissionKey => $_permissionVal) {

                    if ($_permissionVal == '1' && (!isset($_permissionContainer[$_departmentKey][$_permissionKey]) || $_permissionContainer[$_departmentKey][$_permissionKey] == '')) {
                        // Reserved
                        $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffsettings', array('staffid' => $_staffID, 'name' => $_permissionKey, 'value' => '1', 'departmentid' => $_departmentKey), array('staffid', 'departmentid', 'name'));
                    } else {
                        $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffsettings', array('staffid' => $_staffID, 'name' => $_permissionKey, 'value' => $_permissionVal, 'departmentid' => $_departmentKey), array('staffid', 'departmentid', 'name'));
                    }
                }
            }
        } else {
            self::DeleteOnStaffList(array($_staffID));
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve Staff Settings based on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return array
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnDepartment($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        if (empty($_departmentID) || !isset($_departmentCache[$_departmentID])) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_permissionContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffsettings WHERE departmentid = '" . $_departmentID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_permissionContainer[$_SWIFT->Database->Record['staffid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        return $_permissionContainer;
    }

    /**
     * Retrieve Staff Settings based on Staff ID
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return array
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnStaff($_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        if (empty($_staffID) || !isset($_staffCache[$_staffID])) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_permissionContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffsettings WHERE staffid = '" . $_staffID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_permissionContainer[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['name']] = $_SWIFT->Database->Record['value'];
        }

        return $_permissionContainer;
    }

    /**
     * Deletes on the department list
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnDepartmentList($_departmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_departmentIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffsettings WHERE departmentid IN (" . BuildIN($_departmentIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Deletes on the staff list
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaffList($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffsettings WHERE staffid IN (" . BuildIN($_staffIDList) . ")");

        self::RebuildCache();

        return true;
    }
}

?>
