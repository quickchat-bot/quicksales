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
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketManager;

trait Controller_TicketWatchTrait
{
    /**
     * Watch a Ticket
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
    public function Watch($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');

        $_departmentTitle = $_ticketStatusTitle = '';
        if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']);
        }

        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title']);
        }

        SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitywatchticket'),
                htmlspecialchars($_SWIFT_TicketObject->GetProperty('subject')), text_to_html_entities($_departmentTitle),
                htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT_TicketObject->GetProperty('fullname'))),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        SWIFT_Ticket::Watch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        // SWIFT-2285 Watching a ticket or adding a ticket watcher resets ticket due time

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }


    /**
     * UnWatch a Ticket
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
    public function UnWatch($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
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

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');

        $_departmentTitle = $_ticketStatusTitle = '';
        if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']);
        }

        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title']);
        }

        SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityunwatchticket'),
                htmlspecialchars($_SWIFT_TicketObject->GetProperty('subject')), text_to_html_entities($_departmentTitle),
                htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT_TicketObject->GetProperty('fullname'))),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        SWIFT_Ticket::UnWatch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        // SWIFT-2285 Watching a ticket or adding a ticket watcher resets ticket due time

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }
}
