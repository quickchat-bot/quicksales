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
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\HTML\SWIFT_HTML;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Tickets\Library\Ticket\SWIFT_TicketManager;

trait Controller_TicketFollowUpTrait
{
    /**
     * Render the FollowUp tab for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @param string $_prefix (OPTIONAL) Whether its an inline display
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function FollowUp(
        $_ticketID,
        $_listType = 'inbox',
        $_departmentID = -1,
        $_ticketStatusID = -1,
        $_ticketTypeID = -1,
        $_ticketLimitOffset = 0,
        $_prefix = ''
    ) {
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanfollowup') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        $_finalPrefix = 'fu';
        $_isInline = false;
        if (!empty($_prefix) && strlen($_prefix) == '2') {
            $_finalPrefix = $_prefix;
            $_isInline = true;
        }

        $this->View->RenderFollowUp(
            $_SWIFT_TicketObject,
            $_SWIFT_UserObject,
            $_finalPrefix,
            $_listType,
            $_departmentID,
            $_ticketStatusID,
            $_ticketTypeID,
            $_ticketLimitOffset,
            $_isInline
        );

        return true;
    }

    /**
     * Follow-Up Submission Processor
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
    public function FollowUpSubmit(
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanfollowup') == '0') {
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
            if ($_SWIFT_TicketObject->Get('departmentid') != $_POST['fudepartmentid'] && !in_array($_POST['fudepartmentid'], $_SWIFT->Staff->GetAssignedDepartments())) {
                $this->UserInterface->Header(
                    $this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'),
                    self::MENU_ID,
                    self::NAVIGATION_ID
                );
                $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
                $this->UserInterface->Footer();

                return false;
            }
        }

        $this->_ProcessFollowUp($_SWIFT_TicketObject, 'fu');

        // Begin Hook: staff_ticket_followup
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_followup')) ? eval($_hookCode) : false;
        // End Hook

        SWIFT_TicketManager::RebuildCache();

        SWIFT_TicketAuditLog::AddToLog(
            $_SWIFT_TicketObject,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            $_SWIFT->Language->Get('al_createfollowup'),
            SWIFT_TicketAuditLog::VALUE_NONE,
            0,
            '',
            0,
            '',
	        ['al_createfollowup']
        );

        // Activity Log
        SWIFT_StaffActivityLog::AddToLog(
            $_SWIFT->Language->Get('al_createfollowup'),
            SWIFT_StaffActivityLog::ACTION_UPDATE,
            SWIFT_StaffActivityLog::SECTION_TICKETS,
            SWIFT_StaffActivityLog::INTERFACE_STAFF
        );

        /**
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3151: Due time gets reset when follow up is added on ticket.
         *
         * Comments: None.
         */
        if (isset($_POST['fudochangeproperties'])) {
            $_SWIFT_TicketObject->ExecuteSLA(false, true, false);
        }

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Delete Follow-Up Processer
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketFollowUpID The Ticket FollowUp ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteFollowUp($_ticketID, $_ticketFollowUpID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcandeletefollowup') == '0') {
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

        $_SWIFT_TicketFollowUpObject = new SWIFT_TicketFollowUp(new SWIFT_DataID($_ticketFollowUpID));
        if (!$_SWIFT_TicketFollowUpObject instanceof SWIFT_TicketFollowUp || !$_SWIFT_TicketFollowUpObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_TicketFollowUpObject->Delete();

        $_SWIFT_TicketObject->RebuildProperties();

        SWIFT_TicketAuditLog::AddToLog(
            $_SWIFT_TicketObject,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            $_SWIFT->Language->Get('al_delfollowup'),
            SWIFT_TicketAuditLog::VALUE_NONE,
            0,
            '',
            0,
            '',
	        ['al_delfollowup']
        );

        $this->Load->Method('View', $_ticketID);

        return true;
    }

    /**
     * Process the follow-up POST data and create a follow up entry
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param string $_prefix The Prefix for Variables (to prevent race errors)
     * @param SWIFT_TicketFollowUp|null $_SWIFT_TicketFollowUpObject (OPTIONAL) The SWIFT_TicketFollowUp Object for Updates
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function _ProcessFollowUp(SWIFT_Ticket $_SWIFT_TicketObject, $_prefix, SWIFT_TicketFollowUp $_SWIFT_TicketFollowUpObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $idx = $_prefix . 'followuptype';
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($_POST[$idx])) {
            return false;
        }

        $_followUpDate = false;
        if ($_POST[$idx] === 'custom') {
            $_followUpDate = GetDateFieldTimestamp($_prefix . 'followupcustomvalue');
        } else {
            $_followUpDate = self::_ReturnFollowUpDate($_POST[$idx], $_POST[$_prefix . 'followupvalue']);
        }

        if (empty($_followUpDate)) {
            return false;
        }

        // Now that we have the follow up execution date, we process the properties
        $_executionDateline = $_followUpDate;

        $_doChangeProperties = false;
        $_ownerStaffID = $_departmentID = $_ticketStatusID = $_ticketTypeID = $_ticketPriorityID = 0;

        $idx = $_prefix . 'dochangeproperties';
        if (isset($_POST[$idx]) && $_POST[$idx] == 1) {
            $_doChangeProperties = true;
            $_ownerStaffID = (int) ($_POST[$_prefix . 'ownerstaffid']);
            $_departmentID = (int) ($_POST[$_prefix . 'departmentid']);
            $_ticketStatusID = (int) ($_POST[$_prefix . 'ticketstatusid']);
            $_ticketTypeID = (int) ($_POST[$_prefix . 'tickettypeid']);
            $_ticketPriorityID = (int) ($_POST[$_prefix . 'ticketpriorityid']);
        }

        $_doChangeDueDateline = false;
        $_dueDateline = $_resolutionDueDateline = 0;
        $_timeWorked = $_timeBillable = 0;

        $_doNote = false;
        $_noteType = 'ticket';
        $_noteColor = 1;
        $_ticketNotes = '';
        $idx = $_prefix . 'donote';
        if (isset($_POST[$idx]) && $_POST[$idx] == 1) {
            $_doNote = true;
            $_noteType = $_POST[$_prefix . 'notetype'];
            $_noteColor = (int) ($_POST['notecolor_' . $_prefix . 'ticketnotes']);
            $_ticketNotes = $_POST[$_prefix . 'ticketnotes'];
        }

        $_doReply = false;
        $_replyContents = '';
        $idx = $_prefix . 'doreply';
        if (isset($_POST[$idx]) && $_POST[$idx] == 1) {
            $_doReply = true;

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1508 Signatures does not append on ticket, if ticket is replied using 'Follow-Up' tab
             *
             * Comments: None
             */
            $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature(false, $_SWIFT->Staff);
            $doesNotContainHTML = !SWIFT_HTML::DetectHTMLContent($_signatureContentsDefault);
            $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature($doesNotContainHTML, $_SWIFT->Staff);

            $_replyContents            = $_POST[$_prefix . 'replycontents'];

            if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {
                $_POST[$_prefix . 'replycontents'] = htmlspecialchars_decode($_POST[$_prefix . 'replycontents']);
            }

            if (!empty($_signatureContentsDefault)) {
                $_replyContents = $_POST[$_prefix . 'replycontents'] . SWIFT_CRLF . $_signatureContentsDefault;
            }
        }

        $_doForward = false;
        $_forwardEmailTo = '';
        $_forwardContents = '';
        $idx = $_prefix . 'doforward';
        if (isset($_POST[$idx]) && $_POST[$idx] == '1') {
            $_forwardEmailToContainer = self::GetSanitizedEmailList($_prefix . 'to');

            if (_is_array($_forwardEmailToContainer)) {
                $_doForward       = true;
                $_forwardEmailTo  = $_forwardEmailToContainer[0];
                $_forwardContents = $_POST[$_prefix . 'forwardcontents'];

                if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {
                    $_forwardContents = htmlspecialchars_decode($_forwardContents);
                }
            }
        }

        if ($_SWIFT_TicketFollowUpObject instanceof SWIFT_TicketFollowUp && $_SWIFT_TicketFollowUpObject->GetIsClassLoaded()) {
            $_SWIFT_TicketFollowUpObject->Update(
                $_SWIFT_TicketObject,
                $_executionDateline,
                $_doChangeProperties,
                $_ownerStaffID,
                $_departmentID,
                $_ticketStatusID,
                $_ticketTypeID,
                $_ticketPriorityID,
                $_doChangeDueDateline,
                $_dueDateline,
                $_resolutionDueDateline,
                $_timeWorked,
                $_timeBillable,
                $_doNote,
                $_noteType,
                $_noteColor,
                $_ticketNotes,
                $_doReply,
                $_replyContents,
                $_doForward,
                $_forwardEmailTo,
                $_forwardContents,
                $_SWIFT->Staff
            );
        } else {
            SWIFT_TicketFollowUp::Create(
                $_SWIFT_TicketObject,
                $_executionDateline,
                $_doChangeProperties,
                $_ownerStaffID,
                $_departmentID,
                $_ticketStatusID,
                $_ticketTypeID,
                $_ticketPriorityID,
                $_doChangeDueDateline,
                $_dueDateline,
                $_resolutionDueDateline,
                $_timeWorked,
                $_timeBillable,
                $_doNote,
                $_noteType,
                $_noteColor,
                $_ticketNotes,
                $_doReply,
                $_replyContents,
                $_doForward,
                $_forwardEmailTo,
                $_forwardContents,
                $_SWIFT->Staff
            );
        }

        $_SWIFT_TicketObject->RebuildProperties();

        return true;
    }

    /**
     * Return a UNIX Timestamp for Follow Up
     *
     * @author Varun Shoor
     * @param string $_followUpType The Follow-Up Type
     * @param string $_followUpValue The Follow-Up Value
     * @return int|bool "true" on Success, "false" otherwise
     */
    public static function _ReturnFollowUpDate($_followUpType, $_followUpValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        switch ($_followUpType) {
            case 'minutes':
                return DATENOW + ((int) ($_followUpValue) * 60);
                break;
            case 'hours':
                return DATENOW + ((int) ($_followUpValue) * 60 * 60);
                break;
            case 'days':
                return DATENOW + ((int) ($_followUpValue) * 60 * 60 * 24);
                break;
            case 'weeks':
                return DATENOW + ((int) ($_followUpValue) * 60 * 60 * 24 * 7);
                break;
            case 'months':
                return DATENOW + ((int) ($_followUpValue) * 60 * 60 * 24 * 30);
                break;
            case 'custom':
                return strtotime($_followUpValue);
                break;
            default:
                return false;
                break;
                // @codeCoverageIgnoreStart
                // this code will never be executed
        }

        return false;
        // @codeCoverageIgnoreEnd
    }
}
