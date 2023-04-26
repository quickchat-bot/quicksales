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

namespace Base\Models\Template;

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_Language_Exception;
use SWIFT_Model;
use Base\Library\Template\SWIFT_Template_Exception;
use Base\Models\User\SWIFT_UserGroup;

/**
 * The Template Group Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TemplateGroup extends SWIFT_Model
{
    const TABLE_NAME = 'templategroups';
    const PRIMARY_KEY = 'tgroupid';

    const TABLE_STRUCTURE = "tgroupid I PRIMARY AUTO NOTNULL,
                                languageid I DEFAULT '0' NOTNULL,
                                isenabled I2 DEFAULT '0' NOTNULL,
                                guestusergroupid I DEFAULT '0' NOTNULL,
                                regusergroupid I DEFAULT '0' NOTNULL,
                                title C(155) DEFAULT '' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL,
                                companyname C(255) DEFAULT '' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL,
                                enablepassword I2 DEFAULT '0' NOTNULL,
                                groupusername C(100) DEFAULT '' NOTNULL,
                                grouppassword C(32) DEFAULT '' NOTNULL,
                                restrictgroups I2 DEFAULT '0' NOTNULL,
                                isdefault I2 DEFAULT '0' NOTNULL,
                                useloginshare I2 DEFAULT '0' NOTNULL,
                                loginapi_appid I2 DEFAULT '0' NOTNULL,
                                ticketstatusid I DEFAULT '0' NOTNULL,
                                priorityid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL,
                                departmentid_livechat I DEFAULT '0' NOTNULL,
                                tickets_prompttype I2 DEFAULT '0' NOTNULL,
                                tickets_promptpriority I2 DEFAULT '0' NOTNULL";


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Record could not be loaded
     */
    public function __construct($_templateGroupID)
    {
        parent::__construct();

        if (!$this->LoadData($_templateGroupID)) {
            throw new SWIFT_Template_Exception('Failed to load Template Group ID: ' . $_templateGroupID);
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
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', $this->GetUpdatePool(), 'UPDATE', "tgroupid = '" . (int)($this->GetTemplateGroupID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Template Group ID
     *
     * @author Varun Shoor
     * @return mixed "tgroupid" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetTemplateGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['tgroupid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_templateGroupID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid = '" . $_templateGroupID . "'");
        if (isset($_dataStore['tgroupid']) && !empty($_dataStore['tgroupid'])) {
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
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Unsets the isdefault property for all template groups
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UnsetDefaultGroup()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('isdefault' => '0'), 'UPDATE', '1 = 1');

        return true;
    }

    /**
     * Create a new Template Group
     *
     * @author Varun Shoor
     * @param string $_groupTitle The Template Group Title
     * @param string $_groupDescription The Template Group Description
     * @param string $_groupCompanyName The Template Group Company Name
     * @param bool $_enablePassword Whether to Enable Password Protection
     * @param string $_groupUserName The Group Username (If Password Protection is On)
     * @param string $_groupPassword The Group Password (If Password Protection is On)
     * @param int $_languageID The Default Language ID
     * @param bool $_isDefault Whether this Template Group is default
     * @param bool $_restrictUsers Whether only the users from the specified groups can login to this template group
     * @param int $_guestUserGroupID The Guest User Group ID
     * @param int $_registeredUserGroupID The Registered User Group ID
     * @param bool $_useLoginShare Whether to use LoginShare for Authentication
     * @param int $_copyFromTemplateGroupID (OPTIONAL) Specifiy the Group to Copy the Templates from
     * @param int $_departmentID The Default Department
     * @param int $_ticketStatusID The Default Ticket Status
     * @param int $_ticketPriorityID The Default Ticket Priority
     * @param int $_ticketTypeID The Default Ticket Type
     * @param bool $_ticketPromptType Whether to prompt for ticket type
     * @param bool $_ticketPromptPriority Whether to prompt for priority
     * @param int $_departmentID_LiveChat The default live chat department id
     * @param bool $_isEnabled Whether this Template Group is Enabled
     * @param bool $_isMaster Whether this is a Master Template Group
     * @return mixed "SWIFT_TemplateGroup" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_groupTitle, $_groupDescription, $_groupCompanyName, $_enablePassword, $_groupUserName, $_groupPassword,
                                  $_languageID, $_isDefault, $_restrictUsers, $_guestUserGroupID, $_registeredUserGroupID, $_useLoginShare, $_copyFromTemplateGroupID = 0,
                                  $_departmentID = 0, $_ticketStatusID = 0, $_ticketPriorityID = 0, $_ticketTypeID = 0, $_ticketPromptType = false,
                                  $_ticketPromptPriority = false, $_departmentID_LiveChat = 0, $_isEnabled = true, $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_groupTitle) || empty($_languageID) || empty($_guestUserGroupID) || empty($_registeredUserGroupID) ||
            !SWIFT_UserGroup::IsValidUserGroupID($_guestUserGroupID) || !SWIFT_UserGroup::IsValidUserGroupID($_registeredUserGroupID)) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        // If we are setting this group as default then unset the default status for all other groups
        if ($_isDefault == true) {
            self::UnsetDefaultGroup();
        }

        $_groupTitle = Clean($_groupTitle);

        $_finalGroupPassword = $_finalGroupUserName = '';
        if ($_enablePassword && !empty($_groupPassword) && !empty($_groupUserName)) {
            $_finalGroupPassword = $_groupPassword;
            $_finalGroupUserName = $_groupUserName;
        } else {
            $_enablePassword = false;
        }

        if (!empty($_copyFromTemplateGroupID)) {
            self::IsValidTemplateGroupID($_copyFromTemplateGroupID);
        }

        // Insert the new template group
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('languageid' => $_languageID,
            'guestusergroupid' => $_guestUserGroupID, 'regusergroupid' => $_registeredUserGroupID,
            'title' => $_groupTitle, 'description' => $_groupDescription, 'companyname' => $_groupCompanyName,
            'ismaster' => (int)($_isMaster), 'enablepassword' => (int)($_enablePassword), 'groupusername' => $_finalGroupUserName,
            'grouppassword' => $_finalGroupPassword, 'restrictgroups' => (int)($_restrictUsers), 'isdefault' => (int)($_isDefault),
            'useloginshare' => (int)($_useLoginShare), 'loginapi_appid' => (int)($_useLoginShare),
            'ticketstatusid' => $_ticketStatusID, 'priorityid' => $_ticketPriorityID, 'departmentid' => $_departmentID,
            'isenabled' => (int)($_isEnabled), 'tickets_prompttype' => (int)($_ticketPromptType),
            'tickets_promptpriority' => (int)($_ticketPromptPriority), 'departmentid_livechat' => $_departmentID_LiveChat,
            'tickettypeid' => $_ticketTypeID), 'INSERT');
        $_templateGroupID = $_SWIFT->Database->Insert_ID();
        if (!$_templateGroupID) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        // Now that the group is created, copy all templates from the copy group to this one.
        if (!empty($_copyFromTemplateGroupID)) {
            $_copyResult = $_SWIFT_TemplateGroupObject->Copy($_copyFromTemplateGroupID);
            if (!$_copyResult) {
                throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
            }
        }

        // Rebuild the template group cache
        self::RebuildCache();

        return $_SWIFT_TemplateGroupObject;
    }

    /**
     * Update the Template Group Record
     *
     * @author Varun Shoor
     * @param string $_groupTitle The Template Group Title
     * @param string $_groupDescription The Template Group Description
     * @param string $_groupCompanyName The Template Group Company Name
     * @param bool $_enablePassword Whether to Enable Password Protection
     * @param string $_groupUserName The Group Username (If Password Protection is On)
     * @param string $_groupPassword The Group Password (If Password Protection is On)
     * @param int $_languageID The Default Language ID
     * @param bool $_isDefault Whether this Template Group is default
     * @param bool $_restrictUsers Whether only the users from the specified groups can login to this template group
     * @param int $_guestUserGroupID The Guest User Group ID
     * @param int $_registeredUserGroupID The Registered User Group ID
     * @param bool $_useLoginShare Whether to use LoginShare for Authentication
     * @param int $_departmentID The Default Department
     * @param int $_ticketStatusID The Default Ticket Status
     * @param int $_ticketPriorityID The Default Ticket Priority
     * @param int $_ticketTypeID The Default Ticket Type
     * @param bool $_ticketPromptType Whether to prompt for ticket type
     * @param bool $_ticketPromptPriority Whether to prompt for priority
     * @param int $_departmentID_LiveChat The default live chat department id
     * @param bool $_isEnabled Whether this Template Group is Enabled
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_groupTitle, $_groupDescription, $_groupCompanyName, $_enablePassword, $_groupUserName, $_groupPassword, $_languageID,
                           $_isDefault, $_restrictUsers, $_guestUserGroupID, $_registeredUserGroupID, $_useLoginShare, $_departmentID = 0, $_ticketStatusID = 0,
                           $_ticketPriorityID = 0, $_ticketTypeID = 0, $_ticketPromptType = false, $_ticketPromptPriority = false, $_departmentID_LiveChat = 0,
                           $_isEnabled = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_groupTitle) || empty($_languageID) || empty($_guestUserGroupID) || empty($_registeredUserGroupID) ||
            !SWIFT_UserGroup::IsValidUserGroupID($_guestUserGroupID) || !SWIFT_UserGroup::IsValidUserGroupID($_registeredUserGroupID)) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        // If we are setting this group as default then unset the default status for all other groups
        if ($_isDefault == true) {
            self::UnsetDefaultGroup();
        }

        $_groupTitle = Clean($_groupTitle);

        $_finalGroupPassword = $_finalGroupUserName = '';
        if ($_enablePassword && !empty($_groupPassword) && !empty($_groupUserName)) {
            $_finalGroupPassword = $_groupPassword;
            $_finalGroupUserName = $_groupUserName;
            $this->UpdatePool('grouppassword', $_finalGroupPassword);
        } else if ($_enablePassword && (trim($this->GetProperty('grouppassword')) == '' || trim($this->GetProperty('groupusername')) == '')) {
            $_enablePassword = false;
        } else if (!$_enablePassword) {
            $this->UpdatePool('groupusername', '');
            $this->UpdatePool('grouppassword', '');
        } else if ($_enablePassword == true && !empty($_groupUserName)) {
            $_finalGroupUserName = $_groupUserName;
        }

        $this->UpdatePool('groupusername', $_finalGroupUserName);
        $this->UpdatePool('languageid', $_languageID);
        $this->UpdatePool('guestusergroupid', $_guestUserGroupID);
        $this->UpdatePool('regusergroupid', $_registeredUserGroupID);
        $this->UpdatePool('title', $_groupTitle);
        $this->UpdatePool('description', $_groupDescription);
        $this->UpdatePool('companyname', $_groupCompanyName);
        $this->UpdatePool('enablepassword', (int)($_enablePassword));
        $this->UpdatePool('restrictgroups', (int)($_restrictUsers));
        $this->UpdatePool('isdefault', (int)($_isDefault));
        $this->UpdatePool('useloginshare', (int)($_useLoginShare));
        $this->UpdatePool('useloginshare', (int)($_useLoginShare));
        $this->UpdatePool('ticketstatusid', $_ticketStatusID);
        $this->UpdatePool('priorityid', $_ticketPriorityID);
        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('isenabled', (int)($_isEnabled));
        $this->UpdatePool('tickets_promptpriority', (int)($_ticketPromptPriority));
        $this->UpdatePool('tickets_prompttype', (int)($_ticketPromptType));
        $this->UpdatePool('departmentid_livechat', $_departmentID_LiveChat);

        $this->ProcessUpdatePool();

        // Rebuild the template group cache
        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve all the Template IDs under this Group
     *
     * @author Varun Shoor
     * @return mixed "_templateIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetTemplateIDList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_Template::GetTemplateIDListFromTemplateGroup($this->GetTemplateGroupID());
    }

    /**
     * Restore all Templates under this group to their default counter parts
     *
     * @author Varun Shoor
     * @param int $_staffID (OPTIONAL) The Staff ID of the Staff Making the Change
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Restore($_staffID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        // First get the template ids..
        $_templateIDList = $this->GetTemplateIDList();
        if (empty($_templateIDList)) {
            return false;
        }

        foreach ($_templateIDList as $_key => $_val) {
            $_SWIFT_TemplateObject = new SWIFT_Template($_val);
            if ($_SWIFT_TemplateObject instanceof SWIFT_Template && $_SWIFT_TemplateObject->GetIsClassLoaded()) {
                $_SWIFT_TemplateObject->Restore(true, $_staffID);
            }
        }

        return true;
    }

    /**
     * Restore a List of Template Groups
     *
     * @author Varun Shoor
     * @param array $_templateGroupIDList The Template Group ID List
     * @param int $_staffID (OPTIONAL) The Staff ID of the Staff Making the Change
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RestoreList($_templateGroupIDList, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateGroupIDList)) {
            return false;
        }

        $_finalTemplateGroupIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid IN (" . BuildIN($_templateGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTemplateGroupIDList[] = $_SWIFT->Database->Record['tgroupid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['companyname']) . ')<br />';

            $_index++;
        }

        if (!count($_finalTemplateGroupIDList)) {
            return false;
        }

        foreach ($_finalTemplateGroupIDList as $_key => $_val) {
            $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_val);
            if ($_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup && $_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
                $_SWIFT_TemplateGroupObject->Restore($_staffID);
            }
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titletgrouprestore'), count($_finalTemplateGroupIDList)), $_SWIFT->Language->Get('msgtgrouprestore') . '<br />' . $_finalText);

        return true;
    }

    /**
     * Copy the Templates from another template group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Group to Copy Templates From
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Copy($_templateGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_templateGroupID)) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        $_templateContainer = $_SWIFT_TemplateGroupObject->GetTemplateData();
        if (!_is_array($_templateContainer)) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        $_templateCategoryContainer = $_SWIFT_TemplateGroupObject->GetCategoryData();
        if (!_is_array($_templateCategoryContainer)) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        $_templateCategoryIDList = $_templateCategoryMap = array();
        foreach ($_templateCategoryContainer as $_key => $_val) {

            if (!SWIFT_App::IsInstalled($_val['app'])) {
                continue;
            }

            $_templateCategoryID = SWIFT_TemplateCategory::Create($this->GetTemplateGroupID(), $_val['name'], $_val['app'], $_val['description'], $_val['icon']);
            if (!$_templateCategoryID) {
                throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
            }

            $_templateCategoryMap[$_val['tcategoryid']] = $_templateCategoryID;
            $_templateCategoryIDList[] = $_val['tcategoryid'];
        }

        foreach ($_templateContainer as $_key => $_val) {
            if (!in_array($_val['tcategoryid'], $_templateCategoryIDList)) {
                unset($_templateContainer[$_key]);
            }
        }

        foreach ($_templateContainer as $_key => $_val) {
            if (!isset($_templateCategoryMap[$_val['tcategoryid']])) {
                continue;
            }

            $_templateID = SWIFT_Template::Create($this->GetTemplateGroupID(), $_templateCategoryMap[$_val['tcategoryid']], $_val['name'], $_val['contents'], $_val['contentsdefault'], 0);
            if (!$_templateID) {
                throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
            }
        }

        return true;
    }

    /**
     * Retrieve the Templates as an Array
     *
     * @author Varun Shoor
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    protected function GetTemplateData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_Template::GetTemplateData($this->GetTemplateGroupID());
    }

    /**
     * Retrieve the Template Categories as an Array
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    protected function GetCategoryData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_TemplateCategory::GetCategoryData($this->GetTemplateGroupID());
    }

    /**
     * Delete the Template Group record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTemplateGroupID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Update the Company Name for this Group
     *
     * @author Varun Shoor
     * @param string $_companyName The New Company Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateCompanyName($_companyName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('companyname', $_companyName);
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Reset the Default Group to Master Group
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ResetDefaultGroupToMaster()
    {
        $_SWIFT = SWIFT::GetInstance();

        self::UnsetDefaultGroup();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('isdefault' => '1'), 'UPDATE', "ismaster = '1'");

        return true;
    }

    /**
     * Delete a list of Template Group IDs
     *
     * @author Varun Shoor
     * @param array $_templateGroupIDList The Template Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_templateGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateGroupIDList)) {
            return false;
        }

        $_resetDefaultGroup = false;
        $_finalTemplateGroupIDList = array();

        $_index = $_masterIndex = 1;

        $_finalText = $_finalMasterText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid IN (" . BuildIN($_templateGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['isdefault'] == 1) {
                $_resetDefaultGroup = true;
            }

            // Check to see if we are deleting a master group
            if ($_SWIFT->Database->Record['ismaster'] == '1') {
                // woops!.. no go.. nuke that value
                $_finalMasterText .= $_masterIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['companyname']) . ')<br />';
                $_masterIndex++;
            } else {
                $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['companyname']) . ')<br />';

                $_finalTemplateGroupIDList[] = $_SWIFT->Database->Record['tgroupid'];

                $_index++;
            }
        }

        if (!empty($_finalMasterText)) {
            SWIFT::Error($_SWIFT->Language->Get('titletgroupnodel'), $_SWIFT->Language->Get('msgtgroupnodel') . '<br />' . $_finalMasterText);
        }

        if (!count($_finalTemplateGroupIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titletgroupdel'), count($_finalTemplateGroupIDList)), $_SWIFT->Language->Get('msgtgroupdel') . '<br />' . $_finalText);

        SWIFT_TemplateCategory::DeleteOnTemplateGroup($_finalTemplateGroupIDList);
        SWIFT_Template::DeleteOnTemplateGroup($_finalTemplateGroupIDList);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid IN (" . BuildIN($_finalTemplateGroupIDList) . ")");

        // Reset the default group to a master group
        if ($_resetDefaultGroup == true) {
            self::ResetDefaultGroupToMaster();
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Check to see if its a valid template group id
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID to Check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Template Group ID is Invalid
     */
    public static function IsValidTemplateGroupID($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateGroupID)) {
            return false;
        }

        $_templateGroupContainer = $_SWIFT->Database->QueryFetch("SELECT tgroupid FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid = '" . $_templateGroupID . "'");
        if (!$_templateGroupContainer || !isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid'])) {

            return false;
        }

        return true;
    }

    /**
     * Rebuild the Template Group Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['tgroupid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('templategroupcache', $_cache);

        return true;
    }

    /**
     * Retrieve the Master Group ID
     *
     * @author Varun Shoor
     * @return mixed "tgroupid" (INT) on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Master Template Group record could not be located
     */
    public static function GetMasterGroupID()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_templateGroupContainer = $_SWIFT->Database->QueryFetch("SELECT tgroupid FROM " . TABLE_PREFIX . "templategroups WHERE ismaster = '1'");
        if (!$_templateGroupContainer || !isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid'])) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        return (int)($_templateGroupContainer['tgroupid']);
    }

    /**
     * Retrieve the Default Template Group ID
     *
     * @author Varun Shoor
     * @return mixed "tgroupid" (INT) on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Master Template Group record could not be located
     */
    public static function GetDefaultGroupID()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_templateGroupContainer = $_SWIFT->Database->QueryFetch("SELECT tgroupid FROM " . TABLE_PREFIX . "templategroups WHERE isdefault = '1'");
        if (!$_templateGroupContainer || !isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid'])) {
            return self::GetMasterGroupID();
        }

        return (int)($_templateGroupContainer['tgroupid']);
    }

    /**
     * Retrieve the registered user group id
     *
     * @author Varun Shoor
     * @return mixed "_userGroupID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetRegisteredUserGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userGroupID = false;

        $_userGroupCache = $this->Cache->Get('usergroupcache');

        if (!isset($_userGroupCache[$this->GetProperty('regusergroupid')])) {
            foreach ($_userGroupCache as $_key => $_val) {
                // Revert to master group
                if ($_val['ismaster'] == '1' && $_val['grouptype'] == SWIFT_UserGroup::TYPE_REGISTERED) {
                    $_userGroupID = (int)($_val['usergroupid']);
                }
            }
        } else {
            $_userGroupID = (int)($this->GetProperty('regusergroupid'));
        }

        if (!$_userGroupID) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_userGroupID;
    }
}

?>
