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
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Escalation\SWIFT_EscalationNotification;
use Tickets\Models\Escalation\SWIFT_EscalationRule;

/**
 * The Escalation Controller
 *
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property SWIFT_TicketFlag $TicketFlag
 * @property Controller_Escalation $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Escalation $View
 * @author Varun Shoor
 */
class Controller_Escalation extends Controller_admin
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

        $this->Language->Load('adminescalations');
        $this->Language->Load('adminsla');
    }

    /**
     * Delete the Escalation Rule from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_escalationRuleIDList The Escalation Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_escalationRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeleteescalations') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_escalationRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "escalationrules WHERE escalationruleid IN (" .
                    BuildIN($_escalationRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityescalationruledelete'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_EscalationRule::DeleteList($_escalationRuleIDList);
        }

        return true;
    }

    /**
     * Delete the Given Escalation Rule ID
     *
     * @author Varun Shoor
     * @param int $_escalationRuleID The Escalation Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_escalationRuleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_escalationRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Escalation Rule Grid
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

        $this->UserInterface->Header($this->Language->Get('escalations') . ' > ' . $this->Language->Get('manage'),
                self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewescalations') == '0')
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

        if (trim($_POST['title']) == '' || trim($_POST['slaplanid']) == '' || empty($_POST['slaplanid']))
        {
            $this->UserInterface->CheckFields('title', 'slaplanid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertescalations') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdateescalations') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Escalation Rule
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

        // Check for SLA Plans
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        if (!_is_array($_slaPlanCache))
        {
            SWIFT::Alert($this->Language->Get('titlenoplanavail'), $this->Language->Get('msgnoplanavail'));
        }

        $this->UserInterface->Header($this->Language->Get('escalations') . ' > ' . $this->Language->Get('insertrule'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertescalations') == '0')
        {
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
     * @param mixed $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Cache->Queue('slaplancache', 'staffcache', 'prioritycache', 'statuscache', 'departmentcache', 'tickettypecache');
        $this->Cache->LoadQueue();

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_priorityCache = $this->Cache->Get('prioritycache');
        $_statusCache = $this->Cache->Get('statuscache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $this->Load->Library('Flag:TicketFlag', [], true, false, 'tickets');

        $_flagContainer = $this->TicketFlag->GetFlagList();

        $_finalText = '<b>' . $this->Language->Get('ruletitle') . ': </b>' . htmlspecialchars($_POST['title']) . '<br />';

        $_finalText .= '<b>' . $this->Language->Get('escalationplan') . ': </b>' . htmlspecialchars($_slaPlanCache[$_POST['slaplanid']]['title']) .
                '<br />';

        $_finalText .= '<b>' . '<img src="' . SWIFT::Get('themepath') . 'images/doublearrows.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('escalationaction') . '</b><br />';

        if (isset($_staffCache[$_POST['staffid']]))
        {
            $_staffName = text_to_html_entities($_staffCache[$_POST['staffid']]['fullname']);
        } else {
            $_staffName = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationstaff') . ': </b> ' . $_staffName . '<br />';

        if (isset($_ticketTypeCache[$_POST['tickettypeid']]))
        {
            $_ticketTypeTitle = htmlspecialchars($_ticketTypeCache[$_POST['tickettypeid']]['title']);
        } else {
            $_ticketTypeTitle = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationtickettype') . ': </b> ' . $_ticketTypeTitle . '<br />';

        if (isset($_priorityCache[$_POST['priorityid']]))
        {
            $_priorityTitle = htmlspecialchars($_priorityCache[$_POST['priorityid']]['title']);
        } else {
            $_priorityTitle = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationpriority') . ': </b> ' . $_priorityTitle . '<br />';

        if (isset($_statusCache[$_POST['ticketstatusid']]))
        {
            $_statusTitle = htmlspecialchars($_statusCache[$_POST['ticketstatusid']]['title']);
        } else {
            $_statusTitle = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationticketstatus') . ': </b> ' . $_statusTitle . '<br />';

        if (isset($_departmentCache[$_POST['departmentid']]))
        {
            $_departmentTitle = text_to_html_entities($_departmentCache[$_POST['departmentid']]['title']);
        } else {
            $_departmentTitle = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationdepartment') . ': </b> ' . $_departmentTitle . '<br />';

        if (isset($_slaPlanCache[$_POST['newslaplanid']]))
        {
            $_newSlaPlanTitle = htmlspecialchars($_slaPlanCache[$_POST['newslaplanid']]['title']);
        } else {
            $_newSlaPlanTitle = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationslaplan') . ': </b> ' . $_newSlaPlanTitle . '<br />';

        if (isset($_flagContainer[$_POST['flagtype']]))
        {
            $_flagTitle = htmlspecialchars($_flagContainer[$_POST['flagtype']]);
        } else {
            $_flagTitle = $this->Language->Get('nochange');
        }

        $_finalText .= '<b>' . $this->Language->Get('escalationflagtype') . ': </b> ' . $_flagTitle . '<br />';

        $_escalationAddTagsList = SWIFT_UserInterface::GetMultipleInputValues('addtags');
        $_escalationRemoveTagsList = SWIFT_UserInterface::GetMultipleInputValues('removetags');

        if (_is_array($_escalationAddTagsList)) {
            $_escalationAddTagTitle = implode(', ', $_escalationAddTagsList);

            $_finalText .= '<b>' . $this->Language->Get('escalationaddtags') . ': </b>' . $_escalationAddTagTitle . '<br />';
        }

        if (_is_array($_escalationRemoveTagsList)) {
            $_escalationRemoveTagTitle = implode(', ', $_escalationRemoveTagsList);

            $_finalText .= '<b>' . $this->Language->Get('escalationremovetags') . ': </b>' . $_escalationRemoveTagTitle . '<br />';
        }

        SWIFT::Info($this->Language->Get('title' . $_type . 'escalationrule'), sprintf($this->Language->Get('msg' . $_type . 'escalationrule'),
                htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Retrieves the Notification Container
     *
     * @author Varun Shoor
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetNotificationContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_notificationContainer = array();

        if (isset($_POST['notifications']) && _is_array($_POST['notifications']))
        {
            foreach ($_POST['notifications'] as $_key => $_val)
            {
                if (isset($_val[0]) && isset($_val[1]) && isset($_val[2]) && !empty($_val[0]) && !empty($_val[1]) && !empty($_val[2]) &&
                        SWIFT_EscalationNotification::IsValidType($_val[0]))
                {
                    $_notificationContainer[] = array($_val[0], $_val[1], $_val[2]);
                }
            }
        }

        return $_notificationContainer;
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
            $_escalationRuleID = SWIFT_EscalationRule::Create($_POST['title'], $_POST['slaplanid'], $_POST['staffid'], $_POST['ruletype'], $_POST['tickettypeid'],
                    $_POST['priorityid'], $_POST['ticketstatusid'], $_POST['departmentid'], $_POST['flagtype'], $_POST['newslaplanid'],
                    $this->_GetNotificationContainer(), SWIFT_UserInterface::GetMultipleInputValues('addtags'),
                    SWIFT_UserInterface::GetMultipleInputValues('removetags'));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertescalation'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_escalationRuleID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Escalation Rule ID
     *
     * @author Varun Shoor
     * @param int $_escalationRuleID The Escalation Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_escalationRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_escalationRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_EscalationRuleObject = new SWIFT_EscalationRule(new SWIFT_DataID($_escalationRuleID));
        if (!$_SWIFT_EscalationRuleObject instanceof SWIFT_EscalationRule || !$_SWIFT_EscalationRuleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        // Check for SLA Plans
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        if (!_is_array($_slaPlanCache))
        {
            SWIFT::Alert($this->Language->Get('titlenoplanavail'), $this->Language->Get('msgnoplanavail'));
        }

        $this->UserInterface->Header($this->Language->Get('escalations') . ' > ' . $this->Language->Get('editrule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateescalations') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_EscalationRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_escalationRuleID The Escalation Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_escalationRuleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_escalationRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_EscalationRuleObject = new SWIFT_EscalationRule(new SWIFT_DataID($_escalationRuleID));
        if (!$_SWIFT_EscalationRuleObject instanceof SWIFT_EscalationRule || !$_SWIFT_EscalationRuleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_EscalationRuleObject->Update($_POST['title'], $_POST['slaplanid'], $_POST['staffid'], $_POST['ruletype'], $_POST['tickettypeid'],
                    $_POST['priorityid'], $_POST['ticketstatusid'], $_POST['departmentid'], $_POST['flagtype'], $_POST['newslaplanid'],
                    $this->_GetNotificationContainer(), SWIFT_UserInterface::GetMultipleInputValues('addtags'),
                    SWIFT_UserInterface::GetMultipleInputValues('removetags'));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateescalation'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_escalationRuleID);

        return false;
    }
}
