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

namespace LiveChat\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use SWIFT_Session;
use LiveChat\Models\Group\SWIFT_VisitorGroup;

/**
 * The Live Chat Visitor Group Manager Class
 *
 * @author Varun Shoor
 *
 * @property View_Group $View
 */
class Controller_Group extends Controller_admin
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
     * Delete the Visitor Groups from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_visitorGroupIDList The Visitor Group ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_visitorGroupIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lrcandeletevisitorgroup') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_visitorGroupIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "visitorgroups WHERE visitorgroupid IN (" . BuildIN($_visitorGroupIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletelsgroup'), $_SWIFT->Database->Record['title']), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_VisitorGroup::DeleteVisitorGroupList($_visitorGroupIDList);
        }

        return true;
    }

    /**
     * Delete the Given Visitor Group ID
     *
     * @author Varun Shoor
     * @param int $_visitorGroupID The Visitor Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_visitorGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        self::DeleteList(array($_visitorGroupID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Visitor Group Grid
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

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('visitorgroups'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanviewvisitorgroups') == '0') {
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

        if (trim($_POST['title']) == "") {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_lrcaninsertvisitorgroup') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_lrcanupdatevisitorgroup') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Visitor Group
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

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('insertgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcaninsertvisitorgroup') == '0') {
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
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_SWIFT_VisitorGroupObject = SWIFT_VisitorGroup::Insert($_POST['title'], $_POST['color']);
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertlsgroup'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            if (!$_SWIFT_VisitorGroupObject instanceof SWIFT_VisitorGroup || !$_SWIFT_VisitorGroupObject->GetIsClassLoaded()) {
                return false;
            }

            $this->UserInterface->Info(sprintf($this->Language->Get('titleinsertgroup'), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get('msginsertgroup'), htmlspecialchars($_POST['color']), htmlspecialchars($_POST['title'])));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Visitor Group
     *
     * @author Varun Shoor
     * @param int $_visitorGroupID The Visitor Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Edit($_visitorGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_visitorGroupID)) {
            return false;
        }

        $_SWIFT_VisitorGroupObject = new SWIFT_VisitorGroup($_visitorGroupID);
        if (!$_SWIFT_VisitorGroupObject instanceof SWIFT_VisitorGroup || !$_SWIFT_VisitorGroupObject->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('editgroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanupdatevisitorgroup') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_VisitorGroupObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_visitorGroupID The Visitor Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function EditSubmit($_visitorGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_visitorGroupID)) {
            return false;
        }

        $_SWIFT_VisitorGroupObject = new SWIFT_VisitorGroup($_visitorGroupID);
        if (!$_SWIFT_VisitorGroupObject instanceof SWIFT_VisitorGroup || !$_SWIFT_VisitorGroupObject->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_VisitorGroupObject->Update($_POST['title'], $_POST['color']);
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatelsgroup'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                return false;
            }

            $this->UserInterface->Info(sprintf($this->Language->Get('titleupdategroup'), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get('msgupdategroup'), htmlspecialchars($_POST['color']), htmlspecialchars($_POST['title'])));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_visitorGroupID);

        return false;
    }
}
