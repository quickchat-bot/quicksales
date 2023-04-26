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

use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * The Staff <> Department Link Management Object
 *
 * @author Varun Shoor
 */
class SWIFT_StaffAssign extends SWIFT_Model
{
    const TABLE_NAME = 'staffassigns';
    const PRIMARY_KEY = 'staffassignid';

    const TABLE_STRUCTURE = "staffassignid I PRIMARY AUTO NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'staffid';
    const INDEX_2 = 'departmentid';

    const INDEX_3 = 'departmentid, staffid';
    const INDEXTYPE_3 = 'UNIQUE';


    /**
     * Deletes the Staff Assignment Records
     *
     * @author Varun Shoor
     * @param SWIFT_Model $_SWIFT_LinkObject The Link Object (SWIFT_Department OR SWIFT_Staff)
     * @param array $_idList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function DeleteList($_SWIFT_LinkObject, $_idList = [])
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((!$_SWIFT_LinkObject instanceof SWIFT_Department || !$_SWIFT_LinkObject->GetIsClassLoaded()) && (!$_SWIFT_LinkObject instanceof SWIFT_Staff || !$_SWIFT_LinkObject->GetIsClassLoaded())) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_LinkObject instanceof SWIFT_Department) {
            if (empty($_idList)) {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffassigns WHERE departmentid = '" . (int)($_SWIFT_LinkObject->GetDepartmentID()) . "'");
            } else {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffassigns WHERE departmentid = '" . (int)($_SWIFT_LinkObject->GetDepartmentID()) . "' AND staffid IN (" . BuildIN($_idList) . ")");
            }
        } else if ($_SWIFT_LinkObject instanceof SWIFT_Staff) {
            if (empty($_idList)) {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffassigns WHERE staffid = '" . (int)($_SWIFT_LinkObject->GetStaffID()) . "'");
            } else {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffassigns WHERE staffid = '" . (int)($_SWIFT_LinkObject->GetStaffID()) . "' AND departmentid IN (" . BuildIN($_idList) . ")");
            }
        }

        return true;
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

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffassigns WHERE departmentid IN (" . BuildIN($_departmentIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Deletes on the Staff ID list
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

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffassigns WHERE staffid IN (" . BuildIN($_staffIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffassigns ORDER BY staffassignid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['staffid']][] = $_SWIFT->Database->Record['departmentid'];
        }

        $_SWIFT->Cache->Update('staffassigncache', $_cache);

        return false;
    }

    /**
     * Insert a new Staff <> Department Assignment Record
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The Staff Object
     * @param array $_departmentIDList The Department ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function AssignStaffList(SWIFT_Staff $_SWIFT_StaffObject, $_departmentIDList = [])
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_departmentIDList)) {
            self::DeleteList($_SWIFT_StaffObject);

            self::RebuildCache();

            return true;
        }

        // First we iterate through all staff and unassign any that are not in list
        $_unassignDepartmentIDList = array();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        foreach ($_departmentCache as $_key => $_val) {
            if (!in_array($_val['departmentid'], $_departmentIDList)) {
                $_unassignDepartmentIDList[] = $_val['departmentid'];
            }
        }

        // Are there any entries?
        if (count($_unassignDepartmentIDList)) {
            self::DeleteList($_SWIFT_StaffObject, $_unassignDepartmentIDList);
        }

        if (empty($_departmentIDList)) {
            return true;
        }

        // Assign the staff users to this department
        foreach ($_departmentIDList as $_key => $_val) {
            self::Assign($_SWIFT_StaffObject, $_val);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Insert a new Staff <> Department Assignment Record
     *
     * @author Varun Shoor
     * @param SWIFT_Department $_SWIFT_DepartmentObject The Department Object
     * @param array $_staffIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function AssignDepartmentList(SWIFT_Department $_SWIFT_DepartmentObject, $_staffIDList = [])
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_staffIDList)) {
            self::DeleteList($_SWIFT_DepartmentObject);

            self::RebuildCache();

            return true;
        }

        // First we iterate through all staff and unassign any that are not in list
        $_unassignStaffIDList = array();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        if (_is_array($_staffCache)) {
            foreach ($_staffCache as $_key => $_val) {
                if (!in_array($_val['staffid'], $_staffIDList)) {
                    $_unassignStaffIDList[] = $_val['staffid'];
                }
            }
        }

        // Are there any entries?
        if (count($_unassignStaffIDList)) {
            self::DeleteList($_SWIFT_DepartmentObject, $_unassignStaffIDList);
        }

        if (empty($_staffIDList)) {
            return true;
        }

        // Assign the staff users to this department
        foreach ($_staffIDList as $_key => $_val) {
            self::Assign($_SWIFT_DepartmentObject, $_val);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Assign the Staff to Given Department
     *
     * @author Varun Shoor
     * @param SWIFT_Model $_SWIFT_LinkObject The Department/Staff Object
     * @param int $_typeID The Type ID (Staff/Department)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    static private function Assign($_SWIFT_LinkObject, $_typeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((!$_SWIFT_LinkObject instanceof SWIFT_Department || !$_SWIFT_LinkObject->GetIsClassLoaded()) && (!$_SWIFT_LinkObject instanceof SWIFT_Staff || !$_SWIFT_LinkObject->GetIsClassLoaded())) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_LinkObject instanceof SWIFT_Department) {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffassigns', array('departmentid' => $_SWIFT_LinkObject->GetDepartmentID(), 'staffid' => $_typeID), array('departmentid', 'staffid'));

            // Assign to parent department if it exists
            $_SWIFT_ParentDepartmentObject = $_SWIFT_LinkObject->GetParentDepartmentObject();

            // We return true even if the object was not loaded because parentdepartmentid is optional field
            if (!$_SWIFT_ParentDepartmentObject instanceof SWIFT_Department || !$_SWIFT_ParentDepartmentObject->GetIsClassLoaded()) {
                return true;
            }

            $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffassigns', array('departmentid' => $_SWIFT_ParentDepartmentObject->GetDepartmentID(), 'staffid' => $_typeID), array('departmentid', 'staffid'));
        } else if ($_SWIFT_LinkObject instanceof SWIFT_Staff) {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'staffassigns', array('staffid' => $_SWIFT_LinkObject->GetStaffID(), 'departmentid' => $_typeID), array('staffid', 'departmentid'));
        }

        return true;
    }
}

?>
