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

namespace Tickets\Library\Escalation;
use SWIFT;
use SWIFT_DataStore;
use Tickets\Models\Escalation\SWIFT_EscalationNotification;
use Tickets\Models\Escalation\SWIFT_EscalationRule;
use SWIFT_Exception;
use SWIFT_Library;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Library\Notification\SWIFT_TicketNotification;

/**
 * The Escalation Rule Manager Class. Handles all the execution, logging and other routines
 *
 * @author Varun Shoor
 */
class SWIFT_EscalationRuleManager extends SWIFT_Library {
    const ESCALATION_ITEMS_LIMIT = 1000;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Run the Escalation Rules
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Run()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_ticketIDList = $_ticketObjectContainer = array();

        // First retrieve all the tickets that are supposedly due
        $this->Database->QueryLimit("SELECT " . TABLE_PREFIX . "tickets.* FROM " . TABLE_PREFIX . "tickets"
            . " INNER JOIN " . TABLE_PREFIX . "slaplans ON " . TABLE_PREFIX . "tickets.slaplanid = " . TABLE_PREFIX ."slaplans.slaplanid"
            . " INNER JOIN " . TABLE_PREFIX . "escalationrules ON " . TABLE_PREFIX . "tickets.slaplanid = " . TABLE_PREFIX ."escalationrules.slaplanid"
            . " WHERE ((duetime <= '" . DATENOW . "' AND duetime != '0')"
            . " OR (resolutionduedateline <= '" . DATENOW . "' AND resolutionduedateline != '0'))"
            . " AND isescalatedvolatile = '0' AND isresolved = '0'"
            . " AND " . TABLE_PREFIX . "tickets.departmentid in (" . join(',', array_keys($_departmentCache)) . ")"
            . " AND " . TABLE_PREFIX . "tickets.slaplanid in (" . join(',', array_keys($_slaPlanCache)) . ")"
            . " AND " . TABLE_PREFIX . "slaplans.isenabled = 1"
            . " GROUP BY ticketid ORDER BY duetime DESC",
            self::ESCALATION_ITEMS_LIMIT);
        while ($this->Database->NextRecord())
        {
            $_ticketIDList[] = $this->Database->Record['ticketid'];
            $_ticketObjectContainer[$this->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));
        }

        // Now itterate through each ticket and see which escalation rule it belongs to and execute accordingly
        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject)
        {
            $_escalationRuleObjectContainer = $this->RetrieveEscalationRuleObjects($_SWIFT_TicketObject);

            foreach ($_escalationRuleObjectContainer as $_escalationRuleID => $_SWIFT_EscalationRuleObject)
            {
                // Already escalated under same rule?
                if ($_SWIFT_TicketObject->GetProperty('escalationruleid') == $_escalationRuleID) {
                    continue;
                }

                $_SWIFT_TicketObject->Escalate($_SWIFT_EscalationRuleObject);
                $_SWIFT_TicketObject->ProcessUpdatePool();

                /*
                 * BUG FIX - Mahesh Salaria
                 *
                 * SWIFT-773 SLA time ticker starts again, after Escalation is triggered on the ticket, regardless of SLA plan criteria
                 *
                 * Comments: None
                 */
                $_SWIFT_TicketObject->ExecuteSLA();
                /*
                 * BUG FIX - Ravi Sharma
                 *
                 * SWIFT-471 Audit Log does not show the SLA plan and Escalation rule applied on the ticket
                 *
                 * Comments: None
                 */
                SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null,
	                SWIFT_TicketAuditLog::ACTION_SLA,
	                sprintf($_SWIFT->Language->Get('al_escalated'), $_slaPlanCache[$_SWIFT_TicketObject->GetProperty('slaplanid')]['title']),
	                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
	                ['al_escalated', $_slaPlanCache[$_SWIFT_TicketObject->GetProperty('slaplanid')]['title']]
                );

                $_escalationNotificationContainer =  SWIFT_EscalationNotification::RetrieveOnEscalationRule($_escalationRuleID);
                foreach ($_escalationNotificationContainer as $_escalationNotificationID => $_escalationNotificationContainer) {
                    $_notificationSubject = $_escalationNotificationContainer['subject'];
                    $_notificationContents = $_escalationNotificationContainer['notificationcontents'];

                    $_notificationType = false;
                    switch ($_escalationNotificationContainer['notificationtype']) {
                        case SWIFT_EscalationNotification::TYPE_USER:
                            $_notificationType = SWIFT_TicketNotification::TYPE_USER;
                            break;

                        case SWIFT_EscalationNotification::TYPE_USERORGANIZATION:
                            $_notificationType = SWIFT_TicketNotification::TYPE_USERORGANIZATION;
                            break;

                        case SWIFT_EscalationNotification::TYPE_STAFF:
                            $_notificationType = SWIFT_TicketNotification::TYPE_STAFF;
                            break;

                        case SWIFT_EscalationNotification::TYPE_TEAM:
                            $_notificationType = SWIFT_TicketNotification::TYPE_TEAM;
                            break;

                        case SWIFT_EscalationNotification::TYPE_DEPARTMENT:
                            $_notificationType = SWIFT_TicketNotification::TYPE_DEPARTMENT;
                            break;

                        default:
                            $_notificationType = SWIFT_TicketNotification::TYPE_CUSTOM;
                            break;
                    }

                    // We add $_escalationNotificationID as a token Identifier for the notifications token cache to allow
                    // sending emails for each escalation notification rule to the same email (i.e ticket owner)
                    $_SWIFT_TicketObject->Notification->Dispatch($_notificationType, array(), $_notificationSubject,
                                                                 $_notificationContents, '', '', false, '', $_escalationNotificationID);
                }
            }
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Retrieve the associated escalation rule object with a ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function RetrieveEscalationRuleObjects(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_slaPlanCache = (array) $this->Cache->Get('slaplancache');
        $_escalationRuleCache = (array) $this->Cache->Get('escalationrulecache');

        $_escalationRuleIDList = $_escalationRuleObjectContainer = array();

        $_ticketSLAPlanID = $_SWIFT_TicketObject->GetProperty('slaplanid');
        foreach ($_escalationRuleCache as $_escalationRuleID => $_escalationRuleContainer)
        {
            /*
             * BUG FIX - Mahesh Salaria
             *
             * SWIFT-676 Escalation rule also works on completion of resolution time even if we set the Escalation Type as 'Due'
             *
             * Comments: None
             */

            // We found a escalation rule?
            if ($_escalationRuleContainer['slaplanid'] == $_ticketSLAPlanID)
            {
                if ($_escalationRuleContainer['ruletype'] == SWIFT_EscalationRule::TYPE_BOTH
                        && (($_SWIFT_TicketObject->GetProperty('duetime') <= DATENOW && $_SWIFT_TicketObject->GetProperty('duetime') != '0')
                                || ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') <= DATENOW && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0'))) {
                    $_escalationRuleIDList[] = $_escalationRuleID;
                } else if ($_escalationRuleContainer['ruletype'] == SWIFT_EscalationRule::TYPE_DUE
                        && ($_SWIFT_TicketObject->GetProperty('duetime') <= DATENOW && $_SWIFT_TicketObject->GetProperty('duetime') != '0')) {
                    $_escalationRuleIDList[] = $_escalationRuleID;

                } else if ($_escalationRuleContainer['ruletype'] == SWIFT_EscalationRule::TYPE_RESOLUTIONDUE
                        && ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') <= DATENOW && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0')) {
                    $_escalationRuleIDList[] = $_escalationRuleID;

                }
            }
        }

        if (count($_escalationRuleIDList))
        {
            foreach ($_escalationRuleIDList as $_escalationRuleID)
            {
                if (!isset($_escalationRuleCache[$_escalationRuleID]))
                {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    continue;
                    // @codeCoverageIgnoreEnd
                }

                $_escalationRuleObjectContainer[$_escalationRuleID] = new SWIFT_EscalationRule(new SWIFT_DataStore($_escalationRuleCache[$_escalationRuleID]));
            }
        }

        return $_escalationRuleObjectContainer;
    }
}
