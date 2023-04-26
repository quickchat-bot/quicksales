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

namespace Tickets\Console;

use Controller_console;
use SWIFT;
use SWIFT_Console;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Loader;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Library\SLA\SWIFT_SLAManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Search Engine Index Rebuild
 *
 * @author Varun Shoor
 */
class Controller_RebuildIndex extends Controller_console
{
    const DEFAULT_BATCH_SIZE      = 100;
    const MAX_BATCH_SIZE          = 1000;
    const MAX_WORKFLOW_BATCH_SIZE = 500;
    const MAX_LIMIT_BATCH         = 1500;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');
    }

    /**
     * Start the Index Rebuilding
     *
     * @author Ryan Lederman
     * @param int $_startAt (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Start($_startAt = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $c = &$this->Console;
        $s = SWIFT::GetInstance();

        $bProceed = false;

        while (!$bProceed)
        {
            $confirm = $c->Prompt('This script rebuilds the search engine index for ticket posts.  It is HIGHLY RECOMMENDED that you back up the \'' . TABLE_PREFIX . 'searchindex\' table before proceeding!  Type "CONFIRM" to proceed or "Q" to quit: ');

            if (0 == strcasecmp("confirm", $confirm))
            {
                $bProceed = true;
            }
            else if (0 == strcasecmp("q", $confirm))
            {
                $c->Message('Ciao!');
                return true;
            }
        }

        $c->Message('Please wait, counting posts...', SWIFT_Console::CONSOLE_INFO);

        $numPosts = SWIFT_TicketPost::GetPostCount();

        if (!$numPosts)
        {
            $c->Message('No ticket posts found to index; quitting.', SWIFT_Console::CONSOLE_WARNING);
            return true;
        }

        $c->Message(number_format($numPosts, 0) . ' ticket posts found.');

        while ($_startAt > $numPosts)
        {
            $c->Message('The specified start offset (' . number_format($_startAt, 0) . ') is greater than the total post count.', SWIFT_Console::CONSOLE_ERROR);

            $newStart = $c->Prompt('Please enter a start offset lower than ' . number_format($numPosts, 0) . ': ');

            $_startAt = (int) ($newStart);
        }

        $bProceed = false;
        $batchSize = 0;

        while (!$bProceed)
        {
            $c->Message('The batch size determines how many ticket posts are processed at a time.  If you experience issues with memory or timeout errors, try lowering the batch size.',
                    SWIFT_Console::CONSOLE_INFO);

            $batchSize = $c->Prompt('Enter the number of posts to process in each batch or press return to use the default size (' . self::DEFAULT_BATCH_SIZE . '): ');
            $batchSize = ('' === $batchSize) ? self::DEFAULT_BATCH_SIZE : (int) ($batchSize);

            if (0 < $batchSize && $batchSize <= self::MAX_BATCH_SIZE)
            {
                if ($batchSize > $numPosts)
                    $batchSize = $numPosts;

                $bProceed = true;
            }
        }

        $bDelete = false;

        while (true)
        {
            $shouldDelete = $c->Prompt('Type "DELETE" to delete all existing ticket post search engine records (recommended if offset = 0), or hit return to skip: ');

            $res = 0 === strcasecmp('delete', $shouldDelete);
            if ('' === $shouldDelete || $res)
            {
                $bDelete = $res;
                break;
            }
        }

        if ($bDelete)
        {
            $c->Message('Deleting all existing ticket post search engine records...');

            $se = new SWIFT_SearchEngine();
            $se->DeleteAll(SWIFT_SearchEngine::TYPE_TICKET);
        }

        $c->Message('Beginning index rebuild at offset ' . number_format($_startAt, 0) . ' with a batch size of ' . $batchSize . '...');

        $numDone   = 0;
        $startTime = 0;
        $endTime   = 0;
        $totalTime = 0;

        while (true)
        {
            // Measure the time each batch takes for better HCI
            $startTime = getmicrotime();

            if (!$s->Database->QueryLimit("SELECT ticketid, ticketpostid, contents FROM " . TABLE_PREFIX . "ticketposts ORDER BY ticketpostid ASC", $batchSize, $_startAt + $numDone))
            {
                $c->Message('Database error! Quitting!', SWIFT_Console::CONSOLE_ERROR);
            }

            $bResults = false;
            $thisBatchSize = 0;

            $se = new SWIFT_SearchEngine();

            while ($s->Database->NextRecord())
            {
                $bResults = true;
                $se->Insert($s->Database->Record['ticketid'], $s->Database->Record['ticketpostid'], SWIFT_SearchEngine::TYPE_TICKET, $s->Database->Record['contents']);
                $numDone++;
                $thisBatchSize++;
            }

            unset($se);

            $endTime = getmicrotime();
            $totalTime += ($endTime - $startTime);
            $timeString = '';

            if ($totalTime < 60)
            {
                $timeString = number_format($totalTime, 2) . ' seconds';
            }
            else if ($totalTime >= 60 && $totalTime < 3600)
            {
                $timeString = number_format(($totalTime / 60), 2) . ' minutes';
            }
            else
            {
                $timeString = number_format(($totalTime / 60 / 60), 2) . ' hours';
            }

            if ($bResults)
            {
                $c->Message($thisBatchSize . ' processed, total: ' . number_format($numDone, 0) . ' (~' . number_format((($numDone * 100) / $numPosts), 0) . ' %) in ' . number_format(($endTime - $startTime), 3) . ' seconds; total elapsed: ' . $timeString . '; mem usage: ' . number_format(memory_get_usage() / 1024, 2) . ' KiB...');
            }
            else
            {
                // All done.
                break;
            }
        }

        $c->Message('All done! Ciao!');

        return true;
    }

    /**
     * Rebuild Work Flow Links
     *
     * @author Mahesh Salaria <mahesh.salaria@kayako.com>
     * @author Nidhi Gupta <nidhi.gupta@kayako.com>
     *
     * @param int $_startAt
     *
     * @throws SWIFT_Exception
     *
     * @return bool
     */
    public function WorkFlowLinks($_startAt)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Default value is 500
        $_BatchSize   = self::MAX_WORKFLOW_BATCH_SIZE;
        $_ticketCount = 0;
        do {

            $this->Database->QueryLimit('SELECT * FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . '
                                     ORDER BY ticketid ASC', $_BatchSize, $_startAt);
            while ($this->Database->NextRecord()) {
                $_ticketCount++;

                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));
                if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    throw new SWIFT_Ticket_Exception(SWIFT_CREATEFAILED);
                    // @codeCoverageIgnoreEnd
                }

                $this->Console->Message('Adding Ticket: ' . $_SWIFT_TicketObject->GetID());

                SWIFT_Ticket::AddToWorkflowQueue($_SWIFT_TicketObject);
                unset($_SWIFT_TicketObject);
            }

            SWIFT_Ticket::ProcessWorkflowQueue();
            $_startAt += $_BatchSize;
        } while (($_ticketCount % 500) == 0 && $_ticketCount < self::MAX_LIMIT_BATCH && $_ticketCount);

        return true;
    }

    /**
     * Rebuild the ticket properties
     *
     * @author Varun Shoor
     * @param int $_startAt (OPTIONAL) The Starting Offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Properties($_startAt = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Console->WriteLine($this->Console->Green('=============================='));
        $this->Console->WriteLine($this->Console->Yellow('Rebuild Ticket Properties'));
        $this->Console->WriteLine($this->Console->Green('=============================='));

        $_totalTicketCountContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets");
        $_totalTicketCount = 0;
        if (isset($_totalTicketCountContainer['totalitems'])) {
            $_totalTicketCount = (int) ($_totalTicketCountContainer['totalitems']);
        }

        $this->Console->WriteLine();
        $this->Console->WriteLine('Total Ticket Count: ' . number_format($_totalTicketCount, 0));

        if (!$_totalTicketCount)
        {
            $this->Console->Message('No tickets found to index; quitting.', SWIFT_Console::CONSOLE_WARNING);

            return true;
        }

        while ($_startAt > $_totalTicketCount)
        {
            $this->Console->Message('The specified start offset (' . number_format($_startAt, 0) . ') is greater than the total ticket count.', SWIFT_Console::CONSOLE_ERROR);

            $_newStart = $this->Console->Prompt('Please enter a start offset lower than ' . number_format($_totalTicketCount, 0) . ': ');

            $_startAt = (int) ($_newStart);
        }

        $_proceed = false;

        $_batchSize = 0;

        while (!$_proceed)
        {
            $this->Console->Message('The batch size determines how many tickets are processed at a time. If you experience issues with memory or timeout errors, try lowering the batch size.', SWIFT_Console::CONSOLE_INFO);

            $_batchSize = $this->Console->Prompt('Enter the number of tickets to process in each batch or press return to use the default size (' . self::DEFAULT_BATCH_SIZE . '): ');
            $_batchSize = ('' === $_batchSize) ? self::DEFAULT_BATCH_SIZE : (int) ($_batchSize);

            if (0 < $_batchSize && $_batchSize <= self::MAX_BATCH_SIZE)
            {
                if ($_batchSize > $_totalTicketCount)
                    $_batchSize = $_totalTicketCount;

                $_proceed = true;
            }
        }


        $this->Console->Message('Beginning properties rebuild at offset ' . number_format($_startAt, 0) . ' with a batch size of ' . $_batchSize . '...');

        $_numDone   = 0;
        $_startTime = 0;
        $_endTime   = 0;
        $_totalTime = 0;

        while (true)
        {
            // Measure the time each batch takes for better HCI
            $_startTime = getmicrotime();

            if (!$this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets ORDER BY ticketid ASC", $_batchSize, $_startAt + $_numDone))
            {
                $this->Console->Message('Database error! Quitting!', SWIFT_Console::CONSOLE_ERROR);
            }

            $_ticketsContainer = array();

            while ($this->Database->NextRecord()) {
                $_ticketsContainer[$this->Database->Record['ticketid']] = $this->Database->Record;
            }

            $_results = false;
            $_thisBatchSize = 0;

            foreach ($_ticketsContainer as $_ticketID => $_ticket) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_ticket));
                if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    throw new SWIFT_Exception('Invalid Ticket Object');
                    // @codeCoverageIgnoreEnd
                }

                $_SWIFT_TicketObject->RebuildProperties();
                $_results = true;

                unset($_SWIFT_TicketObject);

                $_thisBatchSize++;
                $_numDone++;
            }

            unset($_ticketsContainer);

            $_endTime = getmicrotime();
            $_totalTime += ($_endTime - $_startTime);
            $_timeString = '';

            if ($_totalTime < 60)
            {
                $_timeString = number_format($_totalTime, 2) . ' seconds';
            } else if ($_totalTime >= 60 && $_totalTime < 3600) {
                $_timeString = number_format(($_totalTime / 60), 2) . ' minutes';
            } else {
                $_timeString = number_format(($_totalTime / 60 / 60), 2) . ' hours';
            }

            if ($_results)
            {
                $this->Console->Message($_thisBatchSize . ' processed, total: ' . number_format($_numDone, 0) . ' (~' . number_format((($_numDone * 100) / $_totalTicketCount), 0) . ' %) in ' . number_format(($_endTime - $_startTime), 3) . ' seconds; total elapsed: ' . $_timeString . '; mem usage: ' . number_format(memory_get_usage() / 1024, 2) . ' KiB...');
            } else {
                // All done.
                break;
            }
        }

        $this->Console->Message('All done! Ciao!');

        return true;
    }

    /**
     * Calculate SLA Response(Average)time
     *
     * @author     Nidhi Gupta <nidhi.gupta@kayako.com>
     *
     * @param int $startAt
     *
     * @throws SWIFT_Exception
     *
     * @return bool
     */
    public function CalculateResponseTime($startAt)
    {
        if (!is_numeric($startAt))
        {
            $this->Console->Message('Invalid StartAt parameter specified', SWIFT_Console::CONSOLE_WARNING);

            return true;
        }

        SWIFT_Loader::LoadLibrary('SLA:SLAManager', APP_TICKETS);

        $SLAManger = new SWIFT_SLAManager();

        $tickets     = array();
        $ticketCount = 0;

        do {
            $this->Database->QueryLimit('SELECT tickets.ticketid, tickets.slaplanid, ticketposts.slaresponsetime, ticketposts.dateline, slaplan.slaplanid FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . ' AS tickets
                                         LEFT JOIN ' . TABLE_PREFIX . 'ticketposts AS ticketposts ON (ticketposts.ticketid = tickets.ticketid)
                                         LEFT JOIN ' . TABLE_PREFIX . 'slaplans AS slaplan ON (tickets.slaplanid = slaplan. slaplanid)
                                          WHERE slaplan.isenabled = 1 AND tickets.departmentid != 0 AND tickets.averageslaresponsetime = 0 AND ticketposts.slaresponsetime = 0
                                         ORDER BY ticketposts.ticketid ASC ', self::MAX_WORKFLOW_BATCH_SIZE, $startAt);

            while ($this->Database->NextRecord()) {
                $tickets[] = $this->Database->Record;
            }

            foreach ($tickets as $ticket) {
                $ticketCount++;

                $Ticket  = new SWIFT_Ticket(new SWIFT_DataID($ticket['ticketid']));

                $SLAPlan = new SWIFT_SLA(new SWIFT_DataID($ticket['slaplanid']));

                if (!$SLAPlan instanceof SWIFT_SLA || !$SLAPlan->GetIsClassLoaded()) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                    // @codeCoverageIgnoreEnd
                }

                $startTimeline = $slaResponseTime = $endTimeline = 0;

                $this->Database->Query('SELECT ticketposts.responsetime, ticketposts.ticketpostid,ticketposts.ticketid, ticketposts.creator, ticketposts.dateline FROM ' . TABLE_PREFIX . 'ticketposts AS ticketposts
                                         WHERE ticketposts.ticketid = ' . $ticket['ticketid'] . '
                                        ORDER BY ticketposts.dateline ASC');

                while ($this->Database->NextRecord()) {

                    $TicketPost = new SWIFT_TicketPost(new SWIFT_DataID($this->Database->Record['ticketpostid']));

                    $creator  = $TicketPost->Get('creator');
                    $timeline = $TicketPost->Get('dateline');

                    // If its a ticket post by client, we will store startTimeline. If there are also repeated posts by client, we will only store the timeline of first one
                    if ($creator == SWIFT_TicketPost::CREATOR_USER) {
                        if ($startTimeline == 0) {
                            $startTimeline = $timeline;
                        } else {
                            // @codeCoverageIgnoreStart
                            // this code will never be executed
                            continue;
                            // @codeCoverageIgnoreEnd
                        }
                    }
                    // If this is a post by staff, we will calculate SLA response time and will update both ticket post and ticket (for average)
                    else if ($creator == SWIFT_TicketPost::CREATOR_STAFF) {

                        // Seems, there is no Post by client prior to the staff post
                        if ($startTimeline == 0) {
                            continue;
                        }

                        $endTimeline     = $timeline;
                        $slaResponseTime = $SLAManger->GetSLAResponseTime($SLAPlan, $startTimeline, $endTimeline);

                        $TicketPost->SetProperty('slaresponsetime', $slaResponseTime);

                        // Now we will update ticket with new average sla response time
                        $Ticket->UpdateAverageSLAResponseTime($slaResponseTime, $TicketPost->Get('ticketid'));

                        $startTimeline = 0;
                    } else {
                        continue;
                    }
                }
                $this->Console->Message('Processed Tickets : ' . $Ticket->GetID());
            }

            $startAt += self::MAX_WORKFLOW_BATCH_SIZE;
        } while (($ticketCount % self::MAX_WORKFLOW_BATCH_SIZE) == 0 && $ticketCount < self::MAX_LIMIT_BATCH && $ticketCount);

        return true;
    }
}
