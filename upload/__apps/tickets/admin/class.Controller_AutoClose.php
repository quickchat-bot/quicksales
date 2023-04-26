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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;

/**
 * The Auto Close Controller
 *
 * @author Varun Shoor
 *
 * @property Controller_AutoClose $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_AutoClose $View
 */
class Controller_AutoClose extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('tickets');
        $this->Language->Load('admin_autoclose');
    }

    /**
     * Delete the Auto Close Rules from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_autoCloseRuleIDList The Auto Close Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_autoCloseRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeleteautoclose') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_autoCloseRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (" . BuildIN($_autoCloseRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityautoclosedelete'), htmlspecialchars($_SWIFT->Database->Record['title'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_AutoCloseRule::DeleteList($_autoCloseRuleIDList);
        }

        return true;
    }

    /**
     * Enable the Auto Close Rules from Mass Action
     *
     * @author Varun Shoor
     * @param array $_autoCloseRuleIDList The Auto Close Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function EnableList($_autoCloseRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateautoclose') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_autoCloseRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (" . BuildIN($_autoCloseRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityautocloseenable'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_AutoCloseRule::EnableList($_autoCloseRuleIDList);
        }

        return true;
    }

    /**
     * Disable the Auto Close Rules from Mass Action
     *
     * @author Varun Shoor
     * @param array $_autoCloseRuleIDList The Auto Close Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DisableList($_autoCloseRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateautoclose') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_autoCloseRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (" . BuildIN($_autoCloseRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityautoclosedisable'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_AutoCloseRule::DisableList($_autoCloseRuleIDList);
        }

        return true;
    }

    /**
     * Delete the given Auto Close Rule ID
     *
     * @author Varun Shoor
     * @param int $_autoCloseRuleID The Auto Close Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_autoCloseRuleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_autoCloseRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Auto Close Rule Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('autoclose') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewautoclose') == '0')
        {
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
    protected function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '' || empty($_POST['targetticketstatusid']) || empty($_POST['inactivitythreshold']) || empty($_POST['closurethreshold']))
        {
            $this->UserInterface->CheckFields('title', 'targetticketstatusid', 'inactivitythreshold', 'closurethreshold');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (!isset($_POST['rulecriteria']) || !count($_POST['rulecriteria'])) {
            $this->UserInterface->Error($this->Language->Get('titlenocriteriaadded'), $this->Language->Get('msgnocriteriaadded'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertautoclose') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdateautoclose') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Loads the Rule Criteria into $_POST
     *
     * @author Varun Shoor
     * @param int $_autoCloseRuleID The Auto Close Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    private function _LoadPOSTVariables($_autoCloseRuleID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['rulecriteria']))
        {
            $_POST['rulecriteria'] = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autoclosecriteria
                WHERE autocloseruleid = '" . ($_autoCloseRuleID) . "'
                ORDER BY autoclosecriteriaid ASC");
            while ($this->Database->NextRecord())
            {
                $_POST['rulecriteria'][] = array($this->Database->Record['name'], $this->Database->Record['ruleop'],
                    $this->Database->Record['rulematch'], $this->Database->Record['rulematchtype']);
            }
        }

        return true;
    }

    /**
     * Insert a new Auto Close Rule
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('autoclose') . ' > ' . $this->Language->Get('insertrule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertautoclose') == '0')
        {
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_SWIFT_AutoCloseRuleObject = SWIFT_AutoCloseRule::Create($_POST['title'], $_POST['targetticketstatusid'], $_POST['inactivitythreshold'], $_POST['closurethreshold'],
                    $_POST['sendpendingnotification'], $_POST['sendfinalnotification'], $_POST['isenabled'], $_POST['sortorder'], $_POST['rulecriteria'], $_POST['suppresssurveyemail']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityautocloseinsert'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_AutoCloseRuleObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Auto Close Rule
     *
     * @author Varun Shoor
     * @param int $_autoCloseRuleID The Auto Close Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_autoCloseRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_autoCloseRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AutoCloseRuleObject = new SWIFT_AutoCloseRule(new SWIFT_DataID($_autoCloseRuleID));
        if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->_LoadPOSTVariables($_autoCloseRuleID);

        $this->UserInterface->Header($this->Language->Get('autoclose') . ' > ' . $this->Language->Get('editrule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateautoclose') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_AutoCloseRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_autoCloseRuleID The Auto Close Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_autoCloseRuleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_autoCloseRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AutoCloseRuleObject = new SWIFT_AutoCloseRule(new SWIFT_DataID($_autoCloseRuleID));
        if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_AutoCloseRuleObject->Update($_POST['title'], $_POST['targetticketstatusid'], $_POST['inactivitythreshold'], $_POST['closurethreshold'],
                    $_POST['sendpendingnotification'], $_POST['sendfinalnotification'], $_POST['isenabled'], $_POST['sortorder'], $_POST['rulecriteria'], $_POST['suppresssurveyemail']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityautocloseupdate'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_AutoCloseRuleObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_autoCloseRuleID);

        return false;
    }

    /**
     * Render the User Interface Confirmation
     *
     * @author Varun Shoor
     * @param mixed $_mode The UI Mode
     * @param SWIFT_AutoCLoseRule $_SWIFT_AutoCloseRuleObject The Auto Close Rule Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');

        $_criteriaPointer = SWIFT_AutoCloseRule::GetCriteriaPointer();
        SWIFT_AutoCloseRule::ExtendCustomCriteria($_criteriaPointer);

        $__type = IIF($_mode == SWIFT_UserInterface::MODE_INSERT, 'insert', 'update');
        $_autoCloseRule = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid = '" . $_SWIFT_AutoCloseRuleObject->GetAutoCloseRuleID() . "'");

        $_finalText = '<b>' . $this->Language->Get('ruletitle') . ':</b> ' . htmlspecialchars($_autoCloseRule['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('targetticketstatus') . ':</b> ' . htmlspecialchars($_ticketStatusCache[$_autoCloseRule['targetticketstatusid']]['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('inactivitythreshold') . ':</b> ' . htmlspecialchars($_autoCloseRule['inactivitythreshold']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('closurethreshold') . ':</b> ' . htmlspecialchars($_autoCloseRule['closurethreshold']) . '<br />';

        $_finalText .= '<b>' . $this->Language->Get('isenabled') . ':</b> ' . IIF($_autoCloseRule['isenabled'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('sortorder') . ':</b> ' . (int) ($_autoCloseRule['sortorder']) . '<br />';

        $_index = 1;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autoclosecriteria WHERE autocloseruleid = '" . (int) ($_SWIFT_AutoCloseRuleObject->GetAutoCloseRuleID()) . "'");
        while ($this->Database->NextRecord())
        {
            $_finalText .= '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif' . '" border="0" align="absmiddle" /> ' . $this->Language->Get('if') . ' <b>"' . $this->Language->Get('ar' . $this->Database->Record['name']) . '"</b> ' . SWIFT_Rules::GetOperText($this->Database->Record['ruleop']) . ' <b>"';

            $_extendedName = '';
            if (isset($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) && _is_array($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) && $_criteriaPointer[$this->Database->Record['name']]['field'] === 'custom')
            {
                foreach ($_criteriaPointer[$this->Database->Record['name']]['fieldcontents'] as $_key => $_val)
                {
                    if ($_val['contents'] == $this->Database->Record['rulematch'])
                    {
                        $_extendedName = $_val['title'];

                        break;
                    }
                }
            }

            $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $this->Database->Record['rulematch'])) . '"</b><br>';
            $_index++;
        }


        SWIFT::Info($this->Language->Get('titleautocloserule' . $__type), sprintf($this->Language->Get('msgautocloserule' . $__type), htmlspecialchars($_autoCloseRule['title'])) . '<br />' . $_finalText);

        return true;
    }
}
