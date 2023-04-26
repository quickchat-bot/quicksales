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

namespace Tickets\Cron;

use Controller_cron;
use SWIFT;
use Tickets\Library\AutoClose\SWIFT_AutoCloseManager;
use SWIFT_Cron;
use SWIFT_CronLog;
use Tickets\Library\Escalation\SWIFT_EscalationRuleManager;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\FollowUp\SWIFT_TicketFollowUpManager;
use Tickets\Models\Lock\SWIFT_TicketPostLock;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;

/**
 * The Minute Controller
 *
 * @author Varun Shoor
 */
class Controller_TicketsMinute extends Controller_cron
{
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
        $this->Language->Load('tickets');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * The Follow-Up Executor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function FollowUp()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_TicketFollowUpManager::ExecutePending();

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual excution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('ticketfollowup');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        return true;
    }

    /**
     * The GeneralTasks Executor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GeneralTasks()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Run Escalation Rules
        $_SWIFT_EscalationRuleManagerObject = new SWIFT_EscalationRuleManager();
        $_SWIFT_EscalationRuleManagerObject->Run();

        // Run Recurrence
        SWIFT_TicketRecurrence::Execute();

        // Cleanup
        SWIFT_TicketPostLock::Cleanup();

        return true;
    }

    /**
     * Runs the Ticket Auto Close Routines
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AutoClose()
    {
        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Execute the Ticket Auto Close Routines
        SWIFT_AutoCloseManager::ExecutePending();
        SWIFT_AutoCloseManager::ExecuteClosure();

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual excution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('ticketautoclose');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        return true;
    }
}
