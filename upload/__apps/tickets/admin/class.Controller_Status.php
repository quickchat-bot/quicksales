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
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\Status\SWIFT_TicketStatus;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Status Controller
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @property Controller_Status $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Status $View
 * @author Varun Shoor
 */
class Controller_Status extends Controller_admin
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

        $this->Language->Load('admin_ticketsstatus');
    }

    /**
     * Resort the Ticket Status'es
     *
     * @author Varun Shoor
     * @param mixed $_ticketStatusIDSortList The Ticket Status ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function SortList($_ticketStatusIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatestatus') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_TicketStatus::UpdateDisplayOrderList($_ticketStatusIDSortList);

        return true;
    }

    /**
     * Delete the Ticket Status from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketStatusIDList The Ticket Status ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketStatusIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeletestatus') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_finalTicketStatusIDList = $_masterTicketStatusIDList = array();

        $_masterFinalText = '';
        $_masterIndex = 1;

        if (_is_array($_ticketStatusIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."ticketstatus WHERE ticketstatusid IN (" . BuildIN($_ticketStatusIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == 1)
                {
                    $_masterTicketStatusIDList[] = (int) ($_SWIFT->Database->Record['ticketstatusid']);

                    $_masterFinalText .= $_masterIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

                    $_masterIndex++;
                } else {
                    $_finalTicketStatusIDList[] = (int) ($_SWIFT->Database->Record['ticketstatusid']);

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activititydeleteticketstatus'),
                            htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                            SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            }

            SWIFT_TicketStatus::DeleteList($_finalTicketStatusIDList);
        }

        if (count($_masterTicketStatusIDList))
        {
            SWIFT::Alert(sprintf($_SWIFT->Language->Get('titlemasterstatusdelete'), count($_masterTicketStatusIDList)),
                    $_SWIFT->Language->Get('msgmasterstatusdelete') . '<BR />' . $_masterFinalText);
        }

        return true;
    }

    /**
     * Delete the Given Ticket Status ID
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketStatusID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketStatusID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket Status Grid
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('managestatus'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewstatus') == '0')
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

        if (trim($_POST['title']) == '' || trim($_POST['displayorder']) == '' || trim($_POST['type']) == '' || trim($_POST['statusbgcolor']) == '')
        {
            $this->UserInterface->CheckFields('title', 'displayorder', 'type', 'statusbgcolor');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (isset($_POST['statuscolor']) && !empty($_POST['statuscolor']) && !IsValidColor($_POST['statuscolor'])) {
            SWIFT::ErrorField('statuscolor');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (isset($_POST['statusbgcolor']) && !empty($_POST['statusbgcolor']) && !IsValidColor($_POST['statusbgcolor'])) {
            SWIFT::ErrorField('statusbgcolor');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertstatus') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdatestatus') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        // Any uploaded file? Check extensions...
        foreach (array('displayicon') as $_key => $_val)
        {
            $_uploadedFieldName = 'file_' . $_val;

            if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name']))
            {
                $_pathInfoContainer = pathinfo($_FILES[$_uploadedFieldName]['name']);
                $_fileExtension = mb_strtolower($_pathInfoContainer['extension']);
                if (!isset($_pathInfoContainer['extension']) || empty($_pathInfoContainer['extension']) || ($_fileExtension !== 'gif' && $_fileExtension !== 'jpeg' && $_fileExtension !== 'jpg' && $_fileExtension !== 'png'))
                {
                    SWIFT::ErrorField($_val);

                    $this->UserInterface->Error($this->Language->Get('titleinvalidfileext'), $this->Language->Get('msginvalidfileext'));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Insert a new Ticket Status
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertstatus'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertstatus') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Assigned Staff Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedStaffGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetAssignedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['staffgroupidlist']) || !_is_array($_POST['staffgroupidlist']))
        {
            return array();
        }

        $_assignedStaffGroupIDList = array();
        foreach ($_POST['staffgroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedStaffGroupIDList[] = (int) ($_key);
            }
        }

        return $_assignedStaffGroupIDList;
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

        $_departmentCache = $this->Cache->Get('departmentcache');

        $_finalText = '<b>' . $this->Language->Get('statustitle') . ':</b> <font color="' . htmlspecialchars($_POST['statuscolor']) . '">' .
                IIF(!empty($_POST['displayicon']), '<img src="' . str_replace('{$themepath}', SWIFT::Get('themepath') .
                        'images/', $_POST['displayicon']) . '" align="absmiddle" border="0" /> ') . htmlspecialchars($_POST['title']) .
                '</font><br />';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int) ($_POST['displayorder']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('statustype2') . ':</b> ' . IIF($_POST['type'] == true, $this->Language->Get('public'),
                $this->Language->Get('private')) . '<br />';

        if (isset($_departmentCache[$_POST['departmentid']]))
        {
            $_finalText .= '<b>' . $this->Language->Get('statusdep') . ':</b> ' .
                text_to_html_entities($_departmentCache[$_POST['departmentid']]['title']) . '<br />';
        } else {
            $_finalText .= '<b>' . $this->Language->Get('statusdep') . ':</b> ' . $this->Language->Get('statusalldep') . '<br />';
        }

        SWIFT::Info(sprintf($this->Language->Get('titleticketstatus' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgticketstatus' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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
            $_POST['displayicon'] = SWIFT_UserInterface::GetIconURL('displayicon');

            $_ticketStatusID = SWIFT_TicketStatus::Create($_POST['title'], $_POST['displayorder'], $_POST['markasresolved'], $_POST['displaycount'],
                    $_POST['statuscolor'], $_POST['statusbgcolor'], $_POST['departmentid'], IIF($_POST['type'] == true, SWIFT_PUBLIC, SWIFT_PRIVATE),
                    $_POST['resetduetime'], $_POST['displayicon'], $_POST['dispatchnotification'], $_POST['triggersurvey'], $_POST['staffvisibilitycustom'],
                    $this->_GetAssignedStaffGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityisnertticketstatus'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_ticketStatusID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $_ticketStatusID, $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Ticket Status ID
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketStatusID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketStatusID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketStatusObject = new SWIFT_TicketStatus($_ticketStatusID);
        if (!$_SWIFT_TicketStatusObject instanceof SWIFT_TicketStatus || !$_SWIFT_TicketStatusObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editstatus'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatestatus') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketStatusObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketStatusID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketStatusID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketStatusObject = new SWIFT_TicketStatus($_ticketStatusID);

        /*
         * Bug Fix: SWIFT-3065 Tickets stays in old department eventhough the ticket-status is moved
         *
         * Comments: Validation while changing linked department to a status.
         */
        $_linkedDepartmentID = $_SWIFT_TicketStatusObject->GetProperty('departmentid');

        if ($_POST['departmentid'] != $_linkedDepartmentID) {
            //For linked Departments
            if ($_linkedDepartmentID != 0 && $_POST['departmentid'] != 0) {
                $_result = $this->Database->QueryFetch("SELECT COUNT(*) as ticketcount FROM " . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . " 
                                    WHERE ticketstatusid = " .  ($_ticketStatusID) . " AND departmentid = " .  ($_linkedDepartmentID));
                if ($_result['ticketcount'] > 0) {
                    $this->UserInterface->Error($this->Language->Get('title_statusinuse'), $this->Language->Get('message_statusinuse_editassociation'));

                    $this->Load->Manage();

                    return false;
                }
            } else if ($_linkedDepartmentID == 0) {
                // For Non linked Departments i.e.Status exists under all departments
                $_result = $this->Database->QueryFetch("SELECT COUNT(*) as ticketcount FROM " . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . " 
                                    WHERE ticketstatusid = " .  ($_ticketStatusID) . " AND departmentid NOT IN (" . (int) ($_POST['departmentid']) . ", 0 )");

                if ($_result['ticketcount'] > 0) {
                    $this->UserInterface->Error($this->Language->Get('title_statusinuse'), $this->Language->Get('message_statusinuse_createassociation'));

                    $this->Load->Manage();

                    return false;
                }
            }
        }

        if (!$_SWIFT_TicketStatusObject instanceof SWIFT_TicketStatus || !$_SWIFT_TicketStatusObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_POST['displayicon'] = SWIFT_UserInterface::GetIconURL('displayicon');

            $_updateResult = $_SWIFT_TicketStatusObject->Update($_POST['title'], $_POST['displayorder'], $_POST['markasresolved'],
                    $_POST['displaycount'], $_POST['statuscolor'], $_POST['statusbgcolor'], $_POST['departmentid'],
                    IIF($_POST['type'] == true, SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['resetduetime'], $_POST['displayicon'],
                    $_POST['dispatchnotification'], $_POST['triggersurvey'], $_POST['staffvisibilitycustom'], $this->_GetAssignedStaffGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateticketstatus'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $_ticketStatusID, $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ticketStatusID);

        return false;
    }
}
