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
 * The Staff Group (Team) <> Department Link Management Object
 *
 * @author Varun Shoor
 */
class SWIFT_StaffGroupAssign extends SWIFT_Model
{
    const TABLE_NAME = 'groupassigns';
    const PRIMARY_KEY = 'groupassignid';

    const TABLE_STRUCTURE = "groupassignid I PRIMARY AUTO NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                staffgroupid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'staffgroupid';
    const INDEX_2 = 'departmentid';

    const INDEX_3 = 'departmentid, staffgroupid';
    const INDEXTYPE_3 = 'UNIQUE';

    /**
     * Deletes the Staff Group Assignment Records
     *
     * @author Varun Shoor
     * @param SWIFT_Model $_SWIFT_LinkObject The Link Object
     * @param array $_typeIDList The Type ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function DeleteList($_SWIFT_LinkObject, $_typeIDList = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((!$_SWIFT_LinkObject instanceof SWIFT_Department || !$_SWIFT_LinkObject->GetIsClassLoaded()) && (!$_SWIFT_LinkObject instanceof SWIFT_StaffGroup || !$_SWIFT_LinkObject->GetIsClassLoaded())) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_LinkObject instanceof SWIFT_Department) {
            if (empty($_typeIDList)) {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "groupassigns WHERE departmentid = '" . (int)($_SWIFT_LinkObject->GetDepartmentID()) . "'");
            } else {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "groupassigns WHERE departmentid = '" . (int)($_SWIFT_LinkObject->GetDepartmentID()) . "' AND staffgroupid IN (" . BuildIN($_typeIDList) . ")");
            }
        } else if ($_SWIFT_LinkObject instanceof SWIFT_StaffGroup) {
            if (empty($_typeIDList)) {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "groupassigns WHERE staffgroupid = '" . (int)($_SWIFT_LinkObject->GetStaffGroupID()) . "'");
            } else {
                $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "groupassigns WHERE staffgroupid = '" . (int)($_SWIFT_LinkObject->GetStaffGroupID()) . "' AND departmentid IN (" . BuildIN($_typeIDList) . ")");
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

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "groupassigns WHERE departmentid IN (" . BuildIN($_departmentIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete Staff on Staff Group ID List
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

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "groupassigns WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");

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

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "groupassigns ORDER BY groupassignid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['staffgroupid']][] = $_SWIFT->Database->Record['departmentid'];
        }

        $_SWIFT->Cache->Update('groupassigncache', $_cache);

        return false;
    }

    /**
     * Insert a new Staff Group <> Department Assignment Record
     *
     * @author Varun Shoor
     * @param SWIFT_Department $_SWIFT_DepartmentObject The Department Object
     * @param array $_staffGroupIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function AssignDepartmentList($_SWIFT_DepartmentObject, $_staffGroupIDList)
    {
        if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!count($_staffGroupIDList)) {
            self::DeleteList($_SWIFT_DepartmentObject);

            self::RebuildCache();

            return true;
        }

        // First we iterate through all staff and unassign any that are not in list
        $_unassignStaffGroupIDList = array();

        $_staffGroupCache = $_SWIFT->Cache->Get('staffgroupcache');

        if (_is_array($_staffGroupCache)) {
            foreach ($_staffGroupCache as $_key => $_val) {
                if (!in_array($_val['staffgroupid'], $_staffGroupIDList)) {
                    $_unassignStaffGroupIDList[] = $_val['staffgroupid'];
                }
            }
        }

        // Are there any entries?
        if (count($_unassignStaffGroupIDList)) {
            self::DeleteList($_SWIFT_DepartmentObject, $_unassignStaffGroupIDList);
        }

        if (!count($_staffGroupIDList)) {
            return true;
        }

        // Assign the staff users to this department
        foreach ($_staffGroupIDList as $key => $val) {
            self::Assign($_SWIFT_DepartmentObject, $val);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Insert a new Staff Group <> Department Assignment Record
     *
     * @author Varun Shoor
     * @param SWIFT_StaffGroup $_SWIFT_StaffGroupObject The StaffGroup Object
     * @param array $_departmentIDList The Department ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function AssignStaffGroupList($_SWIFT_StaffGroupObject, $_departmentIDList)
    {
        if (!$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup || !$_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!count($_departmentIDList)) {
            self::DeleteList($_SWIFT_StaffGroupObject);

            self::RebuildCache();

            return true;
        }

        // First we iterate through all departments and unassign any that are not in list
        $_unassignDepartmentIDList = array();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        foreach ($_departmentCache as $_key => $_val) {
            if (!in_array($_val['departmentid'], $_departmentIDList)) {
                $_unassignDepartmentIDList[] = $_val['departmentid'];
            }
        }

        // Are there any entries?
        if (count($_unassignDepartmentIDList)) {
            self::DeleteList($_SWIFT_StaffGroupObject, $_unassignDepartmentIDList);
        }

        if (!count($_departmentIDList)) {
            return true;
        }

        // Assign the staff users to this department
        foreach ($_departmentIDList as $_key => $_val) {
            self::Assign($_SWIFT_StaffGroupObject, $_val);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Assign the Staff Group/Team to Given Department
     *
     * @author Varun Shoor
     * @param SWIFT_Model $_SWIFT_LinkObject The Department/Staff Group Object
     * @param int|array $_typeID The Staff Group/Department ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function Assign($_SWIFT_LinkObject, $_typeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((!$_SWIFT_LinkObject instanceof SWIFT_Department || !$_SWIFT_LinkObject->GetIsClassLoaded()) && (!$_SWIFT_LinkObject instanceof SWIFT_StaffGroup || !$_SWIFT_LinkObject->GetIsClassLoaded())) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_LinkObject instanceof SWIFT_Department) {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'groupassigns', array('departmentid' => $_SWIFT_LinkObject->GetDepartmentID(), 'staffgroupid' => $_typeID), array('departmentid', 'staffgroupid'));

            // Assign to parent department if it exists
            $_SWIFT_ParentDepartmentObject = $_SWIFT_LinkObject->GetParentDepartmentObject();

            // We return true even if the object was not loaded because parentdepartmentid is optional field
            if (!$_SWIFT_ParentDepartmentObject instanceof SWIFT_Department || !$_SWIFT_ParentDepartmentObject->GetIsClassLoaded()) {
                return true;
            }

            $_SWIFT->Database->Replace(TABLE_PREFIX . 'groupassigns', array('departmentid' => $_SWIFT_ParentDepartmentObject->GetDepartmentID(), 'staffgroupid' => $_typeID), array('departmentid', 'staffgroupid'));
        } else if ($_SWIFT_LinkObject instanceof SWIFT_StaffGroup) {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'groupassigns', array('staffgroupid' => $_SWIFT_LinkObject->GetStaffGroupID(), 'departmentid' => $_typeID), array('staffgroupid', 'departmentid'));
        }

        return true;
    }
}

?>
