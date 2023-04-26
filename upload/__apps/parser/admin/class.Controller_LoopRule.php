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
use SWIFT_Exception;
use Parser\Models\Loop\SWIFT_LoopRule;
use SWIFT_Session;

/**
 * The Loop Rule Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_LoopRule $View
 */
class Controller_LoopRule extends Controller_admin
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
     * Delete the Parser Loop Rules from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_parserLoopRuleIDList The Parser Loop Rule ID List Container Array
     * @param bool  $_byPassCSRF           Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_parserLoopRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcandeletelooprule') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_masterFinalText = '';
        $_finalParserLoopRuleIDList = array();
        $_masterIndex = 1;

        if (_is_array($_parserLoopRuleIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserlooprules WHERE parserloopruleid IN (" .
                BuildIN($_parserLoopRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == '1') {
                    $_masterFinalText .= $_masterIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
                    $_masterIndex++;
                } else {
                    $_finalParserLoopRuleIDList[] = $_SWIFT->Database->Record['parserloopruleid'];
                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteparserlooprule'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['title'], 35))), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }

                if (count($_finalParserLoopRuleIDList)) {
                    SWIFT_LoopRule::DeleteList($_parserLoopRuleIDList);
                }
            }
        }

        if (!empty($_masterFinalText)) {
            SWIFT::Alert($_SWIFT->Language->Get('titlelooprulemasterdel'), $_SWIFT->Language->Get('msglooprulemasterdel') . '<br />' .
                $_masterFinalText);
        }

        return true;
    }

    /**
     * Delete the Given Parser Loop Rule ID
     *
     * @author Varun Shoor
     *
     * @param int $_parserLoopRuleID The Parser Loop Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_parserLoopRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_parserLoopRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Parser Loop Rule Grid
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

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('pr_mangelooprules'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanviewlooprules') == '0') {
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

        $_POST['rulelength'] = (int)($_POST['rulelength']);
        $_POST['maxhits'] = (int)($_POST['maxhits']);
        $_POST['restoreafter'] = (int)($_POST['restoreafter']);

        if (empty($_POST['rulelength']) || trim($_POST['title']) == '' || empty($_POST['maxhits']) || empty($_POST['restoreafter'])) {
            $this->UserInterface->CheckFields('rulelength', 'title', 'maxhits', 'restoreafter');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_mpcaninsertlooprule') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_mpcanupdatelooprule') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Parser Loop Rule
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

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('pr_insert_new_loopcutter_rule_title'),
            self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcaninsertlooprule') == '0') {
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

        $_finalText = '<b>' . $this->Language->Get('thresholdruletitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('pr_newloopcontrolwatchlength_title') . ':</b> ' . (int)($_POST['rulelength']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('pr_newloopcontrolmaxcontacts_title') . ':</b> ' . (int)($_POST['maxhits']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('pr_newloopcontrolrestoreafter_title') . ':</b> ' . (int)($_POST['restoreafter']) . '<br />';

        SWIFT::Info($this->Language->Get('title' . $_type . 'looprule'), $this->Language->Get('msg' . $_type . 'looprule') . '<br />' . $_finalText);

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
            $_parserLoopRuleID = SWIFT_LoopRule::Create($_POST['title'], $_POST['rulelength'], $_POST['maxhits'], $_POST['restoreafter']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertparserlooprule'),
                htmlspecialchars(StripName($_POST['title'], 35))), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_PARSER,
                SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_parserLoopRuleID) {
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
     * Edit the Parser Loop Rule ID
     *
     * @author Varun Shoor
     *
     * @param int $_parserLoopRuleID The Parser Loop Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_parserLoopRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_parserLoopRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_LoopRuleObject = new SWIFT_LoopRule($_parserLoopRuleID);
        if (!$_SWIFT_LoopRuleObject instanceof SWIFT_LoopRule || !$_SWIFT_LoopRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('pr_edit_new_loopcutter_rule_title'),
            self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcaninsertlooprule') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_LoopRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     *
     * @param int $_parserLoopRuleID The Parser Loop Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_parserLoopRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_parserLoopRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_LoopRuleObject = new SWIFT_LoopRule($_parserLoopRuleID);
        if (!$_SWIFT_LoopRuleObject instanceof SWIFT_LoopRule || !$_SWIFT_LoopRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_LoopRuleObject->Update($_POST['title'], $_POST['rulelength'], $_POST['maxhits'], $_POST['restoreafter']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateparserlooprule'),
                htmlspecialchars(StripName($_POST['title'], 35))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_PARSER,
                SWIFT_StaffActivityLog::INTERFACE_ADMIN);

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

        $this->Load->Edit($_parserLoopRuleID);

        return false;
    }
}

?>
