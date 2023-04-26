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
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_XML;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;

/**
 * The TicketTimeTrack API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @author Varun Shoor
 */
class Controller_TicketTimeTrack extends Controller_api implements SWIFT_REST_Interface
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

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * GetList
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

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call /Tickets/TicketTimeTrack/ListAll instead.');

        return true;
    }

    /**
     * Get a list of ticket time tracking entries for the given ticket
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketTimeTrack
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param bool|int $_ticketTimeTrackID (OPTIONAL) To filter result set to a single ticket time track id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_ticketID, $_ticketTimeTrackID = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_querySuffix = '';
        if (!empty($_ticketTimeTrackID)) {
            $_querySuffix .= " AND tickettimetracks.tickettimetrackid = '" . (int) ($_ticketTimeTrackID) . "'";
        }

        $_ticketTimeTrackContainer = array();
        $this->Database->Query("SELECT tickettimetracks.*, tickettimetracknotes.notes AS notes FROM " . TABLE_PREFIX . "tickettimetracks AS tickettimetracks
            LEFT JOIN " . TABLE_PREFIX . "tickettimetracknotes AS tickettimetracknotes ON (tickettimetracks.tickettimetrackid = tickettimetracknotes.tickettimetrackid)
            WHERE tickettimetracks.ticketid = '" . ($_ticketID) . "'" . $_querySuffix);
        while ($this->Database->NextRecord()) {
            $_ticketTimeTrackContainer[$this->Database->Record['tickettimetrackid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('timetracks');
        foreach ($_ticketTimeTrackContainer as $_tID => $_ticketTimeTrack) {
            $this->XML->AddTag('timetrack', $_ticketTimeTrack['notes'], array('id' => $_ticketTimeTrack['tickettimetrackid'], 'ticketid' => $_ticketTimeTrack['ticketid'], 'timeworked' => $_ticketTimeTrack['timespent'], 'timebillable' => $_ticketTimeTrack['timebillable'],
                    'billdate' => $_ticketTimeTrack['dateline'], 'workdate' => $_ticketTimeTrack['workdateline'],
                    'workerstaffid' => $_ticketTimeTrack['workerstaffid'], 'workerstaffname' => $_ticketTimeTrack['workerstaffname'],
                    'creatorstaffid' => $_ticketTimeTrack['creatorstaffid'], 'creatorstaffname' => $_ticketTimeTrack['creatorstaffname'],
                    'notecolor' => $_ticketTimeTrack['notecolor']));
        }
        $this->XML->EndParentTag('timetracks');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Ticket Time Track
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketTimeTrack
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketTimeTrackID The Ticket Time Track ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketID, $_ticketTimeTrackID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_SWIFT_TicketTimeTrackObject = false;

        try {
            $_SWIFT_TicketTimeTrackObject = new SWIFT_TicketTimeTrack(new SWIFT_DataID($_ticketTimeTrackID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket time tracking note not Found');

            return false;
        }

        if ($_SWIFT_TicketTimeTrackObject->GetProperty('ticketid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket time tracking note does not belong to the specified ticket');

            return false;
        }

        $this->ListAll($_ticketID, $_ticketTimeTrackID);

        return true;
    }

    /**
     * Create a new Ticket Time Tracking Entry
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketTimeTrack
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_ticketID = 0;
        if (!isset($_POST['ticketid']) || empty($_POST['ticketid']) || trim($_POST['ticketid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Ticket ID Specified');

            return false;
        }

        $_ticketID = $_POST['ticketid'];

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        if (!isset($_POST['staffid']) || !isset($_staffCache[$_POST['staffid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No staff id specified');

            return false;
        }

        if (!isset($_POST['contents'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No contents specified');

            return false;
        }

        if (!isset($_POST['worktimeline']) || empty($_POST['worktimeline'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No work timeline specified');

            return false;
        }

        if (!isset($_POST['billtimeline']) || empty($_POST['billtimeline'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No bill timeline specified');

            return false;
        }

        if (!isset($_POST['timespent'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No time spent specified');

            return false;
        }

        if (!isset($_POST['timebillable'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No time billable specified');

            return false;
        }

        if (isset($_POST['workerstaffid']) && (empty($_POST['workerstaffid']) || !isset($_staffCache[$_POST['workerstaffid']]))) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid worker staff id specified');

            return false;
        }

        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_POST['staffid']));

        $_SWIFT_StaffObject_Worker = $_SWIFT_StaffObject;
        if (isset($_POST['workerstaffid']) && isset($_staffCache[$_POST['workerstaffid']])) {
            $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_POST['workerstaffid']));
        }

        $_noteContents = $_POST['contents'];

        $_noteColor = 1;
        if (isset($_POST['notecolor']) && !empty($_POST['notecolor'])) {
            $_noteColor = $_POST['notecolor'];
        }

        $_timeSpent = (int) ($_POST['timespent']);
        $_timeBillable = (int) ($_POST['timebillable']);
        $_workDateline = (int) ($_POST['worktimeline']);
        $_billDateline = (int) ($_POST['billtimeline']);

        $_SWIFT_TicketTimeTrackObject = SWIFT_TicketTimeTrack::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject, $_timeSpent, $_timeBillable, $_noteColor, $_noteContents,
                $_SWIFT_StaffObject_Worker, $_workDateline, $_billDateline);

        $this->ListAll($_ticketID, $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID());

        return true;
    }

    /**
     * Delete a Ticket Time Track Entry
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketTimeTrackID The Ticket Time Track ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketID, $_ticketTimeTrackID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_SWIFT_TicketTimeTrackObject = false;

        try {
            $_SWIFT_TicketTimeTrackObject = new SWIFT_TicketTimeTrack(new SWIFT_DataID($_ticketTimeTrackID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket time tracking note not Found');

            return false;
        }

        if ($_SWIFT_TicketTimeTrackObject->GetProperty('ticketid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket time tracking note does not belong to the specified ticket');

            return false;
        }

        SWIFT_TicketTimeTrack::DeleteList(array($_ticketTimeTrackID));

        return true;
    }
}
