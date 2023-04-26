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
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use Parser\Models\CatchAll\SWIFT_CatchAllRule;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Parser Catch-All Rule Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 *
 * @property View_CatchAll $View
 */
class Controller_CatchAll extends Controller_admin
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

        $this->Language->Load('mailparser_misc');
    }

    /**
     * Delete the Catch-All Rules from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_catchAllRuleIDList The Parser Catch-All Rule ID List Container Array
     * @param bool  $_byPassCSRF         Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_catchAllRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletecatchall') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_catchAllRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "catchallrules WHERE catchallruleid IN (" .
                BuildIN($_catchAllRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecatchallrule'),
                    htmlspecialchars(StripName($_SWIFT->Database->Record['title'], 25))), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_CatchAllRule::DeleteList($_catchAllRuleIDList);
        }

        return true;
    }

    /**
     * Delete the Given Parser Catch-All Rule ID
     *
     * @author Varun Shoor
     *
     * @param int $_catchAllRuleID The Parser Catch-All Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_catchAllRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_catchAllRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Parser Catch-All Grid
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

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('catchallrules'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewcatchall') == '0') {
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

        $_queueCache = $this->Cache->Get('queuecache');

        if (trim($_POST['title']) == '' || trim($_POST['ruleexpr']) == '' || trim($_POST['emailqueueid']) == '' ||
            !isset($_queueCache['list'][$_POST['emailqueueid']])) {
            $this->UserInterface->CheckFields('title', 'ruleexpr', 'emailqueueid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_mpcaninsertcatchall') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_mpcanupdatecatchall') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Parser Catch-All Rule
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

        $_queueCache = $this->Cache->Get('queuecache');
        if (!$_queueCache || !isset($_queueCache['list']) || !count($_queueCache['list'])) {
            SWIFT::Alert($this->Language->Get('titlenqcatchall'), $this->Language->Get('msgnqcatchall'));
        }

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('insertcatchallrule'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcaninsertcatchall') == '0') {
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
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param mixed  $_mode The User Interface Mode
     * @param string $_currentTitle The object title before update
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_currentTitle = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_queueCache = $this->Cache->Get('queuecache');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }


        $_finalText = '<b>' . $this->Language->Get('ruletitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('rregexp') . ':</b> ' . htmlspecialchars($_POST['ruleexpr']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('sortorder') . ':</b> ' . (int)($_POST['sortorder']) . '<br />';

        if ($_queueCache && isset($_queueCache['list'][$_POST['emailqueueid']])) {
            $_finalText .= '<b>' . $this->Language->Get('emailqueue') . ':</b> ' .
                htmlspecialchars($_queueCache['list'][$_POST['emailqueueid']]['email']) . '<br />';
        } else {
            $_finalText .= '<b>' . $this->Language->Get('emailqueue') . ':</b> ' . htmlspecialchars($this->Language->Get('na')) . '<br />';
        }
        if ($_type == 'update') {
            SWIFT::Info(sprintf($this->Language->Get('titleupdatecatchall'), $_currentTitle),
               sprintf($this->Language->Get('msgupdatecatchall'), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);
        } else {
            SWIFT::Info($this->Language->Get('titleinsertcatchall'), sprintf($this->Language->Get('msginsertcatchall'),
                    htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);
        }

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
            $_catchAllRuleID = SWIFT_CatchAllRule::Create($_POST['title'], $_POST['ruleexpr'], $_POST['emailqueueid'], $_POST['sortorder']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertcatchall'), htmlspecialchars(StripName($_POST['title'], 25))),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_catchAllRuleID) {
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
     * Edit the Parser Catch-All Rule ID
     *
     * @author Varun Shoor
     *
     * @param int $_catchAllRuleID The Parser Catch-All Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_catchAllRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_catchAllRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CatchAllRuleObject = new SWIFT_CatchAllRule($_catchAllRuleID);
        if (!$_SWIFT_CatchAllRuleObject instanceof SWIFT_CatchAllRule || !$_SWIFT_CatchAllRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('editcatchallrule'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdatecatchall') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CatchAllRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param int $_catchAllRuleID The Parser Catch-All Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_catchAllRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_catchAllRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CatchAllRuleObject = new SWIFT_CatchAllRule($_catchAllRuleID);
        if (!$_SWIFT_CatchAllRuleObject instanceof SWIFT_CatchAllRule || !$_SWIFT_CatchAllRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_currentTitle = $_SWIFT_CatchAllRuleObject->GetProperty('title');
            $_updateResult = $_SWIFT_CatchAllRuleObject->Update($_POST['title'], $_POST['ruleexpr'], $_POST['emailqueueid'], $_POST['sortorder']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatecatchallrule'),
                htmlspecialchars(StripName($_POST['title'], 25))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_PARSER,
                SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_currentTitle);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_catchAllRuleID);

        return false;
    }
}

?>
