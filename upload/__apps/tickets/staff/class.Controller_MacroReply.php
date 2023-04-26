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

namespace Tickets\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_StaffBase;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Models\Macro\SWIFT_MacroReply;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Macro Reply Controller
 *
 * @property Controller_MacroReply $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_MacroReply $View
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @author Varun Shoor
 */
class Controller_MacroReply extends Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_TicketViewRenderer::RenderTree('none', -1, -1, -1));

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

        if (trim($_POST['title']) === '' || trim($_POST['macrocategoryid']) === '')
        {
            $this->UserInterface->CheckFields('title', 'macrocategoryid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (!isset($_POST['replycontents']) || $_POST['replycontents'] === '') {
            SWIFT::ErrorField('urldata', 'imagedata', 'responsecontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_lscaninsertmacro') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_lscanupdatemacro') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Macro Reply
     *
     * @author Varun Shoor
     * @param int|bool $_selectedMacroCategoryID (OPTIONAL) The Selected Macro Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert($_selectedMacroCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertmacro'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertmacro') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, $_selectedMacroCategoryID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a new Macro Reply but from tickets
     *
     * @author Varun Shoor
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param bool $_addKBArticle (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertTicket(SWIFT_TicketPost $_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_addKBArticle = false)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertmacro'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertmacro') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, 0, $_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_addKBArticle);
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

        if ($_POST['macrocategoryid'] == '0')
        {
            $_parentCategoryTitle = $this->Language->Get('parentcategoryitem');
        } else {
            $_SWIFT_MacroCategoryObject = new SWIFT_MacroCategory(new SWIFT_DataID($_POST['macrocategoryid']));
            if (!$_SWIFT_MacroCategoryObject instanceof SWIFT_MacroCategory || !$_SWIFT_MacroCategoryObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $_parentCategoryTitle = $_SWIFT_MacroCategoryObject->GetProperty('title');
        }

        $_finalText = '<b>' . $this->Language->Get('macroreplytitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('parentcategory') . ':</b> ' . htmlspecialchars($_parentCategoryTitle) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlemacroreply' . $_type), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get('msgmacroreply' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {

            $_addTagsList = SWIFT_UserInterface::GetMultipleInputValues('addtags');
            $_macroReplyID = SWIFT_MacroReply::Create($_POST['macrocategoryid'], $_POST['title'], $_POST['replycontents'], $_addTagsList, $_POST['departmentid'],
                    $_POST['ownerstaffid'], $_POST['tickettypeid'], $_POST['ticketstatusid'], $_POST['ticketpriorityid'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertmacroreply'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_macroReplyID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
             }

            if ($_POST['tredir_ticketid'] != '0')
            {
                if ($_POST['tredir_addkb'] == '1')
                {
                    $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_POST['tredir_ticketpostid']));
                    $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_POST['tredir_ticketid'], $_POST['tredir_listtype'],
                            $_POST['tredir_departmentid'], $_POST['tredir_ticketstatusid'], $_POST['tredir_tickettypeid']);
                } else if ($_POST['tredir_listtype'] === 'viewticket') {
                    $this->Load->Controller('Ticket')->View($_POST['tredir_ticketid'], $_POST['tredir_listtype'], $_POST['tredir_departmentid'], $_POST['tredir_ticketstatusid'],
                            $_POST['tredir_tickettypeid']);
                } else {
                    $this->Load->Controller('Manage')->Redirect($_POST['tredir_listtype'], $_POST['tredir_departmentid'], $_POST['tredir_ticketstatusid'], $_POST['tredir_tickettypeid']);
                }

                return true;
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Controller('MacroCategory')->Manage(false, true);

            return true;
        }

        if (isset($_POST['tredir_ticketpostid']) && !empty($_POST['tredir_ticketpostid']))
        {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_POST['tredir_ticketpostid']));

            $this->Load->InsertTicket($_SWIFT_TicketPostObject, $_POST['tredir_ticketid'], $_POST['tredir_listtype'], $_POST['tredir_departmentid'],
                    $_POST['tredir_ticketstatusid'], $_POST['tredir_tickettypeid'], $_POST['tredir_addkb']);
        } else {
            $this->Load->Insert();
        }

        return false;
    }

    /**
     * Edit the Macro Reply ID
     *
     * @author Varun Shoor
     * @param int $_macroReplyID The Macro Reply ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_macroReplyID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_macroReplyID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MacroReplyObject = new SWIFT_MacroReply(new SWIFT_DataID($_macroReplyID));
        if (!$_SWIFT_MacroReplyObject instanceof SWIFT_MacroReply || !$_SWIFT_MacroReplyObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editmacro'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdatemacro') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_MacroReplyObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_macroReplyID The Macro Reply ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_macroReplyID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_macroReplyID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MacroReplyObject = new SWIFT_MacroReply(new SWIFT_DataID($_macroReplyID));
        if (!$_SWIFT_MacroReplyObject instanceof SWIFT_MacroReply || !$_SWIFT_MacroReplyObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {

            $_addTagsList = SWIFT_UserInterface::GetMultipleInputValues('addtags');
            $_updateResult = $_SWIFT_MacroReplyObject->Update($_POST['macrocategoryid'], $_POST['title'], $_POST['replycontents'], $_addTagsList, $_POST['departmentid'],
                    $_POST['ownerstaffid'], $_POST['tickettypeid'], $_POST['ticketstatusid'], $_POST['ticketpriorityid'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatemacroreply'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Controller('MacroCategory')->Manage(false, true);

            return true;
        }

        $this->Load->Edit($_macroReplyID);

        return false;
    }
}
