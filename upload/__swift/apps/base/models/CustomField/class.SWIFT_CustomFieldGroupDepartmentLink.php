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

namespace Base\Models\CustomField;

use SWIFT;
use Base\Library\CustomField\SWIFT_CustomField_Exception;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Custom Field Group <> Department Link Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldGroupDepartmentLink extends SWIFT_Model
{
    const TABLE_NAME = 'customfielddeplinks';
    const PRIMARY_KEY = 'customfielddeplinkid';

    const TABLE_STRUCTURE = "customfielddeplinkid I PRIMARY AUTO NOTNULL,
                                customfieldgroupid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'customfieldgroupid, departmentid';
    const INDEXTYPE_1 = 'UNIQUE';

    private $_SWIFT_CustomFieldGroupObject = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_CustomFieldGroup $_SWIFT_CustomFieldGroupObject The Custom Field Group Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_CustomFieldGroup $_SWIFT_CustomFieldGroupObject)
    {
        parent::__construct();

        $this->_SWIFT_CustomFieldGroupObject = $_SWIFT_CustomFieldGroupObject;
    }

    /**
     * Link the Custom Field Group with the given Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID to link with
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function Link($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_groupType = $this->_SWIFT_CustomFieldGroupObject->GetProperty('grouptype');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        if (!SWIFT_CustomFieldGroup::IsValidGroupType($_groupType) || !isset($_departmentCache[$_departmentID]) || empty($_departmentID)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        if ((($_groupType == SWIFT_CustomFieldGroup::GROUP_USERTICKET || $_groupType == SWIFT_CustomFieldGroup::GROUP_STAFFTICKET || $_groupType == SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET) && $_departmentCache[$_departmentID]['departmentapp'] == APP_TICKETS) || (($_groupType == SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE || $_groupType == SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST) && $_departmentCache[$_departmentID]['departmentapp'] == APP_LIVECHAT)) {
            $_SWIFT->Database->Replace(TABLE_PREFIX . 'customfielddeplinks', array('customfieldgroupid' => (int)($this->_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()), 'departmentid' => $_departmentID), array('customfieldgroupid', 'departmentid'));
        } else {
            return false;
        }

        return true;
    }

    /**
     * Link the Custom Field Group with a list of Department ID's
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function LinkList($_departmentIDList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_departmentIDList)) {
            return false;
        }

        foreach ($_departmentIDList as $_key => $_val) {
            $this->Link($_val);
        }

        return true;
    }

    /**
     * Retrieve the Department List on the basis of a Custom Field Group ID
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return mixed "_departmentIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function GetDepartmentListOnCustomFieldGroup($_customFieldGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldGroupID)) {
            return array();
        }

        $_departmentIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfielddeplinks WHERE customfieldgroupid = '" . $_customFieldGroupID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_departmentIDList[] = $_SWIFT->Database->Record['departmentid'];
        }

        return $_departmentIDList;
    }

    /**
     * Delete the Links based on Custom Field Group
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

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfielddeplinks WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");

        return true;
    }

    /**
     * Delete the Links based on Department ID
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnDepartment($_departmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_departmentIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfielddeplinks WHERE departmentid IN (" . BuildIN($_departmentIDList) . ")");

        return true;
    }
}

?>
