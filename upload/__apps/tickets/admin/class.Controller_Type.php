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
use Tickets\Models\Type\SWIFT_TicketType;

/**
 * The Ticket Type Controller
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @property Controller_Type $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Type $View
 * @author Varun Shoor
 */
class Controller_Type extends Controller_admin
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

        $this->Language->Load('admin_tickettypes');
    }

    /**
     * Resort the ticket types
     *
     * @author Varun Shoor
     * @param mixed $_ticketTypeIDSortList The Ticket Type ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function SortList($_ticketTypeIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatetype') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_TicketType::UpdateDisplayOrderList($_ticketTypeIDSortList);

        return true;
    }

    /**
     * Delete the Ticket Types from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketTypeIDList The Ticket Type ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketTypeIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeletetypes') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_masterTicketTypeIDList = array();
        $_masterDeleteFinalText = '';

        if (_is_array($_ticketTypeIDList)) {
            $_finalTicketTypeIDList = array();

            $_masterDeleteIndex = 1;

            $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."tickettypes WHERE tickettypeid IN (" . BuildIN($_ticketTypeIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == 1)
                {
                    $_masterDeleteFinalText .= $_masterDeleteIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';
                    $_masterTicketTypeIDList[] = (int) ($_SWIFT->Database->Record['tickettypeid']);

                    $_masterDeleteIndex++;
                } else {
                    $_finalTicketTypeIDList[] = (int) ($_SWIFT->Database->Record['tickettypeid']);

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletetickettype'),
                            htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                            SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            }

            SWIFT_TicketType::DeleteList($_finalTicketTypeIDList);
        }

        if (count($_masterTicketTypeIDList))
        {
            SWIFT::Alert(sprintf($_SWIFT->Language->Get('titlemastertypedelete'), count($_masterTicketTypeIDList)),
                    $_SWIFT->Language->Get('msgmastertypedelete') . '<BR />' . $_masterDeleteFinalText);
        }

        return true;
    }

    /**
     * Delete the Given Ticket Type ID
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketTypeID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketTypeID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket Type Grid
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('managetypes'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewtypes') == '0')
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

        if (trim($_POST['title']) == '' || trim($_POST['displayorder']) == '')
        {
            $this->UserInterface->CheckFields('title', 'displayorder');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninserttype') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdatetype') == '0')) {
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
     * Insert a new Ticket Type
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('inserttype'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninserttype') == '0')
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


        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        $_finalText = '<b>' . $this->Language->Get('typetitle') . ':</b> ' . IIF(!empty($_POST['displayicon']), '<img src="' .
                str_replace('{$themepath}', SWIFT::Get('themepath') . 'images/', $_POST['displayicon']) . '" align="absmiddle" border="0" /> ') .
                htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int) ($_POST['displayorder']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('typevisibility2') . ':</b> ' . IIF($_POST['type'] == true, $this->Language->Get('public'),
                $this->Language->Get('private')) . '<br />';

        if (isset($_departmentCache[$_POST['departmentid']]))
        {
            $_finalText .= '<b>' . $this->Language->Get('typedepartment') . ':</b> ' .
                text_to_html_entities($_departmentCache[$_POST['departmentid']]['title']) . '<br />';
        } else {
            $_finalText .= '<b>' . $this->Language->Get('typedepartment') . ':</b> ' . $this->Language->Get('typealldep') . '<br />';
        }

        SWIFT::Info(sprintf($this->Language->Get('titletickettype' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgtickettype' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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
            $_ticketTypeID = SWIFT_TicketType::Create($_POST['title'], $_POST['displayicon'], $_POST['displayorder'],
                    IIF($_POST['type'] == 1, SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['departmentid'], $_POST['uservisibilitycustom'],
                    $this->_GetAssignedUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinserttickettype'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_ticketTypeID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $_ticketTypeID, $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Ticket Type ID
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketTypeObject = new SWIFT_TicketType($_ticketTypeID);
        if (!$_SWIFT_TicketTypeObject instanceof SWIFT_TicketType || !$_SWIFT_TicketTypeObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('edittype'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatetype') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketTypeObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketTypeID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketTypeObject = new SWIFT_TicketType($_ticketTypeID);
        if (!$_SWIFT_TicketTypeObject instanceof SWIFT_TicketType || !$_SWIFT_TicketTypeObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_POST['displayicon'] = SWIFT_UserInterface::GetIconURL('displayicon');
            $_updateResult = $_SWIFT_TicketTypeObject->Update($_POST['title'], $_POST['displayicon'],
                    $_POST['displayorder'], IIF($_POST['type'] == 1, SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['departmentid'],
                    $_POST['uservisibilitycustom'], $this->_GetAssignedUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatetickettype'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $_ticketTypeID, $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ticketTypeID);

        return false;
    }
}
