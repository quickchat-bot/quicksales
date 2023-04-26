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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;

trait Controller_TicketReleaseTrait
{
    /**
     * Take a Ticket
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
    public function Take($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');

        $_departmentTitle = $_ticketStatusTitle = '';
        if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']);
        }

        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title']);
        }

        SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitytaketicket'),
                htmlspecialchars($_SWIFT_TicketObject->GetProperty('subject')), text_to_html_entities($_departmentTitle),
                htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT_TicketObject->GetProperty('fullname'))),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        $_SWIFT_TicketObject->SetOwner($_SWIFT->Staff->GetStaffID());

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-3616 Reply due time should not be reset when flagging a ticket
         *
         * Comments: Issue also persists when using "Take"
         */
        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }


    /**
     * Take a Ticket
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
    public function Surrender($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');

        $_departmentTitle = $_ticketStatusTitle = '';
        if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']);
        }

        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title']);
        }

        SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitysurrenderticket'),
                htmlspecialchars($_SWIFT_TicketObject->GetProperty('subject')), text_to_html_entities($_departmentTitle),
                htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT_TicketObject->GetProperty('fullname'))),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        $_SWIFT_TicketObject->SetOwner('0');

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-3616 Reply due time should not be reset when flagging a ticket
         *
         * Comments: Issue also persists when using "Surrender"
         */
        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Render the Release tab for this Ticket
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
    public function Release($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanrelease') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        $this->View->RenderRelease($_SWIFT_TicketObject, $_SWIFT_UserObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID,
            $_ticketLimitOffset);

        return true;
    }

    /**
     * Render the Release tab for this Ticket
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
    public function ReleaseSubmit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanrelease') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        /*
         * BUG FIX - Nidhi Gupta
         *
         * SWIFT-839: Permission to restrict staff from moving tickets to departments they're not assigned to
         *
         * Comments: Added check for staff to move tickets in unassigned departments
         */
        if ($_SWIFT->Staff->GetPermission('staff_tcanchangeunassigneddepartment') == '0') {
            if ($_SWIFT_TicketObject->Get('departmentid') != $_POST['reldepartmentid'] && !in_array($_POST['reldepartmentid'], $_SWIFT->Staff->GetAssignedDepartments())) {
                $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
                $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
                $this->UserInterface->Footer();

                return false;
            }
        }

        // Process Tags
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0')
        {
            SWIFT_Tag::Process(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(),
                    SWIFT_UserInterface::GetMultipleInputValues('reltags'), $_SWIFT->Staff->GetStaffID());
        }

        // Update Properties
        if (isset($_POST['reldepartmentid']) && !empty($_POST['reldepartmentid'])) {
            $_SWIFT_TicketObject->SetDepartment($_POST['reldepartmentid']);
        }

        if (isset($_POST['relownerstaffid'])) {
            $_SWIFT_TicketObject->SetOwner($_POST['relownerstaffid']);
        }

        if (isset($_POST['reltickettypeid']) && !empty($_POST['reltickettypeid'])) {
            $_SWIFT_TicketObject->SetType($_POST['reltickettypeid']);
        }

        if (isset($_POST['relticketstatusid']) && !empty($_POST['relticketstatusid'])) {
            $_SWIFT_TicketObject->SetStatus($_POST['relticketstatusid']);
        }

        if (isset($_POST['relticketpriorityid']) && !empty($_POST['relticketpriorityid'])) {
            $_SWIFT_TicketObject->SetPriority($_POST['relticketpriorityid']);
        }

        if (isset($_POST['releaseticketnotes']) && trim($_POST['releaseticketnotes']) != '') {
            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-738 New ticket notification does not work if the note is added from the Release tab
             *
             * Comments: Trigger notification event
             */

            $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');

            $_staffName = $_SWIFT->Staff->GetProperty('fullname');
            $_staffEmail = $_SWIFT->Staff->GetProperty('email');

            $_SWIFT_TicketObject->SetWatcherProperties($_staffName, sprintf($_SWIFT->Language->Get('watcherprefix'), $_staffName, $_staffEmail) . SWIFT_CRLF . $_POST['releaseticketnotes']);

            $_SWIFT_TicketObject->CreateNote($_SWIFT_UserObject, $_POST['releaseticketnotes'], $_POST['notecolor_releaseticketnotes'], $_POST['releasenotetype']);
        }

        $_dueDateline = GetDateFieldTimestamp('releasedue');
        $_resolutionDueDateline = GetDateFieldTimestamp('releaseresolutiondue');

        // Due Dateline
        if (!empty($_dueDateline) && gmdate('d M Y h:i A', $_dueDateline) != gmdate('d M Y h:i A', $_SWIFT_TicketObject->GetProperty('duetime'))) {
            $_SWIFT_TicketObject->SetDue($_dueDateline);

        // We need to clear it?
        } else if (empty($_dueDateline) && $_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            $_SWIFT_TicketObject->ClearOverdue();
        }

        // Resolution Due Dateline
        if (!empty($_resolutionDueDateline) && gmdate('d M Y h:i A', $_resolutionDueDateline) != gmdate('d M Y h:i A', $_SWIFT_TicketObject->GetProperty('resolutionduedateline'))) {
            $_SWIFT_TicketObject->SetResolutionDue($_resolutionDueDateline);

        // We need to clear it?
        } else if (empty($_resolutionDueDateline) && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
            $_SWIFT_TicketObject->ClearResolutionDue();
        }

        // Time tracking stuff
        if (!empty($_POST['relbillingtimeworked']) || !empty($_POST['relbillingtimebillable']))
        {
            // Create the time worked entry
            $_SWIFT_TicketTimeTrackObject = SWIFT_TicketTimeTrack::Create($_SWIFT_TicketObject, $_SWIFT->Staff,
                    self::GetBillingTime($_POST['relbillingtimeworked']), self::GetBillingTime($_POST['relbillingtimebillable']),
                    1, '', $_SWIFT->Staff, DATENOW, DATENOW);
        }

        // Begin Hook: staff_ticket_release
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_release')) ? eval($_hookCode) : false;
        // End Hook

        $_SWIFT_TicketObject->ProcessUpdatePool();
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4446 Resolved date and Creation date is identical for Resolved tickets.
         *
         * Comments: Rebuild properties method setting resolved date from last activity(which is not updated).
         */
        //$_SWIFT_TicketObject->RebuildProperties();
        SWIFT_TicketManager::RebuildCache();

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetTicketViewObject($_departmentID);

        $_nextTicketID = false;
        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $_nextTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'next', $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        // Does the new department belong to this staff? if not, we need to jump him back to list!
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (isset($_POST['resdepartmentid']) && !empty($_POST['resdepartmentid']) && !in_array($_POST['resdepartmentid'], $_assignedDepartmentIDList)) {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        } else {
            if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TICKET)
            {
                $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
            } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST) {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

            } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TOPTICKETLIST) {
                $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);

            } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
                if (!empty($_nextTicketID))
                {
                    $this->Load->Method('View', $_nextTicketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
                } else {
                    $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
                }
            }
        }

        return true;
    }
}
