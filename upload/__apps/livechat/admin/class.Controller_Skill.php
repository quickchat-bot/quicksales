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

namespace LiveChat\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use LiveChat\Models\Skill\SWIFT_ChatSkill;
use SWIFT_Session;

/**
 * The Live Chat Skill Manager Class
 *
 * @author Varun Shoor
 *
 * @property View_Skill $View
 */
class Controller_Skill extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 13;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_livesupport');
    }

    /**
     * Delete the Chat Skills from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_chatSkillIDList The Chat Skill ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_chatSkillIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lrcandeleteskill') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_chatSkillIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "chatskills WHERE chatskillid IN (" . BuildIN($_chatSkillIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletelsskill'), $_SWIFT->Database->Record['title']), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_ChatSkill::DeleteList($_chatSkillIDList);
        }

        return true;
    }

    /**
     * Delete the Given Chat Skill ID
     *
     * @author Varun Shoor
     * @param int $_chatSkillID The Chat Skill ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_chatSkillID)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        self::DeleteList(array($_chatSkillID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Chat Skill Grid
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

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('skills'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanviewskills') == '0') {
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

        if (trim($_POST['title']) == '' || trim($_POST['description']) == '') {
            $this->UserInterface->CheckFields('title', 'description');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_lrcaninsertskill') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_lrcanupdateskill') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Chat Skill
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

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('insertskill'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcaninsertskill') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Assigned Staff ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedStaffIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetAssignedStaffIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['permstaffid']) || !_is_array($_POST['permstaffid'])) {
            return array();
        }

        $_assignedStaffIDList = array();
        foreach ($_POST['permstaffid'] as $_key => $_val) {
            if ($_val == '1') {
                $_assignedStaffIDList[] = (int)($_key);
            }
        }

        return $_assignedStaffIDList;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_chatSkillID = SWIFT_ChatSkill::Insert($_POST['title'], $_POST['description'], $this->_GetAssignedStaffIDList());
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertlsskill'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_chatSkillID) {
                return false;
            }

            $this->UserInterface->Info($this->Language->Get('titleinsertskill'), sprintf($this->Language->Get('msginsertskill'), htmlspecialchars($_POST['title'])));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Chat Skill ID
     *
     * @author Varun Shoor
     * @param int $_chatSkillID The Chat Skill ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Edit($_chatSkillID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_chatSkillID)) {
            return false;
        }

        $_SWIFT_ChatSkillObject = new SWIFT_ChatSkill($_chatSkillID);
        if (!$_SWIFT_ChatSkillObject instanceof SWIFT_ChatSkill || !$_SWIFT_ChatSkillObject->GetIsClassLoaded()) {
            return false;
        }

        $_permissionContainer = array();
        $_chatSkill = $_SWIFT_ChatSkillObject->GetDataStore();
        if (isset($_chatSkill['links']) && is_array($_chatSkill['links'])) {
            /* Bug Fix : Parminder Singh
             *
             * SWIFT-2977 : Illegal string offset 'staffid' (./__apps/livechat/admin/class.Controller_Skill.php:313)
             *
             * Comments : None
             */
            foreach ($_chatSkill['links'] as $_staffID) {
                $_permissionContainer[$_staffID] = '1';
            }
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('editskill'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanupdateskill') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ChatSkillObject, $_permissionContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_chatSkillID The Chat Skill ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function EditSubmit($_chatSkillID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_chatSkillID)) {
            return false;
        }

        $_SWIFT_ChatSkillObject = new SWIFT_ChatSkill($_chatSkillID);
        if (!$_SWIFT_ChatSkillObject instanceof SWIFT_ChatSkill || !$_SWIFT_ChatSkillObject->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_ChatSkillObject->Update($_POST['title'], $_POST['description'], $this->_GetAssignedStaffIDList());
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatelsskill'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                return false;
            }

            $this->UserInterface->Info($this->Language->Get('titleupdateskill'), sprintf($this->Language->Get('msgupdateskill'), htmlspecialchars($_POST['title'])));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_chatSkillID);

        return false;
    }

}
