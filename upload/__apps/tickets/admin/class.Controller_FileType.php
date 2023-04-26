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
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\FileType\SWIFT_TicketFileType;

/**
 * The Ticket File Type Controller
 *
 * @method Library($_libraryName)
 * @property Controller_FileType $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_FileType $View
 * @author Varun Shoor
 */
class Controller_FileType extends Controller_admin
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

        $this->Load->Library('MIME:MIMEList');

        $this->Language->Load('admin_ticketsfiletypes');
    }

    /**
     * Delete the Ticket File Type from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketFileTypeIDList The Ticket File Type ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketFileTypeIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeletefiletypes') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketFileTypeIDList)) {
            $_SWIFT->Database->Query("SELECT extension FROM " . TABLE_PREFIX . "ticketfiletypes WHERE ticketfiletypeid IN (" .
                    BuildIN($_ticketFileTypeIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteticketfiletype'),
                        htmlspecialchars($_SWIFT->Database->Record['extension'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_TicketFileType::DeleteList($_ticketFileTypeIDList);
        }

        return true;
    }

    /**
     * Delete the Given Ticket File Type ID
     *
     * @author Varun Shoor
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketFileTypeID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketFileTypeID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket File Type Grid
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('managefiletypes'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewfiletypes') == '0')
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
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['extension']) == '' || trim($_POST['maxsize']) == '')
        {
            $this->UserInterface->CheckFields('extension', 'maxsize');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertfiletype') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdatefiletype') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_ticketFileTypeContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketfiletypes WHERE extension = '" .
                $this->Database->Escape($_POST['extension']) . "'");
        if (isset($_ticketFileTypeContainer['ticketfiletypeid']) && $_ticketFileTypeContainer['ticketfiletypeid'] != $_ticketFileTypeID)
        {
            $this->UserInterface->Error(sprintf($this->Language->Get('titlefiletypeextmismatch'),
                    htmlspecialchars($_ticketFileTypeContainer['extension'])), sprintf($this->Language->Get('msgfiletypeextmismatch'),
                            htmlspecialchars($_ticketFileTypeContainer['extension'])));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Ticket File Type
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertfiletype'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertfiletype') == '0')
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

        $_finalText = '<b>' . $this->Language->Get('extension') . ':</b> ' . htmlspecialchars($_POST['extension']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('maxsize') . ':</b> ' . SWIFT_TicketFileType::GetSize($_POST['maxsize']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('acceptsupportcenter') . ':</b> ' . IIF($_POST['acceptsupportcenter'] == '1',
                $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('acceptmailparser') . ':</b> ' . IIF($_POST['acceptmailparser'] == '1',
                $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlefiletype' . $_type), htmlspecialchars($_POST['extension'])),
                sprintf($this->Language->Get('msgfiletype' . $_type), htmlspecialchars($_POST['extension'])) . '<br />' . $_finalText);

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
            $_ticketFileTypeID = SWIFT_TicketFileType::Create($_POST['extension'], $_POST['maxsize'], $_POST['acceptsupportcenter'],
                    $_POST['acceptmailparser']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertticketfiletype'), htmlspecialchars($_POST['extension'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_ticketFileTypeID)
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
     * Edit the Ticket File Type ID
     *
     * @author Varun Shoor
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketFileTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketFileTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketFileTypeObject = new SWIFT_TicketFileType($_ticketFileTypeID);
        if (!$_SWIFT_TicketFileTypeObject instanceof SWIFT_TicketFileType || !$_SWIFT_TicketFileTypeObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editfiletype'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatefiletype') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketFileTypeObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketFileTypeID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketFileTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketFileTypeObject = new SWIFT_TicketFileType($_ticketFileTypeID);
        if (!$_SWIFT_TicketFileTypeObject instanceof SWIFT_TicketFileType || !$_SWIFT_TicketFileTypeObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketFileTypeObject->GetTicketFiletypeID()))
        {
            $_updateResult = $_SWIFT_TicketFileTypeObject->Update($_POST['extension'], $_POST['maxsize'], $_POST['acceptsupportcenter'],
                    $_POST['acceptmailparser']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateticketfiletype'), htmlspecialchars($_POST['extension'])),
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

        $this->Load->Edit($_ticketFileTypeID);

        return false;
    }
}
