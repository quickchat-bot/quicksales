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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffGroup;
use Base\Models\Staff\SWIFT_StaffGroupAssign;
use Base\Models\Staff\SWIFT_StaffGroupSettings;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Staff Group Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \SWIFT_CacheManager $CacheManager
 * @property \Base\Library\Permissions\SWIFT_PermissionsRenderer $PermissionsRenderer
 * @property View_StaffGroup $View
 * @author Varun Shoor
 */
class Controller_StaffGroup extends Controller_admin
{
    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

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
        $this->Load->Library('Cache:CacheManager');

        $this->Language->Load('staff');
        $this->Language->Load('admin_staffpermissions');
    }

    /**
     * Delete the Staff Group ID from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_staffGroupIDList The Staff Group ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_staffGroupIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletestaffgroup') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_staffGroupCache = $_SWIFT->Cache->Get('staffgroupcache');

        // See if the array contains the same staff id as this user
        if (in_array($_SWIFT->Staff->GetProperty('staffgroupid'), $_staffGroupIDList) && isset($_staffGroupCache[$_SWIFT->Staff->GetProperty('staffgroupid')])) {
            SWIFT::Error($_SWIFT->Language->Get('titlestaffgroupdelsame'), sprintf($_SWIFT->Language->Get('msgstaffgroupdelsame'), htmlspecialchars($_staffGroupCache[$_SWIFT->Staff->GetProperty('staffgroupid')]['title'])));

            return false;
        }

        if (_is_array($_staffGroupIDList)) {

            $_index = 1;
            $_finalStaffGroupIDList = array();
            $_finalText = '';

            /*
             * BUG FIX - Pankaj Garg
             *
             * SWIFT-186, Prevent deletion of a team which isn't empty
             *
             * Comments:
             */
            $_resultContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) as staffcount FROM " . TABLE_PREFIX . SWIFT_Staff::TABLE_NAME . "
                                                               WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");

            if (isset($_resultContainer['staffcount']) && (int)($_resultContainer['staffcount']) > 0) {
                SWIFT::Error($_SWIFT->Language->Get('titlestaffgroupdelete'), sprintf($_SWIFT->Language->Get('msgstaffgroupdelete')));

                return false;
            }

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_finalStaffGroupIDList[] = $_SWIFT->Database->Record['staffgroupid'];

                $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . ' (' . $_SWIFT->Language->Get('isadmin') . ': ' . IIF($_SWIFT->Database->Record['isadmin'], $_SWIFT->Language->Get('yes'), $_SWIFT->Language->Get('no')) . ")<BR />";

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletestaffgroup'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $_index++;
            }

            if (!count($_finalStaffGroupIDList)) {
                return false;
            }

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelstaffteammul'), count($_finalStaffGroupIDList)), sprintf($_SWIFT->Language->Get('msgdelstaffteammul'), $_finalText));

            // Begin Hook: admin_staffteam_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_staffteam_delete')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_StaffGroup::DeleteList($_finalStaffGroupIDList);
        }

        return true;
    }

    /**
     * Delete the Given Staff Group ID
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_staffGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_staffGroupID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Staff Group Grid
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

        $this->UserInterface->Header($this->Language->Get('staff') . " > " . $this->Language->Get('managegroups'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewstaffgroup') == '0') {
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
    private function RunChecks($_mode, SWIFT_StaffGroup $_SWIFT_StaffGroupObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        /**
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2424: Inserting a new Team results in Undefined index: csrfhash (admin/class.Controller_StaffGroup.php:217) error
         *
         */
        if (!isset($_POST['csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '' || !isset($_POST['perm']) || !_is_array($_POST['perm']) || !isset($_POST['assigned']) || !_is_array($_POST['assigned'])) {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertstaffgroup') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_caneditstaffgroup') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        // Check for duplicate names
        $_sqlSuffix = '';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_sqlSuffix = " AND staffgroupid != '" . (int)($_SWIFT_StaffGroupObject->GetStaffGroupID()) . "'";
        }

        $_staffGroupContainer = $_SWIFT->Database->QueryFetch("SELECT staffgroupid FROM " . TABLE_PREFIX . "staffgroup WHERE LOWER(title) = '" . mb_strtolower($_POST['title']) . "'" . $_sqlSuffix);
        if (isset($_staffGroupContainer['staffgroupid']) && !empty($_staffGroupContainer['staffgroupid'])) {
            SWIFT::ErrorField('title');
            $this->UserInterface->Error($this->Language->Get('titleduplicatesgroup'), $this->Language->Get('msgduplicatesgroup'));

            return false;
        }

        // Begin Hook: admin_staffteam_runchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('admin_staffteam_runchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        return true;
    }

    /**
     * Get the Permissions from another staff group
     *
     * @author Varun Shoor
     * @param string $_permissionType The Permission Type
     * @param int $_staffGroupID The Staff Group ID to get Permissions From
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPermissions($_permissionType, $_staffGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffGroupCache = $this->Cache->Get('staffgroupcache');

        if (empty($_permissionType) || empty($_staffGroupID) || !isset($_staffGroupCache[$_staffGroupID])) {
            return false;
        }

        $_SWIFT_StaffGroupSettingsObject = new SWIFT_StaffGroupSettings($_staffGroupID);
        $_permissionContainer = $_SWIFT_StaffGroupSettingsObject->GetSettings();

        $this->PermissionsRenderer->RenderPermissionsHTML($this->UserInterface, $_permissionType, $_permissionContainer);

        return true;
    }

    /**
     * Insert a new Staff Group
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

        $this->UserInterface->Header($this->Language->Get('staff') . ' > ' . $this->Language->Get('insertstaffgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertstaffgroup') == '0') {
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
     * @param SWIFT_StaffGroup $_SWIFT_StaffGroupObject The SWIFT_StaffGroup Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_StaffGroup $_SWIFT_StaffGroupObject)
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

        $_finalText = '';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "groupassigns WHERE staffgroupid = '" . (int)($_SWIFT_StaffGroupObject->GetStaffGroupID()) . "'");
        while ($this->Database->NextRecord()) {
            if (!isset($_departmentCache[$this->Database->Record['departmentid']])) {
                continue;
            }

            $_departmentApp = $_departmentCache[$this->Database->Record['departmentid']]['departmentapp'];

            $_finalText .= text_to_html_entities($_departmentCache[$this->Database->Record['departmentid']]['title']) . ' (' . $this->Language->Get('app_' . $_departmentApp) . ')<BR />';
        }

        SWIFT::Info(sprintf($this->Language->Get('title' . $_type . 'staffgroup'), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get('msg' . $_type . 'staffgroup'), htmlspecialchars($_POST['title']), htmlspecialchars($_POST['title']), IIF($_POST['isadmin'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')), '<BR />' . $_finalText));

        return true;
    }

    /**
     * Processes the post data and combined a department id list for processing of group assignments
     *
     * @author Varun Shoor
     * @return array The Assigned Department ID List
     */
    protected static function _ProcessAssignedDepartmentData()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        if (!isset($_POST['assigned'])) {
            return array();
        }

        $_assignedDepartmentIDList = array();
        foreach ($_POST['assigned'] as $_key => $_val) {
            $_department = $_departmentCache[$_key];
            $_parentDepartment = array();
            if (!empty($_department['parentdepartmentid'])) {
                $_parentDepartment = $_departmentCache[$_department['parentdepartmentid']];
            }

            $_isAssigned = false;
            if ($_val) {
                $_isAssigned = true;
                // Make sure that its parent is set to yes
                if (!empty($_parentDepartment['parentdepartmentid'])) {
                    if (!$_POST["assigned"][$_parentDepartment['departmentid']]) {
                        $_isAssigned = false;
                    }
                }
            }

            if ($_isAssigned) {
                $_assignedDepartmentIDList[] = $_key;
            }
        }

        return $_assignedDepartmentIDList;
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
            $_SWIFT_StaffGroupObject = SWIFT_StaffGroup::Insert($_POST['title'], IIF($_POST['isadmin'] == 1, true, false));

            if ($_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup && $_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertstaffgroup'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $_SWIFT_StaffGroupSettingsObject = new SWIFT_StaffGroupSettings($_SWIFT_StaffGroupObject->GetStaffGroupID());
                $_SWIFT_StaffGroupSettingsObject->ReprocessGroupSettings($_POST['perm']);

                $_assignedDepartmentIDList = self::_ProcessAssignedDepartmentData();

                SWIFT_StaffGroupAssign::AssignStaffGroupList($_SWIFT_StaffGroupObject, $_assignedDepartmentIDList);

                // Begin Hook: admin_staffteam_insert
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('admin_staffteam_insert')) ? eval($_hookCode) : false;
                // End Hook

                $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_StaffGroupObject);

                $this->CacheManager->EmptyCacheDirectory();
            } else {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Staff Group ID
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_staffGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_staffGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_StaffGroupObject = new SWIFT_StaffGroup($_staffGroupID);
        if (!$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup || !$_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('staff') . " > " . $this->Language->Get('editstaffgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caneditstaffgroup') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_StaffGroupObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_staffGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_staffGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_StaffGroupObject = new SWIFT_StaffGroup($_staffGroupID);
        if (!$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup || !$_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_StaffGroupObject)) {
            $_updateResult = $_SWIFT_StaffGroupObject->Update($_POST['title'], IIF($_POST['isadmin'] == 1, true, false));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatestaffgroup'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            $_SWIFT_StaffGroupSettingsObject = new SWIFT_StaffGroupSettings($_SWIFT_StaffGroupObject->GetStaffGroupID());
            $_SWIFT_StaffGroupSettingsObject->ReprocessGroupSettings($_POST['perm']);

            $_assignedDepartmentIDList = self::_ProcessAssignedDepartmentData();

            SWIFT_StaffGroupAssign::AssignStaffGroupList($_SWIFT_StaffGroupObject, $_assignedDepartmentIDList);

            // Begin Hook: admin_staffteam_update
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_staffteam_update')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_StaffGroupObject);

            $this->CacheManager->EmptyCacheDirectory();

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_staffGroupID);

        return false;
    }
}

?>
