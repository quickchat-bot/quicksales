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

use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use Parser\Models\Rule\SWIFT_ParserRule;
use SWIFT_Session;

/**
 * The Parser Rule Controller
 *
 * @property \SWIFT_Loader $Load
 * @property \Parser\Admin\View_Rule $View
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_Rule extends Controller_admin
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

        $this->Language->Load('mailparser_rules');
    }

    /**
     * Delete the Parser Rules from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_parserRuleIDList The Parser Rule ID List Container Array
     * @param bool  $_byPassCSRF       Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_parserRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcandeleterule') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_parserRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteparserrule'),
                    htmlspecialchars(StripName($_SWIFT->Database->Record['title'], 100))), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_ParserRule::DeleteList($_parserRuleIDList);
        }

        return true;
    }

    /**
     * Enable the Parser Rules from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_parserRuleIDList The Parser Rule ID List Container Array
     * @param bool  $_byPassCSRF       Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_parserRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdaterule') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_parserRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityenableparserrule'),
                    htmlspecialchars(StripName($_SWIFT->Database->Record['title'], 100))), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_ParserRule::EnableList($_parserRuleIDList);
        }

        return true;
    }

    /**
     * Disable the Parser Rules from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_parserRuleIDList The Parser Rule ID List Container Array
     * @param bool  $_byPassCSRF       Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_parserRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdaterule') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_parserRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydisableparserrule'),
                    htmlspecialchars(StripName($_SWIFT->Database->Record['title'], 100))), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_ParserRule::DisableList($_parserRuleIDList);
        }

        return true;
    }

    /**
     * Delete the Given Parser Rule ID
     *
     * @author Varun Shoor
     *
     * @param int $_parserRuleID The Parser Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_parserRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_parserRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Parser Rule Grid
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

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('managerules'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanviewrules') == '0') {
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

        $_actionContainer = $this->_GetActionsContainer();

        if (trim($_POST['title']) == '' || !isset($_POST['rulecriteria']) || !_is_array($_POST['rulecriteria'])) {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!count($_actionContainer)) {
            $this->UserInterface->Error($this->Language->Get('titlenoaction'), $this->Language->Get('msgnoaction'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_mpcaninsertrule') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_mpcanupdaterule') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Parser Rule
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

        $this->_ResetPOSTVariables();

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('insertrule'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcaninsertrule') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Criteria Container
     *
     * @author Varun Shoor
     * @return mixed "_criteriaContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function _GetCriteriaContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_POST['rulecriteria'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_criteriaContainer = array();
        $_index = 0;

        if (!_is_array($_POST['rulecriteria'])) {
            return $_criteriaContainer;
        }

        foreach ($_POST['rulecriteria'] as $_key => $_val) {
            if (!isset($_val[0]) || !isset($_val[1]) || !isset($_val[2])) {
                continue;
            }

            $_criteriaContainer[$_index]['name'] = $_val[0];
            $_criteriaContainer[$_index]['ruleop'] = $_val[1];
            $_criteriaContainer[$_index]['rulematch'] = $_val[2];
            $_criteriaContainer[$_index]['rulematchtype'] = $_val[3];

            $_index++;
        }

        return $_criteriaContainer;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     *
     * @param mixed $_mode         The User Interface Mode
     * @param int   $_parserRuleID The Parser Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_parserRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_criteriaPointer = SWIFT_ParserRule::GetCriteriaPointer();
        SWIFT_ParserRule::ExtendCustomCriteria($_criteriaPointer);

        $_finalText = '';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrulecriteria WHERE parserruleid = '" . $_parserRuleID . "'");
        while ($this->Database->NextRecord()) {
            $_criteriaName = 'p' . $this->Database->Record['name'];

            $_finalText .= $this->Language->Get('if') . ' <b>"' . $this->Language->Get($_criteriaName) . '"</b> ' .
                SWIFT_Rules::GetOperText($this->Database->Record['ruleop']) . ' <b>"';

            $_extendedName = '';

            if (isset($_criteriaPointer[$this->Database->Record['name']]) &&
                isset($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) &&
                _is_array($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) &&
                $_criteriaPointer[$this->Database->Record['name']]['field'] == 'custom') {
                foreach ($_criteriaPointer[$this->Database->Record['name']]['fieldcontents'] as $_key => $_val) {
                    if ($_val['contents'] == $this->Database->Record['rulematch']) {
                        $_extendedName = $_val['title'];

                        break;
                    }
                }
            }

            $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $this->Database->Record['rulematch'])) . '"</b><BR />';
        }

        $_finalText .= '<BR />';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserruleactions WHERE parserruleid = '" . $_parserRuleID . "'");
        while ($this->Database->NextRecord()) {
            $_finalText .= $this->View->RenderRuleAction($this->Database->Record);
        }

        SWIFT::Info(sprintf($this->Language->Get('title' . $_type . 'rule'), htmlspecialchars($_POST['title'])),
            sprintf($this->Language->Get('msg' . $_type . 'rule'), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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
            $_ParserRuleObject = SWIFT_ParserRule::Create($_POST['title'], $_POST['isenabled'], $_POST['sortorder'], $_POST['ruletype'],
                $_POST['ruleoptions'], $_POST['stopprocessing'], $this->_GetCriteriaContainer(), $this->_GetActionsContainer());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertparserrule'), htmlspecialchars(StripName($_POST['title'], 55))),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_ParserRuleObject instanceof SWIFT_ParserRule || !$_ParserRuleObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_ParserRuleObject->GetParserRuleID());

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Parser Rule ID
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param int $_parserRuleID The Parser Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_parserRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_parserRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ParserRuleObject = new SWIFT_ParserRule($_parserRuleID);
        if (!$_SWIFT_ParserRuleObject instanceof SWIFT_ParserRule || !$_SWIFT_ParserRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        $this->_LoadPOSTVariables($_SWIFT_ParserRuleObject);

        $this->_ProcessActionContainerToPOST($_SWIFT_ParserRuleObject->GetActionContainer());

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('editrule'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdaterule') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ParserRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     *
     * @param int $_parserRuleID The Parser Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_parserRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_parserRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ParserRuleObject = new SWIFT_ParserRule($_parserRuleID);
        if (!$_SWIFT_ParserRuleObject instanceof SWIFT_ParserRule || !$_SWIFT_ParserRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_ParserRuleObject->Update($_POST['title'], $_POST['isenabled'], $_POST['sortorder'], $_POST['ruletype'],
                $_POST['ruleoptions'], $_POST['stopprocessing'], $this->_GetCriteriaContainer(), $this->_GetActionsContainer());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateparserrule'), htmlspecialchars(StripName($_POST['title'], 55))),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_parserRuleID);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_parserRuleID);

        return false;
    }

    /**
     * Load the Criteria & Actions into $_POST
     *
     * @author Varun Shoor
     *
     * @param SWIFT_ParserRule $_SWIFT_ParserRuleObject The Parser\Models\Rule\SWIFT_ParserRule Object Pointer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _LoadPOSTVariables(SWIFT_ParserRule $_SWIFT_ParserRuleObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['rulecriteria'])) {
            $_POST['rulecriteria'] = array();

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrulecriteria
                WHERE parserruleid = '" . (int)($_SWIFT_ParserRuleObject->GetParserRuleID()) . "' ORDER BY parserrulecriteriaid ASC");
            while ($this->Database->NextRecord()) {
                $_ruleMatchType = $this->Database->Record['rulematchtype'];

                // An old entry?
                if (empty($_ruleMatchType) && $_SWIFT_ParserRuleObject->GetProperty('matchtype') != SWIFT_Rules::RULE_MATCHEXTENDED) {
                    $_ruleMatchType = $_SWIFT_ParserRuleObject->GetProperty('matchtype');
                }

                $_POST['rulecriteria'][] = array($this->Database->Record['name'], $this->Database->Record['ruleop'], $this->Database->Record['rulematch'], $_ruleMatchType);
            }
        }

        if (!isset($_POST['ruleaction'])) {
            $_POST['ruleaction'] = $_actionContainer = array();

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserruleactions
                WHERE parserruleid = '" . (int)($_SWIFT_ParserRuleObject->GetParserRuleID()) . "'");
            while ($this->Database->NextRecord()) {
                $_actionContainer[$this->Database->Record['parserruleactionid']] = $this->Database->Record;
            }

            $this->_ProcessActionContainerToPOST($_actionContainer);
        }

        return true;
    }

    /**
     * Processes the Individual Form Fields and Returns a Unified Action Array
     *
     * @author Varun Shoor
     * @return mixed "_actionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetActionsContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_actionContainer = array();
        $_index = 0;

        // ======= PRE PARSE RULES =======
        if ($_POST['ruletype'] == SWIFT_ParserRule::TYPE_PREPARSE) {
            if (isset($_POST['replycontents']) && trim($_POST['replycontents']) != '') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_REPLY;
                $_actionContainer[$_index]['typedata'] = $_POST['replycontents'];
                $_index++;
            }

            if (isset($_POST['forwardemail']) && trim($_POST['forwardemail']) != '') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_FORWARD;
                $_actionContainer[$_index]['typechar'] = $_POST['forwardemail'];
                $_index++;
            }

            if (isset($_POST[SWIFT_ParserRule::PARSERACTION_IGNORE]) && $_POST[SWIFT_ParserRule::PARSERACTION_IGNORE] == '1') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_IGNORE;
                $_actionContainer[$_index]['typeid'] = '1';
                $_index++;
            }

            if (isset($_POST[SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER]) && $_POST[SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER] == '1') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER;
                $_actionContainer[$_index]['typeid'] = '1';
                $_index++;
            }

            if (isset($_POST[SWIFT_ParserRule::PARSERACTION_NOALERTRULES]) && $_POST[SWIFT_ParserRule::PARSERACTION_NOALERTRULES] == '1') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_NOALERTRULES;
                $_actionContainer[$_index]['typeid'] = '1';
                $_index++;
            }

            if (isset($_POST[SWIFT_ParserRule::PARSERACTION_NOTICKET]) && $_POST[SWIFT_ParserRule::PARSERACTION_NOTICKET] == '1') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_NOTICKET;
                $_actionContainer[$_index]['typeid'] = '1';
                $_index++;
            }
        }

        // ======= POST PARSE RULES =======
        if ($_POST['ruletype'] == SWIFT_ParserRule::TYPE_POSTPARSE) {
            if (isset($_POST['departmentid']) && !empty($_POST['departmentid'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_DEPARTMENT;
                $_actionContainer[$_index]['typeid'] = $_POST['departmentid'];
                $_index++;
            }

            if (isset($_POST['ticketstatusid']) && !empty($_POST['ticketstatusid'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_STATUS;
                $_actionContainer[$_index]['typeid'] = $_POST['ticketstatusid'];
                $_index++;
            }

            if (isset($_POST['tickettypeid']) && !empty($_POST['tickettypeid'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_TICKETTYPE;
                $_actionContainer[$_index]['typeid'] = $_POST['tickettypeid'];
                $_index++;
            }

            if (isset($_POST['ticketpriorityid']) && !empty($_POST['ticketpriorityid'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_PRIORITY;
                $_actionContainer[$_index]['typeid'] = $_POST['ticketpriorityid'];
                $_index++;
            }

            if (isset($_POST['staffid']) && !empty($_POST['staffid'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_OWNER;
                $_actionContainer[$_index]['typeid'] = $_POST['staffid'];
                $_index++;
            }

            if (isset($_POST['flagtype']) && !empty($_POST['flagtype'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_FLAGTICKET;
                $_actionContainer[$_index]['typeid'] = $_POST['flagtype'];
                $_index++;
            }

            if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_SLAPLAN;
                $_actionContainer[$_index]['typeid'] = $_POST['slaplanid'];
                $_index++;
            }

            if (isset($_POST['notes']) && trim($_POST['notes']) != '') {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_ADDNOTE;
                $_actionContainer[$_index]['typedata'] = $_POST['notes'];
                $_index++;
            }

            if (isset($_POST['movetotrash']) && !empty($_POST['movetotrash'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_MOVETOTRASH;
                $_actionContainer[$_index]['typeid'] = '1';
                $_index++;
            }

            if (isset($_POST['private']) && !empty($_POST['private'])) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_PRIVATE;
                $_actionContainer[$_index]['typeid'] = '1';
                $_index++;
            }


            $_addTagsList = SWIFT_UserInterface::GetMultipleInputValues('addtags');
            $_removeTagsList = SWIFT_UserInterface::GetMultipleInputValues('removetags');

            if (_is_array($_addTagsList)) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_ADDTAGS;
                $_actionContainer[$_index]['typedata'] = json_encode($_addTagsList);
                $_index++;
            }

            if (_is_array($_removeTagsList)) {
                $_actionContainer[$_index]['name'] = SWIFT_ParserRule::PARSERACTION_REMOVETAGS;
                $_actionContainer[$_index]['typedata'] = json_encode($_removeTagsList);
                $_index++;
            }
        }

        return $_actionContainer;
    }

    /**
     * Processes the Action Container Array to relevant POST variables
     *
     * @author Varun Shoor
     *
     * @param array $_actionContainer The Action Container Array
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessActionContainerToPOST($_actionContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_actionContainer)) {
            return false;
        }

        $_MapRuleToPOST = [
            SWIFT_ParserRule::PARSERACTION_REPLY => [1, 'replycontents'],
            SWIFT_ParserRule::PARSERACTION_ADDNOTE => [1, 'notes'],
            SWIFT_ParserRule::PARSERACTION_FORWARD => [2, 'forwardemail'],
            SWIFT_ParserRule::PARSERACTION_IGNORE => [3],
            SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER => [3],
            SWIFT_ParserRule::PARSERACTION_NOALERTRULES => [3],
            SWIFT_ParserRule::PARSERACTION_NOTICKET => [3],
            SWIFT_ParserRule::PARSERACTION_DEPARTMENT => [4, 'departmentid'],
            SWIFT_ParserRule::PARSERACTION_STATUS => [4, 'ticketstatusid'],
            SWIFT_ParserRule::PARSERACTION_TICKETTYPE => [4, 'tickettypeid'],
            SWIFT_ParserRule::PARSERACTION_PRIORITY => [4, 'ticketpriorityid'],
            SWIFT_ParserRule::PARSERACTION_OWNER => [4, 'staffid'],
            SWIFT_ParserRule::PARSERACTION_SLAPLAN => [4, 'slaplanid'],
            SWIFT_ParserRule::PARSERACTION_FLAGTICKET => [5, 'flagtype'],
            SWIFT_ParserRule::PARSERACTION_MOVETOTRASH => [5, 'movetotrash'],
            SWIFT_ParserRule::PARSERACTION_PRIVATE => [5, 'private'],
            SWIFT_ParserRule::PARSERACTION_ADDTAGS => [6, 'addtags'],
            SWIFT_ParserRule::PARSERACTION_REMOVETAGS => [6, 'removetags'],
        ];

        foreach ($_actionContainer as $_key => $_val) {
            $_pointer = $_MapRuleToPOST[$_val['name']][0];
            $_key = $_MapRuleToPOST[$_val['name']][1] ?? 0;

            switch ($_pointer) {
                case 1:
                    $_POST[$_key] ?? $_POST[$_key] = $_val['typedata'];
                    break;
                case 2:
                    $_POST[$_key] ?? $_POST[$_key] = $_val['typechar'];
                    break;
                case 3:
                    $_POST[$_val['name']] ?? $_POST[$_val['name']] = '1';
                    break;
                case 4:
                    $_POST[$_key] ?? $_POST[$_key] = (int)($_val['typeid']);
                    break;
                case 5:
                    $_POST[$_key] ?? $_POST[$_key] = $_val['typeid'];
                    break;
                case 6:
                    $_POST[$_key] ?? $_POST[$_key] = json_decode($_val['typedata']);
                    break;
            }
        }

        return true;
    }

    /**
     * Reset the POST Variables if needed
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ResetPOSTVariables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['replycontents'])) {
            $_POST['replycontents'] = '';
        }

        if (!isset($_POST['forwardemail'])) {
            $_POST['forwardemail'] = '';
        }

        foreach (array(SWIFT_ParserRule::PARSERACTION_IGNORE, SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER,
                     SWIFT_ParserRule::PARSERACTION_NOALERTRULES, SWIFT_ParserRule::PARSERACTION_NOTICKET) as $_key => $_val) {
            if (!isset($_POST[$_val])) {
                $_POST[$_val] = '0';
            }
        }

        $_POST['departmentid'] = $_POST['ticketstatusid'] = $_POST['ticketpriorityid'] = $_POST['staffid'] =
        $_POST['flagtype'] = $_POST['slaplanid'] = $_POST['tickettypeid'] = '0';

        $_POST['addtags'] = array();
        $_POST['removetags'] = array();

        $_POST['notes'] = '';

        return true;
    }
}

?>
