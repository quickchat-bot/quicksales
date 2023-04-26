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

use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\CustomField\SWIFT_CustomFieldLink;
use Base\Models\CustomField\SWIFT_CustomFieldValue;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Library\HTML\SWIFT_HTML;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Link\SWIFT_TicketLinkChain;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Lock\SWIFT_TicketPostLock;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Library\Split\SWIFT_TicketSplitManager;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Tickets\Models\Watcher\SWIFT_TicketWatcher;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;

trait Controller_TicketReplyTrait
{
    /**
     * SUBMISSION: General Tab
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
    public function GeneralSubmit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        $_SWIFT_TicketObject->SetOldTicketProperties();

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        /*
         * BUG FIX - Nidhi Gupta
         *
         * SWIFT-839: Permission to restrict staff from moving tickets to departments they're not assigned to
         *
         * Comments: Added check for staff to move tickets in unassigned departments
         */
        // Update Properties
        if ($_SWIFT->Staff->GetPermission('staff_tcanchangeunassigneddepartment') == '0') {
            if ($_SWIFT_TicketObject->Get('departmentid') != $_POST['gendepartmentid'] && !in_array($_POST['gendepartmentid'], $_SWIFT->Staff->GetAssignedDepartments())) {
                $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
                $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
                $this->UserInterface->Footer();

                return false;
            }
// @codeCoverageIgnoreStart
// this code will never be executed
        }

// @codeCoverageIgnoreEnd
        // Process Tags
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0')
        {
            SWIFT_Tag::Process(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(),
                    SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
        }

        if (isset($_POST['gendepartmentid']) && !empty($_POST['gendepartmentid'])) {
            $_SWIFT_TicketObject->SetDepartment($_POST['gendepartmentid']);
        }

        if (isset($_POST['genownerstaffid'])) {
            $_SWIFT_TicketObject->SetOwner($_POST['genownerstaffid']);
        }

        if (isset($_POST['gentickettypeid']) && !empty($_POST['gentickettypeid'])) {
            $_SWIFT_TicketObject->SetType($_POST['gentickettypeid']);
        }

        if (isset($_POST['genticketstatusid']) && !empty($_POST['genticketstatusid'])) {
            $_SWIFT_TicketObject->SetStatus($_POST['genticketstatusid']);
        }

        if (isset($_POST['genticketpriorityid']) && !empty($_POST['genticketpriorityid'])) {
            $_SWIFT_TicketObject->SetPriority($_POST['genticketpriorityid']);
        }

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        // Begin Hook: staff_ticket_general
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_general')) ? eval($_hookCode) : false;
        // End Hook

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetTicketViewObject($_departmentID);

        $_nextTicketID = false;
        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $_nextTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'next', $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        // Does the new department belong to this staff? if not, we need to jump him back to list!
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (isset($_POST['gendepartmentid']) && !empty($_POST['gendepartmentid']) && !in_array($_POST['gendepartmentid'], $_assignedDepartmentIDList)) {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

            return true;
        }

        if ($_SWIFT_TicketObject->GetProperty('isresolved') == '1' && $_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TICKET)
        {
            $GLOBALS['_POST'] = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
            /**
             * BUG FIX: Simaranjit Singh <simaranjit.singh@kayako.com>
             *
             * SWIFT-3087: In Internet Explorer, Tickets View page gets garbled when ticket is move to Resolved status.
             *
             */
            $GLOBALS['_POST']['isajax'] = true;
            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
        } else if ($_SWIFT_TicketObject->GetProperty('isresolved') == '1' && $_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST) {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

        } else if ($_SWIFT_TicketObject->GetProperty('isresolved') == '1' && $_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TOPTICKETLIST) {
            $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);

        } else if ($_SWIFT_TicketObject->GetProperty('isresolved') == '1' && $_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        } else {
            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-4708 - Resolution due is set incorrect after staff reply.
             */
            $GLOBALS['_POST']           = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
            $GLOBALS['_POST']['isajax'] = true;

            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
        }

        return true;
    }


    /**
     * Flag a Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketFlagType The Flag Type
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Flag($_ticketID, $_ticketFlagType, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;

        }

        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        $_ticketFlagContainer = $_SWIFT_TicketFlagObject->GetFlagContainer();
        if (!isset($_ticketFlagContainer[$_ticketFlagType]))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketObject->SetFlag($_ticketFlagType);

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Ticket Jump Processor (Prev/Next)
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
    public function Jump($_jumpType, $_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        if ($_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0') {
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

        if ($_jumpType === 'next')
        {
            $_jumpTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'next', $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        } else {
            $_jumpTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'previous', $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        if (!empty($_jumpTicketID))
        {
            $this->Load->Method('View', $_jumpTicketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
        } else {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        return true;
    }

    /**
     * Removes ticketreplylock
     *
     * @param int $_ticketID
     * @param string $_listType
     * @param int $_departmentID
     * @param int $_ticketStatusID
     * @param int $_ticketTypeID
     * @param int $_ticketLimitOffset
     * @return bool
     * @throws SWIFT_Exception
     */
    public function CancelReply($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
        $_ticketLimitOffset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        SWIFT_TicketPostLock::DeleteOnTicket(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff->GetStaffID());

        $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);

        return true;
    }

    /**
     * Ticket Reply Submission Processor
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
    public function ReplySubmit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        if ($_SWIFT->Staff->GetPermission('staff_tcanreply') == '0') {
            return false;
        }

        $_emailQueueCache = $this->Cache->Get('queuecache');
        $_templateGroupCache = $this->Cache->Get('templategroupcache');

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
            if ((isset($_POST['replydepartmentid']) && !in_array($_POST['replydepartmentid'], $_SWIFT->Staff->GetAssignedDepartments()))
                || (isset($_POST['redepartmentid']) && !in_array($_POST['redepartmentid'], $_SWIFT->Staff->GetAssignedDepartments())))
            {
                $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
                $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
                $this->UserInterface->Footer();

                return false;
            }
        }

        // Check for valid attachments
        $_attachmentCheckResult = static::CheckForValidAttachments('replyattachments');
        if ($_attachmentCheckResult[0] == false && _is_array($_attachmentCheckResult[1])) {
            $_SWIFT::Error($this->Language->Get('error'), sprintf($this->Language->Get('invalidattachments'), implode(', ', $_attachmentCheckResult[1])));

            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

            return false;
        }

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetTicketViewObject($_departmentID);

        $_nextTicketID = false;
        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $_nextTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'next', $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        $this->_ProcessDispatchTab($_SWIFT_TicketObject, 'reply', true);

        $_dontSendEmail = false;
        if (!isset($_POST['optreply_sendemail']) || (isset($_POST['optreply_sendemail']) && $_POST['optreply_sendemail'] != '1'))
        {
            $_dontSendEmail = true;
        }

        $_isPrivate = false;
        if (isset($_POST['optreply_private']) && $_POST['optreply_private'] == '1')
        {
            $_dontSendEmail = true;
            $_isPrivate = true;
        }

        /**
         * BUG FIX - Werner Garcia
         * KAYAKOC-6832 - Raw HTML/XML in ticket content
         *
         * Comments - handle HTML correctly according to settings
         */
        $_isHTML = SWIFT_HTML::DetectHTMLContent($_POST['replycontents']);
        $_htmlSetting = $_SWIFT->Settings->GetString('t_ochtml');
        $_replyContents = SWIFT_TicketPost::GetParsedContents($_POST['replycontents'], $_htmlSetting, $_isHTML);
        $_replyContents = SWIFT_TicketPost::SmartReply($_SWIFT_TicketObject, $_replyContents);

        $_fromEmailAddress = self::_GetDispatchFromEmail('reply');
        $_emailQueueID = 0;
        if (isset($_POST['replyfrom']) && $_POST['replyfrom'] > 0 && isset($_emailQueueCache['list'][$_POST['replyfrom']]))
        {
            $_emailQueueID = $_POST['replyfrom'];
        }

        $_SWIFT_TicketObject->UpdateQueue($_emailQueueID);

        $_SWIFT_TicketPostObject = false;
        $_notEmpty = !empty($_replyContents) && trim($_replyContents) != '';
        if ($_notEmpty && (!isset($_POST['optreply_createasuser']) || $_POST['optreply_createasuser'] == '0'))
        {
            $_SWIFT_TicketPostObject = SWIFT_TicketPost::CreateStaff($_SWIFT_TicketObject, $_SWIFT->Staff, SWIFT_Ticket::CREATIONMODE_STAFFCP, $_replyContents,
                    '', $_dontSendEmail, $_isHTML, $_fromEmailAddress, $_isPrivate);
            $_ticketPostID = $_SWIFT_TicketPostObject->GetTicketPostID();

        } else if ($_notEmpty && isset($_POST['optreply_createasuser']) && $_POST['optreply_createasuser'] == '1') {
            $_emailQueueID = $_SWIFT_TicketObject->GetProperty('emailqueueid');

            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID] ?? false;

            $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();

            if (_is_array($_emailQueueContainer) && isset($_templateGroupCache[$_emailQueueContainer['tgroupid']]))
            {
                $_templateGroupID = $_emailQueueContainer['tgroupid'];
            }

            $_templateGroupContainer = $_templateGroupCache[$_templateGroupID];

            $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded())
            {
                $_userID = SWIFT_Ticket::GetOrCreateUserID($_SWIFT_TicketObject->GetProperty('fullname'), $_SWIFT_TicketObject->GetProperty('email'), $_templateGroupContainer['regusergroupid']);
                $_SWIFT_TicketObject->UpdateUser($_userID);
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
            }

            $_ticketPostID = SWIFT_TicketPost::CreateClient($_SWIFT_TicketObject, $_SWIFT_UserObject, SWIFT_Ticket::CREATIONMODE_STAFFCP,
                    $_replyContents, '', SWIFT_TicketPost::CREATOR_USER, $_isHTML, false, array());
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        }

        /*
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4815 Survey email should be sent after a ticket reply when closing a ticket
         * SWIFT-4937 Closing a ticket along with a reply doesn't dispatch survey email
         */
        $_ticketStatusCache = $this->Cache->Get('statuscache');

        if (isset($_ticketStatusCache[$_POST['replyticketstatusid']]) && $_ticketStatusCache[$_POST['replyticketstatusid']]['triggersurvey'] == '1') {
            $this->Load->Library('Ticket:TicketEmailDispatch', array($_SWIFT_TicketObject), true, false, 'tickets');
            $this->TicketEmailDispatch->DispatchSurvey();
        }

        SWIFT_TicketPostLock::DeleteOnTicket(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff->GetStaffID());

        if ($_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && isset($_POST['optreply_createasuser']) && $_POST['optreply_createasuser'] == '1')
        {
            $_SWIFT_TicketObject->ProcessPostAttachments($_SWIFT_TicketPostObject, 'replyattachments');
        }

        if (isset($_POST['optreply_watch']) && $_POST['optreply_watch'] == '1')
        {
            SWIFT_Ticket::Watch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        } else {
            SWIFT_Ticket::UnWatch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        }

        $this->_ProcessFollowUp($_SWIFT_TicketObject, 're');

        // Begin Hook: staff_ticket_reply
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_reply')) ? eval($_hookCode) : false;
        // End Hook

        if ($_isPrivate == false)
        {
            $_SWIFT_TicketObject->ProcessUpdatePool();
            SWIFT_TicketManager::RebuildCache();
            /**
             * BUG FIX - Ravi Sharma
             *
             * SWIFT-3333: Due time gets reapplied while replying the ticket, if Due time is already cleared from the ticket
             *
             * Comments: None.
             */
            if($_SWIFT_TicketObject->GetProperty('duetime') != '0')
            {
                $_SWIFT_TicketObject->ExecuteSLA(false, true, false);
            }
        }


        // Activity Log
        SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('log_newreply'), $_SWIFT_TicketObject->GetTicketDisplayID()),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        // Do we need to add a macro
        $_addMacro = false;
        if (isset($_POST['optreply_addmacro']) && $_POST['optreply_addmacro'] == '1' && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded())
        {
            $_addMacro = true;
        }

        // Do we need to add a KB article
        $_addKBArticle = false;
        if (isset($_POST['optreply_addkb']) && $_POST['optreply_addkb'] == '1' && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded() && SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE))
        {
            $_addKBArticle = true;
        }

        // Does the new department belong to this staff? if not, we need to jump him back to list!
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (isset($_POST['replydepartmentid']) && !empty($_POST['replydepartmentid']) && !in_array($_POST['replydepartmentid'], $_assignedDepartmentIDList)) {
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
                $GLOBALS['_POST'] = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
                $GLOBALS['_POST']['isajax'] = true;

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
                $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'inbox', -1, -1, -1, $_addKBArticle);
            } else if ($_addKBArticle) {
                $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'inbox', -1, -1, -1);
            } else {
                $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);
            }

        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            if (!empty($_nextTicketID))
            {
                $GLOBALS['_POST'] = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
                $GLOBALS['_POST']['isajax'] = true;

                $this->Load->Method('View', $_nextTicketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
            } else {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            }
        }

        return true;
    }

    /**
     * Ticket Rating Handler
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Rating($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($_POST['ratingid']) || empty($_POST['ratingid']) || !isset($_POST['ratingvalue'])) {
            return false;
        }

        if ($_SWIFT->Staff->GetPermission('staff_canviewratings') == '0' || $_SWIFT->Staff->GetPermission('staff_canupdateratings') == '0') {
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

        $_SWIFT_RatingObject = new SWIFT_Rating((int) ($_POST['ratingid']));
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_SWIFT_TicketObject->GetTicketID(), $_POST['ratingvalue'], SWIFT_RatingResult::CREATOR_STAFF, $_SWIFT->Staff->GetStaffID());

        $_SWIFT_TicketObject->MarkHasRatings();

        return true;
    }

    /**
     * Split or copy ticket submit
     *
     * @author Simaranjit Singh
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded OR If Access Denied OR If Invalid Data is Provided
     */
    public function SplitOrDuplicateSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_Ticket::LoadLanguageTable();

        $_oldTicketID     = (int) ($_POST['ticketid']);
        $_oldTicketPostID = (int) ($_POST['splitat']);
        $_operationMode   = (int) ($_POST['operationmode']);

        if (($_operationMode == SWIFT_TICKET::MODE_SPLIT && $_SWIFT->Staff->GetPermission('staff_tcansplitticket') == '0') || ($_operationMode == SWIFT_TICKET::MODE_DUPLICATE && $_SWIFT->Staff->GetPermission('staff_tcanduplicateticket') == '0')) {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }

        if ($_operationMode != SWIFT_TICKET::MODE_SPLIT && $_operationMode != SWIFT_TICKET::MODE_DUPLICATE) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_phrase = IIF($_operationMode == SWIFT_TICKET::MODE_SPLIT, 'split', 'duplicate');

        $_TicketOld = new SWIFT_Ticket(new SWIFT_DataID($_oldTicketID));

        if (!$_TicketOld instanceof SWIFT_Ticket || !$_TicketOld->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_TicketPostOld = new SWIFT_TicketPost(new SWIFT_DataID($_oldTicketPostID));

        if (!$_TicketPostOld->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        // Get the dateline for the post from which we're going to split or copy.
        $_dateline = $_TicketPostOld->Get('dateline');

        if ($_dateline > 0) {
            // Single transaction.
            $_SWIFT->Database->StartTrans();

            $_TicketNew = SWIFT_Ticket::Create($_POST['newtitle'], $_TicketOld->Get('fullname'), $_TicketOld->Get('email'), 'DeleteMe', $_TicketOld->Get('ownerstaffid'),
                $_TicketOld->Get('departmentid'), $_TicketOld->Get('ticketstatusid'), $_TicketOld->Get('priorityid'), $_TicketOld->Get('tickettypeid'),
                $_TicketOld->Get('userid'), $_SWIFT->Staff->GetStaffID(), $_TicketOld->Get('tickettype'), SWIFT_TicketAuditLog::CREATOR_STAFF,
                $_TicketOld->Get('creationmode'), '', 0, static::$_dispatchAutoResponder, $_TicketOld->Get('replyto'), $_TicketPostOld->Get('isprivate'));

            $_TicketNew->SetTemplateGroup($_TicketOld->Get('tgroupid'));

            $_newTicketID = $_TicketNew->GetTicketID();

            // Get rid of the ticket post that's created as part of creating the ticket.
            SWIFT_TicketPost::DeleteOnTicket(array($_newTicketID));

            // Find and add any additional recipients.
            $_ticketRecipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_TicketOld);

            if (_is_array($_ticketRecipientContainer)) {
                foreach ($_ticketRecipientContainer as $_recipientType => $_recipientName) {
                    SWIFT_TicketRecipient::Create($_TicketNew, $_recipientType, $_recipientName);
                }
            }

            // Set up a bulk copy.
            if ($_operationMode == SWIFT_TICKET::MODE_DUPLICATE) {
                $_ticketPostContainer = $_TicketOld->GetTicketPosts(false, false, 'ASC');

                // Copy the posts first.
                foreach ($_ticketPostContainer as $_ticketPostID => $_TicketPost) {

                    // If this post is one that we're meant to copy...
                    if ($_TicketPost->GetTicketPostID() >= $_oldTicketPostID) {
                        $_creatorType = $_TicketPost->Get('creator');

                        /**
                         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                         *
                         * SWIFT-4852 Private Staff replies are sent in emails as well as visible at Client Panel when ticket is duplicated.
                         */
                        $_newTicketPostID = SWIFT_TicketPost::Create($_TicketNew, $_TicketPost->Get('fullname'), $_TicketPost->Get('email'), $_TicketPost->Get('contents'),
                            $_creatorType, $_TicketPost->Get(IIF($_creatorType == SWIFT_Ticket::CREATOR_CLIENT, 'userid', 'staffid')), $_TicketPost->Get('creationmode'),
                            $_TicketPost->Get('subject'), $_TicketPost->Get('emailto'), $_TicketPost->Get('ishtml'), $_TicketPost->Get('isthirdparty'),
                            $_TicketPost->Get('issurveycomment'), $_TicketPost->Get('dateline'), $_TicketPost->Get('isprivate'));

                        if (!empty($_newTicketPostID)) {
                            $_ticketAttachmentContainer = $_TicketPost->RetrieveAttachments($_TicketOld);

                            $_ticketAttachmentIDList = array_keys($_ticketAttachmentContainer);

                            if (_is_array($_ticketAttachmentIDList)) {
                                $_TicketPostNew = new SWIFT_TicketPost(new SWIFT_DataID($_newTicketPostID));

                                foreach ($_ticketAttachmentIDList as $_ticketAttachmentID) {
                                    $_Attachment = new SWIFT_Attachment($_ticketAttachmentID);

                                    if ($_Attachment instanceof SWIFT_Attachment) {
                                        SWIFT_Attachment::CloneOnTicket($_TicketNew, $_TicketPostNew, $_Attachment)->SetDate($_Attachment->GetProperty('dateline'));

                                        unset($_Attachment);
                                    }
                                }

                                unset($_TicketPostNew);
                            }
                        } else {
                           // @codeCoverageIgnoreStart
                           // this code will never be executed
                            SWIFT::Error($_SWIFT->Language->Get('ticketsplitter'), $_SWIFT->Language->Get('no_such_post'));

                            $_SWIFT->Database->Rollback();

                            return false;
                           // @codeCoverageIgnoreEnd
                        }
                    }
                }

                // Retrieve ticket notes
                $_ticketNoteContainer = $_TicketOld->RetrieveNotes();

                foreach ($_ticketNoteContainer as $_ticketNoteID => $_ticketNote) {

                    // Create a copy of the note.
                    $_newTicketNoteID = SWIFT_TicketNote::Create($_TicketNew, $_ticketNote['forstaffid'], $_ticketNote['staffid'], $_ticketNote['staffname'], $_ticketNote['note'], $_ticketNote['notecolor']);

                    $_TicketNote = new SWIFT_TicketNote($_newTicketNoteID);

                    $_TicketNote->UpdatePool('dateline', $_ticketNote['dateline']);
                    $_TicketNote->ProcessUpdatePool();
                }
            } else {
                $_TicketSplitManager = new SWIFT_TicketSplitManager();
                $_TicketSplitManager->SetFrom($_oldTicketID)->SetTo($_newTicketID)->SetStartDateline($_dateline)->Split();
            }

            $_ticketWatcherContainer = SWIFT_TicketWatcher::RetrieveOnTicket($_TicketOld);

            foreach ($_ticketWatcherContainer as $_ticketWatcherStaffID => $_ticketWatcherList) {
                $_ticketWatcherDateline = $_ticketWatcherList['dateline'];

                $_StaffAsWatcher = new SWIFT_Staff(new SWIFT_DataID($_ticketWatcherStaffID));

                if (!$_StaffAsWatcher instanceof SWIFT_Staff || !$_StaffAsWatcher->GetIsClassLoaded()) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                    // @codeCoverageIgnoreEnd
                }

                $_ticketWatcherID = SWIFT_TicketWatcher::Create($_TicketNew, $_StaffAsWatcher);

                $_TicketWatcher = new SWIFT_TicketWatcher($_ticketWatcherID);

                $_TicketWatcher->UpdatePool('dateline', $_ticketWatcherDateline);
                $_TicketWatcher->ProcessUpdatePool();
            }

            // Create something in swticketauditlogs to record this split/copy.
            $_lastTicketPostContainer = $_SWIFT->Database->QueryFetch("SELECT MAX(ticketpostid) AS ticketpostid FROM " . TABLE_PREFIX . SWIFT_TicketPost::TABLE_NAME . "
                                                                       WHERE ticketid = " .  ($_oldTicketID));

            if (is_array($_lastTicketPostContainer) && !empty($_lastTicketPostContainer['ticketpostid'])) {
                $_TicketPost = new SWIFT_TicketPost(new SWIFT_DataID($_lastTicketPostContainer['ticketpostid']));

                SWIFT_TicketAuditLog::Create($_TicketOld, $_TicketPost, SWIFT_TicketAuditLog::CREATOR_STAFF, $_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'),
                    SWIFT_TicketAuditLog::ACTION_LINKTICKET, $_SWIFT->Language->Get($_phrase . '_into') . ' ' . $_TicketNew->GetTicketDisplayID(), SWIFT_TicketAuditLog::VALUE_NONE, '');
            }

            // and for the new ticket too.
            $_firstTicketPostContainer = $_SWIFT->Database->QueryFetch("SELECT MIN(ticketpostid) AS ticketpostid FROM " . TABLE_PREFIX . SWIFT_TicketPost::TABLE_NAME . "
                                                                        WHERE ticketid = " . (int) ($_newTicketID));

            if (is_array($_firstTicketPostContainer) && !empty($_firstTicketPostContainer['ticketpostid'])) {
                $_TicketPost = new SWIFT_TicketPost(new SWIFT_DataID($_firstTicketPostContainer['ticketpostid']));

                SWIFT_TicketAuditLog::Create($_TicketNew, $_TicketPost, SWIFT_TicketAuditLog::CREATOR_STAFF, $_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'),
                    SWIFT_TicketAuditLog::ACTION_LINKTICKET, $_SWIFT->Language->Get($_phrase . '_from') . ' ' . $_TicketOld->GetTicketDisplayID(), SWIFT_TicketAuditLog::VALUE_NONE, '');
            }

            // Reset all the various counts in the owning ticket objects.
            $_TicketOld->RebuildProperties();
            $_TicketNew->RebuildProperties();

            if (isset($_POST['closeold']) && $_POST['closeold'] == '1' && $_operationMode == SWIFT_TICKET::MODE_SPLIT) {
                // Close the old ticket if requested, using the first available 'resolved' status.
                $_ticketStatusCache = (array)$_SWIFT->Cache->Get('statuscache');
                $_ticketDepartment  = $_TicketOld->GetProperty('departmentid');
                /** @var array $_ticketStatus */
                foreach ($_ticketStatusCache as $_ticketStatus) {
                    if (isset($_ticketStatus['markasresolved']) && $_ticketStatus['markasresolved'] == '1') {
                        if ($_ticketStatus['departmentid'] == 0 || $_ticketStatus['departmentid'] == $_ticketDepartment) {

                            // This is available to this department, and is resolved.
                            $_TicketOld->SetStatus($_ticketStatus['ticketstatusid']);

                            break;
                        }
                    }
                }
            }

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-4501 Custom Fields are not duplicated on splitting or duplicating a ticket.
             */
            // Duplicate custom fields to new ticket
            SWIFT_CustomFieldValue::DuplicateCustomfields($_oldTicketID, $_newTicketID);

            // Duplicate the links
            SWIFT_CustomFieldLink::DuplicateLinks($_oldTicketID, $_newTicketID);

            $_SWIFT->Database->CompleteTrans();
        } else {
            SWIFT::Error($_SWIFT->Language->Get('ticketsplitter'), $_SWIFT->Language->Get('no_such_post'));
        }

        return $this->Load->Controller('Manage', APP_TICKETS)->Load->Index();
    }

    /**
     * Ability to unlink linked ticket
     *
     * @author Rahul Bhattacharya
     *
     * @param int $_ticketID
     * @param int $_linkedTicketDisplayID
     * @param int $_linkTypeID
     *
     * @return bool
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function Unlink($_ticketID, $_linkedTicketDisplayID, $_linkTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketHashContainer = $_linkedTicketHashContainer = array();

        //Fetch the chainhash list of the Ticket
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_TicketLinkChain::TABLE_NAME . "
                                  WHERE ticketid = " .  ($_ticketID) . "
                                    AND ticketlinktypeid = " .  ($_linkTypeID));

        while ($_SWIFT->Database->NextRecord()) {
            $_ticketHashContainer[] = $_SWIFT->Database->Record['chainhash'];
        }

        // Fetch the chainhash list of the linked Ticket
        $_LinkedTicket = SWIFT_Ticket::GetObjectOnID($_linkedTicketDisplayID);
        $_linkedTicketID = $_LinkedTicket->GetID();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_TicketLinkChain::TABLE_NAME . "
                                  WHERE ticketid = " .  ($_linkedTicketID) . "
                                    AND ticketlinktypeid = " .  ($_linkTypeID));

        while ($_SWIFT->Database->NextRecord()) {
            $_linkedTicketHashContainer[] = $_SWIFT->Database->Record['chainhash'];
        }

        $_chainHashContainer = array_intersect($_ticketHashContainer, $_linkedTicketHashContainer);

        if (_is_array($_chainHashContainer)) {
            $_finalTicketIDList = array($_linkedTicketID);
            $_chainHash         = end($_chainHashContainer);

            $_hashCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalrecords FROM " . TABLE_PREFIX . SWIFT_TicketLinkChain::TABLE_NAME . "
                                                         WHERE chainhash = '" . $_SWIFT->Database->Escape($_chainHash) . "'");

            if ($_hashCount['totalrecords'] <= 2) {
                $_finalTicketIDList[] = $_ticketID;
            }

            $_finalTicketLinkChainIDList = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_TicketLinkChain::TABLE_NAME . "
                                      WHERE ticketid IN (" . BuildIN($_finalTicketIDList, true) . ")
                                        AND ticketlinktypeid = " . ($_linkTypeID) . "
                                        AND chainhash = '" . $_SWIFT->Database->Escape($_chainHash) . "'");

            while ($_SWIFT->Database->NextRecord()) {
                $_finalTicketLinkChainIDList[] = $_SWIFT->Database->Record['ticketlinkchainid'];
            }

            if (_is_array($_finalTicketLinkChainIDList)) {
                SWIFT_TicketLinkChain::DeleteList($_finalTicketLinkChainIDList);
            }

            $_Ticket       = SWIFT_Ticket::GetObjectOnID($_ticketID);
            $_LinkedTicket = SWIFT_Ticket::GetObjectOnID($_linkedTicketID);

            if ($_Ticket instanceof SWIFT_Ticket && !_is_array($_Ticket->GetLinks())) {
                $_Ticket->MarkAsUnlinked();
            }

            if ($_LinkedTicket instanceof SWIFT_Ticket && !_is_array($_LinkedTicket->GetLinks())) {
                $_LinkedTicket->MarkAsUnlinked();
            }
        }

        $this->View($_ticketID, 'inbox', '-1', '-1', '-1');

        return true;
    }
}
