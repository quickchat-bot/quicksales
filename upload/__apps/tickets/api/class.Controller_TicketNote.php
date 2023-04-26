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

namespace Tickets\Api;

use Controller_api;
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use SWIFT_XML;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The TicketNote API Controller
 *
 * @property SWIFT_RESTServer $RESTServer
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketNote extends Controller_api implements SWIFT_REST_Interface
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

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call /Tickets/TicketNote/ListAll instead.');

        return true;
    }


    /**
     * Get a list of ticket notes for the given ticket
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+TicketNote
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param bool|int $_ticketNoteID (OPTIONAL) To filter result set to a single ticket note id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_ticketID, $_ticketNoteID = false)
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
        if (!empty($_ticketNoteID)) {
            $_querySuffix .= " AND ticketnoteid = '" . (int) ($_ticketNoteID) . "'";
        }

        $_ticketNoteContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketnotes
             WHERE linktype = '" . SWIFT_TicketNote::LINKTYPE_TICKET . "' AND linktypeid = '" . ($_ticketID) . "'" . $_querySuffix);
        while ($this->Database->NextRecord()) {
            $_ticketNoteContainer[$this->Database->Record['ticketnoteid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('notes');
        foreach ($_ticketNoteContainer as $_tID => $_ticketNote) {
            $this->XML->AddTag('note', $_ticketNote['note'], array('type' => 'ticket', 'id' => $_tID, 'ticketid' => $_ticketNote['linktypeid'],
                'notecolor' => $_ticketNote['notecolor'],
                'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => $_ticketNote['forstaffid'],
                'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
        }
        $this->XML->EndParentTag('notes');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Ticket Note
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+TicketNote
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketNoteID The Ticket Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketID, $_ticketNoteID)
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

        $_SWIFT_TicketNoteObject = false;

        try {
            $_SWIFT_TicketNoteObject = new SWIFT_TicketNote($_ticketNoteID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket note not Found');

            return false;
        }

        if ($_SWIFT_TicketNoteObject->GetProperty('linktype') != SWIFT_TicketNote::LINKTYPE_TICKET
            || $_SWIFT_TicketNoteObject->GetProperty('linktypeid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket note does not belong to the specified ticket');

            return false;
        }

        $this->ListAll($_ticketID, $_ticketNoteID);

        return true;
    }

    /**
     * Create a new Ticket Note
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+TicketNote
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        $_SWIFT = SWIFT::GetInstance();

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

        if ((!isset($_POST['fullname']) || empty($_POST['fullname']) || trim($_POST['fullname']) == '')
            && (!isset($_POST['staffid']) || !isset($_staffCache[$_POST['staffid']]))) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No fullname or staff id specified');

            return false;
        }

        if (!isset($_POST['contents'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No contents specified');

            return false;
        }

        $_staffName = $_staffEmail = '';
        $_staffID = 0;
        if (isset($_POST['staffid']) && isset($_staffCache[$_POST['staffid']])) {
            $_staffName = $_staffCache[$_POST['staffid']]['fullname'];
            $_staffID = $_POST['staffid'];
            $_staffEmail = $_staffCache[$_POST['staffid']]['email'];
        } else if (isset($_POST['fullname'])) {
            $_staffName = $_POST['fullname'];
        } else {
          // @codeCoverageIgnoreStart
          // this code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No fullname or staff id specified');

            return false;
          // @codeCoverageIgnoreEnd
        }

        $_forStaffID = 0;
        if (isset($_POST['forstaffid']) && isset($_staffCache[$_POST['forstaffid']])) {
            $_forStaffID = $_POST['forstaffid'];
        }

        $_noteContents = $_POST['contents'];

        $_noteColor = 1;
        if (isset($_POST['notecolor']) && !empty($_POST['notecolor'])) {
            $_noteColor = $_POST['notecolor'];
        }

        $_ticketNoteID = SWIFT_TicketNote::Create($_SWIFT_TicketObject, $_forStaffID, $_staffID, $_staffName, $_noteContents, $_noteColor);
        /*
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-3012 No entry in audit logs for the ticket notes, added via API
         *
         */
        if (!empty($_ticketNoteID)) {
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null,
	            SWIFT_TicketAuditLog::ACTION_NEWNOTE, $_SWIFT->Language->Get('al_ticketnote'),
	            SWIFT_TicketAuditLog::VALUE_NONE,
	            0, '', 0, '', ['al_ticketnote']);
        }

        /*
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-2580 When a ticket note is added via API, notification for the New Ticket Note is not triggered.
         *
         */
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');

        $_SWIFT_TicketObject->SetWatcherProperties($_staffName, sprintf($_SWIFT->Language->Get('watcherprefix'), $_staffName, $_staffEmail) . SWIFT_CRLF . $_POST['contents']);

        $this->ListAll($_ticketID, $_ticketNoteID);

        return true;
    }

    /**
     * Delete a Ticket Note
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketNoteID The Ticket Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketID, $_ticketNoteID)
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

        $_SWIFT_TicketNoteObject = false;

        try {
            $_SWIFT_TicketNoteObject = new SWIFT_TicketNote($_ticketNoteID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket note not Found');

            return false;
        }

        if ($_SWIFT_TicketNoteObject->GetProperty('linktype') != SWIFT_TicketNote::LINKTYPE_TICKET
            || $_SWIFT_TicketNoteObject->GetProperty('linktypeid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket note does not belong to the specified ticket');

            return false;
        }

        SWIFT_TicketNote::DeleteList(array($_ticketNoteID));

        return true;
    }
}
