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

namespace Tickets\Api;

use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_XML;

/**
 * The TicketCount API Controller
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketCount extends Controller_api implements SWIFT_REST_Interface
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

        $this->Load->Library('XML:XML');
    }

    /**
     * Dispatch the ticket counts
     *
     * Example Output:
     *
     * <ticketstatuses>
     *    <ticketstatus>
     *        <id>3</id>
     *        <title>Closed</title>
     *        <displayorder>3</displayorder>
     *        <departmentid>0</departmentid>
     *        <displayicon>{$themepath}icon_ticketstatusclosed.png</displayicon>
     *        <type>private</type>
     *        <displayinmainlist>0</displayinmainlist>
     *        <markasresolved>1</markasresolved>
     *        <displaycount>0</displaycount>
     *        <statuscolor>#5f5f5f</statuscolor>
     *        <statusbgcolor>#5f5f5f</statusbgcolor>
     *        <resetduetime>0</resetduetime>
     *        <triggersurvey>1</triggersurvey>
     *        <staffvisibilitycustom>1</staffvisibilitycustom>
     *        <staffgroupid>1</staffgroupid>
     *    </ticketstatus>
     * </ticketstatuses>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketCountCache = (array) $this->Cache->Get('ticketcountcache');
        if (!isset($_ticketCountCache['departments'])) {
            $_ticketCountCache['departments'] = [];
        }

        $this->XML->AddParentTag('ticketcount');
        /**
         * Departments
         */
        $this->XML->AddParentTag('departments');
        $departments = $_ticketCountCache['departments'];
        if (_is_array($departments)) {
            foreach ($departments as $_departmentID => $_countContainer) {
                $this->XML->AddParentTag('department', array('id' => $_departmentID));
                    $this->XML->AddTag('totalitems', $_countContainer['totalitems']);
                    $this->XML->AddTag('lastactivity', $_countContainer['lastactivity']);
                    $this->XML->AddTag('totalunresolveditems', $_countContainer['totalunresolveditems']);

                    foreach ($_countContainer['ticketstatus'] as $_ticketStatusID => $_ticketStatusCountContainer) {
                        $this->XML->AddTag('ticketstatus', '', array('id' => $_ticketStatusID, 'lastactivity' => $_ticketStatusCountContainer['lastactivity'], 'totalitems' => $_ticketStatusCountContainer['totalitems']));
                    }

                    foreach ($_countContainer['tickettypes'] as $_ticketTypeID => $_ticketTypeCountContainer) {
                        $this->XML->AddTag('tickettype', '', array('id' => $_ticketTypeID, 'lastactivity' => $_ticketTypeCountContainer['lastactivity'], 'totalitems' => $_ticketTypeCountContainer['totalitems'], 'totalunresolveditems' => $_ticketTypeCountContainer['totalunresolveditems']));
                    }

                    foreach ($_countContainer['ownerstaff'] as $_staffID => $_ticketOwnerCountContainer) {
                        $this->XML->AddTag('ownerstaff', '', array('id' => $_staffID, 'lastactivity' => $_ticketOwnerCountContainer['lastactivity'], 'totalitems' => $_ticketOwnerCountContainer['totalitems'], 'totalunresolveditems' => $_ticketOwnerCountContainer['totalunresolveditems']));
                    }
                $this->XML->EndParentTag('department');
            }
        }
        $this->XML->EndParentTag('departments');

        /**
           Ticket Status
         */
        $this->XML->AddComment('Ticket Count grouped by Status');
        $this->XML->AddParentTag('statuses');
        if (!isset($_ticketCountCache['ticketstatus'])) {
            $_ticketCountCache['ticketstatus'] = [];
        }
        $ticketstatus = $_ticketCountCache['ticketstatus'];
        if (_is_array($ticketstatus)) {
            foreach ($ticketstatus as $_ticketStatusID => $_ticketStatusCountContainer) {
                $this->XML->AddTag('ticketstatus', '', array('id' => $_ticketStatusID, 'lastactivity' => $_ticketStatusCountContainer['lastactivity'], 'totalitems' => $_ticketStatusCountContainer['totalitems']));
            }
        }
        $this->XML->EndParentTag('statuses');

        /**
           Owner Staff
         */
        $this->XML->AddComment('Ticket Count grouped by Owner Staff');
        $this->XML->AddParentTag('owners');
        if (!isset($_ticketCountCache['ownerstaff'])) {
            $_ticketCountCache['ownerstaff'] = [];
        }
        $ownerstaff = $_ticketCountCache['ownerstaff'];
        foreach ($ownerstaff as $_staffID => $_ticketOwnerCountContainer) {
            $this->XML->AddTag('ownerstaff', '', array('id' => $_staffID, 'lastactivity' => $_ticketOwnerCountContainer['lastactivity'], 'totalitems' => $_ticketOwnerCountContainer['totalitems'], 'totalunresolveditems' => $_ticketOwnerCountContainer['totalunresolveditems']));
        }
        $this->XML->EndParentTag('owners');

        /**
         * Unassigned Tickets
         */
        $this->XML->AddComment('Unassigned Ticket Count grouped by Department');
        $this->XML->AddParentTag('unassigned');

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4567 In case of multiple Ticket departments TicketCount API displays incorrect Results.
         */
        foreach ($departments as $_departmentID => $_ticketOwnerCountContainer) {
            // Skip if no unassigned ticket within department
            if (!isset($_ticketOwnerCountContainer['ownerstaff'][0])) {
                continue;
            }

            $this->XML->AddTag('department', '', array(
                'id'                   => $_departmentID, 'lastactivity' => $_ticketOwnerCountContainer['ownerstaff'][0]['lastactivity'],
                'totalitems'           => $_ticketOwnerCountContainer['ownerstaff'][0]['totalitems'],
                'totalunresolveditems' => $_ticketOwnerCountContainer['ownerstaff'][0]['totalunresolveditems']
            ));
        }

        $this->XML->EndParentTag('unassigned');

        $this->XML->EndParentTag('ticketcount');

        $this->XML->EchoXML();

        return true;
    }
}
