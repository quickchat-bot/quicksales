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

namespace Tickets\Api;

use Controller_api;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_User;
use SWIFT_XML;
use Tickets\Library\API\SWIFT_TicketAPIManager;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_TicketAPIManager $TicketAPIManager
 * @property SWIFT_RESTServer $RESTServer
 * @author Varun Shoor
 */
class Controller_Ticket extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * Constructor
     *
     * @author Varun Shoor
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
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @param string $_sortField
     *
     * @return bool
     */
    public static function IsValidSortField($_sortField)
    {
        return in_array(
            strtolower($_sortField),
            array(
                'ticketstatustitle', 'departmenttitle', 'userid', 'ownerstaffid', 'dateline', 'totalreplies',
                'hasattachments', 'hasnotes', 'lastactivity', 'duetime', 'creator', 'isphonecall', 'isescalated',
                'hasfollowup', 'hasratings', 'islinked', 'tickettype', 'tickettypetitle', 'tickettypeid', 'creationmode', 'resolutiondateline', 'isresolved', 'iswatched', 'prioritytitle', 'lastreplier'
            )
        );
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
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call /Tickets/Ticket/ListAll instead.');

        return true;
    }


    /**
     * Get a list of tickets
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     *
     * @param string    $_incomingDepartmentIDList
     * @param int|string    $_incomingTicketStatusIDList (OPTIONAL)
     * @param int|string    $_incomingOwnerStaffIDList   (OPTIONAL)
     * @param int|string    $_incomingTicketUserIDList   (OPTIONAL)
     * @param int       $_rowsPerPage                (OPTIONAL)
     * @param int       $_rowOffset                  (OPTIONAL)
     * @param string    $_sortField                  (OPTIONAL)
     * @param string    $_sortOrder                  (OPTIONAL)
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    public function ListAll(
        $_incomingDepartmentIDList,
        $_incomingTicketStatusIDList = -1,
        $_incomingOwnerStaffIDList = -1,
        $_incomingTicketUserIDList = -1,
        $_rowsPerPage = -1,
        $_rowOffset = 0,
        $_sortField = null,
        $_sortOrder = self::SORT_ASC
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sortField = Clean($_sortField);

        /**
         * BUG FIX - Amarjeet Kaur
         *
         * SWIFT-3324: Pagination Support for API requests
         *
         * Comments: None
         */
        $_sqlQueryList = $this->ProcessTicketRelatedData($_incomingDepartmentIDList, $_incomingTicketStatusIDList, $_incomingOwnerStaffIDList, $_incomingTicketUserIDList);

        $_condition = '';
        if (!empty($_POST['email'])) {
            $_condition = " AND email = '" . $_SWIFT->Database->Escape($_POST['email']) . "'";
        }

        $_sqlExtended  = ' ORDER BY ';
        $_sqlExtended .= IIF(self::IsValidSortField($_sortField), $_SWIFT->Database->Escape($_sortField) . ' ', 'ticketid ');
        $_sqlExtended .= IIF(self::IsValidSortOrder($_sortOrder), $_sortOrder, self::SORT_ASC);

        $_ticketIDList = array();
        $_rowsPerPage  = IIF(!($_rowsPerPage), -1, ($_rowsPerPage));
        $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . "
                                       WHERE " . implode(" AND ", $_sqlQueryList) . $_condition . $_sqlExtended, $_rowsPerPage, ($_rowOffset));

        while ($_SWIFT->Database->NextRecord()) {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        $this->TicketAPIManager->RenderTickets($_ticketIDList, false);

        return true;
    }

    /**
     * Retrieve the Ticket
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $this->TicketAPIManager->RenderTickets(array($_SWIFT_TicketObject->GetTicketID()), true);

        return true;
    }

    /**
     * Create a new Ticket
     *
     * Get a list of tickets
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache   = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketTypeCache     = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_departmentCache     = (array) $_SWIFT->Cache->Get('departmentcache');
        $_staffCache          = (array) $_SWIFT->Cache->Get('staffcache');
        $_ticketFilterCache   = (array) $_SWIFT->Cache->Get('ticketfiltercache');
        $_ticketWorkflowCache = (array) $_SWIFT->Cache->Get('ticketworkflowcache');
        $_templateGroupCache  = (array) $_SWIFT->Cache->Get('templategroupcache');
        $_emailQueueCache     = (array) $_SWIFT->Cache->Get('queuecache');

        if (!isset($_POST['subject']) || empty($_POST['subject']) || trim($_POST['subject']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Subject is empty');

            return false;
        }

        if (!isset($_POST['fullname']) || empty($_POST['fullname']) || trim($_POST['fullname']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Fullname is empty');

            return false;
        }

        if (!isset($_POST['email']) || empty($_POST['email']) || trim($_POST['email']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Email is empty');

            return false;
        }

        if (!isset($_POST['contents']) || empty($_POST['contents']) || trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Contents is empty');

            return false;
        }

        if (!isset($_POST['departmentid']) || empty($_POST['departmentid']) || trim($_POST['departmentid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Department ID is empty');

            return false;
        }

        if (!isset($_POST['ticketstatusid']) || empty($_POST['ticketstatusid']) || trim($_POST['ticketstatusid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Status ID is empty');

            return false;
        }

        if (!isset($_POST['ticketpriorityid']) || empty($_POST['ticketpriorityid']) || trim($_POST['ticketpriorityid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Priority ID is empty');

            return false;
        }

        if (!isset($_POST['tickettypeid']) || empty($_POST['tickettypeid']) || trim($_POST['tickettypeid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Type ID is empty');

            return false;
        }

        if ((!isset($_POST['userid']) || empty($_POST['userid']) || trim($_POST['userid']) == '')
            && (!isset($_POST['staffid']) || empty($_POST['staffid']) || trim($_POST['staffid']) == '')
            && (!isset($_POST['autouserid']) || empty($_POST['autouserid']) || trim($_POST['autouserid']) == '')
        ) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff ID, User ID and Auto User ID values are empty or not provided');

            return false;
        }

        if (!isset($_departmentCache[$_POST['departmentid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Department ID is Invalid');

            return false;
        }

        if ($_departmentCache[$_POST['departmentid']]['departmentapp'] != APP_TICKETS) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Department App is not Tickets');

            return false;
        }

        if (!isset($_ticketStatusCache[$_POST['ticketstatusid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Status ID is Invalid');

            return false;
        }

        if (!isset($_ticketPriorityCache[$_POST['ticketpriorityid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Priority ID is Invalid');

            return false;
        }

        if (!isset($_ticketTypeCache[$_POST['tickettypeid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Type ID is Invalid');

            return false;
        }

        if (isset($_POST['staffid']) && !isset($_staffCache[$_POST['staffid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff ID is Invalid');

            return false;
        }

        $_userID = $_staffID = 0;
        $_creatorType = SWIFT_Ticket::CREATOR_USER;
        $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
        if (isset($_POST['templategroup'])) {
            $_templateGroup = $_POST['templategroup'];
            if (is_numeric($_templateGroup) && isset($_templateGroupCache[$_templateGroup])) {
                $_templateGroupID = $_templateGroup;
            } else {
                if (is_string($_templateGroup)) {
                    $_finalTemplateGroupID = false;
                    foreach ($_templateGroupCache as $_templateGroupID => $_templateGroupContainer) {
                        if (mb_strtolower($_templateGroupContainer['title']) == mb_strtolower($_templateGroup)) {
                            $_finalTemplateGroupID = $_templateGroupID;
                        }
                    }

                    if (!empty($_finalTemplateGroupID)) {
                        $_templateGroupID = $_finalTemplateGroupID;
                    }
                }
            }
        }

        if (isset($_POST['autouserid']) && $_POST['autouserid'] == '1') {
            $_userID = $this->createUserAuto($_templateGroupID, $_POST['fullname'], $_POST['email']);
        } else if (isset($_POST['userid'])) {
            $_userID = (int) ($_POST['userid']);
            /*
             * BUG FIX - Saloni Dhall
             *
             * SWIFT-2920 If argument 'user id' is set for new ticket via API, ticket is displayed under two users accounts.
             *
             * Comments : None
             */
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
                if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                    $_userEmailList = $_SWIFT_UserObject->GetEmailList();
                    if (!in_array($_POST['email'], $_userEmailList)) {
                        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Email is Invalid');

                        return false;
                    }

                    $_POST['fullname'] = $_SWIFT_UserObject->GetFullName();
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                // Do Nothing
            }
        } else if (isset($_POST['staffid'])) {
            $_staffID = (int) ($_POST['staffid']);
            $_userID = $this->createUserAuto($_templateGroupID, $_POST['fullname'], $_POST['email']);
            $_creatorType = SWIFT_Ticket::CREATOR_STAFF;
        }

        $_ticketType = SWIFT_Ticket::TYPE_DEFAULT;
        if (isset($_POST['type']) && $_POST['type'] === 'phone') {
            $_ticketType = SWIFT_Ticket::TYPE_PHONE;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4387 When ticket is created via RestAPI, a new option to set email queue id need to implement.
         *
         * Comments: Only email queues those are linked with department will be selected.
         */
        // Setting email queue ID
        $_emailQueueID = 0;
        if (isset($_POST['emailqueueid']) && isset($_emailQueueCache['list'][$_POST['emailqueueid']])) {
            $_emailQueueID = $_POST['emailqueueid'];
        }

        $_creatorEmailAddress = $_POST['email'];
        $_creatorFullName = $_POST['fullname'];

        $_SWIFT_StaffObject = false;
        if (!empty($_staffID)) {
            try {
                $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        $_emailTo = '';
        $_ownerStaffID = 0;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded() && $_ticketType == SWIFT_Ticket::TYPE_DEFAULT && isset($_POST['email']) && IsEmailValid($_POST['email'])) {
            $_emailTo = $_POST['email'];

            $_creatorEmailAddress = $_SWIFT_StaffObject->GetProperty('email');
            $_creatorFullName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::Create(
            $_POST['subject'],
            $_creatorFullName,
            $_creatorEmailAddress,
            $_POST['contents'],
            $_ownerStaffID,
            $_POST['departmentid'],
            $_POST['ticketstatusid'],
            $_POST['ticketpriorityid'],
            $_POST['tickettypeid'],
            $_userID,
            $_staffID,
            $_ticketType,
            $_creatorType,
            SWIFT_Ticket::CREATIONMODE_API,
            '',
            $_emailQueueID,
            false,
            $_emailTo
        );

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Creation Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        if (!isset($_POST['templategroup'])) {
            // Attempt to retrieve template group id based on department
            $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
            if (!empty($_POST['departmentid']) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
                foreach ($_emailQueueCache['list'] as $_emailQueueID => $_emailQueueContainer) {
                    if ($_emailQueueContainer['departmentid'] == $_POST['departmentid'] && $_emailQueueContainer['isenabled'] == '1') {
                        $_templateGroupID = $_emailQueueContainer['tgroupid'];
                    }
                }
            }
            // Attempt to retrieve template group id based on user
            if (!empty($_userID)) {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
                if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                    $_templateGroupID = $_SWIFT_UserObject->GetTemplateGroupID();
                }
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4268 Exception with ticket recurrence when 'Create ticket as user' is enabled.
         *
         * Comments: Set the default template group in case system is unable to found associated one.
         */
        if (!empty($_templateGroupID) && isset($_templateGroupCache[$_templateGroupID])) {
            $_SWIFT_TicketObject->SetTemplateGroup($_templateGroupID);
        } else {
            $_userTemplateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
            $_SWIFT_TicketObject->SetTemplateGroup($_userTemplateGroupID);
        }

        if (isset($_POST['ownerstaffid']) && isset($_staffCache[$_POST['ownerstaffid']])) {
            $_SWIFT_TicketObject->SetOwner($_POST['ownerstaffid']);
        }

        $_SWIFT_TicketObject->ProcessUpdatePool();

        /*
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-1903 No option to send email using API
         *
         */
        if ($_ticketType == SWIFT_Ticket::TYPE_DEFAULT && $_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature(false, $_SWIFT_StaffObject);
            $_signatureContentsHTML = $_SWIFT_TicketObject->GetSignature(true, $_SWIFT_StaffObject);

            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);

            $_ticketDispatchContents = $_POST['contents'] . SWIFT_CRLF;
            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply($_SWIFT_StaffObject, $_ticketDispatchContents, false, false, array($_signatureContentsDefault, $_signatureContentsHTML));
        } else if (!isset($_POST['ignoreautoresponder']) || (isset($_POST['ignoreautoresponder']) && $_POST['ignoreautoresponder'] == '0')) {
            $_SWIFT_TicketObject->DispatchAutoresponder();
        }

        $this->TicketAPIManager->RenderTickets(array($_SWIFT_TicketObject->GetTicketID()), true);

        return true;
    }

    /**
     * Update a Ticket
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');
        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_staffCache = (array) $this->Cache->Get('staffcache');
        $_templateGroupCache = (array) $_SWIFT->Cache->Get('templategroupcache');

        if (isset($_POST['departmentid']) && isset($_departmentCache[$_POST['departmentid']]) && $_departmentCache[$_POST['departmentid']]['departmentapp'] == APP_TICKETS) {
            $_SWIFT_TicketObject->SetDepartment($_POST['departmentid']);
        }

        if (isset($_POST['ticketstatusid']) && isset($_ticketStatusCache[$_POST['ticketstatusid']])) {
            $_SWIFT_TicketObject->SetStatus($_POST['ticketstatusid']);
        }

        if (isset($_POST['ticketpriorityid']) && isset($_ticketPriorityCache[$_POST['ticketpriorityid']])) {
            $_SWIFT_TicketObject->SetPriority($_POST['ticketpriorityid']);
        }

        if (isset($_POST['tickettypeid']) && isset($_ticketTypeCache[$_POST['tickettypeid']])) {
            $_SWIFT_TicketObject->SetType($_POST['tickettypeid']);
        }

        if (isset($_POST['ownerstaffid']) && ($_POST['ownerstaffid'] == '0' || isset($_staffCache[$_POST['ownerstaffid']]))) {
            $_SWIFT_TicketObject->SetOwner($_POST['ownerstaffid']);
        }

        if (isset($_POST['fullname']) || isset($_POST['email']) || isset($_POST['subject'])) {
            $_fullName = $_SWIFT_TicketObject->GetProperty('fullname');
            $_email = $_SWIFT_TicketObject->GetProperty('email');
            $_subject = $_SWIFT_TicketObject->GetProperty('subject');
            $_update_replyto = false;

            if (isset($_POST['fullname'])) {
                $_fullName = $_POST['fullname'];
            }

            if (isset($_POST['email'])) {
                // only update replyto field if the email has changed
                $_update_replyto = ($_POST['email'] !== $_email);
                $_email = $_POST['email'];
            }

            if (isset($_POST['subject'])) {
                $_subject = $_POST['subject'];
            }

            $_SWIFT_TicketObject->Update($_subject, $_fullName, $_email, $_update_replyto);
        }

        if (isset($_POST['userid']) && !empty($_POST['userid'])) {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_POST['userid']));

                if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                    $_SWIFT_TicketObject->UpdateUser($_POST['userid']);
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if (isset($_POST['templategroup'])) {
            $_templateGroup = $_POST['templategroup'];
            if (is_numeric($_templateGroup) && isset($_templateGroupCache[$_templateGroup])) {
                $_templateGroupID = $_templateGroup;
            } else if (is_string($_templateGroup)) {
                $_finalTemplateGroupID = false;
                foreach ($_templateGroupCache as $_templateGroupID => $_templateGroupContainer) {
                    if (mb_strtolower($_templateGroupContainer['title']) == mb_strtolower($_templateGroup)) {
                        $_finalTemplateGroupID = $_templateGroupID;
                    }
                }

                if (!empty($_finalTemplateGroupID)) {
                    $_templateGroupID = $_finalTemplateGroupID;
                }
            }

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-4268 Exception with ticket recurrence when 'Create ticket as user' is enabled.
             *
             * Comments: Set the default template group in case system is unable to found associated one.
             */
            if (!empty($_templateGroupID)) {
                $_SWIFT_TicketObject->SetTemplateGroup($_templateGroupID);
            } else {
                $_userTemplateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
                $_SWIFT_TicketObject->SetTemplateGroup($_userTemplateGroupID);
            }
        }


        $_SWIFT_TicketObject->ProcessUpdatePool();

        $this->TicketAPIManager->RenderTickets(array($_SWIFT_TicketObject->GetTicketID()), true);

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Delete a Ticket
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_departmentID = $_SWIFT_TicketObject->GetProperty('departmentid');

        SWIFT_Ticket::DeleteList(array($_ticketID));

        SWIFT_TicketManager::RebuildCache(array($_departmentID));

        return true;
    }

    /**
     * Retrieve ID List from CSV
     *
     * @author Varun Shoor
     * @param string $_csvIDList
     * @return array The ID List as an Array
     */
    public static function RetrieveIDListFromCSV($_csvIDList)
    {
        if (is_numeric($_csvIDList)) {
            return array((int) ($_csvIDList));
        }

        if (stristr($_csvIDList, ',')) {
            $_chunkIDList = explode(',', $_csvIDList);

            $_finalChunkIDList = array();

            foreach ($_chunkIDList as $_value) {
                if (is_numeric($_value)) {
                    $_finalChunkIDList[] = (int) ($_value);
                }
            }

            return $_finalChunkIDList;
        }

        return array(0);
    }

    /**
     * Process Ticket Related Data
     *
     * @author Amarjeet Kaur
     *
     * @param string $_incomingDepartmentIDList
     * @param int|string $_incomingTicketStatusIDList (OPTIONAL)
     * @param int|string $_incomingOwnerStaffIDList   (OPTIONAL)
     * @param int|string $_incomingTicketUserIDList   (OPTIONAL)
     *
     * @return array $_sqlQueryList
     * @throws SWIFT_Exception If Class is not loaded
     */
    public function ProcessTicketRelatedData($_incomingDepartmentIDList, $_incomingTicketStatusIDList = -1, $_incomingOwnerStaffIDList = -1, $_incomingTicketUserIDList = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache   = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketTypeCache     = (array) $_SWIFT->Cache->Get('tickettypecache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_departmentCache     = (array) $_SWIFT->Cache->Get('departmentcache');
        $_staffCache          = (array) $_SWIFT->Cache->Get('staffcache');
        $_ticketFilterCache   = (array) $_SWIFT->Cache->Get('ticketfiltercache');
        $_ticketWorkflowCache = (array) $_SWIFT->Cache->Get('ticketworkflowcache');

        $_departmentIDList = self::RetrieveIDListFromCSV($_incomingDepartmentIDList);

        $_ticketStatusIDList = array();
        if ($_incomingTicketStatusIDList != -1) {
            $_ticketStatusIDList = self::RetrieveIDListFromCSV($_incomingTicketStatusIDList);
        }

        $_ownerStaffIDList = array();
        if ($_incomingOwnerStaffIDList != -1) {
            $_ownerStaffIDList = self::RetrieveIDListFromCSV($_incomingOwnerStaffIDList);
        }

        $_finalTicketUserIDList = array();
        if ($_incomingTicketUserIDList != -1) {
            $_finalTicketUserIDList = self::RetrieveIDListFromCSV($_incomingTicketUserIDList);
        }

        $_finalDepartmentIDList = $_finalTicketStatusIDList = $_finalOwnerStaffIDList = array();

        /**
         * ---------------------------------------------
         * Process Departments
         * ---------------------------------------------
         */

        foreach ($_departmentIDList as $_departmentID) {
            if (isset($_departmentCache[$_departmentID])) {
                $_finalDepartmentIDList[] = $_departmentID;
            }
        }

        /**
         * ---------------------------------------------
         * Process Ticket Status
         * ---------------------------------------------
         */
        foreach ($_ticketStatusIDList as $_ticketStatusID) {
            if (isset($_ticketStatusCache[$_ticketStatusID]) && ($_ticketStatusCache[$_ticketStatusID]['departmentid'] == '0' || in_array($_ticketStatusCache[$_ticketStatusID]['departmentid'], $_finalDepartmentIDList))) {
                $_finalTicketStatusIDList[] = $_ticketStatusID;
            }
        }

        // If no ticket status'es were received, we use the unresolved ones
        if (!count($_finalTicketStatusIDList)) {
            foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
                if (($_ticketStatus['departmentid'] == '0' || in_array($_ticketStatusID, $_finalDepartmentIDList)) && $_ticketStatus['markasresolved'] == '0') {
                    $_finalTicketStatusIDList[] = $_ticketStatusID;
                }
            }
        }

        /**
         * ---------------------------------------------
         * Process Owner Staff
         * ---------------------------------------------
         */

        foreach ($_ownerStaffIDList as $_staffID) {
            if ($_staffID == '0' || isset($_staffCache[$_staffID])) {
                $_finalOwnerStaffIDList[] = $_staffID;
            }
        }

        $_finalOwnerStaffIDList = $_ownerStaffIDList;

        // Process using Department ID List and other variables
        $_sqlQueryList   = array();
        $_sqlQueryList[] = "departmentid IN (" . BuildIn($_finalDepartmentIDList) . ")";

        if (count($_finalTicketStatusIDList)) {
            $_sqlQueryList[] = "ticketstatusid IN (" . BuildIn($_finalTicketStatusIDList) . ")";
        }

        if (count($_finalOwnerStaffIDList)) {
            $_sqlQueryList[] = "ownerstaffid IN (" . BuildIN($_finalOwnerStaffIDList) . ")";
        }

        if (count($_finalTicketUserIDList)) {
            $_sqlQueryList[] = "userid IN (" . BuildIN($_finalTicketUserIDList) . ")";
        }

        return $_sqlQueryList;
    }

    /**
     * Get Total Tickets Count
     *
     * @author Amarjeet Kaur
     *
     * @param string $_incomingDepartmentIDList
     * @param int|string $_incomingTicketStatusIDList (OPTIONAL)
     * @param int|string $_incomingOwnerStaffIDList   (OPTIONAL)
     * @param int|string $_incomingTicketUserIDList   (OPTIONAL)
     *
     * @return bool
     * @throws SWIFT_Exception If Class is not Loaded
     */
    public function GetTicketCount($_incomingDepartmentIDList, $_incomingTicketStatusIDList = -1, $_incomingOwnerStaffIDList = -1, $_incomingTicketUserIDList = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sqlQueryList = $this->ProcessTicketRelatedData($_incomingDepartmentIDList, $_incomingTicketStatusIDList, $_incomingOwnerStaffIDList, $_incomingTicketUserIDList);

        $_condition = '';
        if (!empty($_POST['email'])) {
            $_condition = " AND email = '" . $_SWIFT->Database->Escape($_POST['email']) . "'";
        }

        $_ticketContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totaltickets FROM " . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . "
                                                           WHERE " . implode(" AND ", $_sqlQueryList) . $_condition);

        $this->XML->AddParentTag('tickets');

        $this->XML->AddTag('totalcount', $_ticketContainer['totaltickets']);

        $this->XML->EndParentTag('tickets');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Creates a new user or gets userid from email in POST
     *
     * @param int $_templateGroupID
     * @param string $fullname
     * @param string $email
     * @return int
     * @throws SWIFT_Exception
     * @throws \Base\Library\Template\SWIFT_Template_Exception
     * @author Werner Garcia
     */
    public function createUserAuto($_templateGroupID, $fullname, $email)
    {
        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception('Unable to load master template group object');
            // @codeCoverageIgnoreEnd
        }

        $_userID = SWIFT_Ticket::GetOrCreateUserID(
            $fullname,
            $email,
            $_SWIFT_TemplateGroupObject->GetRegisteredUserGroupID()
        );

        return $_userID;
    }
}
