<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use Parser\Models\Breakline\SWIFT_Breakline;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Breakline Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 *
 * @property View_Breakline $View
 */
class Controller_Breakline extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 4;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('emailparser');
    }

    /**
     * Delete the Parser Breaklines from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_breaklineIDList The Parser Breakline ID List Container Array
     * @param bool  $_byPassCSRF      Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_breaklineIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcandeletebreaklines') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_breaklineIDList)) {
            $_SWIFT->Database->Query("SELECT breakline FROM " . TABLE_PREFIX . "breaklines WHERE breaklineid IN (" . BuildIN($_breaklineIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletebreakline'),
                    htmlspecialchars(StripName($_SWIFT->Database->Record['breakline'], 15))), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Breakline::DeleteList($_breaklineIDList);
        }

        return true;
    }

    /**
     * Delete the Given Parser Breakline ID
     *
     * @author Varun Shoor
     *
     * @param int $_breaklineID The Parser Breakline ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_breaklineID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_breaklineID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Parser Breakline Grid
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

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('breaklines'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanviewbreaklines') == '0') {
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
     *
     * @param int $_mode The User Interface Mode
     *
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

        if (trim($_POST['breakline']) == '') {
            $this->UserInterface->CheckFields('breakline');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_mpcaninsertbreakline') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_mpcanupdatebreakline') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Breakline
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

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('insertbreakline'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcaninsertbreakline') == '0') {
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
     *
     * @param mixed $_mode The User Interface Mode
     *
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

        $_finalText = '<b>' . $this->Language->Get('breaklinetitle') . ':</b> ' . htmlspecialchars($_POST['breakline']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('isregexp') . ':</b> ' . IIF($_POST['isregexp'] == 1, $this->Language->Get('yes'),
                $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('sortorder') . ':</b> ' . (int)($_POST['sortorder']) . '<br />';

        SWIFT::Info($this->Language->Get('title' . $_type . 'breakline'), $this->Language->Get('msg' . $_type . 'breakline') . '<br />' .
            $_finalText);

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_breaklineID = SWIFT_Breakline::Create($_POST['breakline'], $_POST['isregexp'], $_POST['sortorder']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertbreakline'),
                htmlspecialchars(StripName($_POST['breakline'], 15))), SWIFT_StaffActivityLog::ACTION_INSERT,
                SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_breaklineID) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Breakline ID
     *
     * @author Varun Shoor
     *
     * @param int $_breaklineID The Breakline ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_breaklineID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_breaklineID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_BreaklineObject = new SWIFT_Breakline($_breaklineID);
        if (!$_SWIFT_BreaklineObject instanceof SWIFT_Breakline || !$_SWIFT_BreaklineObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('editbreakline'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdatebreakline') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_BreaklineObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     *
     * @param int $_breaklineID The Breakline ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_breaklineID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_breaklineID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_BreaklineObject = new SWIFT_Breakline($_breaklineID);
        if (!$_SWIFT_BreaklineObject instanceof SWIFT_Breakline || !$_SWIFT_BreaklineObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_BreaklineObject->Update($_POST['breakline'], $_POST['isregexp'], $_POST['sortorder']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatebreakline'),
                htmlspecialchars(StripName($_POST['breakline'], 15))), SWIFT_StaffActivityLog::ACTION_UPDATE,
                SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_breaklineID);

        return false;
    }
}

?>
