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
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;

trait Controller_TicketDispatchTrait
{
    /**
     * Retrieve the From Email Address for Dispatch Tab
     *
     * @author Varun Shoor
     * @param string $_tabType The Tab Type
     * @return string The From Email Address
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function _GetDispatchFromEmail($_tabType)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fromEmailAddress = '';

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');

        $idx = $_tabType . 'from';
        if (isset($_POST[$idx])) {
            if ($_POST[$idx] == '-1') {
                $_fromEmailAddress = $_SWIFT->Staff->GetProperty('email');
            } else {
                if ($_POST[$idx] == '0') {
                    $_fromEmailAddress = $_SWIFT->Settings->Get('general_returnemail');
                } else {
                    if (isset($_emailQueueCache['list'][$_POST[$idx]])) {
                        if (!empty($_emailQueueCache['list'][$_POST[$idx]]['customfromemail'])) {
                            $_fromEmailAddress = $_emailQueueCache['list'][$_POST[$idx]]['customfromemail'];
                        } else {
                            $_fromEmailAddress = $_emailQueueCache['list'][$_POST[$idx]]['email'];
                        }
                    }
                }
            }
        }

        return $_fromEmailAddress;
    }

    /**
     * Processes the common actions associated in Reply/Forward tabs.
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param string $_tabType The Tab Type
     * @param bool $_suppressSurveyEmail Forcibly suppress survey email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessDispatchTab(SWIFT_Ticket $_SWIFT_TicketObject, $_tabType, $_suppressSurveyEmail = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Process the notes
        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        $idx = $_tabType . 'ticketnotes';
        if (isset($_POST[$idx]) && trim($_POST[$idx]) != '') {
            /*
             * BUG FIX - Saloni Dhall
             *
             * SWIFT-1278 "New Ticket Note" notification does not work, when Note is added while replying the ticket
             *
             * Comments: Trigger notification event
             */
            $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');

            $_staffName = $_SWIFT->Staff->GetProperty('fullname');
            $_staffEmail = $_SWIFT->Staff->GetProperty('email');

            $_SWIFT_TicketObject->SetWatcherProperties($_staffName,
                sprintf($_SWIFT->Language->Get('watcherprefix'), $_staffName,
                    $_staffEmail) . SWIFT_CRLF . $_POST[$idx]);
            $_SWIFT_TicketObject->CreateNote($_SWIFT_UserObject, $_POST[$idx],
                $_POST['notecolor_' . $_tabType . 'ticketnotes'], $_POST[$_tabType . 'notetype'], false);
        }

        // Process Ticket Priorities
        $_departmentField = $_tabType . 'departmentid';
        $_ownerField = $_tabType . 'ownerstaffid';
        $_ticketTypeField = $_tabType . 'tickettypeid';
        $_ticketStatusField = $_tabType . 'ticketstatusid';
        $_ticketPriorityField = $_tabType . 'ticketpriorityid';

        if (isset($_POST[$_departmentField]) && $_POST[$_departmentField] != '0' &&
            $_POST[$_departmentField] != $_SWIFT_TicketObject->GetProperty('departmentid')) {
            $_SWIFT_TicketObject->SetDepartment($_POST[$_departmentField]);
        }

        if (isset($_POST[$_ownerField]) && $_POST[$_ownerField] != $_SWIFT_TicketObject->GetProperty('ownerstaffid')) {
            $_SWIFT_TicketObject->SetOwner($_POST[$_ownerField]);
        }

        if (isset($_POST[$_ticketTypeField]) && $_POST[$_ticketTypeField] != $_SWIFT_TicketObject->GetProperty('tickettypeid')) {
            $_SWIFT_TicketObject->SetType($_POST[$_ticketTypeField]);
        }

        if (isset($_POST[$_ticketStatusField]) && $_POST[$_ticketStatusField] != $_SWIFT_TicketObject->GetProperty('ticketstatusid')) {
            $_SWIFT_TicketObject->SetStatus($_POST[$_ticketStatusField], false, $_suppressSurveyEmail);
        }

        if (isset($_POST[$_ticketPriorityField]) && $_POST[$_ticketPriorityField] != $_SWIFT_TicketObject->GetProperty('priorityid')) {
            $_SWIFT_TicketObject->SetPriority($_POST[$_ticketPriorityField]);
        }

        // Update Tags
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            SWIFT_Tag::Process(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(),
                SWIFT_UserInterface::GetMultipleInputValues($_tabType . 'tags'), $_SWIFT->Staff->GetStaffID());
        }

        // Update Recipients
        SWIFT_TicketRecipient::DeleteOnTicket([$_SWIFT_TicketObject->GetTicketID()], true);

        $_ccEmailContainer = self::GetSanitizedEmailList($_tabType . 'cc');
        $_bccEmailContainer = self::GetSanitizedEmailList($_tabType . 'bcc');
        $_destinationEmailContainer = self::GetSanitizedEmailList($_tabType . 'to');
        /*
         * BUG FIX - Mansi Wason
         *
         * SWIFT-1618 If we forward a ticket to more than one e-mail address, mail will be received at first address only
         *
         * Comments: Add multiple To: address to CC: list, except first To: email address, which would be marked as it is.
         */
        array_shift($_destinationEmailContainer);

        $_ccEmailContainer = !empty($_destinationEmailContainer) ? array_merge($_ccEmailContainer,
            $_destinationEmailContainer) : $_ccEmailContainer;
        $_thirdpartyEmailContainer = [];

        $_checkboxCCEmailContainer = self::GetSanitizedEmailList($_tabType . 'cc', true);
        $_checkboxBCCEmailContainer = self::GetSanitizedEmailList($_tabType . 'bcc', true);

        $_ignoreCCEmailList = $_ignoreBCCEmailList = [];

        if (_is_array($_destinationEmailContainer) && count($_destinationEmailContainer) > 1) {
            foreach ($_destinationEmailContainer as $_key => $_emailAddress) {
                if ($_key == 0) {
                    continue;
                }

                // @codeCoverageIgnoreStart
                // this code will never be executed because of the array_merge from the bug fix
                if (!in_array($_emailAddress, $_ccEmailContainer)) {
                    if ($_tabType === 'forward' && isset($_POST['optforward_addrecipients']) && is_array($_POST['optforward_addrecipients'])
                        && in_array($_emailAddress, $_POST['optforward_addrecipients'])
                    ) {
                        $_thirdpartyEmailContainer[] = $_emailAddress;
                    } else {
                        if ($_tabType !== 'forward') {
                            $_ccEmailContainer[] = $_emailAddress;
                        }
                    }
                }
                // @codeCoverageIgnoreEnd
            }
        }

        if (_is_array($_ccEmailContainer)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_CC, $_ccEmailContainer);

            foreach ($_ccEmailContainer as $_emailAddress) {
                $_checkHash = md5('taginputcheck_' . $_tabType . 'cc' . $_emailAddress);

                // Is it an existing entry AND the user didnt check it?
                if (isset($_POST[$_checkHash]) && !in_array($_emailAddress, $_checkboxCCEmailContainer)) {
                    $_ignoreCCEmailList[] = $_emailAddress;
                }
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1100 Forwarding a ticket to more than one email adds the 2nd address or further in CC list instead of third party
         *
         */
// @codeCoverageIgnoreStart
// this code will never be executed because of the array_merge from the bug fix above
        if (_is_array($_thirdpartyEmailContainer)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_THIRDPARTY,
                $_thirdpartyEmailContainer);

            foreach ($_thirdpartyEmailContainer as $_emailAddress) {
                $_ccEmailContainer[] = $_emailAddress;
            }
        }
// @codeCoverageIgnoreEnd

        if (_is_array($_bccEmailContainer)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_BCC, $_bccEmailContainer);

            foreach ($_bccEmailContainer as $_emailAddress) {
                $_checkHash = md5('taginputcheck_' . $_tabType . 'bcc' . $_emailAddress);

                // Is it an existing entry AND the user didnt check it?
                if (isset($_POST[$_checkHash]) && !in_array($_emailAddress, $_checkboxBCCEmailContainer)) {
                    $_ignoreBCCEmailList[] = $_emailAddress;
                }
            }
        }

        SWIFT::Set('_ignoreCCEmail', $_ignoreCCEmailList);
        SWIFT::Set('_ignoreBCCEmail', $_ignoreBCCEmailList);

        // Process Options
        $_dueDateline = GetDateFieldTimestamp($_tabType . 'due');
        $_resolutionDueDateline = GetDateFieldTimestamp($_tabType . 'resolutiondue');

        // Due Dateline
        $_isDueTime = $_SWIFT_TicketObject->GetProperty('duetime') != '0';
        if (!empty($_dueDateline) && $_isDueTime && gmdate('d M Y h:i A', $_dueDateline) != gmdate('d M Y h:i A',
                $_SWIFT_TicketObject->GetProperty('duetime'))) {
            $_SWIFT_TicketObject->SetDue($_dueDateline);

            // We need to clear it?
        } else {
            if ($_tabType !== 'newticket' && empty($_dueDateline) && $_isDueTime) {
                /*
                 * BUG FIX - Bishwanath Jha
                 *
                 * SWIFT-2078: SLA plan is not applied correctly when ticket status is changed with staff reply
                 *
                 * Comments: Added this method to execute on shutdown. which allow SLA calculation first then clear overdue time.
                 */
                register_shutdown_function([$_SWIFT_TicketObject, 'ClearOverdue']);
            }
        }

        // Resolution Due Dateline
        if (!empty($_resolutionDueDateline) && gmdate('d M Y h:i A', $_resolutionDueDateline) != gmdate('d M Y h:i A',
                $_SWIFT_TicketObject->GetProperty('resolutionduedateline')) && $_SWIFT_TicketObject->GetProperty('isresolved') != '1') {
            //    $_SWIFT_TicketObject->SetResolutionDue($_resolutionDueDateline);

            // We need to clear it?
        } else {
            if ($_tabType !== 'newticket' && empty($_resolutionDueDateline) && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
                $_SWIFT_TicketObject->ClearResolutionDue();
            }
        }

        // Process Draft
        if ($_SWIFT_TicketObject->GetProperty('hasdraft') == '1') {
            SWIFT_TicketDraft::DeleteOnTicket([$_SWIFT_TicketObject->GetTicketID()]);

            $_SWIFT_TicketObject->ClearHasDraft();
        }

        // Time tracking stuff
        if (!empty($_POST[$_tabType . 'billingtimeworked']) || !empty($_POST[$_tabType . 'billingtimebillable'])) {
            // Create the time worked entry
            $_SWIFT_TicketTimeTrackObject = SWIFT_TicketTimeTrack::Create($_SWIFT_TicketObject, $_SWIFT->Staff,
                self::GetBillingTime($_POST[$_tabType . 'billingtimeworked']),
                self::GetBillingTime($_POST[$_tabType . 'billingtimebillable']),
                1, '', $_SWIFT->Staff, DATENOW, DATENOW);
        }

        return true;
    }

    /**
     * Ticket Dispatch Window
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
    public function Dispatch(
        $_ticketID,
        $_listType = 'inbox',
        $_departmentID = -1,
        $_ticketStatusID = -1,
        $_ticketTypeID = -1,
        $_ticketLimitOffset = 0
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
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

        SWIFT::Set('ticketurlsuffix',
            $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID . '/' . $_ticketLimitOffset);

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('dispatch'),
            self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderDispatchForm(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_TicketObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Ticket Dispatch Submission
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
    public function DispatchSubmit(
        $_ticketID,
        $_listType = 'inbox',
        $_departmentID = -1,
        $_ticketStatusID = -1,
        $_ticketTypeID = -1,
        $_ticketLimitOffset = 0
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
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

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetTicketViewObject($_departmentID);

        $_nextTicketID = false;
        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $_nextTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, 'next', $_listType,
                $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        if (!empty($_POST['dispatchstaffid']) && isset($_staffCache[$_POST['dispatchstaffid']])) {
            $_SWIFT_TicketObject->SetOwner($_POST['dispatchstaffid']);
        }

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-3835: Incorrect SLA plan gets linked with the ticket when ticket is moved from a resolved status to unresolved status
         *
         * Comments: Update reply due time only in case if SLA plan changed
         */
        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TICKET) {
            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID,
                $_ticketLimitOffset);
        } else {
            if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST) {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID,
                    $_ticketTypeID);

            } else {
                if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TOPTICKETLIST) {
                    $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);

                } else {
                    if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
                        if (!empty($_nextTicketID)) {
                            $this->Load->Method('View', $_nextTicketID, $_listType, $_departmentID, $_ticketStatusID,
                                $_ticketTypeID, $_ticketLimitOffset);
                        } else {
                            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID,
                                $_ticketTypeID);
                        }

                    }
                }
            }
        }
        return true;
    }
}
