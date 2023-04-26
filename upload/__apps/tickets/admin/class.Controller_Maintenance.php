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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_DataStore;
use SWIFT_Exception;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Ticket Maintenance Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Maintenance $View
 * @author Varun Shoor
 */
class Controller_Maintenance extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('tickets');
        $this->Language->Load('admin_tmaintenance');
    }

    /**
     * Render the Maintenance Tabs
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('maintenance'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanrunmaintenance') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Re-Index the Ticket Posts
     *
     * @author Varun Shoor
     * @param int $_postsPerPass Number of Posts to Process in a Single Pass
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReIndex($_postsPerPass, $_totalTicketPosts = 0, $_startTime = 0, $_processCount = 0, $_firstpass = 1)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $bPerformDbOps = ($_firstpass == 1) ? false : true;

        if (!is_numeric($_postsPerPass) || ($_postsPerPass) <= 0)
        {
            $_postsPerPass = 100;
        }

        if (!is_numeric($_totalTicketPosts)) {
            $_totalTicketPosts = 0;
        }

        if (empty($_totalTicketPosts))
        {
            $_totalTicketPosts = SWIFT_TicketPost::GetPostCount();
            $_startTime       = DATENOW;

            /**
             * BUG FIX - Bishwanath Jha
             *
             * SWIFT-4510 : Rebuilding tickets search index creates duplicate entries in swsearchindex table which affects front-end search performance.
             *
             * Comment- Silly Logical mistake(Rebuild Index always added total no of(ticketpost) record into searchindex table which cause duplicate data) fixed.
             */
            if (!$bPerformDbOps) {
                $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();
                $_SWIFT_SearchEngineObject->DeleteAll(SWIFT_SearchEngine::TYPE_TICKET);
            }
        } else {
            $_startTime = ($_startTime);
        }

        // Cap in case pass size is greater than total count
        if ($_postsPerPass > $_totalTicketPosts)
        {
            $_postsPerPass = $_totalTicketPosts;
        }

        $_processCount = ($_processCount);
        if (empty($_processCount))
        {
            $_processCount = 0;
        }

        // Process this chunk of posts in an iterative fashion; don't load them
        // into memory and then process.
        if ($bPerformDbOps)
        {
            $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();

            $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketposts ORDER BY ticketpostid ASC", $_postsPerPass, $_processCount);

            while ($this->Database->NextRecord())
            {
                $_SWIFT_SearchEngineObject->Insert($this->Database->Record['ticketid'], $this->Database->Record['ticketpostid'], SWIFT_SearchEngine::TYPE_TICKET, $this->Database->Record['contents']);
                $_processCount++;
            }
        }

        $_percent = 100;

        if (0 < $_totalTicketPosts)
        {
            $_percent = floor(($_processCount * 100) / $_totalTicketPosts);
        }

        $_postsRemaining  = ($_totalTicketPosts - $_processCount);
        $_averagePostTime = 0;

        if (0 < $_processCount)
        {
            $_averagePostTime = ((DATENOW - $_startTime) / $_processCount);
        }

        $_timeRemaining = ($_postsRemaining * $_averagePostTime);

        $_redirectURL = false;

        if ($_percent <= 100 && ($_processCount < $_totalTicketPosts))
        {
            $_redirectURL = SWIFT::Get('basename') . '/Tickets/Maintenance/ReIndex/' . ($_postsPerPass) . '/' . ($_totalTicketPosts) . '/' . ($_startTime) . '/' . ($_processCount) . '/0';
        }

        $this->View->RenderReIndexData($_percent, $_redirectURL, $_processCount, $_totalTicketPosts, $_startTime, $_timeRemaining);

        return true;
    }

    /**
     * Re-Index the Ticket Properties
     *
     * @author Varun Shoor
     * @param int $_ticketsPerPass Number of Tickets to Process in a Single Pass
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReIndexProperties($_ticketsPerPass, $_totalTickets = 0, $_startTime = 0, $_processCount = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_ticketsPerPass) || ($_ticketsPerPass) <= 0)
        {
            $_ticketsPerPass = 100;
        } else {
            $_ticketsPerPass = ($_ticketsPerPass);
        }

        if (is_numeric($_totalTickets))
        {
            $_totalTickets = ($_totalTickets);
        } else {
            $_totalTickets = 0;
        }

        if (empty($_totalTickets))
        {
            $_totalTickets = SWIFT_Ticket::GetTicketCount();
            $_startTime       = DATENOW;

        } else {
            $_startTime = ($_startTime);
        }

        // Cap in case pass size is greater than total count
        if ($_ticketsPerPass > $_totalTickets)
        {
            $_ticketsPerPass = $_totalTickets;
        }

        $_processCount = ($_processCount);
        if (empty($_processCount))
        {
            $_processCount = 0;
        }

        $_ticketsContainer = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets ORDER BY ticketid ASC", $_ticketsPerPass, $_processCount);
        while ($this->Database->NextRecord())
        {
            $_ticketsContainer[$this->Database->Record['ticketid']] = $this->Database->Record;
        }

        foreach ($_ticketsContainer as $_ticketID => $_ticket) {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_ticket));
            if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception('Invalid Ticket Object');
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_TicketObject->RebuildProperties();

            $_processCount++;
        }

        unset($_ticketsContainer);

        $_percent = 100;

        if (0 < $_totalTickets)
        {
            $_percent = floor(($_processCount * 100) / $_totalTickets);
        }

        $_ticketsRemaining  = ($_totalTickets - $_processCount);
        $_averageTicketTime = 0;

        if (0 < $_processCount)
        {
            $_averageTicketTime = ((DATENOW - $_startTime) / $_processCount);
        }

        $_timeRemaining = ($_ticketsRemaining * $_averageTicketTime);

        $_redirectURL = false;

        if ($_percent <= 100 && ($_processCount < $_totalTickets))
        {
            $_redirectURL = SWIFT::Get('basename') . '/Tickets/Maintenance/ReIndexProperties/' . ($_ticketsPerPass) . '/' . ($_totalTickets) . '/' . ($_startTime) . '/' . ($_processCount);
        }

        $this->View->RenderReIndexPropertiesData($_percent, $_redirectURL, $_processCount, $_totalTickets, $_startTime, $_timeRemaining);

        return true;
    }
}
