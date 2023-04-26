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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Custom Field Group Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @property View_CustomFieldGroup $View
 * @author Varun Shoor
 */
class Controller_CustomFieldGroup extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Language:LanguagePhraseLinked', [], true, false, 'base');

        $this->Language->Load('customfields');
    }

    /**
     * Resort the Custom Field Groups
     *
     * @author Varun Shoor
     * @param mixed $_customFieldGroupIDSortList The Custom Field Group ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SortList($_customFieldGroupIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatecfgroup') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_CustomFieldGroup::UpdateDisplayOrderList($_customFieldGroupIDSortList);

        return true;
    }

    /**
     * Delete the Custom Field Group from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_customFieldGroupIDList The Custom Field Group ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldGroupIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletecfgroup') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_customFieldGroupIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "customfieldgroups WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecustomfieldgroup'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_CUSTOMFIELDS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_CustomFieldGroup::DeleteList($_customFieldGroupIDList);
        }

        return true;
    }

    /**
     * Delete the Given Custom Field Group ID
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_customFieldGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_customFieldGroupID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Custom Field Group Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('managegroups'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewcfgroups') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '' || trim($_POST['displayorder']) == '' || trim($_POST['grouptype']) == '') {
            $this->UserInterface->CheckFields('title', 'displayorder', 'grouptype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertcfgroup') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_canupdatecfgroup') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Custom Field Group
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('insertgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertcfgroup') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param SWIFT_CustomFieldGroup $_SWIFT_CustomFieldGroupObject The SWIFT_CustomFieldGroup Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_CustomFieldGroup $_SWIFT_CustomFieldGroupObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_departmentListText = '';

        // Groups that require a department list to be built..
        if ($_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERTICKET || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFTICKET || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST) {
            $_departmentListText = '<br>';

            // Load the links
            $_departmentIDList = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfielddeplinks WHERE customfieldgroupid = '" . (int)($_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()) . "'");
            while ($this->Database->NextRecord()) {
                if (in_array($this->Database->Record['departmentid'], $_departmentIDList)) {
                    continue;
                }

                $_departmentIDList[] = $this->Database->Record['departmentid'];
            }

            $_index = 1;
            foreach ($_departmentIDList as $_departmentID) {
                if (!isset($_departmentCache[$_departmentID])) {
                    continue;
                }

                if ((($_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERTICKET || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFTICKET || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET) && $_departmentCache[$_departmentID]['departmentapp'] == APP_TICKETS) || (($_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST) && $_departmentCache[$_departmentID]['departmentapp'] == APP_LIVECHAT)) {
                    $_departmentListText .= IIF(!empty($_departmentCache[$_departmentID]['parentdepartmentid']), '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ') . $_index . '. ' . $_departmentCache[$_departmentID]['title'] . '<BR />';

                    $_index++;
                }
            }
        }

        SWIFT::Info(sprintf($this->Language->Get($_type . 'cfgrouptitle'), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get($_type . 'cfgroupmsg'), htmlspecialchars($_POST['title']), htmlspecialchars($_POST['title']), htmlspecialchars(SWIFT_CustomFieldGroup::GetGroupLabel($_POST['grouptype'])), htmlspecialchars($_POST['displayorder'])) . $_departmentListText);

        return true;
    }

    /**
     * Retrieves the Department ID List Based on POST data
     *
     * @author Varun Shoor
     * @return mixed "_departmentIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetDepartmentIDList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentIDList = array();

        $_departmentCache = $this->Cache->Get('departmentcache');

        if (!isset($_POST['assigned']) || !_is_array($_POST['assigned'])) {
            return $_departmentIDList;
        }

        foreach ($_POST['assigned'] as $_key => $_val) {
            if (!isset($_departmentCache[$_key])) {
                continue;
            }

            $_departmentContainer = $_departmentCache[$_key];

            $_parentDepartmentContainer = array();
            if (!empty($_departmentContainer['parentdepartmentid'])) {
                $_parentDepartmentContainer = $_departmentCache[$_departmentContainer['parentdepartmentid']];
            }

            $_isAssigned = false;
            if ($_val) {
                $_isAssigned = true;

                // Make sure that its parent is set to yes
                if (!empty($_parentDepartmentContainer['parentdepartmentid'])) {
                    if (!$_POST['assigned'][$_parentDepartmentContainer['parentdepartmentid']]) {
                        $_isAssigned = false;
                    }
                }
            }

            $_groupList = SWIFT_CustomFieldGroup::GetGroupListOnApp($_departmentContainer['departmentapp']);
            if (!in_array($_POST['grouptype'], $_groupList)) {
                $_isAssigned = false;
            }

            if ($_isAssigned && !in_array($_key, $_departmentIDList)) {
                $_departmentIDList[] = (int)($_key);
            }
        }

        return $_departmentIDList;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_visibilityType = SWIFT_PUBLICINT;
            if (isset($_POST['visibilitytype'])) {
                $_visibilityType = (int)($_POST['visibilitytype']);
            }

            $_customFieldGroupID = SWIFT_CustomFieldGroup::Create($_POST['title'], $_POST['grouptype'], $_POST['displayorder'], $this->_GetDepartmentIDList(), $_POST['permstaffgroupid'], $_POST['permstaffid'], $_visibilityType);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertcfgroup'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_CUSTOMFIELDS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_customFieldGroupID) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELDGROUP, $_customFieldGroupID, $_POST['languages']);

            $_CustomFieldGroupObject = new SWIFT_CustomFieldGroup($_customFieldGroupID);
            if (!$_CustomFieldGroupObject instanceof SWIFT_CustomFieldGroup || !$_CustomFieldGroupObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_CustomFieldGroupObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Custom Field Group ID
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_customFieldGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_customFieldGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CustomFieldGroupObject = new SWIFT_CustomFieldGroup($_customFieldGroupID);
        if (!$_SWIFT_CustomFieldGroupObject instanceof SWIFT_CustomFieldGroup || !$_SWIFT_CustomFieldGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('editgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatecfgroup') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CustomFieldGroupObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_customFieldGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_customFieldGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CustomFieldGroupObject = new SWIFT_CustomFieldGroup($_customFieldGroupID);
        if (!$_SWIFT_CustomFieldGroupObject instanceof SWIFT_CustomFieldGroup || !$_SWIFT_CustomFieldGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID())) {
            $_visibilityType = SWIFT_PUBLICINT;
            if (isset($_POST['visibilitytype'])) {
                $_visibilityType = (int)($_POST['visibilitytype']);
            }

            $_updateResult = $_SWIFT_CustomFieldGroupObject->Update($_POST['title'], $_POST['displayorder'], $this->_GetDepartmentIDList(), $_POST['permstaffgroupid'], $_POST['permstaffid'], $_visibilityType);

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELDGROUP, $_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID(), $_POST['languages']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatecfgroup'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_CUSTOMFIELDS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CustomFieldGroupObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_customFieldGroupID);

        return false;
    }
}

?>
