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
use SWIFT_Session;
use LiveChat\Models\Ban\SWIFT_VisitorBan;

/**
 * The Live Chat Visitor Ban Management Class
 *
 * @author Varun Shoor
 *
 * @property View_Ban $View
 */
class Controller_Ban extends Controller_admin
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
     * Delete the Visitor Bans from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_visitorBanIDList The Visitor Ban ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_visitorBanIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lrcandeleteban') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_visitorBanIDList)) {
            $_SWIFT->Database->Query("SELECT ipaddress FROM " . TABLE_PREFIX . "visitorbans WHERE visitorbanid IN (" . BuildIN($_visitorBanIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletelsban'), $_SWIFT->Database->Record['ipaddress']), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_VisitorBan::DeleteList($_visitorBanIDList);
        }

        return true;
    }

    /**
     * Delete the Given Visitor Ban ID
     *
     * @author Varun Shoor
     * @param int $_visitorBanID The Visitor Ban ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_visitorBanID)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        self::DeleteList(array($_visitorBanID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Visitor Ban Grid
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

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('visitorbans'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanviewbans') == '0') {
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

        if (trim($_POST['ipaddress']) == "") {
            $this->UserInterface->CheckFields('ipaddress');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_lrcaninsertban') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_lrcanupdateban') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Visitor Ban
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

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('insertban'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcaninsertban') == '0') {
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
            $_visitorBanID = SWIFT_VisitorBan::Insert($_POST['ipaddress'], $_POST['isregex'], $_SWIFT->Staff->GetStaffID());
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertlsban'), $_POST['ipaddress']), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_visitorBanID) {
                return false;
            }

            $this->UserInterface->Info($this->Language->Get('titleinsertban'), sprintf($this->Language->Get('msginsertban'), htmlspecialchars($_POST['ipaddress'])));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Visitor Ban
     *
     * @author Varun Shoor
     * @param int $_visitorBanID The Visitor Ban ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Edit($_visitorBanID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_visitorBanID)) {
            return false;
        }

        $_SWIFT_VisitorBanObject = new SWIFT_VisitorBan($_visitorBanID);
        if (!$_SWIFT_VisitorBanObject instanceof SWIFT_VisitorBan || !$_SWIFT_VisitorBanObject->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('editban'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanupdateban') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_VisitorBanObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_visitorBanID The Visitor Ban ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function EditSubmit($_visitorBanID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_visitorBanID)) {
            return false;
        }

        $_SWIFT_VisitorBanObject = new SWIFT_VisitorBan($_visitorBanID);
        if (!$_SWIFT_VisitorBanObject instanceof SWIFT_VisitorBan || !$_SWIFT_VisitorBanObject->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_VisitorBanObject->Update($_POST['ipaddress'], $_POST['isregex'], $_SWIFT->Staff->GetStaffID());
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatelsban'), $_POST['ipaddress']), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                return false;
            }

            $this->UserInterface->Info($this->Language->Get('titleupdateban'), sprintf($this->Language->Get('msgupdateban'), htmlspecialchars($_POST['ipaddress'])));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_visitorBanID);

        return false;
    }
}
