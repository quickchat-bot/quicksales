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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\Priority\SWIFT_TicketPriority;

/**
 * The Ticket Priority Controller
 *
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_Priority $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @property View_Priority $View
 * @author Varun Shoor
 */
class Controller_Priority extends Controller_admin
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

        $this->Load->Library('Language:LanguagePhraseLinked', [], true, false, 'base');

        $this->Language->Load('admin_ticketspriority');
    }

    /**
     * Resort the ticket priorities
     *
     * @author Varun Shoor
     * @param mixed $_ticketPriorityIDSortList The Ticket Priority ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function SortList($_ticketPriorityIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatepriority') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_TicketPriority::UpdateDisplayOrderList($_ticketPriorityIDSortList);

        return true;
    }

    /**
     * Delete the Ticket Priority from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketPriorityIDList The Ticket Priority ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketPriorityIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeletepriority') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_finalTicketPriorityIDList = $_masterTicketPriorityIDList = array();

        $_masterFinalText = '';
        $_masterIndex = 1;

        if (_is_array($_ticketPriorityIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."ticketpriorities WHERE priorityid IN (" . BuildIN($_ticketPriorityIDList) .
                    ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == 1)
                {
                    $_masterTicketPriorityIDList[] = (int) ($_SWIFT->Database->Record['priorityid']);

                    $_masterFinalText .= $_masterIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

                    $_masterIndex++;
                } else {
                    $_finalTicketPriorityIDList[] = (int) ($_SWIFT->Database->Record['priorityid']);

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activititydeleteticketpriority'),
                            htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                            SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            }

            SWIFT_TicketPriority::DeleteList($_finalTicketPriorityIDList);
            // update tickets with deleted priorities
            $_SWIFT->Database->Query('UPDATE ' . TABLE_PREFIX ."tickets SET priorityid=0, prioritytitle='' WHERE priorityid IN (" . BuildIN($_finalTicketPriorityIDList) . ')');
        }

        if (count($_masterTicketPriorityIDList))
        {
            SWIFT::Alert(sprintf($_SWIFT->Language->Get('titlemasterprioritydelete'), count($_masterTicketPriorityIDList)),
                    $_SWIFT->Language->Get('msgmasterprioritydelete') . '<BR />' . $_masterFinalText);
        }

        return true;
    }

    /**
     * Delete the Given Ticket Priority ID
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketPriorityID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketPriorityID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket Priority Grid
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('managepriorities'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewpriorities') == '0')
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

        if (trim($_POST['title']) == '' || trim($_POST['displayorder']) == '' || trim($_POST['type']) == '')
        {
            $this->UserInterface->CheckFields('title', 'displayorder', 'type');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (isset($_POST['frcolorcode']) && !empty($_POST['frcolorcode']) && !IsValidColor($_POST['frcolorcode'])) {
            SWIFT::ErrorField('frcolorcode');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (isset($_POST['bgcolorcode']) && !empty($_POST['bgcolorcode']) && !IsValidColor($_POST['bgcolorcode'])) {
            SWIFT::ErrorField('bgcolorcode');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertpriority') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdatepriority') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Ticket Priority
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertpriority'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertpriority') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Assigned User Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedUserGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetAssignedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['usergroupidlist']) || !_is_array($_POST['usergroupidlist']))
        {
            return array();
        }

        $_assignedUserGroupIDList = array();
        foreach ($_POST['usergroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedUserGroupIDList[] = (int) ($_key);
            }
        }

        return $_assignedUserGroupIDList;
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

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<table border="0" cellpadding="0" cellspacing="1"><tr><td>' . '<b>' . $this->Language->Get('prioritytitle') . ':&nbsp;</b> ' .
                '</td><td bgcolor="' . $_POST['bgcolorcode'] . '"><font color="' . $_POST['frcolorcode'] . '">' .
                htmlspecialchars($_POST['title']) . '</font></td></tr></table>';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int) ($_POST['displayorder']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('prioritytype') . ':</b> ' . IIF($_POST['type'] == true, $this->Language->Get('public'),
                $this->Language->Get('private')) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titleticketpriority' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgticketpriority' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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
            $_ticketPriorityID = SWIFT_TicketPriority::Create($_POST['title'], $_POST['displayorder'],
                    IIF($_POST['type'] == '1', SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['frcolorcode'], $_POST['bgcolorcode'],
                    $_POST['uservisibilitycustom'], $this->_GetAssignedUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertticketpriority'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_ticketPriorityID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $_ticketPriorityID, $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Ticket Priority ID
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketPriorityID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketPriorityID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketPriorityObject = new SWIFT_TicketPriority($_ticketPriorityID);
        if (!$_SWIFT_TicketPriorityObject instanceof SWIFT_TicketPriority || !$_SWIFT_TicketPriorityObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editpriority'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatepriority') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketPriorityObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketPriorityID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketPriorityID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketPriorityObject = new SWIFT_TicketPriority($_ticketPriorityID);
        if (!$_SWIFT_TicketPriorityObject instanceof SWIFT_TicketPriority || !$_SWIFT_TicketPriorityObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_TicketPriorityObject->Update($_POST['title'], $_POST['displayorder'],
                    IIF($_POST['type'] == '1', SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['frcolorcode'], $_POST['bgcolorcode'],
                    $_POST['uservisibilitycustom'], $this->_GetAssignedUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateticketpriority'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $_ticketPriorityID, $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ticketPriorityID);

        return false;
    }
}
