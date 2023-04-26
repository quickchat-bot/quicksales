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

namespace Tickets\Library\FollowUp;

use SWIFT;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;

/**
 * The Follow-Up Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketFollowUpManager extends SWIFT_Library {
    const FOLLOWUP_ITEMS_LIMIT = 1000;

    /**
     * Execute the Pending Ticket Follow-Ups
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ExecutePending()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');
        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');

        $_ticketFollowUpContainer = $_ticketFollowUpIDList = $_ticketIDList = $_staffIDList = $_ticketObjectContainer = $_staffObjectContainer = array();
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketfollowups WHERE executiondateline <= '" . DATENOW . "'", self::FOLLOWUP_ITEMS_LIMIT);
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketFollowUpContainer[$_SWIFT->Database->Record['ticketfollowupid']] = new SWIFT_TicketFollowUp(new SWIFT_DataStore($_SWIFT->Database->Record));

            $_ticketFollowUpIDList[] = (int) ($_SWIFT->Database->Record['ticketfollowupid']);

            if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList))
            {
                $_ticketIDList[] = (int) ($_SWIFT->Database->Record['ticketid']);
            }

            if (!in_array($_SWIFT->Database->Record['staffid'], $_staffIDList))
            {
                $_staffIDList[] = (int) ($_SWIFT->Database->Record['staffid']);
            }
        }

        if (!count($_ticketFollowUpContainer))
        {
            return false;
        }

        // Load the Ticket Objects
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")", self::FOLLOWUP_ITEMS_LIMIT);
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        // Load the Staff Objects
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ")", self::FOLLOWUP_ITEMS_LIMIT);
        while ($_SWIFT->Database->NextRecord())
        {
            $_staffObjectContainer[$_SWIFT->Database->Record['staffid']] = new SWIFT_Staff(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        // Now that we have loaded the ticket objects, we need to execute the follow-up actions one by one
        foreach ($_ticketFollowUpContainer as $_ticketFollowUpID => $_SWIFT_TicketFollowUpObject)
        {
            if (!isset($_ticketObjectContainer[$_SWIFT_TicketFollowUpObject->GetProperty('ticketid')]))
            {
                continue;
            }

            $_SWIFT_TicketObject = $_ticketObjectContainer[$_SWIFT_TicketFollowUpObject->GetProperty('ticketid')];

             if (!isset($_staffObjectContainer[$_SWIFT_TicketFollowUpObject->GetProperty('staffid')]))
            {
                continue;
            }
            $_SWIFT_StaffObject = $_staffObjectContainer[$_SWIFT_TicketFollowUpObject->GetProperty('staffid')];

            // Do we need to change the ticket properties?
            if ($_SWIFT_TicketFollowUpObject->GetProperty('dochangeproperties') == '1')
            {
                if ($_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid') != '-1' &&
                    (($_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid') == '0' || !empty($_staffCache[$_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid')]))))
                {
                    $_SWIFT_TicketObject->SetOwner($_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid'));
                }

                if ($_SWIFT_TicketFollowUpObject->GetProperty('departmentid') != '-1' &&
                        (isset($_departmentCache[$_SWIFT_TicketFollowUpObject->GetProperty('departmentid')])))
                {
                    $_SWIFT_TicketObject->SetDepartment($_SWIFT_TicketFollowUpObject->GetProperty('departmentid'));
                }

                if ($_SWIFT_TicketFollowUpObject->GetProperty('ticketstatusid') != '-1' &&
                        (isset($_ticketStatusCache[$_SWIFT_TicketFollowUpObject->GetProperty('ticketstatusid')])))
                {
                    $_SWIFT_TicketObject->SetStatus($_SWIFT_TicketFollowUpObject->GetProperty('ticketstatusid'));
                }

                if ($_SWIFT_TicketFollowUpObject->GetProperty('tickettypeid') != '-1' &&
                        (isset($_ticketTypeCache[$_SWIFT_TicketFollowUpObject->GetProperty('tickettypeid')])))
                {
                    $_SWIFT_TicketObject->SetType($_SWIFT_TicketFollowUpObject->GetProperty('tickettypeid'));
                }

                if ($_SWIFT_TicketFollowUpObject->GetProperty('priorityid') != '-1' &&
                        (isset($_ticketPriorityCache[$_SWIFT_TicketFollowUpObject->GetProperty('priorityid')])))
                {
                    $_SWIFT_TicketObject->SetPriority($_SWIFT_TicketFollowUpObject->GetProperty('priorityid'));
                }
            }

            // Do we need to update the due timelines?
            if ($_SWIFT_TicketFollowUpObject->GetProperty('dochangeduedateline') == '1')
            {
                if ($_SWIFT_TicketFollowUpObject->GetProperty('duedateline') != '-1')
                {
                    $_SWIFT_TicketObject->SetDue($_SWIFT_TicketFollowUpObject->GetProperty('duedateline'));
                }

                if ($_SWIFT_TicketFollowUpObject->GetProperty('resolutionduedateline') != '-1')
                {
                    $_SWIFT_TicketObject->SetResolutionDue($_SWIFT_TicketFollowUpObject->GetProperty('resolutionduedateline'));
                }
            }

            // Any billing entries?
            if ($_SWIFT_TicketFollowUpObject->GetProperty('timeworked') != '0' || $_SWIFT_TicketFollowUpObject->GetProperty('timebillable') != '0')
            {
                // Create the time worked entry
                $_SWIFT_TicketTimeTrackObject = SWIFT_TicketTimeTrack::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject,
                        $_SWIFT_TicketFollowUpObject->GetProperty('timeworked'), $_SWIFT_TicketFollowUpObject->GetProperty('timebillable'),
                        1, '', $_SWIFT_StaffObject, DATENOW, DATENOW);
            }

            // We need to create a note?
            if ($_SWIFT_TicketFollowUpObject->GetProperty('donote') == '1')
            {
                $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1676 When a follow-up creates a note, the note shows the wrong name.
                 *
                 * Comments: Added last argument to send the staff object directly
                 */

                $_SWIFT_TicketObject->CreateNote($_SWIFT_UserObject, $_SWIFT_TicketFollowUpObject->GetProperty('ticketnotes'),
                        $_SWIFT_TicketFollowUpObject->GetProperty('notecolor'), $_SWIFT_TicketFollowUpObject->GetProperty('notetype'), false, $_SWIFT_StaffObject);

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1732 Notification for 'Ticket Note' does not work in case added as ticket follow-up
                 *
                 */

                // We need to send notification?
                $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');
                $_staffID = $_SWIFT_TicketFollowUpObject->GetProperty('staffid');

                if (isset($_staffCache[$_staffID])) {
                    $_staffName = $_staffCache[$_staffID]['fullname'];
                    $_staffEmail = $_staffCache[$_staffID]['email'];

                    $_SWIFT_TicketObject->SetWatcherProperties($_staffName, sprintf($_SWIFT->Language->Get('watcherprefix'), $_staffName, $_staffEmail) . SWIFT_CRLF .$_SWIFT_TicketFollowUpObject->GetProperty('ticketnotes'));
                }
            }

            // We need to create a reply?
            if ($_SWIFT_TicketFollowUpObject->GetProperty('doreply') == '1' && $_SWIFT_TicketFollowUpObject->GetProperty('replycontents') != '')
            {
                SWIFT_TicketPost::CreateStaff($_SWIFT_TicketObject, $_SWIFT_StaffObject, SWIFT_Ticket::CREATIONMODE_STAFFCP,
                        $_SWIFT_TicketFollowUpObject->GetProperty('replycontents'), $_SWIFT_TicketObject->GetProperty('subject'));
            }

            // We need to forward the ticket?
            if ($_SWIFT_TicketFollowUpObject->GetProperty('doforward') == '1' && $_SWIFT_TicketFollowUpObject->GetProperty('forwardcontents') != '' &&
                    $_SWIFT_TicketFollowUpObject->GetProperty('forwardemailto') != '' && IsEmailValid($_SWIFT_TicketFollowUpObject->GetProperty('forwardemailto')))
            {
                /**
                 * Bug Fix : Saloni Dhall
                 *
                 * SWIFT-2440 : Help desk should not send follow up emails to the CC users of a ticket.
                 *
                 * Comments : Added $_isSendToCcBcc argument, preventing from sending emails to CC or BCC users.
                 */
                $_isSendToCcBcc = false;
                SWIFT_TicketPost::CreateForward($_SWIFT_TicketObject, $_SWIFT_StaffObject, SWIFT_Ticket::CREATIONMODE_STAFFCP,
                        $_SWIFT_TicketFollowUpObject->GetProperty('forwardcontents'), $_SWIFT_TicketObject->GetProperty('subject'),
                        $_SWIFT_TicketFollowUpObject->GetProperty('forwardemailto'), false, null, '', false, $_isSendToCcBcc);
            }

        }

        // Delete the follow ups
        SWIFT_TicketFollowUp::DeleteList($_ticketFollowUpIDList);

        // Rebuild the properties
        foreach ($_ticketObjectContainer as $_SWIFT_TicketObject)
        {
            $_SWIFT_TicketObject->ProcessUpdatePool();
            $_SWIFT_TicketObject->RebuildProperties();
        }
        SWIFT_TicketManager::RebuildCache();

        return true;
    }
}
