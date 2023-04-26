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
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use SWIFT_XML;
use Tickets\Library\API\SWIFT_TicketAPIManager;
use Tickets\Library\Search\SWIFT_TicketSearchManager;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The TicketSearch API Controller
 *
 * @property SWIFT_TicketAPIManager $TicketAPIManager
 * @property SWIFT_RESTServer $RESTServer
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketSearch extends Controller_api implements SWIFT_REST_Interface
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

        $this->Load->Library('API:TicketAPIManager', array($this->XML), true, false, 'tickets');

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Initiate the Ticket Search
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketSearch
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

        $_query = '';
        if (isset($_POST['query']) && !empty($_POST['query'])) {
            $_query = $_POST['query'];
        }

        $_finalTicketIDList = $_ticketIDList = array();

        $_ticketID = $_contents = $_author = $_email = $_fullName = $_notes = $_userGroup = $_userOrganization = $_user = $_tags = $_creatorEmail = $_phrase = false;

        if (isset($_POST['ticketid']) && $_POST['ticketid'] == '1') {
            $_ticketID = true;
        }

        if (isset($_POST['contents']) && $_POST['contents'] == '1') {
            $_contents = true;
        }

        if (isset($_POST['phrase']) && $_POST['phrase'] == '1') {
            $_phrase = true;
        }

        if (isset($_POST['author']) && $_POST['author'] == '1') {
            $_author = true;
        }

        if (isset($_POST['email']) && $_POST['email'] == '1') {
            $_email = true;
        }

        if (isset($_POST['creatoremail']) && $_POST['creatoremail'] == '1') {
            $_creatorEmail = true;
        }

        if (isset($_POST['fullname']) && $_POST['fullname'] == '1') {
            $_fullName = true;
        }

        if (isset($_POST['notes']) && $_POST['notes'] == '1') {
            $_notes = true;
        }

        if (isset($_POST['usergroup']) && $_POST['usergroup'] == '1') {
            $_userGroup = true;
        }

        if (isset($_POST['userorganization']) && $_POST['userorganization'] == '1') {
            $_userOrganization = true;
        }

        if (isset($_POST['user']) && $_POST['user'] == '1') {
            $_user = true;
        }

        if (isset($_POST['tags']) && $_POST['tags'] == '1') {
            $_tags = true;
        }

        // Search Ticket ID?
        if ($_ticketID == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchTicketID($_query));
        }

        // Search Contents?
        if ($_contents == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::QuickSearch($_query, false, false, !$_phrase));
        }

        // Search Author?
        if ($_author == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchCreator($_query));
        }

        // Search Email?
        if ($_email == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchEmail($_query));
        }

        // Search Creator Email?
        if ($_creatorEmail == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchCreatorEmail($_query));
        }

        // Search Full Name?
        if ($_fullName == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::SearchFullName($_query));
        }

        // Search Notes?
        if ($_notes == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchTicketNotes($_query));
        }

        // Search User Group?
        if ($_userGroup == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchUserGroup($_query));
        }

        // Search User Organizations?
        if ($_userOrganization == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchUserOrganization($_query));
        }

        // Search Users?
        if ($_user == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchUser($_query));
        }

        // Search Tags?
        if ($_tags == true) {
            $_ticketIDList = array_merge($_ticketIDList, SWIFT_TicketSearchManager::GetSearchTags($_query));
        }

        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")", $_SWIFT->Settings->Get('t_resultlimit'));
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        $this->TicketAPIManager->RenderTickets($_finalTicketIDList, true);

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketSearch
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

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Tickets/TicketSearch instead.');

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketSearch
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Tickets/TicketSearch instead.');

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketSearch
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Tickets/TicketSearch instead.');

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+TicketSearch
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Tickets/TicketSearch instead.');

        return true;
    }
}
