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

use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;
use LiveChat\Models\Rule\SWIFT_VisitorRule;

/**
 * The Live Chat Visitor Rule Management Class
 *
 * @author Varun Shoor
 *
 * @property View_Rule $View
 */
class Controller_Rule extends Controller_admin
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
     * Delete the Visitor Rules from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_visitorRuleIDList The Visitor Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_visitorRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lrcandeleterule') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_visitorRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "visitorrules WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletelsrule'), $_SWIFT->Database->Record['title']), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_VisitorRule::DeleteList($_visitorRuleIDList);
        }

        return true;
    }

    /**
     * Delete the Given Visitor Rule ID
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_visitorRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($_visitorRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Visitor Rule Grid
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

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('rules'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanviewrules') == '0') {
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
    private function _RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '' || trim($_POST['sortorder']) == '') {
            $this->UserInterface->CheckFields('title', 'sortorder');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!isset($_POST['rulecriteria']) || !_is_array($_POST['rulecriteria'])) {
            $this->UserInterface->Error($this->Language->Get('titlenorulecriteria'), $this->Language->Get('msgnorulecriteria'));

            return false;

        } else if (!isset($_POST['ruleaction']) || !_is_array($_POST['ruleaction'])) {
            $this->UserInterface->Error($this->Language->Get('titlenoruleactions'), $this->Language->Get('msgnoruleactions'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_lrcaninsertrule') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_lrcanupdaterule') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Visitor Rule
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

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('insertrule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcaninsertrule') == '0') {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->_RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_SWIFT_VisitorRuleObject = SWIFT_VisitorRule::Insert($_POST['title'], $_POST['stopprocessing'], $_POST['sortorder'], IIF($_POST['ruleoptions'] == 'all', SWIFT_VisitorRule::RULE_MATCHALL, SWIFT_VisitorRule::RULE_MATCHANY), $_POST['rulecriteria'], $_POST['ruleaction'], $_POST['ruletype']);

            if ($_SWIFT_VisitorRuleObject instanceof SWIFT_VisitorRule && $_SWIFT_VisitorRuleObject->GetIsClassLoaded()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertlsrule'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $this->_RenderConfirmation($_SWIFT_VisitorRuleObject->GetVisitorRuleID(), SWIFT_UserInterface::MODE_INSERT);

                $this->Load->Manage();

                return true;
            } else {
                $this->UserInterface->Error($this->Language->Get('titlevrcoreerror'), $this->Language->Get('msgvrcoreerror'));
            }
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Visitor Rule
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Edit($_visitorRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (empty($_visitorRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_VisitorRuleObject = new SWIFT_VisitorRule($_visitorRuleID);
        if (!$_SWIFT_VisitorRuleObject instanceof SWIFT_VisitorRule || !$_SWIFT_VisitorRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('editrule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanupdaterule') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_VisitorRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_visitorRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (empty($_visitorRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_VisitorRuleObject = new SWIFT_VisitorRule($_visitorRuleID);
        if (!$_SWIFT_VisitorRuleObject instanceof SWIFT_VisitorRule || !$_SWIFT_VisitorRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($this->_RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_SWIFT_VisitorRuleObject->Update($_POST['title'], $_POST['stopprocessing'], $_POST['sortorder'], IIF($_POST['ruleoptions'] == 'all', SWIFT_VisitorRule::RULE_MATCHALL, SWIFT_VisitorRule::RULE_MATCHANY), $_POST['rulecriteria'], $_POST['ruleaction'], $_POST['ruletype']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatelsrule'), $_POST['title']), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            $this->_RenderConfirmation($_visitorRuleID, SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_visitorRuleID);

        return false;
    }

    /**
     * Renders the Confirmation Dialog
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @param int $_mode The Current Mode (INSERT/EDIT)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _RenderConfirmation($_visitorRuleID, $_mode = SWIFT_UserInterface::MODE_INSERT)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_skillsCache = $_SWIFT->Cache->Get('skillscache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_visitorGroupCache = $_SWIFT->Cache->Get('visitorgroupcache');

        // ======= BEGIN VISITOR RULE INSERT/UPDATE RENDERING CODE =======
        $_visitorRule = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorrules WHERE visitorruleid = '" . ($_visitorRuleID) . "'");

        if (isset($_visitorRule['visitorruleid']) && !empty($_visitorRule['visitorruleid'])) {
            unset($_finalText);

            $_finalText = '';
            // Get all the criterias
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorrulecriteria WHERE visitorruleid = '" . (int)($_visitorRule['visitorruleid']) . "'");
            while ($this->Database->NextRecord()) {
                $_criteriaName = 'rule_' . $this->Database->Record['name'];

                if ($this->Database->Record['name'] == 'onlinestaffskills' && isset($_skillsCache[$this->Database->Record['rulematch']])) {
                    $_value = $_skillsCache[$this->Database->Record['rulematch']]['title'];
                } else if ($this->Database->Record['name'] == 'onlinestaffdepartments' && isset($_departmentCache[$this->Database->Record['rulematch']])) {
                    $_value = $_departmentCache[$this->Database->Record['rulematch']]['title'];
                } else {
                    $_value = $this->Database->Record['rulematch'];
                }

                $_finalText .= $this->Language->Get('if') . ' <b>"' . $this->Language->Get($_criteriaName) . '"</b> ' . SWIFT_Rules::GetOperText($this->Database->Record['ruleop']) . ' <b>"' . htmlspecialchars($_value) . '"</b><br>';
            }

            $_finalText .= '<br>';

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorruleactions WHERE visitorruleid = '" . (int)($_visitorRule['visitorruleid']) . "'");
            while ($this->Database->NextRecord()) {
                if ($this->Database->Record['actiontype'] == 'variable') {
                    $_finalText .= sprintf($this->Language->Get('actaddvariable'), htmlspecialchars($this->Database->Record['actionname']), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'visitorexperience') {
                    $_customtxt = '';
                    if ($this->Database->Record['actionname'] == 'engage') {
                        $_customtxt = $this->Language->Get('engagevisitor');
                    } else if ($this->Database->Record['actionname'] == 'inline') {
                        $_customtxt = $this->Language->Get('inlinechat');
                    } else if ($this->Database->Record['actionname'] == 'customengage') {
                        $_customtxt = $this->Language->Get('customengagevisitor');
                    }

                    $_finalText .= sprintf($this->Language->Get('actvisitorexperience'), htmlspecialchars($_customtxt)) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'staffalert') {
                    $_finalText .= sprintf($this->Language->Get('actstaffalert'), htmlspecialchars($this->Database->Record['actionname']), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'setskill' && isset($_skillsCache[$this->Database->Record['actionname']])) {
                    $_finalText .= sprintf($this->Language->Get('actsetskill'), htmlspecialchars($_skillsCache[$this->Database->Record['actionname']]['title']), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'setgroup' && isset($_visitorGroupCache[$this->Database->Record['actionname']])) {
                    $_finalText .= sprintf($this->Language->Get('actsetgroup'), htmlspecialchars($_visitorGroupCache[$this->Database->Record['actionname']]['title']), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'setdepartment' && isset($_departmentCache[$this->Database->Record['actionname']])) {
                    $_finalText .= sprintf($this->Language->Get('actsetdepartment'), htmlspecialchars($_departmentCache[$this->Database->Record['actionname']]['title']), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'setcolor') {
                    $_finalText .= sprintf($this->Language->Get('actsetcolor'), htmlspecialchars($this->Database->Record['actionname']), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                } else if ($this->Database->Record['actiontype'] == 'banvisitor') {
                    $_customText = '';

                    if ($this->Database->Record['actionname'] == 'ip') {
                        $_customText = $this->Language->Get('banip');
                    } else if ($this->Database->Record['actionname'] == 'classc') {
                        $_customText = $this->Language->Get('banclassc');
                    } else if ($this->Database->Record['actionname'] == 'classb') {
                        $_customText = $this->Language->Get('banclassb');
                    } else if ($this->Database->Record['actionname'] == 'classa') {
                        $_customText = $this->Language->Get('banclassa');
                    }

                    $_finalText .= sprintf($this->Language->Get('actbanvisitor'), htmlspecialchars($_customText), htmlspecialchars($this->Database->Record['actionvalue'])) . '<br>';
                }
            }

            if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                $this->UserInterface->Info(sprintf($this->Language->Get('titleinsertrule'), htmlspecialchars($_visitorRule['title'])), sprintf($this->Language->Get('msginsertrule'), htmlspecialchars($_visitorRule['title'])) . '<br>' . $_finalText);
            } else {
                $this->UserInterface->Info(sprintf($this->Language->Get('titleupdaterule'), htmlspecialchars($_visitorRule['title'])), sprintf($this->Language->Get('msgupdaterule'), htmlspecialchars($_visitorRule['title'])) . '<br>' . $_finalText);
            }
        }
        // ======= END VISITOR RULE INSERT/UPDATE RENDERING CODE =======
    }
}
