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

namespace Base\Models\CustomField;

use SWIFT;
use Base\Library\CustomField\SWIFT_CustomField_Exception;
use Base\Models\CustomField\SWIFT_CustomFieldGroupDepartmentLink;
use Base\Models\CustomField\SWIFT_CustomFieldGroupPermission;
use Base\Models\CustomField\SWIFT_CustomFieldLink;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Custom Field Group Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldGroup extends SWIFT_Model
{
    const TABLE_NAME = 'customfieldgroups';
    const PRIMARY_KEY = 'customfieldgroupid';

    const TABLE_STRUCTURE = "customfieldgroupid I PRIMARY AUTO NOTNULL,
                                title C(200) DEFAULT '' NOTNULL,
                                grouptype I2 DEFAULT '0' NOTNULL,
                                visibilitytype I2 DEFAULT '1' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'grouptype';


    protected $_dataStore = array();

    // Core Constants
    const GROUP_USER = 1; // User Registration
    const GROUP_USERORGANIZATION = 2; // User Organization
    const GROUP_LIVECHATPRE = 10; // Live Chat (Pre Chat)
    const GROUP_LIVECHATPOST = 11; // Live Chat (Post Chat)
    const GROUP_STAFFTICKET = 3; // Staff Ticket Creation
    const GROUP_USERTICKET = 4; // User Ticket Creation
    const GROUP_STAFFUSERTICKET = 9; // Staff & User Ticket Creation
    const GROUP_TIMETRACK = 5; // Ticket Time Tracking
    const GROUP_KNOWLEDGEBASE = 12; // Knowledgebase Articles
    const GROUP_NEWS = 13; // News Items
    const GROUP_TROUBLESHOOTER = 14; // Troubleshooter Items

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Record could not be loaded
     */
    public function __construct($_customFieldGroupID)
    {
        parent::__construct();

        if (!$this->LoadData($_customFieldGroupID)) {
            throw new SWIFT_CustomField_Exception('Failed to load Custom Field Group ID: ' . $_customFieldGroupID);
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldgroups', $this->GetUpdatePool(), 'UPDATE', "customfieldgroupid = '" . (int)($this->GetCustomFieldGroupID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field Group ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldgroupid" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetCustomFieldGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldgroupid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_customFieldGroupID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups WHERE customfieldgroupid = '" . $_customFieldGroupID . "'");
        if (isset($_dataStore['customfieldgroupid']) && !empty($_dataStore['customfieldgroupid'])) {
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
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Custom Field Group Type
     *
     * @author Varun Shoor
     * @param mixed $_groupType The Custom Field Group Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidGroupType($_groupType)
    {
        if ($_groupType == self::GROUP_USER || $_groupType == self::GROUP_USERORGANIZATION || $_groupType == self::GROUP_LIVECHATPRE || $_groupType == self::GROUP_LIVECHATPOST || $_groupType == self::GROUP_STAFFTICKET || $_groupType == self::GROUP_USERTICKET || $_groupType == self::GROUP_STAFFUSERTICKET || $_groupType == self::GROUP_TIMETRACK || $_groupType == self::GROUP_KNOWLEDGEBASE || $_groupType == self::GROUP_NEWS || $_groupType == self::GROUP_TROUBLESHOOTER) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Custom Field Group Label Text
     *
     * @author Varun Shoor
     * @param mixed $_groupType The Group Type
     * @return string The Label Text on success, "" otherwise
     */
    public static function GetGroupLabel($_groupType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidGroupType($_groupType)) {
            return '';
        }

        switch ($_groupType) {
            case self::GROUP_USER:
                return $_SWIFT->Language->Get('grouptypeuser');

                break;

            case self::GROUP_USERORGANIZATION:
                return $_SWIFT->Language->Get('grouptypeuserorganization');

                break;
            case self::GROUP_STAFFTICKET:
                return $_SWIFT->Language->Get('grouptypestaffticket');

                break;
            case self::GROUP_USERTICKET:
                return $_SWIFT->Language->Get('grouptypeuserticket');

                break;
            case self::GROUP_STAFFUSERTICKET:
                return $_SWIFT->Language->Get('grouptypestaffuserticket');

                break;
            case self::GROUP_TIMETRACK:
                return $_SWIFT->Language->Get('grouptypetimetrack');

                break;

            case self::GROUP_LIVECHATPRE:
                return $_SWIFT->Language->Get('grouptypelivesupportpre');

                break;

            case self::GROUP_LIVECHATPOST:
                return $_SWIFT->Language->Get('grouptypelivesupportpost');

                break;

            case self::GROUP_KNOWLEDGEBASE:
                return $_SWIFT->Language->Get('grouptypeknowledgebase');

                break;

            case self::GROUP_NEWS:
                return $_SWIFT->Language->Get('grouptypenews');

                break;

            case self::GROUP_TROUBLESHOOTER:
                return $_SWIFT->Language->Get('grouptypetroubleshooter');

                break;

            default:
                break;
        }

        return '';
    }

    /**
     * Get the List of Groups Based on a App
     *
     * @author Varun Shoor
     * @param mixed $_appName The App Name
     * @return array The Group List Container Array
     */
    public static function GetGroupListOnApp($_appName)
    {
        $_appContainer = array();

        $_appContainer[APP_CORE][] = self::GROUP_USER;
        $_appContainer[APP_CORE][] = self::GROUP_USERORGANIZATION;

        $_appContainer[APP_LIVECHAT][] = self::GROUP_LIVECHATPRE;
        $_appContainer[APP_LIVECHAT][] = self::GROUP_LIVECHATPOST;


        $_appContainer[APP_TICKETS][] = self::GROUP_STAFFTICKET;
        $_appContainer[APP_TICKETS][] = self::GROUP_USERTICKET;
        $_appContainer[APP_TICKETS][] = self::GROUP_STAFFUSERTICKET;
        $_appContainer[APP_TICKETS][] = self::GROUP_TIMETRACK;

        if (!isset($_appContainer[$_appName])) {
            return array();
        }

        return $_appContainer[$_appName];
    }

    /**
     * Create a new Custom Field Group
     *
     * @author Varun Shoor
     * @param string $_groupTitle The Custom Field Group Title
     * @param mixed $_groupType The Custom Field Group Type
     * @param int $_displayOrder The Custom Field Group Display Order
     * @param array $_departmentIDList The Department ID's this group is associated to
     * @param array $_staffGroupPermissionContainer The Staff Group Permission Container
     * @param array $_staffPermissionContainer The Staff Permission Constainer
     * @param mixed $_visibilityType (OPTIONAL) The Group Visibility Type
     * @return int
     * @throws SWIFT_CustomField_Exception If Invalid Data is Provided or If the Object could not be created
     * @throws SWIFT_Exception
     */
    public static function Create($_groupTitle, $_groupType, $_displayOrder, $_departmentIDList, $_staffGroupPermissionContainer, $_staffPermissionContainer, $_visibilityType = SWIFT_PUBLICINT)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_groupTitle) || !self::IsValidGroupType($_groupType)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldgroups', array('title' => $_groupTitle, 'grouptype' => (int)($_groupType), 'displayorder' => $_displayOrder,
            'visibilitytype' => (int)($_visibilityType)), 'INSERT');
        $_customFieldGroupID = $_SWIFT->Database->Insert_ID();
        if (!$_customFieldGroupID) {
            throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_CustomFieldGroupObject = new SWIFT_CustomFieldGroup($_customFieldGroupID);
        if (!$_SWIFT_CustomFieldGroupObject instanceof SWIFT_CustomFieldGroup || !$_SWIFT_CustomFieldGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_departmentIDList)) {
            $_SWIFT_CustomFieldGroupDepartmentLinkObject = new SWIFT_CustomFieldGroupDepartmentLink($_SWIFT_CustomFieldGroupObject);
            if ($_SWIFT_CustomFieldGroupDepartmentLinkObject instanceof SWIFT_CustomFieldGroupDepartmentLink && $_SWIFT_CustomFieldGroupDepartmentLinkObject->GetIsClassLoaded()) {
                $_SWIFT_CustomFieldGroupDepartmentLinkObject->LinkList($_departmentIDList);
            }
        }

        $_SWIFT_CustomFieldGroupObject->ProcessPermissions($_staffGroupPermissionContainer, $_staffPermissionContainer);

        SWIFT_CustomFieldGroupPermission::RebuildCache();

        SWIFT_CustomFieldManager::RebuildCache();

        return $_customFieldGroupID;
    }

    /**
     * Update the Custom Field Group Record
     *
     * @author Varun Shoor
     * @param string $_groupTitle The Custom Field Group Title
     * @param int $_displayOrder The Custom Field Group Display Order
     * @param array $_departmentIDList The Department ID's this group is associated to
     * @param array $_staffGroupPermissionContainer The Staff Group Permission Container
     * @param array $_staffPermissionContainer The Staff Permission Constainer
     * @param mixed $_visibilityType (OPTIONAL) The Group Visibility Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function Update($_groupTitle, $_displayOrder, $_departmentIDList, $_staffGroupPermissionContainer, $_staffPermissionContainer, $_visibilityType = SWIFT_PUBLICINT)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupTitle)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_groupTitle);
        $this->UpdatePool('visibilitytype', (int)($_visibilityType));
        $this->UpdatePool('displayorder', $_displayOrder);

        $this->ProcessUpdatePool();

        SWIFT_CustomFieldGroupDepartmentLink::DeleteOnCustomFieldGroup(array($this->GetCustomFieldGroupID()));

        if (_is_array($_departmentIDList)) {
            $_SWIFT_CustomFieldGroupDepartmentLinkObject = new SWIFT_CustomFieldGroupDepartmentLink($this);
            if ($_SWIFT_CustomFieldGroupDepartmentLinkObject instanceof SWIFT_CustomFieldGroupDepartmentLink && $_SWIFT_CustomFieldGroupDepartmentLinkObject->GetIsClassLoaded()) {
                $_SWIFT_CustomFieldGroupDepartmentLinkObject->LinkList($_departmentIDList);
            }
        }

        $this->ProcessPermissions($_staffGroupPermissionContainer, $_staffPermissionContainer);

        SWIFT_CustomFieldGroupPermission::RebuildCache();

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }

    /**
     * Process the Permission Container Values
     *
     * @author Varun Shoor
     * @param array $_staffGroupPermissionContainer The Staff Group Permission Container
     * @param array $_staffPermissionContainer The Staff Permission Constainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    protected function ProcessPermissions($_staffGroupPermissionContainer, $_staffPermissionContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_CustomFieldGroupPermission::DeleteOnCustomFieldGroup(array($this->GetCustomFieldGroupID()));

        if (_is_array($_staffGroupPermissionContainer)) {

            foreach ($_staffGroupPermissionContainer as $_key => $_val) {
                $_val = (int)($_val);

                if ($_val == 1) {
                    $_accessMask = SWIFT_CustomFieldGroupPermission::ACCESS_YES;
                } else {
                    $_accessMask = SWIFT_CustomFieldGroupPermission::ACCESS_NO;
                }

                SWIFT_CustomFieldGroupPermission::Create($this->GetCustomFieldGroupID(), (int)($_key), SWIFT_CustomFieldGroupPermission::TYPE_STAFFGROUP, $_accessMask, false);
            }
        }

        if (_is_array($_staffPermissionContainer)) {

            foreach ($_staffPermissionContainer as $_key => $_val) {
                $_val = (int)($_val);

                if ($_val == -1) {
                    $_accessMask = SWIFT_CustomFieldGroupPermission::ACCESS_NOTSET;
                } else if ($_val == 1) {
                    $_accessMask = SWIFT_CustomFieldGroupPermission::ACCESS_YES;
                } else {
                    $_accessMask = SWIFT_CustomFieldGroupPermission::ACCESS_NO;
                }

                SWIFT_CustomFieldGroupPermission::Create($this->GetCustomFieldGroupID(), (int)($_key), SWIFT_CustomFieldGroupPermission::TYPE_STAFF, $_accessMask, false);
            }
        }

        return true;
    }

    /**
     * Delete the Custom Field Group record
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

        self::DeleteList(array($this->GetCustomFieldGroupID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Field Groups
     *
     * @author Varun Shoor
     * @param array $_customFieldGroupIDList The Custom Field Group ID Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldGroupIDList)) {
            return false;
        }

        $_finalCustomFieldGroupIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . $_SWIFT->Database->Record['title'] . ' (' . self::GetGroupLabel($_SWIFT->Database->Record['grouptype']) . ')<br />';

            $_finalCustomFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];

            $_index++;
        }

        if (!count($_finalCustomFieldGroupIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelcfgroup'), count($_finalCustomFieldGroupIDList)), sprintf($_SWIFT->Language->Get('msgdelcfgroup'), $_finalText));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldgroups WHERE customfieldgroupid IN (" . BuildIN($_finalCustomFieldGroupIDList) . ")");

        SWIFT_CustomField::DeleteOnCustomFieldGroup($_finalCustomFieldGroupIDList);
        SWIFT_CustomFieldGroupDepartmentLink::DeleteOnCustomFieldGroup($_finalCustomFieldGroupIDList);
        SWIFT_CustomFieldGroupPermission::DeleteOnCustomFieldGroup($_finalCustomFieldGroupIDList);
        SWIFT_CustomFieldLink::DeleteOnCustomFieldGroup($_finalCustomFieldGroupIDList);

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }

    /**
     * Check to see if its a valid custom field group id
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCustomFieldGroupID($_customFieldGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldGroupID)) {
            return false;
        }

        $_customFieldContainer = $_SWIFT->Database->QueryFetch("SELECT customfieldgroupid FROM " . TABLE_PREFIX . "customfieldgroups WHERE customfieldgroupid = '" . $_customFieldGroupID . "'");
        if (isset($_customFieldContainer['customfieldgroupid']) && !empty($_customFieldContainer['customfieldgroupid'])) {
            return true;
        }

        return false;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_customFieldGroupIDSortList The Custom Field Group ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_customFieldGroupIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldGroupIDSortList)) {
            return false;
        }

        foreach ($_customFieldGroupIDSortList as $_customFieldGroupID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldgroups', array('displayorder' => $_displayOrder), 'UPDATE',
                "customfieldgroupid = '" . $_customFieldGroupID . "'");
        }

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }
}

?>
