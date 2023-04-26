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

use Base\Library\Permissions\SWIFT_PermissionsRenderer;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserGroupSettings;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The User Group Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_UserGroup $View
 * @property SWIFT_PermissionsRenderer $PermissionsRenderer
 * @author Varun Shoor
 */
class Controller_UserGroup extends Controller_admin
{
    // Core Constants
    const MENU_ID = 4;
    const NAVIGATION_ID = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Permissions:PermissionsRenderer', [], true, false, 'base');

        $this->Language->Load('admin_users');
        $this->Language->Load('admin_userpermissions');
    }

    /**
     * Delete the User Groups from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_userGroupIDList The Ticket File Type ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userGroupIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeleteusergroups') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_userGroupIDList)) {
            $_finalUserGroupIDList = $_masterUserGroupIDList = array();
            $_masterUserGroupText = '';
            $_masterIndex = 1;

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE usergroupid IN (" . BuildIN($_userGroupIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == '1') {
                    $_masterUserGroupIDList[] = $_SWIFT->Database->Record['usergroupid'];
                    $_masterUserGroupText .= $_masterIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

                    $_masterIndex++;
                } else {
                    $_finalUserGroupIDList[] = $_SWIFT->Database->Record['usergroupid'];

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteusergroup'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            }

            if (count($_masterUserGroupIDList)) {
                SWIFT::Alert(sprintf($_SWIFT->Language->Get('titlenodelmasterugroup'), count($_masterUserGroupIDList)), $_SWIFT->Language->Get('msgnodelmasterugroup') . '<BR />' . $_masterUserGroupText);
            }

            if (count($_finalUserGroupIDList)) {
                // Begin Hook: admin_usergroup_delete
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('admin_usergroup_delete')) ? eval($_hookCode) : false;
                // End Hook

                SWIFT_UserGroup::DeleteList($_finalUserGroupIDList);
            }
        }

        return true;
    }

    /**
     * Delete the Given User Group ID
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_userGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_userGroupID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the User Group Grid
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

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('managegroups'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewusergroups') == '0') {
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode)
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

        if (trim($_POST['title']) == '' || trim($_POST['grouptype']) == '') {
            $this->UserInterface->CheckFields('title', 'grouptype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertusergroup') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_canupdateusergroup') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        // Begin Hook: admin_usergroup_runchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('admin_usergroup_runchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        return true;
    }

    /**
     * Insert a new User Group
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

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('insertgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertusergroup') == '0') {
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<b>' . $this->Language->Get('usergrouptitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('usergrouptype') . ':</b> ' . SWIFT_UserGroup::GetGroupTypeLabel($_POST['grouptype']) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titleusergroup' . $_type), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get('msgusergroup' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
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
            $_SWIFT_UserGroupObject = SWIFT_UserGroup::Create($_POST['title'], $_POST['grouptype']);
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertusergroup'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_SWIFT_UserGroupObject instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            $_SWIFT_UserGroupSettingsObject = new SWIFT_UserGroupSettings($_SWIFT_UserGroupObject->GetUserGroupID());
            $_SWIFT_UserGroupSettingsObject->ReprocessGroupSettings($_POST['perm']);

            // Begin Hook: admin_usergroup_insert
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_usergroup_insert')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the User Group ID
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserGroupObject = new SWIFT_UserGroup($_userGroupID);
        if (!$_SWIFT_UserGroupObject instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('editusergroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdateusergroup') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserGroupObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_userGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserGroupObject = new SWIFT_UserGroup($_userGroupID);
        if (!$_SWIFT_UserGroupObject instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_UserGroupObject->Update($_POST['title'], $_POST['grouptype']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateusergroup'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_UserGroupSettingsObject = new SWIFT_UserGroupSettings($_SWIFT_UserGroupObject->GetUserGroupID());
            $_SWIFT_UserGroupSettingsObject->ReprocessGroupSettings($_POST['perm']);

            // Begin Hook: admin_usergroup_update
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_usergroup_update')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_userGroupID);

        return false;
    }
}

?>
