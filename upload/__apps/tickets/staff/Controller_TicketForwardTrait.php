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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Tickets\Staff;

use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_TicketForward;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Library\View\SWIFT_TicketViewRenderer;

trait Controller_TicketForwardTrait
{
    /**
     * Render the Forward tab for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Forward($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanforward') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        SWIFT::Set('ticketurlsuffix', $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID . '/' . $_ticketLimitOffset);

        $this->View->RenderForward($_SWIFT_TicketObject, $_SWIFT_UserObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID,
            $_ticketLimitOffset);

        return true;
    }

    /**
     * Ticket Forward Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ForwardSubmit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanforward') == '0') {
            return false;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        /*
         * BUG FIX - Nidhi Gupta
         *
         * SWIFT-839: Permission to restrict staff from moving tickets to departments they're not assigned to
         *
         * Comments: Added check for staff to move tickets in unassigned departments
         */
        if ($_SWIFT->Staff->GetPermission('staff_tcanchangeunassigneddepartment') == '0') {
            if ((isset($_POST['forwarddepartmentid']) && !in_array($_POST['forwarddepartmentid'], $_SWIFT->Staff->GetAssignedDepartments()))
                || (isset($_POST['frdepartmentid']) && !in_array($_POST['frdepartmentid'], $_SWIFT->Staff->GetAssignedDepartments())))
            {
                $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
                $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
                $this->UserInterface->Footer();

                return false;
            }
        }

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetTicketViewObject($_departmentID);

        $_nextTicketID = false;
        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $_nextTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'next', $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        $_destinationEmailContainer = self::GetSanitizedEmailList('forwardto');
        if (!_is_array($_destinationEmailContainer))
        {
            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

            return false;
        }

        // Check for valid attachments
        $_attachmentCheckResult = static::CheckForValidAttachments('forwardattachments');
        if ($_attachmentCheckResult[0] == false && _is_array($_attachmentCheckResult[1])) {
            $_SWIFT::Error($this->Language->Get('error'), sprintf($this->Language->Get('invalidattachments'), implode(', ', $_attachmentCheckResult[1])));

            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

            return false;
        }

        $_destinationEmailAddress = $_destinationEmailContainer[0];

        $this->_ProcessDispatchTab($_SWIFT_TicketObject, 'forward');

        $_ticketRecipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_SWIFT_TicketObject);
        if ((!isset($_ticketRecipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]) ||
                (isset($_ticketRecipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]) && !in_array($_destinationEmailAddress, $_ticketRecipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY])))
                && isset($_POST['optforward_addrecipients']) && is_array($_POST['optforward_addrecipients']) && in_array($_destinationEmailAddress, $_POST['optforward_addrecipients']))
        {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_THIRDPARTY, array($_destinationEmailAddress));
        }

        // This field is not available in the forward tab
//        $_dontSendEmail = false;
//        if (isset($_POST['optforward_sendemail']) && (int)$_POST['optforward_sendemail'] === 1)
//        {
//            $_dontSendEmail = true;
//        }

        $_isPrivate = false;
        if (isset($_POST['optforward_private']) && $_POST['optforward_private'] == '1')
        {
//            $_dontSendEmail = true;
            $_isPrivate = true;
        }

        $_fromEmailAddress = self::_GetDispatchFromEmail('forward');

        /**
         * BUG FIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKO-4907 - Remove vulnerable tags from post content
         *
         * Comments - None.
         */
        $_forwardContents = removeTags($_POST['forwardcontents']);

        $_forwardSubject = removeTags($_POST['forwardsubject']);

        $_forwardContents = SWIFT_TicketPost::SmartReply($_SWIFT_TicketObject, $_forwardContents);

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_emailQueueID = 0;
        if ($_POST['forwardfrom'] > 0 && isset($_emailQueueCache['list'][$_POST['forwardfrom']]))
        {
            $_emailQueueID = $_POST['forwardfrom'];
        }

        $_SWIFT_TicketObject->UpdateQueue($_emailQueueID);

        $_SWIFT_TicketPostObject = false;
        if ($_forwardContents != '')
        {
            if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {
                $_forwardContents = htmlspecialchars_decode($_forwardContents);
            }

            /*
            * BUG FIX - Ravi Sharma
            *
            * SWIFT-3402 Edit Subject Field option should be available on ticket forwarding page.
            */
            $_ticketPostID = SWIFT_TicketPost::CreateForward($_SWIFT_TicketObject, $_SWIFT->Staff, SWIFT_Ticket::CREATIONMODE_STAFFCP, $_forwardContents, $_forwardSubject, $_destinationEmailAddress, !static::$_sendEmail, null, $_fromEmailAddress, $_isPrivate);

            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));

            SWIFT_TicketForward::CreateFromEmailList($_ticketPostID, $_destinationEmailContainer);
        }

        if (isset($_POST['optforward_watch']) && $_POST['optforward_watch'] == '1')
        {
            SWIFT_Ticket::Watch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        } else {
            SWIFT_Ticket::UnWatch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        }

        $this->_ProcessFollowUp($_SWIFT_TicketObject, 'fr');

        // Begin Hook: staff_ticket_forward
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_forward')) ? eval($_hookCode) : false;
        // End Hook

        if ($_isPrivate == false)
        {
            $_SWIFT_TicketObject->ProcessUpdatePool();
            SWIFT_TicketManager::RebuildCache();
        }


        // Activity Log
        SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('log_forward'), $_SWIFT_TicketObject->GetTicketDisplayID(), $_destinationEmailAddress),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        // Do we need to add a macro
        $_addMacro = false;
        if (isset($_POST['optforward_addmacro']) && $_POST['optforward_addmacro'] == '1' && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded())
        {
            $_addMacro = true;
        }

        // Do we need to add a KB article
        $_addKBArticle = false;
        if (isset($_POST['optforward_addkb']) && $_POST['optforward_addkb'] == '1' && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded() && SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE))
        {
            $_addKBArticle = true;
        }

        // Does the new department belong to this staff? if not, we need to jump him back to list!
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (isset($_POST['forwarddepartmentid']) && !empty($_POST['forwarddepartmentid']) && !in_array($_POST['forwarddepartmentid'], $_assignedDepartmentIDList)) {
            if ($_addMacro)
            {
                $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_addKBArticle);
            } else if ($_addKBArticle) {
                $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            } else {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            }

            return true;
        }

        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TICKET)
        {
            if ($_addMacro)
            {
                $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'viewticket', $_departmentID, $_ticketStatusID, $_ticketTypeID, $_addKBArticle);
            } else if ($_addKBArticle) {
                $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'viewticket', $_departmentID, $_ticketStatusID, $_ticketTypeID);
            } else {
                $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
            }
        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST) {
            if ($_addMacro)
            {
                $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_addKBArticle);
            } else if ($_addKBArticle) {
                $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            } else {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            }

        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TOPTICKETLIST) {
            if ($_addMacro)
            {
                $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'inbox', '-1', '-1', '-1', $_addKBArticle);
            } else if ($_addKBArticle) {
                $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'inbox', '-1', '-1', '-1');
            } else {
                $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);
            }

        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            if (!empty($_nextTicketID))
            {
                $this->Load->Method('View', $_nextTicketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
            } else {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            }

        }

        return true;
    }
}
