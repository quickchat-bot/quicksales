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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffAssign;
use Base\Models\Staff\SWIFT_StaffGroupAssign;
use Base\Models\Staff\SWIFT_StaffSettings;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Department Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @property View_Department $View
 * @author Varun Shoor
 */
class Controller_Department extends Controller_admin
{
    // Core Constants
    const MENU_ID = 3;
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

        $this->Load->Library('Language:LanguagePhraseLinked', [], true, false, 'base');

        $this->Language->Load('departments');
        $this->Language->Load('admin_staffpermissions');
    }

    /**
     * Resort the departments
     *
     * @author Varun Shoor
     * @param mixed $_departmentIDSortList The Department ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SortList($_departmentIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatedepartment') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_Department::UpdateDisplayOrderList($_departmentIDSortList);

        return true;
    }

    /**
     * Delete the Departments from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_departmentIDList The Department ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_departmentIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletedepartment') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_departmentIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentid IN (" . BuildIN($_departmentIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_appLanguageKey = "app_" . $_SWIFT->Database->Record['departmentapp'];

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletedepartment'), $_SWIFT->Database->Record['title'], $_SWIFT->Language->Get($_appLanguageKey)), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_DEPARTMENTS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            // Begin Hook: admin_department_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_department_delete')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_Department::DeleteList($_departmentIDList);
        }

        return true;
    }

    /**
     * Delete the Given Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        self::DeleteList(array($_departmentID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Department Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('departments') . " > " . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewdepartments') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
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

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        }

        $_finalText = '<b>' . $this->Language->Get('deptitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int)($_POST['displayorder']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('deptype') . ':</b> ' . IIF($_POST['type'] == '1', $this->Language->Get('public'), $this->Language->Get('private')) . '<br />';

        $_departmentApp = false;
        if (isset($_POST['departmentapp'])) {
            $_departmentApp = $_POST['departmentapp'];
        }

        if (isset($_POST['parentdepartmentid']) && !empty($_POST['parentdepartmentid'])) {
            $_SWIFT_ParentDepartmentObject = new SWIFT_Department($_POST['parentdepartmentid']);
            $_departmentApp = $_SWIFT_ParentDepartmentObject->GetProperty('departmentapp');
            $_finalText .= '<b>' . $this->Language->Get('parentdepartment') . ':</b> ' . text_to_html_entities($_SWIFT_ParentDepartmentObject->GetProperty('title')) . '<br />';
        }

        $_appKey = 'app_' . $_departmentApp;
        $_appTitle = $_departmentApp;
        if ($this->Language->Get($_appKey)) {
            $_appTitle = $this->Language->Get($_appKey);
        }
        $_finalText .= '<b>' . $this->Language->Get('departmentapp') . ':</b> ' . htmlspecialchars($_appTitle) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('rtitle' . $_type), htmlspecialchars($_POST['title'])),
            sprintf($this->Language->Get('rdesc' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     */
    private function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '') {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertdepartment') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_caneditdepartment') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        // Begin Hook: admin_department_runchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('admin_department_runchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        if (isset($_POST['parentdepartmentid']) && !empty($_POST['parentdepartmentid'])) {
            $_parentDepartment = $this->Database->queryFetch("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentid = '" . (int)($_POST['parentdepartmentid']) . "'");

            if (!isset($_parentDepartment['departmentapp'])) {
                SWIFT::ErrorField('departmentapp');

                $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                return false;
            }

            if (isset($_POST['departmentapp']) && $_parentDepartment['departmentapp'] != $_POST['departmentapp']) {
                $_parentApp = 'app_' . $_parentDepartment['departmentapp'];

                $_appKey = 'app_' . $_POST['departmentapp'];
                SWIFT::Error($this->Language->Get('titledepmodmismatch'), sprintf($this->Language->Get('msgdepmodmismatch'), text_to_html_entities($_parentDepartment['title']), htmlspecialchars($this->Language->Get($_parentApp)), htmlspecialchars($this->Language->Get($_appKey))));

                SWIFT::ErrorField('departmentapp');

                return false;
            }
        } elseif (isset($_POST['parentdepartmentid'])) {
            return true;
        } else {
            return false;
        }

        return true;
    }

    /**
     * Render the Access Overview
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function AccessOverview()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('departments') . " > " . $this->Language->Get('accessoverview'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewdepartments') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderAccessOverview();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a new Department
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('departments') . " > " . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertdepartment') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertSubmit()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_POST['displayorder'] = (int)($_POST['displayorder']);
            if (empty($_POST['displayorder'])) {
                $_POST['displayorder'] = 1;
            }

            $_departmentApp = false;
            if (isset($_POST['departmentapp'])) {
                $_departmentApp = $_POST['departmentapp'];
            } elseif (!isset($_POST['departmentapp']) && !empty($_POST['parentdepartmentid'])) {
                $_SWIFT_ParentDepartmentObject = new SWIFT_Department($_POST['parentdepartmentid']);
                $_departmentApp = $_SWIFT_ParentDepartmentObject->GetProperty('departmentapp');
            } else {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            // Process POST Data into data that can be parsed easily
            $_variableContainer = self::_ProcessPOSTData();
            $_userGroupIDList = array();
            $_assignedStaffIDList = $_assignedStaffGroupIDList = array();
            extract($_variableContainer);

            $_SWIFT_DepartmentObject = SWIFT_Department::Insert($_POST['title'], $_departmentApp, IIF($_POST['type'] == 1, SWIFT_Department::DEPARTMENT_PUBLIC, SWIFT_Department::DEPARTMENT_PRIVATE), $_POST['displayorder'], $_POST['parentdepartmentid'], $_POST['uservisibilitycustom'], $_userGroupIDList);
            if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception($this->Language->Get('invaliddepartment'));
            }

            $_appLanguageKey = 'app_' . $_departmentApp;
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertdepartment'), $_POST['title'], $this->Language->Get($_appLanguageKey)), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_DEPARTMENTS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            SWIFT_StaffAssign::AssignDepartmentList($_SWIFT_DepartmentObject, $_assignedStaffIDList);
            SWIFT_StaffGroupAssign::AssignDepartmentList($_SWIFT_DepartmentObject, $_assignedStaffGroupIDList);

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_SWIFT_DepartmentObject->GetDepartmentID(), $_POST['languages']);

            // Begin Hook: admin_department_insert
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_department_insert')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function Edit($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_departmentID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_DepartmentObject = new SWIFT_Department($_departmentID);
        if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('departments') . " > " . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caneditdepartment') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_DepartmentObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function EditSubmit($_departmentID)
    {
        if (!$this->GetIsClassLoaded() || empty($_departmentID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_DepartmentObject = new SWIFT_Department($_departmentID);
        if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            // Process POST Data into data that can be parsed easily
            $_variableContainer = self::_ProcessPOSTData();
            $_userGroupIDList = array();
            $_assignedStaffIDList = $_assignedStaffGroupIDList = array();
            extract($_variableContainer);

            $_POST['displayorder'] = (int)($_POST['displayorder']);
            if (empty($_POST['displayorder'])) {
                $_POST['displayorder'] = 1;
            }

            $_departmentApp = false;
            if (isset($_POST['departmentapp'])) {
                $_departmentApp = $_POST['departmentapp'];
            } elseif (!isset($_POST['departmentapp']) && !empty($_POST['parentdepartmentid'])) {
                $_SWIFT_ParentDepartmentObject = new SWIFT_Department($_POST['parentdepartmentid']);
                $_departmentApp = $_SWIFT_ParentDepartmentObject->GetProperty('departmentapp');
            } else {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_DepartmentObject->Update($_POST['title'], $_departmentApp, IIF($_POST['type'] == 1, SWIFT_Department::DEPARTMENT_PUBLIC, SWIFT_Department::DEPARTMENT_PRIVATE), $_POST['displayorder'], $_POST['parentdepartmentid'], $_POST['uservisibilitycustom'], $_userGroupIDList);

            $_appLanguageKey = 'app_' . $_POST['departmentapp'];
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatedepartment'), $_POST['title'], $this->Language->Get($_appLanguageKey)), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_DEPARTMENTS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!isset($_POST['perm']) || !is_array($_POST['perm'])) {
                $_permissionContainer = array();
            } else {
                $_permissionContainer = $_POST['perm'];
            }

            SWIFT_StaffSettings::RebuildDepartmentSettings($_SWIFT_DepartmentObject->GetDepartmentID(), $_permissionContainer);
            SWIFT_StaffAssign::AssignDepartmentList($_SWIFT_DepartmentObject, $_assignedStaffIDList);
            SWIFT_StaffGroupAssign::AssignDepartmentList($_SWIFT_DepartmentObject, $_assignedStaffGroupIDList);

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_SWIFT_DepartmentObject->GetDepartmentID(), $_POST['languages']);

            // Begin Hook: admin_department_update
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('admin_department_update')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_departmentID);

        return false;
    }

    /**
     * Processes the POST Data into assigned list of user group ids, staff group ids and staff ids..
     *
     * @author Varun Shoor
     * @return array
     */
    static private function _ProcessPOSTData()
    {
        $_userGroupIDList = array();
        if (isset($_POST['usergroupidlist']) && _is_array($_POST['usergroupidlist'])) {
            foreach ($_POST['usergroupidlist'] as $key => $val) {
                if ($val == '1') {
                    $_userGroupIDList[] = $key;
                }
            }
        }

        $_assignedStaffIDList = $_assignedStaffGroupIDList = array();
        if (isset($_POST['assignedgroups']) && _is_array($_POST['assignedgroups'])) {
            foreach ($_POST['assignedgroups'] as $key => $val) {
                if ($val == '1') {
                    $_assignedStaffGroupIDList[] = $key;
                }
            }
        }

        if (isset($_POST['assignedstaff']) && _is_array($_POST['assignedstaff'])) {
            foreach ($_POST['assignedstaff'] as $key => $val) {
                if ($val == '1') {
                    $_assignedStaffIDList[] = $key;
                }

            }
        }

        return array('_userGroupIDList' => $_userGroupIDList, '_assignedStaffGroupIDList' => $_assignedStaffGroupIDList, '_assignedStaffIDList' => $_assignedStaffIDList);
    }
}

?>
